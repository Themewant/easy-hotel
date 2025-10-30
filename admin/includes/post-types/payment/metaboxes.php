<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
function eshb_add_custom_columns_payment($columns) {
    $columns['amount'] = esc_html__( 'Amount', 'easy-hotel' );
    $columns['payment_status'] = esc_html__( 'Status', 'easy-hotel' );
    $columns['booking_id'] = esc_html__( 'Booking', 'easy-hotel' );
    return $columns;
}
add_filter('manage_eshb_payment_posts_columns', 'eshb_add_custom_columns_payment');

function eshb_custom_column_content_payment($column, $post_id) {

    $eshb_payment_metaboxes = get_post_meta($post_id, 'eshb_payment_metaboxes', true);
    $payment_status = get_post_status( $post_id );
    $eshb_payment_metaboxes = get_post_meta( $post_id, 'eshb_payment_metaboxes', true );
    $booking_id = $eshb_payment_metaboxes['booking_id'];
    $booking_status = get_post_status( $booking_id );

    $hotel_core = new ESHB_Core();
    $amount = $hotel_core->eshb_price($eshb_payment_metaboxes['amount']);

    switch ($column) {
        case 'amount':
            $edit_url = get_edit_post_link($post_id);
            echo '<a href="'.esc_url( $edit_url ).'" target="_blank"><div class="order-amount"><span>' . esc_html($amount) . '</span></div></a>';
            break;
        case 'payment_status':
            $edit_url = get_edit_post_link($post_id);
            echo '<a href="'.esc_url( $edit_url ).'" target="_blank"><mark class="order-status status-' . esc_attr($payment_status) . '"><span>' . esc_html($payment_status) . '</span></mark></a>';
            break;
        case 'booking_id':
            $edit_url = get_edit_post_link($booking_id) ?? '';
            if($edit_url) 
            echo '<a href="'.esc_url( $edit_url ).'" target="_blank"><mark class="order-status status-' . esc_attr($booking_status) . '"><span>#' . esc_html($booking_id) . '</span></mark></a>';
            break;
    }
}
add_action('manage_eshb_payment_posts_custom_column', 'eshb_custom_column_content_payment', 10, 2);


function eshb_reorder_columns_payment($columns) {

    // Save the date column
    $date_column = $columns['date'];
    unset($columns['date']); // Remove the date column temporarily

    // Add your custom columns
    $columns['amount'] = esc_html__( 'Amount', 'easy-hotel' );
    $columns['payment_status'] = esc_html__( 'Status', 'easy-hotel' );
    $columns['booking_id'] = esc_html__( 'Booking', 'easy-hotel' );

    // Add the date column back as the last column
    $columns['date'] = $date_column;

    return $columns;

}
add_filter('manage_eshb_payment_posts_columns', 'eshb_reorder_columns_payment');

