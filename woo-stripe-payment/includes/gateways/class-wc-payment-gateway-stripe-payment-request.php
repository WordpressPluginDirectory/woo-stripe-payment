<?php
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe' ) ) {
	return;
}

/**
 * This gateway is provided so merchants can accept Chrome Payments, Microsoft Pay, etc.
 *
 * @author  PaymentPlugins
 * @package PaymentPlugins\Gateways
 * @deprecated 4.0.0 - Now that Stripe's Google Pay integration is fully features across all browsers, the Payment Request Gateway
 *  would be redundant.
 *
 */
class WC_Payment_Gateway_Stripe_Payment_Request extends WC_Payment_Gateway_Stripe {

	use WC_Stripe_Payment_Intent_Trait;

	use WC_Stripe_Express_Payment_Trait;

	public $id = 'stripe_payment_request';

	protected $payment_method_type = 'card';

	private $supported_locales = [
		'ar',
		'bg',
		'cs',
		'da',
		'de',
		'el',
		'en',
		'en-GB',
		'es',
		'es-419',
		'et',
		'fi',
		'fil',
		'fr',
		'fr-CA',
		'he',
		'hr',
		'hu',
		'id',
		'it',
		'ja',
		'ko',
		'lt',
		'lv',
		'ms',
		'mt',
		'nb',
		'nl',
		'pl',
		'pt-BR',
		'pt',
		'ro',
		'ru',
		'sk',
		'sl',
		'sv',
		'th',
		'tr',
		'vi',
		'zh',
		'zh-HK',
		'zh-TW'
	];

	public function __construct( ...$args ) {
		$this->tab_title          = __( 'PaymentRequest Gateway', 'woo-stripe-payment' );
		$this->template_name      = 'payment-request.php';
		$this->token_type         = 'Stripe_CC';
		$this->method_title       = __( 'Payment Request (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Gateway that renders based on the user\'s browser. Chrome payment methods, Microsoft pay, etc.', 'woo-stripe-payment' );
		$this->has_digital_wallet = true;
		$this->icon               = stripe_wc()->assets_url( 'img/googlepay_round_outline.svg' );
		parent::__construct( ...$args );
	}

	public function hooks() {

	}

	public function get_button_height() {
		$value = $this->get_option( 'button_height' );
		$value .= strpos( $value, 'px' ) === false ? 'px' : '';

		return $value;
	}

	protected function get_element_options_locale() {
		$locale = wc_stripe_get_site_locale();

		if ( $locale === 'auto' ) {
			return $locale;
		}

		if ( in_array( $locale, $this->supported_locales ) ) {
			return $locale;
		}

		$formatted_locale = substr( $locale, 0, 2 );

		if ( in_array( $formatted_locale, $this->supported_locales ) ) {
			$locale = $formatted_locale;
		}

		return $locale;
	}

}
