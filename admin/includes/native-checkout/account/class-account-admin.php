<?php
/**
 * Admin-side cancellation support for Native Checkout bookings.
 *
 *   - Runs the full cancellation routine (metadata, room restore, hooks,
 *     emails) whenever a booking transitions to the `cancelled` status —
 *     including a manual cancel via the booking status dropdown.
 *   - Adds a read-only "Cancellation details" metabox to the booking edit
 *     screen showing reason / date / who cancelled it.
 *
 * @package EasyHotel\NativeCheckout\Account
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_Account_Admin {

    /** @var ESHB_Native_Account_Bookings */
    private $bookings;

    public function __construct( ESHB_Native_Account_Bookings $bookings ) {
        $this->bookings = $bookings;

        // Catch every route into the cancelled status (admin edit, code,
        // our own customer flow — which is made idempotent by a guard meta).
        add_action( 'transition_post_status', [ $this, 'on_status_transition' ], 10, 3 );

        if ( is_admin() ) {
            add_action( 'add_meta_boxes', [ $this, 'register_metabox' ] );
            add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        }

        // Manual refund endpoint (admin only).
        add_action( 'wp_ajax_eshb_native_admin_refund_booking', [ $this, 'ajax_refund' ] );
    }

    /**
     * Fire the cancellation routine when a booking becomes cancelled.
     *
     * @param string  $new_status
     * @param string  $old_status
     * @param WP_Post $post
     */
    public function on_status_transition( $new_status, $old_status, $post ) {
        if ( ! $post instanceof WP_Post || $post->post_type !== 'eshb_booking' ) {
            return;
        }
        if ( 'cancelled' !== $new_status || 'cancelled' === $old_status ) {
            return;
        }
        if ( wp_is_post_autosave( $post->ID ) || wp_is_post_revision( $post->ID ) ) {
            return;
        }

        // 'system' by default; an admin doing it from wp-admin is attributed
        // as 'admin'. The customer AJAX flow sets the guard before its own
        // wp_update_post, so this call is a harmless no-op in that case.
        $by = is_admin() && current_user_can( 'edit_posts' ) ? 'admin' : 'system';
        $this->bookings->process_cancellation( $post->ID, '', $by );
    }

    /* -----------------------------------------------------------------
     * Cancellation details metabox
     * -------------------------------------------------------------- */

    public function register_metabox() {
        add_meta_box(
            'eshb_booking_cancellation',
            __( 'Cancellation Details', 'easy-hotel' ),
            [ $this, 'render_metabox' ],
            'eshb_booking',
            'side',
            'low'
        );
    }

    public function render_metabox( $post ) {
        $meta = get_post_meta( $post->ID, 'eshb_booking_metaboxes', true );
        $meta = is_array( $meta ) ? $meta : [];

        // Prefer the dedicated flat meta (survives metabox re-saves).
        $cancelled_at = (string) ( get_post_meta( $post->ID, ESHB_Native_Account_Bookings::META_CANCELLED_AT, true )
            ?: ( $meta['cancelled_at'] ?? '' ) );

        if ( '' === $cancelled_at ) {
            echo '<p>' . esc_html__( 'This booking has not been cancelled.', 'easy-hotel' ) . '</p>';
            echo '<p style="color:#6b7280;font-size:12px;">' . esc_html__( 'Set the status to “Cancelled” and update to cancel this booking. The customer and admin will be notified.', 'easy-hotel' ) . '</p>';
        } else {
            $when = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $cancelled_at ) );
            $by   = (string) ( get_post_meta( $post->ID, ESHB_Native_Account_Bookings::META_CANCELLED_BY, true ) ?: ( $meta['cancelled_by'] ?? '' ) );
            $why  = (string) ( get_post_meta( $post->ID, ESHB_Native_Account_Bookings::META_CANCEL_REASON, true ) ?: ( $meta['cancellation_reason'] ?? '' ) );
            ?>
            <p><strong><?php esc_html_e( 'Cancelled on:', 'easy-hotel' ); ?></strong><br><?php echo esc_html( $when ); ?></p>
            <p><strong><?php esc_html_e( 'Cancelled by:', 'easy-hotel' ); ?></strong><br><?php echo esc_html( ucfirst( $by ) ); ?></p>
            <?php if ( $why ) : ?>
                <p><strong><?php esc_html_e( 'Reason:', 'easy-hotel' ); ?></strong><br><?php echo esc_html( $why ); ?></p>
            <?php endif;
        }

        $this->render_refund_box( $post, $meta );
    }

    /**
     * Refund controls + history shown inside the cancellation metabox.
     */
    private function render_refund_box( $post, array $meta ) {
        $core     = new ESHB_Core();
        $paid     = (float) ( $meta['total_paid'] ?? 0 );
        $refunded = (float) ( get_post_meta( $post->ID, ESHB_Native_Account_Bookings::META_TOTAL_REFUNDED, true )
            ?: ( $meta['total_refunded'] ?? 0 ) );
        $log      = $this->bookings->get_refunds( $post->ID );
        ?>
        <div class="eshb-refund-box" data-eshb-refund-box data-booking-id="<?php echo esc_attr( $post->ID ); ?>"
             style="margin-top:14px;padding-top:12px;border-top:1px solid #e5e7eb;">
            <p style="margin:0 0 4px;"><strong><?php esc_html_e( 'Amount paid:', 'easy-hotel' ); ?></strong>
                <span data-eshb-paid-display><?php echo wp_kses_post( $core->eshb_price( $paid ) ); ?></span></p>
            <p style="margin:0 0 8px;<?php echo $refunded > 0 ? '' : 'display:none;'; ?>" data-eshb-refunded-wrap>
                <strong><?php esc_html_e( 'Refunded:', 'easy-hotel' ); ?></strong>
                <span data-eshb-refunded-display><?php echo wp_kses_post( $core->eshb_price( $refunded ) ); ?></span></p>

            <?php if ( ! empty( $log ) ) : ?>
                <ul class="eshb-refund-log" data-eshb-refund-log style="margin:0 0 8px;padding-left:16px;font-size:12px;color:#4b5563;">
                    <?php foreach ( $log as $entry ) :
                        $amt  = $core->eshb_price( (float) ( $entry['amount'] ?? 0 ) );
                        $date = ! empty( $entry['date'] ) ? date_i18n( get_option( 'date_format' ), strtotime( $entry['date'] ) ) : '';
                        ?>
                        <li><?php echo wp_kses_post( $amt ); ?> — <?php echo esc_html( $date ); ?> (<?php echo esc_html( ucfirst( (string) ( $entry['by'] ?? 'admin' ) ) ); ?>)</li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <ul class="eshb-refund-log" data-eshb-refund-log style="margin:0 0 8px;padding-left:16px;font-size:12px;color:#4b5563;"></ul>
            <?php endif; ?>

            <?php if ( $paid > 0 ) : ?>
                <button type="button" class="button" data-eshb-refund-toggle><?php esc_html_e( 'Refund', 'easy-hotel' ); ?></button>

                <div class="eshb-refund-form" data-eshb-refund-form hidden style="margin-top:10px;">
                    <p style="margin:0 0 6px;">
                        <label style="display:block;margin-bottom:4px;">
                            <input type="radio" name="eshb_refund_type" value="full" checked>
                            <?php esc_html_e( 'Full amount', 'easy-hotel' ); ?>
                            (<span data-eshb-full-display><?php echo wp_kses_post( $core->eshb_price( $paid ) ); ?></span>)
                        </label>
                        <label style="display:block;">
                            <input type="radio" name="eshb_refund_type" value="custom">
                            <?php esc_html_e( 'Custom amount', 'easy-hotel' ); ?>
                        </label>
                    </p>
                    <input type="number" step="0.01" min="0.01" data-eshb-refund-amount
                        placeholder="<?php esc_attr_e( 'Enter amount', 'easy-hotel' ); ?>"
                        style="width:100%;margin-bottom:8px;" disabled hidden>

                    <p class="eshb-refund-msg" data-eshb-refund-msg style="margin:0 0 8px;font-size:12px;"></p>

                    <button type="button" class="button button-primary" data-eshb-refund-submit><?php esc_html_e( 'Process refund', 'easy-hotel' ); ?></button>
                    <button type="button" class="button" data-eshb-refund-cancel><?php esc_html_e( 'Cancel', 'easy-hotel' ); ?></button>
                </div>
            <?php else : ?>
                <p style="color:#6b7280;font-size:12px;margin:0;"><?php esc_html_e( 'Nothing left to refund.', 'easy-hotel' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /* -----------------------------------------------------------------
     * Refund — assets + AJAX
     * -------------------------------------------------------------- */

    public function enqueue_admin_assets( $hook ) {
        if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
            return;
        }
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'eshb_booking' ) {
            return;
        }

        $url  = ESHB_PL_URL . 'admin/includes/native-checkout/account/assets/js/account-admin.js';
        $path = ESHB_PL_PATH . 'admin/includes/native-checkout/account/assets/js/account-admin.js';
        wp_enqueue_script(
            'eshb-native-account-admin',
            $url,
            [ 'jquery' ],
            file_exists( $path ) ? filemtime( $path ) : ESHB_VERSION,
            true
        );
        wp_localize_script( 'eshb-native-account-admin', 'eshbNativeAccountAdmin', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'eshb_native_admin_refund' ),
            'i18n'    => [
                'processing' => __( 'Processing…', 'easy-hotel' ),
                'confirm'    => __( 'Process this refund?', 'easy-hotel' ),
                'error'      => __( 'Something went wrong. Please try again.', 'easy-hotel' ),
                'enterValid' => __( 'Please enter a valid amount.', 'easy-hotel' ),
            ],
        ] );
    }

    /**
     * Process a manual refund from the booking edit screen.
     */
    public function ajax_refund() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'eshb_native_admin_refund' ) ) {
            wp_send_json_error( [ 'message' => __( 'Security check failed.', 'easy-hotel' ) ] );
        }
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $booking_id = isset( $_POST['booking_id'] ) ? absint( wp_unslash( $_POST['booking_id'] ) ) : 0;
        $type       = isset( $_POST['refund_type'] ) ? sanitize_key( wp_unslash( $_POST['refund_type'] ) ) : 'custom';
        $amount_in  = isset( $_POST['amount'] ) ? (float) wp_unslash( $_POST['amount'] ) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if ( ! $booking_id || get_post_type( $booking_id ) !== 'eshb_booking' || ! current_user_can( 'edit_post', $booking_id ) ) {
            wp_send_json_error( [ 'message' => __( 'You are not allowed to refund this booking.', 'easy-hotel' ) ] );
        }

        if ( 'full' === $type ) {
            $meta      = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
            $amount_in = is_array( $meta ) ? (float) ( $meta['total_paid'] ?? 0 ) : 0;
        }

        $result = $this->bookings->process_refund( $booking_id, $amount_in, 'admin' );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        $core = new ESHB_Core();
        wp_send_json_success( [
            'message'              => __( 'Refund processed.', 'easy-hotel' ),
            'refunded_html'        => $core->eshb_price( $result['refunded'] ),
            'total_paid'           => $result['total_paid'],
            'total_paid_html'      => $core->eshb_price( $result['total_paid'] ),
            'total_refunded_html'  => $core->eshb_price( $result['total_refunded'] ),
            'date_label'           => date_i18n( get_option( 'date_format' ) ),
            'by_label'             => __( 'Admin', 'easy-hotel' ),
        ] );
    }
}
