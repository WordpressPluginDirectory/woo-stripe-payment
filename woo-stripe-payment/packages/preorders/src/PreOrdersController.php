<?php

namespace PaymentPlugins\Stripe\WooCommercePreOrders;

use PaymentPlugins\Stripe\ContextHandler;
use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;

class PreOrdersController {

	/**
	 * @var PaymentController
	 */
	private $payment_controller;

	/**
	 * @var ContextHandler
	 */
	private $context;

	private $registry;

	public function __construct( PaymentController $payment_controller, ContextHandler $context, PaymentGatewayRegistry $registry ) {
		$this->payment_controller = $payment_controller;
		$this->context            = $context;
		$this->registry           = $registry;
	}

	public function initialize() {
		if ( $this->registry->is_initialized() ) {
			$this->register_gateways( $this->registry );
		} else {
			add_action( 'wc_stripe_payment_gateways_registered', [ $this, 'register_gateways' ] );
		}
		add_filter( 'wc_stripe_process_payment_result', [ $this, 'process_payment' ], 10, 3 );
		add_filter( 'wc_stripe_show_save_payment_method', [ $this, 'show_save_payment_method' ] );

		add_filter( 'wc_stripe_bnpl_shop_message_gateways', [ $this, 'filter_shop_message_gateways' ], 10, 2 );
	}

	public function register_gateways( PaymentGatewayRegistry $registry ) {
		foreach ( $registry->get_registered_integrations() as $integration ) {
			add_action(
				'wc_pre_orders_process_pre_order_completion_payment_' . $integration->id,
				function ( $order ) use ( $integration ) {
					$this->process_pre_order_payment( $order, $integration );
				},
				10, 2
			);
		}
	}

	public function process_payment( $result, \WC_Order $order, AbstractGateway $gateway ) {
		if ( \WC_Pre_Orders_Order::order_contains_pre_order( $order ) ) {
			if ( \WC_Pre_Orders_Order::order_requires_payment_tokenization( $order ) ) {
				$result = $this->payment_controller->process_payment( $order, $gateway );
			}
		}

		return $result;
	}

	public function process_pre_order_payment( \WC_Order $order, AbstractGateway $gateway ) {
		$gateway->processing_payment = true;

		$result = $this->payment_controller->process_pre_order_payment( $order, $gateway );

		if ( is_wp_error( $result ) ) {
			$order->update_status( 'failed' );
			$order->add_order_note( sprintf( __( 'Pre-order payment for order failed. Reason: %s', 'woo-stripe-payment' ), $result->get_error_message() ) );
		} else {
			if ( $result->complete_payment ) {
				$gateway->save_order_meta( $order, $result->charge );

				if ( $result->charge->captured ) {
					if ( $result->charge->status === 'pending' ) {
						$order->update_status( apply_filters( 'wc_stripe_pending_preorder_order_status', 'on-hold', $order, $gateway ), __( 'Pre-order payment initiated in Stripe. Waiting for the payment to clear.', 'woo-stripe-payment' ) );
					} else {
						\WC_Stripe_Utils::add_balance_transaction_to_order( $result->charge, $order );
						$order->payment_complete( $result->charge->id );
						$order->add_order_note( sprintf( __( 'Pre-order payment captured in Stripe. Payment method: %s', 'woo-stripe-payment' ), $order->get_payment_method_title() ) );
					}
				} else {
					$order->update_status( apply_filters( 'wc_stripe_authorized_preorder_order_status', 'on-hold', $order, $gateway ),
						sprintf( __( 'Pre-order payment authorized in Stripe. Payment method: %s', 'woo-stripe-payment' ), $order->get_payment_method_title() ) );
				}
			} else {
				$order->update_status( 'pending', sprintf( __( 'Customer must manually complete payment for payment method %s', 'woo-stripe-payment' ), $order->get_payment_method_title() ) );
			}
		}
	}

	/**
	 * @param $show
	 *
	 * @return bool
	 */
	public function show_save_payment_method( $show ) {
		if ( ! $show ) {
			return $show;
		}
		if ( $this->context->is_checkout() ) {
			if ( \WC_Pre_Orders_Cart::cart_contains_pre_order() ) {
				if ( \WC_Pre_Orders_Product::product_is_charged_upon_release( \WC_Pre_Orders_Cart::get_pre_order_product() ) ) {
					$show = false;
				}
			}
		}

		return $show;
	}

	/**
	 * @param AbstractGateway[] $gateways
	 * @param \WC_Product       $product
	 *
	 * @return AbstractGateway[]
	 */
	public function filter_shop_message_gateways( $gateways, $product ) {
		if ( \WC_Pre_Orders_Product::product_can_be_pre_ordered( $product ) ) {
			if ( \WC_Pre_Orders_Product::product_is_charged_upon_release( $product ) ) {
				foreach ( $gateways as $gateway ) {
					if ( ! $gateway->supports( 'pre-orders' ) ) {
						unset( $gateways[ $gateway->id ] );
					}
				}
			}
		}

		return $gateways;
	}
}