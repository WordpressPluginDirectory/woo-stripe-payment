<?php

namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripePayment;

class GooglePayPayment extends AbstractStripePayment {

	protected $name = 'stripe_googlepay';

	public function get_payment_method_script_handles() {
		$this->assets_api->register_script( 'wc-stripe-blocks-googlepay', 'build/wc-stripe-googlepay.js' );

		return array( 'wc-stripe-blocks-googlepay' );
	}

	public function get_payment_method_data() {
		return wp_parse_args( array(
			'icon'        => $this->get_payment_method_icon(),
			'editorIcons' => array(
				'long'  => $this->assets_api->assets_url( 'assets/img/gpay_button_buy_black.svg' ),
				'short' => $this->assets_api->assets_url( 'assets/img/gpay_button_black.svg' )
			),
			'button'      => [
				'type'   => $this->get_setting( 'button_type_checkout', 'buy' ),
				'theme'  => $this->get_setting( 'button_theme', 'black' ),
				'height' => $this->get_setting( 'button_height', 40 ),
				'radius' => $this->get_setting( 'button_radius', 4 ) . 'px',
			],
			'buttonTypes' => [
				'checkout'         => $this->get_setting( 'button_type_checkout', 'buy' ),
				'express_checkout' => $this->get_setting( 'button_type_express_checkout', 'buy' ),
				'cart'             => $this->get_setting( 'button_type_cart', 'buy' ),
			],
			'displayRule' => \wc_string_to_bool( $this->get_setting( 'all_browsers', 'yes' ) ) ? 'always' : 'auto'
		), parent::get_payment_method_data() );
	}

	protected function get_payment_method_icon() {
		$icon = $this->payment_method->get_option( 'icon' );

		return array(
			'id'  => "{$this->name}_icon",
			'alt' => '',
			'src' => stripe_wc()->assets_url( "img/{$icon}.svg" )
		);
	}

	private function get_merchant_id() {
		return 'test' === wc_stripe_mode() ? '' : $this->payment_method->get_option( 'merchant_id' );
	}

	private function get_google_pay_environment() {
		return wc_stripe_mode() === 'test' ? 'TEST' : 'PRODUCTION';
	}

}