<?php

namespace PaymentPlugins\Stripe\Container;

/**
 * Dependency Injection Container
 *
 * Simple container implementation that supports singleton and non-singleton services.
 * Services are registered as closures that receive the container instance for dependency resolution.
 *
 * @since 4.0.0
 */
class Container {

	/**
	 * Registry of service resolvers.
	 *
	 * @var array<string, AbstractResolver>
	 */
	private $registry = [];

	/**
	 * Register a service in the container.
	 *
	 * @param string $id Service identifier (typically the class name).
	 * @param mixed  $value Either a closure that returns the service instance, or the instance itself.
	 * @param bool   $singleton Whether the service should be a singleton (default: true).
	 *
	 * @return void
	 */
	public function register( $id, $value, $singleton = true ) {
		if ( empty( $this->registry[ $id ] ) ) {
			$this->registry[ $id ] = new BaseResolver( $value, $singleton );
		}
	}

	/**
	 * Retrieve a service from the container.
	 *
	 * @param string $id Service identifier.
	 *
	 * @return mixed The resolved service instance.
	 * @throws \Exception If the service is not registered.
	 */
	public function get( $id ) {
		if ( empty( $this->registry[ $id ] ) ) {
			throw new \Exception( sprintf( 'There is no service registered for id %s', $id ) );
		}

		return $this->registry[ $id ]->get( $this );
	}

	/**
	 * Check if a service is registered.
	 *
	 * @param string $id Service identifier.
	 *
	 * @return bool True if the service is registered, false otherwise.
	 */
	public function has( $id ) {
		return ! empty( $this->registry[ $id ] );
	}
}