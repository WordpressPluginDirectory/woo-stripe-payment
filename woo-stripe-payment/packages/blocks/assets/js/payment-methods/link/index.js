import {Elements, PaymentElement} from "@stripe/react-stripe-js";
import {registerExpressPaymentMethod} from '@woocommerce/blocks-registry';
import {useLink, useLinkIcon} from './hooks';
import {getSettings, initStripe as loadStripe} from "../util";

const getData = getSettings('stripe_link_checkout_data');

export const LinkComponent = (props) => {
    const {cartTotal, currency} = props.billing;
    let options = {
        mode: 'payment',
        paymentMethodCreation: 'manual',
        amount: cartTotal.value,
        currency: currency.code.toLowerCase(),
        payment_method_types: ['card', 'link']
    }
    if (cartTotal.value === 0) {
        options = {
            mode: 'setup',
            currency: currency.code.toLowerCase(),
        }
    }
    return (
        <Elements stripe={loadStripe} options={options}>
            <LinkCheckout {...props}/>
        </Elements>
    );
    return null;
}

const LinkCheckout = (
    {
        billing,
        shipping,
        eventRegistration,
        onClick,
        onSubmit,
        onError,
        activePaymentMethod,
        emitResponse,
        ...props
    }) => {
    const {billingAddress} = billing;
    const {email} = billingAddress;
    const iconEnabled = getData('linkIconEnabled');
    const linkIcon = getData('linkIcon');

    useLink({
        email,
        eventRegistration,
        onClick,
        onSubmit,
        onError,
        activePaymentMethod,
        emitResponse,
        paymentStatus: props.paymentStatus
    });

    useLinkIcon({enabled: linkIcon, email, icon: linkIcon});

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
        return true;
    },
    content: <LinkComponent/>,
    edit: <LinkComponent/>,
    supports: {
        showSavedCards: getData('showSavedCards'),
        showSaveOption: getData('showSaveOption'),
        features: getData('features')
    }
})