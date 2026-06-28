<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1;

use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;

class OrderPay extends AbstractRoute {

	/**
	 * @inheritDoc
	 */
	public function get_path() {
		return 'order/pay';
	}

	/**
	 * @inheritDoc
	 */
	public function get_routes() {
		return [
			[
				'methods'  => \WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'handle_request' ],
			]
		];
	}

	public function handle_post_request( \WP_REST_Request $request ) {
		global $wp;
		/**
		 * Only set when the order pay is being processed via Ajax.
		 */
		wc_maybe_define_constant( \WC_Stripe_Constants::PROCESSING_ORDER_PAY, true );

		$this->populate_post_data( $request );
		$order_id = absint( $request->get_param( 'order_id' ) );
		$order    = wc_get_order( $order_id );
		if ( ! $order ) {
			throw new \Exception( __( 'Invalid order ID.', 'woo-stripe-payment' ) );
		}
		if ( ! $order->key_is_valid( $request->get_param( 'order_key' ) ) ) {
			throw new \Exception( __( 'Invalid order key.', 'woo-stripe-payment' ) );
		}
		/**
		 * @var AbstractGateway $payment_method
		 */
		$payment_method = WC()->payment_gateways()->payment_gateways()[ $request->get_param( 'payment_method' ) ] ?? null;
		if ( ! $payment_method || ! $payment_method instanceof AbstractGateway ) {
			throw new \Exception( __( 'Invalid payment method.', 'woo-stripe-payment' ) );
		}

		$wp->set_query_var( 'order-pay', $order_id );
		$order->set_payment_method( $payment_method->id );
		$payment_method->payment_controller->set_update_payment_intent( true );

		$result = $payment_method->payment_controller->process_payment( $order );

		if ( is_wp_error( $result ) ) {
			throw new \Exception( $result->get_error_message() );
		}

		$response = (object) [
			'complete' => false,
			'redirect' => ''
		];

		if ( $result->complete_payment ) {
			$response->complete = true;
		} else {
			$response->redirect = $result->redirect;
		}

		return $response;
	}
}