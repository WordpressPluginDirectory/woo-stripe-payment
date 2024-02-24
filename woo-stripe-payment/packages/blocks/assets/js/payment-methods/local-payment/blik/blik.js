import {useState, useEffect, useRef} from '@wordpress/element';
import {registerPaymentMethod} from '@woocommerce/blocks-registry';
import {
    ensureErrorResponse,
    ensureSuccessResponse,
    getBillingDetailsFromAddress,
    getSettings, initStripe as loadStripe,
    isNextActionRequired,
    StripeError
} from "../../util";
import {PaymentMethod, PaymentMethodLabel} from "../../../components/checkout";
import {canMakePayment} from "../local-payment-method";
import {Elements, useStripe} from "@stripe/react-stripe-js";
import InputCodes from "./input-codes";
import Timer from './timer';
import {useProcessCheckoutError} from "../../hooks";

const getData = getSettings('stripe_blik_data');
const i18n = getData('i18n');

const BLIKPaymentMethod = (props) => {
    return (
        <Elements stripe={loadStripe} options={getData('elementOptions')}>
            <PaymentMethodContent {...props}/>
        </Elements>
    )
}

const PaymentMethodContent = (props) => {
    const currentData = useRef();
    const [showTimer, setShowTimer] = useState(false);
    const {eventRegistration, billing, activePaymentMethod} = props;
    const {emitResponse} = props;
    const {
        onPaymentSetup,
        onCheckoutFail,
        onCheckoutSuccess,
        onCheckoutValidation
    } = eventRegistration;
    const [codes, setCodes] = useState([]);
    const stripe = useStripe();

    useEffect(() => {
        currentData.current = {codes, billing, activePaymentMethod};
    }, [
        codes,
        billing,
        activePaymentMethod
    ]);

    const formatCodes = () => {
        const response = currentData.current.codes.reduce((carry, code, idx) => {
            return {...carry, [`blik_code_${idx}`]: code}
        }, {});
        return response;
    }

    useProcessCheckoutError({
        emitResponse,
        subscriber: onCheckoutFail,
        messageContext: emitResponse.noticeContexts.PAYMENTS
    });

    useEffect(() => {
        const unsubscribe = onPaymentSetup(() => {
            const {activePaymentMethod} = currentData.current;
            if (getData('name') === activePaymentMethod) {
                return ensureSuccessResponse(
                    emitResponse.responseTypes,
                    {
                        meta: {
                            paymentMethodData: {
                                ...formatCodes()
                            }
                        }
                    });
            }
        });
        return unsubscribe;
    }, [
        onPaymentSetup
    ])

    useEffect(() => {
        const unsubscribe = onCheckoutValidation(() => {
            const {activePaymentMethod} = currentData.current;
            if (getData('name') === activePaymentMethod) {
                if (codes.length < 6) {
                    return {
                        errorMessage: i18n.enter_blik_code,
                        context: emitResponse.noticeContexts.PAYMENTS
                    }
                }
            }
        });
        return unsubscribe;
    }, [
        codes,
        onCheckoutValidation
    ]);

    useEffect(() => {
        const unsubscribe = onCheckoutSuccess((async ({redirectUrl}) => {
            const {activePaymentMethod} = currentData.current;
            if (getData('name') === activePaymentMethod) {
                const {billingAddress} = currentData.current.billing;
                try {
                    const args = isNextActionRequired(redirectUrl);
                    if (args) {
                        let {client_secret, return_url, ...order} = args;
                        setShowTimer(true)
                        let result = await stripe.confirmBlikPayment(client_secret, {
                            payment_method: {
                                billing_details: getBillingDetailsFromAddress(billingAddress),
                                blik: {}
                            },
                            payment_method_options: {
                                blik: {
                                    code: codes.join('')
                                }
                            },
                            return_url
                        });
                        if (result.error) {
                            throw new StripeError(result.error);
                        }
                        if (result.paymentIntent.status === 'requires_payment_method') {
                            throw new StripeError(result.paymentIntent.last_payment_error);
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
                } finally {
                    setShowTimer(false);
                }
            }
        }));
        return unsubscribe;
    }, [
        codes,
        stripe,
        onCheckoutSuccess
    ])

    return (
        <>
            <Instructions/>
            {!showTimer && <InputCodes onComplete={codes => setCodes(codes)} i18n={getData('i18n')}/>}
            {showTimer && <Timer onTimeout={() => setShowTimer(false)} i18n={getData('i18n')}/>}
        </>
    )
}

const Instructions = () => {
    return (
        <ol>
            <li>{getData('i18n').step1}</li>
            <li dangerouslySetInnerHTML={{__html: getData('i18n').step2}}/>
            <li>{getData('i18n').step3}</li>
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
        ariaLabel: 'BLIK',
        placeOrderButtonLabel: getData('placeOrderButtonLabel'),
        canMakePayment: canMakePayment(getData),
        content: <PaymentMethod content={BLIKPaymentMethod} getData={getData}/>,
        edit: <PaymentMethod content={BLIKPaymentMethod} getData={getData}/>,
        supports: {
            showSavedCards: false,
            showSaveOption: false,
            features: getData('features')
        }
    })
}