<?php
use SureCart\Support\Currency;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class ESHB_Core {

    private static $_instance = null;
	
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

    public function __construct() {}

    public function eshb_send_html_email($to, $subject, $message, $from_name = 'reactheme.com/easyhotel', $from_email = 'rubel.reacthemes@gmail.com') {
        
        require_once( trailingslashit( ABSPATH ) .'wp-load.php' );

        if (!function_exists('wp_mail')) {
            //error_log('Error: wp_mail() function is missing!');
            return false;
        }

        function eshb_html_email_filter_calback(){
            return 'text/html';
        }

        // Set content type to HTML
        add_filter('wp_mail_content_type', 'eshb_html_email_filter_calback');
    
        // Set email headers
        $headers = array();
        $headers[] = "From: {$from_name} <{$from_email}>";
        $headers[] = "Reply-To: {$from_email}";
    
        // Send email
        $sent = wp_mail($to, $subject, $message, $headers);
    
        // Remove filter to avoid conflicts
        remove_filter('wp_mail_content_type', 'eshb_html_email_filter_calback');
        

        if (!$sent) {
            //error_log('Email sending failed: ' . print_r(error_get_last(), true));
        }
    
        //return $sent; // Returns true if sent, false if failed
        return $sent ? 'Email Sent!' : 'Failed to Send!';
    }

    public function get_eshb_currency_symbol(){

        $eshb_settings = get_option( 'eshb_settings' ,[]);
        $booking_type = isset($eshb_settings['booking-type']) ? $eshb_settings['booking-type'] : '';

        if( !empty($eshb_settings['currency_symbol']) ){
            $currency_symbol = $eshb_settings['currency_symbol'];
        }else if($booking_type == 'woocommerce' && class_exists('woocommerce')){
            $currency_symbol = get_woocommerce_currency_symbol();
        }else if($booking_type == 'surecart' && class_exists('SureCart')){
            $currency = new Currency();
            $currency_symbol = $currency->getCurrencySymbol();
        }else {
            $currency_symbol = '$';
        }

        $currency_symbol = apply_filters('eshb_currency_symbol', $currency_symbol, $booking_type);

        return $currency_symbol;
    }

    public function get_eshb_currency_position(){

        $currency_position = 'left';
        
        $eshb_settings = get_option( 'eshb_settings' ,[]);
        if(isset($eshb_settings['currency_position']) && !empty($eshb_settings['currency_position'])){
            $currency_position = $eshb_settings['currency_position'];
        }

        return $currency_position;
    }

    public function get_eshb_default_start_end_date(){
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


    public function has_upcoming_or_current_session_price($accomodation_id, $start_date, $end_date, $night = 1, $adult = 1, $children = '') {
        $metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
        $regular_price = !empty($metaboxes['regular_price']) ? $metaboxes['regular_price'] : 0;

        // Get all sessions
        $qargs = array(
            'post_type'      => 'eshb_session',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($qargs);

        $sessions = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $metaboxes = maybe_unserialize(get_post_meta($post_id, 'eshb_session_metaboxes', true));

                if (empty($metaboxes['start_date']) || empty($metaboxes['end_date'])) continue;

                $sessions[] = [
                    'id'                 => $post_id,
                    'start_date'         => $metaboxes['start_date'],
                    'end_date'           => $metaboxes['end_date'],
                    'price'              => $metaboxes['session_price'] ?? $regular_price,
                    'accomodation_ids'   => $metaboxes['accomodation_ids'] ?? [],
                    'longstay_pricing'   => $metaboxes['longstay_pricing'] ?? [],
                    'variable_pricing'   => $metaboxes['variable_pricing'] ?? [],
                    'days'               => $metaboxes['days'] ?? [],
                ];
            }
            wp_reset_postdata();
        }

        // Create date range (exclude checkout date if multi-day)
        $period = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            (new DateTime($start_date) == new DateTime($end_date))
                ? (new DateTime($end_date))->modify('+1 day')
                : new DateTime($end_date)
        );

        $total_nights = iterator_count($period);

        // Initialize flags
        $longstay_applied = false;
        $variable_applied = false;
        $session_price_applied = false;
        
        foreach ($period as $day) {
            $current_date = $day->format('Y-m-d');
            $current_day_name = strtolower($day->format('l'));
            foreach ($sessions as $session) {

                if(empty($session['accomodation_ids'])){
                    continue;
                }

                if (!empty($session['accomodation_ids']) && !in_array($accomodation_id, $session['accomodation_ids'])) {
                    continue;
                }
                $session_days = !empty($session['days']) ? $session['days'] : ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                if(in_array('all', $session['days'])){
                    $session_days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                }
                if ($current_date >= $session['start_date'] && $current_date <= $session['end_date'] && in_array($current_day_name, $session_days)) {
                 
                    // 1. Longstay Pricing
                    if (!empty($session['longstay_pricing']) && $total_nights >= 1) {
                        // reverse the longstay_pricing array
                        $session['longstay_pricing'] = array_reverse($session['longstay_pricing']);
                        foreach ($session['longstay_pricing'] as $ls) {
                            if (!empty($ls['night']) && $total_nights >= $ls['night']) {
                                $longstay_applied = true;
                                break 2; // early return since found
                            }
                        }
                    }

                    // 2. Variable Pricing
                    if (!empty($session['variable_pricing'])) {
                        foreach ($session['variable_pricing'] as $vp) {
                            $vp_adult = $vp['adult_quantity'] ?? 0;
                            $vp_children = $vp['children_quantity'] ?? 0;
                            if ($vp_adult == $adult && $vp_children == $children) {
                                $variable_applied = true;
                                break 2;
                            }
                        }
                    }

                    // 3. Session Price (basic price)
                    if (!empty($session['price']) && $session['price'] != $regular_price) {
                        $session_price_applied = true;
                        break 2;
                    }
                }
            }
        }

        if ($longstay_applied || $variable_applied || $session_price_applied) {
            return true;
        }

        return false;
    }

    public function get_eshb_price_by_session($accomodation_id, $start_date, $end_date, $night = '', $adult = '', $children = '') {
    
        $metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
        $regular_price = !empty($metaboxes['regular_price']) ? $metaboxes['regular_price'] : 0;
       

        // Get all sessions
        $qargs = array(
            'post_type'      => 'eshb_session',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($qargs);

        $sessions = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $metaboxes = maybe_unserialize(get_post_meta($post_id, 'eshb_session_metaboxes', true));

                if (empty($metaboxes['start_date']) || empty($metaboxes['end_date'])) continue;

                $sessions[] = [
                    'id'                 => $post_id,
                    'start_date'         => $metaboxes['start_date'],
                    'end_date'           => $metaboxes['end_date'],
                    'price'              => $metaboxes['session_price'] ?? $regular_price,
                    'accomodation_ids'   => $metaboxes['accomodation_ids'] ?? [],
                    'longstay_pricing'   => $metaboxes['longstay_pricing'] ?? [],
                    'variable_pricing'   => $metaboxes['variable_pricing'] ?? [],
                    'days'               => $metaboxes['days'] ?? [],
                ];
            }
            wp_reset_postdata();
        }

        // Create date range (exclude checkout date if multi-day)
        $period = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            (new DateTime($start_date) == new DateTime($end_date))
                ? (new DateTime($end_date))->modify('+1 day') // same-day booking
                : new DateTime($end_date) // exclude checkout
        );

        $total_nights = iterator_count($period);
        $per_day_prices = [];


        foreach ($period as $day) {
            $current_date = $day->format('Y-m-d');
            $current_day_name = strtolower($day->format('l'));
            $matched_price = $regular_price;

            foreach ($sessions as $session) {


                if(empty($session['accomodation_ids'])){
                    continue;
                }

                if (!empty($session['accomodation_ids']) && !in_array($accomodation_id, $session['accomodation_ids'])) {
                    continue;
                }

                $session_days = !empty($session['days']) ? $session['days'] : ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                if(in_array('all', $session_days)){
                    $session_days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                }

                if ($current_date >= $session['start_date'] && $current_date <= $session['end_date'] && in_array($current_day_name, $session_days)) {
         
                    // 1. Longstay Pricing (highest priority)
                    $longstay_applied = false;
                    
                    
                    if (!empty($session['longstay_pricing']) && $total_nights >= 1) {
                        $session['longstay_pricing'] = array_reverse($session['longstay_pricing']);
                        foreach ($session['longstay_pricing'] as $ls) {
                            if (!empty($ls['night']) && $total_nights >= $ls['night']) {
                                $matched_price = floatval($ls['price']);
                                $longstay_applied = true;
                                break;
                            }
                        }
                  
                    }

                    if ($longstay_applied) break;

                    // 2. Variable Pricing
                    $variable_applied = false;
                    if (!empty($session['variable_pricing'])) {
                        foreach ($session['variable_pricing'] as $vp) {
                            $vp_adult = $vp['adult_quantity'] ?? 0;
                            $vp_children = $vp['children_quantity'] ?? 0;
                            if ($vp_adult == $adult && $vp_children == $children) {
                                $matched_price = floatval($vp['price']);
                                $variable_applied = true;
                                break;
                            }
                        }
                    }

                    if ($variable_applied) break;

                    // 3. Session price
                    $matched_price = floatval($session['price']);
                    break;
                }
            }

            
            

            $per_day_prices[] = $matched_price;
        }

        $total_price = array_sum($per_day_prices);
        $total_price = apply_filters('eshb_session_price', $total_price);
        return $total_price;
    }

    public function get_eshb_price($start_date = null, $end_date = null, $accomodation_id = null, $session_price = true, $night = '', $adult = '', $children = '', $sale = true, $single_day = true) {

        if($start_date == null || empty($start_date) || $end_date == null || empty($end_date)){
            $dates = $this->get_eshb_default_start_end_date();
            $start_date = $dates['start_date'];
            $end_date = $dates['end_date'];
        }

    
        if( $accomodation_id == null || empty($accomodation_id) ){
            global $post;
            $accomodation_id = $post->ID;
        }
        $price = '';
        $metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);

        $regular_price = !empty($metaboxes['regular_price']) ? $metaboxes['regular_price'] : 0;
        $sale_price = !empty($metaboxes['sale_price']) ? $metaboxes['sale_price'] : 0;

        if($single_day){
            $single_day_price = apply_filters( 'eshb_single_day_price', 0, $accomodation_id);
            $eshb_single_day_sale_price = apply_filters( 'eshb_single_day_sale_price', 0, $accomodation_id);
            $regular_price = !empty($single_day_price) ? $single_day_price : $regular_price;
            $sale_price = !empty($eshb_single_day_sale_price) ? $eshb_single_day_sale_price : $sale_price;
        }
        
        $price = $regular_price;

        if($sale == true){
            $price = !empty($sale_price) ? $sale_price : $regular_price;
        }
      
        if($session_price == true){
            $session_price = $this->get_eshb_price_by_session($accomodation_id, $start_date, $end_date, $night, $adult, $children);
            $price = !empty($session_price) ? $session_price : $price;
        }

        $price = apply_filters('eshb_price', $price);

        return $price;
    }

    public function eshb_price ($price, $currency_symbol = NULL){

        if(!$currency_symbol || $currency_symbol = NULL || empty($currency_symbol)) {
            // Get the currency symbol
            $currency_symbol = $this->get_eshb_currency_symbol();
        }
       
        // Prepare the price HTML, with or without currency symbol
        $currency_position = $this->get_eshb_currency_position();

        if(!empty($price)){
            $price = number_format($price,2);
        }
        
        $price_html = $currency_symbol . $price;
        if($currency_position == 'right') {
            $price_html = $price . $currency_symbol;
        }

        return $price_html;
    }
    
    public function get_eshb_price_html($start_date = null, $end_date = null, $accomodation_id = null, $show_currency = true, $include_single_day_price = true, $include_day_wise_price = true, $include_session_price = true, $format = 'sale') {
        
        if($start_date == null || empty($start_date) || $end_date == null || empty($end_date)){
            $dates = $this->get_eshb_default_start_end_date();
            $start_date = $dates['start_date'];
            $end_date = $dates['end_date'];
        }

    
        // Get the currency symbol
        $currency_symbol = $this->get_eshb_currency_symbol();
    
        // Get price values from different sources
        $regular_price = $this->get_eshb_price($start_date, $end_date, $accomodation_id, false, '', '', '', false);
        $sale_price = $this->get_eshb_price($start_date, $end_date, $accomodation_id, false, '', '', '', true);

        $prices = [
                'regular_price' => $regular_price,
                'sale_price' => $sale_price
            ];

        $day_wise_price = $this->get_eshb_day_wise_price($start_date, $end_date, $accomodation_id, false, 1, 1, 0);

        $has_session_price = $this->has_upcoming_or_current_session_price($accomodation_id, $start_date, $end_date, 1, 1, 0);
        
        if ($has_session_price && $include_session_price) {
            $include_session_price = true;
        } else {
            $include_session_price = false;
        }

        
        $session_price = $this->get_eshb_price_by_session($accomodation_id, $start_date, $end_date);
        $single_day_price = apply_filters( 'eshb_single_day_price', 0, $accomodation_id );
    
        // Determine the price to use
        if ($include_session_price === true && !empty($session_price) && ($session_price !== $regular_price && $session_price !== $sale_price)) {
            $price = $session_price;
        } elseif ($include_day_wise_price === true && !empty($day_wise_price) && ($day_wise_price !== $regular_price && $day_wise_price !== $sale_price)) {
            $price = $day_wise_price;
        } elseif ($include_single_day_price === true && !empty($single_day_price) && ($single_day_price !== $regular_price && $single_day_price !== $sale_price)) {
            $price = $single_day_price;
        } else {
            if(!empty($sale_price ) && $sale_price !== $regular_price){
                if($format == 'sale'){
                    $price = $prices;
                }else{
                    $price = $sale_price;
                }
            }else{
                $price = $regular_price;
            }
        }

        
        // Return empty string if price is empty
        if (empty($price)) {
            return '';
        }

        
        // Prepare the price HTML, with or without currency symbol
        $currency_position = $this->get_eshb_currency_position();
        $price_html = '';
        $price = apply_filters('eshb_price_html_price', $price);

        if ( is_array( $price ) ) {
            // Regular & Sale Price
            $regular_price = $price['regular_price'];
            $sale_price    = $price['sale_price'];

            if ( $show_currency ) {
                if ( $currency_position == 'right' ) {
                    $regular_price = $regular_price . $currency_symbol;
                    $sale_price    = $sale_price . $currency_symbol;
                } else {
                    $regular_price = $currency_symbol . $regular_price;
                    $sale_price    = $currency_symbol . $sale_price;
                }
            }

            $price_html  = '<span class="eshb-price">';
            $price_html .= '<del aria-hidden="true"><span class="eshb-price-amount amount"><bdi>' . $regular_price . '</bdi></span></del>';
            $price_html .= '<span class="screen-reader-text">Original price was: ' . $regular_price . '.</span>';
            $price_html .= '<ins aria-hidden="true"><span class="eshb-price-amount amount"><bdi>' . $sale_price . '</bdi></span></ins>';
            $price_html .= '<span class="screen-reader-text">Current price is: ' . $sale_price . '.</span>';
            $price_html .= '</span>';

        } else {
            // Single Price (No Sale)
            $single_price = $price;

            if ( $show_currency ) {
                if ( $currency_position == 'right' ) {
                    $single_price = $single_price . $currency_symbol;
                } else {
                    $single_price = $currency_symbol . $single_price;
                }
            }

            $price_html  = '<span class="eshb-price">';
            $price_html .= '<span class="eshb-price-amount amount"><bdi>' . $single_price . '</bdi></span>';
            $price_html .= '</span>';
        }

        $price_html = apply_filters('eshb_price_html', $price_html, $price);
        return $price_html;
    }
    
    public function get_eshb_day_names_from_date_ranges($start_date, $end_date){
        $period = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            new DateTime($end_date) // Stop before the end date
        );
    
        $dayNames = [];
        foreach ($period as $date) {
            $dayNames[] = strtolower($date->format('l')); // Convert to lowercase
        }
    
        return $dayNames;
    }

    public function get_eshb_day_wise_price($start_date, $end_date, $accomodation_id, $fallback_regular_price = true, $night = '', $adult = '', $children = ''){

        if($start_date == null || empty($start_date)  || $end_date == null || empty($end_date) || $accomodation_id == null || empty($accomodation_id)) return 0;
        

        $regular_price = $fallback_regular_price == true ? $this->get_eshb_price($start_date, $end_date, $accomodation_id, false, $night, $adult, $children,) : 0;

        if ($start_date === $end_date) {
            // Add 1 day to end date if they are the same
            $end_date = gmdate('Y-m-d', strtotime($end_date . ' +1 day'));
        }

        $period = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            new DateTime($end_date) // Stop before the end date
        );
        
        $dayNames = [];
        $days_count = 0;
        foreach ($period as $date) {
            $dayNames[] = strtolower($date->format('l')); // Convert to lowercase
            $days_count++;
        }

        // If only one day is selected, return the price for that day
        // if(is_archive() || isset($_GET['eshb_global_nonce_action']) || isset($_GET['eshb_search_nonce'])){
        //     $todayName = strtolower(gmdate('l'));
        //     //remove all day names from the array but today's name
        //     $dayNames = array_filter($dayNames, function($day) use ($todayName) {
        //         return $day === $todayName;
        //     });
        // }
        
        $metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
        $day_wise_price = isset($metaboxes['day_wise_price']) && !empty($metaboxes['day_wise_price']) ? $metaboxes['day_wise_price'] : '';
        
        $price_by_day = 0;
    
        if(!empty($day_wise_price) && isset($day_wise_price[0]) && !empty($day_wise_price[0])){
            $day_wise_price = $day_wise_price[0];
            foreach ($dayNames as $key => $day) {
                if( !empty($day_wise_price[$day]) ){
                    $price_by_day += $day_wise_price[$day];
                }else{
                    $price_by_day += $regular_price;
                }
                
            }
        }else{
            
            $price_by_day = $regular_price  * $days_count;
            
        }
        $price_by_day = apply_filters('eshb_day_wise_price', $price_by_day);
        return $price_by_day;
    }
}
ESHB_Core::instance();






















