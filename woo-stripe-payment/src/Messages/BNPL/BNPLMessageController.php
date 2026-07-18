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

		// Block-based Single Product templates render the Product Price block directly,
		// bypassing the relative hook order above_price/below_price rely on. WooCommerce
		// bridges woocommerce_single_product_summary as a single buffer placed before the
		// price/summary block, so both locations end up rendering in the same spot.
		// Inject directly into the block instead, and tell WooCommerce's compatibility
		// layer to drop the classic-hook version on block templates so it isn't duplicated.
		//
		// The same Product Price/Product Button blocks are also used directly by
		// Product Collection-based Archive/Shop templates, which never fire the classic
		// woocommerce_after_shop_loop_item_title/woocommerce_after_shop_loop_item hooks
		// our shop rendering otherwise relies on.
		add_filter( 'render_block_woocommerce/product-price', [ $this, 'render_block_product_price' ], 10, 3 );
		add_filter( 'render_block_woocommerce/product-button', [ $this, 'render_block_product_button' ], 10, 3 );
		add_filter( 'woocommerce_blocks_hook_compatibility_additional_data', [
			$this,
			'remove_block_template_duplicate_hooks'
		], 10, 2 );

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

		$this->push_shop_product_data( $product );
	}

	/**
	 * Adds BNPL script data for a shop-loop product, shared by both the classic
	 * woocommerce_shop_loop hook and the block-based Archive/Shop template path
	 * (render_block_product_price()/render_block_product_button()), which resolves
	 * the product from the block's own postId context instead, since Product
	 * Collection templates never fire that classic hook.
	 *
	 * @param \WC_Product $product
	 *
	 * @return void
	 */
	private function push_shop_product_data( \WC_Product $product ) {
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
		$data       = $asset_data->get( 'bnplShopProducts', [] );

		foreach ( $data as $entry ) {
			if ( $entry['id'] === $product->get_id() ) {
				return;
			}
		}

		$data[] = [
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
			echo $this->get_shop_message_container( $product->get_id() );
		}
	}

	private function get_shop_message_container( $product_id ) {
		return sprintf(
			'<div id="wc-stripe-bnpl-shop-msg-%d" class="wc-stripe-bnpl-shop-message"></div>',
			$product_id
		);
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
			echo $this->get_product_message_container();
		}
	}

	private function get_product_message_container() {
		return '<div id="wc-stripe-bnpl-product-msg" class="wc-stripe-bnpl-product-message" style="display:none"></div>';
	}

	/**
	 * Injects the BNPL message container directly into the rendered Product Price block,
	 * so above_price/below_price positioning is respected on block-based Single Product
	 * templates, and below_price positioning is respected on block-based Archive/Shop
	 * templates (Product Collection with an inherited query).
	 *
	 * @param string    $block_content
	 * @param array     $block
	 * @param \WP_Block $instance
	 *
	 * @return string
	 */
	public function render_block_product_price( $block_content, $block, $instance ) {
		if ( is_product() ) {
			if ( ! empty( $instance->context['query'] ) ) {
				// Part of a query loop (Related Products, Upsells, etc.), not the main product.
				return $block_content;
			}

			return $this->inject_product_price_container( $block_content );
		}

		if ( $this->is_main_shop_loop( $block ) ) {
			return $this->inject_shop_container( $block_content, $instance->context['postId'] ?? '', 'below_price' );
		}

		return $block_content;
	}

	/**
	 * Injects the BNPL message container after the rendered Product Button block,
	 * for the after_button shop location on block-based Archive/Shop templates.
	 *
	 * @param string    $block_content
	 * @param array     $block
	 * @param \WP_Block $instance
	 *
	 * @return string
	 */
	public function render_block_product_button( $block_content, $block, $instance ) {
		if ( $this->is_main_shop_loop( $block ) ) {
			return $this->inject_shop_container( $block_content, $instance->context['postId'] ?? '', 'after_button' );
		}

		return $block_content;
	}

	private function inject_product_price_container( $block_content ) {
		$location = $this->get_location( 'bnpl_product_location', 'below_price' );

		if ( $location === 'above_price' ) {
			return $this->get_product_message_container() . $block_content;
		}

		if ( $location === 'below_price' ) {
			return $block_content . $this->get_product_message_container();
		}

		return $block_content;
	}

	/**
	 * True when the block is part of the Archive/Shop template's own inherited query
	 * loop (the real shop grid), as opposed to a secondary Product Collection instance
	 * placed elsewhere on the same page (e.g. a "Best Sellers" block).
	 *
	 * isInherited is the attribute WooCommerce's own ArchiveProductTemplatesCompatibility
	 * injects onto every descendant block of an inherited-query Products/Product Collection
	 * block (recursively, via the render_block_data filter, before blocks render) -- the
	 * same signal its own inject_hooks() relies on. $instance->context['query']['inherit']
	 * looked like the equivalent signal but isn't reliably populated across WC versions
	 * (confirmed empty on WC 9.7.1), so read the attribute WC's compatibility layer sets
	 * directly instead of re-deriving it from context propagation.
	 *
	 * @param array $block
	 *
	 * @return bool
	 */
	private function is_main_shop_loop( $block ) {
		return ( is_shop() || is_product_taxonomy() ) && ! empty( $block['attrs']['isInherited'] );
	}

	private function inject_shop_container( $block_content, $product_id, $location ) {
		if ( ! $product_id || $this->get_location( 'bnpl_shop_location', 'below_price' ) !== $location ) {
			return $block_content;
		}

		$product = wc_get_product( $product_id );

		if ( ! $product instanceof \WC_Product ) {
			return $block_content;
		}

		$this->push_shop_product_data( $product );

		return $block_content . $this->get_shop_message_container( $product->get_id() );
	}

	/**
	 * Prevents the classic hook-based rendering from also firing on block-based
	 * templates, since render_block_product_price()/render_block_product_button()
	 * already handle those cases directly: woocommerce_single_product_summary on
	 * block-based Single Product templates, and woocommerce_after_shop_loop_item_title/
	 * woocommerce_after_shop_loop_item on block-based Archive/Shop templates.
	 *
	 * @param array  $data
	 * @param string $class_name
	 *
	 * @return array
	 */
	public function remove_block_template_duplicate_hooks( $data, $class_name ) {
		if ( $class_name === 'SingleProductTemplateCompatibility' ) {
			$data[] = [
				'hook'     => 'woocommerce_single_product_summary',
				'function' => [ $this, 'render_product_above_price' ],
				'priority' => 8,
			];
			$data[] = [
				'hook'     => 'woocommerce_single_product_summary',
				'function' => [ $this, 'render_product_below_price' ],
				'priority' => 15,
			];
		} elseif ( $class_name === 'ArchiveProductTemplatesCompatibility' ) {
			$data[] = [
				'hook'     => 'woocommerce_after_shop_loop_item_title',
				'function' => [ $this, 'render_shop_below_price' ],
				'priority' => 20,
			];
			$data[] = [
				'hook'     => 'woocommerce_after_shop_loop_item',
				'function' => [ $this, 'render_shop_after_button' ],
				'priority' => 15,
			];
		}

		return $data;
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