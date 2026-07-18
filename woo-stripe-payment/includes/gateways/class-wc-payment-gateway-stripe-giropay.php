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
class WC_Payment_Gateway_Stripe_Giropay extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	public $id = 'stripe_giropay';

	protected $payment_method_type = 'giropay';

	public function __construct( ...$args ) {
		$this->currencies         = array( 'EUR' );
		$this->countries          = array( 'DE' );
		$this->tab_title          = __( 'Giropay', 'woo-stripe-payment' );
		$this->method_title       = __( 'Giropay (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Giropay gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/giropay.svg' );
	}

}
