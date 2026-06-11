<?php
/**
 * Cancellation notification emails for the Native Checkout account area.
 *
 * Mirrors the structure of ESHB_Native_Email_Handler so confirmation and
 * cancellation mails look consistent, and exposes filters so the email
 * template add-on can swap subjects/bodies without forking this class.
 *
 * @package EasyHotel\NativeCheckout\Account
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_Account_Email {

    /**
     * Send both the customer and admin cancellation notifications.
     *
     * Mail failures are swallowed — a cancellation must never fail just
     * because the mail server is misconfigured.
     *
     * @param int   $booking_id
     * @param array $booking_data Detail view-model (see ESHB_Native_Account_Bookings::get_detail_view()).
     */
    public static function send_cancellation_emails( $booking_id, array $booking_data ) {
        try {
            self::send_customer_email( $booking_id, $booking_data );
        } catch ( \Throwable $e ) {
            unset( $e );
        }
        try {
            self::send_admin_email( $booking_id, $booking_data );
        } catch ( \Throwable $e ) {
            unset( $e );
        }
    }

    public static function send_customer_email( $booking_id, array $booking_data ) {
        $to = sanitize_email( $booking_data['customer_email'] ?? '' );
        if ( ! $to ) {
            return false;
        }
        $subject = sprintf(
            /* translators: %d: booking ID */
            __( 'Your booking has been cancelled - #%d', 'easy-hotel' ),
            (int) $booking_id
        );
        $body = self::build_body( $booking_id, $booking_data, 'customer' );

        return self::dispatch( $to, $subject, $body, 'customer', $booking_id );
    }

    public static function send_admin_email( $booking_id, array $booking_data ) {
        $settings = get_option( 'eshb_settings', [] );
        $to = ! empty( $settings['recipent_email'] ) ? $settings['recipent_email'] : get_option( 'admin_email' );

        $subject = sprintf(
            /* translators: %d: booking ID */
            __( 'Booking cancelled by customer - #%d', 'easy-hotel' ),
            (int) $booking_id
        );
        $body = self::build_body( $booking_id, $booking_data, 'admin' );

        return self::dispatch( $to, $subject, $body, 'admin', $booking_id );
    }

    /**
     * Apply the shared subject/body filters and send via ESHB_Core.
     */
    private static function dispatch( $to, $subject, $body, $context, $booking_id ) {
        $args = [ 'context' => $context, 'email_id' => 'booking_cancelled', 'booking_id' => $booking_id ];

        /** Filter the cancellation email subject. */
        $subject = apply_filters( 'eshb_native_checkout_cancellation_email_subject', $subject, $args );
        /** Filter the cancellation email HTML body. */
        $body    = apply_filters( 'eshb_native_checkout_cancellation_email_body', $body, $args );

        $core       = new ESHB_Core();
        $from_name  = get_bloginfo( 'name' );
        $host       = preg_replace( '/^www\./i', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
        $from_email = $host ? ( 'no-reply@' . $host ) : get_option( 'admin_email' );

        return $core->eshb_send_html_email( $to, $subject, $body, $from_name, $from_email );
    }

    /**
     * Render the cancellation email body. Customer and admin share the
     * summary table; admin additionally sees the customer block + reason.
     */
    private static function build_body( $booking_id, array $b, $context ) {
        $heading = ( 'customer' === $context )
            ? __( 'Your booking has been cancelled', 'easy-hotel' )
            : sprintf(
                /* translators: %d: booking ID */
                __( 'Booking #%d was cancelled', 'easy-hotel' ),
                (int) $booking_id
            );

        $cancelled_label = $b['cancelled_at'] ? date_i18n(
            get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
            strtotime( $b['cancelled_at'] )
        ) : '';

        ob_start();
        ?>
        <div style="font-family:Arial,Helvetica,sans-serif;max-width:640px;margin:0 auto;color:#333;">
            <div style="background:#b91c1c;color:#fff;padding:20px 24px;">
                <h2 style="margin:0;font-size:20px;"><?php echo esc_html( $heading ); ?></h2>
            </div>
            <div style="padding:24px;background:#fff;border:1px solid #e5e7eb;border-top:none;">
                <p style="margin:0 0 16px;">
                    <?php echo 'customer' === $context
                        ? esc_html__( 'The following booking has been cancelled. If this was a mistake, please contact us.', 'easy-hotel' )
                        : esc_html__( 'A customer has cancelled their booking.', 'easy-hotel' ); ?>
                </p>

                <table style="width:100%;border-collapse:collapse;">
                    <tr><td style="padding:6px 0;"><?php esc_html_e( 'Booking ID', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;">#<?php echo esc_html( $booking_id ); ?></td></tr>
                    <tr><td style="padding:6px 0;"><?php esc_html_e( 'Accommodation', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $b['accomodation'] ?? '' ); ?></td></tr>
                    <tr><td style="padding:6px 0;"><?php esc_html_e( 'Check-in', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $b['check_in_label'] ?? '' ); ?></td></tr>
                    <tr><td style="padding:6px 0;"><?php esc_html_e( 'Check-out', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $b['check_out_label'] ?? '' ); ?></td></tr>
                    <?php if ( $cancelled_label ) : ?>
                        <tr><td style="padding:6px 0;"><?php esc_html_e( 'Cancelled on', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $cancelled_label ); ?></td></tr>
                    <?php endif; ?>
                </table>

                <?php
                // Customer-only link back to the account dashboard.
                if ( 'customer' === $context && class_exists( 'ESHB_Native_Account' ) ) :
                    $account_url = ESHB_Native_Account::instance()->get_account_url();
                    if ( $account_url ) :
                        ?>
                        <p style="margin:24px 0 0;">
                            <a href="<?php echo esc_url( $account_url ); ?>" style="display:inline-block;background:#212121;color:#ffffff;padding:12px 22px;border-radius:6px;text-decoration:none;font-weight:bold;">
                                <?php esc_html_e( 'View your bookings', 'easy-hotel' ); ?>
                            </a>
                        </p>
                        <?php
                    endif;
                endif;
                ?>

                <?php if ( 'admin' === $context ) : ?>
                    <h3 style="font-size:16px;margin:24px 0 8px;border-bottom:1px solid #e5e7eb;padding-bottom:4px;"><?php esc_html_e( 'Customer', 'easy-hotel' ); ?></h3>
                    <p style="margin:0;line-height:1.6;">
                        <strong><?php echo esc_html( $b['customer_name'] ?? '' ); ?></strong><br>
                        <?php echo esc_html( $b['customer_email'] ?? '' ); ?><br>
                        <?php echo esc_html( $b['customer_phone'] ?? '' ); ?>
                    </p>
                    <?php if ( ! empty( $b['cancel_reason'] ) ) : ?>
                        <h3 style="font-size:16px;margin:24px 0 8px;border-bottom:1px solid #e5e7eb;padding-bottom:4px;"><?php esc_html_e( 'Reason', 'easy-hotel' ); ?></h3>
                        <p style="margin:0;"><?php echo esc_html( $b['cancel_reason'] ); ?></p>
                    <?php endif; ?>
                <?php endif; ?>

                <p style="margin:24px 0 0;color:#6b7280;font-size:12px;">
                    <?php echo esc_html__( 'This email was generated by Easy Hotel.', 'easy-hotel' ); ?>
                </p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
