<?php

namespace PaymentPlugins\Stripe\WooCommerceSubscriptions;

use PaymentPlugins\Stripe\ContextHandler;
use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;
use PaymentPlugins\Stripe\Registry\BaseRegistry;
use WC_Product;

/**
 * @since 4.0.0
 */
class SubscriptionsController {

	private $payment_controller;

	/**
	 * @var ContextHandler
	 */
	private $context_handler;

	/**
	 * @var PaymentGatewayRegistry
	 */
	private $registry;

	public function __construct( PaymentController $payment_controller, ContextHandler $context_handler, PaymentGatewayRegistry $registry ) {
		$this->payment_controller = $payment_controller;
		$this->context_handler    = $context_handler;
		$this->registry           = $registry;
	}

	public function initialize() {
		// Register all applicable actions and filters
		if ( $this->registry->is_initialized() ) {
			$this->register_gateways( $this->registry );
		} else {
			add_action( 'wc_stripe_payment_gateways_registered', [ $this, 'register_gateways' ] );
		}

		add_filter( 'wc_stripe_process_payment_result', [ $this, 'process_payment' ], 10, 3 );

		add_filter( 'wc_stripe_create_payment_method_return_url', [ $this, 'add_change_payment_method_query' ], 10, 2 );

		add_filter( 'wc_stripe_process_redirect_change_payment_method', [
			$this,
			'process_change_payment_method_redirect'
		], 10, 3 );

		add_filter( 'wc_stripe_show_save_payment_method', [ $this, 'show_save_payment_method' ], 10, 2 );

		add_filter( 'wc_stripe_should_save_payment_method', [ $this, 'should_save_payment_method' ], 10, 3 );

		add_filter( 'wc_stripe_bnpl_message_gateways', [ $this, 'filter_message_gateways' ], 10, 2 );

		add_filter( 'wc_stripe_bnpl_shop_message_gateways', [ $this, 'filter_shop_message_gateways' ], 10, 2 );

		add_filter( 'wc_stripe_cart_shipping_packages', [ $this, 'get_shipping_packages' ] );
	}

	public function register_gateways( PaymentGatewayRegistry $registry ) {
		foreach ( $registry->get_registered_integrations() as $integration ) {
			add_action(
				'woocommerce_scheduled_subscription_payment_' . $integration->id,
				function ( $amount, $order ) use ( $integration ) {
					$this->scheduled_subscription_payment( $amount, $order, $integration );
				},
				10, 2
			);
			add_action(
				'woocommerce_subscription_failing_payment_method_updated_' . $integration->id,
				function ( $subscription, $order ) use ( $integration ) {
					$this->update_failing_payment_method( $subscription, $order, $integration );
				},
				10, 2
			);
			add_filter(
				'woocommerce_subscription_payment_meta',
				function ( $payment_meta, $subscription ) use ( $integration ) {
					return $this->add_subscription_payment_meta( $payment_meta, $subscription, $integration );
				}, 10, 2
			);
		}
	}

	/**
	 * @param                 $result
	 * @param \WC_Order       $order
	 * @param AbstractGateway $payment_method
	 *
	 * @return array|bool
	 */
	public function process_payment( $result, \WC_Order $order, AbstractGateway $payment_method ) {
		if ( $this->is_change_payment_method_request() && \wcs_is_subscription( $order ) ) {
			$result = $this->payment_controller->process_change_payment_method( $order, $payment_method );
		} elseif ( wcs_order_contains_subscription( $order ) || wcs_order_contains_renewal( $order ) ) {
			if ( $order->get_total() <= 0 ) {
				/**
				 * Gateways that don't support 'subscriptions' can't be saved for future off-session
				 * use, so creating a setup intent for them (as PaymentController::process_payment()
				 * would) results in a Stripe API error. There's nothing to save for them anyway under
				 * manual renewals - the customer will pay fresh when the real renewal is due - so just
				 * complete the zero total order directly.
				 */
				if ( $payment_method->supports( 'subscriptions' ) ) {
					$result = $this->payment_controller->process_payment( $order, $payment_method );
				} else {
					$result = $this->payment_controller->process_zero_total_order( $order, $payment_method );
				}
			}
		}

		return $result;
	}

