<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class BancontactPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_bancontact';

}