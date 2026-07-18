<?php


namespace PaymentPlugins\Stripe\Blocks\Payments;

use Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry;
use Automattic\WooCommerce\Blocks\Payments\PaymentContext;
use Automattic\WooCommerce\Blocks\Payments\PaymentResult;
use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use PaymentPlugins\Stripe\Blocks\Package;
use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\ContextHandler;
use PaymentPlugins\Stripe\Controllers\PaymentIntent;
use PaymentPlugins\Stripe\Controllers\PaymentIntentController;
use PaymentPlugins\Stripe\Installments\InstallmentController;
use PaymentPlugins\Stripe\Link\LinkIntegration;
use \PaymentPlugins\Stripe\Container\Container;

class PaymentsApi {

	private $container;

	private $config;

	private $assets_registry;

	/**
	 * @var PaymentMethodRegistry
	 */
	private $payment_method_registry;

	/**
	 * @var PaymentResult
	 */
	protected $payment_result;

	private $payment_methods = [];

	public function __construct( Container $container, AssetDataRegistry $assets_registry ) {
		$this->container       = $container;
		$this->assets_registry = $assets_registry;
		$this->add_payment_methods();
		$this->initialize();
	}

	private function initialize() {
		add_action( 'woocommerce_blocks_payment_method_type_registration', array( $this, 'register_payment_methods' ) );
		add_action( 'woocommerce_blocks_checkout_enqueue_data', array( $this, 'enqueue_checkout_data' ) );
		add_action( 'woocommerce_blocks_cart_enqueue_data', array( $this, 'enqueue_cart_data' ) );
		add_action( 'woocommerce_rest_checkout_process_payment_with_context', array(
			$this,
			'payment_with_context'
		), 10, 2 );
		add_action( 'wc_stripe_blocks_enqueue_styles', array( $this, 'enqueue_payment_styles' ) );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_styles' ] );
		add_filter( 'woocommerce_saved_payment_methods_list', [ $this, 'transform_payment_method_type' ], 99 );
	}

