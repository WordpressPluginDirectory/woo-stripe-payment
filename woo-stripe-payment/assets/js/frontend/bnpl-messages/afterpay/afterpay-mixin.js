export default function (Base) {
    return class Afterpay extends Base {

        getMessageOptions() {
            return {
                ...super.getMessageOptions(),
                isEligible: this.isEligible(this.getTotalPrice())
            }
        }

        isEligible(price) {
            return (price >= this.getMin() && price <= this.getMax());
        }

        getMin() {
            const currency = this.gateway.get_currency();
            const params = this.gateway.params.requirements[currency];
            return params ? params[1] : 0;
        }

        getMax() {
            const currency = this.gateway.get_currency();
            const params = this.gateway.params.requirements[currency];
            return params ? params[2] : 0;
        }
    }
}

