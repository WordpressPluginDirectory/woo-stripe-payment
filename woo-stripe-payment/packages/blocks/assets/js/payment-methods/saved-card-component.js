import {useEffect, useCallback} from '@wordpress/element';
import {handleCardAction} from '@paymentplugins/stripe/util';
import {useProcessCheckoutError} from './hooks';

const SavedCardComponent = (
    {
        eventRegistration,
        emitResponse,
        getData,
        method = 'handleCardAction'
    }) => {
    const {onCheckoutSuccess, onCheckoutFail} = eventRegistration;
    useProcessCheckoutError({
        emitResponse,
        subscriber: onCheckoutFail,
        messageContext: emitResponse.noticeContexts.PAYMENTS
    })
    const handleSuccessResult = useCallback(async ({redirectUrl}) => {
        return await handleCardAction({
            redirectUrl,
            getData,
            emitResponse,
            method
        });
    }, []);

    useEffect(() => {
        const unsubscribe = onCheckoutSuccess(handleSuccessResult);
        return () => unsubscribe();
    }, [onCheckoutSuccess, handleSuccessResult]);
    return null;
}

export default SavedCardComponent;
