import $ from 'jquery';
import AbstractMessage from "./abstract-message";

class CheckoutMessage extends AbstractMessage {

    constructor(...params) {
        super(...params);
        this.initialize();
    }

    isEnabled() {
        return this.gateway.params.payment_sections.includes('checkout');
    }

    initialize() {
        if (this.isEnabled()) {
            $(document.body).on('updated_checkout', this.updatedCheckout.bind(this));
            if (this.gateway.has_gateway_data()) {
                this.createMessage();
            }
        }
    }

    updatedCheckout() {
        this.createMessage();
    }

    createMessage() {
        if (this.gateway.has_gateway_data()) {
            super.createMessage();
        }
    }

    getElementContainer() {
        if (!$(`#${this.elementId}`).length) {
            $(`label[for="payment_method_${this.gateway.gateway_id}"]`).append(`<div id="${this.elementId}"></div>`);
        }
        return document.getElementById(this.elementId);
    }
}

export default CheckoutMessage;