/**
 * Easy Hotel — admin booking refund controls.
 *
 * Lives in the "Cancellation Details" metabox on the booking edit screen.
 * Lets an admin refund the full paid amount or a custom amount; the value
 * is deducted from the booking's paid total server-side.
 */
(function ($) {
    'use strict';

    var cfg = window.eshbNativeAccountAdmin;
    if (!cfg) return;

    function i18n(key) {
        return (cfg.i18n && cfg.i18n[key]) || '';
    }

    var $box = $('[data-eshb-refund-box]');
    if (!$box.length) return;

    var $form = $box.find('[data-eshb-refund-form]');
    var $amount = $box.find('[data-eshb-refund-amount]');
    var $msg = $box.find('[data-eshb-refund-msg]');

    // Toggle the refund form.
    $box.on('click', '[data-eshb-refund-toggle]', function () {
        $form.prop('hidden', false);
        $(this).prop('hidden', true);
    });
    $box.on('click', '[data-eshb-refund-cancel]', function () {
        $form.prop('hidden', true);
        $box.find('[data-eshb-refund-toggle]').prop('hidden', false);
        $msg.text('').css('color', '');
        // Reset back to the default "Full amount" state.
        $box.find('input[name="eshb_refund_type"][value="full"]').prop('checked', true);
        $amount.prop('hidden', true).prop('disabled', true).val('');
    });

    // Show + enable the amount input only for a custom refund; hide otherwise.
    $box.on('change', 'input[name="eshb_refund_type"]', function () {
        var isCustom = $box.find('input[name="eshb_refund_type"]:checked').val() === 'custom';
        $amount.prop('hidden', !isCustom).prop('disabled', !isCustom);
        if (isCustom) { $amount.trigger('focus'); } else { $amount.val(''); }
    });

    // Submit the refund.
    $box.on('click', '[data-eshb-refund-submit]', function () {
        var $btn = $(this);
        var type = $box.find('input[name="eshb_refund_type"]:checked').val() || 'full';
        var amount = $amount.val();

        $msg.text('').css('color', '');

        if (type === 'custom' && (!amount || parseFloat(amount) <= 0)) {
            $msg.text(i18n('enterValid')).css('color', '#b91c1c');
            return;
        }
        if (!window.confirm(i18n('confirm'))) {
            return;
        }

        var original = $btn.text();
        $btn.prop('disabled', true).text(i18n('processing'));

        $.post(cfg.ajaxUrl, {
            action: 'eshb_native_admin_refund_booking',
            nonce: cfg.nonce,
            booking_id: $box.data('booking-id'),
            refund_type: type,
            amount: amount
        }).done(function (resp) {
            if (resp && resp.success) {
                var d = resp.data;
                // Update paid + refunded displays.
                $box.find('[data-eshb-paid-display]').html(d.total_paid_html);
                $box.find('[data-eshb-refunded-display]').html(d.total_refunded_html);
                $box.find('[data-eshb-refunded-wrap]').css('display', '');
                $box.find('[data-eshb-full-display]').html(d.total_paid_html);

                // Prepend a log entry.
                $box.find('[data-eshb-refund-log]').append(
                    $('<li/>').html(d.refunded_html + ' — ' + d.date_label + ' (' + d.by_label + ')')
                );

                $msg.text(d.message).css('color', '#166534');
                $amount.val('');

                // Nothing left to refund → retire the controls.
                if (parseFloat(d.total_paid) <= 0) {
                    $form.prop('hidden', true);
                    $box.find('[data-eshb-refund-toggle]').prop('hidden', true);
                } else {
                    $btn.prop('disabled', false).text(original);
                }
            } else {
                $msg.text((resp && resp.data && resp.data.message) || i18n('error')).css('color', '#b91c1c');
                $btn.prop('disabled', false).text(original);
            }
        }).fail(function () {
            $msg.text(i18n('error')).css('color', '#b91c1c');
            $btn.prop('disabled', false).text(original);
        });
    });

})(jQuery);
