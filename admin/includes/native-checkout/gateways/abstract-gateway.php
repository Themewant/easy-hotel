<?php
/**
 * Abstract base class for native-checkout payment gateways.
 *
 * Concrete gateways extend this and implement the create_payment() /
 * capture_payment() lifecycle. Adding a new gateway is a matter of
 * dropping a subclass into ./gateways/ and registering it with the
 * gateway manager — no edits to the checkout flow are required.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

abstract class ESHB_Native_Abstract_Gateway {

    /** Unique gateway identifier (e.g. 'paypal'). */
    protected $id = '';

    /** Human-readable title shown in the checkout. */
    protected $title = '';

    /** Short description shown under the title. */
    protected $description = '';

    public function get_id() {
        return $this->id;
    }

    public function get_title() {
        return $this->title;
    }

    public function get_description() {
        return $this->description;
    }

    /**
     * Whether this gateway is fully configured and enabled.
     */
    abstract public function is_enabled();

    /**
     * Post-checkout instructions shown on the thank-you page and in the
     * customer confirmation email — e.g. where to wire the money for an
     * offline gateway. Empty for gateways that take payment online.
     *
     * @param int[] $booking_ids   Bookings created by the completed checkout.
     * @param bool  $inline_styles Inline the CSS (email) instead of relying
     *                             on the stylesheet (thank-you page).
     * @return string
     */
    public function get_instructions_html( array $booking_ids = [], $inline_styles = false ) {
        return '';
    }

    /**
     * Opening line for the customer confirmation email, when this gateway
     * needs one of its own.
     *
     * The booking status alone cannot describe every gateway. A pay-on-
     * arrival booking sits in `processing` — or `completed`, with
     * auto-approval on — exactly like a booking paid by card, yet the
     * customer still owes the money; telling them "your payment went
     * through" would be plainly wrong. Gateways that take the money online
     * need no override: the status already says what happened.
     *
     * Return an empty string to keep the wording that belongs to the status.
     *
     * @param string $status Booking status the email is being sent for.
     * @return string
     */
    public function get_email_intro( $status ) {
        return '';
    }

    /**
     * Booking status to apply once checkout completes. Gateways that wait
     * for funds to arrive (bank transfer) override this to keep the
     * booking on hold instead of moving it to processing/completed.
     *
     * @param string $default_status Status resolved from the global settings.
     * @return string
     */
    public function get_completed_status( $default_status ) {
        return $default_status;
    }

    /**
     * Note saved on a booking's payment record at checkout, shown in the
     * admin Payments table. Offline gateways use it to say what is still
     * owed (e.g. "€500 due on arrival") while the recorded amount stays 0.
     *
     * @param float $booking_total Total of the booking the payment belongs to.
     * @return string Plain text, or '' for no note.
     */
    public function get_payment_note( $booking_total ) {
        return '';
    }

    /**
     * Whether this gateway takes money online through a server-created
     * order. When true, create_payment() must return the order's
     * `data.order_id` plus top-level `amount` and `currency`, and checkout
     * completion only accepts that order for the same cart and total.
     * Offline gateways (bank transfer, pay on arrival) keep the default.
     *
     * @return bool
     */
    public function requires_payment_verification() {
        return false;
    }

    /**
     * Resolve an ISO-4217 currency code for payment records. Prefers
     * WooCommerce's configured currency (the plugin already integrates
     * with it for symbol formatting), then maps the configured symbol,
     * then falls back to USD.
     *
     * The fallback goes through the gateway's own filter,
     * `eshb_native_{gateway id}_currency` (e.g. eshb_native_cod_currency).
     *
     * @param string $filter_name Unused; kept so existing calls stay valid.
     * @return string
     */
    protected function resolve_currency_code( $filter_name = '' ) {
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

        return $this->id !== '' ? apply_filters( "eshb_native_{$this->id}_currency", 'USD' ) : 'USD';
    }

    /**
     * Frontend payload (script handles, API keys, gateway-specific
     * settings) needed by the JS layer to render the gateway button.
     *
     * @return array
     */
    public function get_frontend_data() {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
        ];
    }

    /**
     * Create a server-side payment intent / order for the given reservation.
     * Returns gateway-specific data the JS layer needs to launch the flow
     * (e.g. PayPal order id).
     *
     * @return array { success: bool, data: array, message?: string }
     */
    abstract public function create_payment( array $reservation, array $customer, array $pricing );

    /**
     * Verify and capture a previously-created payment. Called server-side
     * once the JS layer signals user approval. Must return a normalized
     * result so the checkout flow can persist payment metadata.
     *
     * @return array { success: bool, transaction_id?: string, amount?: float, currency?: string, mode?: string, raw?: array, message?: string }
     */
    abstract public function capture_payment( array $params );
}
