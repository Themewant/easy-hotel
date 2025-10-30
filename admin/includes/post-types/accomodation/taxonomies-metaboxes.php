<?php
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
// Control core classes for avoid errors
if( class_exists( 'ESHB' ) ) {
    // Set a unique slug-like ID
    $prefix = 'eshb_accomodation_taxonomy_metaboxes';

    // Create taxonomy options
    ESHB::createTaxonomyOptions( $prefix, array(
        'taxonomy'  => 'eshb_category',
        'data_type' => 'unserialize ', // The type of the database save options. `serialize` or `unserialize`
    ) 
    );

    // Create a section
    ESHB::createSection( $prefix, array(
            'fields' => array(
                array(
                    'id'      => 'thumbnail',
                    'type'    => 'media',
                    'title'   => 'Thumbnail',
                    'library' => 'image',
                    'placeholder' => '',
                ),
            )
        ) 
    );
}
