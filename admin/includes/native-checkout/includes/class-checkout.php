<?php
/**
 * Native Checkout controller.
 *
 * Responsibilities:
 *   - Register the [eshb_native_checkout] shortcode and render the page.
 *   - Hook into the reservation form so a "native_checkout" booking type
 *     stores the reservation in a transient and redirects to the page.
 *   - AJAX endpoints used by the page:
 *       * eshb_native_apply_coupon       — recalculates pricing with coupon
 *       * eshb_native_create_payment     — server-side create-order for a gateway
 *       * eshb_native_complete_checkout  — capture, persist booking, email
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_Checkout {

    /** Booking meta holding the secret that unlocks its thank-you page. */
    const THANKYOU_KEY_META = '_eshb_thankyou_key';

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init',                 [ $this, 'register_shortcode' ] );
        add_action( 'init',                 [ $this, 'ensure_checkout_page' ], 20 );
        add_action( 'wp_enqueue_scripts',   [ $this, 'enqueue_assets' ], 1000 );

        // Hook BEFORE ESHB_Booking::eshb_add_to_cart_reservation runs so we can
        // intercept the native_checkout booking type and short-circuit the WC flow.
        add_action( 'wp_ajax_eshb_add_to_cart_reservation',        [ $this, 'maybe_handle_reservation' ], 1 );
        add_action( 'wp_ajax_nopriv_eshb_add_to_cart_reservation', [ $this, 'maybe_handle_reservation' ], 1 );

        // Checkout AJAX endpoints.
        add_action( 'wp_ajax_eshb_native_apply_coupon',         [ $this, 'ajax_apply_coupon' ] );
        add_action( 'wp_ajax_nopriv_eshb_native_apply_coupon',  [ $this, 'ajax_apply_coupon' ] );
        add_action( 'wp_ajax_eshb_native_create_payment',        [ $this, 'ajax_create_payment' ] );
        add_action( 'wp_ajax_nopriv_eshb_native_create_payment', [ $this, 'ajax_create_payment' ] );
        add_action( 'wp_ajax_eshb_native_complete_checkout',        [ $this, 'ajax_complete_checkout' ] );
        add_action( 'wp_ajax_nopriv_eshb_native_complete_checkout', [ $this, 'ajax_complete_checkout' ] );

        // Cart-blocking: release the reservation + hold when the hold
        // countdown expires on the checkout page.
        add_action( 'wp_ajax_eshb_native_release_reservation',        [ $this, 'ajax_release_reservation' ] );
        add_action( 'wp_ajax_nopriv_eshb_native_release_reservation', [ $this, 'ajax_release_reservation' ] );

        // Multi-accommodation cart: live recalculation (service edits) and
        // per-item removal.
        add_action( 'wp_ajax_eshb_native_recalculate',         [ $this, 'ajax_recalculate' ] );
        add_action( 'wp_ajax_nopriv_eshb_native_recalculate',  [ $this, 'ajax_recalculate' ] );
        add_action( 'wp_ajax_eshb_native_remove_item',         [ $this, 'ajax_remove_item' ] );
        add_action( 'wp_ajax_nopriv_eshb_native_remove_item',  [ $this, 'ajax_remove_item' ] );
    }

    public function register_shortcode() {
        add_shortcode( 'eshb_native_checkout', [ $this, 'render_shortcode' ] );
    }

    /**
     * Create the checkout page if the admin selected `native_checkout`
     * but no page with the [eshb_native_checkout] shortcode exists yet.
     * Skipped on AJAX/REST/CLI to avoid running unnecessarily on every
     * background request.
     */
    public function ensure_checkout_page() {
        if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
            return;
        }
        if ( ! eshb_native_checkout_is_enabled() ) {
            return;
        }
        eshb_native_checkout_ensure_page();
    }

    /**
     * Intercept the standard add-to-cart endpoint when booking-type is native.
     *
     * We piggy-back on the existing endpoint instead of registering a new one,
     * so the frontend booking form keeps working without changes — only the
     * post-validation branch differs.
     */
    public function maybe_handle_reservation() {
        if ( ! eshb_native_checkout_is_enabled() ) {
            return; // Let ESHB_Booking handle it normally.
        }

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), ESHB_Helper::generate_secure_nonce_action( 'eshb_global_nonce_action' ) ) ) {
            wp_send_json_error( [ 'error' => [ 'code' => 'invalid_nonce', 'message' => __( 'Invalid request.', 'easy-hotel' ) ] ] );
        }

        $reservation = $this->collect_reservation_from_request();
        if ( empty( $reservation['accomodation_id'] ) ) {
            wp_send_json_error( [ 'error' => [ 'code' => 'missing_accomodation', 'message' => __( 'Accommodation is required.', 'easy-hotel' ) ] ] );
        }

        if ( get_post_type( $reservation['accomodation_id'] ) !== 'eshb_accomodation' ) {
            wp_send_json_error( [ 'error' => [ 'code' => 'invalid_accomodation', 'message' => __( 'Invalid accommodation.', 'easy-hotel' ) ] ] );
        }

        // 0 / negative rooms or adults would price the stay at 0 or below and,
        // once stored, subtract from the booked-room count.
        $quantities = ESHB_Booking::eshb_read_booking_quantities();
        if ( is_wp_error( $quantities ) ) {
            wp_send_json_error( [ 'error' => [ 'code' => $quantities->get_error_code(), 'message' => $quantities->get_error_message() ] ] );
        }
        $reservation = array_merge( $reservation, $quantities );

        $valid_dates = ESHB_Booking::eshb_validate_booking_dates( $reservation['start_date'], $reservation['end_date'] );
        if ( is_wp_error( $valid_dates ) ) {
            wp_send_json_error( [ 'error' => [ 'code' => $valid_dates->get_error_code(), 'message' => $valid_dates->get_error_message() ] ] );
        }

        // Same booking rules as the WooCommerce flow: time slot, booked dates,
        // holidays, check-in day, min/max nights and capacity. This handler
        // answers before ESHB_Booking::eshb_add_to_cart_reservation runs, so
        // without this none of them were enforced.
        $rule_error = ESHB_Booking::instance()->eshb_validate_booking_rules( $reservation );
        if ( $rule_error ) {
            wp_send_json_error( $rule_error );
        }

        $availability_error = $this->validate_availability( $reservation );
        if ( $availability_error ) {
            wp_send_json_error( [ 'error' => $availability_error ] );
        }

        // Reuse existing pricing pipeline; if it returns no data, the request is invalid.
        $pricing = ESHB_Native_Pricing::calculate( $reservation );
        if ( empty( $pricing ) ) {
            wp_send_json_error( [ 'error' => [ 'code' => 'pricing_failed', 'message' => __( 'Unable to calculate price.', 'easy-hotel' ) ] ] );
        }

        // APPEND the accommodation to the visitor's cart (multi-accommodation
        // checkout). Each submit adds another item rather than replacing.
        $added = eshb_native_checkout_add_item( $reservation );
        if ( ! $added || empty( $added['token'] ) ) {
            wp_send_json_error( [ 'error' => [ 'code' => 'cart_failed', 'message' => __( 'Could not add to cart. Please try again.', 'easy-hotel' ) ] ] );
        }
        $token = $added['token'];

        // Place a temporary hold on the dates so concurrent visitors are
        // told these dates are reserved. The hold is keyed by the native
        // token (now resolvable via the cookie set above), and released on
        // booking completion / item removal / countdown expiry. Must run
        // after add_item() so the session token exists.
        ESHB_Booking::instance()->eshb_block_dates_for_reservation(
            $reservation['accomodation_id'],
            $reservation['start_date'],
            $reservation['end_date'],
            max( 1, (int) $reservation['room_quantity'] )
        );

        // Pass the reservation token in the redirect URL — cookies are
        // unreliable on some live hosts (Set-Cookie stripped from AJAX
        // responses, edge caches, etc.) so the URL is the source of
        // truth and the cookie is a best-effort backup. Force a
        // trailing slash before adding the query arg so WP's canonical
        // redirect can't bounce the URL and drop the param.
        // Keep the visitor in the language they were browsing. This handler runs
        // over admin-ajax.php, so the referer is the only thing that knows it;
        // the booked accommodation is the backstop when the browser sends none.
        $checkout_lang = eshb_native_checkout_request_language();

        if ( '' === $checkout_lang ) {
            $checkout_lang = eshb_native_checkout_post_language( $reservation['accomodation_id'] );
        }

        $checkout_url = trailingslashit( eshb_native_checkout_url( $checkout_lang ) );
        $checkout_url = add_query_arg( eshb_native_checkout_request_param(), $token, $checkout_url );

        wp_send_json_success( [
            'booking-type'    => 'native_checkout',
            'message'         => __( 'Redirecting to checkout…', 'easy-hotel' ),
            'redirect_url'    => $checkout_url,
            'token'           => $token,
            'cart_count'      => count( eshb_native_checkout_get_items() ),
            'accomodation_id' => $reservation['accomodation_id'],
            'start_date'      => $reservation['start_date'],
            'end_date'        => $reservation['end_date'],
        ] );
    }

    /**
     * Reuse the room-availability check from ESHB_Booking so the
     * native flow refuses overbooked or unavailable date ranges
     * (same guarantees as the WooCommerce flow).
     *
     * @return array|null Error descriptor or null when ok.
     */
    private function validate_availability( array $reservation ) {
        $accomodation_id = (int) $reservation['accomodation_id'];
        $start_date      = $reservation['start_date'];
        $end_date        = $reservation['end_date'];
        $room_quantity   = max( 1, (int) $reservation['room_quantity'] );

        $booking  = new ESHB_Booking();
        $available = $booking->get_available_room_count_by_date_range(
            $accomodation_id,
            $start_date,
            $end_date,
            $reservation['start_time'] ?? '',
            $reservation['end_time'] ?? ''
        );

        if ( is_wp_error( $available ) ) {
            return [ 'code' => 'invalid_date_range', 'message' => $available->get_error_message() ];
        }

        if ( $room_quantity > (int) $available ) {
            return [
                'code'    => 'room_capacity_not_enough',
                'message' => sprintf(
                    /* translators: %s: number of available rooms */
                    esc_html__( 'Selected room is not available. Available room: %s', 'easy-hotel' ),
                    esc_html( $available )
                ),
            ];
        }

        // Reject dates another visitor is temporarily holding (cart
        // blocking), mirroring the WooCommerce add-to-cart flow.
        $conflict = $booking->eshb_get_cart_block_conflict( $accomodation_id, $start_date, $end_date );
        if ( ! empty( $conflict ) ) {
            return [ 'code' => 'cart_blocked', 'message' => $conflict ];
        }

        // Account for rooms the visitor already holds in their OWN cart for
        // the same accommodation + overlapping dates, so adding a second
        // item can't push the combined quantity past real availability.
        $in_cart = $this->cart_reserved_rooms( $accomodation_id, $start_date, $end_date );
        if ( $room_quantity + $in_cart > (int) $available ) {
            $remaining = max( 0, (int) $available - $in_cart );
            return [
                'code'    => 'room_capacity_not_enough',
                'message' => sprintf(
                    /* translators: %s: number of available rooms */
                    esc_html__( 'Selected room is not available. Available room: %s', 'easy-hotel' ),
                    esc_html( $remaining )
                ),
            ];
        }

        return null;
    }

    /**
     * Count rooms already in the visitor's cart for an accommodation whose
     * dates overlap the requested range (optionally excluding one item key).
     */
    private function cart_reserved_rooms( $accom_id, $start_date, $end_date, $exclude_key = '' ) {
        $accom_id = (int) $accom_id;
        $count    = 0;
        foreach ( eshb_native_checkout_get_items() as $key => $item ) {
            if ( $key === $exclude_key ) continue;
            if ( (int) ( $item['accomodation_id'] ?? 0 ) !== $accom_id ) continue;
            $i_start = (string) ( $item['start_date'] ?? '' );
            $i_end   = (string) ( $item['end_date'] ?? '' );
            // Half-open overlap on Y-m-d strings (lexicographic compare ok).
            if ( $start_date < $i_end && $i_start < $end_date ) {
                $count += max( 1, (int) ( $item['room_quantity'] ?? 1 ) );
            }
        }
        return $count;
    }

    /**
     * Payment note for a capture the gateway is still holding, e.g.
     * "PayPal payment pending (ECHECK): €500.00 not received yet".
     */
    private function pending_payment_note( $gateway, $booking_total, $reason = '' ) {
        $core   = new ESHB_Core();
        $price  = html_entity_decode( wp_strip_all_tags( $core->eshb_price( (float) $booking_total ) ), ENT_QUOTES, 'UTF-8' );
        $reason = sanitize_text_field( (string) $reason );

        return sprintf(
            /* translators: 1: gateway name, e.g. PayPal, 2: pending reason in brackets or empty, 3: amount, e.g. €500.00 */
            __( '%1$s payment pending%2$s: %3$s not received yet', 'easy-hotel' ),
            $gateway->get_title(),
            $reason !== '' ? ' (' . $reason . ')' : '',
            $price
        );
    }

    /**
     * Lock every accommodation in the cart for the rest of this request, so
     * the availability check, payment capture and booking insert can't
     * interleave with another checkout of the same accommodation. Waits up
     * to ~5 seconds for a lock; a lock older than 2 minutes is a dead
     * request and is taken over. Locks are released on shutdown, which also
     * runs after wp_send_json_* and fatal errors.
     *
     * INSERT IGNORE on the unique option_name is atomic, unlike add_option()
     * which reads first and then upserts.
     *
     * @return bool False when a lock could not be acquired.
     */
    private function lock_cart_accommodations( array $items ) {
        global $wpdb;

        $accom_ids = [];
        foreach ( $items as $item ) {
            $id = (int) ( $item['accomodation_id'] ?? 0 );
            if ( $id ) $accom_ids[ $id ] = $id;
        }
        sort( $accom_ids ); // Fixed order, so two carts can't deadlock each other.

        $acquired = [];
        foreach ( $accom_ids as $accom_id ) {
            $name   = 'eshb_native_booking_lock_' . $accom_id;
            $locked = false;

            for ( $attempt = 0; $attempt < 20; $attempt++ ) {
                $now = time();
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Atomic lock; must bypass the options cache.
                $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')", $name, $now ) );
                if ( 1 === (int) $wpdb->rows_affected ) {
                    $locked = true;
                    break;
                }

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Atomic takeover of a stale lock.
                $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value < %d", $now, $name, $now - 2 * MINUTE_IN_SECONDS ) );
                if ( 1 === (int) $wpdb->rows_affected ) {
                    $locked = true;
                    break;
                }

                usleep( 250000 );
            }

            if ( ! $locked ) {
                $this->release_accommodation_locks( $acquired );
                return false;
            }
            $acquired[] = $name;
        }

        if ( $acquired ) {
            register_shutdown_function( [ $this, 'release_accommodation_locks' ], $acquired );
        }
        return true;
    }

    /**
     * @param string[] $names Lock option names taken by lock_cart_accommodations().
     */
    public function release_accommodation_locks( array $names ) {
        global $wpdb;
        foreach ( $names as $name ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Lock row written with a direct query above.
            $wpdb->delete( $wpdb->options, [ 'option_name' => $name ] );
        }
    }

    private function collect_reservation_from_request() {
        // Nonce verified in maybe_handle_reservation() before this helper
        // is called; the static analyzer can't trace that, so we silence
        // its complaint on the $_POST reads below.
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $today    = gmdate( 'Y-m-d' );
        $tomorrow = gmdate( 'Y-m-d', strtotime( '+1 day' ) );

        $selected_services = [];
        if ( ! empty( $_POST['selectedServices'] ) ) {
            $raw = sanitize_text_field( wp_unslash( $_POST['selectedServices'] ) );
            $decoded = json_decode( $raw, true );
            if ( is_array( $decoded ) ) {
                foreach ( $decoded as $svc ) {
                    if ( ! is_array( $svc ) ) continue;
                    $svc_id = isset( $svc['id'] ) ? (int) $svc['id'] : 0;
                    $selected_services[] = [
                        'id'       => $svc_id,
                        'quantity' => ESHB_Helper::eshb_clamp_service_quantity( $svc_id, $svc['quantity'] ?? 0 ),
                    ];
                }
            }
        }

        $payload = [
            'accomodation_id'     => isset( $_POST['accomodationId'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['accomodationId'] ) ) : 0,
            'start_date'          => isset( $_POST['startDate'] ) ? sanitize_text_field( wp_unslash( $_POST['startDate'] ) ) : $today,
            'end_date'            => isset( $_POST['endDate'] ) ? sanitize_text_field( wp_unslash( $_POST['endDate'] ) ) : $tomorrow,
            'start_time'          => isset( $_POST['startTime'] ) ? sanitize_text_field( wp_unslash( $_POST['startTime'] ) ) : '',
            'end_time'            => isset( $_POST['endTime'] ) ? sanitize_text_field( wp_unslash( $_POST['endTime'] ) ) : '',
            'room_quantity'       => isset( $_POST['roomQuantity'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['roomQuantity'] ) ) : 1,
            'extra_bed_quantity'  => isset( $_POST['extraBedQuantity'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['extraBedQuantity'] ) ) : 0,
            'adult_quantity'      => isset( $_POST['adultQuantity'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['adultQuantity'] ) ) : 1,
            'children_quantity'   => isset( $_POST['childrenQuantity'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['childrenQuantity'] ) ) : 0,
            'extra_services'      => $selected_services,
        ];
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        // The booking form only offers this accommodation's services and locks
        // the mandatory ones, but the payload is still just a POST — both rules
        // are re-applied before anything is stored.
        $payload['extra_services'] = ESHB_Helper::eshb_filter_services_by_accomodation(
            $payload['extra_services'],
            $payload['accomodation_id']
        );
        $payload['extra_services'] = ESHB_Helper::eshb_enforce_mandatory_services(
            $payload['extra_services'],
            $payload['accomodation_id']
        );

        return $payload;
    }

    public function enqueue_assets() {
        if ( ! $this->is_checkout_page() ) return;

        // CSS is bundled into public.css via public.scss (@import 'native-checkout'),
        // which is already enqueued site-wide — nothing to enqueue here for styles.
        $base_url  = ESHB_PL_URL . 'admin/includes/native-checkout/assets/';
        $base_path = ESHB_PL_PATH . 'admin/includes/native-checkout/assets/';
        $js_path   = $base_path . 'js/checkout.js';

        wp_enqueue_script(
            'eshb-native-checkout',
            $base_url . 'js/checkout.js',
            [ 'jquery' ],
            file_exists( $js_path ) ? filemtime( $js_path ) : ESHB_VERSION,
            true
        );

        $items   = eshb_native_checkout_get_items();
        $pricing = ! empty( $items ) ? ESHB_Native_Pricing::calculate_cart( $items ) : [];

        $manager  = ESHB_Native_Gateway_Manager::instance();
        $gateways = [];
        foreach ( $manager->get_gateways( true ) as $gateway ) {
            $gateways[] = $gateway->get_frontend_data();
        }

        $items_view = $this->build_cart_items_view( $items, $pricing );
        // First item view kept for backward-compat with add-ons that read
        // `reservation` from the localized data (e.g. EHB Deposit).
        $reservation_view = ! empty( $items_view ) ? $items_view[0] : null;

        // Cart-blocking countdown data for the checkout page. `until` is
        // the soonest-expiring hold across all cart items (0 when none or
        // blocking disabled); the JS runs a live countdown and releases the
        // cart when it reaches zero.
        $eshb_settings_cb = get_option( 'eshb_settings', [] );
        $cart_block = [ 'enabled' => false, 'until' => 0 ];
        if ( ! empty( $eshb_settings_cb['cart-blocking-switcher'] ) && ! empty( $items ) ) {
            $cart_block = [
                'enabled' => true,
                'until'   => $this->earliest_block_until( $items ),
            ];
        }

        // Load PayPal SDK if available (no-op when no gateway configured).
        $paypal_settings = $manager->get_gateway( 'paypal' );
        if ( $paypal_settings && $paypal_settings->is_enabled() ) {
            $data    = $paypal_settings->get_frontend_data();
            $sdk_url = add_query_arg( [
                'client-id' => $data['clientId'],
                'currency'  => $data['currency'],
                'intent'    => 'capture',
            ], 'https://www.paypal.com/sdk/js' );
            // The PayPal SDK validates its querystring strictly and
            // returns 400 if WordPress appends `?ver=...` to the URL,
            // so we pass null (no version) instead of ESHB_VERSION.
            // The SDK is a third-party hosted script with its own
            // versioning at the CDN edge.
            // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
            wp_enqueue_script( 'eshb-native-paypal-sdk', $sdk_url, [], null, true );
        }

        $localized = [
            'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
            'nonce'            => wp_create_nonce( 'eshb_native_checkout' ),
            'countriesJsonUrl' => ESHB_PL_URL . 'public/assets/lib/countries.json',
            'tokenParam'       => eshb_native_checkout_request_param(),
            // The token resolved for the current page render. JS sends
            // it back on every AJAX call so reservation lookups don't
            // depend on the cookie surviving.
            'token'            => eshb_native_checkout_token_from_request(),
            'reservation'      => $reservation_view,
            'pricing'          => $pricing,
            'gateways'         => $gateways,
            'cartBlock'        => $cart_block,
            // Multi-accommodation cart payload.
            'cart'             => [
                'items'      => $items_view,
                'count'      => count( $items_view ),
                'browseUrl'  => $this->browse_accommodations_url(),
            ],
            'i18n'        => [
                'missingTerms'         => __( 'Please accept the terms and conditions.', 'easy-hotel' ),
                'missingFields'        => __( 'Please fill in all required fields.', 'easy-hotel' ),
                'missingPayment'       => __( 'Please choose a payment method.', 'easy-hotel' ),
                'invalidEmail'         => __( 'Please enter a valid email address.', 'easy-hotel' ),
                'paymentFailed'        => __( 'Payment could not be completed. Please try again.', 'easy-hotel' ),
                'bookingSuccess'       => __( 'Booking confirmed! Redirecting…', 'easy-hotel' ),
                'couponApplying'       => __( 'Applying coupon…', 'easy-hotel' ),
                'couponRemoved'        => __( 'Coupon removed.', 'easy-hotel' ),
                'processing'           => __( 'Processing…', 'easy-hotel' ),
                'editServices'         => __( 'Edit', 'easy-hotel' ),
                'doneEditingServices'  => __( 'Done', 'easy-hotel' ),
                'noServicesSelected'   => __( 'None selected', 'easy-hotel' ),
                'confirmRemove'        => __( 'Remove this accommodation from your booking?', 'easy-hotel' ),
                'cartEmpty'            => __( 'Your booking is empty.', 'easy-hotel' ),
            ],
        ];

        /**
         * Filter the data localized to the checkout page. Extensions
         * (e.g. the EHB Deposit add-on) use this to inject their own
         * config blocks under custom keys without overwriting core fields.
         *
         * @param array $localized   The data passed to wp_localize_script.
         * @param array $pricing     The pricing payload.
         * @param array|null $reservation_view The reservation view-model.
         */
        $localized = apply_filters( 'eshb_native_checkout_localized_data', $localized, $pricing, $reservation_view );

        wp_localize_script( 'eshb-native-checkout', 'eshbNativeCheckout', $localized );
    }

    /**
     * Whether the current request is for the native checkout page.
     * Detection is shortcode-based so the page lookup is robust against renaming.
     */
    public function is_checkout_page() {
        if ( is_admin() || ! is_singular( 'page' ) ) return false;
        global $post;
        if ( ! $post ) return false;
        return has_shortcode( (string) $post->post_content, 'eshb_native_checkout' );
    }

    public function render_shortcode() {
        // The checkout page is per-visitor (reservation transient keyed
        // by cookie). On a live server with page caching enabled (WP
        // Rocket, W3TC, Cloudflare APO, hosting-level caches, etc.) the
        // page can otherwise be served from cache as the "no
        // reservation found" view to every visitor. Disable caching for
        // this page so the cookie/transient is always read fresh.
        nocache_headers();
        // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Standard caching-plugin constants, names cannot be prefixed.
        if ( ! defined( 'DONOTCACHEPAGE' ) ) {
            define( 'DONOTCACHEPAGE', true );
        }
        if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
            define( 'DONOTCACHEOBJECT', true );
        }
        if ( ! defined( 'DONOTCACHEDB' ) ) {
            define( 'DONOTCACHEDB', true );
        }
        // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

        ob_start();

        // Thank-you flow takes priority. Once payment captures, we
        // intentionally clear the reservation transient — so by the time
        // the browser arrives at ?booking=<id> the session is empty.
        // Render the template (which has its own thank-you branch) as
        // long as the booking exists, regardless of reservation state.
        // Read-only GET parameter used purely as a post lookup; no
        // state-changing action depends on it, so a nonce isn't required.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $booking_id_param = isset( $_GET['booking'] ) ? absint( $_GET['booking'] ) : 0;
        if ( $booking_id_param && get_post_type( $booking_id_param ) === 'eshb_booking' ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $thankyou_key = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';

            // Only the guest who just checked out (holding the key from the
            // redirect), the account that owns the booking, or staff may see it.
            if ( ! $this->can_view_thankyou( $booking_id_param, $thankyou_key ) ) {
                ?>
                <div class="eshb-native-checkout eshb-native-checkout--empty">
                    <div class="eshb-container">
                        <div class="eshb-card">
                            <h2><?php esc_html_e( 'Booking not found', 'easy-hotel' ); ?></h2>
                            <p><?php esc_html_e( 'Please use the link from your confirmation, or log in to your account to view your bookings.', 'easy-hotel' ); ?></p>
                        </div>
                    </div>
                </div>
                <?php
                return ob_get_clean();
            }

            $reservation_view = [];
            $items_view       = [];
            $pricing          = [];
            $gateways         = [];
            // Sibling bookings created in the same multi-accommodation
            // checkout, so the thank-you page can list them all. Each one is
            // checked the same way, so a foreign group id reveals nothing.
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $group_id        = isset( $_GET['group'] ) ? sanitize_text_field( wp_unslash( $_GET['group'] ) ) : '';
            $group_booking_ids = $group_id ? $this->get_group_booking_ids( $group_id ) : [ $booking_id_param ];
            $group_booking_ids = array_values( array_filter( $group_booking_ids, function ( $id ) use ( $thankyou_key ) {
                return $this->can_view_thankyou( $id, $thankyou_key );
            } ) );
            if ( empty( $group_booking_ids ) ) {
                $group_booking_ids = [ $booking_id_param ];
            }
            $template = ESHB_PL_PATH . 'admin/includes/native-checkout/templates/checkout-page.php';
            if ( file_exists( $template ) ) {
                include $template;
            }
            return ob_get_clean();
        }

        $items   = eshb_native_checkout_get_items();
        $pricing = ! empty( $items ) ? ESHB_Native_Pricing::calculate_cart( $items ) : [];

        $manager  = ESHB_Native_Gateway_Manager::instance();
        $gateways = $manager->get_gateways( true );

        if ( empty( $items ) ) {
            $token_param = eshb_native_checkout_request_param();
            ?>
            <div class="eshb-native-checkout eshb-native-checkout--empty">
                <div class="eshb-container">
                    <div class="eshb-card">
                        <h2><?php esc_html_e( 'No reservation found', 'easy-hotel' ); ?></h2>
                        <p><?php esc_html_e( 'Please go back and choose your accommodation to start booking.', 'easy-hotel' ); ?></p>
                    </div>
                </div>
            </div>
            <?php
            // Self-healing fallback: if the booking-form submit stored
            // a reservation token in sessionStorage but a CDN / security
            // plugin / canonical redirect dropped it from the URL,
            // reload once with the token appended so the server can
            // find the reservation.
            wp_register_script( 'eshb-native-checkout-recover', false, [], ESHB_VERSION, true );
            wp_enqueue_script( 'eshb-native-checkout-recover' );
            wp_add_inline_script( 'eshb-native-checkout-recover', '(function (param) {
                try {
                    var token = sessionStorage.getItem("eshb_native_checkout_token");
                    if (!token) return;
                    if (sessionStorage.getItem("eshb_native_checkout_recovered") === token) return;
                    var url = new URL(window.location.href);
                    if (url.searchParams.get(param) === token) return;
                    url.searchParams.set(param, token);
                    sessionStorage.setItem("eshb_native_checkout_recovered", token);
                    window.location.replace(url.toString());
                } catch (e) { /* ignore */ }
            })(' . wp_json_encode( $token_param ) . ');' );
            return ob_get_clean();
        }

        $items_view       = $this->build_cart_items_view( $items, $pricing );
        $reservation_view = ! empty( $items_view ) ? $items_view[0] : null;

        $template = ESHB_PL_PATH . 'admin/includes/native-checkout/templates/checkout-page.php';
        if ( file_exists( $template ) ) {
            include $template;
        }

        return ob_get_clean();
    }

    /**
     * Build the per-item view-models for the cart (each = a reservation view
     * plus its computed pricing slice). Returned as a numeric list so the
     * template/JS can iterate in insertion order.
     */
    private function build_cart_items_view( array $items, array $cart_pricing = [] ) {
        $views         = [];
        $item_pricings = isset( $cart_pricing['items'] ) && is_array( $cart_pricing['items'] ) ? $cart_pricing['items'] : [];
        foreach ( $items as $item_key => $reservation ) {
            if ( ! is_array( $reservation ) ) continue;
            $view             = $this->build_reservation_view( $reservation );
            $view['item_key'] = $item_key;
            $view['pricing']  = isset( $item_pricings[ $item_key ] ) ? $item_pricings[ $item_key ] : [];
            $views[]          = $view;
        }
        return $views;
    }

    /**
     * Soonest-expiring cart-blocking hold (unix ts) across all cart items.
     */
    private function earliest_block_until( array $items ) {
        $booking  = ESHB_Booking::instance();
        $earliest = 0;
        foreach ( $items as $item ) {
            $accom = (int) ( $item['accomodation_id'] ?? 0 );
            if ( ! $accom ) continue;
            $until = (int) $booking->eshb_get_my_block_until( $accom );
            if ( $until > 0 && ( $earliest === 0 || $until < $earliest ) ) {
                $earliest = $until;
            }
        }
        return $earliest;
    }

    /**
     * URL to send the buyer to when they want to add another accommodation.
     */
    private function browse_accommodations_url() {
        $archive = get_post_type_archive_link( 'eshb_accomodation' );
        return $archive ? $archive : home_url( '/' );
    }

    /**
     * All booking ids that belong to one checkout group, ordered oldest first.
     */
    private function get_group_booking_ids( $group_id ) {
        $group_id = (string) $group_id;
        if ( $group_id === '' ) return [];

        // Fast path: ids stashed at checkout completion — independent of
        // meta_query / post-status nuances.
        $cached = get_transient( 'eshb_native_group_' . $group_id );
        if ( is_array( $cached ) && ! empty( $cached ) ) {
            return array_map( 'intval', $cached );
        }

        // Fallback: query bookings carrying this group meta across every
        // registered booking status.
        $statuses = array_keys( ESHB_Helper::eshb_get_booking_statuses() );
        $statuses = array_merge( $statuses, [ 'publish', 'pending', 'draft', 'private' ] );

        $q = get_posts( [
            'post_type'      => 'eshb_booking',
            'post_status'    => $statuses,
            'posts_per_page' => -1,
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'no_found_rows'  => true,
            'fields'         => 'ids',
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Fallback lookup only when the group transient is missing; no alternative index exists.
            'meta_query'     => [
                [
                    'key'   => 'native_group_id',
                    'value' => $group_id,
                ],
            ],
        ] );
        return is_array( $q ) ? array_map( 'intval', $q ) : [];
    }

    /**
     * Whether the current visitor may see a booking on the thank-you page:
     * they hold the key from the checkout redirect, own the booking through
     * their account, or can edit it from the admin.
     *
     * @param int    $booking_id
     * @param string $key Key from the URL ('' when absent).
     * @return bool
     */
    private function can_view_thankyou( $booking_id, $key ) {
        $booking_id = (int) $booking_id;
        if ( ! $booking_id || get_post_type( $booking_id ) !== 'eshb_booking' ) {
            return false;
        }

        $stored = (string) get_post_meta( $booking_id, self::THANKYOU_KEY_META, true );
        if ( $stored !== '' && $key !== '' && hash_equals( $stored, $key ) ) {
            return true;
        }

        if ( current_user_can( 'edit_post', $booking_id ) ) {
            return true;
        }

        return class_exists( 'ESHB_Native_Account' )
            && ESHB_Native_Account::instance()->customer instanceof ESHB_Native_Account_Customer
            && ESHB_Native_Account::instance()->customer->user_owns_booking( $booking_id );
    }

    /**
     * Map the reservation transient + pricing into a view-model used by
     * the template. Keeping the template free of business logic.
     */
    public function build_reservation_view( array $reservation ) {
        $accomodation_id = (int) ( $reservation['accomodation_id'] ?? 0 );

        $services = [];

        // Only what the accommodation actually offers (Accommodation Options →
        // Services). An empty list means this room has no extras, in which case
        // the template drops the Additional Services block entirely.
        $available_services = ESHB_Helper::eshb_get_accomodation_service_ids( $accomodation_id );

        $mandatory_ids = ESHB_Helper::eshb_get_mandatory_service_ids( $accomodation_id );

        foreach ( $available_services as $service_id ) {
            $service_id  = (int) $service_id;
            $svc_meta    = get_post_meta( $service_id, 'eshb_service_metaboxes', true );
            if ( empty( $svc_meta ) ) continue;
            $services[] = [
                'id'           => $service_id,
                'title'        => get_the_title( $service_id ),
                'price'        => floatval( $svc_meta['service_price'] ?? 0 ),
                'periodicity'  => $svc_meta['service_periodicity'] ?? 'once',
                'charge_type'  => $svc_meta['service_charge_type'] ?? 'room',
                'max_quantity' => ESHB_Helper::eshb_get_service_max_quantity( $service_id, $svc_meta ),
                // "Per Room" services are priced by the room count, so the
                // editor shows no stepper for them — same rule as the form.
                'allows_qty'   => ESHB_Helper::eshb_service_allows_quantity( $service_id, $svc_meta ),
                'is_mandatory' => isset( $mandatory_ids[ $service_id ] ),
            ];
        }

        // Preselected services from the reservation.
        $selected = [];
        if ( ! empty( $reservation['extra_services'] ) && is_array( $reservation['extra_services'] ) ) {
            foreach ( $reservation['extra_services'] as $sel ) {
                if ( ! empty( $sel['id'] ) ) {
                    $selected[ (int) $sel['id'] ] = ESHB_Helper::eshb_clamp_service_quantity(
                        $sel['id'],
                        $sel['quantity'] ?? 1
                    );
                }
            }
        }

        // A mandatory service is part of the reservation whether or not the
        // stored selection still carries it (older cart, tampered request).
        foreach ( array_keys( $mandatory_ids ) as $mandatory_id ) {
            if ( empty( $selected[ $mandatory_id ] ) ) {
                $selected[ $mandatory_id ] = ESHB_Helper::eshb_clamp_service_quantity( $mandatory_id, 1 );
            }
        }

        $check_in_format  = get_option( 'date_format' );

        return [
            'accomodation_id'     => $accomodation_id,
            'accomodation_title'  => get_the_title( $accomodation_id ),
            'start_date'          => $reservation['start_date'] ?? '',
            'end_date'            => $reservation['end_date'] ?? '',
            'start_date_label'    => $reservation['start_date'] ? ESHB_Helper::eshb_format_date( $reservation['start_date'], $check_in_format ) : '',
            'end_date_label'      => $reservation['end_date'] ? ESHB_Helper::eshb_format_date( $reservation['end_date'], $check_in_format ) : '',
            'start_time'          => $reservation['start_time'] ?? '',
            'end_time'            => $reservation['end_time'] ?? '',
            'room_quantity'       => (int) ( $reservation['room_quantity'] ?? 1 ),
            'extra_bed_quantity'  => (int) ( $reservation['extra_bed_quantity'] ?? 0 ),
            'adult_quantity'      => (int) ( $reservation['adult_quantity'] ?? 1 ),
            'children_quantity'   => (int) ( $reservation['children_quantity'] ?? 0 ),
            'services'            => $services,
            'selected_services'   => $selected,
        ];
    }

    /* -----------------------------------------------------------------------
     * AJAX endpoints
     * -------------------------------------------------------------------- */

    public function ajax_apply_coupon() {
        $this->verify_native_nonce();

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $coupon         = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';
        // Customer email may be empty here (user hasn't typed it yet
        // when applying the coupon). Per-user check is then skipped and
        // re-enforced server-side during create_payment / complete.
        $customer_email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        $items = $this->update_cart_extras_from_post();

        $pricing = ESHB_Native_Pricing::calculate_cart( $items, $coupon, $customer_email );
        if ( $coupon !== '' && empty( $pricing['couponValid'] ) ) {
            wp_send_json_error( [ 'message' => $pricing['couponMessage'] ?: __( 'Invalid coupon.', 'easy-hotel' ), 'pricing' => $pricing ] );
        }

        wp_send_json_success( [ 'pricing' => $pricing ] );
    }

    /**
     * Live recalculation when the buyer edits extra services on any item.
     * Server-authoritative: the cart is the source of truth for pricing, so
     * the JS just renders whatever this returns.
     */
    public function ajax_recalculate() {
        $this->verify_native_nonce();

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $coupon         = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';
        $customer_email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        $items = $this->update_cart_extras_from_post();

        $pricing = ESHB_Native_Pricing::calculate_cart( $items, $coupon, $customer_email );
        wp_send_json_success( [ 'pricing' => $pricing ] );
    }

    /**
     * Remove one accommodation from the cart. Releases its hold (unless
     * another cart item shares the accommodation) and returns the updated
     * cart pricing, or cart_empty=true when nothing is left.
     */
    public function ajax_remove_item() {
        $this->verify_native_nonce();

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $item_key       = isset( $_POST['item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['item_key'] ) ) : '';
        $coupon         = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';
        $customer_email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if ( $item_key === '' ) {
            wp_send_json_error( [ 'message' => __( 'Invalid item.', 'easy-hotel' ) ] );
        }

        $removed = eshb_native_checkout_remove_item( $item_key );
        if ( is_array( $removed ) && ! empty( $removed['accomodation_id'] ) ) {
            $accom = (int) $removed['accomodation_id'];
            // Only release the hold when no remaining item shares the
            // accommodation (holds are keyed per accommodation per session).
            $still_held = false;
            foreach ( eshb_native_checkout_get_items() as $it ) {
                if ( (int) ( $it['accomodation_id'] ?? 0 ) === $accom ) { $still_held = true; break; }
            }
            if ( ! $still_held ) {
                ESHB_Booking::instance()->eshb_release_cart_block( $accom );
            }
        }

        $items = eshb_native_checkout_get_items();
        if ( empty( $items ) ) {
            wp_send_json_success( [ 'cart_empty' => true ] );
        }

        $pricing = ESHB_Native_Pricing::calculate_cart( $items, $coupon, $customer_email );
        wp_send_json_success( [ 'cart_empty' => false, 'pricing' => $pricing ] );
    }

    public function ajax_create_payment() {
        $this->verify_native_nonce();

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $gateway_id = isset( $_POST['gateway'] ) ? sanitize_key( wp_unslash( $_POST['gateway'] ) ) : '';
        $coupon     = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        $customer = $this->customer_from_post();
        $items    = $this->update_cart_extras_from_post();

        $validation = $this->validate_customer( $customer );
        if ( is_wp_error( $validation ) ) {
            wp_send_json_error( [ 'message' => $validation->get_error_message() ] );
        }

        $gateway = ESHB_Native_Gateway_Manager::instance()->get_gateway( $gateway_id );
        if ( ! $gateway || ! $gateway->is_enabled() ) {
            wp_send_json_error( [ 'message' => __( 'Selected payment method is not available.', 'easy-hotel' ) ] );
        }

        // Rooms may have been booked by someone else since they were added to
        // the cart; stop before a payment is even started.
        $availability_error = ESHB_Booking::instance()->eshb_validate_cart_availability( $items );
        if ( $availability_error !== '' ) {
            wp_send_json_error( [ 'message' => $availability_error ] );
        }

        $pricing = ESHB_Native_Pricing::calculate_cart( $items, $coupon, $customer['email'] ?? '' );

        // Block payment creation if the coupon was rejected by the
        // per-user / global limit checks; otherwise the discount would
        // silently zero out at the strict re-validation step below.
        if ( $coupon !== '' && empty( $pricing['couponValid'] ) ) {
            wp_send_json_error( [ 'message' => $pricing['couponMessage'] ?: __( 'Coupon is no longer valid.', 'easy-hotel' ) ] );
        }

        // First item is the representative reservation for gateway metadata
        // (e.g. the PayPal order description); the charged amount comes from
        // the whole-cart pricing grandTotal.
        $representative = reset( $items );
        $result = $gateway->create_payment( is_array( $representative ) ? $representative : [], $customer, $pricing );

        if ( empty( $result['success'] ) ) {
            wp_send_json_error( [ 'message' => $result['message'] ?? __( 'Could not initiate payment.', 'easy-hotel' ) ] );
        }

        // Remember which order this cart is paying with, and for how much,
        // so completion can refuse any other order or a changed total.
        if ( $gateway->requires_payment_verification() ) {
            $order_id = (string) ( $result['data']['order_id'] ?? '' );
            $bound    = $order_id !== '' && eshb_native_checkout_set_pending_payment( [
                'gateway'  => $gateway->get_id(),
                'order_id' => $order_id,
                'amount'   => $result['amount'] ?? ( $pricing['grandTotal'] ?? 0 ),
                'currency' => $result['currency'] ?? '',
            ] );
            if ( ! $bound ) {
                wp_send_json_error( [ 'message' => __( 'Could not initiate payment.', 'easy-hotel' ) ] );
            }
        }

        wp_send_json_success( $result['data'] ?? [] );
    }

    public function ajax_complete_checkout() {
        $this->verify_native_nonce();

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $gateway_id = isset( $_POST['gateway'] ) ? sanitize_key( wp_unslash( $_POST['gateway'] ) ) : '';
        $coupon     = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        $customer = $this->customer_from_post();
        $items    = $this->update_cart_extras_from_post();

        $validation = $this->validate_customer( $customer );
        if ( is_wp_error( $validation ) ) {
            wp_send_json_error( [ 'message' => $validation->get_error_message() ] );
        }

        $gateway = ESHB_Native_Gateway_Manager::instance()->get_gateway( $gateway_id );
        if ( ! $gateway || ! $gateway->is_enabled() ) {
            wp_send_json_error( [ 'message' => __( 'Selected payment method is not available.', 'easy-hotel' ) ] );
        }

        // 1. Collect the gateway's approval data (e.g. PayPal order id). Nonce is verified
        // at the top of this method; the loop sanitizes each value, so the
        // outer raw $_POST access is safe.
        $gateway_params = [];
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( ! empty( $_POST['gatewayParams'] ) && is_array( $_POST['gatewayParams'] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            foreach ( wp_unslash( $_POST['gatewayParams'] ) as $key => $val ) {
                $gateway_params[ sanitize_key( $key ) ] = sanitize_text_field( $val );
            }
        }

        // 2. Re-calculate cart pricing server-side; never trust client total.
        //    Done before capturing so a changed total is refused before any
        //    money moves.
        $pricing = ESHB_Native_Pricing::calculate_cart( $items, $coupon, $customer['email'] ?? '' );

        // Online gateways: accept only the order created for this cart, for
        // this total, and only once.
        $pending      = null;
        $payment_lock = '';
        if ( $gateway->requires_payment_verification() ) {
            $pending  = eshb_native_checkout_get_pending_payment();
            $order_id = (string) ( $gateway_params['order_id'] ?? '' );

            if ( ! $pending || $pending['gateway'] !== $gateway->get_id() || $order_id === '' || ! hash_equals( $pending['order_id'], $order_id ) ) {
                wp_send_json_error( [ 'message' => __( 'This payment does not belong to your reservation. Please try again.', 'easy-hotel' ) ] );
            }

            $expected_amount = number_format( (float) ( $pricing['grandTotal'] ?? 0 ), 2, '.', '' );
            if ( $expected_amount !== $pending['amount'] ) {
                // Cart, extras or coupon changed after the order was created.
                // Nothing has been captured yet, so start the payment over.
                eshb_native_checkout_set_pending_payment( [] );
                wp_send_json_error( [ 'message' => __( 'Your booking total changed after the payment was started. Please pay again.', 'easy-hotel' ) ] );
            }

            // add_option() is atomic on the unique option_name, so two
            // concurrent requests with the same order can't both continue.
            // A lock left by a request that died is taken over after a while.
            $payment_lock = 'eshb_native_payment_lock_' . md5( $gateway->get_id() . '|' . $order_id );
            if ( ! add_option( $payment_lock, time(), '', false ) ) {
                $locked_at = (int) get_option( $payment_lock, 0 );
                if ( ( time() - $locked_at ) < 5 * MINUTE_IN_SECONDS ) {
                    wp_send_json_error( [ 'message' => __( 'This payment is already being processed. Please wait a moment.', 'easy-hotel' ) ] );
                }
                update_option( $payment_lock, time(), false );
            }
        }

        // 2a. Final availability check before any money moves. The rooms are
        //     locked until this request ends, so a guest checking out the same
        //     accommodation at the same moment waits and then sees the booking
        //     created here.
        if ( ! $this->lock_cart_accommodations( $items ) ) {
            if ( $payment_lock ) delete_option( $payment_lock );
            wp_send_json_error( [ 'message' => __( 'Another guest is completing a booking for this accommodation. Please try again in a moment.', 'easy-hotel' ) ] );
        }

        $availability_error = ESHB_Booking::instance()->eshb_validate_cart_availability( $items );
        if ( $availability_error !== '' ) {
            if ( $payment_lock ) delete_option( $payment_lock );
            wp_send_json_error( [ 'message' => $availability_error ] );
        }

        $capture = $gateway->capture_payment( $gateway_params );
        if ( empty( $capture['success'] ) ) {
            if ( $payment_lock ) delete_option( $payment_lock );
            wp_send_json_error( [ 'message' => $capture['message'] ?? __( 'Payment could not be confirmed.', 'easy-hotel' ) ] );
        }

        // 2b. Capture succeeded; for online gateways check it matches the order.

        if ( $pending ) {
            $captured_amount   = number_format( (float) ( $capture['amount'] ?? 0 ), 2, '.', '' );
            $captured_currency = strtoupper( (string) ( $capture['currency'] ?? '' ) );

            if ( $captured_amount !== $pending['amount'] || $captured_currency !== $pending['currency'] ) {
                // Money moved but not the amount this cart costs: create no
                // booking, and keep the lock so the order can't be retried.
                wp_send_json_error( [ 'message' => sprintf(
                    /* translators: %s: payment transaction ID */
                    __( 'Your payment could not be verified, so no booking was made. Please contact us with transaction ID %s.', 'easy-hotel' ),
                    $capture['transaction_id'] ?? $order_id
                ) ] );
            }
        }

        // 3. Insert one linked booking per accommodation.
        $customer['gateway'] = $gateway->get_id();
        $group       = ESHB_Native_Booking_Handler::insert_cart_bookings( $items, $customer, $pricing );
        $booking_ids = ! empty( $group['booking_ids'] ) ? $group['booking_ids'] : [];
        $group_id    = $group['group_id'] ?? '';
        $totals      = ! empty( $group['totals'] ) ? $group['totals'] : [];

        if ( empty( $booking_ids ) ) {
            // The cart and its order binding are still there, so a retry
            // re-runs the (idempotent) capture and creates the bookings.
            if ( $payment_lock ) delete_option( $payment_lock );
            wp_send_json_error( [ 'message' => __( 'Booking could not be created. Please contact us.', 'easy-hotel' ) ] );
        }

        // 4. Record one payment per booking, splitting the captured amount in
        //    proportion to each booking total (last booking takes the
        //    rounding remainder so the parts sum to the captured amount).
        // A capture the gateway reports as pending (e.g. PayPal eCheck or
        // payment review) has not brought any money in yet: record 0 and say
        // why on the payment, the same way offline gateways do.
        $payment_pending = ! empty( $capture['pending'] );
        $captured_amount = $payment_pending ? 0.0 : (float) ( $capture['amount'] ?? ( $pricing['grandTotal'] ?? 0 ) );
        $grand_total     = (float) ( $pricing['grandTotal'] ?? 0 );
        $assigned        = 0.0;
        $last_booking    = end( $booking_ids );
        foreach ( $booking_ids as $bid ) {
            $booking_total = (float) ( $totals[ $bid ] ?? 0 );
            if ( $bid === $last_booking ) {
                $share = round( $captured_amount - $assigned, 2 );
            } elseif ( $grand_total > 0 ) {
                $share = round( $captured_amount * ( $booking_total / $grand_total ), 2 );
            } else {
                $share = 0.0;
            }
            $assigned += $share;

            $payment_meta = [
                'transaction_id' => $capture['transaction_id'] ?? '',
                'gateway'        => $gateway->get_id(),
                'amount'         => $share,
                'currency'       => $capture['currency'] ?? '',
                'mode'           => $capture['mode'] ?? 'live',
                'fee'            => 0,
                'note'           => $payment_pending
                    ? $this->pending_payment_note( $gateway, $booking_total, $capture['pending_reason'] ?? '' )
                    : $gateway->get_payment_note( $booking_total ),
            ];
            ESHB_Native_Booking_Handler::record_payment( $bid, $payment_meta, $customer );
        }

        // 5. Transition every booking to its completed/processing status.
        //
        // The announcements are held back until step 6 has mailed the
        // customer. Listeners on eshb_native_checkout_booking_status_changed
        // send their own status email, and they fire once per booking — so
        // announcing here would beat the group email to the inbox and turn
        // one checkout into several messages.
        $eshb_settings = get_option( 'eshb_settings', [] );
        $new_status    = ! empty( $eshb_settings['booking-auto-approval'] ) ? 'completed' : 'processing';
        // Offline gateways that wait for funds (bank transfer) keep the
        // booking on hold instead of moving it straight to processing.
        $new_status    = $gateway->get_completed_status( $new_status );
        // An online payment the gateway is still holding keeps the booking on
        // hold until staff see it settle.
        if ( $payment_pending ) {
            $new_status = 'on-hold';
        }
        $announce = [];
        foreach ( $booking_ids as $bid ) {
            $completed_status = apply_filters(
                'eshb_native_checkout_completed_status',
                $new_status,
                $bid,
                $items,
                $pricing
            );
            ESHB_Native_Booking_Handler::update_status( $bid, $completed_status, false );
            $announce[ $bid ] = $completed_status;
        }

        // 5b. Record coupon usage ONCE for the whole checkout (logged
        // against the first booking in the group).
        $coupon_id_used = (int) ( $pricing['couponId'] ?? 0 );
        if ( $coupon_id_used > 0 && ! empty( $pricing['couponValid'] ) ) {
            $coupon_obj = new ESHB_Native_Checkout_Coupon( $coupon_id_used );
            $coupon_obj->set_usage_count( (int) $coupon_obj->get_usage_count() + 1 );

            $used_by = $coupon_obj->get_used_by();
            if ( ! is_array( $used_by ) ) {
                $used_by = [];
            }
            $used_by[] = [
                'booking_id' => reset( $booking_ids ),
                'name'       => trim( ( $customer['first_name'] ?? '' ) . ' ' . ( $customer['last_name'] ?? '' ) ),
                'email'      => $customer['email'] ?? '',
                'code'       => $pricing['couponCode'] ?? '',
                'discount'   => (float) ( $pricing['couponDiscount'] ?? 0 ),
                'used_at'    => current_time( 'mysql' ),
            ];
            $coupon_obj->set_used_by( $used_by );
        }

        // 6. Send emails (one confirmation listing all bookings in the group).
        // A misconfigured SMTP / mail server must not bubble up as a 500 —
        // payment has already been captured and the bookings exist, so we
        // swallow failures silently.
        try {
            ESHB_Native_Email_Handler::send_customer_confirmation( $booking_ids, $customer );
        } catch ( \Throwable $e ) {
            unset( $e );
        }
        try {
            ESHB_Native_Email_Handler::send_admin_notification( $booking_ids, $customer );
        } catch ( \Throwable $e ) {
            unset( $e );
        }

        // 6b. Now that the customer has their one email, let the rest of the
        // system know the bookings changed status. Listeners that mail on a
        // status change find it already recorded as sent and stay quiet.
        foreach ( $announce as $bid => $status ) {
            ESHB_Native_Booking_Handler::announce_status( $bid, $status );
        }

        // 7. Release every hold and clear the cart — no double-bookings on
        //    refresh.
        foreach ( $items as $item ) {
            $accom = (int) ( $item['accomodation_id'] ?? 0 );
            if ( $accom ) {
                ESHB_Booking::instance()->eshb_release_cart_block( $accom );
            }
        }
        eshb_native_checkout_clear_reservation();

        // The cart (and its order binding) is gone, so the order can't be
        // completed again; the lock has done its job.
        if ( $payment_lock ) {
            delete_option( $payment_lock );
        }

        // Stash the group's booking ids so the thank-you page can list them
        // all without depending on a meta_query (which can miss on some
        // object-cache / status configurations). Falls back to the meta
        // query in get_group_booking_ids() if this ever expires.
        if ( $group_id !== '' && ! empty( $booking_ids ) ) {
            set_transient( 'eshb_native_group_' . $group_id, array_map( 'intval', $booking_ids ), DAY_IN_SECONDS );
        }

        $first_booking = (int) reset( $booking_ids );

        // Same language handling as the checkout redirect above: this is an
        // AJAX handler, so the referer (the checkout page) is what knows the
        // language, with the booked accommodation as the backstop.
        $thankyou_lang = eshb_native_checkout_request_language();

        if ( '' === $thankyou_lang ) {
            $first_meta    = get_post_meta( $first_booking, 'eshb_booking_metaboxes', true );
            $thankyou_lang = is_array( $first_meta ) && ! empty( $first_meta['booking_accomodation_id'] )
                ? eshb_native_checkout_post_language( $first_meta['booking_accomodation_id'] )
                : '';
        }

        // Random key that unlocks the thank-you page for these bookings;
        // without it, ?booking=<id> could be enumerated to read any booking.
        $thankyou_key = wp_generate_password( 24, false );
        foreach ( $booking_ids as $bid ) {
            update_post_meta( $bid, self::THANKYOU_KEY_META, $thankyou_key );
        }

        $redirect = add_query_arg(
            [ 'booking' => $first_booking, 'group' => $group_id, 'key' => $thankyou_key ],
            eshb_native_checkout_url( $thankyou_lang )
        );

        wp_send_json_success( [
            'booking_id'   => $first_booking,
            'booking_ids'  => $booking_ids,
            'redirect_url' => apply_filters( 'eshb_native_checkout_thankyou_url', $redirect, $first_booking ),
        ] );
    }

    /**
     * Release the current reservation and its cart-blocking hold. Called
     * from the checkout page when the hold countdown reaches zero so the
     * held dates are freed for other visitors and a stale reservation can't
     * be completed after expiry (the page reloads to the empty state).
     */
    public function ajax_release_reservation() {
        $this->verify_native_nonce();

        foreach ( eshb_native_checkout_get_items() as $item ) {
            $accom = (int) ( $item['accomodation_id'] ?? 0 );
            if ( $accom ) {
                ESHB_Booking::instance()->eshb_release_cart_block( $accom );
            }
        }

        eshb_native_checkout_clear_reservation();

        wp_send_json_success( [ 'released' => true ] );
    }

    /* -----------------------------------------------------------------------
     * Helpers
     * -------------------------------------------------------------------- */

    private function verify_native_nonce() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'eshb_native_checkout' ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid request. Please refresh the page.', 'easy-hotel' ) ] );
        }
    }

    /**
     * Apply the latest per-item extra-services selections from the request
     * to the stored cart, then return the (keyed) cart items. Bails with a
     * JSON error when the cart has expired/emptied.
     *
     * Expected payload: `itemsServices` = JSON object
     *   { "<item_key>": [ { id, quantity }, ... ], ... }
     */
    private function update_cart_extras_from_post() {
        // Nonce verified in the calling ajax_* method.
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if ( ! empty( $_POST['itemsServices'] ) ) {
            $raw     = sanitize_text_field( wp_unslash( $_POST['itemsServices'] ) );
            $decoded = json_decode( $raw, true );
            if ( is_array( $decoded ) ) {
                $cart = eshb_native_checkout_get_cart();
                if ( is_array( $cart ) && ! empty( $cart['items'] ) ) {
                    foreach ( $decoded as $item_key => $svcs ) {
                        $item_key = sanitize_text_field( $item_key );
                        if ( ! isset( $cart['items'][ $item_key ] ) || ! is_array( $svcs ) ) continue;
                        $services = [];
                        foreach ( $svcs as $svc ) {
                            if ( ! is_array( $svc ) || empty( $svc['id'] ) ) continue;
                            $services[] = [
                                'id'       => (int) $svc['id'],
                                'quantity' => ESHB_Helper::eshb_clamp_service_quantity(
                                    $svc['id'],
                                    max( 0, (int) ( $svc['quantity'] ?? 0 ) )
                                ),
                            ];
                        }
                        $accom_id = (int) ( $cart['items'][ $item_key ]['accomodation_id'] ?? 0 );

                        // The editor only offers this accommodation's services
                        // and locks the mandatory ones; a hand-crafted request
                        // does neither, so both rules are re-applied here.
                        $services = ESHB_Helper::eshb_filter_services_by_accomodation( $services, $accom_id );
                        $cart['items'][ $item_key ]['extra_services'] = ESHB_Helper::eshb_enforce_mandatory_services( $services, $accom_id );
                    }
                    eshb_native_checkout_save_cart( $cart );
                }
            }
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        $items = eshb_native_checkout_get_items();
        if ( empty( $items ) ) {
            wp_send_json_error( [ 'message' => __( 'Your reservation has expired. Please start again.', 'easy-hotel' ), 'cart_empty' => true ] );
        }
        return $items;
    }

    private function customer_from_post() {
        // Nonce verified in the calling ajax_* method.
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $customer = [
            'first_name' => isset( $_POST['firstName'] ) ? sanitize_text_field( wp_unslash( $_POST['firstName'] ) ) : '',
            'last_name'  => isset( $_POST['lastName'] ) ? sanitize_text_field( wp_unslash( $_POST['lastName'] ) ) : '',
            'email'      => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
            'phone'      => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
            'country'    => isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '',
            'state'      => isset( $_POST['state'] ) ? sanitize_text_field( wp_unslash( $_POST['state'] ) ) : '',
            'city'       => isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '',
            'postcode'   => isset( $_POST['postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['postcode'] ) ) : '',
            'notes'      => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
        ];
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        /**
         * Filter the customer data collected from the checkout request.
         *
         * Add-ons that put extra fields on the checkout (e.g. EHB Checkout
         * Field Manager) append their own sanitized keys here. Whatever this
         * returns is stored verbatim as the booking's
         * `eshb_booking_customer_details_metaboxes` meta, so anything added
         * must already be sanitized and should be a scalar.
         *
         * The nine built-in keys must not be renamed or removed — every
         * booking ever placed is stored under them.
         *
         * @param array $customer Sanitized customer data.
         */
        return (array) apply_filters( 'eshb_native_checkout_customer_from_post', $customer );
    }

    private function validate_customer( array $customer ) {
        // City and country are always required. State is conditional —
        // some countries (e.g. Vatican City) have no states in the JSON,
        // so the JS layer disables the field and we mirror that here by
        // skipping the state requirement when it's absent.
        $required = [ 'first_name', 'last_name', 'email', 'phone', 'country', 'city' ];

        /**
         * Filter which customer keys must be filled in.
         *
         * Add-ons that let the site owner turn checkout fields off (e.g. EHB
         * Checkout Field Manager) narrow this list so the server mirrors the
         * form actually shown. `state` is deliberately never in the default
         * list — see the note above.
         *
         * @param string[] $required Customer array keys that must be non-empty.
         * @param array    $customer The sanitized customer data.
         */
        $required = (array) apply_filters( 'eshb_native_checkout_required_customer_fields', $required, $customer );

        foreach ( $required as $key ) {
            if ( empty( $customer[ $key ] ) ) {
                return new WP_Error( 'missing_field', __( 'Please fill in all required fields.', 'easy-hotel' ) );
            }
        }
        // Only enforce the format when an address was actually collected; the
        // emptiness check above already covers the required case.
        if ( ! empty( $customer['email'] ) && ! is_email( $customer['email'] ) ) {
            return new WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'easy-hotel' ) );
        }
        return true;
    }
}
