import {BaseGateway, ProductGateway} from '@paymentplugins/wc-stripe';
import $ from 'jquery';
import LinkMixin from './link-mixin';

function Gateway(params) {
    this.type = 'link';
    this.container = 'li.payment_method_stripe_link_checkout';
    this.elementSelector = 'li.payment_method_stripe_link_checkout #wc-stripe-link-element';
    BaseGateway.call(this, params);
}

Gateway.prototype = Object.assign(Gateway.prototype, BaseGateway.prototype, ProductGateway.prototype);

class LinkExpressProduct extends LinkMixin(Gateway) {

    initialize() {
        this.modalOpen = false;
        ProductGateway.call(this);
        this.createExpressElement();
        this.mountPaymentElement();

        window.addEventListener('hashchange', this.onHashChange.bind(this));
    }

    onReady({availablePaymentMethods}) {
        const {link = false} = availablePaymentMethods || {};
        if (link) {
            this.addEvents();
            $(this.container).show().addClass('active');
            $(this.container).parent().parent().addClass('active');
        } else {
            $(this.container).hide();
        }
    }

    addEvents() {
        window.addEventListener('hashchange', this.onHashChange.bind(this));
        $(document.body).on('change', '[name="quantity"]', this.onQuantityChange.bind(this));
    }

    onClickElement(event) {
        if ($(this.elementSelector).is('.disabled')) {
            return event.reject();
        }
        if (!this.needs_shipping()) {
            this.add_to_cart();
        }
        super.onClickElement(event);
    }

    found_variation(e, variation) {
        const needsShipping = this.needs_shipping();
        this.params.product.price = variation.display_price;
        this.params.product.price_cents = variation.display_price_cents;
        this.params.needs_shipping = !variation.is_virtual;
        this.params.product.variation = variation;
        if (!variation.is_in_stock) {
            this.disableButton();
        } else {
            this.calculateCart();
        }
        if (this.expressCheckoutElement) {
            if (needsShipping !== this.needs_shipping()) {
                if (this.expressCheckoutElement) {
                    this.expressCheckoutElement.unmount();
                }
                // have to create a new elements instance since Stripe doesn't allow multiple expressCheckout elements
                // per element instance.
                this.elements = this.create_stripe_elements();
                this.createExpressElement();
                this.mountPaymentElement();
            }
        }
    }

    reset_variation_data() {
        this.params.product.variation = false
        this.disableButton();
    }

    disableButton() {
        $(this.elementSelector).addClass('disabled');
    }

    enableButton() {
        $(this.elementSelector).removeClass('disabled');
    }

    get_product_data() {
        return this.params.product;
    }

    onQuantityChange() {
        if (this.is_variable_product()) {
            if (!this.variable_product_selected()) {
                return;
            } else if (!this.params.product?.variation?.is_in_stock) {
                return;
            }
        }
        this.calculateCart();
    }

    async calculateCart() {
        try {
            this.disableButton();
            const response = await super.cart_calculation();
            if (response[this.gateway_id]) {
                const {
                    totalCents,
                    displayItems,
                    shippingOptions
                } = response[this.gateway_id];
                this.params.total_cents = parseFloat(totalCents);
                this.params.items = displayItems;
                if (this.needs_shipping()) {
                    this.params.shipping_options = shippingOptions;
                } else {
                    this.params.shipping_options = [];
                }
                this.elements.update({
                    amount: this.params.total_cents
                });
            }
        } catch (error) {
            return this.submit_error(error);
        } finally {
            this.enableButton();
        }
    }
}

if (typeof wc_stripe_link_product_params !== 'undefined') {
    new LinkExpressProduct(wc_stripe_link_product_params);
}