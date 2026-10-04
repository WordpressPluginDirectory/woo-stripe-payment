<?php

namespace PaymentPlugins\Stripe\AdaptivePricing;

use PaymentPlugins\Stripe\Assets\AssetDataApi;
use PaymentPlugins\Stripe\Client\StripeClient;
use PaymentPlugins\Stripe\ContextHandler;

/**
 * Coordinates Stripe CheckoutSessions for Adaptive Pricing (AP) enabled checkouts.
 *
 * The session is created once, server-side, on checkout/order-pay page load (see initialize())
 * and its client_secret/session_id are embedded directly into the page's localized script data.
 * Its id is also persisted to the WC session so it can be found again on later requests. As the
 * cart total changes (coupon, shipping method, etc.), the session's line items are kept in sync
 * as a side effect of WooCommerce's own order-review recalculation request, rather than through a
 * dedicated request of our own (classic/shortcode checkout only for now).
 *
 * Checkout and order-pay are kept as two parallel flows (create_session_from_cart() /
 * create_session_from_order() and their own reuse/params/line-items methods) rather than one
 * context-aware implementation - they don't just differ in where the amount comes from, they
 * differ in how a cached session gets validated for reuse (a cart session can be freely claimed
 * by any fresh attempt; an order-pay session is only ever valid for the one order it was created
 * for) and in whether the session needs to bind to an order at all at creation time (order-pay's
 * order already exists; the cart flow's order doesn't exist until checkout is submitted).
 *
 * @since 4.0.15
 */
class CheckoutSessionController {
	/**
	 * WC session key the CheckoutSession id is persisted under.
	 */
	const SESSION_KEY = 'wc_stripe_checkout_session_id';
	/**
	 * WC session key the signature of the last cart sync actually applied to the session is
	 * cached under - lets update_session_from_cart() skip the Stripe API call entirely when
	 * nothing Stripe-relevant (currency, total, setup_future_usage) has changed since then,
	 * without needing to retrieve the session first to find out.
	 */
	const SYNC_SIGNATURE_SESSION_KEY = 'wc_stripe_checkout_session_signature';
	/**
	 * @var \StripeClient
	 */
	private $client;
	/**
	 * @var ContextHandler
	 */
	private $context;
	/**
	 * Guards against updating the session more than once per request, in case something
	 * (core or a 3rd party) triggers more than one calculate_totals() call.
	 *
	 * @var bool
	 */
	private $synced_this_request = false;
	/**
	 * The checkout block's "save payment method" checkbox state, pushed via
	 * update_save_payment_method() (this payment method's Store API cart/extensions update
	 * callback - see AbstractStripePayment::get_update_callback()). Blocks has no post_data
	 * equivalent for it. Only needs to survive for the rest of this request:
	 * CartExtensionsSchema::get_item_response() calls the update callback, then calculate_totals(),
	 * which is what triggers get_payment_intent_data_for_update() via maybe_sync_session_with_cart().
	 *
	 * @var bool|null
	 */
	private $save_payment_method_override = null;
	/**
	 * In-memory (not WC()->session) cache of the last CheckoutSession object this request
	 * actually retrieved or wrote to Stripe. Nothing else could have changed the session's status
	 * between two touches of it within the same PHP request, so a second call to
	 * reuse_session_from_cart() for the same session id (e.g. Blocks calling
	 * get_checkout_session_for_cart() more than once per request) can reuse it instead of
	 * retrieving again.
	 *
	 * @var \PaymentPlugins\Vendor\Stripe\Checkout\Session|null
	 */
	private $session_cache = null;

	public function __construct( StripeClient $client, ContextHandler $context ) {
		$this->client  = $client;
		$this->context = $context;
	}

	/**
	 * Phase 2 (standalone-gateway AP) is dormant - UPMCheckoutSessionController::initialize()
	 * overrides this rather than calling it, and wires its own hooks. This instance is still used
	 * directly though (e.g. the checkout-session REST route, via update_session_from_cart()), so
	 * initialize() being unreachable can't gate anything this class otherwise depends on.
	 */
	public function initialize() {
	}

	/**
	 * @return bool
	 */
	public function is_enabled() {
		$config = stripe_wc()->advanced_settings->get_option( 'adaptive_pricing_config', [] );

		return is_array( $config ) && ( $config['enabled'] ?? 'no' ) === 'yes';
	}

	/**
	 * @param array  $args
	 * @param string $property
	 * @param string $method
	 *
	 * @return array
	 * @since 4.0.15
	 */
	public function add_checkout_server_update_beta_header( $args, $property, $method ) {
		if ( $property !== 'sessions' || $method !== 'update' || ! $this->is_enabled() ) {
			return $args;
		}

		$idx     = count( $args ) - 1;
		$version = wc_stripe_get_container()->get( 'API_VERSION' );

		foreach ( wc_stripe_get_container()->get( 'REQUEST_HEADERS' ) as $beta => $beta_version ) {
			$version .= ";{$beta}={$beta_version}";
		}

		$args[ $idx ]['stripe_version'] = $version;

		return $args;
	}

