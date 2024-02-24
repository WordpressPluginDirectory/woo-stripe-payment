import {useState, useEffect} from '@wordpress/element';
import {registerPaymentMethod} from '@woocommerce/blocks-registry';
import {getSettings, initStripe, isCartPage} from "../util";
import {LocalPaymentIntentContent} from './local-payment-method';
import {OffsiteNotice, PaymentMethod, PaymentMethodLabel} from "../../components/checkout";
import {canMakePayment} from "./local-payment-method";
import {AfterpayClearpayMessageElement, Elements} from "@stripe/react-stripe-js";
import {registerPlugin} from '@wordpress/plugins';
import {ExperimentalOrderMeta, TotalsWrapper} from '@woocommerce/blocks-checkout';

const getData = getSettings('stripe_afterpay_data');

const dispatchAfterpayChange = (options) => {
    document.dispatchEvent(new CustomEvent('stripeAfterpayChange', {
        detail: {
            options
        }
    }))
}

const isAvailable = ({total, currency, country}) => {
    let available = false;
    const billingCountry = country;
    const requiredParams = getData('requiredParams');
    const accountCountry = getData('accountCountry');
    const requiredParamObj = requiredParams.hasOwnProperty(currency) ? requiredParams[currency] : false;
    if (requiredParamObj) {
        let countries = requiredParamObj?.[0];
        if (!Array.isArray(countries)) {
            countries = [countries];
        }
        available = countries.indexOf(accountCountry) > -1
            && (currency !== 'EUR' || !billingCountry || accountCountry === billingCountry)
            && (total > requiredParamObj?.[1] && total < requiredParamObj?.[2]);
    }
    return available;
}

const AfterpayPaymentMethodLabel = ({title, getData, ...props}) => {
    const {PaymentMethodLabel: Label} = props.components;
    const [options, setOptions] = useState({
        amount: getData('cartTotal'),
        currency: getData('currency'),
        isCartEligible: true
    });
    useEffect(() => {
        const updateOptions = e => setOptions(e.detail.options);
        document.addEventListener('stripeAfterpayChange', updateOptions);
        return () => document.removeEventListener('stripeAfterpayChange', updateOptions);
    }, []);

    if (!getData('paymentSections').includes('checkout')) {
        return (
            <PaymentMethodLabel
                paymentMethod={props.paymentMethod}
                title={title}
                icons={props.icons}
                components={props.components}/>
        )
    }

    return (
        <div className={'wc-stripe-label-container'}>
            <Label text={title}/>
            <div className={'wc-stripe-afterpay-message-container'}>
                <Elements stripe={initStripe} options={getData('elementOptions')}>
                    <div className='wc-stripe-blocks-afterpay__label'>
                        <AfterpayClearpayMessageElement options={{
                            ...getData('checkoutMessageOptions'),
                            ...options
                        }}/>
                    </div>
                </Elements>
            </div>
        </div>
    )
}

const AfterpayPaymentMethod = ({content, billing, shippingData, ...props}) => {
    const Content = content;
    const {cartTotal, currency, billingAddress: {country}} = billing;
    const total = parseInt(cartTotal.value) / 10 ** currency.minorUnit;
    return (
        <>
            <div className='wc-stripe-blocks-payment-method-content'>
                <div className="wc-stripe-blocks-afterpay-offsite__container">
                    <OffsiteNotice text={getData('i18n').offsite}/>
                </div>
                <Content {...{...props, billing, shippingData}}/>
            </div>
        </>
    );
}

if (getData()) {
    registerPaymentMethod({
        name: getData('name'),
        label: <AfterpayPaymentMethodLabel
            title={getData('title')}
            paymentMethod={getData('name')}
            icons={getData('icon')}
            getData={getData}/>,
        ariaLabel: getData('title'),
        placeOrderButtonLabel: getData('placeOrderButtonLabel'),
        canMakePayment: canMakePayment(getData, ({settings, cartTotals, billingAddress}) => {
            const {currency_code: currency, currency_minor_unit, total_price} = cartTotals;
            const {country} = billingAddress;
            const total = parseInt(total_price) / (10 ** currency_minor_unit);
            const available = isAvailable({total, currency, country});
            dispatchAfterpayChange({
                amount: parseInt(cartTotals.total_price),
                currency,
                isCartEligible: available
            });
            if (!available && !settings('hideIneligible')) {
                return true;
            }
            return available;
        }),
        content: <AfterpayPaymentMethod
            content={LocalPaymentIntentContent}
            getData={getData}
            confirmationMethod={'confirmAfterpayClearpayPayment'}/>,
        edit: <PaymentMethod content={LocalPaymentIntentContent} getData={getData}/>,
        supports: {
            showSavedCards: false,
            showSaveOption: false,
            features: getData('features')
        }
    });
}

if (isCartPage() && getData('cartEnabled')) {

    const AfterpayCartMessage = ({cart}) => {
        const {billingAddress} = cart;
        const {currency_code: currency, currency_minor_unit, total_price} = cart.cartTotals;
        const {country} = billingAddress;
        const total = parseInt(total_price) / (10 ** currency_minor_unit);
        const available = isAvailable({total, currency, country});
        if (available) {
            return (
                <TotalsWrapper>
                    <div className={'wc-block-components-totals-item wc-stripe-cart-message-container stripe_afterpay'}>
                        <AfterpayClearpayMessageElement options={{
                            ...getData('cartMessageOptions'),
                            ...{amount: parseInt(total_price), currency, isCartEligible: true}
                        }}/>
                    </div>
                </TotalsWrapper>
            )
        }
    }

    const render = (props) => {
        const Component = (props) => (
            <Elements stripe={initStripe} options={getData('elementOptions')}>
                <AfterpayCartMessage {...props}/>
            </Elements>
        );
        return (
            <ExperimentalOrderMeta>
                <Component/>
            </ExperimentalOrderMeta>
        )
    }
    registerPlugin('wc-stripe-blocks-afterpay', {render, scope: 'woocommerce-checkout'});
}