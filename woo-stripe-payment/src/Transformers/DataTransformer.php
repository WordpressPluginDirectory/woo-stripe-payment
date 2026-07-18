<?php

namespace PaymentPlugins\Stripe\Transformers;

use PaymentPlugins\Stripe\Utilities\NumberUtil;

/**
 * Transforms WooCommerce entities into Stripe-compatible data structures.
 *
 * This class is responsible for converting WooCommerce objects (Cart, Product, Order)
 * into normalized data arrays that the Stripe JavaScript integration expects.
 */
class DataTransformer {

	/**
	 * Transform WooCommerce cart into Stripe data structure
	 *
	 * @param \WC_Cart $cart
	 *
	 * @return array
	 */
	public function transform_cart( $cart ) {
		$currency = get_woocommerce_currency();
		$packages = $this->get_cart_shipping_packages( $cart );

		$data = [
			'total'                  => NumberUtil::round( $cart->get_total( 'float' ), 2 ),
			'subtotal'               => NumberUtil::round( $cart->get_subtotal(), 2 ),
			'totalCents'             => wc_stripe_add_number_precision( $cart->get_total( 'float' ), $currency ),
			'subtotalCents'          => wc_stripe_add_number_precision( $cart->get_subtotal(), $currency ),
			'needsPayment'           => $cart->needs_payment(),
			'needsShipping'          => $cart->needs_shipping(),
			'isEmpty'                => $cart->is_empty(),
			'currency'               => $currency,
			'countryCode'            => WC()->countries ? WC()->countries->get_base_country() : wc_get_base_location()['country'],
			'lineItems'              => $this->get_line_items_from_cart( $cart ),
			'shippingOptions'        => $this->get_shipping_options_from_packages( $cart, $packages ),
			'selectedShippingMethod' => $this->get_selected_shipping_method( $packages )
		];

		return apply_filters( 'wc_stripe_cart_data', $data, $cart );
	}

