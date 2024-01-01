import {BaseGateway, ProductGateway, stripe} from '@paymentplugins/wc-stripe';
import CartMessage from '../cart-message';
import CheckoutMessage from '../checkout-message';
import CategoryMessage from '../category-message';
import ProductMessage from '../product-message';
import Mixin from './afterpay-mixin';

const elementType = 'afterpayClearpayMessage';
const isSupported = (gateway) => {
    return true;
}

class AfterpayGateway extends BaseGateway {
    constructor(params) {
        super(params);
    }
};

if (typeof wc_stripe_afterpay_cart_params !== 'undefined') {
    const clazz = Mixin(CartMessage);
    new clazz(new AfterpayGateway(wc_stripe_afterpay_cart_params), {
        elementType,
        elementId: 'wc-stripe-afterpay-cart-msg',
        containerId: 'wc-stripe-afterpay-cart-container',
        isSupported
    });
}
if (typeof wc_stripe_afterpay_product_params !== 'undefined') {
    Object.assign(AfterpayGateway.prototype, ProductGateway.prototype);
    new ProductMessage(new AfterpayGateway(wc_stripe_afterpay_product_params), {
        elementType,
        elementId: 'wc-stripe-afterpay-product-msg',
        isSupported
    });
}
if (typeof wc_stripe_local_payment_params !== 'undefined') {
    if (wc_stripe_local_payment_params?.gateways?.stripe_afterpay) {
        const clazz = Mixin(CheckoutMessage);
        new clazz(new AfterpayGateway(wc_stripe_local_payment_params.gateways.stripe_afterpay), {
            elementType,
            elementId: 'wc-stripe-afterpay-message-container',
            isSupported
        });
    }
}
if (typeof wc_stripe_bnpl_shop_params !== 'undefined') {
    new CategoryMessage(stripe, wc_stripe_bnpl_shop_params, {
        id: 'stripe_afterpay',
        elementType,
        isSupported: (instance) => {
            const {currency} = instance.data;
            return instance.data.stripe_afterpay.supportedCurrencies.includes(currency);
        },
        isSupportedProduct: (instance, product) => {
            const price = product.price;
            const {currency} = instance.data;
            const {hideIneligible} = instance.data.stripe_afterpay;
            const [country, min, max] = instance.data[instance.id].requiredParams[currency];
            return !hideIneligible || min <= price && price <= max;
        }
    });
}