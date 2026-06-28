<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class BoletoPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_boleto';

}