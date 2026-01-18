import {BaseGateway, CartGateway} from '@paymentplugins/wc-stripe';
import $ from 'jquery';
import PaymentRequestMixin from './payment-request-mixin';

function Gateway(params) {
    this.type = 'googlePay';
    this.elementSelector = 'li.payment_method_stripe_payment_request #wc-stripe-payment-request-container';
    BaseGateway.call(this, params);
}

Gateway.prototype = Object.assign(Gateway.prototype, BaseGateway.prototype, CartGateway.prototype);

class PaymentRequestCart extends PaymentRequestMixin(Gateway) {

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
        const {googlePay = false} = availablePaymentMethods || {};
        if (googlePay) {
            $('li.payment_method_stripe_payment_request').show().addClass('active');
            $('.wc_stripe_cart_payment_methods').addClass('active');
            this.add_cart_totals_class();
        } else {
            $(this.elementSelector).hide();
        }
    }

    updated_html(e) {
        const data = $('.woocommerce_' + this.gateway_id + '_gateway_data').data('gateway');
        if (typeof data === 'object') {
            this.params = {...this.params, ...data};
        }
        this.updatePaymentElement();
        this.mountPaymentElement();
    }

    set_selected_shipping_methods(shipping_methods) {
        this.fields.set('shipping_method', shipping_methods);
    }

    set_nonce(value) {
        super.set_nonce(value);
        this.fields.set('stripe_payment_request_token_key', value);
    }

}

if (typeof wc_stripe_payment_request_cart_params !== 'undefined') {
    new PaymentRequestCart(wc_stripe_payment_request_cart_params);
}