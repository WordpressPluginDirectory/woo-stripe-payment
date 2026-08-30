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

		// This request only simulates adding a product to determine totals; none of it should
		// be visible to the customer as a real cart change. Three separate side effects have to
		// be suppressed for that:
		// - WC_Cart_Session::set_session() only writes to WC()->session in memory - the actual
		//   DB write happens once, on shutdown, via WC_Session_Handler::save_data(). Removing it
		//   makes it safe to skip re-syncing totals after we remove our temporary item below,
		//   since nothing will read or persist them.
		// - woocommerce_add_to_cart triggers WC_Cart_Session::maybe_set_cart_cookies()
		//   immediately, which would set woocommerce_items_in_cart/woocommerce_cart_hash
		//   cookies reflecting the temporary item before it's removed.
		// - woocommerce_add_to_cart/woocommerce_cart_item_removed both trigger
		//   WC_Cart_Session::persistent_cart_update(), writing the simulated cart to usermeta
		//   for logged-in users.
		if ( WC()->session && method_exists( WC()->session, 'save_data' ) ) {
			remove_action( 'shutdown', [ WC()->session, 'save_data' ], 20 );
		}
		add_filter( 'woocommerce_set_cookie_enabled', '__return_false' );
		add_filter( 'woocommerce_persistent_cart_enabled', '__return_false' );

		try {
			add_filter( 'woocommerce_cart_id', $filter_fn );
			// add_to_cart() already triggers WC_Cart's own calculate_totals() via the
			// woocommerce_add_to_cart hook (registered in WC_Cart::__construct()), so totals
			// are current below without an explicit call.
			$cart_item_key = WC()->cart->add_to_cart( $product_id, $qty, $variation_id, $variation );

			if ( ! $cart_item_key ) {
				return new \WP_Error(
					'cart_calculation_error',
					$this->get_wc_notice( 'error' ),
					[ 'status' => 400 ]
				);
			}

			// Transform cart data
			$data_transformer = new DataTransformer();
			$cart_data        = $data_transformer->transform_cart( WC()->cart );

			return [ 'cart' => $cart_data ];

		} catch ( \Exception $e ) {
			wc_stripe_log_error( sprintf( 'Error performing cart calculation: %s', $e->getMessage() ) );
		} finally {
			remove_filter( 'woocommerce_cart_id', $filter_fn );
			// woocommerce_set_cookie_enabled/woocommerce_persistent_cart_enabled are deliberately
			// left disabled for the rest of this request, same as the shutdown removal above -
			// this is a single-purpose AJAX bridge call, so nothing downstream needs them back.
			// Always remove the item we added. Suppressing WC_Cart's own recalculation-on-removal
			// (triggered via the woocommerce_cart_item_removed hook) is left off for the same
			// reason - nothing reads or persists the result, and nothing else removes a cart item
			// in this request that would need the hook restored.
			if ( $cart_item_key ) {
				remove_action( 'woocommerce_cart_item_removed', [ WC()->cart, 'calculate_totals' ], 20 );
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