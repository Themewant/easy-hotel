(function ($, window) {

    window.initESHBSlider = function (scope) {
        $(scope)
            .find('.eshb-accomodation-slider-block-wrap')
            .each(function () {

                const $wrap = $(this);
                const unique = $wrap.data('unique');

                if ($wrap.data('initialized')) return;

                new Swiper('.rt_room_slider-' + unique, {
                    slidesPerView: $wrap.data('slides-per-view'),
                    spaceBetween: $wrap.data('space-between'),
                    centeredSlides: $wrap.data('centered-slides'),
                    loop: $wrap.data('loop'),
                    effect: $wrap.data('effect'),
                    speed: $wrap.data('speed'),
                    navigation: {
                        nextEl: '.rt_room_slider-btn-wrapper-' + unique + ' .swiper-button-next',
                        prevEl: '.rt_room_slider-btn-wrapper-' + unique + ' .swiper-button-prev',
                    },

                    breakpoints: {
                        575: { slidesPerView: $wrap.data('slides-per-view-mobile-small') },
                        767: { slidesPerView: $wrap.data('slides-per-view-mobile') },
                        991: { slidesPerView: $wrap.data('slides-per-view-tablet') },
                        1199: { slidesPerView: $wrap.data('slides-per-view') },
                    },
                });

                $wrap.data('initialized', true);
            });
    };

    // Frontend initial load
    $(document).ready(function () {
        window.initESHBSlider(document);
    });

})(jQuery, window);