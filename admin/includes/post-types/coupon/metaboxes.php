<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
if( class_exists( 'ESHB' ) ) {

    $settings       = get_option('eshb_settings', []);
    $booking_type   = $settings['booking-type'] ?? 'woocommerce';

    $discount_type = array(
        'percent' => 'Percentage discount',
        'fixed_cart' => 'Fixed cart discount',
        'fixed_product' => 'Fixed product discount',
    );

    if($booking_type == 'surecart') {
        unset($discount_type['fixed_product']);
    }
    

    // Set a unique slug-like ID
    $prefix = 'eshb_coupon_metaboxes';


    // Create a metabox
    ESHB::createMetabox( $prefix, array(
        'title'              => 'Coupon Options',
        'post_type'          => 'eshb_coupon',
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
        //'title'  => 'Coupons',
        'fields'    => array(
                  array(
                    'id'    => 'coupon-code',
                    'type'  => 'text',
                    'title' => 'Coupon Code',
                    'desc'  => 'Add coupon code from here.',
                  ),
                  array(
                    'id'    => 'discount-type',
                    'type'  => 'select',
                    'title' => 'Discount Type',
                    'desc'  => 'Add coupon discount type from here.',
                    'options' => $discount_type,
                    'default' => 'percent'
                  ),
                  array(
                    'id'    => 'coupon-amount',
                    'type'  => 'text',
                    'title' => 'Amount',
                    'desc'  => 'Add only amount from here. for ex: 10',
                    'default' => 0
                  ),
                  array(
                    'id'    => 'expiry-date',
                    'type'  => 'datetime',
                    'title' => 'Expiry Date',
                    'desc'  => 'Add Expiration date of the coupon',
                    'settings' => array(
                      'altFormat'  => 'F j, Y',
                      'dateFormat' => 'Y-m-d',
                      ), 
                  ),
                  array(
                    'id'    => 'usage-limit',
                    'type'  => 'text',
                    'title' => 'Usages Limit',
                    'desc'  => 'Add Maximum number of times the coupon can be used. Leave empty for unlimited.',
                  ),
                  array(
                    'id'    => 'usage-limit-per-user',
                    'type'  => 'text',
                    'title' => 'Usages Limit Per User',
                    'desc'  => 'Add Maximum number of times a single user can use the coupon. Leave empty for unlimited.',
                  ),
                  array(
                    'id'          => 'accomodation-ids',
                    'type'        => 'select',
                    'title'       => 'Accomodations',
                    'placeholder' => 'Select Accomodations',
                    'desc'  => 'Select Accomodations for this coupon.',
                    'options'     => 'posts',
                    'multiple'     => true,
                    'query_args'  => array(
                                        'post_type' => 'eshb_accomodation',
                                        'posts_per_page' => -1,
                                    ),
                  ),
                  
                  
              
                ),
          )
      );



}


function eshb_add_custom_columns_coupon_post($columns) {
    $columns['coupon-amount'] = __('Price', 'easy-hotel');
    $columns['coupon-type'] = __('Coupon Type', 'easy-hotel');
    $columns['usages-limit'] = __('Usage / Limit', 'easy-hotel');
    $columns['expiry-date'] = esc_html__('Expiry Date', 'easy-hotel');
    return $columns;
}
add_filter('manage_eshb_coupon_posts_columns', 'eshb_add_custom_columns_coupon_post');

function eshb_custom_column_content_coupon_post($column, $post_id) {

    $eshb_coupon_metaboxes = get_post_meta($post_id, 'eshb_coupon_metaboxes', true);
    $hotel_core = new ESHB_Core();
    $currency_symbol = $hotel_core->get_eshb_currency_symbol();
    $coupon_type = $eshb_coupon_metaboxes['discount-type'];	

    $wc_coupon_id = get_post_meta( $post_id, 'eshb_coupon_wc_id', true );
    $wc_coupon = new WC_Coupon( $wc_coupon_id );


    if($coupon_type == 'percent') {
        $coupon_amount = $eshb_coupon_metaboxes['coupon-amount'] . '%';
    } else {
        $coupon_amount = $currency_symbol . $eshb_coupon_metaboxes['coupon-amount'];
    }

    $coupon_type_text_map = array(
        'percent' => 'Percentage discount',
        'fixed_cart' => 'Fixed cart discount',
        'fixed_product' => 'Fixed product discount',
    );

    switch ($column) {
        case 'coupon-amount':
            echo esc_html($coupon_amount);
            break;
        case 'coupon-type':
            echo esc_html($coupon_type_text_map[$coupon_type]);
            break;
        case 'usages-limit':
            echo esc_html($wc_coupon->get_usage_count()) . ' / ' . esc_html($wc_coupon->get_usage_limit());
            break;
        case 'expiry-date':
            echo esc_html($eshb_coupon_metaboxes['expiry-date']);
            break;
    }
}
add_action('manage_eshb_coupon_posts_custom_column', 'eshb_custom_column_content_coupon_post', 10, 2);


function eshb_reorder_columns__coupon_post($columns) {
    // Save the date column
    $date_column = $columns['date'];
    unset($columns['date']); // Remove the date column temporarily

    // Add your custom columns
    $columns['coupon-amount'] = esc_html__('Amount', 'easy-hotel');
    $columns['coupon-type'] = esc_html__('Coupon Type', 'easy-hotel');
    $columns['usages-limit'] = __('Usage / Limit', 'easy-hotel');
    $columns['expiry-date'] = esc_html__('Expiry Date', 'easy-hotel');

    // Add the date column back as the last column
    $columns['date'] = $date_column;

    return $columns;
}
add_filter('manage_eshb_coupon_posts_columns', 'eshb_reorder_columns__coupon_post');