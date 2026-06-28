<?php

namespace PaymentPlugins\Stripe\WooCommercePreOrders;

use PaymentPlugins\Stripe\Payments\AbstractPaymentController;
use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;

/**
 * @since 4.0.0
 */
class PaymentController extends AbstractPaymentController {

	/**
	 * @param \WC_Order       $order
	 * @param AbstractGateway $payment_method
	 *
	 * @return array|\WP_Error
	 */
	public function process_payment( \WC_Order $order, AbstractGateway $payment_method ) {
		if ( $payment_method->should_use_saved_payment_method() ) {
			$payment_method->payment_method_token = $payment_method->get_payment_method_from_request();
		} else {
			// a new payment method is required so create a setup intent.
			$result = $this->process_setup_intent( $order, $payment_method );
			if ( is_wp_error( $result ) || is_array( $result ) ) {
				return $result;
			}
			// $result is a succeeded setup intent.
			$payment_method->save_payment_method( $result->payment_method->id, $order, $result->payment_method );
			$payment_method->payment_method_token = $result->payment_method->id;
		}
		\WC_Pre_Orders_Order::mark_order_as_pre_ordered( $order );
		$payment_method->save_zero_total_meta( $order );

		return array(
			'result'   => 'success',
			'redirect' => $payment_method->get_return_url( $order ),
		);
	}

	public function process_pre_order_payment( \WC_Order $order, AbstractGateway $gateway ) {
		$args = $gateway->payment_controller->get_payment_intent_args( $order );

		// unset in case 3rd party code adds this attribute.
		unset( $args['setup_future_usage'] );

		$args['confirm']        = true;
		$args['off_session']    = true;
		$args['payment_method'] = $gateway->get_order_meta_data( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN, $order );

		if ( ( $customer = $gateway->get_order_meta_data( \WC_Stripe_Constants::CUSTOMER_ID, $order ) ) ) {
			$args['customer'] = $customer;
		}

		$intent = $gateway->client->mode( $order )->paymentIntents->create( $args );

		if ( is_wp_error( $intent ) ) {
			return $intent;
		} else {
			$order->update_meta_data( \WC_Stripe_Constants::PAYMENT_INTENT_ID, $intent->id );

			$charge = $intent->latest_charge;

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
