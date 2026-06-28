<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class SatispayPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_satispay';
}