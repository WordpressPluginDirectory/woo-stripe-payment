<?php

namespace PaymentPlugins\Stripe\Payments;

use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;
use WC_Stripe_Constants;

abstract class AbstractPaymentController {

	/**
	 * @param \WC_Order       $order
	 * @param AbstractGateway $payment_method
	 *
	 * @return \PaymentPlugins\Vendor\Stripe\SetupIntent|array|\WP_Error The setup intent object on success, a redirect array if action is required, or WP_Error on failure.
	 * @throws \Exception
	 */
	public function process_setup_intent( \WC_Order $order, AbstractGateway $payment_method ) {
		try {
			$setup_intent_id = $order->get_meta( WC_Stripe_Constants::SETUP_INTENT_ID );
			if ( ! $setup_intent_id ) {
				// create the setup intent
				$payment_method_id = $payment_method->get_payment_method_from_request();
				$args              = [
					'usage'                => 'off_session',
					'payment_method_types' => [ $payment_method->get_payment_method_type() ],
					'metadata'             => [
						'gateway_id' => $payment_method->id,
						'order_id'   => $order->get_id()
					],
					'confirm'              => (bool) $payment_method_id,
					'expand'               => [ 'payment_method' ]
				];
				/**
				 * Some payment methods (e.g. CashApp) do not create a payment method immediately on the
				 * client side. In these cases, $payment_method_id will be empty at this point — the payment
				 * method is created later during the client side confirmation.
				 *
				 * When $payment_method_id is empty:
				 * - 'confirm' must be false, otherwise Stripe returns a validation error because there is
				 *   no payment method to confirm with.
				 * - 'payment_method' must be omitted entirely for the same reason.
				 *
				 * When $payment_method_id is present the setup intent is confirmed immediately.
				 */
				if ( $payment_method_id ) {
					$args['payment_method'] = $payment_method_id;
				}
				if ( $args['confirm'] === true ) {
					$args['return_url'] = $payment_method->get_complete_payment_return_url( $order );
					$payment_method->add_payment_intent_mandate_args( $args, $order );
				}
				if ( ( $customer_id = wc_stripe_get_customer_id( $order->get_customer_id() ) ) ) {
					$args['customer'] = $customer_id;
				} elseif ( ( $customer_id = $order->get_meta( WC_Stripe_Constants::CUSTOMER_ID ) ) ) {
					$args['customer'] = $customer_id;
				}
				$payment_method->add_stripe_order_args( $args, $order );
				$setup_intent = $payment_method->client->mode( $order )->setupIntents->create(
					apply_filters( 'wc_stripe_setup_intent_params', $args, $order, $payment_method )
				);
				if ( is_wp_error( $setup_intent ) ) {
					/**
					 * @var \WP_Error $setup_intent
					 */
					throw new \Exception( $setup_intent->get_error_message() );
				}
				$order->update_meta_data( WC_Stripe_Constants::SETUP_INTENT_ID, $setup_intent->id );
				if ( ! empty( $setup_intent->mandate ) ) {
					$order->update_meta_data( WC_Stripe_Constants::STRIPE_MANDATE, $setup_intent->mandate );
				}
				$order->save();
			} else {
				// update the setup intent after retrieving it.
				$setup_intent = $payment_method->client->mode( $order )->setupIntents->retrieve( $setup_intent_id, [ 'expand' => [ 'payment_method' ] ] );
				if ( is_wp_error( $setup_intent ) ) {
					/**
					 * @var \WP_Error $setup_intent
					 */
					throw new \Exception( $setup_intent->get_error_message() );
				}

				// only update setup intents that don't have a succeeded status.
				if ( $setup_intent->status !== 'succeeded' ) {
					$args         = [
						'payment_method'       => $payment_method->get_payment_method_from_request(),
						'payment_method_types' => [ $payment_method->get_payment_method_type() ]
					];
					$args         = apply_filters( 'wc_stripe_update_setup_intent_params', $args, $order );
					$setup_intent = $payment_method->client->mode( $order )->setupIntents->update( $setup_intent_id, $args );
					if ( is_wp_error( $setup_intent ) ) {
						/**
						 * @var \WP_Error $setup_intent
						 */
						throw new \Exception( $setup_intent->get_error_message() );
					}
				}
			}
			if ( \in_array( $setup_intent->status, [
				'requires_action',
				'requires_payment_method',
				'requires_confirmation'
			] ) ) {
				return [
					'result'   => 'success',
					'redirect' => $payment_method->get_payment_intent_checkout_url( $setup_intent, $order, 'setup_intent' ),
				];
			}

			return $setup_intent;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'stripe-error', $e->getMessage() );
		}
	}

	/**
	 * @param \WC_Order       $order
	 * @param AbstractGateway $payment_method
	 *
	 * @since 4.0.0
	 */
	public function process_zero_total_order( $order, $payment_method ) {
		$payment_method->save_zero_total_meta( $order );
		if ( 'capture' === $payment_method->get_option( 'charge_type' ) ) {
			$order->payment_complete();
		} else {
			$order_status = $payment_method->get_option( 'order_status' );
			$order->update_status( apply_filters( 'wc_stripe_authorized_order_status', 'default' === $order_status ? 'on-hold' : $order_status, $order, $payment_method ) );
		}
		WC()->cart->empty_cart();
		$this->destroy_session_data();

		return [
			'result'   => 'success',
			'redirect' => $payment_method->get_return_url( $order ),
		];
	}

	public function destroy_session_data() {
		\WC_Stripe_Utils::delete_payment_intent_to_session();
	}
}
