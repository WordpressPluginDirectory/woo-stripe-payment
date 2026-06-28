<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe' ) ) {
	return;
}

/**
 *
 * @since   3.0.0
 * @author  PaymentPlugins
 * @package PaymentPlugins\Gateways
 */
class WC_Payment_Gateway_Stripe_GooglePay extends WC_Payment_Gateway_Stripe {

	use WC_Stripe_Payment_Intent_Trait;
	use \PaymentPlugins\Stripe\Traits\TokenizationTrait;
	use \PaymentPlugins\Stripe\WooCommercePreOrders\Traits\PreOrdersTrait;
	use \PaymentPlugins\Stripe\WooCommerceSubscriptions\Traits\WooCommerceSubscriptionsTrait;

	public $id = 'stripe_googlepay';

	protected $payment_method_type = 'card';

	public function __construct( ...$args ) {
		$this->tab_title          = __( 'Google Pay', 'woo-stripe-payment' );
		$this->template_name      = 'googlepay.php';
		$this->token_type         = 'Stripe_GooglePay';
		$this->method_title       = __( 'Google Pay (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Google Pay gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		$this->has_digital_wallet = true;
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/' . $this->get_option( 'icon' ) . '.svg' );
	}

	public function init_supports() {
		parent::init_supports();
		$this->supports[] = 'wc_stripe_cart_checkout';
		$this->supports[] = 'wc_stripe_product_checkout';
		$this->supports[] = 'wc_stripe_banner_checkout';
		$this->supports[] = 'wc_stripe_mini_cart_checkout';
	}

	public function get_checkout_script_handles() {
		$this->assets->register_script( 'wc-stripe-googlepay-checkout', 'build/googlepay-checkout.js' );

		return [ 'wc-stripe-googlepay-checkout' ];
	}

	public function get_add_payment_method_script_handles() {
		$this->assets->register_script( 'wc-stripe-googlepay-add-payment', 'build/googlepay-add-payment.js' );

		return [ 'wc-stripe-googlepay-add-payment' ];
	}

	public function get_express_checkout_script_handles() {
		$this->assets->register_script( 'wc-stripe-googlepay-express-checkout', 'build/googlepay-express-checkout.js' );

		return [ 'wc-stripe-googlepay-express-checkout' ];
	}

	public function get_product_script_handles() {
		$this->assets->register_script( 'wc-stripe-googlepay-product', 'build/googlepay-product.js' );

		return [ 'wc-stripe-googlepay-product' ];
	}

	public function get_cart_script_handles() {
		$this->assets->register_script( 'wc-stripe-googlepay-cart', 'build/googlepay-cart.js' );

		return [ 'wc-stripe-googlepay-cart' ];
	}

	public function get_minicart_script_handles() {
		$this->assets->register_script( 'wc-stripe-googlepay-minicart', 'build/googlepay-minicart.js' );

		return [ 'wc-stripe-googlepay-minicart' ];
	}

	public function enqueue_admin_scripts() {
		wp_register_script( 'wc-stripe-googlepay-external', 'https://pay.google.com/gp/p/js/pay.js', [], null, true );

		$this->assets->register_script(
			'wc-stripe-googlepay-settings',
			'build/googlepay-settings.js',
			[ 'wc-stripe-googlepay-external', 'wc-stripe-admin-settings' ]
		);

		wp_enqueue_script( 'wc-stripe-googlepay-settings' );
	}

	public function get_payment_method_data() {
		return array_merge(
			parent::get_payment_method_data(),
			[
				'button'       => [
					'height' => (int) $this->get_option( 'button_height', 40 ),
					'radius' => $this->get_option( 'button_radius', 4 ) . 'px',
					'theme'  => $this->get_option( 'button_theme', 'black' ),
					'type'   => $this->get_option( 'button_type_checkout', 'buy' )
				],
				'buttonTypes'  => [
					'checkout'         => $this->get_option( 'button_type_checkout', 'buy' ),
					'express_checkout' => $this->get_option( 'button_type_express_checkout', 'buy' ),
					'cart'             => $this->get_option( 'button_type_cart', 'buy' ),
					'product'          => $this->get_option( 'button_type_product', 'buy' ),
					'minicart'         => $this->get_option( 'button_type_cart', 'buy' )
				],
				'display_rule' => \wc_string_to_bool( $this->get_option( 'all_browsers', 'yes' ) ) ? 'always' : 'auto'
			]
		);
	}

	/**
	 * @return mixed|void
	 * @since 3.3.14
	 */
	public function get_payment_button_locale() {
		$locale        = wc_stripe_get_site_locale();
		$button_locale = null;
		if ( 'auto' !== $locale ) {
			$button_locale = substr( $locale, 0, 2 );
			if ( ! in_array( $button_locale, $this->get_supported_button_locales() ) ) {
				$button_locale = null;
			}
		}

		return apply_filters( 'wc_stripe_googlepay_get_button_locale', $button_locale, $this );
	}

	/**
	 * @return mixed|void
	 * @since 3.3.14
	 */
	public function get_supported_button_locales() {
		return apply_filters( 'wc_stripe_googlepay_supported_button_locales',
			array(
				'en',
				'ar',
				'bg',
				'ca',
				'cs',
				'da',
				'de',
				'el',
				'es',
				'et',
				'fi',
				'fr',
				'hr',
				'id',
				'it',
				'ja',
				'ko',
				'ms',
				'nl',
				'no',
				'pl',
				'pt',
				'ru',
				'sk',
				'sl',
				'sr',
				'sv',
				'th',
				'tr',
				'uk',
				'zh'
			)
		);
	}

}
