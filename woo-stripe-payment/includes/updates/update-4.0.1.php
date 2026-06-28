<?php
defined( 'ABSPATH' ) || exit();

if ( function_exists( 'WC' ) ) {
	$payment_gateways = WC()->payment_gateways()->payment_gateways();

	$stripe_cc = $payment_gateways['stripe_cc'] ?? null;
	if ( $stripe_cc ) {
		$cards = $stripe_cc->get_option( 'cards', [] );
		if ( $cards && \is_array( $cards ) ) {
			$stripe_cc->validate_card_icons_field( 'card_icons', $cards );
			$stripe_cc->update_option( 'card_icons', $cards );
			$stripe_cc->update_option( 'icon_url', $stripe_cc->settings['icon_url'] );
		}
	}
}