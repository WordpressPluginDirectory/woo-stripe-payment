<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class KlarnaPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_klarna';

}