import $ from 'jquery';
import {isEmail, isPhoneNumber} from "@wordpress/url";

export default function (Base) {
    return class extends Base {

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
                    emailRequired: true,
                    phoneNumberRequired: true,
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

        onClickElement(event) {
            // pass initial data.
            const data = {};

            const calculatedTotal = this.params.items.reduce((total, item) => total + item.amount, 0);

            if (calculatedTotal === parseFloat(this.params.total_cents)) {
                data.lineItems = this.params.items;
            }

            if (this.needs_shipping()) {
                if (this.params.shipping_options.length) {
                    data.shippingRates = this.params.shipping_options;
                }
            }

            event.resolve(data);

            this.modalOpen = true;
        }

        onReady() {
        }

        async onConfirm(event) {
            const {
                billingDetails,
                shippingAddress
            } = event;

            if (shippingAddress) {
                const {
                    name,
                    address
                } = shippingAddress;
                const sa = {
                    name,
                    shipping_address_1: address.line1 || '',
                    shipping_address_2: address.line2 || '',
                    shipping_city: address.city || '',
                    shipping_state: address.state || '',
                    shipping_postcode: address.postal_code || '',
                    shipping_country: address.country || ''
                }
                // update the address data on the checkout page.
                this.populate_shipping_fields(sa);
            }
            if (billingDetails) {
                const {
                    name,
                    email = '',
                    phone = '',
                    address
                } = billingDetails;
                const ba = {
                    name,
                    billing_address_1: address.line1 || '',
                    billing_address_2: address.line2 || '',
                    billing_city: address.city || '',
                    billing_state: address.state || '',
                    billing_postcode: address.postal_code || '',
                    billing_country: address.country || '',
                    ...(isEmail(email) && {
                        billing_email: email
                    }),
                    ...(isPhoneNumber(phone) && {
                        billing_phone: phone
                    })
                }
                this.populate_billing_fields(ba);
                this.maybe_set_ship_to_different();
                this.fields.toFormFields({update_shipping_method: false});

                // submit element
                try {
                    await this.elements.submit();
                    const response = await this.stripe.createPaymentMethod({
                        elements: this.elements
                    });
                    if (response.error) {
                        return this.submit_error(response.error);
                    }
                    this.on_token_received(response.paymentMethod);
                } catch (error) {
                    return this.submit_error(error);
                }
            }
        }

        async onShippingAddressChange(event) {
            const {
                reject,
                resolve,
                address
            } = event;
            try {
                const response = await this.update_shipping_address({
                    shippingAddress: address
                });
                const {
                    total,
                    displayItems,
                    shippingOptions
                } = response.newData;

                this.elements.update({
                    amount: total.amount
                });
                resolve({
                    shippingRates: shippingOptions,
                    ...(displayItems.reduce((total, item) => total + item.amount, 0) === total.amount && {
                        lineItems: displayItems
                    })
                });
            } catch (error) {
                reject();
            }
        }

        async onShippingRateChange(event) {
            const {
                reject,
                resolve,
                shippingRate
            } = event;

            try {
                const response = await this.update_shipping_method({
                    shippingOption: shippingRate
                });
                const {
                    total,
                    displayItems,
                    shippingOptions
                } = response.newData;

                this.elements.update({
                    amount: total.amount
                });
                resolve({
                    shippingRates: shippingOptions,
                    ...(displayItems.reduce((total, item) => total + item.amount, 0) === total.amount && {
                        lineItems: displayItems
                    })
                });
            } catch (error) {
                reject();
            }
        }

        onCancel() {
            this.modalOpen = false;
        }

        mountPaymentElement() {
            try {
                if (this.expressCheckoutElement && $(this.elementSelector).length) {
                    if ($(this.elementSelector).find('iframe').length === 0) {
                        this.expressCheckoutElement.unmount();
                        this.expressCheckoutElement.mount(this.elementSelector);
                    }
                }
            } catch (error) {
                console.log(`Error mounting expressCheckoutElement. `, error);
            }
        }

        updatePaymentElement() {
            try {
                if (this.elements) {
                    this.elements.update(this.get_element_options());
                }
            } catch (error) {
                console.log(`Error updating expressCheckoutElement. `, error);
            }
        }

        onLoadError(error) {
            $(this.elementSelector).hide();
            console.log('Error loading expressCheckout Element: ', error);
        }

        get_element_options() {
            const options = {
                currency: this.params.currency.toLowerCase(),
                ...this.params.elementOptions
            };
            delete options.paymentMethodTypes;
            if (this.isPaymentMode() || this.isSubscriptionMode()) {
                options.amount = parseFloat(this.params.total_cents) > 0 ? parseFloat(this.params.total_cents) : 100;
            }
            return options;
        }

        get_gateway_data() {
            return this.params;
        }

        isPaymentMode() {
            return this.params.elementOptions.mode === 'payment';
        }

        isSubscriptionMode() {
            return this.params.elementOptions.mode === 'subscription';
        }

        isSetupMode() {
            return this.params.elementOptions.mode === 'setup';
        }

        needs_shipping() {
            return this.params.needs_shipping === "1" || this.params.needs_shipping === true;
        }

        get_currency() {
            return this.params.currency;
        }

        map_address(address) {
            return {
                city: address.city,
                postcode: address.postal_code,
                state: address.state,
                country: address.country
            }
        }

        get_form() {
            return $(this.elementSelector).closest('form');
        }

        set_nonce(value) {
            if (!$('[name="stripe_link_checkout_token_key"]').length) {
                $(this.elementSelector).append('<input type="hidden" name="stripe_link_checkout_token_key"/>');
            }
            $('[name="stripe_link_checkout_token_key"]').val(value);
            this.fields.set(this.gateway_id + '_token_key', value);
        }

        onHashChange(e) {
            const match = window.location.hash.match(/response=(.*)/);
            if (match) {
                try {
                    const obj = JSON.parse(window.atob(decodeURIComponent(match[1])));
                    if (obj && obj.hasOwnProperty('client_secret')) {
                        if (obj.gateway_id === this.gateway_id && this.modalOpen) {
                            history.pushState({}, '', window.location.pathname);
                            if (obj.type === 'payment_intent') {
                                this.processPaymentIntent(obj);
                            } else {
                                this.processSetupIntent(obj);
                            }
                        }
                    }
                } catch (err) {

                }
            }
            return true;
        }

        processPaymentIntent(data) {
            this.stripe.confirmPayment({
                clientSecret: data.client_secret,
                redirect: 'if_required',
                confirmParams: {
                    return_url: data.return_url,
                    payment_method_data: {
                        billing_details: data.billing_details || this.get_billing_details()
                    },
                    ...(data.confirmation_args && data.confirmation_args)
                }
            }).then(response => {
                if (response.error) {
                    this.payment_token_received = false;
                    return this.submit_error(response.error);
                }
                let redirect = decodeURI(data.return_url);
                redirect += '&' + $.param({
                    '_stripe_local_payment': this.gateway_id,
                    payment_intent: response.paymentIntent.id,
                    payment_intent_client_secret: response.paymentIntent.client_secret
                });

                if (['promptpay', 'swish', 'paynow', 'cashapp'].includes(this.paymentMethodType)) {
                    if (response.paymentIntent.status === 'requires_action') {
                        return this.get_form().unblock().removeClass('processing');
                    }
                    if (response.paymentIntent.status === 'requires_payment_method') {
                        this.get_form().unblock().removeClass('processing');
                        return this.submit_error({code: response.paymentIntent.last_payment_error.code});
                    }
                }

                window.location.href = redirect;
            }).catch(error => {
                return this.submit_error(error);
            })
        }

        processSetupIntent(data = null) {
            return this.stripe.confirmSetup({
                elements: this.elements,
                clientSecret: data.client_secret,
                redirect: 'if_required',
                ...(data && {
                    confirmParams: {
                        ...(data.return_url && {
                            return_url: data.return_url
                        }),
                        payment_method_data: {
                            billing_details: this.get_billing_details()
                        },
                        ...(data.confirmParams && data.confirmParams)
                    }
                })
            }).then(response => {
                if (response.error) {
                    this.payment_token_received = false;
                    return this.submit_error(response.error);
                }
                this.setupIntent = response.setupIntent;
                this.payment_token_received = true;
                this.set_nonce(response.setupIntent.payment_method);
                this.set_intent(response.setupIntent.id);

                this.get_form().removeClass('processing');
                this.get_form().trigger('submit');
            }).catch(error => {
                return this.submit_error(error);
            });
        }
    }
}