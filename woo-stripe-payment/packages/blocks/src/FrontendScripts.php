<?php

namespace PaymentPlugins\Stripe\Blocks;

use PaymentPlugins\Stripe\Assets\AssetsApi;

class FrontendScripts {

	private $assets;

	public function __construct( AssetsApi $assets ) {
		$this->assets = $assets;
	}

	public function initialize() {
		add_action( 'init', [ $this, 'register_scripts' ] );
		add_action( 'woocommerce_blocks_enqueue_cart_block_scripts_after', [ $this, 'enqueue_scripts' ] );
		add_action( 'woocommerce_blocks_enqueue_checkout_block_scripts_after', [ $this, 'enqueue_scripts' ] );
	}

	public function register_scripts() {
		$this->assets->register_script( 'wc-stripe-blocks-vendors', 'build/wc-stripe-blocks-vendors.js' );
		$this->assets->register_script( 'wc-stripe-blocks-utils', 'build/blocks-utils.js' );
		$this->assets->register_script( 'wc-stripe-blocks-express-checkout', 'build/blocks-express-checkout.js' );
		$this->assets->register_script( 'wc-stripe-blocks-components', 'build/blocks-components.js' );

		$this->assets->register_style( 'wc-stripe-blocks-styles', 'build/style.css' );
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'wc-stripe-blocks-styles' );

		do_action( 'wc_stripe_blocks_enqueue_styles' );
	}
}