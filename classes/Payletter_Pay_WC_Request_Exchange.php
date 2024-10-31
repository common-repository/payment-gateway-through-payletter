<?php

namespace Onepix\Payletter\Classes;

use WC_Order;

/**
 * Payletter_Pay_WC_Request_Exchange
 * @link https://www.payletter.com/
 */
class Payletter_Pay_WC_Request_Exchange {

	public function __construct() {
		add_action( 'init', [ $this, 'register_post_status' ] );
		add_action( 'wp_footer', [ $this, 'request_exchange_script' ] );
		add_filter( 'wc_order_statuses', [ $this, 'add_order_status' ], 10, 1 );
		add_action( 'admin_post_payletter_request_exchange', [ $this, 'request_exchange' ] );
		add_action( 'admin_post_nopriv_payletter_request_exchange', [ $this, 'request_exchange' ] );
		add_action( 'admin_head', [ $this, 'request_exchange_color' ] );
		add_filter( 'woocommerce_my_account_my_orders_actions', [ $this, 'orders_actions' ], 10, 2 );
		add_action( 'woocommerce_order_status_request-exchange', [ $this, 'request_exchange_mail' ], 10, 1 );

		$this->register_post_status();
	}

	/**
	 * Register the exchange request in the post state.
	 */
	function register_post_status() {
		register_post_status( 'wc-request-exchange', [
			'label'                     => __( 'Request exchange', 'payletter_pay_wc' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Request exchange <span class="count">(%s)</span>', 'Request exchange <span class="count">(%s)</span>',
				'payletter_pay_wc' )
		] );
	}

	/**
	 * Register the exchange request in the order status.
	 */
	function add_order_status( $order_statuses ) {
		$order_statuses['wc-request-exchange'] = __( 'Request exchange', 'payletter_pay_wc' );

		return $order_statuses;
	}

	/**
	 * In the process of processing, when the status is completed, the exchange request button is displayed.
	 */
	public function orders_actions( $actions, $order ) {
		$status = $order->get_status();
		foreach ( $order->get_items() as $order_item ) {
			$product = wc_get_product( $order_item->get_product_id() );

			// Displays the exchange request button.
			if ( ! get_option( 'payletter_pay_request_exchange' ) ) {
				if ( $status == 'processing' || $status == 'on-hold' || $status == 'pre-shipping' || $status == 'shipping' || $status == 'completed' ) {
					$actions['request-exchange']['url']  = '#' . $order->id;
					$actions['request-exchange']['name'] = __( 'Request exchange', 'payletter_pay_wc' );
				} elseif ( $status == 'request-exchange' ) {
					$actions['withdraw-exchange']['url']  = '#' . $order->id;
					$actions['withdraw-exchange']['name'] = __( 'Withdraw the exchange request', 'payletter_pay_wc' );
				}
			} elseif ( get_option( 'payletter_pay_request_exchange' ) == 'hide_virtual' ) {
				if ( ! $product->is_virtual() ) {
					if ( $status == 'processing' || $status == 'on-hold' || $status == 'pre-shipping' || $status == 'shipping' || $status == 'completed' ) {
						$actions['request-exchange']['url']  = '#' . $order->id;
						$actions['request-exchange']['name'] = __( 'Request exchange', 'payletter_pay_wc' );
					} elseif ( $status == 'request-exchange' ) {
						$actions['withdraw-exchange']['url']  = '#' . $order->id;
						$actions['withdraw-exchange']['name'] = __( 'Withdraw the exchange request', 'payletter_pay_wc' );
					}
				}
			}
		}

		return $actions;
	}

