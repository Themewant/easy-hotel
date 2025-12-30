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
                        575: { slidesPerView: $wrap.data('slides-per-view-mobile-small') },
                        767: { slidesPerView: $wrap.data('slides-per-view-mobile') },
                        991: { slidesPerView: $wrap.data('slides-per-view-tablet') },
                        1199: { slidesPerView: $wrap.data('slides-per-view') },
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