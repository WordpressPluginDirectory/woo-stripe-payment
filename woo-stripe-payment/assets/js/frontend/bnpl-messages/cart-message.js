import $ from 'jquery';
import AbstractMessage from './abstract-message';

class CartMessage extends AbstractMessage {

    constructor(gateway, props) {
        super(gateway, props);
        this.containerId = props.containerId;
        this.initialize();
    }

    initialize() {
        $(document.body).on('updated_wc_div', this.updatedHtml.bind(this));
        $(document.body).on('updated_cart_totals', this.updatedHtml.bind(this));
        this.createMessage();
    }

    updatedHtml() {
        if (this.gateway.has_gateway_data()) {
            this.createMessage();
        }
    }

    getElementContainer() {
        const $el = $(`#${this.elementId}`);
        if (!$el.length) {
            $('.cart_totals table.shop_table > tbody').append(`<tr id="${this.containerId}"><td colspan="2"><div id="${this.elementId}"></div></td></tr>`);
        }
        return document.getElementById(this.elementId);
    }
}

export default CartMessage;

