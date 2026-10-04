<?php
/**
 * @var WC_Payment_Gateway_Stripe[] $gateways
 * @var bool                        $show_skeleton Whether to render skeleton placeholders while the buttons load.
 * @version 3.0.0
 *
 */
defined( 'ABSPATH' ) || exit;

?>
<div class="wc-stripe-banner-checkout<?php echo $show_skeleton ? ' wc-stripe-express-loading' : '' ?>">
    <fieldset>
        <legend class="banner-title"><?php esc_html_e( 'Express Checkout', 'woo-stripe-payment' ) ?></legend>
        <ul class="wc_stripe_checkout_banner_gateways" style="list-style: none">
			<?php foreach ( $gateways as $gateway ):
				$slot_style = $show_skeleton
					? sprintf( ' style="--wc-stripe-express-btn-h:%dpx"', max( 40, min( 55, (int) $gateway->get_option( 'button_height', 50 ) ) ) )
					: '';
				?>
                <li class="wc-stripe-checkout-banner-gateway banner_payment_method_<?php echo esc_attr( $gateway->id ) ?>"<?php echo $slot_style ?>>
					<?php // A 3rd-party gateway sharing this banner (e.g. PayPal) has no way to ever
					// resolve our skeleton loader - it never fires express_element_ready/
					// payment_unavailable, so a placeholder rendered for it is never removed. ?>
					<?php if ( $show_skeleton && $gateway instanceof \PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway ) : ?>
                        <div class="wc-stripe-express-skeleton" aria-hidden="true"></div>
					<?php endif; ?>
                </li>
			<?php endforeach; ?>
        </ul>
    </fieldset>
    <span class="banner-divider"><?php esc_html_e( 'OR', 'woo-stripe-payment' ) ?></span>
</div>