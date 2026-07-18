<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1;

/**
 * Abstract base class for all REST API routes
 *
 * @since 4.0.0
 */
abstract class AbstractRoute {

	/**
	 * Get the route namespace
	 *
	 * @return string
	 */
	public function get_namespace() {
		return 'wc-stripe/v1';
	}

	/**
	 * Get the route path
	 *
	 * @return string
	 */
	public abstract function get_path();

	/**
	 * Get route configuration
	 *
	 * @return array|array[] Route args for register_rest_route
	 */
	public abstract function get_routes();

	/**
	 * Get full path including namespace
	 *
	 * @return string
	 */
	public function get_full_path() {
		return '/' . trim( $this->get_namespace(), '/' ) . '/' . trim( $this->get_path(), '/' );
	}

	/**
	 * Handle REST request
	 *
	 * Routes to appropriate method handler based on HTTP method
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_request( \WP_REST_Request $request ) {
		$method = strtolower( $request->get_method() );
		$func   = "handle_{$method}_request";

		try {
			$result = $this->{$func}( $request );

			if ( \is_wp_error( $result ) ) {
				return $this->get_error_response( $result );
			}

			return rest_ensure_response( apply_filters( "wc_stripe_{$method}_{$this->get_path()}", $result ) );
		} catch ( \Exception $e ) {
			return $this->get_error_response( $e );
		}
	}

	/**
	 * Convert exceptions/WP_Error to error responses
	 *
	 * @param \Exception|\WP_Error $error
	 *
	 * @return \WP_Error
	 */
	public function get_error_response( $error ) {
		if ( $error instanceof \Exception ) {
			return new \WP_Error(
				'rest-error',
				$error->getMessage(),
				[ 'status' => $error->getCode() ?: 400 ]
			);
		} elseif ( is_wp_error( $error ) ) {
			return $error;
		}

		return new \WP_Error( 'unknown-error', __( 'An unknown error occurred', 'woo-stripe-payment' ), [ 'status' => 500 ] );
	}

	/**
	 * Handle GET request
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function handle_get_request( \WP_REST_Request $request ) {
		throw new \Exception( __( 'Method not implemented', 'woo-stripe-payment' ), 405 );
	}

	/**
	 * Handle POST request
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function handle_post_request( \WP_REST_Request $request ) {
		throw new \Exception( __( 'Method not implemented', 'woo-stripe-payment' ), 405 );
	}

	/**
	 * Handle PUT request
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function handle_put_request( \WP_REST_Request $request ) {
		throw new \Exception( __( 'Method not implemented', 'woo-stripe-payment' ), 405 );
	}

	/**
	 * Handle DELETE request
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function handle_delete_request( \WP_REST_Request $request ) {
		throw new \Exception( __( 'Method not implemented', 'woo-stripe-payment' ), 405 );
	}

	/**
	 * Get WooCommerce notice
	 *
	 * @param string $notice_type Notice type (error, success, notice)
	 * @param string $default Default message if no notice found
	 *
	 * @return string
	 */
	protected function get_wc_notice( $notice_type = '', $default = '' ) {
		$notices = \wc_get_notices( $notice_type );

		if ( \is_array( $notices ) && \count( $notices ) > 0 ) {
			$notice = current( $notices );

			return $notice['notice'] ?? $default;
		}

		return $default;
	}

	/**
	 * Populate $_POST and $_REQUEST with request data
	 *
	 * Some 3rd party plugins depend on $_POST being populated
	 *
	 * @param \WP_REST_Request $request
	 */
	protected function populate_post_data( \WP_REST_Request $request ) {
		$_POST    = array_merge( $_POST, $request->get_json_params() );
		$_REQUEST = array_merge( $_REQUEST, $request->get_json_params() );
	}

}