<?php

namespace PaymentPlugins\Stripe\Payments;

use PaymentPlugins\Stripe\Assets\AssetDataApi;
use PaymentPlugins\Stripe\Client\StripeClient;
use PaymentPlugins\Stripe\Container\Container;
use PaymentPlugins\Stripe\ContextHandler;
use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;
use PaymentPlugins\Stripe\ServiceProvider;

class PaymentGatewaysController {

	private $registry;

	private $context_handler;

	private $check_payment_availability = false;

	public function __construct( PaymentGatewayRegistry $registry, ContextHandler $context_handler ) {
		$this->registry        = $registry;
		$this->context_handler = $context_handler;
	}

	public function initialize( ServiceProvider $service_provider ) {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_minicart_scripts' ] );

		add_action( 'woocommerce_stripe_payment_gateways_registration', [ $this, 'register_payment_gateways' ], 10, 2 );

		add_filter( 'woocommerce_payment_gateways', function ( $gateways ) use ( $service_provider ) {
			// ensure WC dependencies are always loaded before adding gateways.
			$service_provider->include_woo_dependencies();

			return $this->add_woocommerce_payment_gateway( $gateways );
		} );

		add_filter( 'woocommerce_available_payment_gateways', [ $this, 'filter_available_payment_gateways' ] );

		add_action( 'wc_stripe_add_script_data', [ $this, 'add_payment_gateway_data' ], 10, 2 );

		add_filter( 'wc_stripe_cart_data', [ $this, 'add_cart_data' ] );
	}

	public function get_payment_gateway_classes() {
		return apply_filters(
			'wc_stripe_payment_gateways',
			array(
				'WC_Payment_Gateway_Stripe_CC',
				'WC_Payment_Gateway_Stripe_ApplePay',
				'WC_Payment_Gateway_Stripe_GooglePay',
				'WC_Payment_Gateway_Stripe_Payment_Request',
				'WC_Payment_Gateway_Stripe_Afterpay',
				'WC_Payment_Gateway_Stripe_Affirm',
				'WC_Payment_Gateway_Stripe_ACH',
				'WC_Payment_Gateway_Stripe_Ideal',
				'WC_Payment_Gateway_Stripe_P24',
				'WC_Payment_Gateway_Stripe_Klarna',
				'WC_Payment_Gateway_Stripe_Bancontact',
				'WC_Payment_Gateway_Stripe_EPS',
				'WC_Payment_Gateway_Stripe_Multibanco',
				'WC_Payment_Gateway_Stripe_Sepa',
				'WC_Payment_Gateway_Stripe_WeChat',
				'WC_Payment_Gateway_Stripe_FPX',
				'WC_Payment_Gateway_Stripe_BECS',
				'WC_Payment_Gateway_Stripe_Alipay',
				'WC_Payment_Gateway_Stripe_GrabPay',
				'WC_Payment_Gateway_Stripe_Boleto',
				'WC_Payment_Gateway_Stripe_OXXO',
				'WC_Payment_Gateway_Stripe_BLIK',
				'WC_Payment_Gateway_Stripe_Konbini',
				'WC_Payment_Gateway_Stripe_PayNow',
				'WC_Payment_Gateway_Stripe_PromptPay',
				'WC_Payment_Gateway_Stripe_Swish',
				'WC_Payment_Gateway_Stripe_AmazonPay',
				'WC_Payment_Gateway_Stripe_CashApp',
				'WC_Payment_Gateway_Stripe_Revolut',
				'WC_Payment_Gateway_Stripe_Zip',
				'WC_Payment_Gateway_Stripe_MobilePay',
				'WC_Payment_Gateway_Stripe_Twint',
				'WC_Payment_Gateway_Stripe_PayByBank',
				'WC_Payment_Gateway_Stripe_UPM',
				'WC_Payment_Gateway_Stripe_Link',
				'WC_Payment_Gateway_Stripe_Billie',
				'WC_Payment_Gateway_Stripe_Satispay',
				'WC_Payment_Gateway_Stripe_Scalapay',
				'WC_Payment_Gateway_Stripe_MBWay'
			)
		);
	}

	private function add_woocommerce_payment_gateway( $gateways ) {
		if ( $this->registry->is_empty() ) {
			$this->registry->initialize();
		}
		foreach ( $this->registry->get_registered_integrations() as $integration ) {
			$gateways[ $integration->id ] = $integration;
		}

		return $gateways;
	}

