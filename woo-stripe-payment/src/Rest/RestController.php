<?php

namespace PaymentPlugins\Stripe\Rest;

use PaymentPlugins\Stripe\Assets\AssetDataApi;

/**
 * REST Controller
 *
 * Manages REST API route registration and provides route URLs to JavaScript
 *
 * @since 4.0.0
 */
class RestController {

	/**
	 * @var array
	 */
	private $routes = [];

	/**
	 * @var array
	 */
	private $excluded = [];

	/**
	 * @param array $routes Array of route instances
	 * @param array $excluded Array of route class names to exclude from script data
	 */
	public function __construct( array $routes, array $excluded = [] ) {
		$this->routes   = $routes;
		$this->excluded = $excluded;
	}

	/**
	 * Initialize REST controller
	 *
	 * @return void
	 */
	public function initialize() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
		add_filter( 'wc_stripe_add_script_data', [ $this, 'add_route_data' ] );
		add_filter( 'wc_stripe_blocks_general_data', [ $this, 'add_block_route_data' ] );
	}

	/**
	 * Register REST routes
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
	 * Add route URLs to localized script data
	 *
	 * Makes REST routes available to JavaScript via wcStripeSettings.restRoutes
	 *
	 * @param AssetDataApi $asset_data Localized script data
	 *
	 * @return array
	 */
	public function add_route_data( $asset_data ) {
		$data = [];

		foreach ( $this->routes as $key => $route ) {
			if ( in_array( get_class( $route ), $this->excluded, true ) ) {
				continue;
			}
			$data[ $key ] = [
				'namespace' => $route->get_namespace(),
				'path'      => $route->get_full_path(),
				'url'       => \WC_Stripe_Rest_API::get_endpoint( $route->get_full_path() )
			];
		}
		$asset_data->add( 'restRoutes', $data );
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function add_block_route_data( $data ) {
		$data['routes'] = [];
		foreach ( $this->routes as $key => $route ) {
			if ( in_array( get_class( $route ), $this->excluded, true ) ) {
				continue;
			}
			$data['routes'][ $key ] = \WC_Stripe_Rest_API::get_endpoint( $route->get_full_path() );
		}

		return $data;
	}

	/**
	 * Decorates REST API routes with default args
	 *
	 * Adds default permission_callback to allow public access
	 * (individual routes should implement their own auth if needed)
	 *
	 * @param array|array[] $routes Route configuration
	 *
	 * @return array
	 */
	private function add_default_args( $routes ) {
		if ( ! \is_array( $routes ) ) {
			$routes = [ $routes ];
		}

		return array_map( function ( $arg ) {
			return array_merge( [
				'permission_callback' => '__return_true'
			], $arg );
		}, $routes );
	}

}