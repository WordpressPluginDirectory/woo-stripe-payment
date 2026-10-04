<?php

namespace PaymentPlugins\Stripe\Cart;

/**
 * Cross-cutting cart concerns not specific to any single REST route.
 *
 * @since 4.0.17
 */
class CartController {

	public function initialize() {
		add_filter( 'woocommerce_pre_remove_cart_item_from_session', [
			$this,
			'discard_calculation_item'
		], 10, 2 );
	}

	/**
	 * Self-healing: discards any leftover _wc_stripe_calculation-suffixed cart item (added by
	 * CartCalculation, which simulates adding a product to determine totals without it being a
	 * real cart change) whenever the cart loads from session, regardless of how it ended up
	 * persisted. CartCalculation's own shutdown-save suppression covers the known paths, but
	 * can't anticipate every possible synchronous session write - WC core and 3rd party code
	 * both have some (e.g. WC_Session_Handler::migrate_guest_session_to_user_session() saves
	 * directly, not via the shutdown hook).
	 *
	 * @param bool   $remove
	 * @param string $key
	 *
	 * @return bool
	 */
	public function discard_calculation_item( $remove, $key ) {
		if ( $remove ) {
			return $remove;
		}
		$suffix = '_wc_stripe_calculation';

		return substr( $key, - strlen( $suffix ) ) === $suffix;
	}

}