<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe' ) ) {
	return;
}

/**
 *
 * @package PaymentPlugins\Gateways
 * @author  PaymentPlugins
 *
 */
class WC_Payment_Gateway_Stripe_ApplePay extends WC_Payment_Gateway_Stripe {

	use WC_Stripe_Payment_Intent_Trait;
	use \PaymentPlugins\Stripe\Traits\TokenizationTrait;
	use \PaymentPlugins\Stripe\Traits\ExpressCheckoutTrait;
	use \PaymentPlugins\Stripe\WooCommercePreOrders\Traits\PreOrdersTrait;
	use \PaymentPlugins\Stripe\WooCommerceSubscriptions\Traits\WooCommerceSubscriptionsTrait;

	public $id = 'stripe_applepay';

	protected $payment_method_type = 'card';

	public function __construct( ...$args ) {
		$this->tab_title          = __( 'Apple Pay', 'woo-stripe-payment' );
		$this->template_name      = 'applepay.php';
		$this->token_type         = 'Stripe_ApplePay';
		$this->method_title       = __( 'Apple Pay (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Apple Pay gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		$this->has_digital_wallet = true;
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/applepay.svg' );
	}

	public function get_checkout_script_handles() {
		$this->assets->register_script( 'wc-stripe-applepay-checkout', 'build/applepay-checkout.js' );

		return [ 'wc-stripe-applepay-checkout' ];
	}

	public function get_add_payment_method_script_handles() {
		$this->assets->register_script( 'wc-stripe-applepay-add-payment', 'build/applepay-add-payment.js' );

		return [ 'wc-stripe-applepay-add-payment' ];
	}

	public function get_express_checkout_script_handles() {
		$this->assets->register_script(
			'wc-stripe-applepay-express-checkout',
			'build/applepay-express-checkout.js'
		);

		return [ 'wc-stripe-applepay-express-checkout' ];
	}

	public function get_cart_script_handles() {
		$this->assets->register_script( 'wc-stripe-applepay-cart', 'build/applepay-cart.js' );

		return [ 'wc-stripe-applepay-cart' ];
	}

	public function get_product_script_handles() {
		$this->assets->register_script( 'wc-stripe-applepay-product', 'build/applepay-product.js' );

		return [ 'wc-stripe-applepay-product' ];
	}

	public function get_minicart_script_handles() {
		$this->assets->register_script( 'wc-stripe-applepay-minicart', 'build/applepay-minicart.js' );

		return [ 'wc-stripe-applepay-minicart' ];
	}

	public function get_payment_method_data() {
		return array_merge(
			parent::get_payment_method_data(),
			[
				'button'       => [
					'height' => (int) $this->get_option( 'button_height', 40 ),
					'radius' => $this->get_option( 'button_radius', 4 ) . 'px',
					'theme'  => $this->get_option( 'button_theme', 'black' ),
					'type'   => $this->get_option( 'button_type_checkout', 'plain' )
				],
				'buttonTypes'  => [
					'checkout'         => $this->get_option( 'button_type_checkout' ),
					'express_checkout' => $this->get_option( 'button_type_express_checkout', 'plain' ),
					'cart'             => $this->get_option( 'button_type_cart', 'plain' ),
					'product'          => $this->get_option( 'button_type_product', 'plain' ),
					'minicart'         => $this->get_option( 'button_type_cart', 'plain' )
				],
				'display_rule' => \wc_string_to_bool( $this->get_option( 'all_browsers', 'yes' ) ) ? 'always' : 'auto'
			]
		);
	}

	/**
	 * Returns the Apple Pay button type based on the current page.
	 *
	 * @return string
	 */
	protected function get_button_type() {
		if ( is_checkout() ) {
			return $this->get_option( 'button_type_checkout' );
		}
		if ( is_cart() ) {
			return $this->get_option( 'button_type_cart' );
		}
		if ( is_product() ) {
			return $this->get_option( 'button_type_product' );
		}

		return $this->get_option( 'button_type_cart' );
	}

}
