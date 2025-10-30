<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
if( class_exists( 'ESHB' ) ) {

    function eshb_booking_request_get_woo_order_statuses(){

        $order_status = array(
            'pending'   => 'Pending ayment',
            'processing' => 'Processing',
            'on-hold'    => 'On hold',
            'completed'  => 'Completed',
            'cancelled'  => 'Cancelled',
            'refunded'   => 'Refunded',
            'failed'     => 'Failed'
        );
        return $order_status;
    }


    function eshb_booking_request_get_extra_services (){
        

        $booking_id = get_the_ID();
        if( !$booking_id ) return;

        $eshb_booking_request_metaboxes = get_post_meta($booking_id, 'eshb_booking_request_metaboxes', true);

        if(!$eshb_booking_request_metaboxes || empty($eshb_booking_request_metaboxes)) return;

        $booking_accomodation_id = $eshb_booking_request_metaboxes['booking_accomodation_id'];
        $eshb_accomodation_metaboxes = get_post_meta( $booking_accomodation_id, 'eshb_accomodation_metaboxes', true );
        $extra_services_ids = [];

        if(isset($eshb_accomodation_metaboxes['accomodation_services']) && !empty($eshb_accomodation_metaboxes['accomodation_services'])) {
            $extra_services_ids = $eshb_accomodation_metaboxes['accomodation_services'];
        }

        $extra_services = array();

        if(is_array($extra_services_ids) && count($extra_services_ids) > 0){
            foreach ($extra_services_ids as $key => $id) {
                
                $extra_services[$id] = get_the_title($id);
            
            }
        }

        return $extra_services;
    }

    // Set a unique slug-like ID
    $prefix = 'eshb_booking_request_customer_metaboxes';


    // Create a metabox
    ESHB::createMetabox( $prefix, array(
        'title'              => 'Customer Details',
        'post_type'          => 'eshb_booking_request',
        'data_type'          => 'serialize',
        'context'            => 'advanced',
        'show_restore'       => false,
        'enqueue_webfont'    => true,
        'async_webfont'      => false,
        'output_css'         => true,
        'nav'                => 'inline',
        'theme'              => 'light',
    ) );

     // Create a section
     ESHB::createSection( $prefix, array(
        'title'  => '',
        'fields' => array(
            array(
                'id'          => 'name',
                'type'        => 'text',
                'title'       => 'Name',
                //'class'       => 'hidden-metabox'
            ),
            array(
                'id'          => 'email',
                'type'        => 'text',
                'title'       => 'Email',
                //'class'       => 'hidden-metabox'
            ),
            array(
                'id'          => 'phone',
                'type'        => 'text',
                'title'       => 'Phone',
                //'class'       => 'hidden-metabox'
            ),
            array(
                'id'          => 'message',
                'type'        => 'textarea',
                'title'       => 'Message',
                //'class'       => 'hidden-metabox'
            )

        )
    ) );



    // Set a unique slug-like ID
    $prefix = 'eshb_booking_request_metaboxes';


    // Create a metabox
    ESHB::createMetabox( $prefix, array(
        'title'              => 'Booking Options',
        'post_type'          => 'eshb_booking_request',
        'data_type'          => 'serialize',
        'context'            => 'advanced',
        'show_restore'       => false,
        'enqueue_webfont'    => true,
        'async_webfont'      => false,
        'output_css'         => true,
        'nav'                => 'inline',
        'theme'              => 'light',
    ) );
   
    // Create a section
    ESHB::createSection( $prefix, array(
        'title'  => '',
        'fields' => array(
            array(
                'id'          => 'booking_status',
                'type'        => 'select',
                'title'       => 'Booking Status',
                'placeholder' => 'Select an option',
                'options'     => eshb_booking_request_get_woo_order_statuses(),
            ),
            array(
                'id'          => 'order_id',
                'type'        => 'text',
                'title'       => 'Booking Order Id',
                'placeholder' => 'Select an option',
                'class'       => 'hidden-metabox'
            ),
            array(
                'id'          => 'booking_accomodation_id',
                'type'        => 'select',
                'title'       => 'Accomodation',
                'placeholder' => 'Select an option',
                'options'     => 'posts',
                'query_args'  => array(
                                    'post_type' => 'eshb_accomodation',
                                    'posts_per_page' => -1,
                                ),
            ),
            array(
                'id'    => 'booking_start_date',
                'type'  => 'datetime',
                'title' => 'Start Date',
                'settings' => array(
                    'altFormat'  => 'F j, Y',
                    'dateFormat' => 'Y-m-d',
                ),
            ),
            array(
                'id'    => 'booking_end_date',
                'type'  => 'datetime',
                'title' => 'End Date',
                'settings' => array(
                    'altFormat'  => 'F j, Y',
                    'dateFormat' => 'Y-m-d',
                ),
            ),
            array(
                'id'          => 'dates',
                'type'        => 'text',
                'title'       => 'Dates',
                //'class'       => 'hidden-metabox'
            ),
            array(
                'id'    => 'booking_start_time',
                'type'  => 'text',
                'title' => 'Start Time',
            ),
            array(
                'id'    => 'booking_end_time',
                'type'  => 'text',
                'title' => 'End Time',
            ),
            array(
                'id'          => 'room_quantity',
                'type'        => 'text',
                'title'       => 'Room Quantity',
                //'class'       => 'hidden-metabox'
            ),
            array(
                'id'          => 'extra_bed_quantity',
                'type'        => 'text',
                'title'       => 'Extra Bed Quantity',
                //'class'       => 'hidden-metabox'
            ),
            array(
                'id'          => 'adult_quantity',
                'type'        => 'text',
                'title'       => 'Adult Quantity',
                //'class'       => 'hidden-metabox'
            ),
            array(
                'id'          => 'children_quantity',
                'type'        => 'text',
                'title'       => 'Children Quantity',
                //'class'       => 'hidden-metabox'
            ),
            array(
                'id'          => 'details_html',
                'type'        => 'text',
                'title'       => 'Details Html',
                //'class'       => 'hidden-metabox'
            ),
            array(
                'id'          => 'extra_services_html',
                'type'        => 'text',
                'title'       => 'Extra Services Html',
                //'class'       => 'hidden-metabox'
            ),
            array(
                'id'        => 'extra_services',
                'type'      => 'group',
                'title'     => 'Extra Services',
                //'class'       => 'hidden-metabox',
                'fields'    => array(
                  array(
                    'id'    => 'id',
                    'type'        => 'select',
                    'title'       => 'Service',
                    'options'     => eshb_booking_request_get_extra_services(),
                  ),
                  array(
                    'id'    => 'quantity',
                    'type'  => 'text',
                    'title' => 'Quantity',
                  ),
                ),
            ),
              

        )
    ) );
}


