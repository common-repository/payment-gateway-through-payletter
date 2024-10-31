<?php

namespace Onepix\Payletter\Classes;

/**
 * Payletter_Pay_WC_Shortcode
 * @link https://www.payletter.com/
 */
class Payletter_Pay_WC_Shortcode {

	public function __construct() {
		add_shortcode( 'cpwc_paid_content', [ $this, 'cpwc_paid_content' ] );
		add_shortcode( 'cpwc_unpaid_content', [ $this, 'cpwc_unpaid_content' ] );
	}

	/**
	 * What you see after purchase
	 * @return string
	 */
	public function cpwc_paid_content( $atts, $content = '' ) {
		if ( isset( $atts['product_id'] ) && $atts['product_id'] ) {
			if ( $this->bought_product_check_by_id( $atts['product_id'] ) ) { //[cpwc_paid_content product_id="00"](Specific products) Contents displayed after purchase[/cpwc_paid_content]
				return do_shortcode( $content );
			}
		} else {
			if ( $this->bought_product_check() ) { // [cpwc_paid_content]What you see after purchase[/cpwc_paid_content]
				return do_shortcode( $content );
			}
		}

		return '';
	}

	/**
	 * What you see before purchase
	 * @return string
	 */
	public function cpwc_unpaid_content( $atts, $content = '' ) {
		if ( isset( $atts['product_id'] ) && $atts['product_id'] ) {
			if ( ! $this->bought_product_check_by_id( $atts['product_id'] ) ) { //[cpwc_unpaid_content product_id="00"](Specific products) Contents displayed before purchase[/cpwc_unpaid_content]
				return do_shortcode( $content );
			}
		} else {
			if ( ! $this->bought_product_check() ) { // [cpwc_unpaid_content]What you see before purchase[/cpwc_unpaid_content]
				return do_shortcode( $content );
			}
		}

		return '';
	}

	/**
	 * Check whether you have purchased WooCommerce products
	 * @return boolean
	 */
	public function bought_product_check() {
		global $product;

		$is_buyer = false;

		if ( $product ) {
			$product_id     = $product->get_id();
			$user_id        = get_current_user_id();
			$customer_email = wp_get_current_user()->data->user_email;

			if ( function_exists( 'wc_customer_bought_product' ) ) {
				$is_buyer = wc_customer_bought_product( $customer_email, $user_id, $product_id );
			}
		}

		return $is_buyer;
	}

	/**
	 * Check whether you have purchased WooCommerce products by product id
	 * @return boolean
	 */
	function bought_product_check_by_id( $id ) {
		global $product;

		$is_buyer = false;

		if ( $product ) {
			$product_id     = $id; //$product->get_id();
			$user_id        = get_current_user_id();
			$customer_email = wp_get_current_user()->data->user_email;

			if ( function_exists( 'wc_customer_bought_product' ) ) {
				$is_buyer = wc_customer_bought_product( $customer_email, $user_id, $product_id );
			}
		}

		return $is_buyer;
	}
}