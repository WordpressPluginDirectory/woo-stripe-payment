<?php

use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;

defined( 'ABSPATH' ) || exit();

/**
 *
 * @since   3.0.0
 * @package PaymentPlugins\Classes
 * @author  Payment Plugins
 * @deprecated 4.0.0 - Use PaymentPlugins\Stripe\Checkout\ExpressCheckoutRenderer
 */
class WC_Stripe_Field_Manager {

	private static $_cart_priority = 30;

	private static $_product_button_position;

	public static function init() {

	}

	public static function init_action() {
		self::$_cart_priority = apply_filters( 'wc_stripe_cart_buttons_order', 30 );
		add_action( 'woocommerce_proceed_to_checkout', array(
			__CLASS__,
			'output_cart_fields'
		), self::$_cart_priority );
	}

	public static function output_banner_checkout_fields() {
		wc_stripe_get_container()->get(
			\PaymentPlugins\Stripe\Checkout\ExpressCheckoutRenderer::class
		)->render_express_checkout();
	}

	public static function output_checkout_fields() {
		do_action( 'wc_stripe_output_checkout_fields' );
	}

	public static function before_add_to_cart() {
		global $product;
		self::$_product_button_position = is_object( $product ) ? $product->get_meta( WC_Stripe_Constants::BUTTON_POSITION ) : null;
		if ( empty( self::$_product_button_position ) ) {
			self::$_product_button_position = 'bottom';
		}

		if ( 'bottom' == self::$_product_button_position ) {
			$action = 'woocommerce_after_add_to_cart_button';
		} else {
			$action = 'woocommerce_before_add_to_cart_button';
		}
		add_action( $action, array( __CLASS__, 'output_product_checkout_fields' ) );
	}

	public static function output_product_checkout_fields() {
		wc_stripe_get_container()->get(
			\PaymentPlugins\Stripe\Checkout\ExpressCheckoutRenderer::class
		)->render_product_buttons();
	}

	public static function output_cart_fields() {
		wc_stripe_get_container()->get(
			\PaymentPlugins\Stripe\Checkout\ExpressCheckoutRenderer::class
		)->render_cart_buttons();
	}

	public static function mini_cart_buttons() {
		wc_stripe_get_container()->get(
			\PaymentPlugins\Stripe\Checkout\ExpressCheckoutRenderer::class
		)->render_mini_cart_buttons();
	}

	/**
	 * @deprecated 3.1.8
	 */
	public static function change_payment_request() {
	}

	public static function add_payment_method_fields() {
		wc_stripe_hidden_field( 'billing_first_name', '', WC()->customer->get_first_name() );
		wc_stripe_hidden_field( 'billing_last_name', '', WC()->customer->get_last_name() );
	}

	/**
	 * @deprecated 3.1.8
	 */
	public static function pay_order_fields() {
		global $wp;
		$order = wc_get_order( absint( $wp->query_vars['order-pay'] ) );
		self::output_required_fields( 'checkout', $order );
	}

	/**
	 * @param string   $page
	 * @param WC_Order $order
	 */
	public static function output_required_fields( $page, $order = null ) {
		if ( in_array( $page, array( 'cart', 'checkout' ) ) ) {
			if ( 'cart' === $page ) {
				self::output_fields( 'billing' );

				if ( WC()->cart->needs_shipping() ) {
					self::output_fields( 'shipping' );
				}
			}
		} elseif ( 'product' === $page ) {
			global $product;

			self::output_fields( 'billing' );

			if ( $product->needs_shipping() ) {
				self::output_fields( 'shipping' );
			}
		}
	}

	public static function output_fields( $prefix ) {
		$fields = WC()->checkout()->get_checkout_fields( $prefix );
		foreach ( $fields as $key => $field ) {
			printf( '<input type="hidden" id="%1$s" name="%1$s" value="%2$s"/>', $key, WC()->checkout()->get_value( $key ) );
		}
	}

	/**
	 * @param bool $needs_shipping
	 *
	 * @deprecated
	 */
	public static function output_needs_shipping( $needs_shipping ) {
	}

}
