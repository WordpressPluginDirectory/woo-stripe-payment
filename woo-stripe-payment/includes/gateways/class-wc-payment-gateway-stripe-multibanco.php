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
class WC_Payment_Gateway_Stripe_Multibanco extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	use WC_Stripe_Voucher_Payment_Trait;

	public $id = 'stripe_multibanco';

	protected $payment_method_type = 'multibanco';

	public $synchronous = false;

	public $is_voucher_payment = true;

	public function __construct( ...$args ) {
		$this->currencies         = array( 'EUR' );
		$this->countries          = array( 'PT' );
		$this->tab_title          = __( 'Multibanco', 'woo-stripe-payment' );
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'Multibanco (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Multibanco gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/multibanco.svg' );
	}

}
