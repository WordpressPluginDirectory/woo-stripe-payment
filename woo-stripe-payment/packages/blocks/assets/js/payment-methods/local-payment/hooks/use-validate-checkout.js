import {useEffect, useRef, useState} from '@wordpress/element';
import {ensureErrorResponse} from "../../util";
import {__} from "@wordpress/i18n";

export const useValidateCheckout = (
    {
        subscriber,
        emitResponse,
        component = null,
        shouldSavePayment = false,
        paymentMethodName = '',
        msg = __('Please enter your payment info before proceeding.', 'woo-stripe-payment')
    }) => {
    const [isValid, setIsValid] = useState(false);
    const currentData = useRef({});

    useEffect(() => {
        currentData.current = {shouldSavePayment, paymentMethodName};
    }, [shouldSavePayment, paymentMethodName]);

    useEffect(() => {
        const unsubscribe = subscriber(() => {
            const {shouldSavePayment, paymentMethodName} = currentData.current;
            if (component && !isValid) {
                return ensureErrorResponse(emitResponse.responseTypes, msg, {messageContext: emitResponse.noticeContexts.PAYMENTS});
            }
            return {
                type: emitResponse.responseTypes.SUCCESS,
                meta: {
                    paymentMethodData: {
                        [`${paymentMethodName}_save_source_key`]: shouldSavePayment
                    }
                }
            };
        });
        return () => unsubscribe();
    }, [
        subscriber,
        isValid,
        setIsValid,
        component
    ]);
    return {isValid, setIsValid};
}