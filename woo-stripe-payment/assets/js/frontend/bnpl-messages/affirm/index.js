import {BaseGateway, ProductGateway, stripe} from '@paymentplugins/wc-stripe';
import CartMessage from '../cart-message';
import CheckoutMessage from '../checkout-message';
import CategoryMessage from '../category-message';
import ProductMessage from '../product-message';

const elementType = 'affirmMessage';
const isSupported = (gateway) => {
    const currency = gateway?.get_gateway_data()?.currency;
    return gateway?.params?.supportedCurrencies?.includes(currency);
}

class AffirmGateway extends BaseGateway {
    constructor(params) {
        super(params);
    }
};

if (typeof wc_stripe_affirm_cart_params !== 'undefined') {
    new CartMessage(new AffirmGateway(wc_stripe_affirm_cart_params), {
        elementType,
        elementId: 'wc-stripe-affirm-cart-msg',
        containerId: 'wc-stripe-affirm-cart-container',
        isSupported
    });
}
if (typeof wc_stripe_affirm_product_params !== 'undefined') {
    Object.assign(AffirmGateway.prototype, ProductGateway.prototype);
    new ProductMessage(new AffirmGateway(wc_stripe_affirm_product_params), {
        elementType,
        elementId: 'wc-stripe-affirm-product-msg',
        isSupported
    });
}
if (typeof wc_stripe_local_payment_params !== 'undefined') {
    if (wc_stripe_local_payment_params?.gateways?.stripe_affirm) {
        new CheckoutMessage(new AffirmGateway(wc_stripe_local_payment_params.gateways.stripe_affirm), {
            elementType,
            elementId: 'wc-stripe-affirm-message-container',
            isSupported
        });
    }
}
if (typeof wc_stripe_bnpl_shop_params !== 'undefined') {
    new CategoryMessage(
        stripe,
        wc_stripe_bnpl_shop_params,
        {
            id: 'stripe_affirm',
            elementType,
            isSupported: (instance) => {
                const {currency} = instance.data;
                return instance.data.stripe_affirm.supportedCurrencies.includes(currency);
            }
        });
}