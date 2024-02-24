import {useState, useRef, useEffect} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import classnames from 'classnames';
import {getRoute} from "../../../payment-methods/util";
import './style.scss';

export const Installments = (
    {
        i18n,
        paymentMethodName,
        cardFormComplete = false,
        addPaymentMethodData = null,
        getPaymentMethod
    }) => {
    const [installments, setInstallments] = useState(null);
    const [installment, setInstallment] = useState('');
    const [loading, setLoading] = useState(false);
    const onInstallmentSelected = (e) => {
        setInstallment(e.target.value);
        if (addPaymentMethodData) {
            addPaymentMethodData({_stripe_installment_plan: e.target.value});
        }
    }

    useEffect(() => {
        if (cardFormComplete) {
            // fetch the installments
            setLoading(true);
            setInstallment('');

            getPaymentMethod().then(async paymentMethod => {
                if (paymentMethod) {
                    // fetch the installment plans
                    const result = await apiFetch({
                        url: getRoute('create/payment_intent'),
                        method: 'POST',
                        data: {payment_method_id: paymentMethod, payment_method: paymentMethodName}
                    });
                    setInstallments(result.installments);
                    if (Object.keys(result.installments)?.length) {
                        setInstallment(Object.keys(result.installments)[0]);
                    }
                }
            }).catch(error => {
                console.log(error);
            }).finally(() => setLoading(false));
        }
    }, [cardFormComplete, getPaymentMethod]);

    return (
        <div className='wc-stripe-installments__container'>
            <label className={'wc-stripe-installments__label'}>
                {i18n.installments.pay}
                <Loader loading={loading}/>
            </label>
            <InstallmentOptions
                i18n={i18n}
                installment={installment}
                onChange={onInstallmentSelected}
                installments={installments}
                isLoading={loading}/>
        </div>
    )
}

const InstallmentOptions = ({installment, installments, onChange, isLoading, i18n}) => {
    let OPTIONS = null;
    if (isLoading) {
        OPTIONS = <option value="" disabled>{i18n.installments.loading}</option>
    } else {
        if (installments === null) {
            OPTIONS = <option value="" disabled>{i18n.installments.complete_form}</option>
        } else {
            OPTIONS = Object.keys(installments).map(id => {
                return <option key={id} value={id} dangerouslySetInnerHTML={{__html: installments[id].text}}/>
            });
        }
    }
    return (
        <select
            value={installment}
            onChange={onChange}
            className={classnames('wc-stripe-installment__options', {loading: isLoading})}>
            {OPTIONS}
        </select>
    );
}

const Loader = ({loading}) => {
    return (
        <div className="wc-stripe-installment-loader__container">
            {loading && <div className="wc-stripe-installment-loader">
                <div></div>
                <div></div>
                <div></div>
            </div>}
        </div>
    );
}
export default Installments;