<?php

defined( 'ABSPATH' ) || exit();

/**
 *
 * @since   3.1.0
 * @author  Payment Plugins
 * @package PaymentPlugins\Classes
 *
 */
class WC_Stripe_Gateway_Conversion {

	public static function init() {
		add_filter( 'woocommerce_order_get_payment_method', array( __CLASS__, 'convert_payment_method' ), 10, 2 );
		add_filter( 'woocommerce_subscription_get_payment_method', array(
			__CLASS__,
			'convert_payment_method'
		), 10, 2 );
	}

	/**
	 *
	 * @param string   $payment_method
	 * @param WC_Order $order
	 */
	public static function convert_payment_method( $current_payment_method, $order ) {
		$payment_method = $current_payment_method;
		switch ( $current_payment_method ) {
			case 'stripe':
			case 'fkwcs_stripe':
				$payment_method = 'stripe_cc';
				break;
			case 'fkwcs_stripe_sepa':
				$payment_method = 'stripe_sepa';
				break;
			// @since 4.0.0 - The Payment Request Gateway has been deprecated.
			case 'stripe_payment_request':
				$payment_method = 'stripe_googlepay';
				break;
			// @since 4.0.10 - eh-stripe-payment-gateway conversions.
			case 'eh_stripe_pay':
			case 'eh_stripe_checkout':
				$payment_method = 'stripe_cc';
				break;
			case 'eh_affirm_stripe':
				$payment_method = 'stripe_affirm';
				break;
			case 'eh_afterpay_stripe':
				$payment_method = 'stripe_afterpay';
				break;
			case 'eh_alipay_stripe':
				$payment_method = 'stripe_alipay';
				break;
			case 'eh_amazon_pay_stripe':
				$payment_method = 'stripe_amazonpay';
				break;
			case 'eh_bancontact_stripe':
				$payment_method = 'stripe_bancontact';
				break;
			case 'eh_becs_stripe':
				$payment_method = 'stripe_becs';
				break;
			case 'eh_boleto_stripe':
				$payment_method = 'stripe_boleto';
				break;
			case 'eh_eps_stripe':
				$payment_method = 'stripe_eps';
				break;
			case 'eh_fpx_stripe':
				$payment_method = 'stripe_fpx';
				break;
			case 'eh_grabpay_stripe':
				$payment_method = 'stripe_grabpay';
				break;
			case 'eh_ideal_stripe':
				$payment_method = 'stripe_ideal';
				break;
			case 'eh_klarna_stripe':
				$payment_method = 'stripe_klarna';
				break;
			case 'eh_multibanco_stripe':
				$payment_method = 'stripe_multibanco';
				break;
			case 'eh_oxxo_stripe':
				$payment_method = 'stripe_oxxo';
				break;
			case 'eh_revolut_pay_stripe':
				$payment_method = 'stripe_revolut';
				break;
			case 'eh_sepa_stripe':
				$payment_method = 'stripe_sepa';
				break;
			case 'eh_wechat_stripe':
				$payment_method = 'stripe_wechat';
				break;
			case 'eh_pay_by_bank_stripe':
				$payment_method = 'stripe_paybybank';
				break;
		}
		// Another Stripe plugin is active, don't convert $payment_method as that could affect
		// checkout functionality.
		if ( did_action( 'woocommerce_checkout_order_processed' ) ) {
			return $current_payment_method;
		}

		return $payment_method;
	}

}

WC_Stripe_Gateway_Conversion::init();
