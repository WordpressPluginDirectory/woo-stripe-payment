<?php

namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;

use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripePayment;
use PaymentPlugins\Stripe\Controllers\PaymentIntentController;
use PaymentPlugins\Stripe\Installments\InstallmentController;

class CreditCardPayment extends AbstractStripePayment {

	protected $name = 'stripe_cc';

	/**
	 * @var InstallmentController
	 */
	private $installments;

	/**
	 * @var \PaymentPlugins\Stripe\Controllers\PaymentIntentController
	 */
	private $payment_intent_ctrl;

	public function is_active() {
		return wc_string_to_bool( $this->get_setting( 'enabled', 'yes' ) );
	}

	public function get_payment_method_script_handles() {
		$this->assets_api->register_script( 'wc-stripe-block-credit-card', 'build/wc-stripe-credit-card.js' );

		return array( 'wc-stripe-block-credit-card' );
	}

	public function get_payment_method_data() {
		$assets_url = $this->assets_api->assets_url( '../../assets/img/cards/' );

		$installmentsActive = $this->installments->is_available();
		$client_secret      = null;
		if ( $installmentsActive && $this->is_payment_element_active() ) {
			$payment_intent = $this->installments->get_or_create_installment_intent();
			if ( ! is_wp_error( $payment_intent ) ) {
				$client_secret = $payment_intent->client_secret;
			} else {
				$installmentsActive = false;
			}
		}

		return wp_parse_args( array(
			'cardOptions'            => $this->payment_method->get_card_form_options(),
			'customFieldOptions'     => $this->payment_method->get_card_custom_field_options(),
			'customFormActive'       => $this->payment_method->is_custom_form_active(),
			'isPaymentElement'       => $this->payment_method->is_payment_element_active(),
			'customForm'             => $this->payment_method->get_option( 'custom_form' ),
			'customFormLabels'       => wp_list_pluck( wc_stripe_get_custom_forms(), 'label' ),
			'postalCodeEnabled'      => $this->payment_method->postal_enabled(),
			'saveCardEnabled'        => $this->payment_method->is_active( 'save_card_enabled' ),
			'savePaymentMethodLabel' => __( 'Save Card', 'woo-stripe-payment' ),
			'installmentsActive'     => $installmentsActive,
			'clientSecret'           => $client_secret,
			'cards'                  => array(
				'visa'       => $assets_url . 'visa.svg',
				'amex'       => $assets_url . 'amex.svg',
				'mastercard' => $assets_url . 'mastercard.svg',
				'discover'   => $assets_url . 'discover.svg',
				'diners'     => $assets_url . 'diners.svg',
				'jcb'        => $assets_url . 'jcb.svg',
				'maestro'    => $assets_url . 'maestro.svg',
				'unionpay'   => $assets_url . 'china_union_pay.svg',
				'unknown'    => $this->payment_method->get_custom_form()['cardBrand'],
			)
		), parent::get_payment_method_data() );
	}

	protected function get_payment_method_icon() {
		$icons    = array();
		$cards    = $this->get_setting( 'card_icons', [] );
		$icon_url = $this->get_setting( 'icon_url', '' );
		if ( $icon_url ) {
			$icons[] = [
				'id'  => 'stripe_cc_icon',
				'alt' => 'Credit Cards',
				'src' => $icon_url
			];
		} else {
			$cards = ! \is_array( $cards ) ? [] : $cards;
			foreach ( $cards as $id ) {
				$icons[] = array(
					'id'  => $id,
					'alt' => '',
					'src' => stripe_wc()->assets_url( "img/cards/{$id}.svg" )
				);
			}
		}

		return $icons;
	}

	/**
	 * @param \PaymentPlugins\Stripe\Blocks\Assets\Api $style_api
	 */
	public function enqueue_payment_method_styles() {
		if ( $this->payment_method->is_custom_form_active() ) {
			$form = $this->payment_method->get_option( 'custom_form' );
			if ( \in_array( $form, [ 'bootstrap', 'simple' ] ) ) {
				wp_enqueue_style( 'wc-stripe-credit-card-style', $this->assets_api->assets_url( "build/credit-card/{$form}.css" ) );
				wp_style_add_data( 'wc-stripe-credit-card-style', 'rtl', 'replace' );
			}
		}
	}

	public function set_installments( InstallmentController $installments ) {
		$this->installments = $installments;
	}

	public function set_payment_intent_controller( PaymentIntentController $controller ) {
		$this->payment_intent_ctrl = $controller;
	}

	public function is_payment_element_active() {
		return $this->get_setting( 'form_type' ) === 'payment';
	}

	protected function get_script_translations() {
		return [
			'labels'           => [
				'number' => __( 'Card Number', 'woo-stripe-payment' ),
				'exp'    => __( 'Expiration', 'woo-stripe-payment' ),
				'cvv'    => __( 'CVV', 'woo-stripe-payment' )
			],
			'unsupported_form' => __( 'Unsupported custom form. Please choose another custom form option in the Credit Card Settings.', 'woo-stripe-payment' ),
			'installments'     => [
				'pay'           => __( 'Pay in installments:', 'woo-stripe-payment' ),
				'loading'       => __( 'Loading installments...', 'woo-stripe-payment' ),
				'complete_form' => __( 'Fill out card form for eligibility.', 'woo-stripe-payment' )
			]
		];
	}

}