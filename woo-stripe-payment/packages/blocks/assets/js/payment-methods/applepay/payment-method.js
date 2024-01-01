import {useCallback} from '@wordpress/element';
import {registerExpressPaymentMethod} from '@woocommerce/blocks-registry';
import {getSettings, initStripe as loadStripe, canMakePayment, isCartPage, isCheckoutPage} from "../util";
import {Elements, useStripe} from "@stripe/react-stripe-js";
import ErrorBoundary from "../error-boundary";
import {
    usePaymentRequest,
    useProcessPaymentIntent,
    useExportedValues,
    useAfterProcessingPayment,
    useStripeError,
    useExpressBreakpointWidth
} from '../hooks';

const getData = getSettings('stripe_applepay_data');

const ApplePayContent = (props) => {
    return (
        <ErrorBoundary>
            <div className='wc-stripe-apple-pay-container'>
                <Elements stripe={loadStripe}>
                    <ApplePayButton {...props}/>
                </Elements>
            </div>
        </ErrorBoundary>
    );
}

const ApplePayButton = (
    {
        getData,
        onClick,
        onClose,
        billing,
        shippingData,
        eventRegistration,
        emitResponse,
        onSubmit,
        activePaymentMethod,
        ...props
    }) => {
    const {onPaymentSetup} = eventRegistration;
    const {noticeContexts} = emitResponse;
    const stripe = useStripe();
    const [error] = useStripeError();
    const canPay = (result) => result != null && result.applePay;
    const exportedValues = useExportedValues();

    useExpressBreakpointWidth({payment_method: getData('name'), width: 375});

    const {setPaymentMethod} = useProcessPaymentIntent({
        getData,
        billing,
        shippingData,
        onPaymentSetup,
        emitResponse,
        error,
        onSubmit,
        activePaymentMethod,
        exportedValues
    });
    useAfterProcessingPayment({
        getData,
        eventRegistration,
        emitResponse,
        activePaymentMethod,
        messageContext: noticeContexts.EXPRESS_PAYMENTS
    });
    const {paymentRequest} = usePaymentRequest({
        getData,
        onClose,
        stripe,
        billing,
        shippingData,
        setPaymentMethod,
        exportedValues,
        canPay
    });

    const handleClick = useCallback((e) => {
        if (paymentRequest) {
            e.preventDefault();
            onClick();
            paymentRequest.show();
        }
    }, [paymentRequest, onClick]);

    if (paymentRequest) {
        return (
            <button
                className={`apple-pay-button ${getData('buttonStyle')}`}
                style={{
                    'ApplePayButtonType': getData('buttonType')
                }}
                onClick={handleClick}/>

        )
    }
    return null;
}

const ApplePayEdit = ({getData, ...props}) => {
    return (
        <div className={'apple-pay-block-editor'}>
            <img src={getData('editorIcon')}/>
        </div>
    )
}

if ((isCartPage() && getData('cartCheckoutEnabled')) ||
    (isCheckoutPage() && getData('expressCheckoutEnabled'))) {
    registerExpressPaymentMethod({
        name: getData('name'),
        canMakePayment: ({cartTotals, ...props}) => {
            if (getData('isAdmin')) {
                return true;
            }
            const {currency_code: currency, total_price} = cartTotals;
            return canMakePayment({
                country: getData('countryCode'),
                currency: currency.toLowerCase(),
                total: {
                    label: getData('totalLabel'),
                    amount: parseInt(total_price)
                }
            }, (result) => result != null && result.applePay);
        },
        content: <ApplePayContent getData={getData}/>,
        edit: <ApplePayEdit getData={getData}/>,
        supports: {
            showSavedCards: getData('showSavedCards'),
            showSaveOption: getData('showSaveOption'),
            features: getData('features')
        }
    })
}