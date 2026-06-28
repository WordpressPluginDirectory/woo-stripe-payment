<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class SwishPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_swish';

}