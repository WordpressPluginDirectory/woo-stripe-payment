<?php
defined( 'ABSPATH' ) || exit();

if ( function_exists( 'WC' ) ) {
	$payment_gateways = WC()->payment_gateways()->payment_gateways();

	/**
	 * @var WC_Payment_Gateway_Stripe_ApplePay $applepay
	 */
	$applepay = $payment_gateways['stripe_applepay'] ?? null;

	if ( $applepay ) {
		$applepay->update_option( 'button_type_express_checkout', $applepay->get_option( 'button_type_checkout', 'plain' ) );
	}

	/**
	 * @var WC_Payment_Gateway_Stripe_GooglePay $googlepay
	 */
	$googlepay = $payment_gateways['stripe_googlepay'] ?? null;

	if ( $googlepay ) {
		$button_type = $googlepay->get_option( 'button_type', 'buy' );
		$googlepay->update_option( 'button_type_checkout', $button_type );
		$googlepay->update_option( 'button_type_express_checkout', $button_type );
		$googlepay->update_option( 'button_type_cart', $button_type );
		$googlepay->update_option( 'button_type_product', $button_type );
	}
}