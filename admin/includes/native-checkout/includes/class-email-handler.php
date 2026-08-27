<?php
/**
 * Native Checkout email notifications.
 *
 * Wraps ESHB_Core::eshb_send_html_email() so the gateway flow can fire
 * both confirmation and admin emails without duplicating template logic.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_Email_Handler {

    /**
     * Which emails a booking has already been sent.
     *
     * Holds a list of email ids. Both the checkout and the status-change
     * listener want to mail the customer the instant a payment succeeds, and
     * a multi-room checkout announces a status once per room — without a
     * record of what already went out, the same notice reaches the customer
     * several times for one booking.
     */
    const SENT_META = '_eshb_native_emails_sent';

    /**
     * Booking status -> the customer email that belongs to it.
     *
     * Statuses with no entry fall back to the processing email.
     */
    const STATUS_EMAILS = [
        'completed'  => 'customer_completed_order',
        'processing' => 'customer_processing_order',
        'on-hold'    => 'customer_on_hold_order',
        'cancelled'  => 'customer_cancelled_order',
        'refunded'   => 'customer_refunded_order',
        'failed'     => 'customer_failed_order',
    ];

    /**
     * Whether a booking has already been sent a given email.
     *
     * Public so add-ons that mail on their own (EHB Email Template sends the
     * status-change emails itself) can share the one record instead of
     * keeping a second, disagreeing one.
     *
     * @param int    $booking_id
     * @param string $email_id
     * @return bool
     */
    public static function already_sent( $booking_id, $email_id ) {
        $sent = get_post_meta( (int) $booking_id, self::SENT_META, true );
        return is_array( $sent ) && in_array( (string) $email_id, $sent, true );
    }

    /**
     * Record that an email went out, for every booking in the group.
     *
     * Recorded against all of them, not just the primary: the customer copy
     * covers the whole checkout, so a later per-booking status announcement
     * must see it too.
     *
     * @param int|int[] $booking_ids
     * @param string    $email_id
     */
    public static function mark_sent( $booking_ids, $email_id ) {

        $email_id = (string) $email_id;

        foreach ( self::normalize_ids( $booking_ids ) as $bid ) {

            $sent = get_post_meta( $bid, self::SENT_META, true );
            if ( ! is_array( $sent ) ) {
                $sent = [];
            }

            if ( ! in_array( $email_id, $sent, true ) ) {
                $sent[] = $email_id;
                update_post_meta( $bid, self::SENT_META, $sent );
            }
        }
    }

    /**
     * Forget the status emails a booking has been sent, except one.
     *
     * Called when a booking enters a status: the record for that status is
     * kept — the checkout may have just mailed it and must not be doubled —
     * while every other status is re-armed. A booking put back on hold and
     * then confirmed again notifies the customer both times, which the plain
     * send-once record would have swallowed.
     *
     * Only the status emails are cleared. The admin's new-booking notice is
     * tied to the booking existing, not to where it currently sits, so it
     * stays recorded for good.
     *
     * @param int    $booking_id
     * @param string $keep Email id to leave in the record.
     */
    public static function reset_status_emails( $booking_id, $keep = '' ) {

        $booking_id = (int) $booking_id;
        $sent       = get_post_meta( $booking_id, self::SENT_META, true );

        if ( ! is_array( $sent ) || empty( $sent ) ) {
            return;
        }

        $status_emails = array_values( self::STATUS_EMAILS );
        $kept          = [];

        foreach ( $sent as $email_id ) {
            if ( $email_id === $keep || ! in_array( $email_id, $status_emails, true ) ) {
                $kept[] = $email_id;
            }
        }

        if ( $kept !== $sent ) {
            update_post_meta( $booking_id, self::SENT_META, $kept );
        }
    }

    /**
     * The status a booking group is in.
     *
     * Read from the primary booking: every booking in one checkout moves
     * through the same statuses together.
     *
     * @param int[] $ids
     * @return string
     */
    public static function group_status( array $ids ) {

        $primary = (int) reset( $ids );
        if ( ! $primary ) {
            return '';
        }

        $meta   = get_post_meta( $primary, 'eshb_booking_metaboxes', true );
        $status = is_array( $meta ) ? (string) ( $meta['booking_status'] ?? '' ) : '';

        return '' !== $status ? $status : (string) get_post_status( $primary );
    }

    /**
     * The customer email that belongs to a booking status.
     *
     * The checkout sets the booking's final status before it mails, so a
     * booking paid with an online gateway is already `completed` by then and
     * gets the completed email — not the "awaiting payment" one it used to
     * send regardless of what actually happened.
     *
     * @param string $status
     * @param int[]  $ids
     * @return string
     */
    public static function email_id_for_status( $status, array $ids = [] ) {

        $email_id = self::STATUS_EMAILS[ $status ] ?? 'customer_processing_order';

        /**
         * Filter which email a booking status sends.
         *
         * @param string $email_id
         * @param string $status
         * @param int[]  $ids
         */
        return (string) apply_filters( 'eshb_native_checkout_email_id_for_status', $email_id, $status, $ids );
    }


    /**
     * Normalize a single booking id or an array of ids (one
     * multi-accommodation checkout) into a clean list of ints.
     */
    private static function normalize_ids( $booking_ids ) {
        $ids = array_map( 'intval', (array) $booking_ids );
        return array_values( array_filter( $ids ) );
    }

    /**
     * Switch WordPress to the language the booking was made in.
     *
     * Checkout completes over admin-ajax.php, so without this the subject and
     * the fallback body below are built in the site's default language even
     * when the customer booked from the Bangla site. Only the EHB Email
     * Template add-on knows how to resolve a booking's language, so this is a
     * no-op when it is not installed.
     *
     * @param int $booking_id Booking post id.
     * @return bool Pass the return value to restore_booking_locale().
     */
    private static function switch_to_booking_locale( $booking_id ) {

        if ( ! class_exists( 'ESHB_Email_Language' ) ) {
            return false;
        }

        return ESHB_Email_Language::switch_to( ESHB_Email_Language::for_booking( $booking_id ) );
    }

    /**
     * Undo switch_to_booking_locale().
     *
     * @param bool $switched Its return value.
     */
    private static function restore_booking_locale( $switched ) {

        if ( $switched && class_exists( 'ESHB_Email_Language' ) ) {
            ESHB_Email_Language::restore( $switched );
        }
    }

    public static function send_customer_confirmation( $booking_ids, array $customer ) {
        $ids = self::normalize_ids( $booking_ids );
        if ( empty( $ids ) || empty( $customer['email'] ) ) return false;

        $switched = self::switch_to_booking_locale( reset( $ids ) );

        try {
            $status = self::group_status( $ids );

            return self::send_templated_email(
                $ids,
                $customer,
                'customer',
                self::email_id_for_status( $status, $ids ),
                $customer['email'],
                self::default_customer_subject( $status )
            );
        } finally {
            self::restore_booking_locale( $switched );
        }
    }

    /**
     * Fallback subject for the customer email, used when no template
     * defines one of its own.
     *
     * @param string $status Booking status the email is being sent for.
     * @return string
     */
    private static function default_customer_subject( $status ) {

        if ( 'completed' === $status ) {
            return sprintf(
                /* translators: %s: site name */
                __( 'Your booking is confirmed - %s', 'easy-hotel' ),
                get_bloginfo( 'name' )
            );
        }

        return sprintf(
            /* translators: %s: site name */
            __( 'Your booking confirmation - %s', 'easy-hotel' ),
            get_bloginfo( 'name' )
        );
    }


    public static function send_admin_notification( $booking_ids, array $customer ) {
        $ids = self::normalize_ids( $booking_ids );
        if ( empty( $ids ) ) return false;

        $settings = get_option( 'eshb_settings', [] );
        $to = ! empty( $settings['recipent_email'] ) ? $settings['recipent_email'] : get_option( 'admin_email' );

        $switched = self::switch_to_booking_locale( reset( $ids ) );

        try {
            return self::send_templated_email( $ids, $customer, 'admin', 'new_order', $to,
                sprintf(
                    /* translators: %d: booking ID */
                    __( 'New booking received - #%d', 'easy-hotel' ),
                    reset( $ids )
                )
            );
        } finally {
            self::restore_booking_locale( $switched );
        }
    }

    /**
     * Build and dispatch an email, exposing the subject and body through
     * filters so extensions (e.g. the EHB Email Template add-on) can
     * swap in their own templates without forking this class.
     *
     * @param int    $booking_id      Booking post id.
     * @param array  $customer        Customer details captured at checkout.
     * @param string $context         'customer' or 'admin'.
     * @param string $email_id        Logical email id used to look up a
     *                                custom template ('customer_processing_order',
     *                                'new_order', etc.).
     * @param string $to              Recipient address.
     * @param string $default_subject Subject used if no extension overrides it.
     */
    private static function send_templated_email( $booking_ids, array $customer, $context, $email_id, $to, $default_subject ) {
        $ids        = self::normalize_ids( $booking_ids );
        $primary_id = reset( $ids );

        $args = [
            'context'     => $context,
            'email_id'    => $email_id,
            'booking_id'  => $primary_id,
            'booking_ids' => $booking_ids,
            'customer'    => $customer,
        ];

        // One email of a kind per booking. The checkout and the status-change
        // listener both want to mail the customer the moment a payment
        // succeeds, and a multi-room checkout announces its status once per
        // room — without this the customer gets the same notice two or three
        // times for a single booking.
        if ( self::already_sent( $primary_id, $email_id ) ) {
            return false;
        }

        /**
         * Whether to send this email at all.
         *
         * Returning false cancels the send before the subject and body are
         * built. The EHB Email Template add-on hooks this to honour a
         * deleted template: an email whose template the admin moved to the
         * trash is skipped rather than silently going out with the built-in
         * default HTML below.
         *
         * @param bool  $send
         * @param array $args Context, email_id, booking_id, booking_ids, customer.
         */
        if ( ! apply_filters( 'eshb_native_checkout_send_email', true, $args ) ) {
            return false;
        }

        /**
         * Filter the subject of a native-checkout email. Extensions can
         * return a string sourced from a custom template; the default
         * is the static subject built in send_customer_confirmation /
         * send_admin_notification.
         *
         * @param string $default_subject
         * @param array  $args Context, email_id, booking_id, customer.
         */
        $subject = apply_filters( 'eshb_native_checkout_email_subject', $default_subject, $args );

        // Built only once the send is certain — rendering the fallback body
        // for an email that is about to be skipped is wasted work.
        $default_body = self::build_email_body( $ids, $customer, $context );

        /**
         * Filter the HTML body of a native-checkout email. Extensions
         * can return a fully-rendered builder template here.
         *
         * @param string $default_body
         * @param array  $args Context, email_id, booking_id, customer.
         */
        $message = apply_filters( 'eshb_native_checkout_email_body', $default_body, $args );

        $core       = new ESHB_Core();
        $from_name  = get_bloginfo( 'name' );
        $from_email = self::get_from_email();

        // Recorded before the send, not after: a mail server that hangs or a
        // listener that re-enters must not be able to produce a second copy.
        self::mark_sent( $ids, $email_id );

        return $core->eshb_send_html_email( $to, $subject, $message, $from_name, $from_email );
    }

    private static function get_from_email() {
        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        $host = preg_replace( '/^www\./i', '', (string) $host );
        return $host ? ( 'no-reply@' . $host ) : get_option( 'admin_email' );
    }

    /**
     * The gateway a booking group was placed with, or null when it cannot
     * be resolved.
     *
     * @param int[] $ids
     * @param array $customer Customer payload captured at checkout.
     * @return object|null
     */
    private static function gateway_for( array $ids, array $customer = [] ) {

        if ( empty( $ids ) || ! class_exists( 'ESHB_Native_Gateway_Manager' ) ) {
            return null;
        }

        $meta       = get_post_meta( (int) reset( $ids ), 'eshb_booking_metaboxes', true );
        $meta       = is_array( $meta ) ? $meta : [];
        $gateway_id = $meta['payment_gateway'] ?? '';

        if ( '' === $gateway_id ) {
            $gateway_id = $customer['gateway'] ?? '';
        }

        if ( '' === $gateway_id ) {
            return null;
        }

        return ESHB_Native_Gateway_Manager::instance()->get_gateway( $gateway_id );
    }

    /**
     * The opening line of the built-in customer email.
     *
     * The status says what happened to the booking; the gateway says what
     * happened to the money, and the two do not always agree. A
     * pay-on-arrival booking is `completed` without a penny having moved,
     * so the gateway is given the first say and the status wording is the
     * fallback.
     *
     * @param string $status   Booking status the email is being sent for.
     * @param int[]  $ids      Bookings in the group.
     * @param array  $customer Customer payload captured at checkout.
     * @return string
     */
    private static function customer_intro( $status, array $ids = [], array $customer = [] ) {

        $intro   = self::status_intro( $status );
        $gateway = self::gateway_for( $ids, $customer );

        if ( $gateway && method_exists( $gateway, 'get_email_intro' ) ) {
            $from_gateway = (string) $gateway->get_email_intro( $status );
            if ( '' !== $from_gateway ) {
                $intro = $from_gateway;
            }
        }

        /**
         * Filter the opening line of the built-in customer email.
         *
         * @param string      $intro
         * @param string      $status  Booking status.
         * @param int[]       $ids     Bookings in the group.
         * @param object|null $gateway Gateway the booking was placed with.
         */
        return (string) apply_filters( 'eshb_native_checkout_email_intro', $intro, $status, $ids, $gateway );
    }

    /**
     * The opening line of the built-in customer email.
     *
     * Status-aware, because this body is what goes out when no template
     * exists for the email: telling somebody whose card has just been
     * charged that their reservation is "on hold pending payment" is worse
     * than sending nothing at all.
     *
     * @param string $status Booking status the email is being sent for.
     * @return string
     */
    private static function status_intro( $status ) {

        switch ( $status ) {

            case 'completed':
                return __( 'Your payment went through and your booking is confirmed. The details are below.', 'easy-hotel' );

            case 'processing':
                return __( 'We have received your booking and are getting it ready. The details are below.', 'easy-hotel' );

            case 'cancelled':
                return __( 'Your booking has been cancelled. The details are below.', 'easy-hotel' );

            case 'refunded':
                return __( 'Your booking has been refunded. The details are below.', 'easy-hotel' );

            case 'failed':
                return __( 'Your payment could not be completed, so your booking is not confirmed yet.', 'easy-hotel' );

            default:
                return __( 'Your reservation is on hold and will be confirmed once we process your payment.', 'easy-hotel' );
        }
    }

    private static function build_email_body( $booking_ids, array $customer, $context ) {
        $ids        = self::normalize_ids( $booking_ids );
        $core       = new ESHB_Core();
        $first_name = $customer['first_name'] ?? '';
        $multiple   = count( $ids ) > 1;

        $heading = ( $context === 'customer' )
            ? sprintf(
                /* translators: %s: customer first name */
                __( 'Hi %s, thank you for your booking!', 'easy-hotel' ),
                esc_html( $first_name )
            )
            : ( $multiple
                ? __( 'New booking received', 'easy-hotel' )
                : sprintf(
                    /* translators: %d: booking ID */
                    __( 'New booking #%d received', 'easy-hotel' ),
                    (int) reset( $ids )
                )
            );

        // Combined grand total across every booking in the group.
        $grand_total = 0.0;
        foreach ( $ids as $bid ) {
            $bm = get_post_meta( $bid, 'eshb_booking_metaboxes', true );
            if ( is_array( $bm ) ) {
                $grand_total += (float) ( $bm['total_price'] ?? 0 );
            }
        }

        ob_start();
        ?>
        <div style="font-family:Arial,Helvetica,sans-serif;max-width:640px;margin:0 auto;color:#333;">
            <div style="background:#212121;color:#fff;padding:20px 24px;">
                <h2 style="margin:0;font-size:20px;"><?php echo esc_html( $heading ); ?></h2>
            </div>
            <div style="padding:24px;background:#fff;border:1px solid #e5e7eb;border-top:none;">
                <p style="margin:0 0 16px;">
                    <?php echo $context === 'customer'
                        ? esc_html( self::customer_intro( self::group_status( $ids ), $ids, $customer ) )
                        : esc_html__( 'A new booking has been created from the native checkout.', 'easy-hotel' ); ?>
                </p>

                <?php
                // Gateway instructions sit above the booking tables, the
                // same slot WooCommerce uses for BACS details
                // (woocommerce_email_before_order_table). Customer-only —
                // the admin copy does not carry payment instructions.
                if ( $context === 'customer' ) {
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the gateway.
                    echo self::render_gateway_instructions( $ids, $customer );
                }

                foreach ( $ids as $index => $bid ) {
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside helper.
                    echo self::render_booking_table( $bid, $customer, $multiple ? ( (int) $index + 1 ) : 0 );
                }

                if ( $multiple ) : ?>
                    <table style="width:100%;border-collapse:collapse;margin-top:8px;">
                        <tr>
                            <td style="padding:10px 0;font-weight:bold;border-top:2px solid #212121;font-size:15px;"><?php esc_html_e( 'Grand Total', 'easy-hotel' ); ?></td>
                            <td style="padding:10px 0;text-align:right;font-weight:bold;border-top:2px solid #212121;font-size:15px;"><?php echo wp_kses_post( $core->eshb_price( $grand_total ) ); ?></td>
                        </tr>
                    </table>
                <?php endif; ?>

                <?php
                // Customer-only call to action: let them jump straight to
                // the account area to view or manage their bookings.
                if ( $context === 'customer' && class_exists( 'ESHB_Native_Account' ) ) :
                    $account_url = ESHB_Native_Account::instance()->get_account_url();
                    if ( $account_url ) :
                        ?>
                        <p style="margin:24px 0 0;">
                            <a href="<?php echo esc_url( $account_url ); ?>" style="display:inline-block;background:#212121;color:#ffffff;padding:12px 22px;border-radius:6px;text-decoration:none;font-weight:bold;">
                                <?php esc_html_e( 'Manage your booking', 'easy-hotel' ); ?>
                            </a>
                        </p>
                        <p style="margin:8px 0 0;color:#6b7280;font-size:12px;">
                            <?php esc_html_e( 'View your bookings and manage your account anytime from your account dashboard.', 'easy-hotel' ); ?>
                        </p>
                        <?php
                    endif;
                endif;
                ?>

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

    /**
     * Post-checkout instructions from the gateway the booking was placed
     * with (e.g. where to wire the money for a bank transfer). Empty for
     * gateways that already took the payment online.
     *
     * Like WooCommerce, the instructions are only mailed while the
     * booking is still awaiting payment — once the admin confirms the
     * transfer, later emails no longer ask for it.
     *
     * @param int[] $ids      Bookings in the group.
     * @param array $customer Customer details captured at checkout.
     */
    private static function render_gateway_instructions( array $ids, array $customer ) {
        $gateway = self::gateway_for( $ids, $customer );
        if ( ! $gateway ) {
            return '';
        }

        $meta = get_post_meta( (int) reset( $ids ), 'eshb_booking_metaboxes', true );
        $meta = is_array( $meta ) ? $meta : [];

        /**
         * Booking status the instructions are mailed for. Mirrors
         * WooCommerce's 'woocommerce_bacs_email_instructions_order_status'.
         *
         * @param string $status
         * @param int[]  $ids
         */
        $awaiting_status = apply_filters( 'eshb_native_checkout_email_instructions_status', 'on-hold', $ids );
        $booking_status  = (string) ( $meta['booking_status'] ?? get_post_status( (int) reset( $ids ) ) );

        if ( $booking_status !== $awaiting_status ) {
            return '';
        }

        return $gateway->get_instructions_html( $ids, true );
    }

    /**
     * Render the summary table for one booking. When $position > 0 the
     * accommodation is labelled "Accommodation N" so multi-accommodation
     * emails read clearly.
     */
    private static function render_booking_table( $booking_id, array $customer, $position = 0 ) {
        $meta = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
        if ( ! is_array( $meta ) ) $meta = [];

        $core               = new ESHB_Core();
        $accomodation_id    = (int) ( $meta['booking_accomodation_id'] ?? 0 );
        $accomodation_title = $accomodation_id ? get_the_title( $accomodation_id ) : '';
        $total_html         = $core->eshb_price( (float) ( $meta['total_price'] ?? 0 ) );

        $total_price      = (float) ( $meta['total_price'] ?? 0 );
        $total_paid       = (float) ( $meta['total_paid'] ?? 0 );
        $due_amount       = (float) max( 0, $total_price - $total_paid );

        $total_paid_html         = $core->eshb_price( (float) ( $total_paid ) );
        $due_amount_html         = $core->eshb_price( (float) ( $due_amount ) );

        // Resolve the gateway id stored on the booking into a human-readable
        // payment method title, falling back to the raw id when the gateway
        // is no longer registered.
        $payment_gateway_id   = $meta['payment_gateway'] ?? ( $customer['gateway'] ?? '' );
        $payment_method_label = '';
        if ( $payment_gateway_id && class_exists( 'ESHB_Native_Gateway_Manager' ) ) {
            $gateway = ESHB_Native_Gateway_Manager::instance()->get_gateway( $payment_gateway_id );
            if ( $gateway ) {
                $payment_method_label = $gateway->get_title();
            }
        }
        if ( '' === $payment_method_label ) {
            $payment_method_label = $payment_gateway_id;
        }

        $section_title = $position > 0
            ? sprintf(
                /* translators: 1: accommodation index, 2: accommodation title */
                __( 'Accommodation %1$d — %2$s', 'easy-hotel' ),
                (int) $position,
                $accomodation_title
            )
            : __( 'Booking summary', 'easy-hotel' );

        ob_start();
        ?>
        <h3 style="font-size:16px;margin:24px 0 8px;border-bottom:1px solid #e5e7eb;padding-bottom:4px;">
            <?php echo esc_html( $section_title ); ?>
        </h3>
        <table style="width:100%;border-collapse:collapse;">
            <tr><td style="padding:6px 0;"><?php esc_html_e( 'Booking ID', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;">#<?php echo esc_html( $booking_id ); ?></td></tr>
            <tr><td style="padding:6px 0;"><?php esc_html_e( 'Accommodation', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $accomodation_title ); ?></td></tr>
            <tr><td style="padding:6px 0;"><?php esc_html_e( 'Check-in', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $meta['booking_start_date'] ?? '' ); ?></td></tr>
            <tr><td style="padding:6px 0;"><?php esc_html_e( 'Check-out', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $meta['booking_end_date'] ?? '' ); ?></td></tr>
            <tr><td style="padding:6px 0;"><?php esc_html_e( 'Guests', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( (int) ( $meta['adult_quantity'] ?? 0 ) + (int) ( $meta['children_quantity'] ?? 0 ) ); ?></td></tr>
            <tr><td style="padding:6px 0;"><?php esc_html_e( 'Rooms', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $meta['room_quantity'] ?? 1 ); ?></td></tr>
            <?php $eshb_services_label = ESHB_Helper::eshb_booking_services_label( $meta ); ?>
            <?php if ( ! empty( $eshb_services_label ) ) : ?>
                <tr><td style="padding:6px 0;"><?php esc_html_e( 'Extra services', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $eshb_services_label ); ?></td></tr>
            <?php endif; ?>
            <?php if ( ! empty( $meta['coupon_code'] ) ) : ?>
                <tr><td style="padding:6px 0;"><?php esc_html_e( 'Coupon', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $meta['coupon_code'] ); ?></td></tr>
            <?php endif; ?>
            <tr><td style="padding:6px 0;font-weight:bold;border-top:1px solid #e5e7eb;"><?php esc_html_e( 'Total', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;font-weight:bold;border-top:1px solid #e5e7eb;"><?php echo wp_kses_post( $total_html ); ?></td></tr>
            
            <?php if ( ! empty( $meta['total_paid'] ) ) : ?>
                <tr><td style="padding:6px 0;font-weight:bold;border-top:1px solid #e5e7eb;"><?php esc_html_e( 'Amount Paid', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;font-weight:bold;border-top:1px solid #e5e7eb;"><?php echo wp_kses_post( $total_paid_html ); ?></td></tr>
            <?php endif; ?>
            <?php if ( ! empty( $due_amount ) ) : ?>
                <tr><td style="padding:6px 0;font-weight:bold;border-top:1px solid #e5e7eb;"><?php esc_html_e( 'Due Balance', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;font-weight:bold;border-top:1px solid #e5e7eb;"><?php echo wp_kses_post( $due_amount_html ); ?></td></tr>
            <?php endif; ?>

            <?php if ( ! empty( $payment_method_label ) ) : ?>
                <tr><td style="padding:6px 0;"><?php esc_html_e( 'Payment method', 'easy-hotel' ); ?></td><td style="padding:6px 0;text-align:right;"><?php echo esc_html( $payment_method_label ); ?></td></tr>
            <?php endif; ?>
        </table>
        <?php
        return ob_get_clean();
    }
}
