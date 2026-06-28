<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1\Admin;

use PaymentPlugins\Stripe\Client\StripeClient;
use WP_REST_Server;

/**
 * @since 4.0.0
 */
class OrderActions extends AbstractAdminRoute {

	private $client;

	public function __construct( StripeClient $client ) {
		$this->client = $client;
	}

	public function get_path() {
		return '/order-actions/(?P<task>[a-z\-]+)';
	}

	public function get_routes() {
		return [
			[
				'methods'     => WP_REST_Server::CREATABLE,
				'callback'    => [ $this, 'handle_request' ],
				'permissions' => [ 'edit_shop_orders' ],
				'args'        => [
					'task' => [
						'required' => true,
						'type'     => 'string',
					]
				]
			],
			[
				'methods'     => WP_REST_Server::READABLE,
				'callback'    => [ $this, 'handle_request' ],
				'permissions' => [ 'edit_shop_orders' ],
				'args'        => [
					'task' => [
						'required' => true,
						'type'     => 'string',
					]
				]
			]
		];
	}

	public function handle_post_request( \WP_REST_Request $request ) {
		$action = $request->get_param( 'task' );
		switch ( $action ) {
			case 'capture':
				return $this->capture( $request );
			case 'void':
				return $this->void( $request );
			case 'pay':
				return $this->process_payment( $request );
		}

		throw new \Exception( sprintf( __( 'Unknown task: %s', 'woo-stripe-payment' ), $action ), 400 );
	}

	public function handle_get_request( \WP_REST_Request $request ) {
		$action = $request->get_param( 'task' );
		switch ( $action ) {
			case 'customer-payment-methods':
				return $this->customer_payment_methods( $request );
			case 'charge-view':
				return $this->charge_view( $request );
		}

		throw new \Exception( sprintf( __( 'Unknown task: %s', 'woo-stripe-payment' ), $action ), 400 );
	}

	private function capture( \WP_REST_Request $request ) {
		$order_id = absint( $request->get_param( 'order_id' ) );
		$order    = wc_get_order( $order_id );
		$amount   = $request->get_param( 'amount' );

		if ( ! is_numeric( $amount ) ) {
			throw new \Exception( __( 'Invalid amount entered.', 'woo-stripe-payment' ), 400 );
		}

		/** @var \WC_Payment_Gateway_Stripe $gateway */
		$gateway = WC()->payment_gateways()->payment_gateways()[ $order->get_payment_method() ];
		$result  = $gateway->capture_charge( $amount, $order );

		if ( \is_wp_error( $result ) ) {
			throw new \Exception( $result->get_error_message() );
		}

		return [];
	}

	private function void( \WP_REST_Request $request ) {
		$order          = wc_get_order( absint( $request->get_param( 'order_id' ) ) );
		$payment_method = WC()->payment_gateways()->payment_gateways()[ $order->get_payment_method() ] ?? null;

		if ( $payment_method ) {
			remove_action( 'woocommerce_order_status_cancelled', 'wc_stripe_order_cancelled' );
			$result = $payment_method->void_charge( $order );
			if ( ! \is_wp_error( $result ) ) {
				$order->update_status( 'cancelled' );
			}
		}

		return [];
	}

