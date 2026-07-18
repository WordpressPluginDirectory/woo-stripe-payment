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
class WC_Payment_Gateway_Stripe_Swish extends WC_Payment_Gateway_Stripe_Local_Payment {

	public $id = 'stripe_swish';

	protected $payment_method_type = 'swish';

	use WC_Stripe_Local_Payment_Intent_Trait;

	public function __construct( ...$args ) {
		$this->currencies         = array( 'SEK' );
		$this->countries          = array( 'SE' );
		$this->tab_title          = __( 'Swish', 'woo-stripe-payment' );
		$this->method_title       = __( 'Swish (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Swish gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/swish.svg' );
	}

}
