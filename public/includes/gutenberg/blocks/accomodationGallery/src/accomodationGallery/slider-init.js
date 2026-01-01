(function ($, window) {

    window.initESHBAccomodationGallery = function (scope) {
        $(scope)
            .find('.eshb-accomodation-gallery-block-wrapper')
            .each(function () {

                const $wrap = $(this);
                const unique = $wrap.data('unique');

                if ($wrap.data('initializedAccomodationGallery' + unique)) return;

                new Swiper('.has-accomodation-gallery-' + unique, {
                    slidesPerView: $wrap.data('slides-per-view'),
                    spaceBetween: $wrap.data('space-between'),
                    centeredSlides: $wrap.data('centered-slides'),
                    loop: $wrap.data('loop'),
                    effect: $wrap.data('effect'),
                    speed: $wrap.data('speed'),
                    navigation: {
                        nextEl: '.has-accomodation-gallery-' + unique + ' .swiper-button-next',
                        prevEl: '.has-accomodation-gallery-' + unique + ' .swiper-button-prev',
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
                        991: { slidesPerView: $wrap.data('slides-per-view-tablet'), spaceBetween: 0 },
                        1199: { slidesPerView: $wrap.data('slides-per-view'), spaceBetween: 0 },
                    },
                });

                $wrap.data('initializedAccomodationGallery' + unique, true);
            });
    };

    // Frontend initial load
    $(document).ready(function () {
        window.initESHBAccomodationGallery(document);
    });

})(jQuery, window);