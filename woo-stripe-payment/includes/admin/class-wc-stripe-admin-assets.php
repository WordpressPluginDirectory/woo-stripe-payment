<?php

defined( 'ABSPATH' ) || exit();

use \PaymentPlugins\Stripe\Assets\AssetsApi;

/**
 *
 * @package PaymentPlugins\Admin
 */
class WC_Stripe_Admin_Assets {

	public function __construct() {
		add_action( 'admin_head', array( $this, 'add_inline_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_print_scripts', array( __CLASS__, 'localize_scripts' ) );
		add_action( 'admin_footer', array( __CLASS__, 'localize_scripts' ) );
		add_action( 'wc_stripe_localize_stripe_advanced_settings', array( __CLASS__, 'localize_advanced_scripts' ) );
	}

	public function add_inline_styles() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		if ( strpos( $screen_id, 'wc-settings' ) !== false ) {
			if ( isset( $_REQUEST['section'] ) && preg_match( '/stripe_[\w]*/', $_REQUEST['section'] ) ) {
				?>
                <style>
                    body[class*="woocommerce_page_wc-settings-checkout-section-stripe_"] .woocommerce-layout__header {
                        display: none;
                    }

                    body[class*="woocommerce_page_wc-settings-checkout-section-stripe_"] .woo-nav-tab-wrapper {
                        display: none !important;
                    }

                    #wpcontent #wpbody {
                        margin-top: 0;
                    }
                </style>
				<?php
			}
		}
	}

	public function enqueue_scripts() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		$js_path   = stripe_wc()->assets_url() . 'js/';
		$css_path  = stripe_wc()->assets_url() . 'css/';

		/**
		 * @var AssetsApi $assets
		 */
		$assets = wc_stripe_get_container()->get( AssetsApi::class );

		$assets->register_script( 'wc-stripe-admin-dashboard-vendors', 'build/wc-stripe-admin-dashboard-vendors.js' );

		$assets->register_script( 'wc-stripe-admin-help-widget', 'build/admin-help-widget.js' );

		$assets->register_script( 'wc-stripe-admin-settings', 'build/admin-settings.js', array(
			wc_stripe_get_script_handle( 'jquery-blockui' ),
			'wc-backbone-modal'
		) );

		$assets->register_script(
			'wc-stripe-product-data',
			'build/meta-boxes-product-data.js',
			[
				wc_stripe_get_script_handle( 'jquery-blockui' ),
				'jquery-ui-sortable',
				'jquery-ui-widget',
				'jquery-ui-core',
				wc_stripe_get_script_handle( 'jquery-tiptip' )
			]
		);

		$assets->register_script(
			'wc-stripe-admin-order-metabox',
			'build/meta-boxes-order.js',
			[
				'wc-stripe-admin-modals',
				wc_stripe_get_script_handle( 'jquery-blockui' ),

			]
		);

		$assets->register_script(
			'wc-stripe-admin-modals',
			'build/admin-modals.js',
			[
				'jquery',
				'wc-backbone-modal',
				'wc-stripe-utils',
				wc_stripe_get_script_handle( 'jquery-blockui' )
			]
		);

		$assets->register_style( 'wc-stripe-admin-style', 'build/admin.css' );

		$assets->register_style( 'wc-stripe-admin-dashboard', 'build/dashboard.css' );

		wp_register_style( 'wc-stripe-admin-main-style', $css_path . 'admin/main.css', array( 'woocommerce_admin_styles' ), stripe_wc()->version );

		if ( strpos( $screen_id, 'wc-settings' ) !== false ) {
			if ( isset( $_REQUEST['section'] ) && preg_match( '/stripe_[\w]*/', $_REQUEST['section'] ) ) {
				wp_enqueue_script( 'wc-stripe-admin-settings' );
				wp_enqueue_script( 'wc-stripe-admin-help-widget' );

				wp_enqueue_style( 'wc-stripe-admin-style' );
				wp_enqueue_style( 'wc-stripe-admin-dashboard' );
				wp_style_add_data( 'wc-stripe-admin-style', 'rtl', 'replace' );

				$user = wp_get_current_user();

				ob_start();
				include stripe_wc()->plugin_path() . 'includes/admin/views/html-help-widget.php';

				$help_widget = ob_get_clean();


				wp_localize_script(
					'wc-stripe-admin-settings',
					'wc_stripe_setting_params',
					array(
						'routes'      => array(
							'register_domain'               => WC_Stripe_Rest_API::get_admin_endpoint( '/wc-stripe/v1/gateway-settings/register-domain' ),
							'create_webhook'                => WC_Stripe_Rest_API::get_admin_endpoint( '/wc-stripe/v1/gateway-settings/create-webhook' ),
							'delete_webhook'                => WC_Stripe_Rest_API::get_admin_endpoint( '/wc-stripe/v1/gateway-settings/delete-webhook' ),
							'connection_test'               => WC_Stripe_Rest_API::get_admin_endpoint( '/wc-stripe/v1/gateway-settings/connection-test' ),
							'delete_connection'             => WC_Stripe_Rest_API::get_admin_endpoint( '/wc-stripe/v1/gateway-settings/delete-connection' ),
							'fetch_payment_method_config'   => WC_Stripe_Rest_API::get_admin_endpoint( '/wc-stripe/v1/gateway-settings/fetch-payment-config' ),
							'create_payment_method_config'  => WC_Stripe_Rest_API::get_admin_endpoint( '/wc-stripe/v1/gateway-settings/create-payment-config' ),
							'refresh_payment_method_config' => WC_Stripe_Rest_API::get_admin_endpoint( '/wc-stripe/v1/gateway-settings/refresh-payment-config' )
						),
						'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
						'messages'    => array(
							'delete_connection' => __( 'Are you sure you want to delete your connection data?', 'woo-stripe-payment' ),
							'create'            => __( 'Creating', 'woo-stripe-payment' ),
							'upm_validation'    => __( 'Please select or create a payment method configuration', 'woo-stripe-payment' )
						),
						'user'        => [
							'name'  => $user->get( 'first_name' ) . ' ' . $user->get( 'last_name' ),
							'email' => $user->get( 'user_email' )
						],
						'help_widget' => $help_widget
					)
				);
			}
		}
		if ( $screen_id === 'shop_order' || $screen_id === 'woocommerce_page_wc-orders' ) {
			wp_enqueue_style( 'wc-stripe-admin-style' );
		}
		if ( $screen_id === 'product' ) {
			wp_enqueue_script( 'wc-stripe-product-data' );
			wp_enqueue_style( 'wc-stripe-admin-style' );
			wp_localize_script(
				'wc-stripe-product-data',
				'wc_stripe_product_params',
				array(
					'_wpnonce' => wp_create_nonce( 'wp_rest' ),
					'routes'   => array(
						'enable_gateway' => \WC_Stripe_Rest_API::get_admin_endpoint( 'wc-stripe/v1/product/gateway' ),
						'save'           => \WC_Stripe_Rest_API::get_admin_endpoint( 'wc-stripe/v1/product/save' ),
					),
				)
			);
		}
		if ( $screen_id === 'woocommerce_page_wc-stripe-main' ) {
			wp_enqueue_style( 'wc-stripe-admin-main-style' );
			if ( isset( $_GET['section'] ) ) {
				if ( $_GET['section'] === 'support' ) {
					wp_enqueue_script( 'wc-stripe-admin-help-widget' );
				}
			}
		}
	}

	public static function localize_scripts() {
		global $current_section, $wc_stripe_subsection;
		if ( ! empty( $current_section ) ) {
			$wc_stripe_subsection = isset( $_GET['sub_section'] ) ? sanitize_title( $_GET['sub_section'] ) : '';
			do_action( 'wc_stripe_localize_' . $current_section . '_settings' );
			// added for WC 3.0.0 compatability.
			remove_action( 'admin_footer', array( __CLASS__, 'localize_scripts' ) );
		}
	}

	public static function localize_advanced_scripts() {
		global $current_section, $wc_stripe_subsection;
		do_action( 'wc_stripe_localize_' . $wc_stripe_subsection . '_settings' );
	}

}
