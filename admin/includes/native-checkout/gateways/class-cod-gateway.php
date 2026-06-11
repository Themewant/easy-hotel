<?php
/**
 * Cash on Delivery (pay on arrival) gateway for the native checkout.
 *
 * This is an "offline" gateway: there is no remote payment provider to
 * talk to. The booking is created immediately and the balance is
 * collected later (on arrival / at the property). Because nothing is
 * captured online, total_paid stays 0 and the full amount is recorded
 * as the due balance on the booking.
 *
 * Settings live in the existing eshb_settings option:
 *   - gateway-cod-enable  (switcher; on by default)
 *   - cod-title           (label shown at checkout)
 *   - cod-description     (instructions shown under the label)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_COD_Gateway extends ESHB_Native_Abstract_Gateway {

    protected $id          = 'cod';
    protected $title       = '';
    protected $description = '';

    public function __construct() {
        $title = trim( (string) eshb_native_checkout_get_setting( 'cod-title', '' ) );
        $desc  = trim( (string) eshb_native_checkout_get_setting( 'cod-description', '' ) );

        $this->title       = $title !== '' ? $title : __( 'Cash on Delivery', 'easy-hotel' );
        $this->description = $desc  !== '' ? $desc  : __( 'Pay with cash upon arrival / at the property.', 'easy-hotel' );
    }

    /**
     * Enabled when the admin switch is on. Defaults to enabled when the
     * setting has never been saved, so COD is available out of the box.
     */
    public function is_enabled() {
        return ! empty( eshb_native_checkout_get_setting( 'gateway-cod-enable', true ) );
    }

    /**
     * No remote order to create for an offline gateway — succeed
     * immediately so the checkout flow can proceed to completion.
     */
    public function create_payment( array $reservation, array $customer, array $pricing ) {
        return [ 'success' => true, 'data' => [] ];
    }

    /**
     * Nothing is captured online for COD, but we still record the order
     * amount and currency so the payment entry reflects the real booking
     * total. The amount is recomputed from the current reservation using
     * the same coupon/email inputs the checkout handler uses, so it stays
     * in lockstep with the booking total.
     */
    public function capture_payment( array $params ) {
        $amount   = 0.0;
        $currency = $this->get_currency_code();

        if ( function_exists( 'eshb_native_checkout_get_items' ) && class_exists( 'ESHB_Native_Pricing' ) ) {
            $items = eshb_native_checkout_get_items();
            if ( ! empty( $items ) ) {
                // Nonce is verified by ESHB_Native_Checkout before this
                // gateway method runs; mirror the handler's inputs so the
                // recalculated total matches the booking total exactly.
                // phpcs:disable WordPress.Security.NonceVerification.Missing
                $coupon = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';
                $email  = isset( $_POST['email'] )  ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
                // phpcs:enable WordPress.Security.NonceVerification.Missing

                $pricing = ESHB_Native_Pricing::calculate_cart( $items, $coupon, $email );
                $amount  = (float) ( $pricing['grandTotal'] ?? $pricing['totalPrice'] ?? 0 );
            }
        }

        return [
            'success'        => true,
            'transaction_id' => uniqid( 'COD-' ),
            'amount'         => $amount,
            'currency'       => $currency,
            'mode'           => 'live',
        ];
    }

    /**
     * Resolve an ISO-4217 currency code for the payment record. Prefers
     * WooCommerce's configured currency (the plugin already integrates
     * with it for symbol formatting), then maps the configured symbol,
     * then falls back to USD.
     */
    private function get_currency_code() {
        if ( function_exists( 'get_woocommerce_currency' ) ) {
            $code = get_woocommerce_currency();
            if ( ! empty( $code ) ) return $code;
        }

        $settings = get_option( 'eshb_settings', [] );
        $symbol   = isset( $settings['currency_symbol'] ) ? trim( (string) $settings['currency_symbol'] ) : '';
        $map = [
            '$'  => 'USD',
            '€'  => 'EUR',
            '£'  => 'GBP',
            '¥'  => 'JPY',
            'A$' => 'AUD',
            'C$' => 'CAD',
            '₹'  => 'INR',
            '₺'  => 'TRY',
            '₽'  => 'RUB',
        ];
        if ( ! empty( $symbol ) && isset( $map[ $symbol ] ) ) {
            return $map[ $symbol ];
        }

        return apply_filters( 'eshb_native_cod_currency', 'USD' );
    }
}
