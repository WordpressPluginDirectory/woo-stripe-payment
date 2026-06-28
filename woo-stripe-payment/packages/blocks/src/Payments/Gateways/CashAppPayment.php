<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class CashAppPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_cashapp';

}