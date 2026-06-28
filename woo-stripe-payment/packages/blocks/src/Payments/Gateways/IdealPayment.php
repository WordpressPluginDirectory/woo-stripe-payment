<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class IdealPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_ideal';
}