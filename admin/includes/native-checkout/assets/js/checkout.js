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

            $.post(state.config.ajaxUrl, {
                action: 'eshb_native_apply_coupon',
                nonce: state.config.nonce,
                coupon: code,
                extraServices: JSON.stringify(state.selectedServices)
            }).done(function (resp) {
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
            if (state.gateway === 'paypal') initPayPalButtons();
        });
    }

    function showError(msg) {
        $('#eshbCheckoutError').text(msg).show();
        setTimeout(function () { $('#eshbCheckoutError').fadeOut(); }, 6000);
    }

    function getCustomer() {
        var $form = $('#eshbNativeCheckoutForm');
        return {
            firstName: $form.find('input[name="firstName"]').val(),
            lastName: $form.find('input[name="lastName"]').val(),
            email: $form.find('input[name="email"]').val(),
            phone: $form.find('input[name="phone"]').val(),
            country: $form.find('input[name="country"]').val(),
            notes: $form.find('textarea[name="notes"]').val()
        };
    }

    function validateForm() {
        var customer = getCustomer();
        var required = ['firstName', 'lastName', 'email', 'phone', 'country'];
        for (var i = 0; i < required.length; i++) {
            if (!customer[required[i]]) {
                showError(state.config.i18n.missingFields);
                return null;
            }
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(customer.email)) {
            showError(state.config.i18n.invalidEmail);
            return null;
        }
        if (!$('#eshbTerms').prop('checked')) {
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
        return $.extend({
            nonce: state.config.nonce,
            gateway: state.gateway,
            coupon: state.coupon.valid ? state.coupon.code : '',
            firstName: customer.firstName,
            lastName: customer.lastName,
            email: customer.email,
            phone: customer.phone,
            country: customer.country,
            notes: customer.notes,
            extraServices: JSON.stringify(state.selectedServices)
        }, extra || {});
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
        applyPricingToDOM();
    });
})(jQuery);
