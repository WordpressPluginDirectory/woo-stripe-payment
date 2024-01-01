import {useState, useEffect, useRef, useCallback} from '@wordpress/element';
import {useStripe, useElements} from "@stripe/react-stripe-js";
import {ensureErrorResponse, ensureSuccessResponse, getBillingDetailsFromAddress, StripeError, isNextActionRequired, getRoute, handleCardAction} from '../util';

export const useDeferredPaymentIntent = (
    {
        billingAddress,
        eventRegistration,
        emitResponse,
        name,
        shouldSavePayment,
        noticeContexts
    }
) => {
    const {onPaymentSetup, onCheckoutSuccess} = eventRegistration;
    const currentData = useRef({billingAddress});
    const paymentMethodData = useRef({});
    const stripe = useStripe();
    const elements = useElements();

    const getSuccessResponse = useCallback((paymentMethod, shouldSavePayment) => {
        const response = {
            meta: {
                paymentMethodData: {
                    [`${name}_token_key`]: paymentMethod,
                    [`${name}_save_source_key`]: shouldSavePayment,
                    ...paymentMethodData.current
                }
            }
        }
        return response;
    }, []);

    const addPaymentMethodData = useCallback((data) => {
        paymentMethodData.current = {...paymentMethodData.current, ...data};
    }, []);

    const createPaymentMethod = useCallback(async () => {
        const {billingAddress} = currentData.current;
        await elements.submit();
        return await stripe.createPaymentMethod({
            elements,
            params: {
                billing_details: getBillingDetailsFromAddress(billingAddress)
            }
        });
    }, [stripe, elements]);

    useEffect(() => {
        currentData.current.billingAddress = billingAddress;
    });

    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            try {
                const result = await createPaymentMethod();
                if (result.error) {
                    throw new StripeError(result.error);
                }
                const paymentMethod = result.paymentMethod.id;
                currentData.current.paymentMethod = paymentMethod;
                return ensureSuccessResponse(emitResponse.responseTypes, getSuccessResponse(paymentMethod, shouldSavePayment));
            } catch (error) {
                return ensureErrorResponse(emitResponse.responseTypes, error, {messageContext: noticeContexts.PAYMENTS});
            }
        });
        return () => unsubscribe();
    }, [
        onPaymentSetup,
        createPaymentMethod,
        shouldSavePayment
    ]);

    useEffect(() => {
        const unsubscribe = onCheckoutSuccess(async ({redirectUrl}) => {
            return await handleCardAction({
                redirectUrl,
                emitResponse,
                name,
                savePaymentMethod: shouldSavePayment,
                data: {
                    [`${name}_token_key`]: currentData.current.paymentMethod
                }
            })
        });
        return () => unsubscribe();
    }, [
        onCheckoutSuccess,
        shouldSavePayment,
        name
    ]);

    return {
        createPaymentMethod,
        addPaymentMethodData
    }
}