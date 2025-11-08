<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

add_action('admin_enqueue_scripts', 'eshb_admin_enqueue_scripts');
function eshb_admin_enqueue_scripts (){
    wp_enqueue_style( 'eshb-daterangepicker-style', ESHB_PL_URL . 'public/assets/css/date-range-picker.css', array(), ESHB_VERSION );
    wp_enqueue_style( 'eshb-admin-style', ESHB_PL_URL . 'admin/assets/css/admin.min.css', array(), ESHB_VERSION);
    wp_enqueue_script('moment');
    wp_enqueue_script( 'eshb-date-range-picker-js', ESHB_PL_URL . 'public/assets/js/date-range-picker.js', array('jquery'),'1.0.0',true );
    wp_enqueue_script( 'eshb-admin-script', ESHB_PL_URL . 'admin/assets/js/admin.js', array('jquery'), ESHB_VERSION, true );
    wp_enqueue_script( 'eshb-admin-booking-script', ESHB_PL_URL . 'public/assets/js/booking.js', array('jquery'), ESHB_VERSION, true );

    $min_max_settings_global = [
        'calendar_start_date_buffer' => 0,
    ];
    $min_max_settings = apply_filters( 'eshb_min_max_global_settings_localize', $min_max_settings_global);
    $calendar_start_date_buffer = !empty($eshb_min_max_settings['calendar_start_date_buffer']) ? $eshb_min_max_settings['calendar_start_date_buffer'] : 0;
  
    $eshb_admin_translations = [
        'billingEmailErr' => __('Billing email not found!', 'easy-hotel'),       
        'maximumTimeSlot' => __('Allowed max time for this slot is', 'easy-hotel'),
        'minimumTimeSlot' => __('Allowed min time for this slot is', 'easy-hotel'), 
        'minNightsErrorMsg' => __('Ops! This Reservation has been failed. Requried Minimum', 'easy-hotel'),
        'maxNightsErrorMsg' => __('Ops! This Reservation has been failed. Requried Maximum', 'easy-hotel'),
        'minNightsErrorMsgAvCal' => __('Requried Minimum Nights:', 'easy-hotel'),
        'maxNightsErrorMsgAvCal' => __('Requried Maximum Nights:', 'easy-hotel'),
    ];

    $nonce_action = ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action');
    wp_localize_script( 'eshb-admin-script', 'eshb_ajax',
        array( 
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'is_admin'  => is_admin(),
            'nonce'     => wp_create_nonce($nonce_action),
            'pluginURL' => ESHB_DIR_URL,
            'admin_translations' => $eshb_admin_translations,
            'calendar_start_date_buffer' => $calendar_start_date_buffer,
        ) 
    );
    

    // Get WordPress current locale
    $locale = get_locale();
    $eshb_settings = get_option('eshb_settings');
    $apply_text_default = isset($eshb_settings['string_apply']) && !empty($eshb_settings['string_apply']) ? $eshb_settings['string_apply'] : '';
    $cancel_text_default = isset($eshb_settings['string_cancel']) && !empty($eshb_settings['string_cancel']) ? $eshb_settings['string_cancel'] : '';
   

    // Prepare translations dynamically based on current locale
    $calendar_translations = [
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
    wp_localize_script('eshb-date-range-picker-js', 'eshb_daterangepicker_i18n', $calendar_translations);
}