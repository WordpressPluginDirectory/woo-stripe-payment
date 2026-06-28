<?php

namespace PaymentPlugins\Stripe\Packages;

use PaymentPlugins\Stripe\Container\Container;

class PackagesController {

	private $registry;

	private $packages = [];

	public function __construct( PackageRegistry $registry, $packages ) {
		$this->registry = $registry;
		$this->packages = $packages;
	}

	public function initialize() {
		add_action( 'woocommerce_stripe_packages_registration', [ $this, 'register_packages' ], 10, 2 );
		add_action( 'woocommerce_loaded', [ $this, 'init_packages' ] );
	}

	public function register_packages( PackageRegistry $registry, Container $container ) {
		foreach ( $this->packages as $package_name ) {
			if ( $container->has( $package_name ) ) {
				$package = $container->get( $package_name );
				$registry->register( $package );
				if ( $package->is_active() ) {
					$package->register();
				}
			}
		}
	}

	public function init_packages() {
		foreach ( $this->registry->get_registered_integrations() as $integration_name => $integration ) {
			if ( $integration->is_active() ) {
				$integration->initialize();
			}
		}
	}
}