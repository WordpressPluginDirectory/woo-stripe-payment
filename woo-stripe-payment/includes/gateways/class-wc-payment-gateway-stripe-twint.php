<?php
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe_Local_Payment' ) ) {
	return;
}

/**
 *
 * @package Stripe/Gateways
 * @author  PaymentPlugins
 *
 */
class WC_Payment_Gateway_Stripe_Twint extends WC_Payment_Gateway_Stripe_Local_Payment {

	protected $payment_method_type = 'twint';

	use WC_Stripe_Local_Payment_Intent_Trait;

	public function __construct() {
		$this->local_payment_type = 'twint';
		$this->currencies         = array( 'CHF' );
		$this->countries          = array( 'CH' );
		$this->limited_countries  = array( 'CH' );
		$this->id                 = 'stripe_twint';
		$this->tab_title          = __( 'Twint', 'woo-stripe-payment' );
		$this->method_title       = __( 'Twint (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Twint gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		$this->icon               = stripe_wc()->assets_url( 'img/twint.svg' );
		parent::__construct();
	}

}
