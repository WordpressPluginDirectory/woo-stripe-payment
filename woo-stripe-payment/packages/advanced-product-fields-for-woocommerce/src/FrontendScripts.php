<?php

namespace PaymentPlugins\Stripe\AdvancedProductFieldsForWooCommerce;

use PaymentPlugins\Stripe\Assets\AssetsApi;

class FrontendScripts {

	private $assets_api;

	public function __construct( AssetsApi $assets_api ) {
		$this->assets_api = $assets_api;
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
		$this->assets_api->register_script( 'wc-stripe-wapf-product', 'build/product.js' );
	}

	private function enqueue_scripts() {
		if ( is_product() ) {
			wp_enqueue_script( 'wc-stripe-wapf-product' );
		}
	}
}