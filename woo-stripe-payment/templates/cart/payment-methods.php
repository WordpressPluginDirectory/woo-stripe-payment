<?php
/**
 * @var WC_Payment_Gateway_Stripe[] $gateways
 * @var bool                        $after
 * @var bool                        $show_skeleton Whether to render skeleton placeholders while the buttons load.
 * @package PaymentPlugins\Templates
 * @version 3.0.9
 */
defined( 'ABSPATH' ) || exit;
do_action( 'wc_stripe_cart_before_payment_methods' );
?>
<div class="wc-stripe-cart-checkout-container<?php echo $show_skeleton ? ' wc-stripe-express-loading' : '' ?>">
    <ul class="wc_stripe_cart_payment_methods" style="list-style: none">
		<?php if ( $after ): ?>
            <li class="wc-stripe-payment-method or">
                <p class="wc-stripe-cart-or">
                    &mdash;&nbsp;<?php esc_html_e( 'or', 'woo-stripe-payment' ) ?>&nbsp;&mdash;
                </p>
            </li>
		<?php endif; ?>
		<?php foreach ( $gateways as $gateway ):
			$slot_style = $show_skeleton
				? sprintf( ' style="--wc-stripe-express-btn-h:%dpx"', max( 40, min( 55, (int) $gateway->get_option( 'button_height', 50 ) ) ) )
				: '';
			?>
            <li
                    class="wc-stripe-payment-method payment_method_<?php echo esc_attr( $gateway->id ) ?>"<?php echo $slot_style ?>>
                <div class="payment-box">
					<?php // A 3rd-party gateway sharing this container has no way to ever resolve
					// our skeleton loader - see checkout/checkout-banner.php. ?>
					<?php if ( $show_skeleton && $gateway instanceof \PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway ) : ?>
                        <div class="wc-stripe-express-skeleton" aria-hidden="true"></div>
					<?php endif; ?>
					<?php $gateway->cart_fields() ?>
                </div>
            </li>
		<?php endforeach; ?>
		<?php if ( ! $after ): ?>
            <li class="wc-stripe-payment-method or">
                <p class="wc-stripe-cart-or">
                    &mdash;&nbsp;<?php esc_html_e( 'or', 'woo-stripe-payment' ) ?>&nbsp;&mdash;
                </p>
            </li>
		<?php endif; ?>
    </ul>
</div>