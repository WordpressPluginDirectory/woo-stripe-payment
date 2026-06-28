<?php

namespace PaymentPlugins\Stripe\WooCommerceTMExtraProductOptions;

use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\Packages\AbstractPackage;
use PaymentPlugins\Stripe\WooCommerceProductAddons\FrontendScripts;

/**
 * @package PaymentPlugins\WooCommerceExtraProductOptions\Stripe
 */
class Package extends AbstractPackage {

	public $id = 'woocommerce_extra_product_options';

	public function is_active() {
		return \defined( 'THEMECOMPLETE_EPO_PLUGIN_FILE' );
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