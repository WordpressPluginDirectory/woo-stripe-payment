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
class WC_Payment_Gateway_Stripe_Bancontact extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;
	use \PaymentPlugins\Stripe\Traits\TokenizationTrait;
	use \PaymentPlugins\Stripe\WooCommercePreOrders\Traits\PreOrdersTrait;
	use \PaymentPlugins\Stripe\WooCommerceSubscriptions\Traits\WooCommerceSubscriptionsTrait;

	public $id = 'stripe_bancontact';

	protected $payment_method_type = 'bancontact';

	public $token_type = 'Stripe_Sepa';

	public function __construct( ...$args ) {
		$this->currencies         = array( 'EUR' );
		$this->countries          = array( 'BE' );
		$this->tab_title          = __( 'Bancontact', 'woo-stripe-payment' );
		$this->method_title       = __( 'Bancontact (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Bancontact gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/bancontact.svg' );
	}

}
