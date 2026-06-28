<?php

namespace PaymentPlugins\Stripe\Payments;

use PaymentPlugins\Stripe\ContextHandler;
use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;
use PaymentPlugins\Stripe\Registry\BaseRegistry;
use PaymentPlugins\Stripe\Utilities\ProductUtils;

/**
 * Payment Gateway Registry
 *
 * Manages registration and retrieval of payment gateways.
 *
 * @since 4.0.0
 */
class PaymentGatewayRegistry extends BaseRegistry {

	protected $registry_id = 'payment_gateways';

	public function get_active_integrations() {
		return array_filter( $this->get_registered_integrations(), function ( $payment_method ) {

			/**
			 * The "enabled" property is used here instead of the is_available() method because
			 * we want to know about all enabled gateways, not just the ones that pass the is_available() check.
			 * The is_available() method checks conditions more than just if the gateway's "enabled" property is "yes".
			 * This method is called by other code like get_checkout_script_handles so it's important to make sure
			 * those scripts are loaded if the gateway is enabled.
			 * @var AbstractGateway $payment_method
			 */
			return $payment_method->enabled === 'yes';
		} );
	}

	/**
	 * Get payment gateways available on the cart page.
	 *
	 * @return array
	 */
	public function get_cart_payment_gateways() {
		$gateways = [];
		foreach ( $this->get_active_integrations() as $payment_method ) {
			/**
			 * @var AbstractGateway $payment_method
			 */
			if ( $payment_method->supports( 'wc_stripe_cart_checkout' ) && $payment_method->is_cart_section_enabled() ) {
				$gateways[ $payment_method->id ] = $payment_method;
			}
		}

		return apply_filters( 'wc_stripe_cart_payment_methods', $gateways );
	}

	/**
	 * Get payment gateways available on the product page.
	 *
	 * @return array
	 */
	public function get_product_payment_gateways() {
		$product  = ProductUtils::get_queried_product();
		$gateways = [];
		foreach ( $this->get_active_integrations() as $gateway ) {
			/**
			 * @var AbstractGateway $gateway
			 */
			if ( ! $gateway->supports( 'wc_stripe_product_checkout' ) ) {
				continue;
			}

			if ( $gateway->is_product_section_enabled( $product ) ) {
				$gateways[ $gateway->id ] = $gateway;
			}

		}

		return apply_filters( 'wc_stripe_product_payment_methods', $gateways, $product );
	}

	/**
	 * Get payment gateways available on the checkout page.
	 *
	 * @return array
	 */
	public function get_checkout_payment_gateways() {
		// TODO: Filter gateways that support checkout page
		return array_filter( $this->registry, function ( $gateway ) {
			return true; // Placeholder
		} );
	}

	/**
	 * Get express checkout payment gateways (Link, Apple Pay, Google Pay).
	 *
	 * @return array
	 */
	public function get_express_payment_gateways() {
		$gateways = [];
		foreach ( $this->get_active_integrations() as $gateway ) {
			if ( $gateway->is_express_checkout_enabled() ) {
				$gateways[ $gateway->id ] = $gateway;
			}
		}

		/**
		 * @since 3.3.47
		 */
		return apply_filters( 'wc_stripe_express_payment_methods', $gateways );
	}

	/**
	 * Get payment gateways available in the mini cart.
	 *
	 * @return array
	 */
	public function get_minicart_payment_gateways() {
		$gateways = [];
		foreach ( $this->get_active_integrations() as $payment_method ) {
			/**
			 * @var AbstractGateway $payment_method
			 */
			if ( $payment_method->supports( 'wc_stripe_mini_cart_checkout' )
			     && $payment_method->is_minicart_section_enabled() ) {
				$gateways[ $payment_method->id ] = $payment_method;
			}
		}

		return apply_filters( 'wc_stripe_mini_cart_payment_methods', $gateways );
	}

