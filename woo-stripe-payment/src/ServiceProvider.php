<?php

namespace PaymentPlugins\Stripe;

use PaymentPlugins\Stripe\Admin\AdminPageController;
use PaymentPlugins\Stripe\Admin\DashboardPage;
use PaymentPlugins\Stripe\Assets\AssetDataApi;
use PaymentPlugins\Stripe\Assets\AssetDataController;
use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\Checkout\ExpressCheckoutRenderer;
use PaymentPlugins\Stripe\Client\StripeClient;
use PaymentPlugins\Stripe\Container\Container;
use PaymentPlugins\Stripe\Controllers\PaymentIntentController;
use PaymentPlugins\Stripe\Installments\InstallmentController;
use PaymentPlugins\Stripe\Messages\BNPL\BNPLMessageController;
use PaymentPlugins\Stripe\Messages\MessageController;
use PaymentPlugins\Stripe\Orders\OrderAttributionController;
use PaymentPlugins\Stripe\Packages\PackageRegistry;
use PaymentPlugins\Stripe\Packages\PackagesController;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;
use PaymentPlugins\Stripe\Payments\PaymentGatewaysController;
use PaymentPlugins\Stripe\PaymentTokens\PaymentTokenController;
use PaymentPlugins\Stripe\Rest\AdminRestController;
use PaymentPlugins\Stripe\Rest\RestController;
use PaymentPlugins\Stripe\Webhooks\DeferredWebhookHandler;
use PaymentPlugins\WC_Stripe_Admin_Meta_Box_Product_Data;

/**
 * Service Provider
 *
 * Handles registration of all services in the dependency injection container.
 * Organizes service registration by domain to keep things maintainable.
 *
 * @since 4.0.0
 */
class ServiceProvider {

	private $container;

	private $dependencies_loaded = false;

	public function __construct( Container $container ) {
		$this->container = $container;
	}

