<?php

namespace PaymentPlugins\Stripe\Client;

/**
 * Used as a wrapper for API requests to Stripe.
 * Allows method chaining so things like mode can
 * be set intuitively.
 *
 * @since   4.0.0
 * @author  PaymentPlugins
 * @package PaymentPlugins\Stripe\Client
 */
class ClientOperation {

	/**
	 * @var \PaymentPlugins\Vendor\Stripe\StripeClient
	 */
	private $client;

	/**
	 * @var string
	 */
	private $property;

	/**
	 * @var \PaymentPlugins\Vendor\Stripe\Service\AbstractService
	 */
	private $service;

	/**
	 * @var string
	 */
	private $secret_key;

	/**
	 * @var string
	 */
	private $mode;

	/**
	 * @var array
	 */
	private $messages = [];

	/**
	 * @param \PaymentPlugins\Vendor\Stripe\StripeClient $client
	 * @param string                                     $property
	 * @param string                                     $secret_key
	 * @param string                                     $mode
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( \PaymentPlugins\Vendor\Stripe\StripeClient $client, string $property, ?string $secret_key = '', ?string $mode = '' ) {
		$this->client     = $client;
		$this->property   = $property;
		$this->secret_key = $secret_key ?? '';
		$this->mode       = $mode ?? '';

		$service = $this->client->__get( $property );

		if ( ! $service ) {
			throw new \InvalidArgumentException( sprintf( 'Property %s is not a valid entry', $property ) );
		}

		$this->service = $service;
	}

	public function has_property( $key ) {
		return null !== $this->client->__get( $key );
	}

	/**
	 * Wrapper for Stripe API operations.
	 * This way, all exceptions can be caught gracefully.
	 *
	 * @param string $method
	 * @param array  $args
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __call( $method, $args ) {
		if ( ! method_exists( $this->service, $method ) ) {
			throw new \InvalidArgumentException( sprintf( 'Method %s does not exist for class %s.', $method, get_class( $this->service ) ) );
		}
		$args = $this->parse_args( $args, $method );
		try {
			/**
			 * Filters arguments before they are sent to the service for an API request.
			 *
			 * @param array  $args The array of arguments that will be passed to the service method.
			 * @param string $property The name of the service being called.
			 * @param string $method The method of the service. Ex: create, delete, retrieve
			 *
			 * @since 4.0.0
			 */
			$args = \apply_filters( 'wc_stripe_api_request_args', $args, $this->property, $method );

