<?php
/**
 * Ajax endpoints for the PMS module.
 *
 * @package Easy_Hotel\PMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles the rack drag/select interactions.
 */
class ESHB_PMS_Ajax {

    /**
     * Nonce action shared by every endpoint.
     */
    const NONCE = 'eshb_pms_ajax';

    /**
     * Hooks the endpoints in.
     */
    public static function register() {
        add_action( 'wp_ajax_eshb_pms_get_free_units', [ __CLASS__, 'get_free_units' ] );
        add_action( 'wp_ajax_eshb_pms_assign', [ __CLASS__, 'assign' ] );
        add_action( 'wp_ajax_eshb_pms_auto_assign_window', [ __CLASS__, 'auto_assign_window' ] );
    }

    /**
     * Validates the request and returns the booking, or dies with an error.
     *
     * @return array Normalised booking record.
     */
    protected static function require_booking() {

        check_ajax_referer( self::NONCE, 'nonce' );

        if ( ! current_user_can( ESHB_PMS::capability() ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'You are not allowed to do that.', 'easy-hotel' ) ], 403 );
        }

        $booking_id = isset( $_POST['booking'] ) ? (int) $_POST['booking'] : 0;
        $booking    = ESHB_PMS_Assignment::get_booking( $booking_id );

        if ( ! $booking ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Booking not found.', 'easy-hotel' ) ], 404 );
        }

        return $booking;
    }

    /**
     * Returns the units a booking could move into.
     */
    public static function get_free_units() {

        $booking = self::require_booking();

        $free    = ESHB_PMS_Assignment::get_free_units(
            $booking['accommodation_id'],
            $booking['start_date'],
            $booking['end_date'],
            $booking['id']
        );

        // Units held by the other slots of this same booking are not offered.
        $slot    = isset( $_POST['slot'] ) ? (int) $_POST['slot'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked in require_booking().
        $slots   = ESHB_PMS_Assignment::get_assignment( $booking['id'] );
        $current = isset( $slots[ $slot ] ) ? (int) $slots[ $slot ] : 0;

        unset( $slots[ $slot ] );

        $free  = array_values( array_diff( $free, array_filter( $slots ) ) );
        $units = [];

        foreach ( $free as $index ) {
            $units[] = [
                'index' => (int) $index,
                'label' => ESHB_PMS_Units::get_label( $booking['accommodation_id'], $index ),
            ];
        }

        wp_send_json_success(
            [
                'units'   => $units,
                'current' => $current,
            ]
        );
    }

    /**
     * Assigns (or releases) one slot of a booking.
     */
    public static function assign() {

        $booking = self::require_booking();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Checked in require_booking().
        $slot = isset( $_POST['slot'] ) ? (int) $_POST['slot'] : 0;
        $unit = isset( $_POST['unit'] ) ? (int) $_POST['unit'] : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        $result = ESHB_PMS_Assignment::assign_slot( $booking['id'], $slot, $unit );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ], 409 );
        }

        wp_send_json_success(
            [
                'label' => $unit
                    ? ESHB_PMS_Units::get_label( $booking['accommodation_id'], $unit )
                    : esc_html__( 'Unassigned', 'easy-hotel' ),
            ]
        );
    }

    /**
     * Fills every empty slot found in the visible window.
     */
    public static function auto_assign_window() {

        check_ajax_referer( self::NONCE, 'nonce' );

        if ( ! current_user_can( ESHB_PMS::capability() ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'You are not allowed to do that.', 'easy-hotel' ) ], 403 );
        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Checked above.
        $accommodation_id = isset( $_POST['accommodation'] ) ? (int) $_POST['accommodation'] : 0;
        $from             = isset( $_POST['from'] ) ? sanitize_text_field( wp_unslash( $_POST['from'] ) ) : '';
        $to               = isset( $_POST['to'] ) ? sanitize_text_field( wp_unslash( $_POST['to'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if ( ! $accommodation_id ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Pick an accommodation first.', 'easy-hotel' ) ], 400 );
        }

        $pending  = ESHB_PMS_Assignment::get_unassigned_bookings( $accommodation_id, $from, $to );
        $assigned = 0;

        // Earliest arrivals first, so the low unit numbers fill up predictably.
        uasort(
            $pending,
            function ( $a, $b ) {
                return strcmp( $a['start_date'], $b['start_date'] );
            }
        );

        foreach ( $pending as $booking_id => $booking ) {
            $assigned += ESHB_PMS_Assignment::auto_assign( $booking_id );
        }

        wp_send_json_success(
            [
                'assigned' => $assigned,
                'pending'  => count( $pending ),
                'message'  => sprintf(
                    /* translators: %d: number of rooms assigned. */
                    esc_html__( '%d room(s) assigned.', 'easy-hotel' ),
                    $assigned
                ),
            ]
        );
    }
}
