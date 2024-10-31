<?php

namespace Onepix\Payletter\Classes;

/**
 * Payletter_Pay_WC_Payment_Settings
 * @link https://www.payletter.com/
 */
class Payletter_Pay_WC_Payment_Settings {

	public function __construct() {
		add_filter( 'woocommerce_settings_tabs_array', [ $this, 'tabs_array' ], 21, 1 );
		add_action( 'woocommerce_settings_tabs_payletter_pay_payment_settings', [ $this, 'tabs' ], 10 );
		add_action( 'woocommerce_update_options_payletter_pay_payment_settings', [ $this, 'update_options' ], 10 );
		add_action( 'woocommerce_view_order', [ $this, 'get_bacs_display' ], 5, 1 );

		add_filter( 'woocommerce_product_single_add_to_cart_text', [ $this, 'change_cart_button_text' ], 10, 1 );
		add_filter( 'woocommerce_product_add_to_cart_text', [ $this, 'change_cart_button_text' ], 10, 1 );
	}

	/**
	 * Add Pay tab to WooCommerce settings.
	 *
	 * @param  array  $tabs
	 */
	public function tabs_array( $tabs ) {
		$tabs['payletter_pay_payment_settings'] = 'Payletter Pay';

		return $tabs;
	}

	/**
	 * Prints the contents of the Pay tab in WooCommerce settings.
	 */
	public function tabs() {
		?>
        <h2>Payment window settings</h2>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="payletter-pay-payment-window">Payment window method</label>
                </th>
                <td class="forminp forminp-text">
                    <select id="payletter-pay-payment-window" name="payletter_pay_payment_window">
                        <option value="">Open as an iframe (default)</option>
                        <option value="all"<?php if ( get_option( 'payletter_pay_payment_window' ) == 'all' ): ?> selected<?php endif ?>>Open both PC and Mobile as a pop-up window
                        </option>
                    </select>
                    <p class="description">If payment cannot be made due to browser security settings, try changing the payment window to a pop-up window.</p>
                </td>
            </tr>
            </tbody>
        </table>
        <hr>
        <h2>shopping cart button set</h2>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="payletter-pay-wc-cart-button-text">shopping cart button text</label>
                </th>
                <td class="forminp forminp-text">
                    <input type="text" id="payletter-pay-wc-cart-button-text" name="payletter_pay_wc_cart_button_text"
                           value="<?php echo esc_attr( get_option( 'payletter_pay_wc_cart_button_text' ) ) ?>">
                    <p class="description">You can change the text of the shopping cart button.</p>
                </td>
            </tr>
            </tbody>
        </table>
        <hr>
        <h2>Shipping information settings</h2>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="payletter-pay-delivery-corp-name">Default shipping company name</label>
                </th>
                <td class="forminp forminp-text">
                    <select id="payletter-pay-delivery-corp-name" name="payletter_pay_delivery_corp_name">
                        <option value="">No carrier</option>
						<?php foreach ( payletter_pay_delivery_corp_name_list() as $code => $list ): ?>
							<?php $name = $list['name'] ?>
                            <option value="<?php echo esc_attr( $code ) ?>"<?php if ( get_option( 'payletter_pay_delivery_corp_name' ) == $code ): ?> selected<?php endif ?>><?php echo esc_html( $name ) ?></option>
						<?php endforeach ?>
                    </select>
                    <p class="description">Please select your default shipping company.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="payletter-pay-escrow-reg-person-name">Default registrant name</label>
                </th>
                <td class="forminp forminp-text">
                    <input id="payletter-pay-escrow-reg-person-name" name="payletter_pay_escrow_reg_person_name" type="text" style=""
                           value="<?php echo esc_attr( get_option( 'payletter_pay_escrow_reg_person_name' ) ) ?>" class="" placeholder="ex) Hong Gil Dong">
                    <p class="description">When using escrow, please enter the name of the person in charge of registering shipping information.</p>
                </td>
            </tr>
            </tbody>
        </table>
        <hr>
        <h2>Refund, exchange request settings</h2>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="payletter-pay-request-refund">Refund Request Button</label>
                </th>
                <td class="forminp forminp-text">
                    <select id="payletter-pay-request-refund" name="payletter_pay_request_refund">
                        <option value="">mark</option>
                        <option value="hide_virtual"<?php if ( get_option( 'payletter_pay_request_refund' ) == 'hide_virtual' ): ?> selected<?php endif ?>>digital
                            hide the product
                        </option>
                        <option value="hide"<?php if ( get_option( 'payletter_pay_request_refund' ) == 'hide' ): ?> selected<?php endif ?>>hide</option>
                    </select>
                    <p class="description">Display a Request Refund button in the customer's order history. If it's a digital product, you can hide it.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="payletter-pay-request-refund-message">Refund request completion message</label>
                </th>
                <td class="forminp forminp-text">
                    <input type="text" id="payletter-pay-request-refund-message" name="payletter_pay_request_refund_message" placeholder="A refund has been requested."
                           value="<?php echo esc_attr( get_option( 'payletter_pay_request_refund_message' ) ) ?>">
                    <p class="description">This message is displayed after completing the refund request.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="payletter-pay-withdrawn-refund-message">Refund withdrawal message</label>
                </th>
                <td class="forminp forminp-text">
                    <input type="text" id="payletter-pay-withdrawn-refund-message" name="payletter_pay_withdrawn_refund_message" placeholder="Your refund request has been withdrawn."
                           value="<?php echo esc_attr( get_option( 'payletter_pay_withdrawn_refund_message' ) ) ?>">
                    <p class="description">This message is displayed after the refund is withdrawn.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="payletter-pay-request-exchange">exchange request button</label>
                </th>
                <td class="forminp forminp-text">
                    <select id="payletter-pay-request-exchange" name="payletter_pay_request_exchange">
                        <option value="">Mark</option>
                        <option value="hide_virtual"<?php if ( get_option( 'payletter_pay_request_exchange' ) == 'hide_virtual' ): ?> selected<?php endif ?>>
                            hide digital goods
                        </option>
                        <option value="hide"<?php if ( get_option( 'payletter_pay_request_exchange' ) == 'hide' ): ?> selected<?php endif ?>>hide</option>
                    </select>
                    <p class="description">Display an exchange request button in the customer's order history. If it's a digital product, you can hide it.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="payletter-pay-request-exchange-message">Exchange request completion message</label>
                </th>
                <td class="forminp forminp-text">
                    <input type="text" id="payletter-pay-request-exchange-message" name="payletter_pay_request_exchange_message" placeholder="An exchange has been requested."
                           value="<?php echo esc_attr( get_option( 'payletter_pay_request_exchange_message' ) ) ?>">
                    <p class="description">This message is displayed after completing the exchange request.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="payletter-pay-withdrawn-exchang-messagee">Exchange withdrawal message</label>
                </th>
                <td class="forminp forminp-text">
                    <input type="text" id="payletter-pay-withdrawn-exchange-message" name="payletter_pay_withdrawn_exchange_message"
                           placeholder="The exchange request has been withdrawn." value="<?php echo esc_attr( get_option( 'payletter_pay_withdrawn_exchange_message' ) ) ?>">
                    <p class="description">This message is displayed after the exchange is completed.</p>
                </td>
            </tr>
            </tbody>
        </table>
		<?php
	}

