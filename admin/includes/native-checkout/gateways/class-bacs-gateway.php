<?php
/**
 * Direct Bank Transfer (BACS) gateway for the native checkout.
 *
 * A port of WooCommerce's BACS gateway to the native checkout: the
 * booking is placed immediately and left On hold ("awaiting BACS
 * payment"), the instructions and bank details are shown on the
 * thank-you page and in the customer email, and the guest wires the
 * money afterwards. Nothing is captured online, so the payment record
 * carries a zero amount and the booking keeps its full total as the due
 * balance until the admin confirms the transfer.
 *
 * Settings live in the existing eshb_settings option, under the
 * 'payment-gateways' group — the same four fields plus account details
 * WooCommerce exposes:
 *   - gateway-bacs-enable  (switcher; off by default)
 *   - bacs-title           (label shown at checkout)
 *   - bacs-description     (shown under the label at checkout)
 *   - bacs-instructions    (added to the thank-you page and emails)
 *   - bacs-accounts        (repeater of bank account rows)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_BACS_Gateway extends ESHB_Native_Abstract_Gateway {

    protected $id          = 'bacs';
    protected $title       = '';
    protected $description = '';

    public function __construct() {
        $title = trim( (string) eshb_native_checkout_get_setting( 'bacs-title', '' ) );
        $desc  = trim( (string) eshb_native_checkout_get_setting( 'bacs-description', '' ) );

        $this->title       = $title !== '' ? $title : __( 'Direct bank transfer', 'easy-hotel' );
        $this->description = $desc  !== '' ? $desc  : __( 'Make your payment directly into our bank account. Please use your Booking ID as the payment reference. Your booking will not be confirmed until the funds have cleared in our account.', 'easy-hotel' );
    }

    /**
     * Off by default, mirroring WooCommerce: an install that has not
     * entered bank details yet must not offer a payment method nobody
     * can act on.
     */
    public function is_enabled() {
        return ! empty( eshb_native_checkout_get_setting( 'gateway-bacs-enable', false ) );
    }

    /**
     * The bank accounts the guest can transfer to, as saved by the
     * 'bacs-accounts' repeater. Rows where every field is blank are
     * dropped so a stray empty row never renders an empty list.
     *
     * @return array[] List of rows keyed by field id.
     */
    public function get_accounts() {
        $rows = eshb_native_checkout_get_setting( 'bacs-accounts', [] );
        if ( ! is_array( $rows ) ) {
            return [];
        }

        $accounts = [];
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) continue;

            $account = [
                'account_name'   => trim( (string) ( $row['account_name'] ?? '' ) ),
                'account_number' => trim( (string) ( $row['account_number'] ?? '' ) ),
                'bank_name'      => trim( (string) ( $row['bank_name'] ?? '' ) ),
                'sort_code'      => trim( (string) ( $row['sort_code'] ?? '' ) ),
                'iban'           => trim( (string) ( $row['iban'] ?? '' ) ),
                'bic'            => trim( (string) ( $row['bic'] ?? '' ) ),
            ];

            if ( '' === implode( '', $account ) ) continue;

            $accounts[] = $account;
        }

        return $accounts;
    }

    /**
     * Countries that call the sort code something else. Same list (and
     * same filter shape) as WooCommerce's get_country_locale().
     *
     * Deliberately plain strings, not __() calls: the settings screen
     * builds its field array on 'plugins_loaded', and translating there
     * would load the textdomain before 'init' — the "_load_textdomain_
     * just_in_time was called incorrectly" notice WordPress 6.7 added.
     * Every other label in admin-settings.php is a plain string for the
     * same reason; translators can still hook the filter.
     */
    public static function get_country_locale() {
        return apply_filters( 'eshb_get_bacs_locale', [
            'AU' => [ 'sortcode' => [ 'label' => 'BSB' ] ],
            'CA' => [ 'sortcode' => [ 'label' => 'Bank transit number' ] ],
            'IN' => [ 'sortcode' => [ 'label' => 'IFSC' ] ],
            'IT' => [ 'sortcode' => [ 'label' => 'Branch sort' ] ],
            'NZ' => [ 'sortcode' => [ 'label' => 'Bank code' ] ],
            'SE' => [ 'sortcode' => [ 'label' => 'Bank code' ] ],
            'US' => [ 'sortcode' => [ 'label' => 'Routing number' ] ],
            'ZA' => [ 'sortcode' => [ 'label' => 'Branch code' ] ],
        ] );
    }

    /**
     * Sort code label for a country, falling back to "Sort code".
     *
     * @param string $country ISO-3166 alpha-2 code.
     */
    public static function sortcode_label( $country = '' ) {
        $locale  = self::get_country_locale();
        $country = strtoupper( (string) $country );

        return isset( $locale[ $country ]['sortcode']['label'] )
            ? $locale[ $country ]['sortcode']['label']
            : 'Sort code';
    }

    /**
     * The store's own country, used for the sort code label in the admin
     * settings table.
     *
     * Read straight from the option WooCommerce stores it in rather than
     * through WC()->countries: that object pulls in WooCommerce's own
     * translated country list, and this runs on 'plugins_loaded' — early
     * enough to trip the same just-in-time textdomain notice.
     */
    public static function base_country() {
        // Stored as "US:CA" — the state half is irrelevant here.
        $default = (string) get_option( 'woocommerce_default_country', '' );
        return $default ? strtok( $default, ':' ) : '';
    }

    /**
     * Bank details in WooCommerce's layout: the account name as a
     * heading, then the remaining fields as a list. $inline_styles adds
     * email-safe CSS, since email clients drop the stylesheet.
     *
     * @param string $country  Guest country, for the sort code label.
     * @param bool   $inline_styles Whether to inline the CSS.
     */
    private function render_accounts( $country = '', $inline_styles = false ) {
        $accounts = $this->get_accounts();
        if ( empty( $accounts ) ) {
            return '';
        }

        $labels = [
            'bank_name'      => __( 'Bank', 'easy-hotel' ),
            'account_number' => __( 'Account number', 'easy-hotel' ),
            'sort_code'      => self::sortcode_label( $country ),
            'iban'           => __( 'IBAN', 'easy-hotel' ),
            'bic'            => __( 'BIC', 'easy-hotel' ),
        ];

        $h3_attr = $inline_styles ? ' style="font-size:15px;margin:16px 0 4px;"' : '';
        $ul_attr = $inline_styles ? ' style="margin:0;padding:0 0 0 18px;line-height:1.7;"' : '';

        $html        = '';
        $has_details = false;

        foreach ( $accounts as $account ) {
            $rows = '';
            foreach ( $labels as $key => $label ) {
                if ( empty( $account[ $key ] ) ) continue;
                $rows .= '<li class="' . esc_attr( $key ) . '">' . esc_html( $label ) . ': <strong>' . esc_html( $account[ $key ] ) . '</strong></li>';
                $has_details = true;
            }

            if ( '' === $rows ) continue;

            if ( ! empty( $account['account_name'] ) ) {
                $html .= '<h3 class="eshb-bacs-account-name"' . $h3_attr . '>' . esc_html( $account['account_name'] ) . ':</h3>';
            }
            $html .= '<ul class="eshb-bacs-details"' . $ul_attr . '>' . $rows . '</ul>';
        }

        if ( ! $has_details ) {
            return '';
        }

        $heading_attr = $inline_styles ? ' style="font-size:16px;margin:24px 0 8px;border-bottom:1px solid #e5e7eb;padding-bottom:4px;"' : '';

        return '<section class="eshb-bacs-bank-details">'
            . '<h2 class="eshb-bacs-bank-details-heading"' . $heading_attr . '>' . esc_html__( 'Our bank details', 'easy-hotel' ) . '</h2>'
            . $html
            . '</section>';
    }

    /**
     * Instructions + bank details for the thank-you page and the
     * customer email — the only two places WooCommerce shows them.
     *
     * @param int[] $booking_ids   Bookings created by the completed checkout.
     * @param bool  $inline_styles Inline the CSS (email) instead of relying
     *                             on the stylesheet (thank-you page).
     */
    public function get_instructions_html( array $booking_ids = [], $inline_styles = false ) {
        $instructions = trim( (string) eshb_native_checkout_get_setting( 'bacs-instructions', '' ) );

        // The sort code label follows the guest's country, exactly as
        // WooCommerce keys it off the order's billing country.
        $country = '';
        $first   = (int) reset( $booking_ids );
        if ( $first ) {
            $customer = get_post_meta( $first, 'eshb_booking_customer_details_metaboxes', true );
            if ( is_array( $customer ) ) {
                $country = (string) ( $customer['country'] ?? '' );
            }
        }

        $accounts = $this->render_accounts( $country, $inline_styles );

        if ( '' === $instructions && '' === $accounts ) {
            return '';
        }

        $html = '';
        if ( '' !== $instructions ) {
            $html .= wpautop( wptexturize( esc_html( $instructions ) ) );
        }

        return $html . $accounts;
    }

    /**
     * The booking waits on funds that have not moved yet, so say what is
     * being waited for rather than the generic "processing your payment".
     */
    public function get_email_intro( $status ) {

        if ( 'on-hold' === $status ) {
            return __( 'Your reservation is on hold until your bank transfer reaches us. Our account details are below.', 'easy-hotel' );
        }

        return '';
    }

    /**
     * The money has not arrived yet, so — exactly like WooCommerce's
     * BACS — the booking is left On hold awaiting the transfer. No
     * setting for this: WooCommerce does not expose one either.
     */
    public function get_completed_status( $default_status ) {
        $status  = apply_filters( 'eshb_native_bacs_booking_status', 'on-hold', $default_status );
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
     * Nothing has been received yet: the payment record is created with a
     * zero amount so the booking keeps its full total as the due balance
     * until the admin confirms the transfer.
     */
    public function capture_payment( array $params ) {
        return [
            'success'        => true,
            'transaction_id' => uniqid( 'BACS-' ),
            'amount'         => 0.0,
            'currency'       => $this->resolve_currency_code( 'eshb_native_bacs_currency' ),
            'mode'           => 'live',
        ];
    }
}
