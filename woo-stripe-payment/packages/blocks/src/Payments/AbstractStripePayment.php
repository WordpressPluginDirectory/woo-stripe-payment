<?php

namespace PaymentPlugins\Stripe\Blocks\Payments;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use \PaymentPlugins\Stripe\Assets\AssetsApi;

/**
 * @property \WC_Payment_Gateway_Stripe $payment_method
 */
abstract class AbstractStripePayment extends AbstractPaymentMethodType {

	protected $assets_api;

	/**
	 * @var \WC_Payment_Gateway_Stripe
	 */
	protected $payment_gateway;

	public function __construct( AssetsApi $assets_api ) {
		$this->assets_api = $assets_api;
		$this->init();
	}

	public function __get( $key ) {
		switch ( $key ) {
			case 'payment_method':
				return $this->payment_method();
		}
	}

	protected function payment_method() {
		if ( ! $this->payment_gateway ) {
			$payment_methods = WC()->payment_gateways()->payment_gateways();

			$this->payment_gateway = isset( $payment_methods[ $this->get_name() ] ) ? $payment_methods[ $this->get_name() ] : null;
			/**
			 * It's possible that some 3rd party code has unset the payment gateway using the
			 * woocommerce_payment_gateways filter. To prevent null exceptions, ensure this variable
			 * is never null
			 */
			if ( ! $this->payment_gateway ) {
				$this->payment_gateway = new MagicPaymentMethod( $this->get_name() );
			}
		}

		return $this->payment_gateway;
	}

	protected function init() {
	}

	public function initialize() {
		$this->settings = get_option( "woocommerce_{$this->name}_settings", [] );
	}


	public function is_active() {
		return wc_string_to_bool( $this->get_setting( 'enabled', 'no' ) );
	}

	public function get_payment_method_script_handles() {
		return [];
	}

	public function get_payment_method_data() {
		$formats               = $this->payment_method->get_payment_method_formats();
		$payment_method_format = array_reduce( array_keys( $formats ), function ( $carry, $key ) use ( $formats ) {
			if ( $key === $this->get_setting( 'method_format', 'type_ending_in' ) ) {
				$carry = $formats[ $key ]['format'];
			}

			return $carry;
		}, '' );


		return [
			'name'                  => $this->get_name(),
			'gatewayId'             => $this->get_name(),
			'title'                 => $this->get_setting( 'title_text' ),
			'showSaveOption'        => \in_array( 'tokenization', $this->get_supported_features() ) && wc_string_to_bool( $this->get_setting( 'save_card_enabled', true ) ),
			'showSavedCards'        => \in_array( 'tokenization', $this->get_supported_features() ),
			'features'              => $this->get_supported_features(),
			'sections'              => $this->get_setting( 'payment_sections', [] ),
			'countryCode'           => wc_get_base_location()['country'],
			'totalLabel'            => __( 'Total', 'woo-stripe-payment' ),
			'isAdmin'               => is_admin(),
			'icons'                 => $this->get_payment_method_icon(),
			'placeOrderButtonLabel' => \esc_html( $this->get_setting( 'order_button_text' ) ),
			'description'           => $this->get_setting( 'description' ),
			'i18n'                  => $this->get_script_translations(),
			'elementOptions'        => $this->payment_method->get_element_options(),
			'paymentElementOptions' => $this->payment_method->get_payment_element_options(),
			'termsDisplayRule'      => stripe_wc()->advanced_settings->get_terms_display_rule(),
			'paymentMethodFormat'   => $payment_method_format
		];
	}

	public function get_supported_features() {
		return $this->payment_method->supports;
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
		return $list;
	}

	public function enqueue_payment_method_styles() {
	}

	protected function get_payment_method_icon() {
		return array();
	}

	public function get_endpoint_data() {
		return [];
	}

	protected function get_script_translations() {
		return [];
	}

}