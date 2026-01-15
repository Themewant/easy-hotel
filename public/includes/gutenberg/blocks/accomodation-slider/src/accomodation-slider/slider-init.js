(function (window) {

    window.initESHBSlider = function (scope) {

        const wraps = (scope || document).querySelectorAll(
            '.eshb-accomodation-slider-block-wrap'
        );

        wraps.forEach(function (wrap) {

            const unique = wrap.dataset.unique;

            // already initialized হলে skip
            if (wrap.dataset.initialized === 'true') {
                return;
            }

            new Swiper('.rt_room_slider-' + unique, {
                slidesPerView: Number(wrap.dataset.slidesPerView),
                spaceBetween: Number(wrap.dataset.spaceBetween),
                centeredSlides: wrap.dataset.centeredSlides === 'true',
                loop: wrap.dataset.loop === 'true',
                effect: wrap.dataset.effect,
                speed: Number(wrap.dataset.speed),

                navigation: {
                    nextEl:
                        '.rt_room_slider-btn-wrapper-' +
                        unique +
                        ' .swiper-button-next',
                    prevEl:
                        '.rt_room_slider-btn-wrapper-' +
                        unique +
                        ' .swiper-button-prev',
                },

                breakpoints: {
                    0: { slidesPerView: 1, spaceBetween: 10 },
                    360: { slidesPerView: 1, spaceBetween: 10 },
                    375: { slidesPerView: 1, spaceBetween: 10 },
                    480: { slidesPerView: 1, spaceBetween: 10 },
                    520: { slidesPerView: 1, spaceBetween: 10 },
                    575: {
                        slidesPerView: Number(
                            wrap.dataset.slidesPerViewMobileSmall
                        ),
                        spaceBetween: 10,
                    },
                    767: {
                        slidesPerView: Number(
                            wrap.dataset.slidesPerViewMobile
                        ),
                        spaceBetween: 10,
                    },
                    991: {
                        slidesPerView: Number(
                            wrap.dataset.slidesPerViewTablet
                        ),
                        spaceBetween: 10,
                    },
                    1199: {
                        slidesPerView: Number(wrap.dataset.slidesPerView),
                        spaceBetween: 20,
                    },
                    1600: {
                        slidesPerView: Number(wrap.dataset.slidesPerView),
                        spaceBetween: 30,
                    },
                },
            });

            // mark as initialized
            wrap.dataset.initialized = 'true';
        });
    };

    // Frontend initial load
    document.addEventListener('DOMContentLoaded', function () {
        window.initESHBSlider(document);
    });

})(window);