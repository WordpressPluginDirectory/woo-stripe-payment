<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class P24Payment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_p24';
}