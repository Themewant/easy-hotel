<?php
use SureCart\Models\Order;
use SureCart\Models\Checkout;
use SureCart\Models\Customer;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

class ESHB_Booking_Calendar {
    public $plugin_settings = array();
    public $calendar = array();
    public $accomodations = array();
    public $bookings = array();
    public $calendar_settings = array(
        'start_date' => '',
        'end_date' => '',
        'booking_period' => 'custom',
        'current_accomodation' => 'all',
        'current_status' => ['processing', 'on-hold', 'completed', 'pending', 'blocked'],
        'allowed_status' => ['processing', 'on-hold', 'completed', 'pending', 'blocked'],
    );

    public function __construct() {

        $this->plugin_settings = get_option( 'eshb_settings', [] );

        // Setup calendar settings
        $today_date = gmdate("Y-m-d");
        $default_end_date = gmdate('Y-m-d', strtotime('+14 days'));

        // use defaults
        $start_date = $today_date;
        $end_date = $default_end_date;
        $current_status = $this->calendar_settings['current_status'];
        $current_accomodation = $this->calendar_settings['current_accomodation'];
        $booking_period = $this->calendar_settings['booking_period'];

        // Verify nonce for form processing
        if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), ESHB_Helper::generate_secure_nonce_action('booking_calendar_nonce'))) {
            // Sanitize and validate GET parameters
            $start_date = !empty($_POST['start-date']) ? sanitize_text_field(wp_unslash($_POST['start-date'])) : $today_date;
            $end_date = !empty($_POST['end-date']) ? sanitize_text_field(wp_unslash($_POST['end-date'])) : $default_end_date;
            $current_status = !empty($_POST['booking-status']) ? [sanitize_text_field(wp_unslash($_POST['booking-status']))] : $this->calendar_settings['current_status'];
            $current_accomodation = !empty($_POST['accomodation-id']) ? sanitize_text_field(wp_unslash($_POST['accomodation-id'])) : $this->calendar_settings['current_accomodation'];
            $booking_period = !empty($_POST['booking-period']) ? sanitize_text_field(wp_unslash($_POST['booking-period'])) : $this->calendar_settings['booking_period'];
        }

        if ($booking_period != 'custom') {

            // Current date
            $start_date = gmdate('Y-m-01');
            $end_date = gmdate('Y-m-t');

            
            if (!empty($_POST['action-prev-period']) || !empty($_POST['action-next-period'])){
                $start_date = !empty($_POST['start-date']) ? sanitize_text_field(wp_unslash($_POST['start-date'])) : $start_date;
                $end_date = !empty($_POST['end-date']) ? sanitize_text_field(wp_unslash($_POST['end-date'])) : $end_date;
            }

            if ($booking_period == 'month') {
            
                if (!empty($_POST['action-prev-period'])) {
                    $date = $start_date;
                    $start_date = gmdate('Y-m-01', strtotime('-1 month', strtotime($date)));
                    $end_date = gmdate('Y-m-t', strtotime('-1 month', strtotime($date)));
                }
        
                if (!empty($_POST['action-next-period'])) {
                    $date = $start_date;
                    $start_date = gmdate('Y-m-01', strtotime('+1 month', strtotime($date)));
                    $end_date = gmdate('Y-m-t', strtotime('+1 month', strtotime($date)));
                }
        
            } elseif ($booking_period == 'quarter') {
                // Default: current quarter
                $current_month = gmdate('n');
                $quarter_start_month = floor(($current_month - 1) / 3) * 3 + 1;
                $start_date = gmdate('Y-' . str_pad($quarter_start_month, 2, '0', STR_PAD_LEFT) . '-01');
                $end_date = gmdate('Y-m-t', strtotime("+2 months", strtotime($start_date))); // end of third month


                if (!empty($_POST['action-prev-period'])) {
                    $start_date = !empty($_POST['start-date']) ? sanitize_text_field(wp_unslash($_POST['start-date'])) : $start_date;
                    $end_date = !empty($_POST['end-date']) ? sanitize_text_field(wp_unslash($_POST['end-date'])) : $end_date;
                    $start_date = gmdate('Y-m-01', strtotime('-3 months', strtotime($start_date)));
                    $end_date = gmdate('Y-m-t', strtotime('+2 months', strtotime($start_date)));
                }

                if (!empty($_POST['action-next-period'])) {
                    $start_date = !empty($_POST['start-date']) ? sanitize_text_field(wp_unslash($_POST['start-date'])) : $start_date;
                    $end_date = !empty($_POST['end-date']) ? sanitize_text_field(wp_unslash($_POST['end-date'])) : $end_date;
                    $start_date = gmdate('Y-m-01', strtotime('+3 months', strtotime($start_date)));
                    $end_date = gmdate('Y-m-t', strtotime('+2 months', strtotime($start_date)));
                }
        
            } elseif ($booking_period == 'year') {
                // Default to full current year
                $start_date = gmdate('Y-01-01');
                $end_date = gmdate('Y-12-31');
        
                if (!empty($_POST['action-prev-period'])) {
                    $start_date = !empty($_POST['start-date']) ? sanitize_text_field(wp_unslash($_POST['start-date'])) : $start_date;
                    $end_date = !empty($_POST['end-date']) ? sanitize_text_field(wp_unslash($_POST['end-date'])) : $end_date;
                    $start_date = gmdate('Y-01-01', strtotime('-1 year', strtotime($start_date)));
                    $end_date = gmdate('Y-12-31', strtotime('-1 year', strtotime($end_date)));
                }
        
                if (!empty($_POST['action-next-period'])) {
                    $start_date = !empty($_POST['start-date']) ? sanitize_text_field(wp_unslash($_POST['start-date'])) : $start_date;
                    $end_date = !empty($_POST['end-date']) ? sanitize_text_field(wp_unslash($_POST['end-date'])) : $end_date;
                    $start_date = gmdate('Y-01-01', strtotime('+1 year', strtotime($start_date)));
                    $end_date = gmdate('Y-12-31', strtotime('+1 year', strtotime($end_date)));
                }
            }
        }
        
        
        $this->calendar_settings = array_merge(
            $this->calendar_settings,
            [
            'start' => $start_date, 
            'end' => $end_date,
            'current_status' => $current_status,
            'current_accomodation' => $current_accomodation,
            ]
        );

        $this->calendar = [
            'name' => 'Booking Calendar',
            'settings' => $this->calendar_settings,
            'dates' => $this->get_dates_from_range($this->calendar_settings['start'], $this->calendar_settings['end']),
            'accomodations' => $this->accomodations,
        ];

        // Set all accomodations & bookings
        $this->set_all_accomodations();
        $this->set_all_bookings();
    }
 
    public function get_dates_from_range($start_date, $end_date) {
        $dates = array();
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $end = $end->modify('+1 day'); // Include end date

        while ($start < $end) {
            $dates[] = $start->format('Y-m-d');
            $start->modify('+1 day');
        }

        return $dates;
    }

    public function set_all_accomodations () {
        // Fetch all accomodations
        $accomodation_posts = get_posts([
            'post_type' => 'eshb_accomodation',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'DESC',
        ]);

        foreach ($accomodation_posts as $post) {
            $accomodation_id = $post->ID;
            $metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
            $total_rooms = isset($metaboxes['total_rooms']) ? floatval($metaboxes['total_rooms']) : 0;

            $this->accomodations[] = [
                'id' => $accomodation_id,
                'name' => $post->post_title,
                'type' => $post->post_type,
                'status' => $post->post_status,
                'total_rooms' => $total_rooms,
            ];
        }
    }

    public function set_all_bookings (){

        $plugin_settings = $this->plugin_settings;
        $booked_type = $plugin_settings['booking-type'];
        $bookings = [];

        $booking_posts = get_posts([
            'post_type' => 'eshb_booking',
            'posts_per_page' => -1,
            'post_status' => ['publish', 'deposit-payment', 'pending', 'processing', 'on-hold', 'completed'],
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        foreach ($booking_posts as $post) {
            $booking_id = $post->ID;
            $metaboxes = get_post_meta($booking_id, 'eshb_booking_metaboxes', true);

            $accomodation_id = floatval($metaboxes['booking_accomodation_id'] ?? 0);
            $order_id = $metaboxes['order_id'] ?? '';
            $check_in = $metaboxes['booking_start_date'] ?? '';
            $check_out = $metaboxes['booking_end_date'] ?? '';
            $booked_dates = $this->get_dates_from_range($check_in, $check_out);

            if (count($booked_dates) > 1) {
               array_pop($booked_dates);
            }

            $booking_status = $metaboxes['booking_status'] ?? '';
            $customer = '';
            

            if (class_exists('WooCommerce') && wc_get_order($order_id)) {
                $order = wc_get_order($order_id);
                $customer = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
            }
            
            $customer = apply_filters( 'eshb_customer_data_in_calendar',  $customer, $order_id, $booked_type);
            
            
            if (in_array($booking_status, ['processing', 'on-hold'])) {
                $booking_status = 'blocked';
            }

            if(in_array($booking_status, $this->calendar_settings['current_status'])){
                $bookings[] = [
                    'id' => $booking_id,
                    'check_in' => $check_in,
                    'check_out' => $check_out,
                    'status' => $booking_status,
                    'accomodation_id' => $accomodation_id,
                    'customer' => $customer,
                    'source_type' => 'internal',
                ];
            }

            

        }

        $bookings = apply_filters('eshb_modify_calendar_booking_data', $bookings, $this->calendar_settings);

        $this->bookings = array_merge($this->bookings, $bookings);
    }

    

    private function get_bookings_for_accomodation($accomodation_id) {
        return array_filter($this->bookings, function($booking) use ($accomodation_id) {
            return intval($booking['accomodation_id']) === intval($accomodation_id);
        });
    }

    public function render_booking_cells($dates, $accomodations, $accomodation_limit = 0) {

        if ( ! isset( $_POST['nonce'] ) ||
            ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), ESHB_Helper::generate_secure_nonce_action('booking_calendar_nonce') ) ) {
            $filtered_accomodation_id = '';
        }

        if ($accomodation_limit > 0) {
            $accomodations = array_slice($accomodations, 0, $accomodation_limit);
        }
    
        $filtered_accomodation_id = !empty($_REQUEST['accomodation-id']) ? sanitize_text_field(wp_unslash($_REQUEST['accomodation-id'])): '';
        if(!empty($filtered_accomodation_id)){
            $accomodations = array_filter($accomodations, function($accomodation) use ($filtered_accomodation_id) {
                return $accomodation['id'] == $filtered_accomodation_id;
            });
        }

        $html = '';
        $dates_map = array_flip($dates);
        $total_dates = count($dates);
    
        // Group bookings by accomodation id for faster access
        $bookings_by_accomodation = [];
        foreach ($this->bookings as $booking) {
            if (!isset($booking['accomodation_id'])) continue;
            $acc_id = $booking['accomodation_id'];
            $bookings_by_accomodation[$acc_id][] = $booking;
        }
    
        foreach ($accomodations as $accomodation) {
            $accomodation_id = $accomodation['id'];

            if ( function_exists( 'pll_get_post' )) {
                $default_lang = pll_default_language();
                $main_post_id = pll_get_post( $accomodation_id, $default_lang ) ? pll_get_post( $accomodation_id, $default_lang ) : $accomodation_id ;
                $accomodation_id = $main_post_id;
            }elseif ( function_exists( 'apply_filters' ) && function_exists( 'icl_object_id' ) ) {
                 $accomodation_id = apply_filters( 'wpml_original_element_id', NULL, $accomodation_id, 'post_post' );
            }

            $accomodation_name = $accomodation['name'];
            
            $bookings = isset($bookings_by_accomodation[$accomodation_id]) ? $bookings_by_accomodation[$accomodation_id] : [];
    
            // Group bookings by date range
            $grouped_bookings = [];
            foreach ($bookings as $booking) {
                $key = $booking['check_in'] . '|' . $booking['check_out'];
                $grouped_bookings[$key][] = $booking;
            }
    
            $html .= '<tr>';
            $html .= '<th class="left-heading"><div class="room-info">' . esc_html($accomodation_name) . '</div></th>';
            $date_pointer = 0;
    
            while ($date_pointer < $total_dates) {
                $current_date = $dates[$date_pointer];
                $matched = false;
    
                foreach ($grouped_bookings as $range_key => $group) {
                    [$check_in, $check_out] = explode('|', $range_key);
    
                    if ($check_in <= $current_date && $check_out > $current_date) {
                        $colspan = 0;
                        $tmp_date = $current_date;
                        $max_days = $total_dates; // do not exceed 60 days range
                        $loop_count = 0;
                    
                        while (
                            $tmp_date < $check_out &&
                            isset($dates_map[$tmp_date]) &&
                            $loop_count < $max_days
                        ) {
                            $colspan++;
                            $tmp_date = gmdate('Y-m-d', strtotime($tmp_date . ' +1 day'));
                            $loop_count++;
                        }
                    
                        if ($colspan <= 0) {
                            $colspan = 1;
                        }
                    
                        $html .= '<td class="booking-info-col" colspan="' . $colspan  . '">';
                    
                        foreach ($group as $booking) {
                            
                            $source_type = $booking['source_type'];
                            
                            if($source_type == 'internal'){
                                $status_class = 'status-' . esc_attr($booking['status']);
                                $html .= '<a href="#" class="booking-info ' . esc_attr($status_class) . '" data-source-type="internal" data-booking-id="' . esc_attr($booking['id']) . '" data-accomodation-id="' . esc_attr($accomodation_id) . '">';
                                $html .= '<span class="booking-id">#' . esc_html($booking['id']) . '</span>';
                                $html .= '<span class="booking-customer">' . esc_html($booking['customer']) . '</span>';
                                $html .= '</a>';
                            }

                            $html = apply_filters( 'eshb_calendar_booking_info_btn', $html, $booking, $accomodation_id );
                            
                        }
                    
                        $html .= '</td>';
                    
                        $date_pointer += $colspan;
                        $matched = true;
                        break;
                    }
                    
                }
    
                if (!$matched) {
                    $html .= '<td class="status-available"><div class="eshb-booking-info"></div></td>';
                    $date_pointer++;
                }
            }
    
            $html .= '</tr>';
        }
    
        return $html;
    }
    
    public function render_accomodation_cells($accomodations, $accomodation_limit = 0) {


        if ( ! isset( $_POST['nonce'] ) ||
            ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), ESHB_Helper::generate_secure_nonce_action('booking_calendar_nonce') ) ) {
            $filtered_accomodation_id = '';
        }else{
            $filtered_accomodation_id = !empty(sanitize_text_field(wp_unslash($_REQUEST['accomodation-id']))) ? sanitize_text_field(wp_unslash($_REQUEST['accomodation-id'])): '';
        }

        if ($accomodation_limit > 0) {
            $accomodations = array_slice($accomodations, 0, $accomodation_limit);
        }
        
        if(!empty($filtered_accomodation_id)){
            $accomodations = array_filter($accomodations, function($accomodation) use ($filtered_accomodation_id) {
                return $accomodation['id'] == $filtered_accomodation_id;
            });
        }
        
        $html = '';
        foreach ($accomodations as $accomodation) {
            $accomodation_name = $accomodation['name'];
            $html .= '<tr>';
            $html .= '<td><div class="room-info">' . esc_html($accomodation_name) . '</div></td>';
            $html .= '</tr>';
        }
    
        return $html;
    }

    public function render_dates_table ($dates, $accomodations, $accomodation_limit = 0) {
        if ($accomodation_limit > 0) {
            $accomodations = array_slice($accomodations, 0, $accomodation_limit);
        }

        $html = '<div class="eshb-accomodations-dates-table-wrapper table-wrapper">';
        $html .= '<table class="eshb-accomodations-dates-table sticky-table">';

        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th class="left-heading"><div class="table-heading">'. esc_html__('Accomodations', 'easy-hotel') .'</div></th>';
        foreach ($dates as $date) {
            $wp_date_format = get_option('date_format');
            $formatted_date = DateTime::createFromFormat('Y-m-d', $date)->format($wp_date_format);
            $html .= '<th>';
            $html .= '<div class="date">'; 
            $html .= esc_html(  DateTime::createFromFormat('Y-m-d', $date)->format('j') ) . '<br />';
			$html .= esc_html(  DateTime::createFromFormat('Y-m-d', $date)->format('M'), DateTime::createFromFormat('Y-m-d', $date)->format('F'). ' abbreviation') . '<br />'; 
			$html .= '<small class="eshb-subscript">' .  DateTime::createFromFormat('Y-m-d', $date)->format('Y').'</small><br />';
			$html .=  '<small class="eshb-subscript">' .  DateTime::createFromFormat('Y-m-d', $date)->format('D') . '</small>';
            $html .= '</div>';
            $html .= '</th>';
        }
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $html .= $this->render_booking_cells($dates, $accomodations, ' ');

        $html .= '</tbody>';
        $html .= '<tfoot>';
        $html .= '<tr>';
        $html .= '<th class="left-heading"><div class="table-heading">'. esc_html__('Accomodations', 'easy-hotel') .'</div></th>';
        foreach ($dates as $date) {
            $wp_date_format = get_option('date_format');
            $formatted_date = DateTime::createFromFormat('Y-m-d', $date)->format($wp_date_format);
            $html .= '<th>';
            $html .= '<div class="date">'; 
            $html .= esc_html(  DateTime::createFromFormat('Y-m-d', $date)->format('j') ) . '<br />';
			$html .= esc_html(  DateTime::createFromFormat('Y-m-d', $date)->format('M'), DateTime::createFromFormat('Y-m-d', $date)->format('F'). ' abbreviation' ) . '<br />'; 
			$html .= '<small class="eshb-subscript">' .  DateTime::createFromFormat('Y-m-d', $date)->format('Y').'</small><br />';
			$html .=  '<small class="eshb-subscript">' .  DateTime::createFromFormat('Y-m-d', $date)->format('D') . '</small>';
            $html .= '</div>';
            $html .= '</th>';
        }
        $html .= '</tr>';
        $html .= '</tfoot>';
        $html .= '</table>';
        $html .= '</div>';
 
        return $html;
    }

    public function render_booking_info_calendar () {
        $calendar = $this->calendar;
        $html = '<div class="eshb-booking-info-calendar">';
        $html .= '<h1>' . esc_html($calendar['name']) . '</h1>';
        $html .= $this->render_calendar_filter();
        $html .= '<div class="eshb-booking-info-calendar-tables">';
        $html .= $this->render_dates_table($calendar['dates'], $this->accomodations);
        $html .= '</div>';
        $html .= $this->render_booking_info_modal();
        $html .= '</div>';
        return $html;
    }

    public function render_calendar_filter () {

        if ( ! isset( $_POST['nonce'] ) ||
            ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), ESHB_Helper::generate_secure_nonce_action('booking_calendar_nonce') ) ) {
            $current_booking_period = 'custom';
        }else{
            $current_booking_period = !empty($_POST['booking-period']) ? sanitize_text_field(wp_unslash($_POST['booking-period'])) : 'custom';
        }

        // filter by accomodation, booking status, period, date range, etc.
        $calendar_settings = $this->calendar_settings;
        $current_status = count($calendar_settings['current_status']) > 1 ? '' : $calendar_settings['current_status'][0];
        $current_accomodation = $calendar_settings['current_accomodation'];
        $nonce_field = ESHB_Helper::eshb_nonce_field('booking_calendar_nonce', 'nonce', false);
        $action_url = add_query_arg(  array(
            'post_type' => 'eshb_accomodation',
            'page' => 'eshb_bookings_calendar',
        ), admin_url( 'edit.php' ));

        
        $period_select_field_dispaly = $current_booking_period != 'custom' ? 'none' : 'block'; 

        $html = '<div class="eshb-calendar-filter">';
            $html .= '<form action="'.$action_url.'" method="post" class="eshb-calendar-filter-form">';
                $html .= $nonce_field;
                $html .= '<div class="eshb-calendar-filter-options">';
                $html .= '<select id="eshb-calendar-filter-accomodation" name="accomodation-id">';
                $html .= '<option value="">' . esc_html__('All Accomodations', 'easy-hotel') . '</option>';
                foreach ($this->accomodations as $accomodation) {
                    $html .= '<option value="' . esc_attr($accomodation['id']) . '" '. selected( $accomodation['id'], $current_accomodation, false ).'>' . esc_html($accomodation['name']) . '</option>';
                }
                $html .= '</select>';
                $html .= '<select id="eshb-calendar-filter-status" name="booking-status">';
                $html .= '<option '. selected( '', $current_status, false ).' value="">' . esc_html__('All Statuses', 'easy-hotel') . '</option>';
                $html .= '<option '. selected( 'pending', $current_status, false ).' value="pending">' . esc_html__('Pending', 'easy-hotel') . '</option>';
                $html .= '<option '. selected( 'completed', $current_status, false ).' value="completed">' . esc_html__('Confirmed', 'easy-hotel') . '</option>';
                $html .= '<option '. selected( 'blocked', $current_status, false ).' value="blocked">' . esc_html__('Blocked', 'easy-hotel') . '</option>';
                $html .= '</select>';
                $html .= '<div id="eshb-calendar-filter-period-wrapper">';
                    $html .= '<span>' . esc_html__('Period ', 'easy-hotel') . '</span> ';
                    if($current_booking_period != 'custom') {
                        $html .= '<input type="submit" name="action-prev-period" class="button period-navigator" value="Prev" />';
                    }
                    $html .= '<select id="eshb-calendar-filter-period" name="booking-period">';
                    $html .= '<option '. selected( 'custom', $current_booking_period, false ) .' value="custom">' . esc_html__('Custom', 'easy-hotel') . '</option>';
                    $html .= '<option '. selected( 'month', $current_booking_period, false ) .' value="month">' . esc_html__('Month', 'easy-hotel') . '</option>';
                    $html .= '<option '. selected( 'quarter', $current_booking_period, false ) .' value="quarter">' . esc_html__('Quarter', 'easy-hotel') . '</option>';
                    $html .= '<option '. selected( 'year', $current_booking_period, false ) .' value="year">' . esc_html__('Year', 'easy-hotel') . '</option>';
                    $html .= '</select>';
                    if($current_booking_period != 'custom') {
                        $html .= '<input type="submit" name="action-next-period" class="button period-navigator" value="Next" />';
                    }
                    $html .= '<div id="eshb-calendar-filter-date-range" style="display:'. $period_select_field_dispaly .'">';
                    $html .= '<input type="date" name="start-date" class="eshb-datepicker" value="'. $calendar_settings['start'] .'"/> to ';
                    $html .= '<input type="date" name="end-date" class="eshb-datepicker" value="'. $calendar_settings['end'] .'"/>';
                    $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';

                $html .= '<div class="eshb-calendar-filter-actions">';
                $html .= '<button type="submit" id="eshb-calendar-filter-apply" class="button button-primary">' . esc_html__('Apply Filter', 'easy-hotel') . '</button>';
                $html .= '</div>';

            $html .= '</form>';
            $html .= '<div class="eshb-calendar-legends">';
                $html .= '<legend class="legend-item complete" title="' . esc_html__('Confirmed', 'easy-hotel') . '"><span>' . esc_html__('Confirmed', 'easy-hotel') . '</span></legend>';

                $html = apply_filters( 'eshb_calendar_legend', $html);

                $html .= '<legend class="legend-item pending" title="' . esc_html__('Pending', 'easy-hotel') . '"><span>' . esc_html__('Pending', 'easy-hotel') . '</span></legend>';
                $html .= '<legend class="legend-item blocked" title="' . esc_html__('Blocked', 'easy-hotel') . '"><span>' . esc_html__('Blocked', 'easy-hotel') . '</span></legend>';
            $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    public function render_booking_info_modal(){
        ?>
        <div id="eshb-booking-info-modal" class="modal">
            <div class="booking-info-modal-bg-overlay"></div>
            <div class="booking-info-modal-main">
                <div class="booking-info-modal-header">
                    <h2 class="booking-info-modal-bookig-details-title"><?php echo esc_html__( 'Booking Details', 'easy-hotel' ) ?></h2>
                    <button class="booking-info-modal-close">
                        <span class="screen-reader-text"><?php echo esc_html__( 'Close popup panel', 'easy-hotel' ) ?></span>
                    </button>
                </div>
                <div class="booking-info-modal-content">
                    <!-- contents will come by ajax -->
                </div>
            </div>
            
        </div>
        <?php
    }
}