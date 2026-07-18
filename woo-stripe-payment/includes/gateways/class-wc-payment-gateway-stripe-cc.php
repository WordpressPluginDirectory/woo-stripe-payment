<?php

use PaymentPlugins\Stripe\Controllers\PaymentIntentController;

defined( 'ABSPATH' ) || exit();

/**
 *
 * @since   3.0.0
 * @package PaymentPlugins\Gateways
 * @author  User
 *
 */
class WC_Payment_Gateway_Stripe_CC extends WC_Payment_Gateway_Stripe {

	use WC_Stripe_Payment_Intent_Trait;
	use \PaymentPlugins\Stripe\Traits\TokenizationTrait;
	use \PaymentPlugins\Stripe\WooCommercePreOrders\Traits\PreOrdersTrait;
	use \PaymentPlugins\Stripe\WooCommerceSubscriptions\Traits\WooCommerceSubscriptionsTrait;

	public $id = 'stripe_cc';

	protected $payment_method_type = 'card';

	protected $supports_save_payment_method = true;

	public $token_type = 'Stripe_CC';

	public $template_name = 'credit-card.php';

	public function __construct( ...$args ) {
		parent::__construct( ...$args );
		$this->tab_title          = __( 'Credit/Debit Cards', 'woo-stripe-payment' );
		$this->method_title       = __( 'Credit Cards (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Credit card gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		$this->icon               = $this->get_option( 'icon_url', '' );
	}

	public function get_icon() {
		$icon_url = $this->get_option( 'icon_url', '' );
		if ( ! $icon_url ) {
			$cards = (array) $this->get_option( 'card_icons', array() );
			if ( ! empty( $cards ) ) {
				$this->validate_card_icons_field( 'card_icons', $cards );
			}
			$this->icon = $this->get_option( 'icon_url', '' );
		}

		return parent::get_icon();
	}

	public function get_checkout_script_handles() {
		//if ( $this->is_payment_element_active() ) {
		$this->assets->register_script( 'wc-stripe-credit-card', 'build/credit-card.js' );

		//}

		return [ 'wc-stripe-credit-card' ];
	}

	public function get_add_payment_method_script_handles() {
		//if ( $this->is_payment_element_active() ) {
		$this->assets->register_script( 'wc-stripe-credit-card-add-payment', 'build/credit-card-add-payment.js' );

		//}

		return [ 'wc-stripe-credit-card-add-payment' ];
	}

	public function get_payment_method_data() {
		return array_merge(
			parent::get_payment_method_data(),
			[
				'cardFormType'         => $this->get_active_card_form_type(),
				'inlineFormOptions'    => $this->get_card_form_options(),
				'paymentElementActive' => $this->is_payment_element_active(),
				'customFieldOptions'   => $this->get_card_custom_field_options(),
				'cardIcons'            => array(
					'visa'       => $this->assets->assets_url( 'img/cards/visa.svg' ),
					'amex'       => $this->assets->assets_url( 'img/cards/amex.svg' ),
					'mastercard' => $this->assets->assets_url( 'img/cards/mastercard.svg' ),
					'discover'   => $this->assets->assets_url( 'img/cards/discover.svg' ),
					'diners'     => $this->assets->assets_url( 'img/cards/diners.svg' ),
					'jcb'        => $this->assets->assets_url( 'img/cards/jcb.svg' ),
					'unionpay'   => $this->assets->assets_url( 'img/cards/china_union_pay.svg' ),
					'unknown'    => $this->get_custom_form()['cardBrand'],
				),
				'html'                 => [
					'card_brand' => sprintf( '<img id="wc-stripe-card" src="%s" />', $this->get_custom_form()['cardBrand'] )
				],
				'noticeLocation'       => $this->get_option( 'notice_location' ),
				'noticeSelector'       => $this->get_notice_css_selector(),
				'installments'         => [
					'clientSecret' => null,
				],
			]
		);
	}

	/**
	 * @since 3.3.0
	 */
	public function get_card_form_options() {
		$options = array(
			'style'       => $this->get_form_style(),
			'disableLink' => ! \wc_string_to_bool( $this->get_option( 'link_enabled', 'yes' ) )
		);

		return apply_filters( 'wc_stripe_cc_form_options', $options, $this );
	}

	/**
	 * @return mixed|void
	 * @since 3.3.0
	 */
	public function get_card_custom_field_options() {
		$style   = $this->get_form_style();
		$options = array();
		foreach ( [ 'cardNumber', 'cardExpiry', 'cardCvc' ] as $key ) {
			$options[ $key ] = array( 'style' => $style );
		}

		return apply_filters( 'wc_stripe_get_card_custom_field_options', $options, $this );
	}

	public function get_form_style() {
		if ( $this->is_custom_form_active() ) {
			$style = $this->get_custom_form()['elementStyles'];
		} else {
			$style = array(
				'base'    => array(
					'color'         => '#32325d',
					'fontFamily'    => '"Helvetica Neue", Helvetica, sans-serif',
					'fontSmoothing' => 'antialiased',
					'fontSize'      => '18px',
					'::placeholder' => array( 'color' => '#aab7c4' ),
					':focus'        => array(),
				),
				'invalid' => array(
					'color'     => '#fa755a',
					'iconColor' => '#fa755a',
				),
			);
		}

		return apply_filters( 'wc_stripe_cc_element_style', $style, $this );
	}

	public function get_custom_form() {
		return wc_stripe_get_custom_forms()[ $this->get_option( 'custom_form' ) ] ??
		       wc_stripe_get_custom_forms()['bootstrap'];
	}

	public function get_element_options( $options = array() ) {
		if ( $this->is_custom_form_active() || ! $this->is_payment_element_active() ) {
			$options = array( 'locale' => wc_stripe_get_site_locale() );

			if ( $this->is_custom_form_active() ) {
				$options = array_merge(
					$this->get_custom_form()['elementOptions'],
					$options,
				);
			}

			$options = array_merge(
				$options,
				wc_stripe_get_container()->get( PaymentIntentController::class )->get_element_options()
			);

			return apply_filters( 'wc_stripe_get_element_options', $options, $this );
		} elseif ( $this->is_payment_element_active() ) {
			$options                       = wc_stripe_get_container()->get( PaymentIntentController::class )->get_element_options();
			$options['paymentMethodTypes'] = array( 'card' );
			if ( $this->is_link_enabled() && ! is_add_payment_method_page() ) {
				$options['paymentMethodTypes'][] = 'link';
			}
			$options['appearance'] = array( 'theme' => $this->get_option( 'theme', 'stripe' ) );

			return parent::get_element_options( $options );
		}

		return parent::get_element_options();
	}


	/**
	 * Returns true if custom forms are enabled.
	 *
	 * @return bool
	 */
	public function is_custom_form_active() {
		return $this->get_option( 'form_type' ) === 'custom';
	}

	public function is_payment_element_active() {
		return $this->get_option( 'form_type' ) === 'payment';
	}

	public function get_custom_form_template() {
		$form         = $this->get_option( 'custom_form' );
		$custom_forms = wc_stripe_get_custom_forms();

		return $custom_forms[ $form ]['template'] ?? $custom_forms['bootstrap']['template'];
	}

	/**
	 * Returns true if the postal code field is enabled.
	 *
	 * @return bool
	 */
	public function postal_enabled() {
		if ( is_checkout() ) {
			return $this->is_active( 'postal_enabled' );
		}
		if ( is_add_payment_method_page() ) {
			return true;
		}
	}

	/**
	 * Returns true if the cvv field is enabled.
	 *
	 * @return bool
	 */
	public function cvv_enabled() {
		return $this->is_active( 'cvv_enabled' );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Gateway_Stripe::add_stripe_order_args()
	 */
	public function add_stripe_order_args( &$args, $order, $intent = null ) {
		// if the merchant is forcing 3D secure for all intents then add the required args.
		if ( $this->is_active( 'force_3d_secure' ) && is_checkout() && ! doing_action( 'woocommerce_scheduled_subscription_payment_' . $this->id ) ) {
			$args['payment_method_options']['card']['request_three_d_secure'] = 'any';
		}
		if ( stripe_wc()->advanced_settings && wc_string_to_bool( stripe_wc()->advanced_settings->get_option( 'extended_authorization', 'no' ) ) ) {
			if ( isset( $args['capture_method'] ) && $args['capture_method'] === WC_Stripe_Constants::MANUAL ) {
				$args['payment_method_options']['card']['request_extended_authorization'] = 'if_available';
			}
		}
		// Merge existing card payment_method_options from the intent so we don't overwrite
		// properties that were already set on it (e.g. installments).
		if ( $intent && ! empty( $intent->payment_method_options->card->installments->enabled ) && isset( $args['payment_method_options']['card'] ) ) {
			$args['payment_method_options']['card'] = array_merge(
				[ 'installments' => [ 'enabled' => $intent->payment_method_options->card->installments->enabled ] ],
				$args['payment_method_options']['card'] );
		}
		/**
		 * @var \PaymentPlugins\Stripe\Link\LinkIntegration $link_integration
		 */
		$link_integration = wc_stripe_get_container()->get( \PaymentPlugins\Stripe\Link\LinkIntegration::class );
		if ( $this->is_link_enabled() && $link_integration->is_active() ) {
			$args['payment_method_types'][] = 'link';
		}
	}

	/**
	 * @return mixed|void
	 * @since 3.3.32
	 */
	private function get_notice_css_selector() {
		$location = $this->get_option( 'notice_location' );
		$selector = '';
		switch ( $location ) {
			case 'acf':
				$selector = 'div.payment_method_stripe_cc';
				break;
			case 'bcf':
				$selector = '.wc-stripe-card-notice';
				break;
			case 'toc':
				$selector = 'form.checkout';
				break;
			case 'custom':
				$selector = $this->get_option( 'notice_selector', 'div.payment_method_stripe_cc' );
				break;
		}

		return $selector;
	}

	/**
	 * @return string Serves as a wrapper for the form_type option with some validations to ensure
	 *                a payment intent exists in the session.
	 */
	protected function get_active_card_form_type() {
		return $this->get_option( 'form_type' );
	}

	public function validate_form_type_field( $key, $value ) {
		if ( ! in_array( $value, array( 'payment', 'inline' ) ) && $this->is_active( 'link_enabled' ) ) {
			$value = $this->get_option( 'form_type' );
			WC_Admin_Settings::add_error( __( 'Only the Stripe payment form and inline form can be used while Link is enabled.', 'woo-stripe-payment' ) );
		}

		return $value;
	}

	public function is_deferred_intent_creation() {
		return $this->is_payment_element_active();
	}

	public function get_save_payment_method_label() {
		return __( 'Save Card', 'woo-stripe-payment' );
	}

	/**
	 * Return true if link is enabled.
	 * @return bool
	 * @since 4.0.0
	 */
	private function is_link_enabled() {
		return \wc_string_to_bool( $this->get_option( 'link_enabled', 'yes' ) );
	}

	public function validate_card_icons_field( $key, $value ) {
		if ( ! \is_array( $value ) ) {
			$value = [];
		}

		if ( empty( $value ) ) {
			return $value;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			return $value;
		}

		$count      = \count( $value );
		$svg_width  = ( $count * 750 ) + ( ( $count - 1 ) * 78 );
		$svg_height = '22.5';//$count * 5.5;
		$svg        = '<svg xmlns="http://www.w3.org/2000/svg" height="' . $svg_height . '" viewBox="0 0 ' . $svg_width . ' 468.75">';

		foreach ( $value as $idx => $card ) {
			$file_path = wc_stripe_get_container()->get( 'PLUGIN_PATH' ) . 'assets/img/cards/' . $card . '.svg';
			if ( $wp_filesystem->exists( $file_path ) ) {
				$icon        = $wp_filesystem->get_contents( $file_path );
				$inner       = preg_replace( '/<svg[^>]*>|<\/svg>/i', '', $icon );
				$transform_x = $idx * ( 750 + 78 );
				$svg         .= '<g transform="translate(' . $transform_x . ',0)">' . $inner . '</g>';
			}
		}

		$svg .= '</svg>';

		$uploads = wp_upload_dir( current_time( 'mysql' ) );
		$file    = $uploads['path'] . '/stripe-card-icons.svg';
		if ( $wp_filesystem->put_contents( $file, $svg ) ) {
			$this->settings['icon_url'] = $uploads['url'] . '/stripe-card-icons.svg?ver=' . uniqid();
		}

		return $value;
	}
}