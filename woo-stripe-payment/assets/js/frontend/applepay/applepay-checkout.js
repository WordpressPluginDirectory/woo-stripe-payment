import {BaseGateway, CheckoutGateway as StripeCheckoutGateway} from '@paymentplugins/wc-stripe';
import $ from 'jquery';
import {isEmail, isPhoneNumber} from "@wordpress/url";
import ApplePayMixin from './applepay-mixin';

function Gateway(params, container) {
    this.container = container;
    this.elementSelector = '.wc-stripe-applepay-button';
    this.expressElementOptions = null;
    this._oldExpressElementOptions = null;
    this.setupIntent = null;
    this.paymentMethodType = null;
    this.paymentElementComplete = false;
    BaseGateway.call(this, params);
    StripeCheckoutGateway.call(this);
};

Gateway.prototype = Object.assign(Gateway.prototype, BaseGateway.prototype, StripeCheckoutGateway.prototype);

class ApplePayCheckout extends ApplePayMixin(Gateway) {

    constructor(params, elementSelector) {
        super(params, elementSelector);
    }

    initialize() {
        this.type = 'applePay';
        this.setupEvents();
        this.createExpressElement();
        this.mountPaymentElement();
    }

    setupEvents() {
        $(document.body).on('updated_checkout', this.onUpdatedCheckout.bind(this));
        $(document.body).on('change', '[name^="billing_"], [name^="shipping_"]', this.onAddressFieldChange.bind(this));
        $(document.body).on('wc_stripe_applepay_payment_method_received', (e, paymentMethod) => {
            wc_stripe.CheckoutGateway.prototype.on_token_received.call(this, paymentMethod);
        });
        window.addEventListener('hashchange', this.onHashChange.bind(this));
    }

    createElementSelectorHTML() {
        this.$button = $('<div class="wc-stripe-applepay-button" style="clear:both"></div>');
        $('#place_order').after(this.$button);
    }

    onClickElement(event) {
        if (!this.is_valid_checkout()) {
            this.submit_error(this.params.messages.terms);
            return event.reject();
        }
        super.onClickElement(event);
    }

    onReady({availablePaymentMethods}) {
        const {applePay = false} = availablePaymentMethods || {};
        if (applePay) {
            $(this.container).show().addClass('active');
            this.trigger_payment_method_selected();
        } else {
            $(this.container).hide();
        }
    }

    onUpdatedCheckout(e, data) {
        if (data && data?.fragments?.[this.gateway_id]) {
            this.params = {...this.params, ...data.fragments[this.gateway_id]};
            this.updatePaymentElement();
        }
        if (this.expressCheckoutElement) {
            // If the modal is open the elements instance should not be re-created
            // because it's needed later for payment method creation.
            if (!this.isModalOpen()) {
                if (JSON.stringify(this._oldExpressElementOptions) !== JSON.stringify(this.getExpressElementOptions())) {
                    this.expressCheckoutElement.unmount();
                    this.elements = this.create_stripe_elements();
                    this.createExpressElement();
                }
            }
            this.mountPaymentElement();
        }
    }

    onAddressFieldChange(e) {
        if (JSON.stringify(this._oldExpressElementOptions) !== JSON.stringify(this.getExpressElementOptions())) {
            this.expressCheckoutElement.unmount();
            this.elements = this.create_stripe_elements();
            this.createExpressElement();
            this.mountPaymentElement();
        }
    }

    on_token_received(paymentMethod) {
        wc_stripe.CheckoutGateway.prototype.on_token_received.apply(this, arguments);
        if (this.expressElementOptions.shippingAddressRequired) {
            this.maybe_set_ship_to_different();
        }
        this.fields.toFormFields({update_shipping_method: false});
        if (this._updateRequired) {
            $(document.body).one('updated_checkout', () => {
                if (this.checkout_fields_valid()) {
                    this.get_form().trigger('submit');
                }
            });
        } else {
            if (this.checkout_fields_valid()) {
                this.get_form().trigger('submit');
            }
        }
    }

    getExpressElementOptions() {
        this._oldExpressElementOptions = {...this.expressElementOptions};
        this.expressElementOptions = super.getExpressElementOptions();
        this.expressElementOptions.emailRequired = !isEmail($('#billing_email').val() ?? '');
        this.expressElementOptions.phoneNumberRequired = $('#billing_phone').length > 0 && !isPhoneNumber($('#billing_phone').val() ?? '');
        if (this.expressElementOptions.shippingAddressRequired) {
            // if the address is complete, we don't need to collect address info
            var prefix = this.get_shipping_prefix();
            if (this.is_valid_address(this.get_address_object(prefix), prefix, ['email', 'phone'])) {
                this.expressElementOptions.shippingAddressRequired = false;
            }
        }
        const billingAddress = this.get_address_object('billing');
        if (this.is_valid_address(billingAddress, 'billing', ['email'])) {
            this.expressElementOptions.billingAddressRequired = false;
        }
        return this.expressElementOptions;
    }

    async update_shipping_address() {
        this._updateRequired = true;
        return await wc_stripe.BaseGateway.prototype.update_shipping_address.apply(this, arguments);
    }

    set_selected_shipping_methods(shipping_methods) {
        this.fields.set('shipping_method', shipping_methods);
        for (let index of Object.keys(shipping_methods)) {
            const method = shipping_methods[index];
            const el = $(`[name="shipping_method[${index}]"][value="${method}"]`);
            if (el.length) {
                el.prop('checked', true);
            } else {
                this._updateRequired = true;
            }
        }
    }

}

new ApplePayCheckout(
    wc_stripe_applepay_checkout_params,
    'li.payment_method_stripe_applepay'
);
