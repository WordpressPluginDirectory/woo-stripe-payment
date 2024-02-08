import {useEffect, useState, useRef} from '@wordpress/element';
import {useStripe, useElements} from "@stripe/react-stripe-js";
import {useSelect, useDispatch} from '@wordpress/data';
import {CART_STORE_KEY, PAYMENT_STORE_KEY, CHECKOUT_STORE_KEY} from '@woocommerce/block-data';
import {
    toCartAddress as mapToCartAddress,
    DEFAULT_BILLING_ADDRESS,
    DEFAULT_SHIPPING_ADDRESS, getSettings, getErrorMessage
} from '../../util';

const toCartAddress = mapToCartAddress();
const getData = getSettings('stripe_link_checkout_data');

export const useLink = (
    {
        email,
    }) => {
    const [link, setLink] = useState();
    const stripe = useStripe();
    const elements = useElements();
    const currentData = useRef({email, oldEmail: email, isProcessing: false});
    const launchLink = getData('launchLink');

    const {
        __internalSetActivePaymentMethod: setActivePaymentMethod,
        __internalSetPaymentMethodData: setPaymentMethodData,
        __internalSetExpressPaymentError: setExpressPaymentError,
        __internalSetExpressPaymentStarted
    } = useDispatch(PAYMENT_STORE_KEY);

    const {
        __internalSetBeforeProcessing: onSubmit
    } = useDispatch(CHECKOUT_STORE_KEY);

    const {
        setBillingAddress,
        setShippingAddress
    } = useDispatch(CART_STORE_KEY);

    const paymentStatus = useSelect(select => {
        const store = select(PAYMENT_STORE_KEY);
        return {
            isProcessing: store.isPaymentProcessing()
        }
    });

    useEffect(() => {
        if (stripe && elements && !link) {
            setLink(stripe.linkAutofillModal(elements));
        }
    }, [
        stripe,
        elements,
        link
    ]);

    useEffect(() => {
        if (link && launchLink) {
            const {email} = currentData.current;
            link.launch({email});
        }
    }, [link]);

    useEffect(() => {
        const {oldEmail = '', isProcessing = false} = currentData.current;
        if (link && oldEmail !== email && !isProcessing) {
            link.launch({email});
            currentData.current.oldEmail = email;
        }

    }, [
        link,
        email
    ]);

    useEffect(() => {
        currentData.current.email = email;
        currentData.current.isProcessing = paymentStatus.isProcessing;
    }, [email, paymentStatus.isProcessing]);

    useEffect(() => {
        if (link) {
            link.on('autofill', async event => {
                currentData.current.event = event;
                const {billingAddress = null, shippingAddress = null} = event.value;
                //const billing_details = getBillingDetailsFromAddress(billingAddress);
                try {
                    await elements.submit();
                    const result = await stripe.createPaymentMethod({
                        elements,
                        params: {
                            billing_details: billingAddress
                        }
                    });
                    if (result.error) {
                        throw result.error;
                    }

                    if (billingAddress) {
                        setBillingAddress({
                            ...DEFAULT_BILLING_ADDRESS,
                            ...toCartAddress({...billingAddress.address, recipient: billingAddress.name}),
                            email: currentData.current.email
                        });
                    }
                    if (shippingAddress) {
                        setShippingAddress({
                            ...DEFAULT_SHIPPING_ADDRESS,
                            ...toCartAddress({...shippingAddress.address, recipient: shippingAddress.name})
                        });
                    }

                    setPaymentMethodData({
                        stripe_cc_token_key: result.paymentMethod.id,
                        stripe_cc_save_source_key: false,
                    });
                    onSubmit();
                } catch (error) {
                    // set express error
                    console.log(error);
                    setExpressPaymentError(error.message);
                }
            });
            link.on('authenticated', event => {
                __internalSetExpressPaymentStarted();
                setActivePaymentMethod(getData('name'));
            })
        }
    }, [
        link,
        stripe,
        elements,
        onSubmit,
        setBillingAddress,
        setShippingAddress,
        setPaymentMethodData,
        setActivePaymentMethod,
        setExpressPaymentError,
        __internalSetExpressPaymentStarted
    ]);
}