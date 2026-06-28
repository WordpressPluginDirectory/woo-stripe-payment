<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1;

use PaymentPlugins\Stripe\Rest\Routes\AbstractRoute;
use PaymentPlugins\Stripe\Transformers\DataTransformer;

class CartCalculation extends AbstractCart {

	/**
	 * @inheritDoc
	 */
	public function get_path() {
		return 'cart/calculation';
	}

	/**
	 * @inheritDoc
	 */
	public function get_routes() {
		return [
			[
				'methods'  => \WP_REST_Server::EDITABLE,
				'callback' => [ $this, 'handle_request' ],
				'args'     => [
					'product_id' => [
						'required' => true
					],
					'qty'        => [
						'required'          => true,
						'validate_callback' => function ( ...$args ) {
							return $this->validate_quantity( ...$args );
						}
					]
				]
			]
		];
	}

	/**
	 * Handle cart calculation request
	 *
	 * Calculates what the cart would look like with the given product/variation/quantity
	 * without actually modifying the real cart. Used for updating payment element totals
	 * for non-shippable products.
	 *
	 * @param \WP_REST_Request $request Request containing product_id, qty, variation_id, variation
	 *
	 * @return array|\WP_Error Cart data
	 */
	public function handle_post_request( \WP_REST_Request $request ) {
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		$this->populate_post_data( $request );

		$product_id    = $request->get_param( 'product_id' );
		$qty           = $request->get_param( 'qty' );
		$variation_id  = $request->get_param( 'variation_id' );
		$variation     = $this->get_variation_data( $request );
		$cart_item_key = null;
		// Use a unique cart ID so our temporary item gets its own key even if the same product/variation
		// is already in the cart. Without this, add_to_cart() returns the existing item's key and the
		// finally block would remove the customer's real cart item instead of our temporary one.
		$filter_fn = function ( $cart_id ) {
			return $cart_id . '_wc_stripe_calculation';
		};
		try {
			add_filter( 'woocommerce_cart_id', $filter_fn );
			$cart_item_key = WC()->cart->add_to_cart( $product_id, $qty, $variation_id, $variation );

			if ( ! $cart_item_key ) {
				return new \WP_Error(
					'cart_calculation_error',
					$this->get_wc_notice( 'error' ),
					[ 'status' => 400 ]
				);
			}


			// Calculate totals with product included
			WC()->cart->calculate_totals();

			// Transform cart data
			$data_transformer = new DataTransformer();
			$cart_data        = $data_transformer->transform_cart( WC()->cart );

			return [ 'cart' => $cart_data ];

		} catch ( \Exception $e ) {
			wc_stripe_log_error( sprintf( 'Error performing cart calculation: %s', $e->getMessage() ) );
		} finally {
			remove_filter( 'woocommerce_cart_id', $filter_fn );
			// Always remove the item we added
			if ( $cart_item_key ) {
				// cart totals are re-calculated when an item is removed from the cart.
				WC()->cart->remove_cart_item( $cart_item_key );
			}
		}
	}

	/**
	 * Get variation data from request
	 *
	 * Extracts all parameters starting with 'attribute_' to build variation array
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array Variation attributes
	 */
	protected function get_variation_data( \WP_REST_Request $request ) {
		$variation = [];

		if ( $request->get_param( 'variation_id' ) ) {
			foreach ( $request->get_params() as $key => $value ) {
				if ( strpos( $key, 'attribute_' ) === 0 ) {
					$variation[ sanitize_title( wp_unslash( $key ) ) ] = wp_unslash( $value );
				}
			}
		}

		return $variation;
	}

	/**
	 *
	 * @param int             $qty
	 * @param WP_REST_Request $request
	 */
	private function validate_quantity( $qty, $request ) {
		if ( $qty == 0 ) {
			return new \WP_Error( 'cart-error', __( 'Quantity must be greater than zero.', 'woo-stripe-payment' ) );
		}

		return true;
	}
}