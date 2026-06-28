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
class WC_Payment_Gateway_Stripe_Billie extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	public $id = 'stripe_billie';

	protected $payment_method_type = 'billie';

	public function __construct( ...$args ) {
		parent::__construct( ...$args );
		$this->currencies         = array( 'EUR', 'SEK', 'NOK', 'DKK', 'GBP', 'CHF' );
		$this->countries          = array( 'NL' );
		$this->tab_title          = __( 'Billie', 'woo-stripe-payment' );
		$this->method_title       = __( 'Billie (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Billie gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		$this->icon               = $this->assets->assets_url( 'img/billie.svg' );
	}

	public function get_required_parameters() {
		return apply_filters( 'wc_stripe_billie_get_required_parameters', array(
			'EUR' => array( 'DE', 'FR', 'NL', 'SE', 'NO', 'FI', 'AT', 'ES', 'DK' ),
			'SEK' => array( 'DE', 'FR', 'NL', 'SE', 'NO', 'FI', 'AT', 'ES', 'DK' ),
			'NOK' => array( 'DE', 'FR', 'NL', 'SE', 'NO', 'FI', 'AT', 'ES', 'DK' ),
			'DKK' => array( 'DE', 'FR', 'NL', 'SE', 'NO', 'FI', 'AT', 'ES', 'DK' ),
			'GBP' => array( 'GB' ),
			'CHF' => array( 'CH' ),
		), $this );
	}

	/**
	 * @param string $currency
	 * @param string $billing_country
	 * @param float  $total
	 *
	 * @return bool
	 */
	protected function validate_local_payment_available( $currency, $billing_country, $total ) {
		$result = false;

		if ( $billing_country ) {
			// Rule 1: Check if billing country is in EU or GB
			if ( \in_array( $billing_country, \PaymentPlugins\Stripe\Utilities\CountryUtils::get_eu_counties(), true ) || $billing_country === 'GB' ) {
				$account_country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );
				$params          = $this->get_required_parameters();

				// Rule 2 & 3: Check if currency is valid and account country matches currency requirements
				if ( isset( $params[ $currency ] ) && in_array( $account_country, $params[ $currency ], true ) ) {
					$result = true;
				}
			}
		}

		return $result;
	}
}
