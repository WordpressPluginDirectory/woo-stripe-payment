<?php

namespace PaymentPlugins\Stripe\Blocks\BlockTypes;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;

/**
 * Bridges the woocommerce/add-to-cart-with-options interactivity API block
 * with the Stripe express payment button infrastructure.
 *
 * The block's variation selector updates variationId in the woocommerce/products
 * context rather than firing jQuery found_variation events, so a small iAPI
 * view script watches the context and dispatches CustomEvents that the existing
 * product controller can subscribe to via DOMEvents.js actions.
 *
 * @since   4.0.0
 * @package PaymentPlugins\Stripe\Blocks\BlockTypes
 */
class SingleProductExpressPaymentBlock implements IntegrationInterface {

	private $assets;

	private $payment_registry;

	public function __construct( AssetsApi $assets, PaymentGatewayRegistry $payment_registry ) {
		$this->assets           = $assets;
		$this->payment_registry = $payment_registry;
	}

	public function initialize() {
		add_filter( 'render_block_woocommerce/add-to-cart-with-options', [
			$this,
			'append_interactivity_container'
		], 100 );

		$this->assets->register_script_module(
			'@wc-stripe/single-product-block',
			'build/single-product-block.js'
		);
	}

	/**
	 * Injects a hidden interactivity container inside the block's outer wrapper.
	 *
	 * The element must be a descendant of the woocommerce/products context element
	 * (set by woocommerce/add-to-cart-with-options) so that getContext('woocommerce/products')
	 * inside the view script resolves to the per-product {productId, variationId} object.
	 */
	public function append_interactivity_container( $block_content ) {
		$gateways = $this->payment_registry->get_product_payment_gateways();

		if ( empty( $gateways ) ) {
			return $block_content;
		}

		wp_enqueue_script_module( '@wc-stripe/single-product-block' );

		$container = '<div'
		             . ' data-wp-interactive="wc-stripe/single-product-express"'
		             . ' data-wp-init="callbacks.onInit"'
		             . ' data-wp-watch--variation="callbacks.onVariationChanged"'
		             . ' style="display:none"'
		             . '></div>';

		// Append inside the outer wrapper div (before its closing tag) so the
		// element inherits the woocommerce/products context carrying variationId.
		// strrpos finds the last </div>, which is the outer wrapper's closing tag.
		$pos = strrpos( $block_content, '</div>' );
		if ( $pos !== false ) {
			return substr( $block_content, 0, $pos ) . $container . substr( $block_content, $pos );
		}

		return $block_content . $container;
	}

	public function get_name() {
		return 'wcStripeSingleProductExpressPaymentBlock';
	}

	public function get_script_handles() {
		return [];
	}

	public function get_editor_script_handles() {
		return [];
	}

	public function get_script_data() {
		return [];
	}
}
