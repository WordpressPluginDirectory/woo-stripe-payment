<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class AfterpayPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_afterpay';

	public function get_supported_locales() {
		return apply_filters( 'wc_stripe_afterpay_supported_locales', [
			'en-US',
			'en-CA',
			'en-AU',
			'en-NZ',
			'en-GB',
			'fr-FR',
			'it-IT',
			'es-ES'
		] );
	}

	protected function get_payment_method_icon() {
		return array(
			'id'  => $this->get_name(),
			'alt' => 'Afterpay',
			'src' => stripe_wc()->assets_url( "img/{$this->get_setting('icon', 'afterpay')}.svg" )
		);
	}

}