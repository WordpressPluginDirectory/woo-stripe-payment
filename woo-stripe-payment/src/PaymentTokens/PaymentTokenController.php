<?php

namespace PaymentPlugins\Stripe\PaymentTokens;

use PaymentPlugins\Stripe\Client\StripeClient;
use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;

class PaymentTokenController {

	private $client;

	private $payment_gateway_registry;

	public function __construct( StripeClient $client, PaymentGatewayRegistry $payment_gateway_registry ) {
		$this->client                   = $client;
		$this->payment_gateway_registry = $payment_gateway_registry;
	}

	public function initialize() {
		add_action( 'woocommerce_payment_token_deleted', function ( ...$args ) {
			$this->detach_payment_method( ...$args );
		}, 10, 2 );
		add_filter( 'woocommerce_payment_methods_list_item', [ $this, 'get_payment_methods_list_item' ], 10, 2 );
		add_filter( 'woocommerce_get_customer_payment_tokens', [ $this, 'get_customer_payment_tokens' ], 10, 3 );
	}

	/**
	 * @param $item
	 * @param $payment_token
	 *
	 * @return void
	 */
	public function get_payment_methods_list_item( $item, $payment_token ) {
		if ( $payment_token instanceof \WC_Payment_Token_Stripe ) {
			$gateway = $this->payment_gateway_registry->get( $payment_token->get_gateway_id() );
			if ( $gateway && $gateway->token_type === $payment_token->get_type() ) {
				if ( method_exists( $payment_token, 'get_payment_token_item_format' ) ) {
					$item = $payment_token->get_payment_token_item_format( $item );
				}
			}
		}

		return $item;
	}

	/**
	 * @param int                      $id
	 * @param \WC_Payment_Token_Stripe $token
	 *
	 * @return void
	 */
	private function detach_payment_method( $id, $token ) {
		if ( ! is_account_page() ) {
			return;
		}
		if ( ! $token instanceof \WC_Payment_Token_Stripe ) {
			return;
		}
		$gateway = $this->payment_gateway_registry->get( $token->get_gateway_id() );
		if ( ! $gateway || $gateway->token_type !== $token->get_type() ) {
			return;
		}

		$result = $this->client->mode( $token->get_environment() )->paymentMethods->detach( $token->get_token() );

		if ( is_wp_error( $result ) ) {
			/**
			 * @var \WP_Error $result
			 */
			wc_stripe_log_info(
				sprintf(
					'Error deleting Stripe payment token. ID: %s. Reason: %s',
					$token->get_id(),
					$result->get_error_message()
				)
			);
		}
	}

	public function get_customer_payment_tokens( $tokens, $customer_id, $gateway_id ) {
		$mode = wc_stripe_mode();
		foreach ( $tokens as $idx => $token ) {
			if ( $token instanceof \WC_Payment_Token_Stripe ) {
				if ( $token->get_environment() != $mode ) {
					unset( $tokens[ $idx ] );
				}
				$gateway = $this->payment_gateway_registry->get( $token->get_gateway_id() );
				if ( $gateway && $gateway instanceof AbstractGateway ) {
					// Make sure the token has the current format
					$token->set_format( $gateway->get_option( 'method_format' ) );
				}
			}
		}

		return $tokens;
	}
}