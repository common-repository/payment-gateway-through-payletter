<?php

namespace Onepix\Payletter\Classes;

use WC_Order;

/**
 * Payletter_Pay_WC_Request_Refund
 * @link https://www.payletter.com/
 */
class Payletter_Pay_WC_Request_Refund {

	public function __construct() {
		add_action( 'init', [ $this, 'register_post_status' ] );
		add_action( 'wp_footer', [ $this, 'request_refund_script' ] );
		add_filter( 'wc_order_statuses', [ $this, 'add_order_status' ], 10, 1 );
		add_action( 'admin_post_payletter_request_refund', [ $this, 'request_refund' ] );
		add_action( 'admin_post_nopriv_payletter_request_refund', [ $this, 'request_refund' ] );
		add_action( 'admin_head', [ $this, 'request_refund_color' ] );
		add_filter( 'woocommerce_my_account_my_orders_actions', [ $this, 'orders_actions' ], 10, 2 );
		add_action( 'woocommerce_order_status_request-refund', [ $this, 'request_refund_mail' ], 10, 1 );

		$this->register_post_status();
	}

	/**
	 * Register a refund request in the post status.
	 */
	function register_post_status() {
		register_post_status( 'wc-request-refund', [
			'label'                     => __( 'Request refund', 'payletter_pay_wc' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Request refund <span class="count">(%s)</span>', 'Request refund <span class="count">(%s)</span>',
				'payletter_pay_wc' )
		] );
	}

	/**
	 * Register a refund request in the order status.
	 */
	function add_order_status( $order_statuses ) {
		$order_statuses['wc-request-refund'] = __( 'Request refund', 'payletter_pay_wc' );

		return $order_statuses;
	}

	/**
	 * In the process of processing, when the status is completed, a refund request button is displayed.
	 */
	public function orders_actions( $actions, $order ) {
		$status = $order->get_status();
		foreach ( $order->get_items() as $order_item ) {
			$product = wc_get_product( $order_item->get_product_id() );

			// Display the refund request button.
			if ( ! get_option( 'payletter_pay_request_refund' ) ) {
				if ( $status == 'processing' || $status == 'on-hold' || $status == 'pre-shipping' || $status == 'shipping' || $status == 'completed' ) {
					$actions['request-refund']['url']  = '#' . $order->id;
					$actions['request-refund']['name'] = __( 'Request refund', 'payletter_pay_wc' );
				} elseif ( $status == 'request-refund' ) {
					$actions['withdraw-refund']['url']  = '#' . $order->id;
					$actions['withdraw-refund']['name'] = __( 'Withdraw the refund request', 'payletter_pay_wc' );
				}
			} elseif ( get_option( 'payletter_pay_request_refund' ) == 'hide_virtual' ) {
				if ( ! $product->is_virtual() ) {
					if ( $status == 'processing' || $status == 'on-hold' || $status == 'pre-shipping' || $status == 'shipping' || $status == 'completed' ) {
						$actions['request-refund']['url']  = '#' . $order->id;
						$actions['request-refund']['name'] = __( 'Request refund', 'payletter_pay_wc' );
					} elseif ( $status == 'request-refund' ) {
						$actions['withdraw-refund']['url']  = '#' . $order->id;
						$actions['withdraw-refund']['name'] = __( 'Withdraw the refund request', 'payletter_pay_wc' );
					}
				}
			}
		}

		return $actions;
	}

