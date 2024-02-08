import {Elements, PaymentElement} from "@stripe/react-stripe-js";
import {useSelect} from '@wordpress/data';
import {CART_STORE_KEY} from '@woocommerce/block-data';
import {registerExpressPaymentMethod} from '@woocommerce/blocks-registry';
import {useLinkIcon, useLink} from "./hooks";
import {getSettings, initStripe} from "../util";
import ErrorBoundary from "./error-boundary";

const getData = getSettings('stripe_link_checkout_data');

export default function Block() {
    const cart = useSelect(select => {
        const store = select(CART_STORE_KEY);
        const data = store.getCartData();
        return data;
    })
    let options = {
        mode: 'payment',
        paymentMethodCreation: 'manual',
        amount: parseInt(cart.totals.total_price),
        currency: cart.totals.currency_code.toLowerCase(),
        payment_method_types: ['card', 'link']
    }
    if (parseInt(cart.totals.total_price) === 0) {
        options = {
            mode: 'setup',
            currency: cart.totals.currency_code.toLowerCase(),
        }
    }
    return (
        <ErrorBoundary>
            <Elements stripe={initStripe} options={options}>
                <LinkComponent cart={cart}/>
            </Elements>
        </ErrorBoundary>
    )
}

const LinkComponent = ({cart}) => {
    const {billingAddress} = cart;
    const {email} = billingAddress;
    const linkIcon = getData('linkIcon');
    const popupEnabled = getData('popupEnabled');

    if (popupEnabled) {
        useLink({
            email
        });
    }

    useLinkIcon({enabled: linkIcon, icon: linkIcon});

    const options = {
        fields: {
            billingDetails: {address: 'never'}
        },
        wallets: {applePay: 'never', googlePay: 'never'}
    };

    return (
        <div style={{display: 'none'}}>
            <PaymentElement options={options}/>
        </div>
    );
}

registerExpressPaymentMethod({
    name: getData('name'),
    canMakePayment: (props) => {
        return false;
    },
    content: <LinkComponent/>,
    edit: <LinkComponent/>,
    supports: {
        showSavedCards: getData('showSavedCards'),
        showSaveOption: getData('showSaveOption'),
        features: getData('features')
    }
})