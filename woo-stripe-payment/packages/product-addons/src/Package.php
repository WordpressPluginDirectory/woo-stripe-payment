<?php

namespace PaymentPlugins\Stripe\ProductAddons;

use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\Packages\AbstractPackage;

class Package extends AbstractPackage {

	public $id = 'product-addons';

	public function is_active() {
		return function_exists( 'product_addons' ) && function_exists( 'prad_init' );
	}

	public function register() {
		$this->container->register( FrontendScripts::class, function ( $container ) {
			return new FrontendScripts(
				new AssetsApi(
					dirname( __DIR__ ) . '/',
					trailingslashit( plugin_dir_url( __DIR__ ) ),
					$container->get( 'VERSION' )
				)
			);
		} );
	}

	public function initialize() {
		$this->container->get( FrontendScripts::class )->initialize();
	}
}