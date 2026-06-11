/**
 * Easy Hotel — Native Checkout frontend (multi-accommodation cart).
 *
 * Server-authoritative pricing: any change that affects the total (editing
 * extra services on an item, applying/removing a coupon, removing an item)
 * round-trips to the server, which recomputes the whole-cart pricing and
 * returns it. The JS just renders whatever the server returns, so the
 * displayed total always matches what will be charged.
 */
(function ($) {
    'use strict';

    if (typeof window.eshbNativeCheckout === 'undefined') return;

    var state = {
        config: window.eshbNativeCheckout,
        pricing: window.eshbNativeCheckout.pricing || {},
        coupon: { code: '', valid: false },
        gateway: '',
        recalcTimer: null
    };

    // Seed coupon state from the server payload (e.g. user navigated back).
    if (state.pricing && state.pricing.couponValid && state.pricing.couponCode) {
        state.coupon = { code: state.pricing.couponCode, valid: true };
    }

    /* -----------------------------------------------------------------
     * Pricing rendering
     * --------------------------------------------------------------- */
    function applyPricing(pricing) {
        if (!pricing || typeof pricing !== 'object') return;
        state.pricing = pricing;

        // Whole-cart figures bound by [data-eshb-price].
        $('[data-eshb-price]').each(function () {
            var key = $(this).attr('data-eshb-price');
            if (typeof pricing[key] === 'undefined') return;
            if (key === 'couponDiscountHtml') {
                $(this).html('- ' + pricing[key]);
            } else {
                $(this).html(pricing[key]);
            }
        });

        // Conditional rows.
        $('[data-eshb-row="coupon"]').toggle(parseFloat(pricing.couponDiscount || 0) > 0);
        $('[data-eshb-row="tax"]').toggle(parseFloat(pricing.taxAmount || 0) > 0);
        $('[data-eshb-coupon-code]').text(pricing.couponCode || '');
        $('[data-eshb-tax-rate]').text(pricing.taxRate || 0);

        // Per-item totals (pricing.items is keyed by item key).
        var items = pricing.items || {};
        Object.keys(items).forEach(function (key) {
            var it = items[key] || {};
            $('[data-eshb-item-total="' + key + '"]').html(it.totalPriceHtml || '');
        });
    }

    /* -----------------------------------------------------------------
     * Collect per-item service selections for the server
     * --------------------------------------------------------------- */
    function collectItemsServices() {
        var out = {};
        $('.eshb-cart-item').each(function () {
            var key = $(this).attr('data-item-key');
            if (!key) return;
            var svcs = [];
            $(this).find('.eshb-service-option').each(function () {
                var $opt = $(this);
                if (!$opt.find('input[type="checkbox"]').prop('checked')) return;
                var id = parseInt($opt.attr('data-service-id'), 10);
                if (!id) return;
                var qty = parseInt($opt.find('input[data-service-qty]').val(), 10) || 1;
                svcs.push({ id: id, quantity: Math.max(1, qty) });
            });
            out[key] = svcs;
        });
        return out;
    }

    function getCustomer() {
        var $form = $('#eshbNativeCheckoutForm');
        return {
            firstName: $form.find('[name="firstName"]').val(),
            lastName: $form.find('[name="lastName"]').val(),
            email: $form.find('[name="email"]').val(),
            phone: $form.find('[name="phone"]').val(),
            country: $form.find('[name="country"]').val(),
            state: $form.find('[name="state"]').val(),
            city: $form.find('[name="city"]').val(),
            notes: $form.find('textarea[name="notes"]').val()
        };
    }

    function ajaxData(extra) {
        var customer = getCustomer();
        var payload = {
            nonce: state.config.nonce,
            gateway: state.gateway,
            coupon: state.coupon.valid ? state.coupon.code : '',
            itemsServices: JSON.stringify(collectItemsServices()),
            firstName: customer.firstName,
            lastName: customer.lastName,
            email: customer.email,
            phone: customer.phone,
            country: customer.country,
            state: customer.state,
            city: customer.city,
            notes: customer.notes
        };
        // Reservation token: cookies can be stripped on some live hosts, so
        // always carry it in the request body.
        if (state.config.token && state.config.tokenParam) {
            payload[state.config.tokenParam] = state.config.token;
        }
        return $.extend(payload, extra || {});
    }

    /* -----------------------------------------------------------------
     * Live recalculation (debounced) on service edits
     * --------------------------------------------------------------- */
    function scheduleRecalc() {
        if (state.recalcTimer) clearTimeout(state.recalcTimer);
        state.recalcTimer = setTimeout(recalc, 400);
    }

    function recalc() {
        $.post(state.config.ajaxUrl, ajaxData({ action: 'eshb_native_recalculate' }))
            .done(function (resp) {
                if (resp && resp.success && resp.data && resp.data.pricing) {
                    applyPricing(resp.data.pricing);
                }
            });
    }

    function updateItemSummary($item) {
        var titles = [];
        $item.find('.eshb-service-option').each(function () {
            var $opt = $(this);
            if (!$opt.find('input[type="checkbox"]').prop('checked')) return;
            var title = ($opt.find('.eshb-choice-title').text() || '').trim();
            var qty = parseInt($opt.find('input[data-service-qty]').val(), 10) || 1;
            titles.push(qty > 1 ? title + ' × ' + qty : title);
        });
        var fallback = (state.config.i18n && state.config.i18n.noServicesSelected) || 'None selected';
        $item.find('.eshb-services-summary-list').text(titles.length ? titles.join(', ') : fallback);
    }

    function bindServiceEvents() {
        // Toggle the services editor open/closed per item.
        $(document).on('click', '.eshb-services-edit-toggle', function (e) {
            e.preventDefault();
            var $editor = $(this).closest('.eshb-cart-item').find('.eshb-services-editor');
            var i18n = state.config.i18n || {};
            if ($editor.prop('hidden')) {
                $editor.prop('hidden', false);
                $(this).text(i18n.doneEditingServices || 'Done');
            } else {
                $editor.prop('hidden', true);
                $(this).text(i18n.editServices || 'Edit');
            }
        });

        $(document).on('change', '.eshb-service-option input[type="checkbox"]', function () {
            var $opt = $(this).closest('.eshb-service-option');
            $opt.find('.eshb-service-qty').css('display', this.checked ? 'flex' : 'none');
            updateItemSummary($opt.closest('.eshb-cart-item'));
            scheduleRecalc();
        });

        $(document).on('click', '.eshb-service-option .eshb-qty-btn', function () {
            var dir = parseInt($(this).data('dir'), 10);
            var $input = $(this).siblings('input[data-service-qty]');
            var next = (parseInt($input.val(), 10) || 1) + dir;
            $input.val(Math.max(1, next));
            updateItemSummary($(this).closest('.eshb-cart-item'));
            scheduleRecalc();
        });

        $(document).on('input change', '.eshb-service-option input[data-service-qty]', function () {
            updateItemSummary($(this).closest('.eshb-cart-item'));
            scheduleRecalc();
        });
    }

    /* -----------------------------------------------------------------
     * Remove an accommodation from the cart
     * --------------------------------------------------------------- */
    function bindRemoveItem() {
        $(document).on('click', '.eshb-remove-item', function () {
            var key = $(this).attr('data-item-key');
            if (!key) return;
            var msg = (state.config.i18n && state.config.i18n.confirmRemove) || 'Remove this accommodation?';
            if (!window.confirm(msg)) return;

            var $btn = $(this);
            $btn.prop('disabled', true);

            $.post(state.config.ajaxUrl, ajaxData({ action: 'eshb_native_remove_item', item_key: key }))
                .done(function (resp) {
                    if (resp && resp.success) {
                        if (resp.data.cart_empty) {
                            window.location.reload();
                            return;
                        }
                        $('.eshb-cart-item[data-item-key="' + key + '"]').remove();
                        applyPricing(resp.data.pricing);
                    } else {
                        $btn.prop('disabled', false);
                        showError((resp && resp.data && resp.data.message) || state.config.i18n.paymentFailed);
                    }
                })
                .fail(function () {
                    $btn.prop('disabled', false);
                    showError(state.config.i18n.paymentFailed);
                });
        });
    }

    /* -----------------------------------------------------------------
     * Coupon
     * --------------------------------------------------------------- */
    function bindCouponEvents() {
        var $toggle = $('#eshbCouponToggle');
        var $prompt = $('.eshb-coupon-prompt');
        var $panel = $('#eshbCouponPanel');
        var $code = $('#eshbCouponCode');
        var $apply = $('#eshbApplyCoupon');
        var $remove = $('#eshbRemoveCoupon');
        var $msg = $('#eshbCouponMessage');

        $toggle.on('click', function (e) {
            e.preventDefault();
            $prompt.prop('hidden', true);
            $panel.prop('hidden', false);
            $toggle.attr('aria-expanded', 'true');
            $code.trigger('focus');
        });

        $apply.on('click', function () {
            var code = ($code.val() || '').trim();
            if (!code) {
                $msg.text(state.config.i18n.invalidCoupon || '').addClass('eshb-msg-error');
                return;
            }
            $apply.prop('disabled', true);
            $msg.removeClass('eshb-msg-error eshb-msg-success').text(state.config.i18n.couponApplying);

            $.post(state.config.ajaxUrl, ajaxData({ action: 'eshb_native_apply_coupon', coupon: code }))
                .done(function (resp) {
                    if (resp && resp.success && resp.data && resp.data.pricing && resp.data.pricing.couponValid) {
                        state.coupon = { code: resp.data.pricing.couponCode, valid: true };
                        $msg.text(resp.data.pricing.couponMessage || '').addClass('eshb-msg-success').removeClass('eshb-msg-error');
                        $remove.show();
                        $apply.hide();
                        $code.prop('disabled', true);
                        applyPricing(resp.data.pricing);
                    } else {
                        var err = (resp && resp.data && resp.data.message) || 'Invalid coupon';
                        $msg.text(err).addClass('eshb-msg-error').removeClass('eshb-msg-success');
                        state.coupon = { code: '', valid: false };
                        if (resp && resp.data && resp.data.pricing) {
                            applyPricing(resp.data.pricing);
                        }
                        // Per-user coupon needs the email — focus it.
                        if (/email/i.test(err)) {
                            var $emailInput = $('#eshbNativeCheckoutForm [name="email"]');
                            if ($emailInput.length) {
                                $emailInput[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                setTimeout(function () { $emailInput.trigger('focus'); }, 350);
                            }
                        }
                    }
                })
                .fail(function () {
                    $msg.text(state.config.i18n.paymentFailed).addClass('eshb-msg-error');
                })
                .always(function () {
                    $apply.prop('disabled', false);
                });
        });

        $remove.on('click', function () {
            state.coupon = { code: '', valid: false };
            $code.prop('disabled', false).val('');
            $msg.text(state.config.i18n.couponRemoved).removeClass('eshb-msg-error eshb-msg-success');
            $remove.hide();
            $apply.show();
            $panel.prop('hidden', true);
            $prompt.prop('hidden', false);
            $toggle.attr('aria-expanded', 'false');
            recalc();
        });
    }

    /* -----------------------------------------------------------------
     * Gateways / form
     * --------------------------------------------------------------- */
    function bindGatewaySelection() {
        $('input[name="eshbPaymentMethod"]').on('change', function () {
            state.gateway = $(this).val();
            $('#eshbGatewayMount > div').hide();
            $('#eshbGatewayMount [data-gateway="' + state.gateway + '"]').show();
            if (state.gateway === 'paypal') {
                $('#eshbCheckoutSubmit').hide();
                initPayPalButtons();
            } else {
                $('#eshbCheckoutSubmit').show();
            }
        });
    }

    function showError(msg) {
        $('#eshbCheckoutError').text(msg).show();
        setTimeout(function () { $('#eshbCheckoutError').fadeOut(); }, 6000);
    }

    function clearFieldErrors() {
        $('#eshbNativeCheckoutForm .eshb-error-input').removeClass('eshb-error-input');
    }

    function markFieldError(selector) {
        var $field = $('#eshbNativeCheckoutForm').find(selector).first();
        if (!$field.length) return null;
        $field.addClass('eshb-error-input');
        $field.one('input change', function () { $(this).removeClass('eshb-error-input'); });
        return $field;
    }

    function validateForm() {
        clearFieldErrors();
        var customer = getCustomer();
        var requiredFields = [
            { key: 'firstName', selector: '[name="firstName"]' },
            { key: 'lastName',  selector: '[name="lastName"]' },
            { key: 'email',     selector: '[name="email"]' },
            { key: 'phone',     selector: '[name="phone"]' },
            { key: 'country',   selector: '[name="country"]' },
            { key: 'city',      selector: '[name="city"]' }
        ];

        var firstInvalid = null;
        for (var i = 0; i < requiredFields.length; i++) {
            if (!customer[requiredFields[i].key]) {
                var $marked = markFieldError(requiredFields[i].selector);
                if (!firstInvalid && $marked) firstInvalid = $marked;
            }
        }
        if (!$('#eshbStateSelect').prop('disabled') && !customer.state) {
            var $stateMarked = markFieldError('[name="state"]');
            if (!firstInvalid && $stateMarked) firstInvalid = $stateMarked;
        }

        if (firstInvalid) {
            showError(state.config.i18n.missingFields);
            firstInvalid.trigger('focus');
            return null;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(customer.email)) {
            markFieldError('[name="email"]').trigger('focus');
            showError(state.config.i18n.invalidEmail);
            return null;
        }
        if (!$('#eshbTerms').prop('checked')) {
            markFieldError('#eshbTerms');
            showError(state.config.i18n.missingTerms);
            return null;
        }
        if (!state.gateway) {
            showError(state.config.i18n.missingPayment);
            return null;
        }
        return customer;
    }

    function initLocationSelects() {
        var $country = $('#eshbCountrySelect');
        var $stateSel = $('#eshbStateSelect');
        var $stateGroup = $('#eshbStateGroup');
        if (!$country.length || !state.config.countriesJsonUrl) return;

        $.getJSON(state.config.countriesJsonUrl).done(function (data) {
            if (!Array.isArray(data)) return;
            state.countries = data.slice().sort(function (a, b) {
                return (a.name || '').localeCompare(b.name || '');
            });
            state.countries.forEach(function (c) {
                $country.append($('<option/>', { value: c.code2.trim(), text: c.name }));
            });
        }).fail(function () {
            var $fallback = $('<input type="text" name="country" required>');
            $country.replaceWith($fallback);
            $stateSel.replaceWith($('<input type="text" name="state">'));
        });

        $country.on('change', function () {
            var name = $country.val();
            $stateSel.empty().append($('<option/>', { value: '', text: ($stateSel.find('option').first().text() || 'Select a state…') }));
            var match = (state.countries || []).find(function (c) { return c.code2.trim() === name; });
            var hasStates = match && Array.isArray(match.states) && match.states.length > 0;
            if (hasStates) {
                match.states.slice()
                    .sort(function (a, b) { return (a.name || '').localeCompare(b.name || ''); })
                    .forEach(function (s) {
                        $stateSel.append($('<option/>', { value: s.name, text: s.name }));
                    });
                $stateSel.prop('disabled', false);
                $stateGroup.show();
            } else {
                $stateSel.prop('disabled', true).val('');
                $stateGroup.hide();
            }
        });
    }

    function completeCheckout(gatewayParams) {
        return $.post(state.config.ajaxUrl, $.extend(ajaxData({
            action: 'eshb_native_complete_checkout'
        }), { gatewayParams: gatewayParams || {} }));
    }

    // Clear the saved cart token so the next booking starts a fresh cart
    // (the cart was consumed/cleared server-side on completion).
    function clearCartToken() {
        try { sessionStorage.removeItem('eshb_native_checkout_token'); } catch (e) { /* ignore */ }
    }

    function initPayPalButtons() {
        if (typeof window.paypal === 'undefined' || !window.paypal.Buttons) return;
        var $mount = $('#eshbPayPalButtons');
        if ($mount.data('rendered')) return;
        $mount.data('rendered', true);
        $mount.empty();

        window.paypal.Buttons({
            style: { layout: 'vertical', shape: 'rect' },
            onClick: function (data, actions) {
                var customer = validateForm();
                if (!customer) return actions.reject();
                return actions.resolve();
            },
            createOrder: function () {
                return $.post(state.config.ajaxUrl, ajaxData({
                    action: 'eshb_native_create_payment'
                })).then(function (resp) {
                    if (!resp || !resp.success || !resp.data || !resp.data.order_id) {
                        var msg = (resp && resp.data && resp.data.message) || state.config.i18n.paymentFailed;
                        showError(msg);
                        throw new Error(msg);
                    }
                    return resp.data.order_id;
                });
            },
            onApprove: function (data) {
                return completeCheckout({ order_id: data.orderID }).then(function (resp) {
                    if (resp && resp.success && resp.data && resp.data.redirect_url) {
                        clearCartToken();
                        window.location.href = resp.data.redirect_url;
                    } else {
                        showError((resp && resp.data && resp.data.message) || state.config.i18n.paymentFailed);
                    }
                }).fail(function () {
                    showError(state.config.i18n.paymentFailed);
                });
            },
            onError: function () {
                showError(state.config.i18n.paymentFailed);
            }
        }).render('#eshbPayPalButtons');
    }

    function submitOfflineCheckout() {
        var $btn = $('#eshbCheckoutSubmit');
        if ($btn.prop('disabled')) return;
        var originalText = $btn.text();
        $btn.prop('disabled', true).text(state.config.i18n.processing || 'Processing…');

        completeCheckout({}).done(function (resp) {
            if (resp && resp.success && resp.data && resp.data.redirect_url) {
                clearCartToken();
                window.location.href = resp.data.redirect_url;
            } else {
                showError((resp && resp.data && resp.data.message) || state.config.i18n.paymentFailed);
                $btn.prop('disabled', false).text(originalText);
            }
        }).fail(function () {
            showError(state.config.i18n.paymentFailed);
            $btn.prop('disabled', false).text(originalText);
        });
    }

    function bindFormSubmit() {
        $('#eshbNativeCheckoutForm').on('submit', function (e) {
            e.preventDefault();
            var customer = validateForm();
            if (!customer) return;
            if (state.gateway === 'paypal') {
                showError('Please use the PayPal button above to complete your payment.');
                return;
            }
            submitOfflineCheckout();
        });
    }

    /* -----------------------------------------------------------------
     * Cart-blocking hold countdown
     * --------------------------------------------------------------- */
    function releaseReservationAndReload() {
        var payload = { action: 'eshb_native_release_reservation', nonce: state.config.nonce };
        if (state.config.token && state.config.tokenParam) {
            payload[state.config.tokenParam] = state.config.token;
        }
        clearCartToken();
        $.post(state.config.ajaxUrl, payload).always(function () {
            window.location.reload();
        });
    }

    function initCartBlockTimer() {
        var cb = state.config.cartBlock;
        if (!cb || !cb.enabled || !cb.until) return;
        var notice = document.getElementById('eshb-cart-block-notice');
        if (!notice) return;

        var untilMs = parseInt(cb.until, 10) * 1000;
        if (!untilMs || untilMs <= Date.now()) {
            releaseReservationAndReload();
            return;
        }

        notice.style.display = 'block';
        var timerEl = notice.querySelector('.eshb-block-timer');

        var intervalId = setInterval(function () {
            var remaining = Math.floor((untilMs - Date.now()) / 1000);
            if (remaining <= 0) {
                clearInterval(intervalId);
                if (timerEl) timerEl.textContent = '0:00';
                releaseReservationAndReload();
                return;
            }
            var mins = Math.floor(remaining / 60);
            var secs = remaining % 60;
            if (timerEl) timerEl.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
        }, 1000);
    }

    /* -----------------------------------------------------------------
     * Init
     * --------------------------------------------------------------- */
    $(function () {
        bindServiceEvents();
        bindRemoveItem();
        bindCouponEvents();
        bindGatewaySelection();
        bindFormSubmit();
        initLocationSelects();
        applyPricing(state.pricing);
        initCartBlockTimer();

        $('input[name="eshbPaymentMethod"]:checked').trigger('change');
    });
})(jQuery);
