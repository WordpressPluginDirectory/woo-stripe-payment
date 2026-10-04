<?php
/**
 * @var WC_Payment_Gateway_Stripe[] $gateways
 * @var string                      $position
 * @var bool                        $show_skeleton Whether to render skeleton placeholders while the buttons load.
 * @version 3.0.0
 */
defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'wc_stripe_product_before_payment_methods' );
}
?>
<div class="wc-stripe-clear"></div>
<div class="wc-stripe-product-checkout-container <?php echo esc_attr( $position ) ?><?php echo $show_skeleton ? ' wc-stripe-express-loading' : '' ?>">
    <ul class="wc_stripe_product_payment_methods" style="list-style: none">
		<?php foreach ( $gateways as $gateway ):
			$slot_style = $show_skeleton
				? sprintf( ' style="--wc-stripe-express-btn-h:%dpx"', max( 40, min( 55, (int) $gateway->get_option( 'button_height', 50 ) ) ) )
				: '';
			?>
            <li class="payment_method_<?php echo esc_attr( $gateway->id ) ?>"<?php echo $slot_style ?>>
                <div class="payment-box">
					<?php // A 3rd-party gateway sharing this container has no way to ever resolve
					// our skeleton loader - see checkout/checkout-banner.php. ?>
					<?php if ( $show_skeleton && $gateway instanceof \PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway ) : ?>
                        <div class="wc-stripe-express-skeleton" aria-hidden="true"></div>
					<?php endif; ?>
					<?php $gateway->product_fields() ?>
                </div>
            </li>
		<?php endforeach; ?>
    </ul>
</div>