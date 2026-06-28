<?php

namespace PaymentPlugins\Stripe\WooFunnels\Cart;

use PaymentPlugins\Stripe\Checkout\ExpressCheckoutRenderer;

class CartIntegration {

	private $renderer;

	public function __construct( ExpressCheckoutRenderer $renderer ) {
		$this->renderer = $renderer;
	}

	public function initialize() {
		add_action( 'fkcart_after_checkout_button', [ $this, 'render_after_checkout_button' ] );
	}

	public function render_after_checkout_button() {
		$cart = WC()->cart;
		if ( $cart && $cart->needs_payment() ) {
			?>
            <div class="wc-stripe-mini-cart-container">
				<?php
				$this->renderer->render_mini_cart_buttons();
				?>
            </div>
			<?php
			if ( is_ajax() ) {
				?>
                <script>
                    if (window.jQuery) {
                        jQuery(document.body).triggerHandler('wc_fragments_refreshed');
                    }
                </script>
                <style>
                    .wc-stripe-mini-cart-container {
                        padding-left: 16px;
                        padding-right: 16px;
                    }

                    .wc-stripe_googlepay-mini-cart,
                    .wc-stripe_applepay-mini-cart,
                    .wc-stripe_payment-request-mini-cart {
                        margin-top: 10px;
                        display: block;
                    }
                </style>
				<?php
			}
		}
	}

}