<?php

namespace PaymentPlugins\Stripe\AdaptivePricing;

/**
 * Adaptive Pricing coordinator for Phase 1 (Universal Payment Method only).
 *
 * Reuses CheckoutSessionController's session creation/sync mechanics as-is, overriding only what's
 * specific to the UPM-only rollout:
 *
 * - is_enabled() reads the enable flag from UPM's own settings (where the admin field now lives)
 *   instead of the generic Advanced Settings tab.
 * - get_session_params_from_cart() / get_session_params_from_order() both add
 *   payment_method_configuration, reusing UPM's existing Stripe-side payment method configuration
 *   instead of the session falling back to Stripe account-level defaults for which payment
 *   methods it can offer.
 * - initialize() wires only session creation + cart sync. Script enqueue is driven by
 *   WC_Payment_Gateway_Stripe_UPM::get_checkout_script_handles(), which swaps in the
 *   wc-stripe-upm-checkout-session handle when AP is enabled - so the base class's
 *   wp_enqueue_scripts / wc_stripe_enqueue_checkout_script_handles hooks aren't registered here.
 *   Every other standalone gateway keeps its normal (non-AP) flow untouched in Phase 1.
 *
 * UPM's settings are read via a raw get_option() call rather than injecting
 * WC_Payment_Gateway_Stripe_UPM, since that gateway object is only constructed when
 * PaymentGatewayRegistry::initialize() runs (via WooCommerce's own woocommerce_payment_gateways
 * filter) - depending on the gateway instance directly would tie this class's own construction
 * timing to that sequence for no real benefit.
 *
 * Phase 2's CheckoutSessionController is not initialized while this class is active (see
 * ServiceProvider::do_woocommerce_init()) - only one of the two ever runs at a time.
 *
 * @since 4.0.15
 */
class UPMCheckoutSessionController extends CheckoutSessionController {

	const SETTINGS_OPTION = 'woocommerce_stripe_upm_settings';
	
	public function initialize() {
		add_action( 'wc_stripe_add_script_data', [ $this, 'add_checkout_session_data' ] );
		add_action( 'woocommerce_after_calculate_totals', [ $this, 'maybe_sync_session_with_cart' ] );
		// Priority 20: runs after SubscriptionsController/PreOrdersController's own
		// wc_stripe_process_payment_result callbacks (priority 10), so their more specific flows
		// (change-payment-method, zero-total renewal, charge-upon-release tokenization) get first
		// claim on $result. is_available()'s wc_stripe_adaptive_pricing_is_available veto already
		// covers these cases too, but this avoids depending on that alone.
		add_filter( 'wc_stripe_process_payment_result', [ $this, 'process_payment_result' ], 20, 3 );
		add_action( 'wc_stripe_order_payment_complete', [ $this, 'maybe_clear_completed_session' ], 10, 2 );
		add_filter( 'wc_stripe_api_request_args', [ $this, 'add_checkout_server_update_beta_header' ], 10, 3 );

		add_filter( 'woocommerce_payment_gateway_get_saved_payment_method_option_html', [
			$this,
			'add_payment_method_type_attribute'
		], 10, 3 );
	}

	/**
	 * Stamps each UPM saved-payment-method <li> with the Stripe payment method type its token
	 * belongs to (e.g. 'cashapp', 'card'), so the client can cross-reference it against the
	 * Payment Element's own 'availablepaymentmethodschange' event and hide/disable saved methods
	 * the currently selected currency doesn't support - there's no equivalent event on the
	 * Currency Selector Element itself. UPM is the only gateway whose saved-methods list mixes
	 * token types in the first place (every other gateway's list is one type already), and this
	 * only matters while a currency selector can actually be showing, so both are guarded here
	 * rather than in the shared AbstractGateway::get_saved_payment_method_option_html().
	 *
	 * @param string                   $html
	 * @param \WC_Payment_Token_Stripe $token
	 * @param \WC_Payment_Gateway      $gateway
	 *
	 * @return string
	 */
	public function add_payment_method_type_attribute( $html, $token, $gateway ) {
		if ( $gateway->id !== 'stripe_upm' || ! $this->is_available() ) {
			return $html;
		}

		$gateways = WC()->payment_gateways()->payment_gateways();
		$owner    = $gateways[ $token->get_gateway_id() ] ?? null;
		$type     = $owner instanceof \PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway ? $owner->get_payment_method_type() : '';

		if ( ! $type ) {
			return $html;
		}

		return preg_replace( '/<li /', sprintf( '<li data-payment-method-type="%s" ', esc_attr( $type ) ), $html, 1 );
	}

	/**
	 * @return bool
	 */
	public function is_enabled() {
		$settings = $this->get_upm_settings();
		if ( ( $settings['enabled'] ?? 'no' ) !== 'yes' ) {
			return false;
		}

		$config = $settings['adaptive_pricing_config'] ?? [];

		return is_array( $config ) && ( $config['enabled'] ?? 'no' ) === 'yes';
	}

	/**
	 * @return array
	 */
	protected function get_session_params_from_cart() {
		$params = parent::get_session_params_from_cart();

		$mode   = wc_stripe_mode();
		$config = $this->get_upm_settings()[ "{$mode}_payment_method_configuration" ] ?? '';

		if ( $config ) {
			$params['payment_method_configuration'] = $config;
		}

		return $params;
	}

	/**
	 * @param \WC_Order $order
	 *
	 * @return array
	 */
	protected function get_session_params_from_order( $order ) {
		$params = parent::get_session_params_from_order( $order );

		$mode   = wc_stripe_mode();
		$config = $this->get_upm_settings()[ "{$mode}_payment_method_configuration" ] ?? '';

		if ( $config ) {
			$params['payment_method_configuration'] = $config;
		}

		return $params;
	}

	/**
	 * @return array
	 */
	private function get_upm_settings() {
		$settings = get_option( self::SETTINGS_OPTION, [] );

		return is_array( $settings ) ? $settings : [];
	}

}