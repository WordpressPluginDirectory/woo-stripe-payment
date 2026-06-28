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
class WC_Payment_Gateway_Stripe_MBWay extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	public $id = 'stripe_mbway';

	protected $payment_method_type = 'mb_way';

	private $account_countries = array(
		'AT', // Austria
		'AU', // Australia
		'BE', // Belgium
		'BG', // Bulgaria
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
		'GI', // Gibraltar
		'GR', // Greece
		'HK', // Hong Kong
		'HR', // Croatia
		'HU', // Hungary
		'IE', // Ireland
		'IT', // Italy
		'JP', // Japan
		'LI', // Liechtenstein
		'LT', // Lithuania
		'LU', // Luxembourg
		'LV', // Latvia
		'MT', // Malta
		'MX', // Mexico
		'NL', // Netherlands
		'NO', // Norway
		'NZ', // New Zealand
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
		$this->countries          = $this->limited_countries = array( 'PT' );
		$this->tab_title          = __( 'MB Way', 'woo-stripe-payment' );
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'MB Way (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'MB Way gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/' . $this->get_option( 'icon' ) . '.svg' );
	}

	protected function validate_local_payment_available( $currency, $billing_country, $total ) {
		$account_country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );

		return in_array( $account_country, $this->account_countries );
	}

	public function get_local_payment_settings() {
		return array_merge(
			parent::get_local_payment_settings(),
			[
				'icon' => array(
					'title'       => __( 'Icon', 'woo-stripe-payment' ),
					'type'        => 'select',
					'options'     => array(
						'mbway_red'   => __( 'MB Way red outline', 'woo-stripe-payment' ),
						'mbway_black' => __( 'MB Way all black', 'woo-stripe-payment' ),
					),
					'default'     => 'mbway_red',
					'desc_tip'    => true,
					'description' => __( 'This is the icon style that appears next to the gateway on the checkout page. If you have messaging enabled on the checkout page, that will override the icon.', 'woo-stripe-payment' ),
				),
			]
		);
	}

}
