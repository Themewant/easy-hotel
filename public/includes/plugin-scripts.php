<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
add_action('wp_enqueue_scripts', 'eshb_wp_enqueue_scripts');
function eshb_wp_enqueue_scripts (){

    $css_version = filemtime( ESHB_PL_PATH . 'public/assets/css/public.css' );
   
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style( 'eshb-daterangepicker-style', ESHB_PL_URL . 'public/assets/css/date-range-picker.css', array(), $css_version );
    wp_enqueue_style( 'eshb-style', ESHB_PL_URL . 'public/assets/css/public.min.css', array(), $css_version );
    wp_enqueue_style( 'eshb-fontawesome-style', ESHB_PL_URL . 'public/assets/css/fontawesome-5.13-all.css', array(), '5.13.0', 'all' );
    wp_enqueue_style( 'swiper', ESHB_PL_URL . 'public/assets/css/swiper-bundle.min.css', array(), $css_version, 'all' );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'moment' );
    wp_enqueue_script( 'eshb-date-range-picker-js', ESHB_PL_URL . 'public/assets/js/date-range-picker.js', array('jquery'),'1.0.0',true );
    wp_enqueue_script( 'eshb-swiper', ESHB_PL_URL . 'public/assets/js/swiper-bundle.min.js', array('jquery'),'1.0.0',true );
    wp_enqueue_script( 'eshb-public-script', ESHB_PL_URL . 'public/assets/js/public.js', array(),'1.0.0',true );
    wp_enqueue_script( 'eshb-booking-script', ESHB_PL_URL . 'public/assets/js/booking.js', array(), ESHB_VERSION, true );
  
    
    $cart_url = site_url('/cart');
    if(class_exists('woocommerce')) $cart_url = wc_get_cart_url();
    
    $min_max_settings = [
        'calendar_start_date_buffer' => 0,
        'required_min_nights' => 1,
        'required_max_nights' => 999,
    ];
	$eshb_min_max_settings = apply_filters( 'eshb_min_max_global_settings_localize', $min_max_settings);
    $calendar_start_date_buffer = !empty($eshb_min_max_settings['calendar_start_date_buffer']) ? $eshb_min_max_settings['calendar_start_date_buffer'] : 0;
    $required_min_nights = !empty($eshb_min_max_settings['required_min_nights']) ? $eshb_min_max_settings['required_min_nights'] : 1;
    $required_max_nights = !empty($eshb_min_max_settings['required_max_nights']) ? $eshb_min_max_settings['required_max_nights'] : 999;
   
    $eshb_week_settings = apply_filters( 'eshb_week_settings', [] );
    $string_check_in_day_error_msg = !empty($eshb_week_settings['string_check_in_day_error_msg']) ? $eshb_week_settings['string_check_in_day_error_msg'] : '';


    // accomodation details metadata
    $eshb_accomodation_metaboxes = [];
    $is_it_single_day_booking_accomodation = false;

    if(class_exists('ESHB_Booking') && is_singular( 'eshb_accomodation' )){

        $eshb_booking = new ESHB_Booking();
        $start_date = '';
        $end_date = '';
        
        if (isset($_GET['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
            $start_date = isset($_POST['start_date']) ? sanitize_text_field( wp_unslash($_POST['start_date']) ) : '';
            $end_date = isset($_POST['end_date']) ? sanitize_text_field( wp_unslash($_POST['end_date']) ) : '';
        }

        $accomodation_id = get_the_ID();
        $eshb_accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
        $available_rooms = $eshb_booking->get_available_room_count_by_date_range($accomodation_id, $start_date, $end_date);
        $available_rooms = $available_rooms > 0 ? $available_rooms : 0;
        $eshb_accomodation_metaboxes['available_rooms'] = $available_rooms;


        $is_global_source_for_min_max = !empty($eshb_accomodation_metaboxes['is_global_source_for_min_max']) ? true : false;
        if($is_global_source_for_min_max != true) {
            $required_min_nights = !empty($eshb_accomodation_metaboxes['required_min_nights']) ? $eshb_accomodation_metaboxes['required_min_nights'] : 1;
            $required_max_nights = !empty($eshb_accomodation_metaboxes['required_max_nights']) ? $eshb_accomodation_metaboxes['required_max_nights'] : 999;
        }
    }

  		
    $eshb_translations = [
        'maximumCapacity' => __('Maximum Capacity', 'easy-hotel'),
        'availableCapacity' => __('Available Capacity', 'easy-hotel'),
        'availableRoom' => __('Available Room', 'easy-hotel'),
        'maximumAdultAndChildrenCapacity' => __('Maximum Adult and Children Capacity', 'easy-hotel'),
        'maximumTimeSlot' => __('Allowed max time for this slot is', 'easy-hotel'),
        'minimumTimeSlot' => __('Allowed min time for this slot is', 'easy-hotel'),
        'minNightsErrorMsg' => __('Ops! This Reservation has been failed. Requried Minimum', 'easy-hotel'),
        'maxNightsErrorMsg' => __('Ops! This Reservation has been failed. Requried Maximum', 'easy-hotel'),
        'minNightsErrorMsgAvCal' => __('Requried Minimum Nights:', 'easy-hotel'),
        'maxNightsErrorMsgAvCal' => __('Requried Maximum Nights:', 'easy-hotel'),
    ];

    $nonce_action = ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action');
    wp_localize_script(
            'eshb-public-script', 
            'eshb_ajax',
                [
                    'ajaxurl'          => admin_url( 'admin-ajax.php' ),
                    'adminURL'         => admin_url(),
                    'wooCartUrl'       => $cart_url,
                    'is_admin'         => is_admin(),
                    'nonce'            => wp_create_nonce($nonce_action),
                    'add_to_cart_reservation_nonce' => wp_create_nonce('eshb_add_to_cart_reservation_nonce'),
                    'reservation_request_nonce' => wp_create_nonce('eshb_reservation_request_nonce'),
                    'version'          => ESHB_VERSION,
                    'pluginURL'        => ESHB_DIR_URL,
                    'dateFormat'       => get_option( 'date_format' ),
                    'requiredMinNights' => $required_min_nights,
                    'requiredMaxNights' => $required_max_nights,
                    'calendar_start_date_buffer' => $calendar_start_date_buffer,
                    'checkInDayErrorMsg' => $string_check_in_day_error_msg,
                    'currentAccomodationMeta' => $eshb_accomodation_metaboxes,
                    'translations' => $eshb_translations
                ]
        );


     // Get WordPress current locale
     $locale = get_locale();

     $eshb_settings = get_option('eshb_settings');
     $apply_text_default = isset($eshb_settings['string_apply']) && !empty($eshb_settings['string_apply']) ? $eshb_settings['string_apply'] : '';
     $cancel_text_default = isset($eshb_settings['string_cancel']) && !empty($eshb_settings['string_cancel']) ? $eshb_settings['string_cancel'] : '';
    


     // Prepare translations dynamically based on current locale
     $translations = [
         'applyLabel'        => !empty($apply_text_default) ? eshb_get_translated_string($apply_text_default) : __('Apply', 'easy-hotel'),
         'cancelLabel'       => !empty($cancel_text_default) ? eshb_get_translated_string($cancel_text_default) : __('Cancel', 'easy-hotel'),
         'BookedTooltip'       => __('Already Booked!', 'easy-hotel'),
         'holidayTooltip'       => __('This is Holiday!', 'easy-hotel'),
         'fromLabel'         => __('From', 'easy-hotel'),
         'toLabel'           => __('To', 'easy-hotel'),
         'customRangeLabel'  => __('Custom Range', 'easy-hotel'),
         'weekLabel'         => __('W', 'easy-hotel'),
         'firstDay'          => get_option('start_of_week'), // Get WordPress's start of the week setting
         'currentLocale'     => $locale, // Pass current locale for debugging or further use
     ];

     // Pass translations to JavaScript
     wp_localize_script('eshb-date-range-picker-js', 'daterangepicker_i18n', $translations);
}



