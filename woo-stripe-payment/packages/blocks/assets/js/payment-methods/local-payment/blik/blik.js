import {useState, useEffect, useRef} from '@wordpress/element';
import {registerPaymentMethod} from '@woocommerce/blocks-registry';
import {sprintf, __} from '@wordpress/i18n';
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

const getData = getSettings('stripe_blik_data');

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
        onCheckoutSuccess,
        onCheckoutValidationBeforeProcessing
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
        const unsubscribe = onCheckoutValidationBeforeProcessing(() => {
            const {activePaymentMethod} = currentData.current;
            if (getData('name') === activePaymentMethod) {
                if (codes.length < 6) {
                    return ensureErrorResponse(
                        emitResponse.responseTypes,
                        __('Please enter your 6-digit BLIK code.', 'woo-stripe-payment'),
                        {
                            messageContext: emitResponse.noticeContexts.PAYMENTS
                        }
                    );
                }
            }
        });
        return unsubscribe;
    }, [
        codes,
        onCheckoutValidationBeforeProcessing
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
            {!showTimer && <InputCodes onComplete={codes => setCodes(codes)}/>}
            {showTimer && <Timer onTimeout={() => setShowTimer(false)}/>}
        </>
    )
}

const Instructions = () => {
    return (
        <ol>
            <li>{__('Request your 6-digit code from your banking application.', 'woo-stripe-payment')}</li>
            <li dangerouslySetInnerHTML={{__html: sprintf(__('Enter the code into the input fields below. Click %1$s once you have entered the code.', 'woo-stripe-payment'), '<b>' + getData('placeOrderButtonLabel') + '</b>')}}/>
            <li>{__('You will receive a notification on your mobile device asking you to authorize the payment.', 'woo-stripe-payment')}</li>
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