<?php
/**
 * Native Checkout page template.
 *
 * Rendered by ESHB_Native_Checkout::render_shortcode(). Expects these
 * locals to be defined by the caller:
 *   - $reservation       (raw transient payload)
 *   - $reservation_view  (view-model produced by build_reservation_view())
 *   - $pricing           (output of ESHB_Native_Pricing::calculate())
 *   - $gateways          (enabled gateway instances)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset( $reservation_view, $pricing, $gateways ) ) {
    return;
}

// Read-only thank-you lookup; no state change happens here, so a nonce
// is not required. The id is cast through absint() before use.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$booking_id_param = isset( $_GET['booking'] ) ? absint( $_GET['booking'] ) : 0;
if ( $booking_id_param && get_post_type( $booking_id_param ) === 'eshb_booking' ) {
    $booking_meta = get_post_meta( $booking_id_param, 'eshb_booking_metaboxes', true );
    if ( is_array( $booking_meta ) ) :
        $core = new ESHB_Core();
        ?>
        <div class="eshb-native-checkout eshb-native-checkout--thankyou">
            <div class="eshb-container">
                <div class="eshb-card eshb-card--success">
                    <h2><?php esc_html_e( 'Thank you! Your booking is confirmed.', 'easy-hotel' ); ?></h2>
                    <p><?php
                        printf(
                            /* translators: %d: booking id */
                            esc_html__( 'Your booking reference is #%d. A confirmation email has been sent.', 'easy-hotel' ),
                            (int) $booking_id_param
                        );
                    ?></p>
                    <ul class="eshb-thankyou-meta">
                        <li><strong><?php esc_html_e( 'Accommodation:', 'easy-hotel' ); ?></strong> <?php echo esc_html( get_the_title( (int) ( $booking_meta['booking_accomodation_id'] ?? 0 ) ) ); ?></li>
                        <li><strong><?php esc_html_e( 'Check-in:', 'easy-hotel' ); ?></strong> <?php echo esc_html( $booking_meta['booking_start_date'] ?? '' ); ?></li>
                        <li><strong><?php esc_html_e( 'Check-out:', 'easy-hotel' ); ?></strong> <?php echo esc_html( $booking_meta['booking_end_date'] ?? '' ); ?></li>
                        <li><strong><?php esc_html_e( 'Total paid:', 'easy-hotel' ); ?></strong> <?php echo wp_kses_post( $core->eshb_price( (float) ( $booking_meta['total_price'] ?? 0 ) ) ); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
        return;
    endif;
}