	/**
	 * @param float           $amount
	 * @param \WC_Order       $order
	 * @param AbstractGateway $gateway
	 *
	 * @return void
	 */
	private function scheduled_subscription_payment( $amount, $order, $gateway ) {
		$gateway->processing_payment = true;

		$result = $this->payment_controller->scheduled_subscription_payment( $amount, $order, $gateway );

		if ( is_wp_error( $result ) ) {
			$order->update_status( 'failed' );
			$order->add_order_note( sprintf( __( 'Recurring payment for order failed. Reason: %s', 'woo-stripe-payment' ), $result->get_error_message() ) );

			return;
		}

		if ( $result->charge ) {
			$gateway->save_order_meta( $order, $result->charge );
		}

		// set the payment method token that was used to process the renewal order.
		$gateway->payment_method_token = $order->get_meta( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN );

		if ( $result->complete_payment ) {
			if ( $result->charge ) {
				if ( $result->charge->captured ) {
					if ( $result->charge->status === 'pending' ) {
						// pending status means this is an asynchronous payment method.
						$order->update_status( apply_filters( 'wc_stripe_renewal_pending_order_status', 'on-hold', $order, $gateway, $result->charge ), __( 'Renewal payment initiated in Stripe. Waiting for the payment to clear.', 'woo-stripe-payment' ) );
					} else {
						\WC_Stripe_Utils::add_balance_transaction_to_order( $result->charge, $order );
						$order->payment_complete( $result->charge->id );
						$order->add_order_note( sprintf( __( 'Recurring payment captured in Stripe. Payment method: %s', 'woo-stripe-payment' ), $order->get_payment_method_title() ) );
					}
				} else {
					$order->update_status( apply_filters( 'wc_stripe_authorized_renewal_order_status', 'on-hold', $order, $gateway ),
						sprintf( __( 'Recurring payment authorized in Stripe. Payment method: %s', 'woo-stripe-payment' ), $order->get_payment_method_title() ) );
				}
			} else {
				$order->update_status( 'on-hold' );
			}
		} else {
			$order->update_status( 'pending', sprintf( __( 'Customer must manually complete payment for payment method %s', 'woo-stripe-payment' ), $order->get_payment_method_title() ) );
		}
	}

	/**
	 * @param \WC_Subscription $subscription
	 * @param \WC_Order        $order
	 * @param AbstractGateway  $integration
	 *
	 * @return void
	 */
	private function update_failing_payment_method( $subscription, $order, $gateway ) {
		$payment_method_token = $order->get_meta( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN );
		if ( $payment_method_token ) {
			$subscription->update_meta_data( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN, $payment_method_token );
			$token = $gateway->get_token( $order->get_meta( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN ), $order->get_customer_id() );
			if ( $token ) {
				$subscription->update_meta_data( \WC_Stripe_Constants::CUSTOMER_ID, $token->get_customer_id() );
				$subscription->set_payment_method_title( $token->get_payment_method_title( $gateway->get_option( 'method_format' ) ) );
				$gateway_id = $token->get_gateway_id();
				if ( $gateway_id && $gateway_id !== $gateway->id ) {
					$subscription->set_payment_method( $gateway_id );
				}
			}
			$subscription->save();
		}
	}

	private function is_change_payment_method_request() {
		if ( isset( $_POST['_wcsnonce'] ) &&
		     wp_verify_nonce( wc_clean( wp_unslash( $_POST['_wcsnonce'] ) ), 'wcs_change_payment_method' )
		     && defined( \WC_Stripe_Constants::PROCESSING_ORDER_PAY ) ) {
			return true;
		}

		return did_action( 'woocommerce_subscriptions_pre_update_payment_method' )
		       || \WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment;
	}

	/**
	 * @param                            $url
	 * @param \WC_Payment_Gateway_Stripe $gateway
	 * @param                            $page
	 *
	 * @return mixed|string
	 */
	public function add_change_payment_method_query( $url, $gateway ) {
		if ( $this->context_handler->is_order_pay() ) {
			$order = $this->context_handler->get_order_from_query();
			if ( $order && \wcs_is_subscription( $order ) ) {
				$url = add_query_arg(
					[
						'order_id'  => $order->get_id(),
						'order_key' => $order->get_order_key(),
						'context'   => 'change_payment_method'
					],
					$url
				);
			}
		}

		return $url;
	}

	/**
	 * @param array                                     $result
	 * @param \WC_Payment_Gateway_Stripe                $gateway
	 * @param \PaymentPlugins\Vendor\Stripe\SetupIntent $setup_intent
	 *
	 * @return array
	 */
	public function process_change_payment_method_redirect( $result, $gateway, $setup_intent ) {
		$id        = wc_get_var( $_GET['order_id'], '' );
		$order_key = wc_get_var( $_GET['order_key'], '' );
		if ( ! $id ) {
			return [
				'result'   => 'error',
				'redirect' => wc_get_page_permalink( 'myaccount' )
			];
		}
		$subscription = wc_get_order( absint( $id ) );

		if ( ! $subscription || ! $subscription->key_is_valid( $order_key ) ) {
			return [
				'result'   => 'error',
				'redirect' => wc_get_page_permalink( 'myaccount' )
			];
		}
		if ( $setup_intent->status === 'requires_payment_method' ) {
			return [
				'result'   => 'error',
				'redirect' => add_query_arg(
					[ '_wpnonce' => wp_create_nonce(), 'change_payment_method' => $subscription->get_id() ],
					$subscription->get_checkout_payment_url()
				)
			];
		} elseif ( $setup_intent->status === 'succeeded' ) {
			// update the payment method on the subscription
			\WC_Subscriptions_Change_Payment_Gateway::update_payment_method( $subscription, $gateway->id );

			if ( wc_notice_count( 'error' ) == 0 ) {
				$gateway->set_setup_intent( $setup_intent );
				$gateway->set_payment_method_id( $setup_intent->payment_method->id );
				$this->payment_controller->process_change_payment_method( $subscription, $gateway );
			}
			wp_safe_redirect( $subscription->get_view_order_url() );
			exit();
		}
	}

