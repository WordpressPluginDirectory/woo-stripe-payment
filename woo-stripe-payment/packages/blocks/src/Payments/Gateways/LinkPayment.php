<?php

namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;

use PaymentPlugins\Stripe\Blocks\Assets\Api;
use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripePayment;
use PaymentPlugins\Stripe\Controllers\PaymentIntentController;
use PaymentPlugins\Stripe\Link\LinkIntegration;

class LinkPayment extends AbstractStripePayment {

	protected $name = 'stripe_link_checkout';

	private $link;

	/**
	 * @var \PaymentPlugins\Stripe\Controllers\PaymentIntentController
	 */
	private $payment_intent_ctrl;

	public function is_active() {
		return \wc_string_to_bool( $this->get_setting( 'enabled', 'no' ) );
	}

	public function get_payment_method_data() {
		return wp_parse_args(
			[
				'button' => [
					'radius' => $this->get_setting( 'button_radius', 4 ) . 'px',
					'height' => (int) $this->get_setting( 'button_height', 40 )
				],
			],
			parent::get_payment_method_data()
		);
	}

	public function get_payment_method_script_handles() {
		$this->assets_api->register_script( 'wc-stripe-blocks-link', 'build/wc-stripe-link-checkout.js' );

		return [ 'wc-stripe-blocks-link' ];
	}

	protected function get_payment_method_icon() {
		return [
			'id'  => "{$this->name}_icon",
			'alt' => 'Link',
			'src' => stripe_wc()->assets_url( "img/link.svg" )
		];
	}

}