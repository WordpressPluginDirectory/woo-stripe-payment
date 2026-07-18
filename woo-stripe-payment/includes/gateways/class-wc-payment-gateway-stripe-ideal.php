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
class WC_Payment_Gateway_Stripe_Ideal extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;
	use \PaymentPlugins\Stripe\Traits\TokenizationTrait;
	use \PaymentPlugins\Stripe\WooCommercePreOrders\Traits\PreOrdersTrait;
	use \PaymentPlugins\Stripe\WooCommerceSubscriptions\Traits\WooCommerceSubscriptionsTrait;

	public $id = 'stripe_ideal';

	protected $payment_method_type = 'ideal';

	public $token_type = 'Stripe_Sepa';

	public function __construct( ...$args ) {
		$this->currencies         = array( 'EUR' );
		$this->countries          = array( 'NL' );
		$this->tab_title          = __( 'iDEAL', 'woo-stripe-payment' );
		$this->method_title       = __( 'iDEAL (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Ideal gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/' . $this->get_option( 'icon' ) . '.svg' );
	}

	public function get_local_payment_settings() {
		return array_merge(
			parent::get_local_payment_settings(),
			[
				'icon' => array(
					'title'       => __( 'Icon', 'woo-stripe-payment' ),
					'type'        => 'select',
					'options'     => array(
						'ideal'      => __( 'iDEAL | Wero', 'woo-stripe-payment' ),
						'ideal_dark' => __( 'iDEAL | Wero dark', 'woo-stripe-payment' ),
					),
					'default'     => 'ideal',
					'desc_tip'    => true,
					'description' => __( 'This is the icon style that appears next to the gateway on the checkout page. If you have messaging enabled on the checkout page, that will override the icon.', 'woo-stripe-payment' ),
				),
			]
		);
	}

}
