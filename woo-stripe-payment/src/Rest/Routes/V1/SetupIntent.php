<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1;


use PaymentPlugins\Stripe\ContextHandler;
use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;
use PaymentPlugins\Stripe\Utilities\PaymentMethodUtils;

class SetupIntent extends AbstractRoute {

	private $payment_registry;

	public function __construct( PaymentGatewayRegistry $payment_registry ) {
		$this->payment_registry = $payment_registry;
	}

	/**
	 * @inheritDoc
	 */
	public function get_path() {
		return 'setup-intent';
	}

	/**
	 * @inheritDoc
	 */
	public function get_routes() {
		return [
			[
				'methods'  => \WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'handle_request' ],
				'args'     => [
					'payment_method' => [
						'required' => true
					],
					'context'        => [
						'required' => true
					]
				]
			]
		];
	}

	public function handle_post_request( \WP_REST_Request $request ) {
		$this->populate_post_data( $request );
		/**
		 * @var AbstractGateway $payment_method
		 */
		$payment_method = $this->payment_registry->get( $request->get_param( 'payment_method' ) );

		if ( ! $payment_method ) {
			throw new \Exception( 'Payment method not found' );
		}

		$params = [
			'usage' => 'off_session'
		];

		if ( $payment_method->id === 'stripe_upm' ) {
			/**
			 * @var \WC_Payment_Gateway_Stripe_UPM $payment_method
			 */
			$params['automatic_payment_methods']    = [ 'enabled' => true ];
			$params['payment_method_configuration'] = $payment_method->get_payment_method_configuration( wc_stripe_mode() );
		} else {
			$params['payment_method_types'] = [ $payment_method->get_payment_method_type() ];
		}

		$customer = wc_stripe_get_customer_id( get_current_user_id() );
		if ( $customer ) {
			$params['customer'] = $customer;
		}

		if ( $payment_method->is_active( 'force_3d_secure' ) ) {
			$params['payment_method_options']['card']['request_three_d_secure'] = 'any';
		}

		/**
		 * @var ContextHandler $context
		 */
		$context = wc_stripe_get_container()->get( ContextHandler::class );
		$context->set_context( $request['context'] ?? '' );

		$response = [];

		if ( $context->is_order_pay() ) {
			$order = \wc_get_order( absint( $request['order_id'] ) );

			if ( ! $order ) {
				throw new \Exception( __( 'Invalid order ID.', 'woo-stripe-payment' ) );
			}
			if ( ! $order->key_is_valid( $request->get_param( 'order_key' ) ) ) {
				throw new \Exception( __( 'Invalid order key.', 'woo-stripe-payment' ) );
			}
			// Set the order so it's available to any code that uses the ContextHandler
			$context->set_order( $order );
		}

		$ip_address                                    = \WC_Geolocation::get_ip_address();
		$user_agent                                    = wc_get_user_agent();
		$response['confirmation_args']['mandate_data'] = [
			'customer_acceptance' => [
				'type'   => 'online',
				'online' => [
					'ip_address' => $ip_address ? $ip_address : \WC_Geolocation::get_external_ip_address(),
					'user_agent' => $user_agent ? $user_agent : 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' )
				]
			]
		];

		if ( ! empty( $request['payment_method_id'] ) ) {
			$params['payment_method'] = $request['payment_method_id'];
		}

		// Add the return url in case this is a payment method that requires a redirect in order to save the payment method.
		$response['return_url'] = PaymentMethodUtils::create_return_url( $payment_method, $context->get_context() );

		/**
		 * @param array            $params
		 * @param AbstractGateway  $payment_method
		 * @param \WP_REST_Request $request
		 */
		$params = apply_filters( 'wc_stripe_create_setup_intent_params', $params, $payment_method, $request );

		$result = $payment_method->client->setupIntents->create( $params );

		if ( is_wp_error( $result ) ) {
			throw new \Exception(
				sprintf( __( 'Error creating setup intent. Reason: %s', 'woo-stripe-payment' ), $result->get_error_message() ),
			);
		}

		$response = array_merge(
			$response,
			[
				'id'            => $result->id,
				'type'          => 'setup_intent',
				'status'        => $result->status,
				'client_secret' => $result->client_secret,
			]
		);

		return $response;
	}
}