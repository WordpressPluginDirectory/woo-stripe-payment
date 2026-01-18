import {BaseGateway, CartGateway} from '@paymentplugins/wc-stripe';
import $ from 'jquery';
import PaymentRequestMixin from './payment-request-mixin';

function Gateway(params) {
    this.type = 'googlePay';
    this.elementSelector = 'a.wc-stripe-payment-request-mini-cart';
    BaseGateway.call(this, params);
}

Gateway.prototype = Object.assign(Gateway.prototype, BaseGateway.prototype, CartGateway.prototype);

class PaymentRequestMinicart extends PaymentRequestMixin(Gateway) {

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
        const {googlePay = false} = availablePaymentMethods || {};
        if (googlePay) {
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
            $('.woocommerce-mini-cart__buttons').prepend('<a class="wc-stripe-payment-request-mini-cart"></a>')
        } else if ($('.wc-stripe-mini-cart-idx-0').length) {
            this.elementSelector = '.wc-stripe-mini-cart-idx-0 .wc-stripe-payment-request-mini-cart';
            $('.wc-stripe-mini-cart-idx-0').prepend('<a class="wc-stripe-payment-request-mini-cart"></a>')
        }
    }

}

if (typeof wc_stripe_payment_request_mini_cart_params !== 'undefined') {
    setTimeout(() => {
        new PaymentRequestMinicart(wc_stripe_payment_request_mini_cart_params);
    }, 250);
}