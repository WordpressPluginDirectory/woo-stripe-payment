<?php

namespace PaymentPlugins\Stripe\WooCommerceSubscriptions;

use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\ContextHandler;
use PaymentPlugins\Stripe\Packages\AbstractPackage;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;
use PaymentPlugins\Stripe\WooCommerceSubscriptions\Controllers\ChangePaymentGatewayController;
use PaymentPlugins\Stripe\WooCommerceSubscriptions\Controllers\OrderMetadata;
use PaymentPlugins\Stripe\WooCommerceSubscriptions\Controllers\PaymentIntent;

/**
 * @package PaymentPlugins\WooCommerceSubscriptions\Stripe
 */
class Package extends AbstractPackage {

	public $id = 'woocommerce_subscriptions';

	public function is_active() {
		return \function_exists( 'wcs_is_subscription' );
	}

	public function register() {
		$this->container->register( FrontendRequests::class, function ( $container ) {
			return new FrontendRequests( $container->get( ContextHandler::class ) );
		} );
		$this->container->register( PaymentIntent::class, function ( $container ) {
			return new PaymentIntent( $container->get( FrontendRequests::class ) );
		} );
		$this->container->register( OrderMetadata::class, function ( $container ) {
			return new OrderMetadata();
		} );
		$this->container->register( ChangePaymentGatewayController::class, function ( $container ) {
			return new ChangePaymentGatewayController();
		} );
		$this->container->register( SubscriptionsController::class, function ( $container ) {
			return new SubscriptionsController(
				new PaymentController(),
				$container->get( ContextHandler::class ),
				$container->get( PaymentGatewayRegistry::class )
			);
		} );
		$this->container->register( FrontendScripts::class, function ( $container ) {
			return new FrontendScripts(
				new AssetsApi( dirname( __DIR__ ) . '/', trailingslashit( plugin_dir_url( __DIR__ ) ), stripe_wc()->version() ),
				$container->get( FrontendRequests::class )
			);
		} );
	}

	public function initialize() {
		$this->container->get( PaymentIntent::class );
		$this->container->get( OrderMetadata::class );
		$this->container->get( SubscriptionsController::class )->initialize();
		$this->container->get( ChangePaymentGatewayController::class )->initialize();
		$this->container->get( FrontendScripts::class )->initialize();
	}
}