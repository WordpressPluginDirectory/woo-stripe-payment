<?php
/**
 * Plugin Name: Payment Plugins for Stripe WooCommerce
 * Plugin URI: https://paymentplugins.com/documentation/stripe/
 * Description: Accept Credit Cards, Google Pay, Apple Pay, ACH, Klarna and more using Stripe.
 * Version: 4.0.8
 * Author: Payment Plugins, support@paymentplugins.com
 * Text Domain: woo-stripe-payment
 * Domain Path: /i18n/languages/
 * Requires at least: 4.7
 * Requires PHP: 7.4
 * Tested up to: 7.0
 * WC requires at least: 3.4.0
 * WC tested up to: 10.9
 * Requires Plugins: woocommerce
 */
defined( 'ABSPATH' ) || exit ();

require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );

PaymentPlugins\Stripe\PluginValidation::is_valid( function () {
	new \PaymentPlugins\Stripe\Plugin( '4.0.8', __FILE__ );
} );