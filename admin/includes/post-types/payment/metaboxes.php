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
            echo '<a href="'.esc_url( $edit_url ).'" target="_blank"><div class="order-amount"><span>' . wp_kses_post($amount) . '</span></div></a>';
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
    


    $post_id = '';

    // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
    if ( isset( $_GET['post'] ) && isset( $_GET['nonce'] ) ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
        $post_id = sanitize_text_field( wp_unslash( $_GET['post'] ) );
    }
    

    $eshb_conditional_hidden_class = 'hidden-metabox';
    $eshb_saved_currency = 'USD';

    if(!empty($post_id)){
        $status = get_post_status( $post_id );

        if($status != 'publish'){
            $eshb_conditional_hidden_class = 'hidden-metabox';
        }

        $eshb_payment_metaboxes = get_post_meta($post_id, 'eshb_payment_metaboxes', true);
        $eshb_saved_currency = !empty($post_id) && !empty($eshb_payment_metaboxes['currency'] ) ? $eshb_payment_metaboxes['currency'] : 'USD';

    }else{
        $eshb_conditional_hidden_class = '';
    }

    $eshb_booking_id = '';
    // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
    if(!empty($_GET['booking'])) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
        $eshb_booking_id = sanitize_text_field( wp_unslash( $_GET['booking'] ) );
    }

    $eshb_saved_country = !empty($post_id) ? ESHB_Helper::get_current_payment_customer_metadata( $post_id, 'country' ) : ESHB_Helper::get_current_booking_customer_metadata( $eshb_booking_id, 'country' );
    $eshb_saved_state = !empty($post_id) ? ESHB_Helper::get_current_payment_customer_metadata( $post_id, 'state' ) : ESHB_Helper::get_current_booking_customer_metadata( $eshb_booking_id, 'state' );
    $eshb_saved_city = !empty($post_id) ? ESHB_Helper::get_current_payment_customer_metadata( $post_id, 'city' ) : ESHB_Helper::get_current_booking_customer_metadata( $eshb_booking_id, 'city' );
    
    $eshb_saved_state_name = !empty(ESHB_Helper::eshb_get_wc_state_city_name($eshb_saved_country, $eshb_saved_state)) ? ESHB_Helper::eshb_get_wc_state_city_name($eshb_saved_country, $eshb_saved_state) : $eshb_saved_state;
    $eshb_saved_city_name = !empty(ESHB_Helper::eshb_get_wc_state_city_name($eshb_saved_country, $eshb_saved_city)) ? ESHB_Helper::eshb_get_wc_state_city_name($eshb_saved_country, $eshb_saved_city) : $eshb_saved_city;
    // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
    $eshb_default_payment_amount = !empty($_GET['amount']) ? sanitize_text_field( wp_unslash( $_GET['amount'] ) ) : 0;

    $eshb_current_booking_customer_metadata = [];
    // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
    if(!empty($_GET['booking'])){
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
        $eshb_booking_id = sanitize_text_field( wp_unslash( $_GET['booking'] ) );
        $eshb_current_booking_customer_metadata = ESHB_Helper::get_current_booking_customer_metadata($eshb_booking_id, '', '');
        $eshb_saved_state_name = $eshb_current_booking_customer_metadata['state'] ?? '';
        $eshb_saved_city_name = $eshb_current_booking_customer_metadata['city'] ?? '';
    }

    

    // Set a unique slug-like ID
    $eshb_prefix = 'eshb_payment_metaboxes';
    // Create a metabox
    ESHB::createMetabox( $eshb_prefix, array(
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
    ESHB::createSection( $eshb_prefix, array(
        'title'  => '',
        'fields' => array(
            array(
                'id'          => 'booking_id',
                'type'        => 'select',
                'title'       => 'Booking Id',
                //'placeholder' => 'Enter booking id',
                //'class'       => 'hidden-metabox'
                'options'     => ESHB_Helper::eshb_get_booking_ids(),
                // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
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
                'default'     => $eshb_default_payment_amount,
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
                    'data-saved-value' => $eshb_saved_currency ?? 'USD',
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
    $eshb_prefix = 'eshb_payment_customer_details_metaboxes';
    // Create a metabox
    ESHB::createMetabox( $eshb_prefix, array(
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

    ESHB::createSection( $eshb_prefix, array(
        'title'  => '',
        'fields' => array(
            array(
                'id'          => 'first_name',
                'type'        => 'text',
                'title'       => 'First Name',
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'default' => $eshb_current_booking_customer_metadata['first_name'] ?? '',
                'class'    => 'required-field',
            ),
            array(
                'id'          => 'last_name',
                'type'        => 'text',
                'title'       => 'Last Name',
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'default' => $eshb_current_booking_customer_metadata['last_name'] ?? '',
                'class'    => 'required-field',
            ),
            array(
                'id'          => 'email',
                'type'        => 'text',
                'title'       => 'Email',
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'default' => $eshb_current_booking_customer_metadata['email'] ?? '',
                'class'    => 'required-field',
            ),
            array(
                'id'          => 'phone',
                'type'        => 'text',
                'title'       => 'phone',
                'validate' => 'eshb_validate_for_required', // Required validation
                'required' => true,
                'default' => $eshb_current_booking_customer_metadata['phone'] ?? '',
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
                        'data-saved-value' => $eshb_saved_country,
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
                        'data-saved-value' => $eshb_saved_state_name,
                        'class' => 'eshb-customer-state'
                    ),
                    // Required only for countries that actually have states.
                    'validate' => 'eshb_validate_state_for_required',
                    'required' => true,
                ),
            array(
                'id'          => 'city',
                'type'        => 'text',
                'title'       => 'City',
                'default' => $eshb_current_booking_customer_metadata['city'] ?? '',
            ),
            array(
                'id'          => 'address_1',
                'type'        => 'text',
                'title'       => 'Address line one',
                'required' => true,
                'default' => $eshb_current_booking_customer_metadata['address_1'] ?? '',
            ),
            array(
                'id'          => 'address_2',
                'type'        => 'text',
                'title'       => 'Address line two',
                'default' => $eshb_current_booking_customer_metadata['address_2'] ?? '',
            ),
            array(
                'id'          => 'postcode',
                'type'        => 'text',
                'title'       => 'Postcode / ZIP',
                'required' => true,
                'default' => $eshb_current_booking_customer_metadata['postcode'] ?? '',
            ),
        )
    ) );



}


