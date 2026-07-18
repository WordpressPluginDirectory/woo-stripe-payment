<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1;

use PaymentPlugins\Stripe\Transformers\DataTransformer;

/**
 * Cart Shipping Route
 *
 * Handles shipping address and method updates for express checkout
 * Used by Link, Apple Pay, Google Pay payment sheets
 *
 * @since 4.0.0
 */
class CartShipping extends AbstractCart {

	/**
	 * Get the route path
	 *
	 * @return string
	 */
	public function get_path() {
		return 'cart/shipping';
	}

	/**
	 * Get route configuration
	 *
	 * @return array
	 */
	public function get_routes() {
		return [
			[
				'methods'  => \WP_REST_Server::EDITABLE,
				'callback' => [ $this, 'handle_request' ],
				'args'     => [
					'payment_method' => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_payment_method' ]
					]
				]
			]
		];
	}

	/**
	 * Handle POST request - Update shipping
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error
	 */
	public function handle_post_request( \WP_REST_Request $request ) {
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		try {
			// Step 1: Update shipping address if provided
			if ( $request->has_param( 'address' ) ) {
				$this->update_shipping_address( $request->get_param( 'address' ) );
			}

			// Always apply postcode format filter - needed for redacted postcodes (e.g. Apple Pay CA/GB)
			$country = $request->get_param( 'address' )['country'] ?? WC()->customer->get_shipping_country();
			$this->add_postcode_format_filter( $country );

			// Step 2: Update shipping method if provided
			if ( $request->has_param( 'shipping_method' ) ) {
				$this->update_shipping_method( $request->get_param( 'shipping_method' ) );
			}

			// Step 3: Recalculate totals
			$this->populate_post_data( $request );
			$this->clear_cached_shipping_rates();
			add_filter( 'woocommerce_cart_ready_to_calc_shipping', '__return_true', 1000 );
			$this->calculate_totals();

			// Step 4: Build response (applies wc_stripe_cart_shipping_packages filter once)
			$response = $this->get_shipping_response();

			// Step 5: Validate shipping options are available
			if ( ! $this->has_shipping_options( $response ) ) {
				$address = $request->get_param( 'address' );

				// Only throw error if address is complete
				if ( $this->is_address_complete( $address ) ) {
					throw new \Exception(
						__( 'There are no shipping options available for the provided address.', 'woo-stripe-payment' ),
						404
					);
				}
			}

			return $response;

		} catch ( \Exception $e ) {
			return new \WP_Error(
				'shipping-error',
				$e->getMessage(),
				[ 'status' => $e->getCode() ?: 400 ]
			);
		}
	}

	/**
	 * Update customer shipping address
	 *
	 * @param array $address Address data
	 *
	 * @return void
	 */
	protected function update_shipping_address( $address ) {
		$customer = WC()->customer;

		$country  = $address['country'] ?? '';
		$state    = $address['state'] ?? '';
		$postcode = $address['postcode'] ?? '';
		$city     = $address['city'] ?? '';

		// Normalize state code
		if ( $country && $state ) {
			$state = $this->normalize_state( $state, $country );
		}

		$customer->set_billing_location( $country, $state, $postcode, $city );
		$customer->set_shipping_location( $country, $state, $postcode, $city );
		$customer->set_calculated_shipping( true );
		$customer->save();
	}

	/**
	 * Update selected shipping method
	 *
	 * @param string $shipping_method Shipping method ID
	 *
	 * @return void
	 */
	protected function update_shipping_method( $shipping_method ) {
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods', [] );

		// Format: "{package_index}:{method_id}:{instance_id}" or "{method_id}:{instance_id}"
		// Rate IDs always have exactly one colon, so more than one colon means a package index is present.
		// The index can be an integer (WC default) or a word string (e.g. WC Subscriptions recurring cart key).
		if ( substr_count( $shipping_method, ':' ) > 1 ) {
			$pos   = strpos( $shipping_method, ':' );
			$index = substr( $shipping_method, 0, $pos );
			$id    = substr( $shipping_method, $pos + 1 );
		} else {
			$index = 0;
			$id    = $shipping_method;
		}

		$chosen_shipping_methods[ $index ] = $id;

		WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
	}

	/**
	 * Check if the shipping response contains available shipping options.
	 *
	 * @param array $response
	 *
	 * @return bool
	 */
	protected function has_shipping_options( $response ) {
		return ! empty( $response['shippingOptions'] );
	}

	/**
	 * Check if address is complete enough to require shipping
	 *
	 * @param array|null $address Address data
	 *
	 * @return bool
	 */
	protected function is_address_complete( $address ) {
		if ( ! $address || empty( $address['country'] ) ) {
			return false;
		}

		$country = $address['country'];
		$fields  = WC()->countries->get_address_fields( $country, 'shipping_' );

		foreach ( [ 'country', 'state', 'postcode', 'city' ] as $key ) {
			$field_key = 'shipping_' . $key;

			if ( isset( $fields[ $field_key ] ) && ! empty( $fields[ $field_key ]['required'] ) ) {
				if ( empty( $address[ $key ] ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Normalize state code for country
	 *
	 * @param string $state State code or name
	 * @param string $country Country code
	 *
	 * @return string
	 */
	protected function normalize_state( $state, $country ) {
		$states = WC()->countries->get_states( $country );

		if ( ! is_array( $states ) ) {
			return $state;
		}

		// If state is already a valid code, return it
		if ( isset( $states[ $state ] ) ) {
			return $state;
		}

		// Try to find state code by name
		$state_lower = strtolower( $state );
		foreach ( $states as $code => $name ) {
			if ( strtolower( $name ) === $state_lower ) {
				return $code;
			}
		}

		return $state;
	}

	/**
	 * Get shipping response data
	 *
	 * @return array
	 */
	protected function get_shipping_response() {
		$cart        = WC()->cart;
		$transformer = new DataTransformer();

		return $transformer->transform_cart( $cart );
	}

	private function add_postcode_format_filter( $country ) {
		if ( in_array( $country, array( 'CA', 'GB' ) ) ) {
			add_filter( 'woocommerce_format_postcode', function ( $formatted_postcode, $country ) {
				switch ( $country ) {
					case 'CA':
					case 'GB':
						$postcode = str_replace( ' ', '', $formatted_postcode );
						if ( strlen( $postcode ) <= 4 ) {
							$formatted_postcode = $postcode;
						}
						break;
				}

				return $formatted_postcode;
			}, 10, 2 );
		}
	}

}