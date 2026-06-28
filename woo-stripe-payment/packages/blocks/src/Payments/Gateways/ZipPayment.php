<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class ZipPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_zip';

}