<?php

namespace PaymentPlugins\Stripe\CheckoutWC;

use PaymentPlugins\Stripe\CheckoutWC\OrderBumps\OrderBumpsController;
use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\Checkout\ExpressCheckoutRenderer;
use PaymentPlugins\Stripe\Packages\AbstractPackage;

class Package extends AbstractPackage {

	public $id = 'checkoutwc';

	public function is_active() {
		return defined( 'CFW_NAME' );
	}

	public function register() {
		$this->container->register( AssetsController::class, function ( $container ) {
			return new AssetsController(
				new AssetsApi(
					dirname( __DIR__ ) . '/',
					plugin_dir_url( __DIR__ ),
					$container->get( 'VERSION' )
				),
				$container->get( 'VERSION' ),
				plugin_dir_url( __DIR__ )
			);
		} );
		$this->container->register( OrderBumpsController::class, function () {
			return new OrderBumpsController( __DIR__ );
		} );

		add_action( 'wp', function () {
			if ( function_exists( 'cfw_is_checkout' ) && cfw_is_checkout() || is_checkout_pay_page() ) {
				$renderer = wc_stripe_get_container()->get( ExpressCheckoutRenderer::class );
				remove_action(
					'woocommerce_checkout_before_customer_details',
					[
						$renderer,
						'render_express_checkout'
					]
				);
			}
		} );
	}

	public function initialize() {
		$this->container->get( AssetsController::class )->initialize();
		$this->container->get( OrderBumpsController::class )->initialize();
	}
}