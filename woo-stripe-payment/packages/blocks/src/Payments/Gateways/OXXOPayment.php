<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class OXXOPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_oxxo';
}