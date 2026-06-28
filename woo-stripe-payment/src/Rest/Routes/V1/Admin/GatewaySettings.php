<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1\Admin;

use PaymentPlugins\Stripe\Client\StripeClient;
use WP_REST_Server;

/**
 * @since 4.0.0
 */
class GatewaySettings extends AbstractAdminRoute {

	private $client;

	public function __construct( StripeClient $client ) {
		$this->client = $client;
	}

	public function get_path() {
		return '/gateway-settings/(?P<task>[a-z\-]+)';
	}

	public function get_routes() {
		return [
			[
				'methods'     => WP_REST_Server::CREATABLE,
				'callback'    => [ $this, 'handle_request' ],
				'permissions' => [ 'manage_woocommerce' ],
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
			case 'register-domain':
				return $this->register_domain( $request );
			case 'create-webhook':
				return $this->create_webhook( $request );
			case 'delete-webhook':
				return $this->delete_webhook( $request );
			case 'connection-test':
				return $this->connection_test( $request );
			case 'delete-connection':
				return $this->delete_connection( $request );
			case 'create-payment-config':
				return $this->create_payment_config( $request );
			case 'fetch-payment-config':
				return $this->fetch_payment_config( $request );
			case 'refresh-payment-config':
				return $this->refresh_payment_config( $request );
		}

		throw new \Exception( sprintf( __( 'Unknown task: %s', 'woo-stripe-payment' ), $action ), 400 );
	}

	private function register_domain( \WP_REST_Request $request ) {
		if ( isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
			$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . '.well-known';
			$file = $path . DIRECTORY_SEPARATOR . 'apple-developer-merchantid-domain-association';
			if ( ! file_exists( $file ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				if ( function_exists( 'WP_Filesystem' ) && WP_Filesystem() ) {
					global $wp_filesystem;
					if ( ! $wp_filesystem->is_dir( $path ) ) {
						$wp_filesystem->mkdir( $path );
					}
					$contents = $wp_filesystem->get_contents( WC_STRIPE_PLUGIN_FILE_PATH . 'apple-developer-merchantid-domain-association' );
					$wp_filesystem->put_contents( $file, $contents, 0755 );
				}
			}
		}

		$server_name = ! empty( $request['hostname'] ) ? $request['hostname'] : $_SERVER['SERVER_NAME'];
		$server_name = apply_filters( 'wc_stripe_apple_pay_domain', $server_name );
		$domains     = [ $server_name ];
		$messages    = [];

		foreach ( [ 'test', 'live' ] as $mode ) {
			$api_key = wc_stripe_get_secret_key( $mode );
			if ( empty( $api_key ) ) {
				$messages[] = sprintf(
					__( 'Domain could not be registered in %s mode. %s mode is not connected.', 'woocommerce' ),
					$server_name,
					ucfirst( $mode )
				);
				continue;
			}

			$registered_domains = $this->client->mode( $mode )->paymentMethodDomains->all( [ 'limit' => 50 ] );

			if ( \is_wp_error( $registered_domains ) ) {
				$messages[] = sprintf( __( 'Error fetching domains for %s mode: %s', 'woo-stripe-payment' ), $mode, $registered_domains->get_error_message() );
				continue;
			}

			$filtered_domains = array_filter( $registered_domains->data, fn( $domain ) => in_array( $domain->domain_name, $domains, true ) );

			foreach ( $domains as $domain_name ) {
				$result = null;
				foreach ( $filtered_domains as $filtered_domain ) {
					if ( $filtered_domain->domain_name === $domain_name ) {
						$result = $filtered_domain;
						break;
					}
				}

				if ( ! $result ) {
					$response = $this->client->mode( $mode )->paymentMethodDomains->create( [
						'domain_name' => $domain_name,
						'enabled'     => true
					] );
					if ( \is_wp_error( $response ) ) {
						$messages[] = sprintf( __( 'Error creating domain %s for %s mode: %s', 'woo-stripe-payment' ), $domain_name, $mode, $response->get_error_message() );
					} else {
						$messages[] = sprintf(
							__( 'Domain %s registered successfully. You can confirm in your Stripe Dashboard at %s.', 'woo-stripe-payment' ),
							$domain_name,
							'https://dashboard.stripe.com/settings/payment_method_domains'
						);
					}
				} else {
					$response = $this->client->mode( $mode )->paymentMethodDomains->update( $result->id, [ 'enabled' => true ] );
					if ( \is_wp_error( $response ) ) {
						$messages[] = sprintf( __( 'Error updating domain %s for %s mode: %s', 'woo-stripe-payment' ), $domain_name, $mode, $response->get_error_message() );
					} else {
						$messages[] = sprintf( __( 'Domain %s enabled successfully for %s mode.', 'woo-stripe-payment' ), $domain_name, $mode );
					}
				}
			}
		}

		return [ 'messages' => $messages ];
	}

	private function create_webhook( \WP_REST_Request $request ) {
		$api_settings = stripe_wc()->api_settings;
		$env          = $request->get_param( 'environment' );
		$events       = [];

		if ( empty( wc_stripe_get_secret_key( $env ) ) ) {
			throw new \Exception( __( 'You must configure your secret key before creating webhooks.', 'woo-stripe-payment' ) );
		}

		$url      = get_rest_url( null, '/wc-stripe/v1/webhook' );
		$webhooks = $this->client->webhookEndpoints->all( [ 'limit' => 100 ] );

		if ( ! \is_wp_error( $webhooks ) ) {
			foreach ( $webhooks->data as $webhook ) {
				if ( $webhook->url === $url ) {
					if ( ! $api_settings->get_option( "webhook_secret_{$env}", null ) ) {
						$events = $webhook->enabled_events;
						$this->client->webhookEndpoints->delete( $webhook->id );
						$api_settings->delete_webhook_settings( $env );
					} else {
						throw new \Exception( __( 'There is already a webhook configured for this site. If you want to delete the webhook, login to your Stripe Dashboard.', 'woo-stripe-payment' ) );
					}
				}
			}
		}

		$webhook = $api_settings->create_webhook( $env, $events );

		if ( \is_wp_error( $webhook ) ) {
			throw new \Exception( $webhook->get_error_message() );
		}

		return [
			'message' => sprintf(
				__( 'Webhook created in Stripe for %s environment. You can test your webhook by logging in to the Stripe dashboard', 'woo-stripe-payment' ),
				'live' === $env ? __( 'Live', 'woo-stripe-payment' ) : __( 'Test', 'woo-stripe-payment' )
			),
			'secret'  => $webhook['secret'],
		];
	}

	private function delete_webhook( \WP_REST_Request $request ) {
		$api_settings = stripe_wc()->api_settings;
		$mode         = $request['mode'];
		$webhook_id   = $api_settings->get_webhook_id( $mode );

		if ( $webhook_id ) {
			$result = $this->client->mode( $mode )->webhookEndpoints->delete( $webhook_id );
			$api_settings->delete_webhook_settings( $mode );
			if ( \is_wp_error( $result ) ) {
				/**
				 * @var \WP_Error $result
				 */
				throw new \Exception( $result->get_error_message() );
			}
		}

		return [ 'success' => true ];
	}

	private function connection_test( \WP_REST_Request $request ) {
		$mode     = $request->get_param( 'mode' );
		$settings = stripe_wc()->api_settings;

		ob_start();
		try {
			if ( $mode === 'test' ) {
				$api_keys = [ $request->get_param( 'secret_key' ), $request->get_param( 'publishable_key' ) ];
				if ( in_array( null, $api_keys, true ) ) {
					throw new \Exception( __( 'You must enter your API keys or connect the plugin before performing a connection test.', 'woo-stripe-payment' ) );
				}
				$settings->settings['publishable_key_test'] = $settings->validate_text_field( 'publishable_key_test', $request->get_param( 'publishable_key' ) );
				$settings->settings['secret_key_test']      = $settings->validate_password_field( 'secret_key_test', $request->get_param( 'secret_key' ) );
			}

			$response = $this->client->mode( $mode )->customers->all( [ 'limit' => 1 ] );
			if ( \is_wp_error( $response ) ) {
				throw new \Exception( sprintf( __( 'Mode: %s. Invalid secret key. Please check your entry.', 'woo-stripe-payment' ), $mode ) );
			}

			$response = wp_remote_post( 'https://api.stripe.com/v1/payment_methods', [
				'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
				'body'    => [
					'key'      => wc_stripe_get_publishable_key( $mode ),
					'type'     => 'card',
					'card'     => [
						'number'    => '4242424242424242',
						'exp_month' => 12,
						'exp_year'  => 2030,
						'cvc'       => 314
					],
					'metadata' => [ 'origin' => 'API Settings connection test' ]
				],
			] );

			if ( \is_wp_error( $response ) || ( $response['response']['code'] ?? 0 ) == 401 ) {
				throw new \Exception( sprintf( __( 'Mode: %s. Invalid publishable key. Please check your entry.', 'woo-stripe-payment' ), $mode ) );
			}

			if ( isset( $api_keys ) ) {
				update_option( $settings->get_option_key(), $settings->settings, 'yes' );
				do_action( 'wc_stripe_api_connection_test_success', $mode );
			}
			ob_get_clean();
		} catch ( \Exception $e ) {
			ob_get_clean();
			throw $e;
		}

		return [ 'message' => sprintf( __( 'Connection test to Stripe was successful. Mode: %s.', 'woo-stripe-payment' ), $mode ) ];
	}

	private function delete_connection( \WP_REST_Request $request ) {
		stripe_wc()->api_settings->delete_account_settings();
		stripe_wc()->account_settings->delete_account_settings();

		return [ 'success' => true ];
	}

	private function create_payment_config( \WP_REST_Request $request ) {
		global $current_section;
		$mode = wc_stripe_mode();
		/** @var \WC_Payment_Gateway_Stripe_UPM $payment_method */
		$payment_method = WC()->payment_gateways()->payment_gateways()['stripe_upm'];

		$response = $payment_method->gateway->mode( $mode )->paymentMethodConfigurations->create( [
			'name' => $request->get_param( 'name' )
		] );

		if ( \is_wp_error( $response ) ) {
			throw new \Exception( sprintf( __( 'Error creating payment method configuration. Reason: %s', 'woo-stripe-payment' ), $response->get_error_message() ) );
		}

		$payment_method->update_payment_method_configuration( $response->id, $mode );
		$payment_method->update_available_payment_methods( $payment_method->map_payment_config_to_payment_methods( $response ), $mode );
		$current_section = $payment_method->id;
		$payment_method->init_form_fields();

		ob_start();
		$payment_method->admin_options();

		return [ 'html' => ob_get_clean() ];
	}

	private function fetch_payment_config( \WP_REST_Request $request ) {
		global $current_section;
		$mode = wc_stripe_mode();
		/** @var \WC_Payment_Gateway_Stripe_UPM $payment_method */
		$payment_method = WC()->payment_gateways()->payment_gateways()['stripe_upm'];
		$id             = $request->get_param( 'payment_config' );

		$config = $payment_method->gateway->mode( $mode )->paymentMethodConfigurations->retrieve( $id );
		if ( \is_wp_error( $config ) ) {
			throw new \Exception( __( 'Error retrieving payment method config. Check logs for more details.', 'woo-stripe-payment' ) );
		}

		$payment_method->update_payment_method_configuration( $id, $mode );
		$payment_method->update_available_payment_methods( $payment_method->map_payment_config_to_payment_methods( $config ), $mode );
		$current_section = $payment_method->id;
		$payment_method->init_form_fields();

		ob_start();
		$payment_method->admin_options();

		return [ 'html' => ob_get_clean() ];
	}

	private function refresh_payment_config( \WP_REST_Request $request ) {
		/** @var \WC_Payment_Gateway_Stripe_UPM $payment_method */
		$payment_method = WC()->payment_gateways()->payment_gateways()['stripe_upm'];
		$request->set_param( 'payment_config', $payment_method->get_payment_method_configuration() );

		return $this->fetch_payment_config( $request );
	}

}
