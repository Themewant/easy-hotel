<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
add_action('wp_enqueue_scripts', 'eshb_wp_enqueue_scripts', 999);
function eshb_wp_enqueue_scripts (){

    $css_version = filemtime( ESHB_PL_PATH . 'public/assets/css/public.css' );
   
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style( 'eshb-daterangepicker-style', ESHB_PL_URL . 'public/assets/css/date-range-picker.css', array(), $css_version );
    wp_enqueue_style( 'eshb-style', ESHB_PL_URL . 'public/assets/css/public.css', array(), $css_version );
    // Show a formatted, translated date on top of the machine (Y-m-d) date input.
    // The machine input stays in normal flow (so the calendar opens exactly as before);
    // only its text is hidden. The display overlay is click-through (pointer-events:none).
    wp_add_inline_style( 'eshb-style', '.eshb-date-field{position:relative;display:block;width:100%}.eshb-date-field .eshb-date-machine{color:transparent;-webkit-text-fill-color:transparent;caret-color:transparent}.eshb-date-field .eshb-date-machine::selection{background:transparent}.eshb-date-field .eshb-date-display{position:absolute;top:0;left:0;width:100%;height:100%;margin:0;pointer-events:none;background:transparent;border-color:transparent;box-shadow:none}.daterangepicker td.active.start-date{pointer-events:none;cursor:not-allowed}' );
    wp_enqueue_style( 'eshb-fontawesome-style', ESHB_PL_URL . 'public/assets/css/fontawesome.all.min.css', array(), '7.2.0', 'all' );
    wp_enqueue_style( 'swiper', ESHB_PL_URL . 'public/assets/css/swiper-bundle.min.css', array(), $css_version, 'all' );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'moment' );
    wp_enqueue_script( 'eshb-date-range-picker-js', ESHB_PL_URL . 'public/assets/js/date-range-picker.js', array('jquery'),'3.1',true );
    wp_enqueue_script( 'eshb-swiper', ESHB_PL_URL . 'public/assets/js/swiper-bundle.min.js', array(),'12.1.4',false );
    wp_enqueue_script( 'eshb-public-script', ESHB_PL_URL . 'public/assets/js/public.js', array(),'1.0.0',true );
    wp_enqueue_script( 'eshb-booking-script', ESHB_PL_URL . 'public/assets/js/booking.js', array(), ESHB_VERSION, true );
  
    
     // Get WordPress current locale
     $locale = get_locale();
     $eshb_settings = get_option('eshb_settings');
     $apply_text_default = isset($eshb_settings['string_apply']) && !empty($eshb_settings['string_apply']) ? $eshb_settings['string_apply'] : '';
     $cancel_text_default = isset($eshb_settings['string_cancel']) && !empty($eshb_settings['string_cancel']) ? $eshb_settings['string_cancel'] : '';
    
    ESHB_Helper::eshb_set_accomodation_localize();

    // Cart blocking notice config for JS injection
    $eshb_notice_msg = ! empty( $eshb_settings['cart-blocking-notice-msg'] )
        ? esc_html( $eshb_settings['cart-blocking-notice-msg'] )
        : esc_html__( 'Your reservation is held for', 'easy-hotel' );
    wp_localize_script( 'eshb-booking-script', 'eshb_cart_notice', [
        'enabled' => ! empty( $eshb_settings['cart-blocking-switcher'] ) ? '1' : '0',
        'msg'     => $eshb_notice_msg,
    ] );

    wp_localize_script('eshb-public-script', 'eshb_rest', [
        'root'  => esc_url(rest_url()),
        'nonce' => wp_create_nonce('wp_rest')
    ]);

     // Convert the WordPress "date_format" (Settings > General) into a moment.js format
     // so the calendar can display dates exactly as WordPress does, per the site locale.
     $eshb_php_to_moment = array(
         'd' => 'DD', 'j' => 'D', 'D' => 'ddd', 'l' => 'dddd', 'N' => 'E', 'w' => 'd', 'S' => '',
         'm' => 'MM', 'n' => 'M', 'M' => 'MMM', 'F' => 'MMMM',
         'Y' => 'YYYY', 'y' => 'YY',
     );
     $eshb_wp_date_format = get_option( 'date_format', 'F j, Y' );
     $eshb_moment_format  = strtr( $eshb_wp_date_format, $eshb_php_to_moment );

     // Translated month + short weekday names (from WordPress translations, so they are
     // guaranteed localized even when moment.js has no bundled locale on the page).
     $eshb_months = array();
     for ( $m = 1; $m <= 12; $m++ ) {
         $eshb_months[] = wp_date( 'F', mktime( 0, 0, 0, $m, 1, 2025 ) );
     }
     $eshb_weekdays_short = array(); // Sunday-first to match moment.js weekdaysShort()
     for ( $d = 0; $d < 7; $d++ ) {
         $eshb_weekdays_short[] = wp_date( 'D', strtotime( 'Sunday +' . $d . ' days' ) );
     }

     // Prepare translations dynamically based on current locale
     $translations = [
         'displayFormat'     => $eshb_moment_format,
         'locale'            => str_replace( '_', '-', strtolower( $locale ) ),
         'months'            => $eshb_months,
         'weekdaysShort'     => $eshb_weekdays_short,
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
     wp_localize_script('eshb-date-range-picker-js', 'eshb_daterangepicker_i18n', $translations);
}



