import './style.scss';
import {registerCreditCardForm} from "@paymentplugins/stripe/util";
import {CardNumberElement, CardExpiryElement, CardCvcElement} from '@stripe/react-stripe-js';
import {useEffect} from '@wordpress/element';

const SimpleForm = ({CardIcon, options, onChange, i18n}) => {
    useEffect(() => {
    }, []);
    return (
        <div className='wc-stripe-simple-form'>
            <div className="row">
                <div className="field">
                    <div className='field-item'>
                        <CardNumberElement id="stripe-card-number" className="input empty"
                                           options={options['cardNumber']}
                                           onChange={onChange(CardNumberElement)}/>
                        <label htmlFor="stripe-card-number"
                               data-tid="">{i18n.labels.number}</label>
                        <div className="baseline"></div>
                        {CardIcon}
                    </div>
                </div>
            </div>
            <div className="row">
                <div className="field half-width">
                    <div className='field-item'>
                        <CardExpiryElement id="stripe-exp" className="input empty" options={options['cardExpiry']}
                                           onChange={onChange(CardExpiryElement)}/>
                        <label htmlFor="stripe-exp"
                               data-tid="">{i18n.labels.exp}</label>
                        <div className="baseline"></div>
                    </div>
                </div>
                <div className="field half-width cvc">
                    <div className='field-item'>
                        <CardCvcElement id="stripe-cvv" className="input empty" options={options['cardCvc']}
                                        onChange={onChange(CardCvcElement)}/>
                        <label htmlFor="stripe-cvv"
                               data-tid="">{i18n.labels.cvv}</label>
                        <div className="baseline"></div>
                    </div>
                </div>
            </div>
        </div>
    )
}

registerCreditCardForm({
    id: 'simple',
    component: <SimpleForm/>,
    breakpoint: 375
});