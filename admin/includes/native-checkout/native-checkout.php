<?php
/**
 * Easy Hotel - Native Checkout bootstrap.
 *
 * Pulls together the pricing, booking, email and gateway classes and
 * instantiates the public-facing checkout controller. Included from the
 * main plugin loader (see admin/includes/admin-init.php).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'ESHB_NATIVE_CHECKOUT_PATH' ) ) {
    define( 'ESHB_NATIVE_CHECKOUT_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'ESHB_NATIVE_CHECKOUT_URL' ) ) {
    define( 'ESHB_NATIVE_CHECKOUT_URL', plugin_dir_url( __FILE__ ) );
}

require_once ESHB_NATIVE_CHECKOUT_PATH . 'includes/helpers.php';
require_once ESHB_NATIVE_CHECKOUT_PATH . 'includes/class-coupon.php';
require_once ESHB_NATIVE_CHECKOUT_PATH . 'includes/class-pricing.php';
require_once ESHB_NATIVE_CHECKOUT_PATH . 'includes/class-booking-handler.php';
require_once ESHB_NATIVE_CHECKOUT_PATH . 'includes/class-email-handler.php';

require_once ESHB_NATIVE_CHECKOUT_PATH . 'gateways/abstract-gateway.php';
require_once ESHB_NATIVE_CHECKOUT_PATH . 'gateways/class-cod-gateway.php';
require_once ESHB_NATIVE_CHECKOUT_PATH . 'gateways/class-paypal-gateway.php';
require_once ESHB_NATIVE_CHECKOUT_PATH . 'gateways/class-gateway-manager.php';

require_once ESHB_NATIVE_CHECKOUT_PATH . 'includes/class-checkout.php';

// Customer account area + booking cancellation system.
require_once ESHB_NATIVE_CHECKOUT_PATH . 'account/class-customer.php';
require_once ESHB_NATIVE_CHECKOUT_PATH . 'account/class-cancellation-email.php';
require_once ESHB_NATIVE_CHECKOUT_PATH . 'account/class-bookings.php';
require_once ESHB_NATIVE_CHECKOUT_PATH . 'account/class-account-ajax.php';
require_once ESHB_NATIVE_CHECKOUT_PATH . 'account/class-account-admin.php';
require_once ESHB_NATIVE_CHECKOUT_PATH . 'account/class-account.php';

add_action( 'plugins_loaded', function () {
    ESHB_Native_Checkout::instance();
    ESHB_Native_Account::instance();
}, 15 );

/* -----------------------------------------------------------------------
 * Abandoned-reservation cleanup
 *
 * Reservations live in wp_options keyed by the per-visitor token. A
 * customer who lands on the checkout page and then closes the tab or
 * cancels payment never triggers the on-success delete path, so we
 * need a janitor to keep wp_options from accumulating stale rows.
 * -------------------------------------------------------------------- */

add_action( 'eshb_native_checkout_cleanup_cron', 'eshb_native_checkout_cleanup_stale_reservations' );

add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'eshb_native_checkout_cleanup_cron' ) ) {
        wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'eshb_native_checkout_cleanup_cron' );
    }
} );

register_deactivation_hook( ESHB_PL_ROOT, 'eshb_native_checkout_unschedule_cleanup' );
if ( ! function_exists( 'eshb_native_checkout_unschedule_cleanup' ) ) {
    function eshb_native_checkout_unschedule_cleanup() {
        $timestamp = wp_next_scheduled( 'eshb_native_checkout_cleanup_cron' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'eshb_native_checkout_cleanup_cron' );
        }
    }
}

/**
 * Auto-create the checkout page on plugin activation when Native Checkout
 * is in use, so the shortcode is always reachable from a known URL.
 */
register_activation_hook( ESHB_PL_ROOT, 'eshb_native_checkout_create_page' );
if ( ! function_exists( 'eshb_native_checkout_create_page' ) ) {
    function eshb_native_checkout_create_page() {
        $existing = eshb_native_checkout_page_id();
        if ( $existing ) return;

        $page_id = wp_insert_post( [
            'post_title'   => __( 'Easy Hotel Checkout', 'easy-hotel' ),
            'post_name'    => 'eshb-checkout',
            'post_content' => '[eshb_native_checkout]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ] );

        if ( ! is_wp_error( $page_id ) && $page_id ) {
            update_option( 'eshb_native_checkout_page_id', (int) $page_id );
        }
    }
}

/**
 * Auto-create the customer account page on plugin activation so the
 * [eshb_account] shortcode is reachable from a known URL.
 */
register_activation_hook( ESHB_PL_ROOT, 'eshb_native_account_create_page' );
if ( ! function_exists( 'eshb_native_account_create_page' ) ) {
    function eshb_native_account_create_page() {
        if ( class_exists( 'ESHB_Native_Account' ) ) {
            $account = ESHB_Native_Account::instance();
            $account->customer->register_role();
            $account->get_account_page_id( true );
        }
    }
}
