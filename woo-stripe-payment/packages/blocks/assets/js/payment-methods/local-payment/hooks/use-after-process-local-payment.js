import {useEffect, useRef} from '@wordpress/element';
import {useStripe} from "@stripe/react-stripe-js";
import {ensureErrorResponse, StripeError} from "../../util";

export const useAfterProcessLocalPayment = (
    {
        getData,
        billingAddress,
        eventRegistration,
        emitResponse,
        activePaymentMethod,
        confirmationMethod,
        getPaymentMethodArgs = () => ({})
    }
) => {
    const stripe = useStripe();
    const {onCheckoutSuccess, onCheckoutFail} = eventRegistration;
    const currentBillingAddress = useRef(billingAddress);
    const currentPaymentMethodArgs = useRef(getPaymentMethodArgs);
    useEffect(() => {
        currentBillingAddress.current = billingAddress;
    }, [billingAddress]);

    useEffect(() => {
        currentPaymentMethodArgs.current = getPaymentMethodArgs;
    }, [getPaymentMethodArgs]);

    useEffect(() => {
        const unsubscribeAfterProcessingWithSuccess = onCheckoutSuccess(async ({redirectUrl}) => {
            if (getData('name') === activePaymentMethod) {
                try {
                    let match = redirectUrl.match(/#response=(.+)/);
                    if (match) {
                        let {client_secret, return_url, billing_details, confirmation_args = {}, ...order} = JSON.parse(window.atob(decodeURIComponent(match[1])));
                        let result = await stripe[confirmationMethod](client_secret, {
                            payment_method: {
                                billing_details,
                                ...currentPaymentMethodArgs.current(currentBillingAddress.current)
                            },
                            return_url,
                            ...confirmation_args
                        });
                        if (result.error) {
                            throw new StripeError(result.error);
                        }
                        if (result.paymentIntent.status === 'requires_action') {
                            if (['stripe_wechat', 'stripe_swish'].includes(activePaymentMethod)) {
                                return ensureErrorResponse(
                                    emitResponse.responseTypes,
                                    getData('i18n').payment_cancelled,
                                    {
                                        messageContext: emitResponse.noticeContexts.PAYMENTS
                                    }
                                );
                            }
                            window.location = decodeURI(order.order_received_url);
                        }
                        window.location = decodeURI(order.order_received_url);
                    }
                } catch (e) {
                    console.log(e);
                    return ensureErrorResponse(
                        emitResponse.responseTypes,
                        e.error,
                        {
                            messageContext: emitResponse.noticeContexts.PAYMENTS
                        }
                    );
                }
            }
        })
        return () => unsubscribeAfterProcessingWithSuccess();
    }, [
        stripe,
        onCheckoutSuccess,
        onCheckoutFail,
        activePaymentMethod
    ]);
}