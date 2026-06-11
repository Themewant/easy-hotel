<?php
/**
 * Booking read/cancel service for the Native Checkout account area.
 *
 * Centralizes everything the account pages and the cancellation system
 * need so the logic lives in one place:
 *
 *   - View-models for the bookings table and the detailed booking view.
 *   - `can_cancel_booking()` — the single source of truth for whether a
 *     booking may be cancelled (status, ownership, check-in date, filter).
 *   - `process_cancellation()` — idempotent routine that flips the status,
 *     records cancellation metadata, restores room availability, fires the
 *     extensibility hooks and triggers notification emails. Invoked from
 *     both the customer AJAX flow and the admin status transition.
 *
 * @package EasyHotel\NativeCheckout\Account
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_Account_Bookings {

    /** Statuses from which a booking may still be cancelled. */
    const CANCELLABLE_STATUSES = [ 'pending', 'on-hold', 'processing', 'deposit-payment' ];

    /** Guard meta so cancellation side-effects run exactly once. */
    const META_PROCESSED = '_eshb_cancellation_processed';

    /**
     * Cancellation metadata is stored in dedicated flat post meta (not just
     * inside the serialized eshb_booking_metaboxes array) so it survives a
     * subsequent admin metabox save, which rewrites that whole array.
     */
    const META_CANCELLED_AT = '_eshb_cancelled_at';
    const META_CANCEL_REASON = '_eshb_cancellation_reason';
    const META_CANCELLED_BY = '_eshb_cancelled_by';

    /** Refund tracking (flat meta, same survival rationale as above). */
    const META_TOTAL_REFUNDED = '_eshb_total_refunded';
    const META_REFUNDS = '_eshb_refunds';

    /** @var ESHB_Native_Account_Customer */
    private $customer;

    public function __construct( ESHB_Native_Account_Customer $customer ) {
        $this->customer = $customer;
    }

    /* -----------------------------------------------------------------
     * Read helpers / view-models
     * -------------------------------------------------------------- */

    /**
     * Raw booking meta + customer meta for a booking, or null if missing.
     *
     * @return array|null { meta: array, customer: array }
     */
    public function get_booking_data( $booking_id ) {
        $booking_id = (int) $booking_id;
        if ( ! $booking_id || get_post_type( $booking_id ) !== 'eshb_booking' ) {
            return null;
        }
        $meta     = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
        $customer = get_post_meta( $booking_id, 'eshb_booking_customer_details_metaboxes', true );
        return [
            'meta'     => is_array( $meta ) ? $meta : [],
            'customer' => is_array( $customer ) ? $customer : [],
        ];
    }

    /**
     * Compact view-model for one row of the bookings table.
     *
     * @return array
     */
    public function get_row_view( $booking_id ) {
        $data = $this->get_booking_data( $booking_id );
        if ( ! $data ) {
            return [];
        }
        $meta   = $data['meta'];
        $core   = new ESHB_Core();
        $status = (string) ( $meta['booking_status'] ?? get_post_status( $booking_id ) );

        return [
            'id'             => (int) $booking_id,
            'accomodation'   => get_the_title( (int) ( $meta['booking_accomodation_id'] ?? 0 ) ),
            'room_quantity'  => (int) ( $meta['room_quantity'] ?? 1 ),
            'check_in'       => (string) ( $meta['booking_start_date'] ?? '' ),
            'check_out'      => (string) ( $meta['booking_end_date'] ?? '' ),
            'check_in_label' => $this->format_date( $meta['booking_start_date'] ?? '' ),
            'check_out_label'=> $this->format_date( $meta['booking_end_date'] ?? '' ),
            'guests'         => (int) ( $meta['adult_quantity'] ?? 0 ) + (int) ( $meta['children_quantity'] ?? 0 ),
            'total_html'     => $core->eshb_price( (float) ( $meta['total_price'] ?? 0 ) ),
            'status'         => $status,
            'status_label'   => $this->status_label( $status ),
            'can_cancel'     => $this->can_cancel_booking( $booking_id ) === true,
        ];
    }

    /**
     * Full detail view-model for the "View" modal.
     *
     * @return array
     */
    public function get_detail_view( $booking_id ) {
        $data = $this->get_booking_data( $booking_id );
        if ( ! $data ) {
            return [];
        }
        $meta     = $data['meta'];
        $customer = $data['customer'];
        $core     = new ESHB_Core();
        $status   = (string) ( $meta['booking_status'] ?? get_post_status( $booking_id ) );

        return [
            'id'              => (int) $booking_id,
            'status'          => $status,
            'status_label'    => $this->status_label( $status ),
            'accomodation'    => get_the_title( (int) ( $meta['booking_accomodation_id'] ?? 0 ) ),
            'check_in_label'  => $this->format_date( $meta['booking_start_date'] ?? '' ),
            'check_out_label' => $this->format_date( $meta['booking_end_date'] ?? '' ),
            'check_in_time'   => (string) ( $meta['booking_start_time'] ?? '' ),
            'check_out_time'  => (string) ( $meta['booking_end_time'] ?? '' ),
            'room_quantity'   => (int) ( $meta['room_quantity'] ?? 1 ),
            'adults'          => (int) ( $meta['adult_quantity'] ?? 0 ),
            'children'        => (int) ( $meta['children_quantity'] ?? 0 ),
            'extra_beds'      => (int) ( $meta['extra_bed_quantity'] ?? 0 ),
            'extra_services'  => (string) ( $meta['extra_services_html'] ?? '' ),
            'coupon_code'     => (string) ( $meta['coupon_code'] ?? '' ),
            'coupon_html'     => $core->eshb_price( (float) ( $meta['coupon_discount'] ?? 0 ) ),
            'tax_html'        => $core->eshb_price( (float) ( $meta['tax_amount'] ?? 0 ) ),
            'subtotal_html'   => $core->eshb_price( (float) ( $meta['subtotal_price'] ?? 0 ) ),
            'total_html'      => $core->eshb_price( (float) ( $meta['total_price'] ?? 0 ) ),
            'paid_html'       => $core->eshb_price( (float) ( $meta['total_paid'] ?? 0 ) ),
            'gateway'         => (string) ( $meta['payment_gateway'] ?? '' ),
            'customer_name'   => trim( ( $customer['first_name'] ?? '' ) . ' ' . ( $customer['last_name'] ?? '' ) ),
            'customer_email'  => (string) ( $customer['email'] ?? '' ),
            'customer_phone'  => (string) ( $customer['phone'] ?? '' ),
            'cancelled_at'    => (string) ( get_post_meta( $booking_id, self::META_CANCELLED_AT, true ) ?: ( $meta['cancelled_at'] ?? '' ) ),
            'cancel_reason'   => (string) ( get_post_meta( $booking_id, self::META_CANCEL_REASON, true ) ?: ( $meta['cancellation_reason'] ?? '' ) ),
            'cancelled_by'    => (string) ( get_post_meta( $booking_id, self::META_CANCELLED_BY, true ) ?: ( $meta['cancelled_by'] ?? '' ) ),
        ];
    }

    /**
     * Aggregate dashboard counters for a user's bookings.
     *
     * @return array
     */
    public function get_dashboard_stats( $user = null ) {
        $ids   = $this->customer->get_user_booking_ids( $user );
        $stats = [ 'total' => count( $ids ), 'active' => 0, 'cancelled' => 0, 'upcoming' => 0, 'completed' => 0 ];
        $today = current_time( 'Y-m-d' );

        foreach ( $ids as $id ) {
            $meta   = get_post_meta( $id, 'eshb_booking_metaboxes', true );
            $status = is_array( $meta ) ? (string) ( $meta['booking_status'] ?? '' ) : (string) get_post_status( $id );
            $start  = is_array( $meta ) ? (string) ( $meta['booking_start_date'] ?? '' ) : '';

            if ( 'cancelled' === $status ) {
                $stats['cancelled']++;
            } elseif ( 'completed' === $status ) {
                $stats['completed']++;
            } else {
                $stats['active']++;
            }
            if ( 'cancelled' !== $status && $start && $start >= $today ) {
                $stats['upcoming']++;
            }
        }
        return $stats;
    }

    /* -----------------------------------------------------------------
     * Cancellation rules
     * -------------------------------------------------------------- */

    /**
     * Whether a booking can be cancelled by the current context.
     *
     * Returns true when allowed, or a WP_Error describing why not — so
     * callers can surface a precise message. The
     * `easy_hotel_can_cancel_booking` filter gets the final say.
     *
     * @param int  $booking_id
     * @param bool $check_ownership Enforce that the current user owns it
     *                              (true for the customer flow; false for
     *                              trusted admin/system callers).
     * @return true|WP_Error
     */
    public function can_cancel_booking( $booking_id, $check_ownership = true ) {
        $booking_id = (int) $booking_id;

        $result = true;

        if ( ! $booking_id || get_post_type( $booking_id ) !== 'eshb_booking' ) {
            $result = new WP_Error( 'not_found', __( 'Booking not found.', 'easy-hotel' ) );
        } elseif ( $check_ownership && ! $this->customer->user_owns_booking( $booking_id ) ) {
            $result = new WP_Error( 'forbidden', __( 'You are not allowed to cancel this booking.', 'easy-hotel' ) );
        } else {
            $meta   = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
            $status = is_array( $meta ) ? (string) ( $meta['booking_status'] ?? '' ) : (string) get_post_status( $booking_id );

            if ( ! in_array( $status, self::CANCELLABLE_STATUSES, true ) ) {
                $result = new WP_Error( 'not_cancellable', __( 'This booking can no longer be cancelled.', 'easy-hotel' ) );
            } else {
                $check_in = is_array( $meta ) ? (string) ( $meta['booking_start_date'] ?? '' ) : '';
                if ( $check_in && $check_in < current_time( 'Y-m-d' ) ) {
                    $result = new WP_Error( 'past_checkin', __( 'The check-in date has already passed.', 'easy-hotel' ) );
                }
            }
        }

        /**
         * Final say over whether a booking may be cancelled.
         *
         * @param true|WP_Error $result     true if allowed, WP_Error otherwise.
         * @param int           $booking_id
         */
        return apply_filters( 'easy_hotel_can_cancel_booking', $result, $booking_id );
    }

    /* -----------------------------------------------------------------
     * Cancellation processing
     * -------------------------------------------------------------- */

    /**
     * Cancel a booking and run all side-effects exactly once.
     *
     * Idempotent: a `_eshb_cancellation_processed` guard is written before
     * the status transition so the re-entrant `transition_post_status`
     * hook (fired by our own wp_update_post) does not double-process.
     *
     * @param int    $booking_id
     * @param string $reason  Optional cancellation reason.
     * @param string $by      'customer' | 'admin' | 'system'.
     * @param bool   $silent  Skip the cancellation emails when true.
     * @return true|WP_Error
     */
    public function process_cancellation( $booking_id, $reason = '', $by = 'system', $silent = false ) {
        $booking_id = (int) $booking_id;
        if ( ! $booking_id || get_post_type( $booking_id ) !== 'eshb_booking' ) {
            return new WP_Error( 'not_found', __( 'Booking not found.', 'easy-hotel' ) );
        }

        // Already handled (e.g. re-entrant transition hook) — no-op.
        if ( get_post_meta( $booking_id, self::META_PROCESSED, true ) ) {
            return true;
        }

        $by     = in_array( $by, [ 'customer', 'admin', 'system' ], true ) ? $by : 'system';
        $reason = sanitize_textarea_field( $reason );

        /**
         * Fires before a booking is cancelled.
         *
         * @param int $booking_id
         */
        do_action( 'easy_hotel_before_booking_cancelled', $booking_id );

        // Set the guard + metadata BEFORE the status change so the
        // re-entrant transition hook short-circuits.
        update_post_meta( $booking_id, self::META_PROCESSED, 1 );

        $meta = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
        if ( ! is_array( $meta ) ) {
            $meta = [];
        }
        $previous_status               = (string) ( $meta['booking_status'] ?? get_post_status( $booking_id ) );
        $cancelled_at                  = current_time( 'mysql' );
        $meta['booking_status']        = 'cancelled';
        $meta['previous_status']       = $previous_status;
        $meta['cancelled_at']          = $cancelled_at;
        $meta['cancellation_reason']   = $reason;
        $meta['cancelled_by']          = $by;
        update_post_meta( $booking_id, 'eshb_booking_metaboxes', $meta );

        // Mirror into dedicated flat meta so an admin metabox re-save (which
        // rewrites eshb_booking_metaboxes) cannot wipe the cancellation trail.
        update_post_meta( $booking_id, self::META_CANCELLED_AT, $cancelled_at );
        update_post_meta( $booking_id, self::META_CANCEL_REASON, $reason );
        update_post_meta( $booking_id, self::META_CANCELLED_BY, $by );

        // Flip the post status (kept in lockstep with the meta status).
        wp_update_post( [ 'ID' => $booking_id, 'post_status' => 'cancelled' ] );

        // Release the rooms this booking was holding.
        $this->restore_room_availability( $booking_id, $meta );

        $booking_data = $this->get_detail_view( $booking_id );

        if ( ! $silent ) {
            ESHB_Native_Account_Email::send_cancellation_emails( $booking_id, $booking_data );
        }

        /**
         * Fires after a booking has been cancelled.
         *
         * @param int   $booking_id
         * @param array $booking_data Detail view-model of the cancelled booking.
         */
        do_action( 'easy_hotel_booking_cancelled', $booking_id, $booking_data );

        return true;
    }

    /**
     * Give back the rooms a cancelled booking was holding, mirroring the
     * decrement performed in ESHB_Native_Booking_Handler::insert_booking().
     */
    private function restore_room_availability( $booking_id, array $meta ) {
        $accomodation_id = (int) ( $meta['booking_accomodation_id'] ?? 0 );
        if ( ! $accomodation_id ) {
            return;
        }
        $accom_meta = get_post_meta( $accomodation_id, 'eshb_accomodation_metaboxes', true );
        if ( ! is_array( $accom_meta ) ) {
            return;
        }
        $total_rooms   = ! empty( $accom_meta['total_rooms'] ) ? floatval( $accom_meta['total_rooms'] ) : 0;
        $current_avail = ! empty( $accom_meta['available_rooms'] ) ? floatval( $accom_meta['available_rooms'] ) : 0;
        $room_quantity = ! empty( $meta['room_quantity'] ) ? floatval( $meta['room_quantity'] ) : 1;

        $restored = $current_avail + $room_quantity;
        if ( $total_rooms > 0 ) {
            $restored = min( $restored, $total_rooms ); // never exceed capacity
        }
        $accom_meta['available_rooms'] = $restored;
        update_post_meta( $accomodation_id, 'eshb_accomodation_metaboxes', $accom_meta );
    }

    /* -----------------------------------------------------------------
     * Refunds
     * -------------------------------------------------------------- */

    /**
     * Record a manual refund against a booking. The amount is deducted
     * from `total_paid`; a running `total_refunded` and a refund log are
     * kept (in both the metaboxes array and flat meta so an admin save
     * cannot wipe them).
     *
     * @param int    $booking_id
     * @param float  $amount  Amount to refund (must be > 0 and ≤ amount paid).
     * @param string $by      Who issued it ('admin' by default).
     * @return array|WP_Error { total_paid, total_refunded, refunded } or error.
     */
    public function process_refund( $booking_id, $amount, $by = 'admin' ) {
        $booking_id = (int) $booking_id;
        if ( ! $booking_id || get_post_type( $booking_id ) !== 'eshb_booking' ) {
            return new WP_Error( 'not_found', __( 'Booking not found.', 'easy-hotel' ) );
        }

        $meta = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
        if ( ! is_array( $meta ) ) {
            $meta = [];
        }

        $paid   = (float) ( $meta['total_paid'] ?? 0 );
        $amount = round( (float) $amount, 2 );

        if ( $amount <= 0 ) {
            return new WP_Error( 'invalid_amount', __( 'Please enter a valid refund amount.', 'easy-hotel' ) );
        }
        if ( $amount > $paid + 0.0001 ) {
            return new WP_Error( 'exceeds_paid', __( 'Refund amount cannot exceed the amount paid.', 'easy-hotel' ) );
        }

        $new_paid       = max( 0, round( $paid - $amount, 2 ) );
        $total_refunded = round( (float) ( $meta['total_refunded'] ?? 0 ) + $amount, 2 );

        /**
         * Fires before a refund is recorded.
         *
         * @param int   $booking_id
         * @param float $amount
         */
        do_action( 'eshb_native_checkout_before_booking_refunded', $booking_id, $amount );

        $meta['total_paid']     = $new_paid;
        $meta['total_refunded'] = $total_refunded;
        update_post_meta( $booking_id, 'eshb_booking_metaboxes', $meta );

        // Flat meta + log (survive a metabox re-save).
        update_post_meta( $booking_id, self::META_TOTAL_REFUNDED, $total_refunded );
        $log = get_post_meta( $booking_id, self::META_REFUNDS, true );
        if ( ! is_array( $log ) ) {
            $log = [];
        }
        $log[] = [
            'amount' => $amount,
            'date'   => current_time( 'mysql' ),
            'by'     => in_array( $by, [ 'admin', 'system' ], true ) ? $by : 'admin',
        ];
        update_post_meta( $booking_id, self::META_REFUNDS, $log );

        /**
         * Fires after a refund has been recorded.
         *
         * @param int   $booking_id
         * @param float $amount   Amount refunded this time.
         * @param float $new_paid Remaining amount paid after the refund.
         */
        do_action( 'eshb_native_checkout_booking_refunded', $booking_id, $amount, $new_paid );

        return [
            'total_paid'     => $new_paid,
            'total_refunded' => $total_refunded,
            'refunded'       => $amount,
        ];
    }

    /**
     * Refund log entries for a booking (newest stored last).
     *
     * @return array[]
     */
    public function get_refunds( $booking_id ) {
        $log = get_post_meta( (int) $booking_id, self::META_REFUNDS, true );
        return is_array( $log ) ? $log : [];
    }

    /* -----------------------------------------------------------------
     * Status presentation
     * -------------------------------------------------------------- */

    /**
     * Human label for a status slug (falls back to a title-cased slug).
     */
    public function status_label( $status ) {
        $statuses = ESHB_Helper::eshb_get_booking_statuses();
        if ( isset( $statuses[ $status ] ) ) {
            return $statuses[ $status ];
        }
        return ucwords( str_replace( [ '-', '_' ], ' ', (string) $status ) );
    }

    /**
     * Format a Y-m-d date with the site's date format.
     */
    private function format_date( $date ) {
        $date = (string) $date;
        if ( '' === $date ) {
            return '';
        }
        $ts = strtotime( $date );
        return $ts ? date_i18n( get_option( 'date_format' ), $ts ) : $date;
    }
}
