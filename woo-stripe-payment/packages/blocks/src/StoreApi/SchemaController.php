<?php

namespace PaymentPlugins\Blocks\Stripe\StoreApi;

use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use PaymentPlugins\Blocks\Stripe\Payments\PaymentsApi;
use PaymentPlugins\Stripe\Transformers\DataTransformer;

class SchemaController {

	private $extend_schema;

	private $payments_api;

	public function __construct( ExtendSchema $extend_schema, PaymentsApi $payments_api ) {
		$this->extend_schema = $extend_schema;
		$this->payments_api  = $payments_api;
		add_action( 'init', [ $this, 'initialize' ], 20 );
	}

	public function initialize() {
		$this->register_cart_data();
		$this->register_payment_gateway_data();

		/**
		 * @todo - temporary use of filter. Woo checkout block has a bug where if an express payment method was used
		 * on the previous order, that causes the express checkout section to be disabled because Woo considers the express payment
		 * option active since the last used payment method is the default payment method.
		 */
		add_filter( 'woocommerce_hydration_request_after_callbacks', function ( $response, $handler, $request ) {
			/**
			 * @var \WP_REST_Request $request
			 * @var \WP_REST_Response $response
			 */
			if ( $request->get_route() === '/wc/store/v1/checkout' ) {
				$data = $response->get_data();
				if ( isset( $data['order_id'], $data['status'], $data['payment_method'] ) ) {
					if ( $data['status'] === 'checkout-draft' ) {
						if ( \in_array( $data['payment_method'], [
							'stripe_googlepay',
							'stripe_applepay',
							'stripe_payment_request',
							'stripe_link_checkout'
						] ) ) {
							$data['payment_method'] = '';
							$response->set_data( $data );
						}
					}
				}
			};

			return $response;
		}, 10, 3 );
	}

	private function register_payment_gateway_data() {
		foreach ( $this->payments_api->get_payment_methods() as $payment_method ) {
			if ( $payment_method->is_active() ) {
				$data = $payment_method->get_endpoint_data();
				if ( ! empty( $data ) ) {
					if ( $data instanceof EndpointData ) {
						$data = $data->to_array();
					}
					$this->extend_schema->register_endpoint_data( $data );
				}
			}
		}

	}

	private function register_cart_data() {
		$data = new EndpointData();
		$data->set_namespace( 'wc_stripe' );
		$data->set_endpoint( CartSchema::IDENTIFIER );
		$data->set_schema_type( ARRAY_A );
		$data->set_data_callback( function () {
			return [
				'cart' => ( new DataTransformer() )->transform_cart( WC()->cart )
			];
		} );
		$this->extend_schema->register_endpoint_data( $data->to_array() );
	}

}