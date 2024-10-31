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
            width: 100%;
            height: 100%;
            min-width: 100%;
            *width: 100%;
        }
    </style>
</head>
<body onload="payletter_start()">
<div style="display:none">
    <form id="send-pay-form" action="<?php echo esc_url( $this->request_url ) ?>" method="post" name="payletter_payment">
		<?php foreach ( $api->get_init_values() as $name => $value ): ?>
            <input type="hidden" name="<?php echo esc_attr( $name ) ?>" value="<?php echo esc_attr( $value ) ?>">
		<?php endforeach ?>
    </form>
    <script>
        function payletter_start() {
            document.forms['payletter_payment'].submit();
        }
    </script>
</div>
</body>
</html>