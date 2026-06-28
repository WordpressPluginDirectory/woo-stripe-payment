<?php

namespace PaymentPlugins\Stripe\Admin;

use PaymentPlugins\Stripe\Assets\AssetsApi;

class AbstractAdminPage {

	protected $assets;

	public function __construct( AssetsApi $assets ) {
		$this->assets = $assets;
	}

	public function get_id() {
		return '';
	}

	public function get_script_data() {
		return [];
	}

	/**
	 * @return array
	 */
	public function get_script_handles() {
		return [];
	}

	/**
	 * @return array
	 */
	public function get_registration_data() {
		return [];
	}
}