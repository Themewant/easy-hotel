<?php
/**
 * Native Checkout helper functions.
 *
 * Reservation handoff between the booking form and the checkout page is
 * stored in a server-side transient keyed by a per-visitor token cookie.
 * Cookies hold only the token; the reservation payload itself stays in WP.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'eshb_native_checkout_token_cookie' ) ) {
    function eshb_native_checkout_token_cookie() {
        return 'eshb_native_checkout_token';
    }
}

if ( ! function_exists( 'eshb_native_checkout_get_token' ) ) {
    function eshb_native_checkout_get_token( $create_if_missing = false ) {
        $cookie = eshb_native_checkout_token_cookie();

        if ( ! empty( $_COOKIE[ $cookie ] ) ) {
            $token = sanitize_text_field( wp_unslash( $_COOKIE[ $cookie ] ) );
            if ( preg_match( '/^[a-f0-9]{32,64}$/', $token ) ) {
                return $token;
            }
        }

        if ( ! $create_if_missing ) {
            return '';
        }

        $token = wp_generate_password( 40, false, false );
        $token = md5( $token . wp_salt( 'auth' ) );

        if ( ! headers_sent() ) {
            setcookie( $cookie, $token, time() + HOUR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
        }
        $_COOKIE[ $cookie ] = $token;

        return $token;
    }
}

if ( ! function_exists( 'eshb_native_checkout_transient_key' ) ) {
    function eshb_native_checkout_transient_key( $token ) {
        return 'eshb_native_chk_' . md5( $token );
    }
}

if ( ! function_exists( 'eshb_native_checkout_set_reservation' ) ) {
    function eshb_native_checkout_set_reservation( array $reservation ) {
        $token = eshb_native_checkout_get_token( true );
        if ( empty( $token ) ) return false;

        $reservation['_created'] = time();
        set_transient( eshb_native_checkout_transient_key( $token ), $reservation, HOUR_IN_SECONDS );
        return $token;
    }
}

if ( ! function_exists( 'eshb_native_checkout_get_reservation' ) ) {
    function eshb_native_checkout_get_reservation() {
        $token = eshb_native_checkout_get_token( false );
        if ( empty( $token ) ) return null;

        $data = get_transient( eshb_native_checkout_transient_key( $token ) );
        return is_array( $data ) ? $data : null;
    }
}

if ( ! function_exists( 'eshb_native_checkout_clear_reservation' ) ) {
    function eshb_native_checkout_clear_reservation() {
        $token = eshb_native_checkout_get_token( false );
        if ( empty( $token ) ) return;
        delete_transient( eshb_native_checkout_transient_key( $token ) );
    }
}

if ( ! function_exists( 'eshb_native_checkout_page_id' ) ) {
    function eshb_native_checkout_page_id() {
        $page_id = (int) get_option( 'eshb_native_checkout_page_id', 0 );

        if ( $page_id && get_post_status( $page_id ) === 'publish' ) {
            $content = get_post_field( 'post_content', $page_id );
            if ( has_shortcode( (string) $content, 'eshb_native_checkout' ) ) {
                return $page_id;
            }
        }

        // Scan published pages for the shortcode. The `s` query parameter
        // does a fuzzy fulltext match and isn't reliable for `[shortcodes]`,
        // so we look at post_content directly via has_shortcode().
        $candidates = get_posts( [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            'no_found_rows'  => true,
        ] );

        foreach ( $candidates as $candidate ) {
            if ( has_shortcode( (string) $candidate->post_content, 'eshb_native_checkout' ) ) {
                update_option( 'eshb_native_checkout_page_id', (int) $candidate->ID );
                return (int) $candidate->ID;
            }
        }

        return 0;
    }
}

if ( ! function_exists( 'eshb_native_checkout_ensure_page' ) ) {
    /**
     * Find the native-checkout page or create it if missing.
     * Used as a runtime safety net so existing installs that didn't go
     * through the activation hook still get a working checkout URL.
     */
    function eshb_native_checkout_ensure_page() {
        $page_id = eshb_native_checkout_page_id();
        if ( $page_id ) return $page_id;

        $page_id = wp_insert_post( [
            'post_title'   => __( 'Easy Hotel Checkout', 'easy-hotel' ),
            'post_name'    => 'easy-hotel-checkout',
            'post_content' => '[eshb_native_checkout]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ] );

        if ( ! is_wp_error( $page_id ) && $page_id ) {
            update_option( 'eshb_native_checkout_page_id', (int) $page_id );
            return (int) $page_id;
        }

        return 0;
    }
}

if ( ! function_exists( 'eshb_native_checkout_url' ) ) {
    function eshb_native_checkout_url() {
        $page_id = eshb_native_checkout_ensure_page();
        if ( $page_id ) {
            return get_permalink( $page_id );
        }
        // Avoid colliding with WooCommerce's /checkout/ as a last resort —
        // surface the home URL so the user notices instead of landing on
        // an unrelated page.
        return home_url( '/' );
    }
}

if ( ! function_exists( 'eshb_native_checkout_is_enabled' ) ) {
    function eshb_native_checkout_is_enabled() {
        $settings = get_option( 'eshb_settings', [] );
        return ( isset( $settings['booking-type'] ) && $settings['booking-type'] === 'native_checkout' );
    }
}
