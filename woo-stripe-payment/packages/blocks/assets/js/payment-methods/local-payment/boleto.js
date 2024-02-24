import {useState, useEffect, useCallback} from '@wordpress/element';
import {registerPaymentMethod} from '@woocommerce/blocks-registry';
import classnames from 'classnames';
import {ensureErrorResponse, ensureSuccessResponse, getSettings, isTestMode} from "../util";
import {LocalPaymentIntentContent} from './local-payment-method';
import {PaymentMethodLabel, PaymentMethod} from "../../components/checkout";
import {canMakePayment} from "./local-payment-method";

const getData = getSettings('stripe_boleto_data');

const BoletoPaymentMethodContainer = ({eventRegistration, ...props}) => {
    const [taxId, setTaxId] = useState('');
    const [isActive, setIsActive] = useState(false);
    const {onPaymentSetup} = eventRegistration;
    const callback = useCallback(() => {
        return {
            boleto: {
                tax_id: taxId
            }
        };
    }, [taxId]);

    useEffect(() => {
        const unsubscribe = onPaymentSetup(() => {
            if (!taxId) {
                return ensureErrorResponse(
                    props.emitResponse.responseTypes,
                    getData('i18n').cpf_notice,
                    {
                        messageContext: props.emitResponse.noticeContexts.PAYMENTS
                    }
                );
            }
            return ensureSuccessResponse(props.emitResponse.responseTypes, {
                meta: {
                    paymentMethodData: {
                        wc_stripe_boleto_tax_id: taxId
                    }
                }
            });
        })
        return () => unsubscribe();
    }, [onPaymentSetup, taxId]);
    return (
        <>
            <div className={classnames('wc-block-components-text-input', {
                'is-active': isActive || taxId
            })}>
                <input
                    type='text'
                    id='wc-stripe-boleto-tax_id'
                    onChange={e => setTaxId(e.target.value)}
                    onFocus={() => setIsActive(true)}
                    onBlur={() => setIsActive(false)}/>
                <label htmlFor='wc-stripe-boleto-tax_id'>{getData('i18n').tax_id_label}</label>
            </div>
            {isTestMode() &&
                <div className='wc-stripe-boleto__description'>
                    <p>{getData('i18n').test_desc}</p>
                    <div>
                        <label>CPF:</label>&nbsp;<span>000.000.000-00</span>
                    </div>
                    <div>
                        <label>CNPJ:</label>&nbsp;<span>00.000.000/0000-00</span>
                    </div>
                </div>}
            {!isTestMode() &&
                <div className="wc-stripe-boleto__description">
                    <p>{getData('i18n').formats}</p>
                    <div>
                        <label>CPF:</label>&nbsp;
                        <span>{getData('i18n').cpf_format}</span>
                    </div>
                    <div>
                        <label>CNPJ:</label>&nbsp;
                        <span>{getData('i18n').cnpj_format}</span>
                    </div>
                </div>}
            <LocalPaymentIntentContent callback={callback} {...{...props, ...{eventRegistration}}}/>
        </>
    )
}

if (getData()) {
    registerPaymentMethod({
        name: getData('name'),
        label: <PaymentMethodLabel
            title={getData('title')}
            paymentMethod={getData('name')}
            icons={getData('icon')}/>,
        ariaLabel: 'Boleto',
        placeOrderButtonLabel: getData('placeOrderButtonLabel'),
        canMakePayment: canMakePayment(getData),
        content: <PaymentMethod
            content={BoletoPaymentMethodContainer}
            getData={getData}
            confirmationMethod={'confirmBoletoPayment'}/>,
        edit: <PaymentMethod content={LocalPaymentIntentContent} getData={getData}/>,
        supports: {
            showSavedCards: false,
            showSaveOption: false,
            features: getData('features')
        }
    })
}