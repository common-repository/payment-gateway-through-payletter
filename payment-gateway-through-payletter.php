<?php
/**
 * Plugin Name:       Payment Gateway through Payletter
 * Description:       Payletter is an electronic payment service that helps you to make payments safely and conveniently wherever goods and services are sold on the Internet and customers pay using a foreign brand of credit card, easy payment, prepaid card. Payletter provides service to 25 countries around the world, including North America, China, Europe, Japan, and Southeast Asia.
 * Version:           1.0.2
 * Requires at least: 5.5
 * Requires PHP:      7.4
 * Author:            OnePix
 * Author URI:        https://onepix.net
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       payment-gateway-through-payletter
 */

use Onepix\Payletter\Classes\Payletter_Pay_WC_Custom_Button;
use Onepix\Payletter\Classes\Payletter_Pay_WC_Delivery;
use Onepix\Payletter\Classes\Payletter_Pay_WC_Payment_Settings;
use Onepix\Payletter\Classes\Payletter_Pay_WC_Request_Exchange;
use Onepix\Payletter\Classes\Payletter_Pay_WC_Request_Purchase_Confirmation;
use Onepix\Payletter\Classes\Payletter_Pay_WC_Request_Refund;
use Onepix\Payletter\Classes\Payletter_Pay_WC_Shortcode;
use Onepix\Payletter\Classes\Payletter_Pay_WC_Transient;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PAYLETTER_PAY_WC_VERSION', '1.0.2' );
define( 'PAYLETTER_PAY_WC_DIR', dirname( __FILE__ ) );
define( 'PAYLETTER_PAY_WC_URL', plugins_url( '', __FILE__ ) );

require_once 'vendor/autoload.php';

add_action( 'plugins_loaded', 'payletter_pay_woocommerce_init_gateway_class' );
function payletter_pay_woocommerce_init_gateway_class() {
	global $payletter_pay_wc_transient;
	$payletter_pay_wc_transient = new Payletter_Pay_WC_Transient();

	new Payletter_Pay_WC_Delivery();
	new Payletter_Pay_WC_Payment_Settings();

	if ( class_exists( 'WC_Payment_Gateway' ) ) {
		add_action( 'init', 'payletter_pay_woocommerce_init' );
		add_action( 'init', 'payletter_pay_woocommerce_register_post_status' );
		add_filter( 'wc_order_statuses', 'payletter_pay_woocommerce_wc_order_statuses', 10, 1 );
		add_filter( 'woocommerce_payment_gateways', 'payletter_pay_woocommerce_payment_gateways' );
		add_action( 'woocommerce_pay_order_after_submit', 'payletter_pay_woocommerce_pay_order_after_submit' );
		add_action( 'wp_ajax_payletter_pay_payment', 'payletter_pay_payment' );
		add_action( 'wp_ajax_nopriv_payletter_pay_payment', 'payletter_pay_payment' );
		add_action( 'woocommerce_order_status_processing_to_cancelled', 'payletter_pay_processing_to_cancelled', 10, 1 );
	}
}

function payletter_pay_woocommerce_init() {
	new Payletter_Pay_WC_Shortcode();
	new Payletter_Pay_WC_Custom_Button();
	new Payletter_Pay_WC_Request_Purchase_Confirmation();

	new Payletter_Pay_WC_Request_Refund();
	new Payletter_Pay_WC_Request_Exchange();
}

/**
 * WooCommerce status registration
 */
function payletter_pay_woocommerce_register_post_status() {
	register_post_status( 'wc-pre-shipping', [
		'label'                     => 'Preparing for delivery',
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Preparing for delivery <span class="count">(%s)</span>', 'Preparing for delivery <span class="count">(%s)</span>' )
	] );

	register_post_status( 'wc-shipping', [
		'label'                     => 'Shipping',
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Shipping <span class="count">(%s)</span>', 'Shipping <span class="count">(%s)</span>' )
	] );
}

/**
 * Add WooCommerce Status
 */
function payletter_pay_woocommerce_wc_order_statuses( $order_statuses ) {
	$new_order_statuses = [];

	foreach ( $order_statuses as $key => $status ) {
		$new_order_statuses[ $key ] = $status;

		if ( ! isset( $order_statuses['wc-pre-shipping'] ) && $key == 'wc-on-hold' ) {
			$new_order_statuses['wc-pre-shipping'] = 'Preparing for delivery';
		}

		if ( ! isset( $order_statuses['wc-shipping'] ) && $key == 'wc-on-hold' ) {
			$new_order_statuses['wc-shipping'] = 'Shipping';
		}
	}

	return $new_order_statuses;
}

function payletter_pay_woocommerce_payment_gateways( $gateways ) {
	$gateways[] = 'Onepix\Payletter\Gateways\Payletter_Pay_WC_Payletter_PLCreditCard';
	$gateways[] = 'Onepix\Payletter\Gateways\Payletter_Pay_WC_Payletter_PLCreditCardMpi';
	$gateways[] = 'Onepix\Payletter\Gateways\Payletter_Pay_WC_Payletter_PLUnionPay';
	$gateways[] = 'Onepix\Payletter\Gateways\Payletter_Pay_WC_Payletter_PaypalExpressCheckout';

	return $gateways;
}

