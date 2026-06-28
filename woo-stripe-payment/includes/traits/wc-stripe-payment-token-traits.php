<?php
defined( 'ABSPATH' ) || exit();

/**
 *
 * @author PaymentPlugins
 * @since 3.1.5
 * @package PaymentPlugins\Traits
 *
 */
trait WC_Payment_Token_Payment_Method_Trait {

	public function save_payment_method() {
		return wc_stripe_get_container()
			->get( \PaymentPlugins\Stripe\Client\StripeClient::class )
			->paymentMethods
			->attach( $this->get_token(), array( 'customer' => $this->get_customer_id() ) );
	}

	/**
	 * @return mixed
	 * @deprecated
	 */
	public function delete_from_stripe() {
		return wc_stripe_get_container()
			->get( \PaymentPlugins\Stripe\Client\StripeClient::class )
			->mode( $this->get_environment() )
			->paymentMethods
			->detach( $this->get_token() );
	}
}
