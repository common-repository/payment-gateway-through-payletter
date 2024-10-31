<?php

namespace Onepix\Payletter\Gateways;

use Exception;
use stdClass;
use WC_Payment_Gateway;

/**
 * Payletter_Pay_WC_Payletter_Gateway
 * @link https://onepix.net/
 */
abstract class Payletter_Pay_WC_Payletter_Gateway extends WC_Payment_Gateway {
	/**
	 * @var string
	 */
	public $storeid;

	/**
	 * @var string
	 */
	public $store_hash;

	/**
	 * @var string
	 */
	public $request_url;

	/**
	 * @var string
	 */
	public $request_url_mobile;

	/**
	 * @var string
	 */
	public $cancel_url;

	/**
	 * @var bool
	 */
	public $test;

	abstract public function get_gateway_id();

	public function __construct() {
		$this->id         = $this->get_gateway_id();
		$this->icon       = '';
		$this->has_fields = false;
		$this->supports   = [ 'products', 'refunds' ];

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->test        = $this->get_option( 'test' ) === 'yes';

		if ( $this->test ) {
			$this->storeid            = $this->get_option( 'payletter_storeid_test' );
			$this->store_hash         = $this->get_option( 'payletter_hashkey_test' );
			$this->request_url        = 'https://dev-gpgclient.payletter.com/Web/Payment.aspx';
			$this->request_url_mobile = 'https://dev-gpgclient.payletter.com/Smart/Payment.aspx';
			$this->cancel_url         = 'https://dev-api.payletter.com/payment/';
		} else {
			$this->storeid            = $this->get_option( 'payletter_storeid' );
			$this->store_hash         = $this->get_option( 'payletter_hashkey' );
			$this->request_url        = 'https://psp.payletter.com/Smart/Payment.aspx';
			$this->request_url_mobile = 'https://psp.payletter.com/Smart/Payment.aspx';
			$this->cancel_url         = 'https://api.payletter.com/payment/';
		}

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'payment_scripts' ] );
	}

	public function is_test() {
		if ( $this->get_option( 'payletter_storeid' ) ) {
			return false;
		}

		return true;
	}

	public function is_mobile() {
		return wp_is_mobile();
	}

	public function payment_method_list() {
		return [
			'payletter_plcreditcard',
			'payletter_plcreditcardmpi',
			'payletter_plunionpay',
			'payletter_paypalexpresscheckout'
		];
	}

	public function init_form_fields() {
		parent::init_form_fields();

		$this->form_fields = array_merge( $this->form_fields, [
			'enabled'                => [
				'title'       => 'Enable/Disable',
				'label'       => 'activate',
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			],
			'title'                  => [
				'title'       => 'title',
				'type'        => 'text',
				'description' => 'The title of the payment method the user will see when checking out.',
				'default'     => '',
			],
			'description'            => [
				'title'       => 'Explanation',
				'type'        => 'text',
				'description' => 'A description of the payment method the user will see when checking out.',
				'default'     => '',
			],
			'payletter_html'         => [
				'title'       => 'Sign up for Payletter',
				'type'        => 'payletter_html',
				'description' => '',
			],
			'payletter_storeid'      => [
				'title'   => 'store id',
				'type'    => 'text',
				'default' => '',
			],
			'payletter_hashkey'      => [
				'title'   => 'store hashkey',
				'type'    => 'text',
				'default' => '',
			],
			'test'                   => [
				'title'       => 'Test mode',
				'label'       => 'activate',
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			],
			'payletter_storeid_test' => [
				'title'   => 'test store id',
				'type'    => 'text',
				'default' => '',
			],
			'payletter_hashkey_test' => [
				'title'   => 'test store hashkey',
				'type'    => 'text',
				'default' => '',
			],
		] );
	}

	public function get_refund_bank_list() {
		$refund_bank_list                          = [];
		$refund_bank_list['KB Kookmin Bank']       = new stdClass();
		$refund_bank_list['KB Kookmin Bank']->name = 'KB Kookmin Bank';
		$refund_bank_list['KB Kookmin Bank']->code = '004';

		$refund_bank_list['SC First Bank']       = new stdClass();
		$refund_bank_list['SC First Bank']->name = 'SC First Bank';
		$refund_bank_list['SC First Bank']->code = '023';

		$refund_bank_list['Kyongnam Bank']       = new stdClass();
		$refund_bank_list['Kyongnam Bank']->name = 'Kyongnam Bank';
		$refund_bank_list['Kyongnam Bank']->code = '039';

		$refund_bank_list['Gwangju Bank']       = new stdClass();
		$refund_bank_list['Gwangju Bank']->name = 'Gwangju Bank';
		$refund_bank_list['Gwangju Bank']->code = '034';

		$refund_bank_list['Industrial BAnk']       = new stdClass();
		$refund_bank_list['Industrial BAnk']->name = 'Industrial BAnk';
		$refund_bank_list['Industrial BAnk']->code = '003';

		$refund_bank_list['NH']       = new stdClass();
		$refund_bank_list['NH']->name = 'NH';
		$refund_bank_list['NH']->code = '011';

		$refund_bank_list['Daegu Bank']       = new stdClass();
		$refund_bank_list['Daegu Bank']->name = 'Daegu Bank';
		$refund_bank_list['Daegu Bank']->code = '031';

		$refund_bank_list['Busan Bank']       = new stdClass();
		$refund_bank_list['Busan Bank']->name = 'Busan Bank';
		$refund_bank_list['Busan Bank']->code = '032';

		$refund_bank_list['Korea Development Bank']       = new stdClass();
		$refund_bank_list['Korea Development Bank']->name = 'Korea Development Bank';
		$refund_bank_list['Korea Development Bank']->code = '002';

		$refund_bank_list['Saemaul Geumgo']       = new stdClass();
		$refund_bank_list['Saemaul Geumgo']->name = 'Saemaul Geumgo';
		$refund_bank_list['Saemaul Geumgo']->code = '045';

		$refund_bank_list['fisheries']       = new stdClass();
		$refund_bank_list['fisheries']->name = 'fisheries';
		$refund_bank_list['fisheries']->code = '007';

		$refund_bank_list['Shinhan Bank']       = new stdClass();
		$refund_bank_list['Shinhan Bank']->name = 'Shinhan Bank';
		$refund_bank_list['Shinhan Bank']->code = '088';

		$refund_bank_list['credit union']       = new stdClass();
		$refund_bank_list['credit union']->name = 'credit union';
		$refund_bank_list['credit union']->code = '048';

		$refund_bank_list['Exchange Bank']       = new stdClass();
		$refund_bank_list['Exchange Bank']->name = 'Exchange Bank';
		$refund_bank_list['Exchange Bank']->code = '005';

		$refund_bank_list['Our bank']       = new stdClass();
		$refund_bank_list['Our bank']->name = 'Our bank';
		$refund_bank_list['Our bank']->code = '020';

		$refund_bank_list['post office']       = new stdClass();
		$refund_bank_list['post office']->name = 'Post office';
		$refund_bank_list['post office']->code = '071';

		$refund_bank_list['Jeonbuk Bank']       = new stdClass();
		$refund_bank_list['Jeonbuk Bank']->name = 'Jeonbuk Bank';
		$refund_bank_list['Jeonbuk Bank']->code = '037';

		$refund_bank_list['Kakao Bank']       = new stdClass();
		$refund_bank_list['Kakao Bank']->name = 'Kakao Bank';
		$refund_bank_list['Kakao Bank']->code = '090';

		$refund_bank_list['K bank']       = new stdClass();
		$refund_bank_list['K bank']->name = 'K bank';
		$refund_bank_list['K bank']->code = '089';

		$refund_bank_list['Hana Bank']       = new stdClass();
		$refund_bank_list['Hana Bank']->name = 'Hana Bank';
		$refund_bank_list['Hana Bank']->code = '081';

		$refund_bank_list['Citi Bank']       = new stdClass();
		$refund_bank_list['Citi Bank']->name = 'Citi Bank';
		$refund_bank_list['Citi Bank']->code = '027';

		return $refund_bank_list;
	}

	public function get_refund_bank_code( $name ) {
		$refund_bank_list = $this->get_refund_bank_list();
		if ( isset( $refund_bank_list[ $name ] ) ) {
			return $refund_bank_list[ $name ]->code;
		}

		return '';
	}

	/**
	 * Create Payletter_html field
	 *
	 * @param  string  $key
	 * @param  array  $data
	 *
	 * @return string
	 */
	public function generate_Payletter_html_html( $key, $data ) {
		ob_start();
		?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label><?php echo wp_kses_post( $data['title'] ) ?></label>
            </th>
            <td class="forminp">
                <fieldset>
                    <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ) ?></span>
                    </legend>
                    <p class="description">You can make a test payment without entering the information below.</p>
                    <p class="description">Only actual payment is possible with an existing card number, and if you want
                        to make a test payment, you can test by entering a non-existent card number.</p>
                </fieldset>
            </td>
        </tr>
		<?php
		return ob_get_clean();
	}

	public function payment_scripts() {
		$gateways = WC()->payment_gateways->get_available_payment_gateways();
		$gateways = array_keys( $gateways );

		if ( array_intersect( $this->payment_method_list(), $gateways ) ) {
			wp_register_script(
				'payletter-pay-wc-payletter',
				PAYLETTER_PAY_WC_URL . '/gateways/js/script.js',
				[ 'jquery' ],
				PAYLETTER_PAY_WC_VERSION
			);

			wp_localize_script( 'payletter-pay-wc-payletter', 'payletter_pay_wc_payletter_args', [
				'site_url'                                        => site_url( '/', 'relative' ),
				'is_mobile'                                       => wp_is_mobile(),
				'method_list'                                     => $this->payment_method_list(),
				'open_dialog_payletter_plcreditcard_url'          => site_url( '?wc-api=payletter_pay_wc_payletter_dialog_open_plcreditcard' ),
				'open_dialog_payletter_plcreditcardmpi_url'       => site_url( '?wc-api=payletter_pay_wc_payletter_dialog_open_plcreditcardmpi' ),
				'open_dialog_payletter_plunionpay_url'            => site_url( '?wc-api=payletter_pay_wc_payletter_dialog_open_plunionpay' ),
				'open_dialog_payletter_paypalexpresscheckout_url' => site_url( '?wc-api=payletter_pay_wc_payletter_dialog_open_paypalexpresscheckout' ),
				'open_dialog_pay_success_url'                     => $this->get_return_url(),
			] );

			wp_enqueue_script( 'payletter-pay-wc-payletter' );
		}
	}

	public function process_payment( $order_id ) {
		return [ 'result' => 'success', 'order_id' => $order_id ];
	}

	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$this->order = wc_get_order( $order_id );

		if ( ! $this->order ) {
			return false;
		}

		if ( ! $this->can_refund_order( $this->order ) ) {
			return false;
		}

		if ( ! in_array( $this->order->get_payment_method(), $this->payment_method_list() ) ) {
			return false;
		}

		$paytoken     = $this->order->get_meta( 'paytoken' );
		$currency     = $this->order->get_currency();
		$pginfo       = $this->order->get_meta( 'pginfo' );

		if ( ! $paytoken || ! $pginfo ) {
			return false;
		}

		$result = $this->cancel( [
			'order_id' => $order_id,
			'paytoken' => $paytoken,
			'currency' => $currency,
			'amount'   => $amount,
			'pginfo'   => $pginfo,
		] );

		if ( $result->status == 'cancelled' ) {
			$total   = number_format( $amount );
			$message = sprintf( '%s%s has been refunded.', $total, $currency );
			$this->order->add_order_note( $message );

			return true;
		}

		$message = sprintf( 'Refund Failed: %s', $result->error_message );
		$this->order->add_order_note( $message );
		throw new Exception( $message );
	}

	public function cancel( $args ) {
		$order_id = ( isset( $args['order_id'] ) && $args['order_id'] ) ? sanitize_text_field( $args['order_id'] ) : '';
		$paytoken = ( isset( $args['paytoken'] ) && $args['paytoken'] ) ? sanitize_text_field( $args['paytoken'] ) : '';
		$currency = ( isset( $args['currency'] ) && $args['currency'] ) ? sanitize_text_field( $args['currency'] ) : '';
		$amount   = ( isset( $args['amount'] ) && $args['amount'] ) ? sanitize_text_field( $args['amount'] ) : 0;
		$pginfo   = ( isset( $args['pginfo'] ) && $args['pginfo'] ) ? sanitize_text_field( $args['pginfo'] ) : '';

		$storeid         = $this->storeid;
		$store_hash      = $this->store_hash;
		$uppercase       = 'POST';
		$lowercase       = mb_strtolower( urlencode( $this->cancel_url . $pginfo . '/refund' ) );
		$time            = current_time( time() );
		$nonce           = hash( 'sha256', "$order_id$time" );
		$request_content = "storeid=$storeid&paytoken=$paytoken&currency=$currency&amount=$amount&pginfo=$pginfo";
		$signature       = hash( 'sha256', "$storeid$store_hash$uppercase$lowercase$time$nonce$request_content" );

		$args            = [];
		$args['method']  = 'POST';
		$args['timeout'] = '15';
		$args['headers'] = [
			'Content-type'  => 'application/x-www-form-urlencoded;charset=euc-kr',
			'Authorization' => "POQAPI $storeid:$signature:$nonce:$time",
		];
		$args['body']    = [
			'storeid'  => $storeid,
			'paytoken' => $paytoken,
			'currency' => $currency,
			'amount'   => $amount,
			'pginfo'   => $pginfo,
		];

		$response = wp_remote_request( $this->cancel_url . $pginfo . '/refund', $args );

		// Return data initialization
		$result                = new stdClass();
		$result->status        = 'ready';
		$result->message       = '';
		$result->error_message = '';

		if ( ! is_wp_error( $response ) && ( $response['response']['code'] == 200 || $response['response']['code'] == 201 ) ) {
			$body = wp_remote_retrieve_body( $response );
			if ( $body ) {
				$body = json_decode( $body );

				if ( isset( $body->data ) ) {
					$result->status  = 'cancelled';
					$result->message = 'The payment has been canceled.';

					return $result;
				} else {
					$result->status        = 'failed';
					$result->error_message = $body->message;

					return $result;
				}
			}
		}

		$result->status        = 'failed';
		$result->error_message = 'Failed to cancel payment.';

		return $result;
	}

	public function dialog_open() {
		$this->order_id = isset( $_REQUEST['order_id'] ) ? intval( $_REQUEST['order_id'] ) : '';
		$this->order    = wc_get_order( $this->order_id );
		$api            = $this;

		$this->log( [
			'source' => 'Payletter_Pay_WC_Payletter_Gateway::dialog_open',
			'url'    => $this->request_url,
			'data'   => $api->get_init_values()
		] );

		if ( $this->is_mobile() ) {
			include PAYLETTER_PAY_WC_DIR . '/gateways/includes/dialog-mobile.php';
		} else {
			include PAYLETTER_PAY_WC_DIR . '/gateways/includes/dialog.php';
		}
		exit;
	}

	public function callback() {
		$this->order_id = isset( $_REQUEST['order_id'] ) ? intval( $_REQUEST['order_id'] ) : '';
		$this->order    = wc_get_order( $this->order_id );

		if ( empty( $_POST ) ) {
			$_POST = json_decode( file_get_contents( "php://input" ), true );
		}

		$this->log( [
			'source'   => 'Payletter_Pay_WC_Payletter_Gateway::callback',
			'order_id' => $this->order->get_id(),
			'$_GET'    => $_GET,
			'$_POST'   => $_POST,
		] );

		if ( ! isset( $_POST['notifytype'] ) ) {
			$this->log( [
				'source'   => 'Payletter_Pay_WC_Payletter_Gateway::callback:error_1',
				'order_id' => $this->order->get_id(),
                'error' => 'notifytype is null'
			] );

			die( '<RESULT>FAIL</RESULT>' );
		}

		$payerid      = isset( $_POST['payerid'] ) ? sanitize_text_field( $_POST['payerid'] ) : '';
		$servicename  = isset( $_POST['servicename'] ) ? sanitize_text_field( $_POST['servicename'] ) : '';
		$payinfo      = isset( $_POST['payinfo'] ) ? sanitize_text_field( $_POST['payinfo'] ) : '';
		$timestamp    = isset( $_POST['timestamp'] ) ? sanitize_text_field( $_POST['timestamp'] ) : '';
		$hash         = isset( $_POST['hash'] ) ? sanitize_text_field( $_POST['hash'] ) : '';
		$notifytype   = isset( $_POST['notifytype'] ) ? sanitize_text_field( $_POST['notifytype'] ) : '';
		$paytoken     = isset( $_POST['paytoken'] ) ? sanitize_text_field( $_POST['paytoken'] ) : '';
		$trantime     = isset( $_POST['trantime'] ) ? sanitize_text_field( $_POST['trantime'] ) : '';
		$retcode      = isset( $_POST['retcode'] ) ? sanitize_text_field( $_POST['retcode'] ) : '';
		$retmsg       = isset( $_POST['retmsg'] ) ? sanitize_text_field( $_POST['retmsg'] ) : '';
		$payamt       = isset( $_POST['payamt'] ) ? sanitize_text_field( $_POST['payamt'] ) : '';
		$pginfo       = isset( $_POST['pginfo'] ) ? sanitize_text_field( $_POST['pginfo'] ) : '';
		$storeorderno = isset( $_POST['storeorderno'] ) ? sanitize_text_field( $_POST['storeorderno'] ) : '';
		$storeid      = isset( $_POST['storeid'] ) ? sanitize_text_field( $_POST['storeid'] ) : '';
		$custom       = isset( $_POST['custom'] ) ? sanitize_text_field( $_POST['custom'] ) : '';
		$currency     = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : '';

		$verification_hash = hash(
				'sha256',
				"{$storeid}{$currency}{$storeorderno}{$payamt}{$payerid}{$timestamp}{$this->store_hash}"
			);

		if ( $retcode != 0 || $hash !== $verification_hash ) {
			$this->log( [
				'source'   => 'Payletter_Pay_WC_Payletter_Gateway::callback:error_2',
				'order_id' => $this->order->get_id(),
				'$retcode' => $retcode,
                '$hash' => $hash,
				'$verification_hash' => $verification_hash,
			] );

			die( '<RESULT>FAIL</RESULT>' );
		}

		if ( $notifytype == 1 ) {
			$this->order->payment_complete();
			wc_reduce_stock_levels( $this->order_id );
			WC()->cart->empty_cart();

			add_post_meta( $this->order_id, 'payerid', $payerid, true );
			add_post_meta( $this->order_id, 'servicename', $servicename, true );
			add_post_meta( $this->order_id, 'custom', $custom, true );
			add_post_meta( $this->order_id, 'payinfo', $payinfo, true );
			add_post_meta( $this->order_id, 'timestamp', $timestamp, true );
			add_post_meta( $this->order_id, 'hash', $hash, true );
			add_post_meta( $this->order_id, 'notifytype', $notifytype, true );
			add_post_meta( $this->order_id, 'paytoken', $paytoken, true );
			add_post_meta( $this->order_id, 'trantime', $trantime, true );
			add_post_meta( $this->order_id, 'retcode', $retcode, true );
			add_post_meta( $this->order_id, 'retmsg', $retmsg, true );
			add_post_meta( $this->order_id, 'payamt', $payamt, true );
			add_post_meta( $this->order_id, 'pginfo', $pginfo, true );
			add_post_meta( $this->order_id, 'storeorderno', $storeorderno, true );
			add_post_meta( $this->order_id, 'storeid', $storeid, true );
		} else {
			update_post_meta( $this->order_id, 'notifytype', $notifytype );
		}

		die( '<RESULT>OK</RESULT>' );
	}


	public function get_init_values() {
		global $payletter_pay_wc_transient;

		$order_name  = '';
		$items_count = 0;

		foreach ( $this->order->get_items() as $item ) {
			$product       = wc_get_product( $item['product_id'] );
			$product_title = $product->get_title();
			$quantity      = $item['quantity'];

			if ( $quantity > 1 ) {
				$product_title .= ' x ' . $quantity;
			}

			if ( ! $order_name ) {
				$order_name = preg_replace( "/\'|\"|\||\,|\&|\;/", "", $product_title );
			}

			$items_count ++;
		}

		if ( $items_count > 1 ) {
			$order_name = sprintf( '%s et al. %d case', $order_name, $items_count );
		}

		$order_buyer_name  = trim( $this->order->get_billing_last_name() . $this->order->get_billing_first_name() );
		$order_buyer_tel   = $this->order->get_billing_phone();
		$order_buyer_email = $this->order->get_billing_email();

		$storeid      = $this->storeid;
		$storeorderno = $this->order->get_order_number();
		$currency     = get_woocommerce_currency();
		$payamt       = $this->order->get_total();
		$payerid      = get_userdata( $this->order->get_customer_id() )->user_login;
		$payeremail   = $order_buyer_email ? sanitize_text_field( $order_buyer_email ) : '';
		$callbackurl  = $this->get_callback_url();
		$time         = current_time( time() );
		$store_hash   = $this->store_hash;
		$hash         = hash( 'sha256', "{$storeid}{$currency}{$storeorderno}{$payamt}{$payerid}{$time}{$store_hash}" );
		$pginfo       = $this->pginfo;
		$payername    = $order_buyer_name ? sanitize_text_field( $order_buyer_name ) : '';
		$payerphone   = $order_buyer_tel ? sanitize_text_field( $order_buyer_tel ) : '';
		$backurl      = wc_get_checkout_url();

		$values = [
			'storeid'      => $storeid,
			'storeorderno' => $storeorderno,
			'currency'     => $currency,
			'payamt'       => $payamt,
			'payerid'      => $payerid,
			'servicename'  => $order_name,
			'payeremail'   => $payeremail,
			'returnurl'    => $this->get_return_url( $this->order ),
			'timestamp'    => $time,
			'hash'         => $hash,
			'notiurl'      => $callbackurl,
			'pginfo'       => $pginfo,
			'custom'       => $payername . ' ' . $payerphone,
			'backurl'      => $backurl
		];

		$payletter_pay_wc_transient->set_value( $this->order->get_order_number(), $_POST );

		return $values;
	}

	public function get_callback_url() {
		return '';
	}

	function log( $data ) {
		wc_get_logger()->debug( print_r( $data, true ), [ 'source' => 'payletter' ] );
	}

}
