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

		if ( function_exists( 'wcs_is_manual_renewal_enabled' ) && wcs_is_manual_renewal_enabled() ) {
			add_filter( 'wc_stripe_payment_gateway_order_total', [ $this, 'maybe_use_subscription_total' ], 10, 2 );
			add_filter( 'wc_stripe_cart_data', [ $this, 'add_recurring_total_to_cart_data' ] );
		}
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
	 * cart_contains_subscription() is checked in addition to is_checkout_with_subscription() since
	 * this also needs to be correct for gateway components that can render on any page regardless
	 * of checkout/cart/order-pay context, e.g. the Mini Cart block's express payment buttons.
	 *
	 * @param $bool
	 *
	 * @return bool|mixed
	 * @since 3.3.60
	 */
	public function is_subscription_mode( $bool, RequestContext $context ) {
		if ( ! $bool ) {
			if ( $this->request->is_order_pay_with_subscription() ) {
				$bool = true;
			} elseif ( $this->request->is_checkout_with_subscription() ) {
				$bool = true;
			} elseif ( $this->request->cart_contains_subscription() ) {
				$bool = true;
			} elseif ( $this->request->is_product_page_with_subscription() ) {
				$bool = true;
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

	/**
	 * Gateways that don't support 'subscriptions' still become valid choices once manual renewals
	 * are enabled - the renewal is just a manual "pay this invoice" flow instead of an automatic
	 * off-session charge. But WC_Payment_Gateway::get_order_total() (and any availability check
	 * built on it, e.g. WC_Payment_Gateway_Stripe_Local_Payment::is_local_payment_available()) uses
	 * the live cart/order total, which is 0 during a free trial - failing gateways' own min/max
	 * amount checks and hiding them even though nothing is actually due today. Substitute the
	 * subscription's real recurring total so those checks evaluate against a meaningful amount.
	 *
	 * @param float                      $total
	 * @param \WC_Payment_Gateway_Stripe $gateway
	 *
	 * @return float
	 * @since 4.0.7
	 */
	public function maybe_use_subscription_total( $total, $gateway ) {
		if ( $total > 0 ) {
			return $total;
		}

		global $wp;
		if ( isset( $wp->query_vars['order-pay'] ) ) {
			$order = wc_get_order( absint( $wp->query_vars['order-pay'] ) );
			if ( ! $order ) {
				return $total;
			}
			$subscriptions = wcs_get_subscriptions_for_order( $order );
			if ( ! $subscriptions ) {
				return $total;
			}

			return max( array_map( function ( $subscription ) {
				return (float) $subscription->get_total();
			}, $subscriptions ) );
		}

		$recurring_total = $this->get_recurring_cart_total();

		return $recurring_total > 0 ? $recurring_total : $total;
	}

	/**
	 * Exposes the recurring cart total to the client so it can be used as a stand-in amount for
	 * gateways that don't support 'subscriptions' when the live cart total is 0 (free trial) - the
	 * cart's line items alone don't account for this correctly client-side, since shipping/tax on
	 * the live cart reflect today's (trial-zeroed) totals while the recurring cart's totals reflect
	 * what will actually be charged going forward.
	 *
	 * @param array $data
	 *
	 * @return array
	 * @since 4.0.7
	 */
	public function add_recurring_total_to_cart_data( $data ) {
		$data['recurringTotalCents'] = wc_stripe_add_number_precision( $this->get_recurring_cart_total(), $data['currency'] );

		return $data;
	}

	/**
	 * The Store API's Checkout::update_session_from_request()/update_order_from_request() validate
	 * the selected payment method (via get_available_payment_gateways() -> is_available()) before
	 * recalculating cart totals - so recurring_carts isn't populated yet the first time this runs
	 * in that request. Trigger the calculation ourselves rather than guessing at which specific
	 * request/route needs it; this only actually recalculates once per request, since recurring_carts
	 * stays populated for every gateway checked afterward.
	 *
	 * @return float
	 * @since 4.0.7
	 */
	private function get_recurring_cart_total() {
		if ( ! WC()->cart ) {
			return 0;
		}
		if ( ( empty( WC()->cart->recurring_carts ) || ! is_array( WC()->cart->recurring_carts ) )
		     && \WC_Subscriptions_Cart::cart_contains_subscription() ) {
			WC()->cart->calculate_totals();
		}
		if ( empty( WC()->cart->recurring_carts ) || ! is_array( WC()->cart->recurring_carts ) ) {
			return 0;
		}

		return max( array_map( function ( $recurring_cart ) {
			return (float) $recurring_cart->total;
		}, WC()->cart->recurring_carts ) );
	}

}