<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1;

use PaymentPlugins\Stripe\Transformers\DataTransformer;

/**
 * Cart Item Route
 *
 * Handles adding products to cart and removing items
 * Used by express checkout methods on product pages
 *
 * @since 4.0.0
 */
class CartItem extends AbstractCart {

	private DataTransformer $transformer;

	public function __construct() {
		$this->transformer = new DataTransformer();
	}

	/**
	 * Get the route path
	 *
	 * @return string
	 */
	public function get_path() {
		return 'cart/item';
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
					],
					'product_id'     => [
						'required' => true
					],
					'qty'            => [
						'required' => false,
						'type'     => 'integer'
					],
					'variation_id'   => [
						'required' => false,
					],
					'variation'      => [
						'required' => false
					]
				]
			],
			[
				'methods'  => \WP_REST_Server::DELETABLE,
				'callback' => [ $this, 'handle_request' ],
				'args'     => [
					'key' => [
						'required' => true,
						'type'     => 'string'
					]
				]
			]
		];
	}

	/**
	 * Handle POST request - Add item to cart
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error
	 */
	public function handle_post_request( \WP_REST_Request $request ) {
		wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );

		$this->populate_post_data( $request );

		$product_id   = $request->get_param( 'product_id' );
		$qty          = $request->get_param( 'qty' );
		$variation_id = $request->get_param( 'variation_id' );
		$variation    = $request->get_param( 'variation' ) ?: [];

		try {
			// Remove existing cart item if it exists (ensures qty is accurate)
			$cart_id = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation );
			WC()->cart->remove_cart_item( $cart_id );

			// Add product to cart
			$cart_item_key = WC()->cart->add_to_cart( $product_id, $qty, $variation_id, $variation );

			if ( $cart_item_key === false ) {
				throw new \Exception(
					$this->get_wc_notice( 'error', __( 'Error adding product to cart.', 'woo-stripe-payment' ) )
				);
			}

			// Calculate totals
			$this->calculate_totals();

			return [
				'cart_item_key' => $cart_item_key,
				'cart'          => $this->transformer->transform_cart( WC()->cart )
			];

		} catch ( \Exception $e ) {
			return new \WP_Error(
				'add-to-cart-error',
				$e->getMessage(),
				[ 'status' => 200 ]
			);
		}
	}

	/**
	 * Handle DELETE request - Remove item from cart
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error
	 */
	public function handle_delete_request( \WP_REST_Request $request ) {
		$cart_item_key = $request->get_param( 'key' );

		try {
			$result = WC()->cart->remove_cart_item( $cart_item_key );

			if ( ! $result ) {
				throw new \Exception( __( 'Failed to remove cart item.', 'woo-stripe-payment' ) );
			}

			// Recalculate totals
			$this->calculate_totals();

			return [
				'cart_item_key' => $cart_item_key,
				'cart'          => $this->transformer->transform_cart( WC()->cart )
			];

		} catch ( \Exception $e ) {
			return new \WP_Error(
				'remove-cart-item-error',
				$e->getMessage(),
				[ 'status' => 200 ]
			);
		}
	}

}