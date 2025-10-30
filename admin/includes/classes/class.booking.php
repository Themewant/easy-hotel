<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use SureCart\Support\Currency;
use SureCart\Models\Product;
use SureCart\Models\Price;
use SureCart\Models\LineItem;
use SureCart\Models\ProductMedia;
use SureCart\Models\Order;
use SureCart\Models\Checkout;
use SureCart\Models\Customer;
use SureCart\Models\Coupon;

class ESHB_Booking {

    private static $_instance = null;
	
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

    public function __construct() {

		add_filter('litespeed_cacheable', [$this, 'disable_eshb_cache_from_cacheplugin'], 10, 1);

		if(!is_admin()){
			add_action( 'woocommerce_before_calculate_totals', [ $this, 'apply_custom_price_to_cart_item' ], 20, 1 );
			add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'save_custom_meta_to_order' ], 10, 4 );
			add_filter( 'woocommerce_get_item_data', [ $this, 'display_custom_meta_in_cart_and_order' ], 10, 2 );
			
			add_action(	'woocommerce_thankyou', [$this, 'create_woocommerce_booking_on_checkout'], 10, 1 );
			add_action(	'woocommerce_thankyou', [$this, 'capture_payment_after_checkout'], 20, 1 );
			add_filter(	'woocommerce_hidden_order_itemmeta', [$this, 'hide_meta_from_display'] );
			add_filter(	'woocommerce_order_item_get_formatted_meta_data', [$this, 'unset_specific_order_item_meta_data'], 10, 2 );
		}
		
		add_action(	'woocommerce_order_status_changed', [$this, 'update_booking_status_on_woocommerce_order_status_change'], 10, 4 );
		add_action(	'save_post_eshb_booking', [$this, 'update_woocommerce_order_status_on_booking_status_change'], 20, 3 );
		add_action(	'save_post_eshb_booking', [$this, 'send_booking_email_notification_for_booking_status'], 20, 3 );
		add_filter('woocommerce_payment_complete_order_status', [$this, 'filter_status_based_on_booking_meta'], 10, 2);
		
		add_action( "wp_ajax_nopriv_get_extra_services_charge", [$this, 'get_extra_services_charge'] );
        add_action( "wp_ajax_get_extra_services_charge", [$this, 'get_extra_services_charge'] );
        add_action( "wp_ajax_nopriv_get_booking_prices", [$this, 'get_booking_prices'] );
        add_action( "wp_ajax_get_booking_prices", [$this, 'get_booking_prices'] );
		add_action( "wp_ajax_nopriv_add_to_cart_reservation", [$this, 'add_to_cart_reservation'] );
		add_action( "wp_ajax_add_to_cart_reservation", [$this, 'add_to_cart_reservation'] );
		add_action( "wp_ajax_nopriv_send_reservation_request", [$this, 'send_reservation_request'] );
		add_action( "wp_ajax_send_reservation_request", [$this, 'send_reservation_request'] );
		add_action( 'wp_ajax_nopriv_get_accomodation_available_capacity_counts', [ $this, 'get_accomodation_available_capacity_counts' ] );
        add_action( 'wp_ajax_get_accomodation_available_capacity_counts', [ $this, 'get_accomodation_available_capacity_counts' ] );
		add_action( 'wp_ajax_nopriv_get_available_rooms_counts_data', [ $this, 'get_available_rooms_counts_data' ] );
        add_action( 'wp_ajax_get_available_rooms_counts_data', [ $this, 'get_available_rooms_counts_data' ] );
		
	}

	function disable_eshb_cache_from_cacheplugin($cacheable){
	     // Disable LSCache for accommodation single pages
        if( is_singular('eshb_accomodation') ){
            return false; // don't cache
        }
        return $cacheable;
	}
	
	public function update_product_price_by_id($product_id, $new_price) {
		// Update the regular price
		update_post_meta($product_id, '_regular_price', $new_price);
		
		// Update the price (current price)
		update_post_meta($product_id, '_price', $new_price);
		
		// Optional: if the product is on sale, update the sale price to match the new price
		//update_post_meta($product_id, '_sale_price', $new_price);
	
		// Clear WooCommerce product cache
		wc_delete_product_transients($product_id);
	}

	public function get_booked_dates($accomodation_id, $start_date = '', $end_date = '') {

		$single_day_booking = false;
		if($start_date == $end_date){
			$single_day_booking = true;
		}

		$booked_dates = [];
		$total_room_bookings_per_date = []; // Track room bookings per date
		$eshb_settings = get_option('eshb_settings', []);
	
		// Get total rooms allowed for this accommodation
		$accomodation_metas = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
		$allowed_total_rooms = isset($accomodation_metas['total_rooms']) ? intval($accomodation_metas['total_rooms']) : 0;
	
		// Find all bookings for this accommodation
		$bookings_args = [
			'post_type'      => 'eshb_booking',
			'posts_per_page' => -1,
			'post_status'    => ['publish', 'deposit-payment', 'pending', 'processing', 'on-hold', 'completed'],
			'fields' => 'ids',
		];
	
		$bookings = new WP_Query($bookings_args);
	
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

					$booked_accomodation_ids = [];
                    $booking_accomodation_id = ESHB_Helper::get_main_post_id_for_translated($booking_accomodation_id);
	
					if ($booking_accomodation_id == $accomodation_id) {
						// Convert dates to DateTime objects
						$booking_start_date = new DateTime($booking_start_date);
						$booking_end_date = new DateTime($booking_end_date);

						// if($booking_end_date == $booking_end_date) {
						// 	$booking_end_date->modify('+1 day');
						// }

						while ($booking_start_date <= $booking_end_date) {
							
							$formatted_date = $booking_start_date->format('Y-m-d');

							if($meta_values['booking_end_date'] != $formatted_date){
								$booked_id = get_the_ID();
								$booking_metas = get_post_meta($booked_id, 'eshb_booking_metaboxes', true);
								$booked_room_quantity = isset($booking_metas['room_quantity']) ? intval($booking_metas['room_quantity']) : 0;
								
								
								// Track total booked rooms per date
								if (!isset($total_room_bookings_per_date[$formatted_date])) {
									$total_room_bookings_per_date[$formatted_date] = 0;
								}
								$total_room_bookings_per_date[$formatted_date] = $booked_room_quantity;
							}
							// Move to next day
							$booking_start_date->modify('+1 day');
						}
						
					}
				}
			}
		}

		if(!$single_day_booking){
			unset($total_room_bookings_per_date[$end_date]);
		}

		// Determine which dates should be disabled (fully booked)
		foreach ($total_room_bookings_per_date as $date => $total_booked_rooms) {

			 // skip this booking if slots hours available
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
				$date_obj = new DateTime($date);
				$booked_dates[] = $date_obj->format('Y, m, d');
			}
		}

	
		// Remove duplicate dates
		$booked_dates = array_unique($booked_dates);
	
		// Apply filter hook to allow external modifications
		$booked_dates = apply_filters('eshb_modify_booked_dates', $booked_dates, $accomodation_id);

	
		return $booked_dates;
	}

	public function get_holiday_dates($accomodation_id, $start_date = '', $end_date = '') {

		$eshb_settings = get_option('eshb_settings', []);

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

		return $finally_holidays;
	}

	public function get_holiday_dates_in_date_ranges($accomodation_id, $start_date, $end_date){

		$overlap = [];
		if(!empty($accomodation_id)){
			$holiday_dates = $this->get_holiday_dates($accomodation_id, $start_date, $end_date);
			$overlap = [];
			// Generate all dates in the range
			$range_dates = [];
			$current_date = new DateTime($start_date);
			$end_date_obj = new DateTime($end_date);
		
			while ($current_date <= $end_date_obj) {
				$range_dates[] = $current_date->format('Y-m-d');
				$current_date->modify('+1 day');
			}
		
			// Check for overlap
			$overlap = array_intersect($range_dates, $holiday_dates);
		}
		return $overlap;
	}

	public function get_actual_booked_dates_in_date_ranges($accomodation_id, $start_date, $end_date){

		$booked_dates = $this->get_booked_dates($accomodation_id, $start_date, $end_date); // retun arary like ['2025, 01, 27', '2025, 01, 28', '2025, 01, 13']

		// Format booked dates to 'Y-m-d' for comparison
		$booked_dates = array_map(function($date) {
			return DateTime::createFromFormat('Y, m, d', $date)->format('Y-m-d');
		}, $booked_dates);
	
		// Generate all dates in the range
		$range_dates = [];
		$current_date = new DateTime($start_date);
		$end_date_obj = new DateTime($end_date);
	
		while ($current_date <= $end_date_obj) {
			$range_dates[] = $current_date->format('Y-m-d');
			$current_date->modify('+1 day');
		}

		$single_day_booking = false;
		if($start_date == $end_date){
			$single_day_booking = true;
		}

		if(!$single_day_booking){
			array_pop($range_dates); // Remove the last date if it's a range booking
		}
	
		// Check for overlap
		$overlap = array_intersect($range_dates, $booked_dates);
		
		return $overlap;

	}

	public function save_booking_request($booking_args = array(), $customer_args = array()) {
		$defaults = array(
			'booking_accomodation_id'   => '',
			'booking_start_date'        => '',
			'booking_end_date'          => '',
			'room_quantity'     => 1,
			'extra_bed_quantity'=> 0,
			'adult_quantity'    => 1,
			'children_quantity' => 0,
			'details_html'      => '',
			'extra_services'    => array(),
			'extra_services_html' => '',
			'booking_status'	=> 'pending'
		);
		
		// Merge with default values
		$booking_args = wp_parse_args($booking_args, $defaults);
	
		$accomodation_id = $booking_args['booking_accomodation_id'];

		if(!empty($accomodation_id)){

			$start_date = $booking_args['booking_start_date'];
			$end_date = $booking_args['booking_end_date'];

			// Insert the post
			$post_id = wp_insert_post(array(
				'post_type'   => 'eshb_booking_request',
				'post_title'  => 'Booking request for ' . $start_date . ' - ' . $end_date,
				'post_status' => 'publish',
				'meta_input'  => array(
					'eshb_booking_request_metaboxes' => $booking_args,
					'eshb_booking_request_customer_metaboxes' => $customer_args,
				)
			));

			// Check if the post was inserted successfully
			if (!is_wp_error($post_id)) {
				// Update the post title to include the post ID

				$accomodation_title = get_the_title($accomodation_id);

				wp_update_post(array(
					'ID'         => $post_id,
					'post_title' => 'Booking request #' . $post_id . ' for: ' . $accomodation_title,
					'post_status' => 'publish' // Update status to 'publish'
				));
			}

		}
		
	}
	
	public function send_reservation_request(){

		// Verify nonce for security
		if (!isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {

			$error = array(
				'code' => 'invalid_nonce', 
				'message' => __('invalid_nonce', 'easy-hotel')
			);
			
			wp_send_json_error([
				'error' => $error,
			]);

			die();
		}

		
		if(isset($_POST['accomodationId']) && !empty($_POST['accomodationId'])){
			
			$hotel_core = new ESHB_Core();
			$customer = !empty($_POST['customerInfo']) ? map_deep( sanitize_text_field( wp_unslash($_POST['customerInfo'])), 'sanitize_text_field' ) : [];
			$accomodation_id = isset( $_POST['accomodationId'] ) ? (int) sanitize_text_field( wp_unslash($_POST['accomodationId']) ) : '';
			$today_date = esc_html(gmdate('Y-m-d')); // Get today's date
			$date = new DateTime($today_date); // Create a DateTime object from today's date
			$date->modify('+1 day'); // Add one day
			$tomorrow = $date->format('Y-m-d'); // Get the new date in 'Y-m-d' format
			$start_date = isset( $_POST['startDate'] ) ? sanitize_text_field( wp_unslash($_POST['startDate']) ) : $today_date;
			$end_date = isset( $_POST['endDate'] ) ? sanitize_text_field( wp_unslash($_POST['endDate']) ) : $tomorrow;
			$start_time = isset( $_POST['startTime'] ) ? sanitize_text_field( wp_unslash($_POST['startTime']) ) : '';
			$end_time = isset( $_POST['endTime'] ) ? sanitize_text_field( wp_unslash($_POST['endTime']) ) : '';
			$start_day_name = $start_date ? strtolower(gmdate('l', strtotime($start_date))) : '';
			$accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
			$eshb_settings = get_option( 'eshb_settings', [] );
			$string_booking_success_msg = isset($eshb_settings['string_booking_request_success_msg']) && !empty($eshb_settings['string_booking_request_success_msg']) ? $eshb_settings['string_booking_request_success_msg'] : 'Booking request has been sent successfully.';
			$string_booking_failed_msg = isset($eshb_settings['string_booking_request_failed_msg']) && !empty($eshb_settings['string_booking_request_failed_msg']) ? $eshb_settings['string_booking_request_failed_msg'] : 'Ops! Booking request can\'t be sent.';
			$string_already_booked_msg = isset($eshb_settings['string_already_booked_msg']) && !empty($eshb_settings['string_already_booked_msg']) ? $eshb_settings['string_already_booked_msg'] : 'Ops! The date range is already booked:';
			$room_visibility = in_array('rooms', $eshb_settings['booking-form-fields']) ? true : false;
			$adult_visibility = in_array('adults', $eshb_settings['booking-form-fields']) ? true : false;
			$childrens_visibility = in_array('childrens', $eshb_settings['booking-form-fields']) ? true : false;
			$extra_beds_visibility = in_array('extra_beds', $eshb_settings['booking-form-fields']) ? true : false;

			$eshb_week_settings = [
				'string_check_in_day_error_msg' => 'Only allowed check in day is',
				'eshb_booking_allowed_check_in_day' => 'all',
			];
			$eshb_week_settings = apply_filters( 'eshb_week_settings', $eshb_week_settings );
			$allowed_check_in_day = apply_filters( 'eshb_booking_allowed_check_in_day', 'all', $accomodation_id, $accomodation_metaboxes );
			$string_check_in_day_error_msg = !empty($eshb_week_settings['string_check_in_day_error_msg']) ? $eshb_week_settings['string_check_in_day_error_msg'] : 'Only allowed check in day is : ';

			
			// Min Max Booking Configurations
			$min_max_settings = [
				'required_min_nights' => '',
				'required_max_nights' => '',
				'is_global_source_for_min_max' => true,
			];
			$min_max_settings = apply_filters( 'eshb_min_max_settings', $min_max_settings, $accomodation_id, $accomodation_metaboxes );
			$required_min_nights = isset($min_max_settings['required_min_nights']) ? $min_max_settings['required_min_nights'] : '';
			$required_max_nights = isset($min_max_settings['required_max_nights']) ? $min_max_settings['required_max_nights'] : '';
			$string_required_minimum_nights_msg = esc_html__( 'Ops! This Reservation has been failed. Requried Minumum', 'easy-hotel' );
			$string_required_maximum_nights_msg = esc_html__( 'Ops! This Reservation has been failed. Requried Maximum', 'easy-hotel' );

			
			// get booked dates in the range
			$actual_booked_dates = $this->get_actual_booked_dates_in_date_ranges($accomodation_id, $start_date, $end_date);

			if(!empty($actual_booked_dates)){

				$booked_dates_str = implode( ', ', array_map( 'esc_html', $actual_booked_dates ) );

				$error_message = sprintf(
					/* translators: 1: already booked message text, 2: list of booked dates */
					esc_html__( '%1$s %2$s', 'easy-hotel' ),
					esc_html( $string_already_booked_msg ),
					$booked_dates_str
				);

				$error = array(
					'code'    => 'already_booked',
					'message' => $error_message,
				);

				wp_send_json_error([
					'error' => $error,
					'booked_dates' => implode(', ', $actual_booked_dates),
				]);

				wp_die();

			}

			// check rooms capacity
			if(isset($_POST['roomQuantity']) && !empty($_POST['roomQuantity'])){
				$roomQuantity = isset( $_POST['roomQuantity'] ) ? (int) sanitize_text_field( wp_unslash($_POST['roomQuantity'] ) ) : 1;
				
				$availableRoom = $this->get_available_room_count_by_date_range($accomodation_id, $start_date, $end_date);

				if($roomQuantity > $availableRoom){
					$error = array(
						'code'    => 'room_capacity_not_enough',
						'message' => sprintf(
							/* translators: %s: number of available rooms */
							esc_html__( 'Selected room is not available. Available room: %s', 'easy-hotel' ),
							esc_html( $availableRoom )
						),
					);

					wp_send_json_error([
						'error' => $error,
					]);
				}
				
			}


			// Validate check-in day
			if($allowed_check_in_day != 'all' && $start_day_name != '' && !empty($allowed_check_in_day) && strpos($allowed_check_in_day, $start_day_name) === false){
				$error = array(
					'code'    => 'check_in_day_error',
					'message' => sprintf(
						/* translators: %s: allowed check-in day */
						esc_html__( '%1$s %1$s', 'easy-hotel' ),
						esc_html( $string_check_in_day_error_msg ),
						esc_html( $allowed_check_in_day )
					),
				);

				wp_send_json_error([
					'error' => $error,
				]);
			}

			$start = new DateTime($start_date);
			$end = new DateTime($end_date);
			$interval = $start->diff($end);
			$days_count = $interval->days;
			$room_quantity = isset( $_POST['roomQuantity'] ) ? (int) sanitize_text_field( wp_unslash($_POST['roomQuantity'] ) ) : 1;
			$extra_bed_quantity = isset( $_POST['extraBedQuantity'] ) ? (int) sanitize_text_field( wp_unslash($_POST['extraBedQuantity'] ) ) : 0;
			$adult_quantity = isset( $_POST['adultQuantity'] ) ? (int) sanitize_text_field( wp_unslash($_POST['adultQuantity'] ) ) : 1;
			$children_quantity = isset( $_POST['childrenQuantity'] ) ? (int) sanitize_text_field( wp_unslash($_POST['childrenQuantity'] ) ) : 0;
			$total_guest_quantity = $adult_quantity + $children_quantity;

			$selected_services = [];
			if(isset($_POST['selectedServices']) && !empty($_POST['selectedServices'])){

				$selected_services = sanitize_text_field(wp_unslash($_POST['selectedServices']));
				$selected_services = json_decode($selected_services, true);
				
			}


			if(!empty($allowed_check_in_day) && $allowed_check_in_day != 'all'){
				$days_count = 1;
			}
			
			// validate minimum nights
			if(!empty($required_min_nights) && $days_count < $required_min_nights){
				$error = array(
					'code'    => 'required_min_nights',
					'message' => sprintf(
						/* translators: 1: minimum nights message text, 2: required minimum nights */
						esc_html__( '%1$s %2$s nights!', 'easy-hotel' ),
						esc_html( $string_required_minimum_nights_msg ),
						esc_html( $required_min_nights )
					),
				);

				wp_send_json_error([
					'error' => $error,
				]);
			}
			if(!empty($required_max_nights) && $days_count > $required_max_nights){
				$error = array(
					'code'    => 'required_max_nights',
					'message' => sprintf(
						/* translators: 1: maximum nights message text, 2: required maximum nights */
						esc_html__( '%1$s %2$s nights allowed!', 'easy-hotel' ),
						esc_html( $string_required_maximum_nights_msg ),
						esc_html( $required_max_nights )
					),
				);

				wp_send_json_error([
					'error' => $error,
				]);
			}
			

			if (get_post_type($accomodation_id) === 'eshb_accomodation') {

				$details = [];

				if ($room_visibility && $room_quantity > 0) {
					$details[] = 'Room: ' . $room_quantity;
				}
				if ($extra_beds_visibility && $extra_bed_quantity > 0) {
					$details[] = 'Extra Bed: ' . $extra_bed_quantity;
				}
				if ($adult_visibility && $adult_quantity > 0) {
					$details[] = 'Adult: ' . $adult_quantity;
				}
				if ($childrens_visibility && $children_quantity > 0) {
					$details[] = 'Children: ' . $children_quantity;
				}

				$details_html = implode(', ', $details);

				$extra_service_titles = [];
				$extra_services_html = '';
				
				if(!empty($selected_services) && is_array($selected_services) && count($selected_services) > 0 ){
				
					foreach ($selected_services as $service) {
		
						$service_id = $service['id'];

						$service_quantity = $service['quantity'];

						if(!empty($service_quantity)){
							$eshb_service_metaboxes = get_post_meta($service_id, 'eshb_service_metaboxes', true);

							$charge_type = $eshb_service_metaboxes['service_charge_type'];

							$service_title = get_the_title($service_id) . ' For ' . $service_quantity . ' ' .  ucfirst(strtolower($charge_type));

							if(!$room_visibility && $charge_type == 'room'){
								$service_title = get_the_title($service_id);
							}
						}else{
							$service_title = get_the_title($service_id);
						}

						array_push($extra_service_titles, $service_title);
		
					}
					$extra_services_html = implode(", ",$extra_service_titles);
				}
				

				$pricing = $this->calculate_booking_pricing($accomodation_id, $start_date, $end_date, $room_quantity, $extra_bed_quantity, $adult_quantity, $children_quantity, $selected_services, true, $start_time, $end_time);

				$base_price = $pricing['basePrice'];
				$extra_services_charge = $pricing['extraServicesPrice'];
				$extra_bed_price= $pricing['extraBedPrice'];
				$subtotal_price = $pricing['subtotalPrice'];
				$total_price = $pricing['totalPrice'];

				$dates = date_i18n( get_option('date_format'), strtotime( $start_date ) ) .' - '. date_i18n( get_option('date_format'), strtotime( $end_date ) );
				if($start_date == $end_date){
					$dates = date_i18n( get_option('date_format'), strtotime( $start_date ) );
				}

				
				$times = '';
				if(!empty($start_time) && !empty($end_time)) {
					// Get WP formatted time (according to Settings > General > Time Format)
					$formated_start_timestamp = strtotime( $start_time );
					$formated_end_timestamp = strtotime( $end_time );
					$wp_time_format = get_option( 'time_format' ); // WP settings
					$formatted_start_time = wp_date( $wp_time_format, $formated_start_timestamp );
					$formatted_end_time = wp_date( $wp_time_format, $formated_end_timestamp );
					$times = $formatted_start_time . ' - ' . $formatted_end_time;
				}


				$accomodation_title = get_the_title($accomodation_id);

				$customer_name = $customer['name'];
				$customer_email = $customer['email'];
				$customer_phone = $customer['phone'];
				$customer_message = $customer['message'];

				$admin_email = get_option('admin_email');
				$recipent_email = $eshb_settings['recipent_email'];
				$recipent_email = empty($recipent_email) ? $admin_email : $recipent_email;
				$from_name = wp_parse_url(get_site_url(), PHP_URL_HOST);
				
				$subject = __('Booking Request!', 'easy-hotel');

				$message = '';
				$message .= '<div>';
				$message .= '<h2>Great news!</h2><p>You have a new booking request.</p>';
				$message .= '<h3>Customer  Details</h3>';
				$message .= '<table style="text-align:left;">';
				$message .= '<tr><th>Name: ' . $customer_name . '</th></tr>';
				$message .= '<tr><th>Email: ' . $customer_email . '</th></tr>';
				$message .= '<tr><th>Phone: ' . $customer_phone . '</th></tr>';
				$message .= '</table>';
				$message .= '<h3>Booking  Details</h3>';
				$message .= '<table style="text-align:left;">';
				$message .= '<tr><th>Accomodation: ' . $accomodation_title . '</th></tr>';
				$message .= '<tr><th>Date: ' . date_i18n( get_option('date_format'), strtotime( $start_date ) ) .' - '. date_i18n( get_option('date_format'), strtotime( $end_date ) ) . '</th></tr>';
				
				if(!empty($times)) {
					$message .= '<tr><th>Times: ' . $times . '</th></tr>';
				}

				$message .= '<tr><th>Details: ' . $details_html . '</th></tr>';

				if(!empty($extra_services_html)){
					$message .= '<tr><th>Exra Services: ' . $extra_services_html . '</th></tr>';
				}

				$message .= '</table>';
				$message .= '<h5>Message:</h5><p>'.$customer_message.'</p>';
				$message .= '</div>';

				// Prepare the data array to store in post meta
				$form_data = [
					'booking_status' => 'processing',
					'booking_accomodation_id' => $accomodation_id,
					'total_price' => $total_price,
					'subtotal_price' => $subtotal_price,
					'base_price' => $base_price,
					'extra_service_price' => $extra_services_charge,
					'extra_bed_price' => $extra_bed_price,
					'booking_start_date' => $start_date,
					'booking_end_date' => $end_date,
					'booking_start_time' => $start_time,
					'booking_end_time' => $end_time,
					'dates' => $dates,
					'room_quantity' => $room_quantity,
					'extra_bed_quantity' => $extra_bed_quantity,
					'adult_quantity' => $adult_quantity,
					'children_quantity' => $children_quantity,
					'extra_services' => $selected_services,
					'details_html' => $details_html,
					'extra_services_html' => $extra_services_html,
					'customer' => $customer
				];

				if(!empty($start_time)) {
					$form_data['start_time'] = $start_time;
				}

				if(!empty($end_time)) {
					$form_data['end_time'] = $end_time;
				}

				if(!empty($times)) {
					$form_data['times'] = $times;
				}

				// Insert the post
				$post_id = wp_insert_post(array(
					'post_type'   => 'eshb_booking',
					'post_title'  => 'Booking for ' . $start_date . ' - ' . $end_date,
					'post_status' => 'publish',
					'meta_input'  => array(
						'eshb_booking_metaboxes' => $form_data
					)
				));
		
				// Check if the post was inserted successfully
				
				if (!is_wp_error($post_id)) {

					// Update the post title to include the post ID
					wp_update_post(array(
						'ID'         => $post_id,
						'post_title' => 'Booking #' . $post_id . ' for: ' . $accomodation_title,
						'post_status' => 'publish' // Update status to 'publish'
					));
					
					// Update Available Rooms Count For This Accomodation
					$accomodation_metaboxes = get_post_meta( $accomodation_id, 'eshb_accomodation_metaboxes', true );
					$total_rooms = !empty($accomodation_metaboxes['total_rooms']) ? floatval($accomodation_metaboxes['total_rooms']) : 1;
					$current_available_rooms = !empty($accomodation_metaboxes['available_rooms']) ? floatval($accomodation_metaboxes['available_rooms']) : 0;

					if(!empty($current_available_rooms)){
						$available_rooms = $current_available_rooms - floatval($room_quantity);
					}else{
						$available_rooms = $total_rooms - floatval($room_quantity);
					}
					
					$accomodation_metaboxes['available_rooms'] = $available_rooms;
					update_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', $accomodation_metaboxes);
					
				}
				
				$save_booking = $this->save_booking_request($form_data, $customer);
				$send_email = $hotel_core->eshb_send_html_email($recipent_email, $subject, $message, $from_name, $customer_email);
				
				if ( $send_email ) {

					// Return success response
					wp_send_json_success([
						'message' => esc_html( $string_booking_success_msg ),
						'admin_email' => esc_html( $admin_email ),
					]);

				} else {
					$error = array(
						'code'    => 'email_failed',
						'message' => esc_html( $string_booking_failed_msg ),
					);

					wp_send_json_error([
						'error' => $error,
					]);
				}

			}else{
				$error = array(
					'code'    => 'accomodation_post_type_not_found',
					'message' => esc_html( $string_booking_failed_msg ),
				);

				wp_send_json_error([
					'error' => $error,
				]);
			}
			wp_die();
		}
			
	}

	public function apply_custom_price_to_cart_item($cart) {

		// Early return for admin requests (except AJAX)
		if (is_admin() && !defined('DOING_AJAX')) {
			return;
		}

		// Use the passed cart object if valid, otherwise fallback
		if (!$cart || !is_object($cart) || !method_exists($cart, 'get_cart')) {
			$cart = WC()->cart;
			if (!$cart || !is_object($cart) || !method_exists($cart, 'get_cart')) {
				return;
			}
		}
		
		$redirect_added = false;

		foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
			if (empty($cart_item['total_price']) || $cart_item['total_price'] <= 0) {
				continue;
			}
			if (isset($cart_item['data']) && is_object($cart_item['data'])) {
				$cart_item['data']->set_price($cart_item['total_price']);
			}
		}
	}

	private function add_currency_redirect() {
		static $redirect_added = false;
		if ($redirect_added) {
			return;
		}
		add_action('wp_footer', function() {
			echo '<script>if(typeof window.eshb_currency_redirect==="undefined"){window.eshb_currency_redirect=true;window.location.href="' . esc_url(get_permalink(get_the_ID())) . '";}</script>';
		});
		$redirect_added = true;
	}

	public function save_custom_meta_to_order($item, $cart_item_key, $values, $order) {

		if (isset($values['accomodation_id'])) {
			$item->add_meta_data('Accomodation ID', $values['accomodation_id'], true);
		} 

		if (isset($values['total_price'])) {
			$item->add_meta_data('Total Price', $values['total_price'], true);
		}

		if (isset($values['subtotal_price'])) {
			$item->add_meta_data('Subtotal Price', $values['subtotal_price'], true);
		}

		if (isset($values['base_price'])) {
			$item->add_meta_data('Base Price', $values['base_price'], true);
		}

		if (isset($values['extra_services_charge'])) {
			$item->add_meta_data('Extra Services Charge', $values['extra_services_charge'], true);
		}

		if (isset($values['extra_bed_price'])) {
			$item->add_meta_data('Extra Bed Price', $values['extra_bed_price'], true);
		}

		if(class_exists('ESHB_CURRENCY_WOO_FUNCTIONS')){
			$currency_functions = new ESHB_CURRENCY_WOO_FUNCTIONS();

			if (isset($values['total_price'])) {
				$item->add_meta_data('Total Price', $currency_functions->apply_custom_currency_rate($values['total_price']), true);
			}

			if (isset($values['subtotal_price'])) {
				$item->add_meta_data('Subtotal Price', $currency_functions->apply_custom_currency_rate($values['subtotal_price']), true);
			}
	
			if (isset($values['base_price'])) {
				$item->add_meta_data('Base Price', $currency_functions->apply_custom_currency_rate($values['base_price']), true);
			}
	
			if (isset($values['extra_services_charge'])) {
				$item->add_meta_data('Extra Services Charge', $currency_functions->apply_custom_currency_rate($values['extra_services_charge']), true);
			}
	
			if (isset($values['extra_bed_price'])) {
				$item->add_meta_data('Extra Bed Price', $currency_functions->apply_custom_currency_rate($values['extra_bed_price']), true);
			}
		}

		if (isset($values['room_quantity'])) {
			$item->add_meta_data('Rooms', $values['room_quantity'], true);
		} 

		if (isset($values['extra_bed_quantity'])) {
			$item->add_meta_data('Extra Bed', $values['extra_bed_quantity'], true);
		} 

		if (isset($values['adult_quantity'])) {
			$item->add_meta_data('Adults', $values['adult_quantity'], true);
		} 

		if (isset($values['children_quantity'])) {
			$item->add_meta_data('Children', $values['children_quantity'], true);
		}

		if (isset($values['start_date'])) {
			$item->add_meta_data('Start Date', $values['start_date'], true);
		} 

		if (isset($values['end_date'])) {
			$item->add_meta_data('End Date', $values['end_date'], true);
		} 
	
		if (isset($values['dates'])) {
			$item->add_meta_data('Date', $values['dates'], true);
		} 

		if (isset($values['start_time'])) {
			$item->add_meta_data('Start Time', $values['start_time'], true);
		} 

		if (isset($values['end_time'])) {
			$item->add_meta_data('End Time', $values['end_time'], true);
		}

		if (!empty($values['times'])) {
			$item->add_meta_data('Time', $values['times'], true);
		} 

		if (isset($values['details_html'])) {
			$item->add_meta_data('Details', $values['details_html'], true);
		} 

		if (isset($values['extra_services'])) {
			$item->add_meta_data('Extra Services IDs', $values['extra_services'], true);
		} 

		if (isset($values['extra_services_html'])) {
			$item->add_meta_data('Extra Services', $values['extra_services_html'], true);
		} 

	}
	
	public function capture_payment_after_checkout($order_id){
		// capture payment
		ESHB_Helper::eshb_capture_payment($order_id);
	}
	public function create_woocommerce_booking_on_checkout($order_id){

		if (!$order_id) {
			// error_log("Error: Missing order ID.");
			return;
		}
	
		// Get the order object
		$order = wc_get_order($order_id);
	
		if (!$order) {
			// error_log("Error: Invalid order ID: $order_id");
			return;
		}
		
		$eshb_settings = get_option('eshb_settings', []);
		
		// Get the order object
		$order = wc_get_order($order_id);
		$order_status = $order->get_status();
		$booking_status = $order_status;

		if ( $order && $order->is_paid() && $order->get_status() !== 'completed' && isset($eshb_settings['booking-auto-approval']) && $eshb_settings['booking-auto-approval'] == true) {
			$order->update_status('completed', 'Payment successful, status updated via code.');
			$booking_status = 'completed';
		}
		
		$first_name = $order->get_billing_first_name();
		$last_name = $order->get_billing_last_name();
		$customer_name = trim($first_name . ' ' . $last_name); // Concatenate first and last name
		$customer_email = $order->get_billing_email(); // Get customer email
		$customer_phone = $order->get_billing_phone(); // Get customer phone number

		// Check if the booking post has already been created
		if (get_post_meta($order_id, '_booking_post_created', true)) {
			return; // Exit if the booking post has already been created
		}
		
		// Loop through order items
		foreach ($order->get_items() as $item_id => $item) {

			// Retrieve meta data saved in the order item
			$accomodation_id = $item->get_meta('Accomodation ID');
			$accomodation_title = get_the_title( $accomodation_id );
			$room_quantity = $item->get_meta('Rooms');
			$extra_bed_quantity = $item->get_meta('Extra Bed');
			$adult_quantity = $item->get_meta('Adults');
			$children_quantity = $item->get_meta('Children');
			$start_date = $item->get_meta('Start Date');
			$end_date = $item->get_meta('End Date');
			$dates = $item->get_meta('Date');
			$times = $item->get_meta('Time');
			$start_time = $item->get_meta('Start Time');
			$end_time = $item->get_meta('End Time');
			$details_html = $item->get_meta('Details');
			$extra_services = $item->get_meta('Extra Services IDs');
			$extra_services_html = $item->get_meta('Extra Services');
			$base_price = $item->get_meta('Base Price');
			$extra_services_charge = $item->get_meta('Extra Service Price');
			$extra_bed_price = $item->get_meta('Extra Bed Price');
			$total_without_discount = $item->get_meta('Subtotal Price');
			$total_price = $item->get_meta('Total Price');
	
			// Prepare the data array to store in post meta
			$cart_item_data = [
				'booking_status' => $booking_status,
				'order_id' => $order_id,
				'booking_accomodation_id' => $accomodation_id,
				'subtotal_price' => $order->get_subtotal(),
				'total_price' => $total_price,
				'total_paid' => 0,
				'base_price' => $base_price,
				'extra_service_price' => $extra_services_charge,
				'extra_bed_price' => $extra_bed_price,
				'booking_start_date' => $start_date,
				'booking_end_date' => $end_date,
				'booking_start_time' => !empty($start_time) ? $start_time : '10:00',
				'booking_end_time' => !empty($end_time) ? $end_time : '22:00',
				'dates' => $dates,
				'room_quantity' => $room_quantity,
				'extra_bed_quantity' => $extra_bed_quantity,
				'adult_quantity' => $adult_quantity,
				'children_quantity' => $children_quantity,
				'extra_services' => $extra_services,
				'start_time' => !empty($start_time) ? $start_time : '10:00',
				'end_time' => !empty($end_time) ? $end_time : '22:00',
				'times' => $times,
				'details_html' => $details_html,
				'extra_services_html' => $extra_services_html,
			];

			// filter for $cart_item_data;
			$cart_item_data = apply_filters( 'on_booking_cart_item_data', $cart_item_data );
	
			// Insert the booking
			ESHB_Helper::eshb_insert_booking($order_id, $booking_status, $cart_item_data);
			
		}

	}

	public function display_custom_meta_in_cart_and_order($item_data, $cart_item) {

		if (!empty($cart_item['discount']) && is_cart()) {
			$discount = $cart_item['discount'];
			$currency_symbol = $cart_item['currency_symbol'];

			$item_data[] = array(
				'key' => __('Save', 'easy-hotel'),
				'value' => wc_price($discount)
			);
		}

		if (isset($cart_item['dates'])) {

			$dates = $cart_item['dates'];

			$item_data[] = array(
				'key' => __('Date', 'easy-hotel'),
				'value' => esc_html( $dates )
			);
		}

		if (!empty($cart_item['times'])) {

			$times = $cart_item['times'];

			$item_data[] = array(
				'key' => __('Time', 'easy-hotel'),
				'value' => esc_html( $times )
			);
		}


		if (!empty($cart_item['night'])) {

			$night = $cart_item['night'];
			$night_label = esc_html__('Night', 'easy-hotel');
			if ($night > 1) {
				$night_label = esc_html__('Nights', 'easy-hotel');
			}

			$item_data[] = array(
				'key' => $night_label,
				'value' => esc_html( $night )
			);
		}

		

		if (isset($cart_item['details_html'])) {

			$details_html = $cart_item['details_html'];

			$item_data[] = array(
				'key' => __('Details', 'easy-hotel'),
				'value' => esc_html( $details_html )
			);
		}

		$extra_services_ids = isset($cart_item['extra_services']) ? $cart_item['extra_services'] : '';

		if(!empty($extra_services_ids)){
			if (isset($cart_item['extra_services_html'])) {

				$extra_services_html = $cart_item['extra_services_html'];
	
				$item_data[] = array(
					'key' => __('Extra Services', 'easy-hotel'),
					'value' => esc_html( $extra_services_html )
				);
			}
		}

		// make unique item data
		$item_data = array_unique($item_data, SORT_REGULAR);

		return $item_data;
	
	}

	public function hide_meta_from_display($hidden_meta_keys) {
		// Add meta keys to hide
		$keys_to_hide = array(
			'Accomodation ID',
			'Subtotal Price',
			'Total Price',
			'Base Price',
			'Extra Services Charge',
			'Extra Bed Price',
			'Rooms',
			'Extra Bed',
			'Adults',
			'Children',
			'Start Date',
			'End Date',
			'Start Time',
			'End Time',
			'Save'
		);
	
		return array_merge($hidden_meta_keys, $keys_to_hide);
	}
	
	public function unset_specific_order_item_meta_data($formatted_meta, $item){
	
		$keys_to_hide = array(
			'Accomodation ID',
			'Subtotal Price',
			'Total Price',
			'Base Price',
			'Extra Services Charge',
			'Extra Bed Price',
			'Rooms',
			'Extra Bed',
			'Adults',
			'Children',
			'Start Date',
			'End Date',
			'Start Time',
			'End Time',
			'Save'
		);
	
		foreach($formatted_meta as $key => $meta){
			if(in_array($meta->key, $keys_to_hide)) {
				unset($formatted_meta[$key]);
			}
		}
		return $formatted_meta;
	}

	public function calculate_booking_pricing($accomodation_id, $start_date, $end_date, $room_quantity, $extra_bed_quantity, $adult_quantity, $children_quantity, $selected_services, $currency_converter = false, $start_time = '', $end_time = '') {
		if (empty($accomodation_id)) return;
	
		$accomodation_id = (int) $accomodation_id;
		$total_guest_quantity = (int) $adult_quantity + (int) $children_quantity;
		$start = new DateTime($start_date);
		$end = new DateTime($end_date);
		$days_count = max(1, $start->diff($end)->days);
		$hours_count = ESHB_Helper::eshb_calculate_time_diff($start_date, $end_date, $start_time, $end_time);
		$today = new DateTime('now', new DateTimeZone('GMT'));
		$today_date = $today->format('Y-m-d');
		$tomorrow = $today->modify('+1 day')->format('Y-m-d');
		$hotel_core = new ESHB_Core();
		$metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
		$allowed_check_in_day = apply_filters( 'eshb_booking_allowed_check_in_day', 'all', $accomodation_id, $metaboxes );
		$single_day_price = 0;
	
		// Handle week-based or same-day bookings
		if (!empty($allowed_check_in_day) && $allowed_check_in_day !== 'all') {
			$days_count = 1;
		}
		
		$pricing_periodicity = false;

		if ($start_date === $end_date) {
			$days_count = 1;
			$pricing_periodicity = apply_filters( 'eshb_pricing_periodicity', false, $accomodation_id, $metaboxes );
			$single_day_price = apply_filters( 'eshb_single_day_price', 0, $accomodation_id);
		}


		// Base price
		$base_price = 0;
		$base_price = $hotel_core->get_eshb_day_wise_price($start_date, $end_date, $accomodation_id, true, $days_count, $adult_quantity, $children_quantity);
		$base_price = !empty($single_day_price) ? $single_day_price : $base_price;
		$has_session_price = $hotel_core->has_upcoming_or_current_session_price($accomodation_id, $start_date, $end_date, $days_count, $adult_quantity, $children_quantity);

		if($has_session_price){
			$session_price = $hotel_core->get_eshb_price_by_session($accomodation_id, $start_date, $end_date, $days_count, $adult_quantity, $children_quantity);
		}

        $base_price = !empty($session_price) && $session_price > 0 ? $session_price : $base_price;
		$regular_base_price = $hotel_core->get_eshb_day_wise_price($start_date, $end_date, $accomodation_id, true, $days_count, $adult_quantity, $children_quantity);
	
		// Pricing logic
		$bed_price = (int) ($metaboxes['extra_bed_price'] ?? 0);
		$pricing_type = $metaboxes['pricing_type'] ?? 'room_wise';
	
		
		$pricing_base = $pricing_type === 'room_wise'
			? $base_price * $room_quantity
			: $base_price * $total_guest_quantity;

		
		$regular_pricing_base = $pricing_type === 'room_wise'
		? $regular_base_price * $room_quantity
		: $regular_base_price * $total_guest_quantity;

	
		$extra_bed_price = $extra_bed_quantity > 0
			? $extra_bed_quantity * $bed_price * $days_count
			: 0;
	
		

		$subtotal_price = $pricing_base + $extra_bed_price;
		$regular_subtotal_price = $regular_pricing_base + $extra_bed_price;

		if($pricing_periodicity && !empty($hours_count)){
			$subtotal_price = $pricing_periodicity == 'per_hour' ? $subtotal_price * (int) $hours_count : $subtotal_price;
			$regular_subtotal_price = $pricing_periodicity == 'per_hour' ? $regular_subtotal_price * (int) $hours_count : $regular_subtotal_price;
		}

	
		// Extra services
		$extra_services_charge = 0;
	
		if (!empty($selected_services) && is_array($selected_services)) {
			$extra_services_charge = array_reduce($selected_services, function ($carry, $service) use ($days_count, $room_quantity, $total_guest_quantity) {
				$service_id = $service['id'];
				$service_quantity = $service['quantity'];
	
				$meta = get_post_meta($service_id, 'eshb_service_metaboxes', true);
				$price = $meta['service_price'] ?? 0;
				$periodicity = $meta['service_periodicity'] ?? 'once';
				$charge_type = $meta['service_charge_type'] ?? 'room';
	
				//$single_price = $price * $service_quantity;
				$single_price = $price ? $price : 0;
	
				if ($periodicity == 'per_day') {
					$single_price *= $days_count;
				}

				if($charge_type == 'room'){
					$single_price *= $room_quantity;
				}else{
					$single_price *= $service_quantity;
				}
	
				return $carry + floatval($single_price);
			}, 0);
		}


		$eshb_settings = get_option('eshb_settings');
		$booking_type = isset($eshb_settings['booking-type']) ? $eshb_settings['booking-type'] : '';

		$regular_subtotal_price = $regular_subtotal_price + $extra_services_charge;
		$regular_total_price = $regular_subtotal_price;

		$subtotal_price = $subtotal_price + $extra_services_charge;
		$total_price = $subtotal_price;

		$discount =  $total_price < $regular_total_price ? abs( (float) $regular_total_price - (float) $total_price) : 0;

		$currency_symbol = $hotel_core->get_eshb_currency_symbol();
		$currency_symbol = html_entity_decode($currency_symbol);
		$currency_position = $hotel_core->get_eshb_currency_position();
	
		
		$extra_bed_price = apply_filters( 'eshb_apply_currency_converter_on_pricing_calculation', $extra_bed_price, $currency_converter);
		$extra_services_charge = apply_filters( 'eshb_apply_currency_converter_on_pricing_calculation', $extra_services_charge, $currency_converter);
		$base_price = apply_filters( 'eshb_apply_currency_converter_on_pricing_calculation', $base_price, $currency_converter);
		$regular_base_price = apply_filters( 'eshb_apply_currency_converter_on_pricing_calculation', $regular_base_price, $currency_converter);
		$subtotal_price = apply_filters( 'eshb_apply_currency_converter_on_pricing_calculation', $subtotal_price, $currency_converter);
		$regular_subtotal_price = apply_filters( 'eshb_apply_currency_converter_on_pricing_calculation', $regular_subtotal_price, $currency_converter);
		$total_price = apply_filters( 'eshb_apply_currency_converter_on_pricing_calculation', $total_price, $currency_converter);
		$regular_total_price = apply_filters( 'eshb_apply_currency_converter_on_pricing_calculation', $regular_total_price, $currency_converter);
		$single_day_price = apply_filters( 'eshb_apply_currency_converter_on_pricing_calculation', $single_day_price, $currency_converter);
		$discount = apply_filters( 'eshb_apply_currency_converter_on_pricing_calculation', $discount, $currency_converter);

		return [
			'startDate'           => $start_date,
			'endDate'             => $end_date,
			'today'               => $today_date,
			'tomorrow'            => $tomorrow,
			'daysCount'           => $days_count,
			'singleDayPrice'      => $single_day_price,
			'extraServicesPrice'  => $extra_services_charge,
			'extraBedPrice'       => $extra_bed_price,
			'basePrice'           => $base_price,
			'regularBasePrice'    => $regular_base_price,
			'subtotalPrice'       => $subtotal_price,
			'regularSubtotalPrice'=> $regular_subtotal_price,
			'totalPrice'          => $total_price,
			'regularTotalPrice'   => $regular_total_price,
			'discount'   		  => $discount,
			'currencySymbol'      => $currency_symbol,
			'currencyPosition'    => $currency_position
		];
	}

	public function add_to_cart_reservation(){

		// Verify nonce for security
		if (!isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {

			$error = array(
				'code' => 'invalid_nonce', 
				'message' => __('invalid_nonce', 'easy-hotel')
			);
			
			wp_send_json_error([
				'error' => $error,
			]);

			die();
		}


		if(isset($_POST['accomodationId']) && !empty($_POST['accomodationId'])){
	
			$hotel_core = new ESHB_Core();
			$accomodation_id = isset( $_POST['accomodationId'] ) ? (int) sanitize_text_field( wp_unslash($_POST['accomodationId']) ) : '';
			$today_date = esc_html(gmdate('Y-m-d')); // Get today's date
			$date = new DateTime($today_date); // Create a DateTime object from today's date
			$date->modify('+1 day'); // Add one day
			$tomorrow = $date->format('Y-m-d'); // Get the new date in 'Y-m-d' format
			$start_date = isset( $_POST['startDate'] ) ? sanitize_text_field( wp_unslash($_POST['startDate']) ) : $today_date;
			$end_date = isset( $_POST['endDate'] ) ? sanitize_text_field( wp_unslash($_POST['endDate']) ) : $tomorrow;
			$start_day_name = $start_date ? strtolower(gmdate('l', strtotime($start_date))) : '';
			$start_time = isset( $_POST['startTime'] ) ? sanitize_text_field( wp_unslash($_POST['startTime']) ) : '';
			$end_time = isset( $_POST['endTime'] ) ? sanitize_text_field( wp_unslash($_POST['endTime']) ) : '';
			$accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);

			$start = new DateTime($start_date);
			$end = new DateTime($end_date);
			$interval = $start->diff($end);
			$days_count = $interval->days;

			$room_quantity = isset( $_POST['roomQuantity'] ) ? (int) sanitize_text_field( wp_unslash($_POST['roomQuantity'] ) ) : 1;
			$extra_bed_quantity = isset( $_POST['extraBedQuantity'] ) ? (int) sanitize_text_field( wp_unslash($_POST['extraBedQuantity'] ) ) : 0;
			$adult_quantity = isset( $_POST['adultQuantity'] ) ? (int) sanitize_text_field( wp_unslash($_POST['adultQuantity'] ) ) : 1;
			$children_quantity = isset( $_POST['childrenQuantity'] ) ? (int) sanitize_text_field( wp_unslash($_POST['childrenQuantity'] ) ) : 0;
			$total_guest_quantity = $adult_quantity + $children_quantity;

			$selected_services = [];
			if(isset($_POST['selectedServices']) && !empty($_POST['selectedServices'])){

				$selected_services = sanitize_text_field(wp_unslash($_POST['selectedServices']));
				$selected_services = json_decode($selected_services, true);
				
			}

			$eshb_settings = get_option( 'eshb_settings', [] );
			$booking_type = isset($eshb_settings['booking-type']) && !empty($eshb_settings['booking-type']) ? $eshb_settings['booking-type'] : 'woocommerce';
			$string_booking_success_msg = isset($eshb_settings['string_book_success_msg']) && !empty($eshb_settings['string_booking_success_msg']) ? $eshb_settings['string_booking_success_msg'] : 'Reservation Successfully added to your cart.';
			$string_booking_failed_msg = isset($eshb_settings['string_booking_failed_msg']) && !empty($eshb_settings['string_booking_failed_msg']) ? $eshb_settings['string_booking_failed_msg'] : 'Ops! This Reservation has been failed.';
			$string_already_booked_msg = isset($eshb_settings['string_already_booked_msg']) && !empty($eshb_settings['string_already_booked_msg']) ? $eshb_settings['string_already_booked_msg'] : 'Ops! The date range is already booked:';
			$string_minimum_week_nights_msg = isset($eshb_settings['string_minimum_week_nights_msg']) && !empty($eshb_settings['string_minimum_week_nights_msg']) ? $eshb_settings['string_minimum_week_nights_msg'] : 'Please select more than 1 night!';
			$string_time_error_msg = isset($eshb_settings['string_time_error_msg']) && !empty($eshb_settings['string_time_error_msg']) ? $eshb_settings['string_time_error_msg'] : 'Selected time slot is not available!';
			$room_visibility = in_array('rooms', $eshb_settings['booking-form-fields']) ? true : false;
			$adult_visibility = in_array('adults', $eshb_settings['booking-form-fields']) ? true : false;
			$childrens_visibility = in_array('childrens', $eshb_settings['booking-form-fields']) ? true : false;
			$extra_beds_visibility = in_array('extra_beds', $eshb_settings['booking-form-fields']) ? true : false;

			$eshb_week_settings = [
				'string_check_in_day_error_msg' => 'Only allowed check in day is',
				'eshb_booking_allowed_check_in_day' => 'all',
			];
			$eshb_week_settings = apply_filters( 'eshb_week_settings', $eshb_week_settings );
			$allowed_check_in_day = apply_filters( 'eshb_booking_allowed_check_in_day', 'all', $accomodation_id, $accomodation_metaboxes );
			$string_check_in_day_error_msg = !empty($eshb_week_settings['string_check_in_day_error_msg']) ? $eshb_week_settings['string_check_in_day_error_msg'] : 'Only allowed check in day is : ';

			// Min Max Booking Configurations
			$min_max_settings = [
				'required_min_nights' => '',
				'required_max_nights' => '',
				'is_global_source_for_min_max' => true,
			];
			$min_max_settings = apply_filters( 'eshb_min_max_settings', $min_max_settings, $accomodation_id, $accomodation_metaboxes );
			$required_min_nights = $start_date !== $end_date && isset($min_max_settings['required_min_nights']) ? $min_max_settings['required_min_nights'] : '';
			$required_max_nights = $start_date !== $end_date && isset($min_max_settings['required_max_nights']) ? $min_max_settings['required_max_nights'] : '';
			$string_required_minimum_nights_msg = esc_html__( 'Ops! This Reservation has been failed. Requried Minumum', 'easy-hotel' );
			$string_required_maximum_nights_msg = esc_html__( 'Ops! This Reservation has been failed. Requried Maximum', 'easy-hotel' );
			$pricing_periodicity = apply_filters( 'eshb_pricing_periodicity', false, $accomodation_id, $accomodation_metaboxes );
			
			// Validate booking type
			if ( $booking_type == 'woocommerce' && ! class_exists( 'WooCommerce' ) ) {
				$error = array(
					'code'    => 'woocommerce_not_activated',
					'message' => esc_html__( 'Ops! Woocommerce not activated. Activate Woocommerce.', 'easy-hotel'),
				);
				wp_send_json_error( [ 'error' => $error ] );

			} elseif ( $booking_type == 'surecart' && ! class_exists( 'SureCart' ) ) {
				$error = array(
					'code'    => 'surecart_not_activated',
					'message' => esc_html__( 'Ops! Surecart not activated. Activate Surecart.', 'easy-hotel' ),
				);
				wp_send_json_error( [ 'error' => $error ] );

			} elseif ( $booking_type == 'surecart' && ! class_exists( 'ESHB_SURECART' ) ) {
				$error = array(
					'code'    => 'surecart_addon_not_activated',
					'message' => esc_html__( 'Ops! EHB Surecart addons not activated. Activate EHB Surecart.', 'easy-hotel' ),
				);
				wp_send_json_error( [ 'error' => $error ] );
			}


			$available_times = ESHB_Helper::get_available_times_by_date($accomodation_id, $start_date);
			$is_valid_time = false;
			
			if($pricing_periodicity == 'per_hour' && (!empty($accomodation_metaboxes['single_day_price']) || !empty($accomodation_metaboxes['single_day_sale_price'])) && !empty($available_times)){
				$available_slots = isset($available_times['available_slots']) ? $available_times['available_slots'] : [];

				if(count($available_slots) > 0) {
					foreach ($available_slots as $slot) {
						if ($start_time >= $slot[0] && $end_time <= $slot[1]) {
							$is_valid_time = true;
							break;
						}
					}
				}else{
					$is_valid_time = false;
				}

				if(!$is_valid_time){
					$error = array('code' => 'invalid_time', 'message' => esc_html($string_time_error_msg));
					wp_send_json_error([
						'error' => $error,
					]);
				}
			}

			$actual_booked_dates = $this->get_actual_booked_dates_in_date_ranges($accomodation_id, $start_date, $end_date);

			if(!empty($actual_booked_dates)){

				$error_message = sprintf(
					/* translators: 1: already booked message text, 2: list of booked dates */
					esc_html__( '%1$s %2$s', 'easy-hotel' ),
					esc_html( $string_already_booked_msg ),
					$booked_dates_str
				);

				$error = array(
					'code'    => 'already_booked',
					'message' => $error_message,
				);
				wp_send_json_error([
					'error' => $error,
					'booked_dates' => implode(', ', $actual_booked_dates),
				]);

			}

			$holiday_dates = $this->get_holiday_dates_in_date_ranges($accomodation_id, $start_date, $end_date);

			if(!empty($holiday_dates)){
				$error = array('code' => 'holiday_dates', 'message' => esc_html__('Selected date range is not available. These dates are in holidays.', 'easy-hotel'));
				wp_send_json_error([
					'error' => $error,
					'holiday_dates' => implode(', ', $holiday_dates),
				]);
			}

			// check rooms capacity
			if(!$is_valid_time && isset($_POST['roomQuantity']) && !empty($_POST['roomQuantity'])){
				$roomQuantity = isset( $_POST['roomQuantity'] ) ? (int) sanitize_text_field( wp_unslash($_POST['roomQuantity'] ) ) : 1;
				
				$availableRoom = $this->get_available_room_count_by_date_range($accomodation_id, $start_date, $end_date);

				if($roomQuantity > $availableRoom){
					$error = array(
						'code'    => 'room_capacity_not_enough',
						'message' => sprintf(
							/* translators: %s: number of available rooms */
							esc_html__( 'Selected room is not available. Available room: %s', 'easy-hotel' ),
							esc_html( $availableRoom )
						),
					);
					wp_send_json_error([
						'error' => $error,
					]);
				}
			}


			// Validate check-in day
			if($allowed_check_in_day != 'all' && $start_day_name != '' && !empty($allowed_check_in_day) && $allowed_check_in_day == $start_day_name) {
				$error = array(
					'code'    => 'check_in_day_error',
					'message' => sprintf(
						/* translators: %s: allowed check-in day */
						esc_html__( '%1$s %1$s', 'easy-hotel' ),
						esc_html( $string_check_in_day_error_msg ),
						esc_html( $allowed_check_in_day )
					),
				);
				wp_send_json_error([
					'error' => $error,
				]);
			}
		

			if(!empty($allowed_check_in_day) && $allowed_check_in_day != 'all'){
				$days_count = 1;
			}
			
			
			if(!empty($required_min_nights) && $days_count < $required_min_nights){
				$error = array(
					'code'    => 'required_min_nights',
					'message' => sprintf(
						/* translators: 1: minimum nights message text, 2: required minimum nights */
						esc_html__( '%1$s %2$s nights!', 'easy-hotel' ),
						esc_html( $string_required_minimum_nights_msg ),
						esc_html( $required_min_nights )
					),
				);
				wp_send_json_error([
					'error' => $error,
				]);
			}
			if(!empty($required_max_nights) && $days_count > $required_max_nights){
				$error = array(
					'code'    => 'required_max_nights',
					'message' => sprintf(
						/* translators: 1: maximum nights message text, 2: required maximum nights */
						esc_html__( '%1$s %2$s nights allowed!', 'easy-hotel' ),
						esc_html( $string_required_maximum_nights_msg ),
						esc_html( $required_max_nights )
					),
				);
				wp_send_json_error([
					'error' => $error,
				]);
			}
			
			// validate capacities
			$adult_capacity = !empty($accomodation_metaboxes['adult_capacity']) ? $accomodation_metaboxes['adult_capacity'] : 0;
			$children_capacity = !empty($accomodation_metaboxes['children_capacity']) ? $accomodation_metaboxes['children_capacity'] : 0;
			$total_capacity = !empty($accomodation_metaboxes['total_capacity']) ? $accomodation_metaboxes['total_capacity'] : 0;
			$extra_bed_capacity = !empty($accomodation_metaboxes['total_extra_beds']) ? $accomodation_metaboxes['total_extra_beds'] : 0;
			

			if(!empty($total_capacity)){
				if($adult_capacity < 1 && $children_capacity > 0){
					$adult_capacity = $total_capacity - $children_quantity;
				}elseif($adult_capacity < 1 && $children_capacity < 1){
					$adult_capacity = $total_capacity;
				}

				if($children_capacity < 1 && $adult_capacity > 0){
					$children_capacity = $total_capacity - $adult_quantity;
				}elseif($children_capacity < 1 && $adult_capacity < 1){
					$children_capacity = $total_capacity;
				}
			}

			
			if($adult_quantity > 0 && $adult_capacity < $adult_quantity){
				$error = array('code' => 'adult_capacity_not_enough', 'message' => esc_html__('Maximum Adult Capacity', 'easy-hotel') . $adult_capacity);
				wp_send_json_error([
					'error' => $error,
				]);
			}

			if($children_quantity > 0 && $children_capacity < $children_quantity){
				$error = array('code' => 'children_capacity_not_enough', 'message' => esc_html__('Maximum Children Capacity', 'easy-hotel') . $children_capacity);
				wp_send_json_error([
					'error' => $error,
				]);
			}
			
			if($extra_bed_quantity > 0 && $extra_bed_capacity < $extra_bed_quantity){
				$error = array('code' => 'extra_bed_capacity_not_enough', 'message' => esc_html__('Maximum Extra Bed Capacity', 'easy-hotel') . $extra_bed_capacity );
				wp_send_json_error([
					'error' => $error,
				]);
			}

			
			
			$pricing = $this->calculate_booking_pricing($accomodation_id, $start_date, $end_date, $room_quantity, $extra_bed_quantity, $adult_quantity, $children_quantity, $selected_services, false, $start_time, $end_time );

			$base_price = $pricing['basePrice'];
			$extra_services_charge = $pricing['extraServicesPrice'];
			$extra_bed_price= $pricing['extraBedPrice'];
			$subtotal_price = $pricing['subtotalPrice'];
			$total_price = $pricing['totalPrice'];
			$discount = $pricing['discount'];
			$currency_symbol = $pricing['currencySymbol'];
			$quantity = 1;

			$details = [];

			if ($room_visibility && $room_quantity > 0) {
				$details[] = 'Room: ' . $room_quantity;
			}
			if ($extra_beds_visibility && $extra_bed_quantity > 0) {
				$details[] = 'Extra Bed: ' . $extra_bed_quantity;
			}
			if ($adult_visibility && $adult_quantity > 0) {
				$details[] = 'Adult: ' . $adult_quantity;
			}
			if ($childrens_visibility && $children_quantity > 0) {
				$details[] = 'Children: ' . $children_quantity;
			}

			$details_html = implode(', ', $details);

			
			$extra_service_titles = [];
			$extra_services_html = '';
			
			if(!empty($selected_services) && is_array($selected_services) && count($selected_services) > 0 ){
			
				foreach ($selected_services as $service) {
	
					$service_id = $service['id'];

					$service_quantity = $service['quantity'];

					if(!empty($service_quantity)){
						$eshb_service_metaboxes = get_post_meta($service_id, 'eshb_service_metaboxes', true);

						$charge_type = $eshb_service_metaboxes['service_charge_type'];

						$service_title = get_the_title($service_id) . ' For ' . $service_quantity . ' ' .  ucfirst(strtolower($charge_type));

						if(!$room_visibility && $charge_type == 'room'){
							$service_title = get_the_title($service_id);
						}
					}else{
						$service_title = get_the_title($service_id);
					}

					

					array_push($extra_service_titles, $service_title);
	
				}
				$extra_services_html = implode(", ",$extra_service_titles);
			}

			$dates = date_i18n( get_option('date_format'), strtotime( $start_date ) ) .' - '. date_i18n( get_option('date_format'), strtotime( $end_date ) );
			if($start_date == $end_date){
				$dates = date_i18n( get_option('date_format'), strtotime( $start_date ) );
			}

			$formated_start_timestamp = strtotime( $start_time );
			$formated_end_timestamp = strtotime( $end_time );

			$cart_item_data = [
					'accomodation_id' => $accomodation_id,
					'subtotal_price' => $subtotal_price,
					'total_price' => $total_price,
					'base_price' => $base_price,
					'discount' => $discount, 
					'extra_services_charge' => $extra_services_charge,
					'extra_bed_price' => $extra_bed_price,
					'currency_symbol' => $currency_symbol,
					'start_date' => $start_date,
					'end_date' => $end_date,
					'night' => $days_count,
					'dates' => $dates,
					'room_quantity' => $room_quantity,
					'adult_quantity' => $adult_quantity,
					'details_html' => $details_html,
					'extra_services_html' => $extra_services_html
				];

				if(!empty($extra_bed_quantity)) {
					$cart_item_data['extra_bed_quantity'] = $extra_bed_quantity;
				}
				if(!empty($start_time)) {
					$cart_item_data['start_time'] = $start_time;
				}
				if(!empty($end_time)) {
					$cart_item_data['end_time'] = $end_time;
				}

				if(!empty($start_time) && !empty($end_time)) {
					// Get WP formatted time (according to Settings > General > Time Format)
					$wp_time_format = get_option( 'time_format' ); // WP settings
					$formatted_start_time = wp_date( $wp_time_format, $formated_start_timestamp );
					$formatted_end_time = wp_date( $wp_time_format, $formated_end_timestamp );
					$times = $formatted_start_time . ' - ' . $formatted_end_time;
					$cart_item_data['times'] = $times;
				}

				if(!empty($children_quantity)) {
					$cart_item_data['children_quantity'] = $children_quantity;
				}

				if(!empty($selected_services)) {
					$cart_item_data['extra_services'] = $selected_services;
				}
				
				if(!empty($extra_services_charge)) {
					$cart_item_data['extra_services_html'] = $extra_services_html;
				}

			if (get_post_type($accomodation_id) === 'eshb_accomodation') {

				$thumbnail_id = get_post_thumbnail_id($accomodation_id);
				$price_id = 0;
				$product_id = ESHB_Helper::get_product_id_for_cart($accomodation_id, $booking_type);
				$product_id = apply_filters( 'eshb_product_id_for_cart', $product_id, $accomodation_id, $thumbnail_id );
				
				$cart_item_data['product_id'] = $product_id;
				$cart_item_data['price_id'] = $price_id;

				do_action( 'eshb_before_add_to_cart', $booking_type, $cart_item_data, $string_booking_success_msg );

				if($booking_type == 'woocommerce'){

					update_post_meta($product_id, '_accomodation_id', $accomodation_id);
					$this->update_product_price_by_id($product_id, $subtotal_price);

					$cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, 0, [], $cart_item_data);
					
					if ( $cart_item_key ) {

						wp_send_json_success([
							'booking-type'   => $booking_type,
							'message'        => esc_html( $string_booking_success_msg ),
							'cart_item_key'  => $cart_item_key,
							'cart_count'     => WC()->cart->get_cart_contents_count(),
						]);

					} else {

						$error = array(
							'code'    => 'cart_item_key_not_found',
							'message' => esc_html( $string_booking_failed_msg ),
						);

						wp_send_json_error([
							'error'          => $error,
							'cart_item_data' => $cart_item_data,
							'product_id'     => $product_id,
						]);
					}

				}

				do_action( 'eshb_after_add_to_cart', $booking_type, $cart_item_data, $string_booking_success_msg );
				

			}else{
				$error = array('code' => 'accomodation_post_type_not_found', 'message' => esc_html($string_booking_failed_msg));
				wp_send_json_error([
					'error' => $error,
				]);
			}
			die();
		}
	
	}

	public function get_booking_prices() {
		// Ensure external filters are loaded during AJAX requests
		do_action('eshb_before_calculate_pricing');
		
		if (isset($_POST['accomodationId']) && !empty($_POST['accomodationId'])) {
	
			// Verify nonce for security
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
				wp_send_json_error(['message' => 'Invalid nonce']);
				die();
			}
	
			$accomodation_id = (int) sanitize_text_field(wp_unslash($_POST['accomodationId']));
	
			$start_date = sanitize_text_field(wp_unslash($_POST['startDate'] ?? $today_date));
			$end_date = sanitize_text_field(wp_unslash($_POST['endDate'] ?? $tomorrow));

			$start_time = sanitize_text_field(wp_unslash($_POST['startTime'] ?? ''));
			$end_time = sanitize_text_field(wp_unslash($_POST['endTime'] ?? ''));
	
			$room_quantity     = (int) sanitize_text_field(wp_unslash($_POST['roomQuantity'] ?? 1));
			$extra_bed_quantity = (int) sanitize_text_field(wp_unslash($_POST['extraBedQuantity'] ?? 0));
			$adult_quantity     = (int) sanitize_text_field(wp_unslash($_POST['adultQuantity'] ?? 1));
			$children_quantity  = (int) sanitize_text_field(wp_unslash($_POST['childrenQuantity'] ?? 0));
			$selected_services = [];
			if(isset($_POST['selectedServices']) && !empty($_POST['selectedServices'])){

				$selected_services = sanitize_text_field(wp_unslash($_POST['selectedServices']));
				$selected_services = json_decode($selected_services, true);
				
			}
			

			$pricing = $this->calculate_booking_pricing($accomodation_id, $start_date, $end_date, $room_quantity, $extra_bed_quantity, $adult_quantity, $children_quantity, $selected_services, true, $start_time, $end_time);
			
			wp_send_json_success($pricing);
		}
	
		wp_die();
	}
	
    public function get_extra_services_charge(){

		// Verify nonce for security
		if (!isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
			wp_send_json_error(['message' => 'Invalid nonce']);
			die();
		}

        if(isset($_POST['selectedServices']) && !empty($_POST['selectedServices'])){

            $selected_services = array_map('sanitize_text_field', wp_unslash($_POST['selectedServices']));
			$days_count = isset($_POST['dayCount']) ? sanitize_text_field(wp_unslash($_POST['dayCount'])) : 1;

            $price = 0;

            if(is_array($selected_services)){
                foreach ($selected_services as $service) {
			
                    $service_id = $service['id'];
                    $quantity = $service['quantity'];
                    
                    $eshb_service_metaboxes = get_post_meta($service_id, 'eshb_service_metaboxes', true);

					$service_periodicity = $eshb_service_metaboxes['service_periodicity'];
                    
                    if (isset($eshb_service_metaboxes['service_price'])) { // Ensure 'service_price' is set
                        $single_price = $eshb_service_metaboxes['service_price'] * $quantity;
						if($service_periodicity == 'perday' && !empty($days_count)){
							$single_price = $days_count * $single_price;
						}
                        $price += floatval($single_price); // Add the single price to the total price
                    }

                }
            }
            
            wp_send_json_success( ['price' => $price] );

            wp_die();

        }

    }

	public function get_available_room_count_by_date_range($accomodation_id, $start_date, $end_date, $start_time = '', $end_time = '') {
		$accomodation_id = intval($accomodation_id);
		$start_date_obj = new DateTime($start_date);
		$end_date_obj = new DateTime($end_date);

		if ($end_date_obj < $start_date_obj) {
			return new WP_Error('invalid_date_range', 'End date cannot be earlier than start date.');
		}

		// Fetch total rooms
		$accomodation_metas = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
		$total_rooms = isset($accomodation_metas['total_rooms']) ? intval($accomodation_metas['total_rooms']) : 1;

		// Initialize bookings count array
		$bookings_per_date = [];

		// Query all bookings
		$bookings_args = [
			'post_type'      => 'eshb_booking',
			'posts_per_page' => -1,
			//'post_status'    => 'publish',
			'post_status'    => ['publish', 'completed', 'processing', 'deposit-payment', 'pending'],
			'fields' => 'ids',
		];
		$bookings = new WP_Query($bookings_args);

		if ($bookings->have_posts()) {
			while ($bookings->have_posts()) {
				$bookings->the_post();
				$booking_id = get_the_ID();
				$booking_metas = get_post_meta($booking_id, 'eshb_booking_metaboxes', true);
				$booking_accomodation_id = $booking_metas['booking_accomodation_id'];
				$booking_status = isset($booking_metas['booking_status']) ? $booking_metas['booking_status'] : '';
				$valid_statuses = array('pending', 'processing', 'on-hold', 'completed', 'deposit-payment');

				if (
					is_array($booking_metas) &&
					intval($booking_accomodation_id) === $accomodation_id &&
					isset($booking_metas['booking_start_date'], $booking_metas['booking_end_date']) &&
					in_array($booking_status, $valid_statuses)
				) {
					$booking_start = new DateTime($booking_metas['booking_start_date']);
					$booking_end = new DateTime($booking_metas['booking_end_date']);
					$booked_rooms = !empty($booking_metas['room_quantity']) ? intval($booking_metas['room_quantity']) : 1;
					$accomodation_metaboxes = get_post_meta( $booking_accomodation_id, 'eshb_accomodation_metaboxes', true );
					$total_rooms = !empty($accomodation_metaboxes['total_rooms']) ? $accomodation_metaboxes['total_rooms'] : 1;

					if(!empty($start_time) && !empty($end_time)) {
						$booking_start_time = !empty($booking_metas['booking_start_time']) ? $booking_metas['booking_start_time'] : '';
						$booking_end_time = !empty($booking_metas['booking_end_time']) ? $booking_metas['booking_end_time'] : '';

						if($booking_start_time !== $start_time) {
							$booked_rooms = 0;
						}
						if($booking_start_time === $start_time && $booking_end_time === $end_time) {
						
							$booked_rooms = 0;
						}
					}

					// Loop through each date in this booking
					$period = new DatePeriod($booking_start, new DateInterval('P1D'), (clone $booking_end)->modify('+0 day'));
					if($booking_start == $booking_end) {
						$period = new DatePeriod($booking_start, new DateInterval('P1D'), (clone $booking_end)->modify('+1 day'));
					}

					foreach ($period as $date) {
						$formatted = $date->format('Y-m-d');
						if (!isset($bookings_per_date[$formatted])) {
							$bookings_per_date[$formatted] = 0;
						}
						$bookings_per_date[$formatted] += $booked_rooms;
					}
					
				}
			}
			wp_reset_postdata();
		}

		

		// Now calculate the max number of rooms booked in the given date range
		$range_period = new DatePeriod($start_date_obj, new DateInterval('P1D'), (clone $end_date_obj)->modify('+0 day'));
		$max_booked_in_range = 0;

		if($start_date_obj == $end_date_obj){
			$formatted = $start_date_obj->format('Y-m-d');
			$available_rooms = isset($bookings_per_date[$formatted]) ? $total_rooms - $bookings_per_date[$formatted] : $total_rooms;
			return $available_rooms;
		}

		foreach ($range_period as $date) {
			$formatted = $date->format('Y-m-d');
			$booked = isset($bookings_per_date[$formatted]) ? $bookings_per_date[$formatted] : 0;
			if ($booked > $max_booked_in_range) {
				$max_booked_in_range = $booked;
			}
		}

		// Final available room count
		$available_rooms = max(0, $total_rooms - $max_booked_in_range);

		if($available_rooms < 0) {
			$available_rooms = 0;
		}

		return $available_rooms;
	}


	public function get_available_rooms_counts_data(){

		// Verify nonce for security
		if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
			wp_send_json_error(['message' => 'Invalid nonce']);
			die();
		}
		

		$accomodation_id = !empty($_POST['accomodationId']) ? sanitize_text_field( wp_unslash($_POST['accomodationId']) ) : '';
		$start_date = !empty($_POST['startDate']) ? sanitize_text_field( wp_unslash($_POST['startDate']) ) : '';
		$end_date = !empty($_POST['endDate']) ? sanitize_text_field( wp_unslash($_POST['endDate']) ) : '';

		$start_time = !empty($_POST['startTime']) ? sanitize_text_field( wp_unslash($_POST['startTime']) ) : '';
		$end_time = !empty($_POST['endTime']) ? sanitize_text_field( wp_unslash($_POST['endTime']) ) : '';


		$start_date = new DateTime($start_date);
		$end_date = new DateTime($end_date);
		
		$eshb_accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
		$total_rooms = !empty($eshb_accomodation_metaboxes['total_rooms']) ? $eshb_accomodation_metaboxes['total_rooms'] : 0;

		$available = 1;
		$available = $this->get_available_room_count_by_date_range($accomodation_id, $start_date->format('Y-m-d'), $end_date->format('Y-m-d'), $start_time, $end_time);
		if($available < 0 ) {
            $available = 0;
        }

		wp_send_json_success( 
			array(
				'available' => $available,
				'total' => $total_rooms,
			) 
		);

		die();

	}

	public function get_accomodation_available_capacity_counts(){

		if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
			wp_send_json_error(['message' => 'Invalid nonce']);
			die();
		}


		if(isset($_POST['accomodationId']) && !empty($_POST['accomodationId']) && isset($_POST['type']) && !empty($_POST['type'])){
			$type = sanitize_text_field( wp_unslash($_POST['type']) );
			$accomodation_id = sanitize_text_field( wp_unslash($_POST['accomodationId']) );
			$accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
			$total_guest_quantity = $accomodation_metaboxes['total_capacity'];
			$selected_rooms_count = !empty($_POST['rooms']) ? sanitize_text_field( wp_unslash($_POST['rooms']) ) : 1;
			$start_date = !empty($_POST['startDate']) ? sanitize_text_field( wp_unslash($_POST['startDate']) ) : '';
			$end_date = !empty($_POST['endDate']) ? sanitize_text_field( wp_unslash($_POST['endDate']) ) : '';
			$start_date = new DateTime($start_date);
			$end_date = new DateTime($end_date);

			$available = 1;
			$msg = __('Maximum Capacity', 'easy-hotel');

			if($type == 'room_quantity'){
				$total = $accomodation_metaboxes['total_rooms']; 

				$available = $this->get_available_room_count_by_date_range($accomodation_id, $start_date->format('Y-m-d'), $end_date->format('Y-m-d'));
				
				if($available < 1){
					$msg = __('Available Room', 'easy-hotel');
				}
				
			}

			if($type == 'adult_quantity'){
			
				if(!empty($accomodation_metaboxes['adult_capacity'])){
					$available = $accomodation_metaboxes['adult_capacity']; 
					$available = $available * $selected_rooms_count;
				}else{
					$childrenQuantity = !empty($_POST['childrenQuantity']) ? sanitize_text_field( wp_unslash( $_POST['childrenQuantity'] ) ) : 0;	
				
					$total_capacity = $accomodation_metaboxes['total_capacity'];
					
					$available = $total_capacity - $childrenQuantity;

					$available = $available * $selected_rooms_count;

					$msg = __('Maximum Adult and Children Capacity', 'easy-hotel');
				}
				
			}

			if($type == 'children_quantity'){

				$total_capacity = $accomodation_metaboxes['total_capacity'];

				if(!empty($accomodation_metaboxes['children_capacity'])){
					$available = $accomodation_metaboxes['children_capacity']; 
					$available = $available * $selected_rooms_count;
				}else{
					$childrenQuantity = !empty($_POST['adultQuantity']) ? sanitize_text_field( wp_unslash( $_POST['adultQuantity'] ) ) : 0;	
				
					$available = $total_capacity - $childrenQuantity;

					$available = $available * $selected_rooms_count;

					$msg = __('Maximum Adult and Children Capacity', 'easy-hotel');
				}
				
			}

			if($type == 'extra_bed_quantity'){
				$available = $accomodation_metaboxes['total_extra_beds']; 
				$available = $available * $selected_rooms_count;
			}

			if($type == 'service-quantity'){
				$available = $total_guest_quantity;
			}


			if(($type != 'room_quantity') && (empty($available) || $available < 1)){
				$available = $total;
			}

			if($available < 0) {
				$available = 0;
			}

			$msg = $type != 'adult_quantity' || $type != 'children_quantity' ? $msg . ' ' . $available : $msg;

			wp_send_json_success( 
				array(
					'message' => $msg,
					'available' => $available,
					'type' => $type,
				) 
			);
		}
	}

	public function get_accomodation_available_extra_bed_counts(){

		// Verify nonce for security
		if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
			wp_send_json_error(['message' => 'Invalid nonce']);
			die();
		}

		if(isset($_POST['accomodationId']) && !empty($_POST['accomodationId']) && !empty($_POST['accomodationId'])){

			$total_extra_beds = 0;
			$accomodation_id = (int) sanitize_text_field( wp_unslash($_POST['accomodationId']) );
			$accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
			
			$total_extra_beds = $accomodation_metaboxes['total_extra_beds']; 

			wp_send_json_success( $total_extra_beds );

		}
	}

	public function send_email_woocommerce_style($email, $subject, $heading, $message) {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}
	
		// Get WooCommerce mailer
		$mailer = WC()->mailer();
	
		// Wrap message with WooCommerce email template
		$wrapped_message = $mailer->wrap_message($heading, $message);
	
		// Create new WC_Email instance
		$wc_email = new WC_Email();
	
		// Style the wrapped message with WooCommerce inline styles
		$html_message = $wc_email->style_inline($wrapped_message);
	
		// Headers
		$headers = array('Content-Type: text/html; charset=UTF-8');
	
		// Send the email
		$mailer->send($email, $subject, $html_message, $headers);
	}

	public function filter_status_based_on_booking_meta($status, $order_id) {
		// Check if custom meta _booking_post_created exists
		$booking_post_id = get_post_meta($order_id, '_booking_post_created', true);

		$eshb_settings = get_option('eshb_settings', []);
		

		if ( ! empty($booking_post_id) ) {
			if(isset($eshb_settings['booking-auto-approval']) && $eshb_settings['booking-auto-approval'] == true){
				return 'completed'; // Mark order as completed
			}
		}

		// Otherwise, keep default status (usually 'processing')
		return $status;
	}
	
	public function update_booking_status_on_woocommerce_order_status_change($order_id, $old_status, $new_status, $order) {
		
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		//if ( $screen && $screen->id !== 'woocommerce_page_wc-orders' ) return;
		if ( $screen && $screen->id === 'edit-eshb_booking' ) return;

		if ( $screen && 'woocommerce_page_wc-orders' === $screen->id ) {
			return;
    	}

		// Retrieve the order object
		$order = wc_get_order($order_id);
	
		// Get the booking post ID from order meta
		$booking_post_id = get_post_meta($order_id, '_booking_post_created', true); // Adjusted to your meta key
	
		if ($booking_post_id) {

			// update booking status
            $updated_booking = array(
                'ID'           => $booking_post_id,
                'post_status'  => $new_status,
            );

            wp_update_post( $updated_booking );

			// Get the existing meta for the custom post
			$booking_meta = get_post_meta($booking_post_id, 'eshb_booking_metaboxes', true);
	
			// Ensure the meta is an array before modifying it
			if (is_array($booking_meta)) {
				// Update the `booking_status` field
				$booking_meta['booking_status'] = $new_status;
	
				// Save the updated meta back to the post
				update_post_meta($booking_post_id, 'eshb_booking_metaboxes', $booking_meta);
			}
		}
	}

	public function update_woocommerce_order_status_on_booking_status_change($post_id, $post, $update) {
		
		if(class_exists('ESHB_MANUAL_BOOKING') && is_admin()){
			return;
		}

		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		// Ensure this runs only during post update
		if (!$update || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
			return;
		}
	
		// Check if the post type matches
		if ($post->post_type !== 'eshb_booking') {
			return;
		}

		
	
		// Retrieve the metabox data
		$eshb_booking_metaboxes = get_post_meta($post_id, 'eshb_booking_metaboxes', true) ?? [];
	
		// Validate the required fields
		if (empty($eshb_booking_metaboxes) || empty($eshb_booking_metaboxes['booking_status']) || empty($eshb_booking_metaboxes['order_id'])) {
			return;
		}
	
		$booking_status = sanitize_text_field($eshb_booking_metaboxes['booking_status']);
		$order_id = intval($eshb_booking_metaboxes['order_id']);
	
		// Get the WooCommerce order object
		$order = wc_get_order($order_id);
		if (!$order) {
			return; // Invalid order
		}
	
		// Check if the current order status matches the booking status to avoid redundant updates
		if ($order->get_status() === $booking_status) {
			return; // Status is already up-to-date
		}
	
		try {
			$order->update_status($booking_status, 'Booking status updated to: ' . $booking_status);
		} catch (Exception $e) {
			// Log error for debugging
			//error_log('Error updating order status: ' . $e->getMessage());
		}
	}

	public function send_booking_email_notification_for_booking_status($post_id, $post, $update){

		// Ensure this runs only during post update
		if (!$update || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
			return;
		}
	
		// Check if the post type matches
		if ($post->post_type !== 'eshb_booking') {
			return;
		}
	
		// Retrieve the metabox data
		$eshb_booking_metaboxes = get_post_meta($post_id, 'eshb_booking_metaboxes', true) ?? [];
	

		// Validate the required fields
		if (empty($eshb_booking_metaboxes) || empty($eshb_booking_metaboxes['booking_status']) || empty($eshb_booking_metaboxes['order_id'])) {
			return;
		}

		$booking_status = $post->post_status;
		$order_id = $eshb_booking_metaboxes['order_id'];

		$eshb_settings = get_option('eshb_settings', []);
		$booking_type = isset($eshb_settings['booking-type']) && !empty($eshb_settings['booking-type']) ? $eshb_settings['booking-type'] : 'woocommerce';


		do_action('eshb_booking_status_changed', $post_id, $booking_status, $booking_type, $order_id);

		// send email for woocommerce
		if ( $booking_type == 'woocommerce' && function_exists( 'WC' ) ) {

			$order = wc_get_order($order_id);
			if (!$order) {
				return; // Invalid order
			}
			// Trigger email manually
			$mailer = WC()->mailer();
			
			switch ( $booking_status ) {

				case 'cancelled':
					$to      = $order->get_billing_email();
					$subject = sprintf(
						/* translators: %s: booking/post ID */
						__( 'Your Booking #%s has been Cancelled', 'easy-hotel' ),
						esc_html( $post_id )
					);
					$heading = __( 'Booking Cancelled', 'easy-hotel' );

					$first_name = esc_html( $order->get_billing_first_name() );
					$post_title = esc_html( get_the_title( $post_id ) );

					$message  = '<p>' . sprintf(
						/* translators: %s: customer first name */
						__( 'Hi %s,', 'easy-hotel' ),
						$first_name
					) . '</p>';

					$message .= '<p>' . sprintf(
						/* translators: %s: booking title */
						__( 'We regret to inform you that your <strong>%s</strong> has been <strong>cancelled</strong>.', 'easy-hotel' ),
						$post_title
					) . '</p>';

					$message .= '<p>' . __( 'If you have any questions, please feel free to reply to this email.', 'easy-hotel' ) . '</p>';
					$message .= '<p>' . __( 'Thank you for shopping with us.', 'easy-hotel' ) . '</p>';

					$this->send_email_woocommerce_style( $to, $subject, $heading, $message );
					break;
			}

		}
	}
}
ESHB_Booking::instance();