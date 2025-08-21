import {registerPaymentMethod} from '@paymentplugins/wc-stripe/checkout';
import {BaseGateway, CheckoutGateway as StripeCheckoutGateway} from '@paymentplugins/wc-stripe';
import $ from 'jquery';
import {isEmail, isPhoneNumber} from "@wordpress/url";
import LinkMixin from './link-mixin';

function Gateway(params, elementSelector) {
    this.elementSelector = elementSelector;
    BaseGateway.call(this, params);
    StripeCheckoutGateway.call(this);
};

Gateway.prototype = Object.assign(Gateway.prototype, BaseGateway.prototype, StripeCheckoutGateway.prototype);

class LinkExpressCheckout extends LinkMixin(Gateway) {

    constructor(params, elementSelector) {
        super(params, elementSelector);
        this.setupIntent = null;
        this.paymentMethodType = null;
        this.paymentElementComplete = false;
    }

    initialize() {
        this.type = 'link';
        this.setupEvents();
        this.createExpressElement();
        this.mountPaymentElement();
    }

    setupEvents() {
        $(document.body).on('updated_checkout', this.onUpdatedCheckout.bind(this));
        window.addEventListener('hashchange', this.onHashChange.bind(this));
    }

    createExpressElement() {
        if (this.elements) {
            this.expressCheckoutElement = this.elements.create('expressCheckout', {
                buttonHeight: 44,
                paymentMethods: {
                    applePay: this.type === 'applePay' ? 'always' : 'never',
                    googlePay: this.type === 'googlePay' ? 'always' : 'never',
                    amazonPay: this.type === 'amazonPay' ? 'auto' : 'never',
                    paypal: 'never',
                    klarna: this.type === 'klarna' ? 'auto' : 'never',
                    link: this.type === 'link' ? 'auto' : 'never',
                },
                emailRequired: !isEmail($('#billing_email').val() ?? ''),
                phoneNumberRequired: $('#billing_phone').length > 0 && !isPhoneNumber($('#billing_phone').val() ?? ''),
                billingAddressRequired: true,
                shippingAddressRequired: this.needs_shipping()
            });

            this.expressCheckoutElement.on('ready', this.onReady.bind(this));
            this.expressCheckoutElement.on('loaderror', this.onLoadError.bind(this));
            this.expressCheckoutElement.on('click', this.onClickElement.bind(this));
            this.expressCheckoutElement.on('confirm', this.onConfirm.bind(this));
            this.expressCheckoutElement.on('cancel', this.onCancel.bind(this));
            this.expressCheckoutElement.on('shippingaddresschange', this.onShippingAddressChange.bind(this));
            this.expressCheckoutElement.on('shippingratechange', this.onShippingRateChange.bind(this));
        }
    }


    onReady({availablePaymentMethods}) {
        const {link = false} = availablePaymentMethods || {};
        if (link) {
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
        this.payment_token_received = true;
        this.set_nonce(paymentMethod.id);
        this.get_form().submit();
    }

}

new LinkExpressCheckout(wc_stripe_link_checkout_params, 'li.banner_payment_method_stripe_link_checkout');
