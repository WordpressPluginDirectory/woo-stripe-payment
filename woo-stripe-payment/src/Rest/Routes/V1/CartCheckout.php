<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1;

/**
 * Cart Checkout Route
 *
 * Processes checkout from cart for express payment methods
 *
 * @since 4.0.0
 */
class CartCheckout extends AbstractCart {

	/**
	 * @var \WC_Payment_Gateway_Stripe
	 */
	private $payment_method;

	/**
	 * @var \WP_REST_Request
	 */
	private $request;

	/**
	 * Initialize hooks for checkout processing
	 */
	private function initialize() {
		add_action( 'woocommerce_after_checkout_validation', [ $this, 'handle_checkout_validation' ], 10, 2 );
		add_action( 'woocommerce_checkout_posted_data', [ $this, 'filter_posted_data' ] );
	}

	/**
	 * Get the route path
	 *
	 * @return string
	 */
	public function get_path() {
		return 'cart/checkout';
	}

	/**
	 * Get route configuration
	 *
	 * @return array
	 */
	public function get_routes() {
		return [
			[
				'methods'  => \WP_REST_Server::EDITABLE,
				'callback' => [ $this, 'handle_request' ],
				'args'     => [
					'payment_method' => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_payment_method' ]
					],
					'context'        => [
						'required' => false,
						'type'     => 'string',
						'default'  => 'cart'
					]
				]
			]
		];
	}

	/**
	 * Handle POST request - Process checkout
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return void
	 */
	public function handle_post_request( \WP_REST_Request $request ) {
		$this->request = $request;

		$this->initialize();

		$this->prepare_request_params( $request );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$_POST = array_merge( $_POST, $request->get_json_params() );

		$this->payment_method = $this->get_payment_method_from_request( $request );

		do_action( 'wc_stripe_rest_process_checkout', $request, $this->payment_method );

		// Create customer account if not logged in and required
		if ( ! is_user_logged_in() ) {
			$this->create_customer( $request );
		}

		// Handle product-specific charge type if checkout from product page
		if ( 'product' === $request->get_param( 'context' ) ) {
			$this->apply_product_gateway_options();
		}

		$this->set_required_fields();

		// Set the checkout nonce so no exceptions are thrown
		$_REQUEST['_wpnonce'] = $_POST['_wpnonce'] = wp_create_nonce( 'woocommerce-process_checkout' );

		WC()->checkout()->process_checkout();
	}

	/**
	 * Handle checkout validation errors
	 *
	 * @param array     $data Posted data
	 * @param \WP_Error $errors Validation errors
	 */
	public function handle_checkout_validation( $data, $errors ) {
		if ( $errors->get_error_codes() ) {
			wc_stripe_log_info( sprintf( '%s::checkout errors: %s', __CLASS__, print_r( $errors->get_error_codes(), true ) ) );

			// Add individual error messages
			WC()->session->set( 'chosen_payment_method', $this->payment_method->id );
			foreach ( $errors->errors as $code => $messages ) {
				foreach ( $messages as $msg ) {
					\wc_add_notice( $msg, 'error', $errors->get_error_data( $code ) );
				}
			}

			// Add instructional notice
			wc_add_notice(
				apply_filters(
					'wc_stripe_after_checkout_validation_notice',
					__( 'Please review your order details then click Place Order.', 'woo-stripe-payment' ),
					$data,
					$errors
				),
				'notice'
			);

			wp_send_json(
				[
					'result'   => 'success',
					'redirect' => $this->get_order_review_url(),
					'reload'   => false,
				],
				200
			);
		}
	}

	/**
	 * Get order review URL with encoded payment data
	 *
	 * @return string
	 */
	private function get_order_review_url() {
		return add_query_arg(
			[
				'_stripe_order_review' => rawurlencode( base64_encode( wp_json_encode( [
					'gateway_id'        => $this->payment_method->id,
					'payment_method_id' => $this->payment_method->get_payment_method_from_request(),
				] ) ) )
			],
			wc_get_checkout_url()
		);
	}

	/**
	 * Set required POST fields for checkout
	 */
	private function set_required_fields() {
		if ( WC()->cart->needs_shipping() ) {
			$_POST['ship_to_different_address'] = true;
		}
		if ( wc_get_page_id( 'terms' ) > 0 ) {
			$_POST['terms'] = 1;
		}
	}

	/**
	 * Create customer account for guest users
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @throws \Exception
	 */
	private function create_customer( $request ) {
		$create = WC()->checkout()->is_registration_required();

		// Create an account for subscriptions if needed
		if ( function_exists( 'wcs_stripe_active' ) && wcs_stripe_active() && \WC_Subscriptions_Cart::cart_contains_subscription() ) {
			$create = true;
		}

		if ( $create ) {
			$password = wp_generate_password();
			$username = $email = $request->get_param( 'billing_email' );
			$result   = wc_create_new_customer( $email, $username, $password );

			if ( $result instanceof \WP_Error ) {
				throw new \Exception( $result->get_error_message() );
			}

			// Log the customer in
			wp_set_current_user( $result );
			wc_set_customer_auth_cookie( $result );

			// Cart will need to refresh to receive updated nonces
			WC()->session->set( 'reload_checkout', true );
		}
	}

	/**
	 * Apply product-specific gateway options
	 */
	private function apply_product_gateway_options() {
		if ( ! class_exists( 'WC_Stripe_Product_Gateway_Option' ) ) {
			return;
		}

		$cart_items = WC()->cart->get_cart();
		if ( empty( $cart_items ) ) {
			return;
		}

		$cart_item = current( $cart_items );
		$option    = new \WC_Stripe_Product_Gateway_Option( $cart_item['data'], $this->payment_method );

		if ( $option->has_product() ) {
			$this->payment_method->settings['charge_type'] = $option->get_option( 'charge_type' );
		}
	}

	/**
	 * Prepare and normalize request parameters
	 *
	 * @param \WP_REST_Request $request
	 */
	private function prepare_request_params( $request ) {
		$customer = WC()->customer;

		// Backfill billing phone from customer if missing
		if ( $customer && $customer->get_id() ) {
			if ( empty( $request['billing_phone'] ) && $customer->get_billing_phone() ) {
				$request['billing_phone'] = $customer->get_billing_phone();
			}
		}

		// Normalize shipping state
		if ( isset( $request['shipping_state'], $request['shipping_country'] ) ) {
			$request['shipping_state'] = wc_stripe_filter_address_state(
				$request['shipping_state'],
				$request['shipping_country']
			);
		}

		// Normalize billing state
		if ( isset( $request['billing_state'], $request['billing_country'] ) ) {
			$request['billing_state'] = wc_stripe_filter_address_state(
				$request['billing_state'],
				$request['billing_country']
			);
		}
	}

	/**
	 * Filter checkout posted data
	 *
	 * @param array $data Posted checkout data
	 *
	 * @return array
	 */
	public function filter_posted_data( $data ) {
		// Normalize shipping state in posted data
		if ( isset( $data['shipping_method'], $data['shipping_country'], $data['shipping_state'] ) ) {
			$data['shipping_state'] = wc_stripe_filter_address_state( $data['shipping_state'], $data['shipping_country'] );
		}

		return $data;
	}

}