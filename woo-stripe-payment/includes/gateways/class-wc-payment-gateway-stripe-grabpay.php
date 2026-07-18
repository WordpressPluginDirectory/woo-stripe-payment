<?php
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe_Local_Payment' ) ) {
	return;
}

/**
 *
 * @package PaymentPlugins\Gateways
 * @author PaymentPlugins
 *
 */
class WC_Payment_Gateway_Stripe_GrabPay extends WC_Payment_Gateway_Stripe_Local_Payment {

	public $id = 'stripe_grabpay';

	protected $payment_method_type = 'grabpay';

	use WC_Stripe_Local_Payment_Intent_Trait;

	public function __construct( ...$args ) {
		$this->currencies         = array( 'SGD', 'MYR' );
		$this->countries          = array( 'MY', 'SG' );
		$this->tab_title          = __( 'GrabPay', 'woo-stripe-payment' );
		$this->method_title       = __( 'GrabPay (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'GrabPay gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/grabpay.svg' );
	}
}
