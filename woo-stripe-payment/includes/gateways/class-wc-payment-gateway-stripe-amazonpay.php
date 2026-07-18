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
class WC_Payment_Gateway_Stripe_AmazonPay extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;
	use \PaymentPlugins\Stripe\Traits\TokenizationTrait;
	use \PaymentPlugins\Stripe\WooCommercePreOrders\Traits\PreOrdersTrait;
	use \PaymentPlugins\Stripe\WooCommerceSubscriptions\Traits\WooCommerceSubscriptionsTrait;

	public $id = 'stripe_amazonpay';

	protected $payment_method_type = 'amazon_pay';

	private $account_countries = array(
		'AT',
		'BE',
		'CY',
		'DK',
		'FR',
		'DE',
		'HU',
		'IE',
		'IT',
		'LU',
		'NL',
		'PT',
		'ES',
		'SE',
		'CH',
		'GB',
		'US'
	);

	private $accepted_currencies = array(
		'US' => array( 'USD' )
	);

	public function __construct( ...$args ) {
		$this->currencies = array(
			'AUD',
			'CHF',
			'DKK',
			'EUR',
			'GBP',
			'HKD',
			'JPY',
			'NOK',
			'NZD',
			'SEK',
			'USD',
			'ZAR'
		);
		//$this->countries          = array( 'US' );
		$this->tab_title          = __( 'Amazon Pay', 'woo-stripe-payment' );
		$this->method_title       = __( 'Amazon Pay (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Amazon Pay gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/amazon_pay.svg' );
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

		$account_country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );

		// Check if account_country exists in the account_countries array
		if ( in_array( $account_country, $this->account_countries ) ) {
			// if there is an accepted_currencies list, verify.
			if ( isset( $this->accepted_currencies[ $account_country ] ) ) {
				$result = in_array( $currency, $this->accepted_currencies[ $account_country ], true );
			} else {
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * @return string[]
	 * @since 3.3.85
	 */
	public function get_account_countries() {
		return $this->account_countries;
	}

	/**
	 * @return mixed
	 * @since 3.3.85
	 */
	public function get_accepted_currencies() {
		return $this->accepted_currencies;
	}

}
