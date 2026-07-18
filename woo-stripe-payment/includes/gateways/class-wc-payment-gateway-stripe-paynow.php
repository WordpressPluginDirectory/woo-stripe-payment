<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe_Local_Payment' ) ) {
	return;
}

/**
 *
 * @package PaymentPlugins\Gateways
 * @author  PaymentPlugins
 *
 */
class WC_Payment_Gateway_Stripe_PayNow extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	public $id = 'stripe_paynow';

	protected $payment_method_type = 'paynow';

	public function __construct( ...$args ) {
		$this->currencies         = array( 'SGD' );
		$this->countries          = array( 'SG' );
		$this->tab_title          = __( 'PayNow', 'woo-stripe-payment' );
		$this->method_title       = __( 'PayNow (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'PayNow gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/paynow.svg' );
	}

}
