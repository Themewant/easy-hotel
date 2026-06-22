<?php
/**
 * Native Checkout page template (multi-accommodation cart).
 *
 * Rendered by ESHB_Native_Checkout::render_shortcode(). Expects:
 *   - $items_view        (list of per-item view-models; each has item_key,
 *                         pricing, services, selected_services, labels…)
 *   - $reservation_view  (first item view — kept for add-on hooks)
 *   - $pricing           (cart pricing from ESHB_Native_Pricing::calculate_cart())
 *   - $gateways          (enabled gateway instances)
 *
 * On the thank-you screen the caller instead provides:
 *   - $group_booking_ids (all booking ids in the completed checkout)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset( $pricing, $gateways ) ) {
    return;
}

// Read-only thank-you lookup; no state change happens here, so a nonce
// is not required. The id is cast through absint() before use.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$booking_id_param = isset( $_GET['booking'] ) ? absint( $_GET['booking'] ) : 0;
if ( $booking_id_param && get_post_type( $booking_id_param ) === 'eshb_booking' ) {

    $core = new ESHB_Core();

    // Every booking created in this checkout (multi-accommodation). Falls
    // back to the single booking when no group context was passed.
    $thankyou_ids = ( isset( $group_booking_ids ) && is_array( $group_booking_ids ) && ! empty( $group_booking_ids ) )
        ? array_map( 'absint', $group_booking_ids )
        : [ $booking_id_param ];

    $grand_total = 0.0;
    $grand_paid  = 0.0;
    ?>
    <div class="eshb-native-checkout eshb-native-checkout--thankyou">
        <div class="eshb-container">
            <div class="eshb-card eshb-card--success">
                <h2><?php esc_html_e( 'Thank you! Your booking is confirmed.', 'easy-hotel' ); ?></h2>
                <p><?php esc_html_e( 'A confirmation email has been sent with your booking details.', 'easy-hotel' ); ?></p>

                <?php foreach ( $thankyou_ids as $tid ) :
                    $booking_meta = get_post_meta( $tid, 'eshb_booking_metaboxes', true );
                    if ( ! is_array( $booking_meta ) ) continue;

                    $ty_total = (float) ( $booking_meta['total_price'] ?? 0 );
                    $ty_paid  = isset( $booking_meta['total_paid'] ) ? (float) $booking_meta['total_paid'] : $ty_total;
                    $ty_due   = max( 0, round( $ty_total - $ty_paid, 2 ) );
                    $grand_total += $ty_total;
                    $grand_paid  += $ty_paid;
                    ?>
                    <ul class="eshb-thankyou-meta">
                        <li><strong><?php esc_html_e( 'Booking reference:', 'easy-hotel' ); ?></strong> #<?php echo esc_html( $tid ); ?></li>
                        <li><strong><?php esc_html_e( 'Accommodation:', 'easy-hotel' ); ?></strong> <?php echo esc_html( get_the_title( (int) ( $booking_meta['booking_accomodation_id'] ?? 0 ) ) ); ?></li>
                        <li><strong><?php esc_html_e( 'Check-in:', 'easy-hotel' ); ?></strong> <?php echo esc_html( $booking_meta['booking_start_date'] ?? '' ); ?></li>
                        <li><strong><?php esc_html_e( 'Check-out:', 'easy-hotel' ); ?></strong> <?php echo esc_html( $booking_meta['booking_end_date'] ?? '' ); ?></li>
                        <?php if ( $ty_due > 0 ) : ?>
                            <li><strong><?php esc_html_e( 'Booking total:', 'easy-hotel' ); ?></strong> <?php echo wp_kses_post( $core->eshb_price( $ty_total ) ); ?></li>
                        <?php endif; ?>
                        <li><strong><?php esc_html_e( 'Amount paid:', 'easy-hotel' ); ?></strong> <?php echo wp_kses_post( $core->eshb_price( $ty_paid ) ); ?></li>
                        <?php if ( $ty_due > 0 ) : ?>
                            <li><strong><?php esc_html_e( 'Due balance:', 'easy-hotel' ); ?></strong> <?php echo wp_kses_post( $core->eshb_price( $ty_due ) ); ?></li>
                        <?php endif; ?>
                    </ul>
                <?php endforeach; ?>

                <?php if ( count( $thankyou_ids ) > 1 ) : ?>
                    <p class="eshb-thankyou-grand">
                        <strong><?php esc_html_e( 'Grand total paid:', 'easy-hotel' ); ?></strong>
                        <?php echo wp_kses_post( $core->eshb_price( $grand_paid ) ); ?>
                    </p>
                <?php endif; ?>

                <?php
                if ( class_exists( 'ESHB_Native_Account' ) ) :
                    $eshb_account_url = ESHB_Native_Account::instance()->get_account_url();
                    if ( $eshb_account_url ) :
                        ?>
                        <p class="eshb-thankyou-actions">
                            <a href="<?php echo esc_url( $eshb_account_url ); ?>" class="eshb-btn-submit">
                                <?php esc_html_e( 'View your bookings', 'easy-hotel' ); ?>
                            </a>
                        </p>
                        <?php
                    endif;
                endif;
                ?>
            </div>
        </div>
    </div>
    <?php
    return;
}

if ( ! isset( $items_view ) || ! is_array( $items_view ) ) {
    $items_view = [];
}

$eshb_settings     = get_option( 'eshb_settings', [] );
$core              = new ESHB_Core();
$currency_symbol   = $core->get_eshb_currency_symbol();
$currency_position = $core->get_eshb_currency_position();
$terms_pid         = $eshb_settings['terms-and-conditions-page'] ?? '';
$terms_url         = $terms_pid ? get_permalink( (int) $terms_pid ) : '#';
$archive_url       = get_post_type_archive_link( 'eshb_accomodation' );
// First item view for the add-on hooks that still expect a single reservation.
$reservation_view  = ! empty( $items_view ) ? $items_view[0] : [];
$multi             = count( $items_view ) > 1;
?>
<div class="eshb-native-checkout" id="eshbNativeCheckoutRoot">
    <div class="eshb-container">
        <?php
        // Cart-blocking hold countdown banner. Hidden by default; the
        // checkout JS reveals it and runs the timer when a hold is active.
        // The markup is shared with the WooCommerce flow.
        if ( ! empty( $eshb_settings['cart-blocking-switcher'] ) && class_exists( 'ESHB_Booking' ) ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the method.
            echo ESHB_Booking::instance()->eshb_cart_block_notice_html( 'inline' );
        }
        ?>
        <form class="eshb-native-checkout-form" id="eshbNativeCheckoutForm" novalidate>

            <div class="eshb-card eshb-cart-items-card">
                <h2><?php echo $multi
                    ? esc_html__( 'Your Accommodations', 'easy-hotel' )
                    : esc_html__( 'Booking Details', 'easy-hotel' ); ?></h2>

                <?php foreach ( $items_view as $item ) :
                    $item_key   = $item['item_key'] ?? '';
                    $item_price = isset( $item['pricing'] ) && is_array( $item['pricing'] ) ? $item['pricing'] : [];
                    $i_nights   = (int) ( $item_price['daysCount'] ?? 0 );
                    ?>
                    <div class="eshb-cart-item" data-item-key="<?php echo esc_attr( $item_key ); ?>">
                        <div class="eshb-cart-item-head">
                            <h3 class="eshb-cart-item-title"><?php echo esc_html( $item['accomodation_title'] ); ?></h3>
                            <div class="eshb-cart-item-aside">
                                <span class="eshb-cart-item-total" data-eshb-item-total="<?php echo esc_attr( $item_key ); ?>"><?php echo wp_kses_post( $item_price['totalPriceHtml'] ?? '' ); ?></span>
                                <button type="button" class="eshb-remove-item" data-item-key="<?php echo esc_attr( $item_key ); ?>" aria-label="<?php esc_attr_e( 'Remove', 'easy-hotel' ); ?>">&times;</button>
                            </div>
                        </div>

                        <div class="eshb-grid-2">
                            <div>
                                <div class="eshb-meta-label"><?php esc_html_e( 'Check-in', 'easy-hotel' ); ?></div>
                                <div class="eshb-meta-value">
                                    <?php echo esc_html( $item['start_date_label'] ); ?>
                                    <?php if ( ! empty( $item['start_time'] ) ) : ?>
                                        <small>(<?php echo esc_html( $item['start_time'] ); ?>)</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <div class="eshb-meta-label"><?php esc_html_e( 'Check-out', 'easy-hotel' ); ?></div>
                                <div class="eshb-meta-value">
                                    <?php echo esc_html( $item['end_date_label'] ); ?>
                                    <?php if ( ! empty( $item['end_time'] ) ) : ?>
                                        <small>(<?php echo esc_html( $item['end_time'] ); ?>)</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="eshb-form-group eshb-meta-grid">
                            <div><strong><?php esc_html_e( 'Rooms:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $item['room_quantity'] ); ?></span></div>
                            <div><strong><?php esc_html_e( 'Adults:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $item['adult_quantity'] ); ?></span></div>
                            <div><strong><?php esc_html_e( 'Children:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $item['children_quantity'] ); ?></span></div>
                            <div><strong><?php esc_html_e( 'Extra Beds:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $item['extra_bed_quantity'] ); ?></span></div>
                            <div><strong><?php esc_html_e( 'Nights:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $i_nights ); ?></span></div>

                            <?php if ( ! empty( $item['services'] ) ) :
                                $initial_summary = [];
                                foreach ( $item['services'] as $svc ) {
                                    $sid = (int) $svc['id'];
                                    if ( isset( $item['selected_services'][ $sid ] ) ) {
                                        $qty = (int) $item['selected_services'][ $sid ];
                                        $initial_summary[] = $svc['title'] . ( $qty > 1 ? ' × ' . $qty : '' );
                                    }
                                }
                                $initial_summary_text = ! empty( $initial_summary )
                                    ? implode( ', ', $initial_summary )
                                    : __( 'None selected', 'easy-hotel' );
                                ?>
                                <div class="eshb-services-summary-cell">
                                    <strong><?php esc_html_e( 'Additional Services:', 'easy-hotel' ); ?></strong>
                                    <span class="eshb-services-summary-list"><?php echo esc_html( $initial_summary_text ); ?></span>
                                    <a href="#" class="eshb-edit-link eshb-services-edit-toggle"><?php esc_html_e( 'Edit', 'easy-hotel' ); ?></a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ( ! empty( $item['services'] ) ) : ?>
                            <div class="eshb-services-editor" hidden>
                                <h4><?php esc_html_e( 'Choose Additional Services', 'easy-hotel' ); ?></h4>
                                <?php foreach ( $item['services'] as $service ) :
                                    $svc_id     = (int) $service['id'];
                                    $selected   = isset( $item['selected_services'][ $svc_id ] );
                                    $quantity   = $selected ? (int) $item['selected_services'][ $svc_id ] : 1;
                                    $price_html = $core->eshb_price( $service['price'] );
                                    $field_id   = 'eshb-service-' . esc_attr( $item_key ) . '-' . $svc_id;
                                    ?>
                                    <div class="eshb-choice-option eshb-service-option" data-service-id="<?php echo esc_attr( $svc_id ); ?>" data-service-price="<?php echo esc_attr( $service['price'] ); ?>" data-service-periodicity="<?php echo esc_attr( $service['periodicity'] ); ?>" data-service-charge-type="<?php echo esc_attr( $service['charge_type'] ); ?>">
                                        <input type="checkbox" id="<?php echo $field_id; ?>" value="<?php echo esc_attr( $svc_id ); ?>" <?php checked( $selected ); ?>>
                                        <label for="<?php echo $field_id; ?>">
                                            <span class="eshb-choice-title"><?php echo esc_html( $service['title'] ); ?></span>
                                            <span class="eshb-choice-meta"><?php echo wp_kses_post( $price_html ); ?>
                                                <?php echo $service['periodicity'] === 'per_day' ? ' / ' . esc_html__( 'day', 'easy-hotel' ) : ''; ?>
                                            </span>
                                        </label>
                                        <div class="eshb-service-qty" style="display:<?php echo $selected ? 'flex' : 'none'; ?>;">
                                            <button type="button" class="eshb-qty-btn" data-dir="-1">&minus;</button>
                                            <input type="number" min="1" value="<?php echo esc_attr( max( 1, $quantity ) ); ?>" data-service-qty="<?php echo esc_attr( $svc_id ); ?>">
                                            <button type="button" class="eshb-qty-btn" data-dir="1">+</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="eshb-card">
                <h2><?php esc_html_e( 'Price Breakdown', 'easy-hotel' ); ?></h2>
                <table class="eshb-price-table">
                    <tr>
                        <td class="eshb-label-col"><?php esc_html_e( 'Subtotal', 'easy-hotel' ); ?></td>
                        <td class="eshb-value-col" data-eshb-price="subtotalPriceHtml"><?php echo wp_kses_post( $pricing['subtotalPriceHtml'] ?? '' ); ?></td>
                    </tr>
                    <tr data-eshb-row="coupon" <?php if ( empty( $pricing['couponDiscount'] ) ) echo 'style="display:none;"'; ?>>
                        <td class="eshb-label-col"><?php esc_html_e( 'Coupon Discount', 'easy-hotel' ); ?> <span data-eshb-coupon-code><?php echo esc_html( $pricing['couponCode'] ?? '' ); ?></span></td>
                        <td class="eshb-value-col" data-eshb-price="couponDiscountHtml">- <?php echo wp_kses_post( $pricing['couponDiscountHtml'] ?? '' ); ?></td>
                    </tr>
                    <tr data-eshb-row="tax" <?php if ( empty( $pricing['taxAmount'] ) ) echo 'style="display:none;"'; ?>>
                        <td class="eshb-label-col"><?php esc_html_e( 'Tax', 'easy-hotel' ); ?> (<span data-eshb-tax-rate><?php echo esc_html( $pricing['taxRate'] ?? 0 ); ?></span>%)</td>
                        <td class="eshb-value-col" data-eshb-price="taxAmountHtml"><?php echo wp_kses_post( $pricing['taxAmountHtml'] ?? '' ); ?></td>
                    </tr>
                    <tr class="eshb-total-row">
                        <td><?php esc_html_e( 'Total', 'easy-hotel' ); ?></td>
                        <td class="eshb-value-col" data-eshb-price="grandTotalHtml"><?php echo wp_kses_post( $pricing['grandTotalHtml'] ?? '' ); ?></td>
                    </tr>
                    <?php
                    /**
                     * Fires inside the price-breakdown table just after the
                     * Total row. Use this to append extra <tr> rows (deposit,
                     * due, etc.) without forking the template.
                     *
                     * @param array $pricing          Cart pricing payload.
                     * @param array $reservation_view First item view-model.
                     */
                    do_action( 'eshb_native_checkout_after_price_total', $pricing, $reservation_view );
                    ?>
                </table>

                <?php
                // Show the coupon panel by default only when a coupon is
                // already applied (e.g. user navigated back to the page).
                $coupon_open = ! empty( $pricing['couponCode'] );
                ?>
                <div class="eshb-coupon-section">
                    <div class="eshb-order-review-actions">
                        <?php if ( $archive_url ) : ?>
                            <a href="<?php echo esc_url( $archive_url ); ?>" class="eshb-add-more-btn">
                                + <?php esc_html_e( 'Add accommodation', 'easy-hotel' ); ?>
                            </a>
                        <?php endif; ?>

                        <div class="eshb-coupon-area">
                            <p class="eshb-coupon-prompt"<?php echo $coupon_open ? ' hidden' : ''; ?>>
                                <?php esc_html_e( 'Do you have coupon?', 'easy-hotel' ); ?>
                                <a href="#" id="eshbCouponToggle" aria-expanded="<?php echo $coupon_open ? 'true' : 'false'; ?>" aria-controls="eshbCouponPanel"><?php esc_html_e( 'Apply', 'easy-hotel' ); ?></a>
                            </p>
                            <div class="eshb-coupon-panel" id="eshbCouponPanel"<?php echo $coupon_open ? '' : ' hidden'; ?>>
                                <div class="eshb-coupon-row">
                                    <input type="text" id="eshbCouponCode" placeholder="<?php esc_attr_e( 'Enter coupon code', 'easy-hotel' ); ?>" value="<?php echo esc_attr( $pricing['couponCode'] ?? '' ); ?>" <?php disabled( $coupon_open ); ?>>
                                    <button type="button" id="eshbApplyCoupon" class="eshb-btn-secondary" style="display:<?php echo $coupon_open ? 'none' : 'inline-block'; ?>;"><?php esc_html_e( 'Apply', 'easy-hotel' ); ?></button>
                                    <button type="button" id="eshbRemoveCoupon" class="eshb-btn-link" style="display:<?php echo $coupon_open ? 'inline-block' : 'none'; ?>;"><?php esc_html_e( 'Remove', 'easy-hotel' ); ?></button>
                                </div>
                                <p class="eshb-coupon-message" id="eshbCouponMessage"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="eshb-card">
                <h2><?php esc_html_e( 'Your Information', 'easy-hotel' ); ?></h2>
                <div class="eshb-grid-2">
                    <div class="eshb-form-group">
                        <label><?php esc_html_e( 'First Name', 'easy-hotel' ); ?> *</label>
                        <input type="text" name="firstName" required>
                    </div>
                    <div class="eshb-form-group">
                        <label><?php esc_html_e( 'Last Name', 'easy-hotel' ); ?> *</label>
                        <input type="text" name="lastName" required>
                    </div>
                </div>
                <div class="eshb-grid-2">
                    <div class="eshb-form-group">
                        <label><?php esc_html_e( 'Email', 'easy-hotel' ); ?> *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="eshb-form-group">
                        <label><?php esc_html_e( 'Phone', 'easy-hotel' ); ?> *</label>
                        <input type="text" name="phone" required>
                    </div>
                </div>
                <div class="eshb-grid-2">
                    <div class="eshb-form-group">
                        <label><?php esc_html_e( 'Country / Region', 'easy-hotel' ); ?> *</label>
                        <select name="country" id="eshbCountrySelect" required>
                            <option value=""><?php esc_html_e( 'Select a country…', 'easy-hotel' ); ?></option>
                        </select>
                    </div>
                    <div class="eshb-form-group" id="eshbStateGroup">
                        <label><?php esc_html_e( 'State', 'easy-hotel' ); ?> *</label>
                        <select name="state" id="eshbStateSelect" required disabled>
                            <option value=""><?php esc_html_e( 'Select a state…', 'easy-hotel' ); ?></option>
                        </select>
                    </div>
                </div>
                <div class="eshb-grid-2">
                    <div class="eshb-form-group">
                        <label><?php esc_html_e( 'City', 'easy-hotel' ); ?> *</label>
                        <input type="text" name="city" required>
                    </div>
                    <div class="eshb-form-group">
                        <label><?php esc_html_e( 'Postal Code / ZIP', 'easy-hotel' ); ?></label>
                        <input type="text" name="postcode">
                    </div>
                </div>
                <div class="eshb-form-group">
                    <label><?php esc_html_e( 'Notes', 'easy-hotel' ); ?></label>
                    <textarea rows="2" name="notes" placeholder="<?php esc_attr_e( 'Special requests, dietary requirements, etc.', 'easy-hotel' ); ?>"></textarea>
                </div>
            </div>

            <?php
            /**
             * Fires before the Payment Method card. Extensions use this to
             * inject extra UI (e.g. the EHB Deposit add-on renders the
             * "Pay Deposit / Pay Full" radio selector here).
             *
             * @param array $pricing          Cart pricing payload.
             * @param array $reservation_view First item view-model.
             */
            do_action( 'eshb_native_checkout_payment_option', $pricing, $reservation_view );
            ?>

            <div class="eshb-card">
                <h2><?php esc_html_e( 'Payment Method', 'easy-hotel' ); ?></h2>

                <?php if ( empty( $gateways ) ) : ?>
                    <p class="eshb-no-gateways"><?php esc_html_e( 'No payment gateways are configured. Please contact the administrator.', 'easy-hotel' ); ?></p>
                <?php
                else :
                    // Decide which gateway is selected by default:
                    //   - if only one is enabled, it is the default;
                    //   - otherwise prefer Cash on Delivery;
                    //   - failing that, the first enabled gateway.
                    $eshb_gateway_ids = array_values( array_map( function ( $gw ) { return $gw->get_id(); }, $gateways ) );
                    if ( count( $eshb_gateway_ids ) === 1 ) {
                        $eshb_default_gateway = $eshb_gateway_ids[0];
                    } elseif ( in_array( 'cod', $eshb_gateway_ids, true ) ) {
                        $eshb_default_gateway = 'cod';
                    } else {
                        $eshb_default_gateway = $eshb_gateway_ids[0] ?? '';
                    }
                    ?>
                    <?php foreach ( $gateways as $gateway ) : ?>
                        <div class="eshb-choice-option eshb-payment-option">
                            <input type="radio" id="eshb-pay-<?php echo esc_attr( $gateway->get_id() ); ?>" name="eshbPaymentMethod" value="<?php echo esc_attr( $gateway->get_id() ); ?>" <?php checked( $eshb_default_gateway, $gateway->get_id() ); ?>>
                            <label for="eshb-pay-<?php echo esc_attr( $gateway->get_id() ); ?>" class="eshb-choice-title"><?php echo esc_html( $gateway->get_title() ); ?></label>
                            <?php if ( $gateway->get_description() ) : ?>
                                <div class="eshb-choice-desc"><?php echo esc_html( $gateway->get_description() ); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="eshb-gateway-mount" id="eshbGatewayMount">
                        <div data-gateway="paypal" id="eshbPayPalButtons" style="display:none;"></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="eshb-terms-container">
                <input type="checkbox" id="eshbTerms" name="terms" required>
                <label for="eshbTerms">
                    <?php
                    printf(
                        /* translators: %s: terms link */
                        esc_html__( 'I have read and accept the %s.', 'easy-hotel' ),
                        '<a href="' . esc_url( apply_filters( 'eshb_native_checkout_terms_url', $terms_url ) ) . '" target="_blank">' . esc_html__( 'terms and conditions', 'easy-hotel' ) . '</a>'
                    );
                    ?>
                </label>
            </div>

            <p class="eshb-checkout-error" id="eshbCheckoutError" style="display:none;"></p>

            <button type="submit" class="eshb-btn-submit" id="eshbCheckoutSubmit"><?php esc_html_e( 'Book Now', 'easy-hotel' ); ?></button>

        </form>
    </div>
</div>
