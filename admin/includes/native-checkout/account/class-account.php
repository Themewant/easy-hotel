<?php
/**
 * Customer account controller for the Native Checkout.
 *
 * Registers the [eshb_account] shortcode and its auto-created page,
 * enqueues the account assets, and renders either a login form (logged
 * out) or the tabbed dashboard / bookings / account screens (logged in).
 *
 * @package EasyHotel\NativeCheckout\Account
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_Account {

    private static $instance = null;

    /** @var ESHB_Native_Account_Customer */
    public $customer;

    /** @var ESHB_Native_Account_Bookings */
    public $bookings;

    const PAGE_OPTION = 'eshb_native_account_page_id';
    const TABS        = [ 'dashboard', 'bookings', 'account' ];

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->customer = new ESHB_Native_Account_Customer();
        $this->bookings = new ESHB_Native_Account_Bookings( $this->customer );

        add_action( 'init', [ $this, 'register_shortcode' ] );
        add_action( 'init', [ $this, 'ensure_account_page' ], 20 );
        // Belt-and-suspenders: guarantee the page exists after any admin load.
        add_action( 'admin_init', [ $this, 'ensure_account_page' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 1000 );

        // After a customer sets/resets their password (e.g. via the
        // auto-created-account email), send them to the account page to log
        // in instead of the default wp-login confirmation screen.
        add_action( 'after_password_reset', [ $this, 'redirect_after_password_reset' ], 10, 2 );

        // AJAX + admin cancellation support live in their own classes.
        new ESHB_Native_Account_Ajax( $this->customer, $this->bookings );
        new ESHB_Native_Account_Admin( $this->bookings );
    }

    public function register_shortcode() {
        add_shortcode( 'eshb_account', [ $this, 'render_shortcode' ] );
    }

    /* -----------------------------------------------------------------
     * Page management
     * -------------------------------------------------------------- */

    public function ensure_account_page() {
        if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
            return;
        }
        if ( ! eshb_native_checkout_is_enabled() ) {
            return;
        }

        // An admin-selected page (Settings → Account Page) takes over as the
        // account endpoint. Replace its content with the [eshb_account]
        // shortcode so it renders the account UI, then we're done — no
        // auto-created page is needed.
        $selected = $this->get_selected_account_page();
        if ( $selected ) {
            update_option( self::PAGE_OPTION, $selected );
            $content = (string) get_post_field( 'post_content', $selected );
            if ( ! has_shortcode( $content, 'eshb_account' ) ) {
                wp_update_post( [ 'ID' => $selected, 'post_content' => '[eshb_account]' ] );
            }
            return;
        }

        // Migrate a previously auto-created page that still uses the old
        // [eshb_native_account] shortcode to the new [eshb_account] tag, so
        // we update the existing page instead of creating a duplicate.
        $stored = (int) get_option( self::PAGE_OPTION, 0 );
        if ( $stored && get_post_status( $stored ) === 'publish' ) {
            $content = (string) get_post_field( 'post_content', $stored );
            if ( false !== strpos( $content, '[eshb_native_account]' ) ) {
                wp_update_post( [
                    'ID'           => $stored,
                    'post_content' => str_replace( '[eshb_native_account]', '[eshb_account]', $content ),
                ] );
            }
        }

        $page_id = $this->get_account_page_id( true );

        // Migrate a previously auto-created page to the current title.
        if ( $page_id && 'My Account' === get_post_field( 'post_title', $page_id ) ) {
            wp_update_post( [ 'ID' => $page_id, 'post_title' => $this->account_page_title() ] );
        }
    }

    /**
     * Title used for the auto-created account page (filterable).
     */
    public function account_page_title() {
        return apply_filters( 'eshb_native_account_page_title', __( 'Easy Hotel Account', 'easy-hotel' ) );
    }

    /**
     * The admin-selected account page id from settings (0 when "Default").
     */
    public function get_selected_account_page() {
        $settings = get_option( 'eshb_settings', [] );
        $page_id  = isset( $settings['account-page'] ) ? (int) $settings['account-page'] : 0;
        return ( $page_id && get_post_status( $page_id ) === 'publish' ) ? $page_id : 0;
    }

    /**
     * Return the account page id, optionally creating the page if missing.
     *
     * An admin-selected page (Settings → Account Page) always wins; it is
     * kept rendering the account UI by ensure_account_page(), which injects
     * the [eshb_account] shortcode.
     */
    public function get_account_page_id( $create_if_missing = false ) {
        $selected = $this->get_selected_account_page();
        if ( $selected ) {
            return $selected;
        }

        $page_id = (int) get_option( self::PAGE_OPTION, 0 );
        if ( $page_id && get_post_status( $page_id ) === 'publish'
            && has_shortcode( (string) get_post_field( 'post_content', $page_id ), 'eshb_account' ) ) {
            return $page_id;
        }

        // Scan existing pages for the shortcode before creating a new one.
        $candidates = get_posts( [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            'no_found_rows'  => true,
        ] );
        foreach ( $candidates as $candidate ) {
            if ( has_shortcode( (string) $candidate->post_content, 'eshb_account' ) ) {
                update_option( self::PAGE_OPTION, (int) $candidate->ID );
                return (int) $candidate->ID;
            }
        }

        if ( ! $create_if_missing ) {
            return 0;
        }

        $page_id = wp_insert_post( [
            'post_title'   => $this->account_page_title(),
            'post_name'    => 'eshb-account',
            'post_content' => '[eshb_account]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ] );
        if ( ! is_wp_error( $page_id ) && $page_id ) {
            update_option( self::PAGE_OPTION, (int) $page_id );
            return (int) $page_id;
        }
        return 0;
    }

    /**
     * Permalink of the account page (home as a last resort).
     */
    public function get_account_url() {
        $page_id = $this->get_account_page_id( true );
        return $page_id ? get_permalink( $page_id ) : home_url( '/' );
    }

    /**
     * URL for a given account tab.
     */
    public function get_tab_url( $tab ) {
        return add_query_arg( 'tab', $tab, $this->get_account_url() );
    }

    /**
     * Whether the current request is the account page.
     */
    public function is_account_page() {
        if ( is_admin() || ! is_singular( 'page' ) ) {
            return false;
        }
        global $post;
        return $post && has_shortcode( (string) $post->post_content, 'eshb_account' );
    }

    /* -----------------------------------------------------------------
     * Assets
     * -------------------------------------------------------------- */

    public function enqueue_assets() {
        if ( ! $this->is_account_page() ) {
            return;
        }
        // Styles live in public/assets/css/_native-checkout.scss which is
        // bundled into the site-wide public.css — nothing to enqueue here.
        $base_url  = ESHB_PL_URL . 'admin/includes/native-checkout/account/assets/';
        $base_path = ESHB_PL_PATH . 'admin/includes/native-checkout/account/assets/';

        $js_path = $base_path . 'js/account.js';
        wp_enqueue_script(
            'eshb-native-account',
            $base_url . 'js/account.js',
            [ 'jquery' ],
            file_exists( $js_path ) ? filemtime( $js_path ) : ESHB_VERSION,
            true
        );

        wp_localize_script( 'eshb-native-account', 'eshbNativeAccount', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'eshb_native_account' ),
            'i18n'    => [
                'cancelConfirm'   => __( 'Are you sure you want to cancel this booking?', 'easy-hotel' ),
                'processing'      => __( 'Processing…', 'easy-hotel' ),
                'genericError'    => __( 'Something went wrong. Please try again.', 'easy-hotel' ),
                'passwordMismatch'=> __( 'New password and confirmation do not match.', 'easy-hotel' ),
                'loading'         => __( 'Loading…', 'easy-hotel' ),
            ],
        ] );
    }

    /* -----------------------------------------------------------------
     * Rendering
     * -------------------------------------------------------------- */

    public function render_shortcode() {
        nocache_headers();
        if ( ! defined( 'DONOTCACHEPAGE' ) ) {
            define( 'DONOTCACHEPAGE', true );
        }

        ob_start();

        if ( ! is_user_logged_in() ) {
            $this->render_template( 'login.php' );
            return ob_get_clean();
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'dashboard';
        if ( ! in_array( $tab, self::TABS, true ) ) {
            $tab = 'dashboard';
        }

        $this->render_template( 'account-page.php', [ 'active_tab' => $tab ] );
        return ob_get_clean();
    }

    /**
     * Include an account template with the controller available as $account
     * plus any extra variables.
     */
    public function render_template( $template, array $vars = [] ) {
        $file = ESHB_PL_PATH . 'admin/includes/native-checkout/account/templates/' . $template;
        if ( ! file_exists( $file ) ) {
            return;
        }
        $account  = $this; // available inside the template
        $bookings = $this->bookings;
        $customer = $this->customer;
        if ( $vars ) {
            extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        }
        include $file;
    }

    /**
     * Send a customer to the account page after they set/reset their
     * password, rather than the default wp-login confirmation screen.
     *
     * Scoped to customer-type users (no `edit_posts` capability) so admin
     * / editor password resets keep WordPress' standard behaviour. The
     * `?password-set=1` flag lets the login template show a confirmation.
     *
     * @param WP_User $user
     * @param string  $new_pass
     */
    public function redirect_after_password_reset( $user, $new_pass ) {
        if ( ! $user instanceof WP_User || headers_sent() ) {
            return;
        }
        if ( ! eshb_native_checkout_is_enabled() ) {
            return;
        }
        // Leave staff (admins/editors/shop managers) on the default flow.
        if ( user_can( $user, 'edit_posts' ) ) {
            return;
        }

        /**
         * Allow disabling the post-password-reset redirect to the account page.
         *
         * @param bool    $do_redirect
         * @param WP_User $user
         */
        if ( ! apply_filters( 'eshb_native_account_redirect_after_password_reset', true, $user ) ) {
            return;
        }

        wp_safe_redirect( add_query_arg( 'password-set', '1', $this->get_account_url() ) );
        exit;
    }

    /**
     * Logout URL with a configurable post-logout redirect.
     */
    public function get_logout_url() {
        /**
         * Where to send the customer after logging out of the account area.
         *
         * @param string $redirect_to Defaults to the site home.
         */
        $redirect = apply_filters( 'eshb_native_account_logout_redirect', home_url( '/' ) );
        return wp_logout_url( $redirect );
    }
}
