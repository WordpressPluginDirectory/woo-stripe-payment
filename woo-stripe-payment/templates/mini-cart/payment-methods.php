<?php
/**
 * @version 3.2.0
 * @var WC_Payment_Gateway_Stripe[] $gateways
 */
defined( 'ABSPATH' ) || exit;
do_action( 'wc_stripe_minicart_before_payment_methods' );
?>
<input type="hidden" class="wc_stripe_mini_cart_payment_methods"/>
<?php foreach ( $gateways as $gateway ) : ?>
	<?php $gateway->mini_cart_fields() ?>
<?php endforeach; ?>



