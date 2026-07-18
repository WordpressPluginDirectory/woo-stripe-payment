<?php

namespace PaymentPlugins\Stripe\WooCommerceSubscriptions;

use PaymentPlugins\Stripe\Assets\AssetsApi;

/**
 * @package PaymentPlugins\WooCommerceSubscriptions\Stripe
 * @since 4.0.7
 */
class FrontendScripts {

	private $assets;

	private $request;

	public function __construct( AssetsApi $assets, FrontendRequests $request ) {
		$this->assets  = $assets;
		$this->request = $request;
	}

	public function initialize() {
		add_action( 'init', [ $this, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'woocommerce_blocks_enqueue_checkout_block_scripts_after', [ $this, 'enqueue_blocks_scripts' ] );
	}

	public function register_scripts() {
		$this->assets->register_script( 'wc-stripe-subscriptions-checkout', 'build/checkout.js' );
		$this->assets->register_script( 'wc-stripe-subscriptions-blocks-checkout', 'build/blocks-checkout.js' );
	}

	/**
	 * Gateways that don't support 'subscriptions' (@see WooCommerceSubscriptionsTrait) still get
	 * mode: 'subscription' from PaymentIntent::is_subscription_mode(), since that decision is made
	 * without knowing which specific gateway will render. Manual renewals don't require those
	 * gateways to support automatic reuse, so these scripts fall back to mode: 'payment' with the
	 * real total for them on the client, rather than teaching the core gateway classes about
	 * subscriptions. Only enqueued when manual renewals are enabled and the cart/order actually
	 * contains a subscription.
	 */
	public function enqueue_scripts() {
		if ( ! $this->is_manual_renewal_enabled() ) {
			return;
		}
		if ( $this->request->is_checkout_with_subscription() || $this->request->is_order_pay_with_subscription() ) {
			wp_enqueue_script( 'wc-stripe-subscriptions-checkout' );
		}
	}

	/**
	 * Same fix as enqueue_scripts(), for the Checkout block. Hooked to the block's own script-enqueue
	 * lifecycle action (fired only while it's actually rendering) rather than wp_enqueue_scripts, so
	 * no separate "is this the checkout block" check is needed. is_checkout() returns true for the
	 * checkout page regardless of block vs shortcode, so enqueue_scripts() will have also enqueued
	 * the classic script - dequeue it here since it's unused/wasted on the block checkout.
	 */
	public function enqueue_blocks_scripts() {
		if ( ! $this->is_manual_renewal_enabled() ) {
			return;
		}
		if ( $this->request->is_checkout_with_subscription() || $this->request->is_order_pay_with_subscription() ) {
			wp_enqueue_script( 'wc-stripe-subscriptions-blocks-checkout' );
			$this->dequeue_classic_script_on_render();
		}
	}

	/**
	 * Block themes render the checkout block's callback (and this action) before wp_enqueue_scripts
	 * fires, so the dequeue must be deferred to a later priority on that same hook. Classic themes
	 * that embed the block inline via the_content() render it after wp_enqueue_scripts has already
	 * fired (and after enqueue_scripts() already ran) - deferring there would never fire, so dequeue
	 * immediately instead. Mirrors PaymentGatewaysController::dequeue_scripts_on_render().
	 */
	private function dequeue_classic_script_on_render() {
		add_action( 'wp_enqueue_scripts', function () {
			wp_dequeue_script( 'wc-stripe-subscriptions-checkout' );
		}, 20 );
	}

	private function is_manual_renewal_enabled() {
		return function_exists( 'wcs_is_manual_renewal_enabled' ) && wcs_is_manual_renewal_enabled();
	}
}