import {BaseGateway, CartGateway} from '@paymentplugins/wc-stripe';
import $ from 'jquery';
import {isEmail, isPhoneNumber} from "@wordpress/url";
import LinkMixin from './link-mixin';

function Gateway(params) {
    this.type = 'link';
    this.elementSelector = 'li.payment_method_stripe_link_checkout';
    BaseGateway.call(this, params);
}

Gateway.prototype = Object.assign(Gateway.prototype, BaseGateway.prototype, CartGateway.prototype);

class LinkExpressCart extends LinkMixin(Gateway) {

    constructor(props) {
        super(props);
    }

    initialize() {
        this.modalOpen = false;
        CartGateway.call(this);
        this.createExpressElement();
        this.mountPaymentElement();

        window.addEventListener('hashchange', this.onHashChange.bind(this));
    }

    onReady({availablePaymentMethods}) {
        const {link = false} = availablePaymentMethods || {};
        if (link) {
            $(this.elementSelector).show().addClass('active');
            $('.wc-stripe-banner-checkout').addClass('active');
            this.add_cart_totals_class();
        } else {
            $(this.elementSelector).hide();
        }
    }

    updated_html(e) {
        const data = $('.woocommerce_' + this.gateway_id + '_gateway_data').data('gateway');
        if (typeof data === 'object') {
            this.params = data;
        }
        this.updatePaymentElement();
        this.mountPaymentElement();
    }

    set_selected_shipping_methods(shipping_methods) {
        this.fields.set('shipping_method', shipping_methods);
    }

    set_nonce(value) {
        super.set_nonce(value);
        this.fields.set('stripe_link_checkout_token_key', value);
    }

}

if (typeof wc_stripe_link_cart_params !== 'undefined') {
    new LinkExpressCart(wc_stripe_link_cart_params);
}