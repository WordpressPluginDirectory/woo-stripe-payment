<?php

namespace PaymentPlugins\Stripe\WooCommerceProductAddons;

use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\Packages\AbstractPackage;

/**
 * @package PaymentPlugins\WooCommerceProductAddons\Stripe
 */
class Package extends AbstractPackage {

	public $id = 'woocommerce_product_addons';

	public function is_active() {
		return \function_exists( 'woocommerce_product_addons_activation' );
	}

	public function register() {
		$this->container->register( FrontendScripts::class, function ( $container ) {
			return new FrontendScripts(
				new AssetsApi(
					dirname( __DIR__ ) . '/',
					trailingslashit( plugin_dir_url( __DIR__ ) ),
					stripe_wc()->version()
				)
			);
		} );
	}

	public function initialize() {
		$this->container->get( FrontendScripts::class )->initialize();
	}
}