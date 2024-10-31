<?php

namespace Onepix\Payletter\Gateways;

/**
 * Payletter_Pay_WC_Payletter_PaypalExpressCheckout
 * @link https://onepix.net/
 */
class Payletter_Pay_WC_Payletter_PaypalExpressCheckout extends Payletter_Pay_WC_Payletter_Gateway {

	var $gateway_id = 'payletter_paypalexpresscheckout';

	var $order_id;
	var $order;
	var $pginfo = 'PayPalExpressCheckout';

	public function __construct() {
		parent::__construct();

		$this->method_title       = 'Payletter Paypal (overseas payment)';
		$this->method_description = 'I use PayPal overseas payment.';

		add_action( 'woocommerce_api_payletter_pay_wc_payletter_dialog_open_paypalexpresscheckout', [
			$this,
			'dialog_open'
		] );
		add_action( 'woocommerce_api_payletter_pay_wc_payletter_callback_paypalexpresscheckout', [
			$this,
			'callback'
		] );
		add_filter( 'woocommerce_gateway_title', [ $this, 'woocommerce_gateway_title_paypal' ], 10, 2 );
	}

	public function woocommerce_gateway_title_paypal( $title, $id ) {
		if ( $id == 'payletter_paypalexpresscheckout' ) {
			if ( is_admin() ) {
				$title = 'PayPal';
			} else {
				$title = '<img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/PP_logo_h_100x26.png" alt="PayPal" style="margin: 0;">';
			}
		}

		return $title;
	}

	public function get_gateway_id() {
		return $this->gateway_id;
	}

	public function init_form_fields() {
		parent::init_form_fields();

		$this->form_fields = array_merge( $this->form_fields, [
			'title' => [
				'title'       => 'title',
				'type'        => 'text',
				'description' => 'The title of the payment method the user will see when checking out.',
				'default'     => 'Paypal',
			],
		] );
	}

	public function get_callback_url() {
		return site_url( '?wc-api=payletter_pay_wc_payletter_callback_paypalexpresscheckout&order_id=' . $this->order_id );
	}
}