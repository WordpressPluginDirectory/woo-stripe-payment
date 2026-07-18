<?php

namespace PaymentPlugins\Stripe;

use PaymentPlugins\Stripe\Utilities\ProductUtils;

/**
 * Keeps track of the page "context"
 */
class ContextHandler {

	const CHECKOUT = 'checkout';

	const CART = 'cart';

	const ORDER_PAY = 'order_pay';

	const ADD_PAYMENT_METHOD = 'add_payment_method';

	const PAYMENT_METHODS = 'payment_methods';

	const PRODUCT = 'product';

	const ORDER_RECEIVED = 'order_received';

	const SHOP = 'shop';

	const ACCOUNT = 'account';

	/**
	 * @var string
	 */
	private $context;

	/**
	 * @var \WC_Order
	 */
	private $order;

	/**
	 * @var bool
	 */
	private $initialized = false;

	public function __construct() {
		add_action( 'wp', [ $this, 'initialize' ] );
		add_action( 'init', function () {
			if ( function_exists( '\is_ajax' ) && \is_ajax() ) {
				add_action( 'woocommerce_before_calculate_totals', function () {
					$this->initialized = false;
					$this->initialize();
				} );
			}
		} );
	}

	public function set_context( $context ) {
		$this->context = $context;
	}

	public function get_context() {
		return $this->context;
	}

	public function initialize() {
		if ( $this->initialized ) {
			return;
		}
		/**
		 * The main query/$post global are only guaranteed to be fully resolved once the 'wp' action
		 * has fired (WP::main() runs query_posts()/register_globals() before firing it). If this runs
		 * earlier - e.g. via is_context()'s lazy call, before 'wp' fires - don't lock the result in
		 * permanently; let a later, reliable call retry instead of getting stuck with whatever an
		 * unreliable early environment resolved (or failed to resolve).
		 */
		if ( did_action( 'wp' ) ) {
			$this->initialized = true;
		}

		global $post;
		if ( ! $this->context ) {
			if ( is_checkout() ) {
				if ( is_checkout_pay_page() ) {
					$this->context = self::ORDER_PAY;
				} elseif ( is_order_received_page() ) {
					$this->context = self::ORDER_RECEIVED;
				} else {
					$this->context = self::CHECKOUT;
				}
			} elseif ( function_exists( 'is_payment_methods_page' ) && is_payment_methods_page() ) {
				$this->context = self::PAYMENT_METHODS;
			} elseif ( is_add_payment_method_page() ) {
				$this->context = self::ADD_PAYMENT_METHOD;
			} elseif ( is_cart() ) {
				$this->context = self::CART;
			} elseif ( is_product() || ( $post && ! empty( $post->post_content ) && strstr( $post->post_content, '[product_page' ) ) ) {
				$this->context = self::PRODUCT;
			} elseif ( is_shop() || is_product_taxonomy() ) {
				$this->context = self::SHOP;
			} elseif ( is_account_page() ) {
				$this->context = self::ACCOUNT;
			} elseif ( $this->is_checkout_block() ) {
				$this->context = self::CHECKOUT;
			} elseif ( $this->is_checkout_shortcode() ) {
				$this->context = self::CHECKOUT;
			} elseif ( $this->is_cart_block() ) {
				$this->context = self::CART;
			} elseif ( $this->is_product_block() ) {
				$this->context = self::PRODUCT;
			}
			do_action( 'wc_stripe_initialize_page_context', $this );
		}
	}

	public function is_frontend() {
		return ! is_admin() && ! defined( 'DOING_CRON' );
	}

	private function is_context( $page ) {
		$this->initialize();

		return $this->context === $page;
	}

	public function is_checkout() {
		return $this->is_context( self::CHECKOUT );
	}

	public function is_add_payment_method() {
		return $this->is_context( self::ADD_PAYMENT_METHOD );
	}

	public function is_cart() {
		return $this->is_context( self::CART );
	}

	public function is_product() {
		return $this->is_context( self::PRODUCT );
	}

	public function is_order_pay() {
		return $this->is_context( self::ORDER_PAY );
	}

	public function is_order_received() {
		return $this->is_context( self::ORDER_RECEIVED );
	}

	public function is_shop() {
		return $this->is_context( self::SHOP );
	}

	public function has_context( $contexts = [] ) {
		if ( is_string( $contexts ) ) {
			$contexts = [ $contexts ];
		}

		return \in_array( $this->context, $contexts );
	}

	public function get_order_from_query() {
		if ( $this->order ) {
			return $this->order;
		}
		global $wp;

		return wc_get_order( absint( $wp->query_vars['order-pay'] ) );
	}

	public function set_order( \WC_Order $order ) {
		$this->order = $order;
	}

	/**
	 * @return array|false|mixed|string|\WC_Product|\WC_Product_Variable|null
	 */
	public function get_product_id() {
		return ProductUtils::get_queried_product();
	}

	public function is_checkout_shortcode() {
		$id = get_queried_object_id();

		return \is_int( $id ) && wc_post_content_has_shortcode( 'woocommerce_checkout' );
	}

	public function is_checkout_block() {
		$id = get_queried_object_id();

		return is_singular() && $id > 0
		       && class_exists( '\WC_Blocks_Utils' )
		       && \WC_Blocks_Utils::has_block_in_page( $id, 'woocommerce/checkout' );
	}

	/**
	 * @return bool
	 * @since 4.0.0
	 */
	public function is_cart_block() {
		$id = get_queried_object_id();

		return is_singular() && $id > 0
		       && class_exists( '\WC_Blocks_Utils' )
		       && \WC_Blocks_Utils::has_block_in_page( $id, 'woocommerce/cart' );
	}

	/**
	 * @return bool
	 * @since 4.0.0
	 */
	public function is_product_block() {
		$id = get_queried_object_id();

		return is_singular() && $id > 0
		       && class_exists( '\WC_Blocks_Utils' )
		       && \WC_Blocks_Utils::has_block_in_page( $id, 'woocommerce/single-product' );
	}

}