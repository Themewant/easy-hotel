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

            return $post_id;

        }else{
            $error_message = $post_id->get_error_message();
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
            $thumbnail_id = get_post_thumbnail_id($accomodation_id);
            $product_id = self::get_or_create_woocommerce_product($accomodation_id, $thumbnail_id);
        } 
        return $product_id;
    }

    public static function get_main_post_id_for_translated ($post_id) {
        $main_post_id = $post_id;
        if ( function_exists( 'pll_get_post' )) {
            $default_lang = pll_default_language() ? pll_default_language() : 'en';
            $main_post_id = pll_get_post( $post_id, $default_lang ) ? pll_get_post( $post_id, $default_lang ) : $post_id ;
            
        }elseif ( function_exists( 'apply_filters' ) && function_exists( 'icl_object_id' ) ) {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Third-party filter provided by WPML, name cannot be prefixed.
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
           
            $value = ( isset($address[$key]) && '' !== $address[$key] ) ? $address[$key] : $default_value;
        }
        return $value;
    }

    public static function get_current_payment_customer_metadata($id, $key) {
        if (empty($id)) {
            $id = get_the_ID();
        }
        if (empty($id)) return '';
        
        $address = get_post_meta($id, 'eshb_payment_customer_details_metaboxes', true);
        return ( isset($address[$key]) && '' !== $address[$key] ) ? $address[$key] : 'default';
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
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Third-party filter provided by WooCommerce, name cannot be prefixed.
            $all_states = apply_filters( 'woocommerce_states', $all_states );
            $state_city_name = isset( $all_states[$country_code][ $code ] ) ? $all_states[$country_code][ $code ] : '';
        }
        return $state_city_name;
    }

    /**
     * Whether a country offers any state to pick.
     *
     * Reads the same countries.json the admin dropdown is built from, so PHP and
     * JS never disagree about which countries are stateless. 52 of the 250 entries
     * — American Samoa, Bermuda, Gibraltar and the like — ship an empty "states"
     * list, and their State dropdown renders with nothing but its placeholder.
     *
     * @param string $country_code Two letter code, e.g. "AS".
     * @return bool False only when the country is known and has no states.
     */
    public static function eshb_country_has_states ( $country_code ) {

        static $countries = null;

        $country_code = trim( (string) $country_code );

        if ( '' === $country_code ) {
            return false;
        }

        if ( null === $countries ) {

            $countries      = array();
            $countries_file = ESHB_PL_PATH . 'public/assets/lib/countries.json';

            if ( file_exists( $countries_file ) ) {

                $decoded = json_decode( file_get_contents( $countries_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin asset.

                if ( is_array( $decoded ) ) {
                    foreach ( $decoded as $country ) {
                        if ( ! empty( $country['code2'] ) ) {
                            $countries[ trim( $country['code2'] ) ] = ! empty( $country['states'] );
                        }
                    }
                }

            }

        }

        if ( ! isset( $countries[ $country_code ] ) ) {
            return true;
        }

        return $countries[ $country_code ];

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

        if ( $amount_paid <= 0 ) {
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

    /**
     * Splits a PHP date() format into its parts, honouring the backslash escape.
     *
     * Locales lean on that escape: Spanish stores "j \d\e F \d\e Y", where the
     * "\d\e" pairs are the literal word "de" and not the day/timezone tokens.
     * Anything that rewrites the format one character at a time (str_replace or
     * strtr) turns that word back into tokens, which is how "4 de agosto de
     * 2026" reached the booking calendar as "4 DDe agosto DDe 2026".
     *
     * @param  string $format PHP date() format.
     * @return array  List of [ character, is_literal ] pairs.
     */
    protected static function eshb_split_date_format( $format ) {

        $parts  = array();
        $format = (string) $format;
        $length = strlen( $format );

        for ( $i = 0; $i < $length; $i++ ) {

            if ( '\\' === $format[ $i ] && $i + 1 < $length ) {
                $i++;
                $parts[] = array( $format[ $i ], true );
                continue;
            }

            $parts[] = array( $format[ $i ], false );
        }

        return $parts;
    }

    /**
     * Wraps a run of literal text for moment.js.
     *
     * Pure punctuation — "/", "-", ", " — means nothing to moment and reads
     * better raw; anything else is bracketed so no word inside the format can
     * ever be mistaken for a token.
     *
     * @param  string $text
     * @return string
     */
    protected static function eshb_moment_literal( $text ) {

        if ( '' === $text ) {
            return '';
        }

        // Brackets are moment's own literal delimiters, so they cannot sit
        // inside one.
        $text = str_replace( array( '[', ']' ), '', $text );

        if ( '' === $text || ! preg_match( '/[^\s\/\-.,:;()]/u', $text ) ) {
            return $text;
        }

        return '[' . $text . ']';
    }

    /**
     * Converts a PHP date() format into a moment.js format.
     *
     * Escaped characters — and anything moment reads as a token but PHP does
     * not define — come back as moment literals, so translated words inside the
     * site date format survive the trip to JavaScript intact.
     *
     * @param  string $format PHP date() format, defaults to Settings > General.
     * @return string
     */
    public static function eshb_date_format_to_moment( $format = '' ) {

        $format = '' !== (string) $format ? $format : get_option( 'date_format', 'F j, Y' );

        $tokens = array(
            'd' => 'DD',   'j' => 'D',    'D' => 'ddd',  'l' => 'dddd',
            'N' => 'E',    'w' => 'd',    'z' => 'DDD',  'W' => 'W',
            'F' => 'MMMM', 'm' => 'MM',   'M' => 'MMM',  'n' => 'M',
            'o' => 'GGGG', 'Y' => 'YYYY', 'y' => 'YY',
            'a' => 'a',    'A' => 'A',    'g' => 'h',    'G' => 'H',
            'h' => 'hh',   'H' => 'HH',   'i' => 'mm',   's' => 'ss',
            'v' => 'SSS',  'u' => 'SSSSSS',
            'O' => 'ZZ',   'P' => 'Z',    'p' => 'Z',    'T' => 'z',
            'U' => 'X',
        );

        $moment  = '';
        $literal = '';

        foreach ( self::eshb_split_date_format( $format ) as $part ) {

            list( $char, $is_literal ) = $part;

            // "S" is the English ordinal suffix. moment folds it into the day
            // token, so "jS" has to come out as "Do" rather than "D" plus junk.
            if ( ! $is_literal && 'S' === $char ) {
                if ( '' === $literal && 'D' === substr( $moment, -1 ) ) {
                    $moment = rtrim( $moment, 'D' ) . 'Do';
                }
                continue;
            }

            if ( ! $is_literal && isset( $tokens[ $char ] ) ) {
                $moment .= self::eshb_moment_literal( $literal ) . $tokens[ $char ];
                $literal = '';
                continue;
            }

            $literal .= $char;
        }

        return $moment . self::eshb_moment_literal( $literal );
    }

    /**
     * A shape hint for the site date format — "DD/MM/YYYY" — shown as the date
     * field placeholder while the calendar is open.
     *
     * Literal text is left as written, so a Spanish site reads "DD de MM de
     * YYYY" instead of the mangled "DD \DD\e MM \DD\e YYYY".
     *
     * @param  string $format PHP date() format, defaults to Settings > General.
     * @return string
     */
    public static function eshb_date_format_hint( $format = '' ) {

        $format = '' !== (string) $format ? $format : get_option( 'date_format', 'm/d/Y' );

        $tokens = array(
            'd' => 'DD', 'j' => 'DD', 'l' => 'DD', 'D' => 'DD', 'N' => 'DD', 'w' => 'DD',
            'm' => 'MM', 'n' => 'MM', 'F' => 'MM', 'M' => 'MM',
            'Y' => 'YYYY', 'y' => 'YY', 'S' => '',
        );

        $hint = '';

        foreach ( self::eshb_split_date_format( $format ) as $part ) {

            list( $char, $is_literal ) = $part;

            $hint .= ( ! $is_literal && isset( $tokens[ $char ] ) ) ? $tokens[ $char ] : $char;
        }

        return $hint;
    }

    /**
     * Today's date in the site timezone.
     *
     * gmdate() answers in UTC, so between local midnight and UTC midnight the
     * booking form offered yesterday as the default check-in: a Madrid site
     * (UTC+2) shows the 3rd to anyone opening the form after 22:00 on the 3rd.
     *
     * @param  string $format
     * @return string
     */
    public static function eshb_today( $format = 'Y-m-d' ) {
        return current_time( $format );
    }

    /**
     * Reads a stored string that carries no timezone of its own — a Y-m-d
     * booking date, a "14:00" check-in time, a current_time('mysql') stamp — as
     * a moment in the site timezone.
     *
     * strtotime() reads such a string as UTC, because WordPress pins PHP's
     * default timezone there, and date_i18n()/wp_date() then convert it into
     * the site timezone. That round trip moves the value by the site offset:
     * a booking date lands on the day before on any site behind UTC, a 2:00 pm
     * check-in prints as 4:00 pm in Madrid, and a cancellation stamp written by
     * current_time() is shifted twice.
     *
     * @param  string $value
     * @return int|false Timestamp, or false when the string is unreadable.
     */
    protected static function eshb_local_timestamp( $value ) {

        $value = trim( (string) $value );

        if ( '' === $value ) {
            return false;
        }

        // A bare calendar date is anchored at midday, so no offset — and no DST
        // jump — can push it onto a neighbouring day.
        if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
            $value .= ' 12:00:00';
        }

        try {
            $moment = new DateTime( $value, wp_timezone() );
        } catch ( Exception $e ) {
            return false;
        }

        return $moment->getTimestamp();
    }

    /**
     * Renders a stored booking date with the site date format.
     *
     * @param  string $date   Date in Y-m-d, or anything DateTime accepts.
     * @param  string $format PHP date() format, defaults to Settings > General.
     * @return string
     */
    public static function eshb_format_date( $date, $format = '' ) {

        $timestamp = self::eshb_local_timestamp( $date );

        if ( false === $timestamp ) {
            return '';
        }

        return wp_date( '' !== (string) $format ? $format : get_option( 'date_format' ), $timestamp );
    }

    /**
     * Renders a wall clock time — a check-in or slot time such as "14:00" —
     * with the site time format.
     *
     * The stored value is what the hotel typed, not an instant in time, so it
     * has to come back out unchanged rather than shifted by the site offset.
     *
     * @param  string $time
     * @param  string $format PHP date() format, defaults to Settings > General.
     * @return string
     */
    public static function eshb_format_time( $time, $format = '' ) {

        $timestamp = self::eshb_local_timestamp( $time );

        if ( false === $timestamp ) {
            return '';
        }

        return wp_date( '' !== (string) $format ? $format : get_option( 'time_format' ), $timestamp );
    }

    /**
     * Renders a stamp written by current_time( 'mysql' ) — already site local —
     * with the site date and time formats.
     *
     * @param  string $value
     * @param  string $format PHP date() format, defaults to Settings > General.
     * @return string
     */
    public static function eshb_format_datetime( $value, $format = '' ) {

        $timestamp = self::eshb_local_timestamp( $value );

        if ( false === $timestamp ) {
            return '';
        }

        if ( '' === (string) $format ) {
            $format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
        }

        return wp_date( $format, $timestamp );
    }

    public static function get_eshb_default_start_end_date(){
        $today_date = self::eshb_today(); // Today in the site timezone, not UTC

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

        if(!$is_end_24 && $start_dt->format('H:i') === $end_dt->format('H:i') && $end_date === $start_date){
            return 0;
        }

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
    
               
                if ($slot_start_ts > $pointer) {
                    $available_slots[] = [gmdate('H:i', $pointer), $slot[0]];
                }
    
                $pointer = max($pointer, $slot_end_ts);
            }
    
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
        return self::eshb_format_time( $time_string );
    }

    public static function eshb_set_accomodation_localize($accomodation_id = null) {
        $cart_url = site_url('/cart');
        $checkout_url = site_url('/checkout');
        if(class_exists('woocommerce')) {
            $cart_url     = wc_get_cart_url();
            $checkout_url = wc_get_checkout_url();
        }
        
        $min_max_settings = [
            'calendar_start_date_buffer' => 0,
            'required_min_nights' => 1,
            'required_max_nights' => 999,
        ];

        $eshb_settings = get_option('eshb_settings');
        $search_capacity_settings = [];
        if(!empty($eshb_settings)){
            $search_capacity_settings = [
                'min_adult_quantity' => !empty($eshb_settings['min-adult-capacity']) ? $eshb_settings['min-adult-capacity'] : 1,
                'min_children_quantity' => !empty($eshb_settings['min-children-capacity']) ? $eshb_settings['min-children-capacity'] : 0,
                'max_adult_quantity' => !empty($eshb_settings['max-adult-capacity']) ? $eshb_settings['max-adult-capacity'] : 1000,
                'max_children_quantity' => !empty($eshb_settings['max-children-capacity']) ? $eshb_settings['max-children-capacity'] : 1000,
            ];
        }
        $booking_capacity_settings = [];
        if(!empty($eshb_settings)){
            $booking_capacity_settings = [
                'min_adult_quantity' => !empty($eshb_settings['booking-min-adult-capacity']) ? $eshb_settings['booking-min-adult-capacity'] : 1,
                'min_children_quantity' => !empty($eshb_settings['booking-min-children-capacity']) ? $eshb_settings['booking-min-children-capacity'] : 0,
            ];
        }

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


            $accomodation_min_max_settings = apply_filters( 'eshb_min_max_settings', [
                'required_min_nights'          => $required_min_nights,
                'required_max_nights'          => $required_max_nights,
                'is_global_source_for_min_max' => true,
            ], $accomodation_id, $eshb_accomodation_metaboxes );

            $required_min_nights = !empty($accomodation_min_max_settings['required_min_nights']) ? $accomodation_min_max_settings['required_min_nights'] : 1;
            $required_max_nights = !empty($accomodation_min_max_settings['required_max_nights']) ? $accomodation_min_max_settings['required_max_nights'] : 999;
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

        $eshb_translations = apply_filters( 'eshb_booking_action_messages', $eshb_translations, [$accomodation_id, $eshb_settings] );
        $show_count = apply_filters( 'eshb_booking_capacity_count_show', true, [$accomodation_id, $eshb_settings] );

        $nonce_action = ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action');
        wp_localize_script(
            'eshb-public-script', 
            'eshb_ajax',
                [
                    'root'  => esc_url(rest_url()),
                    'ajaxurl'          => admin_url( 'admin-ajax.php' ),
                    'adminURL'         => admin_url(),
                    'wooCartUrl'       => $cart_url,
                    'wooCheckoutUrl'   => $checkout_url,
                    'direct_booking'   => !empty($eshb_settings['direct-booking']),
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
                    'search_capacities' => $search_capacity_settings,
                    'booking_capacities' => $booking_capacity_settings,
                    'booking_capacity_count_show' => $show_count,
                    'translations'              => $eshb_translations,
                    'cart_blocking_enabled'     => ! empty( $eshb_settings['cart-blocking-switcher'] ),
                    'cart_blocking_time'        => ! empty( $eshb_settings['cart-blocking-time'] ) ? (int) $eshb_settings['cart-blocking-time'] : 5,
                    'cart_blocking_color'       => ! empty( $eshb_settings['cart-blocking-color'] ) ? esc_attr( $eshb_settings['cart-blocking-color'] ) : '#720eec',
                    'cart_blocking_notice_msg'  => ! empty( $eshb_settings['cart-blocking-notice-msg'] ) ? esc_html( $eshb_settings['cart-blocking-notice-msg'] ) : esc_html__( 'Your reservation is held for', 'easy-hotel' ),
                ]
        );
    }

    /**
     * Every session (season) that carries a "Minimum Nights" rule for this accomodation.
     *
     * Returned in a shape the booking calendar can evaluate client side, so the rule
     * is known the moment a date is clicked instead of only after the range has
     * already been applied.
     *
     * @param  int|string $accomodation_id
     * @return array List of ['start_date','end_date','days','min_nights'].
     */
    public static function get_eshb_session_min_nights_rules($accomodation_id) {

        $all_week = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        $query = new WP_Query( array(
            'post_type'      => 'eshb_session',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ) );

        $rules = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id   = get_the_ID();
                $metaboxes = maybe_unserialize(get_post_meta($post_id, 'eshb_session_metaboxes', true));

                if (empty($metaboxes['start_date']) || empty($metaboxes['end_date'])) continue;

                $min_nights = !empty($metaboxes['min_nights']) ? (int) $metaboxes['min_nights'] : 0;
                if ($min_nights < 1) continue;

                $accomodation_ids = $metaboxes['accomodation_ids'] ?? [];
                if (empty($accomodation_ids) || !in_array($accomodation_id, $accomodation_ids)) continue;

                $session_days = !empty($metaboxes['days']) ? $metaboxes['days'] : $all_week;
                if (in_array('all', (array) $session_days)) {
                    $session_days = $all_week;
                }

                $rules[] = [
                    'start_date' => $metaboxes['start_date'],
                    'end_date'   => $metaboxes['end_date'],
                    'days'       => array_values( (array) $session_days ),
                    'min_nights' => $min_nights,
                ];
            }
            wp_reset_postdata();
        }

        return $rules;
    }

    /**
     * Highest "Minimum Nights" any session imposes on the given stay.
     *
     * Every night of the stay is checked (the check-out day is excluded, it is not a
     * night) and the LARGEST matching value wins — with overlapping seasons the
     * strictest rule has to be the one that applies.
     *
     * @return int 0 when no session covers the stay.
     */
    public static function get_eshb_min_stay_night_by_session($accomodation_id, $start_date, $end_date) {

       
        if (empty($start_date) || empty($end_date)) {
            return 0;
        }

        $rules = self::get_eshb_session_min_nights_rules($accomodation_id);

        if (empty($rules)) {
            return 0;
        }

        // Create date range (exclude checkout date if multi-day)
        $period = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            ($start_date === $end_date)
                ? (new DateTime($end_date))->modify('+1 day') // same-day booking
                : new DateTime($end_date) // exclude checkout
        );

        $min_stay_night = 0;

        foreach ($period as $day) {
            $current_date     = $day->format('Y-m-d');
            $current_day_name = strtolower($day->format('l'));

            foreach ($rules as $rule) {
                if ($current_date >= $rule['start_date'] && $current_date <= $rule['end_date'] && in_array($current_day_name, $rule['days'])) {
                    $min_stay_night = max($min_stay_night, $rule['min_nights']);
                }
            }
        }

        return $min_stay_night;
    }
}