if( class_exists( 'ESHB' ) ) {
    // include pluggable file
    if(is_admin() && isset($_REQUEST['nonce'])) {
        require_once ABSPATH . 'wp-includes/pluggable.php';
    }

    $post_id = '';

    if ( isset( $_GET['post'] ) && isset( $_GET['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action') ) ) {
        $post_id = sanitize_text_field( wp_unslash( $_GET['post'] ) );
    }

    $conditional_hidden_class = 'hidden-metabox';
    $saved_currency = 'USD';

    if(!empty($post_id)){
        $status = get_post_status( $post_id );

        if($status != 'publish'){
            $conditional_hidden_class = 'hidden-metabox';
        }

        $eshb_payment_metaboxes = get_post_meta($post_id, 'eshb_payment_metaboxes', true);
        $saved_currency = !empty($post_id) && !empty($eshb_payment_metaboxes['currency'] ) ? $eshb_payment_metaboxes['currency'] : 'USD';

    }else{
        $conditional_hidden_class = '';
    }

    $booking_id = '';

    if(!empty($_GET['booking'])) {
        $booking_id = sanitize_text_field( wp_unslash( $_GET['booking'] ) );
    }

    $saved_country = !empty($post_id) ? ESHB_Helper::get_current_payment_customer_metadata( $post_id, 'country' ) : ESHB_Helper::get_current_booking_customer_metadata( $booking_id, 'country' );
    $saved_state = !empty($post_id) ? ESHB_Helper::get_current_payment_customer_metadata( $post_id, 'state' ) : ESHB_Helper::get_current_booking_customer_metadata( $booking_id, 'state' );
    $saved_city = !empty($post_id) ? ESHB_Helper::get_current_payment_customer_metadata( $post_id, 'city' ) : ESHB_Helper::get_current_booking_customer_metadata( $booking_id, 'city' );
    
    $saved_state_name = !empty(ESHB_Helper::eshb_get_wc_state_city_name($saved_country, $saved_state)) ? ESHB_Helper::eshb_get_wc_state_city_name($saved_country, $saved_state) : $saved_state;
    $saved_city_name = !empty(ESHB_Helper::eshb_get_wc_state_city_name($saved_country, $saved_city)) ? ESHB_Helper::eshb_get_wc_state_city_name($saved_country, $saved_city) : $saved_city;
    $default_payment_amount = !empty($_GET['amount']) ? sanitize_text_field( wp_unslash( $_GET['amount'] ) ) : 0;

    $current_booking_customer_metadata = [];
    if(!empty($_GET['booking'])){
        $booking_id = sanitize_text_field( wp_unslash( $_GET['booking'] ) );
        $current_booking_customer_metadata = ESHB_Helper::get_current_booking_customer_metadata($booking_id, '', '');
        $saved_state_name = $current_booking_customer_metadata['state'];
        $saved_city_name = $current_booking_customer_metadata['city'];
    }

    

    // Set a unique slug-like ID
    $prefix = 'eshb_payment_metaboxes';
    // Create a metabox
    ESHB::createMetabox( $prefix, array(
        'title'              => 'Payment Options',
        'post_type'          => 'eshb_payment',
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
            array(
                'id'          => 'booking_id',
                'type'        => 'select',
                'title'       => 'Booking Id',
                //'placeholder' => 'Enter booking id',
                //'class'       => 'hidden-metabox'
                'options'     => ESHB_Helper::eshb_get_booking_ids(),
                'default'     => !empty($_GET['booking']) ? sanitize_text_field( wp_unslash( $_GET['booking'] ) ) : '',
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'class'    => 'required-field',
            ),
            array(
                'id'          => 'transaction_id',
                'type'        => 'text',
                'title'       => 'Transaction Id',
                'placeholder' => 'Enter transaction id',
                //'class'       => 'hidden-metabox'
            ),
            array(
                'id'    => 'gateway',
                'type'  => 'select',
                'title' => 'Payment Gateway',
                'placeholder' => 'Select a payment gateway',
                'options' => ESHB_Helper::eshb_get_payment_gateways(),
                'default' => 'manual',
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'class'    => 'required-field',
            ),
            array(
                'id'    => 'gateway_mode',
                'type'  => 'select',
                'title' => 'Payment Gateway Mode',
                'placeholder' => 'Select a payment gateway',
                'options' => [
                    'test' => 'Test Mode',
                    'live' => 'Live Mode'
                ],
                'default' => 'live',
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'class'    => 'required-field',
            ),
            array(
                'id'          => 'amount',
                'type'        => 'number',
                'title'       => 'Amount',
                'min'         => 1,
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'default'     => $default_payment_amount,
                'class'    => 'required-field',
            ),
            array(
                'id'          => 'fee',
                'type'        => 'number',
                'title'       => 'Fee',
            ),
            array(
                'id'          => 'currency',
                'type'        => 'select',
                'title'       => 'Currency',
                'options'     => ['' => 'Select a currency'],
                'class'    => 'required-field',
                'attributes'  => array(
                    'data-saved-value' => $saved_currency ?? 'USD',
                    'class' => 'eshb-payment-currency'
                ),
                'default'     => 'USD'
            ),
            array(
                'id'          => 'payment_type',
                'type'        => 'text',
                'title'       => 'Payment Type',
                'placeholder' => 'Enter a payment type',
            ),
            array(
                'id'          => 'note',
                'type'        => 'text',
                'title'       => 'Note',
            ),
        )
    ) );


    // Set a unique slug-like ID
    $prefix = 'eshb_payment_customer_details_metaboxes';
    // Create a metabox
    ESHB::createMetabox( $prefix, array(
        'title'              => 'Customer Details Options',
        'post_type'          => 'eshb_payment',
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

    ESHB::createSection( $prefix, array(
        'title'  => '',
        'fields' => array(
            array(
                'id'          => 'first_name',
                'type'        => 'text',
                'title'       => 'First Name',
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'default' => $current_booking_customer_metadata['first_name'] ?? '',
                'class'    => 'required-field',
            ),
            array(
                'id'          => 'last_name',
                'type'        => 'text',
                'title'       => 'Last Name',
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'default' => $current_booking_customer_metadata['last_name'] ?? '',
                'class'    => 'required-field',
            ),
            array(
                'id'          => 'email',
                'type'        => 'text',
                'title'       => 'Email',
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'default' => $current_booking_customer_metadata['email'] ?? '',
                'class'    => 'required-field',
            ),
            array(
                'id'          => 'phone',
                'type'        => 'text',
                'title'       => 'phone',
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'default' => $current_booking_customer_metadata['phone'] ?? '',
                'class'    => 'required-field',
            ),
            array(
                    'id'          => 'country',
                    'type'        => 'select',
                    'title'       => 'Country',
                    //'placeholder' => 'Select an country',
                    'options'     => ['' => 'Select an country'],
                    'class'    => 'required-field',
                    'attributes' => array(
                        'data-saved-value' => $saved_country,
                        'class' => 'eshb-customer-country',
                    ),
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                ),
                array(
                    'id'          => 'state',
                    'type'        => 'select',
                    'title'       => 'State',
                    //'placeholder' => 'Select an state',
                    'class'    => 'required-field',
                    'options'     => ['' => 'Select an state'],
                    'attributes' => array(
                        'data-saved-value' => $saved_state_name,
                        'class' => 'eshb-customer-state'
                    ),
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                ),
            array(
                'id'          => 'city',
                'type'        => 'text',
                'title'       => 'City',
                'default' => $current_booking_customer_metadata['city'] ?? '',
            ),
            array(
                'id'          => 'address_1',
                'type'        => 'text',
                'title'       => 'Address line one',
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'default' => $current_booking_customer_metadata['address_1'] ?? '',
                'class'    => 'required-field',
            ),
            array(
                'id'          => 'address_2',
                'type'        => 'text',
                'title'       => 'Address line two',
                'default' => $current_booking_customer_metadata['address_2'] ?? '',
            ),
            array(
                'id'          => 'postcode',
                'type'        => 'text',
                'title'       => 'Postcode / ZIP',
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'default' => $current_booking_customer_metadata['postcode'] ?? '',
                'class'    => 'required-field',
            ),
        )
    ) );



}


