<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe' ) ) {
	return;
}

/**
 * Local payment method classes should extend this abstract class
 *
 * @package PaymentPlugins\Abstract
 * @author  Payment Plugins
 *
 */
abstract class WC_Payment_Gateway_Stripe_Local_Payment extends WC_Payment_Gateway_Stripe {

	protected $tab_title = '';

	/**
	 * Currencies this gateway accepts
	 *
	 * @var array
	 */
	public $currencies = array();

	/**
	 * @var string
	 * @deprecated 4.0.0
	 */
	public $local_payment_type = '';

	public $countries = array();

	/**
	 * @var array
	 * @since 3.2.10
	 */
	public $limited_countries = array();

	protected $local_payment_description = '';

	public $token_type = 'Stripe_Local';

	public function __construct( ...$args ) {
		parent::__construct( ...$args );
		$this->template_name = 'local-payment.php';

		if ( ! isset( $this->form_fields['method_format'] ) ) {
			$this->settings['method_format'] = 'gateway_title';
		}
		if ( ! isset( $this->form_fields['charge_type'] ) ) {
			$this->settings['charge_type'] = 'capture';
		}

		if ( ! array_key_exists( 'order_status', $this->settings ) ) {
			$this->settings['order_status'] = 'default';
		}
		$this->order_button_text = $this->get_option( 'order_button_text' );
	}

	public function hooks() {
		parent::hooks();
		remove_filter( 'wc_stripe_settings_nav_tabs', array( $this, 'admin_nav_tab' ) );
		add_filter( 'wc_stripe_local_gateways_tab', array( $this, 'admin_nav_tab' ) );
	}

	public function get_checkout_script_handles() {
		return [ 'wc-stripe-local-payment-checkout' ];
	}

	public function get_add_payment_method_script_handles() {
		return [ 'wc-stripe-local-payment-add-payment' ];
	}

	/**
	 *
	 * @param \PaymentPlugins\Vendor\Stripe\Source $source
	 * @param WC_Order                             $order
	 */
	public function get_source_redirect_url( $source, $order ) {
		return $source->redirect->url;
	}

	public function output_settings_nav() {
		parent::output_settings_nav();
		include stripe_wc()->plugin_path() . 'includes/admin/views/html-settings-local-payments-nav.php';
	}

	public function init_form_fields() {
		$this->form_fields = apply_filters( 'wc_stripe_form_fields_' . $this->id, $this->get_local_payment_settings() );
	}

