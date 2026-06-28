<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class PayByBankPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_paybybank';

}