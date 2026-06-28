<?php

namespace PaymentPlugins\Stripe\Blocks\BlockTypes;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;

class MiniCartExpressPaymentBlock implements IntegrationInterface {

	private $assets;

	private $payment_registry;

	public function __construct( AssetsApi $assets, PaymentGatewayRegistry $payment_registry ) {
		$this->assets           = $assets;
		$this->payment_registry = $payment_registry;
	}

	public function initialize() {
		add_filter( 'render_block_woocommerce/mini-cart-footer-block', [ $this, 'prepend_express_container' ], 100 );

		$this->assets->register_script_module(
			'@wc-stripe/mini-cart-block',
			'build/mini-cart-block.js'
		);
	}

	public function get_script_handles() {
		return [];
	}

	public function get_editor_script_handles() {
		return [];
	}

	public function get_name() {
		return 'wcStripeMiniCartExpressPaymentBlock';
	}

	public function prepend_express_container( $block_content ) {
		$gateways = $this->payment_registry->get_minicart_payment_gateways();

		if ( empty( $gateways ) ) {
			return $block_content;
		}

		wp_enqueue_script_module( '@wc-stripe/mini-cart-block' );

		$anchors = '';
		foreach ( $gateways as $gateway ) {
			$anchors .= '<a class="wc-' . esc_attr( $gateway->id ) . '-mini-cart" style="display:none"></a>';
		}

		$container = '<div'
		             . ' data-wp-interactive="wc-stripe/mini-cart-express"'
		             . ' data-wp-init="callbacks.onInit"'
		             . ' data-wp-watch--open="callbacks.onDrawerOpen"'
		             . ' data-wp-watch--cart="callbacks.onCartUpdated"'
		             . ' class="wc-stripe-mini-cart-express"'
		             . '>' . $anchors . '</div>';

		return str_replace(
			'<div class="wc-block-mini-cart__footer-actions">',
			'<div class="wc-block-mini-cart__footer-actions">' . $container,
			$block_content
		);
	}

	public function get_script_data() {
		return [];
	}
}