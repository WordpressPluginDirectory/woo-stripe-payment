<?php

namespace PaymentPlugins\Stripe\Assets;

class AssetDataApi {

	private $data = [];

	public function add( $key, $data ) {
		$this->data[ $key ] = $data;
	}

	public function get( $key, $default = null ) {
		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : $default;
	}

	public function remove( $key ) {
		unset( $this->data[ $key ] );
	}

	public function get_data() {
		return $this->data;
	}

	public function has_data() {
		return ! empty( $this->data );
	}

	public function print_data( $name, $data ) {
		// JSON_HEX_TAG escapes < and > in string values so a value containing "</script>"
		// can't prematurely close this script tag at the HTML parser level.
		$data = wp_json_encode( $data, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES );
		echo "<script id=\"$name\">
				window['$name'] = $data;
		</script>";
	}

}