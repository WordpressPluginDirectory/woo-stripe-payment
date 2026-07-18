<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

use \PaymentPlugins\Stripe\Controllers\PaymentIntentController;
use \PaymentPlugins\Stripe\Legacy\AbstractLegacyGateway;

require_once( WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-wc-stripe-payment-intent.php' );
require_once( WC_STRIPE_PLUGIN_FILE_PATH . 'includes/traits/wc-stripe-payment-traits.php' );

/**
 *
 * @since   3.0.0
 * @author  PaymentPlugins
 * @package PaymentPlugins\Abstract
 *
 */
abstract class WC_Payment_Gateway_Stripe extends AbstractLegacyGateway {

	use WC_Stripe_Settings_Trait;

	/**
	 * @var bool
	 * @since 3.1.8
	 */
	protected $has_digital_wallet = false;

	/**
	 * This property is used to determine if a customer is using a saved payment method or a new payment method.
	 * @var string
	 */
	public $payment_type_key;

	/**
	 *
	 * @var string
	 */
	public $payment_intent_key;

	/**
	 *
	 * @var string
	 */
	public $template_name;

	/**
	 *
	 * @var bool
	 */
	protected $checkout_error = false;

	/**
	 * Used to create an instance of a WC_Payment_Token
	 *
	 * @var string
	 */
	public $token_type;

	/**
	 *
	 * @var WP_Error
	 */
	protected $wp_error;

	/**
	 *
	 * @var string
	 */
	public $payment_method_token = null;

	/**
	 * @var \WC_Payment_Token_Stripe
	 */
	public $payment_token_object = null;

	/**
	 * Is the payment method synchronous or asynchronous
	 *
	 * @var bool
	 */
	public $synchronous = true;

	/**
	 *
	 * @var array
	 */
	protected $post_payment_processes = array();

	/**
	 *
	 * @var bool
	 */
	public $processing_payment = false;

	/**
	 * @var WP_Error
	 */
	public $last_payment_error;

	/**
	 * @var bool @since 3.3.16
	 */
	public $is_voucher_payment = false;

	protected $supports_save_payment_method = false;

	protected $new_payment_method_label = '';

	protected $saved_payment_methods_label = '';

	/**
	 * @var \PaymentPlugins\Vendor\Stripe\SetupIntent
	 */
	protected $setup_intent;

	/**
	 * @var \PaymentPlugins\Stripe\RequestContext
	 */
	protected $request_context;

	public function __construct( ...$args ) {
		// This property determines if WooCommerce calls the $gateway->payment_fields()
		$this->has_fields       = true;
		$this->payment_type_key = $this->id . '_payment_type_key';
		$this->hooks();
		parent::__construct( ...$args );
	}

	protected function hooks() {
		add_filter( 'wc_stripe_settings_nav_tabs', array( $this, 'admin_nav_tab' ) );
		add_action( 'woocommerce_stripe_settings_checkout_' . $this->id, array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );
	}

	public function init_form_fields() {
		$this->form_fields = include stripe_wc()->plugin_path() . 'includes/gateways/settings/' . str_replace( array(
				'stripe_',
				'_'
			), array(
				'',
				'-'
			), $this->id ) . '-settings.php';
		$this->form_fields = apply_filters( 'wc_stripe_form_fields_' . $this->id, $this->form_fields );
	}

	public function get_payment_method_formats() {
		$class_name = 'WC_Payment_Token_' . $this->token_type;
		$formats    = array();
		if ( class_exists( $class_name ) ) {
			/**
			 *
			 * @var WC_Payment_Token_Stripe
			 */
			$token = new $class_name();

			$formats = $token->get_formats();
		}

		return $formats;
	}

	public function enqueue_admin_scripts() {
	}

	public function payment_fields() {
		// @ todo - remove wc_stripe_token_field eventually as this is used to store the payment method Id.
		// It's replaced by "_payment_method_id"
		wc_stripe_token_field( $this );
		wc_stripe_hidden_field( $this->id . '_setup_intent_id' );
		wc_stripe_hidden_field( $this->id . '_payment_method_id' );

		if ( $this->supports( 'tokenization' ) && is_checkout() ) {
			$this->saved_payment_methods();
		}
		wc_stripe_get_template(
			'checkout/stripe-payment-method.php',
			array(
				'gateway' => $this,
				'tokens'  => ! empty( $this->tokens )
			)
		);
	}

	/**
	 * Output the product payment fields.
	 */
	public function product_fields() {
		global $product;
		wc_stripe_get_template(
			'product/' . $this->template_name,
			array(
				'gateway' => $this,
				'product' => $product,
			)
		);
	}

	public function cart_fields() {
		wc_stripe_get_template( 'cart/' . $this->template_name, array( 'gateway' => $this ) );
	}

	public function mini_cart_fields() {
		wc_stripe_get_template( 'mini-cart/' . $this->template_name, array( 'gateway' => $this ) );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Gateway::process_payment()
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		$order->set_payment_method( $this->id );

		do_action( 'wc_stripe_before_process_payment', $order, $this->id );

		if ( wc_notice_count( 'error' ) > 0 ) {
			return $this->get_order_error();
		}

		/**
		 * Filter that allows third party code to control the payment result.
		 *
		 * @param null
		 * @param WC_Order                                                 $order
		 * @param \PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway $payment_method
		 *
		 * @since 4.0.0
		 */
		$result = apply_filters( 'wc_stripe_process_payment_result', null, $order, $this );

		if ( is_wp_error( $result ) ) {
			wc_add_notice( $result->get_error_message(), 'error' );

			return [
				'result'   => WC_Stripe_Constants::FAILURE,
				'redirect' => ''
			];
		}

		if ( \is_array( $result ) ) {
			return $result;
		}

		$this->processing_payment = true;

		/**
		 * 3rd party code might have zero total orders that still need processing.
		 */
		if ( $order->get_total() <= 0 ) {
			return $this->process_zero_total_order( $order );
		}

		$result = $this->payment_controller->process_payment( $order );

		if ( is_wp_error( $result ) ) {
			wc_add_notice( $this->is_active( 'generic_error' ) ? $this->get_generic_error( $result ) : $result->get_error_message(), 'error' );

			return $this->get_order_error( $result );
		}

		if ( $result->complete_payment ) {
			WC()->cart->empty_cart();
			$this->payment_controller->payment_complete( $order, $result->charge );
			$this->trigger_post_payment_processes( $order, $this );

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		} else {
			return array(
				'result'   => 'success',
				'redirect' => $result->redirect,
			);
		}
	}

	/**
	 *
	 * @return array
	 * @deprecated 4.0.0
	 */
	public function get_localized_params() {
		wc_deprecated_function(
			'WC_Payment_Gateway_Stripe::get_localized_params',
			'4.0.0',
			'WC_Payment_Gateway_Stripe::get_payment_method_data'
		);

		return $this->get_payment_method_data();
	}

	public function get_payment_element_options() {
		return [
			'layout' => [
				'type' => 'tabs'
			]
		];
	}

	/**
	 * Save the Stripe data to the order.
	 *
	 * @param WC_Order                             $order
	 * @param \PaymentPlugins\Vendor\Stripe\Charge $charge
	 */
	public function save_order_meta( $order, $charge ) {
		/**
		 *
		 * @var WC_Payment_Token_Stripe $token
		 */
		$token = $this->get_payment_token( $this->get_payment_method_from_charge( $charge ), $charge->payment_method_details );
		$order->set_transaction_id( $charge->id );
		$order->set_payment_method_title( $token->get_payment_method_title() );
		$order->update_meta_data( WC_Stripe_Constants::MODE, wc_stripe_mode() );
		$order->update_meta_data( WC_Stripe_Constants::CHARGE_STATUS, $charge->status );
		$order->update_meta_data( WC_Stripe_Constants::PAYMENT_METHOD_TOKEN, $token->get_token() );

		/**
		 * @param WC_Order                             $order
		 * @param WC_Payment_Gateway_Stripe            $this
		 * @param \PaymentPlugins\Vendor\Stripe\Charge $charge
		 * @param \WC_Payment_Token                    $token
		 *
		 * @since 3.2.7
		 */
		do_action( 'wc_stripe_save_order_meta', $order, $this, $charge, $token );

		$order->save();
	}

	/**
	 * Given a charge object, return the ID of the payment method used for the charge.
	 *
	 * @param \PaymentPlugins\Vendor\Stripe\Charge $charge
	 *
	 * @since 3.0.6
	 */
	public function get_payment_method_from_charge( $charge ) {
		return $this->payment_controller->get_payment_method_from_charge( $charge );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Gateway::add_payment_method()
	 */
	public function add_payment_method() {
		$user_id = get_current_user_id();
		try {
			if ( ! is_user_logged_in() ) {
				throw new Exception( __( 'User must be logged in.', 'woo-stripe-payment' ) );
			}

			$customer_id = wc_stripe_get_customer_id( $user_id );

			if ( empty( $customer_id ) ) {
				$customer_id = $this->create_customer( $user_id );
			}

			if ( $this->is_mandate_required() ) {
				$setup_intent = $this->gateway->setupIntents->retrieve( WC()->session->get( WC_Stripe_Constants::SETUP_INTENT_ID ), array(
					'expand' => array( 'payment_method' )
				) );
				if ( is_wp_error( $setup_intent ) ) {
					throw new Exception( $setup_intent->get_error_message() );
				}
				$result = $this->get_payment_token( $setup_intent->payment_method->id, $setup_intent->payment_method );
				$result->set_customer_id( $customer_id );
				$result->update_meta_data( WC_Stripe_Constants::STRIPE_MANDATE, $setup_intent->mandate );
			} else {
				$result = $this->create_payment_method( $this->get_payment_method_from_request(), $customer_id );
			}

			if ( is_wp_error( $result ) ) {
				throw new Exception( $result->get_error_message() );
			}
			$result->set_user_id( $user_id );
			$result->save();
			WC_Payment_Tokens::set_users_default( $user_id, $result->get_id() );

			unset( WC()->session->{WC_Stripe_Constants::PAYMENT_INTENT}, WC()->session->{WC_Stripe_Constants::SETUP_INTENT_ID} );
			do_action( 'wc_stripe_add_payment_method_success', $result );

			return array(
				'result'   => 'success',
				'redirect' => wc_get_account_endpoint_url( 'payment-methods' ),
			);
		} catch ( Exception $e ) {
			wc_add_notice( sprintf( __( 'Error saving payment method. Reason: %s', 'woo-stripe-payment' ), $e->getMessage() ), 'error' );

			return array( 'result' => 'error' );
		}
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Gateway::process_refund()
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order  = wc_get_order( $order_id );
		$result = $this->payment_controller->process_refund( $order, $amount, $reason );

		if ( ! is_wp_error( $result ) ) {
			$order->add_order_note(
				sprintf(
					__( 'Order refunded in Stripe. Refund ID: %s. Amount: %s', 'woo-stripe-payment' ),
					$result->id,
					wc_price(
						$amount,
						array(
							'currency' => $order->get_currency(),
						)
					)
				)
			);
		}

		return $result;
	}

	/**
	 * Captures the charge for the Stripe order.
	 *
	 * @param float    $amount
	 * @param WC_Order $order
	 */
	public function capture_charge( $amount, $order ) {
		$charge = $this->gateway->mode( wc_stripe_order_mode( $order ) )->charges->retrieve( $order->get_transaction_id() );

		if ( is_wp_error( $charge ) ) {
			return $charge;
		} else {
			if ( ! $charge->captured ) {
				$this->processing_payment = true;

				$result = $this->payment_controller->capture_charge( $amount, $order, $charge );

				if ( ! is_wp_error( $result ) ) {
					WC_Stripe_Utils::add_balance_transaction_to_order( $result, $order, true );
					if ( isset( $result->refunds->data[0] ) ) {
						$balance_transaction = $this->gateway->balanceTransactions->retrieve( $result->refunds->data[0]->balance_transaction );
						if ( ! is_wp_error( $balance_transaction ) ) {
							WC_Stripe_Utils::update_balance_transaction( $balance_transaction, $order, true );
						}
					}
					$order->payment_complete();
					$order->add_order_note(
						sprintf(
							__( 'Order amount captured in Stripe. Amount: %s', 'woo-stripe-payment' ),
							wc_price( $amount, array( 'currency' => $order->get_currency() ) )
						)
					);
				} else {
					$order->add_order_note(
						sprintf(
							__( 'Error capturing charge in Stripe. Reason: %s', 'woo-stripe-payment' ),
							$result->get_error_message()
						)
					);

					/**
					 * @var WC_Order                             $order
					 * @var \PaymentPlugins\Vendor\Stripe\Charge $charge
					 * @Var \WC_Payment_Gateway_Stripe           $this
					 */
					$result = apply_filters( 'wc_stripe_capture_charge_failed', $result, $order, $amount, $this );
				}
				$this->processing_payment = false;

				return $result;
			}
		}
	}

	/**
	 * Void the Stripe charge.
	 *
	 * @param WC_Order $order
	 */
	public function void_charge( $order ) {
		// @3.1.1 - check added so errors aren't encountered if the order can't be voided
		if ( ! $this->payment_controller->can_void_order( $order ) ) {
			return;
		}
		$result = $this->payment_controller->void_charge( $order );

		if ( is_wp_error( $result ) ) {
			$order->add_order_note( sprintf( __( 'Error voiding charge. Reason: %s', 'woo-stripe-payment' ), $result->get_error_message() ) );
		} else {
			$order->add_order_note( __( 'Charge voided in Stripe.', 'woo-stripe-payment' ) );
		}

		return $result;
	}

	/**
	 * Return the \Stripe\Charge object
	 *
	 * @param String $charge_id
	 * @param String $mode
	 *
	 * @return WP_Error|\PaymentPlugins\Vendor\Stripe\Charge
	 */
	public function retrieve_charge( $charge_id, $mode = '' ) {
		return $this->gateway->charges->mode( $mode )->retrieve( $charge_id );
	}

	/**
	 *
	 * @param string                                   $method_id
	 * @param \PaymentPlugins\Vendor\Stripe\Card|array $method_details
	 */
	public function get_payment_token( $method_id, $method_details = null ) {
		$class_name = 'WC_Payment_Token_' . $this->token_type;
		if ( class_exists( $class_name ) ) {
			/**
			 *
			 * @var WC_Payment_Token_Stripe $token
			 */
			$token = new $class_name( '', $method_details );
			$token->set_token( $method_id );
			$token->set_gateway_id( $this->id );
			$token->set_format( $this->get_option( 'method_format' ) );
			$token->set_environment( wc_stripe_mode() );
			if ( $method_details ) {
				$token->details_to_props( $method_details );
			}

			return $token;
		}
	}

	/**
	 * Return a failed order response.
	 *
	 * @param WP_Error $error
	 *
	 * @return array
	 */
	public function get_order_error( $error = null ) {
		wc_stripe_set_checkout_error();
		$this->last_payment_error = $error;
		do_action( 'wc_stripe_process_payment_error', $error, $this );

		return array( 'result' => WC_Stripe_Constants::FAILURE, 'redirect' => '' );
	}

	/**
	 * Returns the payment method the customer wants to use.
	 * This can be a saved payment method or a new payment method.
	 */
	public function get_payment_method_from_request() {
		// check if customer is using a saved payment method.
		if ( $this->should_use_saved_payment_method() ) {
			$id    = \wc_clean( \wp_unslash( $_POST["wc-{$this->id}-payment-token"] ) );
			$token = \WC_Payment_Tokens::get( (int) $id );

			return $token ? $token->get_token() : '';
		}
		if ( $this->payment_method_token ) {
			return $this->payment_method_token;
		}
		if ( $this->payment_method_id ) {
			return $this->payment_method_id;
		}
		$key = $this->id . '_payment_method_id';
		if ( ! empty( $_POST[ $key ] ) ) {
			return \wc_clean( \wp_unslash( $_POST[ $key ] ) );
		}

		return '';
	}

	public function get_payment_intent_id() {
		return ! empty( $_POST[ $this->payment_intent_key ] ) ? wc_clean( $_POST[ $this->payment_intent_key ] ) : '';
	}

	/**
	 * Create a customer in the stripe gateway.
	 *
	 * @param int $user_id
	 *
	 * @throws Exception
	 */
	public function create_customer( $user_id ) {
		$customer = WC()->customer;
		$response = WC_Stripe_Customer_Manager::instance()->create_customer( $customer );
		if ( ! is_wp_error( $response ) ) {
			wc_stripe_save_customer( $response->id, $user_id );
		} else {
			throw new Exception( $response->get_error_message() );
		}

		return $response->id;
	}

	/**
	 * Creates a payment method in Stripe.
	 *
	 * @param string $id
	 *          payment method id
	 * @param string $customer_id
	 *          WC Stripe customer ID
	 *
	 * @return WC_Payment_Token_Stripe|WP_Error
	 */
	public function create_payment_method( $id, $customer_id ) {
		if ( $this->setup_intent && isset( $this->setup_intent->latest_attempt->payment_method_details ) ) {
			$token = $this->get_payment_token( $id, $this->setup_intent->latest_attempt->payment_method_details );
		} else {
			$token = $this->get_payment_token( $id );
		}
		$token->set_customer_id( $customer_id );

		$result = $token->save_payment_method();

		if ( is_wp_error( $result ) ) {
			return $result;
		} else {
			if ( $result ) {
				$token->set_token( $result->id );
				$token->details_to_props( $result );
			}

			return $token;
		}
	}

	public function get_new_method_label() {
		return apply_filters( 'wc_stripe_get_new_method_label', $this->new_payment_method_label, $this );
	}

	public function get_saved_methods_label() {
		return apply_filters( 'wc_stripe_get_saved_methods_label', $this->saved_payment_methods_label, $this );
	}

	/**
	 * @return string
	 * @since 3.3.42
	 */
	public function get_save_payment_method_label() {
		return __( 'Save payment method', 'woo-stripe-payment' );
	}

	/**
	 * Return true if shipping is needed.
	 * Shipping is based on things like if the cart or product needs shipping.
	 *
	 * @return bool
	 */
	public function get_needs_shipping() {
		if ( is_checkout() || is_cart() ) {
			global $wp;
			if ( wcs_stripe_active() && WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment ) {
				return false;
			}
			// return false if this is the order pay page. Gateways that have payment sheets don't need
			// to make any changes to the order.
			if ( ! empty( $wp->query_vars['order-pay'] ) ) {
				return false;
			}

			return WC()->cart->needs_shipping();
		}
		if ( is_product() ) {
			global $product;

			return is_a( $product, 'WC_Product' ) && $product->needs_shipping();
		}
	}

	/**
	 * Return true if the payment method should be saved.
	 *
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	public function should_save_payment_method( $order ) {
		// If the customer has checked the save to account checkbox and they aren't using a saved payment method.
		$bool = ! empty( $_POST["wc-{$this->id}-new-payment-method"] ) && ! $this->should_use_saved_payment_method();


		/**
		 * @param bool                      $bool
		 * @param WC_Order                  $order
		 * @param WC_Payment_Gateway_Stripe $payment_method
		 *
		 * @since 3.3.12
		 */
		return apply_filters( 'wc_stripe_should_save_payment_method', $bool, $order, $this );
	}

	/**
	 * Returns true if the save payment method checkbox can be displayed.
	 *
	 * @return boolean
	 */
	public function show_save_source() {
		return false;
	}

	/**
	 * Returns a formatted array of items for display in the payment gateway's payment sheet.
	 *
	 * @param stirng $page
	 *
	 * @return []
	 */
	public function get_display_items( $page = 'checkout', $order = null ) {
		global $wp;
		$items = array();
		if ( in_array( $page, array( 'cart', 'checkout' ) ) ) {
			$items = $this->get_display_items_for_cart( WC()->cart );
		} elseif ( 'order_pay' === $page ) {
			$order = ! is_null( $order ) ? $order : wc_get_order( absint( $wp->query_vars['order-pay'] ) );
			$items = $this->get_display_items_for_order( $order );
		} elseif ( 'product' === $page ) {
			global $product;
			$items = array( $this->get_display_item_for_product( $product ) );
		}

		/**
		 * @param array         $items
		 * @param WC_Order|null $order
		 * @param string        $page
		 */
		return apply_filters( 'wc_stripe_get_display_items', $items, $order, $page );
	}

	/**
	 * Returns a formatted array of shipping methods for display in the payment gateway's
	 * payment sheet.
	 *
	 * @param bool $encode
	 *
	 * @return array
	 * @deprecated
	 */
	public function get_shipping_methods() {
		return $this->get_formatted_shipping_methods();
	}

	/**
	 * Decorate the response with data specific to the gateway.
	 *
	 * @param [] $data
	 */
	public function add_to_cart_response( $data ) {
		return $data;
	}

	/**
	 * Decorate the update shipping method reponse with data.
	 *
	 * @param [] $data
	 */
	public function get_update_shipping_method_response( $data ) {
		return $data;
	}

	/**
	 * Decorate the update shipping address respond with data.
	 *
	 * @param [] $data
	 */
	public function get_update_shipping_address_response( $data ) {
		return $data;
	}

	/**
	 * Save the customer's payment method.
	 * If the payment method has already been saved to the customer
	 * then simply return true.
	 *
	 * @param string   $id
	 * @param WC_Order $order
	 * @param
	 *
	 * @return WP_Error|bool
	 */
	public function save_payment_method( $id, $order, $payment_details = null ) {
		$mode        = wc_stripe_order_mode( $order );
		$user_id     = $order->get_customer_id();
		$customer_id = wc_stripe_get_customer_id( $user_id, $mode );
		if ( ! $customer_id ) {
			$customer_id = $order->get_meta( WC_Stripe_Constants::CUSTOMER_ID );
			if ( ! $customer_id ) {
				$response = WC_Stripe_Customer_Manager::instance()->create_customer( new WC_Customer( $user_id, ! $user_id ), $mode );
				if ( ! is_wp_error( $response ) ) {
					$payment_details = null;
					$customer_id     = $response->id;
					if ( $user_id ) {
						wc_stripe_save_customer( $customer_id, $user_id, $mode );
					} else {
						$order->update_meta_data( WC_Stripe_Constants::CUSTOMER_ID, $customer_id );
					}
				}
			}
		}
		if ( $payment_details ) {
			$token = $this->get_payment_token( $id, $payment_details );
			$token->set_customer_id( $customer_id );
		} else {
			$token = $this->create_payment_method( $id, $customer_id );
			if ( is_wp_error( $token ) ) {
				$this->wp_error = $token;
				$order->add_order_note( sprintf( __( 'Attempt to save payment method failed. Reason: %s', 'woo-stripe-payment' ), $token->get_error_message() ) );

				return $token;
			}
		}
		$token->set_user_id( $user_id );
		if ( $user_id && ! in_array( strtolower( $token->get_brand() ), array( 'link', 'klarna' ) ) ) {
			$token->save();
		}

		// set token value so it can be used for other processes.
		$this->payment_token_object = $token;
		$this->payment_method_token = $token->get_token();

		return true;
	}

	/**
	 * Set an error on the order.
	 * This error is used on the frontend to alert customer's to a failed payment method save.
	 *
	 * @param WC_Order $order
	 * @param WP_Error $error
	 *
	 * @deprecated
	 */
	public function set_payment_save_error( $order, $error ) {
		if ( wcs_stripe_active() && wcs_order_contains_subscription( $order ) ) {
			$message = __( 'We were not able to save your payment method. To prevent billing issues with your subscription, please add a payment method to the subscription.', 'woo-stripe-payment' );
		} else {
			$message = sprintf( __( 'We were not able to save your payment method. Reason: %s', 'woo-stripe-payment' ), $error->get_error_message() );
		}
		$order->update_meta_data( '_wc_stripe_order_error', $message );
		$order->save();
	}

	/**
	 *
	 * @param string $token_id
	 * @param int    $user_id
	 *
	 * @return null|WC_Payment_Token_Stripe
	 */
	public function get_token( $token_id, $user_id ) {
		return \PaymentPlugins\Stripe\Utilities\PaymentMethodUtils::get_payment_token(
			$token_id,
			$user_id
		);
	}

	/**
	 * Return true if this request is to change the payment method of a WC Subscription.
	 *
	 * @return bool
	 */
	public function is_change_payment_method_request() {
		return wcs_stripe_active() && did_action( 'woocommerce_subscriptions_pre_update_payment_method' );
	}

	/**
	 * Sets the ID of a payment token.
	 *
	 * @param string $id
	 */
	public function set_payment_method_token( $id ) {
		$this->payment_method_token = $id;
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @deprecated
	 *
	 */
	public function get_order_description( $order ) {
		return sprintf( __( 'Order %1$s from %2$s', 'woo-stripe-payment' ), $order->get_order_number(), get_bloginfo( 'name' ) );
	}

	/**
	 *
	 * @param WC_Order $order
	 */
	public function save_zero_total_meta( $order, $token = null ) {
		if ( $this->payment_token_object ) {
			$token = $this->payment_token_object;
		} elseif ( ! $token ) {
			$token = $this->payment_method_token ? $this->get_token( $this->payment_method_token, $order->get_user_id() ) : null;
		}
		$order->set_payment_method_title( $token ? $token->get_payment_method_title( $this->get_option( 'method_format' ) ) : $this->get_title() );
		$order->update_meta_data( WC_Stripe_Constants::MODE, wc_stripe_mode() );
		if ( $token ) {
			$order->update_meta_data( WC_Stripe_Constants::PAYMENT_METHOD_TOKEN, $token->get_token() );
		}
		if ( $order->get_customer_id() ) {
			$order->update_meta_data( WC_Stripe_Constants::CUSTOMER_ID, wc_stripe_get_customer_id( $order->get_user_id() ) );
		}

		/**
		 * @param WC_Order                  $order
		 * @param WC_Payment_Gateway_Stripe $this
		 * @param null                      $charge
		 *
		 * @since 3.2.7
		 */
		do_action( 'wc_stripe_save_order_meta', $order, $this, null, $token );

		$order->save();
	}

	/**
	 * Sets a lock on the order.
	 * Default behavior is a 2 minute lock.
	 *
	 * @param WC_Order|int $order
	 */
	public function set_order_lock( $order ) {
		$order_id = ( is_object( $order ) ? $order->get_id() : $order );
		set_transient( 'stripe_lock_order_' . $order_id, $order_id, apply_filters( 'wc_stripe_set_order_lock', 2 * MINUTE_IN_SECONDS ) );
	}

	/**
	 * Removes the lock on the order
	 *
	 * @param WC_Order|int $order
	 */
	public function release_order_lock( $order ) {
		delete_transient( 'stripe_lock_order_' . ( is_object( $order ) ? $order->get_id() : $order ) );
	}

	/**
	 * Returns true of the order has been locked.
	 * If the lock exists and is greater than current time
	 * method returns true;
	 *
	 * @param WC_Order|int $order
	 */
	public function has_order_lock( $order ) {
		$lock = get_transient( 'stripe_lock_order_' . ( is_object( $order ) ? $order->get_id() : $order ) );

		return $lock !== false;
	}

	public function set_post_payment_process( $callback ) {
		$this->post_payment_processes[] = $callback;
	}

	/**
	 *
	 * @param WC_Order                  $order
	 * @param WC_Payment_Gateway_Stripe $gateway
	 */
	public function trigger_post_payment_processes( $order, $gateway ) {
		foreach ( $this->post_payment_processes as $callback ) {
			call_user_func_array( $callback, func_get_args() );
		}
	}

	public function validate_payment_sections_field( $key, $value ) {
		if ( empty( $value ) ) {
			$value = array();
		}

		return $value;
	}

	/**
	 * Given a meta key, see if there is a value for that key in another plugin.
	 * This acts as a lazy conversion
	 * method for merchants that have switched to our plugin from other plugins.
	 *
	 * @param string   $meta_key
	 * @param WC_Order $order
	 * @param string   $context
	 *
	 * @since 3.1.0
	 */
	public function get_order_meta_data( $meta_key, $order, $context = 'view' ) {
		$value = $order->get_meta( $meta_key, true, $context );
		// value is empty so check metadata from other plugins
		if ( empty( $value ) ) {
			$keys = array();
			switch ( $meta_key ) {
				case WC_Stripe_Constants::PAYMENT_METHOD_TOKEN:
					$keys = array( WC_Stripe_Constants::SOURCE_ID, '_fkwcs_source_id' );
					break;
				case WC_Stripe_Constants::CUSTOMER_ID:
					$keys = array( WC_Stripe_Constants::STRIPE_CUSTOMER_ID, '_fkwcs_customer_id' );
					break;
				case WC_Stripe_Constants::PAYMENT_INTENT_ID:
					$keys = array( WC_Stripe_Constants::STRIPE_INTENT_ID );
			}
			if ( $keys ) {
				$meta_data = $order->get_meta_data();
				if ( $meta_data ) {
					$keys       = array_intersect( wp_list_pluck( $meta_data, 'key' ), $keys );
					$array_keys = array_keys( $keys );
					if ( ! empty( $array_keys ) ) {
						$value = $meta_data[ current( $array_keys ) ]->value;
						$order->update_meta_data( $meta_key, $value );
						$order->save();
					}
				}
			}
		}

		return $value;
	}

	/**
	 * Gateways can override this method to add attributes to the Stripe object before it's
	 * sent to Stripe.
	 *
	 * @param array    $args
	 * @param WC_Order $order
	 */
	public function add_stripe_order_args( &$args, $order, $intent = null ) {
	}

	/**
	 *
	 * @param WP_Error $result
	 *
	 * @since 3.1.1
	 */
	public function get_generic_error( $result = null ) {
		$messages = wc_stripe_get_error_messages();
		if ( isset( $messages["{$this->id}_generic"] ) ) {
			return $messages["{$this->id}_generic"];
		}

		return null != $result ? $result->get_error_message() : __( 'Cannot process payment', 'woo-stripe-payment' );
	}

	/**
	 *
	 * @since 3.1.2
	 */
	private function get_payment_section_description() {
		return sprintf( __( 'Increase your conversion rate by offering %1$s on your Product and Cart pages, or at the top of the checkout page. <br/><strong>Note:</strong> you can control which products display %s by going to the product edit page.',
			'woo-stripe-payment' ),
			$this->get_method_title() );
	}

	/**
	 * Outputs fields required by Google Pay to render the payment wallet.
	 *
	 * @param string $page
	 * @param array  $data
	 *
	 * @deprecated 4.0.0
	 */
	public function output_display_items( $page = 'checkout', $data = array() ) {
		/**
		 * @param array                     $data
		 * @param string                    $page
		 * @param WC_Payment_Gateway_Stripe $this
		 *
		 * @since 3.1.8
		 */
		$data = wp_json_encode( apply_filters( 'wc_stripe_output_display_items', $data, $page, $this ) );
		$data = function_exists( 'wc_esc_json' ) ? wc_esc_json( $data ) : _wp_specialchars( $data, ENT_QUOTES, 'UTF-8', true );
		printf( '<input type="hidden" class="%1$s" data-gateway="%2$s"/>', "woocommerce_{$this->id}_gateway_data {$page}-page", $data );
	}

	/**
	 * @return array
	 * @since 3.2.0
	 * @deprecated 4.0.0
	 */
	public function get_shipping_packages() {
		$packages = WC()->shipping()->get_packages();
		if ( empty( $packages ) && wcs_stripe_active() && WC_Subscriptions_Cart::cart_contains_free_trial() ) {
			// there is a subscription with a free trial in the cart. Shipping packages will be in the recurring cart.
			WC_Subscriptions_Cart::set_calculation_type( 'recurring_total' );
			$count = 0;
			if ( isset( WC()->cart->recurring_carts ) ) {
				foreach ( WC()->cart->recurring_carts as $recurring_cart_key => $recurring_cart ) {
					foreach ( $recurring_cart->get_shipping_packages() as $i => $base_package ) {
						if ( version_compare( WC_Subscriptions::$version, '5.1.2', '<' ) ) {
							$packages[ $recurring_cart_key . '_' . $count ] = WC_Subscriptions_Cart::get_calculated_shipping_for_package( $base_package );
						} else {
							$packages[ $recurring_cart_key . '_' . $count ] = WC()->shipping()->calculate_shipping_for_package( $base_package );
						}
					}
					$count ++;
				}
			}
			WC_Subscriptions_Cart::set_calculation_type( 'none' );
		}

		return $packages;
	}

	/**
	 * @param WC_Cart $cart
	 * @param array   $items
	 *
	 * @return array
	 * @since 3.2.1
	 */
	public function get_display_items_for_cart( $cart, $items = array() ) {
		$incl_tax = wc_stripe_display_prices_including_tax();
		foreach ( $cart->get_cart() as $cart_item ) {
			/**
			 *
			 * @var WC_Product $product
			 */
			$product = $cart_item['data'];
			$qty     = $cart_item['quantity'];
			$label   = $qty > 1 ? sprintf( '%s X %s', $product->get_name(), $qty ) : $product->get_name();
			$price   = $incl_tax ? wc_get_price_including_tax( $product, array( 'qty' => $qty ) ) : wc_get_price_excluding_tax( $product, array( 'qty' => $qty ) );
			$items[] = $this->get_display_item_for_cart( $price, $label, 'product', $cart_item, $cart );
		}
		if ( $cart->needs_shipping() ) {
			$price   = $incl_tax ? $cart->shipping_total + $cart->shipping_tax_total : $cart->shipping_total;
			$items[] = $this->get_display_item_for_cart( $price, __( 'Shipping', 'woo-stripe-payment' ), 'shipping' );
		}
		foreach ( $cart->get_fees() as $fee ) {
			$price   = $incl_tax ? $fee->total + $fee->tax : $fee->total;
			$items[] = $this->get_display_item_for_cart( $price, $fee->name, 'fee', $fee, $cart );
		}
		if ( 0 < $cart->discount_cart ) {
			$price   = - 1 * abs( $incl_tax ? $cart->discount_cart + $cart->discount_cart_tax : $cart->discount_cart );
			$items[] = $this->get_display_item_for_cart( $price, __( 'Discount', 'woo-stripe-payment' ), 'discount', $cart );
		}
		if ( ! $incl_tax && wc_tax_enabled() ) {
			$items[] = $this->get_display_item_for_cart( $cart->get_taxes_total(), __( 'Tax', 'woo-stripe-payment' ), 'tax', $cart );
		}

		return $items;
	}

	/**
	 * @param WC_Order $order
	 * @param array    $items
	 *
	 * @return array
	 * @since 3.2.1
	 */
	protected function get_display_items_for_order( $order, $items = array() ) {
		foreach ( $order->get_items() as $item ) {
			$qty     = $item->get_quantity();
			$label   = $qty > 1 ? sprintf( '%s X %s', $item->get_name(), $qty ) : $item->get_name();
			$items[] = $this->get_display_item_for_order( $item->get_subtotal(), $label, $order, 'item', $item );
		}
		if ( 0 < $order->get_shipping_total() ) {
			$items[] = $this->get_display_item_for_order( $order->get_shipping_total(), __( 'Shipping', 'woo-stripe-payment' ), $order, 'shipping' );
		}
		if ( 0 < $order->get_total_discount() ) {
			$items[] = $this->get_display_item_for_order( - 1 * $order->get_total_discount(), __( 'Discount', 'woo-stripe-payment' ), $order, 'discount' );
		}
		if ( 0 < $order->get_fees() ) {
			$fee_total = 0;
			foreach ( $order->get_fees() as $fee ) {
				$fee_total += $fee->get_total();
			}
			$items[] = $this->get_display_item_for_order( $fee_total, __( 'Fees', 'woo-stripe-payment' ), $order, 'fee' );
		}
		if ( 0 < $order->get_total_tax() ) {
			$items[] = $this->get_display_item_for_order( $order->get_total_tax(), __( 'Tax', 'woocommerce' ), $order, 'tax' );
		}

		return $items;
	}

	/**
	 * @param float  $price
	 * @param string $label
	 * @param string $type
	 * @param mixed  ...$args
	 *
	 * @return array
	 * @since 3.2.1
	 */
	protected function get_display_item_for_cart( $price, $label, $type, ...$args ) {
		return array(
			'label'   => $label,
			'pending' => false,
			'amount'  => wc_stripe_add_number_precision( $price )
		);
	}

	/**
	 * @param float    $price
	 * @param string   $label
	 * @param WC_Order $order
	 * @param string   $type
	 * @param mixed    ...$args
	 */
	protected function get_display_item_for_order( $price, $label, $order, $type, ...$args ) {
		return array(
			'label'   => $label,
			'pending' => false,
			'amount'  => wc_stripe_add_number_precision( $price, $order->get_currency() )
		);
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return array
	 * @since 3.2.1
	 *
	 */
	protected function get_display_item_for_product( $product ) {
		return array(
			'label'   => esc_attr( $product->get_name() ),
			'pending' => true,
			'amount'  => wc_stripe_add_number_precision( $product->get_price() )
		);
	}

	/**
	 * @param array $methods
	 * @param       $sort
	 *
	 * @return array
	 * @since 3.2.1
	 */
	public function get_formatted_shipping_methods( $methods = array() ) {
		if ( wcs_stripe_active() && WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment ) {
			return $methods;
		} else {
			$methods        = array();
			$chosen_methods = array();
			$packages       = $this->get_shipping_packages();
			$incl_tax       = wc_stripe_display_prices_including_tax();
			if ( WC()->session ) {
				foreach ( WC()->session->get( 'chosen_shipping_methods', array() ) as $i => $id ) {
					$chosen_methods[] = $this->get_shipping_method_id( $id, $i );
				}
			}
			foreach ( $packages as $i => $package ) {
				foreach ( $package['rates'] as $rate ) {
					/**
					 * @var \WC_Shipping_Rate $rate
					 */
					$cost      = (float) $rate->get_cost();
					$price     = $incl_tax ? $cost + (float) $rate->get_shipping_tax() : $cost;
					$methods[] = $this->get_formatted_shipping_method( $price, $rate, $i, $package, $incl_tax );
				}
			}

			/**
			 * Combined sorting function that:
			 * 1. Prioritizes shipping methods with IDs in $chosen_methods
			 * 2. Then sorts all other methods by amount in ascending order
			 */
			usort( $methods, function ( $method1, $method2 ) use ( $chosen_methods ) {
				// Check if method IDs are in chosen_methods
				$method1_chosen = ! empty( $chosen_methods ) && in_array( $method1['id'], $chosen_methods, true );
				$method2_chosen = ! empty( $chosen_methods ) && in_array( $method2['id'], $chosen_methods, true );

				// If only one is chosen, prioritize it
				if ( $method1_chosen && ! $method2_chosen ) {
					return - 1;
				} elseif ( ! $method1_chosen && $method2_chosen ) {
					return 1;
				}

				if ( isset( $method1['amount'], $method2['amount'] ) ) {
					// Otherwise sort by amount
					return $method1['amount'] <=> $method2['amount'];
				}

				return 1;
			} );
		}

		/**
		 * @param array $methods
		 *
		 * @since 3.3.0
		 */
		return apply_filters( 'wc_stripe_get_formatted_shipping_methods', $methods, $this );
	}

	/**
	 * @param float            $price
	 * @param WC_Shipping_Rate $rate
	 * @param string           $i
	 * @param array            $package
	 * @param bool             $incl_tax
	 *
	 * @return array
	 * @since 3.2.1
	 */
	public function get_formatted_shipping_method( $price, $rate, $i, $package, $incl_tax ) {
		$method = array(
			'id'     => $this->get_shipping_method_id( $rate->id, $i ),
			'label'  => $this->get_formatted_shipping_label( $price, $rate, $incl_tax ),
			'detail' => '',
			'amount' => wc_stripe_add_number_precision( $price )
		);
		if ( $incl_tax ) {
			if ( $rate->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
				$method['detail'] = WC()->countries->inc_tax_or_vat();
			}
		} else {
			if ( $rate->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
				$method['detail'] = WC()->countries->ex_tax_or_vat();
			}
		}

		return $method;
	}

	/**
	 * @param string $id
	 * @param string $index
	 *
	 * @return mixed
	 */
	protected function get_shipping_method_id( $id, $index ) {
		return sprintf( '%s:%s', $index, $id );
	}

	/**
	 * @param float            $price
	 * @param WC_Shipping_Rate $rate
	 * @param bool             $incl_tax
	 *
	 * @since 3.2.1
	 */
	protected function get_formatted_shipping_label( $price, $rate, $incl_tax ) {
		return sprintf( '%s', esc_attr( $rate->get_label() ) );
	}

	/**
	 * Returns true if a scheduled subscription payment is being processed.
	 *
	 * @return bool
	 * @since 3.2.3
	 */
	protected function is_processing_scheduled_payment() {
		return doing_action( 'woocommerce_scheduled_subscription_payment_' . $this->id );
	}

	/**
	 * WC_Payment_Gateway::get_order_total() reads WC()->cart->total directly with no null check on
	 * WC()->cart, which isn't initialized on admin/non-frontend requests. Guard it here rather than
	 * in parent::get_order_total() itself, since that's WooCommerce core.
	 *
	 * @return float
	 * @since 4.0.7
	 */
	protected function get_order_total() {
		$total = ( WC()->cart || absint( get_query_var( 'order-pay' ) ) ) ? parent::get_order_total() : 0;

		return apply_filters( 'wc_stripe_payment_gateway_order_total', $total, $this );
	}

	/**
	 * @param WC_Stripe_Frontend_Scripts $scripts
	 *
	 * @return bool
	 * @since 3.2.5
	 */
	public function has_enqueued_scripts( $scripts ) {
		return false;
	}

	public function get_transaction_url( $order ) {
		if ( wc_stripe_order_mode( $order ) === 'test' ) {
			$this->view_transaction_url = 'https://dashboard.stripe.com/test/payments/%s';
		} else {
			$this->view_transaction_url = 'https://dashboard.stripe.com/payments/%s';
		}

		return parent::get_transaction_url( $order );
	}

	/**
	 * @param array $options
	 *
	 * @return mixed|void
	 * @since 3.3.10
	 */
	public function get_element_options( $options = array() ) {
		$options = array_merge(
			array( 'locale' => wc_stripe_get_site_locale() ),
			wc_stripe_get_container()->get( PaymentIntentController::class )->get_element_options(),
			$options
		);
		if ( $this->get_payment_method_type() ) {
			$options = array_merge(
				array( 'paymentMethodTypes' => array( $this->get_payment_method_type() ) ),
				$options
			);
		}


		return apply_filters( 'wc_stripe_get_element_options', $options, $this );
	}

	public function is_installment_available() {
		return false;
	}

	/**
	 * @return bool|mixed|void|null
	 * @since 3.3.42
	 */
	public function show_save_payment_method_html() {
		$show = $this->supports_save_payment_method
		        && ! is_add_payment_method_page()
		        && $this->is_active( 'save_card_enabled' )
		        && ( ! is_checkout_pay_page() || is_user_logged_in() );

		return apply_filters( 'wc_stripe_show_save_payment_method', $show, $this );
	}

	public function is_mandate_required( $order = null ) {
		$mode = ! $order ? wc_stripe_mode() : wc_stripe_order_mode( $order );

		return stripe_wc()->account_settings->get_account_country( $mode ) === 'IN';
	}

	/**
	 * @return string
	 * @since 3.3.51
	 */
	public function get_payment_token_type() {
		return $this->token_type;
	}

	public function get_complete_payment_return_url( $order = null ) {
		if ( $this->request_context ) {
			return \PaymentPlugins\Stripe\Utilities\PaymentMethodUtils::create_return_url(
				$this,
				$this->request_context->get_context()
			);
		} else {
			global $wp;
			if ( isset( $wp->query_vars['order-pay'] ) ) {
				$url = $order->get_checkout_payment_url();
			} else {
				$url = wc_get_checkout_url();
			}

			return add_query_arg(
				array(
					'key'                    => $order->get_order_key(),
					'order_id'               => $order->get_id(),
					'_stripe_payment_method' => $this->id,
				),
				$url
			);
		}
	}

	public function set_setup_intent( $value ) {
		$this->setup_intent = $value;
	}

	public function get_setup_intent() {
		return $this->setup_intent;
	}

	public function get_payment_method_charge_type() {
		return $this->get_option( 'charge_type', 'capture' ) === 'capture' ? WC_Stripe_Constants::AUTOMATIC : WC_Stripe_Constants::MANUAL;
	}

	public function get_order_status_option() {
		return $this->get_option( 'order_status', 'default' );
	}

	/**
	 * @return \PaymentPlugins\Stripe\RequestContext
	 */
	public function get_request_context() {
		return $this->request_context;
	}

	public function set_request_context( $value ) {
		$this->request_context = $value;
	}

}
