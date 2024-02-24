import {useState, useEffect, useRef, useCallback} from '@wordpress/element';
import {
    getDefaultSourceArgs,
    ensureSuccessResponse,
    ensureErrorResponse,
    StripeError
} from "../../util";
import {useStripe, useElements} from "@stripe/react-stripe-js";

export const useCreateSource = (
    {
        getData,
        billing,
        shippingAddress,
        onPaymentSetup,
        emitResponse,
        getSourceArgs = false,
        element = false
    }) => {
    const [source, setSource] = useState(false);
    const [isValid, setIsValid] = useState(false);
    const currentValues = useRef({
        billing,
        shippingAddress,
    });
    const stripe = useStripe();
    const elements = useElements();
    useEffect(() => {
        currentValues.current = {
            billing,
            shippingAddress
        }
    });

    const getSourceArgsInternal = useCallback(() => {
        const {billing} = currentValues.current;
        const {cartTotal, currency, billingAddress} = billing;
        let args = getDefaultSourceArgs({
            type: getData('paymentType'),
            amount: cartTotal.value,
            billingAddress,
            currency: currency.code,
            returnUrl: getData('returnUrl')
        });
        if (getSourceArgs) {
            args = getSourceArgs(args, {billingAddress});
        }
        return args;
    }, []);

    const getSuccessData = useCallback((sourceId) => {
        return {
            meta: {
                paymentMethodData: {
                    [`${getData('name')}_token_key`]: sourceId
                }
            }
        }
    }, []);

    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            if (source) {
                return ensureSuccessResponse(emitResponse.responseTypes, getSuccessData(source.id));
            }
            // create the source
            try {
                let result;
                if (element) {
                    // validate the element
                    if (!isValid) {
                        throw getData('i18n').empty_data;
                    }
                    result = await stripe.createSource(elements.getElement(element), getSourceArgsInternal());
                } else {
                    result = await stripe.createSource(getSourceArgsInternal());
                }
                if (result.error) {
                    throw new StripeError(result.error);
                }
                setSource(result.source);
                return ensureSuccessResponse(emitResponse.responseTypes, getSuccessData(result.source.id));
            } catch (err) {
                console.log(err);
                return ensureErrorResponse(
                    emitResponse.responseTypes,
                    err.error || err,
                    {
                        messageContext: emitResponse.noticeContexts.PAYMENTS
                    }
                );
            }
        });
        return () => unsubscribe();
    }, [
        source,
        onPaymentSetup,
        stripe,
        element,
        isValid,
        setIsValid
    ]);
    return {setIsValid};
}