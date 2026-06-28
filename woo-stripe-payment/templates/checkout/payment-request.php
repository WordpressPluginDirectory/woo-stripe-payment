<?php
/**
 * @var WC_Payment_Gateway_Stripe $gateway
 * @version 3.3.70
 *
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wc-stripe-payment-request-container">
	<?php wc_stripe_get_template( 'wallet-notice.php', array( 'gateway' => $gateway ) ); ?>
</div>