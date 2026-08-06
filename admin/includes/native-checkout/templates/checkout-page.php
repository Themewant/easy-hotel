<?php
/**
 * Native Checkout page template (multi-accommodation cart).
 *
 * Rendered by ESHB_Native_Checkout::render_shortcode(). Expects:
 *   - $items_view        (list of per-item view-models; each has item_key,
 *                         pricing, services, selected_services, labels…)
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


// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$eshb_booking_id_param = isset( $_GET['booking'] ) ? absint( $_GET['booking'] ) : 0;
if ( $eshb_booking_id_param && get_post_type( $eshb_booking_id_param ) === 'eshb_booking' ) {

    $eshb_core = new ESHB_Core();

    $eshb_thankyou_ids = ( isset( $group_booking_ids ) && is_array( $group_booking_ids ) && ! empty( $group_booking_ids ) )
        ? array_map( 'absint', $group_booking_ids )
        : [ $eshb_booking_id_param ];

    $eshb_grand_total = 0.0;
    $eshb_grand_paid  = 0.0;
    ?>
    <div class="eshb-native-checkout eshb-native-checkout--thankyou">
        <div class="eshb-container">
            <div class="eshb-card eshb-card--success">
                <h2><?php esc_html_e( 'Thank you! Your booking is confirmed.', 'easy-hotel' ); ?></h2>
                <p><?php esc_html_e( 'A confirmation email has been sent with your booking details.', 'easy-hotel' ); ?></p>

                <?php foreach ( $eshb_thankyou_ids as $eshb_tid ) :
                    $eshb_booking_meta = get_post_meta( $eshb_tid, 'eshb_booking_metaboxes', true );
                    if ( ! is_array( $eshb_booking_meta ) ) continue;

                    $eshb_ty_total = (float) ( $eshb_booking_meta['total_price'] ?? 0 );
                    $eshb_ty_paid  = isset( $eshb_booking_meta['total_paid'] ) ? (float) $eshb_booking_meta['total_paid'] : $eshb_ty_total;
                    $eshb_ty_due   = max( 0, round( $eshb_ty_total - $eshb_ty_paid, 2 ) );
                    $eshb_grand_total += $eshb_ty_total;
                    $eshb_grand_paid  += $eshb_ty_paid;
                    ?>
                    <ul class="eshb-thankyou-meta">
                        <li><strong><?php esc_html_e( 'Booking reference:', 'easy-hotel' ); ?></strong> #<?php echo esc_html( $eshb_tid ); ?></li>
                        <li><strong><?php esc_html_e( 'Accommodation:', 'easy-hotel' ); ?></strong> <?php echo esc_html( get_the_title( (int) ( $eshb_booking_meta['booking_accomodation_id'] ?? 0 ) ) ); ?></li>
                        <li><strong><?php esc_html_e( 'Check-in:', 'easy-hotel' ); ?></strong> <?php echo esc_html( $eshb_booking_meta['booking_start_date'] ?? '' ); ?></li>
                        <li><strong><?php esc_html_e( 'Check-out:', 'easy-hotel' ); ?></strong> <?php echo esc_html( $eshb_booking_meta['booking_end_date'] ?? '' ); ?></li>
                        <?php if ( $eshb_ty_due > 0 ) : ?>
                            <li><strong><?php esc_html_e( 'Booking total:', 'easy-hotel' ); ?></strong> <?php echo wp_kses_post( $eshb_core->eshb_price( $eshb_ty_total ) ); ?></li>
                        <?php endif; ?>
                        <li><strong><?php esc_html_e( 'Amount paid:', 'easy-hotel' ); ?></strong> <?php echo wp_kses_post( $eshb_core->eshb_price( $eshb_ty_paid ) ); ?></li>
                        <?php if ( $eshb_ty_due > 0 ) : ?>
                            <li><strong><?php esc_html_e( 'Due balance:', 'easy-hotel' ); ?></strong> <?php echo wp_kses_post( $eshb_core->eshb_price( $eshb_ty_due ) ); ?></li>
                        <?php endif; ?>
                    </ul>
                <?php endforeach; ?>

                <?php if ( count( $eshb_thankyou_ids ) > 1 ) : ?>
                    <p class="eshb-thankyou-grand">
                        <strong><?php esc_html_e( 'Grand total paid:', 'easy-hotel' ); ?></strong>
                        <?php echo wp_kses_post( $eshb_core->eshb_price( $eshb_grand_paid ) ); ?>
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

// $items_view is supplied by the caller (ESHB_Native_Checkout::render_shortcode).
$eshb_items_view = ( isset( $items_view ) && is_array( $items_view ) ) ? $items_view : [];

$eshb_settings          = get_option( 'eshb_settings', [] );
$eshb_core              = new ESHB_Core();
$eshb_currency_symbol   = $eshb_core->get_eshb_currency_symbol();
$eshb_currency_position = $eshb_core->get_eshb_currency_position();
$eshb_terms_pid         = $eshb_settings['terms-and-conditions-page'] ?? '';
$eshb_terms_url         = $eshb_terms_pid ? get_permalink( eshb_native_checkout_translated_page_id( $eshb_terms_pid ) ) : '#';
$eshb_archive_url       = get_post_type_archive_link( 'eshb_accomodation' );
// First item view for the add-on hooks that still expect a single reservation.
$eshb_reservation_view  = ! empty( $eshb_items_view ) ? $eshb_items_view[0] : [];
$eshb_multi             = count( $eshb_items_view ) > 1;
?>
<div class="eshb-native-checkout" id="eshbNativeCheckoutRoot">
    <div class="eshb-container">
        <?php
        if ( ! empty( $eshb_settings['cart-blocking-switcher'] ) && class_exists( 'ESHB_Booking' ) ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the method.
            echo ESHB_Booking::instance()->eshb_cart_block_notice_html( 'inline' );
        }
        ?>
        <form class="eshb-native-checkout-form" id="eshbNativeCheckoutForm" novalidate>

            <div class="eshb-card eshb-cart-items-card">
                <h2><?php echo $eshb_multi
                    ? esc_html__( 'Your Accommodations', 'easy-hotel' )
                    : esc_html__( 'Booking Details', 'easy-hotel' ); ?></h2>

                <?php foreach ( $eshb_items_view as $eshb_item ) :
                    $eshb_item_key   = $eshb_item['item_key'] ?? '';
                    $eshb_item_price = isset( $eshb_item['pricing'] ) && is_array( $eshb_item['pricing'] ) ? $eshb_item['pricing'] : [];
                    $eshb_i_nights   = (int) ( $eshb_item_price['daysCount'] ?? 0 );
                    ?>
                    <div class="eshb-cart-item" data-item-key="<?php echo esc_attr( $eshb_item_key ); ?>">
                        <div class="eshb-cart-item-head">
                            <h3 class="eshb-cart-item-title"><?php echo esc_html( $eshb_item['accomodation_title'] ); ?></h3>
                            <div class="eshb-cart-item-aside">
                                <span class="eshb-cart-item-total" data-eshb-item-total="<?php echo esc_attr( $eshb_item_key ); ?>"><?php echo wp_kses_post( $eshb_item_price['totalPriceHtml'] ?? '' ); ?></span>
                                <button type="button" class="eshb-remove-item" data-item-key="<?php echo esc_attr( $eshb_item_key ); ?>" aria-label="<?php esc_attr_e( 'Remove', 'easy-hotel' ); ?>">&times;</button>
                            </div>
                        </div>

                        <div class="eshb-grid-2">
                            <div>
                                <div class="eshb-meta-label"><?php esc_html_e( 'Check-in', 'easy-hotel' ); ?></div>
                                <div class="eshb-meta-value">
                                    <?php echo esc_html( $eshb_item['start_date_label'] ); ?>
                                    <?php if ( ! empty( $eshb_item['start_time'] ) ) : ?>
                                        <small>(<?php echo esc_html( $eshb_item['start_time'] ); ?>)</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <div class="eshb-meta-label"><?php esc_html_e( 'Check-out', 'easy-hotel' ); ?></div>
                                <div class="eshb-meta-value">
                                    <?php echo esc_html( $eshb_item['end_date_label'] ); ?>
                                    <?php if ( ! empty( $eshb_item['end_time'] ) ) : ?>
                                        <small>(<?php echo esc_html( $eshb_item['end_time'] ); ?>)</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="eshb-form-group eshb-meta-grid">
                            <div><strong><?php esc_html_e( 'Rooms:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $eshb_item['room_quantity'] ); ?></span></div>
                            <div><strong><?php esc_html_e( 'Adults:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $eshb_item['adult_quantity'] ); ?></span></div>
                            <div><strong><?php esc_html_e( 'Children:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $eshb_item['children_quantity'] ); ?></span></div>
                            <div><strong><?php esc_html_e( 'Extra Beds:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $eshb_item['extra_bed_quantity'] ); ?></span></div>
                            <div><strong><?php esc_html_e( 'Nights:', 'easy-hotel' ); ?></strong> <span><?php echo esc_html( $eshb_i_nights ); ?></span></div>

                            <?php if ( ! empty( $eshb_item['services'] ) ) :
                                $eshb_initial_summary = [];
                                foreach ( $eshb_item['services'] as $eshb_svc ) {
                                    $eshb_sid = (int) $eshb_svc['id'];
                                    if ( isset( $eshb_item['selected_services'][ $eshb_sid ] ) ) {
                                        $eshb_qty = (int) $eshb_item['selected_services'][ $eshb_sid ];
                                        $eshb_initial_summary[] = $eshb_svc['title'] . ( $eshb_qty > 1 ? ' × ' . $eshb_qty : '' );
                                    }
                                }
                                $eshb_initial_summary_text = ! empty( $eshb_initial_summary )
                                    ? implode( ', ', $eshb_initial_summary )
                                    : __( 'None selected', 'easy-hotel' );
                                ?>
                                <div class="eshb-services-summary-cell">
                                    <strong><?php esc_html_e( 'Additional Services:', 'easy-hotel' ); ?></strong>
                                    <span class="eshb-services-summary-list"><?php echo esc_html( $eshb_initial_summary_text ); ?></span>
                                    <a href="#" class="eshb-edit-link eshb-services-edit-toggle"><?php esc_html_e( 'Edit', 'easy-hotel' ); ?></a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ( ! empty( $eshb_item['services'] ) ) : ?>
                            <div class="eshb-services-editor" hidden>
                                <h4><?php esc_html_e( 'Choose Additional Services', 'easy-hotel' ); ?></h4>
                                <?php foreach ( $eshb_item['services'] as $eshb_service ) :
                                    $eshb_svc_id     = (int) $eshb_service['id'];
                                    $eshb_selected   = isset( $eshb_item['selected_services'][ $eshb_svc_id ] );
                                    $eshb_quantity   = $eshb_selected ? (int) $eshb_item['selected_services'][ $eshb_svc_id ] : 1;
                                    $eshb_price_html = $eshb_core->eshb_price( $eshb_service['price'] );
                                    $eshb_field_id   = 'eshb-service-' . esc_attr( $eshb_item_key ) . '-' . $eshb_svc_id;
                                    // 0 means no cap was configured for this service.
                                    $eshb_max_qty    = (int) ( $eshb_service['max_quantity'] ?? 0 );
                                    ?>
                                    <div class="eshb-choice-option eshb-service-option" data-service-id="<?php echo esc_attr( $eshb_svc_id ); ?>" data-service-price="<?php echo esc_attr( $eshb_service['price'] ); ?>" data-service-periodicity="<?php echo esc_attr( $eshb_service['periodicity'] ); ?>" data-service-charge-type="<?php echo esc_attr( $eshb_service['charge_type'] ); ?>" data-service-max-qty="<?php echo esc_attr( $eshb_max_qty ); ?>">
                                        <input type="checkbox" id="<?php echo esc_attr( $eshb_field_id ); ?>" value="<?php echo esc_attr( $eshb_svc_id ); ?>" <?php checked( $eshb_selected ); ?>>
                                        <label for="<?php echo esc_attr( $eshb_field_id ); ?>">
                                            <span class="eshb-choice-title"><?php echo esc_html( $eshb_service['title'] ); ?></span>
                                            <span class="eshb-choice-meta"><?php echo wp_kses_post( $eshb_price_html ); ?>
                                                <?php echo $eshb_service['periodicity'] === 'per_day' ? ' / ' . esc_html__( 'day', 'easy-hotel' ) : ''; ?>
                                            </span>
                                        </label>
                                        <div class="eshb-service-qty" style="display:<?php echo $eshb_selected ? 'flex' : 'none'; ?>;">
                                            <button type="button" class="eshb-qty-btn" data-dir="-1">&minus;</button>
                                            <input type="number" min="1"<?php echo $eshb_max_qty > 0 ? ' max="' . esc_attr( $eshb_max_qty ) . '"' : ''; ?> value="<?php echo esc_attr( $eshb_max_qty > 0 ? min( $eshb_max_qty, max( 1, $eshb_quantity ) ) : max( 1, $eshb_quantity ) ); ?>" data-service-qty="<?php echo esc_attr( $eshb_svc_id ); ?>">
                                            <button type="button" class="eshb-qty-btn" data-dir="1">+</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php
            /**
             * Fires before the Payment Method card. Extensions use this to
             * inject extra UI (e.g. the EHB Deposit add-on renders the
             * "Pay Deposit / Pay Full" radio selector here).
             *
             * @param array $pricing          Cart pricing payload.
             * @param array $eshb_reservation_view First item view-model.
             */
            do_action( 'eshb_native_checkout_payment_option', $pricing, $eshb_reservation_view );
            ?>

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
                     * @param array $eshb_reservation_view First item view-model.
                     */
                    do_action( 'eshb_native_checkout_after_price_total', $pricing, $eshb_reservation_view );
                    ?>
                </table>

                <?php
                $eshb_coupon_open = ! empty( $pricing['couponCode'] );
                ?>
                <div class="eshb-coupon-section">
                    <div class="eshb-order-review-actions">
                        <?php if ( $eshb_archive_url ) : ?>
                            <a href="<?php echo esc_url( $eshb_archive_url ); ?>" class="eshb-add-more-btn">
                                + <?php esc_html_e( 'Add accommodation', 'easy-hotel' ); ?>
                            </a>
                        <?php endif; ?>

                        <div class="eshb-coupon-area">
                            <p class="eshb-coupon-prompt"<?php echo $eshb_coupon_open ? ' hidden' : ''; ?>>
                                <?php esc_html_e( 'Do you have coupon?', 'easy-hotel' ); ?>
                                <a href="#" id="eshbCouponToggle" aria-expanded="<?php echo $eshb_coupon_open ? 'true' : 'false'; ?>" aria-controls="eshbCouponPanel"><?php esc_html_e( 'Apply', 'easy-hotel' ); ?></a>
                            </p>
                            <div class="eshb-coupon-panel" id="eshbCouponPanel"<?php echo $eshb_coupon_open ? '' : ' hidden'; ?>>
                                <div class="eshb-coupon-row">
                                    <input type="text" id="eshbCouponCode" placeholder="<?php esc_attr_e( 'Enter coupon code', 'easy-hotel' ); ?>" value="<?php echo esc_attr( $pricing['couponCode'] ?? '' ); ?>" <?php disabled( $eshb_coupon_open ); ?>>
                                    <button type="button" id="eshbApplyCoupon" class="eshb-btn-secondary" style="display:<?php echo $eshb_coupon_open ? 'none' : 'inline-block'; ?>;"><?php esc_html_e( 'Apply', 'easy-hotel' ); ?></button>
                                    <button type="button" id="eshbRemoveCoupon" class="eshb-btn-link" style="display:<?php echo $eshb_coupon_open ? 'inline-block' : 'none'; ?>;"><?php esc_html_e( 'Remove', 'easy-hotel' ); ?></button>
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
                    <?php foreach ( $gateways as $eshb_gateway ) : ?>
                        <div class="eshb-choice-option eshb-payment-option">
                            <input type="radio" id="eshb-pay-<?php echo esc_attr( $eshb_gateway->get_id() ); ?>" name="eshbPaymentMethod" value="<?php echo esc_attr( $eshb_gateway->get_id() ); ?>" <?php checked( $eshb_default_gateway, $eshb_gateway->get_id() ); ?>>
                            <label for="eshb-pay-<?php echo esc_attr( $eshb_gateway->get_id() ); ?>" class="eshb-choice-title"><?php echo esc_html( $eshb_gateway->get_title() ); ?></label>
                            <?php if ( $eshb_gateway->get_description() ) : ?>
                                <div class="eshb-choice-desc"><?php echo esc_html( $eshb_gateway->get_description() ); ?></div>
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
                        '<a href="' . esc_url( apply_filters( 'eshb_native_checkout_terms_url', $eshb_terms_url ) ) . '" target="_blank">' . esc_html__( 'terms and conditions', 'easy-hotel' ) . '</a>'
                    );
                    ?>
                </label>
            </div>

            <p class="eshb-checkout-error" id="eshbCheckoutError" style="display:none;"></p>

            <button type="submit" class="eshb-btn-submit" id="eshbCheckoutSubmit"><?php esc_html_e( 'Book Now', 'easy-hotel' ); ?></button>

        </form>
    </div>
</div>
