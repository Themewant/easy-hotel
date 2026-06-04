/**
 * Easy Hotel — Customer Account area frontend.
 *
 * Handles the bookings modal (view + cancel), profile update and password
 * change. Everything is AJAX so the customer never leaves the page; the
 * server re-validates ownership and rules on every request.
 */
(function ($) {
    'use strict';

    var cfg = window.eshbNativeAccount;
    if (!cfg) return;

    var $modal = $('#eshbAccountModal');
    var $modalBody = $('#eshbAccountModalBody');

    function i18n(key) {
        return (cfg.i18n && cfg.i18n[key]) || '';
    }

    function post(action, data) {
        return $.post(cfg.ajaxUrl, $.extend({ action: action, nonce: cfg.nonce }, data || {}));
    }

    /* ----------------------------------------------------------------
     * Modal helpers
     * ------------------------------------------------------------- */
    function openModal(html) {
        $modalBody.html(html);
        $modal.prop('hidden', false);
        $('body').addClass('eshb-account-modal-open');
    }

    function closeModal() {
        $modal.prop('hidden', true);
        $modalBody.empty();
        $('body').removeClass('eshb-account-modal-open');
    }

    // Top-of-table success notice (e.g. after a cancellation). Auto-hides.
    var noticeTimer = null;
    function showNotice(message) {
        var $notice = $('[data-eshb-notice]');
        if (!$notice.length) return;
        $notice.text(message).prop('hidden', false);
        if (typeof $notice[0].scrollIntoView === 'function') {
            $notice[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        if (noticeTimer) clearTimeout(noticeTimer);
        noticeTimer = setTimeout(function () { $notice.prop('hidden', true); }, 6000);
    }

    $modal.on('click', '[data-eshb-modal-close]', closeModal);
    $(document).on('keyup', function (e) {
        if (e.key === 'Escape' && !$modal.prop('hidden')) closeModal();
    });

    /* ----------------------------------------------------------------
     * View booking
     * ------------------------------------------------------------- */
    $(document).on('click', '[data-eshb-view]', function () {
        var id = $(this).data('eshb-view');
        openModal('<p class="eshb-account-modal-loading">' + i18n('loading') + '</p>');
        post('eshb_native_account_view_booking', { booking_id: id }).done(function (resp) {
            if (resp && resp.success && resp.data && resp.data.html) {
                $modalBody.html(resp.data.html);
            } else {
                $modalBody.html('<p class="eshb-account-modal-msg is-error">' +
                    ((resp && resp.data && resp.data.message) || i18n('genericError')) + '</p>');
            }
        }).fail(function () {
            $modalBody.html('<p class="eshb-account-modal-msg is-error">' + i18n('genericError') + '</p>');
        });
    });

    /* ----------------------------------------------------------------
     * Cancel booking
     * ------------------------------------------------------------- */
    $(document).on('click', '[data-eshb-cancel]', function () {
        var id = $(this).data('eshb-cancel');
        var tpl = $('#eshbCancelTemplate').html() || '';
        openModal(tpl);
        $modalBody.data('booking-id', id);
    });

    // Reveal the free-text field only when "Other" is chosen.
    $modal.on('change', '[data-eshb-cancel-reason]', function () {
        var isOther = $(this).val() === 'other';
        $modal.find('[data-eshb-cancel-custom]').prop('hidden', !isOther);
    });

    $modal.on('click', '[data-eshb-cancel-confirm]', function () {
        var $btn = $(this);
        var originalLabel = $btn.text();
        var id = $modalBody.data('booking-id');
        var $msg = $modal.find('[data-eshb-cancel-msg]');
        var reason = $modal.find('[data-eshb-cancel-reason]').val() || '';
        var custom = $modal.find('[data-eshb-cancel-custom]').val() || '';

        $btn.prop('disabled', true).text(i18n('processing'));
        $msg.removeClass('is-error is-success').text('');

        post('eshb_native_account_cancel_booking', {
            booking_id: id,
            reason: reason,
            reason_custom: custom
        }).done(function (resp) {
            if (resp && resp.success) {
                // Update the row in place: badge + remove action buttons.
                var $row = $('tr[data-booking-id="' + id + '"]');
                $row.find('[data-eshb-status]')
                    .attr('class', 'eshb-badge eshb-badge--' + (resp.data.status || 'cancelled'))
                    .text(resp.data.status_label || '');
                $row.find('[data-eshb-cancel]').remove();
                closeModal();
                showNotice(resp.data.message || '');
            } else {
                $msg.addClass('is-error').text((resp && resp.data && resp.data.message) || i18n('genericError'));
                $btn.prop('disabled', false).text(originalLabel);
            }
        }).fail(function () {
            $msg.addClass('is-error').text(i18n('genericError'));
            $btn.prop('disabled', false).text(originalLabel);
        });
    });

    /* ----------------------------------------------------------------
     * Profile update
     * ------------------------------------------------------------- */
    $(document).on('submit', '#eshbAccountProfileForm', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $msg = $form.find('[data-eshb-profile-msg]');
        $btn.prop('disabled', true);
        $msg.removeClass('is-error is-success').text('');

        post('eshb_native_account_update_profile', {
            first_name: $form.find('[name="first_name"]').val(),
            last_name: $form.find('[name="last_name"]').val(),
            display_name: $form.find('[name="display_name"]').val(),
            email: $form.find('[name="email"]').val()
        }).done(function (resp) {
            if (resp && resp.success) {
                $msg.addClass('is-success').text((resp.data && resp.data.message) || '');
            } else {
                $msg.addClass('is-error').text((resp && resp.data && resp.data.message) || i18n('genericError'));
            }
        }).fail(function () {
            $msg.addClass('is-error').text(i18n('genericError'));
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    /* ----------------------------------------------------------------
     * Password change
     * ------------------------------------------------------------- */
    $(document).on('submit', '#eshbAccountPasswordForm', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $msg = $form.find('[data-eshb-password-msg]');
        var newPass = $form.find('[name="new_password"]').val();
        var confirm = $form.find('[name="confirm_password"]').val();

        $msg.removeClass('is-error is-success').text('');
        if (newPass !== confirm) {
            $msg.addClass('is-error').text(i18n('passwordMismatch'));
            return;
        }

        $btn.prop('disabled', true);
        post('eshb_native_account_change_password', {
            current_password: $form.find('[name="current_password"]').val(),
            new_password: newPass,
            confirm_password: confirm
        }).done(function (resp) {
            if (resp && resp.success) {
                $msg.addClass('is-success').text((resp.data && resp.data.message) || '');
                if (resp.data && resp.data.redirect) {
                    setTimeout(function () { window.location.href = resp.data.redirect; }, 1500);
                }
            } else {
                $msg.addClass('is-error').text((resp && resp.data && resp.data.message) || i18n('genericError'));
                $btn.prop('disabled', false);
            }
        }).fail(function () {
            $msg.addClass('is-error').text(i18n('genericError'));
            $btn.prop('disabled', false);
        });
    });

})(jQuery);
