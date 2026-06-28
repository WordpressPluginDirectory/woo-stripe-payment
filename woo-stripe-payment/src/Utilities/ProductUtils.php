<?php

namespace PaymentPlugins\Stripe\Utilities;

class ProductUtils {

	/**
	 * @param \WC_Product $product
	 *
	 * @return array
	 */
	public static function get_product_variations( $product ) {
		if ( $product && $product->get_type() === 'variable' ) {
			$results = wp_cache_get( 'wc_stripe_product_variations' );
			if ( ! \is_array( $results ) ) {
				$results = [];
			}
			if ( isset( $results[ $product->get_id() ] ) ) {
				return $results[ $product->get_id() ];
			}

			$results[ $product->get_id() ] = [];

			$children = $product->get_children();

			if ( ! empty( $children ) ) {
				foreach ( $children as $child_id ) {
					$variation = wc_get_product( $child_id );
					if ( $variation ) {
						$results[ $product->get_id() ][] = $variation;
					}
				}
			}

			wp_cache_add( 'wc_stripe_product_variations', $results );

			return $results[ $product->get_id() ];
		}

		return [];
	}

	public static function get_queried_product() {
		global $product;
		if ( ! $product || \is_string( $product ) ) {
			$object = get_queried_object();
			if ( $object && $object instanceof \WP_Post ) {
				if ( $object->post_type === 'page' ) {
					$content = $object->post_content;
					if ( $content ) {
						if ( \has_shortcode( $content, 'product_page' ) ) {
							// find the product ID
							preg_match( '/(?<=\[product_page)\s+id=\"?([\d]+)\"?/', $content, $matches );
							if ( $matches ) {
								return wc_get_product( $matches[1] );
							}
						} elseif ( function_exists( 'has_block' ) && has_block( 'woocommerce/single-product', $content ) ) {
							$blocks = parse_blocks( $content );
							foreach ( $blocks as $block ) {
								if ( 'woocommerce/single-product' === $block['blockName'] ) {
									$attributes = $block['attrs'];
									if ( isset( $attributes['productId'] ) ) {
										return wc_get_product( $attributes['productId'] );
									}
								}
							}
						}
					}
				}

				return wc_get_product( $object->ID );
			}
		}
		if ( $product instanceof \WP_Post ) {
			return wc_get_product( $product->ID );
		}

		return $product;
	}

}