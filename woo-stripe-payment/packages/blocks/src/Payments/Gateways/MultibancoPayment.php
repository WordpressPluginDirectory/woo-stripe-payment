<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class MultibancoPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_multibanco';
}