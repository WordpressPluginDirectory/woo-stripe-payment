<?php

namespace PaymentPlugins\Stripe\Admin;

use PaymentPlugins\Stripe\Assets\AssetDataApi;

class AdminPageController {

	private $asset_data;

	/**
	 * @var AbstractAdminPage[]
	 */
	private $pages = [];

	private $current_page;

	public function __construct( AssetDataApi $asset_data, $pages ) {
		$this->asset_data = $asset_data;
		$this->pages      = $pages;
	}

	public function initialize() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_menu', [ $this, 'register_admin_pages' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
		add_action( 'admin_print_scripts', [ $this, 'enqueue_admin_data' ] );
	}

	public function admin_init() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		if ( ! $screen_id ) {
			return;
		}
		foreach ( $this->pages as $page ) {
			$options = $page->get_registration_data();
			if ( isset( $options['parent_slug'] ) ) {
				$page_id = $options['parent_slug'] . '_page_' . $options['menu_slug'];
				if ( $screen_id === $page_id ) {
					$this->current_page = $page;
				}
			}
		}
	}

	public function register_admin_pages() {
		foreach ( $this->pages as $page ) {
			$options = wp_parse_args(
				$page->get_registration_data(),
				[
					'parent_slug' => ''
				]
			);

			if ( ! empty( $options['parent_slug'] ) ) {
				add_submenu_page(
					$options['parent_slug'],
					$options['page_title'],
					$options['menu_title'],
					$options['capability'],
					$options['menu_slug'],
					$options['callback']
				);
			}
		}
	}

	public function enqueue_admin_scripts() {
		if ( ! $this->current_page ) {
			return;
		}
		$handles = $this->current_page->get_script_handles();
		foreach ( $handles as $handle ) {
			wp_enqueue_script( $handle );
		}
	}

	public function enqueue_admin_data() {
		if ( ! $this->current_page ) {
			return;
		}
		$data = [
			$this->current_page->get_id() => $this->current_page->get_script_data()
		];
		$this->asset_data->print_data( 'wcStripeAdminSettings', $data );
	}
}