			return $this->service->{$method}( ...$this->prepare_request_args( $args, $method ) );
		} catch ( \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException $e ) {
			return $this->get_wp_error( $e, $this->property . '-error' );
		} catch ( \PaymentPlugins\Vendor\Stripe\Exception\UnexpectedValueException $e ) {
			return new \WP_Error( 'stripe-error', $e->getMessage(), $e );
		} catch ( \PaymentPlugins\Vendor\Stripe\Exception\InvalidArgumentException $e ) {
			return new \WP_Error( 'stripe-error', $e->getMessage(), $e );
		}
	}

	/**
	 * @param string $mode
	 *
	 * @return $this
	 */
	public function mode( string $mode ): self {
		$this->mode       = $mode;
		$this->secret_key = '';

		return $this;
	}

	/**
	 * @param \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException $e
	 * @param string                                                    $code
	 *
	 * @return \WP_Error
	 * @since 4.0.0
	 */
	public function get_wp_error( \PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException $e, string $code = 'stripe-error' ): \WP_Error {
		$err = ( $json_body = $e->getJsonBody() ) ? $json_body['error'] : '';

		return \apply_filters( 'wc_stripe_api_get_wp_error', new \WP_Error( $code, $this->get_error_message( $err ), $err ), $e, $code );
	}

	/**
	 * @param array  $args
	 * @param string $method
	 *
	 * @return array
	 */
	private function parse_args( array $args, string $method ): array {
		$reflection_method = new \ReflectionMethod( get_class( $this->service ), $method );
		$num_args          = $reflection_method->getNumberOfParameters();

		foreach ( $reflection_method->getParameters() as $parameter ) {
			if ( ! isset( $args[ $parameter->getPosition() ] ) && $parameter->isOptional() ) {
				$args[ $parameter->getPosition() ] = $parameter->getDefaultValue();
			}
		}

		$args[ $num_args - 1 ] = \wp_parse_args( $args[ $num_args - 1 ], $this->get_api_options() );

		return $args;
	}

	/**
	 * Prevents any unwanted arguments from being added to a Stripe request.
	 *
	 * @param array  $args
	 * @param string $method
	 *
	 * @return array
	 * @since 4.0.0
	 */
	private function prepare_request_args( array $args, string $method ): array {
		$method_idx = [
			'create'   => 0,
			'update'   => 1,
			'retrieve' => 1,
			'confirm'  => 1,
			'capture'  => 1,
			'cancel'   => 1,
		];
		$idx        = $method_idx[ $method ] ?? null;

		switch ( $this->property ) {
			case 'paymentIntents':
				if ( $idx !== null ) {
					$params       = $args[ $idx ] ?? [];
					$params       = is_array( $params ) ? $params : [];
					$params       = $this->add_expanded_properties( $params, [
						'latest_charge',
						'latest_charge.refunds',
						'payment_method'
					] );
					$args[ $idx ] = $this->sanitize_intent_params( $params, $method );
				}
				break;
			case 'setupIntents':
				if ( $idx !== null ) {
					$params       = $args[ $idx ] ?? [];
					$params       = is_array( $params ) ? $params : [];
					$params       = $this->add_expanded_properties( $params, [ 'payment_method' ] );
					$args[ $idx ] = $this->sanitize_intent_params( $params, $method );
				}
				break;
		}

		return $args;
	}

	/**
	 * @return array
	 */
	private function get_api_options(): array {
		$args = [ 'api_key' => $this->secret_key ?: \wc_stripe_get_secret_key( $this->mode ) ];

		return \apply_filters( 'wc_stripe_api_options', $args );
	}

	/**
	 * @param array $params
	 * @param array $properties
	 *
	 * @return array
	 */
	private function add_expanded_properties( array $params, array $properties ): array {
		foreach ( $properties as $property ) {
			if ( ! in_array( $property, $params['expand'] ?? [], true ) ) {
				$params['expand'][] = $property;
			}
		}

		return $params;
	}

	/**
	 * @param array  $params
	 * @param string $method
	 *
	 * @return array
	 */
	private function sanitize_intent_params( array $params, string $method ): array {
		if ( isset( $params['payment_method_configuration'] ) ) {
			unset( $params['payment_method_types'], $params['confirmation_method'] );
			if ( $method === 'create' ) {
				$params['automatic_payment_methods'] = [ 'enabled' => true ];
			}
		}

		return $params;
	}

	/**
	 * @param mixed $err
	 *
	 * @return string
	 */
	private function get_error_message( $err ): string {
		$message = '';
		if ( is_a( $err, '\PaymentPlugins\Vendor\Stripe\Exception\ApiErrorException' ) ) {
			$err = $err->getError();
		}
		if ( is_array( $err ) || $err instanceof \PaymentPlugins\Vendor\Stripe\ErrorObject ) {
			$this->messages = $this->messages ?: \wc_stripe_get_error_messages();
			$keys           = [];
			if ( isset( $err['code'] ) ) {
				$keys[] = $err['code'];
				if ( $err['code'] === 'card_declined' && isset( $err['decline_code'] ) ) {
					$keys[] = $err['decline_code'];
				}
			}
			while ( ! empty( $keys ) ) {
				$key = array_pop( $keys );
				if ( isset( $this->messages[ $key ] ) ) {
					$message = $this->messages[ $key ];
					break;
				}
			}
			if ( empty( $message ) && isset( $err['message'] ) ) {
				$message = $err['message'];
			}
		}
		if ( is_string( $err ) ) {
			$message = $err;
		}

		/**
		 * @param string $message
		 * @param mixed  $err
		 *
		 * @since 4.0.0
		 */
		return \apply_filters( 'wc_stripe_api_request_error_message', $message, $err );
	}

}