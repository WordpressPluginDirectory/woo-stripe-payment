<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class FPXPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_fpx';
}