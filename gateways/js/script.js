/**
 * @author https://onepix.net
 */

if (!window.payletter_pay_woocommerce) {
    window.payletter_pay_woocommerce = [];
}

jQuery(document).ready(function ($) {
    const $checkout_form = $('form.woocommerce-checkout');
    const $order_review_form = $('form#order_review');

    const $current_form = ($checkout_form.length ? $checkout_form : ($order_review_form.length ? $order_review_form : false))

    if (!$current_form) {
        return;
    }

    window.payletter_pay_woocommerce['payletter_plcreditcard']
        = window.payletter_pay_woocommerce['payletter_plcreditcardmpi']
        = window.payletter_pay_woocommerce['payletter_plunionpay']
        = window.payletter_pay_woocommerce['payletter_paypalexpresscheckout']
        = (function () {

        const open_dialog = function (args) {
            const payment_method = $('input[name="payment_method"]:checked').val();
            const open_dialog_url = payletter_pay_wc_payletter_args[`open_dialog_${payment_method}_url`];

            if (!open_dialog_url) {
                console.log('Wrong open_dialog_url')
                return;
            }

            const $form = $('<form action="' + open_dialog_url + '" method="post" style="display:none"></form>');

            Object.entries(args).forEach(([key, value]) => $('<input type="hidden">').attr('name', key).attr('value', value).appendTo($form))
            $('body').append($form);

            $form.submit();
        }

        const pay = function (args) {
            $.each($current_form.serializeArray(), function () {
                args[this.name] = this.value;
            });

            open_dialog(args);
        }

        return {
            pay: pay,
        };
    })();

    const show_error = function (error_message) {
        $('.woocommerce-error, .woocommerce-message').remove();

        $current_form
            .prepend(error_message)
            .removeClass('processing')
            .unblock()
            .find('.input-text, select')
            .blur();

        $('html, body').animate({scrollTop: $current_form.offset().top - 100}, 1000);
        $(document.body).trigger('checkout_error');
    }

    $current_form.on('submit', function (e) {
        const payment_method = $current_form.find('input[name="payment_method"]:checked').val();

        if ($.inArray(payment_method, payletter_pay_wc_payletter_args.method_list) !== -1) {
            e.preventDefault();
            e.stopImmediatePropagation();

            $current_form.block({message: null})

            $.ajax({
                type: 'POST',
                url: wc_checkout_params.is_checkout === '1' ? wc_checkout_params.checkout_url : wc_checkout_params.ajax_url,
                data: $current_form.serialize(),
                dataType: 'json',
                success: function (result) {
                    try {
                        if (result.result === 'success') {
                            window.payletter_pay_woocommerce[payment_method].pay(result);
                        } else if (result.result === 'failure') {
                            throw 'Result failure';
                        } else {
                            throw 'Invalid response';
                        }
                    } catch (err) {
                        if (result.reload === 'true') {
                            window.location.reload();
                            return;
                        }
                        if (result.refresh === 'true') {
                            $(document.body).trigger('update_checkout');
                        }
                        if (result.messages) {
                            show_error(result.messages);
                        }
                        $current_form.unblock()
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    show_error('<div class="woocommerce-error">' + errorThrown + '</div>');
                    $current_form.unblock()
                }
            });

            return false;
        }
    });
});