<?php

namespace PaymentPlugins\Stripe\Traits;

trait ExpressCheckoutTrait {

	protected static array $ExpressCheckoutTraitFeatures = [
		'wc_stripe_product_checkout',
		'wc_stripe_cart_checkout',
		'wc_stripe_banner_checkout',
		'wc_stripe_mini_cart_checkout'
	];
}