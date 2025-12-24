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

// include blocks
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodationgrid/accomodationgrid.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/searchform/searchform.php';
require_once ESHB_PL_PATH . 'public/includes/gutenberg/blocks/bookingform/bookingform.php';

