<?php

namespace PaymentPlugins\Stripe\CartFlows;

use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\Packages\AbstractPackage;

class Package extends AbstractPackage {

	public $id = 'cartflows';

	public function is_active() {
		return defined( 'CARTFLOWS_FILE' );
	}

	public function register() {
		$this->container->register( 'CARTFLOWS_ASSETS_API', function ( $container ) {
			return new AssetsApi(
				dirname( __DIR__ ) . '/',
				plugin_dir_url( __DIR__ ) . '/',
				$container->get( 'VERSION' )
			);
		} );
		$this->container->register( PaymentsApi::class, function ( $container ) {
			return new PaymentsApi( $container->get( 'CARTFLOWS_ASSETS_API' ) );
		} );
		$this->container->register( FrontedScripts::class, function ( $container ) {
			return new FrontedScripts(
				$container->get( 'CARTFLOWS_ASSETS_API' )
			);
		} );
	}

	public function initialize() {
		$this->container->get( PaymentsApi::class )->initialize();
		$this->container->get( FrontedScripts::class )->initialize();
	}
}