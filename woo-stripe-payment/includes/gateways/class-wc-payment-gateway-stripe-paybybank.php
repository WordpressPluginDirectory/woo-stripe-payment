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
class WC_Payment_Gateway_Stripe_PayByBank extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	public $id = 'stripe_paybybank';

	protected $payment_method_type = 'pay_by_bank';

	public $max_amount = 5000;

	private $account_countries = array(
		'AU', // Australia
		'AT', // Austria
		'BE', // Belgium
		'BG', // Bulgaria
		'CA', // Canada
		'HR', // Croatia
		'CY', // Cyprus
		'CZ', // Czech Republic
		'DK', // Denmark
		'EE', // Estonia
		'FI', // Finland
		'FR', // France
		'DE', // Germany
		'GR', // Greece
		'HU', // Hungary
		'IE', // Ireland
		'IT', // Italy
		'LV', // Latvia
		'LI', // Liechtenstein
		'LT', // Lithuania
		'LU', // Luxembourg
		'MT', // Malta
		'NL', // Netherlands
		'NO', // Norway
		'PL', // Poland
		'PT', // Portugal
		'RO', // Romania
		'SG', // Singapore
		'SK', // Slovakia
		'SI', // Slovenia
		'ES', // Spain
		'SE', // Sweden
		'CH', // Switzerland
		'GB', // United Kingdom
		'US', // United States
	);

	public function __construct( ...$args ) {
		$this->currencies         = array( 'EUR', 'GBP' );
		$this->countries          = array( 'DE', 'FI', 'FR', 'GB', 'IE' );
		$this->limited_countries  = array( 'DE', 'FI', 'FR', 'GB', 'IE' );
		$this->tab_title          = __( 'Pay By Bank', 'woo-stripe-payment' );
		$this->method_title       = __( 'Pay By Bank (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Pay By Bank gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
	}

	protected function validate_local_payment_available( $currency, $billing_country, $total ) {
		$account_country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );
		if ( ! in_array( $account_country, $this->account_countries ) ) {
			return false;
		}

		return true;
	}

}
