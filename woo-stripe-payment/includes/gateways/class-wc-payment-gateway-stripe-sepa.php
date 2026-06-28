<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe_Local_Payment' ) ) {
	return;
}

/**
 *
 * @package PaymentPlugins\Gateways
 * @author  PaymentPlugins
 *
 */
class WC_Payment_Gateway_Stripe_Sepa extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;
	use \PaymentPlugins\Stripe\Traits\TokenizationTrait;
	use \PaymentPlugins\Stripe\WooCommercePreOrders\Traits\PreOrdersTrait;
	use \PaymentPlugins\Stripe\WooCommerceSubscriptions\Traits\WooCommerceSubscriptionsTrait;

	public $id = 'stripe_sepa';

	protected $payment_method_type = 'sepa_debit';

	public $token_type = 'Stripe_Sepa';

	protected $supports_save_payment_method = true;

	public function __construct( ...$args ) {
		$this->synchronous        = false;
		$this->currencies         = array( 'EUR' );
		$this->tab_title          = __( 'SEPA', 'woo-stripe-payment' );
		$this->method_title       = __( 'SEPA (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'SEPA gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon                          = $this->assets->assets_url( 'img/sepa.svg' );
		$this->settings['save_card_enabled'] = 'yes';
		$this->new_payment_method_label      = __( 'New Account', 'woo-stripe-payment' );
		$this->saved_payment_methods_label   = __( 'Saved Accounts', 'woo-stripe-payment' );
	}

	public function init_form_fields() {
		parent::init_form_fields();
		$this->form_fields['allowed_countries']['default'] = 'all';
	}

	public function get_element_params() {
		return array_merge( parent::get_element_params(), array( 'supportedCountries' => array( 'SEPA' ) ) );
	}

	public function get_local_payment_settings() {
		return parent::get_local_payment_settings() + array(
				'company_name'  => array(
					'title'       => __( 'Company Name', 'woo-stripe-payment' ),
					'type'        => 'text',
					'default'     => get_bloginfo( 'name' ),
					'desc_tip'    => true,
					'description' => __( 'The name of your company that will appear in the SEPA mandate.', 'woo-stripe-payment' ),
				),
				'method_format' => array(
					'title'       => __( 'Payment Method Display', 'woo-stripe-payment' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'options'     => wp_list_pluck( $this->get_payment_method_formats(), 'example' ),
					'default'     => 'type_ending_last4',
					'desc_tip'    => true,
					'description' => __( 'This option allows you to customize how the payment method will display for your customers on orders, subscriptions, etc.', 'woo-stripe-payment' )
				)
			);
	}

	public function get_payment_description() {
		return parent::get_payment_description() .
		       sprintf( '<p><a target="_blank" href="https://stripe.com/docs/sources/sepa-debit#testing">%s</a></p>', __( 'SEPA Test Accounts', 'woo-stripe-payment' ) );
	}

	public function get_payment_token( $method_id, $method_details = array() ) {
		/**
		 * @var WC_Payment_Token_Stripe_Sepa $token
		 */
		$token = parent::get_payment_token( $method_id, $method_details );

		$mandate = $token->get_mandate();
		$url     = $token->get_mandate_url();
		if ( $mandate && ! $url ) {
			$mandate = $this->client->mode( $token->get_environment() )->mandates->retrieve( $mandate );
			if ( ! is_wp_error( $mandate ) ) {
				if ( isset( $mandate->payment_method_details->sepa_debit->url ) ) {
					$token->set_mandate_url( $mandate->payment_method_details->sepa_debit->url );
				}
			}
		}

		return $token;
	}

	public function get_payment_element_options() {
		return array_merge(
			parent::get_payment_element_options(),
			array(
				'business' => array(
					'name' => $this->get_option( 'company_name', '' )
				)
			)
		);
	}
}