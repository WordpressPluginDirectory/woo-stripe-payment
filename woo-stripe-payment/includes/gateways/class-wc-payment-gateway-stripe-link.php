<?php

/**
 * @package PaymentPlugins\Gateways
 */
class WC_Payment_Gateway_Stripe_Link extends \WC_Payment_Gateway_Stripe {

	use WC_Stripe_Payment_Intent_Trait;
	use \PaymentPlugins\Stripe\Traits\TokenizationTrait;
	use \PaymentPlugins\Stripe\Traits\ExpressCheckoutTrait;
	use \PaymentPlugins\Stripe\WooCommercePreOrders\Traits\PreOrdersTrait;
	use \PaymentPlugins\Stripe\WooCommerceSubscriptions\Traits\WooCommerceSubscriptionsTrait;

	public $id = 'stripe_link_checkout';

	public $payment_method_type = 'link';

	protected $has_digital_wallet = true;

	public function __construct( ...$args ) {
		parent::__construct( ...$args );
		$this->tab_title          = __( 'Link Checkout', 'woo-stripe-payment' );
		$this->template_name      = 'link-checkout.php';
		$this->token_type         = 'Stripe_CC';
		$this->method_title       = __( 'Link Checkout (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Link Checkout gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		$this->title              = __( 'Link Checkout', 'woo-stripe-payment' );
		$this->icon               = $this->assets->assets_url( 'img/link.svg' );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'link_title'       => array(
				'title' => __( 'Link Checkout', 'woo-stripe-payment' ),
				'type'  => 'title'
			),
			'enabled'          => array(
				'title'       => __( 'Enabled', 'woo-stripe-payment' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'value'       => 'yes',
				'desc_tip'    => true,
				'description' => __( 'If enabled, Link Checkout will be available in the locations configured.', 'woo-stripe-payment' ),
			),
			'title_text'       => array(
				'type'        => 'text',
				'title'       => __( 'Title', 'woo-stripe-payment' ),
				'default'     => $this->tab_title,
				'desc_tip'    => true,
				'description' => sprintf( __( 'Title of the %s gateway', 'woo-stripe-payment' ), $this->get_method_title() )
			),
			'payment_sections' => array(
				'title'             => __( 'Link Checkout Locations', 'woo-stripe-payment' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select',
				'options'           => array(
					'checkout'         => __( 'Checkout', 'woo-stripe-payment' ),
					'product'          => __( 'Product Page', 'woo-stripe-payment' ),
					'cart'             => __( 'Cart Page', 'woo-stripe-payment' ),
					'mini_cart'        => __( 'Mini Cart', 'woo-stripe-payment' ),
					'express_checkout' => __( 'Express Checkout', 'woo-stripe-payment' )
				),
				'sanitize_callback' => function ( $value ) {
					if ( empty( $value ) ) {
						$value = array();
					}

					return $value;
				},
				'default'           => array( 'cart', 'express_checkout' ),
				'description'       => __( 'Select where Link Express Checkout buttons will appear. Express Checkout allows customers to pay instantly using their saved payment and shipping information from Link, similar to Apple Pay or Google Pay.', 'woo-stripe-payment' )
			),
			'charge_type'      => array(
				'type'        => 'select',
				'title'       => __( 'Charge Type', 'woo-stripe-payment' ),
				'default'     => 'capture',
				'class'       => 'wc-enhanced-select',
				'options'     => array(
					'capture'   => __( 'Capture', 'woo-stripe-payment' ),
					'authorize' => __( 'Authorize', 'woo-stripe-payment' ),
				),
				'desc_tip'    => true,
				'description' => __( 'This option determines whether the customer\'s funds are captured immediately or authorized and can be captured at a later date.', 'woo-stripe-payment' ),
			),
			'order_status'     => array(
				'type'        => 'select',
				'title'       => __( 'Order Status', 'woo-stripe-payment' ),
				'default'     => 'default',
				'class'       => 'wc-enhanced-select',
				'options'     => array_merge( array( 'default' => __( 'Default', 'woo-stripe-payment' ) ), wc_get_order_statuses() ),
				'description' => __( 'This is the status of the order once payment is complete. If <b>Default</b> is selected, then WooCommerce will set the order status automatically based on internal logic which states if a product is virtual and downloadable then status is set to complete. Products that require shipping are set to Processing. Default is the recommended setting as it allows standard WooCommerce code to process the order status.',
					'woo-stripe-payment' ),
			),
			'button_height'    => array(
				'title'             => __( 'Button Height', 'woo-stripe-payment' ),
				'type'              => 'number',
				'default'           => 50,
				'desc_tip'          => true,
				'description'       => __( 'Button height for the Link Checkout button. The button height must be between 40px and 55px.', 'woo-stripe-gateway' ),
				'sanitize_callback' => function ( $value ) {
					if ( ! is_numeric( $value ) ) {
						$value = 40;
					}

					return max( 40, min( 55, $value ) );
				}
			),
			'button_radius'    => array(
				'title'             => __( 'Button Radius', 'woo-stripe-payment' ),
				'type'              => 'number',
				'class'             => 'button-radius',
				'default'           => '4',
				'description'       => __( 'The border radius of the button.', 'woo-stripe-payment' ),
				'sanitize_callback' => function ( $value ) {
					if ( ! preg_match( '/^[\d]+$/', $value ) ) {
						$value = 0;
					}

					return absint( $value );
				}
			),
		);
	}

	public function product_fields() {
		?>
        <div id="wc-<?php echo esc_attr( $this->id ) ?>-product-button"
             class="wc-<?php echo esc_attr( $this->id ) ?>-product-button"></div>
		<?php
	}

	public function cart_fields() {
		?>
        <div id="wc-<?php echo esc_attr( $this->id ) ?>-cart-button"
             class="wc-<?php echo esc_attr( $this->id ) ?>-cart-button"></div>
		<?php
	}

	public function mini_cart_fields() {
		?>
        <a class="wc-<?php echo esc_attr( $this->id ) ?>-mini-cart"></a>
		<?php
	}

	public function get_payment_method_data() {
		return array_merge(
			parent::get_payment_method_data(),
			[
				'button'                => [
					'height' => (int) $this->get_option( 'button_height', 40 ),
					'radius' => $this->get_option( 'button_radius', 4 ) . 'px',
				],
				'paymentElementOptions' => []
			]
		);
	}

	public function get_checkout_script_handles() {
		$this->assets->register_script( 'wc-stripe-link-checkout', 'build/link-checkout.js' );

		return [ 'wc-stripe-link-checkout' ];
	}

	public function get_express_checkout_script_handles() {
		$this->assets->register_script( 'wc-stripe-link-express-checkout', 'build/link-express-checkout.js' );

		return [ 'wc-stripe-link-express-checkout' ];
	}

	public function get_product_script_handles() {
		$this->assets->register_script( 'wc-stripe-link-product', 'build/link-product.js' );

		return [ 'wc-stripe-link-product' ];
	}

	public function get_cart_script_handles() {
		$this->assets->register_script( 'wc-stripe-link-cart', 'build/link-cart.js' );

		return [ 'wc-stripe-link-cart' ];
	}

	public function get_minicart_script_handles() {
		$this->assets->register_script( 'wc-stripe-link-minicart', 'build/link-minicart.js' );

		return [ 'wc-stripe-link-minicart' ];
	}

}