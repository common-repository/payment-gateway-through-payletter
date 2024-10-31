<?php

namespace Onepix\Payletter\Classes;

use WC_Order;

/**
 * Payletter_Pay_WC_Request_Purchase_Confirmation
 * @link https://www.payletter.com/
 */
class Payletter_Pay_WC_Request_Purchase_Confirmation {

	public function __construct() {
		add_action( 'wp_footer', [ $this, 'purchase_confirmation_script' ] );
		add_action( 'admin_post_payletter_purchase_confirmation_return', [ $this, 'return' ] );
		add_action( 'admin_post_nopriv_payletter_purchase_confirmation_return', [ $this, 'return' ] );
		add_action( 'admin_post_payletter_purchase_confirmation_close', [ $this, 'parent_close' ] );
		add_action( 'admin_post_nopriv_payletter_purchase_confirmation_close', [ $this, 'parent_close' ] );
		add_filter( 'woocommerce_my_account_my_orders_actions', [ $this, 'orders_actions' ], 10, 2 );
	}

	/**
	 * Displays a purchase confirmation button.
	 */
	public function orders_actions( $actions, $order ) {
		$status = $order->get_status();

		$is_escrow = get_post_meta( $order->id, '_pay_pg_escrow', true );

		if ( $is_escrow ) {
			if ( $status == 'processing' || $status == 'pre-shipping' || $status == 'shipping' ) {
				$actions['purchase-confirmation']['url']  = add_query_arg(
					[
						'action'   => 'payletter_request_purchase_confirmation',
						'order_id' => $order->id,
						'security' => wp_create_nonce( 'payletter_pay_wc_purchase_confirmation' )
					], admin_url( 'admin-post.php' )
				);
				$actions['purchase-confirmation']['name'] = __( 'Purchase confirmation', 'payletter_pay_wc' );
			}
		}

		return $actions;
	}

	/**
	 * The script to be executed when the purchase confirmation is clicked is output to the footer.
	 */
	public function purchase_confirmation_script() {
		if ( is_user_logged_in() && is_account_page() ) {
			?>
            <script>
                jQuery('.button.purchase-confirmation').on('click', function (event) {
                    event.preventDefault();
                    var url = jQuery(this).attr('href');

					<?php if($this->is_mobile()):?>
                    window.open(url);
					<?php else:?>
                    const w = 650;
                    const h = 500;
                    window.open(url, 'inicis-purchase-confirmation', 'width=' + w + ',height=' + h + ',left=' + (screen.availWidth - w) * 0.5 + ',top=' + (screen.availHeight - h) * 0.5);
					<?php endif?>
                });
            </script>
			<?php
		}
	}

	public function return() {
		if ( $this->is_mobile() ) {
			$tid                  = isset( $_REQUEST['P_ESCROW_TID'] ) ? sanitize_text_field( $_REQUEST['P_ESCROW_TID'] ) : '';
			$resultCode           = isset( $_REQUEST['P_STATUS'] ) ? sanitize_text_field( $_REQUEST['P_STATUS'] ) : '';
			$_REQUEST['P_RMESG1'] = iconv( 'EUC-KR', 'UTF-8', sanitize_text_field( $_REQUEST['P_RMESG1'] ) );
			$resultMsg            = isset( $_REQUEST['P_RMESG1'] ) ? sanitize_text_field( $_REQUEST['P_RMESG1'] ) : '';
		} else {
			$tid        = isset( $_REQUEST["tid"] ) ? sanitize_text_field( $_REQUEST["tid"] ) : '';
			$resultCode = isset( $_REQUEST["ResultCode"] ) ? sanitize_text_field( $_REQUEST["ResultCode"] ) : '';
			$resultMsg  = isset( $_REQUEST["ResultMsg"] ) ? sanitize_text_field( $_REQUEST["ResultMsg"] ) : '';
			$resultDate = isset( $_REQUEST["CNF_Date"] ) ? sanitize_text_field( $_REQUEST["CNF_Date"] ) : '';
			$buyed      = true;

			if ( $resultDate == "" ) {
				$buyed = false;
			}
		}

		$order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : '';
		if ( $order_id ) {
			$order = new WC_Order( $order_id );
		}

		if ( $order && $tid && $resultCode ) {
			if ( $resultCode == '0000' ) {
				if ( $buyed ) {
					$order->add_order_note( __( "The purchase has been confirmed", "payletter_pay_wc" ) );
					$this->alert( __( "The purchase has been confirmed", "payletter_pay_wc" ) );
					update_post_meta( $order_id, '_pay_pg_escrow', 'escrow_buyed' );
				} else {
					$order->add_order_note( __( "The purchase has been declined", "payletter_pay_wc" ) );
					$this->alert( __( "The purchase has been confirmed", "payletter_pay_wc" ) );
					update_post_meta( $order_id, '_pay_pg_escrow', 'escrow_denyed' );
				}
				$this->parent_close();
			} else {
				$order->add_order_note( $resultMsg );
				$this->alert( $resultMsg, true );
				$this->parent_close();
			}
		}
	}

	public function parent_close() {
		?>
        <script>
            window.parent.close();
        </script>
		<?php
		exit;
	}

	public function close() {
		?>
        <script>
            window.close();
        </script>
		<?php
		exit;
	}

	public function is_mobile() {
		if ( wp_is_mobile() ) {
			return true;
		}

		return false;
	}

	public function alert( $msg = '', $is_error = '' ) {
		$msg = $is_error ? 'An error has occurred. ' . $msg : $msg;
		ob_start();
		?>
        <script>
            alert("<?php echo esc_js( $msg ) ?>");
        </script>
		<?php
		echo ob_get_clean();
	}
}
