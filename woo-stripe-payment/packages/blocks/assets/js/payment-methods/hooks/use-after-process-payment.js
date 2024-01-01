import {useEffect} from '@wordpress/element'
import {useStripe} from "@stripe/react-stripe-js";
import {handleCardAction} from "../util";
import {useProcessCheckoutError} from "./use-process-checkout-error";

export const useAfterProcessingPayment = (
    {
        getData,
        eventRegistration,
        emitResponse,
        activePaymentMethod,
        shouldSavePayment = false,
        messageContext = null
    }) => {
    const stripe = useStripe();
    const {onCheckoutSuccess, onCheckoutFail} = eventRegistration;
    useProcessCheckoutError({
        emitResponse,
        subscriber: onCheckoutFail,
        messageContext
    });
    useEffect(() => {
        let unsubscribeAfterProcessingWithSuccess = onCheckoutSuccess(async ({redirectUrl}) => {
            if (getData('name') === activePaymentMethod) {
                //check if response is in redirect. If so, open modal
                return await handleCardAction({
                    redirectUrl,
                    emitResponse,
                    name: activePaymentMethod,
                    savePaymentMethod: shouldSavePayment
                });
            }
            return null;
        })
        return () => unsubscribeAfterProcessingWithSuccess()
    }, [
        stripe,
        onCheckoutSuccess,
        activePaymentMethod,
        shouldSavePayment
    ]);
}