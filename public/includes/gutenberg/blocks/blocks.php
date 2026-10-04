<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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


add_action( 'enqueue_block_assets', 'eshb_enqueue_block_styles' );
add_action( 'enqueue_block_editor_assets', 'eshb_enqueue_block_styles' );
function eshb_enqueue_block_styles() {

    // enqueue_block_assets also fires on every front-end page; there the
    // slider/gallery blocks pull Swiper in as a dependency only when they
    // render. The editor previews them, so it gets Swiper up front.
	if ( is_admin() ) {
		wp_enqueue_style( 'swiper' );
		wp_enqueue_script( 'eshb-swiper' );
	}

    // register plugin style if not exist
	//if ( ! wp_style_is( 'eshb-style', 'registered' ) ) {
		wp_register_style( 
			'eshb-style', 
			ESHB_PL_URL . 'public/assets/css/public.css', 
			array(), 
			ESHB_VERSION 
		);
	//}

    
}

/**
 * Add script dependencies to a block's front-end scripts. The build's
 * *.asset.php only lists @wordpress packages, not Swiper/jQuery.
 *
 * @param WP_Block_Type|false $block_type Result of register_block_type().
 * @param string[]            $deps       Script handles.
 */
function eshb_block_scripts_add_dependency( $block_type, array $deps ) {
	if ( ! $block_type instanceof WP_Block_Type ) {
		return;
	}
	$scripts = wp_scripts();
	$handles = array_merge( (array) $block_type->view_script_handles, (array) $block_type->script_handles );
	foreach ( array_unique( $handles ) as $handle ) {
		if ( isset( $scripts->registered[ $handle ] ) ) {
			$scripts->registered[ $handle ]->deps = array_values( array_unique( array_merge( $scripts->registered[ $handle ]->deps, $deps ) ) );
		}
	}
}

// include blocks
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/class.block-helper.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodation-grid/accomodation-grid.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/search-form/search-form.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/booking-form/booking-form.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodation-gallery/accomodation-gallery.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodation-info/accomodation-info.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/check-in-out-times/check-in-out-times.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/availability-calendars/availability-calendars.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodation-slider/accomodation-slider.php';
