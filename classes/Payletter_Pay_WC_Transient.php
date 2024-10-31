<?php

namespace Onepix\Payletter\Classes;

/**
 * Payletter_Pay_WC_Transient
 * @link https://www.payletter.com/
 */
class Payletter_Pay_WC_Transient {

	var $data = [];

	public function __construct() {
		$this->data = get_option( 'payletter_pay_wc_transient' ) ?: [];

		foreach ( $this->data as $key => $item ) {
			if ( $item['timeout'] < time() ) {
				unset( $this->data[ $key ] );
			}
		}

		update_option( 'payletter_pay_wc_transient', $this->data );
	}

	public function set_value( $key, $value, $expiration = 3600 ) {
		$key = sanitize_key( $key );

		if ( $key ) {
			$this->data[ $key ] = [
				'value'   => $value,
				'timeout' => time() + $expiration,
			];

			update_option( 'payletter_pay_wc_transient', $this->data );
		}
	}

	public function get_value( $key ) {
		$key = sanitize_key( $key );

		if ( $key && isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ]['value'];
		}

		return false;
	}
}