<?php

defined( 'ABSPATH' ) || exit;

use PaymentPlugins\Vendor\Stripe\Webhook;
use PaymentPlugins\Vendor\Stripe\WebhookEndpoint;
use PaymentPlugins\Vendor\Stripe\ApiOperations\Delete;

/**
 * This update reconfigured the webhooks for each environment.
 */
$environments = array( 'live', 'test' );
$api_settings = stripe_wc()->api_settings;
$url          = get_rest_url( null, '/wc-stripe/v1/webhook' );
foreach ( $environments as $env ) {
	$gateway = new WC_Stripe_Gateway();
	$webhooks = $gateway->webhooks( $env );
	if ( ! is_wp_error( $webhooks ) ) {
		// first delete the webhook if it matches the wp-json webhook. Then re-create it.
		foreach ( $webhooks->data as $webhook ) {
			/**
			 *
			 * @var \PaymentPlugins\Vendor\Stripe\WebhookEndpoint $webhook
			 */
			if ( $webhook['url'] == $url ) {
				$webhook->delete();
			}
		}
	}
	// now that endpoint is deleted, re-create it and store details.
	$webhook = $gateway->create_webhook( $url, array(
		'charge.failed',
		'charge.succeeded',
		'source.chargeable'
	), $env );
	if ( ! is_wp_error( $webhook ) ) {
		$api_settings->update_option( "webhook_url_{$env}", $webhook['url'] );
		$api_settings->update_option( "webhook_secret_{$env}", $webhook['secret'] );
		$api_settings->update_option( "webhook_id_{$env}", $webhook['id'] );
	}
}