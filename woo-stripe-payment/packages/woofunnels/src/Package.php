<?php

namespace PaymentPlugins\Stripe\WooFunnels;

use PaymentPlugins\Stripe\Packages\AbstractPackage;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;
use PaymentPlugins\Stripe\WooFunnels\Checkout\Compatibility\ExpressButtonController;
use PaymentPlugins\Stripe\WooFunnels\Upsell\PaymentGateways;

class Package extends AbstractPackage {

	public $id = 'funnelkit';

	public function is_active() {
		return function_exists( 'WFOCU_Core' );
	}

	public function register() {
		$this->container->register( PaymentGateways::class, function () {
			return new PaymentGateways(
				new AssetsApi( __DIR__, stripe_wc()->version() )
			);
		} );
		$this->container->register( ExpressButtonController::class, function ( $container ) {
			return new ExpressButtonController(
				new AssetsApi( __DIR__, stripe_wc()->version() ),
				$container->get( PaymentGatewayRegistry::class )
			);
		} );
	}

	public function initialize() {
		$this->container->get( PaymentGateways::class )->initialize();

		if ( class_exists( 'WFACP_Core' ) ) {
			$this->container->get( ExpressButtonController::class )->initialize();
		}
	}
}