<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class GiropayPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_giropay';

}