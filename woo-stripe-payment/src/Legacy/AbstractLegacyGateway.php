<?php

namespace PaymentPlugins\Stripe\Legacy;

use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;
use PaymentPlugins\Stripe\WooCommercePreOrders\PreOrdersController;
use PaymentPlugins\Stripe\WooCommerceSubscriptions\SubscriptionsController;

/**
 * Legacy and deprecated methods are kept here to keep the new AbstractGateway clean. This class ensures compatability
 * with 3rd party code that uses these methods.
 */
abstract class AbstractLegacyGateway extends AbstractGateway {

	/**
	 * Used to retrieve deprecated properties.
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( $key === 'payment_object' ) {
			return $this->payment_controller;
		} elseif ( $key === 'gateway' ) {
			return $this->client;
		} elseif ( $key === 'token_key' ) {
			return $this->id . '_token_key';
		} elseif ( $key === 'saved_method_key' ) {
			return $this->id . '_saved_method_key';
		} elseif ( $key === 'save_source_key' ) {
			return "wc-{$this->id}-new-payment-method";
		} elseif ( $key === 'payment_intent_key' ) {
			return $this->id . '_payment_intent_key';
		} elseif ( $key === 'new_source_token' ) {
			return $this->payment_method_id;
		}

		return null;
	}

	/**
	 * Return true of the customer is using a saved payment method.
	 * @deprecated 4.0.0
	 */
	public function use_saved_source() {
		wc_deprecated_function(
			'AbstractLegacyGateway::use_saved_source',
			'4.0.0',
			'PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway::should_use_saved_payment_method'
		);

		return ( ! empty( $_POST[ $this->payment_type_key ] ) && wc_clean( $_POST[ $this->payment_type_key ] ) === 'saved' ) || $this->payment_method_token
		       || ( ! empty( $_POST["wc-{$this->id}-payment-token"] ) );
	}

	/**
	 * @param float  $price
	 * @param string $label
	 * @param string $type
	 * @param mixed  ...$args
	 *
	 * @return array
	 * @since 3.2.1
	 * @deprecated 4.0.0
	 */
	protected function get_display_item_for_cart( $price, $label, $type, ...$args ) {
		wc_deprecated_function(
			'AbstractLegacyGateway::get_display_item_for_cart',
			'4.0.0',
			''
		);

		return [
			'name'   => $label,
			'amount' => wc_stripe_add_number_precision( $price )
		];
	}

