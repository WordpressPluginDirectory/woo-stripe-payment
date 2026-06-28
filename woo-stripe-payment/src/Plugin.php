<?php

namespace PaymentPlugins\Stripe;

use PaymentPlugins\Stripe\Container\Container;

/**
 * Main Plugin Bootstrap Class
 *
 * Manages the dependency injection container and plugin initialization.
 *
 * @since 4.0.0
 */
class Plugin {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Plugin file path.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Plugin directory path.
	 *
	 * @var string
	 */
	private $plugin_path;

	/**
	 * Plugin basename.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Container instance.
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * @var ServiceProvider
	 */
	private $service_provider;

	/**
	 * Constructor.
	 *
	 * @param string $version Plugin version.
	 * @param string $file Main plugin file path.
	 */
	public function __construct( $version, $plugin_file ) {
		$this->version     = $version;
		$this->plugin_file = $plugin_file;
		$this->plugin_path = plugin_dir_path( $plugin_file );
		$this->plugin_name = plugin_basename( $plugin_file );
		$this->container   = self::container();

		// Register early dependencies (no WooCommerce dependency)
		$this->register_constants();
		$this->register_classes();
		$this->critical_hooks();

		// Hook into WordPress lifecycle
		add_action( 'woocommerce_loaded', function () {
			$this->load_text_domain();
			$this->service_provider->do_woocommerce_loaded();
		}, 5 );

		add_action( 'woocommerce_init', function () {
			$this->service_provider->do_woocommerce_init();
		} );
	}

	/**
	 * Get the singleton container instance.
	 *
	 * @return Container
	 */
	public static function container() {
		static $container;
		if ( ! $container ) {
			$container = new Container();
		}

		return $container;
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function version() {
		return $this->version;
	}

	/**
	 * Register early dependencies (no WooCommerce dependency required).
	 *
	 * These services can be registered before WooCommerce is loaded.
	 *
	 * @return void
	 */
	private function register_constants() {
		// Register plugin version as a simple value
		$this->container->register( 'VERSION', $this->version );
		$this->container->register( 'API_VERSION', '2026-02-25.clover' );
		$this->container->register( 'CLIENT_ID', 'ca_Gp4vLOJiqHJLZGxakHW7JdbBlcgWK8Up' );

		// Register plugin paths
		$this->container->register( 'PLUGIN_FILE', $this->plugin_file );
		$this->container->register( 'PLUGIN_PATH', $this->plugin_path );
		$this->container->register( 'PLUGIN_NAME', $this->plugin_name );

		define( 'WC_STRIPE_PLUGIN_FILE_PATH', $this->plugin_path );
		define( 'WC_STRIPE_ASSETS', plugin_dir_url( $this->plugin_file ) . 'assets/' );
		define( 'WC_STRIPE_PLUGIN_NAME', $this->plugin_name );

		// Example: Register a logger (if you have one)
		// $this->container->register( Logger::class, function ( $container ) {
		//     return new Logger( 'wc-stripe' );
		// } );
	}

	/**
	 * Register classes used by the Container.
	 *
	 * These services require WooCommerce to be loaded.
	 *
	 * @return void
	 */
	private function register_classes() {
		require_once( WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-stripe.php' );

		$this->service_provider = new ServiceProvider( $this->container );
		$this->service_provider->initialize();
	}

	/**
	 * These are hooks critical to the Stripe plugin. We register them here because some 3rd party plugins can trigger these actions
	 * very early which makes it necessary to register as soon as possible.
	 * @return void
	 */
	private function critical_hooks() {
		// Hook that is used to declare feature compatibility with WooCommerce
		add_action( 'before_woocommerce_init', function () {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				try {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->plugin_path . 'stripe-payments.php', true );
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $this->plugin_path . 'stripe-payments.php', true );
				} catch ( \Exception $e ) {
				}
			}
		} );
	}

	private function load_text_domain() {
		load_plugin_textdomain( 'woo-stripe-payment', false, dirname( $this->plugin_name ) . '/i18n/languages' );
	}

}