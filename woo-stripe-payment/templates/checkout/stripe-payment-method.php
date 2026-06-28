<?php
/**
 * @var WC_Payment_Gateway_Stripe $gateway
 * @version 4.0.0
 *
 */
defined( 'ABSPATH' ) || exit;
$style = ! empty( $tokens ) ? 'display:none;' : '';
?>

<div class="wc-payment-form wc-<?php echo esc_attr( $gateway->id ) ?>-container wc-stripe-gateway-container"
     style="<?php echo $style ?>">
	<?php if ( ( $desc = $gateway->get_description() ) ): ?>
        <div class="wc-stripe-gateway-desc<?php if ( $tokens ): ?> has_tokens<?php endif; ?>">
			<?php echo wpautop( wptexturize( $desc ) ) //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
        </div>
	<?php endif; ?>
	<?php wc_stripe_get_template( 'checkout/' . $gateway->template_name, array( 'gateway' => $gateway ) ) ?>
</div>