class AbstractMessage {

    constructor(
        gateway,
        {
            elementType = '',
            elementId = '',
            isSupported = () => true
        }
    ) {
        this.gateway = gateway;
        this.elementType = elementType;
        this.elementId = elementId;
        this.isSupported = isSupported;
        this.msgElement = null;
    }

    getElementType() {
        return this.elementType;
    }

    createMessage() {
        if (this.isSupported(this.gateway) && this.createMessageElement()) {
            const el = this.getElementContainer();
            if (el) {
                this.mount(el);
            }
        }
    }

    createMessageElement() {
        try {
            if (this.msgElement) {
                this.msgElement.update(this.getMessageOptions());
            } else {
                this.msgElement = this.gateway?.elements?.create(this.getElementType(), this.getMessageOptions());
            }
        } catch (error) {
            //console.log(error);
        }
        return this.msgElement;
    }

    getMessageOptions() {
        return {
            amount: this.getTotalPriceCents(),
            currency: this.gateway.get_currency(),
            ...this.gateway.params.messageOptions
        }
    }

    mount(el) {
        try {
            this.msgElement.mount(el);
        } catch (error) {
            console.log(error);
        }

    }

    getElementContainer() {

    }

    getTotalPriceCents() {
        return this.gateway.get_gateway_data()?.total_cents;
    }

    getTotalPrice() {
        return this.gateway.get_gateway_data()?.total;
    }
}

export default AbstractMessage;