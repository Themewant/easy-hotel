<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
if( class_exists( 'ESHB' ) ) {

    // Set a unique slug-like ID
    $prefix = 'eshb_service_metaboxes';

    // Create a metabox
    ESHB::createMetabox( $prefix, array(
        'title'              => 'Service Options',
        'post_type'          => 'eshb_service',
        'data_type'          => 'serialize',
        'context'            => 'advanced',
        'priority'           => 'default',
        'exclude_post_types' => array(),
        'page_templates'     => '',
        'post_formats'       => '',
        'show_restore'       => false,
        'enqueue_webfont'    => true,
        'async_webfont'      => false,
        'output_css'         => true,
        'nav'                => 'inline',
        'theme'              => 'light',
        'class'              => '',
    ) );


    // Create a section
    ESHB::createSection( $prefix, array(
        'title'  => '',
        'fields' => array(
        // A text field
        array(
            'id'    => 'service_price',
            'type'  => 'number',
            'title' => 'Price',
        ),
        array(
            'id'          => 'service_periodicity',
            'type'        => 'select',
            'title'       => 'Periodicity',
            'options'     => array(
              'once'  => 'Once',
              'per_day'  => 'Per Day',
            ),
            'default'     => 'once'
        ),
        array(
            'id'          => 'service_charge_type',
            'type'        => 'select',
            'title'       => 'Charge Types',
            'options'     => array(
              'room'  => 'Per Room',
              'guest'  => 'Per Guest',
            ),
            'default'     => 'room'
        ),

        )
    ) );

}