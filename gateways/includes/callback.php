<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>
<!DOCTYPE html>
<html <?php language_attributes() ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="robots" content="noindex,follow">
    <title><?php wp_title( '' ) ?></title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 1px;
            min-width: 100%;
            *width: 100%;
        }
    </style>
    <script>
        function payletter_callback() {
			<?php if($success == 'true'):?>
            window.opener.payletter_pay_woocommerce['payletter_plcreditcard'].close_dialog({
                success: true,
                message: '<?php echo esc_js( $message )?>',
                error_msg: '<?php echo esc_js( $error_msg )?>',
                pay_success_url: '<?php echo esc_url_raw( $pay_success_url )?>',
            });
            window.alert('<?php echo esc_js( $message )?>');
            parent.window.close();
			<?php endif?>
        }
    </script>
</head>
<body onload="payletter_callback()">
</body>
</html>
