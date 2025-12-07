<?php
use SureCart\Models\Order;
use SureCart\Models\Checkout;
use SureCart\Models\PaymentMethod;
use SureCart\Models\ManualPaymentMethod;
use SureCart\Models\Customer;
use SureCart\Models\ShippingMethod;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ESHB_Booking_Calendar_Ajax {
    public function __construct() {
        add_action( "wp_ajax_eshb_get_booking_data_tables", [$this, 'eshb_get_booking_data']  );
        add_action( "wp_ajax_eshb_get_accomodation_meta", [$this, 'eshb_get_accomodation_meta']  );
        add_action( "wp_ajax_nopriv_eshb_get_accomodation_meta", [$this, 'eshb_get_accomodation_meta']  );
    }

    public function eshb_get_booking_data() {

        check_ajax_referer( ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'), 'nonce' );
  
        if (isset($_REQUEST['post']) && get_post_type(sanitize_text_field(wp_unslash($_REQUEST['post']))) == 'eshb_booking') {
            $post_id = intval(sanitize_text_field(wp_unslash($_REQUEST['post'])));
           
            $eshb_settings = get_option( 'eshb_settings' );
            $booking_type = $eshb_settings['booking-type'];

            // Retrieve and display your custom data for the eshb_booking post type
            $booking_metaboxes = get_post_meta($post_id, 'eshb_booking_metaboxes', true);

            $hotel_core = new ESHB_Core();
            $currency_symbol = html_entity_decode($hotel_core->get_eshb_currency_symbol());
            $accomodation_id = $booking_metaboxes['booking_accomodation_id'];
            $accomodation_title = get_the_title($accomodation_id);
            $booking_start_date = $booking_metaboxes['booking_start_date'];
            $booking_end_date = $booking_metaboxes['booking_end_date'];

            $time_slot = !empty($booking_metaboxes['booking_start_time']) && !empty($booking_metaboxes['booking_end_time']) ? ESHB_Helper::format_to_wp_time($booking_metaboxes['booking_start_time']) . ' - ' . ESHB_Helper::format_to_wp_time($booking_metaboxes['booking_end_time']) : '';

            $adult_quantity = !empty($booking_metaboxes['adult_quantity']) ? $booking_metaboxes['adult_quantity'] : 0;
            $children_quantity = !empty($booking_metaboxes['children_quantity']) ? $booking_metaboxes['children_quantity'] : 0;
            $room_quantity = !empty($booking_metaboxes['room_quantity']) ? $booking_metaboxes['room_quantity'] : 1;
            $extra_bed_quantity = !empty($booking_metaboxes['extra_bed_quantity']) ? $booking_metaboxes['extra_bed_quantity'] : 0;
            $extra_services = !empty($booking_metaboxes['extra_services_html']) ? $booking_metaboxes['extra_services_html'] : '';
            
            $base_price = !empty($booking_metaboxes['base_price']) ? $booking_metaboxes['base_price'] : 0;
            $subtotal_price = !empty($booking_metaboxes['subtotal_price']) ? $booking_metaboxes['subtotal_price'] : 0;  
            $extra_bed_price = !empty($booking_metaboxes['extra_bed_price']) ? $booking_metaboxes['extra_bed_price'] : 0;  
            $total_price = !empty($booking_metaboxes['total_price']) ? $booking_metaboxes['total_price'] : $subtotal_price;  
            $total_paid = !empty($booking_metaboxes['total_paid']) ? $booking_metaboxes['total_paid'] : 0;
            $deposit_amount = !empty($booking_metaboxes['deposit_amount']) ? $booking_metaboxes['deposit_amount'] : 0;  
            $due_amount = $subtotal_price - $total_paid;

            $extra_services_charge = !empty($booking_metaboxes['extra_service_price']) ? $booking_metaboxes['extra_service_price'] : 0;  

            // Customer Details
            
            $order_id = !empty($booking_metaboxes['order_id']) ? $booking_metaboxes['order_id'] : '';
            $order = '';

            $billing_data = [];
        
            if(class_exists('WooCommerce') && wc_get_order($order_id)){
                $order = wc_get_order($order_id);
                if($order){
                    $billing_data['order_status'] = $order->get_status();
                    $billing_data['first_name'] = $order->get_billing_first_name();
                    $billing_data['last_name'] = $order->get_billing_last_name();
                    $billing_data['customer_name'] = trim($billing_data['first_name'] . ' ' . $billing_data['last_name']); // Concatenate first and last name
                    $payment_gateways = WC()->payment_gateways->payment_gateways();
                    $payment_method_code = $order->get_payment_method();
                    $payment_gateway = $payment_gateways[ $payment_method_code ];
                    $billing_data['payment_method_name'] = $payment_gateway->get_title();
                    $billing_data['billing_email'] = $order->get_billing_email();
                    $billing_data['billing_phone'] = $order->get_billing_phone();
                    $billing_data['billing_company'] = $order->get_billing_company();
                    $billing_data['billing_city'] = $order->get_billing_city();
                    $billing_data['billing_state'] = $order->get_billing_state();
                    $billing_data['billing_postcode'] = $order->get_billing_postcode();
                    $billing_data['billing_country'] = $order->get_billing_country();
                    $billing_data['billing_address_1'] = $order->get_billing_address_1();
                    $billing_data['billing_address_2'] = $order->get_billing_address_2();
                }
            }else {
                $order = $booking_metaboxes;
                $customer = !empty($booking_metaboxes['customer']) ? $booking_metaboxes['customer'] : [];
                
                if(!empty($customer)){
                    $billing_data['order_status'] = $booking_metaboxes['booking_status'];
                    $billing_data['customer_name'] = $customer['name']; // Concatenate first and last name
                    $billing_data['payment_method_name'] = esc_html__('N/A', 'easy-hotel');
                    $billing_data['billing_email'] = $customer['email'];
                    $billing_data['billing_phone'] = $customer['phone'];
                }
            }

            $billing_data = apply_filters( 'eshb_billing_data_booking_view', $billing_data, $order_id, $booking_type);
            $hotel_core = new ESHB_Core();

            ?>
            <div class="booking-details">
                <h3><?php esc_html_e('Rooms & Services', 'easy-hotel') ?></h3>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Field', 'easy-hotel') ?></th>
                            <th><?php esc_html_e('Value', 'easy-hotel') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php esc_html_e('Booking Date', 'easy-hotel') ?></td>
                            <td><?php echo esc_html(date_i18n( get_option('date_format'), strtotime( get_the_date('Y-m-d', $post_id) ) ), 'easy-hotel'); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Booking Start Date', 'easy-hotel') ?></td>
                            <td><?php echo esc_html(date_i18n( get_option('date_format'), strtotime( $booking_start_date ) ), 'easy-hotel'); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Booking End Date', 'easy-hotel') ?></td>
                            <td><?php echo esc_html(date_i18n( get_option('date_format'), strtotime( $booking_end_date ) ), 'easy-hotel'); ?></td>
                        </tr>
                        <?php 
                            if(!empty($time_slot)) {
                                
                                ?>
                                    <tr>
                                        <td><?php esc_html_e('Time Slot', 'easy-hotel') ?></td>
                                        <td><?php echo esc_html($time_slot); ?></td>
                                    </tr>
                                <?php
                            }
                        ?>
                        <tr>
                            <td><?php esc_html_e('Room', 'easy-hotel') ?></td>
                            <td><?php echo esc_html($accomodation_title); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Number of Rooms', 'easy-hotel') ?></td>
                            <td><?php echo esc_html($room_quantity); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Number of Extra Beds', 'easy-hotel') ?></td>
                            <td><?php echo esc_html($extra_bed_quantity); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Adults', 'easy-hotel') ?></td>
                            <td><?php echo esc_html($adult_quantity); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Childrens', 'easy-hotel') ?></td>
                            <td><?php echo esc_html($children_quantity); ?></td>
                        </tr>
                        <?php 
                            if(!empty($extra_services)){
                                ?>
                            <tr>
                                <td><?php esc_html_e('Extra Services', 'easy-hotel') ?></td>
                                <td><?php echo esc_html($extra_services); ?></td>
                            </tr>
                                <?php
                            }
                        ?>
                    </tbody>
                </table>
                <?php 
                    if(count($billing_data) > 0) {
                        ?>
                            <h3><?php esc_html_e('Customer & Billings', 'easy-hotel') ?></h3>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Field', 'easy-hotel') ?></th>
                                        <th><?php esc_html_e('Value', 'easy-hotel') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ( !empty($billing_data['customer_name']) ) : ?>
                                    <tr>
                                        <td><?php esc_html_e('Name', 'easy-hotel') ?></td>
                                        <td><?php echo esc_html($billing_data['customer_name']); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if ( !empty($billing_data['billing_email']) ) : ?>
                                    <tr>
                                        <td><?php esc_html_e('Email Address', 'easy-hotel') ?></td>
                                        <td><?php echo esc_html($billing_data['billing_email']); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if ( !empty($billing_data['billing_phone']) ) : ?>
                                    <tr>
                                        <td><?php esc_html_e('Phone Number', 'easy-hotel') ?></td>
                                        <td><?php echo esc_html($billing_data['billing_phone']); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if ( !empty($billing_data['billing_company']) ) : ?>
                                    <tr>
                                        <td><?php esc_html_e('Company', 'easy-hotel') ?></td>
                                        <td><?php echo esc_html($billing_data['billing_company']); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if ( !empty($billing_data['billing_city']) ) : ?>
                                    <tr>
                                        <td><?php esc_html_e('City', 'easy-hotel') ?></td>
                                        <td><?php echo esc_html($billing_data['billing_city']); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if ( !empty($billing_data['billing_state']) ) : ?>
                                    <tr>
                                        <td><?php esc_html_e('State', 'easy-hotel') ?></td>
                                        <td><?php echo esc_html($billing_data['billing_state']); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if ( !empty($billing_data['billing_postcode']) ) : ?>
                                    <tr>
                                        <td><?php esc_html_e('Post Code', 'easy-hotel') ?></td>
                                        <td><?php echo esc_html($billing_data['billing_postcode']); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if ( !empty($billing_data['billing_country']) ) : ?>
                                    <tr>
                                        <td><?php esc_html_e('Country', 'easy-hotel') ?></td>
                                        <td><?php echo esc_html($billing_data['billing_country']); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if ( !empty($billing_data['billing_address_1']) ) : ?>
                                    <tr>
                                        <td><?php esc_html_e('Address One', 'easy-hotel') ?></td>
                                        <td><?php echo esc_html($billing_data['billing_address_1']); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if ( !empty($billing_data['billing_address_2']) ) : ?>
                                    <tr>
                                        <td><?php esc_html_e('Address Two', 'easy-hotel') ?></td>
                                        <td><?php echo esc_html($billing_data['billing_address_2']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        <?php
                    }
                ?>

                <h3><?php esc_html_e('Payment', 'easy-hotel') ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Field', 'easy-hotel') ?></th>
                            <th><?php esc_html_e('Value', 'easy-hotel') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(!empty($billing_data['order_status'])) {
                            ?>
                                <tr>
                                    <td><?php esc_html_e('Payment Status', 'easy-hotel') ?></td>
                                    <td><?php echo esc_html(ucfirst(strtolower($billing_data['order_status']))); ?></td>
                                </tr>
                            <?php
                        }

                        if(!empty($billing_data['payment_method_name'])) {
                            ?>
                                <tr>
                                    <td><?php esc_html_e('Payment Method', 'easy-hotel') ?></td>
                                    <td><?php echo esc_html(ucfirst(strtolower($billing_data['payment_method_name']))); ?></td>
                                </tr>
                            <?php
                        }
                    ?>
                    
                        <tr>
                            <td><?php esc_html_e('Base Price', 'easy-hotel') ?></td>
                            <td><?php echo esc_html($hotel_core->eshb_price($base_price)); ?></td>
                        </tr>
                        <?php 
                            if(!empty($extra_bed_price)){
                                ?>
                                    <tr>
                                        <td><?php esc_html_e('Extra Bed Price', 'easy-hotel') ?></td>
                                        <td><?php echo esc_html($hotel_core->eshb_price($extra_bed_price)); ?></td>
                                    </tr>
                                <?php
                            }
                            if(!empty($extra_services_charge)){
                                ?>
                                    <tr>
                                        <td><?php esc_html_e('Service Charge', 'easy-hotel') ?></td>
                                        <td><?php echo esc_html($hotel_core->eshb_price($extra_services_charge)); ?></td>
                                    </tr>
                                <?php
                            }
                        ?>
                        <tr>
                            <td><?php esc_html_e('SubTotal', 'easy-hotel') ?></td>
                            <td><?php echo esc_html($hotel_core->eshb_price($subtotal_price)); ?></td>
                        </tr>
                        
                        
                        <?php 
                        
                        if(!empty($deposit_amount)){
                            ?>
                                <tr>
                                    <td><?php esc_html_e('Initial Deposit', 'easy-hotel') ?></td>
                                    <td><?php echo esc_html($hotel_core->eshb_price($deposit_amount)); ?></td>
                                </tr>
                            <?php
                        }
                        if(!empty($due_amount)){
                            ?>
                            <tr>
                                <td><?php esc_html_e('Due', 'easy-hotel') ?></td>
                                <td><?php echo esc_html($hotel_core->eshb_price($due_amount)); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <td><?php esc_html_e('Total Paid', 'easy-hotel') ?></td>
                            <td><?php echo esc_html($hotel_core->eshb_price($total_paid)); ?></td>
                        </tr> 
                        
                    </tbody>
                </table>

                <?php 
                    // after_eshb_booking_details_html
                    do_action( 'after_eshb_booking_details_html', $order_id ); 
                ?>
            </div>
            <?php
        } else if(isset($_REQUEST['post']) && get_post_type(sanitize_text_field(wp_unslash($_REQUEST['post']))) == 'eshb_ical_booking'){

            $booking_id = !empty($_REQUEST['post']) ? sanitize_text_field(wp_unslash($_REQUEST['post'])) : '';
            $booking_start_date = get_post_meta($booking_id, 'start', true);
            $booking_end_date = get_post_meta($booking_id, 'end', true);
            $uid = get_post_meta($booking_id, 'uid', true);
            $accomodation_id = get_post_meta($booking_id, 'accomodation_id', true);
            $source_id = get_post_meta($booking_id, 'source_id', true);
            $uid = get_post_meta($booking_id, 'uid', true);
            $calendar_name = get_post_meta($booking_id, 'calendar_name', true);
            $summary = get_post_meta( $booking_id, 'summary', true );
            $ical_sources = get_post_meta($accomodation_id, 'ical_sources', true);
            $ical_sources = $ical_sources ? $ical_sources : []; // Set to empty array if not set
            $ical_source = array_filter($ical_sources, function($ical_source) use ($source_id) {  
                return isset($ical_source['id']) && $ical_source['id'] == $source_id;
            });
            // Get the first matched item safely
            $ical_source = reset($ical_source);
            $ical_url = $ical_source['url'];


            ?>
                <div class="wrap">
                    <h3><?php esc_html_e('Ical Booking', 'easy-hotel') ?></h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Field', 'easy-hotel') ?></th>
                                <th><?php esc_html_e('Value', 'easy-hotel') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php esc_html_e('Booking Date', 'easy-hotel') ?></td>
                                <td><?php echo esc_html(date_i18n( get_option('date_format'), strtotime( get_the_date('Y-m-d', $booking_id) ) )); ?></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Check-in Date', 'easy-hotel') ?></td>
                                <td><?php echo esc_html(date_i18n( get_option('date_format'), strtotime( $booking_start_date ) )); ?></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Check-out Date', 'easy-hotel') ?></td>
                                <td><?php echo esc_html(date_i18n( get_option('date_format'), strtotime( $booking_end_date ) )); ?></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Summary', 'easy-hotel') ?></td>
                                <td><?php echo  esc_html( $summary, 'easy-hotel' ); ?></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Calendar Name', 'easy-hotel') ?></td>
                                <td><?php echo  esc_html( $calendar_name ); ?></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('UID', 'easy-hotel') ?></td>
                                <td><?php echo  esc_html( $uid ); ?></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Ical Source ID', 'easy-hotel') ?></td>
                                <td><?php echo  esc_html( $source_id ); ?></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Ical Url', 'easy-hotel') ?></td>
                                <td><a href="<?php echo  esc_html( $ical_url ); ?>" target="_blank"><?php echo  esc_html( $ical_url ); ?></a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php

        }else{
            // Handle the case when the post type is not eshb_booking
            echo '<div class="error"><p>' . esc_html__('Invalid post type.', 'easy-hotel') . '</p></div>';
        }
        wp_die(); // This is required to terminate immediately and return a proper response
    }

    public function eshb_get_accomodation_meta(){
        
        check_ajax_referer( ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'), 'nonce' );

        if (isset($_REQUEST['accomodationId']) && get_post_type(sanitize_text_field(wp_unslash($_REQUEST['accomodationId']))) == 'eshb_accomodation') {
            $accomodation_id = intval(sanitize_text_field(wp_unslash($_REQUEST['accomodationId'])));

            $eshb_min_max_settings = get_option( 'eshb_min_max_settings', []);
            $required_min_nights = !empty($eshb_min_max_settings['required_min_nights']) ? $eshb_min_max_settings['required_min_nights'] : 1;
            $required_max_nights = !empty($eshb_min_max_settings['required_max_nights']) ? $eshb_min_max_settings['required_max_nights'] : 999;

            $eshb_week_settings = get_option( 'eshb_week_settings', [] );
            $string_check_in_day_error_msg = !empty($eshb_week_settings['string_check_in_day_error_msg']) ? $eshb_week_settings['string_check_in_day_error_msg'] : '';

            // accomodation details metadata
            $eshb_booking = new ESHB_Booking();
            $eshb_accomodation_metaboxes = [];
            $start_date = isset($_POST['startDate']) ? sanitize_text_field( wp_unslash($_POST['startDate']) ) : '';
            $end_date = isset($_POST['endDate']) ? sanitize_text_field( wp_unslash($_POST['endDate']) ) : '';
            $eshb_accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
            $available_rooms = $eshb_booking->get_available_room_count_by_date_range($accomodation_id, $start_date, $end_date);
            $eshb_accomodation_metaboxes['available_rooms'] = $available_rooms;
            

            $eshb_translations = [
                'maximumCapacity' => __('Maximum Capacity', 'easy-hotel'),
                'availableCapacity' => __('Available Capacity', 'easy-hotel'),
                'availableRoom' => __('Available Room', 'easy-hotel'),
                'maximumAdultAndChildrenCapacity' => __('Maximum Adult and Children Capacity', 'easy-hotel'),
            ];

            wp_send_json_success( [
                'requiredMinNights' => $required_min_nights,
                'requiredMaxNights' => $required_max_nights,
                'checkInDayErrorMsg' => $string_check_in_day_error_msg,
                'currentAccomodationMeta' => $eshb_accomodation_metaboxes,
                'translations' => $eshb_translations
            ]);

            die();
                
        }else{
            wp_send_json_error( 'invalid accomodation id' );
        }
    }
}
new ESHB_Booking_Calendar_Ajax();