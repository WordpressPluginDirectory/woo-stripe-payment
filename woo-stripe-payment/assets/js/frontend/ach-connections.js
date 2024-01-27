import $ from 'jquery';
import {BaseGateway, CheckoutGateway} from '@paymentplugins/wc-stripe';

function Extendor(params) {
    BaseGateway.call(this, params);
    CheckoutGateway.call(this);
};

Object.assign(Extendor.prototype, BaseGateway.prototype, CheckoutGateway.prototype);

class StripeACH extends Extendor {
    constructor(params) {
        super(params);
        this.handle_create_account_change();
    }

    initialize() {
        window.addEventListener('hashchange', this.hashchange.bind(this));
        $(document.body).on('payment_method_selected', this.payment_method_selected.bind(this));
        $(document.body).on('click', '#place_order', this.on_order_pay.bind(this));
        $(document.body).on('change', '#createaccount', this.handle_create_account_change.bind(this));
    }

    updated_checkout() {
        this.handle_create_account_change();
    }

    checkout_place_order() {
        this.payment_token_received = true;
        return super.checkout_place_order();
    }

    on_order_pay(e) {
        if (this.is_gateway_selected()) {
            // create the setup intent.
            if (this.is_change_payment_method()) {
                const {order_id, order_key} = this.params;
                e.preventDefault();
                this.create_setup_intent({order_id, order_key}).then(({setup_intent}) => {
                    this.handle_payment_method_setup({
                        order_id,
                        order_key,
                        client_secret: setup_intent.client_secret
                    });
                });
            } else if (this.is_current_page('order_pay')) {
                e.preventDefault();
                this.process_order_pay();
            }
        }
    }

    handle_next_action(data) {
        const {client_secret} = data;
        this.stripe.collectBankAccountForPayment({
            clientSecret: client_secret,
            params: {
                payment_method_type: 'us_bank_account',
                payment_method_data: {
                    billing_details: {
                        name: this.get_customer_name(this.get_billing_prefix()),
                        email: this.fields.get('billing_email', null),
                    },
                },
            }
        }).then(({paymentIntent, error}) => {
            if (error) {
                return this.submit_error(error);
            }
            // show the mandate confirmation
            if (paymentIntent.status === "requires_confirmation") {
                this.do_payment_confirmation(data);
            } else {
                this.cancel_payment_processing();
            }
        });
    }

    handle_payment_method_setup(data) {
        const {client_secret} = data;
        this.stripe.collectBankAccountForSetup({
            clientSecret: client_secret,
            params: {
                payment_method_type: 'us_bank_account',
                payment_method_data: {
                    billing_details: {
                        name: this.get_customer_name(this.get_billing_prefix()),
                        email: this.fields.get('billing_email', null),
                    },
                },
            }
        }).then(({setupIntent, error}) => {
            if (error) {
                return this.submit_error(error);
            }
            // show the mandate confirmation
            if (setupIntent.status === "requires_confirmation") {
                this.do_setup_confirmation(data);
            } else {
                this.cancel_payment_processing();
            }
        });
    }

    do_payment_confirmation({client_secret, order_id, order_key}) {
        this.stripe.confirmUsBankAccountPayment(client_secret).then(({paymentIntent, error}) => {
            if (error) {
                return this.submit_error(error);
            }
            if (paymentIntent.status === 'requires_payment_method') {
                this.cancel_payment_processing();
            } else if (paymentIntent.status === 'requires_action') {
                // paymentIntent?.next_action?.type === 'verify_with_microdeposits'
                // todo - eventually support microdeposits. For now, show a message
                this.cancel_payment_processing();
                return this.submit_error({code: 'ach_instant_only'});
            } else if (paymentIntent.status === 'processing') {
                this.set_nonce(paymentIntent.payment_method);
                if (this.is_current_page('order_pay')) {
                    this.get_form().trigger('submit');
                } else {
                    this.process_payment(order_id, order_key);
                }
            }
        });
    }

    do_setup_confirmation({client_secret, order_id, order_key}) {
        this.stripe.confirmUsBankAccountSetup(client_secret).then(({setupIntent, error}) => {
            if (error) {
                return this.submit_error(error);
            }
            if (setupIntent.status === 'requires_payment_method') {
                this.cancel_payment_processing();
            } else if (setupIntent.status === 'requires_action') {
                // paymentIntent?.next_action?.type === 'verify_with_microdeposits'
                // todo - eventually support microdeposits. For now, show a message
                this.cancel_payment_processing();
                return this.submit_error({code: 'ach_instant_only'});
            } else if (setupIntent.status === 'succeeded') {
                this.set_nonce(setupIntent.payment_method);
                if (this.is_current_page('order_pay') || this.is_change_payment_method()) {
                    this.get_form().trigger('submit');
                } else {
                    this.process_payment(order_id, order_key);
                }
            }
        });
    }

    hide_place_order() {
    }

    show_payment_button() {
        this.show_place_order();
    }

    cancel_payment_processing() {
        $(this.container).closest('form').removeClass('processing');
        $(this.container).closest('form')?.unblock();
    }

    fees_enabled() {
        return this.params.fees_enabled == "1";
    }

    payment_method_selected() {
        if (this.fees_enabled()) {
            $(document.body).trigger('update_checkout');
        }
    }

    create_setup_intent(data) {
        return new Promise((resolve, reject) => {
            $.ajax({
                method: 'POST',
                dataType: 'json',
                data: {payment_method: this.gateway_id, ...data},
                url: this.params.routes.base_path.replace('%s', 'wc-stripe/v1/subscriptions/setup-intent'),
                beforeSend: this.ajax_before_send.bind(this)
            }).done(response => {
                if (response.code) {
                    reject(response.message);
                } else {
                    resolve(response);
                }
            }).fail((xhr, textStatus, errorThrown) => {
                this.submit_error(errorThrown);
            });
        })
    }
}

//StripeACH.prototype = {...BaseGateway.prototype, ...CheckoutGateway.prototype, ...StripeACH.prototype};

new StripeACH(wc_stripe_ach_connections_params);