<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class ScalapayPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_scalapay';
}