import {useState, useEffect} from '@wordpress/element';
import {registerPaymentMethod} from '@woocommerce/blocks-registry';
import {getSettings, initStripe, isTestMode} from "../util";
import {LocalPaymentIntentContent} from './local-payment-method';
import {OffsiteNotice, PaymentMethod, PaymentMethodLabel} from "../../components/checkout";
import {canMakePayment} from "./local-payment-method";
import {__} from "@wordpress/i18n";
import {PaymentMethodMessagingElement, Elements} from "@stripe/react-stripe-js";

const getData = getSettings('stripe_klarna_data');

const dispatchKlarnaChange = (options) => {
    document.dispatchEvent(new CustomEvent('stripeKlarnaChange', {
        detail: {options}
    }));
}

const KlarnaPaymentMethod = (props) => {
    return (
        <>
            <LocalPaymentIntentContent {...props}/>
            <OffsiteNotice paymentText={getData('title')} buttonText={getData('placeOrderButtonLabel')}/>
        </>
    )
}

const KlarnaPaymentMethodLabel = ({title, paymentMethod, icons, components}) => {
    const {PaymentMethodLabel: Label} = components;
    const [options, setOptions] = useState({
        amount: getData('cartTotals')?.value,
        currency: getData('currency'),
        paymentMethodTypes: ['klarna'],
        ...getData('messageOptions')
    });

    useEffect(() => {
        const updateOptions = (e) => {
            setOptions(e.detail.options);
        }
        document.addEventListener('stripeKlarnaChange', updateOptions);

        return () => document.removeEventListener('stripeKlarnaChange', updateOptions);
    }, []);

    if (!getData('paymentSections').includes('checkout')) {
        return (
            <PaymentMethodLabel
                paymentMethod={paymentMethod}
                title={title}
                icons={icons}
                components={components}/>
        )
    }

    return (
        <div className={'wc-stripe-label-container'}>
            <Label text={title}/>
            <div className={'wc-stripe-klarna-message-container'}>
                <Elements stripe={initStripe} options={getData('elementOptions')}>
                    <PaymentMethodMessagingElement options={options}/>
                </Elements>
            </div>
        </div>
    )
}

if (getData()) {
    registerPaymentMethod({
        name: getData('name'),
        label: <KlarnaPaymentMethodLabel
            title={getData('title')}
            paymentMethod={getData('name')}
            icons={getData('icon')}/>,
        ariaLabel: 'Klarna',
        placeOrderButtonLabel: getData('placeOrderButtonLabel'),
        canMakePayment: canMakePayment(getData, ({settings, billingData, cartTotals}) => {
            const {country} = billingData;
            const {currency_code: currency} = cartTotals;
            const requiredParams = settings('requiredParams');
            const amount = parseInt(cartTotals.total_price);
            const {currency_code} = cartTotals;

            dispatchKlarnaChange({
                amount: amount,
                currency: currency_code,
                countryCode: country
            });

            return [currency] in requiredParams && requiredParams[currency].includes(country);
        }),
        content: <PaymentMethod
            content={KlarnaPaymentMethod}
            getData={getData}
            confirmationMethod={'confirmKlarnaPayment'}/>,
        edit: <PaymentMethod content={LocalPaymentIntentContent} getData={getData}/>,
        supports: {
            showSavedCards: false,
            showSaveOption: false,
            features: getData('features')
        }
    })
}