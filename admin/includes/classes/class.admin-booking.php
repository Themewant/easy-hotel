<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
class ESHB_Admin_Booking {
    public function __construct() {
        add_action( 'wp_after_insert_post', [$this, 'create_order_after_save_booking_post'], 10, 3 );
        add_action( 'save_post_eshb_booking', [$this, 'eshb_update_booking_title_on_save'], 10, 3 );
        add_filter( 'eshb_eshb_booking_metaboxes_save', [$this, 'update_booking_metaboxes'], 10, 3 );
    }

    /**
     * WooCommerce order linked to a booking, or 0.
     *
     * A guest's checkout stores the link in the booking's own
     * `eshb_booking_metaboxes['order_id']`; an order created for an
     * admin-entered booking (EHB Manual Booking) is stored in
     * `_order_post_created`. Only the second used to be read, so saving a
     * checkout booking in the admin cleared its link, and the booking was
     * then treated as having no order (EHB Manual Booking would create a
     * second one).
     *
     * @param int        $booking_id
     * @param array|null $metaboxes Booking meta to read first (e.g. the data being saved).
     * @return int
     */
    public static function get_linked_order_id( $booking_id, $metaboxes = null ) {
        if ( is_array( $metaboxes ) && ! empty( $metaboxes['order_id'] ) ) {
            return absint( $metaboxes['order_id'] );
        }

        $stored = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
        if ( is_array( $stored ) && ! empty( $stored['order_id'] ) ) {
            return absint( $stored['order_id'] );
        }

        return absint( get_post_meta( $booking_id, '_order_post_created', true ) );
    }

    function update_booking_metaboxes ($data, $post_id, $obj) {

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action') ) ) {
            $new_status = '';
        }else{
            $new_status = !empty($_POST['post_status']) ? sanitize_text_field( wp_unslash( $_POST['post_status'] ) ) : '';
        }

        $eshb_booking_metaboxes = $data;

        if(!empty($new_status)){
            $eshb_booking_metaboxes['booking_status'] = $new_status;
        }

        // Keep the WooCommerce order link: the posted hidden field, else what
        // is already stored. Never overwrite it with an empty value.
        $order_id = self::get_linked_order_id( $post_id, $data );
        $eshb_booking_metaboxes['order_id'] = $order_id ? $order_id : '';