	/**
	 * Return an array of form fields for the gateway.
	 *
	 * @return array
	 */
	public function get_local_payment_settings() {
		return array(
			'desc'               => array(
				'type'        => 'description',
				'description' => array( $this, 'get_payment_description' ),
			),
			'enabled'            => array(
				'title'       => __( 'Enabled', 'woo-stripe-payment' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'value'       => 'yes',
				'desc_tip'    => true,
				'description' => sprintf( __( 'If enabled, your site can accept %s payments through Stripe.', 'woo-stripe-payment' ), $this->get_method_title() ),
			),
			'general_settings'   => array(
				'type'  => 'title',
				'title' => __( 'General Settings', 'woo-stripe-payment' ),
			),
			'title_text'         => array(
				'type'        => 'text',
				'title'       => __( 'Title', 'woo-stripe-payment' ),
				'default'     => $this->tab_title,
				'desc_tip'    => true,
				'description' => sprintf( __( 'Title of the %s gateway', 'woo-stripe-payment' ), $this->get_method_title() ),
			),
			'description'        => array(
				'title'       => __( 'Description', 'woo-stripe-payment' ),
				'type'        => 'text',
				'default'     => '',
				'description' => __( 'Leave blank if you don\'t want a description to show for the gateway.', 'woo-stripe-payment' ),
				'desc_tip'    => true,
			),
			'order_button_text'  => array(
				'title'       => __( 'Order Button Text', 'woo-stripe-payment' ),
				'type'        => 'text',
				'default'     => $this->get_order_button_text( $this->tab_title ),
				'description' => __( 'The text on the Place Order button that displays when the gateway is selected on the checkout page.', 'woo-stripe-payment' ),
				'desc_tip'    => true
			),
			'allowed_countries'  => array(
				'title'    => __( 'Selling location(s)', 'woocommerce' ),
				'desc'     => __( 'This option lets you limit which countries you are willing to sell to.', 'woocommerce' ),
				'default'  => 'specific',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select wc-stripe-allowed-countries',
				'css'      => 'min-width: 350px;',
				'desc_tip' => true,
				'options'  => array(
					'all'        => __( 'Sell to all countries', 'woocommerce' ),
					'all_except' => __( 'Sell to all countries, except for&hellip;', 'woocommerce' ),
					'specific'   => __( 'Sell to specific countries', 'woocommerce' ),
				),
			),
			'except_countries'   => array(
				'title'             => __( 'Sell to all countries, except for&hellip;', 'woocommerce' ),
				'type'              => 'multi_select_countries',
				'css'               => 'min-width: 350px;',
				'options'           => $this->limited_countries,
				'default'           => array(),
				'desc_tip'          => true,
				'description'       => __( 'When the billing country matches one of these values, the payment method will be hidden on the checkout page.', 'woo-stripe-payment' ),
				'custom_attributes' => array( 'data-show-if' => array( 'allowed_countries' => 'all_except' ) ),
				'sanitize_callback' => function ( $value ) {
					return is_array( $value ) ? $value : array();
				}
			),
			'specific_countries' => array(
				'title'             => __( 'Sell to specific countries', 'woocommerce' ),
				'type'              => 'multi_select_countries',
				'css'               => 'min-width: 350px;',
				'options'           => $this->limited_countries,
				'default'           => $this->countries,
				'desc_tip'          => true,
				'description'       => __( 'When the billing country matches one of these values, the payment method will be shown on the checkout page.', 'woo-stripe-payment' ),
				'custom_attributes' => array( 'data-show-if' => array( 'allowed_countries' => 'specific' ) ),
				'sanitize_callback' => function ( $value ) {
					return is_array( $value ) ? $value : array();
				}
			)
		);
	}

	public function get_payment_method_data() {
		return array_merge(
			parent::get_payment_method_data(),
			[

			]
		);
	}

	/**
	 * @return array[]
	 * @deprecated 3.3.70
	 */
	public function get_element_params() {
		return array(
			'style' => array(
				'base'    => array(
					'padding'       => '10px 12px',
					'color'         => '#32325d',
					'fontFamily'    => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
					'fontSmoothing' => 'antialiased',
					'fontSize'      => '16px',
					'::placeholder' => array( 'color' => '#aab7c4' ),
				),
				'invalid' => array( 'color' => '#fa755a' ),
			),
		);
	}

	public function is_available() {
		$result = parent::is_available();
		if ( $result ) {
			$result = $this->is_local_payment_available();
		}

		return $result;
	}

	public function is_local_payment_available() {
		global $wp;
		$_available = false;
		$total      = $this->get_order_total();
		if ( isset( $wp->query_vars['order-pay'] ) ) {
			$order           = wc_get_order( absint( $wp->query_vars['order-pay'] ) );
			$currency        = $order->get_currency();
			$billing_country = $order->get_billing_country();
		} else {
			$currency        = get_woocommerce_currency();
			$customer        = WC()->customer;
			$billing_country = $customer ? $customer->get_billing_country() : null;
			if ( ! $billing_country ) {
				$billing_country = WC()->countries->get_base_country();
			}
		}
		if ( in_array( $currency, $this->currencies ) ) {
			$type = $this->get_option( 'allowed_countries' );
			if ( 'all_except' === $type ) {
				$_available = ! in_array( $billing_country, (array) $this->get_option( 'except_countries', array() ) );
				if ( $_available && ! empty( $this->limited_countries ) ) {
					// the billing_country still needs to be in the list of limited countries
					$_available = in_array( $billing_country, $this->limited_countries );
				}
			} elseif ( 'specific' === $type ) {
				$_available = in_array( $billing_country, (array) $this->get_option( 'specific_countries', array() ) );
			} else {
				$_available = ! $this->limited_countries || in_array( $billing_country, $this->limited_countries );
			}
		}
		if ( $_available && method_exists( $this, 'validate_local_payment_available' ) ) {
			$_available = $this->validate_local_payment_available( $currency, $billing_country, $total );
		}

		/**
		 * @param array
		 * @param WC_Payment_Gateway_Stripe_Local_Payment
		 *
		 * @since 3.2.10
		 */
		return apply_filters( 'wc_stripe_local_payment_available', $_available, $this );
	}

	/**
	 * @param string $currency
	 * @param string $billing_country
	 * @param float  $total
	 *
	 * @return bool
	 */
	protected function validate_local_payment_available( $currency, $billing_country, $total ) {
		return true;
	}

	public function get_payment_token( $method_id, $method_details = array() ) {
		/**
		 *
		 * @var WC_Payment_Token_Stripe_Local $token
		 */
		$token = parent::get_payment_token( $method_id, $method_details );
		$token->set_gateway_title( $this->title );

		return $token;
	}

	/**
	 * Return a description for (for admin sections) describing the required currency & or billing country(s).
	 *
	 * @return string
	 */
	protected function get_payment_description() {
		$desc = '';
		if ( $this->currencies ) {
			$desc .= sprintf( __( 'Gateway will appear when store currency is <strong>%s</strong>', 'woo-stripe-payment' ), implode( ', ', $this->currencies ) );
		}
		if ( 'all_except' === $this->get_option( 'allowed_countries' ) ) {
			$desc .= sprintf( __( ' & billing country is not <strong>%s</strong>', 'woo-stripe-payment' ), implode( ', ', $this->get_option( 'except_countries' ) ) );
		} elseif ( 'specific' === $this->get_option( 'allowed_countries' ) ) {
			$desc .= sprintf( __( ' & billing country is <strong>%s</strong>', 'woo-stripe-payment' ), implode( ', ', $this->get_option( 'specific_countries' ) ) );
		} else {
			if ( $this->limited_countries ) {
				$desc .= sprintf( __( ' & billing country is <strong>%s</strong>', 'woo-stripe-payment' ), implode( ', ', $this->limited_countries ) );
			}
		}

		return $desc;
	}

	/**
	 * Return a description of the payment method.
	 */
	public function get_local_payment_description() {
		$text = $this->local_payment_description;

		return apply_filters( 'wc_stripe_local_payment_description', $text, $this );
	}

	/**
	 *
	 * @param string $text
	 *
	 * @return string
	 * @since 3.1.3
	 */
	public function get_order_button_text( $text ) {
		return apply_filters( 'wc_stripe_order_button_text', sprintf( __( 'Pay with %s', 'woo-stripe-payment' ), $text ), $this );
	}

	public function get_stripe_documentation_url() {
		return 'https://docs.paymentplugins.com/wc-stripe/config/#/stripe_local_gateways';
	}

}
