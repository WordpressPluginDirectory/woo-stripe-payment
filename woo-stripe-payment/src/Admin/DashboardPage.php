<?php

namespace PaymentPlugins\Stripe\Admin;

use PaymentPlugins\Stripe\Client\StripeClient;

class DashboardPage extends AbstractAdminPage {
	/**
	 * @var \StripeClient
	 */
	private $client;

	public function __construct( $client, ...$args ) {
		parent::__construct( ...$args );
		$this->client = $client;
	}

	public function get_id() {
		return 'dashboard';
	}

	public function get_registration_data() {
		return [
			'parent_slug' => 'woocommerce',
			'page_title'  => __( 'Stripe by Payment Plugins', 'woo-stripe-payment' ),
			'menu_title'  => __( 'Stripe by Payment Plugins', 'woo-stripe-payment' ),
			'capability'  => 'manage_woocommerce',
			'menu_slug'   => 'wc-settings&tab=checkout&section=stripe_api',
			'callback'    => function () {
				$this->output();
			}
		];
	}

	public function get_script_handles() {
		$this->assets->register_script( 'wc-stripe-admin-dashboard', 'build/admin-dashboard.js' );

		return [ 'wc-stripe-admin-dashboard' ];
	}

	public function get_script_data() {
		$account      = wc_stripe_get_account_id();
		$api_settings = wc_stripe_get_container()->get( \WC_Stripe_API_Settings::class );
		$mode         = wc_stripe_mode();
		$data         = [
			'mode'           => $mode,
			'isConnected'    => $api_settings->has_secret_key( $mode ),
			'i18n'           => [
				'title'          => 'Stripe Payments',
				'connectAccount' => __( 'Connect your Stripe account', 'woo-stripe-payment' ),
				'addAPIKeys'     => __( 'Your payments dashboard will appear here once your Stripe account is connected. Add your API keys on the API Settings page to get started.', 'woo-stripe-payment' ),
				'goToSettings'   => __( 'Go to Settings', 'woo-stripe-payment' ),
				'configGuide'    => __( 'Read the configuration guide', 'woo-stripe-payment' ),
				'notConnected'   => __( 'Not connected', 'woo-stripe-payment' ),
				'modeText'       => $mode === 'live' ? __( 'Live mode', 'woo-stripe-payment' ) : __( 'Test mode', 'woo-stripe-payment' )
			],
			'links'          => [
				'dashboard'     => admin_url( 'admin.php?page=wc-stripe-main' ),
				'settings'      => admin_url( 'admin.php?page=wc-settings&tab=checkout&section=stripe_api' ),
				'documentation' => 'https://paymentplugins.com/documentation/stripe/'
			],
			'accountId'      => $account,
			'accountSession' => []
		];
		$account      = wc_stripe_get_account_id();

		/*if ( $account ) {
					$session = $this->client->accountSessions->create( [
						'account'    => $account,
						'components' => [
							'payments' => [
								'enabled' => true
							]
						]
					] );
					if ( ! is_wp_error( $session ) ) {
						$data['accountSession'] = [
							'clientSecret' => $session->clientSecret
						];
					}
				}*/

		return $data;
	}

	private function output() {
		wp_enqueue_style( 'wc-stripe-admin-dashboard' );
		?>
        <div class="">
            <div id="root"></div>
        </div>
		<?php
	}
}