	private function process_payment( \WP_REST_Request $request ) {
		$order_id     = absint( $request->get_param( 'order_id' ) );
		$payment_type = $request->get_param( 'payment_type' );
		$order        = wc_get_order( $order_id );
		$use_token    = $payment_type === 'token';

		if ( $order->get_total() == 0 ) {
			if ( ! wcs_stripe_active() || ! wcs_order_contains_subscription( $order ) ) {
				throw new \Exception( __( 'Order total must be greater than zero.', 'woo-stripe-payment' ) );
			}
		}

		if ( $order->get_customer_id() != $request->get_param( 'customer_id' ) ) {
			$order->set_customer_id( $request->get_param( 'customer_id' ) );
		}

		if ( $order->get_transaction_id() ) {
			$charge = $this->client->mode( $order )->charges->retrieve( $order->get_transaction_id() );
			if ( $charge->captured ) {
				throw new \Exception( sprintf(
					__( 'This order has already been processed. Transaction ID: %1$s. Payment method: %2$s', 'woo-stripe-payment' ),
					$order->get_transaction_id(),
					$order->get_payment_method_title()
				) );
			}
			$order->set_transaction_id( '' );
		}

		$order->delete_meta_data( \WC_Stripe_Constants::PAYMENT_INTENT_ID );

		if ( isset( $request['payment_intent'] ) ) {
			$order->update_meta_data( \WC_Stripe_Constants::PAYMENT_INTENT_ID, $request['payment_intent'] );
		}

		if ( ! $use_token ) {
			$payment_method_id = 'stripe_cc';
		} else {
			$token_id = intval( $request->get_param( 'payment_token_id' ) );
			$token    = \WC_Payment_Tokens::get( $token_id );
			if ( $token->get_user_id() !== $order->get_customer_id() ) {
				throw new \Exception( __( 'Order customer Id and payment method customer Id do not match.', 'woo-stripe-payment' ) );
			}
			$payment_method_id = $token->get_gateway_id();
		}

		/** @var \PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway $gateway */
		$gateway                          = WC()->payment_gateways()->payment_gateways()[ $payment_method_id ];
		$gateway->settings['charge_type'] = $request->get_param( 'wc_stripe_charge_type' );
		$order->set_payment_method( $gateway->id );
		$order->save();

		if ( ! $use_token ) {
			$gateway->set_payment_method_id( $request->get_param( 'payment_nonce' ) );
		} else {
			$gateway->set_payment_method_id( $token->get_token() );
		}

		add_filter( 'wc_stripe_payment_intent_args', function ( $args ) {
			if ( isset( $args['setup_future_usage'] ) && $args['setup_future_usage'] === 'off_session' ) {
				$args['off_session'] = false;
			}

			return $args;
		} );

		$result = $gateway->process_payment( $order_id );

		if ( isset( $result['result'] ) && $result['result'] === 'success' ) {
			return $result;
		}

		$order = wc_get_order( $order_id );
		$order->update_status( 'pending' );

		throw new \Exception( $this->get_wc_notice( 'error', __( 'Payment failed.', 'woo-stripe-payment' ) ) );
	}

	private function customer_payment_methods( \WP_REST_Request $request ) {
		$customer_id = $request->get_param( 'customer_id' );
		$tokens      = [];

		foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
			if ( $gateway instanceof \WC_Payment_Gateway_Stripe ) {
				$tokens = array_merge( $tokens, \WC_Payment_Tokens::get_customer_tokens( $customer_id, $gateway->id ) );
			}
		}

		return [
			'payment_methods' => array_map( fn( $token ) => $token->to_json(), $tokens )
		];
	}

	private function charge_view( \WP_REST_Request $request ) {
		$order          = wc_get_order( absint( $request->get_param( 'order_id' ) ) );
		$payment_method = WC()->payment_gateways()->payment_gateways()[ $order->get_payment_method() ];

		$charge = $this->client->mode( $order )->charges->retrieve( $order->get_transaction_id() );

		if ( \is_wp_error( $charge ) ) {
			/**
			 * @var \WP_Error $charge
			 */
			throw new \Exception( $charge->get_error_message() );
		}

		$order->update_meta_data( \WC_Stripe_Constants::CHARGE_STATUS, $charge->status );
		$order->save();

		ob_start();
		include stripe_wc()->plugin_path() . 'includes/admin/meta-boxes/views/html-charge-data-subview.php';
		$html = ob_get_clean();

		return [
			'data' => [
				'order_id'     => $order->get_id(),
				'order_number' => $order->get_order_number(),
				'order_total'  => $order->get_total(),
				'charge'       => $charge->jsonSerialize(),
				'html'         => $html,
			]
		];
	}

}
