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
class WC_Payment_Gateway_Stripe_EPS extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	public $id = 'stripe_eps';

	protected $payment_method_type = 'eps';

	public function __construct( ...$args ) {
		$this->currencies         = array( 'EUR' );
		$this->countries          = array( 'AT' );
		$this->tab_title          = __( 'EPS', 'woo-stripe-payment' );
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'EPS (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'EPS gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/eps.svg' );
	}

}