	/**
	 * @param float    $price
	 * @param string   $label
	 * @param WC_Order $order
	 * @param string   $type
	 * @param mixed    ...$args
	 *
	 * @deprecated 4.0.0
	 */
	protected function get_display_item_for_order( $price, $label, $order, $type, ...$args ) {
		wc_deprecated_function(
			'AbstractLegacyGateway::get_display_item_for_order',
			'4.0.0',
			''
		);

		return array(
			'name'   => $label,
			'amount' => wc_stripe_add_number_precision( $price, $order->get_currency() )
		);
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return array
	 * @since 3.2.1
	 * @deprecated 4.0.0
	 *
	 */
	protected function get_display_item_for_product( $product ) {
		wc_deprecated_function(
			'AbstractLegacyGateway::get_display_item_for_product',
			'4.0.0',
			''
		);

		return array(
			'name'   => esc_attr( $product->get_name() ),
			'amount' => wc_stripe_add_number_precision( $product->get_price() )
		);
	}

	/**
	 * @param $price
	 * @param $rate
	 * @param $i
	 * @param $package
	 * @param $incl_tax
	 *
	 * @return array|void
	 * @deprecated 4.0.0
	 */
	public function get_formatted_shipping_method( $price, $rate, $i, $package, $incl_tax ) {
		wc_deprecated_function(
			'AbstractLegacyGateway::get_formatted_shipping_method',
			'4.0.0',
			''
		);

		return array(
			'id'          => $this->get_shipping_method_id( $rate->id, $i ),
			'amount'      => wc_stripe_add_number_precision( $price ),
			'displayName' => $this->get_formatted_shipping_label( $price, $rate, $incl_tax )
		);
	}

	/**
	 * Enqueue scripts needed by the gateway on the frontend of the WC shop.
	 *
	 * @param string $page
	 *
	 * @deprecated 4.0.0
	 */
	public function enqueue_frontend_scripts( $page = '' ) {
		wc_deprecated_function(
			'AbstractLegacyGateway::enqueue_frontend_scripts',
			'4.0.0',
			''
		);
	}

	/**
	 * @return void
	 * @since 3.3.37
	 * @deprecated 4.0.0
	 */
	public function enqueue_payment_method_styles() {
		wc_deprecated_function(
			'AbstractLegacyGateway::enqueue_payment_method_styles',
			'4.0.0',
			''
		);
	}

	/**
	 * Enqueue scripts needed by the gateway on the checkout page.
	 *
	 * @param \WC_Stripe_Frontend_Scripts $scripts
	 *
	 * @deprecated 4.0.0
	 */
	public function enqueue_checkout_scripts( $scripts ) {
		wc_deprecated_function(
			'AbstractLegacyGateway::enqueue_checkout_scripts',
			'4.0.0',
			''
		);
	}

	/**
	 * Enqueue scripts needed by the gateway on the add payment method page.
	 *
	 * @param \WC_Stripe_Frontend_Scripts $scripts
	 *
	 * @deprecated 4.0.0
	 */
	public function enqueue_add_payment_method_scripts( $scripts ) {
		wc_deprecated_function(
			'AbstractLegacyGateway::enqueue_add_payment_method_scripts',
			'4.0.0',
			''
		);
	}

	/**
	 * Enqueue scripts needed by the gateway on the cart page.
	 *
	 * @param \WC_Stripe_Frontend_Scripts $scripts
	 *
	 * @deprecated 4.0.0
	 */
	public function enqueue_cart_scripts( $scripts ) {
		wc_deprecated_function(
			'AbstractLegacyGateway::enqueue_cart_scripts',
			'4.0.0',
			''
		);
	}

	/**
	 * Enqueue scripts needed by the gateway on the product page.
	 *
	 * @param \WC_Stripe_Frontend_Scripts $scripts
	 *
	 * @deprecated 4.0.0
	 */
	public function enqueue_product_scripts( $scripts ) {
		wc_deprecated_function(
			'AbstractLegacyGateway::enqueue_product_scripts',
			'4.0.0',
			''
		);
	}

	/**
	 * @param \WC_Stripe_Frontend_Scripts $scripts
	 *
	 * @since 3.1.8
	 * @deprecated 4.0.0
	 */
	public function enqueue_mini_cart_scripts( $scripts ) {
	}

	/**
	 * @param array $deps
	 * @param       $scripts
	 *
	 * @since 3.1.8
	 * @deprecated 4.0.0
	 */
	public function get_mini_cart_dependencies( $deps, $scripts ) {
		return $deps;
	}

	/**
	 *
	 * @deprecated 4.0.0
	 *
	 */
	public function get_new_source_id() {
		return $this->get_new_source_token();
	}

	/**
	 * Return the payment source the customer has chosen to use.
	 * This can be a saved source
	 * or a one time use source.
	 *
	 * @deprecated 4.0.0
	 */
	public function get_payment_source() {
		if ( $this->use_saved_source() ) {
			return $this->get_saved_source_id();
		} else {
			if ( $this->payment_method_token ) {
				return $this->payment_method_token;
			}

			return $this->get_new_source_token();
		}
	}

	/**
	 * @return array|string|\WC_Stripe_Gateway|\WC_Stripe_Payment_Intent|null
	 * @deprecated 4.0.0
	 */
	public function get_new_source_token() {
		return null != $this->new_source_token ? $this->new_source_token : ( ! empty( $_POST[ $this->token_key ] ) ? wc_clean( $_POST[ $this->token_key ] ) : '' );
	}

	public function set_new_source_token( $token ) {
		$this->payment_method_id = $token;
	}

	/**
	 * @return array|string|\WC_Stripe_Gateway|\WC_Stripe_Payment_Intent|null
	 * @deprecated 4.0.0
	 */
	public function get_saved_source_id() {
		// Check if Blocks are being used
		if ( ! empty( $_POST["wc-{$this->id}-payment-token"] ) ) {
			$token = \WC_Payment_Tokens::get( wc_clean( $_POST["wc-{$this->id}-payment-token"] ) );

			return $token->get_token();
		}
		if ( ! empty( $_POST[ $this->saved_method_key ] ) && ! empty( $_POST[ $this->payment_type_key ] ) && 'saved' == $_POST[ $this->payment_type_key ] ) {
			return wc_clean( $_POST[ $this->saved_method_key ] );
		}

		return $this->payment_method_token;
	}

	/**
	 * Return true if product page checkout is enabled for this gateway
	 *
	 * @return bool
	 */
	public function product_checkout_enabled() {
		return in_array( 'product', (array) $this->get_option( 'payment_sections', array() ) );
	}

	/**
	 * Return true if cart page checkout is enabled for this gateway
	 *
	 * @return bool
	 */
	public function cart_checkout_enabled() {
		return in_array( 'cart', (array) $this->get_option( 'payment_sections', array() ) );
	}

	/**
	 * Return true if mini-cart checkout is enabled for this gateway
	 *
	 * @return bool
	 * @since 3.1.8
	 */
	public function mini_cart_enabled() {
		return in_array( 'mini_cart', (array) $this->get_option( 'payment_sections', array() ) );
	}

	/**
	 * Return true if checkout page banner is enabled for this gateway
	 *
	 * @return bool
	 */
	public function banner_checkout_enabled() {
		global $wp;

		return empty( $wp->query_vars['order-pay'] ) && $this->supports( 'wc_stripe_banner_checkout' ) && in_array( 'express_checkout', $this->get_option( 'payment_sections', array() ) );
	}

	/**
	 * @deprecated 3.3.18
	 */
	public function get_method_formats() {
		return $this->get_payment_method_formats();
	}

	/**
	 * @param WC_Subscription $subscription
	 *
	 * @return array
	 * @since 3.2.13
	 * @deprecated
	 */
	protected function process_change_payment_method_request( $subscription ) {
		return $this->process_subscription_payment_method_updated( $subscription );
	}

	/**
	 * @param \WC_Subscription $subscription
	 *
	 * @return array|string[]
	 * @deprecated 4.0.0
	 */
	public function process_subscription_payment_method_updated( $subscription ) {
		if ( ! $this->use_saved_source() ) {
			$result = $this->save_payment_method( $this->get_new_source_token(), $subscription );
			if ( is_wp_error( $result ) ) {
				wc_add_notice( sprintf( __( 'Error saving payment method for subscription. Reason: %s', 'woo-stripe-payment' ), $result->get_error_message() ), 'error' );

				return array( 'result' => 'error' );
			}
		} else {
			$this->payment_method_token = $this->get_saved_source_id();
		}
		$token = $this->get_token( $this->payment_method_token, $subscription->get_user_id() );

		// update the meta data needed by the gateway to process a subscription payment.
		if ( $token ) {
			$subscription->set_payment_method( $token->get_gateway_id() );
			$subscription->update_meta_data( WC_Stripe_Constants::CUSTOMER_ID, $token->get_customer_id() );
			$subscription->set_payment_method_title( $token->get_payment_method_title() );
		}

		$subscription->update_meta_data( WC_Stripe_Constants::PAYMENT_METHOD_TOKEN, $this->payment_method_token );
		$subscription->save();

		return array( 'result' => 'success', 'redirect' => wc_get_page_permalink( 'myaccount' ) );
	}

	/**
	 * Pre orders can't be mixed with regular products.
	 *
	 * @param WC_Order $order
	 *
	 * @deprecated 4.0.0
	 */
	protected function order_contains_pre_order( $order ) {
		return wc_stripe_pre_orders_active() && WC_Pre_Orders_Order::order_contains_pre_order( $order );
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @return boolean
	 * @deprecated 4.0.0
	 */
	protected function pre_order_requires_tokenization( $order ) {
		return \WC_Pre_Orders_Order::order_requires_payment_tokenization( $order );
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 * @deprecated 4.0.0
	 */
	public function process_pre_order( $order ) {
		$token = null;
		// maybe save payment method
		if ( ! $this->use_saved_source() ) {
			// if user not logged in, create a Stripe customer that won't be assigned to a user.
			if ( ! $order->get_customer_id() ) {
				$customer = \WC_Stripe_Customer_Manager::instance()->create_customer( WC()->customer );
				if ( is_wp_error( $customer ) ) {
					return wc_add_notice( $customer->get_error_message(), 'error' );
				}
				$order->update_meta_data( \WC_Stripe_Constants::CUSTOMER_ID, $customer->id );
				$result = $token = $this->create_payment_method( $this->get_new_source_token(), $customer->id );
			} else {
				$result = $this->save_payment_method( $this->get_new_source_token(), $order );
			}
			if ( is_wp_error( $result ) ) {
				wc_add_notice( $result->get_error_message(), 'error' );

				return $this->get_order_error();
			}
		} else {
			$this->payment_method_token = $this->get_saved_source_id();
		}
		\WC_Pre_Orders_Order::mark_order_as_pre_ordered( $order );
		$this->save_zero_total_meta( $order, $token );

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @deprecated 4.0.0
	 */
	public function process_pre_order_payment( $order ) {
		wc_stripe_get_container()->get( PreOrdersController::class )->process_pre_order_payment( $order, $this );
	}

	/**
	 *
	 * @param array           $payment_meta
	 * @param WC_Subscription $subscription
	 *
	 * @deprecated
	 */
	public function subscription_payment_meta( $payment_meta, $subscription ) {
		return $payment_meta;
	}

	/**
	 *
	 * @param float     $amount
	 * @param \WC_Order $order
	 *
	 * @deprecated 4.0.0
	 */
	public function scheduled_subscription_payment( $amount, $order ) {
		wc_stripe_get_container()
			->get( SubscriptionsController::class )
			->scheduled_subscription_payment( $amount, $order, $this );
	}

	/**
	 * @param \WC_Subscription $subscription
	 * @param \WC_Order        $order
	 *
	 * @deprecated 4.0.0
	 */
	public function update_failing_payment_method( $subscription, $order ) {
		wc_stripe_get_container()
			->get( SubscriptionsController::class )
			->update_failing_payment_method( $subscription, $order );
	}
}