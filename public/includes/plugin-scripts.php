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
  
    
     // Get WordPress current locale
     $locale = get_locale();
     $eshb_settings = get_option('eshb_settings');
     $apply_text_default = isset($eshb_settings['string_apply']) && !empty($eshb_settings['string_apply']) ? $eshb_settings['string_apply'] : '';
     $cancel_text_default = isset($eshb_settings['string_cancel']) && !empty($eshb_settings['string_cancel']) ? $eshb_settings['string_cancel'] : '';
    
    ESHB_Helper::eshb_set_accomodation_localize();

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



