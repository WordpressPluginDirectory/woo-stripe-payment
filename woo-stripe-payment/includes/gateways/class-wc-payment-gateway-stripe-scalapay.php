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
class WC_Payment_Gateway_Stripe_Scalapay extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	public $id = 'stripe_scalapay';

	protected $payment_method_type = 'scalapay';

	private $account_countries = array(
		'AT', // Austria
		'AU', // Australia
		'BE', // Belgium
		'CA', // Canada
		'CH', // Switzerland
		'CY', // Cyprus
		'CZ', // Czech Republic
		'DE', // Germany
		'DK', // Denmark
		'EE', // Estonia
		'ES', // Spain
		'FI', // Finland
		'FR', // France
		'GB', // United Kingdom
		'GR', // Greece
		'HR', // Croatia
		'IE', // Ireland
		'IT', // Italy
		'LI', // Liechtenstein
		'LT', // Lithuania
		'LU', // Luxembourg
		'LV', // Latvia
		'MT', // Malta
		'NL', // Netherlands
		'NO', // Norway
		'PL', // Poland
		'PT', // Portugal
		'RO', // Romania
		'SE', // Sweden
		'SG', // Singapore
		'SI', // Slovenia
		'SK', // Slovakia
		'US', // United States
	);

	public function __construct( ...$args ) {
		$this->currencies         = array( 'EUR' );
		$this->countries          = array( 'IT' );
		$this->tab_title          = __( 'Scalapay', 'woo-stripe-payment' );
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'Scalapay (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Scalapay gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/scalapay.svg' );
	}

	protected function validate_local_payment_available( $currency, $billing_country, $total ) {
		$account_country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );

		return in_array( $account_country, $this->account_countries );
	}

}
