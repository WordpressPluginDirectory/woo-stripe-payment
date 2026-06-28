<?php

namespace PaymentPlugins\Stripe\Messages\BNPL;

use PaymentPlugins\Stripe\Assets\AssetDataApi;
use PaymentPlugins\Stripe\ContextHandler;
use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;

/**
 * Handles BNPL messaging across all page contexts (product, cart, checkout, shop).
 *
 * Queries the PaymentGatewayRegistry for gateways that support 'stripe_bnpl_msg',
 * checks each gateway's message_sections setting to determine if messaging is
 * enabled for the current page, then renders DOM containers and enqueues scripts.
 *
 * @since 4.0.0
 */
class BNPLMessageController {

	/**
	 * @var PaymentGatewayRegistry
	 */
	private $payment_registry;

	/**
	 * @var ContextHandler
	 */
	private $context;

	/**
	 * Cached array of gateways that support BNPL messaging for the current context.
	 *
	 * @var \WC_Payment_Gateway_Stripe_Local_Payment[]|null
	 */
	private $supported_gateways;

	public function __construct( PaymentGatewayRegistry $registry, ContextHandler $context ) {
		$this->payment_registry = $registry;
		$this->context          = $context;
	}

	public function initialize() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_action( 'wc_stripe_add_script_data', [ $this, 'add_script_data' ] );