function eshb_booking_request_add_custom_columns($columns) {
    $columns['booking_start_date'] = esc_html__( 'Start Date', 'easy-hotel' );
    $columns['booking_end_date'] = esc_html__( 'End Date', 'easy-hotel' );
    $columns['booking_status'] = esc_html__( 'Booking Status', 'easy-hotel' );
    $columns['room_quantity'] = esc_html__( 'Booked Rooms', 'easy-hotel' );
    return $columns;
}
add_filter('manage_eshb_booking_request_posts_columns', 'eshb_booking_request_add_custom_columns');

function eshb_booking_request_custom_column_content($column, $post_id) {

    $eshb_booking_request_metaboxes = get_post_meta($post_id, 'eshb_booking_request_metaboxes', true);

    switch ($column) {
        case 'booking_start_date':
            echo esc_html(date_i18n( get_option('date_format'), strtotime( $eshb_booking_request_metaboxes['booking_start_date'] ) ));
            break;
        case 'booking_end_date':
            echo esc_html(date_i18n( get_option('date_format'), strtotime( $eshb_booking_request_metaboxes['booking_end_date'] ) ));
            break;
        case 'room_quantity':
            echo esc_html($eshb_booking_request_metaboxes['room_quantity']);
            break;
        case 'booking_status':
            $edit_url = get_edit_post_link($post_id);
            echo '<a href="'.esc_url( $edit_url ).'"><mark class="order-status status-' . esc_attr($eshb_booking_request_metaboxes['booking_status']) . ' tips"><span>' . esc_html($eshb_booking_request_metaboxes['booking_status']) . '</span></mark></a>';
            break;
    }
}
add_action('manage_eshb_booking_request_posts_custom_column', 'eshb_booking_request_custom_column_content', 10, 2);


function eshb_booking_request_reorder_columns($columns) {

    // Save the date column
    $date_column = $columns['date'];
    unset($columns['date']); // Remove the date column temporarily
    unset($columns['booking_status']); // Remove the date column temporarily

    // Add your custom columns
    $columns['booking_start_date'] = esc_html__( 'Start Date', 'easy-hotel' );
    $columns['booking_end_date'] = esc_html__( 'End Date', 'easy-hotel' );
    $columns['booking_end_date'] = esc_html__( 'End Date', 'easy-hotel' );
    $columns['room_quantity'] = esc_html__( 'Booked Rooms', 'easy-hotel' );

    // Add the date column back as the last column
    $columns['date'] = $date_column;

    return $columns;

}
add_filter('manage_eshb_booking_request_posts_columns', 'eshb_booking_request_reorder_columns');