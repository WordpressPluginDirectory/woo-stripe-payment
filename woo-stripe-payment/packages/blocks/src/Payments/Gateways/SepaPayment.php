<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class SepaPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_sepa';

	public function get_payment_method_data() {
		return wp_parse_args( array(
			'mandateText' => $this->payment_method->get_local_payment_description()
		), parent::get_payment_method_data() );
	}

}