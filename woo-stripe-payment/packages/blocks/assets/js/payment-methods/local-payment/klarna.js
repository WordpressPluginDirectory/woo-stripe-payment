import {useState, useEffect} from '@wordpress/element';
import {registerPaymentMethod} from '@woocommerce/blocks-registry';
import {getSettings, initStripe, isCartPage} from "../util";
import {LocalPaymentIntentContent} from './local-payment-method';
import {OffsiteNotice, PaymentMethod, PaymentMethodLabel} from "../../components/checkout";
import {canMakePayment} from "./local-payment-method";
import {PaymentMethodMessagingElement, Elements} from "@stripe/react-stripe-js";
import {registerPlugin} from '@wordpress/plugins';
import {ExperimentalOrderMeta, TotalsWrapper} from '@woocommerce/blocks-checkout';

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
            <OffsiteNotice text={getData('i18n').offsite}/>
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
        canMakePayment: canMakePayment(getData, ({settings, billingAddress, cartTotals}) => {
            const {country} = billingAddress;
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

if (isCartPage() && getData('cartEnabled')) {
    const KlarnaCartMessage = ({cart}) => {
        const {cartTotals} = cart;
        const options = {
            amount: parseInt(cartTotals.total_price),
            currency: cartTotals.currency_code,
            paymentMethodTypes: ['klarna'],
            ...getData('messageOptions')
        };

        if (options.currency?.length) {
            return (
                <TotalsWrapper>
                    <div className={'wc-block-components-totals-item wc-stripe-cart-message-container stripe_klarna'}>
                        <PaymentMethodMessagingElement options={options}/>
                    </div>
                </TotalsWrapper>
            )
        }
        return null;
    }
    const render = () => {
        const Component = (props) => (
            <Elements stripe={initStripe} options={getData('elementOptions')}>
                <KlarnaCartMessage {...props}/>
            </Elements>
        );

        return (
            <ExperimentalOrderMeta>
                <Component/>
            </ExperimentalOrderMeta>
        )
    }
    registerPlugin('wc-stripe-blocks-klarna', {render, scope: 'woocommerce-checkout'});
}