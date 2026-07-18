<?php

/**
 * @package PaymentPlugins\Functions
 */
defined( 'ABSPATH' ) || exit;
/**
 *
 * @param \PaymentPlugins\Vendor\Stripe\PaymentIntent $intent
 * @param WP_REST_Request                             $request
 * @param \PaymentPlugins\Vendor\Stripe\Event         $event
 *
 * @since   3.1.0
 * @package PaymentPlugins\Functions
 */
function wc_stripe_process_payment_intent_succeeded( $intent, $request, $event ) {
	$order = WC_Stripe_Utils::get_order_from_payment_intent( $intent );
	if ( ! $order ) {
		wc_stripe_log_info( sprintf( 'Could not complete payment_intent.succeeded event for payment_intent %s. No order ID %s was found in your WordPress database. 
		This typically happens when you have multiple webhooks setup for the same Stripe account. This order most likely originated from a different site.', $intent->id, isset( $intent->metadata->order_id ) ? $intent->metadata->order_id : '(No Order ID in metadata)' ) );

		return;
	}
	/**
	 * @var \WC_Payment_Gateway_Stripe $payment_method
	 */
	$payment_method = WC()->payment_gateways()->payment_gateways()[ $order->get_payment_method() ] ?? null;
	if ( $payment_method instanceof WC_Payment_Gateway_Stripe ) {
		if ( $payment_method->has_order_lock( $order ) || $order->get_date_paid() ) {
			wc_stripe_log_info( sprintf( 'payment_intent.succeeded event received. Intent has been completed for order %s. Event exited.', $order->get_id() ) );

			return;
		}
		/**
		 * We want to defer the processing of any credit card payments to prevent race conditions. The Stripe webhook can be
		 * received while the checkout process is still running.
		 */
		if ( $payment_method->get_payment_method_type() === 'card' ) {
			WC()->queue()->schedule_single( time() + 2 * MINUTE_IN_SECONDS, 'wc_stripe_process_deferred_webhook', array(
				'type'           => $event->type,
				'order_id'       => $order->get_id(),
				'payment_intent' => $intent->id
			) );
		} else {
			$payment_method->set_order_lock( $order );
			$order->update_meta_data( WC_Stripe_Constants::PAYMENT_INTENT, WC_Stripe_Utils::sanitize_intent( $intent->toArray() ) );
			$result = $payment_method->payment_controller->process_payment( $order );
			if ( ! is_wp_error( $result ) && $result->complete_payment ) {
				$payment_method->payment_controller->payment_complete( $order, $result->charge );
				$order->add_order_note( __( 'payment_intent.succeeded webhook received. Payment has been completed.', 'woo-stripe-payment' ) );
			}
		}
	}
}

/**
 *
 * @param \PaymentPlugins\Vendor\Stripe\Charge $charge
 * @param WP_REST_Request                      $request
 *
 * @since   3.1.1
 * @package PaymentPlugins\Functions
 */
function wc_stripe_process_charge_failed( $charge, $request ) {
	$order = wc_get_order( wc_stripe_filter_order_id( $charge->metadata['order_id'], $charge ) );
	if ( $order ) {
		$payment_methods = WC()->payment_gateways()->payment_gateways();
		if ( isset( $payment_methods[ $order->get_payment_method() ] ) ) {
			/**
			 *
			 * @var WC_Payment_Gateway_Stripe $payment_method
			 */
			$payment_method = $payment_methods[ $order->get_payment_method() ];
			// only update order status if this is an asynchronous payment method,
			// and there is no completed date on the order. If there is a complete date it
			// means payment_complete was called on the order at some point
			if ( $payment_method instanceof WC_Payment_Gateway_Stripe && ! $payment_method->synchronous && ! $order->get_date_completed() ) {
				$order->update_status( apply_filters( 'wc_stripe_charge_failed_status', 'failed' ), $charge->failure_message );
			}
		}
	}
}

/**
 * Function that processes the charge.refund webhook. If the refund is created in the Stripe dashboard, a
 * refund will be created in the WC system to keep WC and Stripe in sync.
 *
 * @param \PaymentPlugins\Vendor\Stripe\Charge $charge
 *
 * @since 3.2.15
 */
function wc_stripe_process_create_refund( $charge ) {
	$mode  = $charge->livemode ? 'live' : 'test';
	$order = null;
	// get the order ID from the charge
	$order = WC_Stripe_Utils::get_order_from_charge( $charge );
	try {
		if ( ! $order ) {
			throw new Exception( sprintf( 'Could not match order with charge %s.', $charge->id ) );
		}
		if ( isset( $charge->metadata['cancellation_via'] ) && $charge->metadata['cancellation_via'] === 'woocommerce_admin' ) {
			// This refund webhook is the result of an authorized payment intent being cancelled. Don't create a refund object.
			return;
		}
		/**
		 * @var \PaymentPlugins\Stripe\Client\StripeClient $client
		 */
		$client   = wc_stripe_get_container()->get( \PaymentPlugins\Stripe\Client\StripeClient::class );
		$response = $client->mode( $charge )->refunds->all( array( 'charge' => $charge->id ) );
		if ( is_wp_error( $response ) ) {
			throw new Exception( sprintf( 'Could not retrieve refunds for charge %s. Error: %s', $charge->id, $response->get_error_message() ) );
		}
		$refunds = $response->data;
		if ( empty( $refunds ) || ! is_array( $refunds ) ) {
			return;
		}
		usort( $refunds, function ( $a, $b ) {
			// sort so refund with most recent created timestamp is first
			return $a->created < $b->created ? 1 : - 1;
		} );
		$refund = $refunds[0];
		/**
		 * @var \PaymentPlugins\Vendor\Stripe\Refund $refund
		 */
		// refund was not created via WC
		if ( ! isset( $refund->metadata['order_id'], $refund->metadata['created_via'] ) ) {
			$args = array(
				'amount'         => wc_stripe_remove_number_precision( $refund->amount, $order->get_currency() ),
				'order_id'       => $order->get_id(),
				'reason'         => $refund->reason,
				'refund_payment' => false
			);
			// if the order has been fully refunded, items should be re-stocked
			if ( $order->get_total() == $args['amount'] + $order->get_total_refunded() ) {
				$args['restock_items'] = true;
				$line_items            = array();
				foreach ( $order->get_items() as $item_id => $item ) {
					$line_items[ $item_id ] = array( 'qty' => $item->get_quantity() );
				}
				$args['line_items'] = $line_items;
			}
			// create the refund
			$result = wc_create_refund( $args );
			// Update the refund in Stripe with metadata
			if ( ! is_wp_error( $result ) ) {
				$client = wc_stripe_get_container()->get( \PaymentPlugins\Stripe\Client\StripeClient::class )->mode( $mode );
				$order->add_order_note( sprintf( __( 'Order refunded in Stripe. Amount: %s', 'woo-stripe-payment' ), $result->get_formatted_refund_amount() ) );
				$client->refunds->update( $refund->id, array(
					'metadata' => array(
						'order_id'    => $order->get_id(),
						'created_via' => 'stripe_dashboard'
					)
				) );
				if ( stripe_wc()->advanced_settings->is_fee_enabled() ) {
					// retrieve the charge but with expanded objects so fee and net can be calculated.
					$charge = $client->charges->retrieve( $charge->id, array(
						'expand' => array(
							'balance_transaction',
							'refunds.data.balance_transaction'
						)
					) );
					if ( ! is_wp_error( $charge ) ) {
						WC_Stripe_Utils::add_balance_transaction_to_order( $charge, $order, true );
					}
				}
			} else {
				throw new Exception( $result->get_error_message() );
			}
		}
	} catch ( Exception $e ) {
		wc_stripe_log_error( sprintf( 'Error processing refund webhook. Error: %s', $e->getMessage() ) );
	}
}

/**
 * @param \PaymentPlugins\Vendor\Stripe\Dispute $dispute
 */
function wc_stripe_charge_dispute_created( $dispute ) {
	if ( stripe_wc()->advanced_settings->is_dispute_created_enabled() ) {
		$order = wc_stripe_get_order_from_transaction( $dispute->charge );
		if ( ! $order ) {
			wc_stripe_log_info( sprintf( 'No order found for charge %s. Dispute %s', $dispute->charge, $dispute->id ) );
		} else {
			$current_status = $order->get_status();
			$message        = sprintf( __( 'A dispute has been created for charge %1$s. Dispute status: %2$s.', 'woo-stripe-payment' ), $dispute->charge, strtoupper( $dispute->status ) );
			$order->update_status( apply_filters( 'wc_stripe_dispute_created_order_status', stripe_wc()->advanced_settings->get_option( 'dispute_created_status', 'on-hold' ), $dispute, $order ), $message );
			/**
			 * @var \PaymentPlugins\Stripe\Client\StripeClient $client
			 */
			$client = wc_stripe_get_container()->get( \PaymentPlugins\Stripe\Client\StripeClient::class );
			// update the dispute with metadata that can be used later
			$client->mode( $order )->disputes->update( $dispute->id, array(
				'metadata' => array(
					'order_id'          => $order->get_id(),
					'prev_order_status' => $current_status
				)
			) );
			// @todo send an email to the admin so they know a dispute was created
		}
	}
}

/**
 * @param \PaymentPlugins\Vendor\Stripe\Dispute $dispute
 */
function wc_stripe_charge_dispute_closed( $dispute ) {
	if ( stripe_wc()->advanced_settings->is_dispute_closed_enabled() ) {
		if ( isset( $dispute->metadata['order_id'] ) ) {
			$order = wc_get_order( absint( $dispute->metadata['order_id'] ) );
		} else {
			$order = wc_stripe_get_order_from_transaction( $dispute->charge );
		}
		if ( ! $order ) {
			wc_stripe_log_info( sprintf( 'No order found for charge %s. Dispute %s', $dispute->charge, $dispute->id ) );

			return;
		}
		$message = sprintf( __( 'Dispute %1$s has been closed. Result: %2$s.', 'woo-stripe-payment' ), $dispute->id, $dispute->status );
		switch ( $dispute->status ) {
			case 'won':
				//set the order's status back to what it was before the dispute
				if ( ! empty( $dispute->metadata['prev_order_status'] ) ) {
					$status = $dispute->metadata['prev_order_status'];
				} else {
					$status = $order->needs_processing() ? 'processing' : 'completed';
				}
				$order->update_status( $status, $message );
				break;
			case 'lost':
				$order->update_status( apply_filters( 'wc_stripe_dispute_closed_order_status', 'failed', $dispute, $order ), $message );
		}
	}
}

/**
 * @param \PaymentPlugins\Vendor\Stripe\Review $review
 */
function wc_stripe_review_opened( $review ) {
	if ( stripe_wc()->advanced_settings->is_review_opened_enabled() ) {
		if ( isset( $review->charge ) ) {
			$order = wc_stripe_get_order_from_transaction( $review->charge );
		} else {
			// In some cases, Stripe does not provide the charge ID in the Review object.
			$pi = $review->payment_intent;
			if ( $pi ) {
				$payment_intent = wc_stripe_get_container()->get( \PaymentPlugins\Stripe\Client\StripeClient::class )->mode( $review )->paymentIntents->retrieve( $pi );
				if ( ! is_wp_error( $payment_intent ) && isset( $payment_intent->metadata['order_id'] ) ) {
					$order = wc_get_order( $payment_intent->metadata['order_id'] );
				}
			}
		}
		if ( $order ) {
			$status = $order->get_status();
			$order->update_meta_data( WC_Stripe_Constants::PREV_STATUS, $status );
			$message = sprintf( __( 'A review has been opened for charge %1$s. Reason: %2$s.', 'woo-stripe-payment' ), $review->charge, strtoupper( $review->reason ) );
			$order->update_status( apply_filters( 'wc_stripe_review_opened_order_status', 'on-hold', $review, $order ), $message );
		}
	}
}

/**
 * @param \PaymentPlugins\Vendor\Stripe\Review $review
 */
function wc_stripe_review_closed( $review ) {
	if ( stripe_wc()->advanced_settings->is_review_closed_enabled() ) {
		$order = null;
		if ( isset( $review->charge ) ) {
			$order = wc_stripe_get_order_from_transaction( $review->charge );
		} else {
			// In some cases, Stripe does not provide the charge ID in the Review object.
			$pi = $review->payment_intent;
			if ( $pi ) {
				/**
				 * @var \PaymentPlugins\Stripe\Client\StripeClient $client
				 */
				$client         = wc_stripe_get_container()->get( \PaymentPlugins\Stripe\Client\StripeClient::class );
				$payment_intent = $client->mode( $review )->paymentIntents->retrieve( $pi );
				if ( ! is_wp_error( $payment_intent ) && isset( $payment_intent->metadata['order_id'] ) ) {
					$order = wc_get_order( $payment_intent->metadata['order_id'] );
				}
			}
		}
		if ( $order ) {
			$status = $order->get_meta( WC_Stripe_Constants::PREV_STATUS );
			if ( ! $status ) {
				$status = $order->needs_processing() ? 'processing' : 'completed';
			}
			$order->delete_meta_data( WC_Stripe_Constants::PREV_STATUS );
			$message = sprintf( __( 'A review has been closed for charge %1$s. Reason: %2$s.', 'woo-stripe-payment' ), $review->charge, strtoupper( $review->reason ) );
			$order->update_status( $status, $message );
		}
	}
}

/**
 * @param \PaymentPlugins\Vendor\Stripe\PaymentIntent $payment_intent
 */
function wc_stripe_process_requires_action( $payment_intent ) {
	if ( isset( $payment_intent->metadata['gateway_id'], $payment_intent->metadata['order_id'] ) ) {
		if ( in_array( $payment_intent->metadata['gateway_id'], array(
			'stripe_oxxo',
			'stripe_boleto',
			'stripe_konbini'
		), true ) ) {
			$order = WC_Stripe_Utils::get_order_from_payment_intent( $payment_intent );
			if ( ! $order ) {
				return;
			}
			/**
			 *
			 * @var WC_Payment_Gateway_Stripe $payment_method
			 */
			$payment_method = WC()->payment_gateways()->payment_gateways()[ $payment_intent->metadata['gateway_id'] ];
			if ( $payment_method ) {
				$payment_method->process_voucher_order_status( $order );
				wc_stripe_log_info( sprintf( 'Order status processed for Voucher payment. Order ID %s. Payment Intent %s', $order->get_id(), $payment_intent->id ) );
			}
		}
	}
}

/**
 * @param \PaymentPlugins\Vendor\Stripe\Charge $charge
 */
function wc_stripe_process_charge_pending( $charge ) {
	if ( isset( $charge->metadata['gateway_id'], $charge->metadata['order_id'] ) ) {
		$payment_methods = WC()->payment_gateways()->payment_gateways();
		$payment_method  = $charge->metadata['gateway_id'];
		$payment_method  = isset( $payment_methods[ $payment_method ] ) ? $payment_methods[ $payment_method ] : null;
		if ( $payment_method && $payment_method instanceof \WC_Payment_Gateway_Stripe && ! $payment_method->synchronous ) {
			$order = wc_get_order( wc_stripe_filter_order_id( $charge->metadata['order_id'], $charge ) );
			if ( $order ) {
				// temporary check to prevent race conditions caused by status update also occurring in
				// class-wc-stripe-payment-intent.php line 89 at the same time as the webhook being received for ach payments
				if ( $payment_method->id !== 'stripe_ach' && ! $payment_method->has_order_lock( $order ) ) {
					$payment_method->set_order_lock( $order );
					$payment_method->payment_controller->payment_complete( $order, $charge );
					$payment_method->release_order_lock( $order );
				}
			}
		}
	}
}