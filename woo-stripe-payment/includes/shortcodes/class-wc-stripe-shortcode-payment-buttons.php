<?php
defined( 'ABSPATH' ) || exit();

/**
 * Class WC_Stripe_Shortcode_Payment_Buttons
 *
 * @since   3.2.15
 * @package PaymentPlugins\Shortcodes
 */
class WC_Stripe_Shortcode_Payment_Buttons {

	public static function output_product_buttons( $atts ) {
		wc_stripe_get_container()->get(
			\PaymentPlugins\Stripe\Checkout\ExpressCheckoutRenderer::class
		)->render_product_buttons();
	}

	public static function output_cart_buttons( $atts ) {
		wc_stripe_get_container()->get(
			\PaymentPlugins\Stripe\Checkout\ExpressCheckoutRenderer::class
		)->render_cart_buttons();
	}

}