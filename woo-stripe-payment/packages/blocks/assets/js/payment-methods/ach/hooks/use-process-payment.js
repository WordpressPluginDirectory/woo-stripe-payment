import {useEffect, useRef, useCallback} from '@wordpress/element';
import {useStripe} from '@stripe/react-stripe-js';
import {ensureSuccessResponse, ensureErrorResponse, isNextActionRequired, getRoute, getSettings} from "../../util";
import apiFetch from "@wordpress/api-fetch";

const getData = getSettings('stripe_ach_data');
const i18n = getData('i18n');

export const useProcessPayment = (
    {
        onCheckoutSuccess,
        emitResponse,
        billingAddress,

    }) => {
    const stripe = useStripe();
    const currentData = useRef({billingAddress});
    useEffect(() => {
        currentData.current = {...currentData.current, billingAddress};
    });

    useEffect(() => {
        const unsubscribe = onCheckoutSuccess(async ({redirectUrl}) => {
            const result = isNextActionRequired(redirectUrl);
            if (result) {
                if (result.type === 'intent') {
                    return await processPaymentIntent(result, stripe);
                } else {
                    return await processSetupIntent(result, stripe);
                }
            }
        });
        return () => unsubscribe();
    }, [
        onCheckoutSuccess,
        stripe,
        processPaymentIntent,
        processSetupIntent
    ]);

    const processPaymentIntent = useCallback(async (data, stripe) => {
        const {billingAddress} = currentData.current;
        const {client_secret, order_id, order_key} = data;
        try {
            let response = await stripe.collectBankAccountForPayment({
                clientSecret: client_secret,
                params: {
                    payment_method_type: 'us_bank_account',
                    payment_method_data: {
                        billing_details: {
                            name: `${billingAddress.first_name} ${billingAddress.last_name}`,
                            email: billingAddress.email,
                        },
                    },
                }
            });
            if (response.error) {
                throw response.error;
            }
            if (response.paymentIntent.status === "requires_confirmation") {
                let response = await stripe.confirmUsBankAccountPayment(client_secret);
                if (response.error) {
                    throw response.error;
                }
                if (response.paymentIntent.status === 'processing') {
                    let result = await apiFetch({
                        url: getRoute('process/payment'),
                        method: 'POST',
                        data: {
                            order_id,
                            order_key,
                            stripe_ach_token_key: response.paymentIntent.payment_method
                        }
                    });
                    if (result.messages) {
                        throw result.messages;
                    }
                    return ensureSuccessResponse(
                        emitResponse.responseTypes, {
                            redirectUrl: result.redirect
                        });
                } else if (response.paymentIntent.status === 'requires_action') {
                    throw {code: 'ach_instant_only'};
                }
            } else if (response.paymentIntent.status === 'requires_payment_method') {
                return {
                    type: emitResponse.responseTypes.FAIL,
                    message: i18n.ach_payment_cancelled,
                    messageContext: emitResponse.noticeContexts.PAYMENTS,
                    retry: true
                }
            }
        } catch (err) {
            return false;
            return ensureErrorResponse(
                emitResponse.responseTypes,
                err,
                {
                    messageContext: emitResponse.noticeContexts.PAYMENTS
                }
            );
        }
    }, []);

    const processSetupIntent = useCallback(async (data, stripe) => {
        const {billingAddress} = currentData.current;
        const {client_secret, order_id, order_key} = data;
        try {
            let response = await stripe.collectBankAccountForSetup({
                clientSecret: client_secret,
                params: {
                    payment_method_type: 'us_bank_account',
                    payment_method_data: {
                        billing_details: {
                            name: `${billingAddress.first_name} ${billingAddress.last_name}`,
                            email: billingAddress.email,
                        },
                    },
                }
            });
            if (response.error) {
                throw response.error;
            }
            if (response.setupIntent.status === "requires_confirmation") {
                let {setupIntent, error} = await stripe.confirmUsBankAccountSetup(client_secret);
                if (error) {
                    throw error;
                }
                if (setupIntent.status === 'succeeded') {
                    let response = await apiFetch({
                        url: getRoute('process/payment'),
                        method: 'POST',
                        data: {order_id, order_key, stripe_ach_token_key: setupIntent.payment_method}
                    });
                    if (response.messages) {
                        throw response.messages;
                    }
                    return ensureSuccessResponse(emitResponse.responseTypes, {
                        redirectUrl: response.redirect
                    });
                } else if (setupIntent.status === 'requires_action') {
                    throw {code: 'ach_instant_only'};
                }
            }
        } catch (err) {
            console.log(err);
            return ensureErrorResponse(
                emitResponse.responseTypes,
                err,
                {messageContext: emitResponse.noticeContexts.PAYMENTS}
            );
        }
    }, []);
}