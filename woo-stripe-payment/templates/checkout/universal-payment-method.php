<?php
/**
 * @version 4.0.15
 */
defined( 'ABSPATH' ) || exit;
?>
<div id="wc-stripe-upm-element"></div>
<?php if ( $gateway->show_save_payment_method_html() ): ?>
	<?php wc_stripe_get_template( 'save-payment-method.php', array( 'gateway' => $gateway ) ) ?>
<?php endif; ?>