<?php

namespace PaymentPlugins\Stripe\Traits;

trait BNPLPaymentGatewayTrait {

	public function get_woocommerce_gateway_icon( $icon, $gateway_id ) {
		if ( $gateway_id === $this->id ) {
			if ( in_array( 'checkout', $this->get_option( 'payment_sections', array() ) ) ) {
				$icon = '';
			}
		}

		return $icon;
	}

}