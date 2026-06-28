<?php

namespace PaymentPlugins\Stripe\AdvancedProductFieldsForWooCommerce;

use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\Packages\AbstractPackage;

class Package extends AbstractPackage {

	public $id = 'advanced-product-fields-for-woocommerce';

	public function is_active() {
		return function_exists( 'wapf' );
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