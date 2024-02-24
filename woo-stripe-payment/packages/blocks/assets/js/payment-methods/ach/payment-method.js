import {useEffect} from '@wordpress/element';
import {registerPaymentMethod} from '@woocommerce/blocks-registry';
import {Elements} from '@stripe/react-stripe-js';
import {ensureSuccessResponse, getSettings, initStripe as loadStripe} from '../util';
import {PaymentMethodLabel, PaymentMethod} from '../../components/checkout';
import SavedCardComponent from '../saved-card-component';
import {useProcessPayment} from './hooks';
import {useProcessCheckoutError} from "../hooks";

const getData = getSettings('stripe_ach_data');

const ACHPaymentContent = (
    {
        eventRegistration,
        components,
        emitResponse,
        onSubmit,
        billing,
        shouldSavePayment,
        ...props
    }) => {
    const {
        onPaymentSetup,
        onCheckoutFail,
        onCheckoutSuccess
    } = eventRegistration;

    useProcessCheckoutError({
        emitResponse,
        subscriber: onCheckoutFail,
        messageContext: emitResponse.noticeContexts.PAYMENTS
    });


    useProcessPayment({
        onCheckoutSuccess,
        emitResponse,
        billingAddress: billing.billingAddress
    });

    useEffect(() => {
        const unsubscribe = onPaymentSetup(() => {
            return ensureSuccessResponse(emitResponse.responseTypes, {
                meta: {
                    paymentMethodData: {
                        [`${getData('name')}_save_source_key`]: shouldSavePayment,
                    }
                }
            });
        });
        return unsubscribe;
    }, [onPaymentSetup, shouldSavePayment]);

    return (
        <div className={'wc-stripe-ach__container'}>
            <Mandate text={getData('i18n').mandate_text}/>
        </div>
    )
}

const ACHComponent = (props) => {
    return (
        <Elements stripe={loadStripe}>
            <ACHPaymentContent {...props}/>
        </Elements>
    )
}

const Mandate = ({text}) => {
    return (
        <p className={'wc-stripe-ach__mandate'}>
            {text}
        </p>
    )
}

registerPaymentMethod({
    name: getData('name'),
    label: <PaymentMethodLabel title={getData('title')}
                               paymentMethod={getData('name')}
                               icons={getData('icons')}/>,
    ariaLabel: 'ACH Payment',
    canMakePayment: ({cartTotals}) => cartTotals.currency_code === 'USD',
    content: <PaymentMethod
        getData={getData}
        content={ACHComponent}/>,
    savedTokenComponent: <SavedCardComponent getData={getData}/>,
    edit: <ACHComponent/>,
    placeOrderButtonLabel: getData('placeOrderButtonLabel'),
    supports: {
        showSavedCards: getData('showSavedCards'),
        showSaveOption: getData('showSaveOption'),
        features: getData('features')
    }
})