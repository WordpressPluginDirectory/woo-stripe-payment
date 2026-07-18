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
class WC_Payment_Gateway_Stripe_Sofort extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	public $id = 'stripe_sofort';

	protected $payment_method_type = 'sofort';

	public function __construct( ...$args ) {
		$this->synchronous        = false;
		$this->currencies         = array( 'EUR' );
		$this->countries          = $this->limited_countries = array( 'AT', 'BE', 'DE', 'ES', 'IT', 'NL' );
		$this->tab_title          = __( 'Sofort', 'woo-stripe-payment' );
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'Sofort (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Sofort gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/sofort.svg' );
	}

}