	/**
	 * Update the contents of the Pay tab in WooCommerce settings.
	 */
	public function update_options() {
        $data = filter_input_array($_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		foreach ( $data as $key => $value ) {
			$value = sanitize_text_field( $value );
			update_option( $key, $value );
			if ( $key == 'save' ) {
				break;
			}
		}
	}

	/**
	 * When paying by direct deposit, the account is printed on the customer order page.
	 *
	 * @param  int  $order_id
	 */
	public function get_bacs_display( $order_id ) {
		$order          = wc_get_order( $order_id );
		$order_status   = $order->get_status();
		$order_method   = $order->get_payment_method();
		$bacs_data_list = get_option( 'woocommerce_bacs_accounts' );

		// $order_method = Direct bank transfer && $order_status = Before deposit confirmation
		if ( $order_method == 'bacs' && $order_status = 'on-hold' && is_array( $bacs_data_list ) && $bacs_data_list ):?>
            <section class="woocommerce-bacs-display">
                <h2 class="woocommerce-bacs-bank-name">Bank details</h2>
				<?php foreach ( $bacs_data_list as $bacs_data ): ?>
                    <ul class="woocommerce-bacs-bank-details order_details bacs_details">
                        <h3 class="wc-bacs-bank-details-account-name" style="font-size: 1em; line-height: 1.5em; padding: 9px 12px; margin-bottom: 0;"><?php echo esc_html( $bacs_data['account_name'] ) . ':' ?></h3>
						<?php if ( $bacs_data['bank_name'] ): ?>
                            <li>Bank: <strong><?php echo esc_html( $bacs_data['bank_name'] ) ?></strong></li>
						<?php endif ?>
						<?php if ( $bacs_data['account_number'] ): ?>
                            <li>Account number: <strong><?php echo esc_html( $bacs_data['account_number'] ) ?></strong></li>
						<?php endif ?>
						<?php if ( $bacs_data['sort_code'] ): ?>
                            <li>SORT code: <strong><?php echo esc_html( $bacs_data['sort_code'] ) ?></strong></li>
						<?php endif ?>
						<?php if ( $bacs_data['iban'] ): ?>
                            <li>IBAN: <strong><?php echo esc_html( $bacs_data['iban'] ) ?></strong></li>
						<?php endif ?>
						<?php if ( $bacs_data['bic'] ): ?>
                            <li>BIC: <strong><?php echo esc_html( $bacs_data['bic'] ) ?></strong></li>
						<?php endif ?>
                    </ul>
				<?php endforeach ?>
            </section>
		<?php endif;
	}

	/**
	 * Change the shopping cart button text.
	 *
	 * @param  string  $cart_btn_text
	 */
	public function change_cart_button_text( $cart_btn_text ) {
		$payletter_cart_btn_text = get_option( 'payletter_pay_wc_cart_button_text' );
		if ( $payletter_cart_btn_text ) {
			$cart_btn_text = $payletter_cart_btn_text;
		}

		return $cart_btn_text;
	}
}