	/**
	 * @param PaymentGatewayRegistry $registry
	 *
	 * @return void
	 */
	public function register_payment_gateways( $registry, Container $container ) {
		foreach ( $this->get_payment_gateway_classes() as $clazz ) {
			if ( $container->has( $clazz ) ) {
				$gateway = $container->get( $clazz );
				$registry->register( $gateway );
				if ( $gateway->id === 'stripe_upm' ) {
					continue;
				}
				add_action( 'woocommerce_update_options_payment_gateways_' . $gateway->id, function () use ( $gateway ) {
					$this->process_payment_gateway_options( $gateway->id );
				}, 50 );

			}
		}
		do_action( 'wc_stripe_payment_gateways_registered', $registry );
	}

	public function enqueue_scripts() {
		$handles = [];

		if ( $this->registry->is_empty() ) {
			$this->registry->initialize();
		}

		$this->context_handler->initialize();

		if ( $this->context_handler->has_context( [
				'checkout',
				'add_payment_method',
				'order_pay'
			] ) || apply_filters( 'wc_stripe_checkout_scripts', false ) ) {

			// if this is the checkout block, and there is no checkout shortcode, return.
			// order pay page doesn't use the checkout block so if this is the order pay page
			// the scripts needs to be loaded.
			if ( $this->context_handler->is_checkout_block()
			     && ! $this->context_handler->is_checkout_shortcode()
			     && ! $this->context_handler->is_order_pay() ) {
				return;
			}
			if ( $this->context_handler->is_add_payment_method() ) {
				$handles = $this->registry->get_add_payment_method_script_handles();
			} else {
				$handles = $this->registry->get_checkout_script_handles();
				$handles = array_merge( $handles, $this->registry->get_express_checkout_script_handles() );
			}
		} elseif ( $this->context_handler->is_cart() && ! $this->context_handler->is_cart_block() ) {
			$handles = $this->registry->get_cart_script_handles();
		} elseif ( $this->context_handler->is_product() ) {
			$handles = $this->registry->get_product_script_handles();
		} elseif ( $this->context_handler->is_shop() ) {
			$handles = $this->registry->get_shop_script_handles();
		}

		if ( ! empty( $handles ) ) {
			foreach ( $handles as $handle ) {
				wp_enqueue_script( $handle );
			}
			wp_enqueue_style( 'wc-stripe-styles' );
			wp_style_add_data( 'wc-stripe-styles', 'rtl', 'replace' );
		}
	}

	public function enqueue_minicart_scripts() {
		if ( ! $this->context_handler->is_checkout()
		     && ! $this->context_handler->is_cart()
		     && ! $this->context_handler->is_order_pay()
		     && ! $this->context_handler->is_order_received()
		     // don't load minicart on checkout block
		     && ! $this->context_handler->is_checkout_block() ) {
			$handles = $this->registry->get_minicart_script_handles();
			if ( ! empty( $handles ) ) {
				foreach ( $handles as $handle ) {
					wp_enqueue_script( $handle );
				}
				wp_enqueue_style( 'wc-stripe-styles' );
			}
		}
	}

	/**
	 * @param \WC_Payment_Gateway[] $gateways
	 *
	 * @return \WC_Payment_Gateway[] $gateways
	 */
	public function filter_available_payment_gateways( $gateways ) {
		// Stripe payment request gateway is deprectated.
		unset( $gateways['stripe_payment_request'] );

		return $gateways;
	}

	public function add_payment_gateway_data( AssetDataApi $asset_data, ContextHandler $context ) {
		$data            = [];
		$payment_methods = [];
		$terms_rule      = stripe_wc()->advanced_settings->get_terms_display_rule();

		foreach ( $this->registry->get_active_integrations() as $integration ) {
			/**
			 * @var AbstractGateway $integration
			 */
			$data[ $integration->id . '_data' ] = array_merge(
				[
					'paymentSections'   => $integration->get_option( 'payment_sections', [] ),
					'paymentMethodType' => $integration->get_payment_method_type(),
					'description'       => $integration->get_description(),
					'hasPaymentTokens'  => ! is_add_payment_method_page() && ! empty( $integration->get_tokens() ),
					'termsDisplayRule'  => $terms_rule
				],
				$integration->get_payment_method_data(),

			);

			$payment_methods[ $integration->id ] = [
				'enabled'           => wc_string_to_bool( $integration->enabled ),
				'supports'          => $integration->supports,
				'isLocal'           => $integration instanceof \WC_Payment_Gateway_Stripe_Local_Payment,
				'paymentMethodType' => $integration->get_payment_method_type()
			];
		}
		/**
		 * @var array                  $data
		 * @var PaymentGatewayRegistry $asset_data
		 * @var ContextHandler         $context
		 * @version 4.0.0
		 */
		$data = apply_filters( 'wc_stripe_payment_gateway_data', $data, $this->registry, $context );

		foreach ( $data as $id => $payment_gateway_data ) {
			$asset_data->add( $id, $payment_gateway_data );
		}

		$asset_data->add( 'paymentMethods', $payment_methods );
	}