	/**
	 * The script to be executed when the exchange request is clicked is output to the footer.
	 */
	public function request_exchange_script() {
		if ( is_user_logged_in() && is_account_page() ) {
			?>
            <script>
                jQuery(document).ready(function () {
                    jQuery('.button.request-exchange').on('click', function () {
                        var order_id = jQuery(this).attr('href').split("#")[1];

                        jQuery(".form.request-exchange").show();
                        jQuery(".form.request-exchange input[name='status']").val('request-exchange');
                        jQuery(".form.request-exchange input[name='order_id']").val(order_id);
                        return false;
                    });

                    jQuery('.button.withdraw-exchange').on('click', function () {
                        var order_id = jQuery(this).attr('href').split("#")[1];

                        if (confirm("<?php echo esc_js( __( 'Do you want to withdraw your exchange request?', 'payletter_pay_wc' ) )?>")) {
                            jQuery(".form.request-exchange input[name='status']").val('');
                            jQuery(".form.request-exchange input[name='order_id']").val(order_id);
                            jQuery(".form.request-exchange form").submit();
                        }
                        return false;
                    });

                    jQuery('.form.request-exchange .close').on('click', function () {
                        jQuery(".form.request-exchange").hide();
                        jQuery(".form.request-exchange input").each(function () {
                            jQuery(this).val('');
                        });
                        jQuery(".form.request-exchange textarea[name='content']").val('');
                    });
                });
            </script>
            <div class="form request-exchange">
                <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" method="post">
                    <input type="hidden" name="security" value="<?php echo esc_attr( wp_create_nonce( 'payletter_pay_wc_request_exchange' ) ) ?>">
                    <input type="hidden" name="action" value="payletter_request_exchange">
                    <input type="hidden" name="status" value="">
                    <input type="hidden" name="order_id" value="">
                    <textarea name="content" cols="10" rows="10"
                              placeholder="<?php echo esc_attr( __( 'Please enter the reason for the exchange', 'payletter_pay_wc' ) ) ?>" required></textarea>
                    <button type="button" class="button close"><?php echo esc_html( __( 'Close', 'payletter_pay_wc' ) ) ?></button>
                    <button type="submit" onclick="if(confirm('<?php echo esc_js( __( 'Would you like to request a exchange?',
						'payletter_pay_wc' ) ) ?>')){ return true; }else{ return false; }"
                            class="button alt submit"><?php echo esc_html( __( 'Request exchange', 'payletter_pay_wc' ) ) ?></button>
                </form>
            </div>
            <style>
                .form.request-exchange {
                    display: none;
                    position: fixed;
                    margin: 0 auto;
                    width: 30%;
                    height: auto;
                    padding: 10px;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: #e9e9e9;
                }

                .form.request-exchange .button {
                    float: left;
                }

                .form.request-exchange .alt {
                    float: right;
                }

                .form.request-exchange textarea {
                    width: 100%;
                    resize: vertical;
                    background: #ffffff;
                }
            </style>
			<?php
		}
	}

	/**
	 * The exchange request is reflected in the order.
	 */
	public function request_exchange() {
		if ( ! check_ajax_referer( 'payletter_pay_wc_request_exchange', 'security' ) ) {
			wp_die( __( "You don't have permission", "payletter_pay_wc" ) );
		} else {
			$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
			$status   = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 0;
			$content  = isset( $_POST['content'] ) ? sanitize_textarea_field( $_POST['content'] ) : '';
			$msg      = __( 'The exchange has been withdrawn', 'payletter_pay_wc' );

			if ( get_option( 'payletter_pay_withdrawn_exchange_message' ) ) {
				$msg = get_option( 'payletter_pay_withdrawn_exchange_message' );
			}

			if ( $order_id ) {
				$order = new WC_Order( $order_id );

				if ( $order ) {
					$pre_status = $order->get_status();

					if ( $status == 'request-exchange' ) {
						$order->update_meta_data( '_pre_status', $pre_status );
						$order->update_status( $status );
						$order->add_order_note( __( "Reason for exchange", "payletter_pay_wc" ) . ': ' . $content );
						$msg = __( 'A exchange has been requested', 'payletter_pay_wc' );

						if ( get_option( 'payletter_pay_request_exchange_message' ) ) {
							$msg = get_option( 'payletter_pay_request_exchange_message' );
						}
					} else {
						$order->update_status( $order->get_meta( '_pre_status', true ) );
					}

					do_action( 'payletter_pay_wc_after_request_exchange' );
				}
				?>
                <script>
                    alert('<?php echo esc_js( $msg )?>');
                    window.location.href = "<?php echo esc_js( wp_get_referer() )?>";
                </script>
				<?php
			}
			exit;
		}
	}

	/**
	 * Change the color of the exchange request mark on the admin order page to orange.
	 */
	public function request_exchange_color() {
		?>
        <style>
            .order-status.status-request-exchange {
                background: #f8c5a7;
                color: #94380c;
            }
        </style>
		<?php
	}

	public function request_exchange_mail( $order_id ) {
		$admin_mail = get_option( 'admin_email' );

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . '<' . $admin_mail . '>',
		];
		$subject = '[' . get_bloginfo( 'name' ) . '] ' . __( 'Received an exchange request', 'payletter_pay_wc' );
		ob_start();
		?>
        <a href="<?php echo esc_url( admin_url( 'post.php' ) . '?post=' . $order_id . '&action=edit' ); ?>">
			<?php echo esc_html( __( 'View Order that received an exchange request', 'payletter_pay_wc' ) ) ?>
        </a>

		<?php
		$content = ob_get_clean();
		wp_mail( $admin_mail, $subject, $content, $headers );
	}
}