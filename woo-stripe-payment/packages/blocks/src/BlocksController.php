<?php

namespace PaymentPlugins\Stripe\Blocks;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationRegistry;
use PaymentPlugins\Stripe\Blocks\BlockTypes\BNPLMessageCartBlock;
use PaymentPlugins\Stripe\Blocks\BlockTypes\MiniCartExpressPaymentBlock;
use PaymentPlugins\Stripe\Blocks\BlockTypes\SingleProductExpressPaymentBlock;
use PaymentPlugins\Stripe\Messages\BNPL\BNPLMessageController;
use PaymentPlugins\Stripe\Payments\PaymentGatewayRegistry;

class BlocksController {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function initialize() {
		add_action( 'woocommerce_blocks_cart_block_registration', [ $this, 'register_blocks' ] );
		add_action( 'woocommerce_blocks_checkout_block_registration', [ $this, 'register_blocks' ] );
		add_action( 'woocommerce_blocks_mini-cart_block_registration', [
			$this,
			'register_mini_cart_blocks'
		] );
		add_action( 'woocommerce_blocks_single-product_block_registration', [
			$this,
			'register_single_product_blocks'
		] );
	}

	public function register_single_product_blocks( IntegrationRegistry $registry ) {
		if ( version_compare( WC()->version, '10.4', '>=' ) ) {
			$registry->register(
				new SingleProductExpressPaymentBlock(
					$this->container->get( 'BLOCK_ASSETS' ),
					$this->container->get( PaymentGatewayRegistry::class )
				)
			);
		}
	}

	public function register_blocks( IntegrationRegistry $registry ) {
		$registry->register( new BNPLMessageCartBlock(
			$this->container->get( BNPLMessageController::class ),
			$this->container->get( \WC_Stripe_Advanced_Settings::class ),
			$this->container->get( 'BLOCK_ASSETS' )
		) );
	}

	public function register_mini_cart_blocks( IntegrationRegistry $registry ) {
		if ( version_compare( WC()->version, '10.4', '>=' ) ) {
			$registry->register(
				new MiniCartExpressPaymentBlock(
					$this->container->get( 'BLOCK_ASSETS' ),
					$this->container->get( PaymentGatewayRegistry::class )
				)
			);
		}
	}

}