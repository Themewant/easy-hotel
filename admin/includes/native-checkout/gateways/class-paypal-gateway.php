<?php
/**
 * PayPal gateway for the native checkout.
 *
 * Uses the PayPal Orders v2 REST API:
 *   1. JS Smart Buttons call our `create_payment` endpoint, which
 *      creates a PayPal order server-side and returns its id.
 *   2. After the buyer approves in the PayPal popup, the JS layer
 *      calls our `capture_payment` endpoint, which captures the
 *      order server-side. We trust the captured status returned by
 *      PayPal — not anything the client claims.
 *
 * Credentials live in the existing eshb_settings option:
 *   - paypal-client-id
 *   - paypal-client-secret
 *   - paypal-mode  ('sandbox' or 'live')
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_PayPal_Gateway extends ESHB_Native_Abstract_Gateway {

    protected $id          = 'paypal';
    protected $title       = 'PayPal';
    protected $description = '';

    public function __construct() {
        $this->title       = __( 'PayPal', 'easy-hotel' );
        $this->description = __( 'Pay securely using your PayPal account or a credit card.', 'easy-hotel' );
    }

    private function get_settings() {
        $settings = get_option( 'eshb_settings', [] );
        return [
            'client_id'     => trim( (string) ( $settings['paypal-client-id'] ?? '' ) ),
            'client_secret' => trim( (string) ( $settings['paypal-client-secret'] ?? '' ) ),
            'mode'          => ( ( $settings['paypal-mode'] ?? 'sandbox' ) === 'live' ) ? 'live' : 'sandbox',
        ];
    }

    private function api_base() {
        $s = $this->get_settings();
        return $s['mode'] === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function is_enabled() {
        $s = $this->get_settings();
        return ! empty( $s['client_id'] ) && ! empty( $s['client_secret'] );
    }

    public function get_frontend_data() {
        $s = $this->get_settings();
        return array_merge( parent::get_frontend_data(), [
            'clientId' => $s['client_id'],
            'mode'     => $s['mode'],
            'currency' => $this->get_currency_code(),
        ] );
    }

    private function get_currency_code() {
        // PayPal needs an ISO-4217 currency code. Prefer WooCommerce's
        // configured currency (since the plugin already integrates with
        // it for currency symbol formatting), then fall back to a guess
        // from the symbol setting, then USD.
        if ( function_exists( 'get_woocommerce_currency' ) ) {
            $code = get_woocommerce_currency();
            if ( ! empty( $code ) ) return $code;
        }

        $settings = get_option( 'eshb_settings', [] );
        $symbol   = isset( $settings['currency_symbol'] ) ? trim( (string) $settings['currency_symbol'] ) : '';
        $map = [
            '$' => 'USD',
            '€' => 'EUR',
            '£' => 'GBP',
            '¥' => 'JPY',
            'A$' => 'AUD',
            'C$' => 'CAD',
            '₹' => 'INR',
            '₺' => 'TRY',
            '₽' => 'RUB',
        ];
        if ( ! empty( $symbol ) && isset( $map[ $symbol ] ) ) {
            return $map[ $symbol ];
        }

        return apply_filters( 'eshb_native_paypal_currency', 'USD' );
    }

    /**
     * Fetch an OAuth2 token from PayPal. Cached for 8 minutes since
     * PayPal tokens last ~9 minutes.
     */
    private function get_access_token() {
        $s = $this->get_settings();
        if ( empty( $s['client_id'] ) || empty( $s['client_secret'] ) ) {
            return new WP_Error( 'paypal_not_configured', __( 'PayPal credentials are not configured.', 'easy-hotel' ) );
        }

        $cache_key = 'eshb_paypal_token_' . md5( $s['client_id'] . '|' . $s['mode'] );
        $cached    = get_transient( $cache_key );
        if ( $cached ) return $cached;

        $response = wp_remote_post( $this->api_base() . '/v1/oauth2/token', [
            'timeout' => 20,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( $s['client_id'] . ':' . $s['client_secret'] ),
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'body'    => 'grant_type=client_credentials',
        ] );

        if ( is_wp_error( $response ) ) return $response;

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['access_token'] ) ) {
            return new WP_Error( 'paypal_token_failed', $body['error_description'] ?? __( 'Unable to authenticate with PayPal.', 'easy-hotel' ) );
        }

        set_transient( $cache_key, $body['access_token'], 8 * MINUTE_IN_SECONDS );
        return $body['access_token'];
    }

    public function create_payment( array $reservation, array $customer, array $pricing ) {

        $token = $this->get_access_token();
        if ( is_wp_error( $token ) ) {
            return [ 'success' => false, 'message' => $token->get_error_message() ];
        }

        $amount   = number_format( (float) ( $pricing['grandTotal'] ?? $pricing['totalPrice'] ?? 0 ), 2, '.', '' );
        $currency = $this->get_currency_code();

        $payload = [
            'intent'         => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount'      => [
                        'currency_code' => $currency,
                        'value'         => $amount,
                    ],
                    'description' => sprintf(
                        /* translators: %s: accommodation title */
                        __( 'Booking for %s', 'easy-hotel' ),
                        get_the_title( (int) ( $reservation['accomodation_id'] ?? 0 ) )
                    ),
                ],
            ],
            'application_context' => [
                'brand_name' => get_bloginfo( 'name' ),
                'user_action' => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING',
            ],
        ];

        $response = wp_remote_post( $this->api_base() . '/v2/checkout/orders', [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $payload ),
        ] );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'message' => $response->get_error_message() ];
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['id'] ) ) {
            $msg = $body['message'] ?? __( 'Failed to create PayPal order.', 'easy-hotel' );
            return [ 'success' => false, 'message' => $msg ];
        }

        return [
            'success' => true,
            'data'    => [
                'order_id' => $body['id'],
            ],
        ];
    }

    public function capture_payment( array $params ) {
        $order_id = sanitize_text_field( $params['order_id'] ?? '' );
        if ( empty( $order_id ) ) {
            return [ 'success' => false, 'message' => __( 'Missing PayPal order id.', 'easy-hotel' ) ];
        }

        $token = $this->get_access_token();
        if ( is_wp_error( $token ) ) {
            return [ 'success' => false, 'message' => $token->get_error_message() ];
        }

        $response = wp_remote_post( $this->api_base() . '/v2/checkout/orders/' . rawurlencode( $order_id ) . '/capture', [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
                // Idempotency key prevents accidental double-capture if the JS layer retries.
                'PayPal-Request-Id' => 'eshb-' . $order_id,
            ],
            'body'    => '{}',
        ] );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'message' => $response->get_error_message() ];
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        $status = $body['status'] ?? '';

        if ( $status !== 'COMPLETED' ) {
            $msg = $body['message'] ?? sprintf(
                /* translators: %s: PayPal capture status code returned by the Orders v2 API */
                __( 'PayPal capture failed (%s).', 'easy-hotel' ),
                $status
            );
            return [ 'success' => false, 'message' => $msg, 'raw' => $body ];
        }

        $capture = $body['purchase_units'][0]['payments']['captures'][0] ?? [];
        $amount  = (float) ( $capture['amount']['value'] ?? 0 );
        $currency = $capture['amount']['currency_code'] ?? $this->get_currency_code();
        $txn_id  = $capture['id'] ?? $order_id;

        return [
            'success'        => true,
            'transaction_id' => $txn_id,
            'amount'         => $amount,
            'currency'       => $currency,
            'mode'           => $this->get_settings()['mode'],
            'raw'            => $body,
        ];
    }
}