	/**
	 * @param ContextHandler $context
	 *
	 * @return AbstractGateway[]
	 */
	public function get_bnpl_payment_gateways( $context ) {
		$gateways = [];
		$section  = $context->get_context();
		foreach ( $this->get_registered_integrations() as $gateway ) {
			if ( ! $gateway->supports( 'stripe_bnpl_msg' ) ) {
				continue;
			}

			if ( $context->is_product() ) {
				if ( ! $gateway->is_product_section_enabled( $context->get_product_id() ) ) {
					continue;
				}
				if ( \wc_string_to_bool( $gateway->get_option( 'message_enabled', 'yes' ) ) ) {
					$gateways[ $gateway->id ] = $gateway;
				}
				continue;
			}

			$enabled = \wc_string_to_bool( $gateway->get_option( 'message_enabled', 'yes' ) );
			if ( ! $enabled ) {
				continue;
			}

			if ( $section ) {
				$payment_sections = $gateway->get_option( 'message_sections', [] );
				if ( ! is_array( $payment_sections ) ) {
					$payment_sections = [];
				}

				if ( in_array( $section, $payment_sections, true ) ) {
					$gateways[ $gateway->id ] = $gateway;
				}
			} else {
				$gateways[ $gateway->id ] = $gateway;
			}
		}

		return $gateways;
	}

	/**
	 * Get script dependencies for cart page.
	 *
	 * @return array Array of script handles.
	 */
	public function get_cart_script_handles() {
		$handles = [];
		foreach ( $this->get_cart_payment_gateways() as $gateway ) {
			$handles = array_merge( $handles, $gateway->get_cart_script_handles() );
		}

		return $handles;
	}

	/**
	 * Get script dependencies for product page.
	 *
	 * @return array Array of script handles.
	 */
	public function get_product_script_handles() {
		$handles = [];
		foreach ( $this->get_product_payment_gateways() as $integration ) {
			$handles = array_merge( $handles, $integration->get_product_script_handles() );
		}

		return $handles;
	}

	/**
	 * Get script dependencies for checkout page.
	 *
	 * @return array Array of script handles.
	 */
	public function get_checkout_script_handles() {
		$handles = [];
		foreach ( $this->get_active_integrations() as $integration ) {
			/**
			 * @var AbstractGateway $integration
			 */
			$handles = array_merge( $handles, $integration->get_checkout_script_handles() );

			if ( $integration->supports( 'tokenization' ) && ! wp_script_is( 'woocommerce-tokenization-form', 'enqueued' ) ) {
				$integration->tokenization_script();
			}
		}

		return $handles;
	}

	/**
	 * Get script dependencies for express checkout.
	 *
	 * @return array Array of script handles.
	 */
	public function get_express_checkout_script_handles() {
		$handles = [];
		foreach ( $this->get_express_payment_gateways() as $integration ) {
			/**
			 * @var AbstractGateway $integration
			 */
			$handles = array_merge( $handles, $integration->get_express_checkout_script_handles() );
		}

		return $handles;
	}

	/**
	 * Get script dependencies for add payment method page.
	 *
	 * @return array Array of script handles.
	 */
	public function get_add_payment_method_script_handles() {
		$handles = [];
		foreach ( $this->get_active_integrations() as $integration ) {
			if ( $integration->supports( 'add_payment_method' ) ) {
				/**
				 * @var AbstractGateway $integration
				 */
				$handles = array_merge( $handles, $integration->get_add_payment_method_script_handles() );
			}
		}

		return $handles;
	}

	/**
	 * Get script dependencies for mini cart.
	 *
	 * @return array Array of script handles.
	 */
	public function get_minicart_script_handles() {
		$handles = [];
		foreach ( $this->get_minicart_payment_gateways() as $integration ) {
			/**
			 * @var AbstractGateway $integration
			 */
			$handles = array_merge( $handles, $integration->get_minicart_script_handles() );
		}

		return $handles;
	}

	public function get_shop_script_handles() {
		return [];
	}
}