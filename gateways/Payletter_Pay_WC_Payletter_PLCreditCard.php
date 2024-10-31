<?php

namespace Onepix\Payletter\Gateways;

/**
 * Payletter_Pay_WC_Payletter_PLCreditCard
 * @link https://onepix.net/
 */
class Payletter_Pay_WC_Payletter_PLCreditCard extends Payletter_Pay_WC_Payletter_Gateway {

	var $gateway_id = 'payletter_plcreditcard';

	var $order_id;
	var $order;
	var $pginfo = 'PLCreditCard';

	public function __construct() {
		parent::__construct();

		$this->method_title       = 'Credit card Payletter (international payment, Amex)';
		$this->method_description = 'I use a Payletter credit card to pay overseas.';

		add_action( 'woocommerce_api_payletter_pay_wc_payletter_dialog_open_plcreditcard', [ $this, 'dialog_open' ] );
		add_action( 'woocommerce_api_payletter_pay_wc_payletter_callback_plcreditcard', [ $this, 'callback' ] );
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
				'default'     => 'Credit Card(Amex)',
			],
		] );
	}

	public function get_callback_url() {
		return site_url( '?wc-api=payletter_pay_wc_payletter_callback_plcreditcard&order_id=' . $this->order_id );
	}
}