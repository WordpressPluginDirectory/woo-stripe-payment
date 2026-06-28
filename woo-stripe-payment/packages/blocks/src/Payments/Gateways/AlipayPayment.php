<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class AlipayPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_alipay';
}