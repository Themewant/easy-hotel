<?php
// register category
function eshb_block_categories( $block_categories, $editor_context ) {
    //if ( ! empty( $editor_context->post ) ) {
        array_push(
            $block_categories,
            array(
                'slug'  => 'easy-hotel', // A unique slug for your category
                'label' => __( 'Easy Hotel', 'easy-hotel' ), // A human-readable label
            )
        );
   // }
    return $block_categories;
}
add_filter( 'block_categories_all', 'eshb_block_categories', 10, 2 );


add_action( 'init', 'eshb_enqueue_block_styles' );
function eshb_enqueue_block_styles() {
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
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodationgrid/accomodationgrid.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/searchform/searchform.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/bookingform/bookingform.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodationGallery/accomodationGallery.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodationInfo/accomodationInfo.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/checkInOutTimes/checkInOutTimes.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/availabilityCalendars/availabilityCalendars.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodationSlider/accomodationSlider.php';


