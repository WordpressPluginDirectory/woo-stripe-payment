<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1;

use PaymentPlugins\Stripe\Transformers\DataTransformer;

/**
 * Cart Refresh Route
 *
 * Returns current cart data for initializing payment elements
 *
 * @since 4.0.0
 */
class CartRefresh extends AbstractCart {

	/**
	 * @var DataTransformer
	 */
	private $transformer;

	public function __construct() {
		$this->transformer = new DataTransformer();
	}

	/**
	 * Get the route path
	 *
	 * @return string
	 */
	public function get_path() {
		return 'cart/refresh';
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
					'page' => [
						'required' => false,
						'type'     => 'string',
						'default'  => 'cart'
					]
				]
			]
		];
	}

	/**
	 * Handle POST request - Get cart data
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array
	 */
	public function handle_post_request( \WP_REST_Request $request ) {
		return [
			'cart' => $this->transformer->transform_cart( WC()->cart )
		];
	}

}