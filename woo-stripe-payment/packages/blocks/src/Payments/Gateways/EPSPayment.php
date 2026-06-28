<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class EPSPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_eps';
}