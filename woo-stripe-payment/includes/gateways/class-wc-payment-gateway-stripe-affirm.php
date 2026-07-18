<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe_Local_Payment' ) ) {
	return;
}

/**
 * @package PaymentPlugins\Gateways
 */
class WC_Payment_Gateway_Stripe_Affirm extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	use \PaymentPlugins\Stripe\Traits\BNPLPaymentGatewayTrait;

	public $id = 'stripe_affirm';

	protected $payment_method_type = 'affirm';

	public $max_amount = 30001;

	public function __construct( ...$args ) {
		$this->currencies         = array( 'USD', 'CAD' );
		$this->countries          = array( 'US', 'CA' );
		$this->limited_countries  = array( 'US', 'CA' );
		$this->tab_title          = __( 'Affirm', 'woo-stripe-payment' );
		$this->method_title       = __( 'Affirm (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Affirm gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/affirm.svg' );
	}

	public function get_order_button_text( $text ) {
		return __( 'Complete Order', 'woo-stripe-payment' );
	}

	public function is_local_payment_available() {
		if ( parent::is_local_payment_available() ) {
			return WC()->cart && $this->get_order_total() >= 50;
		}

		return false;
	}

	public function get_payment_method_requirements() {
		return apply_filters( 'wc_stripe_affirm_get_required_parameters', array(
			'USD' => array( 'US' ),
			'CAD' => array( 'CA' )
		) );
	}

	protected function validate_local_payment_available( $currency, $billing_country, $total ) {
		$requirements    = $this->get_payment_method_requirements();
		$account_country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );
		// Is this a supported currency?
		if ( isset( $requirements[ $currency ] ) ) {
			$countries = $requirements[ $currency ];

			/**
			 * Validate that the $billing_country matches the Stripe account's registered country
			 * and the $billing_country is in the array of $countries.
			 */
			return $billing_country === $account_country && in_array( $billing_country, $countries, true );
		}

		return false;
	}

	public function get_local_payment_settings() {
		return array_merge( parent::get_local_payment_settings(), array(
			'charge_type'      => array(
				'type'        => 'select',
				'title'       => __( 'Charge Type', 'woo-stripe-payment' ),
				'default'     => 'capture',
				'class'       => 'wc-enhanced-select',
				'options'     => array(
					'capture'   => __( 'Capture', 'woo-stripe-payment' ),
					'authorize' => __( 'Authorize', 'woo-stripe-payment' ),
				),
				'desc_tip'    => true,
				'description' => __( 'This option determines whether the customer\'s funds are captured immediately or authorized and can be captured at a later date.',
					'woo-stripe-payment' ),
			),
			'order_status'     => array(
				'type'        => 'select',
				'title'       => __( 'Order Status', 'woo-stripe-payment' ),
				'default'     => 'default',
				'class'       => 'wc-enhanced-select',
				'options'     => array_merge( array( 'default' => __( 'Default', 'woo-stripe-payment' ) ), wc_get_order_statuses() ),
				'tool_tip'    => true,
				'description' => __( 'This is the status of the order once payment is complete. If <b>Default</b> is selected, then WooCommerce will set the order status automatically based on internal logic which states if a product is virtual and downloadable then status is set to complete. Products that require shipping are set to Processing. Default is the recommended setting as it allows standard WooCommerce code to process the order status.',
					'woo-stripe-payment' ),
			),
			'message_enabled'  => array(
				'title'       => __( 'Messaging Enabled', 'woo-stripe-payment' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'desc_tip'    => true,
				'description' => __( 'When enabled, the Buy Now Pay Later messaging will be available in the sections you configure.', 'woo-stripe-payment' ),
			),
			'message_sections' => array(
				'type'        => 'multiselect',
				'title'       => __( 'Message Sections', 'woo-stripe-payment' ),
				'class'       => 'wc-enhanced-select',
				'options'     => array(
					'checkout' => __( 'Checkout page', 'woo-stripe-payment' ),
					'product'  => __( 'Product Page', 'woo-stripe-payment' ),
					'cart'     => __( 'Cart Page', 'woo-stripe-payment' ),
					'shop'     => __( 'Shop/Category Page', 'woo-stripe-payment' )
				),
				'default'     => array(),
				'desc_tip'    => true,
				'description' => __( 'These are the sections where the Affirm messaging will be enabled.',
					'woo-stripe-payment' ),
			)
		) );
	}

	public function get_payment_description() {
		$desc = parent::get_payment_description();

		return $desc . ' ' . sprintf( __( 'and cart/product total is between %1$s and %2$s.', 'woo-stripe-payment' ),
				wc_price( 50, array( 'currency' => 'USD' ) ),
				wc_price( 30000, array( 'currency' => 'USD' ) ) );
	}

}