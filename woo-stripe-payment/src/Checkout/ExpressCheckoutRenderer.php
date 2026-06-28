<?php

namespace PaymentPlugins\Stripe\Checkout;

use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;

/**
 * Renders HTML containers for express payment buttons (Apple Pay, Google Pay, Link, etc.)
 * on product, cart, mini-cart, and checkout pages.
 *
 * @since   4.0.0
 * @author  PaymentPlugins
 * @package PaymentPlugins\Stripe
 */
class ExpressCheckoutRenderer {

	/**
	 * @var PaymentGatewayRegistry
	 */
	private $registry;

	/**
	 * @var string
	 */
	private $cart_button_position = 'after';

	/**
	 * @var string
	 */
	private $product_button_position = 'bottom';

	public function __construct( PaymentGatewayRegistry $registry ) {
		$this->registry = $registry;
	}

	public function initialize() {
		add_action( 'woocommerce_checkout_before_customer_details', [ $this, 'render_express_checkout' ] );
		add_action( 'woocommerce_before_add_to_cart_form', function () {
			$this->setup_product_button_position();
		} );
		add_action( 'woocommerce_widget_shopping_cart_buttons', [ $this, 'render_mini_cart_buttons' ], 5 );
		add_action( 'init', function () {
			$this->setup_cart_button_position();
		} );
	}

	/**
	 * Sets up the cart button position from the filter and registers the cart hook.
	 * Must run on `init` so filters from themes/plugins are applied first.
	 */
	private function setup_cart_button_position() {
		$priority                   = \apply_filters( 'wc_stripe_cart_buttons_order', 30 );
		$this->cart_button_position = $priority > 20 ? 'after' : 'before';
		add_action( 'woocommerce_proceed_to_checkout', [ $this, 'render_cart_buttons' ], $priority );
	}

	/**
	 * Determines product button position from product meta and registers the appropriate hook.
	 * Runs on `woocommerce_before_add_to_cart_form` so the global $product is available.
	 */
	private function setup_product_button_position() {
		global $product;
		$position = is_object( $product ) ? $product->get_meta( \WC_Stripe_Constants::BUTTON_POSITION ) : null;

		$this->product_button_position = $position ?: 'bottom';

		$action = $this->product_button_position === 'bottom'
			? 'woocommerce_after_add_to_cart_button'
			: 'woocommerce_before_add_to_cart_button';

		add_action( $action, [ $this, 'render_product_buttons' ] );
	}

	/**
	 * Renders the express checkout banner above the checkout form fields.
	 */
	public function render_express_checkout() {
		$gateways = $this->registry->get_express_payment_gateways();
		if ( $gateways ) {
			\wc_stripe_get_template( 'checkout/checkout-banner.php', [ 'gateways' => $gateways ] );
		}
	}

	/**
	 * Renders express payment buttons on the product page.
	 */
	public function render_product_buttons() {
		global $product;

		if ( $product->is_type( 'external' ) ) {
			return;
		}

		$gateways = $this->registry->get_product_payment_gateways();
		if ( ! $gateways ) {
			return;
		}

		$ordering = $product->get_meta( \WC_Stripe_Constants::PRODUCT_GATEWAY_ORDER ) ?: [];
		$sorted   = [];

		foreach ( $gateways as $gateway ) {
			if ( isset( $ordering[ $gateway->id ] ) ) {
				$sorted[ $ordering[ $gateway->id ] ] = $gateway;
			} else {
				$sorted[] = $gateway;
			}
		}
		ksort( $sorted );

		if ( ! $sorted ) {
			return;
		}

		\wc_stripe_get_template( 'product/payment-methods.php', [
			'position' => $this->product_button_position,
			'gateways' => $sorted,
		] );
	}

	/**
	 * Renders express payment buttons on the cart page.
	 */
	public function render_cart_buttons() {
		$gateways = $this->registry->get_cart_payment_gateways();
		if ( $gateways ) {
			\wc_stripe_get_template( 'cart/payment-methods.php', [
				'gateways'   => $gateways,
				'after'      => $this->cart_button_position === 'after',
				'cart_total' => \WC()->cart->get_total( 'float' ),
			] );
		}
	}

	/**
	 * Renders express payment buttons in the mini-cart widget.
	 */
	public function render_mini_cart_buttons() {
		$gateways = $this->registry->get_minicart_payment_gateways();
		if ( $gateways ) {
			\wc_stripe_get_template( 'mini-cart/payment-methods.php', [ 'gateways' => $gateways ] );
		}
	}

}