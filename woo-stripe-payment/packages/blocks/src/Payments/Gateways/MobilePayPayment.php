<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class MobilePayPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_mobilepay';

}