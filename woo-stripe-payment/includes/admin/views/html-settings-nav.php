<?php
defined( 'ABSPATH' ) || exit;

global $current_section;
$tabs       = apply_filters( 'wc_stripe_settings_nav_tabs', array() );
$last       = count( $tabs );
$idx        = 0;
$tab_active = false;
?>
<div class="wc-stripe-header">
    <div class="wc-stripe-header__row">
        <div class="wc-stripe-header__logo">
            <img alt="Payment Plugins"
                 src="<?php echo stripe_wc()->assets()->assets_url( 'img/logo_v2.svg' ) ?>"/>
        </div>
        <div class="wc-stripe-header__logo-stripe">
            <span class="wc-stripe-header__poweredby"><?php echo esc_html__( 'Powered by', 'woo-stripe-payment' ) ?></span>&nbsp;&nbsp;
            <img alt="PaymentPlugins\Vendor\Stripe"
                 src="<?php echo stripe_wc()->assets()->assets_url( 'img/stripe_logo.svg' ) ?>"/>
        </div>
    </div>
    <div class="wc-stripe-header__row">
        <nav class="wc-stripe-nav">
			<?php foreach ( $tabs as $id => $tab ) : $idx ++ ?>
                <a class="wc-stripe-nav__item <?php echo $id ?><?php if ( $current_section === $id || ( ! $tab_active && $last === $idx ) ) {
					echo ' active';
					$tab_active = true;
				} ?>"
                   href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $id ); ?>"><?php echo esc_attr( $tab ); ?></a>
			<?php endforeach; ?>
        </nav>
    </div>
</div>