	public function initialize() {
		$this->register();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * These classes have hooks that need to be registered.
	 * @return void
	 * @throws \Exception
	 */
	private function init_hooks() {
		$this->container->get( RestController::class )->initialize();
		$this->container->get( AdminRestController::class )->initialize();
		$this->container->get( FrontendScripts::class )->initialize();
		$this->container->get( PackagesController::class )->initialize();
		$this->container->get( AssetDataController::class )->initialize();
		$this->container->get( PaymentGatewaysController::class )->initialize( $this );
		$this->container->get( BNPLMessageController::class )->initialize();
		$this->container->get( PaymentIntentController::class )->initialize();
		$this->container->get( MessageController::class )->initialize();
		$this->container->get( DeferredWebhookHandler::class )->initialize();
		$this->container->get( PaymentTokenController::class )->initialize();
		$this->container->get( OrderAttributionController::class )->initialize();
		$this->container->get( ExpressCheckoutRenderer::class )->initialize();

		if ( is_admin() ) {
			$this->container->get( AdminPageController::class )->initialize();
		}
	}

	/**
	 * Register all services in the container.
	 *
	 *
	 * @return void
	 */
	private function register() {
		$this->register_core_services();
		$this->register_rest_services();
		$this->register_payment_services();
		$this->register_packages();
		$this->register_admin_services();
	}

	private function includes() {
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/wc-stripe-functions.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/wc-stripe-webhook-functions.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/wc-stripe-hooks.php';

		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-constants.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-install.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-update.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-rest-api.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-api-operation.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-gateway.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-payment-balance.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-utils.php';

		if ( is_admin() ) {
			include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/admin/class-wc-stripe-admin-menus.php';
			include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/admin/class-wc-stripe-admin-welcome.php';
			include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/admin/class-wc-stripe-admin-assets.php';
			include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/admin/class-wc-stripe-admin-settings.php';
			include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/admin/meta-boxes/class-wc-stripe-admin-order-metaboxes.php';
			include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/admin/meta-boxes/class-wc-stripe-admin-meta-box-product-data.php';
		}

		$this->container->register( \WC_Stripe_Admin_Assets::class, function () {
			return new \WC_Stripe_Admin_Assets();
		} );
	}

	/**
	 * Register core services.
	 *
	 * Services that are fundamental to the plugin and used across multiple contexts.
	 *
	 * @return void
	 */
	private function register_core_services() {
		// TODO: Register core services
		$this->container->register( AssetsApi::class, function ( $container ) {
			return new AssetsApi(
				$container->get( 'PLUGIN_PATH' ) . 'assets/',
				plugin_dir_url( $container->get( 'PLUGIN_FILE' ) ) . 'assets/',
				$container->get( 'VERSION' )
			);
		} );
		$this->container->register( AssetDataApi::class, function ( $container ) {
			return new AssetDataApi();
		} );

		$this->container->register( \WC_Stripe_Rest_API::class, function ( $container ) {
			return apply_filters( 'wc_stripe_rest_api_class', new \WC_Stripe_Rest_API() );
		} );

		$this->container->register( \WC_Stripe_API_Request_Filter::class, function ( $container ) {
			return new \WC_Stripe_API_Request_Filter( $container->get( \WC_Stripe_Advanced_Settings::class ) );
		} );

		$this->container->register( \WC_Stripe_Customer_Manager::class, function ( $container ) {
			return new \WC_Stripe_Customer_Manager( $container->get( StripeClient::class ) );
		} );

		$this->container->register( \PaymentPlugins\Stripe\Link\LinkIntegration::class, function ( $container ) {
			return new \PaymentPlugins\Stripe\Link\LinkIntegration(
				$container->get( \WC_Stripe_Account_Settings::class )
			);
		} );

		$this->container->register( \WC_Stripe_Frontend_Scripts::class, function ( $container ) {
			return new \WC_Stripe_Frontend_Scripts( $container->get( AssetsApi::class ) );
		} );

		$this->container->register( \WC_Stripe_Gateway::class, function () {
			return \WC_Stripe_Gateway::load();
		} );

		$this->container->register( StripeClient::class, function () {
			return new StripeClient();
		}, false );

		$this->container->register( AssetDataController::class, function ( $container ) {
			return new AssetDataController(
				$container->get( AssetDataApi::class ),
				$container->get( ContextHandler::class ),
				$container->get( PaymentGatewayRegistry::class )
			);
		} );

		$this->container->register( FrontendScripts::class, function ( $container ) {
			return new FrontendScripts( $container->get( AssetsApi::class ) );
		} );

		$this->container->register( BNPLMessageController::class, function ( $container ) {
			return new BNPLMessageController(
				$container->get( PaymentGatewayRegistry::class ),
				$container->get( ContextHandler::class )
			);
		} );

		$this->container->register( PaymentTokenController::class, function ( $container ) {
			return new PaymentTokenController(
				$container->get( StripeClient::class ),
				$container->get( PaymentGatewayRegistry::class ),
			);
		} );

		$this->container->register( PaymentIntentController::class, function ( $container ) {
			return new PaymentIntentController();
		} );

		$this->container->register( MessageController::class, function ( $container ) {
			return new MessageController();
		} );

		$this->container->register( DeferredWebhookHandler::class, function ( $container ) {
			return new DeferredWebhookHandler();
		} );

		$this->container->register( InstallmentController::class, function ( $container ) {
			return new InstallmentController(
				$container->get( StripeClient::class ),
				$container->get( \WC_Stripe_Advanced_Settings::class ),
				$container->get( \WC_Stripe_Account_Settings::class )
			);
		} );

		$this->container->register( OrderAttributionController::class, function () {
			return new OrderAttributionController();
		} );

		$this->container->register( ExpressCheckoutRenderer::class, function ( $container ) {
			return new ExpressCheckoutRenderer( $container->get( PaymentGatewayRegistry::class ) );
		} );
	}

	/**
	 * Register rest api services
	 * @return void
	 */
	private function register_rest_services() {
		$this->container->register( AdminRestController::class, function ( $container ) {
			return new AdminRestController( [
				new Rest\Routes\V1\Admin\OrderActions( $container->get( StripeClient::class ) ),
				new Rest\Routes\V1\Admin\GatewaySettings( $container->get( StripeClient::class ) ),
				new Rest\Routes\V1\Admin\ProductData(),
			] );
		} );

		$this->container->register( RestController::class, function ( $container ) {
			return new RestController( [
				'cart/item'                => new Rest\Routes\V1\CartItem(),
				'cart/refresh'             => new Rest\Routes\V1\CartRefresh(),
				'cart/shipping'            => new Rest\Routes\V1\CartShipping(),
				'cart/checkout'            => new Rest\Routes\V1\CartCheckout(),
				'cart/calculation'         => new Rest\Routes\V1\CartCalculation(),
				'checkout/form-validation' => new Rest\Routes\V1\CheckoutFormValidation(),
				'setup-intent'             => new Rest\Routes\V1\SetupIntent(
					$container->get( PaymentGatewayRegistry::class )
				),
				'order/pay'                => new Rest\Routes\V1\OrderPay(),
				'webhook'                  => new Rest\Routes\V1\Webhook()
			], [ Rest\Routes\V1\Webhook::class ] );
		} );
	}

	/**
	 * Register payment-related services.
	 *
	 * Services related to payment processing, gateways, and transactions.
	 *
	 * @return void
	 */
	private function register_payment_services() {
		$container = $this->container;
		// Payment Gateway Registry
		$container->register( PaymentGatewayRegistry::class, function ( $container ) {
			return new PaymentGatewayRegistry( $container );
		} );

		$container->register( ContextHandler::class, function ( $container ) {
			return new ContextHandler();
		} );

		// Payment Gateways Controller
		$container->register( PaymentGatewaysController::class, function ( $container ) {
			return new PaymentGatewaysController(
				$container->get( PaymentGatewayRegistry::class ),
				$container->get( ContextHandler::class )
			);
		} );
	}

	/**
	 * Register 3rd party packages that the Stripe plugin integrates with.
	 * @return void
	 */
	private function register_packages() {
		$this->container->register( PackageRegistry::class, function ( $container ) {
			return new PackageRegistry( $container );
		} );
		$this->container->register( PackagesController::class, function ( $container ) {
			return new PackagesController(
				$container->get( PackageRegistry::class ),
				[
					\PaymentPlugins\Stripe\Blocks\Package::class,
					\PaymentPlugins\Stripe\CartFlows\Package::class,
					\PaymentPlugins\Stripe\WooFunnels\Package::class,
					\PaymentPlugins\Stripe\WooFunnels\Cart\Package::class,
					\PaymentPlugins\Stripe\CheckoutWC\Package::class,
					\PaymentPlugins\Stripe\GermanMarket\Package::class,
					\PaymentPlugins\Stripe\ProductAddons\Package::class,
					\PaymentPlugins\Stripe\WooCommercePreOrders\Package::class,
					\PaymentPlugins\Stripe\WooCommerceSubscriptions\Package::class,
					\PaymentPlugins\Stripe\WooCommerceProductAddons\Package::class,
					\PaymentPlugins\Stripe\WooCommerceTMExtraProductOptions\Package::class,
					\PaymentPlugins\Stripe\AdvancedProductFieldsForWooCommerce\Package::class
				]
			);
		} );
		$this->container->register( \PaymentPlugins\Stripe\Blocks\Package::class, function ( $container ) {
			return new \PaymentPlugins\Stripe\Blocks\Package( $container );
		} );
		$this->container->register( \PaymentPlugins\Stripe\CartFlows\Package::class, function ( $container ) {
			return new \PaymentPlugins\Stripe\CartFlows\Package( $container );
		} );
		$this->container->register( \PaymentPlugins\Stripe\WooFunnels\Package::class, function ( $container ) {
			return new \PaymentPlugins\Stripe\WooFunnels\Package( $container );
		} );
		$this->container->register( \PaymentPlugins\Stripe\WooFunnels\Cart\Package::class, function ( $container ) {
			return new \PaymentPlugins\Stripe\WooFunnels\Cart\Package( $container );
		} );
		$this->container->register( \PaymentPlugins\Stripe\CheckoutWC\Package::class, function ( $container ) {
			return new \PaymentPlugins\Stripe\CheckoutWC\Package( $container );
		} );
		$this->container->register( \PaymentPlugins\Stripe\WooCommerceSubscriptions\Package::class, function ( $container ) {
			return new \PaymentPlugins\Stripe\WooCommerceSubscriptions\Package( $container );
		} );
		$this->container->register( \PaymentPlugins\Stripe\WooCommercePreOrders\Package::class, function ( $container ) {
			return new \PaymentPlugins\Stripe\WooCommercePreOrders\Package( $container );
		} );
		$this->container->register( \PaymentPlugins\Stripe\GermanMarket\Package::class, function ( $container ) {
			return new \PaymentPlugins\Stripe\GermanMarket\Package( $container );
		} );
		$this->container->register( \PaymentPlugins\Stripe\WooCommerceTMExtraProductOptions\Package::class, function ( $container ) {
			return new \PaymentPlugins\Stripe\WooCommerceTMExtraProductOptions\Package( $container );
		} );
		$this->container->register( \PaymentPlugins\Stripe\WooCommerceProductAddons\Package::class, function ( $container ) {
			return new \PaymentPlugins\Stripe\WooCommerceProductAddons\Package( $container );
		} );
		$this->container->register( \PaymentPlugins\Stripe\AdvancedProductFieldsForWooCommerce\Package::class, function ( $container ) {
			return new \PaymentPlugins\Stripe\AdvancedProductFieldsForWooCommerce\Package( $container );
		} );
		$this->container->register( \PaymentPlugins\Stripe\ProductAddons\Package::class, function ( $container ) {
			return new \PaymentPlugins\Stripe\ProductAddons\Package( $container );
		} );
	}

	/**
	 * Register admin-specific services.
	 *
	 * Services that are only used in the WordPress admin area.
	 *
	 * @return void
	 */
	private function register_admin_services() {
		$this->container->register( AdminPageController::class, function ( $container ) {
			return new AdminPageController(
				new AssetDataApi(),
				[
					$container->get( DashboardPage::class )
				] );
		} );
		$this->container->register( DashboardPage::class, function ( $container ) {
			return new DashboardPage(
				$container->get( StripeClient::class ),
				$container->get( AssetsApi::class )
			);
		} );
	}

	private function register_payment_gateways() {
		$payment_gateway_classes = $this->container->get( PaymentGatewaysController::class )->get_payment_gateway_classes();
		foreach ( $payment_gateway_classes as $clazz ) {
			$this->container->register( $clazz, function ( $container ) use ( $clazz ) {
				return new $clazz(
					new \WC_Stripe_Payment_Intent( null, $container->get( StripeClient::class ) ),
					$container->get( StripeClient::class ),
					$container->get( AssetsApi::class )
				);
			} );
		}
	}

	private function register_settings() {
		$this->container->register( \WC_Stripe_API_Settings::class, function ( $container ) {
			return new \WC_Stripe_API_Settings();
		} );
		$this->container->register( \WC_Stripe_Account_Settings::class, function ( $container ) {
			return new \WC_Stripe_Account_Settings();
		} );
		$this->container->register( \WC_Stripe_Advanced_Settings::class, function ( $container ) {
			return new \WC_Stripe_Advanced_Settings();
		} );
	}

	/**
	 * This is where WooCommerce dependent code is called.
	 * @return void
	 */
	public function do_woocommerce_loaded() {
		$this->include_woo_dependencies();

		stripe_wc()->rest_api = $this->container->get( \WC_Stripe_Rest_API::class );

		$this->container->get( PackageRegistry::class )->initialize();

		if ( is_admin() ) {
			\WC_Stripe_Admin_Order_Metaboxes::init();
			WC_Stripe_Admin_Meta_Box_Product_Data::init();
			$this->container->get( \WC_Stripe_Admin_Assets::class );
		}
	}

	public function do_woocommerce_init() {
		$this->container->get( \WC_Stripe_API_Settings::class );
		$this->container->get( \WC_Stripe_Account_Settings::class );
		$this->container->get( \WC_Stripe_Advanced_Settings::class );

		$this->container->get( PaymentGatewayRegistry::class )->initialize();
		$this->container->get( InstallmentController::class )->initialize();

		$this->container->get( \WC_Stripe_API_Request_Filter::class )->initialize();
		$this->container->get( \WC_Stripe_Customer_Manager::class )->initialize();
		$this->container->get( \PaymentPlugins\Stripe\Link\LinkIntegration::class )->initialize();

		/**
		 * Fires when the Stripe plugin container has been initialized.
		 *
		 * @param Container $container The container instance.
		 *
		 * @since 4.0.0
		 *
		 */
		do_action( 'wc_stripe_loaded', $this->container );
	}

	public function include_woo_dependencies() {
		if ( $this->dependencies_loaded ) {
			return;
		}
		// traits
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/traits/wc-stripe-settings-trait.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/traits/wc-stripe-controller-traits.php';

		// load factories
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-payment-factory.php';

		// load gateways
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/abstract/abstract-wc-payment-gateway-stripe.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/abstract/abstract-wc-payment-gateway-stripe-local-payment.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-cc.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-applepay.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-googlepay.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-payment-request.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-ach.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-ideal.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-p24.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-klarna.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-giropay.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-eps.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-multibanco.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-sepa.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-sofort.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-wechat.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-bancontact.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-fpx.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-alipay.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-becs.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-grabpay.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-afterpay.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-boleto.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-oxxo.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-affirm.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-blik.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-konbini.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-paynow.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-promptpay.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-swish.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-amazonpay.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-cashapp.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-revolut.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-zip.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-mobilepay.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-twint.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-paybybank.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-upm.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-link.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-billie.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-satispay.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-scalapay.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/gateways/class-wc-payment-gateway-stripe-mbway.php';

		// tokens
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/abstract/abstract-wc-payment-token-stripe.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/tokens/class-wc-payment-token-stripe-cc.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/tokens/class-wc-payment-token-stripe-applepay.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/tokens/class-wc-payment-token-stripe-googlepay.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/tokens/class-wc-payment-token-stripe-local-payment.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/tokens/class-wc-payment-token-stripe-ach.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/tokens/class-wc-payment-token-stripe-sepa.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/tokens/class-wc-payment-token-stripe-becs.php';

		// main classes
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-frontend-scripts.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-field-manager.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-rest-api.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-customer-manager.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-gateway-conversions.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-redirect-handler.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-product-gateway-option.php';

		// settings
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/abstract/abstract-wc-stripe-settings.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/admin/settings/class-wc-stripe-api-settings.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/admin/settings/class-wc-stripe-advanced-settings.php';
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/admin/settings/class-wc-stripe-account-settings.php';

		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-api-request-filter.php';

		// shortcodes
		include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-shortcodes.php';

		if ( is_admin() ) {
			include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/admin/class-wc-stripe-admin-notices.php';
			include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/admin/class-wc-stripe-admin-user-edit.php';
			include_once WC_STRIPE_PLUGIN_FILE_PATH . 'includes/admin/class-wc-stripe-admin-product-edit.php';
		}

		$this->register_payment_gateways();
		$this->register_settings();

		$this->dependencies_loaded = true;
	}

}
