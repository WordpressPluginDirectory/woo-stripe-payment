import {BaseGateway, ProductGateway, stripe} from '@paymentplugins/wc-stripe';
import CartMessage from '../cart-message';
import CheckoutMessage from './klarna-checkout-message';
import CategoryMessage from '../category-message';
import ProductMessage from '../product-message';

const elementType = 'paymentMethodMessaging';

class KlarnaGateway extends BaseGateway {
    constructor(params) {
        super(params);
    }
};

if (typeof wc_stripe_klarna_cart_params !== 'undefined') {
    new CartMessage(new KlarnaGateway(wc_stripe_klarna_cart_params), {
        elementType,
        elementId: 'wc-stripe-klarna-cart-msg',
        containerId: 'wc-stripe-klarna-cart-container'
    });
}
if (typeof wc_stripe_klarna_product_params !== 'undefined') {
    Object.assign(KlarnaGateway.prototype, ProductGateway.prototype);
    new ProductMessage(new KlarnaGateway(wc_stripe_klarna_product_params), {
        elementType,
        elementId: 'wc-stripe-klarna-product-msg'
    });
}
if (typeof wc_stripe_local_payment_params !== 'undefined') {
    if (wc_stripe_local_payment_params?.gateways?.stripe_klarna) {
        new CheckoutMessage(new KlarnaGateway(wc_stripe_local_payment_params.gateways.stripe_klarna), {
            elementType,
            elementId: 'wc-stripe-klarna-message-container'
        });
    }
}
if (typeof wc_stripe_bnpl_shop_params !== 'undefined') {
    new CategoryMessage(
        stripe,
        wc_stripe_bnpl_shop_params,
        {
            id: 'stripe_klarna',
            elementType,
            isSupported: () => true
        });
}