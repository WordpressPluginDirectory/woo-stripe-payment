<?php

namespace PaymentPlugins\Stripe\CartFlows;

use PaymentPlugins\Stripe\Assets\AssetsApi;

class FrontedScripts {

	private $assets_api;

	public function __construct( AssetsApi $assets_api ) {
		$this->assets_api = $assets_api;
	}

	public function initialize() {
		add_action( 'init', [ $this, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function register_scripts() {
		$this->assets_api->register_script( 'wc-stripe-cartflows-checkout', 'build/wc-stripe-cartflows.js' );
		$this->assets_api->register_style( 'wc-stripe-cartflows-styles', 'build/styles.css' );
	}

	public function enqueue_scripts() {
		if ( function_exists( '_is_wcf_checkout_type' ) ) {
			if ( _is_wcf_checkout_type() ) {
				wp_enqueue_style( 'wc-stripe-cartflows-styles' );
			}
		}
	}
}