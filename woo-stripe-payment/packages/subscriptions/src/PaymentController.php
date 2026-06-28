<?php

namespace PaymentPlugins\Stripe\WooCommerceSubscriptions;

use PaymentPlugins\Stripe\Payments\AbstractPaymentController;
use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;
use WC_Stripe_Constants;

class PaymentController extends AbstractPaymentController {

	/**
	 * @param \WC_Order       $order
	 * @param AbstractGateway $payment_method
	 *
	 * @return array|\WP_Error
	 */
	public function process_payment( \WC_Order $order, AbstractGateway $payment_method ) {
		$result = null;
		// 1. If the customer is using a saved payment method, a setup intent is not needed.
		if ( $payment_method->should_use_saved_payment_method() ) {
			$payment_method->payment_method_token = $payment_method->get_payment_method_from_request();
		} else {
			$result = $this->process_setup_intent( $order, $payment_method );
			// If it's a redirect array or error, return early.
			if ( is_wp_error( $result ) || is_array( $result ) ) {
				return $result;
			}
			// $result is a succeeded setup intent.
			$payment_method->save_payment_method( $result->payment_method->id, $order, $result->payment_method );
			$payment_method->payment_method_token = $result->payment_method->id;
		}
		$result = $this->process_zero_total_order( $order, $payment_method );

		return $result;
	}

	/**
	 * @param \WC_Order       $order
	 * @param AbstractGateway $payment_method
	 *
	 * @return array
	 */
	public function process_change_payment_method( \WC_Order $subscription, AbstractGateway $payment_method ) {
		if ( ! $payment_method->should_use_saved_payment_method() ) {
			$result = $payment_method->save_payment_method( $payment_method->get_payment_method_from_request(), $subscription );
			if ( is_wp_error( $result ) ) {
				wc_add_notice( sprintf( __( 'Error saving payment method for subscription. Reason: %s', 'woo-stripe-payment' ), $result->get_error_message() ), 'error' );

				return array( 'result' => 'error' );
			}
		} else {
			$payment_method->payment_method_token = $payment_method->get_payment_method_from_request();
		}
		$token = $payment_method->get_token( $payment_method->payment_method_token, $subscription->get_user_id() );

		// update the metadata needed by the gateway to process a subscription payment.
		if ( $token ) {
			$subscription->set_payment_method( $token->get_gateway_id() );
			$subscription->update_meta_data( \WC_Stripe_Constants::CUSTOMER_ID, $token->get_customer_id() );
			$subscription->set_payment_method_title( $token->get_payment_method_title() );
		}

		$subscription->update_meta_data( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN, $payment_method->payment_method_token );
		$subscription->save();

		return [ 'result' => 'success', 'redirect' => wc_get_page_permalink( 'myaccount' ) ];
	}

	/**
	 * @param float           $amount
	 * @param \WC_Order       $order
	 * @param AbstractGateway $gateway
	 *
	 * @return \stdClass|\WP_Error
	 */
	public function scheduled_subscription_payment( $amount, $order, $gateway ) {
		$update_subscription = false;
		$subscription        = null;
		$args                = $gateway->payment_controller->get_payment_intent_args( $order );
		$intent_id           = $order->get_meta( \WC_Stripe_Constants::PAYMENT_INTENT_ID );

		// if the renewal order already has an intent_id, this could be a duplicate request. If the intent has already succeeded,
		// don't continue with the payment.
		if ( $intent_id ) {
			$intent = $gateway->client->mode( $order )->paymentIntents->retrieve( $intent_id );
			if ( ! is_wp_error( $intent ) ) {
				if ( in_array( $intent->status, array( 'succeeded', 'requires_capture', 'processing' ) ) ) {
					if ( isset( $intent->metadata['order_id'] ) && absint( $intent->metadata['order_id'] ) === $order->get_id() ) {
						$charge = isset( $intent->latest_charge ) ? $intent->latest_charge : null;

						return (object) array(
							'complete_payment' => true,
							'charge'           => $charge,
						);
					}
				}
			}
		}

		// unset in case 3rd party code adds this attribute.
		unset( $args['setup_future_usage'] );

		$args['confirm']        = true;
		$args['off_session']    = true;
		$args['payment_method'] = trim( $gateway->get_order_meta_data( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN, $order ) );

		if ( ( $customer = $gateway->get_order_meta_data( \WC_Stripe_Constants::CUSTOMER_ID, $order ) ) ) {
			$args['customer'] = $customer;
		}

		if ( ( $mandate = $order->get_meta( WC_Stripe_Constants::STRIPE_MANDATE ) ) ) {
			$args['mandate'] = $mandate;
		}

		// if the payment method is empty, check the subscription's parent order to see if that has the payment method
		if ( empty( $args['payment_method'] ) ) {
			$subscription_id = $order->get_meta( '_subscription_renewal' );
			if ( $subscription_id ) {
				$subscription = wcs_get_subscription( absint( $subscription_id ) );
				if ( $subscription ) {
					$parent_order = $subscription->get_parent();
					if ( $parent_order ) {
						$payment_method_id = $parent_order->get_meta( WC_Stripe_Constants::PAYMENT_METHOD_TOKEN );
						if ( $payment_method_id ) {
							// retrieve the payment method
							$payment_method = $gateway->client->mode( $order )->paymentMethods->retrieve( $payment_method_id );
							if ( ! is_wp_error( $payment_method ) ) {
								$args['payment_method'] = $payment_method->id;
								$args['customer']       = $payment_method->customer;
								$update_subscription    = true;
							}
						}
					}
				}
			}
		}

		$retry_mgr = RetryManager::instance();
		$intent    = $gateway->client->mode( $order )->paymentIntents->create( $args );
		if ( is_wp_error( $intent ) ) {
			/**
			 * @var \WP_Error $intent
			 */
			if ( $retry_mgr->should_retry( $order, $gateway->client, $intent, $args ) ) {
				return $this->scheduled_subscription_payment( $amount, $order, $gateway );
			}

			return $intent;
		} else {
			$order->update_meta_data( WC_Stripe_Constants::PAYMENT_INTENT_ID, $intent->id );

			if ( $subscription && $update_subscription ) {
				$subscription->update_meta_data( WC_Stripe_Constants::PAYMENT_METHOD_TOKEN, is_object( $intent->payment_method ) ? $intent->payment_method->id : $intent->payment_method );
				$subscription->update_meta_data( WC_Stripe_Constants::CUSTOMER_ID, $intent->customer );
				$subscription->save();
			}

			$charge = isset( $intent->latest_charge ) ? $intent->latest_charge : null;

			if ( in_array( $intent->status, array( 'succeeded', 'requires_capture', 'processing' ) ) ) {
				return (object) array(
					'complete_payment' => true,
					'charge'           => $charge,
				);
			} else {
				return (object) array(
					'complete_payment' => false,
					'charge'           => $charge,
				);
			}
		}
	}
}
