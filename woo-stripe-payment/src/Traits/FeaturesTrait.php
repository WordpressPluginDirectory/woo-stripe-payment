<?php

namespace PaymentPlugins\Stripe\Traits;

trait FeaturesTrait {

	public function init_supports() {
		// @todo - change this to use Traits defined in packages
		$this->supports = [
			'refunds',
			'products',
			'default_credit_card_form'
		];

		$traits = [];
		$class  = \get_class( $this );

		do {
			$traits = array_merge( \class_uses( $class ), $traits );
		} while ( $class = \get_parent_class( $class ) );

		foreach ( $traits as $trait ) {
			switch ( $trait ) {
				case 'PaymentPlugins\Stripe\Traits\TokenizationTrait':
					$this->supports[] = 'tokenization';
					$this->supports[] = 'add_payment_method';
					break;
				default:
					$property_name = substr( $trait, strrpos( $trait, '\\' ) + 1 ) . 'Features';
					if ( property_exists( $this, $property_name ) ) {
						$features = $this::$$property_name;
						if ( \is_array( $features ) ) {
							$this->supports = array_merge( $this->supports, $features );
						}
					}
					break;
			}
		}
	}
}