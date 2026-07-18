<?php

namespace PaymentPlugins\Stripe\Installments;

use PaymentPlugins\Stripe\Client\StripeClient;
use PaymentPlugins\Stripe\Installments\Filters\CurrencyFilter;
use PaymentPlugins\Stripe\Installments\Filters\OrderTotalFilter;
use PaymentPlugins\Stripe\Installments\Filters\PreOrdersFilter;
use PaymentPlugins\Stripe\Installments\Filters\SubscriptionFilter;
use PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;
use PaymentPlugins\Vendor\Stripe\PaymentIntent;

class InstallmentController {
	/**
	 * @var \StripeClient
	 */
	private $client;
	/**
	 * @var \WC_Stripe_Advanced_Settings
	 */
	private $advanced_settings;
	/**
	 * @var \WC_Stripe_Account_Settings
	 */
	private $account_settings;
	/**
	 * @var \PaymentPlugins\Stripe\Installments\InstallmentFormatter
	 */
	private $formatter;

	/**
	 * @param \StripeClient                $client
	 * @param \WC_Stripe_Advanced_Settings $advanced_settings
	 * @param \WC_Stripe_Account_Settings  $account_settings
	 */
	public function __construct( $client, $advanced_settings, $account_settings ) {
		$this->client            = $client;
		$this->advanced_settings = $advanced_settings;
		$this->account_settings  = $account_settings;
		$this->formatter         = new InstallmentFormatter();
	}

