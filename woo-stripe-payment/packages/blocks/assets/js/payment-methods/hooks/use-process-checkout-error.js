import {useEffect} from '@wordpress/element';

export const useProcessCheckoutError = (
    {
        emitResponse,
        subscriber,
        messageContext = null
    }) => {
    useEffect(() => {
        const unsubscribe = subscriber((data) => {
            if (data?.processingResponse.paymentDetails?.stripeErrorMessage) {
                return {
                    type: emitResponse.responseTypes.ERROR,
                    message: data.processingResponse.paymentDetails.stripeErrorMessage,
                    messageContext: messageContext || emitResponse.noticeContexts.PAYMENTS
                };
            }
            return null;
        });
        return () => unsubscribe();
    }, [
        subscriber,
        messageContext,
        emitResponse.responseTypes.ERROR,
        emitResponse.noticeContexts.PAYMENTS
    ]);
}