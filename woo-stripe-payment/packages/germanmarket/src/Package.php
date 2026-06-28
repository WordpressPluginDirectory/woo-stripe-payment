<?php

namespace PaymentPlugins\Stripe\GermanMarket;


use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\Packages\AbstractPackage;

class Package extends AbstractPackage {

	public $id = 'germanmarket';

	public function is_active() {
		return \class_exists( 'Woocommerce_German_Market' );
	}

	public function register() {
		add_action( 'wp_enqueue_scripts', function () {
			if ( wc_post_content_has_shortcode( 'woocommerce_de_check' ) ) {
				wp_enqueue_style( 'wc-stripe-german-market' );
			}
			if ( is_checkout() ) {
				wp_enqueue_script( 'wc-stripe-german-market-checkout' );
				wp_localize_script( 'wc-stripe-german-market-checkout', 'wc_stripe_german_market_params', [
					'second_checkout' => get_option( 'woocommerce_de_secondcheckout', 'off' )
				] );
			}
		} );
	}

	public function initialize() {
		$assets = new AssetsApi(
			dirname( __DIR__ ) . '/',
			trailingslashit( plugin_dir_url( __DIR__ ) ),
			stripe_wc()->version()
		);
		$assets->register_style( 'wc-stripe-german-market', 'build/styles.css' );
		$assets->register_script( 'wc-stripe-german-market-checkout', 'build/checkout.js' );
	}
}