<?php

defined( 'ABSPATH' ) || exit();

/**
 *
 * @since   3.0.5
 * @package PaymentPlugins\PaymentTokens
 * @author  Payment Plugins
 *
 */
class WC_Payment_Token_Stripe_ACH extends WC_Payment_Token_Stripe_Local {

	use WC_Payment_Token_Payment_Method_Trait;

	protected $type = 'Stripe_ACH';

	protected $stripe_data = array(
		'bank_name'      => '',
		'routing_number' => '',
		'last4'          => '',
		'account_type'   => ''
	);

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Token_Stripe::details_to_props()
	 */
	public function details_to_props( $details ) {
		if ( isset( $details['us_bank_account'] ) ) {
			$bank = $details['us_bank_account'];
		} elseif ( isset( $details['ach_debit'] ) ) {
			// Plaid used this property
			$bank = $details['ach_debit'];
		} elseif ( $details instanceof \PaymentPlugins\Vendor\Stripe\BankAccount ) {
			$bank = $details;
		}
		$this->set_brand( $bank['bank_name'] );
		$this->set_bank_name( $bank['bank_name'] );
		$this->set_last4( $bank['last4'] );
		$this->set_routing_number( $bank['routing_number'] );
		$this->set_account_type( $bank['account_type'] );
	}

	public function get_bank_name( $context = 'view' ) {
		return $this->get_prop( 'bank_name', $context );
	}

	public function get_routing_number( $context = 'view' ) {
		return $this->get_prop( 'routing_number', $context );
	}

	public function get_last4( $context = 'view' ) {
		return $this->get_prop( 'last4', $context );
	}

	public function get_account_type( $context = 'view' ) {
		return $this->get_prop( 'account_type', $context );
	}

	public function set_bank_name( $value ) {
		$this->set_prop( 'bank_name', $value );
	}

	public function set_routing_number( $value ) {
		$this->set_prop( 'routing_number', $value );
	}

	public function set_last4( $value ) {
		$this->set_prop( 'last4', $value );
	}

	public function set_account_type( $value ) {
		$this->set_prop( 'account_type', $value );
	}

	public function get_formats() {
		return apply_filters( 'wc_stripe_get_token_formats', array(
			'type_ending_in'    => array(
				'label'   => __( 'Type Ending In', 'woo-stripe-payment' ),
				'example' => 'Chase ending in 3434',
				'format'  => __( '{bank_name} ending in {last4}', 'woo-stripe-payment' ),
			),
			'name_masked_last4' => array(
				'label'   => __( 'Type Ending In', 'woo-stripe-payment' ),
				'example' => 'Chase **** 3434',
				'format'  => __( '{bank_name} **** {last4}', 'woo-stripe-payment' ),
			),
			'short_title'       => array(
				'label'   => __( 'Gateway Title', 'woo-stripe-payment' ),
				'example' => $this->get_basic_payment_method_title(),
				'format'  => '{short_title}'
			)
		), $this );
	}

	public function get_html_classes() {
		return 'wc-stripe-ach';
	}

	/**
	 * Keys are snake_case keywords matched as substrings against a normalized (lowercased,
	 * non-alphanumeric runs collapsed to a single underscore) bank_name, rather than exact
	 * strings, since bank_name is a raw institution name from Stripe/Plaid with no stable
	 * format (e.g. "BANK OF AMERICA, N.A.", "PMORGAN CHASE BANK, NA",
	 * "FIRST NATIONAL BANK OF PENNSYLVANIA").
	 *
	 * @return array<string, string> keyword => relative icon path
	 * @since 4.0.8
	 */
	public function get_bank_icon_map() {
		return array(
			'bank_of_america' => 'img/ach/boa.png',
			'capital_one'     => 'img/ach/capitalone.png',
			'chase'           => 'img/ach/chase.png',
			'chime'           => 'img/ach/chime.png',
			'citibank'        => 'img/ach/citibank.png',
			'citizens'        => 'img/ach/citizens.png',
			'navy_federal'    => 'img/ach/navyfederal.png',
			'pnc'             => 'img/ach/pnc.png',
			'us_bank'         => 'img/ach/usbank.png',
			'wells_fargo'     => 'img/ach/wellsfargo.png',
		);
	}

	/**
	 * The brand for ACH tokens is the bank's raw name (e.g. "JP Morgan Chase"), which never
	 * matches an icon file, so the bank_name is matched against a curated set of known banks
	 * instead, falling back to a generic icon for anything unrecognized.
	 *
	 * @return string
	 * @since 4.0.8
	 */
	public function get_icon_file() {
		$bank_name = trim( preg_replace( '/[^a-z0-9]+/', '_', strtolower( (string) $this->get_bank_name( 'edit' ) ) ), '_' );

		foreach ( $this->get_bank_icon_map() as $keyword => $icon_file ) {
			if ( false !== strpos( $bank_name, $keyword ) ) {
				return $icon_file;
			}
		}

		return 'img/ach/default.svg';
	}

	public function get_basic_payment_method_title() {
		return __( 'Bank Payment', 'woo-stripe-payment' );
	}

}
