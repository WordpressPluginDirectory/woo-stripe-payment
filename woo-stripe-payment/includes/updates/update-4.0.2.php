<?php
defined( 'ABSPATH' ) || exit();

if ( function_exists( 'WC' ) ) {
	$payment_gateways = WC()->payment_gateways()->payment_gateways();

	$bnpl_gateways = [ 'stripe_affirm', 'stripe_afterpay', 'stripe_klarna' ];
	foreach ( $bnpl_gateways as $id ) {
		$gateway = $payment_gateways[ $id ] ?? null;
		/**
		 * @var \WC_Payment_Gateway_Stripe $gateway
		 */
		if ( $gateway ) {
			// rename the "payment_sections" option to "message_sections".
			$message_sections = $gateway->get_option( 'message_sections', false );
			if ( ! is_array( $message_sections ) ) {
				$payment_sections = $gateway->get_option( 'payment_sections', [] );
				$gateway->update_option( 'message_sections', $payment_sections );
				$gateway->update_option( 'payment_sections', [] );
			}
		}
	}
}