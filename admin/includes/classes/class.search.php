<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class ESHB_Search {

    private static $_instance = null;
	
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

    public function __construct() {

		add_action( 'plugins_loaded', [ $this, 'init' ] );
        add_action( "wp_ajax_nopriv_eshb_get_disabled_dates_by_accomodation_id", array ( $this, 'eshb_get_disabled_dates_by_accomodation_id' ) );
        add_action( "wp_ajax_eshb_get_disabled_dates_by_accomodation_id",        array ( $this, 'eshb_get_disabled_dates_by_accomodation_id' ) );

	}

    public function init() {
		// Add Plugin actions
		add_filter('theme_page_templates', [$this, 'eshb_register_page_template']);
        add_filter('template_include', [$this, 'eshb_load_template']);
	}

    public function eshb_search_page_by_title( $page_title, $post_type = 'page' ) {
        // Set up the arguments for WP_Query
        $args = array(
            'post_type'      => $post_type,
            'post_status'    => 'publish', // Only fetch published pages
            'title'          => $page_title,
            'posts_per_page' => 1,         // Limit to one result
        );
        
        // Run the query
        $query = new WP_Query( $args );
        
        // Check if any post is found
        if ( $query->have_posts() ) {
            return $query->posts[0]; // Return the first found page
        }
    
        // Return false if no page is found
        return false;
    }

    public function eshb_get_disabled_dates_by_accomodation_id($accomodation_id, $start_date = '', $end_date = '') {

        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            die();
        }
    
        if (isset($_POST['accomodation_id'])) {
            $accomodation_id = sanitize_text_field(wp_unslash($_POST['accomodation_id']));
        }

        // The calendar posts the currently selected range; without reading it here the
        // session (season) rules below were always evaluated against empty dates.
        if (isset($_POST['start_date'])) {
            $start_date = sanitize_text_field(wp_unslash($_POST['start_date']));
        }

        if (isset($_POST['end_date'])) {
            $end_date = sanitize_text_field(wp_unslash($_POST['end_date']));
        }

        $eshb_settings = get_option('eshb_settings', []);
        $booked_dates = [];
        $total_room_bookings_per_date = []; // Track room bookings per date
        $total_booked_slots_hours = 0;
        $total_slots_hours = false;
        
        // Get total rooms allowed for this accommodation
        $accomodation_metas = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);

        $is_single_day_plugin_active = get_option('eshb_single_day_activated');
        $pricing_periodicity = false;
        if ($is_single_day_plugin_active) {
            $pricing_periodicity = !empty($accomodation_metas['pricing_periodicity']) ? $accomodation_metas['pricing_periodicity'] : false;
            $eshb_single_day_settings = get_option( 'eshb_single_day_settings', []);
            $slots_start_time  = $eshb_single_day_settings['start_time'];
            $slots_end_time  = $eshb_single_day_settings['end_time'];
            $total_slots_hours = ESHB_Helper::eshb_calculate_time_diff('', '', $slots_start_time, $slots_end_time);
        }

        $holidays = isset($eshb_settings['holidays']) ? $eshb_settings['holidays'] : [];
        $finally_holidays = [];

        if ( ! empty( $holidays ) ) {
            foreach ( $holidays as $holiday ) {
                $accommodation_ids = $holiday['accomodation-ids'] ?? [];
                $holiday_date      = $holiday['holiday-date'] ?? null;

                // Check if holiday is applicable for all or specific accommodation
                $is_applicable = empty($accommodation_ids) || (is_array($accommodation_ids) && in_array($accomodation_id, $accommodation_ids));

                if ( $is_applicable && $holiday_date ) {
                    if ( is_array($holiday_date) && isset($holiday_date['from'], $holiday_date['to']) ) {
                        $start = DateTime::createFromFormat('Y-m-d', $holiday_date['from']);
                        $end   = DateTime::createFromFormat('Y-m-d', $holiday_date['to']);
                        if ( $start && $end ) {
                            $end->modify('+1 day'); // Include end date
                            $interval  = new DateInterval('P1D');
                            $dateRange = new DatePeriod($start, $interval, $end);

                            foreach ( $dateRange as $date ) {
                                $finally_holidays[] = $date->format('Y-m-d');
                            }
                        }
                    } else {
                        $finally_holidays[] = $holiday_date;
                    }
                }
            }
        }

        
        
        $allowed_total_rooms = isset($accomodation_metas['total_rooms']) ? intval($accomodation_metas['total_rooms']) : 0;
        $total_rooms = isset($accomodation_metas['total_rooms']) ? intval($accomodation_metas['total_rooms']) : 1;
        $available_rooms = isset($accomodation_metas['available_rooms']) ? intval($accomodation_metas['available_rooms']) : $total_rooms;

        $min_max_nights_settings = [
            'required_min_nights' => 1,
            'required_max_nights' => 999,
        ];

        $min_max_nights_settings = apply_filters('eshb_min_max_nights_settings_before_search', $min_max_nights_settings, $accomodation_id, $accomodation_metas);
        
        // Get min stay night by session
        $session_min_nights_rules = [];
        if(class_exists('ESHB_ADVANCED_PRICING')){

            $session_min_nights_rules = ESHB_Helper::get_eshb_session_min_nights_rules($accomodation_id);

            $min_stay_night_by_session = ESHB_Helper::get_eshb_min_stay_night_by_session($accomodation_id, $start_date, $end_date);
            if($min_stay_night_by_session > 0){
                // max(), not a plain override: booking validation enforces the session
                // rule AND the global/accomodation rule, so the calendar has to show
                // whichever is stricter or it would allow a range the server rejects.
                $min_max_nights_settings['required_min_nights'] = max(
                    (int) $min_max_nights_settings['required_min_nights'],
                    (int) $min_stay_night_by_session
                );
            }
        }

        // Find all bookings for this accommodation
        $bookings_args = [
            'post_type'      => 'eshb_booking',
            'posts_per_page' => -1,
            'post_status'    => ['publish', 'deposit-payment', 'pending', 'processing', 'on-hold', 'completed'],
            'fields' => 'ids',
        ];
    
        $bookings = new WP_Query($bookings_args);
        $checked_in_out_dates = [];
    
        if ($bookings->have_posts()) {
            while ($bookings->have_posts()) {
                $bookings->the_post();
                $meta_values = get_post_meta(get_the_ID(), 'eshb_booking_metaboxes', true);
                $booking_status = isset($meta_values['booking_status']) ? $meta_values['booking_status'] : '';

                $completed_booking_status_maps = array(
					'pending',
					'deposit-payment',
					'processing',
					'on-hold',
					'completed'
				); 
    
                // Ensure required keys exist
                if (is_array($meta_values) && in_array($booking_status, $completed_booking_status_maps) && isset($meta_values['booking_accomodation_id'], $meta_values['booking_start_date'], $meta_values['booking_end_date'])) {
                    
                    $booking_accomodation_id = intval($meta_values['booking_accomodation_id']);
                    $booking_start_date = $meta_values['booking_start_date'];
                    $booking_end_date = $meta_values['booking_end_date'];
                    $accommodation_metaboxes = get_post_meta($booking_accomodation_id, 'eshb_accomodation_metaboxes', true);
                    $total_rooms = $accommodation_metaboxes['total_rooms'] ?? 1;

                    $booked_accomodation_ids = [];
                    $accomodation_id = ESHB_Helper::get_main_post_id_for_translated($accomodation_id);

                    $booking_accomodation_id = ESHB_Helper::get_main_post_id_for_translated($booking_accomodation_id);
                    
                    if ($accomodation_id == $booking_accomodation_id) {

                        // Convert dates to DateTime objects
                        $start_date = new DateTime($booking_start_date);
                        $end_date = new DateTime($booking_end_date);
                        $booked_id = get_the_ID();
                        $booking_metas = get_post_meta($booked_id, 'eshb_booking_metaboxes', true);
                        $booked_room_quantity = isset($booking_metas['room_quantity']) ? intval($booking_metas['room_quantity']) : 1;
                        $booking_start_time = isset($booking_metas['booking_start_time']) ? intval($booking_metas['booking_start_time']) : '';
                        $booking_end_time = isset($booking_metas['booking_end_time']) ? intval($booking_metas['booking_end_time']) : '';

                        $checked_in_out_dates['checked_in'][] = $booking_start_date;
                        $checked_in_out_dates['checked_out'][] = $booking_end_date;

                        // NEW: Clone end date and subtract 1 day to exclude checkout day
                        if($booking_start_date !== $booking_end_date) {
                            $actual_end_date = (clone $end_date)->modify('-1 day');
                        }else{
                            $actual_end_date = (clone $end_date)->modify('+1 day');
                        }

                        
                        $period = new DatePeriod($start_date, new DateInterval('P1D'), $actual_end_date->modify('+1 day'));

                        foreach ($period as $date) {
                            $formatted_date = $date->format('Y-m-d');

                            if (!isset($total_room_bookings_per_date[$formatted_date])) {
                                $total_room_bookings_per_date[$formatted_date] = 0;
                            }

                            $total_room_bookings_per_date[$formatted_date] += $booked_room_quantity;
                        }

                        if($booking_start_date === $booking_end_date) {
                            array_pop($total_room_bookings_per_date);
                        }

                    }
                }
            }
        }

        
        $total_booked_rooms_in_date_ranges = 0;
        $dates = [];

        // Determine which dates should be disabled (fully booked)
        foreach ($total_room_bookings_per_date as $date => $total_booked_rooms) {
        
            // allow external plugins to modify available slots
            $available_slots = apply_filters(
                'eshb_available_slots_in_booked_dates_loop',
                false,
                $date,
                $total_booked_rooms,
                $accomodation_id
            );

    
            if($available_slots) {
                $total_booked_rooms = 0;
            }
            
            if ($allowed_total_rooms <= $total_booked_rooms) {
                $date = new DateTime($date);
                $booked_dates[] = $date->format('Y, m, d');
                array_push($dates, array('dates' => $date, 'total_booked_rooms_in_date_ranges' => $total_booked_rooms));
            }
            
        }


        // Apply filter hook to allow external modifications
		$booked_dates = apply_filters('eshb_modify_booked_dates', $booked_dates, $accomodation_id);

		$checked_in_out_dates = apply_filters('eshb_modify_checked_in_out_dates', $checked_in_out_dates, $accomodation_id);

        $all_dates = [
            'checked_in_out_dates'  => $checked_in_out_dates,
            'booked_dates'  => $booked_dates,
            'holiday_dates' => $finally_holidays,
            'allowed_check_in_day' => '',
            'single_day' => false,
            'min_nights' => $min_max_nights_settings['required_min_nights'],
            'max_nights' => $min_max_nights_settings['required_max_nights'],
            // Season rules the calendar evaluates itself, so a stay that starts inside a
            // season is validated on the very first pick instead of one step late.
            'session_min_nights_rules' => $session_min_nights_rules,
            'total_room_bookings_per_date' => $total_room_bookings_per_date,
        ];

        $all_dates = apply_filters('eshb_get_disabled_dates_in_search', $all_dates, $accomodation_id, $accomodation_metas);


        if(isset($_POST['action']) && $_POST['action'] == 'eshb_get_disabled_dates_by_accomodation_id'){
            wp_send_json_success($all_dates);
            wp_die();
        }else{
            return $all_dates;
        }
        
    }
    
    public function eshb_generate_dates_from_date_ranges($start_date, $end_date){
        // $start_date = '2025, 05, 16'; // Format: YYYY, MM, DD
        // $end_date = '2025, 05, 26';

        $start = DateTime::createFromFormat('Y, m, d', $start_date);
        $end = DateTime::createFromFormat('Y, m, d', $end_date);
        $end->modify('+1 day'); // Include the end date in the range

        $interval = new DateInterval('P1D'); // 1-day interval
        $daterange = new DatePeriod($start, $interval, $end);

        $dates_array = [];

        foreach ($daterange as $date) {
            $dates_array[] = $date->format('Y, m, d');
        }
        return $dates_array;
    }

    /**
     * Booking Blocked Dates ("holidays") from the settings, normalised once.
     *
     * The calendar expands these per accommodation on every request; the search
     * loops over every accommodation, so it reads the option once and keeps the
     * rules as [ accomodation_ids, dates ] pairs instead of re-expanding the
     * same ranges for each room.
     *
     * An empty accomodation_ids list means the rule closes every accommodation,
     * which is the same reading used in eshb_get_disabled_dates_by_accomodation_id().
     */
    private function eshb_get_blocked_date_rules() {

        $eshb_settings = get_option('eshb_settings', []);
        $holidays      = isset($eshb_settings['holidays']) ? $eshb_settings['holidays'] : [];
        $rules         = [];

        if (empty($holidays) || !is_array($holidays)) {
            return $rules;
        }

        foreach ($holidays as $holiday) {
            $accommodation_ids = $holiday['accomodation-ids'] ?? [];
            $holiday_date      = $holiday['holiday-date'] ?? null;

            if (empty($holiday_date)) {
                continue;
            }

            $dates = [];

            if (is_array($holiday_date) && isset($holiday_date['from'], $holiday_date['to'])) {
                $start = DateTime::createFromFormat('Y-m-d', $holiday_date['from']);
                $end   = DateTime::createFromFormat('Y-m-d', $holiday_date['to']);

                if ($start && $end) {
                    $end->modify('+1 day'); // Include end date
                    $dateRange = new DatePeriod($start, new DateInterval('P1D'), $end);

                    foreach ($dateRange as $date) {
                        $dates[] = $date->format('Y-m-d');
                    }
                }
            } else {
                $dates[] = $holiday_date;
            }

            if (empty($dates)) {
                continue;
            }

            $rules[] = [
                'accomodation_ids' => is_array($accommodation_ids) ? $accommodation_ids : [],
                'dates'            => $dates,
            ];
        }

        return $rules;
    }

    /**
     * Every date of a stay, checkout day included.
     *
     * Inclusive on purpose: get_holiday_dates_in_date_ranges() — the check that
     * rejects the booking on submit — builds its range the same way, so search
     * hides exactly what booking would refuse.
     */
    private function eshb_get_dates_in_range($start_date, $end_date) {

        $dates = [];

        if (empty($start_date) || empty($end_date) || !strtotime($start_date) || !strtotime($end_date)) {
            return $dates;
        }

        $current_date = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);

        while ($current_date <= $end_date_obj) {
            $dates[] = $current_date->format('Y-m-d');
            $current_date->modify('+1 day');
        }

        return $dates;
    }

    /**
     * Does any Booking Blocked Date rule close this accommodation inside the stay?
     */
    private function eshb_is_blocked_in_date_range($accomodation_id, $range_dates, $rules) {

        if (empty($range_dates)) {
            return false;
        }

        foreach ($rules as $rule) {

            // Empty list = the rule applies to all accommodations.
            if (!empty($rule['accomodation_ids']) && !in_array($accomodation_id, $rule['accomodation_ids'])) {
                continue;
            }

            if (array_intersect($range_dates, $rule['dates'])) {
                return true;
            }
        }

        return false;
    }

    public function eshb_get_available_accomodation_ids($start_date, $end_date, $adult_quantity = 0, $children_quantity = 0, $rooms_quantity = 0) {
        $available_accomodation_ids = [];
    
        if ($start_date && $end_date) {
    
            // Step 1: Get all accommodation IDs
            $accomodations_args = array(
                'post_type'      => 'eshb_accomodation',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            );

            // Narrows what the search may offer at all — a tax_query here keeps a
            // whole category (packages, day trips…) out of the results. Filtered
            // before the loops below so excluded rooms cost no booking lookups.
            $accomodations_args = apply_filters('eshb_search_accomodations_query_args', $accomodations_args, $start_date, $end_date);

            $accomodations = new WP_Query($accomodations_args);
    
            if ($accomodations->have_posts()) {
                $accomodation_ids = $accomodations->posts;
    
                // Step 2: Get all bookings with overlapping dates
                $bookings_args = array(
                    'post_type'      => 'eshb_booking',
                    'posts_per_page' => -1,
                    'post_status'    => ['publish', 'deposit-payment', 'pending', 'processing', 'on-hold', 'completed'],
                    'fields' => 'ids',
                );
    
                $bookings = new WP_Query($bookings_args);
                $accommodation_bookings = array(); // [accommodation_id => total_booked_rooms]
                $booked_accomodation_ids = array();
    
                if ($bookings->have_posts()) {
                    while ($bookings->have_posts()) {
                        $bookings->the_post();
    
                        $meta_values = get_post_meta(get_the_ID(), 'eshb_booking_metaboxes', true);
                        if (!is_array($meta_values)) continue;
    
                        $booking_accomodation_id = intval($meta_values['booking_accomodation_id']);
                        $booking_start_date = $meta_values['booking_start_date'];
                        $booking_end_date = $meta_values['booking_end_date'];
                        $booking_room_quantity = isset($meta_values['room_quantity']) ? intval($meta_values['room_quantity']) : 1;
                        $booking_status = isset($meta_values['booking_status']) ? $meta_values['booking_status'] : '';
                        $valid_statuses = array('deposit-payment', 'pending', 'processing', 'on-hold', 'completed');
    
                        // Only count if booking status is valid and dates overlap
                        if (
                            in_array($booking_status, $valid_statuses) &&
                            $start_date <= $booking_end_date &&
                            $end_date >= $booking_start_date
                        ) {
                            if (!isset($accommodation_bookings[$booking_accomodation_id])) {
                                $accommodation_bookings[$booking_accomodation_id] = 0;
                            }
    
                            $accommodation_bookings[$booking_accomodation_id] += $booking_room_quantity;
                        }
                    }
                    wp_reset_postdata();
    
                    // Step 3: Mark accommodations as fully booked
                    foreach ($accommodation_bookings as $accommodation_id => $booked_rooms) {
                        $accomodation_metaboxes = get_post_meta($accommodation_id, 'eshb_accomodation_metaboxes', true);
                        $total_rooms = !empty($accomodation_metaboxes['total_rooms']) ? intval($accomodation_metaboxes['total_rooms']) : 1;
    
                        if ($booked_rooms >= $total_rooms) {
                            $booked_accomodation_ids[] = $accommodation_id;
                        }
                    }
    
                    // Step 4: Exclude fully booked accommodations
                    $available_accomodation_ids = array_diff($accomodation_ids, $booked_accomodation_ids);
                } else {
                    // No bookings, all accommodations are available
                    $available_accomodation_ids = $accomodation_ids;
                }
    
                // Step 5: Filter by requested capacity
                if (!empty($available_accomodation_ids)) {
                    foreach ($available_accomodation_ids as $key => $available_accomodation_id) {
                        $accomodation_metaboxes = get_post_meta($available_accomodation_id, 'eshb_accomodation_metaboxes', true);
    
                        $total_capacity = !empty($accomodation_metaboxes['total_capacity']) ? intval($accomodation_metaboxes['total_capacity']) : 1;
                        $adult_capacity = !empty($accomodation_metaboxes['adult_capacity']) ? intval($accomodation_metaboxes['adult_capacity']) : 0;
                        $children_capacity = !empty($accomodation_metaboxes['children_capacity']) ? intval($accomodation_metaboxes['children_capacity']) : 0;
                        $total_rooms = !empty($accomodation_metaboxes['total_rooms']) ? intval($accomodation_metaboxes['total_rooms']) : 1;
                        if(empty($adult_capacity)) {
                            $adult_capacity = $total_capacity;
                        }
                        if(empty($children_capacity)) {
                            $children_capacity = $total_capacity;
                        }
                        
                        if (
                            $adult_quantity > $adult_capacity ||
                            $children_quantity > $children_capacity ||
                            $rooms_quantity > $total_rooms
                        ) {
                            unset($available_accomodation_ids[$key]);
                        }
                    }
                }

                // Step 6: Drop accommodations closed by a Booking Blocked Date rule.
                // Bookings and capacity alone are not enough — the calendar greys these
                // dates out and the booking check refuses them, so listing the room as
                // available here advertises a stay that can never be completed.
                if (!empty($available_accomodation_ids)) {

                    $blocked_rules = $this->eshb_get_blocked_date_rules();

                    if (!empty($blocked_rules)) {

                        $range_dates = $this->eshb_get_dates_in_range($start_date, $end_date);

                        foreach ($available_accomodation_ids as $key => $available_accomodation_id) {
                            if ($this->eshb_is_blocked_in_date_range($available_accomodation_id, $range_dates, $blocked_rules)) {
                                unset($available_accomodation_ids[$key]);
                            }
                        }
                    }
                }
            }
        }

        // The last word on what the search offers, once bookings, capacity and
        // blocked dates have had theirs. For rules that cannot be written as query
        // args — use eshb_search_accomodations_query_args when they can, it is cheaper.
        return apply_filters('eshb_available_accomodation_ids', $available_accomodation_ids, $start_date, $end_date, $adult_quantity, $children_quantity, $rooms_quantity);
    }
    
    public function eshb_register_page_template($templates) {
        $templates['easy-hotel-search.php'] = 'Easy Hotel Search';
        $templates['easy-hotel-search-result.php'] = 'Easy Hotel Search Result';
        return $templates;
    }

    public function eshb_load_template($template) {
        
        global $post;
    
        if (is_page() && $post->post_type == 'page') {
            $template_meta = get_post_meta($post->ID, '_wp_page_template', true);
            

            if ($template_meta == 'easy-hotel-search.php') {
    
                $plugin_template = ESHB_PL_PATH . '/public/templates/easy-hotel-search.php';
                $theme_template = get_stylesheet_directory() . '/easy-hotel-booking/easy-hotel-search.php';
                $child_theme_template = get_template_directory() . '/easy-hotel-booking/easy-hotel-search.php';
    
                if (file_exists($theme_template)) {
                    $template = $theme_template;
                } elseif (file_exists($child_theme_template)) {
                    $template = $child_theme_template;
                } else {
                    $template = $plugin_template;
                }
    
            }

            if ($template_meta == 'easy-hotel-search-result.php') {
    
                $plugin_template = ESHB_PL_PATH . '/public/templates/easy-hotel-search-result.php';
                $theme_template = get_stylesheet_directory() . '/easy-hotel-booking/easy-hotel-search-result.php';
                $child_theme_template = get_template_directory() . '/easy-hotel-booking/easy-hotel-search-result.php';
    
                if (file_exists($theme_template)) {
                    $template = $theme_template;
                } elseif (file_exists($child_theme_template)) {
                    $template = $child_theme_template;
                } else {
                    $template = $plugin_template;
                }
    
            }

        
        }
        return $template;
    }

}


ESHB_Search::instance();