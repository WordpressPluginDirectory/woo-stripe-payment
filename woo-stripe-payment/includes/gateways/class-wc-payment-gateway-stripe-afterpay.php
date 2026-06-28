<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe_Local_Payment' ) ) {
	return;
}

/**
 * Class WC_Payment_Gateway_Stripe_Afterpay
 *
 * @since   3.3.1
 * @package PaymentPlugins\Gateways
 */
class WC_Payment_Gateway_Stripe_Afterpay extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	use \PaymentPlugins\Stripe\Traits\BNPLPaymentGatewayTrait;

	public $id = 'stripe_afterpay';

	protected $payment_method_type = 'afterpay_clearpay';

	public function __construct( ...$args ) {
		$this->currencies         = array( 'AUD', 'CAD', 'NZD', 'GBP', 'USD' );
		$this->countries          = array( 'AU', 'CA', 'NZ', 'GB', 'US' );
		$this->tab_title          = __( 'Afterpay', 'woo-stripe-payment' );
		$this->method_title       = __( 'Afterpay (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Afterpay gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/' . $this->get_option( 'icon' ) . '.svg' );
	}

	public function get_order_button_text( $text ) {
		return __( 'Complete Order', 'woo-stripe-payment' );
	}

	public function get_local_payment_settings() {
		$settings = wp_parse_args( array(
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
			'icon'             => array(
				'title'       => __( 'Icon', 'woo-stripe-payment' ),
				'type'        => 'select',
				'options'     => array(
					'afterpay'            => __( 'Afterpay black', 'woo-stripe-payment' ),
					'afterpay_mint_black' => __( 'Afterpay black on mint', 'woo-stripe-payment' ),
					'clearpay_black'      => __( 'Clearpay black', 'woo-stripe-payment' ),
					'clearpay_mint_black' => __( 'Clearpay black on mint', 'woo-stripe-payment' )
				),
				'default'     => 'afterpay',
				'desc_tip'    => true,
				'description' => __( 'This is the icon style that appears next to the gateway on the checkout page. If you have messaging enabled on the checkout page, that will override the icon.', 'woo-stripe-payment' ),
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
				'description' => __( 'These are the additional sections where the Afterpay messaging will be enabled. You can control individual products via the Edit product page.',
					'woo-stripe-payment' ),
			)
		), parent::get_local_payment_settings() );

		// @todo maybe add this option back in a future version.
		//unset( $settings['title_text'] );

		if ( $this->is_restricted_account_country() ) {
			$account_country                           = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );
			$settings['specific_countries']['options'] = array( strtoupper( $account_country ) );
			unset( $settings['allowed_countries']['options']['all_except'] );
		}

		return $settings;
	}

	public function get_required_parameters() {
		return apply_filters( 'wc_stripe_afterpay_get_required_parameters', array(
			'AUD' => array( 'AU', 1, 4000 ),
			'CAD' => array( 'CA', 1, 2000 ),
			'NZD' => array( 'NZ', 1, 4000 ),
			'GBP' => array( 'GB', 1, 1200 ),
			'USD' => array( 'US', 1, 4000 )
		), $this );
	}

	/**
	 * @param $currency
	 * @param $billing_country
	 * @param $total
	 *
	 * @return bool
	 */
	protected function validate_local_payment_available( $currency, $billing_country, $total ) {
		$_available      = false;
		$account_country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );
		// in test mode, the API keys might have been manually entered which
		// means the account settings 'country' value will be blank
		if ( empty( $account_country ) && wc_stripe_mode() === 'test' ) {
			$account_country = wc_get_base_location()['country'];
		}
		$params          = $this->get_required_parameters();
		$filtered_params = isset( $params[ $currency ] ) ? $params[ $currency ] : false;
		if ( $filtered_params ) {
			list( $country, $min_amount, $max_amount ) = $filtered_params;
			if ( ! is_array( $country ) ) {
				$country = array( $country );
			}
			// 1. Country associated with currency must match the Stripe account's registered country
			// 2. Stripe docs state the customer billing country must match the Stripe account country. This rule
			// only pertains to EUR. All currencies do not enforce this requirement.
			// https://stripe.com/docs/payments/afterpay-clearpay#collection-schedule
			$_available = in_array( $account_country, $country, true )
			              && ( $currency !== 'EUR' || ! $billing_country || $account_country === $billing_country )
			              && ( $min_amount <= $total && $total <= $max_amount );
		}

		return $_available;
	}

	public function get_supported_locales() {
		return apply_filters( 'wc_stripe_afterpay_supported_locales', array(
			'en-US',
			'en-CA',
			'en-AU',
			'en-NZ',
			'en-GB',
			'fr-FR',
			'it-IT',
			'es-ES'
		) );
	}

	public function get_payment_token( $method_id, $method_details = array() ) {
		/**
		 *
		 * @var WC_Payment_Token_Stripe_Local $token
		 */
		$token = parent::get_payment_token( $method_id, $method_details );
		$token->set_gateway_title( __( 'Afterpay', 'woo-stripe-payment' ) );

		return $token;
	}

	protected function get_payment_description() {
		$desc = '<p>' . __( 'Stripe accounts in the following countries can accept Afterpay payments with local currency settlement', 'woo-stripe-payment' ) . ': ' . implode( ',', $this->countries ) . '</p>';
		if ( ( $country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() ) ) ) {
			$params = $this->get_required_parameters();
			// get currency for country
			foreach ( $params as $currency => $param ) {
				$account_country = ! is_array( $param[0] ) ? array( $param[0] ) : $param[0];
				if ( in_array( $country, $account_country, true ) ) {
					$desc .= sprintf( __( 'Store currency must be %s for Afterpay to show because your Stripe account is registered in %s. This is a requirement of Afterpay.',
						'woo-stripe-payment' ),
						$currency,
						$country );
					if ( $this->is_restricted_account_country() ) {
						$desc .= __( 'You can accept payments from customers in the same country that you registered your Stripe account in.', 'woo-stripe-payment' );
					}

					return $desc;
				}
			}
		}

		$desc .= __( 'You can accept payments from customers in the same country that you registered your Stripe account in. Payments must also match the local 
			currency of the Stripe account country.', 'woo-stripe-payment' );

		return $desc;
	}

	public function add_stripe_order_args( &$args, $order, $intent = null ) {
		if ( empty( $args['shipping'] ) ) {
			// This ensures digital products can be processed
			$args['shipping'] = array(
				'address' => array(
					'city'        => $order->get_billing_city(),
					'country'     => $order->get_billing_country(),
					'line1'       => $order->get_billing_address_1(),
					'line2'       => $order->get_billing_address_2(),
					'postal_code' => $order->get_billing_postcode(),
					'state'       => $order->get_billing_state(),
				),
				'name'    => $this->payment_controller->get_name_from_order( $order, 'billing' ),
			);
		}
	}

	private function is_restricted_account_country() {
		return false;
	}

}