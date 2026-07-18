<?php

defined( 'ABSPATH' ) || exit();

/**
 *
 * @since   3.1.0
 * @author  Payment Plugins
 * @package PaymentPlugins\Traits
 */
trait WC_Stripe_Payment_Intent_Trait {

	public $has_parent_gateway = false;

	/**
	 *
	 * @param WC_Order $order
	 */
	public function get_confirmation_method( $order = null ) {
		return 'automatic';
	}

	/**
	 *
	 * @param \PaymentPlugins\Vendor\Stripe\PaymentIntent $intent
	 * @param WC_Order                                    $order
	 */
	public function get_payment_intent_checkout_url( $intent, $order, $type = 'payment_intent' ) {
		return sprintf(
			'#response=%s',
			rawurlencode(
				base64_encode(
					wp_json_encode( $this->get_payment_intent_checkout_params( $intent, $order, $type ) )
				)
			)
		);
	}

	/**
	 * @param          $intent
	 * @param WC_Order $order
	 * @param          $type
	 *
	 * @return array
	 */
	protected function get_payment_intent_checkout_params( $intent, $order, $type ) {
		$billing_details = array(
			'name'    => sprintf( '%s %s', $order->get_billing_first_name(), $order->get_billing_last_name() ),
			'phone'   => $order->get_billing_phone(),
			'email'   => $order->get_billing_email(),
			'address' => array(
				'city'        => $order->get_billing_city(),
				'country'     => $order->get_billing_country(),
				'line1'       => $order->get_billing_address_1(),
				'line2'       => $order->get_billing_address_2(),
				'postal_code' => $order->get_billing_postcode(),
				'state'       => $order->get_billing_state()
			)
		);

		/*$billing_details            = array_filter( $billing_details );
		$billing_details['address'] = array_filter( $billing_details['address'] );*/

		$billing_details            = array_map( function ( $value ) {
			return empty( $value ) ? null : $value;
		}, $billing_details );
		$billing_details['address'] = array_map( function ( $value ) {
			return empty( $value ) ? null : $value;
		}, $billing_details['address'] );

		$args = array(
			'pm'                 => $intent->payment_method,
			'type'               => $type,
			'client_secret'      => $intent->client_secret,
			'status'             => $intent->status,
			'gateway_id'         => $this->id,
			'order_id'           => $order->get_id(),
			'order_key'          => $order->get_order_key(),
			'return_url'         => $this->get_complete_payment_return_url( $order ),
			'order_received_url' => $this->get_return_url( $order ),
			'confirmation_args'  => $this->get_payment_intent_confirmation_args( $intent, $order ),
			'billing_details'    => $billing_details,
			'entropy'            => rand(
				0,
				999999
			),
		);

		return $args;
	}

	/**
	 * @param \PaymentPlugins\Vendor\Stripe\PaymentIntent $intent
	 * @param WC_Order                                    $order
	 */
	public function get_payment_intent_confirmation_args( $intent, $order ) {
		$args = array(
			'return_url' => $this->get_complete_payment_return_url( $order )
		);

		if ( $this->requires_confirmation_mandate( $intent ) || ( isset( $intent['setup_future_usage'] ) && $intent['setup_future_usage'] === 'off_session' ) ) {
			$this->add_payment_intent_mandate_args( $args, $order );
		}

		if ( isset( $intent->payment_method ) ) {
			if ( is_object( $intent->payment_method ) ) {
				$id = $intent->payment_method->id;
			} else {
				$id = $intent->payment_method;
			}
			if ( strpos( $id, 'src_' ) !== false ) {
				unset( $args['mandate_data'] );
			}
		}

		return $args;
	}

	public function get_setup_intent_checkout_params( $intent, $order ) {
		return array();
	}

	protected function requires_confirmation_mandate( $intent ) {
		return false;
	}

	public function add_payment_intent_mandate_args( &$args, $order ) {
		$ip_address = $order->get_customer_ip_address();
		$user_agent = $order->get_customer_user_agent();
		if ( ! $ip_address ) {
			$ip_address = WC_Geolocation::get_external_ip_address();
		}
		if ( ! $user_agent ) {
			$user_agent = 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' );
		}

		$args['mandate_data'] = array(
			'customer_acceptance' => array(
				'type'   => 'online',
				'online' => array(
					'ip_address' => $ip_address,
					'user_agent' => $user_agent
				)
			)
		);
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array|void
	 * @deprecated 4.0.0
	 */
	public function handle_setup_intent_for_order( $order ) {
		return [];
	}

	/**
	 * @param \WC_Order $order
	 *
	 * @deprecated 4.0.0
	 */
	public function process_pre_order( $order ) {
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * @return false
	 * @since 3.3.32
	 */
	public function is_deferred_intent_creation() {
		return false;
	}

}

/**
 *
 * @since   3.1.0
 * @author  Payment Plugins
 * @package PaymentPlugins\Traits
 *
 */
trait WC_Stripe_Local_Payment_Intent_Trait {

	use WC_Stripe_Payment_Intent_Trait;

	public function get_setup_intent_checkout_params( $setup_intent, $order ) {
		return $this->get_payment_intent_checkout_params( $setup_intent, $order, 'setup_intent' );
	}

}

trait WC_Stripe_Voucher_Payment_Trait {

	/**
	 * @param \WC_Order $order
	 */
	public function process_voucher_order_status( $order ) {
		if ( $this->is_active( 'email_link' ) ) {
			add_filter( 'woocommerce_email_additional_content_customer_on_hold_order', array(
				$this,
				'add_customer_voucher_email_content'
			), 10, 2 );
		}
		$order->update_status( 'on-hold' );
	}

	/**
	 * @param string    $content
	 * @param \WC_Order $order
	 */
	public function add_customer_voucher_email_content( $content, $order ) {
		if ( $order && $order->get_payment_method() === $this->id ) {
			if ( ( $intent_id = $order->get_meta( WC_Stripe_Constants::PAYMENT_INTENT_ID ) ) ) {
				$payment_intent = $this->gateway->mode( $order )->paymentIntents->retrieve( $intent_id );
				if ( ! is_wp_error( $payment_intent ) ) {
					$voucher_property_name = $this->payment_method_type . '_display_details';
					$link                  = isset( $payment_intent->next_action->{$voucher_property_name}->hosted_voucher_url ) ? $payment_intent->next_action->{$voucher_property_name}->hosted_voucher_url : null;
					if ( $link ) {
						$content .= '<p>' . sprintf( __( 'Please click %shere%s to view your %s voucher.', 'woo-stripe-payment' ), '<a href="' . $link . '" target="_blank">', '</a>', $this->get_title() ) . '</p>';
					}
				}
			}
		}

		return $content;
	}

}

trait WC_Stripe_Express_Payment_Trait {

	public function get_element_options( $options = array() ) {
		$options = array( 'locale' => $this->get_element_options_locale() );

		return apply_filters( 'wc_stripe_get_element_options', $options, $this );
	}

	/**
	 * @return mixed|null
	 * @since 3.3.87
	 */
	protected function get_element_options_locale() {
		return wc_stripe_get_site_locale();
	}

}