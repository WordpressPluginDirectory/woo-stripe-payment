<?php

namespace PaymentPlugins\Stripe\WooCommerceSubscriptions\Controllers;

use PaymentPlugins\Stripe\RequestContext;
use PaymentPlugins\Stripe\WooCommerceSubscriptions\FrontendRequests;

/**
 * @package PaymentPlugins\WooCommerceSubscriptions\Stripe
 */
class PaymentIntent {

	private $request;

	public function __construct( FrontendRequests $request ) {
		$this->request = $request;
		$this->initialize();
	}

	private function initialize() {
		add_filter( 'wc_stripe_payment_intent_args', [ $this, 'update_payment_intent_args' ], 10, 2 );
		add_filter( 'wc_stripe_setup_intent_params', [ $this, 'add_setup_intent_params' ], 10, 3 );
		add_filter( 'wc_stripe_update_setup_intent_params', [ $this, 'update_setup_intent_params' ], 10, 2 );


		add_filter( 'wc_stripe_deferred_intent_subscription_mode', [ $this, 'is_subscription_mode' ], 10, 2 );

		add_filter( 'wc_stripe_create_setup_intent', [ $this, 'is_setup_intent_needed' ], 10, 2 );

		add_filter( 'wc_stripe_is_link_active', [ $this, 'is_link_active' ] );
	}

	private function account_requires_mandate() {
		return stripe_wc()->account_settings->get_account_country( wc_stripe_mode() ) === 'IN';
	}

	/**
	 * @param                $bool
	 * @param RequestContext $context
	 *
	 * @return mixed|true
	 * @deprecated 4.0.0
	 */
	public function is_setup_intent_needed( $bool, RequestContext $context ) {
		if ( ! $bool ) {
			if ( $this->request->is_change_payment_method() ) {
				$bool = true;
			}
		}

		return $bool;
	}

	/**
	 * @param $bool
	 *
	 * @return bool|mixed
	 * @since 3.3.60
	 */
	public function is_subscription_mode( $bool, RequestContext $context ) {
		if ( ! $bool ) {
			$is_manual_enabled = function_exists( 'wcs_is_manual_renewal_enabled' )
			                     && \wcs_is_manual_renewal_enabled();

			// if $is_manual_enabled is enabled then subscription mode isn't needed.
			if ( ! $is_manual_enabled ) {
				if ( $this->request->is_order_pay_with_subscription( $context ) ) {
					$bool = true;
				} elseif ( $this->request->is_checkout_with_subscription( $context ) ) {
					$bool = true;
				}
			}
		}

		return $bool;
	}

	/**
	 * @param array     $args
	 * @param \WC_Order $order
	 *
	 * @return array
	 */
	public function update_payment_intent_args( $args, $order ) {
		return $this->add_params_to_intent( $args, $order );
	}

	public function update_setup_intent_params( $args, $order ) {
		return $this->add_params_to_intent( $args, $order, 'setup_intent' );
	}

	/**
	 * @param array     $args
	 * @param \WC_Order $order
	 * @param string    $type
	 *
	 * @return array
	 */
	private function add_params_to_intent( $args, $order, $type = 'payment_intent' ) {
		if ( isset( $args['payment_method_types'] ) && in_array( 'card', $args['payment_method_types'] ) ) {
			// check if this is an India account. If so, make sure mandate data is included.
			if ( stripe_wc()->account_settings->get_account_country( wc_stripe_order_mode( $order ) ) === 'IN' ) {
				if ( isset( $args['setup_future_usage'] ) && $args['setup_future_usage'] === 'off_session'
				     || $type === 'setup_intent'
				     || wcs_order_contains_subscription( $order )
				) {
					$subscriptions = wcs_get_subscriptions_for_order( $order );
					if ( $subscriptions ) {
						$total = max( array_map( function ( $subscription ) {
							return (float) $subscription->get_total();
						}, $subscriptions ) );
						if ( ! isset( $args['payment_method_options']['card'] ) ) {
							$args['payment_method_options']['card'] = [];
						}
						$args['payment_method_options']['card']['mandate_options'] = array(
							'amount'          => wc_stripe_add_number_precision( $total, $order->get_currency() ),
							'amount_type'     => 'maximum',
							'interval'        => 'sporadic',
							'reference'       => $order->get_id(),
							'start_date'      => time(),
							'supported_types' => [ 'india' ]
						);
						if ( $type === 'setup_intent' ) {
							$args['payment_method_options']['card']['mandate_options']['currency'] = $order->get_currency();
						}
					}
				}
			}
		}

		return $args;
	}

	/**
	 * @param array                      $args
	 * @param \WC_Order                  $order
	 * @param \WC_Payment_Gateway_Stripe $payment_method
	 *
	 * @return array
	 */
	public function add_setup_intent_params( $args, $order, $payment_method ) {
		return $this->add_params_to_intent( $args, $order, 'setup_intent' );
	}

	public function is_link_active( $bool ) {
		if ( $bool ) {
			if ( \WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment ) {
				$bool = false;
			}
		}

		return $bool;
	}

}