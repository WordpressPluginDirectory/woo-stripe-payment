<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripePayment;

class ApplePayPayment extends AbstractStripePayment {

	protected $name = 'stripe_applepay';

	public function get_payment_method_script_handles() {
		$this->assets_api->register_script( 'wc-stripe-blocks-apple-pay', 'build/wc-stripe-apple-pay.js' );

		return [ 'wc-stripe-blocks-apple-pay' ];
	}

	public function get_payment_method_data() {
		return wp_parse_args( [
			'button'      => [
				'type'   => $this->get_setting( 'button_type_checkout', 'plain' ),
				'theme'  => $this->get_setting( 'button_theme', 'black' ),
				'height' => $this->get_setting( 'button_height', 40 ),
				'radius' => $this->get_setting( 'button_radius', 4 ) . 'px',
			],
			'buttonTypes' => [
				'checkout'         => $this->get_setting( 'button_type_checkout', 'plain' ),
				'express_checkout' => $this->get_setting( 'button_type_express_checkout', 'plain' ),
				'cart'             => $this->get_setting( 'button_type_cart', 'plain' ),
			],
			'editorIcon'  => $this->assets_api->assets_url( 'assets/img/apple_pay_button_black.svg' ),
			'displayRule' => \wc_string_to_bool( $this->get_setting( 'all_browsers', 'yes' ) ) ? 'always' : 'auto'
		], parent::get_payment_method_data() );
	}

	protected function get_payment_method_icon() {
		return [
			'id'  => "{$this->name}_icon",
			'alt' => 'Apple Pay',
			'src' => stripe_wc()->assets_url( "img/applepay.svg" )
		];
	}

	private function get_button_theme() {
		$style = $this->get_setting( 'button_style', 'black' );
		switch ( $style ) {
			case 'apple-pay-button-white':
				return 'white';
			case 'apple-pay-button-white-with-line':
				return 'white-outline';
			default:
				return 'black';
		}
	}

}