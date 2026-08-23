(function ($) {

    let ESHBPUBLIC = {
        init: function () {
            ESHBPUBLIC.enableEshbGalleyCarousel();
            ESHBPUBLIC.enableEshbGridAnimation();
        },
        enableEshbGalleyCarousel: function () {

            $('.eshb_accomodation-template-default .eshb-accomodation-gallery-section .has-accomodation-gallery').each(function () {

                var $gallery = $(this);

                if ($gallery.data('eshbGalleryReady')) return;

                // attr() returns a string and Swiper does arithmetic with this.
                var slidesPerView = parseFloat($gallery.attr('data-slides-per-view'));
                if (!slidesPerView || slidesPerView < 1) slidesPerView = 2.1;

                new Swiper(this, {
                    // Bounded rather than endless. Loop mode needs slidesPerView
                    // plus a buffer worth of slides before Swiper can wrap, and
                    // a three-photo gallery is short of that — it then left an
                    // empty slot where a photo should be. Running bounded also
                    // gives the arrows a real end to disable at.
                    loop: false,
                    slidesPerView: slidesPerView,
                    spaceBetween: 0,
                    centeredSlides: true,
                    // Centre the middle photos, but pin the first and last to
                    // the edges, so the run never opens or ends on blank space.
                    centeredSlidesBounds: true,
                    // Fewer photos than fit on screen: centre the whole group
                    // instead of leaving a gap to one side.
                    centerInsufficientSlides: true,
                    // Greys out each arrow at its end, and hides both entirely
                    // when every photo already fits without scrolling.
                    watchOverflow: true,
                    // Navigation arrows, scoped to this gallery — a page can
                    // carry more than one slider.
                    navigation: {
                        nextEl: $gallery.children('.swiper-button-next').get(0),
                        prevEl: $gallery.children('.swiper-button-prev').get(0),
                    },
                    breakpoints: {
                        0: {
                            slidesPerView: 1,
                        },
                        360: {
                            slidesPerView: 1,
                        },
                        375: {
                            slidesPerView: 1,
                        },
                        480: {
                            slidesPerView: 1,
                        },
                        520: {
                            slidesPerView: 1,
                        },
                        640: {
                            slidesPerView: 1,
                        },
                        768: {
                            slidesPerView: 1,
                        },
                        1024: {
                            slidesPerView: 2,
                        },
                        1600: {
                            slidesPerView: slidesPerView,
                        },
                    },
                });

                $gallery.data('eshbGalleryReady', true);
            });
        },
        enableEshbGridAnimation: function () {
            $('.eshb-item-grid .grid-item.has-animation-fade-in-up').each(function (index, element) {

                var delay = parseFloat(3) + parseFloat(index);

                $(element).css('animation-delay', '0.' + delay + 's');

            });
        },

    }

    ESHBPUBLIC.init();

})(jQuery);