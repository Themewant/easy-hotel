<?php
/**
 * Native Checkout email notifications.
 *
 * Wraps ESHB_Core::eshb_send_html_email() so the gateway flow can fire
 * both confirmation and admin emails without duplicating template logic.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_Email_Handler {

    public static function send_customer_confirmation( $booking_id, array $customer ) {
        if ( ! $booking_id || empty( $customer['email'] ) ) return false;

        $core    = new ESHB_Core();
        $subject = sprintf(
            /* translators: %s: site name */
            __( 'Your booking confirmation - %s', 'easy-hotel' ),
            get_bloginfo( 'name' )
        );
        $message = self::build_email_body( $booking_id, $customer, 'customer' );

        $from_name = get_bloginfo( 'name' );
        $from_email = self::get_from_email();

        return $core->eshb_send_html_email( $customer['email'], $subject, $message, $from_name, $from_email );
    }

    public static function send_admin_notification( $booking_id, array $customer ) {
        if ( ! $booking_id ) return false;

        $settings = get_option( 'eshb_settings', [] );
        $to = ! empty( $settings['recipent_email'] ) ? $settings['recipent_email'] : get_option( 'admin_email' );

        $core    = new ESHB_Core();
        $subject = sprintf(
            /* translators: %d: booking ID */
            __( 'New booking received - #%d', 'easy-hotel' ),
            $booking_id
        );
        $message = self::build_email_body( $booking_id, $customer, 'admin' );

        $from_name  = get_bloginfo( 'name' );
        $from_email = self::get_from_email();

        return $core->eshb_send_html_email( $to, $subject, $message, $from_name, $from_email );
    }

    private static function get_from_email() {
        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        $host = preg_replace( '/^www\./i', '', (string) $host );
        return $host ? ( 'no-reply@' . $host ) : get_option( 'admin_email' );
    }

    private static function build_email_body( $booking_id, array $customer, $context ) {
        $meta = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
        if ( ! is_array( $meta ) ) $meta = [];

        $accomodation_id    = (int) ( $meta['booking_accomodation_id'] ?? 0 );
        $accomodation_title = $accomodation_id ? get_the_title( $accomodation_id ) : '';
        $core               = new ESHB_Core();
        $total_html         = $core->eshb_price( (float) ( $meta['total_price'] ?? 0 ) );
        $first_name         = $customer['first_name'] ?? '';

        $heading = ( $context === 'customer' )
            ? sprintf(
                /* translators: %s: customer first name */
                __( 'Hi %s, thank you for your booking!', 'easy-hotel' ),
                esc_html( $first_name )
            )
            : sprintf(
                /* translators: %d: booking ID */
                __( 'New booking #%d received', 'easy-hotel' ),
                (int) $booking_id
            );

        ob_start();
        ?>
        <div style="font-family:Arial,Helvetica,sans-serif;max-width:640px;margin:0 auto;color:#333;">
            <div style="background:#212121;color:#fff;padding:20px 24px;">
                <h2 style="margin:0;font-size:20px;"><?php echo esc_html( $heading ); ?></h2>
            </div>
            <div style="padding:24px;background:#fff;border:1px solid #e5e7eb;border-top:none;">
                <p style="margin:0 0 16px;">
                    <?php echo $context === 'customer'
                        ? esc_html__( 'Your reservation is on hold and will be confirmed once we process your payment.', 'easy-hotel' )
                        : esc_html__( 'A new booking has been created from the native checkout.', 'easy-hotel' ); ?>
                </p>

                <h3 style="font-size:16px;margin:24px 0 8px;border-bottom:1px solid #e5e7eb;padding-bottom:4px;">
                    <?php esc_html_e( 'Booking summary', 'easy-hotel' ); ?>
                </h3>
                <table style="width:100%;border-collapse:collapse;">
                    <tr><td style="padding:6px 0;"><?php esc_html_e( 'Booking ID', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;">#<?php echo esc_html( $booking_id ); ?></td></tr>
                    <tr><td style="padding:6px 0;"><?php esc_html_e( 'Accommodation', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $accomodation_title ); ?></td></tr>
                    <tr><td style="padding:6px 0;"><?php esc_html_e( 'Check-in', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $meta['booking_start_date'] ?? '' ); ?></td></tr>
                    <tr><td style="padding:6px 0;"><?php esc_html_e( 'Check-out', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $meta['booking_end_date'] ?? '' ); ?></td></tr>
                    <tr><td style="padding:6px 0;"><?php esc_html_e( 'Guests', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( (int) ( $meta['adult_quantity'] ?? 0 ) + (int) ( $meta['children_quantity'] ?? 0 ) ); ?></td></tr>
                    <tr><td style="padding:6px 0;"><?php esc_html_e( 'Rooms', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $meta['room_quantity'] ?? 1 ); ?></td></tr>
                    <?php if ( ! empty( $meta['extra_services_html'] ) ) : ?>
                        <tr><td style="padding:6px 0;"><?php esc_html_e( 'Extra services', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $meta['extra_services_html'] ); ?></td></tr>
                    <?php endif; ?>
                    <?php if ( ! empty( $meta['coupon_code'] ) ) : ?>
                        <tr><td style="padding:6px 0;"><?php esc_html_e( 'Coupon', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $meta['coupon_code'] ); ?></td></tr>
                    <?php endif; ?>
                    <tr><td style="padding:6px 0;font-weight:bold;border-top:1px solid #e5e7eb;"><?php esc_html_e( 'Total paid', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;font-weight:bold;border-top:1px solid #e5e7eb;"><?php echo wp_kses_post( $total_html ); ?></td></tr>
                </table>

                <?php if ( $context === 'admin' ) : ?>
                    <h3 style="font-size:16px;margin:24px 0 8px;border-bottom:1px solid #e5e7eb;padding-bottom:4px;">
                        <?php esc_html_e( 'Customer', 'easy-hotel' ); ?>
                    </h3>
                    <p style="margin:0;line-height:1.6;">
                        <strong><?php echo esc_html( trim( ( $customer['first_name'] ?? '' ) . ' ' . ( $customer['last_name'] ?? '' ) ) ); ?></strong><br>
                        <?php echo esc_html( $customer['email'] ?? '' ); ?><br>
                        <?php echo esc_html( $customer['phone'] ?? '' ); ?><br>
                        <?php echo esc_html( $customer['country'] ?? '' ); ?>
                    </p>
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
