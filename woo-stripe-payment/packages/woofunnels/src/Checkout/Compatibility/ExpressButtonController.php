<?php

namespace PaymentPlugins\Stripe\WooFunnels\Checkout\Compatibility;

use PaymentPlugins\Stripe\Checkout\ExpressCheckoutRenderer;
use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;
use PaymentPlugins\Stripe\WooFunnels\AssetsApi;

class ExpressButtonController {

	private $settings;

	/**
	 * @var AbstractGateway[]
	 */
	protected $payment_gateways = [];

	private $assets;

	private $payment_registry;

	public function __construct( AssetsApi $assets, PaymentGatewayRegistry $payment_registry ) {
		$this->assets           = $assets;
		$this->payment_registry = $payment_registry;
	}

	public function initialize() {
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'handle_checkout_page_found' ] );
		add_filter( 'wfacp_smart_buttons', [ $this, 'add_buttons' ], 20 );

		add_filter( 'wfacp_template_localize_data', function ( $data ) {
			if ( $this->has_express_buttons() ) {
				$data['smart_button_wrappers']['dynamic_buttons'] = array_merge(
					$data['smart_button_wrappers']['dynamic_buttons'],
					array_reduce( $this->get_payment_gateways(), function ( $carry, $gateway ) {
						$key           = sprintf( '#wfacp_smart_button_%1$s div.banner_payment_method_%1$s', $gateway->id );
						$carry[ $key ] = sprintf( '#wfacp_smart_button_%1$s', $gateway->id );

						return $carry;
					}, [] )
				);
			}

			return $data;
		} );
	}

	public function handle_checkout_page_found() {
		$this->settings = \WFACP_Common::get_page_settings( \WFACP_Common::get_id() );
		if ( $this->has_express_buttons() ) {
			$this->assets->enqueue_style( 'wc-stripe-woofunnels-checkout', 'build/wc-stripe-woofunnels-checkout-styles.css' );
			$this->assets->enqueue_script( 'wc-stripe-woofunnels-checkout', 'build/wc-stripe-woofunnels-checkout.js' );

			foreach ( $this->get_payment_gateways() as $gateway ) {
				add_action( 'wfacp_smart_button_container_' . $gateway->id, function () use ( $gateway ) {
					$this->render_express_buttons( $gateway );
				} );
			}
		}
	}

	private function has_express_buttons() {
		foreach ( $this->get_payment_gateways() as $gateway ) {
			if ( $gateway->enabled === 'yes' && $gateway->is_express_checkout_enabled() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return AbstractGateway[]
	 */
	private function get_payment_gateways() {
		if ( $this->payment_gateways ) {
			return $this->payment_gateways;
		}
		foreach ( $this->get_payment_gateway_ids() as $id ) {
			$this->payment_gateways[ $id ] = $this->payment_registry->get( $id );
		}

		return $this->payment_gateways;
	}

	private function get_payment_gateway_ids() {
		return [
			'stripe_googlepay',
			'stripe_applepay',
			'stripe_link_checkout'
		];
	}

	public function add_buttons( $buttons ) {
		if ( $this->has_express_buttons() ) {
			$renderer = wc_stripe_get_container()->get( ExpressCheckoutRenderer::class );
			remove_action( 'woocommerce_checkout_before_customer_details', [
				$renderer,
				'render_express_checkout'
			] );

			foreach ( $this->get_payment_gateways() as $gateway ) {
				$buttons[ $gateway->id ] = [
					'iframe' => true
				];
			}
		}

		return $buttons;
	}

	/**
	 * @param $gateway
	 *
	 * @return void
	 */
	private function render_express_buttons( $gateway ) {
		?>
        <div class="wc-stripe-checkout-banner-gateway banner_payment_method_<?php echo esc_attr( $gateway->id ) ?>">

        </div>
		<?php
	}

}