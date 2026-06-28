<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class SofortPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_sofort';
}