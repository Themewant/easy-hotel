<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * One-time repair of WooCommerce bookings made before 2.1.0.
 *
 * Older versions stored only the last booking of an order on the order
 * (`_booking_post_created`), read a line-item meta key that is never written
 * for the extra-services charge, and gave every booking the subtotal of the
 * whole order. New bookings are stored correctly; this walks the existing
 * ones in small batches from wp-admin and:
 *
 *  - adds each booking to its order's `_eshb_booking_ids` list, so order
 *    status changes (e.g. a cancellation) reach every room of the order;
 *  - finds the booking's order line item (accommodation + dates) and fills
 *    an empty `extra_service_price`, replaces a whole-order `subtotal_price`
 *    with the room's own subtotal on multi-room orders, and records the
 *    `order_item_id`.
 *
 * Payment records are left as they are: they are the history of what was
 * received and when.
 */
class ESHB_WC_Booking_Repair {

    /** Option set once every booking has been checked. */
    const DONE_OPTION = 'eshb_wc_booking_repair_done';

    /** Highest booking ID already checked, so each batch continues from there. */
    const CURSOR_OPTION = 'eshb_wc_booking_repair_cursor';

    const BATCH = 50;

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'maybe_run' ] );
    }

    public static function maybe_run() {
        if ( get_option( self::DONE_OPTION ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'wc_get_order' ) ) {
            return;
        }

        global $wpdb;
        $cursor = (int) get_option( self::CURSOR_OPTION, 0 );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time batched maintenance query.
        $booking_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'eshb_booking' AND ID > %d ORDER BY ID ASC LIMIT %d",
            $cursor,
            self::BATCH
        ) );

        if ( empty( $booking_ids ) ) {
            update_option( self::DONE_OPTION, 1, false );
            delete_option( self::CURSOR_OPTION );
            return;
        }

        foreach ( $booking_ids as $booking_id ) {
            self::repair_booking( (int) $booking_id );
        }

        update_option( self::CURSOR_OPTION, (int) end( $booking_ids ), false );
    }

    /**
     * Repair one booking. Safe to run more than once.
     */
    public static function repair_booking( $booking_id ) {
        $meta = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
        if ( ! is_array( $meta ) || empty( $meta['order_id'] ) ) {
            return; // Native checkout / manual bookings without an order.
        }

        $order_id = absint( $meta['order_id'] );
        $order    = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        // 1. Order → all of its bookings.
        $list = get_post_meta( $order_id, '_eshb_booking_ids', true );
        $list = is_array( $list ) ? array_map( 'intval', $list ) : [];
        if ( ! in_array( $booking_id, $list, true ) ) {
            $list[] = $booking_id;
            update_post_meta( $order_id, '_eshb_booking_ids', array_values( array_unique( $list ) ) );
        }

        // 2. Booking ← its own line item.
        $hotel_items = [];
        foreach ( $order->get_items() as $item_id => $item ) {
            if ( $item->get_meta( 'Accomodation ID' ) ) {
                $hotel_items[ $item_id ] = $item;
            }
        }

        $item = null;
        if ( ! empty( $meta['order_item_id'] ) && isset( $hotel_items[ (int) $meta['order_item_id'] ] ) ) {
            $item = $hotel_items[ (int) $meta['order_item_id'] ];
        } else {
            foreach ( $hotel_items as $candidate ) {
                if (
                    (int) $candidate->get_meta( 'Accomodation ID' ) === (int) ( $meta['booking_accomodation_id'] ?? 0 ) &&
                    (string) $candidate->get_meta( 'Start Date' ) === (string) ( $meta['booking_start_date'] ?? '' ) &&
                    (string) $candidate->get_meta( 'End Date' ) === (string) ( $meta['booking_end_date'] ?? '' )
                ) {
                    $item = $candidate;
                    break;
                }
            }
        }

        if ( ! $item ) {
            return; // Can't tell which room of the order this is; leave it.
        }

        $changed = false;

        if ( empty( $meta['order_item_id'] ) ) {
            $meta['order_item_id'] = $item->get_id();
            $changed               = true;
        }

        $extra_services_charge = $item->get_meta( 'Extra Services Charge' );
        if ( ( ! isset( $meta['extra_service_price'] ) || '' === (string) $meta['extra_service_price'] ) && '' !== (string) $extra_services_charge ) {
            $meta['extra_service_price'] = $extra_services_charge;
            $changed                     = true;
        }

        // Only multi-room orders were given the whole-order subtotal, and only
        // a value still equal to it is replaced (an admin edit is kept).
        $item_subtotal = $item->get_meta( 'Subtotal Price' );
        if (
            count( $hotel_items ) > 1 &&
            '' !== (string) $item_subtotal &&
            isset( $meta['subtotal_price'] ) &&
            abs( (float) $meta['subtotal_price'] - (float) $order->get_subtotal() ) < 0.01 &&
            abs( (float) $meta['subtotal_price'] - (float) $item_subtotal ) >= 0.01
        ) {
            $meta['subtotal_price'] = $item_subtotal;
            $changed                = true;
        }

        if ( $changed ) {
            update_post_meta( $booking_id, 'eshb_booking_metaboxes', $meta );
        }
    }
}
ESHB_WC_Booking_Repair::init();
