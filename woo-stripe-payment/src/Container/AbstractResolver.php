<?php

namespace PaymentPlugins\Stripe\Container;

/**
 * Abstract Resolver
 *
 * Base class for service resolvers. Handles the callback storage and resolution logic.
 *
 * @since 4.0.0
 */
abstract class AbstractResolver {

	/**
	 * The callback or value to resolve.
	 *
	 * @var mixed
	 */
	private $callback;

	/**
	 * Whether this resolver should return a singleton instance.
	 *
	 * @var bool
	 */
	protected $singleton;

	/**
	 * Constructor.
	 *
	 * @param mixed $value Either a closure or the value itself.
	 * @param bool  $singleton Whether to cache the resolved value.
	 */
	public function __construct( $value, $singleton ) {
		$this->callback  = $value;
		$this->singleton = $singleton;
	}

	/**
	 * Resolve the callback to a value.
	 *
	 * If the callback is callable, it will be invoked with the container as an argument.
	 * Otherwise, the callback itself is returned.
	 *
	 * If the resolved value is an object with an initialize() method, it will be called
	 * automatically. This allows services to set up hooks and filters upon instantiation.
	 *
	 * @param Container $container The container instance.
	 *
	 * @return mixed The resolved value.
	 */
	protected function resolve( $container ) {
		$callback = $this->callback;
		$resolved = \is_callable( $callback ) ? $callback( $container ) : $this->callback;

		// Auto-call initialize() method if it exists
		/*if ( \is_object( $resolved ) && \method_exists( $resolved, 'initialize' ) ) {
			$resolved->initialize();
		}*/

		return $resolved;
	}

	/**
	 * Get the resolved value.
	 *
	 * @param Container $container The container instance.
	 *
	 * @return mixed The resolved value.
	 */
	abstract public function get( $container );
}