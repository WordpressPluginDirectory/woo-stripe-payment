<?php

namespace PaymentPlugins\Stripe\Webhooks;

class DeferredWebhookHandler {

	public function initialize() {
		add_action( 'wc_stripe_process_deferred_webhook', [ $this, 'process' ], 10, 3 );
	}

	public function process( $type, $order_id, $payment_intent = null ) {
		switch ( $type ) {
			case 'payment_intent.succeeded':
				$order = wc_get_order( absint( $order_id ) );
				if ( $order ) {
					$payment_gateways = WC()->payment_gateways()->payment_gateways();
					/**
					 * @var \WC_Payment_Gateway_Stripe $payment_method
					 */
					$payment_method = $payment_gateways[ $order->get_payment_method() ] ?? null;
					if ( $payment_method ) {
						// The order has already been processed, or is in a status that should never be
						// reactivated by a delayed/out-of-order webhook (e.g. cancelled, refunded).
						if ( $payment_method->has_order_lock( $order ) || $order->get_date_paid() || ! \WC_Stripe_Utils::can_process_webhook_payment( $order ) ) {
							return;
						}
						$payment_method->set_order_lock( $order );
						// can_use_payment_intent() only looks at the order's own PAYMENT_INTENT_ID meta
						// and WC()->session (meaningless in this deferred/cron context) - without this,
						// an order that never got this meta set by a synchronous flow (e.g. a checkout
						// session whose redirect return never happened) would have process_payment()
						// create a brand new, unrelated PaymentIntent instead of using this one.
						if ( $payment_intent && ! $order->get_meta( \WC_Stripe_Constants::PAYMENT_INTENT_ID ) ) {
							$order->update_meta_data( \WC_Stripe_Constants::PAYMENT_INTENT_ID, $payment_intent );
							$order->save();
						}
						$result = $payment_method->payment_controller->process_payment( $order );
						if ( ! is_wp_error( $result ) && $result->complete_payment ) {
							$payment_method->payment_controller->payment_complete( $order, $result->charge );
						}
					}
				}
				break;
		}
	}

}