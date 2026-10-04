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
     * Nothing was paid online, whatever status the booking landed on, so
     * the email must not tell the customer their payment went through.
     */
    public function get_email_intro( $status ) {

        if ( in_array( $status, [ 'completed', 'processing', 'on-hold' ], true ) ) {
            return __( 'Your booking is confirmed. No payment was taken online — the total below is due on arrival.', 'easy-hotel' );
        }

        return '';
    }

    /**
     * A pay-on-arrival booking is confirmed but not paid, so it lands on
     * `processing` — as WooCommerce's own COD gateway does — even with
     * "Auto Approve Booking" on. Staff complete it once the guest has paid.
     *
     * @param string $default_status Status resolved from the global settings.
     * @return string
     */
    public function get_completed_status( $default_status ) {
        $status  = apply_filters( 'eshb_native_cod_booking_status', 'processing', $default_status );
        $allowed = array_keys( ESHB_Helper::eshb_get_booking_statuses() );

        return in_array( $status, $allowed, true ) ? $status : $default_status;
    }

    /**
     * No remote order to create for an offline gateway — succeed
     * immediately so the checkout flow can proceed to completion.
     */
    public function create_payment( array $reservation, array $customer, array $pricing ) {
        return [ 'success' => true, 'data' => [] ];
    }

    /**
     * Nothing is taken online for COD, so the payment record carries a zero
     * amount: the booking keeps its full total as the due balance, staff see
     * what to collect, and a refund can't hand back money that never came
     * in. The amount owed is written to the payment's note instead (see
     * get_payment_note()). Once the guest pays at the property, staff
     * record that payment on the booking.
     */
    public function capture_payment( array $params ) {
        return [
            'success'        => true,
            'transaction_id' => uniqid( 'COD-' ),
            'amount'         => 0.0,
            'currency'       => $this->get_currency_code(),
            'mode'           => 'live',
        ];
    }

    /**
     * "€500 due on arrival" on the booking's payment record.
     */
    public function get_payment_note( $booking_total ) {
        $core  = new ESHB_Core();
        $price = html_entity_decode( wp_strip_all_tags( $core->eshb_price( (float) $booking_total ) ), ENT_QUOTES, 'UTF-8' );

        /* translators: %s: amount the guest still owes, e.g. €500.00 */
        return sprintf( __( '%s due on arrival', 'easy-hotel' ), $price );
    }

    /**
     * Resolve an ISO-4217 currency code for the payment record. The
     * WooCommerce / symbol / USD cascade lives on the base class; only
     * the fallback filter name is gateway-specific.
     */
    private function get_currency_code() {
        return $this->resolve_currency_code( 'eshb_native_cod_currency' );
    }
}
