<?php

namespace PaymentPlugins\Stripe;

use PaymentPlugins\Stripe\Assets\AssetsApi;

/**
 * @since 4.0.0
 */
class FrontendScripts {

	private $assets;

	public function __construct( AssetsApi $assets ) {
		$this->assets = $assets;
	}

	public function initialize() {
		add_action( 'init', [ $this, 'register_scripts' ] );
	}

	public function register_scripts() {
		$this->assets->register_script( 'wc-stripe-cart', 'build/cart.js' );
		$this->assets->register_script( 'wc-stripe-order', 'build/order.js' );
		$this->assets->register_script( 'wc-stripe-product', 'build/product.js' );
		$this->assets->register_script( 'wc-stripe-core-vendors', 'build/wc-stripe-core-vendors.js' );

		$this->assets->register_script( 'wc-stripe-utils', 'build/utils.js' );
		$this->assets->register_script( 'wc-stripe-actions', 'build/actions.js' );
		$this->assets->register_script( 'wc-stripe-context', 'build/context.js' );
		$this->assets->register_script( 'wc-stripe-sdk', 'build/stripe-sdk.js' );
		$this->assets->register_script( 'wc-stripe-checkout-fields', 'build/checkout-fields.js' );
		$this->assets->register_script( 'wc-stripe-dom-events', 'build/dom-events.js' );

		$this->assets->register_script( 'wc-stripe-controllers', 'build/controllers.js' );
		$this->assets->register_script( 'wc-stripe-gateways', 'build/gateways.js' );

		// The local payment method scripts are added here because the same script is used by many gateways. It's better
		// to register it once rather than in each gateway.
		$this->assets->register_script( 'wc-stripe-local-payment-checkout', 'build/local-payment-checkout.js' );
		$this->assets->register_script( 'wc-stripe-local-payment-add-payment', 'build/local-payment-add-payment.js' );

		// BNPL script
		$this->assets->register_script( 'wc-stripe-bnpl-messages', 'build/bnpl-messages.js' );

		$this->assets->register_style( 'wc-stripe-styles', 'build/styles.css' );
	}
}