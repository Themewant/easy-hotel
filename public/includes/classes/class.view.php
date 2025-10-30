<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class ESHB_View extends ESHB_MAIN{

    private static $query = null;
	
    public function eshb_get_accomodation_grid($query, $adult_quantity = 1, $children_quantity = 0, $column = 3, $thumbnail_size='eshb_thumbnail', $style=1, $start_date='', $end_date='', $booked_dates = []){
    
        if($query != NULL || !empty($query)){

             // Check if the custom template exists in your plugin directory
            $plugin_template = ESHB_PL_PATH . 'public/templates/template-parts/grid-style-'.$style. '.php';
            $theme_template = get_stylesheet_directory() . '/easy-hotel/templates/template-parts/grid-style-'.$style.'.php';
            $child_theme_template = get_template_directory() . '/easy-hotel/templates/template-parts/grid-style-'.$style.'.php';
    
            if (file_exists($child_theme_template)) {
                $template = $child_theme_template;
            } elseif (file_exists($theme_template)) {
                $template = $theme_template;
            } else {
                $template = $plugin_template;
            }

            include $template;
       }
    }

    public function eshb_get_search_form_html($start_date = null, $end_date = null){


        if( $start_date == null || empty($start_date) ){
            $today_date = gmdate('Y-m-d');
            $start_date = $today_date;
        }

        if( $end_date == null || empty($end_date) ){

            $min_max_settings = [
				'required_min_nights' => 1,
				'required_max_nights' => 999,
			];
			$eshb_min_max_settings = apply_filters( 'eshb_min_max_global_settings_in_searach', $min_max_settings);
            $required_min_nights = !empty($eshb_min_max_settings['required_min_nights']) ? $eshb_min_max_settings['required_min_nights'] : 1;
            $required_max_nights = !empty($eshb_min_max_settings['required_max_nights']) ? $eshb_min_max_settings['required_max_nights'] : '';
            

            $today_date = gmdate('Y-m-d'); // Get today's date

            // Create a DateTime object from today's date
            $date = new DateTime($today_date);

            // Add one day
            $date->modify('+'.$required_min_nights.' day');

            // Get the new date in 'Y-m-d' format
            $end_date = $date->format('Y-m-d');
        }

        $eshb_settings = get_option( 'eshb_settings' );
        $search_result_page_id = $eshb_settings['search-result-page'];
        $search_result_page_url = !empty($search_result_page_id) ? get_the_permalink( $search_result_page_id ) : site_url( '/easy-hotel-search-result' );
        $seach_form_fileds = isset($eshb_settings['search-form-fields']) && !empty($eshb_settings['search-form-fields']) ? $eshb_settings['search-form-fields'] : [];
       

        $adult_capacity = isset($eshb_settings['adult-capacity']) && !empty($eshb_settings['adult-capacity']) ? $eshb_settings['adult-capacity'] : 1000;
        $children_capacity = isset($eshb_settings['children-capacity']) && !empty($eshb_settings['children-capacity']) ? $eshb_settings['children-capacity'] : 1000;
        $room_capacity = isset($eshb_settings['room-capacity']) && !empty($eshb_settings['room-capacity']) ? $eshb_settings['room-capacity'] : 1000;

         // Translations
         $string_check_availability = isset($eshb_settings['string_check_availability']) && !empty($eshb_settings['string_check_availability']) ? $eshb_settings['string_check_availability'] : 'Check Availability';
         $string_check_in = isset($eshb_settings['string_check_in']) && !empty($eshb_settings['string_check_in']) ? $eshb_settings['string_check_in'] : 'Check In';
         $string_check_out = isset($eshb_settings['string_check_out']) && !empty($eshb_settings['string_check_out']) ? $eshb_settings['string_check_out'] : 'Check Out';
         $string_adult = isset($eshb_settings['string_adult']) && !empty($eshb_settings['string_adult']) ? $eshb_settings['string_adult'] : 'Adult';
         $string_children = isset($eshb_settings['string_children']) && !empty($eshb_settings['string_children']) ? $eshb_settings['string_children'] : 'Children';
         $string_room = isset($eshb_settings['string_room']) && !empty($eshb_settings['string_room']) ? $eshb_settings['string_room'] : 'Room';
 
        ?>
        <div class="eshb-search style-one">
            <form action="<?php echo esc_url($search_result_page_url); ?>" method="get" class="eshb-search-form">
                <?php ESHB_Helper::eshb_nonce_field('eshb_global_nonce_action', 'nonce', true); ?>
                <div class="eshb-form-group dates-wrapper">
                    <div class="eshb-form-group">
                        <h6 class="field-label"><?php echo esc_html(eshb_get_translated_string($string_check_in));?></h6>
                        <input type="text" id="date-picker_start_date" class="search-date-picker form-control" name="start_date" value="<?php echo esc_attr( $start_date ) ?>">
                    </div>
                    <div class="eshb-form-group">
                        <h6 class="field-label"><?php echo esc_html(eshb_get_translated_string($string_check_out));?></h6>
                        <input type="text" id="date-picker_end_date" class="search-date-picker form-control" name="end_date" value="<?php echo esc_attr( $end_date ) ?>">
                    </div>
                </div>
                <?php 
                    if(in_array('adults', $seach_form_fileds)){ ?>
                    <div class="eshb-form-group">
                        <h6 class="field-label"><?php echo esc_html(eshb_get_translated_string($string_adult));?></h6>
                        <div class="de-number">
                            <span class="d-minus"><?php echo esc_html('-')?></span>
                            <input class="form-control" type="text" value="1" name="adult_quantity" max="<?php echo esc_attr( $adult_capacity ) ?>">
                            <span class="d-plus"><?php echo esc_html('+')?></span>
                        </div>
                    </div>

                <?php } 
                
                    if(in_array('childrens', $seach_form_fileds)){ ?>
                        <div class="eshb-form-group">
                            <h6 class="field-label"><?php echo esc_html(eshb_get_translated_string($string_children));?></h6>
                            <div class="de-number">
                                <span class="d-minus"><?php echo esc_html('-')?></span>
                                <input class="form-control" type="text" value="0" name="children_quantity" max="<?php echo esc_attr( $children_capacity ) ?>">
                                <span class="d-plus"><?php echo esc_html('+')?></span>
                            </div>
                        </div>
                    <?php }

                        if(in_array('rooms', $seach_form_fileds)){ ?>
                        <div class="eshb-form-group">
                            <h6 class="field-label"><?php echo esc_html(eshb_get_translated_string($string_room));?></h6>
                            <div class="de-number">
                                <span class="d-minus"><?php echo esc_html('-')?></span>
                                <input class="form-control" type="text" value="0" name="room_quantity" max="<?php echo esc_attr( $room_capacity ) ?>">
                                <span class="d-plus"><?php echo esc_html('+')?></span>
                            </div>
                        </div>
                    <?php }
                ?>                        
                <div class="eshb-form-group submition-wrapper py-0">
                    <button class="eshb-form-submit-btn" href="#"><?php echo esc_html(eshb_get_translated_string($string_check_availability));?></button>
                </div>
            </form>
        </div>
        <?php
    }

    public function getNextAllowedStartDate($allowedDays, $date) {
        $givenDate = DateTime::createFromFormat('Y-m-d', $date);
        $dayOfWeek = (int) $givenDate->format('w'); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
    
      
        $daysOfWeek = [
            "sunday" => 0, "monday" => 1, "tuesday" => 2, "wednesday" => 3,
            "thursday" => 4, "friday" => 5, "saturday" => 6
        ];
        
        if (!isset($daysOfWeek[strtolower($allowedDays[0])])) {
            return false; 
        }
    
        $targetDay = $daysOfWeek[strtolower($allowedDays[0])];
    
        if ($dayOfWeek !== $targetDay) {
            $daysToAdd = ($targetDay - $dayOfWeek + 7) % 7 ?: 7; 
            $givenDate->modify("+$daysToAdd days");
        }
    
        return $givenDate->format('Y-m-d');
    }

    public function eshb_get_booking_form_html($accomodation_id = null, $style = 'style-one'){

        $eshb_settings = get_option( 'eshb_settings' );
        $booking_type = isset($eshb_settings['booking-type']) && !empty($eshb_settings['booking-type']) ? $eshb_settings['booking-type'] : 'woocommerce';
        $booking_form_type = isset($eshb_settings['booking-form-type']) && !empty($eshb_settings['booking-form-type']) ? $eshb_settings['booking-form-type'] : 'default';
        
        if($booking_type != 'booking_request'){
            $booking_form_type = 'default';
        }

        global  $woocommerce;

        $style_class = $style;


        if( $accomodation_id == null || empty($accomodation_id)){
            global $post;
            $accomodation_id = $post->ID;
        }

        
        $hotel_core = new ESHB_Core();

        $currency_symbol = $hotel_core->get_eshb_currency_symbol();

        $accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);

        $min_max_nights_settings = [
            'required_min_nights' => 1,
            'required_max_nights' => 999,
        ];

        $min_max_nights_settings = apply_filters('eshb_min_max_nights_settings_before_search', $min_max_nights_settings, $accomodation_id, $accomodation_metaboxes);
        $required_min_nights = !empty($min_max_nights_settings['required_min_nights']) ? $min_max_nights_settings['required_min_nights'] : 1;
        $required_max_nights = !empty($min_max_nights_settings['required_max_nights']) ? $min_max_nights_settings['required_max_nights'] : 999;

        $calendar_start_date_buffer = !empty($eshb_min_max_settings['calendar_start_date_buffer']) ? $eshb_min_max_settings['calendar_start_date_buffer'] : 0;

        $today_date = gmdate('Y-m-d'); 
        $today_date =  gmdate( 'Y-m-d', strtotime($today_date . ' +' . $calendar_start_date_buffer . ' day')); // Get today's date

        // Create a DateTime object from today's date
        $date = new DateTime($today_date);

        // Add one day
        $date->modify('+'.$required_min_nights.' day');

        // Get the new date in 'Y-m-d' format
        $tomorrow = $date->format('Y-m-d');
        
        $adult_capacity = $accomodation_metaboxes['adult_capacity']; 
        $children_capacity = $accomodation_metaboxes['children_capacity']; 
        $room_visibility = in_array('rooms', $eshb_settings['booking-form-fields']) ? true : false;
        
        $is_single_day_plugin_active = get_option('eshb_single_day_activated');
        
        $pricing_periodicity = false;
        $single_day_price = apply_filters( 'eshb_single_day_price', 0, $accomodation_id );
		if ($is_single_day_plugin_active && $single_day_price) {
			$pricing_periodicity = !empty($accomodation_metaboxes['pricing_periodicity']) ? $accomodation_metaboxes['pricing_periodicity'] : false;
		}

        
        // Verify nonce for security
		if (isset($_GET['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
            $start_date = isset( $_GET['start_date'] ) && !empty($_GET['start_date']) ? sanitize_text_field( wp_unslash($_GET['start_date'] )) : $today_date;
            $end_date = isset( $_GET['end_date'] ) && !empty($_GET['end_date']) ? sanitize_text_field( wp_unslash($_GET['end_date'] )) : $tomorrow;
            $adult_quantity = isset( $_GET['adult_quantity'] ) && !empty($_GET['adult_quantity']) ? sanitize_text_field( wp_unslash($_GET['adult_quantity'] )) : 1;
            $children_quantity = isset( $_GET['children_quantity'] ) && !empty($_GET['children_quantity']) ? sanitize_text_field( wp_unslash($_GET['children_quantity'] )) : 0;
            $adult_quantity = $adult_quantity > $adult_capacity ? $adult_capacity : $adult_quantity;
            $children_quantity = $children_quantity > $children_capacity ? $children_capacity : $children_quantity;
        }else{
            $start_date = $today_date;
            $end_date = $tomorrow;
            $adult_quantity = 1;
            $children_quantity = 0;
        }


        // set end to same as start date for single day hourly booking
        if($pricing_periodicity == 'per_hour') {
            $end_date = $start_date;
        }

        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);
        $days_count = $interval->days;
        $allowed_check_in_day = isset($accomodation_metaboxes['allowed_check_in_day']) && !empty($accomodation_metaboxes['allowed_check_in_day']) ? $accomodation_metaboxes['allowed_check_in_day'] : [];
        
        if(!empty($allowed_check_in_day)){
            // push the day to array if its string  
            if(is_string($allowed_check_in_day)){
                $allowed_check_in_day = [$allowed_check_in_day];
            }
            
            $allowed_check_in_day = array_map('strtolower', $allowed_check_in_day);

            // Add 1 day to get the next allowed end date
            $next_allowed_start_date = $this->getNextAllowedStartDate($allowed_check_in_day, $start_date);

            if(!empty($next_allowed_start_date) && !empty($next_allowed_end_date)){
                $start_date = $next_allowed_start_date;
                //$end_date = $next_allowed_end_date;
            }
        }


        $currency_symbol = $hotel_core->get_eshb_currency_symbol();
        $per_night_price_html = $hotel_core->get_eshb_price_html('', '', $accomodation_id, true, false, true, true);
        $price_html = $hotel_core->get_eshb_price_html($start_date, $end_date, $accomodation_id, true, false, true, true, 'regular');
        $price = $hotel_core->get_eshb_price_html($start_date, $end_date, $accomodation_id, false, true, true, false);
        $price = !empty($price) ? $price : 0;
        
        // discounted pricing
        $discountedPrice = $hotel_core->get_eshb_price_html($start_date, $end_date, $accomodation_id, false, true, true, true);
        $discountedPrice = !empty($discountedPrice) ? $discountedPrice : 0;

        $booking_form_fileds = $eshb_settings['booking-form-fields'];
        $external_booking_link = isset($eshb_settings['external-booking-link']) && !empty($eshb_settings['external-booking-link']) ? $eshb_settings['external-booking-link'] : false;

        // Translations
        $string_reserve = isset($eshb_settings['string_reserve']) && !empty($eshb_settings['string_reserve']) ? $eshb_settings['string_reserve'] : 'Reserve';
        $string_from = isset($eshb_settings['string_from']) && !empty($eshb_settings['string_from']) ? $eshb_settings['string_from'] : 'From';
        $string_night = isset($eshb_settings['string_night']) && !empty($eshb_settings['string_night']) ? $eshb_settings['string_night'] : 'night';
        $string_hour = isset($eshb_settings['string_hour']) && !empty($eshb_settings['string_hour']) ? $eshb_settings['string_hour'] : 'hour';
        $string_check_in = isset($eshb_settings['string_check_in']) && !empty($eshb_settings['string_check_in']) ? $eshb_settings['string_check_in'] : 'Check In';
        $string_check_out = isset($eshb_settings['string_check_out']) && !empty($eshb_settings['string_check_out']) ? $eshb_settings['string_check_out'] : 'Check Out';
        $string_adult = isset($eshb_settings['string_adult']) && !empty($eshb_settings['string_adult']) ? $eshb_settings['string_adult'] : 'Adult';
        $string_children = isset($eshb_settings['string_children']) && !empty($eshb_settings['string_children']) ? $eshb_settings['string_children'] : 'Children';
        $string_rooms = isset($eshb_settings['string_rooms']) && !empty($eshb_settings['string_rooms']) ? $eshb_settings['string_rooms'] : 'Rooms';
        $string_available_rooms = isset($eshb_settings['available_rooms']) && !empty($eshb_settings['available_rooms']) ? $eshb_settings['available_rooms'] : 'Available Rooms:';
        $string_extra_bed = isset($eshb_settings['string_extra_bed']) && !empty($eshb_settings['string_extra_bed']) ? $eshb_settings['string_extra_bed'] : 'Extra Bed';
        $string_extra_services = isset($eshb_settings['string_extra_services']) && !empty($eshb_settings['string_extra_services']) ? $eshb_settings['string_extra_services'] : 'Extra Services';
        $string_total_cost = isset($eshb_settings['string_total_cost']) && !empty($eshb_settings['string_total_cost']) ? $eshb_settings['string_total_cost'] : 'Total Cost';
        $string_disocunted_price = isset($eshb_settings['string_disocunted_price']) && !empty($eshb_settings['string_disocunted_price']) ? $eshb_settings['string_disocunted_price'] : 'Discounted Price';
        $string_book_your_stay = isset($eshb_settings['string_book_your_stay']) && !empty($eshb_settings['string_book_your_stay']) ? $eshb_settings['string_book_your_stay'] : 'Book Your Stay';
        $string_room = isset($eshb_settings['string_room']) && !empty($eshb_settings['string_room']) ? $eshb_settings['string_room'] : 'room';
        $string_guest = isset($eshb_settings['string_guest']) && !empty($eshb_settings['string_guest']) ? $eshb_settings['string_guest'] : 'guest';
        $string_time_slots = isset($eshb_settings['string_time_slots']) && !empty($eshb_settings['string_time_slots']) ? $eshb_settings['string_time_slots'] : 'Available Time Slots';
        $included_service_ids = $accomodation_metaboxes['accomodation_services'];
        $total_rooms = $accomodation_metaboxes['total_rooms']; 
        $eshb_bookings = new ESHB_Booking();
        $available_rooms = $eshb_bookings->get_available_room_count_by_date_range($accomodation_id, $start->format('Y-m-d'), $end->format('Y-m-d'));
        $available_rooms = $available_rooms < 0 ? 0 : $available_rooms;
        $available_times = ESHB_Helper::get_available_times_by_date($accomodation_id, $start->format('Y-m-d'));
        $price_html = $available_rooms < 1 ? $hotel_core->eshb_price(0) : $price_html;
        ?>
        <div class="eshb-booking">
            <div action="<?php echo esc_url(home_url('easy-hotel-search-result')); ?>" method="get" class="eshb-booking-form <?php echo esc_attr($style_class); ?>" data-booking-form-type="<?php echo esc_attr($booking_form_type); ?>" data-pricing-periodicity="<?php echo esc_attr( $pricing_periodicity )?>">
                
                <div class="hidden-fields">
                    <input type="hidden" name="subtotal_price" id="eshb-subtotal-price" value="<?php echo esc_html($price);?>"> 
                    <input type="hidden" name="subtotal_price" id="eshb-discounted-subtotal-price" value="<?php echo esc_html($discountedPrice);?>"> 
                    <input type="hidden" name="accomodation_id" value="<?php echo esc_attr( $accomodation_id ); ?>">
                    <input type="hidden" name="accomodation_title" value="<?php echo esc_attr( get_the_title( $accomodation_id ) ); ?>">
                    <input type="hidden" name="currency_symbol" value="<?php echo esc_attr( $currency_symbol ); ?>">
                </div>

                <div class="eshb-form-group form-title-wrapper">
                    <h3 class="form-title"><?php echo esc_html( eshb_get_translated_string($string_reserve) );?></h3>
                    <span class="pricing"><?php echo esc_html(eshb_get_translated_string($string_from));?> 
                        <h4 class="base-price"><?php echo wp_kses_post($per_night_price_html);?> / </h4>
                        <?php 
                        if($pricing_periodicity && $pricing_periodicity == 'per_hour'){
                            echo esc_html( eshb_get_translated_string($string_hour) );
                        }else{
                            echo esc_html( eshb_get_translated_string($string_night) );
                        }
                        ?>
                    </span>
                </div>
                <div class="eshb-form-groups date-pickers-wrapper">
                    <div class="eshb-form-group start-date-pickers-wrapper">
                        <h6 class="field-label">
                        <?php 
                            if($pricing_periodicity) { ?>
                                <?php echo esc_html( eshb_get_translated_string($string_check_in)) .'/'. esc_html( eshb_get_translated_string($string_check_out) );?>
                        <?php }else{ ?>
                                <?php echo esc_html( eshb_get_translated_string($string_check_in) );?>
                        <?php }
                            ?>
                        </h6>
                        <input type="text" id="booking-date-picker_start_date" class="booking-date-picker form-control" name="start_date" value="<?php echo esc_attr( $start_date ) ?>" accomodation_id="<?php echo esc_attr( $accomodation_id )?>">
                    </div>
                    <div class="eshb-form-group end-date-pickers-wrapper">
                        <h6 class="field-label"><?php echo esc_html( eshb_get_translated_string($string_check_out) );?></h6>
                        <input type="text" id="booking-date-picker_end_date" class="booking-date-picker form-control" name="end_date" value="<?php echo esc_attr( $end_date ) ?>" accomodation_id="<?php echo esc_attr( $accomodation_id )?>">
                    </div>
                    
                </div>
                <p class="date-err-msg err-msg"></p>

                <?php  
                $available_slots = !empty($available_times['available_slots']) ? $available_times['available_slots'] : [];
                if($pricing_periodicity && $pricing_periodicity == 'per_hour' && count($available_times) > 0){ ?>
                    <div class="eshb-form-groups time-slots-wrapper <?php echo count($available_slots) > 0 ? 'has-time-slots' : ''?>">
                        <div class="eshb-form-group">
                            <h6 class="field-label"><?php echo esc_html( eshb_get_translated_string($string_time_slots) );?></h6>
                            <p class="empty-slot-msg err-msg"><?php echo esc_html__( 'All slots are Booked!', 'easy-hotel' ); ?></p>
                            <div class="time-slots">
                                <?php  
                                
                                    if(count($available_slots) > 0){
                                        foreach ($available_slots as $key => $slot) {
                                            
                                            $max_start = gmdate("H:i", strtotime($slot[1] . " -1 hour"));
                                            $min_end = gmdate("H:i", strtotime($slot[0] . " +1 hour"));

                                            ?>
                                                <div class="eshb-form-groups eshb-time-slot selected">
                                                    <input type="radio" name="time-slot" checked="" class="time-slot">
                                                    <div class="eshb-styled-checkbox"></div>
                                                    <div class="eshb-times">
                                                        <div class="eshb-time-wrapper">
                                                            <input type="time" id="booking-time-picker_start_time" class="booking-time-picker form-control" name="start_time" value="<?php echo esc_attr( $slot[0] )?>" min="<?php echo esc_attr( $slot[0] )?>" max="<?php echo esc_attr( $max_start )?>" step="1800"> 
                                                        </div>
                                                        -
                                                        <div class="eshb-time-wrapper">
                                                            <input type="time" id="booking-time-picker_end_time" class="booking-time-picker form-control" name="end_time" value="<?php echo esc_attr( $slot[1] )?>" min="<?php echo esc_attr( $min_end )?>" max="<?php echo esc_attr( $slot[1] )?>" step="1800">
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php
                                        }
                                    }
                                    
                                ?>
                            </div>
                        </div>
                        <p class="time-err-msg err-msg"></p>
                    </div>
                <?php } ?>
                
               
                
                <div class="eshb-form-groups">
                    <?php 
                        if(is_array($booking_form_fileds) && in_array('adults', $booking_form_fileds)){ ?>
                            <div class="eshb-form-group">
                                <h6 class="field-label"><?php echo esc_html( eshb_get_translated_string($string_adult) );?></h6>
                                <div class="de-number">
                                    <span class="d-minus"><?php echo esc_html('-')?></span>
                                    <input type="text" value="<?php echo esc_attr( $adult_quantity ); ?>" name="adult_quantity">
                                    <span class="d-plus"><?php echo esc_html('+')?></span>
                                </div>
                                <p class="err-msg"></p>
                            </div>
                       <?php }
                    
                    if(is_array($booking_form_fileds) && in_array('childrens', $booking_form_fileds)){ ?>
                        <div class="eshb-form-group">
                            <h6 class="field-label"><?php echo esc_html( eshb_get_translated_string($string_children) );?></h6>
                            <div class="de-number">
                                <span class="d-minus"><?php echo esc_html('-')?></span>
                                <input type="text" value="<?php echo esc_attr( $children_quantity ); ?>" name="children_quantity">
                                <span class="d-plus"><?php echo esc_html('+')?></span>
                            </div>
                            <p class="err-msg"></p>
                        </div>
                    <?php }
                    if(is_array($booking_form_fileds) && in_array('rooms', $booking_form_fileds)){ 
                        
                        ?>
                        <div class="eshb-form-group">
                            <h6 class="field-label"><?php echo esc_html( eshb_get_translated_string($string_rooms) );?></h6>
                            <div class="de-number">
                                <span class="d-minus"><?php echo esc_html('-')?></span>
                                <input type="text" value="<?php echo $available_rooms > 0 ? esc_html($available_rooms) : 0 ?>" name="room_quantity">
                                <span class="d-plus" max="5"><?php echo esc_html('+')?></span>
                            </div>
                            <p class="capacity-status room-capacity-status"><?php echo esc_html( eshb_get_translated_string($string_available_rooms) ) . ' '; ?><span class="room-capacity-number"><?php echo esc_html( abs($available_rooms) ); ?></span></p>
                            <p class="err-msg"></p>
                            
                        </div>
                    <?php }
                    if(is_array($booking_form_fileds) && in_array('extra_beds', $booking_form_fileds)){ ?>
                        <div class="eshb-form-group">
                            <h6 class="field-label"><?php echo esc_html( eshb_get_translated_string($string_extra_bed) );?></h6>
                            <div class="de-number">
                                <span class="d-minus"><?php echo esc_html('-')?></span>
                                <input type="text" value="0" name="extra_bed_quantity">
                                <span class="d-plus" max="5"><?php echo esc_html('+')?></span>
                            </div>
                            <p class="err-msg"></p>
                        </div>
                    <?php } ?>
                    
                </div>
                <?php 
                if(is_array($included_service_ids) && count($included_service_ids) > 0 && $eshb_settings['extra-services-switcher'] == true){ ?>
                    <div class="eshb-form-group extra-services-wrapper">
                        <h3 class="field-label"><?php echo esc_html( eshb_get_translated_string($string_extra_services) );?></h3>
                        <ul class="service-list">
                            <?php 

                            if(is_array($included_service_ids) && count($included_service_ids) > 0){

                                $total_services_price = 0;

                                foreach ($included_service_ids as $key => $service_id) {
                                    $service_name = get_the_title( $service_id );
                                    $service_metaboxes = get_post_meta($service_id, 'eshb_service_metaboxes', true);
                                    $service_periodicity = $service_metaboxes['service_periodicity'];
                                    $service_charge_type = $service_metaboxes['service_charge_type'];

                                    $service_price = $service_metaboxes['service_price'];
                                    $service_price = apply_filters('eshb_service_price', $service_price);
                                    $service_charge_type = str_replace('_', ' ', $service_metaboxes['service_charge_type']);

                                    if(!empty($service_price)){
                                        $total_services_price+=$service_price;
                                    }



                                    ?>
                                    <li class="service-item">
                                        <label for="eshb-service-message-<?php echo esc_attr( $service_id )?>" class="label-checkbox">
                                            <input id="eshb-service-message-<?php echo esc_attr( $service_id )?>" type="checkbox" name="extra_services[]" value="<?php echo esc_attr($service_price) ?>" price="<?php echo esc_attr($service_price) ?>" service_id="<?php echo esc_attr( $service_id )?>" charge_type="<?php echo esc_attr( $service_charge_type ) ?>" periodicity="<?php echo esc_attr( $service_periodicity ) ?>" title="<?php echo esc_attr($service_name) ?>">
                                            <div class="eshb-styled-checkbox"></div>
                                            <span class="service-name"><?php echo esc_html($service_name, 'easy-hotel'); ?></span>
                                        </label>
                                        <div class="price-quantity">
                                            <span class="price">
                                                <?php


                                                    if($service_charge_type == 'room'){
                                                        $string_service_charge_type_translated = $string_room;
                                                    }else if($service_charge_type == 'guest'){
                                                        $string_service_charge_type_translated = $string_guest;
                                                    }else{
                                                        $string_service_charge_type_translated = $service_charge_type;
                                                    }

                                                    if ( ! empty( $service_price ) ) {
                                                        if ( ! $room_visibility && $service_charge_type === 'room' ) {
                                                            echo esc_html( $currency_symbol . $service_price );
                                                        } else {
                                                            echo esc_html( $currency_symbol . $service_price . ' / ' . eshb_get_translated_string( $string_service_charge_type_translated ) );
                                                        }
                                                    } else {
                                                        echo esc_html__( 'Free', 'easy-hotel' );
                                                    }

                                                    
                                                ?>
                                            </span>
                                            <?php 
                                                if( $service_charge_type != 'room'){
                                                    ?>
                                                    <div class="service-quantity-selector" id="service-quantity-selector">
                                                        <div class="de-number">
                                                            <span class="d-minus"><?php echo esc_html('-')?></span>
                                                                <span class="quantity-wrapper">
                                                                    <input type="text" value="1" name="service-quantity" price="<?php echo esc_attr($service_price) ?>" charge_type="<?php echo esc_attr( $service_charge_type ) ?>" periodicity="<?php echo esc_attr( $service_periodicity ) ?>">
                                                                    <span class="dropdown-arrow dashicons dashicons-arrow-down"></span>
                                                                </span>
                                                            <span class="d-plus"><?php echo esc_html('+')?></span>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                            ?>
                                            
                                        </div>
                                    </li>
                                <?php
                                }
                            }

                            ?>
                            
                        </ul>
                    </div>
               <?php }
                ?>
               

                <div class="eshb-form-group cost-calculator-wrapper">
                    <h3 class="field-label total-cost-label eshb-booking-total-pricing">
                        <?php echo esc_html(eshb_get_translated_string($string_total_cost));?>
                        <div class="eshb-booking-value" id="eshb-booking-total-price" currency_symbol="<?php echo esc_html( $currency_symbol ) ?>" subtotal_price=""><?php echo wp_kses_post($price_html);?></div>
                    </h3>
                    <h3 class="field-label total-cost-label eshb-booking-total-discounted-pricing">
                        <?php echo esc_html(eshb_get_translated_string($string_disocunted_price));?>
                        <div class="eshb-booking-value" id="eshb-booking-discounted-price" currency_symbol="<?php echo esc_html( $currency_symbol ) ?>" subtotal_price=""><?php echo wp_kses_post($price_html);?></div>
                    </h3>
                </div>
                
                <?php 
                    if($booking_type == 'booking_request'){

                        if($booking_form_type == 'cf7' || $booking_form_type == 'fluentform'){
                            echo do_shortcode($eshb_settings['booking-form-shortcode']);
                        }else{
                            ?>
                                <div class="eshb-form-group eshb-booking-form-customer-details">
                                    <h3 class="field-label"><?php echo esc_html__( 'Enter Your Detais', 'easy-hotel' ); ?></h3>
                                    <div class="eshb-booking-form-customer-details-col">
                                        <div class="eshb-form-group">
                                            <input type="text" class="form-control" name="customer_name" placeholder="<?php echo esc_html__( 'Your name', 'easy-hotel' ); ?>">
                                            <input type="email" class="form-control" name="customer_email" placeholder="<?php echo esc_html__( 'Your email', 'easy-hotel' ); ?>">
                                            <input type="tel" class="form-control" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" name="customer_phone" placeholder="<?php echo esc_html__( 'Your phone', 'easy-hotel' ); ?>">
                                        </div>
                                        <div class="eshb-form-group">
                                            <textarea name="customer_message" placeholder="Your message" class="form-control" style="height:100%"></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php
                        }
                        
                        
                    }
                
                    if($eshb_settings['booking-btn-switcher'] == true && $booking_form_type == 'default'){ ?>
                        <div class="eshb-form-group submition-wrapper"> 
                            <?php 
                                if($booking_type == 'woocommerce'){ ?>
                                    <button class="eshb-form-submit-btn" accomodation_id="<?php echo esc_attr(get_the_ID()); ?>" booking_type="<?php echo esc_attr($booking_type); ?>"><?php echo esc_html( eshb_get_translated_string($string_book_your_stay) );?></button>
                                <?php }else if($booking_type == 'surecart'){ ?>
                                    <button class="eshb-form-submit-btn" accomodation_id="<?php echo esc_attr(get_the_ID()); ?>" booking_type="<?php echo esc_attr($booking_type); ?>"> <?php echo esc_html( eshb_get_translated_string($string_book_your_stay) );?> </button>
                                <?php }else if($booking_type == 'booking_request'){ ?>
                                    <button class="eshb-form-submit-btn" accomodation_id="<?php echo esc_attr(get_the_ID()); ?>" booking_type="<?php echo esc_attr($booking_type); ?>"><?php echo esc_html( eshb_get_translated_string($string_book_your_stay) );?></button>
                                <?php }else{ ?>
                                    <a class="eshb-form-submit-btn" href="<?php echo esc_url( $external_booking_link ); ?>" target="_blank" accomodation_id="<?php echo esc_attr(get_the_ID()); ?>" booking_type="<?php echo esc_attr($booking_type); ?>"> <?php echo esc_html( eshb_get_translated_string($string_book_your_stay) );?> </a>
                                <?php }

                            ?>
                        </div>
                    <?php } ?>

                <p class="eshb-form-err">
                    <span class="status status-success"><span class="status-icon"><img src="<?php echo esc_url( ESHB_PL_URL . 'public/assets/img/checked.png' ); ?>" alt=""></span><span class="status-msg"></span></span>
                    <span class="status status-failed"><span class="status-icon"><img src="<?php echo esc_url( ESHB_PL_URL . 'public/assets/img/warning.png' ); ?>" alt=""></span><span class="status-msg"></span></span>
                </p>

            </div>
        </div>
        <?php
    }

    public function eshb_get_pagination($query = null){
           // Use the global $wp_query if no custom query is provided
        if ( !$query ) {
            global $wp_query;
            $query = $wp_query;
        }

        // Check if there are enough posts to paginate
        if ( $query->max_num_pages <= 1 ) {
            return;
        }

        $big = 999999999; // Need an unlikely integer

        // Generate pagination links
        $pages = paginate_links( array(
            'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format'    => '?paged=%#%',
            'current'   => max( 1, get_query_var('paged') ),
            'total'     => $query->max_num_pages,
            'prev_text' => __('&laquo; Previous', 'easy-hotel'),
            'next_text' => __('Next &raquo;', 'easy-hotel'),
            'type'      => 'array', // Output as array to customize
        ) );

        if ( is_array( $pages ) ) {
            echo '<nav class="eshb-pagination"><ul class="pagination-list">';
            foreach ( $pages as $page ) {
                echo '<li>' . wp_kses_post($page) . '</li>';
            }
            echo '</ul></nav>';
        }
    }

    public function eshb_get_availability_calendar_html($accomodation_id = null, $style = 'style-one'){

        $eshb_settings = get_option('eshb_settings', []);
  
        $availability_calendar_title = isset($eshb_settings['string_availability_calendar']) && !empty($eshb_settings['string_availability_calendar']) ? $eshb_settings['string_availability_calendar'] : __('Availability Calendar', 'easy-hotel');


        global  $woocommerce;

        $style_class = $style;

        if( $accomodation_id == null || empty($accomodation_id)){
            global $post;
            $accomodation_id = $post->ID;
        }
        
        $today_date = gmdate('Y-m-d'); // Get today's date

        $min_max_settings = [
            'calendar_start_date_buffer' => 0,
        ];
		$eshb_min_max_settings = apply_filters( 'eshb_min_max_global_settings_in_searach', $min_max_settings);
        $calendar_start_date_buffer = !empty($eshb_min_max_settings['calendar_start_date_buffer']) ? $eshb_min_max_settings['calendar_start_date_buffer'] : 0;
    

        $start_date = $today_date;
        // Verify nonce for security
		if (isset($_GET['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['nonce'])), 'eshb_nonce_action')) {
            $start_date = isset( $_GET['start_date'] ) && !empty($_GET['start_date']) ? sanitize_text_field( wp_unslash($_GET['start_date'] )) : $today_date;        
        }
        
        ?>
        <div class="eshb-availability-calendars-area">
            <h3 class="calendar-title"><?php echo esc_html( eshb_get_translated_string($availability_calendar_title) );?></h3>
            <div class="eshb-availability-calendars">
                <input type="hidden" id="booking-date-picker_start_date" class="booking-date-picker form-control" name="available_date_picker" value="<?php echo esc_attr( $start_date ) ?>" accomodation_id="<?php echo esc_attr( $accomodation_id )?>">
            </div>
            <p class="eshb-availability-calendars-err eshb-calendar-msg"></p>
            <?php 
                if(!empty($calendar_start_date_buffer)) {
                    ?>
                        <p class="eshb-availability-calendars-notice eshb-calendar-msg"><?php echo esc_html__( 'Some previous dates are not allowed for booking today. We set buffer for today + ', 'easy-hotel' ) . esc_html($calendar_start_date_buffer) ?></p>
                    <?php
                }
            ?>
            
        </div>
        <?php
    }

    // Limit Excerpt Length by number of Words
	public function eshb_custom_excerpt( $limit, $post_id = null ) {

		if($post_id == null || empty($post_id)) $post_id = get_the_ID();

		$excerpt = explode(' ', get_the_excerpt($post_id), $limit);
		if (count($excerpt)>=$limit) {
		array_pop($excerpt);
		$excerpt = implode(" ",$excerpt).'...';
		} else {
		$excerpt = implode(" ",$excerpt);
		}
		$excerpt = preg_replace('`[[^]]*]`','',$excerpt);
		return $excerpt;
	}

    public function eshb_get_gallery_html($accomodation_id = null, $unique_id = '', $thumbnail_size ='full', $sliderDots = 'false', $sliderNav = 'true'){

        $eshb_accomodation_metaboxes_side = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes_side', true);
        $accomodation_hero_type = !empty($eshb_accomodation_metaboxes_side['accomodation_hero_type']) ? $eshb_accomodation_metaboxes_side['accomodation_hero_type'] : 'gallery';
        $accomodation_gallery = !empty($eshb_accomodation_metaboxes_side['accomodation_gallery']) ? $eshb_accomodation_metaboxes_side['accomodation_gallery'] : '';
        $accomodation_video = !empty($eshb_accomodation_metaboxes_side['accomodation_video']) ? $eshb_accomodation_metaboxes_side['accomodation_video'] : '';
        $accomodation_video_source = !empty($eshb_accomodation_metaboxes_side['accomodation_video_source']) ? $eshb_accomodation_metaboxes_side['accomodation_video_source'] : '';
        $accomodation_gallery_ids = explode( ',', $accomodation_gallery );
        $accomodation_gallery_slides_per_view = !empty($eshb_accomodation_metaboxes_side['slides_per_view']) ? $eshb_accomodation_metaboxes_side['slides_per_view'] : '2.1';
        $accomodation_video_height = !empty($eshb_accomodation_metaboxes_side['accomodation_video_height']) ? $eshb_accomodation_metaboxes_side['accomodation_video_height'] : 0;


        if($accomodation_hero_type != 'video'){
            ?>
                <!-- Slider main container -->
                <div class="swiper gallery-wrapper accomodation-gallery accomodation-gallery-<?php echo esc_attr($unique_id) ?> <?php echo count($accomodation_gallery_ids) > 1 ? 'has-accomodation-gallery has-accomodation-gallery-'.esc_attr($unique_id) : '' ?>" data-slides-per-view="<?php echo esc_attr($accomodation_gallery_slides_per_view); ?>">
                    <!-- Additional required wrapper -->
                    <div class="swiper-wrapper">
                        <!-- Slides -->
                        <?php   
                            foreach ( $accomodation_gallery_ids as $gallery_item_id ) { ?>
                                <div class="swiper-slide">
                                <?php  echo wp_get_attachment_image( $gallery_item_id, $thumbnail_size ); ?>
                                </div>
                                
                        <?php }
                        ?>
                        
                    </div>

                    <?php
                    if( count($accomodation_gallery_ids) > 1 && !empty($sliderDots == 'true' || $sliderNav == 'true') ) : ?>
                            <?php
                                if($sliderDots == 'true') : ?>
                                    <div class="swiper-pagination text-center"></div>      
                                <?php endif; 
                                    
                                if($sliderNav == 'true') : ?>
                                     <!-- If we need navigation buttons -->
                                    <div class="swiper-button-prev"></div>
                                    <div class="swiper-button-next"></div>
                                <?php endif; ?>
                               
                    <?php endif; ?>
                
                </div>
                
                <?php 
        }else{
            if(!empty($accomodation_video) && !empty($accomodation_video_source)){ 
               
                
                if($accomodation_video_source  == 'external'){

                     // Extract the video ID from the YouTube URL
                     
                     preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $accomodation_video, $matches);
                     
                     // If a valid video ID is found, create the autoplay embed URL
                     if (isset($matches[1])) {
                         $video_id = $matches[1];
                         $autoplay_url = 'https://www.youtube.com/embed/' . $video_id . '?autoplay=1&mute=1&controls=0&modestbranding=1&rel=0'; 
                     }
                     ?>
                     <div class="accomodation-video eshb-external-video" style="<?php echo !empty($accomodation_video_height) ? 'height:' . esc_attr($accomodation_video_height) . 'px' : 'padding-bottom: 56.25%;'; ?>">
                         <?php 
                         echo '<iframe width="auto" height="' . esc_attr($accomodation_video_height) . 'px" src="' . esc_url($autoplay_url) . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
                         ?>
                     </div>
                     

                <?php
                }else if($accomodation_video_source  == 'self_hosted'){
                    ?>
                        <div class="accomodation-video eshb-self-hosted-video" style="<?php echo !empty($accomodation_video_height) ? 'height:' . esc_attr($accomodation_video_height).'px' : 'height: auto'; ?>">
                            <video autoplay muted loop width="100%" <?php echo !empty($accomodation_video_height) ? 'height=' . esc_attr($accomodation_video_height) : 'height=auto'; ?>>
                                <source src="<?php echo esc_url($accomodation_video);?>" type="video/mp4">
                                <?php echo esc_html__( 'Your browser does not support the video tag.', 'easy-hotel' ); ?>
                            </video>
                        </div>
                    <?php
                }
            
            }

            
        }
    }

    public function eshb_day_wise_pricing_table_html($accomodation_id = null, $show_title = false){

        if( $accomodation_id == null || empty($accomodation_id)){
            global $post;
            $accomodation_id = $post->ID;
        }

        $hotel_core = new ESHB_Core();
        
        $accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
        $day_wise_price = isset($accomodation_metaboxes['day_wise_price']) && !empty($accomodation_metaboxes['day_wise_price'][0]) ? $accomodation_metaboxes['day_wise_price'][0] : [];


        $day_wise_price = array_filter($day_wise_price, function($value) {
            return !empty($value) && $value != 0;
        });

        if (empty($day_wise_price)) {
            return; // return if all values are empty or 0
        }

        $default_price = $hotel_core->get_eshb_price('', '', $accomodation_id, false, true, false, true);
        $all_days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $currency_symbol = $hotel_core->get_eshb_currency_symbol();

        ?>  
            <div class="eshb-day-wise-pricing-table-container">
                <?php 
                if($show_title == true) : ?>
                    <h3 class="eshb-day-wise-pricing-table-title"><?php echo esc_html__( 'Day Wise Pricing', 'easy-hotel' ); ?></h3>
                <?php endif; ?>
                <div class="eshb-day-wise-pricing-table-wrapper table-responsive">
                    <table class="eshb-day-wise-pricing-table table table-striped">
                        <thead>
                            <tr>
                                <?php
                                    foreach ($all_days as $day) {
                                        ?>
                                        <th><?php echo esc_html(ucfirst($day)); ?></th>
                                        <?php
                                    }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            <?php
                                foreach ($all_days as $day) {
                                    $daily_price = isset($day_wise_price[$day]) && $day_wise_price[$day] !== '' ? $day_wise_price[$day] : $default_price;
                                    ?>
                                    <td><?php echo esc_html($currency_symbol . $daily_price); ?></td>
                                    <?php
                                }
                            ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
            </div>
        <?php

    }

    public function eshb_get_eshb_check_in_out_times_html($show_title = false){
        $eshb_settings = get_option('eshb_settings', []);
        $check_in_time = isset($eshb_settings['check-in-time']) && !empty($eshb_settings['check-in-time']) ? $eshb_settings['check-in-time'] : '';
        $check_out_time = isset($eshb_settings['check-out-time']) && !empty($eshb_settings['check-out-time']) ? $eshb_settings['check-out-time'] : '';

        if(empty($check_in_time) && empty($check_out_time)){
            return;
        }
        ?>
            <div class="eshb-check-in-out-times-area">
                <?php 
                if($show_title == true) : ?>
                    <h3 class="eshb-check-in-out-times-title"><?php echo esc_html__( 'Check In & Check Out Times', 'easy-hotel' ); ?></h3>
                <?php endif; ?>
              
                <div class="eshb-check-in-out-times">
                    <?php 
                        if(!empty($check_in_time)){
                            $check_in_time = date_i18n( get_option( 'time_format' ), strtotime($check_in_time) );
                            ?>
                            <p class="eshb-check-in-time"><span class="eshb-check-in-label"><?php echo esc_html__( 'Check In Time:', 'easy-hotel' ); ?></span> <span class="eshb-check-in-time-value"><?php echo esc_html($check_in_time); ?></span></p>
                            <?php
                        }
                        if(!empty($check_out_time)){
                            $check_out_time = date_i18n( get_option( 'time_format' ), strtotime($check_out_time) );
                            ?>
                            <p class="eshb-check-out-time"><span class="eshb-check-out-label"><?php echo esc_html__( 'Check Out Time:', 'easy-hotel' ); ?></span> <span class="eshb-check-out-time-value"><?php echo esc_html($check_out_time); ?></span></p>
                            <?php
                        }
                    ?>
                </div>
            </div>
                
        <?php
    }
}



