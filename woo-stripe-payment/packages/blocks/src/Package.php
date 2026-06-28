<?php


namespace PaymentPlugins\Stripe\Blocks;


use Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\StoreApi;
use PaymentPlugins\Stripe\Blocks\Payments\PaymentsApi;
use PaymentPlugins\Stripe\Blocks\StoreApi\SchemaController;
use PaymentPlugins\Stripe\Container\Container;
use PaymentPlugins\Stripe\Packages\AbstractPackage;

class Package extends AbstractPackage {

	public $id = 'blocks';

	public function is_active() {
		return \class_exists( '\Automattic\WooCommerce\Blocks\Package' );
	}

	public function register() {
		$this->container->register( 'BLOCK_ASSETS', function ( $container ) {
			return new \PaymentPlugins\Stripe\Assets\AssetsApi(
				dirname( __DIR__ ) . '/',
				plugin_dir_url( __DIR__ ),
				$container->get( 'VERSION' )
			);
		} );
		$this->container->register( FrontendScripts::class, function ( $container ) {
			return new FrontendScripts( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( PaymentsApi::class, function ( Container $container ) {
			return new PaymentsApi(
				$container,
				\Automattic\WooCommerce\Blocks\Package::container()->get( AssetDataRegistry::class )
			);
		} );
		$this->container->register( Payments\Gateways\Link\Controller::class, function () {
			return new Payments\Gateways\Link\Controller();
		} );
		$this->container->register( SchemaController::class, function ( $container ) {
			return new SchemaController(
				StoreApi::container()->get( ExtendSchema::class ),
				$container->get( PaymentsApi::class )
			);
		} );
		$this->container->register( BlocksController::class, function ( $container ) {
			return new BlocksController( $container );
		} );
	}

	public function initialize() {
		/**
		 * We use the woocommerce_blocks_loaded action because WooCommerce blocks is loaded in the plugins_loaded
		 * action. This action ensures the blocks are loaded.
		 */
		add_action( 'woocommerce_blocks_loaded', function () {
			$this->container->get( PaymentsApi::class );
			$this->container->get( SchemaController::class );
			$this->container->get( BlocksController::class )->initialize();
			$this->container->get( FrontendScripts::class )->initialize();
		} );
	}
}