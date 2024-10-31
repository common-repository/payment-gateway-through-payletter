<?php

namespace Onepix\Payletter\Gateways;

/**
 * Payletter_Pay_WC_Payletter_PLUnionPay
 * @link https://onepix.net/
 */
class Payletter_Pay_WC_Payletter_PLUnionPay extends Payletter_Pay_WC_Payletter_Gateway {

	var $gateway_id = 'payletter_plunionpay';

	var $order_id;
	var $order;
	var $pginfo = 'PLUnionPay';

	public function __construct() {
		parent::__construct();

		$this->method_title       = 'Payletter UnionPay (international payment)';
		$this->method_description = 'I use Payletter UnionPay overseas payment.';

		add_action( 'woocommerce_api_payletter_pay_wc_payletter_dialog_open_plunionpay', [ $this, 'dialog_open' ] );
		add_action( 'woocommerce_api_payletter_pay_wc_payletter_callback_plunionpay', [ $this, 'callback' ] );
	}

	public function get_gateway_id() {
		return $this->gateway_id;
	}

	public function init_form_fields() {
		parent::init_form_fields();

		$this->form_fields = array_merge( $this->form_fields, [
			'title' => [
				'title'       => 'Title',
				'type'        => 'text',
				'description' => 'The title of the payment method the user will see when checking out.',
				'default'     => 'UnionPay',
			],
		] );
	}

	public function get_callback_url() {
		return site_url( '?wc-api=payletter_pay_wc_payletter_callback_plunionpay&order_id=' . $this->order_id );
	}
}