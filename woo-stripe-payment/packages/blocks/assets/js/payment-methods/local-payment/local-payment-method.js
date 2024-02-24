import {useCallback} from '@wordpress/element';
import {useElements, Elements} from "@stripe/react-stripe-js";
import {initStripe as loadStripe, cartContainsSubscription, cartContainsPreOrder} from '../util'
import {useAfterProcessLocalPayment, useValidateCheckout, useCreateSource} from "./hooks";
import {useProcessCheckoutError} from "../hooks";

/**
 * Return true if the local payment method can be used.
 * @param settings
 * @returns {function({billingAddress: *, [p: string]: *}): *}
 */
export const canMakePayment = (settings, callback = false) => ({billingAddress, cartTotals, ...props}) => {
    const {currency_code} = cartTotals;
    const {country} = billingAddress;
    const countries = settings('countries');
    const type = settings('allowedCountries');
    const supports = settings('features');
    let canMakePayment = false;
    if (settings('isAdmin')) {
        canMakePayment = true;
    } else {
        // Check if there are any subscriptions or pre-orders in the cart.
        if (cartContainsSubscription() && !supports.includes('subscriptions')) {
            return false;
        } else if (cartContainsPreOrder() && !supports.includes('pre-orders')) {
            return false;
        }
        if (settings('currencies').includes(currency_code)) {
            if (type === 'all_except') {
                canMakePayment = !settings('exceptCountries').includes(country);
            } else if (type === 'specific') {
                canMakePayment = settings('specificCountries').includes(country);
            } else {
                canMakePayment = countries.length > 0 ? countries.includes(country) : true;
            }
        }
        if (callback && canMakePayment) {
            canMakePayment = callback({settings, billingAddress, cartTotals, ...props});
        }
    }
    return canMakePayment;
}

export const LocalPaymentIntentContent = ({getData, ...props}) => {
    return (
        <Elements stripe={loadStripe} options={getData('elementOptions')}>
            <LocalPaymentIntentMethod {...{...props, getData}}/>
        </Elements>
    )
}

export const LocalPaymentSourceContent = (props) => {
    return (
        <Elements stripe={loadStripe}>
            <LocalPaymentSourceMethod {...props}/>
        </Elements>
    )
}

const LocalPaymentSourceMethod = (
    {
        getData,
        billing,
        shippingData,
        emitResponse,
        eventRegistration,
        getSourceArgs = false,
        element = false
    }) => {
    const {shippingAddress} = shippingData;
    const {onPaymentSetup, onCheckoutFail} = eventRegistration;
    const onChange = (event) => {
        setIsValid(event.complete);
    }
    const {setIsValid} = useCreateSource({
        getData,
        billing,
        shippingAddress,
        onPaymentSetup,
        emitResponse,
        getSourceArgs,
        element
    });

    if (element) {
        return (
            <LocalPaymentElementContainer
                name={getData('name')}
                options={getData('paymentElementOptions')}
                onChange={onChange}
                element={element}/>
        )
    }
    return null;
}

export const LocalPaymentIntentMethod = (
    {
        getData,
        billing,
        emitResponse,
        eventRegistration,
        activePaymentMethod,
        confirmationMethod = null,
        component = null,
        callback = null,
        shouldSavePayment = false,
        ...props
    }) => {
    const elements = useElements();
    const {billingAddress} = billing;
    const {onPaymentSetup, onCheckoutFail} = eventRegistration;
    const getPaymentMethodArgs = useCallback((billingAddress) => {
        if (component) {
            return {
                [getData('paymentType')]: elements.getElement(component)
            }
        } else if (callback) {
            return callback(billingAddress);
        }
        return {};
    }, [
        elements,
        callback
    ]);
    const {setIsValid} = useValidateCheckout({
            subscriber: onPaymentSetup,
            emitResponse,
            component,
            shouldSavePayment,
            paymentMethodName: getData('name'),
            msg: getData('i18n').empty_data
        }
    );

    useAfterProcessLocalPayment({
        getData,
        billingAddress,
        eventRegistration,
        emitResponse,
        activePaymentMethod,
        confirmationMethod,
        getPaymentMethodArgs
    });
    useProcessCheckoutError({
        emitResponse,
        subscriber: onCheckoutFail,
        messageContext: emitResponse.noticeContexts.PAYMENTS
    });
    if (component) {
        const onChange = (event) => setIsValid(!event.empty)
        return (
            <LocalPaymentElementContainer
                name={getData('name')}
                options={getData('paymentElementOptions')}
                onChange={onChange}
                element={component}
                callback={callback}
                billing={billing}
                {...props}/>
        )
    }
    return null;
}

const LocalPaymentElementContainer = ({name, onChange, element, options, ...props}) => {
    const Tag = element;
    const displayName = Tag?.displayName || '';
    return (
        <div className={`wc-stripe-local-payment-container ${name} ${displayName}`}>
            <Tag options={options} onChange={onChange} {...props}/>
        </div>
    )
}