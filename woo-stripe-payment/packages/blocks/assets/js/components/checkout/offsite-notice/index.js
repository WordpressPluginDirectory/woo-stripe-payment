import {getSetting} from '@woocommerce/settings'

const data = getSetting('stripeGeneralData');

export const OffsiteNotice = (
    {
        text
    }
) => {
    return (
        <div className="wc-stripe-blocks-offsite-notice">
            <div>
                <img src={`${data.assetsUrl}/img/offsite.svg`}/>
                <p>{text}</p>
            </div>
        </div>
    )
}