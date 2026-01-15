<?php
// register category
function eshb_block_categories( $block_categories, $editor_context ) {
    ///if ( ! empty( $editor_context->post ) ) {
       $attr = array(
            array(
                'slug'  => 'easy-hotel',
                'title' => __( 'Easy Hotel', 'easy-hotel' ),
            )
        );
        $block_categories =  array_merge( $attr, $block_categories );
	   
   //}
    return $block_categories;
}
add_filter( 'block_categories_all', 'eshb_block_categories', 999999, 2 );


add_action( 'init', 'eshb_enqueue_block_styles' );
function eshb_enqueue_block_styles() {

    // if swier not existing
	if (!wp_style_is('swiper', 'enqueued')) {
		wp_enqueue_style( 'swiper', ESHB_PL_URL . 'public/assets/css/swiper-bundle.min.css', array(), ESHB_VERSION, 'all' );
	}
	if (!wp_script_is('eshb-swiper', 'enqueued')) {
		wp_enqueue_script( 'eshb-swiper', ESHB_PL_URL . 'public/assets/js/swiper-bundle.min.js', array(),'12.0.3',false );
	}

    // register plugin style if not exist
	if ( ! wp_style_is( 'eshb-style', 'registered' ) ) {
		wp_register_style( 
			'eshb-style', 
			ESHB_PL_URL . 'public/assets/css/public.css', 
			array(), 
			ESHB_VERSION 
		);
	}

    
}

// include blocks
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodation-grid/accomodation-grid.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/search-form/search-form.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/booking-form/booking-form.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodation-gallery/accomodation-gallery.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodation-info/accomodation-info.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/check-in-out-times/check-in-out-times.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/availability-calendars/availability-calendars.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodation-slider/accomodation-slider.php';
