<?php


namespace PaymentPlugins\Stripe\Blocks\Payments;

use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use PaymentPlugins\Stripe\Blocks\StoreApi\EndpointData;
use PaymentPlugins\Stripe\Controllers\PaymentIntentController;
use PaymentPlugins\Stripe\RequestContext;

/**
 * Class AbstractLocalStripePayment
 *
 * @package PaymentPlugins\Stripe\Blocks\Payments
 */
abstract class AbstractStripeLocalPayment extends AbstractStripePayment {

	public function get_payment_method_script_handles() {
		if ( ! wp_script_is( 'wc-stripe-block-local-payment', 'registered' ) ) {
			$this->assets_api->register_script( 'wc-stripe-block-local-payment', 'build/wc-stripe-local-payment.js' );
		}

		return array( 'wc-stripe-block-local-payment' );
	}

	public function get_payment_method_data() {
		return array(
			'name'                  => $this->get_name(),
			'gatewayId'             => $this->get_name(),
			'title'                 => $this->payment_method->get_title(),
			'description'           => $this->payment_method->get_description(),
			'icon'                  => $this->get_payment_method_icon(),
			'features'              => $this->get_supported_features(),
			'placeOrderButtonLabel' => \esc_html( $this->payment_method->order_button_text ),
			'allowedCountries'      => $this->payment_method->get_option( 'allowed_countries' ),
			'exceptCountries'       => $this->payment_method->get_option( 'except_countries', array() ),
			'specificCountries'     => $this->payment_method->get_option( 'specific_countries', array() ),
			'limitedCountries'      => $this->payment_method->limited_countries,
			'currencies'            => $this->payment_method->currencies,
			'paymentElementOptions' => $this->payment_method->get_payment_element_options(),
			'elementOptions'        => $this->payment_method->get_element_options(),
			'isAdmin'               => is_admin(),
			'paymentMethodType'     => $this->payment_method->get_payment_method_type(),
			'locale'                => str_replace( '_', '-', substr( get_locale(), 0, 5 ) ),
			'i18n'                  => $this->get_script_translations(),
			'termsDisplayRule'      => stripe_wc()->advanced_settings->get_terms_display_rule()
		);
	}

	protected function get_payment_method_icon() {
		return array(
			'id'  => $this->get_name(),
			'alt' => '',
			'src' => $this->payment_method->icon
		);
	}

	protected function get_script_translations() {
		return [
			'offsite'           => sprintf(
				__( 'After clicking "%1$s", you will be redirected to %2$s to complete your purchase securely', 'woo-stripe-payment' ),
				$this->payment_method->order_button_text,
				$this->payment_method->get_title()
			),
			'empty_data'        => __( 'Please enter your payment info before proceeding.', 'woo-stripe-payment' ),
			'payment_cancelled' => __( 'Payment has been cancelled.', 'woo-stripe-payment' )
		];
	}

	public function get_endpoint_data() {
		$endpoint_data = new EndpointData();
		$endpoint_data->set_namespace( $this->get_name() );
		$endpoint_data->set_endpoint( CartSchema::IDENTIFIER );
		$endpoint_data->set_schema_type( ARRAY_A );
		$endpoint_data->set_data_callback( [ $this, 'get_cart_extension_data' ] );

		return $endpoint_data;
	}

	public function get_cart_extension_data() {
		/**
		 * @var PaymentIntentController $payment_intent_ctrl
		 */
		$payment_intent_ctrl = wc_stripe_get_container()->get( PaymentIntentController::class );
		$payment_intent_ctrl->set_request_context( new RequestContext( RequestContext::CHECKOUT ) );
		if ( method_exists( $this->payment_method, 'get_payment_method_type' ) ) {
			return [
				'elementOptions' => array_merge(
					$payment_intent_ctrl->get_element_options(),
					[
						'locale'             => wc_stripe_get_site_locale(),
						'paymentMethodTypes' => [ $this->payment_method->get_payment_method_type() ]
					]
				)
			];
		}

		return [];
	}

}