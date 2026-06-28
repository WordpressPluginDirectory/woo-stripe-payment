<?php


namespace PaymentPlugins\Stripe\Blocks;

use Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\StoreApi;
use PaymentPlugins\Stripe\Blocks\Payments\PaymentsApi;
use PaymentPlugins\Stripe\Blocks\Payments\CreditCardPayment;
use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\Blocks\StoreApi\SchemaController;
use PaymentPlugins\Stripe\Container\Container;

class Config {

	private $version;

	private $container;

	private $path;

	private $url;

	/**
	 * Init constructor.
	 *
	 * @param string    $version
	 * @param Container $container
	 * @param string    $path
	 */
	public function __construct( $version, Container $container, $path ) {
		$this->version   = $version;
		$this->container = $container;
		$this->path      = $path;
		$this->url       = plugin_dir_url( $this->path . DIRECTORY_SEPARATOR . 'src' );
	}

	public function get_url( $relative_path = '' ) {
		return $this->url . $relative_path;
	}

	public function get_path( $relative_path ) {
		return trailingslashit( $this->path ) . $relative_path;
	}

	public function get_plugin_path() {
		return stripe_wc()->plugin_path();
	}

	public function get_version() {
		return $this->version;
	}

}