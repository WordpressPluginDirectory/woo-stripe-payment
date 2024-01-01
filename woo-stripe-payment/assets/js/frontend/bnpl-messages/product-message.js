import $ from 'jquery';
import AbstractMessage from './abstract-message';

class ProductMessage extends AbstractMessage {

    constructor(...params) {
        super(...params);
        this.initialize();
    }

    initialize() {
        $(document.body).on('change', '[name="quantity"]', this.createMessage.bind(this, true));
        $('form.cart').on('found_variation', this.foundVariation.bind(this));
        $('form.cart').on('reset_data', this.resetVariationData.bind(this));
        this.createMessage();
    }

    foundVariation(...params) {
        this.gateway.found_variation(...params);
        this.createMessage();
    }

    resetVariationData(...params) {
        this.gateway.reset_variation_data(...params);
        this.createMessage();
    }

    getElementContainer() {
        const $el = $(`#${this.elementId}`);
        if (!$el.length) {
            if ($('.summary .price').length) {
                $('.summary .price').append(`<div id="${this.elementId}"></div>`);
            } else {
                if ($('.price').length) {
                    $($('.price')[0]).append(`<div id="${this.elementId}"></div>`);
                }
            }
        }
        return document.getElementById(this.elementId);
    }

    getTotalPriceCents() {
        if (this.gateway.has_gateway_data()) {
            const price = this.gateway.get_gateway_data()?.product?.price_cents * this.getQuantity();
            return price;
        }
        return 0;
    }

    getQuantity() {
        let qty = $('[name="quantity"]').val();
        if (isNaN(qty)) {
            qty = 0;
        }
        return parseInt(qty);
    }
}

export default ProductMessage;