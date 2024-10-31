<?php

namespace Onepix\Payletter\Classes;

/**
 * Payletter_Pay_WC_Custom_Button
 * @link https://www.payletter.com/
 */
class Payletter_Pay_WC_Custom_Button {

	public function __construct() {
		add_action( 'woocommerce_product_options_advanced', [ $this, 'custom_button_options_advanced' ], 10 );
		add_action( 'woocommerce_process_product_meta', [ $this, 'product_meta' ], 10, 2 );
		add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'add_to_cart_custom_button_detail' ] );
		add_action( 'woocommerce_after_shop_loop_item', [ $this, 'add_to_cart_custom_button_loop' ] );
	}

	/**
	 * Display the custom button field in the advanced tab of the product registration page.
	 */
	public function custom_button_options_advanced() {
		echo '<div class="options_group">';

		woocommerce_wp_checkbox( [
		        'id' => '_payletter_pay_wc_custom_button',
		        'value' => get_post_meta( get_the_ID(), '_payletter_pay_wc_custom_button', true ),
		        'label' => 'Use custom buttons',
		        'desc_tip' => true,
		        'description' => 'When checked, a custom button is displayed on the product detail page.',
        ] );

		woocommerce_wp_checkbox( [
			'id' => '_payletter_pay_wc_custom_button_on_loop',
			'wrapper_class' => 'payletter_wc_custom_button hidden',
			'value' => get_post_meta( get_the_ID(), '_payletter_pay_wc_custom_button_on_loop', true ),
			'label' => 'Show in product list',
			'desc_tip' => true,
			'description' => 'When checked, a custom button is displayed on the product list page.<br>※ It may not be visible in some themes..',
		] );

		woocommerce_wp_checkbox( [
			'id' => '_payletter_pay_wc_custom_button_text',
			'wrapper_class' => 'payletter_wc_custom_button hidden',
			'value' => get_post_meta( get_the_ID(), '_payletter_pay_wc_custom_button_text', true ),
			'label' => 'button text',
			'desc_tip' => true,
			'description' => 'Please enter the text to be displayed on the button.',
		] );

		woocommerce_wp_checkbox( [
			'id' => '_payletter_pay_wc_custom_button_url',
			'wrapper_class' => 'payletter_wc_custom_button hidden',
			'value' => get_post_meta( get_the_ID(), '_payletter_pay_wc_custom_button_url', true ),
			'label' => 'button link',
			'desc_tip' => true,
			'description' => 'Please enter the link to go to when the button is clicked.',
		] );

		woocommerce_wp_checkbox( [
			'id' => '_payletter_pay_wc_custom_button_css',
			'wrapper_class' => 'payletter_wc_custom_button hidden',
			'value' => get_post_meta( get_the_ID(), '_payletter_pay_wc_custom_button_css', true ),
			'label' => 'CSS class',
			'desc_tip' => true,
			'description' => 'Enter the CSS class to be applied to the button.',
		] );

		woocommerce_wp_checkbox( [
			'id' => '_payletter_pay_wc_custom_button_open_new_tab',
			'wrapper_class' => 'payletter_wc_custom_button hidden',
			'value' => get_post_meta( get_the_ID(), '_payletter_pay_wc_custom_button_open_new_tab', true ),
			'label' => 'open in new tab',
			'desc_tip' => true,
			'description' => 'When you click the button, it opens in a new tab.',
		] );

		echo '</div>';
		?>
        <script>
            jQuery(document).ready(function () {
                if (jQuery("#_payletter_pay_wc_custom_button").prop('checked') == true) {
                    jQuery('.payletter_wc_custom_button').removeClass('hidden');
                }
            });

            jQuery("#_payletter_pay_wc_custom_button").on('click', function () {
                if (jQuery(this).prop('checked') == true) {
                    jQuery('.payletter_wc_custom_button').removeClass('hidden');
                } else {
                    jQuery('.payletter_wc_custom_button').addClass('hidden');
                }
            });
        </script>
		<?php
	}

	/**
	 * Store product custom button information
	 */
	public function product_meta( $id, $post ) {
		$payletter_pay_wc_custom_button              = isset( $_POST['_payletter_pay_wc_custom_button'] ) && $_POST['_payletter_pay_wc_custom_button'] ? sanitize_text_field( $_POST['_payletter_pay_wc_custom_button'] ) : '';
		$payletter_pay_wc_custom_button_on_loop      = isset( $_POST['_payletter_pay_wc_custom_button_on_loop'] ) && $_POST['_payletter_pay_wc_custom_button_on_loop'] ? sanitize_text_field( $_POST['_payletter_pay_wc_custom_button_on_loop'] ) : '';
		$payletter_pay_wc_custom_button_text         = isset( $_POST['_payletter_pay_wc_custom_button_text'] ) && $_POST['_payletter_pay_wc_custom_button_text'] ? sanitize_text_field( $_POST['_payletter_pay_wc_custom_button_text'] ) : '';
		$payletter_pay_wc_custom_button_url          = isset( $_POST['_payletter_pay_wc_custom_button_url'] ) && $_POST['_payletter_pay_wc_custom_button_url'] ? sanitize_text_field( $_POST['_payletter_pay_wc_custom_button_url'] ) : '';
		$payletter_pay_wc_custom_button_css          = isset( $_POST['_payletter_pay_wc_custom_button_css'] ) && $_POST['_payletter_pay_wc_custom_button_css'] ? sanitize_text_field( $_POST['_payletter_pay_wc_custom_button_css'] ) : '';
		$payletter_pay_wc_custom_button_open_new_tab = isset( $_POST['_payletter_pay_wc_custom_button_open_new_tab'] ) && $_POST['_payletter_pay_wc_custom_button_open_new_tab'] ? sanitize_text_field( $_POST['_payletter_pay_wc_custom_button_open_new_tab'] ) : '';

		if ( $payletter_pay_wc_custom_button ) {
			update_post_meta( $id, '_payletter_pay_wc_custom_button', $payletter_pay_wc_custom_button );
		} else {
			delete_post_meta( $id, '_payletter_pay_wc_custom_button' );
		}

		if ( $payletter_pay_wc_custom_button_on_loop ) {
			update_post_meta( $id, '_payletter_pay_wc_custom_button_on_loop', $payletter_pay_wc_custom_button_on_loop );
		} else {
			delete_post_meta( $id, '_payletter_pay_wc_custom_button_on_loop' );
		}

		if ( $payletter_pay_wc_custom_button_text ) {
			update_post_meta( $id, '_payletter_pay_wc_custom_button_text', sanitize_text_field( $_POST['_payletter_pay_wc_custom_button_text'] ) );
		} else {
			delete_post_meta( $id, '_payletter_pay_wc_custom_button_text' );
		}

		if ( $payletter_pay_wc_custom_button_url ) {
			update_post_meta( $id, '_payletter_pay_wc_custom_button_url', $payletter_pay_wc_custom_button_url );
		} else {
			delete_post_meta( $id, '_payletter_pay_wc_custom_button_url' );
		}

		if ( $payletter_pay_wc_custom_button_css ) {
			update_post_meta( $id, '_payletter_pay_wc_custom_button_css', $payletter_pay_wc_custom_button_css );
		} else {
			delete_post_meta( $id, '_payletter_pay_wc_custom_button_css' );
		}

		if ( $payletter_pay_wc_custom_button_open_new_tab ) {
			update_post_meta( $id, '_payletter_pay_wc_custom_button_open_new_tab', $payletter_pay_wc_custom_button_open_new_tab );
		} else {
			delete_post_meta( $id, '_payletter_pay_wc_custom_button_open_new_tab' );
		}
	}

	/**
	 * Display a custom button on the product detail page.
	 */
	public function add_to_cart_custom_button_detail() {
		global $product;
		$product_id = $product->get_id();

		$is_use_custom_button = get_post_meta( $product_id, '_payletter_pay_wc_custom_button', true );
		$button_text          = get_post_meta( $product_id, '_payletter_pay_wc_custom_button_text', true );
		$button_url           = get_post_meta( $product_id, '_payletter_pay_wc_custom_button_url', true );
		$button_css           = get_post_meta( $product_id, '_payletter_pay_wc_custom_button_css', true );
		$is_open_new_tab      = get_post_meta( $product_id, '_payletter_pay_wc_custom_button_open_new_tab', true );

		$button = '';
		if ( $is_use_custom_button ) {
			ob_start();
			?>
            <a <?php echo $button_url ? 'href="' . esc_url( $button_url ) . '"' : '' ?>
                    class="button alt <?php echo $button_css ? esc_attr( $button_css ) : '' ?>"<?php echo $is_open_new_tab ? ' target="_blank"' : '' ?>
                    style="margin-left: 5px;"><?php echo $button_text ? esc_html( $button_text ) : '' ?></a>
			<?php
			$button = ob_get_clean();
			echo $button;
		}
	}

	/**
	 * Display a custom button on the product list page.
	 */
	public function add_to_cart_custom_button_loop() {
		global $product;
		$product_id = $product->get_id();

		$is_use_custom_button      = get_post_meta( $product_id, '_payletter_pay_wc_custom_button', true );
		$is_use_custom_button_loop = get_post_meta( $product_id, '_payletter_pay_wc_custom_button_on_loop', true );
		$button_text               = get_post_meta( $product_id, '_payletter_pay_wc_custom_button_text', true );
		$button_url                = get_post_meta( $product_id, '_payletter_pay_wc_custom_button_url', true );
		$button_css                = get_post_meta( $product_id, '_payletter_pay_wc_custom_button_css', true );
		$is_open_new_tab           = get_post_meta( $product_id, '_payletter_pay_wc_custom_button_open_new_tab', true );

		$button = '';
		if ( $is_use_custom_button && $is_use_custom_button_loop ) {
			ob_start();
			?>
            <a <?php echo $button_url ? 'href="' . esc_url_raw( $button_url ) . '"' : '' ?>
                    class="button <?php echo $button_css ? esc_attr( $button_css ) : '' ?>"<?php echo $is_open_new_tab ? ' target="_blank"' : '' ?>><?php echo $button_text ? esc_html( $button_text ) : '' ?></a>
			<?php
			$button = ob_get_clean();
			echo $button;
		}
	}
}
