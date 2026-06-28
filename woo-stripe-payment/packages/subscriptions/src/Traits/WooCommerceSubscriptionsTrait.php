<?php

namespace PaymentPlugins\Stripe\WooCommerceSubscriptions\Traits;

trait WooCommerceSubscriptionsTrait {

	public static array $WooCommerceSubscriptionsTraitFeatures = [
		'subscriptions',
		'subscription_cancellation',
		'multiple_subscriptions',
		'subscription_amount_changes',
		'subscription_date_changes',
		'subscription_payment_method_change_admin',
		'subscription_reactivation',
		'subscription_suspension',
		'subscription_payment_method_change_customer'
	];
}