<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class RevolutPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_revolut';

}