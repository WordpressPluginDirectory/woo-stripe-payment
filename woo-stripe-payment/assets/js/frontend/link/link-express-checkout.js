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
                buttonHeight: parseInt(this.params.button.height),
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

    mountPaymentElement() {
        if (parseInt(this.params.total_cents) === 0) {
            if (this.isSetupMode()) {
                super.mountPaymentElement();
            } else {
                if (this.expressCheckoutElement) {
                    this.expressCheckoutElement.unmount();
                }
            }
        } else {
            super.mountPaymentElement();
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
        this.maybe_set_ship_to_different();
        this.fields.toFormFields({update_shipping_method: false});
        this.payment_token_received = true;
        this.set_nonce(paymentMethod.id);
        this.get_form().submit();
    }

    /**
     * Overrides the default method. Some themes or custom code moves the express checkout section outside
     * the checkout form. It's important that the payment method ID is inside the form when it's submitted.
     * @param value
     */
    set_nonce(value) {
        if (!$('form.checkout [name="stripe_link_checkout_token_key"]').length) {
            $('form.checkout').append('<input type="hidden" name="stripe_link_checkout_token_key"/>');
        }
        $('form.checkout [name="stripe_link_checkout_token_key"]').val(value);
        this.fields.set(this.gateway_id + '_token_key', value);
    }

}

new LinkExpressCheckout(wc_stripe_link_checkout_params, 'li.banner_payment_method_stripe_link_checkout');