	/**
	 * Decorate the array of cart data with available payment methods. This method adds each registered
	 * payment method integration along with whether it's available.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function add_cart_data( $data ) {
		$available_gateways     = WC()->payment_gateways()->get_available_payment_gateways();
		$data['paymentMethods'] = [];

		foreach ( $this->registry->get_registered_integrations() as $integration ) {
			$data['paymentMethods'][ $integration->id ] = [
				'id'        => $integration->id,
				'enabled'   => \wc_string_to_bool( $integration->enabled ),
				'available' => isset( $available_gateways[ $integration->id ] )
			];

			/**
			 * The check_payment_availability property was added so that code in other locations could
			 * affect if is_local_payment_available is calculated. A good example of this is in
			 * PaymentPlugins\Stripe\Blocks\StoreApi\SchemaController.
			 */
			/*if ( $this->context_handler->is_checkout()
			     || $this->context_handler->is_checkout_block()
			     || $this->check_payment_availability ) {
				if ( $integration instanceof \WC_Payment_Gateway_Stripe_Local_Payment ) {
					$data['paymentMethods'][ $integration->id ]['available'] = $integration->is_local_payment_available();
				}
			}*/

		}

		return $data;
	}

	public function set_check_payment_availability( $bool ) {
		$this->check_payment_availability = $bool;
	}

	/**
	 * @param string $id
	 *
	 * @return void
	 */
	public function process_payment_gateway_options( string $id ) {
		/**
		 * @var AbstractGateway $gateway
		 */
		$gateway = $this->registry->get( $id );
		if ( ! $gateway ) {
			return;
		}
		$posted_data = $gateway->get_post_data();
		$key         = 'woocommerce_' . $gateway->id . '_enabled';
		if ( ! isset( $posted_data[ $key ] ) || 'no' === $posted_data[ $key ] ) {
			return;
		}
		/**
		 * @var StripeClient $client
		 */
		$client      = wc_stripe_get_container()->get( StripeClient::class );
		$application = wc_stripe_get_container()->get( 'CLIENT_ID' );
		// Guard against other plugins that use outdated Stripe PHP SDK
		if ( isset( $client->paymentMethodConfigurations ) ) {
			$modes = [ 'live', 'test' ];
			foreach ( $modes as $mode ) {
				$client->mode( $mode );
				if ( ! $client->is_connected() ) {
					continue;
				}
				// fetch the payment method configurations
				$result = $client->paymentMethodConfigurations->all( [ 'limit' => 50 ] );
				if ( ! is_wp_error( $result ) && ! empty( $result->data ) ) {
					// find the default configuration or the config associated with the application
					$pmc = null;
					foreach ( $result->data as $item ) {
						if ( $item->application == $application ) {
							$pmc = $item;
							break;
						} elseif ( $mode === 'test' && $item->is_default ) {
							$pmc = $item;
							break;
						}
					}
					if ( $pmc ) {
						switch ( $gateway->id ) {
							case 'stripe_applepay':
								$pmc_key = 'apple_pay';
								break;
							case 'stripe_googlepay':
								$pmc_key = 'google_pay';
								break;
							default:
								$pmc_key = $gateway->get_payment_method_type();
						}
						if ( ! isset( $pmc[ $pmc_key ] ) ) {
							continue;
						}
						$result = $client->paymentMethodConfigurations->update(
							$pmc->id,
							[
								$pmc_key => [
									'display_preference' => [
										'preference' => 'on'
									]
								]
							]
						);
						if ( is_wp_error( $result ) ) {
							wc_stripe_log_error( sprintf( 'Error updating payment method configuration %s. %s', $pmc->id, $result->get_error_message() ) );
						} else {
							wc_stripe_log_info( sprintf( 'Payment method configuration %s updated from admin options. %s->display_preference->preference = on', $pmc->id, $pmc_key ) );
						}
					}
				}
			}
		}

	}
}