$core             = new ESHB_Core();
$currency_symbol  = $core->get_eshb_currency_symbol();
$currency_position = $core->get_eshb_currency_position();
$nights           = (int) ( $pricing['daysCount'] ?? 0 );
?>
<div class="eshb-native-checkout" id="eshbNativeCheckoutRoot">
    <div class="eshb-container">
        <form class="eshb-native-checkout-form" id="eshbNativeCheckoutForm" novalidate>

            <div class="eshb-card">
                <h2><?php esc_html_e( 'Booking Details', 'easy-hotel' ); ?></h2>
                <div class="eshb-grid-2">
                    <div>
                        <div class="eshb-meta-label"><?php esc_html_e( 'Check-in', 'easy-hotel' ); ?></div>
                        <div class="eshb-meta-value">
                            <?php echo esc_html( $reservation_view['start_date_label'] ); ?>
                            <?php if ( $reservation_view['start_time'] ) : ?>
                                <small>(<?php echo esc_html( $reservation_view['start_time'] ); ?>)</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <div class="eshb-meta-label"><?php esc_html_e( 'Check-out', 'easy-hotel' ); ?></div>
                        <div class="eshb-meta-value">
                            <?php echo esc_html( $reservation_view['end_date_label'] ); ?>
                            <?php if ( $reservation_view['end_time'] ) : ?>
                                <small>(<?php echo esc_html( $reservation_view['end_time'] ); ?>)</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <h3><?php esc_html_e( 'Accommodation', 'easy-hotel' ); ?></h3>
                <div class="eshb-form-group eshb-meta-grid">
                    <div><strong><?php esc_html_e( 'Accommodation:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $reservation_view['accomodation_title'] ); ?></span></div>
                    <div><strong><?php esc_html_e( 'Rooms:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $reservation_view['room_quantity'] ); ?></span></div>
                    <div><strong><?php esc_html_e( 'Adults:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $reservation_view['adult_quantity'] ); ?></span></div>
                    <div><strong><?php esc_html_e( 'Children:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $reservation_view['children_quantity'] ); ?></span></div>
                    <div><strong><?php esc_html_e( 'Extra Beds:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $reservation_view['extra_bed_quantity'] ); ?></span></div>
                    <div><strong><?php esc_html_e( 'Nights:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $nights ); ?></span></div>

                    <?php if ( ! empty( $reservation_view['services'] ) ) :
                        // Build initial human-readable summary for the
                        // selected services, identical to what the JS layer
                        // produces in refreshServicesSummary().
                        $initial_summary = [];
                        foreach ( $reservation_view['services'] as $svc ) {
                            $sid = (int) $svc['id'];
                            if ( isset( $reservation_view['selected_services'][ $sid ] ) ) {
                                $qty = (int) $reservation_view['selected_services'][ $sid ];
                                $initial_summary[] = $svc['title'] . ( $qty > 1 ? ' × ' . $qty : '' );
                            }
                        }
                        $initial_summary_text = ! empty( $initial_summary )
                            ? implode( ', ', $initial_summary )
                            : __( 'None selected', 'easy-hotel' );
                    ?>
                    <div class="eshb-services-summary-cell">
                        <strong><?php esc_html_e( 'Additional Services:', 'easy-hotel' ); ?></strong>
                        <span id="eshbServicesSummaryList"><?php echo esc_html( $initial_summary_text ); ?></span>
                        <a href="#" id="eshbServicesEditToggle" class="eshb-edit-link"><?php esc_html_e( 'Edit', 'easy-hotel' ); ?></a>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ( ! empty( $reservation_view['services'] ) ) : ?>
                <div class="eshb-services-editor" id="eshbServicesEditor" hidden>
                    <h3><?php esc_html_e( 'Choose Additional Services', 'easy-hotel' ); ?></h3>
                    <?php foreach ( $reservation_view['services'] as $service ) :
                        $svc_id    = (int) $service['id'];
                        $selected  = isset( $reservation_view['selected_services'][ $svc_id ] );
                        $quantity  = $selected ? (int) $reservation_view['selected_services'][ $svc_id ] : 1;
                        $price_html = $core->eshb_price( $service['price'] );
                        ?>
                        <div class="eshb-choice-option eshb-service-option" data-service-id="<?php echo esc_attr( $svc_id ); ?>" data-service-price="<?php echo esc_attr( $service['price'] ); ?>" data-service-periodicity="<?php echo esc_attr( $service['periodicity'] ); ?>" data-service-charge-type="<?php echo esc_attr( $service['charge_type'] ); ?>">
                            <input type="checkbox" id="eshb-service-<?php echo esc_attr( $svc_id ); ?>" name="eshb-services[]" value="<?php echo esc_attr( $svc_id ); ?>" <?php checked( $selected ); ?>>
                            <label for="eshb-service-<?php echo esc_attr( $svc_id ); ?>">
                                <span class="eshb-choice-title"><?php echo esc_html( $service['title'] ); ?></span>
                                <span class="eshb-choice-meta"><?php echo wp_kses_post( $price_html ); ?>
                                    <?php echo $service['periodicity'] === 'per_day' ? ' / ' . esc_html__( 'day', 'easy-hotel' ) : ''; ?>
                                </span>
                            </label>
                            <div class="eshb-service-qty" style="display:<?php echo $selected ? 'flex' : 'none'; ?>;">
                                <button type="button" class="eshb-qty-btn" data-dir="-1">−</button>
                                <input type="number" min="1" value="<?php echo esc_attr( max( 1, $quantity ) ); ?>" name="eshb-service-qty-<?php echo esc_attr( $svc_id ); ?>" data-service-qty="<?php echo esc_attr( $svc_id ); ?>">
                                <button type="button" class="eshb-qty-btn" data-dir="1">+</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="eshb-card">
                <h2><?php esc_html_e( 'Price Breakdown', 'easy-hotel' ); ?></h2>
                <table class="eshb-price-table">
                    <tr>
                        <td class="eshb-label-col"><?php echo esc_html( $reservation_view['accomodation_title'] ); ?></td>
                        <td class="eshb-value-col" data-eshb-price="basePriceHtml"><?php echo wp_kses_post( $pricing['basePriceHtml'] ?? '' ); ?></td>
                    </tr>
                    <tr>
                        <td class="eshb-label-col"><?php esc_html_e( 'Guests', 'easy-hotel' ); ?></td>
                        <td class="eshb-value-col"><?php echo esc_html( (int) $reservation_view['adult_quantity'] + (int) $reservation_view['children_quantity'] ); ?></td>
                    </tr>
                    <tr>
                        <td class="eshb-label-col"><?php esc_html_e( 'Nights', 'easy-hotel' ); ?></td>
                        <td class="eshb-value-col" data-eshb-nights><?php echo esc_html( $nights ); ?></td>
                    </tr>
                    <tr>
                        <td class="eshb-label-col"><?php esc_html_e( 'Accommodation Total', 'easy-hotel' ); ?></td>
                        <td class="eshb-value-col" data-eshb-price="subtotalPriceHtml"><?php echo wp_kses_post( $pricing['subtotalPriceHtml'] ?? '' ); ?></td>
                    </tr>
                    <tr data-eshb-row="services">
                        <td class="eshb-label-col"><?php esc_html_e( 'Extra Services', 'easy-hotel' ); ?></td>
                        <td class="eshb-value-col" data-eshb-price="extraServicesPriceHtml"><?php echo wp_kses_post( $pricing['extraServicesPriceHtml'] ?? '' ); ?></td>
                    </tr>
                    <tr data-eshb-row="extraBed" <?php if ( empty( $pricing['extraBedPrice'] ) ) echo 'style="display:none;"'; ?>>
                        <td class="eshb-label-col"><?php esc_html_e( 'Extra Bed', 'easy-hotel' ); ?></td>
                        <td class="eshb-value-col" data-eshb-price="extraBedPriceHtml"><?php echo wp_kses_post( $pricing['extraBedPriceHtml'] ?? '' ); ?></td>
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
                </table>

                <?php
                // Show the coupon panel by default only when a coupon is
                // already applied (e.g. user navigated back to the page).
                $coupon_open = ! empty( $pricing['couponCode'] );
                ?>
                <div class="eshb-coupon-section">
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
                <div class="eshb-form-group">
                    <label><?php esc_html_e( 'City', 'easy-hotel' ); ?> *</label>
                    <input type="text" name="city" required>
                </div>
                <div class="eshb-form-group">
                    <label><?php esc_html_e( 'Notes', 'easy-hotel' ); ?></label>
                    <textarea rows="2" name="notes" placeholder="<?php esc_attr_e( 'Special requests, dietary requirements, etc.', 'easy-hotel' ); ?>"></textarea>
                </div>
            </div>

            <div class="eshb-card">
                <h2><?php esc_html_e( 'Payment Method', 'easy-hotel' ); ?></h2>

                <?php if ( empty( $gateways ) ) : ?>
                    <p class="eshb-no-gateways"><?php esc_html_e( 'No payment gateways are configured. Please contact the administrator.', 'easy-hotel' ); ?></p>
                <?php else : ?>
                    <?php foreach ( $gateways as $gateway ) : ?>
                        <div class="eshb-choice-option eshb-payment-option">
                            <input type="radio" id="eshb-pay-<?php echo esc_attr( $gateway->get_id() ); ?>" name="eshbPaymentMethod" value="<?php echo esc_attr( $gateway->get_id() ); ?>">
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
                        '<a href="' . esc_url( apply_filters( 'eshb_native_checkout_terms_url', '#' ) ) . '">' . esc_html__( 'terms and conditions', 'easy-hotel' ) . '</a>'
                    );
                    ?>
                </label>
            </div>

            <p class="eshb-checkout-error" id="eshbCheckoutError" style="display:none;"></p>

            <button type="submit" class="eshb-btn-submit" id="eshbCheckoutSubmit"><?php esc_html_e( 'Book Now', 'easy-hotel' ); ?></button>

        </form>
    </div>
</div>
