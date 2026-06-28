<?php

namespace PaymentPlugins\Stripe\Blocks\BlockTypes;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use PaymentPlugins\Stripe\Messages\BNPL\BNPLMessageController;

class BNPLMessageCartBlock implements IntegrationInterface {

	private $controller;

	private $settings;

	private $assets;

	public function __construct( BNPLMessageController $controller, \WC_Stripe_Advanced_Settings $settings, $assets ) {
		$this->controller = $controller;
		$this->settings   = $settings;
		$this->assets     = $assets;
	}

	public function get_name() {
		return 'stripeBNPLCart';
	}

	public function initialize() {
		// TODO: Implement initialize() method.
	}

	public function get_script_handles() {
		$handles  = [];
		$gateways = $this->controller->get_supported_gateways();
		if ( ! empty( $gateways ) ) {
			$this->assets->register_script( 'wc-stripe-bpnl-message-cart-block', 'build/bnpl-message-cart-block.js' );
			$handles[] = 'wc-stripe-bpnl-message-cart-block';
		}

		return $handles;
	}

	public function get_editor_script_handles() {
		return [];
	}

	public function get_script_data() {
		$data     = [];
		$gateways = $this->controller->get_supported_gateways();
		if ( ! empty( $gateways ) ) {
			$data = [
				'paymentMethods' => array_values( array_map( function ( $gateway ) {
					return [
						'id'                => $gateway->id,
						'paymentMethodType' => $gateway->get_payment_method_type()
					];
				}, $gateways ) ),
				'currencies'     => $this->controller->get_supported_currencies(),
				'countries'      => $this->controller->get_supported_countries(),
				'countryCode'    => stripe_wc()->account_settings->get_account_country( wc_stripe_mode() ),
				'elementOptions' => [
					'locale'     => wc_stripe_get_site_locale(),
					'appearance' => [
						'theme' => stripe_wc()->advanced_settings->get_option( 'bnpl_theme', 'stripe' )
					]
				],
				'locations'      => [
					'cart'     => 'below_total',
					'checkout' => $this->settings->get_option( 'bnpl_checkout_location', 'payment_method_title' )
				]
			];
		}

		return $data;
	}

}