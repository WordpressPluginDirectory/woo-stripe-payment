import {useEffect, useRef} from '@wordpress/element';
import {registerPaymentMethod} from '@woocommerce/blocks-registry';
import {PaymentMethod, PaymentMethodLabel} from "../../components/checkout";
import {canMakePayment} from "./local-payment-method";
import {ensureErrorResponse, getBillingDetailsFromAddress, getSettings, initStripe as loadStripe, isNextActionRequired, StripeError} from "../util";
import {Elements, useStripe} from "@stripe/react-stripe-js";
import {useProcessCheckoutError} from "../hooks";

const getData = getSettings('stripe_paynow_data');
const i18n = getData('i18n');

const PayNowPaymentMethod = (props) => {
    return (
        <Elements stripe={loadStripe} options={getData('elementOptions')}>
            <PaymentMethodContent {...props}/>
        </Elements>
    )
}

const PaymentMethodContent = (props) => {
    const {eventRegistration, billing, activePaymentMethod} = props;
    const {emitResponse} = props;
    const {
        onCheckoutSuccess,
        onCheckoutFail
    } = eventRegistration;

    const currentData = useRef({billing, activePaymentMethod});
    const stripe = useStripe();

    useEffect(() => {
        currentData.current = {billing, activePaymentMethod};
    }, [
        billing,
        activePaymentMethod
    ]);

    useProcessCheckoutError({
        emitResponse,
        subscriber: onCheckoutFail
    });

    useEffect(() => {
        const unsubscribe = onCheckoutSuccess(async ({redirectUrl}) => {
            if (activePaymentMethod === getData('name')) {
                const {billingAddress} = currentData.current.billing;
                try {
                    const args = isNextActionRequired(redirectUrl);
                    if (args) {
                        let {client_secret, return_url, ...order} = args;
                        let result = await stripe.confirmPayNowPayment(client_secret, {
                            payment_method: {
                                billing_details: getBillingDetailsFromAddress(billingAddress),
                            },
                            return_url
                        });
                        if (result.error) {
                            throw new StripeError(result.error);
                        }
                        if (result.paymentIntent.status === 'requires_action') {
                            throw i18n.payment_cancelled;
                        }
                        if (result.paymentIntent.status === 'requires_payment_method') {
                            throw i18n.payment_expired;
                        }
                        window.location = decodeURI(order.order_received_url);
                    }
                } catch (error) {
                    return ensureErrorResponse(
                        emitResponse.responseTypes,
                        error,
                        {
                            messageContext: emitResponse.noticeContexts.PAYMENTS
                        }
                    );
                }
            }
        });
        return unsubscribe;
    }, [
        stripe,
        activePaymentMethod,
        onCheckoutSuccess
    ]);

    return <Instructions/>
}

const Instructions = () => {
    return (
        <ol>
            <li dangerouslySetInnerHTML={{__html: i18n.step1}}/>
            <li>{i18n.step2}</li>
            <li>{i18n.step3}</li>
        </ol>
    )
}

if (getData()) {
    registerPaymentMethod({
        name: getData('name'),
        label: <PaymentMethodLabel
            title={getData('title')}
            paymentMethod={getData('name')}
            icons={getData('icon')}/>,
        ariaLabel: 'PayNow',
        placeOrderButtonLabel: getData('placeOrderButtonLabel'),
        canMakePayment: canMakePayment(getData),
        content: <PaymentMethod
            content={PayNowPaymentMethod}
            getData={getData}/>,
        edit: <PaymentMethod content={PayNowPaymentMethod} getData={getData}/>,
        supports: {
            showSavedCards: false,
            showSaveOption: false,
            features: getData('features')
        }
    })
}