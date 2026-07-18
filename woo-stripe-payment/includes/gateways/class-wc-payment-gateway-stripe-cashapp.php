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
class WC_Payment_Gateway_Stripe_CashApp extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;
	use \PaymentPlugins\Stripe\Traits\TokenizationTrait;
	use \PaymentPlugins\Stripe\WooCommercePreOrders\Traits\PreOrdersTrait;
	use \PaymentPlugins\Stripe\WooCommerceSubscriptions\Traits\WooCommerceSubscriptionsTrait;

	public $id = 'stripe_cashapp';

	protected $payment_method_type = 'cashapp';

	public function __construct( ...$args ) {
		$this->currencies         = array( 'USD' );
		$this->countries          = array( 'US' );
		$this->tab_title          = __( 'Cash App', 'woo-stripe-payment' );
		$this->method_title       = __( 'Cash App (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Cash App gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/cashapp.svg' );
	}

}
