<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe_Local_Payment' ) ) {
	return;
}

/**
 *
 * @package Stripe/Gateways
 * @author  PaymentPlugins
 *
 */
class WC_Payment_Gateway_Stripe_WeChat extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	protected $payment_method_type = 'wechat_pay';

	public function __construct() {
		$this->local_payment_type = 'wechat_pay';
		$this->currencies         = array( 'AUD', 'CAD', 'CHF', 'CNY', 'DKK', 'EUR', 'GBP', 'HKD', 'JPY', 'NOK', 'SEK', 'SGD', 'USD' );
		$this->id                 = 'stripe_wechat';
		$this->tab_title          = __( 'WeChat', 'woo-stripe-payment' );
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'WeChat (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'WeChat gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		$this->icon               = stripe_wc()->assets_url( 'img/wechat.svg' );
		parent::__construct();
		$this->template_name = 'wechat.php';
	}

	public function init_form_fields() {
		parent::init_form_fields();
		$this->form_fields['allowed_countries']['default'] = 'all';
	}

	public function get_local_payment_settings() {
		return array_merge( parent::get_local_payment_settings(), array(
			'qr_size' => array(
				'type'              => 'input',
				'title'             => __( 'QRCode Size', 'woo-stripe-payment' ),
				'default'           => '128',
				'desc_tip'          => true,
				'description'       => __( 'This option controls the width and height in pixels of the QRCode.', 'woo-stripe-payment' ),
				'sanitize_callback' => function ( $value ) {
					if ( ! is_numeric( $value ) ) {
						$value = 128;
					}

					return $value;
				}
			)
		) );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Gateway_Stripe_Local_Payment::get_source_redirect_url()
	 */
	public function get_source_redirect_url( $source, $order ) {
		if ( wc_stripe_mode() == 'live' ) {
			return sprintf(
				'#qrcode=%s',
				base64_encode(
					wp_json_encode(
						array(
							'code'     => $source->wechat->qr_code_url,
							'redirect' => $this->get_return_url( $order ),
						)
					)
				)
			);
		}
		// test code
		// 'code' => 'weixin:\/\/wxpay\/bizpayurl?pr=tMih4Jo'

		// in test mode just return the redirect url
		return $source->wechat->qr_code_url;
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Gateway_Stripe_Local_Payment::get_localized_params()
	 */
	public function get_localized_params() {
		$data               = parent::get_localized_params();
		$data['qr_message'] = __( 'Scan the QR code using your WeChat app. Once scanned click the Place Order button.', 'woo-stripe-payment' );
		$data['qr_size']    = $this->get_option( 'qr_size', 128 );

		return $data;
	}

	public function get_payment_intent_confirmation_args( $intent, $order ) {
		return array(
			'payment_method_options' => array(
				$this->get_payment_method_type() => array(
					'client' => 'web'
				)
			),
			'return_url'             => add_query_arg( array(
				'payment_intent'               => $intent->id,
				'payment_intent_client_secret' => $intent->client_secret
			), $this->get_local_payment_return_url( $order ) )
		);
	}

}
