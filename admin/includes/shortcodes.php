<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function eshb_search_form_shortcode() {
    
    // Ensure the class exists before attempting to use it
    if (class_exists('ESHB_View')) {
        ob_start();
        
        include ESHB_PL_PATH . 'public/templates/easy-hotel-search.php';

        return ob_get_clean();
    } else {
        return __('ESHB_View class not found.', 'easy-hotel');
    }
}

// Register the shortcode
add_shortcode('eshb_search_form', 'eshb_search_form_shortcode');



// Accomodation Grid
function eshb_accomodation_grid_shortcode() {
    if (class_exists('ESHB_View')) {
        // Start output buffering
        ob_start();
        
        include ESHB_PL_PATH . 'public/templates/easy-hotel-archive.php';

        // Capture the output and clean buffer
        return ob_get_clean();
    }

    // Return empty string if the class doesn't exist
    return '';
}

add_shortcode('eshb_accomodation_grid', 'eshb_accomodation_grid_shortcode'); 



// Accomodation Grid
function eshb_accomodation_search_result_shortcode() {
    if (class_exists('ESHB_View')) {
        // Start output buffering
        ob_start();
        
        include ESHB_PL_PATH . 'public/templates/template-parts/search-results-contents.php';

        // Capture the output and clean buffer
        return ob_get_clean();
    }

    // Return empty string if the class doesn't exist
    return '';
}

add_shortcode('eshb_accomodation_search_result', 'eshb_accomodation_search_result_shortcode'); 


// Accommodation Info Shortcode
function eshb_accomodation_info_shortcode() {
    $accomodation_id = get_the_ID();
    $eshb_accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
    $output = '';

    if ( ! empty( $eshb_accomodation_metaboxes['accomodation_info_group'] ) ) {
        foreach ( $eshb_accomodation_metaboxes['accomodation_info_group'] as $group ) {
            $icon = !empty($group['info_icon']) ? '<i class="info-icon ' . esc_html($group['info_icon']) . '"></i>' : '';
            $title = esc_html($group['info_title']);
            $output .= "<p class='info'>{$icon}<span class='info-title'>{$title}</span></p>";
        }
    }

    return $output;
}
add_shortcode('eshb_accomodation_info', 'eshb_accomodation_info_shortcode');

// Booking Form Shortcode
function eshb_booking_form_shortcode($atts) {
    // Create a new instance of the ESHB_View class
    $defaults = array(
        'accomodation_id' => '',
        'style' => 'style-one',
    );

    $atts = shortcode_atts($defaults, $atts);

    $view = new ESHB_View();
    
    return $view->eshb_get_booking_form_html($atts['accomodation_id'], $atts['style']);
}
add_shortcode('eshb_booking_form', 'eshb_booking_form_shortcode');

// Availability Calendar Shortcode
function eshb_availability_calendar_shortcode($atts) {

    $defaults = array(
        'accomodation_id' => get_the_ID(),
        'style' => 'style-one',
    );

    $atts = shortcode_atts($defaults, $atts);

    $view = new ESHB_View();

    return $view->eshb_get_availability_calendar_html($atts['accomodation_id'], $atts['style']);
}
add_shortcode('eshb_availability_calendar', 'eshb_availability_calendar_shortcode');

// Related Rooms Shortcode
function eshb_related_accomodations_shortcode() {
    if(is_singular( 'eshb_accomodation')) {
        ob_start();
        require ESHB_PL_PATH . 'public/templates/template-parts/room-slider.php';
        return ob_get_clean();
    } 
}
add_shortcode('eshb_related_accomodations', 'eshb_related_accomodations_shortcode');



// Daywise Pricing Table Shortcode
function eshb_daywise_pricing_table_shortcode($atts) {
    // Create a new instance of the ESHB_View class
    $defaults = array(
        'accomodation_id' => get_the_ID(),
        'show_title' => false,
    );

    $atts = shortcode_atts($defaults, $atts);

    $view = new ESHB_View();
    ob_start();
    $view->eshb_day_wise_pricing_table_html($atts['accomodation_id'], $atts['show_title']);
    $output = ob_get_clean();
    return $output;
}
add_shortcode('eshb_daywise_pricing_table', 'eshb_daywise_pricing_table_shortcode');

// Check In/Out Times Shortcode
function eshb_check_in_out_times_shortcode() {
    // Create a new instance of the ESHB_View class
    $view = new ESHB_View();
    ob_start();
    $view->eshb_get_eshb_check_in_out_times_html();
    $output = ob_get_clean();
    return $output;
}
add_shortcode('eshb_check_in_out_times', 'eshb_check_in_out_times_shortcode');