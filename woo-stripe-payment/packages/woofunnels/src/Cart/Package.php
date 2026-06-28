<?php

namespace PaymentPlugins\Stripe\WooFunnels\Cart;

use PaymentPlugins\Stripe\Checkout\ExpressCheckoutRenderer;
use PaymentPlugins\Stripe\Packages\AbstractPackage;

class Package extends AbstractPackage {

	public $id = 'funnelkit_cart';

	public function is_active() {
		return class_exists( '\FKCart\Plugin' );
	}

	public function register() {
		$this->container->register( CartIntegration::class, function ( $container ) {
			return new CartIntegration(
				$container->get( ExpressCheckoutRenderer::class )
			);
		} );
	}

	public function initialize() {
		$this->container->get( CartIntegration::class )->initialize();
	}
}