<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class BECSPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_becs';

	public function get_payment_method_data() {
		return array_merge( parent::get_payment_method_data(), [ 'mandate' => $this->payment_method->get_local_payment_description() ] );
	}

}