	public function initialize() {
		if ( $this->is_active() ) {
			add_action( 'wc_stripe_save_order_meta', [ $this, 'add_order_meta' ], 10, 3 );
			add_filter( 'woocommerce_get_order_item_totals', [ $this, 'add_order_item_total' ], 10, 2 );
			add_filter( 'wc_stripe_can_update_payment_intent', [ $this, 'can_update_payment_intent' ], 10, 2 );
			add_filter( 'wc_stripe_payment_intent_confirmation_args', [ $this, 'add_confirmation_args' ], 10, 2 );
			add_filter( 'wc_stripe_payment_gateway_data', [ $this, 'add_installment_data' ], 10, 3 );
			add_action( 'woocommerce_checkout_update_order_review', [ $this, 'on_update_order_review' ] );
		}
		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_checkout_stripe_advanced', function () {
				//$this->process_advanced_settings_options();
			}, 50 );
		}
	}

	private function is_active() {
		$country = $this->account_settings->get_account_country( wc_stripe_mode() );

		return wc_string_to_bool( $this->advanced_settings->get_option( 'installments' ) ) && \in_array( $country, [
				'MX',
				'BR'
			] );
	}

	public function is_available( $order = null ) {
		if ( $this->is_active() ) {
			if ( $order !== null ) {
				if ( is_int( $order ) ) {
					$order = wc_get_order( $order );
				}
				$filters = $this->order_filters_factory( $order );
			} else {
				$filters = $this->cart_filters_factory();
			}
			$is_available = true;
			foreach ( $filters as $filter ) {
				if ( ! $filter->is_available() ) {
					$is_available = false;
					break;
				}
			}

			return apply_filters( 'wc_stripe_installments_is_available', $is_available );
		}

		return false;
	}

	/**
	 * @return \PaymentPlugins\Stripe\Installments\Filters\CurrencyFilter[]
	 */
	private function cart_filters_factory() {
		$currency = get_woocommerce_currency();

		return [
			new CurrencyFilter( $currency, $this->account_settings->get_account_country( wc_stripe_mode() ) ),
			new OrderTotalFilter( WC()->cart ? WC()->cart->get_total( 'float' ) : 0, $currency ),
			new SubscriptionFilter( WC()->cart, null ),
			new PreOrdersFilter( WC()->cart, null )
		];
	}

	private function order_filters_factory( \WC_Order $order ) {
		$currency = $order->get_currency();

		return [
			new CurrencyFilter( $currency, $this->account_settings->get_account_country( wc_stripe_order_mode( $order ) ) ),
			new OrderTotalFilter( $order->get_total(), $currency ),
			new SubscriptionFilter( null, $order ),
			new PreOrdersFilter( null, $order )
		];
	}

	/**
	 * @param \WC_Order                            $order
	 * @param \WC_Payment_Gateway_Stripe           $payment_method
	 * @param \PaymentPlugins\Vendor\Stripe\Charge $charge
	 */
	public function add_order_meta( $order, $payment_method, $charge ) {
		if ( ! empty( $charge->payment_method_details->card->installments->plan ) ) {
			$plan = $charge->payment_method_details->card->installments->plan;
			$order->update_meta_data( \WC_Stripe_Constants::INSTALLMENT_PLAN, $this->formatter->format_plan_id( $plan ) );
		}
	}

	/**
	 * @param [] $rows
	 * @param \WC_Order $order
	 */
	public function add_order_item_total( $rows, $order ) {
		$plan = $order->get_meta( \WC_Stripe_Constants::INSTALLMENT_PLAN );
		if ( $plan ) {
			$amount                                         = wc_stripe_add_number_precision( $order->get_total(), $order->get_currency() );
			$rows[ \WC_Stripe_Constants::INSTALLMENT_PLAN ] = [
				'label' => __( 'Installments:', 'woo-stripe-payment' ),
				'value' => $this->formatter->format_plan( $this->formatter->parse_plan_from_id( $plan, true ), $amount, $order->get_currency() )
			];
		}

		return $rows;
	}

	/**
	 * @param [] $intent
	 * @param \WC_Order $order
	 */
	public function can_update_payment_intent( $can_update, $intent ) {
		if ( ! $can_update && ! empty( $intent['payment_method_options']['card']['installments']['enabled'] ) ) {
			if ( $intent->status !== 'succeeded' ) {
				$can_update = true;
			}
		}

		return $can_update;
	}

	/**
	 * @param                                             $args
	 * @param \PaymentPlugins\Vendor\Stripe\PaymentIntent $intent
	 */
	public function add_confirmation_args( $args, $intent ) {
		if ( ! empty( $intent->payment_method_options->card->installments->available_plans ) ) {
			$plan_id = isset( $_POST[ \WC_Stripe_Constants::INSTALLMENT_PLAN ] ) ? wc_clean( $_POST[ \WC_Stripe_Constants::INSTALLMENT_PLAN ] ) : null;
			if ( $this->formatter->is_valid_plan( $plan_id ) ) {
				$args['payment_method_options'] = [ 'card' => [ 'installments' => [ 'plan' => $this->formatter->parse_plan_from_id( $plan_id ) ] ] ];
			}
		}

		return $args;
	}

	/**
	 * Decorates the credit card gateway's script data with a payment intent client secret
	 * when installments are available. This allows the Stripe Payment Element to render
	 * installment options natively.
	 *
	 * @param array                                                  $data
	 * @param \PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry $registry
	 * @param \PaymentPlugins\Stripe\ContextHandler                  $context
	 *
	 * @return array
	 * @since 4.0.0
	 */
	public function add_installment_data( $data, $registry, $context ) {
		if ( ! $context->is_checkout() && ! $context->is_order_pay() ) {
			return $data;
		}
		if ( ! isset( $data['stripe_cc_data'] ) ) {
			return $data;
		}
		/**
		 * @var \WC_Payment_Gateway_Stripe_CC $card_gateway
		 */
		$card_gateway = $registry->get( 'stripe_cc' );
		// Installments are only supported by the Payment Element
		if ( $card_gateway && ! $card_gateway->is_payment_element_active() ) {
			return $data;
		}
		$order = $context->is_order_pay() ? $context->get_order_from_query() : null;
		if ( ! $this->is_available( $order ) ) {
			return $data;
		}
		$intent = $this->get_or_create_installment_intent( $order );
		if ( $intent && ! is_wp_error( $intent ) ) {
			$data['stripe_cc_data']['installments'] = [ 'clientSecret' => $intent->client_secret ];
		}

		return $data;
	}

	/**
	 * Schedules a payment intent update after cart totals are calculated.
	 * Triggered by the update_order_review AJAX call (billing country change, coupon, etc.).
	 * Adds a one-time callback to woocommerce_after_calculate_totals since cart totals
	 * aren't ready when woocommerce_checkout_update_order_review fires.
	 *
	 * @param string $posted_data
	 *
	 * @since 4.0.0
	 */
	public function on_update_order_review( $posted_data ) {
		add_action( 'woocommerce_after_calculate_totals', [ $this, 'maybe_update_installment_intent' ] );
	}

	/**
	 * Updates the installment payment intent if the cart total or currency has changed.
	 * Called after cart totals are calculated during the update_order_review AJAX flow.
	 *
	 * @since 4.0.0
	 */
	public function maybe_update_installment_intent() {
		remove_action( 'woocommerce_after_calculate_totals', [ $this, 'maybe_update_installment_intent' ] );
		$existing = \WC_Stripe_Utils::get_payment_intent_from_session();
		if ( ! $existing ) {
			return;
		}
		$params = $this->get_installment_intent_params();
		if ( $this->intent_needs_update( $existing, $params ) ) {
			$intent = $this->client->paymentIntents->update( $existing->id, [
				'amount'   => $params['amount'],
				'currency' => $params['currency']
			] );
			if ( ! is_wp_error( $intent ) ) {
				\WC_Stripe_Utils::save_payment_intent_to_session( $intent );
			}
		}
	}

	/**
	 * Retrieves an existing payment intent from the session or creates a new one
	 * with installments enabled.
	 *
	 * @param \WC_Order|null $order
	 *
	 * @return \PaymentPlugins\Vendor\Stripe\PaymentIntent|\WP_Error|null
	 * @since 4.0.0
	 */
	public function get_or_create_installment_intent( $order = null ) {
		$params = $this->get_installment_intent_params( $order );
		if ( $order ) {
			$existing = $order->get_meta( \WC_Stripe_Constants::PAYMENT_INTENT );
			if ( $existing ) {
				$existing = (object) $existing;
			}
		} else {
			$existing = \WC_Stripe_Utils::get_payment_intent_from_session();
		}
		if ( $existing ) {
			$intent = $this->client->paymentIntents->retrieve( $existing->id );
			if ( ! is_wp_error( $intent ) && ! in_array( $intent->status, [
					'succeeded',
					'requires_capture',
					'canceled'
				], true ) ) {
				if ( $this->intent_needs_update( $intent, $params ) ) {
					$intent = $this->client->paymentIntents->update( $intent->id, [
						'amount'   => $params['amount'],
						'currency' => $params['currency']
					] );
					if ( ! is_wp_error( $intent ) ) {
						\WC_Stripe_Utils::save_payment_intent_to_session( $intent, $order );
					}
				}

				return $intent;
			}
		}
		$intent = $this->client->paymentIntents->create( $params );
		if ( ! is_wp_error( $intent ) ) {
			\WC_Stripe_Utils::save_payment_intent_to_session( $intent, $order );
		}

		return $intent;
	}

	/**
	 * Determines if an existing payment intent needs to be updated by comparing
	 * a hash of the current parameters against the intent's values.
	 *
	 * @param \PaymentPlugins\Vendor\Stripe\PaymentIntent|\stdClass $intent
	 * @param array                                                 $params
	 *
	 * @return bool
	 * @since 4.0.0
	 */
	private function intent_needs_update( $intent, $params ) {
		$intent_hash = md5( $intent->amount . ':' . strtoupper( $intent->currency ?? '' ) );
		$params_hash = md5( $params['amount'] . ':' . $params['currency'] );

		return $intent_hash !== $params_hash;
	}

	/**
	 * Builds the parameters for creating a payment intent with installments enabled.
	 *
	 * @param \WC_Order|null $order
	 *
	 * @return array
	 * @since 4.0.0
	 */
	private function get_installment_intent_params( $order = null ) {
		$params = [
			'payment_method_types'   => [ 'card' ],
			'payment_method_options' => [ 'card' => [ 'installments' => [ 'enabled' => true ] ] ]
		];
		/**
		 * @var AbstractGateway $card_gateway
		 */
		$card_gateway = wc_stripe_get_container()->get( PaymentGatewayRegistry::class )->get( 'stripe_cc' );
		if ( $card_gateway && $card_gateway->get_option( 'force_3d_secure', 'no' ) === 'yes' ) {
			$params['payment_method_options']['card']['request_three_d_secure'] = 'any';
		}
		if ( $order ) {
			$params['amount']   = wc_stripe_add_number_precision( $order->get_total(), $order->get_currency() );
			$params['currency'] = $order->get_currency();
			if ( $order->get_customer_id() ) {
				$customer_id = wc_stripe_get_customer_id( $order->get_customer_id() );
				if ( $customer_id ) {
					$params['customer'] = $customer_id;
				}
			}
		} else {
			$currency           = get_woocommerce_currency();
			$total              = WC()->cart ? WC()->cart->get_total( 'float' ) : 0;
			$params['amount']   = wc_stripe_add_number_precision( $total, $currency );
			$params['currency'] = $currency;
			if ( is_user_logged_in() ) {
				$customer_id = wc_stripe_get_customer_id( get_current_user_id() );
				if ( $customer_id ) {
					$params['customer'] = $customer_id;
				}
			}
		}

		return $params;
	}

	public static function get_supported_countries() {
		$filter = new CurrencyFilter( 0, null );

		return $filter->get_supported_countries();
	}

	public static function get_supported_currencies() {
		$filter = new CurrencyFilter( 0, null );

		return $filter->get_supported_currencies();
	}

	private function process_advanced_settings_options() {
		if ( ! $this->advanced_settings->is_active( 'installments' ) ) {
			return;
		}
		/**
		 * Loop through the modes and enable installments
		 */
		/**
		 * @var \StripeClient $client
		 */
		$client      = wc_stripe_get_container()->get( StripeClient::class );
		$application = wc_stripe_get_container()->get( 'CLIENT_ID' );
		foreach ( [ 'live', 'test' ] as $mode ) {
			$client->mode( $mode );
			if ( ! $client->is_connected() ) {
				continue;
			}
			// fetch the payment method configurations
			$result = $client->paymentMethodConfigurations->all( [ 'limit' => 50 ] );
			if ( ! is_wp_error( $result ) && ! empty( $result->data ) ) {
				$pmc = null;
				foreach ( $result->data as $item ) {
					if ( $item->application == $application ) {
						$pmc = $item;
						break;
					} elseif ( $mode === 'test' && $item->is_default ) {
						$pmc = $item;
						break;
					}
				}
			}
		}
	}
}