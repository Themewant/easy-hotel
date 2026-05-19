<?php
/**
 * Coupon helper for the native checkout flow.
 *
 * The coupon CPT stores all its fields inside a single serialized
 * `eshb_coupon_metaboxes` array (see admin/includes/post-types/coupon/
 * metaboxes.php — `'data_type' => 'serialize'`), so read and write
 * through that array rather than as standalone post-meta entries.
 * Otherwise the admin "Usage / Limit" column and the metabox fields
 * would never reflect what this class writes.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_Checkout_Coupon {

    const META_KEY     = 'eshb_coupon_metaboxes';
    // Structured usage log lives OUTSIDE the framework-managed metabox
    // array, so re-saving the coupon in admin can't strip it.
    const LOG_META_KEY = '_eshb_native_coupon_usage_log';

    private $coupon_id;

    public function __construct( $coupon_id = 0 ) {
        $this->coupon_id = (int) $coupon_id;
    }

    /**
     * Fetch the full coupon metabox array.
     */
    private function get_meta() {
        if ( ! $this->coupon_id ) return [];
        $meta = get_post_meta( $this->coupon_id, self::META_KEY, true );
        return is_array( $meta ) ? $meta : [];
    }

    /**
     * Update a single key inside the serialized metabox array.
     */
    private function set_meta( $key, $value ) {
        if ( ! $this->coupon_id ) return false;
        $meta = $this->get_meta();
        $meta[ $key ] = $value;
        return update_post_meta( $this->coupon_id, self::META_KEY, $meta );
    }

    public function set_usage_count( $count = 0 ) {
        $this->set_meta( 'usage-count', (int) $count );
    }

    /**
     * Replace the "used-by" log. Writes two things:
     *   1. A human-readable newline-separated string into the
     *      framework-managed textarea field (`eshb_coupon_metaboxes['used-by']`)
     *      for the admin UI.
     *   2. The raw structured array into a dedicated post-meta key
     *      (LOG_META_KEY) that the metabox framework does NOT manage,
     *      so admin re-saves can't strip it.
     */
    public function set_used_by( $customer_data ) {
        if ( ! is_array( $customer_data ) || empty( $customer_data ) || ! $this->coupon_id ) {
            return;
        }
        $lines = [];
        foreach ( $customer_data as $row ) {
            if ( ! is_array( $row ) ) continue;
            $name       = $row['name'] ?? '';
            $email      = $row['email'] ?? '';
            $booking_id = $row['booking_id'] ?? '';
            $used_at    = $row['used_at'] ?? '';
            $lines[] = trim( sprintf( '%s <%s> — booking #%s — %s', $name, $email, $booking_id, $used_at ) );
        }
        $this->set_meta( 'used-by', implode( "\n", $lines ) );
        update_post_meta( $this->coupon_id, self::LOG_META_KEY, $customer_data );
    }

    public function get_usage_count() {
        $meta = $this->get_meta();
        return isset( $meta['usage-count'] ) ? (int) $meta['usage-count'] : 0;
    }

    /**
     * Return the structured used-by log. Checks four locations in this
     * order so historical data from earlier versions of the class is
     * never lost:
     *   1. Dedicated post-meta key written by the current class.
     *   2. Legacy `used-by-log` sibling inside the metabox array.
     *   3. The standalone `used-by` post-meta key that the very first
     *      version of this class wrote to (raw serialized array).
     *   4. The metabox's `used-by` textarea — if it contains JSON we
     *      decode it; otherwise it's just human-readable text.
     *
     * Whenever we find structured data anywhere except (1) we migrate
     * it into (1) so subsequent reads are fast and unambiguous.
     */
    public function get_used_by() {
        if ( ! $this->coupon_id ) {
            $meta = $this->get_meta();
            return $meta['used-by'] ?? '';
        }

        // 1. New canonical location.
        $log = get_post_meta( $this->coupon_id, self::LOG_META_KEY, true );
        if ( is_array( $log ) && ! empty( $log ) ) {
            return $log;
        }

        // 2. Legacy in-metabox structured log.
        $meta = $this->get_meta();
        if ( ! empty( $meta['used-by-log'] ) && is_array( $meta['used-by-log'] ) ) {
            update_post_meta( $this->coupon_id, self::LOG_META_KEY, $meta['used-by-log'] );
            return $meta['used-by-log'];
        }

        // 3. Pre-refactor standalone post meta written directly as an
        //    array (the original ESHB_Native_Checkout_Coupon class).
        $standalone = get_post_meta( $this->coupon_id, 'used-by', true );
        if ( is_array( $standalone ) && ! empty( $standalone ) ) {
            update_post_meta( $this->coupon_id, self::LOG_META_KEY, $standalone );
            return $standalone;
        }

        // 4. Textarea — may legitimately hold a JSON array if it was
        //    rendered from a serialized PHP array by an earlier flow.
        $textarea = $meta['used-by'] ?? '';
        if ( is_string( $textarea ) && $textarea !== '' ) {
            $trim = ltrim( $textarea );
            if ( $trim !== '' && ( $trim[0] === '[' || $trim[0] === '{' ) ) {
                $decoded = json_decode( $textarea, true );
                if ( is_array( $decoded ) && ! empty( $decoded ) ) {
                    update_post_meta( $this->coupon_id, self::LOG_META_KEY, $decoded );
                    return $decoded;
                }
            }
        }

        return $textarea;
    }

    public function get_usage_limit() {
        $meta = $this->get_meta();
        return isset( $meta['usage-limit'] ) ? (int) $meta['usage-limit'] : 0;
    }

    public function get_usage_limit_per_user() {
        $meta = $this->get_meta();
        return isset( $meta['usage-limit-per-user'] ) ? (int) $meta['usage-limit-per-user'] : 0;
    }

    /**
     * Count how many times the given email has already redeemed the
     * coupon. Falls back to 0 when the structured log is missing.
     */
    public function get_usage_count_for_user( $email ) {
        $email = strtolower( trim( (string) $email ) );
        if ( $email === '' ) return 0;

        $log = $this->get_used_by();

        if ( ! is_array( $log ) ) return 0;

        $count = 0;
        foreach ( $log as $row ) {
            if ( ! is_array( $row ) ) continue;
            if ( strtolower( (string) ( $row['email'] ?? '' ) ) === $email ) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Validate against the global usage limit and, when an email is
     * provided, the per-user limit too. Sets $this->last_error so
     * error_message() can return a specific reason.
     *
     * @param string $email Optional customer email for per-user check.
     */
    public function is_valid( $email = '' ) {
        $this->last_error = '';

        $usage_limit = $this->get_usage_limit();
        $usage_count = $this->get_usage_count();
        if ( $usage_limit > 0 && $usage_count >= $usage_limit ) {
            $this->last_error = __( 'Coupon usage limit reached', 'easy-hotel' );
            return false;
        }

        $per_user_limit = $this->get_usage_limit_per_user();
        if ( $email !== '' && $per_user_limit > 0 ) {
            $per_user_count = $this->get_usage_count_for_user( $email );
            if ( $per_user_count >= $per_user_limit ) {
                $this->last_error = __( 'You have already used this coupon the maximum number of times.', 'easy-hotel' );
                return false;
            }
        }

        return true;
    }

    public function error_message() {
        if ( empty( $this->coupon_id ) ) {
            return __( 'Please enter a coupon code', 'easy-hotel' );
        }
        // Prefer the reason left behind by the most recent is_valid()
        // call. Re-running is_valid() here would discard a per-user
        // failure because we don't have the email at this layer.
        if ( $this->last_error !== '' ) {
            return $this->last_error;
        }
        if ( ! $this->is_valid() ) {
            return $this->last_error ?: __( 'Coupon is not valid', 'easy-hotel' );
        }
        return '';
    }

    /** @var string */
    private $last_error = '';
}
