<?php
/**
 * Customer ↔ WordPress user bridge for the Native Checkout account area.
 *
 * Native checkout is guest-first: a booking only stores the customer's
 * name/email in post meta. To power a "My Account" dashboard we need a
 * real WordPress user behind each customer, so this class:
 *
 *   - Auto-creates (or links) a WP user when a booking is completed,
 *     hooked onto `eshb_native_checkout_booking_created`.
 *   - Stores fast, queryable flat meta on each booking
 *     (`_eshb_customer_user_id`, `_eshb_customer_email`) so the account
 *     pages can list "my bookings" without LIKE-scanning serialized meta.
 *   - Resolves which bookings belong to the current user and answers the
 *     ownership question used by every account/cancellation action.
 *
 * @package EasyHotel\NativeCheckout\Account
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_Account_Customer {

    /** Flat, queryable booking meta keys. */
    const META_USER_ID = '_eshb_customer_user_id';
    const META_EMAIL   = '_eshb_customer_email';

    /** Custom role assigned to checkout customers. */
    const ROLE = 'eshb_customer';

    public function __construct() {
        // Ensure the customer role exists (for installs already activated).
        add_action( 'init', [ $this, 'register_role' ] );

        // Link / create the WP user right after a native-checkout booking
        // is persisted. Priority 5 so the user exists before other
        // booking_created listeners (e.g. emails) run.
        add_action( 'eshb_native_checkout_booking_created', [ $this, 'link_booking_to_user' ], 5, 4 );
    }

    /**
     * Register the "Easy Hotel Customer" role if it doesn't exist yet.
     * Customers only need front-end read access.
     */
    public function register_role() {
        if ( null === get_role( self::ROLE ) ) {
            add_role( self::ROLE, __( 'Easy Hotel Customer', 'easy-hotel' ), [ 'read' => true ] );
        }
    }

    /**
     * Ensure a WordPress user exists for the booking's customer and stamp
     * the booking with queryable ownership meta.
     *
     * @param int   $booking_id  Booking post id.
     * @param array $reservation Reservation payload (unused but part of the hook signature).
     * @param array $customer    Customer details captured at checkout.
     * @param array $pricing     Pricing payload (unused).
     */
    public function link_booking_to_user( $booking_id, $reservation, $customer, $pricing ) {
        $email = isset( $customer['email'] ) ? sanitize_email( $customer['email'] ) : '';
        if ( ! $booking_id ) {
            return;
        }

        $user_id = $this->ensure_user( $customer );

        if ( $user_id ) {
            update_post_meta( $booking_id, self::META_USER_ID, (int) $user_id );
            // Attribute authorship too, so standard WP tooling sees the link.
            wp_update_post( [ 'ID' => $booking_id, 'post_author' => (int) $user_id ] );
        }
        if ( $email ) {
            update_post_meta( $booking_id, self::META_EMAIL, strtolower( $email ) );
        }
    }

    /**
     * Find or create the WordPress user for a customer array.
     *
     * Order of resolution:
     *   1. A logged-in user is treated as the booking owner.
     *   2. An existing account with the same email is reused.
     *   3. Otherwise a new customer account is created and notified.
     *
     * Auto-creation can be disabled via the
     * `eshb_native_account_auto_create_user` filter.
     *
     * @param array $customer Customer details (email, first_name, last_name).
     * @return int User id, or 0 when none could be resolved.
     */
    public function ensure_user( array $customer ) {
        if ( is_user_logged_in() ) {
            return get_current_user_id();
        }

        $email = isset( $customer['email'] ) ? sanitize_email( $customer['email'] ) : '';
        if ( ! $email || ! is_email( $email ) ) {
            return 0;
        }

        $existing = get_user_by( 'email', $email );
        if ( $existing instanceof WP_User ) {
            return (int) $existing->ID;
        }

        /**
         * Whether to auto-create a customer account at checkout.
         *
         * @param bool  $create
         * @param array $customer
         */
        if ( ! apply_filters( 'eshb_native_account_auto_create_user', true, $customer ) ) {
            return 0;
        }

        return $this->create_customer_user( $customer, $email );
    }

    /**
     * Create a new customer WP user from checkout details.
     *
     * @return int New user id, or 0 on failure.
     */
    private function create_customer_user( array $customer, $email ) {
        $username = $this->generate_username( $email );

        $userdata = [
            'user_login'   => $username,
            'user_email'   => $email,
            'user_pass'    => wp_generate_password( 24, true, true ),
            'first_name'   => sanitize_text_field( $customer['first_name'] ?? '' ),
            'last_name'    => sanitize_text_field( $customer['last_name'] ?? '' ),
            'display_name' => trim( ( $customer['first_name'] ?? '' ) . ' ' . ( $customer['last_name'] ?? '' ) ),
            /**
             * Role assigned to auto-created customers.
             *
             * @param string $role
             */
            'role'         => apply_filters( 'eshb_native_account_customer_role', self::ROLE ),
        ];

        $user_id = wp_insert_user( $userdata );
        if ( is_wp_error( $user_id ) || ! $user_id ) {
            return 0;
        }

        // Send WordPress' "set your password" notification so the customer
        // can log in to manage their booking. Wrapped so a mail failure
        // never breaks the checkout completion.
        try {
            wp_new_user_notification( $user_id, null, 'user' );
        } catch ( \Throwable $e ) {
            unset( $e );
        }

        /**
         * Fires after a customer account is auto-created at checkout.
         *
         * @param int   $user_id
         * @param array $customer
         */
        do_action( 'eshb_native_account_user_created', $user_id, $customer );

        return (int) $user_id;
    }

    /**
     * Build a unique, readable username from an email local-part.
     */
    private function generate_username( $email ) {
        $base = sanitize_user( current( explode( '@', $email ) ), true );
        if ( empty( $base ) ) {
            $base = 'customer';
        }
        $username = $base;
        $i        = 1;
        while ( username_exists( $username ) ) {
            $username = $base . $i;
            $i++;
        }
        return $username;
    }

    /* -----------------------------------------------------------------
     * Ownership / lookup
     * -------------------------------------------------------------- */

    /**
     * Booking ids owned by a user, newest first.
     *
     * Matches on the fast flat meta first, then falls back to the email
     * stored inside the serialized customer meta (legacy bookings created
     * before this feature), back-filling the flat meta as it goes so the
     * slow path only ever runs once per booking.
     *
     * @param WP_User|int|null $user Defaults to the current user.
     * @return int[] Booking post ids.
     */
    public function get_user_booking_ids( $user = null ) {
        $user = $this->resolve_user( $user );
        if ( ! $user ) {
            return [];
        }

        $email = strtolower( $user->user_email );

        $query = new WP_Query( [
            'post_type'      => 'eshb_booking',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'orderby'        => 'date',
            'order'          => 'DESC',
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            'meta_query'     => [
                'relation' => 'OR',
                [ 'key' => self::META_USER_ID, 'value' => (int) $user->ID ],
                [ 'key' => self::META_EMAIL,   'value' => $email ],
                [
                    'key'     => 'eshb_booking_customer_details_metaboxes',
                    'value'   => '"' . $email . '"',
                    'compare' => 'LIKE',
                ],
            ],
        ] );

        $ids = array_map( 'intval', $query->posts );

        // Back-fill flat meta for any legacy booking matched via the
        // serialized-email path so future lookups hit the fast index.
        foreach ( $ids as $id ) {
            if ( '' === get_post_meta( $id, self::META_EMAIL, true ) ) {
                update_post_meta( $id, self::META_EMAIL, $email );
            }
        }

        return $ids;
    }

    /**
     * Whether the given user owns the booking. Used to gate every
     * customer-facing booking action server-side.
     *
     * @param int              $booking_id
     * @param WP_User|int|null $user Defaults to current user.
     */
    public function user_owns_booking( $booking_id, $user = null ) {
        $booking_id = (int) $booking_id;
        $user       = $this->resolve_user( $user );
        if ( ! $booking_id || ! $user || get_post_type( $booking_id ) !== 'eshb_booking' ) {
            return false;
        }

        $owner_id = (int) get_post_meta( $booking_id, self::META_USER_ID, true );
        if ( $owner_id && $owner_id === (int) $user->ID ) {
            return true;
        }

        // Fall back to an email comparison against the authoritative
        // serialized customer meta (covers legacy / unlinked bookings).
        $customer = get_post_meta( $booking_id, 'eshb_booking_customer_details_metaboxes', true );
        $email    = is_array( $customer ) ? strtolower( (string) ( $customer['email'] ?? '' ) ) : '';

        return $email !== '' && $email === strtolower( $user->user_email );
    }

    /**
     * Normalize a user argument (id / WP_User / null) to a WP_User.
     *
     * @return WP_User|null
     */
    private function resolve_user( $user ) {
        if ( $user instanceof WP_User ) {
            return $user;
        }
        if ( is_numeric( $user ) && $user > 0 ) {
            $obj = get_user_by( 'id', (int) $user );
            return $obj instanceof WP_User ? $obj : null;
        }
        if ( is_user_logged_in() ) {
            return wp_get_current_user();
        }
        return null;
    }
}
