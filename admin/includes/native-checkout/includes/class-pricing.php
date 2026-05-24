<?php
/**
 * Pricing helper for the native checkout flow.
 *
 * Wraps the existing ESHB_Booking::calculate_booking_pricing() and adds
 * coupon + tax recalculation. The server-side method is the canonical
 * source of truth; the JS layer mirrors the math for instant feedback
 * but the server always re-runs the calculation before payment.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_Pricing {

    public static function calculate( array $reservation, $coupon_code = '', $customer_email = '' ) {

        $accomodation_id   = (int) ( $reservation['accomodation_id'] ?? 0 );
        $start_date        = sanitize_text_field( $reservation['start_date'] ?? '' );
        $end_date          = sanitize_text_field( $reservation['end_date'] ?? '' );
        $room_quantity     = max( 1, (int) ( $reservation['room_quantity'] ?? 1 ) );
        $extra_bed_qty     = max( 0, (int) ( $reservation['extra_bed_quantity'] ?? 0 ) );
        $adult_qty         = max( 0, (int) ( $reservation['adult_quantity'] ?? 1 ) );
        $children_qty      = max( 0, (int) ( $reservation['children_quantity'] ?? 0 ) );
        $start_time        = sanitize_text_field( $reservation['start_time'] ?? '' );
        $end_time          = sanitize_text_field( $reservation['end_time'] ?? '' );
        $selected_services = isset( $reservation['extra_services'] ) && is_array( $reservation['extra_services'] )
            ? $reservation['extra_services']
            : [];

        $booking = new ESHB_Booking();
        $pricing = $booking->calculate_booking_pricing(
            $accomodation_id,
            $start_date,
            $end_date,
            $room_quantity,
            $extra_bed_qty,
            $adult_qty,
            $children_qty,
            $selected_services,
            false,
            $start_time,
            $end_time
        );

        if ( ! is_array( $pricing ) ) {
            $pricing = [];
        }

        // Native checkout treats `quantity` as authoritative for every
        // service, regardless of the meta-configured charge_type. The
        // upstream calculator multiplies by room_quantity for charge_type
        // === 'room', which would diverge from the JS layer's math after
        // the user adjusts the qty stepper. Recompute the extras line
        // here and patch the affected totals so server and client agree.
        $native_extras = self::calculate_native_extras_charge(
            $selected_services,
            (int) ( $pricing['daysCount'] ?? 1 )
        );
        $old_extras = (float) ( $pricing['extraServicesPrice'] ?? 0 );
        if ( abs( $native_extras - $old_extras ) > 0.0001 ) {
            $delta = $native_extras - $old_extras;
            $pricing['extraServicesPrice']     = $native_extras;
            $pricing['extraServicesPriceHtml'] = ( new ESHB_Core() )->eshb_price( $native_extras );
            $pricing['subtotalPrice']          = max( 0, (float) ( $pricing['subtotalPrice'] ?? 0 ) + $delta );
            $pricing['subtotalPriceHtml']      = ( new ESHB_Core() )->eshb_price( $pricing['subtotalPrice'] );
            $pricing['totalPrice']             = max( 0, (float) ( $pricing['totalPrice'] ?? 0 ) + $delta );
            $pricing['totalPriceHtml']         = ( new ESHB_Core() )->eshb_price( $pricing['totalPrice'] );
            $pricing['regularSubtotalPrice']   = max( 0, (float) ( $pricing['regularSubtotalPrice'] ?? 0 ) + $delta );
            $pricing['regularSubtotalPriceHtml'] = ( new ESHB_Core() )->eshb_price( $pricing['regularSubtotalPrice'] );
            $pricing['regularTotalPrice']      = max( 0, (float) ( $pricing['regularTotalPrice'] ?? 0 ) + $delta );
            $pricing['regularTotalPriceHtml']  = ( new ESHB_Core() )->eshb_price( $pricing['regularTotalPrice'] );
        }

        $tax_amount = self::calculate_tax( (float) ( $pricing['totalPrice'] ?? 0 ) );
        $coupon     = self::evaluate_coupon( $coupon_code, $pricing, $accomodation_id, $customer_email );

        $subtotal_after_coupon = max( 0, (float) ( $pricing['totalPrice'] ?? 0 ) - (float) $coupon['discount'] );
        $grand_total           = $subtotal_after_coupon + $tax_amount;

        $core = new ESHB_Core();

        $pricing['couponCode']         = $coupon['code'];
        $pricing['couponId']           = (int) ( $coupon['coupon_id'] ?? 0 );
        $pricing['couponDiscount']     = $coupon['discount'];
        $pricing['couponDiscountHtml'] = $core->eshb_price( $coupon['discount'] );
        $pricing['couponMessage']      = $coupon['message'];
        $pricing['couponValid']        = $coupon['valid'];
        $pricing['taxRate']         = self::tax_rate();
        $pricing['taxAmount']       = $tax_amount;
        $pricing['taxAmountHtml']   = $core->eshb_price( $tax_amount );
        $pricing['grandTotal']      = $grand_total;
        $pricing['grandTotalHtml']  = $core->eshb_price( $grand_total );

        /**
         * Final-stage filter for the native checkout pricing payload.
         *
         * Extensions (e.g. the EHB Deposit add-on) use this to inject
         * deposit / due / payment-option fields and, when the buyer has
         * chosen to pay only a deposit, to override grandTotal so the
         * gateway charges that lower amount. Keep new keys additive so
         * the JS layer's `[data-eshb-price]` rebinding continues to work.
         *
         * @param array $pricing     The computed pricing array.
         * @param array $reservation The raw reservation payload.
         */
        return apply_filters( 'eshb_native_checkout_pricing', $pricing, $reservation );
    }

    /**
     * Sum the extra services charge using `quantity` as the multiplier
     * for every service (per_day multiplies by night count). This is
     * the source-of-truth math for native checkout and mirrors the
     * client-side calculation in checkout.js.
     */
    public static function calculate_native_extras_charge( array $selected_services, $days_count ) {
        $total = 0.0;
        $days_count = max( 1, (int) $days_count );

        foreach ( $selected_services as $service ) {
            if ( ! is_array( $service ) ) continue;
            $service_id = (int) ( $service['id'] ?? 0 );
            $quantity   = max( 1, (int) ( $service['quantity'] ?? 0 ) );
            if ( ! $service_id || empty( $service['quantity'] ) ) continue;

            $meta = get_post_meta( $service_id, 'eshb_service_metaboxes', true );
            if ( empty( $meta ) ) continue;

            $price       = floatval( $meta['service_price'] ?? 0 );
            $periodicity = $meta['service_periodicity'] ?? 'once';

            if ( $periodicity === 'per_day' || $periodicity === 'perday' ) {
                $price *= $days_count;
            }
            $price *= $quantity;

            $total += $price;
        }

        return round( $total, 2 );
    }

    public static function tax_rate() {
        $settings = get_option( 'eshb_settings', [] );
        $rate = isset( $settings['native-checkout-tax-rate'] ) ? floatval( $settings['native-checkout-tax-rate'] ) : 0;
        return max( 0, $rate );
    }

    public static function calculate_tax( $amount ) {
        $rate = self::tax_rate();
        if ( $rate <= 0 || $amount <= 0 ) return 0;
        return round( ( $amount * $rate ) / 100, 2 );
    }

    public static function evaluate_coupon( $code, array $pricing, $accomodation_id, $customer_email = '' ) {
        $code = strtoupper( trim( (string) $code ) );
        $result = [
            'code'      => '',
            'coupon_id' => 0,
            'discount'  => 0,
            'message'   => '',
            'valid'     => false,
        ];

        if ( empty( $code ) ) return $result;

        // Coupons are a small set in practice — iterate them in PHP so
        // we don't hit a slow LIKE on a serialized meta field.
        $coupons = get_posts( [
            'post_type'      => 'eshb_coupon',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'no_found_rows'  => true,
            'fields'         => 'ids',
        ] );

        $details        = null;
        $matched_coupon_id = 0;
        foreach ( $coupons as $coupon_id ) {
            $candidate = get_post_meta( $coupon_id, 'eshb_coupon_metaboxes', true );
            if ( is_array( $candidate ) && strtoupper( (string) ( $candidate['coupon-code'] ?? '' ) ) === $code ) {
                $details           = $candidate;
                $matched_coupon_id = (int) $coupon_id;
                break;
            }
        }

        if ( empty( $details ) ) {
            $result['message'] = __( 'Invalid coupon code.', 'easy-hotel' );
            return $result;
        }

        // Usage-limit guard delegated to ESHB_Native_Checkout_Coupon so
        // the validity rules live in one place. We check explicitly
        // here for both the global limit and the per-user limit (when
        // an email is available) so a regression in is_valid() can't
        // silently let an over-used coupon through.
        $coupon_obj    = new ESHB_Native_Checkout_Coupon( $matched_coupon_id );
        $usage_limit   = (int) $coupon_obj->get_usage_limit();
        $usage_count   = (int) $coupon_obj->get_usage_count();

        if ( $usage_limit > 0 && $usage_count >= $usage_limit ) {
            $result['message'] = __( 'Coupon usage limit reached.', 'easy-hotel' );
            return $result;
        }

        // Per-user limit guard. When the coupon caps usage per customer
        // and the buyer's email isn't on the request yet (Apply is the
        // first interaction, the customer-info section sits below), the
        // count cannot be computed — so refuse instead of falling back
        // silently. The customer-info section asks them to type the
        // email and try again.
        $per_user_limit = (int) $coupon_obj->get_usage_limit_per_user();
        if ( $per_user_limit > 0 ) {
            if ( $customer_email === '' ) {
                $result['message'] = __( 'Please enter your email address above before applying this coupon.', 'easy-hotel' );
                return $result;
            }
            $per_user_count = (int) $coupon_obj->get_usage_count_for_user( $customer_email );
            if ( $per_user_count >= $per_user_limit ) {
                $result['message'] = __( 'You have already used this coupon the maximum number of times.', 'easy-hotel' );
                return $result;
            }
        }

        $allowed_ids = $details['accomodation-ids'] ?? [];
        if ( ! empty( $allowed_ids ) && is_array( $allowed_ids ) && ! in_array( (int) $accomodation_id, array_map( 'intval', $allowed_ids ), true ) ) {
            $result['message'] = __( 'Coupon is not valid for this accommodation.', 'easy-hotel' );
            return $result;
        }

        $expiry = $details['expiry-date'] ?? '';
        if ( $expiry && strtotime( $expiry ) < strtotime( current_time( 'Y-m-d' ) ) ) {
            $result['message'] = __( 'This coupon has expired.', 'easy-hotel' );
            return $result;
        }

        $amount = floatval( $details['coupon-amount'] ?? 0 );
        $type   = $details['discount-type'] ?? 'fixed';
        $base   = (float) ( $pricing['totalPrice'] ?? 0 );

        if ( $amount <= 0 || $base <= 0 ) {
            $result['message'] = __( 'Coupon is not applicable.', 'easy-hotel' );
            return $result;
        }

        $discount = ( $type === 'percent' ) ? ( $base * $amount / 100 ) : $amount;
        $discount = round( min( $discount, $base ), 2 );

        $result['coupon_id'] = $matched_coupon_id;

        $result['code']     = $code;
        $result['discount'] = $discount;
        $result['valid']    = true;
        $result['message']  = __( 'Coupon applied.', 'easy-hotel' );

        return $result;
    }
}