	/**
	 * Links the CheckoutSession to the WC order and short-circuits the PaymentIntent flow.
	 * Hooked on wc_stripe_process_payment_result; the client then runs actions.confirm(). Order
	 * completion is driven by the redirect handler / checkout.session webhook, not here.
	 *
	 * @param array|\WP_Error|null       $result
	 * @param \WC_Order                  $order
	 * @param \WC_Payment_Gateway_Stripe $payment_method
	 *
	 * @return array|\WP_Error|null
	 */
	public function process_payment_result( $result, $order, $payment_method ) {
		if ( ! is_null( $result ) || ! $this->is_available( $order ) ) {
			return $result;
		}
		// A method picked inside the UPM element routes through one of UPM's child gateways, so
		// accept stripe_upm itself or a gateway acting as its child.
		if ( $payment_method->id !== 'stripe_upm' && empty( $payment_method->has_parent_gateway ) ) {
			return $result;
		}
		// Blocks' saved-payment-method UI (SavedCardComponent) has no CheckoutSession/checkout.confirm()
		// access - it's rendered outside the CheckoutProvider tree. Let a saved token fall through to
		// the ordinary (non-AP) payment flow there instead, which it already knows how to handle.
		// doing_action() ties this to the actual Store API payment dispatch, not just "any REST
		// request" - classic checkout's AJAX submission isn't a WP REST request at all.
		if ( doing_action( 'woocommerce_rest_checkout_process_payment_with_context' ) && $payment_method->should_use_saved_payment_method() ) {
			return $result;
		}
		// A session linked on a prior attempt is the authority on whether payment already
		// completed - WC()->session can have moved on to a fresh session (e.g. a checkout-page
		// reload) while the order stays linked to the earlier one. If that session actually
		// paid (confirm() succeeded but the webhook hasn't landed yet), finish the order now.
		$linked_session_id = $order->get_meta( \WC_Stripe_Constants::CHECKOUT_SESSION_ID );
		if ( $linked_session_id ) {
			$linked_session = $this->client->checkout->sessions->retrieve( $linked_session_id );
			if ( ! is_wp_error( $linked_session ) ) {
				$this->client->mode( $linked_session );
				if ( $linked_session->payment_status === 'paid' ) {
					return $this->complete_order_from_session( $order, $payment_method, $linked_session );
				}
			}
		}
		if ( ( defined( \WC_Stripe_Constants::PROCESSING_ORDER_PAY ) || $this->context->is_order_pay() ) && isset( $linked_session ) && ! is_wp_error( $linked_session ) ) {
			// Order-pay never uses WC()->session - create_session_from_order() is the only writer
			// of this meta, and always keeps it pointing at a currently-valid ('open') session.
			// PROCESSING_ORDER_PAY covers our own REST route; is_order_pay() is a safety net for a
			// native order-pay form submission reaching here directly (e.g. if the client-side fix
			// that forces order-pay through the REST route for every payment method doesn't hold).
			$session_id = $linked_session_id;
			$session    = $linked_session;
		} else {
			$session_id = WC()->session ? WC()->session->get( self::SESSION_KEY ) : null;
			if ( ! $session_id ) {
				// No CheckoutSession to confirm against - the customer's session likely
				// expired/reset before submitting. Reload rather than falling through to the
				// non-AP payment flow, which has nothing valid to process for a UPM/AP checkout.
				return [ 'result' => \WC_Stripe_Constants::FAILURE, 'reload' => true ];
			}
			$session = $this->client->checkout->sessions->retrieve( $session_id );
			if ( is_wp_error( $session ) ) {
				/**
				 * @var \WP_Error
				 */
				return $session;
			}
			$this->client->mode( $session );
		}
		if ( $session->payment_status === 'paid' ) {
			// Idempotency guard: a duplicated request (e.g. a proxy/webhook replay) can arrive with
			// a different, freshly-created order for the same already-paid session. Only complete
			// $order from it when the session's own record agrees this order is the one that paid.
			if ( isset( $session->metadata['order_id'] ) && (int) $session->metadata['order_id'] === $order->get_id() ) {
				$order->update_meta_data( \WC_Stripe_Constants::CHECKOUT_SESSION_ID, $session_id );
				$order->save();

				return $this->complete_order_from_session( $order, $payment_method, $session );
			}

			return $result;
		}
		if ( $session->payment_status !== 'unpaid' ) {
			return new \WP_Error( 'checkout_session_processed', __( 'This checkout session has already been processed.', 'woo-stripe-payment' ) );
		}
		$updated = $this->client->checkout->sessions->update( $session_id, [
			'metadata'            => [ 'order_id' => $order->get_id() ],
			'payment_intent_data' => $this->get_order_payment_intent_data( $order, $payment_method )
		] );
		if ( is_wp_error( $updated ) ) {
			/**
			 * @var \WP_Error
			 */
			return $updated;
		}
		$order->update_meta_data( \WC_Stripe_Constants::CHECKOUT_SESSION_ID, $session_id );
		$order->update_meta_data( \WC_Stripe_Constants::MODE, wc_stripe_mode() );
		$order->set_payment_method( $payment_method->id );
		$order->save();

		return [
			'result'   => 'success',
			'redirect' => $this->get_confirmation_redirect( $session, $order, $payment_method )
		];
	}

