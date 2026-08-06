<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
if( class_exists( 'ESHB' ) ) {

    // Set a unique slug-like ID
    $eshb_prefix = 'eshb_service_metaboxes';

    // Create a metabox
    ESHB::createMetabox( $eshb_prefix, array(
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
    ESHB::createSection( $eshb_prefix, array(
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
        array(
            'id'         => 'service_max_quantity',
            'type'       => 'number',
            'title'      => 'Max Quantity',
            'desc'       => 'Highest quantity a customer may select for this service. Leave empty (or 0) for no limit — the quantity is then capped by the number of guests.',
            'default'    => '',
            'attributes' => array(
                'min' => 0,
            ),
            'dependency' => array( 'service_charge_type', '==', 'guest' ),
        ),
        array(
            'id'         => 'is_checked', // field id
            'type'       => 'switcher',
            'title'      => 'Checked by Default',
            'default'    => false
            ),
        array(
            'id'         => 'is_mandatory', // field id
            'type'       => 'switcher',
            'title'      => 'Mandatory',
            'default'    => false
        ),

        ),
    ) );

}