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

        $availability_error = $this->validate_availability( $reservation );
        if ( $availability_error ) {
            wp_send_json_error( [ 'error' => $availability_error ] );
        }

        // Reuse existing pricing pipeline; if it returns no data, the request is invalid.
        $pricing = ESHB_Native_Pricing::calculate( $reservation );
        if ( empty( $pricing ) ) {
            wp_send_json_error( [ 'error' => [ 'code' => 'pricing_failed', 'message' => __( 'Unable to calculate price.', 'easy-hotel' ) ] ] );
        }

        $token = eshb_native_checkout_set_reservation( $reservation );

        // Place a temporary hold on the dates so concurrent visitors are
        // told these dates are reserved. The hold is keyed by the native
        // token (now resolvable via the cookie set above), and released on
        // booking completion or when the countdown expires. Must run after
        // set_reservation() so the session token exists.
        if ( $token ) {
            ESHB_Booking::instance()->eshb_block_dates_for_reservation(
                $reservation['accomodation_id'],
                $reservation['start_date'],
                $reservation['end_date'],
                max( 1, (int) $reservation['room_quantity'] )
            );
        }

        // Pass the reservation token in the redirect URL — cookies are
        // unreliable on some live hosts (Set-Cookie stripped from AJAX
        // responses, edge caches, etc.) so the URL is the source of
        // truth and the cookie is a best-effort backup. Force a
        // trailing slash before adding the query arg so WP's canonical
        // redirect can't bounce the URL and drop the param.
        $checkout_url = trailingslashit( eshb_native_checkout_url() );
        if ( $token ) {
            $checkout_url = add_query_arg( eshb_native_checkout_request_param(), $token, $checkout_url );
        }

        wp_send_json_success( [
            'booking-type'    => 'native_checkout',
            'message'         => __( 'Redirecting to checkout…', 'easy-hotel' ),
            'redirect_url'    => $checkout_url,
            'token'           => $token,
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

        return null;
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
                    $selected_services[] = [
                        'id'       => isset( $svc['id'] ) ? (int) $svc['id'] : 0,
                        'quantity' => isset( $svc['quantity'] ) ? (int) $svc['quantity'] : 0,
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

        $reservation = eshb_native_checkout_get_reservation();
        $pricing     = is_array( $reservation ) ? ESHB_Native_Pricing::calculate( $reservation ) : [];

        $manager  = ESHB_Native_Gateway_Manager::instance();
        $gateways = [];
        foreach ( $manager->get_gateways( true ) as $gateway ) {
            $gateways[] = $gateway->get_frontend_data();
        }

        $reservation_view = $reservation ? $this->build_reservation_view( $reservation ) : null;

        // Cart-blocking countdown data for the checkout page. `until` is
        // the unix timestamp this visitor's hold expires at (0 when none
        // or blocking disabled); the JS runs a live countdown and releases
        // the reservation when it reaches zero.
        $eshb_settings_cb = get_option( 'eshb_settings', [] );
        $cart_block = [ 'enabled' => false, 'until' => 0 ];
        if ( ! empty( $eshb_settings_cb['cart-blocking-switcher'] ) && $reservation && ! empty( $reservation['accomodation_id'] ) ) {
            $cart_block = [
                'enabled' => true,
                'until'   => ESHB_Booking::instance()->eshb_get_my_block_until( (int) $reservation['accomodation_id'] ),
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
        if ( ! defined( 'DONOTCACHEPAGE' ) ) {
            define( 'DONOTCACHEPAGE', true );
        }
        if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
            define( 'DONOTCACHEOBJECT', true );
        }
        if ( ! defined( 'DONOTCACHEDB' ) ) {
            define( 'DONOTCACHEDB', true );
        }

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
            $reservation_view = [];
            $pricing          = [];
            $gateways         = [];
            $template = ESHB_PL_PATH . 'admin/includes/native-checkout/templates/checkout-page.php';
            if ( file_exists( $template ) ) {
                include $template;
            }
            return ob_get_clean();
        }

        $reservation = eshb_native_checkout_get_reservation();
        $pricing     = is_array( $reservation ) ? ESHB_Native_Pricing::calculate( $reservation ) : [];

        $manager  = ESHB_Native_Gateway_Manager::instance();
        $gateways = $manager->get_gateways( true );

        if ( empty( $reservation ) ) {
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
            <script>
            // Self-healing fallback: if the booking-form submit stored
            // a reservation token in sessionStorage but a CDN / security
            // plugin / canonical redirect dropped it from the URL,
            // reload once with the token appended so the server can
            // find the reservation.
            (function () {
                try {
                    var token = sessionStorage.getItem('eshb_native_checkout_token');
                    if (!token) return;
                    if (sessionStorage.getItem('eshb_native_checkout_recovered') === token) return;
                    var url = new URL(window.location.href);
                    if (url.searchParams.get('<?php echo esc_js( $token_param ); ?>') === token) return;
                    url.searchParams.set('<?php echo esc_js( $token_param ); ?>', token);
                    sessionStorage.setItem('eshb_native_checkout_recovered', token);
                    window.location.replace(url.toString());
                } catch (e) { /* ignore */ }
            })();
            </script>
            <?php
            return ob_get_clean();
        }

        $reservation_view = $this->build_reservation_view( $reservation );

        $template = ESHB_PL_PATH . 'admin/includes/native-checkout/templates/checkout-page.php';
        if ( file_exists( $template ) ) {
            include $template;
        }

        return ob_get_clean();
    }

    /**
     * Map the reservation transient + pricing into a view-model used by
     * the template. Keeping the template free of business logic.
     */
    public function build_reservation_view( array $reservation ) {
        $accomodation_id = (int) ( $reservation['accomodation_id'] ?? 0 );
        $accom_meta      = get_post_meta( $accomodation_id, 'eshb_accomodation_metaboxes', true );

        $services = [];
        $available_services = ! empty( $accom_meta['extra_services'] ) && is_array( $accom_meta['extra_services'] )
            ? $accom_meta['extra_services']
            : [];

        if ( empty( $available_services ) ) {
            // Fallback: any service tagged for this accommodation.
            $service_query = get_posts( [
                'post_type'      => 'eshb_service',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            ] );
            $available_services = $service_query;
        }

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
            ];
        }

        // Preselected services from the reservation.
        $selected = [];
        if ( ! empty( $reservation['extra_services'] ) && is_array( $reservation['extra_services'] ) ) {
            foreach ( $reservation['extra_services'] as $sel ) {
                if ( ! empty( $sel['id'] ) ) {
                    $selected[ (int) $sel['id'] ] = (int) ( $sel['quantity'] ?? 1 );
                }
            }
        }

        $check_in_format  = get_option( 'date_format' );

        return [
            'accomodation_id'     => $accomodation_id,
            'accomodation_title'  => get_the_title( $accomodation_id ),
            'start_date'          => $reservation['start_date'] ?? '',
            'end_date'            => $reservation['end_date'] ?? '',
            'start_date_label'    => $reservation['start_date'] ? date_i18n( $check_in_format, strtotime( $reservation['start_date'] ) ) : '',
            'end_date_label'      => $reservation['end_date'] ? date_i18n( $check_in_format, strtotime( $reservation['end_date'] ) ) : '',
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
        $reservation = $this->reservation_from_post();

        $pricing = ESHB_Native_Pricing::calculate( $reservation, $coupon, $customer_email );
        if ( ! empty( $coupon ) && empty( $pricing['couponValid'] ) ) {
            wp_send_json_error( [ 'message' => $pricing['couponMessage'] ?: __( 'Invalid coupon.', 'easy-hotel' ), 'pricing' => $pricing ] );
        }

        wp_send_json_success( [ 'pricing' => $pricing ] );
    }

    public function ajax_create_payment() {
        $this->verify_native_nonce();

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $gateway_id = isset( $_POST['gateway'] ) ? sanitize_key( wp_unslash( $_POST['gateway'] ) ) : '';
        $coupon     = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        $customer   = $this->customer_from_post();
        $reservation = $this->reservation_from_post();

        $validation = $this->validate_customer( $customer );
        if ( is_wp_error( $validation ) ) {
            wp_send_json_error( [ 'message' => $validation->get_error_message() ] );
        }

        $gateway = ESHB_Native_Gateway_Manager::instance()->get_gateway( $gateway_id );
        if ( ! $gateway || ! $gateway->is_enabled() ) {
            wp_send_json_error( [ 'message' => __( 'Selected payment method is not available.', 'easy-hotel' ) ] );
        }

        $pricing = ESHB_Native_Pricing::calculate( $reservation, $coupon, $customer['email'] ?? '' );

        // Block payment creation if the coupon was rejected by the
        // per-user / global limit checks; otherwise the discount would
        // silently zero out at the strict re-validation step below.
        if ( ! empty( $coupon ) && empty( $pricing['couponValid'] ) ) {
            wp_send_json_error( [ 'message' => $pricing['couponMessage'] ?: __( 'Coupon is no longer valid.', 'easy-hotel' ) ] );
        }

        $result = $gateway->create_payment( $reservation, $customer, $pricing );

        if ( empty( $result['success'] ) ) {
            wp_send_json_error( [ 'message' => $result['message'] ?? __( 'Could not initiate payment.', 'easy-hotel' ) ] );
        }

        wp_send_json_success( $result['data'] ?? [] );
    }

    public function ajax_complete_checkout() {
        $this->verify_native_nonce();

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $gateway_id = isset( $_POST['gateway'] ) ? sanitize_key( wp_unslash( $_POST['gateway'] ) ) : '';
        $coupon     = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        $customer   = $this->customer_from_post();
        $reservation = $this->reservation_from_post();

        $validation = $this->validate_customer( $customer );
        if ( is_wp_error( $validation ) ) {
            wp_send_json_error( [ 'message' => $validation->get_error_message() ] );
        }

        $gateway = ESHB_Native_Gateway_Manager::instance()->get_gateway( $gateway_id );
        if ( ! $gateway || ! $gateway->is_enabled() ) {
            wp_send_json_error( [ 'message' => __( 'Selected payment method is not available.', 'easy-hotel' ) ] );
        }

        // 1. Capture / verify payment. Nonce is verified at the top of
        // this method; the loop sanitizes each value, so the outer raw
        // $_POST access is safe.
        $gateway_params = [];
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( ! empty( $_POST['gatewayParams'] ) && is_array( $_POST['gatewayParams'] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            foreach ( wp_unslash( $_POST['gatewayParams'] ) as $key => $val ) {
                $gateway_params[ sanitize_key( $key ) ] = sanitize_text_field( $val );
            }
        }

        $capture = $gateway->capture_payment( $gateway_params );
        if ( empty( $capture['success'] ) ) {
            wp_send_json_error( [ 'message' => $capture['message'] ?? __( 'Payment could not be confirmed.', 'easy-hotel' ) ] );
        }

        // 2. Re-calculate pricing server-side; never trust client total.
        // Pass the customer email so the coupon's per-user limit is
        // enforced one more time before the discount is applied.
        $pricing  = ESHB_Native_Pricing::calculate( $reservation, $coupon, $customer['email'] ?? '' );

        // 3. Insert booking with status 'on-hold' (per spec)
        $customer['gateway'] = $gateway->get_id();
        $booking_id = ESHB_Native_Booking_Handler::insert_booking( $reservation, $customer, $pricing );

        if ( ! $booking_id ) {
            wp_send_json_error( [ 'message' => __( 'Booking could not be created. Please contact us.', 'easy-hotel' ) ] );
        }

        // 4. Record payment + capture metadata
        $payment_meta = [
            'transaction_id' => $capture['transaction_id'] ?? '',
            'gateway'        => $gateway->get_id(),
            'amount'         => $capture['amount'] ?? ( $pricing['grandTotal'] ?? $pricing['totalPrice'] ?? 0 ),
            'currency'       => $capture['currency'] ?? '',
            'mode'           => $capture['mode'] ?? 'live',
            'fee'            => 0,
        ];
        ESHB_Native_Booking_Handler::record_payment( $booking_id, $payment_meta, $customer );

        // 5. Update booking status on-hold -> completed (per spec). The booking is already paid at this point, so we skip any
        //    status injected by an extension, e.g. 'deposit-payment'
        //    when only a partial deposit was captured).

        $eshb_settings = get_option( 'eshb_settings', [] );
        $new_status = ! empty( $eshb_settings['booking-auto-approval'] ) ? 'completed' : 'processing';

        $completed_status = apply_filters(
            'eshb_native_checkout_completed_status',
            $new_status,
            $booking_id,
            $reservation,
            $pricing
        );
        ESHB_Native_Booking_Handler::update_status( $booking_id, $completed_status );

        // 5b. Record coupon usage if one was applied. Increments the
        // usage count and appends this customer to the used-by log via
        // ESHB_Native_Checkout_Coupon.
        $coupon_id_used = (int) ( $pricing['couponId'] ?? 0 );
        if ( $coupon_id_used > 0 && ! empty( $pricing['couponValid'] ) ) {
            $coupon_obj = new ESHB_Native_Checkout_Coupon( $coupon_id_used );
            $coupon_obj->set_usage_count( (int) $coupon_obj->get_usage_count() + 1 );

            $used_by = $coupon_obj->get_used_by();
            if ( ! is_array( $used_by ) ) {
                $used_by = [];
            }
            $used_by[] = [
                'booking_id' => $booking_id,
                'name'       => trim( ( $customer['first_name'] ?? '' ) . ' ' . ( $customer['last_name'] ?? '' ) ),
                'email'      => $customer['email'] ?? '',
                'code'       => $pricing['couponCode'] ?? '',
                'discount'   => (float) ( $pricing['couponDiscount'] ?? 0 ),
                'used_at'    => current_time( 'mysql' ),
            ];
            $coupon_obj->set_used_by( $used_by );
        }

        // 6. Send emails. A misconfigured SMTP / mail server must not
        // bubble up as a 500 — payment has already been captured and the
        // booking exists, so we swallow failures silently. The booking
        // record itself is the source of truth if an email is missed.
        try {
            ESHB_Native_Email_Handler::send_customer_confirmation( $booking_id, $customer );
        } catch ( \Throwable $e ) {
            // Intentional no-op: see comment above.
            unset( $e );
        }
        try {
            ESHB_Native_Email_Handler::send_admin_notification( $booking_id, $customer );
        } catch ( \Throwable $e ) {
            // Intentional no-op: see comment above.
            unset( $e );
        }

        // 7. Clear the reservation transient — no double-bookings on refresh.
        //    Also release the temporary cart-blocking hold: the dates are
        //    now a confirmed booking, so the short-lived hold is redundant.
        $blocked_accom = (int) ( $reservation['accomodation_id'] ?? 0 );
        if ( $blocked_accom ) {
            ESHB_Booking::instance()->eshb_release_cart_block( $blocked_accom );
        }
        eshb_native_checkout_clear_reservation();

        $redirect = add_query_arg( [ 'booking' => $booking_id ], eshb_native_checkout_url() );

        wp_send_json_success( [
            'booking_id'   => $booking_id,
            'redirect_url' => apply_filters( 'eshb_native_checkout_thankyou_url', $redirect, $booking_id ),
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

        $reservation = eshb_native_checkout_get_reservation();
        if ( is_array( $reservation ) && ! empty( $reservation['accomodation_id'] ) ) {
            ESHB_Booking::instance()->eshb_release_cart_block( (int) $reservation['accomodation_id'] );
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

    private function reservation_from_post() {
        // Nonce verified in the calling ajax_* method.
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $session = eshb_native_checkout_get_reservation();
        if ( ! is_array( $session ) ) {
            wp_send_json_error( [ 'message' => __( 'Your reservation has expired. Please start again.', 'easy-hotel' ) ] );
        }
        // Allow extras update from the page (per spec: client-side adjustments
        // change the price, so we must accept the latest selections).
        if ( ! empty( $_POST['extraServices'] ) ) {
            $raw = sanitize_text_field( wp_unslash( $_POST['extraServices'] ) );
            $decoded = json_decode( $raw, true );
            if ( is_array( $decoded ) ) {
                $services = [];
                foreach ( $decoded as $svc ) {
                    if ( ! is_array( $svc ) || empty( $svc['id'] ) ) continue;
                    $services[] = [
                        'id'       => (int) $svc['id'],
                        'quantity' => max( 0, (int) ( $svc['quantity'] ?? 0 ) ),
                    ];
                }
                $session['extra_services'] = $services;
                eshb_native_checkout_set_reservation( $session );
            }
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        return $session;
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
            'notes'      => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
        ];
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        return $customer;
    }

    private function validate_customer( array $customer ) {
        // City and country are always required. State is conditional —
        // some countries (e.g. Vatican City) have no states in the JSON,
        // so the JS layer disables the field and we mirror that here by
        // skipping the state requirement when it's absent.
        $required = [ 'first_name', 'last_name', 'email', 'phone', 'country', 'city' ];
        foreach ( $required as $key ) {
            if ( empty( $customer[ $key ] ) ) {
                return new WP_Error( 'missing_field', __( 'Please fill in all required fields.', 'easy-hotel' ) );
            }
        }
        if ( ! is_email( $customer['email'] ) ) {
            return new WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'easy-hotel' ) );
        }
        return true;
    }
}
