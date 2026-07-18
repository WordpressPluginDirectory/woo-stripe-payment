<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe_Local_Payment' ) ) {
	return;
}

/**
 *
 * @package PaymentPlugins\Gateways
 * @author  PaymentPlugins
 *
 */
class WC_Payment_Gateway_Stripe_PromptPay extends WC_Payment_Gateway_Stripe_Local_Payment {

	public $id = 'stripe_promptpay';

	protected $payment_method_type = 'promptpay';

	public $synchronous = false;

	use WC_Stripe_Local_Payment_Intent_Trait;

	public function __construct( ...$args ) {
		$this->currencies         = array( 'THB' );
		$this->countries          = array();
		$this->tab_title          = __( 'PromptPay', 'woo-stripe-payment' );
		$this->method_title       = __( 'PromptPay (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'PromptPay gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/promptpay.svg' );
	}

	public function hooks() {
		parent::hooks();
		add_action( 'woocommerce_api_stripe_promptpay', array( $this, 'handle_api_request' ) );
	}

	public function get_local_payment_description() {
		$this->local_payment_description = wc_stripe_get_template_html( 'checkout/promptpay-instructions.php', array( 'button_text' => $this->order_button_text ) );

		return parent::get_local_payment_description();
	}

	public function handle_api_request() {
		if ( WC()->session ) {
			$order_id = WC()->session->get( 'order_awaiting_payment', null );
			if ( $order_id ) {
				$order = wc_get_order( absint( $order_id ) );
				if ( $order ) {
					$payment_intent = $this->gateway->mode( $order )->paymentIntents->retrieve( $order->get_meta( WC_Stripe_Constants::PAYMENT_INTENT_ID ) );
					if ( ! is_wp_error( $payment_intent ) ) {
						if ( $payment_intent->status === 'requires_payment_method' ) {
							return wp_safe_redirect( wc_get_checkout_url() );
						} elseif ( $payment_intent->status === 'succeeded' ) {
							return wp_safe_redirect( $order->get_checkout_order_received_url() );
						}
					}
				}
			}
		}
	}

}