	/**
	 * Clears the CheckoutSession id from WC()->session once its order has paid, so the next
	 * checkout attempt (e.g. buying again) doesn't reuse/retrieve a session that's already done.
	 * Only clears it when the cached id still matches this order's own - WC()->session may have
	 * already moved on to a newer session for a later attempt by the time this fires.
	 *
	 * @param \PaymentPlugins\Vendor\Stripe\Charge $charge
	 * @param \WC_Order                            $order
	 *
	 * @return void
	 */
	public function maybe_clear_completed_session( $charge, $order ) {
		if ( ! WC()->session ) {
			return;
		}
		$linked_session_id = $order->get_meta( \WC_Stripe_Constants::CHECKOUT_SESSION_ID );
		if ( $linked_session_id && WC()->session->get( self::SESSION_KEY ) === $linked_session_id ) {
			unset( WC()->session->{self::SESSION_KEY} );
		}
	}

	/**
	 * Skips a gateway's own checkout script handles when its shared checkout-session element is
	 * handling it instead (see PaymentGatewayRegistry::get_checkout_script_handles()).
	 *
	 * @param bool                                                     $enqueue
	 * @param \PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway $integration
	 *
	 * @return bool
	 */
	public function maybe_skip_checkout_script_handles( $enqueue, $integration ) {
		if ( $this->should_create_session() && $integration->is_adaptive_pricing_compatible() ) {
			return false;
		}

		return $enqueue;
	}

	/**
	 * Enqueues the client-side Adaptive Pricing script on the checkout page, only when a
	 * CheckoutSession is actually going to be created for this request.
	 *
	 * @return void
	 */
	public function maybe_enqueue_scripts() {
		if ( ! $this->should_create_session() ) {
			return;
		}
		wp_enqueue_script( 'wc-stripe-checkout-session-init' );
	}

	/**
	 * Whether Adaptive Pricing should be active for the current request - enabled in settings, on
	 * a context that supports it (checkout or order-pay), and with an actual upfront amount due.
	 * Public so gateway classes that can't/shouldn't inject this controller directly (e.g.
	 * WC_Payment_Gateway_Stripe_UPM::get_checkout_script_handles(), which needs the same answer to
	 * decide script handles) can still call it via wc_stripe_get_container().
	 *
	 * @param \WC_Order|null $order Pass the order when one already exists and is known to be the
	 *                              order this request is acting on (e.g. process_payment_result(),
	 *                              called mid checkout-submission). WC()->cart can no longer be
	 *                              trusted to reflect it at that point in the request, and for
	 *                              order-pay it saves re-resolving the order from the query.
	 *                              Callers that only have page-load context (script enqueueing,
	 *                              session creation before an order exists) should omit it.
	 *
	 * @return bool
	 */
	public function is_available( $order = null ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}
		$available = false;
		if ( $this->context->is_checkout() ) {
			if ( $order instanceof \WC_Order ) {
				$available = (float) $order->get_total() > 0;
			} else {
				// WC_Cart::needs_payment() isn't the right check here - WC Subscriptions overrides
				// it to return true for a $0 free-trial cart (a payment method still needs
				// collecting), but there's no upfront amount for Adaptive Pricing's currency
				// conversion to display.
				$available = (bool) WC()->cart && WC()->cart->get_total( 'float' ) > 0;
			}
		} elseif ( $this->context->is_order_pay() ) {
			$order     = $order instanceof \WC_Order ? $order : $this->context->get_order_from_query();
			$available = $order instanceof \WC_Order && (float) $order->get_total() > 0;
		}

