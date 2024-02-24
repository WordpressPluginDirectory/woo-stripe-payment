import './style.scss';
import {registerCreditCardForm} from "@paymentplugins/stripe/util";
import {CardNumberElement, CardExpiryElement, CardCvcElement} from '@stripe/react-stripe-js';

const Bootstrap = ({CardIcon, options, onChange, i18n}) => {
    return (
        <div className='wc-stripe-bootstrap-form'>
            <div className='row'>
                <div className='col-md-6 mb-3'>
                    <CardNumberElement className='md-form md-outline stripe-input' options={options['cardNumber']}
                                       onChange={onChange(CardNumberElement)}/>
                    <label htmlFor="stripe-card-number">{i18n.labels.number}</label>
                    {CardIcon}
                </div>
                <div className='col-md-3 mb-3'>
                    <CardExpiryElement className='md-form md-outline stripe-input' options={options['cardExpiry']}
                                       onChange={onChange(CardExpiryElement)}/>
                    <label htmlFor="stripe-exp">{i18n.labels.exp}</label>
                </div>
                <div className='col-md-3 mb-3'>
                    <CardCvcElement className="md-form md-outline stripe-input" options={options['cardCvc']}
                                    onChange={onChange(CardCvcElement)}/>
                    <label htmlFor="stripe-cvv">{i18n.labels.cvv}</label>
                </div>
            </div>
        </div>
    )
}

registerCreditCardForm({
    id: 'bootstrap',
    breakpoint: 475,
    component: <Bootstrap/>
})