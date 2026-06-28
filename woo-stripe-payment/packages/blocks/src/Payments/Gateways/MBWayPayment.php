<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class MBWayPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_mbway';
}