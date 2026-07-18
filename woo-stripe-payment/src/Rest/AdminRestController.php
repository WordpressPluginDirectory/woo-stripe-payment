<?php

namespace PaymentPlugins\Stripe\Rest;

/**
 * Admin REST Controller
 *
 * Manages admin-only REST API route registration. Routes registered here are
 * never exposed to frontend JavaScript — URLs are generated server-side only.
 *
 * Routes declare required capabilities via a 'permissions' array in get_routes().
 * This controller converts them to permission_callback functions at registration time.
 *
 * @since 4.0.0
 */
class AdminRestController {

	/**
	 * @var array
	 */
	private $routes = [];

	/**
	 * @param array $routes Array of route instances
	 */
	public function __construct( array $routes ) {
		$this->routes = $routes;
	}

	/**
	 * Initialize admin REST controller
	 *
	 * @return void
	 */
	public function initialize() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	/**
	 * Register admin REST routes
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		foreach ( $this->routes as $route ) {
			register_rest_route(
				$route->get_namespace(),
				$route->get_path(),
				$this->add_default_args( $route->get_routes() )
			);
		}
	}

	/**
	 * Converts declarative 'permissions' arrays to permission_callback functions.
	 * Falls back to __return_false if neither 'permissions' nor 'permission_callback' is set.
	 *
	 * @param array|array[] $routes
	 *
	 * @return array
	 */
	private function add_default_args( $routes ) {
		if ( ! \is_array( $routes ) ) {
			$routes = [ $routes ];
		}

		return array_map( function ( $arg ) {
			if ( isset( $arg['permissions'] ) ) {
				$capabilities               = $arg['permissions'];
				$arg['permission_callback'] = function () use ( $capabilities ) {
					foreach ( $capabilities as $cap ) {
						if ( \current_user_can( $cap ) ) {
							return true;
						}
					}

					return new \WP_Error(
						'permission-error',
						__( 'You do not have permissions to access this resource.', 'woo-stripe-payment' ),
						[ 'status' => 403 ]
					);
				};
				unset( $arg['permissions'] );
			} elseif ( ! isset( $arg['permission_callback'] ) ) {
				$arg['permission_callback'] = '__return_false';
			}

			return $arg;
		}, $routes );
	}

}
