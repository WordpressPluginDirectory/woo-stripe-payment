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
class WC_Payment_Gateway_Stripe_Zip extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	public $id = 'stripe_zip';

	protected $payment_method_type = 'zip';

	public function __construct( ...$args ) {
		$this->currencies         = array( 'AUD' );
		$this->countries          = array( 'AU' );
		$this->tab_title          = __( 'Zip', 'woo-stripe-payment' );
		$this->method_title       = __( 'Zip (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Zip gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/zip.svg' );
	}

	protected function validate_local_payment_available( $currency, $billing_country, $total ) {
		$account_country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );

		return $account_country === 'AU';
	}

}