	/**
	 * Transform WooCommerce product into Stripe data structure
	 *
	 * @param \WC_Product $product
	 *
	 * @return array
	 */
	public function transform_product( $product, $args = [] ) {
		$currency = get_woocommerce_currency();
		$price    = floatval( NumberUtil::round( wc_get_price_to_display( $product ), 2 ) );
		$data     = [
			'id'              => $product->get_id(),
			'qty'             => $product->get_min_purchase_quantity(),
			'type'            => $product->get_type(),
			'price'           => $price,
			'total'           => $price,
			'priceCents'      => wc_stripe_add_number_precision( $product->get_price(), $currency ),
			'currency'        => $currency,
			'lineItems'       => $this->get_line_items_from_product( $product ),
			'isInStock'       => $product->is_in_stock(),
			'needsShipping'   => $product->needs_shipping(),
			'shippingOptions' => [],
			'variation_id'    => 0,
		];

		// If variable product, resolve the active variation to populate price/stock/shipping.
		// Uses $_REQUEST-derived attributes passed from AssetDataController (mirrors WooCommerce's
		// own dropdown pre-fill logic), falling back to the product's default attributes.
		if ( $product->get_type() === 'variable' ) {
			$selected_attributes = $args['selected_attributes'] ?? [];

			if ( empty( $selected_attributes ) ) {
				foreach ( $product->get_default_attributes() as $key => $attribute ) {
					$selected_attributes[ 'attribute_' . sanitize_title( $key ) ] = $attribute;
				}
			}

			if ( ! empty( $selected_attributes ) ) {
				$data_store   = \WC_Data_Store::load( 'product' );
				$variation_id = $data_store->find_matching_product_variation( $product, $selected_attributes );

				if ( $variation_id ) {
					$variation = wc_get_product( $variation_id );
					if ( $variation ) {
						ksort( $selected_attributes );
						$pairs = [];
						foreach ( $selected_attributes as $k => $v ) {
							$pairs[] = $k . ':' . $v;
						}
						$data['price']          = NumberUtil::round( wc_get_price_to_display( $variation ), 2 );
						$data['total']          = NumberUtil::round( $variation->get_price(), 2 );
						$data['totalCents']     = wc_stripe_add_number_precision( $variation->get_price(), $currency );
						$data['isInStock']      = $variation->is_in_stock();
						$data['needsShipping']  = $variation->needs_shipping();
						$data['lineItems']      = $this->get_line_items_from_product( $variation );
						$data['variation_id']   = $variation_id;
						$data['attributes']     = $selected_attributes;
						$data['attributesHash'] = implode( '|', $pairs );
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Transform WooCommerce order into Stripe data structure
	 *
	 * @param \WC_Order $order
	 *
	 * @return array
	 */
	public function transform_order( $order ) {
		return [
			'order_id'        => $order->get_id(),
			'order_key'       => $order->get_order_key(),
			'currency'        => $order->get_currency(),
			'total'           => NumberUtil::round( $order->get_total(), 2 ),
			'totalCents'      => wc_stripe_add_number_precision( $order->get_total(), $order->get_currency() ),
			'needsPayment'    => $order->needs_payment(),
			'lineItems'       => $this->get_line_items_from_order( $order ),
			'shippingOptions' => [],
			'billing_address' => [
				'name'       => sprintf(
					'%1$s %2$s',
					$order->get_billing_first_name(),
					$order->get_billing_last_name()
				),
				'first_name' => $order->get_billing_first_name(),
				'last_name'  => $order->get_billing_last_name(),
				'email'      => $order->get_billing_email(),
				'phone'      => $order->get_billing_phone(),
				'address_1'  => $order->get_billing_address_1(),
				'address_2'  => $order->get_billing_address_2(),
				'city'       => $order->get_billing_city(),
				'state'      => $order->get_billing_state(),
				'postcode'   => $order->get_billing_postcode(),
				'country'    => $order->get_billing_country(),
			]
		];
	}

	/**
	 * @param \WC_Customer $customer
	 *
	 * @return array
	 */
	public function transform_customer( $customer ) {
		return [
			'billing_address' => [
				'name'      => sprintf(
					'%1$s %2$s',
					$customer->get_billing_first_name(),
					$customer->get_billing_last_name()
				),
				'email'     => $customer->get_billing_email(),
				'phone'     => $customer->get_billing_phone(),
				'address_1' => $customer->get_billing_address_1(),
				'address_2' => $customer->get_billing_address_2(),
				'city'      => $customer->get_billing_city(),
				'state'     => $customer->get_billing_state(),
				'postcode'  => $customer->get_billing_postcode(),
				'country'   => $customer->get_billing_country(),
			],
		];
	}

	/**
	 * Get generic line items from cart
	 *
	 * @param \WC_Cart $cart
	 *
	 * @return array
	 */
	private function get_line_items_from_cart( $cart ) {
		$items    = [];
		$incl_tax = wc_tax_enabled() && $cart->display_prices_including_tax();
		$currency = get_woocommerce_currency();

		// Add cart line items
		foreach ( $cart->get_cart() as $cart_item ) {
			$product = $cart_item['data'];
			$qty     = $cart_item['quantity'];
			$label   = $qty > 1 ? sprintf( '%s X %s', $product->get_name(), $qty ) : $product->get_name();
			$price   = $incl_tax
				? wc_get_price_including_tax( $product, [ 'qty' => $qty ] )
				: wc_get_price_excluding_tax( $product, [ 'qty' => $qty ] );
			$items[] = [
				'id'          => $product->get_id(),
				'label'       => $label,
				'amount'      => NumberUtil::round( $price, 2 ),
				'amountCents' => wc_stripe_add_number_precision( $price, $currency ),
				'type'        => 'product',
				'name'        => $product->get_name(),
				'qty'         => $qty
			];
		}

		// Add shipping
		if ( $cart->needs_shipping() ) {
			$price   = $incl_tax
				? $cart->get_shipping_total() + $cart->get_shipping_tax()
				: $cart->get_shipping_total();
			$items[] = [
				'label'       => __( 'Shipping', 'woo-stripe-payment' ),
				'amount'      => NumberUtil::round( $price, 2 ),
				'amountCents' => wc_stripe_add_number_precision( $price, $currency ),
				'type'        => 'shipping'
			];
		}

		// Add fees
		foreach ( $cart->get_fees() as $fee ) {
			$price   = $incl_tax ? $fee->total + $fee->tax : $fee->total;
			$items[] = [
				'label'       => $fee->name,
				'amount'      => NumberUtil::round( $price, 2 ),
				'amountCents' => wc_stripe_add_number_precision( $price, $currency ),
				'type'        => 'fee'
			];
		}

		// Add discount
		if ( 0 < $cart->discount_cart ) {
			$price   = - 1 * abs( $incl_tax
					? $cart->discount_cart + $cart->discount_cart_tax
					: $cart->discount_cart );
			$items[] = [
				'label'       => __( 'Discount', 'woo-stripe-payment' ),
				'amount'      => NumberUtil::round( $price, 2 ),
				'amountCents' => wc_stripe_add_number_precision( $price, $currency ),
				'type'        => 'discount'
			];
		}

		// Add taxes separately if not included in prices
		if ( ! $incl_tax && wc_tax_enabled() ) {
			$items[] = [
				'label'       => __( 'Tax', 'woo-stripe-payment' ),
				'amount'      => NumberUtil::round( $cart->get_taxes_total(), 2 ),
				'amountCents' => wc_stripe_add_number_precision( $cart->get_taxes_total(), $currency ),
				'type'        => 'tax'
			];
		}

		return $items;
	}

	/**
	 * Get generic line items from order
	 *
	 * @param \WC_Order $order
	 *
	 * @return array
	 */
	private function get_line_items_from_order( $order ) {
		$items    = [];
		$currency = $order->get_currency();

		// Add order line items
		foreach ( $order->get_items() as $item ) {
			$qty     = $item->get_quantity();
			$label   = $qty > 1 ? sprintf( '%s X %s', $item->get_name(), $qty ) : $item->get_name();
			$items[] = [
				'label'       => $label,
				'amount'      => NumberUtil::round( $item->get_subtotal(), 2 ),
				'amountCents' => wc_stripe_add_number_precision( $item->get_subtotal(), $currency ),
				'type'        => 'item'
			];
		}

		// Add shipping
		if ( 0 < $order->get_shipping_total() ) {
			$items[] = [
				'label'       => __( 'Shipping', 'woo-stripe-payment' ),
				'amount'      => NumberUtil::round( $order->get_shipping_total(), 2 ),
				'amountCents' => wc_stripe_add_number_precision( $order->get_shipping_total(), $currency ),
				'type'        => 'shipping'
			];
		}

		// Add discount
		if ( 0 < $order->get_total_discount() ) {
			$discount = - 1 * $order->get_total_discount();
			$items[]  = [
				'label'       => __( 'Discount', 'woo-stripe-payment' ),
				'amount'      => NumberUtil::round( $discount, 2 ),
				'amountCents' => wc_stripe_add_number_precision( $discount, $currency ),
				'type'        => 'discount'
			];
		}

		// Add fees (combined)
		if ( 0 < count( $order->get_fees() ) ) {
			$fee_total = 0;
			foreach ( $order->get_fees() as $fee ) {
				$fee_total += $fee->get_total();
			}
			$items[] = [
				'label'       => __( 'Fees', 'woo-stripe-payment' ),
				'amount'      => NumberUtil::round( $fee_total, 2 ),
				'amountCents' => wc_stripe_add_number_precision( $fee_total, $currency ),
				'type'        => 'fee'
			];
		}

		// Add taxes
		if ( 0 < $order->get_total_tax() ) {
			$items[] = [
				'label'       => __( 'Tax', 'woo-stripe-payment' ),
				'amount'      => NumberUtil::round( $order->get_total_tax(), 2 ),
				'amountCents' => wc_stripe_add_number_precision( $order->get_total_tax(), $currency ),
				'type'        => 'tax'
			];
		}

		return $items;
	}

	/**
	 * Get generic line items from product
	 *
	 * @param \WC_Product $product
	 *
	 * @return array
	 */
	private function get_line_items_from_product( $product ) {
		$currency = get_woocommerce_currency();
		$price    = wc_get_price_to_display( $product );

		return [
			[
				'id'          => $product->get_id(),
				'label'       => $product->get_name(),
				'amount'      => floatval( NumberUtil::round( $price, 2 ) ),
				'amountCents' => wc_stripe_add_number_precision( $price, $currency ),
				'type'        => 'product'
			]
		];
	}

	/**
	 * Get the shipping packages for the cart, so they can be shared between
	 * get_shipping_options_from_packages() and get_selected_shipping_method() - both need to
	 * agree on the same package indices, or the "selected" shipping option id sent to wallets
	 * like Google Pay won't match any of the ids in the offered shippingOptions list.
	 *
	 * @param \WC_Cart|null $cart
	 *
	 * @return array
	 */
	private function get_cart_shipping_packages( $cart ) {
		if ( ! $cart || ! $cart->needs_shipping() ) {
			return [];
		}

		$packages = WC()->shipping()->get_packages();
		if ( empty( $packages ) ) {
			$packages = WC()->shipping()->calculate_shipping( $cart->get_shipping_packages() );
		}

		return apply_filters( 'wc_stripe_cart_shipping_packages', $packages );
	}

	/**
	 * Get generic shipping options from cart
	 *
	 * @param \WC_Cart|null $cart
	 * @param array         $packages
	 *
	 * @return array
	 */
	private function get_shipping_options_from_packages( $cart, $packages ) {
		if ( empty( $packages ) ) {
			return [];
		}

		$options  = [];
		$incl_tax = wc_tax_enabled() && $cart->display_prices_including_tax();
		$currency = get_woocommerce_currency();

		foreach ( $packages as $i => $package ) {
			foreach ( $package['rates'] as $rate ) {
				/**
				 * @var \WC_Shipping_Rate $rate
				 */
				$cost        = (float) $rate->get_cost();
				$price       = $incl_tax ? $cost + (float) $rate->get_shipping_tax() : $cost;
				$description = '';
				if ( method_exists( $rate, 'get_description' ) ) {
					$description = $rate->get_description();
				}
				if ( ! $description && method_exists( $rate, 'get_delivery_time' ) ) {
					$description = $rate->get_delivery_time();
				}

				$options[] = [
					'id'          => sprintf( '%s:%s', $i, $rate->get_id() ),
					'label'       => $rate->get_label(),
					'amount'      => NumberUtil::round( $price, 2 ),
					'amountCents' => wc_stripe_add_number_precision( $price, $currency ),
					'description' => $description
				];
			}
		}

		// Sort shipping options by price (least to greatest)
		usort( $options, function ( $a, $b ) {
			return $a['amountCents'] <=> $b['amountCents'];
		} );

		return $options;
	}

	/**
	 * Get selected shipping method ID
	 *
	 * @param array $packages
	 *
	 * @return string
	 */
	private function get_selected_shipping_method( $packages ) {
		if ( ! WC()->session || empty( $packages ) ) {
			return '';
		}

		$chosen_methods = WC()->session->get( 'chosen_shipping_methods', [] );
		// Only consider entries that correspond to an actual package built above - e.g. WooCommerce
		// Subscriptions also stores chosen methods for its own recurring cart packages under this
		// same session key, keyed differently than the numeric package indices used here.
		$chosen_methods = array_intersect_key( $chosen_methods, $packages );

		foreach ( $chosen_methods as $idx => $method ) {
			return sprintf( '%s:%s', $idx, $method );
		}

		return '';
	}

}