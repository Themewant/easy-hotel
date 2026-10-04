/**
 * Starts the Swiper sliders printed by the room slider / room gallery widgets.
 *
 * Each slider leaves a hidden [data-eshb-swiper] element holding its target selector
 * and Swiper options as JSON, instead of an inline <script>. Runs on page load, and
 * again whenever Elementor or Bricks re-renders an element in the builder.
 */
(function () {
    'use strict';

    function init(scope) {
        if (typeof window.Swiper === 'undefined') {
            return;
        }

        var root = scope && scope.querySelectorAll ? scope : document;

        root.querySelectorAll('[data-eshb-swiper]').forEach(function (config) {
            var data;

            try {
                data = JSON.parse(config.getAttribute('data-eshb-swiper'));
            } catch (e) {
                return;
            }

            var target = data && data.selector ? document.querySelector(data.selector) : null;

            // Swiper sets el.swiper once started, so a re-run never starts one twice.
            if (!target || target.swiper) {
                return;
            }

            new window.Swiper(target, data.options || {});
        });
    }

    window.eshbInitSwipers = init;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            init(document);
        });
    } else {
        init(document);
    }

    // Elementor re-renders a widget in the editor without reloading the page.
    if (window.jQuery) {
        window.jQuery(window).on('elementor/frontend/init', function () {
            window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($scope) {
                init($scope[0]);
            });
        });
    }
})();
