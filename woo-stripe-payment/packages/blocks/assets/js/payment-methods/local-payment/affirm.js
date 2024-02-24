import {useState, useEffect} from '@wordpress/element';
import {registerPaymentMethod} from '@woocommerce/blocks-registry';
import {getSettings, initStripe, isCartPage} from "../util";
import {LocalPaymentIntentContent} from './local-payment-method';
import {PaymentMethod, OffsiteNotice, PaymentMethodLabel} from "../../components/checkout";
import {AffirmMessageElement, Elements} from "@stripe/react-stripe-js";
import {registerPlugin} from '@wordpress/plugins';
import {ExperimentalOrderMeta, TotalsWrapper} from '@woocommerce/blocks-checkout';

const getData = getSettings('stripe_affirm_data');

const isAvailable = ({amount, billingCountry = null, currency}) => {
    const requirements = getData('requirements');
    const accountCountry = getData('accountCountry');

    if (!billingCountry) {
        return currency in requirements
            && 5000 <= amount && amount <= 3000000;
    }

    return currency in requirements
        && accountCountry === billingCountry
        && 5000 <= amount && amount <= 3000000;
}

const dispatchAffirmChange = (options) => {
    document.dispatchEvent(new CustomEvent('stripeAffirmChange', {
        detail: {options}
    }));
}

const AffirmPaymentMethod = (props) => {
    return (
        <>
            <LocalPaymentIntentContent {...props}/>
            <OffsiteNotice text={getData('i18n').offsite}/>
        </>
    )
}

const AffirmPaymentMethodLabel = ({title, components, ...props}) => {
    const {PaymentMethodLabel: Label} = components;
    const [options, setOptions] = useState({
        amount: getData('cartTotals')?.value,
        currency: getData('currency'),
        ...getData('messageOptions')
    });
    useEffect(() => {
        const updateOptions = (e) => {
            setOptions(e.detail.options);
        }
        document.addEventListener('stripeAffirmChange', updateOptions);

        return () => document.removeEventListener('stripeAffirmChange', updateOptions);
    }, []);

    if (!getData('paymentSections').includes('checkout')) {
        return (
            <PaymentMethodLabel
                paymentMethod={props.paymentMethod}
                title={title}
                icons={props.icons}
                components={components}/>
        )
    }

    return (
        <div className={'wc-stripe-label-container'}>
            <Label text={title}/>
            <div className={'wc-stripe-affirm-message-container'}>
                <Elements stripe={initStripe} options={getData('elementOptions')}>
                    <AffirmMessageElement options={options}/>
                </Elements>
            </div>
        </div>
    )
}

if (getData()) {
    registerPaymentMethod({
        name: getData('name'),
        label: <AffirmPaymentMethodLabel
            title={getData('title')}
            paymentMethod={getData('name')}
            icons={getData('icon')}/>,
        ariaLabel: 'Affirm',
        placeOrderButtonLabel: getData('placeOrderButtonLabel'),
        canMakePayment: ({cart}) => {
            const {cartTotals, billingAddress} = cart;
            const {currency_code} = cartTotals;
            const amount = parseInt(cartTotals.total_price);
            const requirements = getData('requirements');
            const accountCountry = getData('accountCountry');
            dispatchAffirmChange({
                amount: amount,
                currency: currency_code
            });
            return isAvailable({amount, billingCountry: billingAddress.country, currency: currency_code});
        },
        content: <PaymentMethod
            content={AffirmPaymentMethod}
            getData={getData}
            confirmationMethod={'confirmAffirmPayment'}/>,
        edit: <PaymentMethod content={LocalPaymentIntentContent} getData={getData}/>,
        supports: {
            showSavedCards: false,
            showSaveOption: false,
            features: getData('features')
        }
    })
}

if (isCartPage() && getData('cartEnabled')) {
    const AffirmCartMessage = ({cart}) => {
        const {cartTotals} = cart;
        const options = {
            amount: parseInt(cartTotals.total_price),
            currency: cartTotals.currency_code,
            ...getData('cartMessageOptions')
        };
        if (isAvailable({amount: parseInt(cartTotals.total_price), currency: cartTotals.currency_code})) {
            return (
                <TotalsWrapper>
                    <div className={'wc-block-components-totals-item wc-stripe-cart-message-container stripe_affirm'}>
                        <AffirmMessageElement options={options}/>
                    </div>
                </TotalsWrapper>
            )
        }
        return null;
    }
    const render = () => {
        const Component = (props) => {
            return (
                <Elements stripe={initStripe} options={getData('elementOptions')}>
                    <AffirmCartMessage {...props}/>
                </Elements>
            )
        }
        return (
            <ExperimentalOrderMeta>
                <Component/>
            </ExperimentalOrderMeta>
        )
    }
    registerPlugin('wc-stripe-blocks-affirm', {render, scope: 'woocommerce-checkout'});
}