	private function add_payment_methods() {
		$this->container->register( Gateways\CreditCardPayment::class, function ( Container $container ) {
			$instance = new Gateways\CreditCardPayment( $container->get( 'BLOCK_ASSETS' ) );
			$instance->set_installments( $container->get( InstallmentController::class ) );
			$instance->set_payment_intent_controller( $container->get( PaymentIntentController::class ) );

			return $instance;
		} );
		$this->container->register( Gateways\GooglePayPayment::class, function ( Container $container ) {
			return new Gateways\GooglePayPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\ApplePayPayment::class, function ( Container $container ) {
			return new Gateways\ApplePayPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		/*$this->container->register( Gateways\PaymentRequest::class, function ( Container $container ) {
			return new Gateways\PaymentRequest( $container->get( 'BLOCK_ASSETS' ) );
		} );*/
		$this->container->register( Gateways\IdealPayment::class, function ( Container $container ) {
			return new Gateways\IdealPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\P24Payment::class, function ( Container $container ) {
			return new Gateways\P24Payment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\BancontactPayment::class, function ( Container $container ) {
			return new Gateways\BancontactPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\EPSPayment::class, function ( Container $container ) {
			return new Gateways\EPSPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\MultibancoPayment::class, function ( Container $container ) {
			return new Gateways\MultibancoPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\SepaPayment::class, function ( Container $container ) {
			return new Gateways\SepaPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\WeChatPayment::class, function ( Container $container ) {
			return new Gateways\WeChatPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\FPXPayment::class, function ( Container $container ) {
			return new Gateways\FPXPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\BECSPayment::class, function ( Container $container ) {
			return new Gateways\BECSPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\GrabPayPayment::class, function ( Container $container ) {
			return new Gateways\GrabPayPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\AlipayPayment::class, function ( Container $container ) {
			return new Gateways\AlipayPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\KlarnaPayment::class, function ( Container $container ) {
			return new Gateways\KlarnaPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\ACHPayment::class, function ( Container $container ) {
			return new Gateways\ACHPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\AfterpayPayment::class, function ( Container $container ) {
			return new Gateways\AfterpayPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\BoletoPayment::class, function ( Container $container ) {
			return new Gateways\BoletoPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\OXXOPayment::class, function ( Container $container ) {
			return new Gateways\OXXOPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\LinkPayment::class, function ( $container ) {
			return new Gateways\LinkPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\AffirmPayment::class, function ( Container $container ) {
			return new Gateways\AffirmPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\BlikPayment::class, function ( Container $container ) {
			return new Gateways\BlikPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\KonbiniPayment::class, function ( Container $container ) {
			return new Gateways\KonbiniPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\PayNowPayment::class, function ( Container $container ) {
			return new Gateways\PayNowPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\PromptPayPayment::class, function ( Container $container ) {
			return new Gateways\PromptPayPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\SwishPayment::class, function ( Container $container ) {
			return new Gateways\SwishPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\AmazonPayPayment::class, function ( Container $container ) {
			return new Gateways\AmazonPayPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\CashAppPayment::class, function ( $container ) {
			return new Gateways\CashAppPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\RevolutPayment::class, function ( $container ) {
			return new Gateways\RevolutPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\ZipPayment::class, function ( $container ) {
			return new Gateways\ZipPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\MobilePayPayment::class, function ( $container ) {
			return new Gateways\MobilePayPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\TwintPayment::class, function ( Container $container ) {
			return new Gateways\TwintPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\PayByBankPayment::class, function ( Container $container ) {
			return new Gateways\PayByBankPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\BilliePayment::class, function ( Container $container ) {
			return new Gateways\BilliePayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\SatispayPayment::class, function ( Container $container ) {
			return new Gateways\SatispayPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\ScalapayPayment::class, function ( Container $container ) {
			return new Gateways\ScalapayPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\MBWayPayment::class, function ( Container $container ) {
			return new Gateways\MBWayPayment( $container->get( 'BLOCK_ASSETS' ) );
		} );
		$this->container->register( Gateways\UniversalPayment::class, function ( Container $container ) {
			return new Gateways\UniversalPayment(
				$container->get( 'BLOCK_ASSETS' ),
				$container->get( PaymentIntentController::class ),
				$container->get( InstallmentController::class )
			);
		} );
	}

	/**
	 * Register all payment methods used by the plugin.
	 *
	 * @param PaymentMethodRegistry $registry
	 */
	public function register_payment_methods( PaymentMethodRegistry $registry ) {
		$this->payment_method_registry = $registry;
		$payment_methods               = array(
			Gateways\CreditCardPayment::class,
			Gateways\GooglePayPayment::class,
			Gateways\ApplePayPayment::class,
			//Gateways\PaymentRequest::class,
			Gateways\IdealPayment::class,
			Gateways\P24Payment::class,
			Gateways\BancontactPayment::class,
			Gateways\EPSPayment::class,
			Gateways\MultibancoPayment::class,
			Gateways\SepaPayment::class,
			Gateways\WeChatPayment::class,
			Gateways\FPXPayment::class,
			Gateways\BECSPayment::class,
			Gateways\GrabPayPayment::class,
			Gateways\AlipayPayment::class,
			Gateways\KlarnaPayment::class,
			Gateways\ACHPayment::class,
			Gateways\AfterpayPayment::class,
			Gateways\BoletoPayment::class,
			Gateways\OXXOPayment::class,
			Gateways\LinkPayment::class,
			Gateways\AffirmPayment::class,
			Gateways\BlikPayment::class,
			Gateways\KonbiniPayment::class,
			Gateways\PayNowPayment::class,
			Gateways\PromptPayPayment::class,
			Gateways\SwishPayment::class,
			Gateways\AmazonPayPayment::class,
			Gateways\UniversalPayment::class,
			Gateways\CashAppPayment::class,
			Gateways\RevolutPayment::class,
			Gateways\ZipPayment::class,
			Gateways\MobilePayPayment::class,
			Gateways\PayByBankPayment::class,
			Gateways\TwintPayment::class,
			Gateways\BilliePayment::class,
			Gateways\SatispayPayment::class,
			Gateways\ScalapayPayment::class,
			Gateways\MBWayPayment::class,
		);

		foreach ( $payment_methods as $clazz ) {
			$this->add_payment_method_to_registry( $clazz, $registry );
		}
	}

	/**
	 * @param                       $clazz
	 * @param PaymentMethodRegistry $registry
	 */
	private function add_payment_method_to_registry( $clazz, $registry ) {
		$instance = $this->container->get( $clazz );
		$registry->register( $instance );
		$this->payment_methods[ $instance->get_name() ] = $instance;
	}

	public function enqueue_payment_styles( $style_api ) {
		foreach ( $this->payment_method_registry->get_all_registered() as $payment_method ) {
			if ( $payment_method instanceof AbstractStripePayment ) {
				$payment_method->enqueue_payment_method_styles();
			}
		}
	}

	public function enqueue_editor_styles() {
		if ( wp_script_is( 'wc-checkout-block', 'registered' ) || wp_script_is( 'wc-cart-block', 'registered' ) ) {
			wp_enqueue_style( 'wc-stripe-blocks-styles' );
		}
	}

	public function enqueue_checkout_data() {
		$this->enqueue_data();
	}

	public function enqueue_cart_data() {
		$this->enqueue_data();
	}

	private function enqueue_data() {
		if ( ! $this->assets_registry->exists( 'stripeGeneralData' ) ) {
			$context    = wc_stripe_get_container()->get( ContextHandler::class );
			$assets_url = stripe_wc()->assets()->assets_url( 'img/cards/' );
			$context->initialize();

			$this->assets_registry->add( 'stripeGeneralData', apply_filters( 'wc_stripe_blocks_general_data', [
				'page'           => $context->get_context(),
				'mode'           => wc_stripe_mode(),
				'publishableKey' => wc_stripe_get_publishable_key(),
				'stripeParams'   => [
					'stripeAccount'  => wc_stripe_get_account_id(),
					'betas'          => [
						'deferred_intent_blik_beta_1',
						'disable_deferred_intent_client_validation_beta_1'
					],
					'developerTools' => [
						'assistant' => [
							'enabled' => false
						]
					]
				],
				'version'        => $this->container->get( 'VERSION' ),
				'assetsUrl'      => stripe_wc()->assets_url(),
				'currency'       => get_woocommerce_currency(),
				'cardIcons'      => array(
					'visa'       => $assets_url . 'visa.svg',
					'amex'       => $assets_url . 'amex.svg',
					'mastercard' => $assets_url . 'mastercard.svg',
					'discover'   => $assets_url . 'discover.svg',
					'diners'     => $assets_url . 'diners.svg',
					'jcb'        => $assets_url . 'jcb.svg',
					'maestro'    => $assets_url . 'maestro.svg',
					'unionpay'   => $assets_url . 'china_union_pay.svg',
					'link'       => stripe_wc()->assets_url( "img/link.svg" )
				)
			] ) );
		}
		if ( ! $this->assets_registry->exists( 'stripeErrorMessages' ) ) {
			$this->assets_registry->add( 'stripeErrorMessages', wc_stripe_get_error_messages() );
		}

		if ( ! $this->assets_registry->exists( 'stripePaymentData' ) ) {
			$payment_data = array();
			if ( WC()->cart && wc_stripe_pre_orders_active() && \WC_Pre_Orders_Cart::cart_contains_pre_order() && \WC_Pre_Orders_Product::product_is_charged_upon_release( \WC_Pre_Orders_Cart::get_pre_order_product() ) ) {
				$payment_data['pre_order'] = true;
			}
			if ( WC()->cart && wcs_stripe_active() && \WC_Subscriptions_Cart::cart_contains_subscription() ) {
				$payment_data['subscription'] = true;
			}
			$this->assets_registry->add( 'stripePaymentData', $payment_data );
		}
	}

	public function payment_with_context( PaymentContext $context, PaymentResult $result ) {
		$this->payment_result = $result;
		add_action( 'wc_stripe_process_payment_error', array( $this, 'process_payment_error' ) );
	}

	/**
	 * @param WP_Error $error |null
	 */
	public function process_payment_error( $error ) {
		if ( $this->payment_result && $error ) {
			// add the error to the payment result
			$this->payment_result->set_payment_details( array(
				'stripeErrorMessage' => $error->get_error_message()
			) );
		}
	}

	/**
	 * @return \PaymentPlugins\Stripe\Blocks\Payments\AbstractStripePayment[]
	 */
	public function get_payment_methods() {
		return $this->payment_methods;
	}

	/**
	 * Blocks only recognize payment tokens of type 'cc' therefore it's necessary to map
	 * the 'stripe_cc' list entry to 'cc'.
	 *
	 * @param $list
	 *
	 * @return mixed
	 */
	public function transform_payment_method_type( $list ) {
		$universal_payment_method = $this->payment_method_registry->get_registered( 'stripe_upm' );
		foreach ( $list as $type => $items ) {
			$payment_method = null;
			foreach ( $items as $item ) {
				$payment_method = $this->payment_methods[ $item['method']['gateway'] ] ?? null;
				if ( $payment_method ) {
					if ( $payment_method->is_active() ) {
						$this->add_to_cc_list( $list, $item );
					} elseif ( $universal_payment_method->is_active() && $universal_payment_method->is_payment_method_active( $payment_method->get_name() ) ) {
						$item['method']['gateway'] = $universal_payment_method->get_name();
						$this->add_to_cc_list( $list, $item );
					}
				}
			}
			if ( $payment_method ) {
				unset( $list[ $type ] );
			}
		}

		return $list;
	}

	/**
	 * Helper function to add an item to the cc list, initializing if needed
	 *
	 * @since 3.3.83
	 */
	private function add_to_cc_list( &$list, $item ) {
		if ( ! isset( $list['cc'] ) ) {
			$list['cc'] = [];
		}
		$list['cc'][] = $item;
	}

}