<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class TwintPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_twint';

}