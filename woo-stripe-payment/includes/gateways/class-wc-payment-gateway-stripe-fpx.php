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
class WC_Payment_Gateway_Stripe_FPX extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	public $id = 'stripe_fpx';

	protected $payment_method_type = 'fpx';

	public function __construct( ...$args ) {
		$this->currencies         = array( 'MYR' );
		$this->countries          = array( 'MY' );
		$this->tab_title          = __( 'FPX', 'woo-stripe-payment' );
		$this->method_title       = __( 'FPX (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'FPX gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/fpx.svg' );
	}

	public function get_element_params() {
		$params                      = parent::get_element_params();
		$params['accountHolderType'] = 'individual';

		return $params;
	}
}
