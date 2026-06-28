<?php

namespace PaymentPlugins\Stripe\Container;

/**
 * Base Resolver
 *
 * Handles singleton vs non-singleton resolution of services.
 *
 * @since 4.0.0
 */
class BaseResolver extends AbstractResolver {

	/**
	 * Cached resolved value (for singletons).
	 *
	 * @var mixed
	 */
	private $resolved_value;

	/**
	 * Get the resolved value.
	 *
	 * If singleton is true, the value is cached and returned on subsequent calls.
	 * Otherwise, a new instance is created each time.
	 *
	 * @param Container $container The container instance.
	 *
	 * @return mixed The resolved value.
	 */
	public function get( $container ) {
		if ( ! $this->resolved_value || ! $this->singleton ) {
			$this->resolved_value = $this->resolve( $container );
		}

		return $this->resolved_value;
	}
}