	/**
	 * The script to be executed when a refund request is clicked is output to the footer.
	 */
	public function request_refund_script() {
		if ( is_user_logged_in() && is_account_page() ) {
			?>
            <script>
                jQuery(document).ready(function () {
                    jQuery('.button.request-refund').on('click', function () {
                        var order_id = jQuery(this).attr('href').split("#")[1];

                        jQuery(".form.request-refund").show();
                        jQuery(".form.request-refund input[name='status']").val('request-refund');
                        jQuery(".form.request-refund input[name='order_id']").val(order_id);
                        return false;
                    });

                    jQuery('.button.withdraw-refund').on('click', function () {
                        var order_id = jQuery(this).attr('href').split("#")[1];

                        if (confirm("<?php echo esc_js( __( 'Do you want to withdraw your refund request?', 'payletter_pay_wc' ) )?>")) {
                            jQuery(".form.request-refund input[name='status']").val('');
                            jQuery(".form.request-refund input[name='order_id']").val(order_id);
                            jQuery(".form.request-refund form").submit();
                        }
                        return false;
                    });

                    jQuery('.form.request-refund .close').on('click', function () {
                        jQuery(".form.request-refund").hide();
                        jQuery(".form.request-refund input").each(function () {
                            jQuery(this).val('');
                        });
                        jQuery(".form.request-refund textarea[name='content']").val('');
                    });
                });
            </script>
            <div class="form request-refund">
                <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" method="post">
                    <input type="hidden" name="security" value="<?php echo esc_attr( wp_create_nonce( 'payletter_pay_wc_request_refund' ) ) ?>">
                    <input type="hidden" name="action" value="payletter_request_refund">
                    <input type="hidden" name="status" value="">
                    <input type="hidden" name="order_id" value="">
                    <textarea name="content" cols="10" rows="10"
                              placeholder="<?php echo esc_attr( __( 'Please enter the reason for the refund(optional)', 'payletter_pay_wc' ) ) ?>"></textarea>
                    <button type="button" class="button close"><?php echo esc_html( __( 'Close', 'payletter_pay_wc' ) ) ?></button>
                    <button type="submit" onclick="if(confirm('<?php echo esc_js( __( 'Would you like to request a refund?',
						'payletter_pay_wc' ) ) ?>')){ return true; }else{ return false; }" class="button alt submit"><?php echo esc_html( __( 'Request refund',
							'payletter_pay_wc' ) ) ?></button>
                </form>
            </div>
            <style>
                .form.request-refund {
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

                .form.request-refund .button {
                    float: left;
                }

                .form.request-refund .alt {
                    float: right;
                }

                .form.request-refund textarea {
                    width: 100%;
                    resize: vertical;
                    background: #ffffff;
                }
            </style>
			<?php
		}
	}

	/**
	 * Refund requests are reflected in the order.
	 */
	public function request_refund() {
		if ( ! check_ajax_referer( 'payletter_pay_wc_request_refund', 'security' ) ) {
			wp_die( __( "You don't have permission", "payletter_pay_wc" ) );
		} else {
			$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
			$status   = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 0;
			$content  = isset( $_POST['content'] ) ? sanitize_textarea_field( $_POST['content'] ) : '';
			$msg      = __( 'The refund has been withdrawn', 'payletter_pay_wc' );
			if ( get_option( 'payletter_pay_withdrawn_refund_message' ) ) {
				$msg = get_option( 'payletter_pay_withdrawn_refund_message' );
			}

			if ( $order_id ) {
				$order = new WC_Order( $order_id );

				if ( $order ) {
					$pre_status = $order->get_status();

					if ( $status == 'request-refund' ) {
						$order->update_meta_data( '_pre_status', $pre_status );
						$order->update_status( $status );
						$msg = __( 'A refund has been requested', 'payletter_pay_wc' );

						if ( get_option( 'payletter_pay_request_refund_message' ) ) {
							$msg = get_option( 'payletter_pay_request_refund_message' );
						}
					} else {
						$order->update_status( $order->get_meta( '_pre_status', true ) );
					}
				}
				if ( $content ) {
					$order->add_order_note( __( "Reason for refund", "payletter_pay_wc" ) . ': ' . $content );
				}

				do_action( 'payletter_pay_wc_after_request_refund' );
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
	 * Change the color of the refund request mark on the admin order page to red.
	 */
	public function request_refund_color() {
		?>
        <style>
            .order-status.status-request-refund {
                background: #ff5d5f;
                color: #ffeaea;
            }
        </style>
		<?php
	}

	/**
	 * When requesting a refund, an e-mail is sent to the administrator.
	 */
	public function request_refund_mail( $order_id ) {
		$admin_mail = get_option( 'admin_email' );

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . '<' . $admin_mail . '>',
		];
		$subject = '[' . get_bloginfo( 'name' ) . '] ' . __( 'Received an refund request', 'payletter_pay_wc' );
		ob_start();
		?>

        <a href="<?php echo esc_url( admin_url( 'post.php' ) . '?post=' . $order_id . '&action=edit' ); ?>">
			<?php echo esc_html( __( 'View Order that received an refund request', 'payletter_pay_wc' ) ) ?>
        </a>

		<?php
		$content = ob_get_clean();
		wp_mail( $admin_mail, $subject, $content, $headers );
	}
}