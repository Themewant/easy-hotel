<?php
/**
 * Native Checkout booking handler.
 *
 * Inserts the eshb_booking record after a successful payment and
 * keeps booking status / payment-record bookkeeping in lockstep with
 * the existing helper-based flow used by the WooCommerce gateway.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_Booking_Handler {

    /**
     * Insert booking with status 'on-hold' (per spec).
     *
     * @return int|false Booking post ID on success.
     */
    public static function insert_booking( array $reservation, array $customer, array $pricing ) {

        $accomodation_id = (int) ( $reservation['accomodation_id'] ?? 0 );
        if ( ! $accomodation_id ) return false;

        $accomodation_title = get_the_title( $accomodation_id );
        $total_price        = (float) ( $pricing['grandTotal'] ?? $pricing['totalPrice'] ?? 0 );
        $subtotal_price     = (float) ( $pricing['subtotalPrice'] ?? 0 );
        $base_price         = (float) ( $pricing['basePrice'] ?? 0 );
        $extra_services     = isset( $reservation['extra_services'] ) && is_array( $reservation['extra_services'] )
            ? $reservation['extra_services'] : [];

        // Build human-readable strings for backwards-compatibility with existing booking screens.
        $dates_label = date_i18n( get_option( 'date_format' ), strtotime( $reservation['start_date'] ?? 'now' ) )
            . ' - '
            . date_i18n( get_option( 'date_format' ), strtotime( $reservation['end_date'] ?? 'now' ) );
        if ( ! empty( $reservation['start_date'] ) && $reservation['start_date'] === ( $reservation['end_date'] ?? '' ) ) {
            $dates_label = date_i18n( get_option( 'date_format' ), strtotime( $reservation['start_date'] ) );
        }

        $extra_services_html = '';
        if ( ! empty( $extra_services ) ) {
            $titles = [];
            foreach ( $extra_services as $svc ) {
                if ( ! is_array( $svc ) || empty( $svc['id'] ) ) continue;
                $qty = ! empty( $svc['quantity'] ) ? (int) $svc['quantity'] : 1;
                $titles[] = get_the_title( (int) $svc['id'] ) . ' × ' . $qty;
            }
            $extra_services_html = implode( ', ', $titles );
        }

        $meta = [
            'booking_status'          => 'on-hold',
            'order_id'                => 0, // No WC order in native flow.
            'booking_accomodation_id' => $accomodation_id,
            'subtotal_price'          => $subtotal_price,
            'total_price'             => $total_price,
            'total_paid'              => 0,
            'base_price'              => $base_price,
            'extra_service_price'     => (float) ( $pricing['extraServicesPrice'] ?? 0 ),
            'extra_bed_price'         => (float) ( $pricing['extraBedPrice'] ?? 0 ),
            'booking_start_date'      => $reservation['start_date'] ?? '',
            'booking_end_date'        => $reservation['end_date'] ?? '',
            'booking_start_time'      => $reservation['start_time'] ?? '10:00',
            'booking_end_time'        => $reservation['end_time'] ?? '22:00',
            'dates'                   => $dates_label,
            'room_quantity'           => (int) ( $reservation['room_quantity'] ?? 1 ),
            'extra_bed_quantity'      => (int) ( $reservation['extra_bed_quantity'] ?? 0 ),
            'adult_quantity'          => (int) ( $reservation['adult_quantity'] ?? 1 ),
            'children_quantity'       => (int) ( $reservation['children_quantity'] ?? 0 ),
            'extra_services'          => $extra_services,
            'extra_services_html'     => $extra_services_html,
            'coupon_code'             => $pricing['couponCode'] ?? '',
            'coupon_discount'         => (float) ( $pricing['couponDiscount'] ?? 0 ),
            'tax_amount'              => (float) ( $pricing['taxAmount'] ?? 0 ),
            'payment_gateway'         => $customer['gateway'] ?? '',
            'gateway_source'          => 'native_checkout',
        ];

        $meta = apply_filters( 'eshb_native_checkout_booking_meta', $meta, $reservation, $customer, $pricing );

        $post_id = wp_insert_post( [
            'post_type'   => 'eshb_booking',
            'post_title'  => 'Booking',
            'post_status' => 'on-hold',
            'meta_input'  => [
                'eshb_booking_metaboxes'                       => $meta,
                'eshb_booking_customer_details_metaboxes'      => $customer,
            ],
        ] );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            return false;
        }

        wp_update_post( [
            'ID'          => $post_id,
            'post_title'  => 'Booking #' . $post_id . ' for: ' . $accomodation_title,
            'post_status' => 'on-hold',
        ] );

        // Reduce available rooms (mirrors ESHB_Helper::eshb_insert_booking behavior).
        $accom_meta = get_post_meta( $accomodation_id, 'eshb_accomodation_metaboxes', true );
        if ( is_array( $accom_meta ) ) {
            $total_rooms     = ! empty( $accom_meta['total_rooms'] ) ? floatval( $accom_meta['total_rooms'] ) : 1;
            $current_avail   = ! empty( $accom_meta['available_rooms'] ) ? floatval( $accom_meta['available_rooms'] ) : 0;
            $room_quantity   = ! empty( $meta['room_quantity'] ) ? floatval( $meta['room_quantity'] ) : 1;
            $accom_meta['available_rooms'] = ( $current_avail > 0 ? $current_avail : $total_rooms ) - $room_quantity;
            update_post_meta( $accomodation_id, 'eshb_accomodation_metaboxes', $accom_meta );
        }

        do_action( 'eshb_after_booking_created', $post_id, 0 );
        do_action( 'eshb_native_checkout_booking_created', $post_id, $reservation, $customer, $pricing );

        return $post_id;
    }

    /**
     * Transition a booking's status.
     */
    public static function update_status( $booking_id, $new_status ) {
        if ( ! $booking_id ) return false;

        $meta = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
        if ( is_array( $meta ) ) {
            $meta['booking_status'] = $new_status;
            update_post_meta( $booking_id, 'eshb_booking_metaboxes', $meta );
        }

        wp_update_post( [
            'ID'          => $booking_id,
            'post_status' => $new_status,
        ] );

        do_action( 'eshb_native_checkout_booking_status_changed', $booking_id, $new_status );
        return true;
    }

    /**
     * Record a payment under eshb_payment post type.
     */
    public static function record_payment( $booking_id, array $payment, array $customer ) {
        if ( ! $booking_id ) return false;

        $payment_id = wp_insert_post( [
            'post_title'  => 'Payment for Booking #' . $booking_id,
            'post_type'   => 'eshb_payment',
            'post_status' => 'completed',
        ] );

        if ( is_wp_error( $payment_id ) || ! $payment_id ) return false;

        $transaction_id = ! empty( $payment['transaction_id'] )
            ? $payment['transaction_id']
            : 'TXN-' . str_pad( $payment_id, 8, '0', STR_PAD_LEFT );

        $payment_options = [
            'booking_id'     => $booking_id,
            'transaction_id' => $transaction_id,
            'gateway'        => $payment['gateway'] ?? '',
            'gateway_mode'   => $payment['mode'] ?? 'live',
            'amount'         => (float) ( $payment['amount'] ?? 0 ),
            'fee'            => (float) ( $payment['fee'] ?? 0 ),
            'currency'       => $payment['currency'] ?? '',
            'payment_type'   => 'Full Payment',
        ];

        update_post_meta( $payment_id, 'eshb_payment_metaboxes', $payment_options );
        update_post_meta( $payment_id, 'eshb_payment_customer_details_metaboxes', $customer );

        // Mirror payment ids + total paid into the booking record.
        $meta = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
        if ( ! is_array( $meta ) ) $meta = [];
        $payment_ids = ! empty( $meta['payment_ids'] ) ? $meta['payment_ids'] : [];
        if ( ! in_array( $payment_id, $payment_ids ) ) {
            $payment_ids[] = $payment_id;
        }
        $meta['payment_ids']    = $payment_ids;
        $meta['total_paid']     = (float) ( $payment['amount'] ?? 0 );
        $meta['transaction_id'] = $transaction_id;
        update_post_meta( $booking_id, 'eshb_booking_metaboxes', $meta );
        update_post_meta( $booking_id, 'payment_status', 'completed' );

        return $payment_id;
    }
}
