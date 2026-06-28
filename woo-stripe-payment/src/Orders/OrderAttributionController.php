<?php

namespace PaymentPlugins\Stripe\Orders;

class OrderAttributionController {

	public function initialize() {
		add_filter( 'wc_order_attribution_stamp_checkout_html_actions', function ( $actions ) {
			return array_merge(
				$actions,
				array( 'wc_stripe_product_before_payment_methods', 'wc_stripe_cart_before_payment_methods' ) );
		} );
	}
}