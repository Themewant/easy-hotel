<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
add_action( 'plugins_loaded', function(){
    if( class_exists( 'ESHB' ) ) {

        // Set a unique slug-like ID
        $prefix = 'eshb_session_metaboxes';

        // Create a metabox
        ESHB::createMetabox( $prefix, array(
            'title'              => 'Seasons Options',
            'post_type'          => 'eshb_session',
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
        $session_fields = array(

            array(
                'id'    => 'session_price',
                'type'  => 'number',
                'title' => 'Price',
            ),
            array(
                'id'    => 'longstay_pricing_warning',
                'type'  => 'callback',
                'title' => 'Long Stay Pricing',
                'function' => 'eshb_advanced_pricing_settings_fallback_sm',
            ),
            array(
                'id'    => 'variable_pricing_warning',
                'type'  => 'callback',
                'title' => 'Variable Pricing',
                'function' => 'eshb_advanced_pricing_settings_fallback_sm',
            ),
            array(
                'id'    => 'days_pricing_warning',
                'type'  => 'callback',
                'title' => 'Days',
                'function' => 'eshb_days_pricing_settings_fallback_sm',
            ),
            
            array(
                'id'    => 'start_date',
                'type'  => 'datetime',
                'title' => 'Start Date',
                'settings' => array(
                    'altFormat'  => 'F j, Y',
                    'dateFormat' => 'Y-m-d',
                ),
            ),
            array(
                'id'    => 'end_date',
                'type'  => 'datetime',
                'title' => 'End Date',
                'settings' => array(
                    'altFormat'  => 'F j, Y',
                    'dateFormat' => 'Y-m-d',
                ),
            ),
            array(
                'id'          => 'accomodation_ids',
                'type'        => 'select',
                'title'       => 'Accomodations',
                'placeholder' => 'Select Accomodations',
                'options'     => 'posts',
                'multiple'     => true,
                'query_args'  => array(
                                    'post_type' => 'eshb_accomodation',
                                    'posts_per_page' => -1,
                                ),
            ),
        );

        
        $session_fields = apply_filters( 'after_session_pricing_fields', $session_fields );


        ESHB::createSection( $prefix, array(
            'title'  => '',
            'fields' => $session_fields,
            )
        );
    }
}, 15);






function eshb_add_custom_columns_session_post($columns) {
    $columns['session_price'] = __('Price', 'easy-hotel');
    $columns['start_date'] = __('Start Date', 'easy-hotel');
    $columns['end_date'] = __('End Date', 'easy-hotel');
    return $columns;
}
add_filter('manage_eshb_session_posts_columns', 'eshb_add_custom_columns_session_post');

function eshb_custom_column_content_session_post($column, $post_id) {

    $eshb_session_metaboxes = get_post_meta($post_id, 'eshb_session_metaboxes', true);
    $hotel_core = new ESHB_Core();
    $currency_symbol = $hotel_core->get_eshb_currency_symbol();

    switch ($column) {
        case 'session_price':
            echo esc_html($currency_symbol) . esc_html($eshb_session_metaboxes['session_price']);
            break;
        case 'start_date':
            echo esc_html($eshb_session_metaboxes['start_date']);
            break;
        case 'end_date':
            echo esc_html($eshb_session_metaboxes['end_date']);
            break;
    }
}
add_action('manage_eshb_session_posts_custom_column', 'eshb_custom_column_content_session_post', 10, 2);


function eshb_reorder_columns__session_post($columns) {
    // Save the date column
    $date_column = $columns['date'];
    unset($columns['date']); // Remove the date column temporarily

    // Add your custom columns
    $columns['session_price'] = esc_html__('Price', 'easy-hotel');
    $columns['start_date'] = esc_html__('Start Date', 'easy-hotel');
    $columns['end_date'] = esc_html__('End Date', 'easy-hotel');

    // Add the date column back as the last column
    $columns['date'] = $date_column;

    return $columns;
}
add_filter('manage_eshb_session_posts_columns', 'eshb_reorder_columns__session_post');


// remove unnecessary third party metaboxes
add_action( 'add_meta_boxes', 'eshb_remove_my_custom_metabox', 99 );
function eshb_remove_my_custom_metabox() {
    remove_meta_box( 'slider_revolution_metabox', 'eshb_session', 'side' );
}
