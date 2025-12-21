<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
add_action( 'plugins_loaded', function(){
    if( class_exists( 'ESHB' ) ) {
    
        // A Callback function for diposit details
        function eshb_deposit_requests_html_fallback() {
            $plugin_name = 'EHB Deposit';
            $plugin_slug = 'easy-deposit';
            $plugin_url = 'https://themewant.com/downloads/'.$plugin_slug;
            ?>
                <?php echo wp_kses_post(ESHB_Metabox_Settings::eshb_upgrade_message($plugin_name, $plugin_url, 'p')); ?>
                <div class="eshb-deposit-requests-metaboxes-inner has-required-notice">
                    <p><?php echo esc_html__( 'You can send deposit & due payment request to the customer.', 'easy-hotel' )?></p>
                    <div class="eshb-deposit-requests-metabox-item">
                        <button type="button" id="deposit-payment-request" class="button button-primary"><?php echo esc_html__( 'Send', 'easy-hotel' )?></button>
                        <span><?php echo esc_html__( 'Deposit Request', 'easy-hotel' )?></span>
                    </div>
                    
                    <div class="eshb-deposit-requests-metabox-item">
                        <button type="button" id="due-payment-request" class="button button-secondary"><?php echo esc_html__( 'Send', 'easy-hotel' )?></button>
                        <span><?php echo esc_html__( 'Due Payment Request', 'easy-hotel' )?></span>
                    </div>
                </div>
            <?php
        }

        function eshb_add_custom_columns($columns) {
            $columns['booking_start_date'] = esc_html__( 'Start Date', 'easy-hotel' );
            $columns['booking_end_date'] = esc_html__( 'End Date', 'easy-hotel' );
            $columns['booking_time_slot'] = esc_html__( 'Time Slot', 'easy-hotel' );
            $columns['booking_status'] = esc_html__( 'Booking Status', 'easy-hotel' );
            $columns['room_quantity'] = esc_html__( 'Booked Rooms', 'easy-hotel' );
            return $columns;
        }
        add_filter('manage_eshb_booking_posts_columns', 'eshb_add_custom_columns');
        
        function eshb_custom_column_content($column, $post_id) {
            $eshb_booking_metaboxes = get_post_meta($post_id, 'eshb_booking_metaboxes', true);
            if(!$eshb_booking_metaboxes){
                return;
            }

            $time_slot = !empty($eshb_booking_metaboxes['booking_start_time']) && !empty($eshb_booking_metaboxes['booking_end_time']) ? ESHB_Helper::format_to_wp_time($eshb_booking_metaboxes['booking_start_time']) . ' - ' . ESHB_Helper::format_to_wp_time($eshb_booking_metaboxes['booking_end_time']) : '';
            $room_qty = !empty($eshb_booking_metaboxes['room_quantity']) ? $eshb_booking_metaboxes['room_quantity'] : '';

            switch ($column) {
                case 'booking_start_date':
                    echo esc_html(date_i18n( get_option('date_format'), strtotime( $eshb_booking_metaboxes['booking_start_date'] ) ));
                    break;
                case 'booking_end_date':
                    echo esc_html(date_i18n( get_option('date_format'), strtotime( $eshb_booking_metaboxes['booking_end_date'] ) ));
                    break;
                case 'booking_time_slot':
                    echo esc_html( $time_slot );
                    break;
                case 'room_quantity':
                    echo esc_html($room_qty);
                    break;
                case 'booking_status':
                    $edit_url = get_edit_post_link($post_id);
                    echo '<a href="'.esc_url( $edit_url ).'"><mark class="order-status status-' . esc_attr($eshb_booking_metaboxes['booking_status']) . ' tips"><span>' . esc_html($eshb_booking_metaboxes['booking_status']) . '</span></mark></a>';
                    break;
            }
        }
        add_action('manage_eshb_booking_posts_custom_column', 'eshb_custom_column_content', 10, 2);
        
        
        function eshb_reorder_columns($columns) {
        
            // Save the date column
            $date_column = $columns['date'];
            unset($columns['date']); // Remove the date column temporarily
        
            // Add your custom columns
            $columns['booking_start_date'] = esc_html__( 'Start Date', 'easy-hotel' );
            $columns['booking_end_date'] = esc_html__( 'End Date', 'easy-hotel' );
            $columns['booking_time_slot'] = esc_html__( 'Time Slot', 'easy-hotel' );
            $columns['room_quantity'] = esc_html__( 'Booked Rooms', 'easy-hotel' );
        
            // Add the date column back as the last column
            $columns['date'] = $date_column;
        
            return $columns;
        
        }
        add_filter('manage_eshb_booking_posts_columns', 'eshb_reorder_columns');

        function eshb_booking_metaboxes_custom_hidden_fields(){
            $booking_accomodation_id = '';

            $booking_id = get_the_ID() ?? '';
            $eshb_booking_metaboxes = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
            if(!empty($eshb_booking_metaboxes)){
                $booking_accomodation_id = $eshb_booking_metaboxes['booking_accomodation_id'];
            }
            ?>
            <input type="text" name="accomodation_id" value="<?php echo esc_attr( $booking_accomodation_id )?>">
            <?php
            ESHB_Helper::eshb_nonce_field('eshb_global_nonce_action', 'nonce', true);
        }

        function eshb_time_slots_html() {
            ?>
            <div class="csf-title"><h4><?php echo esc_html__( 'Time Slots', 'easy-hotel' );?></h4></div>
            <div class="csf-fieldset">
                <div class="eshb-form-groups time-slots-wrapper">
                    <div class="eshb-form-group">
                        
                        <div class="time-slots">
                            
                        </div>
                    </div>
                    <p class="time-err-msg err-msg"></p>
                    <p class="empty-slot-msg err-msg"><?php echo esc_html__( 'All slots are Booked!', 'easy-hotel' ); ?></p>
                </div>
            </div>
                    
            <?php
        }

        function eshb_booking_metaboxes_pricing_calculation_fields(){

            $eshb_settings = get_option( 'eshb_settings', [] );

            $string_total_cost = isset($eshb_settings['string_total_cost']) && !empty($eshb_settings['string_total_cost']) ? $eshb_settings['string_total_cost'] : 'Total Cost';
            $string_disocunted_price = isset($eshb_settings['string_disocunted_price']) && !empty($eshb_settings['string_disocunted_price']) ? $eshb_settings['string_disocunted_price'] : 'Discounted Price';
            $booking_accomodation_id = '';

            
            $booking_id = get_the_ID();
            $eshb_booking_metaboxes = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
            if(!empty($eshb_booking_metaboxes)){
                $booking_accomodation_id = $eshb_booking_metaboxes['booking_accomodation_id'];
            }
            
            ?>
                <div class="eshb-form-group cost-calculator-wrapper">
                    <h3 class="field-label total-cost-label eshb-booking-total-pricing">
                        <?php echo esc_html(eshb_get_translated_string($string_total_cost));?>
                        <div class="eshb-booking-value" id="eshb-booking-total-price">--</div>
                    </h3>
                    <h3 class="field-label total-cost-label eshb-booking-total-discounted-pricing">
                        <?php echo esc_html(eshb_get_translated_string($string_disocunted_price));?>
                        <div class="eshb-booking-value" id="eshb-booking-discounted-price"></div>
                    </h3>
                </div>
            
            <?php
        }

        // A Callback function for diposit details
        function eshb_payment_history_html() {
            if ( get_post_type(get_the_ID()) == 'eshb_booking') {

                $booking_id = get_the_ID();
                ESHB_Admin_View::eshb_show_payment_history_in_admin($booking_id);
                
            }
        }

        function eshb_get_default_extra_services (){


            $booking_id = get_the_ID();
            if(empty($booking_id)) return;

            $eshb_booking_metaboxes = get_post_meta($booking_id, 'eshb_booking_metaboxes', true);

            if(!$eshb_booking_metaboxes || empty($eshb_booking_metaboxes)) return;

            $booking_accomodation_id = $eshb_booking_metaboxes['booking_accomodation_id'];
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

    
        $conditional_hidden_class = 'hidden-metabox';
       
        $post_id = '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['post'] ) && isset( $_GET['nonce'] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
            $post_id = sanitize_text_field( wp_unslash( $_GET['post'] ) );
        }
        
        if(!empty($post_id)){

            $status = get_post_status( $post_id );

            if($status != 'publish'){
                $conditional_hidden_class = 'hidden-metabox';
            }

        }else{
            $conditional_hidden_class = '';
        }

        $dates = ESHB_Helper::get_eshb_default_start_end_date();
        $times = ESHB_Helper::get_eshb_default_start_end_time();

        $saved_country = !empty($post_id) ? ESHB_Helper::get_current_booking_customer_metadata( $post_id, 'country' ) : 'default';
        $saved_state = !empty($post_id) ? ESHB_Helper::get_current_booking_customer_metadata( $post_id, 'state' ) : 'default';
        $saved_city = !empty($post_id) ? ESHB_Helper::get_current_booking_customer_metadata( $post_id, 'city' ) : 'default';

        $saved_state_name = !empty(ESHB_Helper::eshb_get_wc_state_city_name($saved_country, $saved_state)) ? ESHB_Helper::eshb_get_wc_state_city_name($saved_country, $saved_state) : $saved_state;
        $saved_city_name = !empty(ESHB_Helper::eshb_get_wc_state_city_name($saved_country, $saved_city)) ? ESHB_Helper::eshb_get_wc_state_city_name($saved_country, $saved_city) : $saved_city;


        // Set a unique slug-like ID
        $prefix = 'eshb_booking_metaboxes';
        // Create a metabox
        ESHB::createMetabox( $prefix, array(
            'title'              => 'Booking Options',
            'post_type'          => 'eshb_booking',
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
                    'id'          => 'booking_status',
                    'type'        => 'select',
                    'title'       => 'Booking Status',
                    'placeholder' => 'Select an option',
                    'options'     => ESHB_Helper::eshb_get_booking_statuses(),
                    'class'       => 'hidden-metabox',
                    'default'    => 'pending',
                ),
                array(
                    'id'          => 'order_id',
                    'type'        => 'text',
                    'title'       => 'Booking Order Id',
                    'class'       => 'hidden-metabox',
                ),
                array(
                    'id'          => 'payment_ids',
                    'type'        => 'select',
                    'title'       => 'Payment Ids',
                    'placeholder' => 'Select an option',
                    'class'       => 'hidden-metabox',
                    'options'     => ESHB_Helper::eshb_get_payment_ids(),
                    'multiple'    => true,
                ),
                array(
                    'id'          => 'booking_accomodation_id',
                    'type'        => 'select',
                    'title'       => 'Accomodation',
                    //'placeholder' => 'Select an option',
                    'options'     => 'posts',
                    'query_args'  => array(
                                        'post_type' => 'eshb_accomodation',
                                        'posts_per_page' => -1,
                                    ),
                ),
                array(
                    'id'          => 'booking_hidden_fields',
                    'type'        => 'callback',
                    'function'    => 'eshb_booking_metaboxes_custom_hidden_fields',
                    'class'       => 'hidden-metabox'
                ),
                array(
                    'id'    => 'booking_start_date',
                    'type'  => 'text',
                    'title' => 'Start Date',
                    'settings' => array(
                        'altFormat'  => 'F j, Y',
                        'dateFormat' => 'Y-m-d',
                    ),
                    'default' => $dates['start_date'],
                    'class' => 'required-field'
                ),
                array(
                    'id'    => 'booking_end_date',
                    'type'  => 'text',
                    'title' => 'End Date',
                    'settings' => array(
                        'altFormat'  => 'F j, Y',
                        'dateFormat' => 'Y-m-d',
                    ),
                    'default' => $dates['end_date'],
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                    'class' => 'booking-end-date-field required-field'
                ),
                array(
                    'id'    => 'booking_start_time',
                    'type'  => 'datetime',
                    'title' => 'Start Time',
                    'class'       => 'time-picker-metabox hidden-metabox',
                    'settings' => array(
                        'noCalendar' => true,
                        'enableTime' => true,
                        'dateFormat' => 'H:i',
                        'time_24hr'  => false,
                    ),
                    'default' => '10:00',
                ),
                array(
                    'id'    => 'booking_end_time',
                    'type'  => 'datetime',
                    'title' => 'End Time',
                    'class'       => 'time-picker-metabox hidden-metabox',
                    'settings' => array(
                        'noCalendar' => true,
                        'enableTime' => true,
                        'dateFormat' => 'H:i',
                        'time_24hr'  => false,
                    ),
                    'default' => '22:00',
                ),
                array(
                    'id'    => 'time_slots_html',
                    'type'     => 'callback',
                    'function' => 'eshb_time_slots_html',
                    'class'    => 'time-slots-metabox'
                ),
                array(
                    'id'          => 'total_paid',
                    'type'        => 'text',
                    'title'       => 'Total Paid',
                    'class'       => 'hidden-metabox',
                ),
                array(
                    'id'          => 'total_price',
                    'type'        => 'text',
                    'title'       => 'Total Price',
                    'class'       => 'hidden-metabox',
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                ),
                array(
                    'id'          => 'subtotal_price',
                    'type'        => 'text',
                    'title'       => 'Subtotal Price',
                    'class'       => 'hidden-metabox',
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                ),
                array(
                    'id'          => 'base_price',
                    'type'        => 'text',
                    'title'       => 'Base Price',
                    'class'       => 'hidden-metabox',
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                ),
                array(
                    'id'          => 'extra_service_price',
                    'type'        => 'text',
                    'title'       => 'Extra Service Prices',
                    'class'       => 'hidden-metabox',
                ),
                array(
                    'id'          => 'extra_bed_price',
                    'type'        => 'text',
                    'title'       => 'Extra Bed Prices',
                    'class'       => 'hidden-metabox',
                ),
                array(
                    'id'          => 'dates',
                    'type'        => 'text',
                    'title'       => 'Dates',
                    'class'       => 'hidden-metabox',
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                ),
                array(
                    'id'          => 'room_quantity',
                    'type'        => 'number',
                    'title'       => 'Room Quantity',
                    'class'       => 'booking-requirement-input required-field',
                    'default'     => 1,
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                ),
                array(
                    'id'          => 'extra_bed_quantity',
                    'type'        => 'number',
                    'title'       => 'Extra Bed Quantity',
                    'class'       => 'booking-requirement-input',
                ),
                array(
                    'id'          => 'adult_quantity',
                    'type'        => 'number',
                    'title'       => 'Adult Quantity',
                    'class'       => 'booking-requirement-input required-field',
                    'default'     => 1,
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                ),
                array(
                    'id'          => 'children_quantity',
                    'type'        => 'number',
                    'title'       => 'Children Quantity',
                    'class'       => 'booking-requirement-input'
                ),
                array(
                    'id'          => 'details_html',
                    'type'        => 'text',
                    'title'       => 'Details Html',
                    'class'       => 'hidden-metabox'
                ),
                array(
                    'id'          => 'extra_services_html',
                    'type'        => 'textarea',
                    'title'       => 'Extra Services Html',
                    'class'       => 'hidden-metabox'
                ),
                array(
                    'id'        => 'extra_services',
                    'type'      => 'group',
                    'title'     => 'Extra Services',
                    'class'       => 'booking-requirement-extra-services-fields',
                    'fields'    => array(
                    array(
                        'id'    => 'id',
                        'type'        => 'select',
                        'title'       => 'Service',
                        'options'     => ESHB_Helper::eshb_get_extra_services(),
                        'class'   => 'booking-requirement-extra-services-id',
                        'default' => eshb_get_default_extra_services(),
                    ),
                    array(
                        'id'    => 'quantity',
                        'type'  => 'text',
                        'title' => 'Quantity',
                        'class'   => 'booking-requirement-extra-services-qty'
                    ),
                    ),
                ),
                array(
                    'id'          => 'booking_pricing_calculation_fields',
                    'type'        => 'callback',
                    'function'    => 'eshb_booking_metaboxes_pricing_calculation_fields',
                    'class'       => 'booking_metaboxes-pricing-calculation-fields'
                ),

            )
        ) );

        // Set a unique slug-like ID
        $prefix = 'eshb_payment_info_metaboxes';
  
        // Create a metabox
        ESHB::createMetabox( $prefix, array(
            'title'              => 'Payment Information',
            'post_type'          => 'eshb_booking',
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

        if(!empty($post_id)){
            // Create a section
            ESHB::createSection( $prefix, array(
                'title'  => '',
                'fields' => array(
                    array(
                        'type'     => 'callback',
                        'function' => 'eshb_payment_history_html',
                    ),
                )
            ) );
        }

        // Set a unique slug-like ID
        $prefix = 'eshb_booking_customer_details_metaboxes';
        // Create a metabox
        ESHB::createMetabox( $prefix, array(
            'title'              => 'Customer Details Options',
            'post_type'          => 'eshb_booking',
            'data_type'          => 'serialize',
            'context'            => 'advanced',
            'priority'           => 'low',
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
                    'class'    => 'required-field'
                ),
                array(
                    'id'          => 'last_name',
                    'type'        => 'text',
                    'title'       => 'Last Name',
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                ),
                array(
                    'id'          => 'email',
                    'type'        => 'text',
                    'title'       => 'Email',
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                    'class'    => 'required-field'
                ),
                array(
                    'id'          => 'phone',
                    'type'        => 'text',
                    'title'       => 'Phone',
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                    'class'    => 'required-field'
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
                ),
                array(
                    'id'          => 'address_1',
                    'type'        => 'text',
                    'title'       => 'Address line one',
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                    'class'    => 'required-field'
                ),
                array(
                    'id'          => 'address_2',
                    'type'        => 'text',
                    'title'       => 'Address line two',
                ),
                array(
                    'id'          => 'postcode',
                    'type'        => 'text',
                    'title'       => 'Postcode / ZIP',
                    'validate' => 'eshb_validate_for_required', // Required validation
                    'required' => true,
                    'class'    => 'required-field'
                ),
                array(
                    'type'     => 'callback',
                    'function' => 'eshb_after_customer_details_fields',
                ),
            )
        ) );

        function eshb_after_customer_details_fields () {
            global $post;
            ?>
            <button type="button" id="publishing-action-booking-custom" class="button button-primary">
                <?php
					in_array( $post->post_status, array( 'new', 'auto-draft' ) ) ? esc_attr_e( 'Create Booking', 'easy-hotel' ) : esc_attr_e( 'Update Booking', 'easy-hotel' );
					?>
            </button>
            <?php
        }

        $plugin_main_file = 'ehb-deposit/ehb-deposit.php';
        $required_notice_class = 'has-required-notice';

        $metabox_callback = 'eshb_deposit_requests_html_fallback';
        if (function_exists( 'eshb_deposit_requests_html' ) ) {
            $metabox_callback = 'eshb_deposit_requests_html';
            $required_notice_class = '';
        };


        // payment request metaboxes
        $prefix = 'eshb_payment_request_metaboxes';
        

        // Create a metabox
        ESHB::createMetabox( $prefix, array(
            'title'              => 'Payment Requests',
            'post_type'          => 'eshb_booking',
            'data_type'          => 'serialize',
            'context'            => 'side',
            'priority'           => 'low',
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
                    'id'         => 'enable-automatic-payment-request',
                    'type'       => 'checkbox',
                    'label'   => 'Enable automatic payment request for this booking.',
                    'default' => false, // or false,
                    'class'   => $required_notice_class,
                ),
                array(
                    'type'     => 'callback',
                    'function' => $metabox_callback,
                ),
                
            )
        ) );
        
    }
} );