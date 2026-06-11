<?php
/**
 * AJAX endpoints for the Native Checkout account area.
 *
 * Every endpoint is logged-in only, nonce-verified, and re-checks
 * ownership server-side — client-supplied booking ids are never trusted.
 *
 * @package EasyHotel\NativeCheckout\Account
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_Account_Ajax {

    /** @var ESHB_Native_Account_Customer */
    private $customer;

    /** @var ESHB_Native_Account_Bookings */
    private $bookings;

    public function __construct( ESHB_Native_Account_Customer $customer, ESHB_Native_Account_Bookings $bookings ) {
        $this->customer = $customer;
        $this->bookings = $bookings;

        // Logged-in only — no nopriv handlers on purpose.
        add_action( 'wp_ajax_eshb_native_account_view_booking',  [ $this, 'view_booking' ] );
        add_action( 'wp_ajax_eshb_native_account_cancel_booking',[ $this, 'cancel_booking' ] );
        add_action( 'wp_ajax_eshb_native_account_update_profile',[ $this, 'update_profile' ] );
        add_action( 'wp_ajax_eshb_native_account_change_password',[ $this, 'change_password' ] );
    }

    /**
     * Verify nonce + login. Sends a JSON error and dies on failure.
     */
    private function guard() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'Please log in to continue.', 'easy-hotel' ) ] );
        }
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'eshb_native_account' ) ) {
            wp_send_json_error( [ 'message' => __( 'Security check failed. Please refresh the page.', 'easy-hotel' ) ] );
        }
    }

    /**
     * Read a booking id from the request and confirm the current user owns
     * it. Sends a JSON error and dies when not.
     */
    private function require_owned_booking() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $booking_id = isset( $_POST['booking_id'] ) ? absint( wp_unslash( $_POST['booking_id'] ) ) : 0;
        if ( ! $booking_id || ! $this->customer->user_owns_booking( $booking_id ) ) {
            wp_send_json_error( [ 'message' => __( 'Booking not found.', 'easy-hotel' ) ] );
        }
        return $booking_id;
    }

    /* -----------------------------------------------------------------
     * Endpoints
     * -------------------------------------------------------------- */

    /**
     * Return the booking-detail HTML for the modal.
     */
    public function view_booking() {
        $this->guard();
        $booking_id = $this->require_owned_booking();

        $detail = $this->bookings->get_detail_view( $booking_id );
        if ( empty( $detail ) ) {
            wp_send_json_error( [ 'message' => __( 'Booking not found.', 'easy-hotel' ) ] );
        }

        ob_start();
        ESHB_Native_Account::instance()->render_template( 'booking-view.php', [ 'b' => $detail ] );
        $html = ob_get_clean();

        wp_send_json_success( [ 'html' => $html ] );
    }

    /**
     * Cancel a booking owned by the current user.
     */
    public function cancel_booking() {
        $this->guard();
        $booking_id = $this->require_owned_booking();

        $can = $this->bookings->can_cancel_booking( $booking_id, true );
        if ( is_wp_error( $can ) ) {
            wp_send_json_error( [ 'message' => $can->get_error_message() ] );
        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $reason_choice = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
        $reason_custom = isset( $_POST['reason_custom'] ) ? sanitize_textarea_field( wp_unslash( $_POST['reason_custom'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        $reason = ( 'other' === $reason_choice && $reason_custom !== '' ) ? $reason_custom : $reason_choice;

        $result = $this->bookings->process_cancellation( $booking_id, $reason, 'customer' );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        $row = $this->bookings->get_row_view( $booking_id );
        wp_send_json_success( [
            'message'      => __( 'Your booking has been cancelled.', 'easy-hotel' ),
            'status'       => $row['status'] ?? 'cancelled',
            'status_label' => $row['status_label'] ?? __( 'Cancelled', 'easy-hotel' ),
        ] );
    }

    /**
     * Update the current user's profile fields.
     */
    public function update_profile() {
        $this->guard();
        $user_id = get_current_user_id();

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $first   = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
        $last    = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
        $display = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
        $email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if ( '' === $email || ! is_email( $email ) ) {
            wp_send_json_error( [ 'message' => __( 'Please enter a valid email address.', 'easy-hotel' ) ] );
        }
        // Reject an email already used by a different account.
        $existing = get_user_by( 'email', $email );
        if ( $existing && (int) $existing->ID !== (int) $user_id ) {
            wp_send_json_error( [ 'message' => __( 'That email address is already in use.', 'easy-hotel' ) ] );
        }
        if ( '' === $display ) {
            $display = trim( $first . ' ' . $last );
        }

        $result = wp_update_user( [
            'ID'           => $user_id,
            'first_name'   => $first,
            'last_name'    => $last,
            'display_name' => $display,
            'user_email'   => $email,
        ] );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        wp_send_json_success( [ 'message' => __( 'Your details have been saved.', 'easy-hotel' ) ] );
    }

    /**
     * Change the current user's password after verifying the current one.
     */
    public function change_password() {
        $this->guard();
        $user = wp_get_current_user();

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $current = isset( $_POST['current_password'] ) ? (string) wp_unslash( $_POST['current_password'] ) : '';
        $new     = isset( $_POST['new_password'] ) ? (string) wp_unslash( $_POST['new_password'] ) : '';
        $confirm = isset( $_POST['confirm_password'] ) ? (string) wp_unslash( $_POST['confirm_password'] ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if ( '' === $current || '' === $new || '' === $confirm ) {
            wp_send_json_error( [ 'message' => __( 'Please fill in all password fields.', 'easy-hotel' ) ] );
        }
        if ( ! wp_check_password( $current, $user->user_pass, $user->ID ) ) {
            wp_send_json_error( [ 'message' => __( 'Your current password is incorrect.', 'easy-hotel' ) ] );
        }
        if ( $new !== $confirm ) {
            wp_send_json_error( [ 'message' => __( 'New password and confirmation do not match.', 'easy-hotel' ) ] );
        }
        if ( strlen( $new ) < 6 ) {
            wp_send_json_error( [ 'message' => __( 'Password must be at least 6 characters.', 'easy-hotel' ) ] );
        }

        wp_set_password( $new, $user->ID );

        wp_send_json_success( [
            'message'  => __( 'Password updated. Please log in again.', 'easy-hotel' ),
            'redirect' => wp_login_url( ESHB_Native_Account::instance()->get_account_url() ),
        ] );
    }
}
