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

if ( ! function_exists( 'eshb_native_checkout_request_param' ) ) {
    /**
     * Name of the request parameter used to carry the reservation token
     * through URL redirects and AJAX requests. Used as the primary
     * channel; cookies are a best-effort fallback because some hosts
     * strip Set-Cookie from AJAX responses.
     */
    function eshb_native_checkout_request_param() {
        return 'eshb_chk';
    }
}

if ( ! function_exists( 'eshb_native_checkout_valid_token' ) ) {
    function eshb_native_checkout_valid_token( $token ) {
        return is_string( $token ) && preg_match( '/^[a-f0-9]{32,64}$/', $token ) === 1;
    }
}

if ( ! function_exists( 'eshb_native_checkout_token_from_request' ) ) {
    /**
     * Best-effort lookup of the visitor's reservation token. Checks the
     * request payload first ($_POST then $_GET) — that channel is
     * controlled by us and immune to cookie stripping. Falls back to
     * the cookie when the request didn't carry one.
     */
    function eshb_native_checkout_token_from_request() {
        $param = eshb_native_checkout_request_param();

        // phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
        if ( ! empty( $_POST[ $param ] ) ) {
            $token = sanitize_text_field( wp_unslash( $_POST[ $param ] ) );
            if ( eshb_native_checkout_valid_token( $token ) ) {
                return $token;
            }
        }
        if ( ! empty( $_GET[ $param ] ) ) {
            $token = sanitize_text_field( wp_unslash( $_GET[ $param ] ) );
            if ( eshb_native_checkout_valid_token( $token ) ) {
                return $token;
            }
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended

        $cookie = eshb_native_checkout_token_cookie();
        if ( ! empty( $_COOKIE[ $cookie ] ) ) {
            $token = sanitize_text_field( wp_unslash( $_COOKIE[ $cookie ] ) );
            if ( eshb_native_checkout_valid_token( $token ) ) {
                return $token;
            }
        }

        return '';
    }
}

if ( ! function_exists( 'eshb_native_checkout_get_token' ) ) {
    function eshb_native_checkout_get_token( $create_if_missing = false ) {
        $existing = eshb_native_checkout_token_from_request();
        if ( $existing !== '' ) {
            return $existing;
        }

        if ( ! $create_if_missing ) {
            return '';
        }

        $token = wp_generate_password( 40, false, false );
        $token = md5( $token . wp_salt( 'auth' ) );

        if ( ! headers_sent() ) {
            // Try to set the cookie as a convenience for the common
            // case — but the real source of truth is the URL/AJAX
            // token, so a stripped Set-Cookie response does not break
            // the flow.
            setcookie( eshb_native_checkout_token_cookie(), $token, [
                'expires'  => time() + HOUR_IN_SECONDS,
                'path'     => COOKIEPATH ? COOKIEPATH : '/',
                'domain'   => COOKIE_DOMAIN,
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ] );
        }
        $_COOKIE[ eshb_native_checkout_token_cookie() ] = $token;

        return $token;
    }
}

if ( ! function_exists( 'eshb_native_checkout_storage_key' ) ) {
    /**
     * wp_options key for the per-token reservation payload. We use
     * update_option / get_option instead of set_transient because some
     * managed-WordPress hosts route transients through a non-persistent
     * object cache (or a Redis backend on a different node than the
     * one that handled the AJAX request), so set_transient writes
     * effectively vanish before the next request sees them.
     * update_option always hits the wp_options table, which survives.
     */
    function eshb_native_checkout_storage_key( $token ) {
        return '_eshb_native_chk_' . md5( $token );
    }
}

/* -----------------------------------------------------------------------
 * Cart storage
 *
 * The native checkout supports multiple accommodations in a single
 * checkout. The stored payload is a "cart":
 *
 *   [
 *     '_created' => <unix ts>,
 *     'items'    => [
 *       '<item_key>' => [ accomodation_id, start_date, end_date, ... , extra_services ],
 *       ...
 *     ],
 *   ]
 *
 * Legacy single-reservation payloads (a flat array with accomodation_id at
 * the top level) are transparently migrated into the items shape on read,
 * so older links / in-flight sessions keep working.
 * -------------------------------------------------------------------- */

if ( ! function_exists( 'eshb_native_checkout_normalize_cart' ) ) {
    /**
     * Coerce a raw stored payload into the canonical cart shape.
     * Returns null when the payload is empty/invalid.
     */
    function eshb_native_checkout_normalize_cart( $data ) {
        if ( ! is_array( $data ) ) return null;

        // Already a cart.
        if ( isset( $data['items'] ) && is_array( $data['items'] ) ) {
            return $data;
        }

        // Legacy single reservation → wrap as a one-item cart.
        if ( ! empty( $data['accomodation_id'] ) ) {
            $created = isset( $data['_created'] ) ? (int) $data['_created'] : time();
            unset( $data['_created'] );
            return [
                '_created' => $created,
                'items'    => [ 'itm_legacy' => $data ],
            ];
        }

        return null;
    }
}

if ( ! function_exists( 'eshb_native_checkout_get_cart' ) ) {
    /**
     * Read the current visitor's cart (or null when empty/expired).
     */
    function eshb_native_checkout_get_cart() {
        $token = eshb_native_checkout_get_token( false );
        if ( empty( $token ) ) return null;

        $key  = eshb_native_checkout_storage_key( $token );
        $data = get_option( $key, null );

        $cart = eshb_native_checkout_normalize_cart( $data );
        if ( null === $cart ) return null;

        // Manual expiry: options don't auto-expire, so we drop carts older
        // than an hour and treat them as absent.
        $created = isset( $cart['_created'] ) ? (int) $cart['_created'] : 0;
        if ( $created > 0 && ( time() - $created ) > HOUR_IN_SECONDS ) {
            delete_option( $key );
            return null;
        }

        if ( empty( $cart['items'] ) || ! is_array( $cart['items'] ) ) {
            return null;
        }

        return $cart;
    }
}

if ( ! function_exists( 'eshb_native_checkout_get_items' ) ) {
    /**
     * Return the cart items keyed by item key (empty array when none).
     */
    function eshb_native_checkout_get_items() {
        $cart = eshb_native_checkout_get_cart();
        return ( $cart && ! empty( $cart['items'] ) ) ? $cart['items'] : [];
    }
}

if ( ! function_exists( 'eshb_native_checkout_save_cart' ) ) {
    /**
     * Persist a cart array. Returns the token, or false when no token
     * could be created.
     */
    function eshb_native_checkout_save_cart( array $cart ) {
        $token = eshb_native_checkout_get_token( true );
        if ( empty( $token ) ) return false;

        if ( empty( $cart['_created'] ) ) {
            $cart['_created'] = time();
        }
        // autoload=no so we don't bloat wp_options bootstrap.
        update_option( eshb_native_checkout_storage_key( $token ), $cart, false );
        return $token;
    }
}

if ( ! function_exists( 'eshb_native_checkout_add_item' ) ) {
    /**
     * Append an accommodation to the cart. Returns
     * [ 'token' => string, 'item_key' => string ] or false on failure.
     */
    function eshb_native_checkout_add_item( array $item ) {
        $cart = eshb_native_checkout_get_cart();
        if ( ! is_array( $cart ) ) {
            $cart = [ '_created' => time(), 'items' => [] ];
        }

        $item_key = uniqid( 'itm_', true );
        // Keep keys filesystem/JSON-safe (uniqid with more_entropy adds a dot).
        $item_key = str_replace( '.', '', $item_key );

        $cart['items'][ $item_key ] = $item;

        $token = eshb_native_checkout_save_cart( $cart );
        if ( ! $token ) return false;

        return [ 'token' => $token, 'item_key' => $item_key ];
    }
}

if ( ! function_exists( 'eshb_native_checkout_update_item' ) ) {
    /**
     * Replace a single item's payload. Returns true when the item existed.
     */
    function eshb_native_checkout_update_item( $item_key, array $item ) {
        $cart = eshb_native_checkout_get_cart();
        if ( ! is_array( $cart ) || ! isset( $cart['items'][ $item_key ] ) ) {
            return false;
        }
        $cart['items'][ $item_key ] = $item;
        eshb_native_checkout_save_cart( $cart );
        return true;
    }
}

if ( ! function_exists( 'eshb_native_checkout_remove_item' ) ) {
    /**
     * Drop one item from the cart. When the cart becomes empty the whole
     * option is deleted. Returns the removed item (for hold release) or null.
     */
    function eshb_native_checkout_remove_item( $item_key ) {
        $cart = eshb_native_checkout_get_cart();
        if ( ! is_array( $cart ) || ! isset( $cart['items'][ $item_key ] ) ) {
            return null;
        }

        $removed = $cart['items'][ $item_key ];
        unset( $cart['items'][ $item_key ] );

        if ( empty( $cart['items'] ) ) {
            eshb_native_checkout_clear_reservation();
        } else {
            eshb_native_checkout_save_cart( $cart );
        }

        return $removed;
    }
}

if ( ! function_exists( 'eshb_native_checkout_set_reservation' ) ) {
    /**
     * Backward-compatible single-reservation setter: replaces the entire
     * cart with one item. New code should use eshb_native_checkout_add_item().
     */
    function eshb_native_checkout_set_reservation( array $reservation ) {
        unset( $reservation['_created'] );
        return eshb_native_checkout_save_cart( [
            '_created' => time(),
            'items'    => [ uniqid( 'itm_', false ) => $reservation ],
        ] );
    }
}

if ( ! function_exists( 'eshb_native_checkout_get_reservation' ) ) {
    /**
     * Backward-compatible single-reservation getter: returns the FIRST
     * cart item as a flat array (the shape legacy callers and add-ons
     * expect). New code should use eshb_native_checkout_get_items().
     */
    function eshb_native_checkout_get_reservation() {
        $items = eshb_native_checkout_get_items();
        if ( empty( $items ) ) return null;
        return reset( $items );
    }
}

if ( ! function_exists( 'eshb_native_checkout_clear_reservation' ) ) {
    function eshb_native_checkout_clear_reservation() {
        $token = eshb_native_checkout_get_token( false );
        if ( empty( $token ) ) return;
        delete_option( eshb_native_checkout_storage_key( $token ) );
    }
}

if ( ! function_exists( 'eshb_native_checkout_cleanup_stale_reservations' ) ) {
    /**
     * Sweep abandoned reservations from wp_options. Triggered by an
     * hourly WP-Cron event (see native-checkout.php) so customers who
     * land on the checkout page and never complete payment don't leave
     * rows behind forever.
     *
     * @return int Number of rows deleted.
     */
    function eshb_native_checkout_cleanup_stale_reservations() {
        global $wpdb;
        // Direct query is unavoidable: there is no WordPress API to look up
        // options by name pattern. Caching is intentionally skipped — this
        // is a one-shot hourly cron sweep, not a hot read path, and it must
        // see the live table state to delete stale rows.
        $like = $wpdb->esc_like( '_eshb_native_chk_' ) . '%';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_id, option_name, option_value
                 FROM {$wpdb->options}
                 WHERE option_name LIKE %s
                 LIMIT 500",
                $like
            )
        );
        if ( empty( $rows ) ) return 0;

        $deleted = 0;
        $cutoff  = time() - HOUR_IN_SECONDS;

        foreach ( $rows as $row ) {
            $data    = maybe_unserialize( $row->option_value );
            $created = is_array( $data ) && isset( $data['_created'] ) ? (int) $data['_created'] : 0;

            // Delete if older than the cutoff, malformed, or missing
            // the timestamp altogether (legacy / corrupted entries).
            if ( $created === 0 || $created < $cutoff ) {
                delete_option( $row->option_name );
                $deleted++;
            }
        }
        return $deleted;
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
            'post_name'    => 'eshb-checkout',
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

if ( ! function_exists( 'eshb_native_checkout_get_setting' ) ) {
    /**
     * Read a native-checkout payment-gateway setting.
     *
     * The payment-gateway options are grouped under the 'payment-gateways'
     * tabbed field in the settings UI, so the framework stores them nested
     * as $settings['payment-gateways'][ $key ]. Older installs (and the
     * tax-rate field before it was grouped) stored them flat at the top
     * level, so we check the nested group first and fall back to flat —
     * keeping both layouts working.
     *
     * @param string $key     Setting id (e.g. 'cod-title').
     * @param mixed  $default Returned when the key is absent everywhere.
     * @return mixed
     */
    function eshb_native_checkout_get_setting( $key, $default = '' ) {
        $settings = get_option( 'eshb_settings', [] );

        if ( isset( $settings['payment-gateways'] ) && is_array( $settings['payment-gateways'] )
            && array_key_exists( $key, $settings['payment-gateways'] ) ) {
            return $settings['payment-gateways'][ $key ];
        }

        if ( array_key_exists( $key, (array) $settings ) ) {
            return $settings[ $key ];
        }

        return $default;
    }
}
