<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Rendered when the metabox is shown, so the strings are translated after init.
function eshb_render_season_extension_note() {
    printf(
        '<p class="eshb-extension-note">%1$s <a href="%2$s">%3$s</a></p>',
        esc_html__( 'Long-stay rates, rates by number of guests, weekday rules and a minimum stay per season are available with the Advanced Pricing extension.', 'easy-hotel' ),
        esc_url( admin_url( 'edit.php?post_type=eshb_accomodation&page=edit.php%3Fpost_type%3Deshb_addons' ) ),
        esc_html__( 'View extensions', 'easy-hotel' )
    );
}

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
            array(
                'id'       => 'season_extension_note',
                'type'     => 'callback',
                'function' => 'eshb_render_season_extension_note',
            ),
        );

        
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public hook name kept for backward compatibility with existing integrations.
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
    $price = !empty($eshb_session_metaboxes['session_price']) ? $currency_symbol . $eshb_session_metaboxes['session_price'] : '';
    $start_date = !empty($eshb_session_metaboxes['start_date']) ? $eshb_session_metaboxes['start_date'] : '';
    $end_date = !empty($eshb_session_metaboxes['end_date']) ? $eshb_session_metaboxes['end_date'] : '';

    switch ($column) {
        case 'session_price':
            echo esc_html($price);
            break;
        case 'start_date':
            echo esc_html($start_date);
            break;
        case 'end_date':
            echo esc_html($end_date);
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
