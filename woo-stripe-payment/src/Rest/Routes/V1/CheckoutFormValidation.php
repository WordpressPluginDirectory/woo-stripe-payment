<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1;

class CheckoutFormValidation extends AbstractRoute {

	/**
	 * @inheritDoc
	 */
	public function get_path() {
		return 'checkout/form-validation';
	}

	/**
	 * @inheritDoc
	 */
	public function get_routes() {
		return [
			[
				'methods'  => \WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'handle_request' ]
			]
		];
	}

	public function handle_post_request( \WP_REST_Request $request ) {
		$this->populate_post_data( $request );
		$checkout = WC()->checkout();

		$data   = $checkout->get_posted_data();
		$errors = new \WP_Error();
		try {
			$class  = new \ReflectionClass( $checkout );
			$method = $class->getMethod( 'validate_posted_data' );
			$method->setAccessible( true );

			/**
			 * Used getClosure here since that's the only way to pass by reference which is required
			 * by the method WC_Checkout::validate_posted_data
			 */
			$method->getClosure( $checkout )( $data, $errors );

			//do_action( 'woocommerce_after_checkout_validation', $data, $errors );

			if ( $errors->has_errors() ) {
				return [
					'success'  => false,
					'messages' => $errors->get_error_messages()
				];
			}

			return [
				'success'  => false,
				'messages' => [
					__( 'Please fill out all required fields before proceeding with payment.', 'woo-stripe-payment' )
				]
			];
		} catch ( \ReflectionException $e ) {
			wc_stripe_log_error( 'Error loading WC_Checkout for form validation.' );
		}
	}

	/**
	 * @param \WP_Error $errors
	 *
	 * @return string
	 */
	private function get_error_messages( $errors ) {
		foreach ( $errors->errors as $code => $messages ) {
			$data = $errors->get_error_data( $code );
			foreach ( $messages as $message ) {
				\wc_add_notice( $message, 'error', $data );
			}
		}

		return \wc_print_notices( true );
	}
}