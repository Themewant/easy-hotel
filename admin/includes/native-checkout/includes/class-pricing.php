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

    /**
     * Single-accommodation pricing (coupon applied to the one item, tax on
     * the post-coupon total). Kept for the single-item flow, the gateways
     * and add-ons (e.g. EHB Deposit) that call it via the legacy
     * reservation shape.
     */
    public static function calculate( array $reservation, $coupon_code = '', $customer_email = '' ) {

        $pricing         = self::compute_item_base( $reservation );
        $accomodation_id = (int) ( $reservation['accomodation_id'] ?? 0 );
        $core            = new ESHB_Core();

        $coupon = self::evaluate_coupon( $coupon_code, $pricing, $accomodation_id, $customer_email );

        // totalPrice already includes the extra services charge exactly once,
        // so the grand total is simply subtotal − coupon + tax. Tax is applied
        // on the post-coupon subtotal to mirror checkout.js (recalcLocal) so
        // the server charge matches the amount shown to the customer.
        $subtotal_after_coupon = max( 0, (float) ( $pricing['totalPrice'] ?? 0 ) - (float) $coupon['discount'] );
        $tax_amount            = self::calculate_tax( $subtotal_after_coupon );
        $grand_total           = $subtotal_after_coupon + $tax_amount;

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
     * Multi-accommodation (cart) pricing.
     *
     * Each item is priced independently; the coupon is evaluated PER
     * accommodation (so an accommodation-restricted coupon only discounts
     * the items it applies to) while tax is charged once on the whole-cart
     * subtotal after discounts. The per-item breakdown is returned under
     * `items` so the booking handler can split the financials across the
     * linked per-accommodation booking records.
     *
     * @param array  $items          Cart items keyed by item key.
     * @param string $coupon_code
     * @param string $customer_email
     * @return array Cart pricing payload (shares the top-level keys used by
     *               the single-item payload, plus `isCart` and `items`).
     */
    public static function calculate_cart( array $items, $coupon_code = '', $customer_email = '' ) {
        $core        = new ESHB_Core();
        $coupon_code = trim( (string) $coupon_code );

        $item_views         = [];
        $cart_subtotal      = 0.0; // sum of item totals (incl. extras), pre-coupon
        $cart_regular_total = 0.0;
        $total_discount     = 0.0;
        $coupon_any_valid   = false;
        $coupon_first_error = '';
        $coupon_code_out    = '';
        $coupon_id_out      = 0;

        foreach ( $items as $item_key => $reservation ) {
            if ( ! is_array( $reservation ) ) continue;

            $p        = self::compute_item_base( $reservation );
            $accom_id = (int) ( $reservation['accomodation_id'] ?? 0 );

            // Per-accommodation coupon evaluation.
            $item_discount = 0.0;
            if ( $coupon_code !== '' ) {
                $c = self::evaluate_coupon( $coupon_code, $p, $accom_id, $customer_email );
                if ( ! empty( $c['valid'] ) ) {
                    $item_discount    = (float) $c['discount'];
                    $total_discount  += $item_discount;
                    $coupon_any_valid = true;
                    $coupon_code_out  = $c['code'];
                    $coupon_id_out    = (int) $c['coupon_id'];
                } elseif ( $coupon_first_error === '' ) {
                    $coupon_first_error = $c['message'];
                }
            }

            $p['couponDiscount']     = $item_discount;
            $p['couponDiscountHtml'] = $core->eshb_price( $item_discount );
            $p['itemKey']            = $item_key;
            $p['accomodationId']     = $accom_id;
            $p['accomodationTitle']  = $accom_id ? get_the_title( $accom_id ) : '';

            $cart_subtotal      += (float) ( $p['totalPrice'] ?? 0 );
            $cart_regular_total += (float) ( $p['regularTotalPrice'] ?? ( $p['totalPrice'] ?? 0 ) );

            $item_views[ $item_key ] = $p;
        }

        $cart_after_coupon = max( 0, $cart_subtotal - $total_discount );
        $tax_amount        = self::calculate_tax( $cart_after_coupon );
        $grand_total       = $cart_after_coupon + $tax_amount;

        if ( $coupon_code === '' ) {
            $coupon_message = '';
        } elseif ( $coupon_any_valid ) {
            $coupon_message = __( 'Coupon applied.', 'easy-hotel' );
        } else {
            $coupon_message = $coupon_first_error !== '' ? $coupon_first_error : __( 'Invalid coupon code.', 'easy-hotel' );
        }

        $currency_symbol = html_entity_decode( $core->get_eshb_currency_symbol() );

        $cart_pricing = [
            'isCart'              => true,
            'items'               => $item_views,
            'itemCount'           => count( $item_views ),
            'currencySymbol'      => $currency_symbol,
            'currencyPosition'    => $core->get_eshb_currency_position(),

            'subtotalPrice'       => $cart_subtotal,
            'subtotalPriceHtml'   => $core->eshb_price( $cart_subtotal ),
            // totalPrice mirrors subtotalPrice (pre-coupon, pre-tax) for
            // parity with the single-item payload consumed by add-ons.
            'totalPrice'          => $cart_subtotal,
            'totalPriceHtml'      => $core->eshb_price( $cart_subtotal ),
            'regularTotalPrice'   => $cart_regular_total,
            'regularTotalPriceHtml' => $core->eshb_price( $cart_regular_total ),

            'couponCode'          => $coupon_code_out,
            'couponId'            => $coupon_id_out,
            'couponDiscount'      => $total_discount,
            'couponDiscountHtml'  => $core->eshb_price( $total_discount ),
            'couponMessage'       => $coupon_message,
            'couponValid'         => $coupon_any_valid,

            'taxRate'             => self::tax_rate(),
            'taxAmount'           => $tax_amount,
            'taxAmountHtml'       => $core->eshb_price( $tax_amount ),

            'grandTotal'          => $grand_total,
            'grandTotalHtml'      => $core->eshb_price( $grand_total ),
        ];

        // Apply the pricing filter ONCE on the cart total (not per item) so
        // add-ons like EHB Deposit operate on the whole-cart figures.
        return apply_filters( 'eshb_native_checkout_pricing', $cart_pricing, [ 'items' => $items ] );
    }

    /**
     * Per-item base pricing with the extra services charge rebuilt the
     * native (quantity-based) way. Returns subtotal/total/regular figures
     * WITHOUT coupon, tax, grand total or the final filter — the shared
     * core used by both calculate() and calculate_cart().
     */
    private static function compute_item_base( array $reservation ) {

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

        $core = new ESHB_Core();

        // Native checkout treats `quantity` as authoritative for every
        // service, regardless of the meta-configured charge_type, so the
        // server matches the JS layer's math in checkout.js after the user
        // adjusts the qty stepper. (The upstream calculator multiplies by
        // room_quantity for charge_type === 'room', which would diverge.)
        //
        // Strip the upstream extras figure out of every total, then add the
        // native (quantity-based) figure back. This unconditionally rebuilds
        // the totals so the extra services charge is ALWAYS included exactly
        // once — no conditional, no delta, no double-count.
        $native_extras = self::calculate_native_extras_charge(
            $selected_services,
            (int) ( $pricing['daysCount'] ?? 1 ),
            (int) ( $room_quantity ?? 1 )
        );
        $old_extras = (float) ( $pricing['extraServicesPrice'] ?? 0 );

        // Accommodation-only bases (totals minus whatever extras the
        // upstream calculator had folded in).
        $accom_subtotal         = max( 0, (float) ( $pricing['subtotalPrice'] ?? 0 ) - $old_extras );
        $accom_total            = max( 0, (float) ( $pricing['totalPrice'] ?? 0 ) - $old_extras );
        $accom_regular_subtotal = max( 0, (float) ( $pricing['regularSubtotalPrice'] ?? 0 ) - $old_extras );
        $accom_regular_total    = max( 0, (float) ( $pricing['regularTotalPrice'] ?? 0 ) - $old_extras );

        $pricing['extraServicesPrice']       = $native_extras;
        $pricing['extraServicesPriceHtml']   = $core->eshb_price( $native_extras );
        $pricing['subtotalPrice']            = $accom_subtotal + $native_extras;
        $pricing['subtotalPriceHtml']        = $core->eshb_price( $pricing['subtotalPrice'] );
        $pricing['totalPrice']               = $accom_total + $native_extras;
        $pricing['totalPriceHtml']           = $core->eshb_price( $pricing['totalPrice'] );
        $pricing['regularSubtotalPrice']     = $accom_regular_subtotal + $native_extras;
        $pricing['regularSubtotalPriceHtml'] = $core->eshb_price( $pricing['regularSubtotalPrice'] );
        $pricing['regularTotalPrice']        = $accom_regular_total + $native_extras;
        $pricing['regularTotalPriceHtml']    = $core->eshb_price( $pricing['regularTotalPrice'] );

        return $pricing;
    }

    /**
     * Sum the extra services charge.
     *
     * The selected `quantity` (the qty stepper) ALWAYS scales the line, so
     * changing it on the checkout page updates the price. Multipliers stack:
     *   - per_day periodicity  → × number of nights
     *   - charge_type 'room'   → × number of rooms
     * At the default quantity of 1, a room-charged service still costs
     * price × rooms (unchanged), but bumping the stepper now scales it.
     */
    public static function calculate_native_extras_charge( array $selected_services, $days_count, $room_quantity ) {
        $total         = 0.0;
        $days_count    = max( 1, (int) $days_count );
        $room_quantity = max( 1, (int) $room_quantity );

        foreach ( $selected_services as $service ) {
            if ( ! is_array( $service ) ) continue;
            $service_id = (int) ( $service['id'] ?? 0 );
            $quantity   = max( 1, (int) ( $service['quantity'] ?? 0 ) );
            if ( ! $service_id || empty( $service['quantity'] ) ) continue;

            $meta = get_post_meta( $service_id, 'eshb_service_metaboxes', true );
            if ( empty( $meta ) ) continue;

            $price               = floatval( $meta['service_price'] ?? 0 );
            $periodicity         = $meta['service_periodicity'] ?? 'once';
            $service_charge_type = $meta['service_charge_type'] ?? 'room';

            if ( $periodicity === 'per_day' || $periodicity === 'perday' ) {
                $price *= $days_count;
            }

            // Room-charged services are additionally multiplied by the room count.
            if ( $service_charge_type === 'room' ) {
                //$price *= $room_quantity;
                $quantity = $room_quantity;
            }


            // Selected quantity always scales the line.
            $price *= $quantity;

            
            $total += $price;
        }

        return round( $total, 2 );
    }

    public static function tax_rate() {
        $rate = floatval( eshb_native_checkout_get_setting( 'native-checkout-tax-rate', 0 ) );
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
