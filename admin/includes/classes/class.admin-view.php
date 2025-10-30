<?php
class ESHB_Admin_View {
    
    public static function eshb_show_payment_history_in_admin( $booking_id ) {

        //$booking_id = get_post_meta($order_id, '_booking_post_created', true);
        $eshb_booking_metaboxes = get_post_meta($booking_id, 'eshb_booking_metaboxes', true);
        $booking_status = !empty($eshb_booking_metaboxes['booking_status']) ? $eshb_booking_metaboxes['booking_status'] : [];
        $total_price = !empty($eshb_booking_metaboxes['total_price']) ? $eshb_booking_metaboxes['total_price'] : 0;
        $total_due = !empty($eshb_booking_metaboxes['total_due']) ? $eshb_booking_metaboxes['total_due'] : 0;
        $payment_ids = !empty($eshb_booking_metaboxes['payment_ids']) ? $eshb_booking_metaboxes['payment_ids'] : [];

        $hotel_core = new ESHB_Core();
    
        echo '<div class="eshb-payment-history"><h3>' . esc_html__( 'Payment History', 'easy-hotel' ) . '</h3>';
        echo '<table class="wp-list-table"><thead><tr>';
        echo '<th>' . esc_html__( 'Date', 'easy-hotel' ) . '</th>';
        echo '<th>' . esc_html__( 'Gateway', 'easy-hotel' ) . '</th>';
        echo '<th>' . esc_html__( 'Note', 'easy-hotel' ) . '</th>';
        echo '<th>' . esc_html__( 'Amount', 'easy-hotel' ) . '</th>';
        echo '</tr></thead><tbody>';
    
        $total_paid = 0;

        

        foreach ( $payment_ids as $payment_id ) {
            $eshb_payment_metaboxes = get_post_meta($payment_id, 'eshb_payment_metaboxes', true);
            $amt   = !empty( $eshb_payment_metaboxes['amount'] ) ? (float) $eshb_payment_metaboxes['amount'] : 0;
            $curr  = !empty( $eshb_payment_metaboxes['currency'] ) ? $eshb_payment_metaboxes['currency'] : $hotel_core->get_eshb_currency_symbol();
            $date  = get_the_date( '', $payment_id );
            $gate  = !empty( $eshb_payment_metaboxes['gateway'] ) ? $eshb_payment_metaboxes['gateway'] : '';
            $note  = !empty( $eshb_payment_metaboxes['note'] ) ? $eshb_payment_metaboxes['note'] : '';
            $total_paid += $amt;
            echo '<tr>';
            echo '<td>' . esc_html( $date ) . '</td>';
            echo '<td>' . esc_html( $gate ) . '</td>';
            echo '<td>' . esc_html( $note ) . '</td>';
            echo '<td>' . wp_kses_post( $hotel_core->eshb_price($amt) ) . '</td>';
            echo '</tr>';
        }

        echo '<tr>';
        echo '<td colspan="3"><strong>' . esc_html__( 'Total Paid', 'easy-hotel' ) . '</strong></td>';


        if(count($payment_ids) < 1 && in_array($booking_status, ['completed', 'processing'])) {
            $total_paid = $total_price;
        }

        echo '<td><strong>' . wp_kses_post( $hotel_core->eshb_price($total_paid) ) . '</strong></td>';
        echo '</tr>';


        if($total_paid < $total_price) {
            $total_due = $total_price - $total_paid;
            echo '<tr>';
            echo '<td colspan="3"><strong>' . esc_html__( 'Total Due', 'easy-hotel' ) . '</strong></td>';
            echo '<td><strong>' . wp_kses_post( $hotel_core->eshb_price($total_due) ) . '</strong></td>';
            echo '</tr>';
        }
        
    
        echo '</tbody></table>';
        if($total_paid < $total_price){
            $add_new_post_url = admin_url( 'post-new.php?post_type=eshb_payment&booking=' . $booking_id );
            $add_new_post_url_with_amount = admin_url( 'post-new.php?post_type=eshb_payment&booking=' . $booking_id . '&amount=' . $total_due);
            echo '<br>
        <a href="'. esc_url( $add_new_post_url ) .'" target="_blank" class="button button-primary">' . esc_html__( 'Add payment', 'easy-hotel' ) . '</a>
        <a href="'. esc_url( $add_new_post_url_with_amount ) .'" target="_blank" class="button button-primary">' . esc_html__( 'Add Due payment', 'easy-hotel' ) . '</a>
        </div>';
        }
        
    }
}