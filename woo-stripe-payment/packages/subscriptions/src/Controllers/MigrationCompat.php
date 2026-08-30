<?php

namespace PaymentPlugins\Stripe\WooCommerceSubscriptions\Controllers;

/**
 * Fallback support for subscriptions migrated from other Stripe plugins that stored renewal payment data
 * outside the order/subscription meta this plugin owns (e.g. as user meta).
 *
 * @package PaymentPlugins\WooCommerceSubscriptions\Stripe
 */
class MigrationCompat {

	public function __construct() {
		$this->initialize();
	}

	private function initialize() {
		add_filter( 'wc_stripe_subscription_renewal_payment_method_fallback', [ $this, 'get_payment_method_fallback' ], 10, 2 );
	}

	/**
	 * @param string    $payment_method_id
	 * @param \WC_Order $order
	 *
	 * @return string
	 */
	public function get_payment_method_fallback( $payment_method_id, $order ) {
		if ( empty( $payment_method_id ) ) {
			$key = $this->get_legacy_meta_key( $order->get_payment_method() );
			if ( $key ) {
				$payment_method_id = trim( (string) get_user_meta( $order->get_user_id(), $key, true ) );
			}
		}

		return $payment_method_id;
	}

	/**
	 * eh-stripe-payment-gateway's legacy user meta key for the customer's default payment method, keyed by
	 * this plugin's equivalent gateway ID. Bancontact/iDEAL both use a SEPA debit PaymentMethod generated
	 * from the original authorization, since neither is directly reusable off-session.
	 *
	 * @param string $gateway_id
	 *
	 * @return string|null
	 */
	private function get_legacy_meta_key( $gateway_id ) {
		$keys = [
			'stripe_cc'         => '_stripe_payment_method',
			'stripe_sepa'       => '_sepa_payment_method',
			'stripe_bancontact' => '_stripe_bc_payment_method',
			'stripe_ideal'      => '_stripe_ideal_payment_method',
		];

		return isset( $keys[ $gateway_id ] ) ? $keys[ $gateway_id ] : null;
	}

}