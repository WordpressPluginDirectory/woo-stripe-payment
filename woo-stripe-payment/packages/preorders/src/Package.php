<?php

namespace PaymentPlugins\Stripe\WooCommercePreOrders;

use PaymentPlugins\Stripe\ContextHandler;
use PaymentPlugins\Stripe\Packages\AbstractPackage;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;
use PaymentPlugins\Stripe\WooCommercePreOrders\Controllers\PaymentIntent;

/**
 * @package PaymentPlugins\WooCommercePreOrders\Stripe
 */
class Package extends AbstractPackage {

	public $id = 'preorders';

	public function is_active() {
		return class_exists( 'WC_Pre_Orders' );
	}

	public function register() {
		$this->container->register( PreOrdersController::class, function ( $container ) {
			return new PreOrdersController(
				new PaymentController(),
				$container->get( ContextHandler::class ),
				$container->get( PaymentGatewayRegistry::class )
			);
		} );
		$this->container->register( PaymentIntent::class, function ( $container ) {
			new PaymentIntent( new FrontendRequests() );
		} );
	}

	public function initialize() {
		$this->container->get( PaymentIntent::class );
		$this->container->get( PreOrdersController::class )->initialize();
	}
}