        return $eshb_booking_metaboxes;
    }

    function update_woocommerce_order($order_id, $subtotal = '', $total_paid = '', $status = 'processing', $product_id = '', $address = '', $item_meta = []) {
        // 1. Get the order
        $order = wc_get_order( $order_id );

        if ( !$order ) {
            return false; // Order not found
        }

        // 2. Update products (product_id, quantity)
        if( !empty($product_id) ) {

            // Remove existing items if needed
            $items = $order->get_items();

            if ( ! empty( $items ) ) {
                $first_item = reset( $items ); // Get the first item object
                $order->remove_item( $first_item->get_id() );
            }

            $item_id = $order->add_product( wc_get_product( $product_id ), 1 ); // 123 = Product ID, 2 = Qty
        }

        // If no product_id is provided, we assume the order already has items and we won't add a new one.
        $item_id = !empty($item_id) ? $item_id : array_key_first($order->get_items());

        // 3. Calculate totals
        $order->calculate_totals();

        // 4. Set customer billing details
        if( !empty($address) ) {
            // Ensure address is an array
            if ( !is_array($address) ) {
                $address = [];
            }
            $order->set_address( $address, 'billing' );
        }
        

        // 5. Set payment method
        //$order->set_payment_method( 'cod' ); // cod = Cash on Delivery

        // 6. Get the line item object
        $item = $order->get_item( $item_id );

        // 7. Set a custom line subtotal/total if needed
        if ( $item && $item instanceof WC_Order_Item_Product ) {

            if( !is_numeric($subtotal) ) {
                $subtotal = $order->get_subtotal(); // If subtotal is not provided, use get the current subtotal
            }

            if( !is_numeric($total_paid) ) {
                $total_paid = $order->get_total(); // If total_paid is not provided, use get the current total
            }

            // Set custom item totals (without tax)
            $item->set_subtotal( $subtotal );
            $item->set_total( $subtotal );

            // --- Add/Update line item meta ---
            // Pass associative array: ['_internal_note' => 'x', 'room_type' => 'Deluxe']
            if ( ! empty( $item_meta ) && is_array( $item_meta ) ) {
                foreach ( $item_meta as $key => $value ) {
                    // update_meta_data prevents duplicate keys; use add_meta_data for duplicates
                    $item->update_meta_data( $key, $value );
                }
            }

            // Save the line item with meta and totals
            $item->save();
        }

        // 8. Set the order total
        if ( !is_numeric($total_paid) ) {
            $total_paid = $order->get_total(); // If total_paid is not provided, use get the current total
        }
        
        $order->set_total( $total_paid );  // without tax

        if (!empty($status) && $status !== $order->get_status()) {
            $order->update_status( $status, 'Order updated programmatically.' );
        }

        // 9. Add custom order meta (loop through meta_data array)
        if ( ! empty($meta_data) && is_array($meta_data) ) {
            foreach ($meta_data as $key => $value) {
                $order->update_meta_data($key, $value);
            }
        }

        // Save the order
        $order->save();

        return true; // Order updated successfully

    }

    function create_order_after_save_booking_post($booking_id, $booking, $update){

       
        if( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $booking->post_type !== 'eshb_booking' ) return;

        static $processing = false;

        if ( $processing ) {
            return;
        }

        if ( $update !== true) {
            return;
        }

        if ( ! isset( $_POST['nonce'] ) ||
            ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action') ) ) {
            $new_status = $booking->post_status;
        }else{
            $new_status = !empty($_POST['post_status']) ? sanitize_text_field( wp_unslash( $_POST['post_status'] ) ) : 'not found';
        }

        $processing = true;
        $order_id = self::get_linked_order_id( $booking_id );

        $booking_status = $booking->post_status;
        

        // create woocommerce order if post status is 
        $allowed_statuses = array_keys(ESHB_Helper::eshb_get_booking_statuses());
        if( !in_array($booking_status, $allowed_statuses) ) {
            return;
        }

        $eshb_settings = get_option('eshb_settings');
        $booking_type = isset($eshb_settings['booking-type']) ? $eshb_settings['booking-type'] : '';

        $room_visibility = in_array('rooms', $eshb_settings['booking-form-fields']) ? true : false;
        $visible_fields  = $eshb_settings['booking-form-fields'] ?? [];

        $thumbnail_id = get_post_meta( $booking_id, '_thumbnail_id', true );
        $address = get_post_meta( $booking_id, 'eshb_booking_customer_details_metaboxes', true);

        $eshb_booking_metaboxes = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true);
        $accomodation_id = !empty($eshb_booking_metaboxes['booking_accomodation_id']) ? $eshb_booking_metaboxes['booking_accomodation_id'] : '';
        $room_quantity = !empty($eshb_booking_metaboxes['room_quantity']) ? $eshb_booking_metaboxes['room_quantity'] : 1;
        $extra_bed_quantity = !empty($eshb_booking_metaboxes['extra_bed_quantity']) ? $eshb_booking_metaboxes['extra_bed_quantity'] : 0;
        $adult_quantity = !empty($eshb_booking_metaboxes['adult_quantity']) ? $eshb_booking_metaboxes['adult_quantity'] : 1;
        $children_quantity = !empty($eshb_booking_metaboxes['children_quantity']) ? $eshb_booking_metaboxes['children_quantity'] : 0;
        // The booking stores its dates as booking_start_date / booking_end_date;
        // the old start_date / end_date keys don't exist and wrote empty dates
        // onto the order once the order link stopped being lost.
        $start_date = !empty($eshb_booking_metaboxes['booking_start_date']) ? $eshb_booking_metaboxes['booking_start_date'] : '';
        $end_date = !empty($eshb_booking_metaboxes['booking_end_date']) ? $eshb_booking_metaboxes['booking_end_date'] : '';
        $dates = !empty($eshb_booking_metaboxes['dates']) ? $eshb_booking_metaboxes['dates'] : '';
        $details_html = !empty($eshb_booking_metaboxes['details_html']) ? $eshb_booking_metaboxes['details_html'] : '';
        $extra_services = !empty($eshb_booking_metaboxes['extra_services']) ? $eshb_booking_metaboxes['extra_services'] : '';
        $subtotal_price = !empty($eshb_booking_metaboxes['subtotal_price']) ? $eshb_booking_metaboxes['subtotal_price'] : 0;
        $base_price = !empty($eshb_booking_metaboxes['base_price']) ? $eshb_booking_metaboxes['base_price'] : 0;
        $total_price = !empty($eshb_booking_metaboxes['total_price']) ? $eshb_booking_metaboxes['total_price'] : 0;
        $extra_services_charge = !empty($eshb_booking_metaboxes['extra_service_price']) ? $eshb_booking_metaboxes['extra_service_price'] : 0;
        $extra_bed_price = !empty($eshb_booking_metaboxes['extra_bed_price']) ? $eshb_booking_metaboxes['extra_bed_price'] : 0;
        $total_paid = !empty($eshb_booking_metaboxes['total_paid']) ? $eshb_booking_metaboxes['total_paid'] : 0;
        $new_due = (float) $total_price - (float) $total_paid;
        $extra_services_html = $this->format_extra_services($extra_services, $room_visibility);
        $details_html = $this->format_details($eshb_booking_metaboxes, $visible_fields);
        
        if(!empty($accomodation_id)){
            $meta_data = [
                'Accomodation ID' => $accomodation_id,
                'Total Price' => $total_price,
                'Subtotal Price' => $subtotal_price,
                'Base Price' => $base_price,
                'Extra Services Charge' => $extra_services_charge,
                'Extra Bed Price' => $extra_bed_price,
                'Rooms' => $room_quantity,
                'Extra Bed' => $extra_bed_quantity,
                'Adults' => $adult_quantity,
                'Children' => $children_quantity,
                'Start Date' => $start_date,
                'End Date' => $end_date,
                'Date' => $dates,
                'Details' => $details_html,
                'Extra Services IDs' => $extra_services,
                'Extra Services' => $extra_services_html,
            ];
            
            $eshb_booking_metaboxes['extra_services_html'] = $extra_services_html; 

            // update woocommerce order if it exists
            if(!empty($order_id)) {

               
                $is_done_initial_update = get_post_meta($booking_id, 'is_done_initial_update', true);
                

                if($is_done_initial_update == 'yes') {

                    // booking status change
                    if($new_due > 0 && $total_paid > 0){
                        $booking_status = 'deposit-payment';
                    }

                    if($new_due > 0){
                        update_post_meta($order_id, 'eshb_booking_due_amount', $new_due);
                    }
                    
                    if($new_due < 1){

                        if (!empty($eshb_settings['booking-auto-approval']) && $eshb_settings['booking-auto-approval'] == true) {
                            $booking_status = 'completed';
                        }else{
                            $booking_status = 'processing';
                        }
                        delete_post_meta( $order_id, 'eshb_booking_due_amount');
                    }

                    if(in_array($new_status, ['processing', 'on-hold', 'cancelled', 'failed', 'refunded', 'trash'])){
                        $booking_status = $new_status;
                    }


                    /**
                     * Whether saving a booking rewrites its WooCommerce order: the
                     * first line item is replaced and the order total is set to the
                     * booking's total paid.
                     *
                     * On by default only for orders created for an admin-entered
                     * booking (EHB Manual Booking stores those in
                     * `_order_post_created`), whose order is meant to follow the
                     * booking. A guest's checkout order is never rewritten: it may
                     * hold other rooms, and its total would become the amount paid
                     * so far (0 for an unpaid bank transfer). Its status still
                     * follows the booking through ESHB_Booking's status sync.
                     *
                     * @param bool $rewrite    Default: true for admin-created orders.
                     * @param int  $order_id   Linked WooCommerce order.
                     * @param int  $booking_id Booking being saved.
                     */
                    $admin_created_order = absint( get_post_meta( $booking_id, '_order_post_created', true ) ) === (int) $order_id;
                    $rewrite_order = apply_filters( 'eshb_admin_booking_rewrite_woocommerce_order', $admin_created_order, $order_id, $booking_id );

                    if ($rewrite_order && $booking_type == 'woocommerce' && class_exists( 'woocommerce' )) {
                        $product_id = get_post_meta($accomodation_id, '_woocommerce_product_id', true);
                        $this->update_woocommerce_order($order_id, $subtotal_price, $total_paid, $booking_status, $product_id, $address, $meta_data);

                        // assign accomodation_id to woocommerce product
                        update_post_meta($product_id, '_accomodation_id', $accomodation_id);
                    }
                }
                
                update_post_meta($booking_id, 'is_done_initial_update', 'yes');

                $processing = false; 
                return;
            }else{

                /**
                 * Fires when an admin saves a booking that has no order linked yet.
                 *
                 * @param int   $booking_id Booking post ID.
                 * @param array $args       Prepared booking data.
                 */
                do_action( 'eshb_admin_booking_saved_without_order', $booking_id, array(
                    'booking_type'    => $booking_type,
                    'booking_status'  => $booking_status,
                    'accomodation_id' => $accomodation_id,
                    'thumbnail_id'    => $thumbnail_id,
                    'address'         => $address,
                    'subtotal_price'  => $subtotal_price,
                    'new_due'         => $new_due,
                    'meta_data'       => $meta_data,
                ) );

                $processing = false;
                return;
            }
        }
    }

    function eshb_update_booking_title_on_save( $post_id, $post, $update ) {

        // Skip autosave/revision
        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Run only on update (not insert)
        if ( !$update ) {
            return;
        }

        // Skip if deleting, trashing, or being moved to trash
        if ( in_array( $post->post_status, [ 'trash', 'auto-draft' ], true ) ) {
            return;
        }

        // Re-entrancy guard
        static $running = false;
        if ( $running ) return;
        $running = true;

        if ( ! isset( $_POST['nonce'] ) ||
            ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action') ) ) {
            $eshb_booking_metaboxes = get_post_meta( $post_id, 'eshb_booking_metaboxes', true );
        }else{
            $eshb_booking_metaboxes = !empty($_POST['eshb_booking_metaboxes']) ? map_deep( wp_unslash( $_POST['eshb_booking_metaboxes']), 'sanitize_text_field' ) : $eshb_booking_metaboxes;
        }


        $accommodation_id = is_array( $eshb_booking_metaboxes ) && ! empty( $eshb_booking_metaboxes['booking_accomodation_id'] ) ? (int) $eshb_booking_metaboxes['booking_accomodation_id'] : 0;
        $accommodation_title = $accommodation_id ? get_the_title( $accommodation_id ) : '';
        $new_title = 'Booking #' . $post_id . ( $accommodation_title ? ' for: ' . $accommodation_title : '' );
    
        // update title if its not same to old title
        if ( get_post_field( 'post_title', $post_id ) !== $new_title ) {
            // Recursion is prevented by the static $running guard above; no
            // need to remove/re-add the hook here.
            wp_update_post( [
                'ID' => $post_id,
                'post_title' => $new_title,
                ] );
        }

        // Prevent duplicate execution
        set_transient( 'eshb_manual_booking_done_' . $post_id, true, 10 ); // expires
        $running = false;
    }

    /**
     * Build extra services titles.
     */
    private function format_extra_services($extra_services, $room_visibility) {
        if (empty($extra_services) || !is_array($extra_services)) return '';

        $titles = [];
        foreach ($extra_services as $service) {
            $service_id = $service['id'];
            $qty        = (int) ($service['quantity'] ?? 0);
            $title      = get_the_title($service_id);

            if ($qty > 0) {
                $meta        = get_post_meta($service_id, 'eshb_service_metaboxes', true);
                $charge_type = $meta['service_charge_type'] ?? '';
                if ($room_visibility || $charge_type !== 'room') {
                    $title .= " For {$qty} " . ucfirst(strtolower($charge_type));
                }
            }
            $titles[] = $title;
        }
        return implode(', ', $titles);
    }

    /**
     * Build booking details string.
     */
    private function format_details($data, $visible_fields) {
        $map = [
            'rooms'      => ['label' => 'Room',     'qty' => $data['room_quantity']],
            'extra_beds' => ['label' => 'Extra Bed','qty' => $data['extra_bed_quantity']],
            'adults'     => ['label' => 'Adult',    'qty' => $data['adult_quantity']],
            'childrens'  => ['label' => 'Children', 'qty' => $data['children_quantity']],
        ];

        $details = [];
        foreach ($map as $key => $info) {
            if (in_array($key, $visible_fields) && $info['qty'] > 0) {
                $details[] = "{$info['label']}: {$info['qty']}";
            }
        }
        return implode(', ', $details);
    }


}
if(is_admin()) {
    new ESHB_Admin_Booking();
}