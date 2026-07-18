<?php

namespace PaymentPlugins\Stripe\Assets;

use PaymentPlugins\Stripe\ContextHandler;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;
use PaymentPlugins\Stripe\Transformers\DataTransformer;
use PaymentPlugins\Stripe\Utilities\ProductUtils;

/**
 * Controller responsible for managing asset data output:
 * - Registers WordPress hooks
 * - Populates default data based on context
 * - Coordinates data output to page
 *
 * @since 4.0.0
 */
class AssetDataController {

	/**
	 * @var AssetDataApi
	 */
	private $asset_data;

	/**
	 * @var ContextHandler
	 */
	private $context;

	/**
	 * @var PaymentGatewayRegistry
	 */
	private $payment_registry;

	/**
	 * @var DataTransformer
	 */
	private $transformer;

	/**
	 * @param AssetDataApi           $asset_data
	 * @param ContextHandler         $context
	 * @param PaymentGatewayRegistry $payment_registry
	 */
	public function __construct( AssetDataApi $asset_data, ContextHandler $context, PaymentGatewayRegistry $payment_registry ) {
		$this->asset_data       = $asset_data;
		$this->context          = $context;
		$this->payment_registry = $payment_registry;
		$this->transformer      = new DataTransformer();
	}

	/**
	 * Register WordPress hooks
	 *
	 * @return void
	 */
	public function initialize() {
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_asset_data' ], 1 );
		add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'get_update_order_review_data' ] );
		add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'get_add_to_cart_fragments' ] );
		add_filter( 'wc_stripe_cart_before_payment_methods', [ $this, 'add_cart_refresh_data' ] );
		add_filter( 'wc_stripe_minicart_before_payment_methods', [ $this, 'add_minicart_refresh_data' ] );
	}

	/**
	 * Output the data from the AssetDataApi to the page.
	 *
	 * @return void
	 */
	public function enqueue_asset_data() {
		if ( is_admin() ) {
			return;
		}

		$this->add_default_data();

		/**
		 * Add script data that's output to frontend pages.
		 *
		 * @param AssetDataApi   $asset_data The data API for adding data
		 * @param ContextHandler $context The context handler
		 *
		 * @since 4.0.0
		 */
		do_action( 'wc_stripe_add_script_data', $this->asset_data, $this->context );

		// Output data if any exists
		if ( $this->asset_data->has_data() ) {
			$this->asset_data->print_data( 'wcStripeSettings', $this->asset_data->get_data() );
		}
	}

	/**
	 * Add data to AJAX cart update responses
	 *
	 * @param array $fragments
	 *
	 * @return array
	 */
	public function get_update_order_review_data( $fragments ) {
		$data = [
			'cart' => $this->transformer->transform_cart( WC()->cart )
		];

		$fragments['wc_stripe_data'] = $data;

		return $fragments;
	}

	public function get_add_to_cart_fragments( $fragments ) {
		$data = [
			'cart' => $this->transformer->transform_cart( WC()->cart )
		];

		$fragments['wc_stripe_data'] = $data;

		return $fragments;
	}

	/**
	 * Add data for cart refresh AJAX requests
	 *
	 * @return void
	 */
	public function add_cart_refresh_data() {
		if ( ! is_ajax() ) {
			return;
		}

		/**
		 * Add additional data for cart refresh requests
		 *
		 * @param AssetDataApi $asset_data The data API for adding data
		 *
		 * @since 4.0.0
		 */
		do_action( 'wc_stripe_cart_refresh_data', $this->asset_data );

		$this->asset_data->add( 'cart', $this->transformer->transform_cart( WC()->cart ) );
		$this->asset_data->print_data( 'wcStripeCartData', $this->asset_data->get_data() );
	}

	public function add_minicart_refresh_data() {
		/**
		 * Add additional data for cart refresh requests
		 *
		 * @param AssetDataApi $asset_data The data API for adding data
		 *
		 * @since 4.0.0
		 */
		do_action( 'wc_stripe_minicart_refresh_data', $this->asset_data );

		$this->asset_data->add( 'cart', $this->transformer->transform_cart( WC()->cart ) );
		$this->asset_data->print_data( 'wcStripeMiniCartData', $this->asset_data->get_data() );
	}

	/**
	 * Add default data based on current WooCommerce context
	 *
	 * @return void
	 */
	private function add_default_data() {
		global $product;

		/**
		 * cart/customer data reflects the current visitor's cart contents and personal details, so
		 * it's only added on pages that actually need it, rather than broadcasting it site-wide.
		 * Mini-cart gateway buttons (Apple Pay/Google Pay/Link) can render on any page though, so
		 * cart data is also added whenever any gateway has mini-cart enabled - customer data isn't
		 * needed there, since those buttons collect billing/shipping via their own native sheet.
		 *
		 * order-pay needs cart data too, even though billing/shipping details are sourced from the
		 * separate order data added below - BaseController::isPaymentMethodAvailable() unconditionally
		 * reads Cart.isPaymentMethodAvailable(), which was never ported to fall back to order data.
		 */
		if ( WC()->cart && ( $this->is_cart_relevant_page() || $this->context->is_order_pay() || $this->has_minicart_gateways_enabled() ) ) {
			$this->asset_data->add( 'cart', $this->transformer->transform_cart( WC()->cart ) );
		}

		if ( $product && \is_object( $product ) || $this->context->is_product() ) {
			if ( ! $product ) {
				$product = ProductUtils::get_queried_product();
			}
			if ( $product instanceof \WP_Post && $product->post_type === 'product' ) {
				$product = wc_get_product( $product->ID );
			}
			if ( $product instanceof \WC_Product ) {
				$args = [];
				if ( $product instanceof \WC_Product_Variable ) {
					$selected_attributes = [];
					foreach ( array_keys( $product->get_variation_attributes() ) as $attribute_name ) {
						$attribute_key = 'attribute_' . sanitize_title( $attribute_name );
						// phpcs:ignore WordPress.Security.NonceVerification.Recommended
						if ( isset( $_REQUEST[ $attribute_key ] ) ) {
							$selected_attributes[ $attribute_key ] = wc_clean( wp_unslash( $_REQUEST[ $attribute_key ] ) );
						}
					}
					if ( ! empty( $selected_attributes ) ) {
						$args['selected_attributes'] = $selected_attributes;
					}
				}
				$this->asset_data->add( 'product', $this->transformer->transform_product( $product, $args ) );
			}
		}

		if ( $this->context->is_order_pay() ) {
			$order = $this->context->get_order_from_query();
			if ( $order ) {
				$this->asset_data->add( 'order', $this->transformer->transform_order( $order ) );
			}
		}

		$symbol     = get_woocommerce_currency_symbol();
		$format     = get_woocommerce_price_format();
		$currency   = get_woocommerce_currency();
		$currencies = wc_stripe_get_currencies();
		$minor_unit = isset( $currencies[ $currency ] ) ? $currencies[ $currency ] : 2;
		$this->asset_data->add( 'currency', [
			'currencyCode'      => $currency,
			'currencySymbol'    => $symbol,
			'minorUnit'         => $minor_unit,
			'decimalSeparator'  => wc_get_price_decimal_separator(),
			'thousandSeparator' => wc_get_price_thousand_separator(),
			'prefix'            => '%1$s%2$s' === $format ? $symbol : '',
			'suffix'            => '%2$s%1$s' === $format ? $symbol : '',
		] );

		$this->asset_data->add( 'version', wc_stripe_get_container()->get( 'VERSION' ) );
		$this->asset_data->add( 'page', $this->context->get_context() );
		$this->asset_data->add( 'publicKey', wc_stripe_get_publishable_key() );
		$this->asset_data->add( 'mode', wc_stripe_mode() );
		$this->asset_data->add( 'sdkParams', [
			'stripeAccount'  => wc_stripe_get_account_id(),
			'betas'          => [
				'deferred_intent_blik_beta_1',
				'disable_deferred_intent_client_validation_beta_1'
			],
			'developerTools' => [
				'assistant' => [
					'enabled' => false
				]
			]
		] );
		$this->asset_data->add( 'addressLocales', wp_json_encode( WC()->countries->get_country_locale() ) );
		$this->asset_data->add( 'errorMessages', wc_stripe_get_error_messages() );

		// Add required fields data
		$this->asset_data->add( 'requiredFields', $this->get_required_fields() );

		// customer data is only ever consumed by BaseGateway.js's isAddPaymentMethod() branches.
		if ( WC()->customer && $this->context->is_add_payment_method() ) {
			$customer = WC()->customer;
			$this->asset_data->add( 'customer', $this->transformer->transform_customer( $customer ) );
		}
	}

	/**
	 * @return bool
	 */
	private function is_cart_relevant_page() {
		return $this->context->has_context( [
			ContextHandler::PRODUCT,
			ContextHandler::CART,
			ContextHandler::CHECKOUT,
			ContextHandler::SHOP,
			ContextHandler::ADD_PAYMENT_METHOD,
		] );
	}

	/**
	 * @return bool
	 */
	private function has_minicart_gateways_enabled() {
		return ! empty( $this->payment_registry->get_minicart_payment_gateways() );
	}

	/**
	 * Get required checkout fields
	 *
	 * @return array Array of field names that are required
	 */
	private function get_required_fields() {
		$required_fields = [];

		if ( WC()->checkout ) {
			$checkout_fields = WC()->checkout->get_checkout_fields();

			foreach ( $checkout_fields as $field_group => $fields ) {
				foreach ( $fields as $field_key => $field_data ) {
					if ( ! empty( $field_data['required'] ) ) {
						$required_fields[] = $field_key;
					}
				}
			}
		}

		return $required_fields;
	}

	/**
	 * Get payment methods with their capabilities
	 *
	 * @return array
	 */
	private function get_payment_methods() {
		$gateways        = $this->payment_registry->get_registered_integrations();
		$payment_methods = [];

		foreach ( $gateways as $gateway_id => $gateway ) {
			$payment_methods[ $gateway_id ] = [
				'enabled'           => wc_string_to_bool( $gateway->enabled ),
				'supports'          => $gateway->supports,
				'isLocal'           => $gateway instanceof \WC_Payment_Gateway_Stripe_Local_Payment,
				'paymentMethodType' => $gateway->get_payment_method_type()
			];
		}

		return $payment_methods;
	}

	/**
	 * Get order from query vars (for order-pay page)
	 *
	 * @return \WC_Order|null
	 */
	private function get_order_from_query_vars() {
		global $wp;

		if ( isset( $wp->query_vars['order-pay'] ) ) {
			$order_id = absint( $wp->query_vars['order-pay'] );

			return wc_get_order( $order_id );
		}

		return null;
	}
}