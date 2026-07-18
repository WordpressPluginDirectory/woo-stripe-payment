<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1;

/**
 * Abstract base class for cart-related REST API routes
 *
 * Provides common functionality for cart operations like
 * adding items, updating shipping, processing checkout, etc.
 *
 * @since 4.0.0
 */
abstract class AbstractCart extends AbstractRoute {

	/**
	 * Get the route path
	 *
	 * @return string
	 */
	public function get_path() {
		return 'cart';
	}

	/**
	 * Get routes configuration
	 *
	 * @return array
	 */
	public function get_routes() {
		return [];
	}

	/**
	 * Get payment gateway from request
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WC_Payment_Gateway|null
	 */
	protected function get_payment_method_from_request( \WP_REST_Request $request ) {
		$payment_method = $request->get_param( 'payment_method' );

		if ( ! $payment_method ) {
			return null;
		}

		$gateways = WC()->payment_gateways()->payment_gateways();

		return $gateways[ $payment_method ] ?? null;
	}

	/**
	 * Calculate cart totals
	 *
	 * @return void
	 */
	protected function calculate_totals() {
		WC()->cart->calculate_totals();
	}

	/**
	 * Clear cached shipping rates
	 *
	 * Forces WooCommerce to recalculate shipping rates
	 *
	 * @return void
	 */
	protected function clear_cached_shipping_rates() {
		$packages = WC()->cart->get_shipping_packages();

		foreach ( $packages as $key => $value ) {
			$shipping_for_package_key = 'shipping_for_package_' . $key;
			unset( WC()->session->{$shipping_for_package_key} );
		}
	}

	/**
	 * Validate payment method
	 *
	 * @param string           $payment_method Payment method ID
	 * @param \WP_REST_Request $request Request object
	 *
	 * @return bool
	 */
	public function validate_payment_method( $payment_method, \WP_REST_Request $request ) {
		$gateways = WC()->payment_gateways()->payment_gateways();

		return isset( $gateways[ $payment_method ] );
	}

}