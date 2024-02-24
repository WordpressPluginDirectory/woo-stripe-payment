<?php


namespace PaymentPlugins\Blocks\Stripe\Payments\Gateways;


use PaymentPlugins\Blocks\Stripe\Payments\AbstractStripeLocalPayment;

class BoletoPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_boleto';

	protected function get_script_translations() {
		return array_merge(
			parent::get_script_translations(),
			[
				'cpf_notice'   => __( 'Please enter a valid CPF/CNPJ value', 'woo-stripe-payment' ),
				'tax_id_label' => __( ' CPF / CNPJ', ' woo-stripe-payment' ),
				'test_desc'    => __( 'Test mode values', 'woo-stripe-payment' ),
				'formats'      => __( 'Accepted formats', 'woo-stripe-payment' ),
				'cpf_format'   => __( 'XXX.XXX.XXX-XX or XXXXXXXXXXX', 'woo-stripe-payment' ),
				'cnpj_format'  => __( 'XX.XXX.XXX/XXXX-XX or XXXXXXXXXXXXXX', 'woo-stripe-payment' )
			]
		);
	}

}