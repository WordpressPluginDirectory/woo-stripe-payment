<?php

namespace PaymentPlugins\Stripe\CheckoutWC;

use PaymentPlugins\Stripe\Assets\AssetsApi;

class AssetsController {

	/**
	 * @var AssetsApi
	 */
	private $assets;

	private $version;

	private $path;

	private $assets_path;

	public function __construct( AssetsApi $assets, $version, $assets_path ) {
		$this->assets      = $assets;
		$this->version     = $version;
		$this->assets_path = $assets_path;
	}

	public function initialize() {
		add_action( 'cfw_payment_request_buttons', [ $this, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function enqueue_styles() {
		foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
			if ( $gateway instanceof \WC_Payment_Gateway_Stripe ) {
				if ( $gateway->supports( 'wc_stripe_banner_checkout' ) && $gateway->is_express_checkout_enabled() ) {
					wp_enqueue_script( 'wc-stripe-checkoutwc-checkout' );
					wp_enqueue_style( 'wc-stripe-checkoutwc-style', $this->assets_path . 'build/styles.css', [], $this->version );
					break;
				}
			}
		}
	}

	public function enqueue_scripts() {
		$this->assets->register_script( 'wc-stripe-checkoutwc-checkout', 'build/checkout.js' );
	}

}