<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class ESHB_Helper {

    public static function eshb_nonce_field( $action = 'eshb_action', $field = 'eshb_nonce', $echo = true ) {
        // Generate site-specific action using home_url()
        $nonce_action = sanitize_key( $action ) . '_' . md5( home_url() );

        // Create the nonce field HTML
        $nonce_field = wp_nonce_field( $nonce_action, sanitize_key( $field ), true, false );

        // Whitelist only the safe HTML tags and attributes
        $allowed_tags = array(
            'input' => array(
                'type'  => true,
                'id'    => true,
                'name'  => true,
                'value' => true,
            ),
        );

        // Sanitize output
        $safe_nonce_field = wp_kses( $nonce_field, $allowed_tags );

        if ( $echo ) {
            // Escaping required by Plugin Check (escape before output)
            echo wp_kses( $safe_nonce_field, $allowed_tags );
            return null;
        }

        // Return sanitized version
        return $safe_nonce_field;
    }
    
    public static function generate_secure_nonce_action($action) {
        $nonce_action = $action . '_' . md5( home_url() );
        return $nonce_action;
    }

    public static function eshb_insert_booking($order_id, $booking_status, $cart_item_data) {

        if(empty($booking_status) || empty($cart_item_data) || $cart_item_data === null) return false;

        $post_id = wp_insert_post(array(
            'post_type'   => 'eshb_booking',
            'post_title'  => 'Booking',
            'post_status' => $booking_status,
            'meta_input'  => array(
                'eshb_booking_metaboxes' => $cart_item_data
            )
        ));

        // Check if the post was inserted successfully
        $accomodation_id = !empty($cart_item_data['booking_accomodation_id']) ? $cart_item_data['booking_accomodation_id'] : 0;
        $accomodation_title = get_the_title( $accomodation_id );

        if (!is_wp_error($post_id)) {
            // Update the post title to include the post ID
            wp_update_post(array(
                'ID'         => $post_id,
                'post_title' => 'Booking #' . $post_id . ' for: ' . $accomodation_title,
                'post_status' => $booking_status, // Update status to 'publish'
            ));


            // Update Available Rooms Count For This Accomodation
            $accomodation_metaboxes = get_post_meta( $accomodation_id, 'eshb_accomodation_metaboxes', true );
            $total_rooms = floatval($accomodation_metaboxes['total_rooms']);
            $current_available_rooms = floatval($accomodation_metaboxes['available_rooms']);
            $room_quantity = isset($cart_item_data['room_quantity']) ? $cart_item_data['room_quantity'] : 1;

            if(!empty($current_available_rooms)){
                $available_rooms = $current_available_rooms - floatval($room_quantity);
            }else{
                $available_rooms = $total_rooms - floatval($room_quantity);
            }
            
            $accomodation_metaboxes['available_rooms'] = $available_rooms;
            update_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', $accomodation_metaboxes);
            
            // Update Order Status
            $new_status = $booking_status;
            update_post_meta($order_id, '_booking_post_created', $post_id);


            do_action( 'eshb_after_booking_created', $post_id, $order_id );

            error_log('booking created with ID: ' . $post_id);

            return $post_id;

        }else{
            $error_message = $post_id->get_error_message();
            //error_log("Error creating booking for order ID: $order_id, Error: $error_message");
            return false;
        }
    }

    public static function get_clean_number($num){
        if (intval($num) == $num) {
            return intval($num);
        }
        return $num;
    }

    public static function get_product_id_for_cart($accomodation_id, $booking_type) {
        $product_id = 0;

        if ($booking_type == 'woocommerce') {
            $product_id = get_post_meta($accomodation_id, '_woocommerce_product_id', true);
            if (empty($product_id) || get_post_status($product_id) !== 'publish') {
                $thumbnail_id = get_post_thumbnail_id($accomodation_id);
                $product_id = self::get_or_create_woocommerce_product($accomodation_id, $thumbnail_id);
            }
        } 
        return $product_id;
    }

    public static function get_main_post_id_for_translated ($post_id) {
        $main_post_id = $post_id;
        if ( function_exists( 'pll_get_post' )) {
            $default_lang = pll_default_language() ? pll_default_language() : 'en';
            $main_post_id = pll_get_post( $post_id, $default_lang ) ? pll_get_post( $post_id, $default_lang ) : $post_id ;
            
        }elseif ( function_exists( 'apply_filters' ) && function_exists( 'icl_object_id' ) ) {
            $main_post_id = apply_filters( 'wpml_original_element_id', NULL, $post_id, 'post_post' );
        }
        return $main_post_id;
    }

    public static function eshb_get_booking_statuses(){

        $statuses = array(
            'pending'   => 'Pending payment',
            'deposit-payment'   => 'Deposit payment',
            'processing' => 'Processing',
            'on-hold'    => 'On hold',
            'completed'  => 'Completed',
            'cancelled'  => 'Cancelled',
            'refunded'   => 'Refunded',
            'failed'     => 'Failed'
        );
        return apply_filters( 'eshb_booking_statuses', $statuses );

    }

    public static function eshb_get_payment_statuses(){

        $order_status = array(
            'pending'   => 'Pending',
            'processing' => 'Processing',
            'on-hold'    => 'On hold',
            'completed'  => 'Completed',
            'cancelled'  => 'Cancelled',
            'refunded'   => 'Refunded',
            'failed'     => 'Failed'
        );
        return $order_status;

    }

    public static function get_or_create_woocommerce_product($accomodation_id, $thumbnail_id) {
        
        $product_id = get_post_meta($accomodation_id, '_woocommerce_product_id', true);
        $product = (!empty($product_id) && get_post_status($product_id) === 'publish') ? wc_get_product($product_id) : false;

        if (!$product) {
            $product = new WC_Product();
            $product->set_name(get_the_title($accomodation_id));
            $product->set_price(1);
            $product->set_regular_price(1);
            $product->set_virtual(true);
            $product->set_image_id($thumbnail_id);
            $product->save();

            $product_id = $product->get_id();

            update_post_meta($accomodation_id, '_woocommerce_product_id', $product_id);
            update_post_meta($accomodation_id, '_regular_price', 1);
        }

        return $product_id;
    }

    public static function update_product_price_by_id($product_id, $new_price) {
		// Update the regular price
		update_post_meta($product_id, '_regular_price', $new_price);
		
		// Update the price (current price)
		update_post_meta($product_id, '_price', $new_price);

		// Clear WooCommerce product cache
		wc_delete_product_transients($product_id);
	}

    public static function get_current_booking_metadata($booking_id, $key) {
        if (empty($booking_id)) {
            $booking_id = get_the_ID();
        }
        if (empty($booking_id)) {
            return '';
        }
        $eshb_booking_metaboxes = get_post_meta($booking_id, 'eshb_booking_metaboxes', true);
        return isset($eshb_booking_metaboxes[$key]) ? $eshb_booking_metaboxes[$key] : '';
    }

    public static function get_current_booking_customer_metadata($booking_id, $key = '', $default_value = 'default') {
        if (empty($booking_id)) {
            $booking_id = get_the_ID();
        }

        $address = get_post_meta($booking_id, 'eshb_booking_customer_details_metaboxes', true);
        $value = $address ?? $default_value;

        if(!empty($key)){
            $value = $address[$key] ?? $default_value;
        } 
        return $value;
    }

    public static function get_current_payment_customer_metadata($id, $key) {
        if (empty($id)) {
            $id = get_the_ID();
        }
        if (empty($id)) return '';
        
        $address = get_post_meta($id, 'eshb_payment_customer_details_metaboxes', true);
        return isset($address[$key]) ? $address[$key] : 'default';
    }

    public static function eshb_get_payment_ids() {

        $args = array(
            'post_type'      => 'eshb_payment',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'post_status'    => array( 'completed' ), 
            'orderby'        => 'ID',
            'order'          => 'DESC',
        );

        $payments = get_posts($args);
        $payment_ids = [];

        foreach ($payments as $payment_id) {
            if(get_post_status( $payment_id ) == 'completed'){
                $payment_ids[$payment_id] = get_the_title($payment_id);
            }
        }

        return $payment_ids;
    }

    public static function eshb_get_booking_ids() {

        $booking_statuses = self::eshb_get_booking_statuses();
        $booking_status_keys = array_keys( $booking_statuses );

        $args = array(
            'post_type'      => 'eshb_booking',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'post_status'    => 'any'
        );

        $bookings = get_posts($args);
        $booking_ids = [];

        foreach ($bookings as $booking_id) {
            $booking_ids[$booking_id] = get_the_title($booking_id);
        }

        return $booking_ids;
    }

    public static function eshb_get_payment_gateways() {
        $gateways = [
            'manual' => 'Manual Payment',
            'cod' => 'Cash On Delivey'
        ];
        return $gateways;
    }

    public static function get_total_price_from_order_meta($order){
        $total_price = 0;
        foreach ( $order->get_items() as $item ) {
            $item_total_price = $item->get_meta('Total Price');
            if (!empty($item_total_price)) {
                $total_price += floatval($item_total_price);
            }
        }
        return $total_price;
    }

    public static function eshb_get_wc_state_city_name ($country_code, $code) {
        $states_file = WP_PLUGIN_DIR . '/woocommerce/i18n/states.php';
        $state_city_name = '';
        if ( file_exists( $states_file ) ) {
            $all_states = include $states_file; // returns array of all countries' states
            $all_states = apply_filters( 'woocommerce_states', $all_states );
            $state_city_name = isset( $all_states[$country_code][ $code ] ) ? $all_states[$country_code][ $code ] : '';
        }
        return $state_city_name;
    }

    public static function eshb_get_extra_services () {

        $services = [];

        $posts = get_posts([
            'post_type' => 'eshb_service',
            'posts_per_page' => -1
        ]);

        foreach ( $posts as $post ) {
            $services[ $post->ID ] = $post->post_title;
        }
        return $services;
    }

    // Record a payment (deposit or subsequent) against an order.
    public static function eshb_assign_payment_to_booking( $payment_id, $order_id, $booking_id, $amount_paid, $update = false, $args = [] ) {

        // error_log('payment assining' . $payment_id);

        if ( $amount_paid <= 0 ) {
            //error_log('invalid payment amount!');
            return false;
        }
    
        $eshb_settings = get_option('eshb_settings');
		$booking_type = isset($eshb_settings['booking-type']) ? $eshb_settings['booking-type'] : '';
        $eshb_booking_metaboxes = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true);
        $eshb_payment_metaboxes = get_post_meta( $payment_id, 'eshb_payment_metaboxes', true);
        $total_price = !empty($eshb_booking_metaboxes['total_price']) ? $eshb_booking_metaboxes['total_price'] : 0;
        $total_paid = !empty($eshb_booking_metaboxes['total_paid']) ? $eshb_booking_metaboxes['total_paid'] : 0;
        $due = (float) get_post_meta( $order_id, 'eshb_booking_due_amount', true );
        $booking_new_status = 'deposit-payment';

        $payment_ids = !empty($eshb_booking_metaboxes['payment_ids']) ? $eshb_booking_metaboxes['payment_ids'] : [];
        if(!in_array($payment_id, $payment_ids)){
            array_push($payment_ids, $payment_id);
        }

        if($booking_type == 'woocommerce' && class_exists( 'woocommerce' )){
            $order = wc_get_order($order_id);
            if(!$order) return;
            $total_price = $order->get_subtotal();
            $total_paid = $order->get_total();
        }

        $total_paid = 0;

        foreach ( $payment_ids as $payment_id ) {
            $metabox = get_post_meta( $payment_id, 'eshb_payment_metaboxes', true );

            if ( ! empty( $metabox['amount'] ) ) {
                $total_paid += floatval( $metabox['amount'] );
            }
        }
        
        $new_due = (float) $total_price - (float)$total_paid;
        $eshb_booking_metaboxes['payment_ids'] = $payment_ids;
        $eshb_booking_metaboxes['total_paid'] = $total_paid;

        if($new_due > 0){
            $eshb_booking_metaboxes['due_amount'] = $new_due;
            update_post_meta($order_id, 'eshb_booking_due_amount', $new_due);
        }else{

            if (!empty($eshb_settings['booking-auto-approval']) && $eshb_settings['booking-auto-approval'] == true) {
                $booking_new_status = 'completed';
            }else{
                $booking_new_status = 'processing';
            }
            delete_post_meta( $order_id, 'eshb_booking_due_amount');
        }

        $eshb_booking_metaboxes['booking_status'] = $booking_new_status;


        // update booking metabox
        update_post_meta($booking_id, 'eshb_booking_metaboxes', $eshb_booking_metaboxes);
        
        
        // update payment metabox
        if(empty($eshb_payment_metaboxes['transaction_id'])){
            $transaction_id  = 'TXN-' . str_pad( $payment_id, 8, '0', STR_PAD_LEFT );
            $eshb_payment_metaboxes['transaction_id'] = $transaction_id;
            update_post_meta($payment_id, 'eshb_payment_metaboxes', $eshb_payment_metaboxes);
        }
        
        // update order
        if($booking_type == 'woocommerce' && class_exists( 'woocommerce' )){
            // update woocommerce order
            $order = wc_get_order($order_id);
            if($order){
                $order->set_total($total_paid);
                $order->update_status($booking_new_status);
                if($new_due <= 0){
                    //$order->payment_complete();
                    $order->add_order_note('Payment received manually.');
                }
                $txn = $order->get_transaction_id();
              
                $order->save();
            }
        }else{
            // update booking status
            wp_update_post( [
                    'ID' => $booking_id,
                    'post_status' => $booking_new_status
                ]);
        }

        return $booking_id;
    }

    public static function get_eshb_default_start_end_date(){
        $today_date = gmdate('Y-m-d'); // Get today's date

        // Create a DateTime object from today's date
        $date = new DateTime($today_date);

        // Add one day
        $date->modify('+1 day');

        // Get the new date in 'Y-m-d' format
        $tomorrow_date = $date->format('Y-m-d');

        return array(
            'start_date' => $today_date,
            'end_date' => $tomorrow_date
        );
    }

    public static function get_eshb_default_start_end_time(){
        return array(
            'start_time' => '10:00',
            'end_time' => '11:00'
        );
    }

    public static function eshb_capture_payment ($order_id){
        
        if(!$order_id) return;

        $order = wc_get_order( $order_id );
        if ( !$order ) return;

        $order_status = $order->get_status();
        $due = (float) ( get_post_meta( $order_id, 'eshb_booking_due_amount', true ) ?: 0 );
        $last_payment_type = get_post_meta( $order_id, 'last_payment_type', true );
        
        if($due < 1 && $order_status == 'completed'){
		    $last_payment_type = 'full_payment';
		}
        
        if(!$last_payment_type) return;
        
        $booking_id = get_post_meta($order_id, '_booking_post_created', true);
        if(!$booking_id) return;
        
        $payment_status = get_post_meta($booking_id, 'payment_status', true);
       
        $eshb_booking_metaboxes = get_post_meta($booking_id, 'eshb_booking_metaboxes', true);
        $total_price = ESHB_Helper::get_total_price_from_order_meta($order);
        $total_paid = !empty($eshb_booking_metaboxes['total_paid']) ? $eshb_booking_metaboxes['total_paid'] : 0;

        if($payment_status == 'completed' && $total_paid > 0) {
            return;
        }

        if(in_array($last_payment_type, ['initial_deposit', 'remaining_payment']) && $total_paid >= $total_price) {
            return;
        };
    

        
        $gateway = $order->get_payment_method();
        $currency = $order->get_currency();
        $fee = 0;
        $amount = $total_paid;
        $payment_type = 'Full Payment';
        $initial_deposit = get_post_meta( $order_id, 'initial_deposit', true );
        $payment_status = 'completed';
        $new_due = 0;
        
        
        if($last_payment_type == 'initial_deposit'){
            $amount = $initial_deposit;
            $payment_type = 'Initial Deposit';
            $status = 'deposit-payment';
            $new_due = $total_price - $initial_deposit;
            $total_paid = $initial_deposit;
        }elseif($last_payment_type == 'remaining_payment'){
            $amount = $due;
            $total_paid = $total_price;
            $payment_type = 'Remaining Payment';
        }else{
            $amount = $total_price;
            $total_paid = $total_price;
            $payment_type = 'Full Payment';
        }

        // create payment options
        $payment_options = [
            'booking_id' => $booking_id,
            'transaction_id' => '',
            'gateway' => $gateway,
            'gateway_mode' => 'live',
            'amount' => $amount,
            'fee' => $fee,
            'currency' => $currency,
            'payment_type' => $payment_type,
        ];


        // load customer details from order
        $first_name = $order->get_billing_first_name();
        $last_name = $order->get_billing_last_name();
        $email = $order->get_billing_email();
        $phone = $order->get_billing_phone();
        $country = $order->get_billing_country();
        $state = $order->get_billing_state();
        $city = $order->get_billing_city();
        $address_1 = $order->get_billing_address_1();
        $address_2 = $order->get_billing_address_2();
        $postcode = $order->get_billing_postcode();

        // create customer details
        $customer_details = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'country' => $country,
            'state' => $state,
            'city' => $city,
            'address_1' => $address_1,
            'address_2' => $address_2,
            'postcode' => $postcode,
        ];

        $post_title = 'Payment for Booking #' . $booking_id;

        // insert payment to eshb_payment posttype
        $payment_id = wp_insert_post( 
            [
                'post_title' => $post_title,
                'post_type' => 'eshb_payment',
                'post_status' => $payment_status, 
            ]
        );

        // update payment metadata if payment success
        if($payment_id){
            $transaction_id = 'TXN-' . str_pad( $payment_id, 8, '0', STR_PAD_LEFT );
            $payment_options['transaction_id'] = $transaction_id;
            update_post_meta($payment_id, 'eshb_payment_metaboxes', $payment_options);
            update_post_meta($payment_id, 'eshb_payment_customer_details_metaboxes', $customer_details);
            update_post_meta($booking_id, 'eshb_booking_customer_details_metaboxes', $customer_details);

            // update booking metaboxes
            $payment_ids = !empty($eshb_booking_metaboxes['payment_ids']) ? $eshb_booking_metaboxes['payment_ids'] : [];
            if(!in_array($payment_id, $payment_ids)){
                array_push($payment_ids, $payment_id);
            }

           
            $eshb_booking_metaboxes['payment_ids'] = $payment_ids;
            $eshb_booking_metaboxes['total_paid'] = $total_paid;
            
            
            // update due meta 
            if($last_payment_type == 'initial_deposit'){
                $eshb_booking_metaboxes['due_amount'] = $new_due;
                update_post_meta($order_id, 'eshb_booking_due_amount', $new_due);
                update_post_meta($order_id, 'last_payment_type', $last_payment_type);
            }elseif($last_payment_type == 'remaining_payment'){
                delete_post_meta( $order_id, 'eshb_booking_due_amount');
                delete_post_meta( $order_id, 'last_payment_type');
            }
            
            if($new_due < 1 || !$new_due){
                update_post_meta($booking_id, 'payment_status', 'completed');
            }

            // update booking
            update_post_meta($booking_id, 'eshb_booking_metaboxes', $eshb_booking_metaboxes);


            // allow other plugins to hook after capture payment
            do_action('eshb_after_capture_payment', $order_id);


            // delete last payment type
            delete_post_meta( $order_id, 'last_payment_type');
            delete_post_meta($order_id, 'last_requested_payment_amount');
        }
        return $payment_id;

    }

    public static function eshb_calculate_time_diff ($start_date, $end_date, $start_time, $end_time) {
        // Require times; if missing return 0 hours
        if (empty($start_time) || empty($end_time)) {
            return 0;
        }

        // Default end date to start date when empty
        $start_date = !empty($start_date) ? $start_date : gmdate('Y-m-d');
        $end_date = !empty($end_date) ? $end_date : $start_date;

        // Normalize special case where end time can be provided as 24:00
        $is_end_24 = ($end_time === '24:00' || $end_time === '24:00:00');
        $normalized_end_time = $is_end_24 ? '00:00' : $end_time;

        $start_dt = DateTime::createFromFormat('Y-m-d H:i:s', $start_date . ' ' . $start_time);
        if(!$start_dt){
            $start_dt = DateTime::createFromFormat('Y-m-d H:i', $start_date . ' ' . $start_time);
        }

        $end_dt = DateTime::createFromFormat('Y-m-d H:i:s', $end_date . ' ' . $normalized_end_time);
        if(!$end_dt){
            $end_dt = DateTime::createFromFormat('Y-m-d H:i', $end_date . ' ' . $normalized_end_time);
        }

        if(!$start_dt || !$end_dt){
            return 0;
        }

        // If the textual times are equal (ignoring seconds) and not a 24:00 case, treat as 0 hours for same-day
        if(!$is_end_24 && $start_dt->format('H:i') === $end_dt->format('H:i') && $end_date === $start_date){
            return 0;
        }

        // If end is not after start, or explicitly 24:00, assume it crosses midnight to the next day
        if($is_end_24 || $end_dt <= $start_dt){
            $end_dt->modify('+1 day');
        }

        $duration_seconds = $end_dt->getTimestamp() - $start_dt->getTimestamp();
        $hours_count = (int) ceil($duration_seconds / 3600);

        return max(0, (int) $hours_count);
    }

    public static function get_available_times_by_date($accommodation_id, $date, $excludes = []) {
        // Get booking settings (working hours)
        $settings   = get_option('eshb_single_day_settings', []);
        $start_time = !empty($settings['start_time']) ? $settings['start_time'] : '10:00';
        $end_time   = !empty($settings['end_time']) ? $settings['end_time'] : '22:00';
    
        $date_str = gmdate('Y-m-d', strtotime($date));
    
        // Fetch all bookings
        $bookings = get_posts([
            'post_type'      => 'eshb_booking',
            'posts_per_page' => -1,
            'post_status'    => ['publish','completed','processing','deposit-payment','pending'],
            'fields'         => 'ids',
        ]);
    
        $booked_slots = [];
        $slot_keys    = []; // Track unique slot keys
    
        foreach ($bookings as $booking_id) {
            $meta = get_post_meta($booking_id, 'eshb_booking_metaboxes', true);
            if (!is_array($meta)) continue;
    
            // Skip if accommodation does not match
            if ((int)($meta['booking_accomodation_id'] ?? 0) !== (int)$accommodation_id) continue;
    
            // Skip if booking date does not match
            if (($meta['booking_start_date'] ?? '') !== $date_str) continue;
    
            $b_start = $meta['booking_start_time'] ?? '';
            $b_end   = $meta['booking_end_time']   ?? '';

            // skip if invalid
            if (!$b_start || !$b_end) continue;

            $slot_key = $b_start.'-'.$b_end;
    
            // Add unique slot only once
            if (!isset($slot_keys[$slot_key])) {
                $slot_keys[$slot_key] = true;
                
                $booked_slots[] = [$b_start, $b_end];
            }
        }
    
        // Build available slots based on working window
        $available_slots = [];

        // skip for excludes
        if (count($excludes) > 0) {
            array_push($available_slots, $excludes);
        }

        $window_start_ts = strtotime("$date_str $start_time");
        $window_end_ts   = strtotime("$date_str $end_time");
    
        if (empty($booked_slots)) {
            // If no bookings, full window is available
            $available_slots[] = [$start_time, $end_time];
        } else {
            // Sort booked slots by start time
            usort($booked_slots, function($a, $b) use ($date_str) {
                return strtotime("$date_str {$a[0]}") <=> strtotime("$date_str {$b[0]}");
            });
    
            $pointer = $window_start_ts;
    
            foreach ($booked_slots as $slot) {
                $slot_start_ts = strtotime("$date_str {$slot[0]}");
                $slot_end_ts   = strtotime("$date_str {$slot[1]}");
    
                // Gap before the booking = available slot
                if ($slot_start_ts > $pointer) {
                    $available_slots[] = [gmdate('H:i', $pointer), $slot[0]];
                }
    
                // Move pointer to end of current booked slot
                $pointer = max($pointer, $slot_end_ts);
            }
    
            // After last booking, remaining time is available
            if ($pointer < $window_end_ts) {
                $available_slots[] = [gmdate('H:i', $pointer), $end_time];
            }
        }
    
        return [
            'booked_slots'    => $booked_slots,
            'available_slots' => $available_slots,
            'next_start_time' => $available_slots[0][0] ?? '',
            'next_end_time'   => $available_slots[0][1] ?? '',
        ];
    }

    public static function format_to_wp_time($time_string){
        $timestamp = strtotime( $time_string );
        return date_i18n( get_option('time_format'), $timestamp );
    }

    public static function eshb_set_accomodation_localize($accomodation_id = null) {
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
        $eshb_accomodation_metaboxes = false;
        $is_it_single_day_booking_accomodation = false;

        if(is_singular( 'eshb_accomodation' ) && ($accomodation_id == null || empty($accomodation_id))){
            $accomodation_id = get_the_ID();
        }

        
        $eshb_booking = new ESHB_Booking();
        $start_date = '';
        $end_date = '';
        
        if (isset($_GET['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
            $start_date = !empty($_POST['start_date']) ? sanitize_text_field( wp_unslash($_POST['start_date']) ) : '';
            $end_date = !empty($_POST['end_date']) ? sanitize_text_field( wp_unslash($_POST['end_date']) ) : '';
        }

        if($accomodation_id) {
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
                    'eshb_add_to_cart_reservation_nonce' => wp_create_nonce('eshb_eshb_add_to_cart_reservation_nonce'),
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
    }
}