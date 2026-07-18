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
class WC_Payment_Gateway_Stripe_P24 extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	public $id = 'stripe_p24';

	protected $payment_method_type = 'p24';

	public function __construct( ...$args ) {
		$this->currencies         = array( 'EUR', 'PLN' );
		$this->countries          = array( 'PL' );
		$this->tab_title          = __( 'Przelewy24', 'woo-stripe-payment' );
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'Przelewy24 (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'P24 gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/p24.svg' );
	}
}
