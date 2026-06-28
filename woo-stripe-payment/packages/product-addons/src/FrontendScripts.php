<?php

namespace PaymentPlugins\Stripe\ProductAddons;

use PaymentPlugins\Stripe\Assets\AssetsApi;

class FrontendScripts {

	private $assets;

	public function __construct( AssetsApi $assets ) {
		$this->assets = $assets;
	}

	public function initialize() {
		add_action( 'init', function () {
			$this->register_scripts();
		} );
		add_action( 'wp_enqueue_scripts', function () {
			$this->enqueue_scripts();
		} );
	}

	private function register_scripts() {
		$this->assets->register_script( 'wc-stripe-product-addons-product', 'build/product.js' );
	}

	private function enqueue_scripts() {
		if ( is_product() ) {
			wp_enqueue_script( 'wc-stripe-product-addons-product' );
		}
	}
}