	/**
	 * @param bool            $show
	 * @param AbstractGateway $gateway
	 *
	 * @return bool
	 */
	public function show_save_payment_method( $show, $gateway ) {
		if ( ! $show ) {
			return $show;
		}
		if ( $this->context_handler->is_checkout() ) {
			if ( \WC_Subscriptions_Cart::cart_contains_subscription() ) {
				$show = false;
			} elseif ( \wcs_cart_contains_renewal() ) {
				$show = false;
			}
		} elseif ( $this->is_change_payment_method_request() ) {
			$show = false;
		}

		return $show;
	}

	/**
	 * @param array            $payment_meta
	 * @param \WC_Subscription $subscription
	 * @param AbstractGateway  $integration
	 *
	 * @return array
	 */
	public function add_subscription_payment_meta( $payment_meta, $subscription, $integration ) {
		$payment_meta[ $integration->id ] = array(
			'post_meta' => array(
				\WC_Stripe_Constants::PAYMENT_METHOD_TOKEN => array(
					'value' => $integration->get_order_meta_data( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN, $subscription ),
					'label' => __( 'Payment Method Token', 'woo-stripe-payment' ),
				),
				\WC_Stripe_Constants::CUSTOMER_ID          => array(
					'value' => $integration->get_order_meta_data( \WC_Stripe_Constants::CUSTOMER_ID, $subscription ),
					'label' => __( 'Stripe Customer ID', 'woo-stripe-payment' ),
				),
			),
		);

		return $payment_meta;
	}

	/**
	 * @param bool            $bool
	 * @param \WC_Order       $order
	 * @param AbstractGateway $payment_method
	 *
	 * @return bool
	 */
	public function should_save_payment_method( $bool, $order, $payment_method ) {
		if ( $bool ) {
			return $bool;
		}
		if ( doing_action( 'woocommerce_scheduled_subscription_payment_' . $payment_method->id ) ) {
			return $bool;
		}
		if ( ! $payment_method->supports( 'subscriptions' ) ) {
			return $bool;
		}
		if ( wcs_order_contains_subscription( $order ) || wcs_order_contains_renewal( $order ) ) {
			$bool = true;
		}

		return $bool;
	}

	/**
	 * @param AbstractGateway[] $gateways
	 * @param ContextHandler    $context_handler
	 *
	 * @return AbstractGateway[]
	 */
	public function filter_message_gateways( $gateways, ContextHandler $context_handler ) {
		if ( $context_handler->is_product() ) {
			global $product;
			if ( ! $product instanceof \WC_Product ) {
				$product_id = $context_handler->get_product_id();
			} else {
				$product_id = $product;
			}
			if ( \WC_Subscriptions_Product::is_subscription( $product_id ) ) {
				foreach ( $gateways as $gateway ) {
					if ( ! $gateway->supports( 'subscriptions' ) ) {
						unset( $gateways[ $gateway->id ] );
					}
				}
			}
		} elseif ( $context_handler->is_cart() ) {
			if ( \WC_Subscriptions_Cart::cart_contains_subscription() ) {
				foreach ( $gateways as $gateway ) {
					if ( ! $gateway->supports( 'subscriptions' ) ) {
						unset( $gateways[ $gateway->id ] );
					}
				}
			}
		}

		return $gateways;
	}

	/**
	 * @param AbstractGateway[] $gateways
	 * @param \WC_Product       $product
	 *
	 * @return AbstractGateway[]
	 */
	public function filter_shop_message_gateways( $gateways, $product ) {
		if ( \WC_Subscriptions_Product::is_subscription( $product ) ) {
			foreach ( $gateways as $gateway ) {
				if ( ! $gateway->supports( 'subscriptions' ) ) {
					unset( $gateways[ $gateway->id ] );
				}
			}
		}

		return $gateways;
	}

	/**
	 * @param $packages
	 *
	 * @return void
	 */
	public function get_shipping_packages( $packages ) {
		if ( ! empty( $packages ) ) {
			return $packages;
		}
		if ( \WC_Subscriptions_Cart::cart_contains_free_trial() ) {
			if ( isset( WC()->cart->recurring_carts ) ) {
				$count = 0;
				\WC_Subscriptions_Cart::set_calculation_type( 'recurring_total' );
				foreach ( WC()->cart->recurring_carts as $recurring_cart_key => $recurring_cart ) {
					foreach ( $recurring_cart->get_shipping_packages() as $i => $base_package ) {
						$packages[ $recurring_cart_key . '_' . $count ] = WC()->shipping()->calculate_shipping_for_package( $base_package );
					}
					$count ++;
				}
				\WC_Subscriptions_Cart::set_calculation_type( 'none' );
			}
		}

		return $packages;
	}
}