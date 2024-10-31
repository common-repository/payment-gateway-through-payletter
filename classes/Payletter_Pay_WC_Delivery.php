<?php

namespace Onepix\Payletter\Classes;

use WC_Order;

/**
 * Payletter_Pay_WC_Delivery
 * @link https://www.payletter.com/
 */
class Payletter_Pay_WC_Delivery {

	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_delivery_order_meta_box' ], 10 );
		add_action( 'save_post_shop_order', [ $this, 'save_delivery_order_meta_box' ], 10 );
		add_action( 'woocommerce_order_details_after_order_table', [ $this, 'get_delivery_display' ], 10, 1 );
	}

	/**
	 * Add shipping information registration meta box to order edit page.
	 */
	public function add_delivery_order_meta_box() {
		add_meta_box( uniqid(), 'Shipping information registration', [
			$this,
			'payletter_pay_delivery_meta_box_callback'
		], 'shop_order', 'side', 'high' );
	}

	/**
	 * Prints the shipping information registration meta box on the order modification page.
	 *
	 * @param object $post
	 */
	public function payletter_pay_delivery_meta_box_callback( $post ) {
		$order_id           = intval( $post->ID );
		$pay_pg             = get_post_meta( $order_id, 'pay_pg', true );
		$is_escrow          = get_post_meta( $order_id, '_pay_pg_escrow', true );
		$delivery_corp_name = get_post_meta( $order_id, 'payletter_pay_delivery_corp_name', true );
		$delivery_corp_name = $delivery_corp_name ? $delivery_corp_name : get_option( 'payletter_pay_delivery_corp_name' );
		$inicis_denyed      = get_post_meta( $order_id, '_pay_pg_escrow', true );

		wp_enqueue_script( 'daum-postcode', '//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js', [], null, true );
		?>
        <input type="hidden" id="delivery-update" name="delivery_update" value="">
        <input type="hidden" id="escrow-denyed" name="escrow_denyed" value="">
        <input type="hidden" name="CharSet" value="utf-8">
        <table id="payletter-delivery-update" style="margin:0;width:100%;">
            <tr>
                <td>
                    <label for="payletter-pay-delivery-corp-name">shipping company</label>
                </td>
                <td>
                    <select name="payletter_pay_delivery_corp_name" id="payletter-pay-delivery-corp-name"
                            style="width: 100%;">
                        <option value="">No carrier</option>
						<?php foreach ( payletter_pay_delivery_corp_name_list() as $code => $list ): ?>
							<?php $name = $list['name'] ?>
                            <option value="<?php echo esc_attr( $code ) ?>"<?php if ( $delivery_corp_name == $code ): ?> selected<?php endif ?>><?php echo esc_html( $name ) ?></option>
						<?php endforeach ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="payletter-pay-delivery-num">invoice number</label>
                </td>
                <td>
                    <input type="text" name="payletter_pay_delivery_num" id="payletter-pay-delivery-num"
                           style="width: 100%;"
                           value="<?php echo esc_attr( get_post_meta( $order_id, 'payletter_pay_delivery_num', true ) ) ?>"
                           placeholder="Please enter the invoice number.">
                </td>
            </tr>
			<?php if ( $is_escrow && ( $pay_pg == 'nicepay' || $pay_pg = 'inicis' ) ): ?>
                <tr>
                    <td>
                        <label for="payletter-pay-escrow-reg-person-name">registrant</label>
                    </td>
                    <td>
                        <input type="text" name="payletter_pay_escrow_reg_person_name"
                               id="payletter-pay-escrow-reg-person-name" style="width: 100%;"
                               value="<?php echo esc_attr( get_option( 'payletter_pay_escrow_reg_person_name' ) ) ?>"
                               placeholder="Registration person's name">
                    </td>
                </tr>
			<?php endif ?>
			<?php if ( $is_escrow && $pay_pg = 'inicis' ): ?>
                <tr>
                    <td>
                        <label for="payletter-pay-escrow-charge">delivery fee</label>
                    </td>
                    <td>
                        <select name="payletter_pay_escrow_charge" id="payletter-pay-escrow-charge"
                                style="width: 100%;">
                            <option value="SH">Seller Burden</option>
                            <option value="BH"
							        <?php if ( get_option( 'payletter_pay_escrow_charge' ) ): ?>selected<?php endif ?>>
                                Buyer pays
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">
                        Sender information
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="payletter-pay-send-name">name</label>
                    </td>
                    <td>
                        <input type="text" name="payletter_pay_send_name" id="payletter-pay-send-name"
                               style="width: 100%;"
                               value="<?php echo esc_attr( get_post_meta( $order_id, 'payletter_pay_send_name', true ) ) ?>"
                               placeholder="<?php _e('Sender name', 'payment-gateway-through-payletter') ?>">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="payletter-pay-send-tel">Phone number</label>
                    </td>
                    <td>
                        <input type="text" name="payletter_pay_send_tel" id="payletter-pay-send-tel"
                               style="width: 100%;"
                               value="<?php echo esc_attr( get_post_meta( $order_id, 'payletter_pay_send_tel', true ) ) ?>"
                               placeholder="Sender's phone number">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="payletter-pay-send-addr1">Address</label>
                    </td>
                    <td>
                        <input type="text" name="payletter_pay_send_addr1" id="payletter-pay-send-addr1"
                               style="width: 100%;"
                               value="<?php echo esc_attr( get_post_meta( $order_id, 'payletter_pay_send_addr1', true ) ) ?>"
                               placeholder="Sender address">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="payletter-pay-send-addr2">Detailed Address</label>
                    </td>
                    <td>
                        <input type="text" name="payletter_pay_send_addr2" id="payletter-pay-send-addr2"
                               style="width: 100%;"
                               value="<?php echo esc_attr( get_post_meta( $order_id, 'payletter_pay_send_addr2', true ) ) ?>"
                               placeholder="Sender's address">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="payletter-pay-send-post">Zip code</label>
                    </td>
                    <td>
                        <input type="text" name="payletter_pay_send_post" id="payletter-pay-send-post"
                               style="width: 100%;"
                               value="<?php echo esc_attr( get_post_meta( $order_id, 'payletter_pay_send_post', true ) ) ?>"
                               placeholder="Sender ZIP Code">
                    </td>
                </tr>
			<?php endif ?>
			<?php if ( $inicis_denyed == 'escrow_denyed' ): ?>
                <tr>
                    <td colspan="2">
                        <hr>
                        <button type="submit" class="button right button-primary escrow_denyed">Order rejection
                            confirmation
                        </button>
                    </td>
                </tr>
			<?php else: ?>
                <tr>
                    <td colspan="2">
                        <hr>
                        <button type="submit" class="button right button-primary">Shipping information registration
                        </button>
                    </td>
                </tr>
			<?php endif ?>
        </table>

        <script>
            window.onload = function () {
                /**
                 * Update when information changes
                 */
                let input_text = document.querySelectorAll('#payletter-delivery-update input[type=text]');
                for (let i = 0; i < input_text.length; i++) {
                    input_text[i].addEventListener('keydown', delivery_update);
                }
                let select = document.querySelectorAll('#payletter-delivery-update select');
                for (let i = 0; i < select.length; i++) {
                    select[i].addEventListener('keydown', delivery_update);
                }

                function delivery_update() {
                    document.querySelector('#delivery-update').value = 1;
                }

                /**
                 * 송신자 주소 daum-post
                 */
                document.querySelector('#payletter-pay-send-addr1').addEventListener('click', execDaumPostcode);
                document.querySelector('#payletter-pay-send-post').addEventListener('click', execDaumPostcode);

                function execDaumPostcode() {
                    new daum.Postcode({
                        oncomplete: function (data) {
                            var addr = '';
                            if (data.userSelectedType === 'R') {
                                addr = data.roadAddress;
                            } else {
                                addr = data.jibunAddress;
                            }
                            document.getElementById('payletter-pay-send-post').value = data.zonecode;
                            document.getElementById('payletter-pay-send-addr1').value = addr;
                            document.getElementById('payletter-pay-send-addr2').focus();
                        },
                    }).open();
                }

                /**
                 * Shipping information disabled when rejection is confirmed
                 */
				<?php if($inicis_denyed == 'escrow_denyed'):?>
                /**
                 * When you click Confirm Rejection
                 */
                document.querySelector('.escrow_denyed').addEventListener('click', confirm_escrow_denyed);

                function confirm_escrow_denyed(event) {
                    event.preventDefault();

                    document.getElementById('escrow-denyed').value = 1;
                    document.querySelector('form#post').submit();
                }

                input_text = document.querySelectorAll('#payletter-delivery-update input[type=text]');
                for (let i = 0; i < input_text.length; i++) {
                    input_text[i].setAttribute('disabled', true);
                }
                select = document.querySelectorAll('#payletter-delivery-update select');
                for (let i = 0; i < select.length; i++) {
                    select[i].setAttribute('disabled', true);
                }
				<?php endif?>
            };
        </script>
		<?php
	}

	/**
	 * Save shipping information. If not registered after escrow payment, register shipping information.
	 *
	 * @param int $order_id
	 *
	 * @return string|void
	 */
	public function save_delivery_order_meta_box( $order_id ) {
		$is_update    = isset( $_POST['delivery_update'] ) ? intval( $_POST['delivery_update'] ) : '';
		$delivery_num = isset( $_POST['payletter_pay_delivery_num'] ) ? sanitize_text_field( $_POST['payletter_pay_delivery_num'] ) : ''; // 송장번호
		$tid          = get_post_meta( $order_id, 'pay_pg_tid', true );

		if ( $is_update && $delivery_num ) {
			$post_method = get_post_meta( $order_id, '_payment_method', true );
			$settings    = get_option( 'woocommerce_' . $post_method . '_settings' );

			$delivery_corp_name = isset( $_POST['payletter_pay_delivery_corp_name'] ) ? sanitize_text_field( $_POST['payletter_pay_delivery_corp_name'] ) : ''; // shipping company

			update_option( 'payletter_pay_delivery_corp_name', $delivery_corp_name );
			update_post_meta( $order_id, 'payletter_pay_delivery_corp_name', $delivery_corp_name );
			update_post_meta( $order_id, 'payletter_pay_delivery_num', $delivery_num );
		}

		$escrow_denyed = isset( $_POST['escrow_denyed'] ) ? intval( $_POST['escrow_denyed'] ) : '';
		if ( $escrow_denyed ) {
			$order     = new WC_Order( $order_id );
			$mid       = isset( $settings['inicis_mid'] ) && $settings['inicis_mid'] ? $settings['inicis_mid'] : 'iniescrow0';
			$api_key   = isset( $settings['inicis_api_key'] ) && $settings['inicis_api_key'] ? $settings['inicis_api_key'] : 'yERbIlJ3NhTeObsA';
			$oid       = get_post_meta( $order_id, 'pay_pg_oid', true );
			$oid       = $oid ? $oid : $order_id;
			$timestamp = date( 'Ymdhms', current_time( 'timestamp' ) );
			$client_ip = wc_clean( $_SERVER['SERVER_ADDR'] );
			$hash_data = hash( 'sha512', $api_key . 'Dncf' . $timestamp . $client_ip . $mid . $tid );

			$escrow_request_url = 'https://iniapi.inicis.com/api/v1/escrow'; // Escrow request URL

			$args            = [];
			$args['header']  = [
				'Content-Type' => 'application/x-www-form-urlencoded',
				'charset'      => 'utf-8',
			];
			$args['timeout'] = '15';
			$args['body']    = [
				'type'        => 'Dncf',
				'mid'         => $mid,
				'clientIp'    => $client_ip,
				'timestamp'   => $timestamp,
				'originalTid' => $tid,
				'dcnfName'    => $oid,
				'hashData'    => $hash_data
			];

			$response = wp_remote_post( $escrow_request_url, $args );
			if ( ! is_wp_error( $response ) && ( $response['response']['code'] == 200 ) ) {
				$body = wp_remote_retrieve_body( $response );
				if ( $body ) {
					$body = json_decode( $body );

					if ( $body->resultCode == '00' ) {
						update_post_meta( $order_id, '_pay_pg_escrow_denyed', 'confirm' );
						$order->add_order_note( '[Inisys] I have confirmed the refusal of purchase.' );
						update_post_meta( $order_id, 'escrow_deilevery_dncf_date', $body->dcnfDate ); // transaction date
						update_post_meta( $order_id, 'escrow_deilevery_dncf_time', $body->dcnfTime ); // trading hours
						wp_redirect( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) );
					} else {
						$order->add_order_note( '[Inesis] ' . $body->resultMsg );
						wp_redirect( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) );
					}

					return;
				}
			} else { //error
				return sprintf( '[An error has occurred]' );
			}
		}
	}

	/**
	 * Print the account on the customer order page.
	 *
	 * @param int $order_id
	 */
	public function get_delivery_display( $order ) {
		$order_id = $order->get_id();

		$delivery_corp_name_code = get_post_meta( $order_id, 'payletter_pay_delivery_corp_name', true );
		$delivery_num            = get_post_meta( $order_id, 'payletter_pay_delivery_num', true );

		if ( $delivery_corp_name_code && $delivery_num ):
			$delivery_corp_name_list = payletter_pay_delivery_corp_name_list();

			$delivery_corp_name = $delivery_corp_name_list[ $delivery_corp_name_code ]['name'];
			$tracking_url       = $delivery_corp_name_list[ $delivery_corp_name_code ]['tracking_url'];
			?>
            <section class="woocommerce-bacs-display">
                <h2 class="woocommerce-bacs-bank-name">invoice number</h2>
                <ul class="order_details">
                    <li>
                        <strong>shipping company : <?php echo esc_html( $delivery_corp_name ) ?></strong>
                    </li>
                    <li>
                        <strong>invoice number : <?php echo esc_html( $delivery_num ) ?></strong>
                    </li>
                    <li>
                        <a href="<?php echo esc_url( sprintf( $tracking_url, $delivery_num ) ) ?>" target="_blank"
                           class="button"
                           style="margin-left: 10px;"><strong>Delivery tracking</strong></a>
                    </li>
                </ul>
            </section>
		<?php endif;
	}

	/**
	 * Convert to inesis courier company code.
	 */
	public function get_translate_inicis_delivery( $delivery_corp_name ) {
		$delivery_data = [];

		if ( $delivery_corp_name == 'CJEX' ) {
			$delivery_data['code'] = 'korex';
			$delivery_data['name'] = 'CJ Logistics';
		} elseif ( $delivery_corp_name == 'LTEX' ) {
			$delivery_data['code'] = 'hyundai';
			$delivery_data['name'] = 'Lotte Global Logis';
		} elseif ( $delivery_corp_name == 'HJEX' ) {
			$delivery_data['code'] = 'hanjin';
			$delivery_data['name'] = 'Hanjin Courier';
		} elseif ( $delivery_corp_name == 'POEX' ) {
			$delivery_data['code'] = 'EPOST';
			$delivery_data['name'] = 'post office';
		} elseif ( $delivery_corp_name == 'LJEX' ) {
			$delivery_data['code'] = 'kgb';
			$delivery_data['name'] = 'Rosen Courier';
		} elseif ( $delivery_corp_name == 'YYEX' ) {
			$delivery_data['code'] = 'ilyang';
			$delivery_data['name'] = 'Ilyang Logis';
		} elseif ( $delivery_corp_name == 'KDEX' ) {
			$delivery_data['code'] = 'kdexp';
			$delivery_data['name'] = 'Kyungdong Courier';
		} elseif ( $delivery_corp_name == 'DSEX' ) {
			$delivery_data['code'] = 'daesin';
			$delivery_data['name'] = 'instead of courier';
		} elseif ( $delivery_corp_name == 'GPEX' ) {
			$delivery_data['code'] = 'cvsnet';
			$delivery_data['name'] = 'GS Postbox Courier';
		}

		return $delivery_data;
	}
}
