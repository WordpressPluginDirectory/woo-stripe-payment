<?php

defined( 'ABSPATH' ) || exit();

/**
 * Singleton class that handles plugin functionality like class loading.
 *
 * @since   3.0.0
 * @author  PaymentPlugins
 * @package PaymentPlugins\Classes
 *
 * @property string                      $version
 * @property WC_Stripe_API_Settings      $api_settings
 * @property WC_Stripe_Advanced_Settings $advanced_settings
 * @property WC_Stripe_Account_Settings  $account_settings
 *
 */
class WC_Stripe_Manager {

	public static $_instance;

	public static function instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 *
	 * @var WC_Stripe_Rest_API
	 */
	public $rest_api;

	/**
	 *
	 * @var string
	 */
	public $client_id = 'ca_Gp4vLOJiqHJLZGxakHW7JdbBlcgWK8Up';

	/**
	 * Test client id;
	 *
	 * @var string
	 */
	//public $client_id = 'ca_Gp4vL3V6FpTguYoZIehD5COPeI80rLpV';

	/**
	 *
	 * @var WC_Stripe_Frontend_Scripts
	 */
	private $scripts;

	private $dependecies_loaded = false;

	public function __get( $key ) {
		if ( $key === 'version' ) {
			return wc_stripe_get_container()->get( 'VERSION' );
		} elseif ( $key === 'api_settings' ) {
			return wc_stripe_get_container()->get( WC_Stripe_API_Settings::class );
		} elseif ( $key === 'account_settings' ) {
			return wc_stripe_get_container()->get( WC_Stripe_Account_Settings::class );
		} elseif ( $key === 'advanced_settings' ) {
			return wc_stripe_get_container()->get( WC_Stripe_Advanced_Settings::class );
		}
	}

	/**
	 * Return the plugin version.
	 *
	 * @return string
	 */
	public function version() {
		return $this->version;
	}

	/**
	 * Return the url for the plugin assets.
	 *
	 * @return string
	 */
	public function assets_url( $uri = '' ) {
		$url = WC_STRIPE_ASSETS . $uri;
		if ( ! preg_match( '/(\.js)|(\.css)|(\.svg)|(\.png)/', $uri ) ) {
			return trailingslashit( $url );
		}

		return $url;
	}

	/**
	 * Return the dir path for the plugin.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return WC_STRIPE_PLUGIN_FILE_PATH;
	}

	public function plugins_loaded() {

	}

	/**
	 * Function that is hooked in to the WordPress init action.
	 */
	public function init() {
	}

	public function includes() {

	}

	/**
	 * Function that is hooked in to the WordPress admin_init action.
	 */
	public function admin_init() {
	}

	public function woocommerce_dependencies() {
		$this->dependecies_loaded = true;
	}

	/**
	 * Initializes the REST API. This method is called after WordPress has initialized
	 * the rewrite rules to prevent errors when get_rest_url() is called.
	 *
	 * @return void
	 * @since 3.3.94
	 * @deprecated
	 */
	public function init_rest_api() {

	}

	/**
	 * Return the plugin template path.
	 */
	public function template_path() {
		return 'woo-stripe-payment';
	}

	/**
	 * Return the plguins default directory path for template files.
	 */
	public function default_template_path() {
		return WC_STRIPE_PLUGIN_FILE_PATH . 'templates/';
	}

	/**
	 *
	 * @return string
	 */
	public function rest_uri() {
		return 'wc-stripe/v1/';
	}

	/**
	 *
	 * @return string
	 */
	public function rest_url() {
		return get_rest_url( null, $this->rest_uri() );
	}

	/**
	 *
	 * @return WC_Stripe_Frontend_Scripts
	 */
	public function scripts() {
		if ( is_null( $this->scripts ) ) {
			$this->scripts = wc_stripe_get_container()->get( \WC_Stripe_Frontend_Scripts::class );
		}

		return $this->scripts;
	}

	/**
	 * @return \PaymentPlugins\Stripe\Assets\AssetsApi
	 * @throws Exception
	 */
	public function assets() {
		static $assets;
		if ( is_null( $assets ) ) {
			$assets = wc_stripe_get_container()->get( \PaymentPlugins\Stripe\Assets\AssetsApi::class );
		}

		return $assets;
	}

	public function data_api() {
		static $data_api;
		if ( is_null( $data_api ) ) {
			$data_api = wc_stripe_get_container()->get( \PaymentPlugins\Stripe\Assets\AssetDataApi::class );
		}

		return $data_api;
	}

	public function payment_gateways() {
		/**
		 * @var \PaymentPlugins\Stripe\Payments\PaymentGatewaysController $controller
		 */
		$controller = wc_stripe_get_container()->get( \PaymentPlugins\Stripe\Payments\PaymentGatewaysController::class );

		return $controller->get_payment_gateway_classes();
	}

	/**
	 * Schedule actions required by the plugin
	 *
	 * @since 3.1.6
	 */
	public function scheduled_actions() {
		if ( function_exists( 'WC' ) ) {
			if ( method_exists( WC(), 'queue' ) && ! WC()->queue()->get_next( 'wc_stripe_remove_order_locks' ) ) {
				WC()->queue()->schedule_recurring( strtotime( 'today midnight' ), DAY_IN_SECONDS, 'wc_stripe_remove_order_locks' );
			}
		}
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 * @since 3.1.9
	 */
	public function is_request( $type ) {
		if ( ! did_action( 'before_woocommerce_init' ) ) {
			return false;
		}
		switch ( $type ) {
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! WC_Stripe_Rest_API::is_wp_rest_request();
			default:
				return true;
		}
	}

}

/**
 * Returns the global instance of the WC_Stripe_Manager. This function replaces
 * the wc_stripe function as of version 3.2.8
 *
 * @return WC_Stripe_Manager
 * @since   3.2.8
 * @package PaymentPlugins\Functions
 */
function stripe_wc() {
	return WC_Stripe_Manager::instance();
}


// load singleton
stripe_wc();
