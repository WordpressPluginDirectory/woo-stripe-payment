<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1;

use PaymentPlugins\Stripe\AdaptivePricing\CheckoutSessionController;

/**
 * Checkout Session Route
 *
 * Updates the Stripe CheckoutSession's line items to reflect the current cart totals.
 * The session itself is created server-side on checkout page load, not through this route
 * (see CheckoutSessionController::add_checkout_session_data()).
 *
 * @since 4.0.15
 */
class CheckoutSession extends AbstractRoute {

	/**
	 * @var CheckoutSessionController
	 */
	private $controller;

	public function __construct( CheckoutSessionController $controller ) {
		$this->controller = $controller;
	}

	/**
	 * @inheritDoc
	 */
	public function get_path() {
		return 'checkout-session';
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
					'session_id' => [
						'required' => true,
					],
				],
			]
		];
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error
	 */
	public function handle_post_request( \WP_REST_Request $request ) {
		return $this->controller->update_session_from_cart( $request->get_param( 'session_id' ) );
	}

}
