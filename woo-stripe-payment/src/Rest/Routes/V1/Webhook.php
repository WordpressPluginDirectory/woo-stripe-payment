<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1;

/**
 * Handles incoming Stripe webhook notifications.
 *
 * @since   4.0.0
 * @author  PaymentPlugins
 * @package PaymentPlugins\Stripe\Rest\Routes\V1
 */
class Webhook extends AbstractRoute {

	public function get_path(): string {
		return 'webhook';
	}

	public function get_routes(): array {
		return [
			[
				'methods'  => \WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'handle_request' ],
			],
			[
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => [ $this, 'handle_request' ],
			],
		];
	}

	public function handle_get_request( \WP_REST_Request $request ): array {
		return [
			'message' => __( 'Stripe sends webhook notifications via the http POST method. You cannot test the webhook using a browser.', 'woo-stripe-payment' ),
		];
	}

	public function handle_post_request( \WP_REST_Request $request ): array {
		$payload      = $request->get_body();
		$json_payload = json_decode( $payload, true );
		$header       = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

		if ( ! $json_payload ) {
			throw new \Exception( 'Invalid request payload.' );
		}

		$mode            = $json_payload['livemode'] == true ? 'live' : 'test';
		$webhook_id_key  = "webhook_id_{$mode}";
		$webhook_id      = \stripe_wc()->api_settings->get_option( $webhook_id_key );
		$webhook_secret  = \stripe_wc()->api_settings->get_option( 'webhook_secret_' . $mode );

		// If the webhook ID exists and doesn't match the ID from the notification, skip processing.
		// This handles Stripe accounts with multiple webhooks configured.
		if ( $webhook_id && isset( $json_payload['data']['object']['metadata']['webhook_id'] ) && $webhook_id !== $json_payload['data']['object']['metadata']['webhook_id'] ) {
			return [];
		}

		try {
			$event = \Stripe\Webhook::constructEvent(
				$payload,
				$header,
				$webhook_secret,
				\apply_filters( 'wc_stripe_webhook_signature_tolerance', 600 )
			);

			\wc_stripe_log_info( sprintf( 'Webhook notification received: Event: %s', $event->type ) );

			$type = str_replace( '.', '_', $event->type );

			\define( \WC_Stripe_Constants::WOOCOMMERCE_STRIPE_PROCESSING_WEBHOOK, true );

			\do_action( 'wc_stripe_webhook_' . $type, $event->data->object, $request, $event );

			return \apply_filters( 'wc_stripe_webhook_response', [], $event, $request );
		} catch ( \Stripe\Exception\SignatureVerificationException $e ) {
			\wc_stripe_log_error( sprintf(
				__( 'Invalid signature received. Verify that your webhook secret is correct. Error: %s', 'woo-stripe-payment' ),
				$e->getMessage()
			) );
			throw new \Exception( __( 'Invalid signature received. Verify that your webhook secret is correct.', 'woo-stripe-payment' ), 401 );
		} catch ( \Exception $e ) {
			\wc_stripe_log_error( sprintf(
				__( 'Error processing webhook. Message: %s Exception: %s', 'woo-stripe-payment' ),
				$e->getMessage(),
				get_class( $e )
			) );
			throw $e;
		}
	}

}