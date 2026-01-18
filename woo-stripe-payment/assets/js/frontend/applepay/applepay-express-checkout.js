import {BaseGateway, CheckoutGateway as StripeCheckoutGateway} from '@paymentplugins/wc-stripe';
import $ from 'jquery';
import {isEmail, isPhoneNumber} from "@wordpress/url";
import ApplePayMixin from './applepay-mixin';

function Gateway(params, elementSelector) {
    this.elementSelector = elementSelector;
    BaseGateway.call(this, params);
    this.message_container = 'li.payment_method_' + this.gateway_id;
    this.banner_container = 'li.banner_payment_method_' + this.gateway_id;
};

Gateway.prototype = Object.assign(Gateway.prototype, BaseGateway.prototype, StripeCheckoutGateway.prototype);

class ApplePayExpressCheckout extends ApplePayMixin(Gateway) {

    constructor(params, elementSelector) {
        super(params, elementSelector);
        this.setupIntent = null;
        this.paymentMethodType = null;
        this.paymentElementComplete = false;
    }

    initialize() {
        this.type = 'applePay';
        this.setupEvents();
        this.createExpressElement();
        this.mountPaymentElement();
    }

    setupEvents() {
        $(document.body).on('updated_checkout', this.onUpdatedCheckout.bind(this));
        window.addEventListener('hashchange', this.onHashChange.bind(this));
    }

    mountPaymentElement() {
        if ($('li.payment_method_' + this.gateway_id).length) {
            super.mountPaymentElement();
        } else {
            if (this.expressCheckoutElement) {
                this.expressCheckoutElement.unmount();
            }
        }
    }

    onReady({availablePaymentMethods}) {
        const {applePay = false} = availablePaymentMethods || {};
        if (applePay) {
            $(this.elementSelector).show().addClass('active');
            $('.wc-stripe-banner-checkout').addClass('active');
        } else {
            $(this.elementSelector).hide();
        }
    }

    onClickElement(event) {
        super.onClickElement(event);
        $('[name="terms"]').prop('checked', true);
    }

    onUpdatedCheckout(e, data) {
        if (data && data?.fragments?.[this.gateway_id]) {
            this.params = data.fragments[this.gateway_id];
            this.updatePaymentElement();
        }
        this.mountPaymentElement();
    }

    on_token_received(paymentMethod) {
        $('[name="payment_method"]').val(this.gateway_id);
        this.maybe_set_ship_to_different();
        this.fields.toFormFields({update_shipping_method: false});
        this.payment_token_received = true;
        this.set_nonce(paymentMethod.id);
        $(document.body).triggerHandler('wc_stripe_applepay_payment_method_received', [paymentMethod]);
        this.get_form().submit();
    }

    getExpressElementOptions() {
        this.expressElementOptions = super.getExpressElementOptions();
        this.expressElementOptions.emailRequired = !isEmail($('#billing_email').val() ?? '');
        this.expressElementOptions.phoneNumberRequired = $('#billing_phone').length > 0 && !isPhoneNumber($('#billing_phone').val() ?? '');
        return this.expressElementOptions;
    }
}

new ApplePayExpressCheckout(
    wc_stripe_applepay_checkout_params,
    'li.banner_payment_method_stripe_applepay'
);
