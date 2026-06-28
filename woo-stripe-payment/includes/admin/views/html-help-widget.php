<?php
defined( 'ABSPATH' ) || exit;
?>
<div id="pp-help-widget">
    <div id="pp-help-dropdown" style="display:none;">
        <a href="https://paymentplugins.com/documentation/stripe/" target="_blank" id="pp-help-docs">
            <svg width="16" height="16" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2 2.5A1.5 1.5 0 0 1 3.5 1h7A1.5 1.5 0 0 1 12 2.5v9a1.5 1.5 0 0 1-1.5 1.5h-7A1.5 1.5 0 0 1 2 11.5v-9Z"
                      stroke="currentColor" stroke-width="1.1"/>
                <path d="M4.5 4.5h5M4.5 7h5M4.5 9.5h3" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/>
            </svg>
			<?php _e( 'Documentation', 'woo-stripe-payment' ); ?>
            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.5 8.5 8.5 1.5M8.5 1.5H4M8.5 1.5V6" stroke="currentColor" stroke-width="1.1"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <div class="pp-help-divider"></div>
        <button id="pp-help-contact">
            <svg width="16" height="16" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.5 9.5C2.5 6.5 11.5 6.5 11.5 9.5" stroke="currentColor" stroke-width="1.1"
                      stroke-linecap="round"/>
                <circle cx="7" cy="4.5" r="2" stroke="currentColor" stroke-width="1.1"/>
            </svg>
			<?php _e( 'Contact Us', 'woo-stripe-payment' ); ?>
        </button>
    </div>
    <button id="pp-help-toggle">
        <svg width="18" height="18" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.1"/>
            <path d="M5.5 5.5a1.5 1.5 0 0 1 3 .5c0 1-1.5 1.5-1.5 2" stroke="currentColor" stroke-width="1.1"
                  stroke-linecap="round"/>
            <circle cx="7" cy="10" r=".6" fill="currentColor"/>
        </svg>
		<?php _e( 'Help', 'woo-stripe-payment' ); ?>
        <svg class="pp-help-chevron" width="10" height="10" viewBox="0 0 10 10" fill="none"
             xmlns="http://www.w3.org/2000/svg">
            <path d="M2 3.5l3 3 3-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                  stroke-linejoin="round"/>
        </svg>
    </button>
</div>