		/**
		 * Filters whether Adaptive Pricing is available for the current request. Lets 3rd party
		 * code (e.g. the Pre-Orders package) veto availability for scenarios Adaptive Pricing's
		 * checkout-session flow doesn't support - a charge-upon-release pre-order, for example,
		 * needs a payment method tokenized with no upfront charge, regardless of the cart/order
		 * total otherwise being non-zero.
		 *
		 * $order is only ever populated here for order-pay (resolved above if not already passed
		 * in) - the cart flow has no order yet at most call sites, and only what process_payment_result()
		 * itself passed in otherwise.
		 *
		 * @param bool                      $available
		 * @param CheckoutSessionController $controller
		 * @param \WC_Order|null            $order
		 *
		 * @since 4.0.15
		 */
		return apply_filters( 'wc_stripe_adaptive_pricing_is_available', $available, $this, $order );
	}

	/**
	 * Cart-only availability check for callers that already know they're in a cart/checkout
	 * context by construction - e.g. Blocks' Store API cart-schema extension data callback,
	 * which only ever runs to build this payment method's own data. ContextHandler::is_checkout()
	 * is page-query-based (is_page()/queried post content) and never resolves true during a Store
	 * API request, so is_available() can't be used as the gate here the way the hook-driven
	 * classic-checkout flows use it.
	 *
	 * @param \WC_Cart|null $cart Pass the cart when the caller already has it (e.g.
	 *                            maybe_sync_session_with_cart(), handed one directly by
	 *                            woocommerce_after_calculate_totals). Falls back to WC()->cart for
	 *                            callers that don't.
	 *
	 * @return bool
	 */
	public function is_available_for_cart( $cart = null ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}
		$cart      = $cart ?? WC()->cart;
		$available = (bool) $cart && $cart->get_total( 'float' ) > 0;

		return apply_filters( 'wc_stripe_adaptive_pricing_is_available', $available, $this, null );
	}

	/**
	 * Protected (not private) so UPMCheckoutSessionController's own overrides (e.g.
	 * maybe_skip_checkout_script_handles()) can call it.
	 *
	 * @return bool
	 */
	protected function should_create_session() {
		return $this->is_available();
	}

	/**
	 * Creates the CheckoutSession on checkout/order-pay page load and adds it to the page's
	 * localized script data.
	 *
	 * @param AssetDataApi $asset_data
	 *
	 * @return void
	 */
	public function add_checkout_session_data( $asset_data ) {
		if ( ! $this->should_create_session() ) {
			return;
		}
		if ( $this->context->is_order_pay() ) {
			$order = $this->context->get_order_from_query();
			// get_order_from_query() doesn't check this itself, and WC core rejecting the page
			// for an invalid key doesn't stop this hook (wp_print_footer_scripts) from still
			// running - without this, the order's billing details and a usable client_secret
			// would be exposed to anyone who can guess/enumerate an order id.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! $order instanceof \WC_Order || ! $order->key_is_valid( wc_clean( wp_unslash( $_GET['key'] ?? '' ) ) ) ) {
				return;
			}
		}
		$session = $this->create_session();
		if ( is_wp_error( $session ) ) {
			wc_stripe_log_error( sprintf( 'Error creating Adaptive Pricing checkout session: %s', $session->get_error_message() ) );

			return;
		}
		$asset_data->add( 'checkoutSession', $this->format_session_response( $session ) );
	}

	/**
	 * Public entry point for consumers outside the wc_stripe_add_script_data hook (e.g. the
	 * Blocks UniversalPayment integration, which supplies its own static payload). Cart-only -
	 * Blocks has no order-pay context, so this skips create_session()'s order-pay branching.
	 *
	 * @return array|\WP_Error
	 */
	public function get_checkout_session_for_cart() {
		$session = $this->create_session_from_cart();
		if ( is_wp_error( $session ) ) {
			wc_stripe_log_error( sprintf( 'Error creating Adaptive Pricing checkout session: %s', $session->get_error_message() ) );

			return $session;
		}

		return $this->format_session_response( $session );
	}

	/**
	 * @return array|\WP_Error
	 */
	private function create_session() {
		if ( $this->context->is_order_pay() ) {
			$order = $this->context->get_order_from_query();
			if ( $order instanceof \WC_Order ) {
				return $this->create_session_from_order( $order );
			}
		}

		return $this->create_session_from_cart();
	}

	/**
	 * Create a CheckoutSession for the current cart and persist its id to the WC session so later
	 * requests (e.g. the order-review sync below) can find it again. Reuses the session already in
	 * WC()->session when it's still open, rather than creating a new one on every checkout page
	 * load - repeated refreshes would otherwise leave a trail of abandoned sessions.
	 *
	 * @return \PaymentPlugins\Vendor\Stripe\Checkout\Session|\WP_Error
	 */
	private function create_session_from_cart() {
		$existing_session_id = WC()->session ? WC()->session->get( self::SESSION_KEY ) : null;
		if ( $existing_session_id ) {
			$reused = $this->reuse_session_from_cart( $existing_session_id );
			if ( $reused ) {
				return $reused;
			}
		}
		$session = $this->client->checkout->sessions->create( $this->get_session_params_from_cart() );
		if ( ! is_wp_error( $session ) ) {
			$this->session_cache = $session;
			/**
			 * Persist the checkout session ID in the WooCommerce session
			 */
			if ( WC()->session ) {
				WC()->session->set( self::SESSION_KEY, $session->id );
			}
		}

		return $session;
	}

	/**
	 * Create a CheckoutSession for the order being paid on the order-pay page, bound to that order
	 * from the start - unlike the cart flow, the order already exists, so there's no deferred
	 * binding step in process_payment_result() needed to know which order this session is for.
	 * Reuses the session already in WC()->session only when it's still open and already bound to
	 * this exact order; a session cached for a different order (or an in-progress cart checkout)
	 * isn't usable here.
	 *
	 * @param \WC_Order $order
	 *
	 * @return \PaymentPlugins\Vendor\Stripe\Checkout\Session|\WP_Error
	 */
	private function create_session_from_order( $order ) {
		$existing_session_id = $order->get_meta( \WC_Stripe_Constants::CHECKOUT_SESSION_ID );
		if ( $existing_session_id ) {
			$reused = $this->reuse_session_from_order( $existing_session_id, $order );
			if ( $reused ) {
				return $reused;
			}
		}
		$session = $this->client->checkout->sessions->create( $this->get_session_params_from_order( $order ) );
		if ( ! is_wp_error( $session ) ) {
			$order->update_meta_data( \WC_Stripe_Constants::CHECKOUT_SESSION_ID, $session->id );
			$order->save();
		}

		return $session;
	}

	/**
	 * status ('open'/'complete'/'expired') is what answers whether the session is still fresh and
	 * untouched - 'open' means payment processing hasn't started, which already implies
	 * payment_status is 'unpaid'. payment_status alone isn't the right gate here: a session can be
	 * status 'complete' with payment_status still 'unpaid' for an async payment method, and that
	 * session must not be reused for a new page load.
	 *
	 * @param string $session_id
	 *
	 * @return @return null|\PaymentPlugins\Vendor\Stripe\Checkout\Session
	 *
	 */
	private function reuse_session_from_cart( $session_id ) {
		if ( $this->session_cache && $this->session_cache->id === $session_id ) {
			$session = $this->session_cache;
		} else {
			$session = $this->client->checkout->sessions->retrieve( $session_id );
			if ( is_wp_error( $session ) || $session->status !== 'open' ) {
				return null;
			}
			$this->session_cache = $session;
		}
		$response = $this->update_session_from_cart( $session_id, $this->session_order_id_is_stale_for_cart( $session ) );
		if ( is_wp_error( $response ) ) {
			return null;
		}
		// update_session_from_cart() returns null when it skipped the update call (nothing
		// Stripe-relevant changed since the last sync) - the session already retrieved above is
		// still accurate in that case.
		return $response ?? $session;
	}

	/**
	 * True when the session's order_id binding no longer matches WC's own record of which order
	 * this browser session is actively paying for - i.e. that order was abandoned/superseded and
	 * it's safe to release the session for a new attempt to claim.
	 *
	 * @param \PaymentPlugins\Vendor\Stripe\Checkout\Session $session
	 *
	 * @return bool
	 */
	private function session_order_id_is_stale_for_cart( $session ) {
		if ( empty( $session->metadata['order_id'] ) ) {
			return false;
		}
		$awaiting = WC()->session ? WC()->session->get( 'order_awaiting_payment' ) : null;

		return (int) $session->metadata['order_id'] !== (int) $awaiting;
	}

	/**
	 * Unlike the cart flow, a cached session only makes sense here if it's already bound to this
	 * exact order - it's created bound to the order from the start (get_session_params_from_order()),
	 * so there's no "claim an unbound session" case. Line items are refreshed on every reuse so an
	 * order total that changed since the session was created (e.g. an admin edited the order) is
	 * picked up rather than declining reuse outright.
	 *
	 * @param string    $session_id
	 * @param \WC_Order $order
	 *
	 * @return null|\PaymentPlugins\Vendor\Stripe\Checkout\Session
	 */
	private function reuse_session_from_order( $session_id, $order ) {
		$session = $this->client->checkout->sessions->retrieve( $session_id );
		if ( is_wp_error( $session ) || $session->status !== 'open' ) {
			return null;
		}
		if ( empty( $session->metadata['order_id'] ) || (int) $session->metadata['order_id'] !== $order->get_id() ) {
			return null;
		}
		$session = $this->update_session_from_order( $session_id, $order );

		return is_wp_error( $session ) ? null : $session;
	}

	/**
	 * Update an existing CheckoutSession's line items to reflect the order's current total.
	 *
	 * @param string    $session_id
	 * @param \WC_Order $order
	 *
	 * @return \WP_Error|\PaymentPlugins\Vendor\Stripe\Checkout\Session
	 */
	private function update_session_from_order( $session_id, $order ) {
		return $this->client->checkout->sessions->update( $session_id, [ 'line_items' => $this->get_line_items_from_order( $order ) ] );
	}

	/**
	 * Update an existing CheckoutSession's line items to reflect the current cart totals, and
	 * setup_future_usage to reflect the "save payment method" checkbox.
	 *
	 * @param string $session_id
	 * @param bool   $clear_order_id Clears metadata.order_id - only appropriate on a checkout page
	 *                               (re)load, not the order-review sync path. Also forces the
	 *                               update through regardless of the signature cache below - it's
	 *                               a discrete state correction (reclaiming a session whose order
	 *                               binding went stale), not a "did the cart change" question.
	 *
	 * @return null|\WP_Error|\PaymentPlugins\Vendor\Stripe\Checkout\Session null means nothing
	 *         Stripe-relevant changed since the last sync, so the update call was skipped.
	 */
	public function update_session_from_cart( $session_id, $clear_order_id = false ) {
		if ( ! $this->is_enabled() ) {
			return new \WP_Error( 'adaptive_pricing_disabled', __( 'Adaptive Pricing is not enabled.', 'woo-stripe-payment' ) );
		}
		$payment_intent_data = $this->get_payment_intent_data_for_update();
		$signature           = $this->get_cart_sync_signature( $payment_intent_data );
		if ( ! $clear_order_id && WC()->session && WC()->session->get( self::SYNC_SIGNATURE_SESSION_KEY ) === $signature ) {
			return null;
		}
		$params = [ 'line_items' => $this->get_line_items_from_cart() ];
		if ( $payment_intent_data ) {
			$params['payment_intent_data'] = $payment_intent_data;
		}
		if ( $clear_order_id ) {
			$params['metadata'] = [ 'order_id' => '' ];
		}
		$session = $this->client->checkout->sessions->update( $session_id, $params );
		if ( ! is_wp_error( $session ) ) {
			$this->session_cache = $session;
			if ( WC()->session ) {
				WC()->session->set( self::SYNC_SIGNATURE_SESSION_KEY, $signature );
			}
		}

		return $session;
	}

	/**
	 * Signature of everything update_session_from_cart() actually sends to Stripe - currency,
	 * total, and setup_future_usage. Mirrors get_line_items_from_cart()'s own amount calculation.
	 *
	 * @param array $payment_intent_data
	 *
	 * @return string
	 */
	private function get_cart_sync_signature( $payment_intent_data ) {
		$currency = get_woocommerce_currency();
		$amount   = wc_stripe_add_number_precision( WC()->cart->get_total( 'float' ), $currency );

		return $currency . ':' . $amount . ':' . ( $payment_intent_data['setup_future_usage'] ?? '' );
	}

	/**
	 * Registered as this payment method's Store API cart/extensions update callback (see
	 * AbstractStripePayment::get_update_callback() / SchemaController) - called with whatever
	 * `data` the client sent to POST /wc/store/v1/cart/extensions.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function update_save_payment_method( $data ) {
		$this->save_payment_method_override = ! empty( $data['shouldSavePayment'] );
	}

	/**
	 * setup_future_usage derived from the "save payment method" checkbox in the order-review
	 * post_data (classic checkout), or update_save_payment_method() (checkout block). Empty
	 * string clears a value set on an earlier update in the same session.
	 *
	 * @return array
	 */
	protected function get_payment_intent_data_for_update() {
		if ( ! is_null( $this->save_payment_method_override ) ) {
			return [ 'setup_future_usage' => $this->save_payment_method_override ? 'off_session' : '' ];
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['post_data'] ) || ! is_string( $_POST['post_data'] ) ) {
			return [];
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		parse_str( wp_unslash( $_POST['post_data'] ), $posted );
		$gateway_id = isset( $posted['payment_method'] ) ? wc_clean( $posted['payment_method'] ) : '';
		if ( ! $gateway_id ) {
			return [];
		}
		$save = ! empty( $posted["wc-{$gateway_id}-new-payment-method"] );

		return [ 'setup_future_usage' => $save ? 'off_session' : '' ];
	}

	/**
	 * Finishes an order whose CheckoutSession already paid - confirm() succeeded on a prior
	 * attempt but WooCommerce hasn't seen the checkout.session.completed webhook yet. Mirrors
	 * WC_Payment_Gateway_Stripe::process_payment()'s complete_payment branch.
	 *
	 * @param \WC_Order                                      $order
	 * @param \WC_Payment_Gateway_Stripe                     $payment_method
	 * @param \PaymentPlugins\Vendor\Stripe\Checkout\Session $session
	 *
	 * @return array
	 */
	private function complete_order_from_session( $order, $payment_method, $session ) {
		$intent = $this->client->paymentIntents->retrieve( $session->payment_intent, [ 'expand' => [ 'latest_charge' ] ] );
		if ( is_wp_error( $intent ) ) {
			// The session paid, so the checkout.session.completed webhook will still finish the
			// order - just send the customer to the received page.
			wc_stripe_log_error( sprintf( 'Adaptive Pricing: could not retrieve PaymentIntent for paid session %s: %s', $session->id, $intent->get_error_message() ) );
		} elseif ( ! $order->get_date_paid() ) {
			if ( WC()->cart ) {
				WC()->cart->empty_cart();
			}
			$order->update_meta_data( \WC_Stripe_Constants::PAYMENT_INTENT_ID, $intent->id );
			$order->update_meta_data( \WC_Stripe_Constants::MODE, $intent->livemode ? 'live' : 'test' );
			$order->set_payment_method( $payment_method->id );
			$order->save();
			$payment_method->payment_controller->payment_complete( $order, $intent->latest_charge );
			$payment_method->trigger_post_payment_processes( $order, $payment_method );
		}

		return [ 'result' => 'success', 'redirect' => $payment_method->get_return_url( $order ) ];
	}

	/**
	 * The subset of payment_intent_data a CheckoutSession update accepts - description, metadata,
	 * setup_future_usage, statement_descriptor / _suffix - built from the order.
	 *
	 * @param \WC_Order                  $order
	 * @param \WC_Payment_Gateway_Stripe $payment_method
	 *
	 * @return array
	 */
	private function get_order_payment_intent_data( $order, $payment_method ) {
		$controller = $payment_method->payment_controller;
		$data       = [];
		$controller->add_order_metadata( $data, $order );
		$controller->add_order_description( $data, $order );
		if ( $payment_method->should_save_payment_method( $order ) ) {
			$data['setup_future_usage'] = 'off_session';
		}
		$advanced = stripe_wc()->advanced_settings;
		if ( $payment_method->get_payment_method_type() === 'card' ) {
			if ( $suffix = $advanced->get_option( 'statement_descriptor_suffix', '' ) ) {
				$data['statement_descriptor_suffix'] = \WC_Stripe_Utils::sanitize_statement_descriptor( \WC_Stripe_Utils::format_statement_descriptor( $suffix, $order ) );
			}
		} elseif ( $descriptor = $advanced->get_option( 'statement_descriptor', '' ) ) {
			$data['statement_descriptor'] = \WC_Stripe_Utils::sanitize_statement_descriptor( \WC_Stripe_Utils::format_statement_descriptor( $descriptor, $order ) );
		}

		/**
		 * Filters the payment_intent_data sent when binding a CheckoutSession to an order
		 * (process_payment_result()'s order-pay confirm step) - the subset of PaymentIntent fields
		 * Stripe's Checkout Session API accepts embedded this way (metadata, description,
		 * setup_future_usage, statement_descriptor/_suffix), not the full args shape
		 * wc_stripe_payment_intent_args filters for the plain PaymentIntent flow.
		 *
		 * @param array                      $data
		 * @param \WC_Order                  $order
		 * @param \WC_Payment_Gateway_Stripe $payment_method
		 *
		 * @since 4.0.15
		 */
		return apply_filters( 'wc_stripe_checkout_session_order_payment_intent_data', $data, $order, $payment_method );
	}

	/**
	 * Hash payload the client picks up (DOMEvents.onPlaceOrderSuccess) to run actions.confirm().
	 *
	 * @param \PaymentPlugins\Vendor\Stripe\Checkout\Session $session
	 * @param \WC_Order                                      $order
	 * @param \WC_Payment_Gateway_Stripe                     $payment_method
	 *
	 * @return string
	 */
	private function get_confirmation_redirect( $session, $order, $payment_method ) {
		$billing_details = $this->get_billing_details_from_order( $order );
		// confirm() rejects `email` when the session already carries customer_email / a customer.
		// Only a guest we had no email for at session create needs it passed client-side.
		if ( ! empty( $session->customer_email ) || ! empty( $session->customer ) ) {
			unset( $billing_details['email'] );
		}
		$args = [
			'type'            => 'checkout_session',
			'session_id'      => $session->id,
			'order_id'        => $order->get_id(),
			'order_key'       => $order->get_order_key(),
			'return_url'      => add_query_arg( '_stripe_checkout_session', '1', $payment_method->get_complete_payment_return_url( $order ) ),
			'billing_details' => $billing_details,
			// Keeps the #response= hash unique per attempt so a retry still fires hashchange
			// even if the client-side hash cleanup was skipped.
			'entropy'         => rand( 0, 999999 ),
		];
		// If a saved payment method is being used, add it to the response so it can be used
		// in the client side actions.confirm() call.
		$requested_payment_method = $payment_method->get_payment_method_from_request( $order );
		if ( $requested_payment_method ) {
			$args['payment_method'] = $requested_payment_method;
		}

		return '#response=' . rawurlencode( base64_encode( wp_json_encode( $args ) ) );
	}

	/**
	 * @param \WC_Order $order
	 *
	 * @return array
	 */
	private function get_billing_details_from_order( $order ) {
		$nullify            = function ( $value ) {
			return $value === '' ? null : $value;
		};
		$details            = [
			'name'    => trim( sprintf( '%s %s', $order->get_billing_first_name(), $order->get_billing_last_name() ) ),
			'phone'   => $order->get_billing_phone(),
			'email'   => $order->get_billing_email(),
			'address' => [
				'city'        => $order->get_billing_city(),
				'country'     => $order->get_billing_country(),
				'line1'       => $order->get_billing_address_1(),
				'line2'       => $order->get_billing_address_2(),
				'postal_code' => $order->get_billing_postcode(),
				'state'       => $order->get_billing_state()
			]
		];
		$details            = array_map( $nullify, $details );
		$details['address'] = array_map( $nullify, $details['address'] );

		return $details;
	}

	/**
	 * Keeps the CheckoutSession's line items in sync with the cart total as a side effect of
	 * WooCommerce's own order-review recalculation request, rather than a dedicated request of
	 * our own. Fires on every calculate_totals() call sitewide, so this is guarded to only act
	 * during the actual update_order_review AJAX request the classic checkout page uses for
	 * cart-total changes (coupon, shipping method, etc.) - Blocks checkout isn't covered by this
	 * yet, see DESIGN.md. Cart-only - order-pay doesn't recalculate totals the way checkout does.
	 *
	 * @param \WC_Cart $cart
	 *
	 * @return void
	 */
	public function maybe_sync_session_with_cart( $cart ) {
		// is_available_for_cart(), not just is_enabled() - a stale session id left in WC()->session
		// from an earlier attempt must not get synced for a request AP shouldn't touch at all (e.g.
		// the cart now only contains a charge-upon-release pre-order). Not is_available() - this
		// fires from woocommerce_after_calculate_totals, which also runs during Blocks Store API
		// requests where ContextHandler::is_checkout() doesn't reliably resolve.
		if ( $this->synced_this_request || ! $this->is_available_for_cart( $cart ) || ! $this->is_order_review_request() ) {
			return;
		}
		$session_id = WC()->session ? WC()->session->get( self::SESSION_KEY ) : null;
		if ( ! $session_id ) {
			return;
		}
		$this->synced_this_request = true;
		try {
			$result = $this->update_session_from_cart( $session_id );
			if ( is_wp_error( $result ) ) {
				wc_stripe_log_error( sprintf( 'Error syncing Adaptive Pricing checkout session on order review: %s', $result->get_error_message() ) );
			}
		} catch ( \Exception $e ) {
			wc_stripe_log_error( sprintf( 'Exception syncing Adaptive Pricing checkout session on order review: %s', $e->getMessage() ) );
		}
	}

	/**
	 * @return bool
	 */
	private function is_order_review_request() {
		// Classic checkout's own recalculation request.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['wc-ajax'] ) && 'update_order_review' === $_REQUEST['wc-ajax'] ) {
			return true;
		}
		// The checkout block's equivalent - its Store API PATCH route calls calculate_totals()
		// directly (no wrapping action to key off), so REST_REQUEST is the reliable signal here.
		// True during the checkout payment POST too, but that's harmless: update_session_from_cart()
		// only refreshes line items on an already-open session, and $synced_this_request already
		// caps this to once per request either way.
		return defined( 'REST_REQUEST' ) && REST_REQUEST;
	}

	/**
	 * @param \PaymentPlugins\Vendor\Stripe\Checkout\Session|\WP_Error $session
	 *
	 * @return array|\WP_Error
	 */
	private function format_session_response( $session ) {
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		return [ 'client_secret' => $session->client_secret, 'session_id' => $session->id ];
	}

	/**
	 * Protected (not private) so UPMCheckoutSessionController can override it, calling
	 * parent::get_session_params_from_cart() and adding to the result.
	 *
	 * @return array
	 */
	protected function get_session_params_from_cart() {
		$params   = [
			'ui_mode'                 => 'custom',
			'mode'                    => 'payment',
			'line_items'              => $this->get_line_items_from_cart(),
			'adaptive_pricing'        => [ 'enabled' => 'true' ],
			// Lets confirm() accept the phone from WC's billing form. In custom ui_mode this
			// only permits the session to carry a phone; the element renders no field.
			'phone_number_collection' => [ 'enabled' => 'true' ],
		];
		$customer = wc_stripe_get_customer_id();
		if ( $customer ) {
			$params['customer'] = $customer;
		} elseif ( WC()->customer && $email = WC()->customer->get_billing_email() ?: WC()->customer->get_email() ) {
			$params['customer_email'] = $email;
		}

		/**
		 * Filters the params used to create/update the Adaptive Pricing CheckoutSession for the current cart.
		 *
		 * @param array    $params
		 * @param \WC_Cart $cart
		 *
		 * @since 4.0.15
		 */
		return apply_filters( 'wc_stripe_checkout_session_params', $params, WC()->cart );
	}

	/**
	 * Single aggregate line item mirroring the cart total, matching how the rest of this plugin
	 * treats WooCommerce (not Stripe) as the source of truth for tax/discount calculation.
	 *
	 * @return array
	 */
	private function get_line_items_from_cart() {
		$currency = get_woocommerce_currency();
		$amount   = wc_stripe_add_number_precision( WC()->cart->get_total( 'float' ), $currency );

		return [
			[
				'price_data' => [
					'currency'     => strtolower( $currency ),
					'unit_amount'  => $amount,
					'product_data' => [ 'name' => __( 'Order total', 'woo-stripe-payment' ) ]
				],
				'quantity'   => 1
			]
		];
	}

	/**
	 * Protected (not private) so UPMCheckoutSessionController can override it, calling
	 * parent::get_session_params_from_order() and adding to the result.
	 *
	 * @param \WC_Order $order
	 *
	 * @return array
	 */
	protected function get_session_params_from_order( $order ) {
		$params   = [
			'ui_mode'                 => 'custom',
			'mode'                    => 'payment',
			'line_items'              => $this->get_line_items_from_order( $order ),
			'adaptive_pricing'        => [ 'enabled' => 'true' ],
			'phone_number_collection' => [ 'enabled' => 'true' ],
			// The order already exists, so bind the session to it from the start rather than
			// deferring binding to process_payment_result() the way the cart flow has to.
			'metadata'                => [ 'order_id' => $order->get_id() ],
		];
		$customer = wc_stripe_get_customer_id( $order->get_customer_id() );
		if ( $customer ) {
			$params['customer'] = $customer;
		} elseif ( $email = $order->get_billing_email() ) {
			$params['customer_email'] = $email;
		}

		/**
		 * Filters the params used to create the Adaptive Pricing CheckoutSession for an order-pay request.
		 *
		 * @param array     $params
		 * @param \WC_Order $order
		 *
		 * @since 4.0.15
		 */
		return apply_filters( 'wc_stripe_checkout_session_order_params', $params, $order );
	}

	/**
	 * Single aggregate line item mirroring the order total, matching how the rest of this plugin
	 * treats WooCommerce (not Stripe) as the source of truth for tax/discount calculation.
	 *
	 * @param \WC_Order $order
	 *
	 * @return array
	 */
	private function get_line_items_from_order( $order ) {
		$currency = $order->get_currency();
		$amount   = wc_stripe_add_number_precision( (float) $order->get_total(), $currency );

		return [
			[
				'price_data' => [
					'currency'     => strtolower( $currency ),
					'unit_amount'  => $amount,
					'product_data' => [ 'name' => __( 'Order total', 'woo-stripe-payment' ) ]
				],
				'quantity'   => 1
			]
		];
	}
}