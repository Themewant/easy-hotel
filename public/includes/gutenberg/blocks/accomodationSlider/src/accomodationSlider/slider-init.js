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
                        0: {
                            slidesPerView: 1,
                            spaceBetween: 0,
                        },
                        360: {
                            slidesPerView: 1,
                            spaceBetween: 0,
                        },
                        375: {
                            slidesPerView: 1,
                            spaceBetween: 0,
                        },
                        480: {
                            slidesPerView: 1,
                            spaceBetween: 0,
                        },
                        520: {
                            slidesPerView: 1,
                            spaceBetween: 0,
                        },
                        575: { slidesPerView: $wrap.data('slides-per-view-mobile-small'), spaceBetween: 0 },
                        767: { slidesPerView: $wrap.data('slides-per-view-mobile'), spaceBetween: 0 },
                        991: { slidesPerView: $wrap.data('slides-per-view-tablet'), spaceBetween: 10 },
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