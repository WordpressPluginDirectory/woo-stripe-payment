import $ from 'jquery'
import CheckoutMessage from '../checkout-message';

class KlarnaCheckoutMessage extends CheckoutMessage {

    getMessageOptions() {
        const options = super.getMessageOptions();
        const billingCountry = $('#billing_country').val();
        if (typeof billingCountry === 'string' && billingCountry.length > 0) {
            options.countryCode = billingCountry;
        }
        return options;
    }
}

export default KlarnaCheckoutMessage;