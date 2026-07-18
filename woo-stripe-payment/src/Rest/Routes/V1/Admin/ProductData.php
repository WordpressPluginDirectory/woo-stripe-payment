<?php

namespace PaymentPlugins\Stripe\Rest\Routes\V1\Admin;

use WP_REST_Server;

/**
 * @since 4.0.0
 */
class ProductData extends AbstractAdminRoute {

	public function get_path() {
		return '/product/(?P<task>[a-z\-]+)';
	}

	public function get_routes() {
		return [
			[
				'methods'     => WP_REST_Server::CREATABLE,
				'callback'    => [ $this, 'handle_request' ],
				'permissions' => [ 'manage_woocommerce' ],
				'args'        => [
					'task' => [
						'required' => true,
						'type'     => 'string',
					]
				]
			]
		];
	}

	public function handle_post_request( \WP_REST_Request $request ) {
		$action = $request->get_param( 'task' );
		switch ( $action ) {
			case 'gateway':
				return $this->toggle_gateway( $request );
			case 'save':
				return $this->save( $request );
		}

		throw new \Exception( sprintf( __( 'Unknown task: %s', 'woo-stripe-payment' ), $action ), 400 );
	}

	private function toggle_gateway( \WP_REST_Request $request ) {
		$product        = wc_get_product( $request->get_param( 'product_id' ) );
		$payment_method = WC()->payment_gateways()->payment_gateways()[ $request->get_param( 'gateway_id' ) ];
		$option         = new \WC_Stripe_Product_Gateway_Option( $product, $payment_method );
		$option->set_option( 'enabled', ! $option->enabled() );
		$option->save();

		return [ 'enabled' => $option->enabled() ];
	}

	private function save( \WP_REST_Request $request ) {
		$gateways         = $request->get_param( 'gateways' );
		$charge_types     = $request->get_param( 'charge_types' );
		$payment_gateways = WC()->payment_gateways()->payment_gateways();
		$product          = wc_get_product( $request->get_param( 'product_id' ) );
		$order            = [];
		$loop             = 0;

		foreach ( $gateways as $gateway ) {
			$order[ $gateway ] = $loop ++;
		}

		$product->update_meta_data( \WC_Stripe_Constants::PRODUCT_GATEWAY_ORDER, $order );

		foreach ( $charge_types as $type ) {
			$option = new \WC_Stripe_Product_Gateway_Option( $product, $payment_gateways[ $type['gateway'] ] );
			$option->set_option( 'charge_type', $type['value'] );
			$option->save();
		}

		$product->update_meta_data( '_stripe_button_position', $request->get_param( 'position' ) );
		$product->save();

		return [ 'order' => $order ];
	}

}