		// Product page hooks
		add_action( 'woocommerce_single_product_summary', [ $this, 'render_product_above_price' ], 8 );
		add_action( 'woocommerce_single_product_summary', [ $this, 'render_product_below_price' ], 15 );
		add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'render_product_below_add_to_cart' ], 5 );

		// Cart page hooks
		add_action( 'woocommerce_cart_totals_after_order_total', [ $this, 'render_cart_below_total' ] );
		add_action( 'woocommerce_proceed_to_checkout', [ $this, 'render_cart_below_checkout_button' ], 21 );

		// Checkout page hooks
		add_action( 'woocommerce_review_order_after_order_total', [ $this, 'render_checkout_below_total' ] );
		add_filter( 'woocommerce_gateway_icon', [ $this, 'get_payment_gateway_icon' ], 10, 2 );

		// Shop page hooks
		add_action( 'woocommerce_shop_loop', [ $this, 'add_shop_script_data' ] );
		add_action( 'woocommerce_after_shop_loop_item_title', [ $this, 'render_shop_below_price' ], 20 );
		add_action( 'woocommerce_after_shop_loop_item', [ $this, 'render_shop_after_button' ], 15 );
	}

	public function enqueue_scripts() {
		if ( $this->get_supported_gateways() ) {
			// We have separate enqueue logic for the block based bnpl messaging.
			if ( $this->context->is_cart_block() || $this->context->is_checkout_block() ) {
				return;
			}
			wp_enqueue_script( 'wc-stripe-bnpl-messages' );
		}
	}

	/**
	 * Add script data that's used by the BNPL integration to render messaging on the frontend.
	 *
	 * A single Stripe element renders all supported BNPL payment method types together.
	 *
	 * @param AssetDataApi $asset_data
	 *
	 * @return void
	 */
	public function add_script_data( AssetDataApi $asset_data ) {
		$gateways = $this->get_supported_gateways();
		if ( empty( $gateways ) ) {
			return;
		}

		$context = $this->context->get_context();

		$data = [
			'paymentMethods' => array_values( array_map( function ( $gateway ) {
				return [
					'id'                => $gateway->id,
					'paymentMethodType' => $gateway->get_payment_method_type()
				];
			}, $gateways ) ),
			'currencies'     => $this->get_supported_currencies(),
			'countries'      => $this->get_supported_countries(),
			'selector'       => sprintf( '#wc-stripe-bnpl-%s-msg', $context ),
			'countryCode'    => stripe_wc()->account_settings->get_account_country( wc_stripe_mode() ),
			'elementOptions' => [
				'locale'     => wc_stripe_get_site_locale(),
				'appearance' => [
					'theme' => stripe_wc()->advanced_settings->get_option( 'bnpl_theme', 'stripe' )
				]
			],
			'locations'      => [
				'product'  => $this->get_location( 'bnpl_product_location', 'below_price' ),
				'cart'     => $this->get_location( 'bnpl_cart_location', 'below_total' ),
				'shop'     => $this->get_location( 'bnpl_shop_location', 'below_price' ),
				'checkout' => $this->get_location( 'bnpl_checkout_location', 'payment_method_title' ),
			]
		];

		/**
		 * Filter which can be used to modify data used by the BNPL message integration.
		 * Example of how to use the appearance API to modify the styling:
		 * https://docs.stripe.com/elements/payment-method-messaging#appearance
		 *
		 * @param array          $data
		 * @param ContextHandler $context
		 *
		 * @since 4.0.0
		 */
		$data = apply_filters( 'wc_stripe_bnpl_message_data', $data, $context );

		$asset_data->add( 'bnplMessages', $data );
	}

	public function add_shop_script_data() {
		global $product;

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		/**
		 * Filters the list of BNPL gateways that support messaging for a specific product.
		 *
		 * Packages like Subscriptions or Pre-Orders can hook into this to remove
		 * gateways that don't support their product types.
		 *
		 * @param \WC_Payment_Gateway_Stripe_Local_Payment[] $gateways The supported BNPL gateways.
		 * @param \WC_Product                                $product The product being checked.
		 */
		$gateways = apply_filters( 'wc_stripe_bnpl_shop_message_gateways', $this->get_supported_gateways(), $product );

		if ( empty( $gateways ) ) {
			return;
		}

		/** @var AssetDataApi $asset_data */
		$asset_data = wc_stripe_get_container()->get( AssetDataApi::class );
		$data       = $asset_data->get( 'bnplShopProducts' );
		$data[]     = [
			'id'             => $product->get_id(),
			'price'          => $product->get_price(),
			'priceCents'     => wc_stripe_add_number_precision( $product->get_price() ),
			'productType'    => $product->get_type(),
			'paymentMethods' => array_values( array_map( function ( $gateway ) {
				return [
					'id'                => $gateway->id,
					'paymentMethodType' => $gateway->get_payment_method_type()
				];
			}, $gateways ) )
		];
		$asset_data->add( 'bnplShopProducts', $data );
	}

	// -------------------------------------------------------------------------
	// Shop page rendering
	// -------------------------------------------------------------------------

	public function render_shop_below_price() {
		$this->render_shop_location( 'below_price' );
	}

	public function render_shop_after_button() {
		$this->render_shop_location( 'after_button' );
	}

	private function render_shop_location( $location ) {
		global $product;

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		if ( ! did_action( 'woocommerce_shop_loop' ) ) {
			$this->add_shop_script_data();
		}

		if ( $this->get_location( 'bnpl_shop_location', 'below_price' ) === $location ) {
			printf(
				'<div id="wc-stripe-bnpl-shop-msg-%d" class="wc-stripe-bnpl-shop-message"></div>',
				$product->get_id()
			);
		}
	}

	/**
	 * Returns a BNPL location setting from Advanced Settings.
	 *
	 * @param string $option_key The advanced setting key (e.g., 'bnpl_product_location')
	 * @param string $default Default value if not set
	 *
	 * @return string
	 */
	private function get_location( $option_key, $default ) {
		$advanced_settings = wc_stripe_get_container()->get( \WC_Stripe_Advanced_Settings::class );

		return $advanced_settings->get_option( $option_key, $default );
	}

	public function get_supported_currencies() {
		return apply_filters(
			'wc_stripe_supported_bnpl_currencies',
			[ 'USD', 'GBP', 'EUR', 'DKK', 'NOK', 'SEK', 'CAD', 'AUD', 'NZD', 'PLN', 'CZK', 'CHF', 'RON' ]
		);
	}

	public function get_supported_countries() {
		return apply_filters(
			'wc_stripe_supported_bnpl_countries',
			[
				'AT',
				'AU',
				'BE',
				'CA',
				'CH',
				'CZ',
				'DE',
				'DK',
				'ES',
				'FI',
				'FR',
				'GB',
				'GR',
				'IE',
				'IT',
				'NL',
				'NO',
				'NZ',
				'PL',
				'PT',
				'RO',
				'SE',
				'US'
			]
		);
	}

	/**
	 * Returns gateways that support BNPL messaging and have it enabled for the current page context.
	 *
	 * @return \WC_Payment_Gateway_Stripe_Local_Payment[]
	 */
	public function get_supported_gateways() {
		if ( $this->supported_gateways === null ) {
			$this->supported_gateways = [];
			$context                  = $this->context->get_context();

			if ( ! $context ) {
				return $this->supported_gateways;
			}

			$this->supported_gateways = $this->payment_registry->get_bnpl_payment_gateways( $this->context );

			/**
			 * Filters the list of BNPL gateways that support messaging for the current page.
			 *
			 * On the product page, packages like Subscriptions or Pre-Orders can use this
			 * to remove gateways that don't support the current product type.
			 *
			 * @param \WC_Payment_Gateway_Stripe_Local_Payment[] $gateways The supported BNPL gateways.
			 * @param ContextHandler                             $context The current page context.
			 */
			$this->supported_gateways = apply_filters( 'wc_stripe_bnpl_message_gateways', $this->supported_gateways, $this->context );
		}

		return $this->supported_gateways;
	}

	// -------------------------------------------------------------------------
	// Product page rendering
	// -------------------------------------------------------------------------

	public function render_product_above_price() {
		$this->render_product_location( 'above_price' );
	}

	public function render_product_below_price() {
		$this->render_product_location( 'below_price' );
	}

	public function render_product_below_add_to_cart() {
		$this->render_product_location( 'below_add_to_cart' );
	}

	private function render_product_location( $location ) {
		if ( $this->get_location( 'bnpl_product_location', 'below_price' ) === $location ) {
			printf( '<div id="wc-stripe-bnpl-product-msg" class="wc-stripe-bnpl-product-message" style="display:none"></div>' );
		}
	}

	// -------------------------------------------------------------------------
	// Cart page rendering
	// -------------------------------------------------------------------------

	public function render_cart_below_total() {
		$this->render_cart_location(
			'below_total',
			'<tr class="wc-stripe-bnpl-cart-message" style="display:none"><td colspan="2"><div id="%s"></div></td></tr>'
		);
	}

	public function render_cart_below_checkout_button() {
		$this->render_cart_location(
			'below_checkout_button',
			'<div id="%s" class="wc-stripe-bnpl-cart-message" style="display:none"></div>'
		);
	}

	private function render_cart_location( $location, $template ) {
		if ( $this->get_location( 'bnpl_cart_location', 'below_total' ) === $location ) {
			printf( $template, 'wc-stripe-bnpl-cart-msg' );
		}
	}

	// -------------------------------------------------------------------------
	// Checkout page rendering
	// -------------------------------------------------------------------------

	public function render_checkout_below_total() {
		if ( $this->get_location( 'bnpl_checkout_location', 'payment_method_title' ) === 'below_total' ) {
			printf(
				'<tr class="wc-stripe-bnpl-checkout-message" style="display:none"><td colspan="2"><div id="%s"></div></td></tr>',
				'wc-stripe-bnpl-checkout-msg'
			);
		}
	}

	/**
	 * @param string $icon
	 * @param string $gateway
	 *
	 * @return string
	 */
	public function get_payment_gateway_icon( $icon, $gateway_id ) {
		$supported_gateways = $this->get_supported_gateways();
		if ( isset( $supported_gateways[ $gateway_id ] ) ) {
			/**
			 * @var AbstractGateway $gateway
			 */
			$gateway  = $supported_gateways[ $gateway_id ];
			$location = $this->get_location( 'bnpl_checkout_location', 'payment_method_title' );
			if ( $location === 'payment_method_title' ) {
				$icon = sprintf(
					'<span id="wc-%1$s-bnpl-checkout-msg" class="wc-stripe-bnpl-checkout-message"></span>',
					$gateway->id
				);
			} else {
				$icon_name = $gateway->get_option( 'icon', '' );
				if ( $icon_name ) {
					$src  = stripe_wc()->assets_url( "img/{$icon_name}.svg" );
					$icon = '<img src="' . \WC_HTTPS::force_https_url( $src ) . '" alt="' . esc_attr( $gateway->get_title() ) . '" />';
				}
			}

		}

		return $icon;
	}

}