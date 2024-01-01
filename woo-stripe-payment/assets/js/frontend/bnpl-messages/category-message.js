class CategoryMessage {

    constructor(
        stripe,
        data,
        props
    ) {
        this.id = props.id;
        this.elementType = props.elementType;
        this.props = props;
        this.stripe = stripe;
        this.elements = stripe.elements({locale: 'auto'});
        this.data = data;
        this.initialize();
    }

    initialize() {
        this.createMessages();
    }

    createMessages() {
        if (this.props.isSupported(this)) {
            for (const product of this.data.products) {
                if (this.isSupportedProduct(product)) {
                    this.createMessage(product);
                }
            }
        }
    }

    createMessage(product) {
        try {
            const element = this.elements.create(this.elementType, this.getMessageOptions(product));
            const el = this.getMessageContainer(product);
            if (el) {
                element.mount(el);
            }
        } catch (error) {
            console.log(error);
        }
    }

    isSupportedProduct(product) {
        let result = this.data.product_types.includes(product.product_type);
        if (result && this.props.isSupportedProduct) {
            result = this.props.isSupportedProduct(this, product);
        }
        return result;
    }

    getMessageContainer(product) {
        let id = `${this.id}-${product.id}`;
        id = `wc-stripe-shop-message-${id}`;
        return document.getElementById(`${id}`);
    }

    getMessageOptions(product) {
        return {
            amount: product.price_cents,
            currency: this.data.currency,
            ...this.data[this.id].messageOptions
        }
    }
}

export default CategoryMessage;