/**
 * A tag to reset order information at checkout on the account order page in WooCommerce.
 */
function payletter_pay_woocommerce_pay_order_after_submit() {
	?>
    <input type="hidden" name="action" value="payletter_pay_payment">
    <input type="hidden" name="key" value="<?php echo esc_attr( payletter_pay_get_order_key() ) ?>">
	<?php
}

/**
 * Reset order information at checkout in WooCommerce My Account Orders page
 */
function payletter_pay_payment() {
	$payment_method_id = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';
	$order_key         = payletter_pay_get_order_key();
	$args              = [ 'result' => 'failure' ];

	if ( $order_key ) {
		$order_id = wc_get_order_id_by_order_key( $order_key );
		if ( $order_id ) {
			$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
			$payment_method     = isset( $available_gateways[ $payment_method_id ] ) ? $available_gateways[ $payment_method_id ] : false;

			if ( $payment_method ) {
				$order = wc_get_order( $order_id );
				$order->set_payment_method( $payment_method->gateway_id );
				$order->set_payment_method_title( $payment_method->title );
				$order->save();

				$args = [ 'result' => 'success', 'order_id' => $order_id ];
			}
		}
	}

	wp_send_json( $args );
	exit;
}

function payletter_pay_get_order_key() {
	$order_key = isset( $_REQUEST['key'] ) ? sanitize_text_field( $_REQUEST['key'] ) : '';

	return $order_key;
}

function payletter_pay_processing_to_cancelled( $order_id ) {
	$order   = wc_get_order( $order_id );
	$gateway = wc_get_payment_gateway_by_order( $order );

	$gateway_name = strtolower( get_class( $gateway ) );

	if ( strpos( $gateway_name, 'payletter' ) === 0 ) {
		if ( $gateway->process_refund( $order_id, $order->get_total() ) ) {
			$order->add_order_note( 'Canceled by buyer.' );
			$order->update_status( 'refunded' );
		}
	}
}

function payletter_pay_delivery_corp_name_list() {
	$payletter_pay_delivery_corp_name_list = [];

	$payletter_pay_delivery_corp_name_list['CJEX'] = [
		'name'         => 'CJ Logistics',
		'tracking_url' => 'https://www.doortodoor.co.kr/parcel/doortodoor.do?fsp_action=PARC_ACT_002&fsp_cmd=retrieveInvNoACT&invc_no=%s',
	];
	$payletter_pay_delivery_corp_name_list['LTEX'] = [
		'name'         => 'Lotte Global Logis',
		'tracking_url' => 'https://www.lotteglogis.com/home/reservation/tracking/linkView?InvNo=%s',
	];
	$payletter_pay_delivery_corp_name_list['HJEX'] = [
		'name'         => 'Hanjin Courier',
		'tracking_url' => 'http://www.hanjin.co.kr/Delivery_html/inquiry/result_waybill.jsp?wbl_num=%s',
	];
	$payletter_pay_delivery_corp_name_list['POEX'] = [
		'name'         => 'Post office',
		'tracking_url' => 'https://service.epost.go.kr/trace.RetrieveDomRigiTraceList.comm?sid1=%s',
	];
	$payletter_pay_delivery_corp_name_list['LJEX'] = [
		'name'         => 'Rosen Courier',
		'tracking_url' => 'http://www.ilogen.com/iLOGEN.Web.New/TRACE/TraceView.aspx?gubun=slipno&slipno=%s',
	];
	$payletter_pay_delivery_corp_name_list['HNEX'] = [
		'name'         => 'Hanaro Courier',
		'tracking_url' => 'http://www.hanarologis.com/branch/chase/listbody.html?a_gb=center&a_cd=4&a_item=0&fr_slipno=%s',
	];
	$payletter_pay_delivery_corp_name_list['YYEX'] = [
		'name'         => 'Ilyang Logis',
		'tracking_url' => 'http://www.ilyanglogis.com/functionality/tracking_result.asp?hawb_no=%s',
	];
	$payletter_pay_delivery_corp_name_list['KDEX'] = [
		'name'         => 'Kyungdong Courier',
		'tracking_url' => 'http://www.kdexp.com/sub4_1.asp?stype=1&p_item=%s',
	];
	$payletter_pay_delivery_corp_name_list['DSEX'] = [
		'name'         => 'Instead of courier',
		'tracking_url' => 'http://home.daesinlogistics.co.kr/daesin/jsp/d_freight_chase/d_general_process2.jsp?%s',
	];
	$payletter_pay_delivery_corp_name_list['CUEX'] = [
		'name'         => 'CU convenience store delivery',
		'tracking_url' => 'https://www.cupost.co.kr/postbox/delivery/localResult.cupost?invoice_no=%s',
	];
	$payletter_pay_delivery_corp_name_list['GPEX'] = [
		'name'         => 'GS Postbox Courier',
		'tracking_url' => 'https://www.cvsnet.co.kr/invoice/tracking.do?invoice_no=%s',
	];

	$payletter_pay_delivery_corp_name_list = apply_filters( 'payletter_pay_delivery_corp_name_list', $payletter_pay_delivery_corp_name_list );

	return $payletter_pay_delivery_corp_name_list;
}
