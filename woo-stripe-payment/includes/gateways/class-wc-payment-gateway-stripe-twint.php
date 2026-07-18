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
class WC_Payment_Gateway_Stripe_Twint extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	public $id = 'stripe_twint';

	protected $payment_method_type = 'twint';

	public function __construct( ...$args ) {
		$this->currencies         = array( 'CHF' );
		$this->countries          = array( 'CH' );
		$this->limited_countries  = array( 'CH' );
		$this->tab_title          = __( 'Twint', 'woo-stripe-payment' );
		$this->method_title       = __( 'Twint (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Twint gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/twint.svg' );
	}

}
