<?php

namespace PaymentPlugins\Stripe\Webhooks;

class DeferredWebhookHandler {

	public function initialize() {
		add_action( 'wc_stripe_process_deferred_webhook', [ $this, 'process' ], 10, 2 );
	}

	public function process( $type, $order_id ) {
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