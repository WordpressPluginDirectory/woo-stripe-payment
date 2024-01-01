import {useEffect, useState, useRef} from '@wordpress/element';
import {useStripe, useElements} from "@stripe/react-stripe-js";
import {getSetting} from '@woocommerce/settings';
import {
    toCartAddress as mapToCartAddress,
    ensureSuccessResponse,
    ensureErrorResponse,
    getBillingDetailsFromAddress,
    DEFAULT_BILLING_ADDRESS,
    DEFAULT_SHIPPING_ADDRESS, getSettings, getErrorMessage
} from '../../util';

const generalData = getSettings('stripeGeneralData');
const linkSettings = getSetting('stripe_link_checkout_data');

const toCartAddress = mapToCartAddress();

export const useLink = (
    {
        email,
        eventRegistration,
        onClick,
        onSubmit,
        onError,
        activePaymentMethod,
        emitResponse,
        paymentStatus,
        ...props
    }) => {
    const [link, setLink] = useState();
    const stripe = useStripe();
    const elements = useElements();
    const currentData = useRef({});
    const linkData = useRef();
    const {onPaymentSetup} = eventRegistration;

    useEffect(() => {
        currentData.current = {...currentData.current, email, onClick, onSubmit, onError, paymentStatus};
    }, [email, onClick, onSubmit, onError, paymentStatus.isProcessing]);

    useEffect(() => {
        if (stripe && elements && !link) {
            setLink(stripe?.linkAutofillModal(elements));
        }
    }, [
        stripe,
        elements,
        link
    ]);

    useEffect(() => {
        if (link && linkSettings.launchLink) {
            const {email} = currentData.current;
            link.launch({email});
        }
    }, [link]);

    useEffect(() => {
        const {oldEmail = '', paymentStatus} = currentData.current;
        if (link && oldEmail !== email && !paymentStatus.isProcessing) {
            link.launch({email});
        }
        currentData.current.oldEmail = email;
    }, [link, email]);

    useEffect(() => {
        if (link) {
            link.on('autofill', event => {
                linkData.current = event;
                currentData.current.onSubmit();

            });
            link.on('authenticated', event => {
                currentData.current.onClick();
            })
        }
    }, [link]);

    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            if (activePaymentMethod !== 'stripe_link_checkout') {
                return null;
            }
            const response = {meta: {}};
            const {shippingAddress = null, billingAddress = null} = linkData.current.value;
            let billing_details;
            if (billingAddress) {
                const address = toCartAddress({...billingAddress.address, recipient: billingAddress.name});
                billing_details = getBillingDetailsFromAddress(address);
                response.meta.billingAddress = {
                    ...DEFAULT_BILLING_ADDRESS,
                    ...address,
                    email: currentData.current.email
                };
            }
            if (shippingAddress) {
                const address = toCartAddress({...shippingAddress.address, recipient: shippingAddress.name});
                response.meta.shippingAddress = {
                    ...DEFAULT_SHIPPING_ADDRESS,
                    ...address
                }
            }
            // update the payment intent
            try {
                await elements.submit();
                const result = await stripe.createPaymentMethod({
                    elements,
                    params: {
                        billing_details
                    }
                });
                if (result.error) {
                    throw result.error;
                }
                response.meta.paymentMethodData = {
                    stripe_cc_token_key: result.paymentMethod.id,
                    stripe_cc_save_source_key: false,
                }
                return ensureSuccessResponse(emitResponse.responseTypes, response);
            } catch (error) {
                console.log(error);
                currentData.current.onError(getErrorMessage(error));
                return {
                    type: emitResponse.responseTypes.FAIL,
                    ...response
                }
            }
        });

        return () => unsubscribe();
    }, [
        onPaymentSetup,
        stripe,
        elements,
        activePaymentMethod
    ]);

    return link;
}