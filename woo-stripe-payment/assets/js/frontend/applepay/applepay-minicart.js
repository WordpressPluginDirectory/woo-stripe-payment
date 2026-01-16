import {BaseGateway, CartGateway} from '@paymentplugins/wc-stripe';
import $ from 'jquery';
import ApplePayMixin from './applepay-mixin';

function Gateway(params) {
    this.type = 'applePay';
    this.elementSelector = 'a.wc-stripe-applepay-mini-cart';
    BaseGateway.call(this, params);
}

Gateway.prototype = Object.assign(Gateway.prototype, BaseGateway.prototype, CartGateway.prototype);

class ApplePayMiniCart extends ApplePayMixin(Gateway) {

    constructor(props) {
        super(props);
    }

    initialize() {
        this.modalOpen = false;
        this.createExpressElement();
        this.mountPaymentElement();

        window.addEventListener('hashchange', this.onHashChange.bind(this));
        $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', this.onFragmentsRefreshed.bind(this));
    }

    onReady({availablePaymentMethods}) {
        const {applePay = false} = availablePaymentMethods || {};
        if (applePay) {
            $(this.elementSelector).show();
        } else {
            $(this.elementSelector).hide();
        }
    }

    set_selected_shipping_methods(shipping_methods) {
        this.fields.set('shipping_method', shipping_methods);
    }

    onFragmentsRefreshed() {
        this.mountPaymentElement();
    }

    createElementSelectorHTML() {
        if ($('.woocommerce-mini-cart__buttons').length) {
            $('.woocommerce-mini-cart__buttons').prepend('<a class="wc-stripe-applepay-mini-cart"></a>')
        } else if ($('.wc-stripe-mini-cart-idx-0').length) {
            this.elementSelector = '.wc-stripe-mini-cart-idx-0 .wc-stripe-applepay-mini-cart';
            $('.wc-stripe-mini-cart-idx-0').prepend('<a class="wc-stripe-applepay-mini-cart"></a>')
        }
    }

}

if (typeof wc_stripe_applepay_mini_cart_params !== 'undefined') {
    setTimeout(() => {
        new ApplePayMiniCart(wc_stripe_applepay_mini_cart_params);
    }, 250);
}