<?php

namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;

use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use PaymentPlugins\Stripe\AdaptivePricing\UPMCheckoutSessionController;
use PaymentPlugins\Stripe\Assets\AssetsApi;
use PaymentPlugins\Stripe\Blocks\StoreApi\EndpointData;
use PaymentPlugins\Stripe\Controllers\PaymentIntentController;
use PaymentPlugins\Stripe\Installments\InstallmentController;
use PaymentPlugins\Stripe\RequestContext;

class UniversalPayment extends \PaymentPlugins\Stripe\Blocks\Payments\AbstractStripePayment {

	protected $name = 'stripe_upm';

	/**
	 * @var InstallmentController
	 */
	private $installments;

	/**
	 * @var \PaymentPlugins\Stripe\Controllers\PaymentIntentController
	 */
	private $payment_intent_ctrl;

	/**
	 * @var UPMCheckoutSessionController
	 */
	private $checkout_session;

	public function __construct( AssetsApi $assets_api, PaymentIntentController $controller, InstallmentController $installments, UPMCheckoutSessionController $checkout_session ) {
		parent::__construct( $assets_api );
		$this->payment_intent_ctrl = $controller;
		$this->installments        = $installments;
		$this->checkout_session    = $checkout_session;
	}

	public function get_payment_method_script_handles() {
		if ( $this->checkout_session->is_available_for_cart() ) {
			$this->assets_api->register_script( 'wc-stripe-block-upm-checkout-session', 'build/wc-stripe-upm-checkout-session.js' );

			return array( 'wc-stripe-block-upm-checkout-session' );
		}

		$this->assets_api->register_script( 'wc-stripe-block-upm', 'build/wc-stripe-upm.js' );

		return array( 'wc-stripe-block-upm' );
	}

	public function get_payment_method_data() {
		$data = [
			'installmentsActive'    => $this->installments->is_available(),
			'paymentElementOptions' => array(
				'layout' => array(
					'type' => $this->get_setting( 'layout_type', 'tabs' )
				)
			)
		];
		if ( $this->get_setting( 'layout_type', 'tabs' ) === 'accordion' ) {
			$data['paymentElementOptions']['layout']['radios']               = wc_string_to_bool( $this->get_setting( 'layout_radios', 'no' ) );
			$data['paymentElementOptions']['layout']['spacedAccordionItems'] = wc_string_to_bool( $this->get_setting( 'spaced_items', 'no' ) );
		}

		$ap_config                        = $this->get_setting( 'adaptive_pricing_config', [] );
		$data['currencySelectorPosition'] = is_array( $ap_config ) ? ( $ap_config['currency_selector_position'] ?? 'above_payment_methods' ) : 'above_payment_methods';

		return array_merge(
			parent::get_payment_method_data(),
			$data
		);
	}

	public function get_endpoint_data() {
		$endpoint_data = new EndpointData();
		$endpoint_data->set_namespace( $this->get_name() );
		$endpoint_data->set_endpoint( CartSchema::IDENTIFIER );
		$endpoint_data->set_schema_type( ARRAY_A );
		$endpoint_data->set_data_callback( [ $this, 'get_cart_extension_data' ] );

		return $endpoint_data;
	}

	public function get_cart_extension_data() {
		$this->payment_intent_ctrl->set_request_context( new RequestContext( RequestContext::CHECKOUT ) );

		$data = [
			'elementOptions' => $this->payment_method->get_element_options()
		];

		if ( $this->checkout_session->is_available_for_cart() ) {
			$session = $this->checkout_session->get_checkout_session_for_cart();
			if ( ! is_wp_error( $session ) ) {
				$data['checkoutSession'] = $session;
			}
		}

		return $data;
	}

	public function get_update_callback() {
		// is_enabled() only, not is_available_for_cart() - this runs on init (SchemaController),
		// before WC()->cart is populated (that happens on wp_loaded), so the cart-total check
		// wouldn't be reliable here.
		if ( ! $this->checkout_session->is_enabled() ) {
			return null;
		}

		return [ $this->checkout_session, 'update_save_payment_method' ];
	}

	public function is_payment_method_active( $name ) {
		$payment_methods = $this->payment_method->get_enabled_payment_methods();

		return is_array( $payment_methods )
		       && isset( $payment_methods[ $name ]['enabled'] )
		       && $payment_methods[ $name ]['enabled'] === true;
	}

	protected function get_script_translations() {
		return [
			'installments' => [
				'pay'           => __( 'Pay in installments:', 'woo-stripe-payment' ),
				'loading'       => __( 'Loading installments...', 'woo-stripe-payment' ),
				'complete_form' => __( 'Fill out card form for eligibility.', 'woo-stripe-payment' )
			]
		];
	}

}