/**
 * Easy Hotel — Native Checkout frontend.
 *
 * Dynamic price recalculation happens entirely client-side using the
 * `eshbNativeCheckout.pricing` baseline localized from PHP. We only
 * round-trip to the server for coupon validation and for payment.
 *
 * The server is still the canonical authority on price: just before
 * payment is captured, the AJAX endpoint recalculates pricing from the
 * persisted reservation, so client tampering cannot affect the charge.
 */
(function ($) {
    'use strict';

    if (typeof window.eshbNativeCheckout === 'undefined') return;

    var state = {
        config: window.eshbNativeCheckout,
        reservation: window.eshbNativeCheckout.reservation || {},
        pricing: window.eshbNativeCheckout.pricing || {},
        services: (window.eshbNativeCheckout.reservation && window.eshbNativeCheckout.reservation.services) || [],
        selectedServices: collectInitialSelections(),
        coupon: { code: '', discount: 0, valid: false },
        gateway: ''
    };

    function collectInitialSelections() {
        var out = [];
        var initial = (window.eshbNativeCheckout.reservation && window.eshbNativeCheckout.reservation.selected_services) || {};
        Object.keys(initial).forEach(function (id) {
            out.push({ id: parseInt(id, 10), quantity: parseInt(initial[id], 10) || 1 });
        });
        return out;
    }

    function nights() {
        return parseInt(state.pricing.daysCount || 0, 10) || 0;
    }

    function formatPrice(value) {
        var n = Number(value) || 0;
        var symbol = state.pricing.currencySymbol || '$';
        var formatted = n.toFixed(2);
        return state.pricing.currencyPosition === 'right'
            ? formatted + symbol
            : symbol + formatted;
    }

    function calculateServicesPrice() {
        // Read service definitions directly from the DOM rather than the
        // localized state.services lookup — the template renders each
        // option with data-service-price / data-service-periodicity /
        // data-service-charge-type attributes, so this is the source of
        // truth and avoids any localization drift. Quantity always
        // multiplies, regardless of charge type (the server is kept in
        // sync with this math in ESHB_Native_Pricing).
        var total = 0;
        var days = nights() || 1;

        $('.eshb-service-option').each(function () {
            var $opt = $(this);
            if (!$opt.find('input[type="checkbox"]').prop('checked')) return;

            var price = parseFloat($opt.attr('data-service-price')) || 0;
            var periodicity = $opt.attr('data-service-periodicity') || 'once';
            var qty = parseInt($opt.find('input[data-service-qty]').val(), 10) || 1;

            if (periodicity === 'per_day') price *= days;
            price *= Math.max(1, qty);

            total += price;
        });

        return total;
    }

    function recalcLocal() {
        // Base pricing comes from the server's initial computation. We
        // only update the parts that change due to client actions:
        // extras quantity, coupon discount, and tax.
        var pricing = state.pricing;
        var subtotalAccommodation = parseFloat(pricing.subtotalPrice || 0) - parseFloat(pricing.extraServicesPrice || 0);
        var extraServicesPrice = calculateServicesPrice();
        var newSubtotal = subtotalAccommodation + extraServicesPrice;

        var couponDiscount = state.coupon.valid ? state.coupon.discount : 0;
        if (couponDiscount > newSubtotal) couponDiscount = newSubtotal;
        var afterCoupon = Math.max(0, newSubtotal - couponDiscount);

        var taxRate = parseFloat(pricing.taxRate || 0);
        var taxAmount = taxRate > 0 ? Math.round((afterCoupon * taxRate) ) / 100 : 0;
        // taxAmount = afterCoupon * (taxRate/100) — use simple formula:
        taxAmount = taxRate > 0 ? +(afterCoupon * taxRate / 100).toFixed(2) : 0;

        var grandTotal = afterCoupon + taxAmount;

        // Mutate the local pricing object so further recalcs are based on the latest extras.
        state.pricing.extraServicesPrice = extraServicesPrice;
        state.pricing.extraServicesPriceHtml = formatPrice(extraServicesPrice);
        state.pricing.subtotalPrice = newSubtotal;
        state.pricing.subtotalPriceHtml = formatPrice(newSubtotal);
        state.pricing.couponDiscount = couponDiscount;
        state.pricing.couponDiscountHtml = formatPrice(couponDiscount);
        state.pricing.taxAmount = taxAmount;
        state.pricing.taxAmountHtml = formatPrice(taxAmount);
        state.pricing.grandTotal = grandTotal;
        state.pricing.grandTotalHtml = formatPrice(grandTotal);

        applyPricingToDOM();
    }

    function applyPricingToDOM() {
        $('[data-eshb-price]').each(function () {
            var key = $(this).data('eshbPrice');
            if (typeof state.pricing[key] !== 'undefined') {
                if (key === 'couponDiscountHtml') {
                    $(this).html('- ' + state.pricing[key]);
                } else {
                    $(this).html(state.pricing[key]);
                }
            }
        });

        // Show/hide conditional rows.
        $('[data-eshb-row="extraBed"]').toggle(parseFloat(state.pricing.extraBedPrice || 0) > 0);
        $('[data-eshb-row="services"]').toggle(parseFloat(state.pricing.extraServicesPrice || 0) > 0);
        $('[data-eshb-row="coupon"]').toggle(parseFloat(state.pricing.couponDiscount || 0) > 0);
        $('[data-eshb-row="tax"]').toggle(parseFloat(state.pricing.taxAmount || 0) > 0);
    }

    function syncSelectionsFromDOM() {
        var out = [];
        $('.eshb-service-option').each(function () {
            var $opt = $(this);
            var id = parseInt($opt.data('service-id'), 10);
            var $cb = $opt.find('input[type="checkbox"]');
            if (!$cb.prop('checked')) return;
            var qty = parseInt($opt.find('input[data-service-qty]').val(), 10) || 1;
            out.push({ id: id, quantity: Math.max(1, qty) });
        });
        state.selectedServices = out;
    }

    function refreshServicesSummary() {
        var titles = [];
        $('.eshb-service-option').each(function () {
            var $opt = $(this);
            if (!$opt.find('input[type="checkbox"]').prop('checked')) return;
            var title = ($opt.find('.eshb-choice-title').text() || '').trim();
            var qty = parseInt($opt.find('input[data-service-qty]').val(), 10) || 1;
            titles.push(qty > 1 ? title + ' × ' + qty : title);
        });
        var fallback = (state.config.i18n && state.config.i18n.noServicesSelected) || 'None selected';
        $('#eshbServicesSummaryList').text(titles.length ? titles.join(', ') : fallback);
    }

    function bindServiceEvents() {
        $('.eshb-service-option').on('change', 'input[type="checkbox"]', function () {
            var $opt = $(this).closest('.eshb-service-option');
            $opt.find('.eshb-service-qty').css('display', this.checked ? 'flex' : 'none');
            syncSelectionsFromDOM();
            refreshServicesSummary();
            recalcLocal();
        });

        $('.eshb-service-option').on('click', '.eshb-qty-btn', function () {
            var dir = parseInt($(this).data('dir'), 10);
            var $input = $(this).siblings('input[data-service-qty]');
            var next = (parseInt($input.val(), 10) || 1) + dir;
            $input.val(Math.max(1, next));
            syncSelectionsFromDOM();
            refreshServicesSummary();
            recalcLocal();
        });

        $('.eshb-service-option').on('input change', 'input[data-service-qty]', function () {
            syncSelectionsFromDOM();
            refreshServicesSummary();
            recalcLocal();
        });

        // Toggle the services editor open/closed. The link text flips
        // between "Edit" and "Done" so the user always knows what the
        // click will do next.
        $('#eshbServicesEditToggle').on('click', function (e) {
            e.preventDefault();
            var $editor = $('#eshbServicesEditor');
            var i18n = state.config.i18n || {};
            if ($editor.prop('hidden')) {
                $editor.prop('hidden', false);
                $(this).text(i18n.doneEditingServices || 'Done');
            } else {
                $editor.prop('hidden', true);
                $(this).text(i18n.editServices || 'Edit');
            }
        });
    }

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

            var couponPayload = {
                action: 'eshb_native_apply_coupon',
                nonce: state.config.nonce,
                coupon: code,
                // Send email so the per-user limit is checked at apply
                // time when possible — empty is fine, the server falls
                // back to the global limit check.
                email: ($('#eshbNativeCheckoutForm [name="email"]').val() || ''),
                extraServices: JSON.stringify(state.selectedServices)
            };
            // Reservation token: cookie-less fallback for hosts that
            // strip Set-Cookie from AJAX responses.
            if (state.config.token && state.config.tokenParam) {
                couponPayload[state.config.tokenParam] = state.config.token;
            }
            $.post(state.config.ajaxUrl, couponPayload).done(function (resp) {
                if (resp && resp.success && resp.data && resp.data.pricing && resp.data.pricing.couponValid) {
                    state.coupon.code = resp.data.pricing.couponCode;
                    state.coupon.discount = parseFloat(resp.data.pricing.couponDiscount) || 0;
                    state.coupon.valid = true;
                    $msg.text(resp.data.pricing.couponMessage || '').addClass('eshb-msg-success').removeClass('eshb-msg-error');
                    $remove.show();
                    $apply.hide();
                    $code.prop('disabled', true);
                    recalcLocal();
                } else {
                    var err = (resp && resp.data && resp.data.message) || 'Invalid coupon';
                    $msg.text(err).addClass('eshb-msg-error').removeClass('eshb-msg-success');
                    state.coupon = { code: '', discount: 0, valid: false };
                    // If the server is asking for the email (per-user
                    // coupon, email empty), scroll the field into view
                    // and focus it so the next step is obvious.
                    if (/email/i.test(err)) {
                        var $emailInput = $('#eshbNativeCheckoutForm [name="email"]');
                        if ($emailInput.length) {
                            $emailInput[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                            setTimeout(function () { $emailInput.trigger('focus'); }, 350);
                        }
                    }
                    recalcLocal();
                }
            }).fail(function () {
                $msg.text(state.config.i18n.paymentFailed).addClass('eshb-msg-error');
            }).always(function () {
                $apply.prop('disabled', false);
            });
        });

        $remove.on('click', function () {
            state.coupon = { code: '', discount: 0, valid: false };
            $code.prop('disabled', false).val('');
            $msg.text(state.config.i18n.couponRemoved).removeClass('eshb-msg-error eshb-msg-success');
            $remove.hide();
            $apply.show();
            // Collapse the panel and bring the prompt back so the page
            // returns to the default compact state.
            $panel.prop('hidden', true);
            $prompt.prop('hidden', false);
            $toggle.attr('aria-expanded', 'false');
            recalcLocal();
        });
    }

    function bindGatewaySelection() {
        $('input[name="eshbPaymentMethod"]').on('change', function () {
            state.gateway = $(this).val();
            $('#eshbGatewayMount > div').hide();
            $('#eshbGatewayMount [data-gateway="' + state.gateway + '"]').show();

            // PayPal renders its own buttons inside the mount, so the
            // generic "Book Now" submit button is redundant there.
            // Hide it for PayPal and restore for other gateways.
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

    function clearFieldErrors() {
        $('#eshbNativeCheckoutForm .eshb-error-input').removeClass('eshb-error-input');
    }

    function markFieldError(selector) {
        var $field = $('#eshbNativeCheckoutForm').find(selector).first();
        if (!$field.length) return null;
        $field.addClass('eshb-error-input');
        // Auto-clear the error styling on the next user edit.
        $field.one('input change', function () {
            $(this).removeClass('eshb-error-input');
        });
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
        // State is only required when the country actually has states,
        // mirrored on the server in validate_customer().
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

    function ajaxData(extra) {
        var customer = getCustomer();
        var payload = {
            nonce: state.config.nonce,
            gateway: state.gateway,
            coupon: state.coupon.valid ? state.coupon.code : '',
            firstName: customer.firstName,
            lastName: customer.lastName,
            email: customer.email,
            phone: customer.phone,
            country: customer.country,
            state: customer.state,
            city: customer.city,
            notes: customer.notes,
            extraServices: JSON.stringify(state.selectedServices)
        };
        // Reservation token: cookies can be stripped or unreliable on
        // some live hosts, so always carry it in the request body.
        if (state.config.token && state.config.tokenParam) {
            payload[state.config.tokenParam] = state.config.token;
        }
        return $.extend(payload, extra || {});
    }

    function initLocationSelects() {
        var $country = $('#eshbCountrySelect');
        var $stateSel = $('#eshbStateSelect');
        var $stateGroup = $('#eshbStateGroup');
        if (!$country.length || !state.config.countriesJsonUrl) return;

        // Cache the parsed JSON across page interactions. The file is
        // ~450KB so we fetch once and reuse for the country → state
        // cascade.
        $.getJSON(state.config.countriesJsonUrl).done(function (data) {
            if (!Array.isArray(data)) return;
            state.countries = data.slice().sort(function (a, b) {
                return (a.name || '').localeCompare(b.name || '');
            });
            state.countries.forEach(function (c) {
                $country.append($('<option/>', { value: c.code2.trim(), text: c.name }));
            });
        }).fail(function () {
            // Fail open — if the JSON can't load, fall back to free text
            // inputs so the user can still complete checkout.
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
                // Hide the entire state field for stateless countries so
                // the user isn't staring at a disabled select.
                $stateGroup.hide();
            }
        });
    }

    function completeCheckout(gatewayParams) {
        return $.post(state.config.ajaxUrl, $.extend(ajaxData({
            action: 'eshb_native_complete_checkout'
        }), { gatewayParams: gatewayParams || {} }));
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
                        window.location.href = resp.data.redirect_url;
                    } else {
                        var msg = (resp && resp.data && resp.data.message) || state.config.i18n.paymentFailed;
                        showError(msg);
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

    function bindFormSubmit() {
        $('#eshbNativeCheckoutForm').on('submit', function (e) {
            e.preventDefault();
            var customer = validateForm();
            if (!customer) return;

            // For PayPal we rely on the rendered button — the submit
            // button just nudges the user toward the right control.
            if (state.gateway === 'paypal') {
                showError($('<div/>').text('Please use the PayPal button above to complete your payment.').text());
            }
        });
    }

    $(function () {
        bindServiceEvents();
        bindCouponEvents();
        bindGatewaySelection();
        bindFormSubmit();
        initLocationSelects();
        applyPricingToDOM();
    });
})(jQuery);
