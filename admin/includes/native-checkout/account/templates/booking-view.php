<?php
/**
 * Booking detail view rendered inside the account modal (via AJAX).
 *
 * @var array $b Detail view-model from ESHB_Native_Account_Bookings::get_detail_view().
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( empty( $b ) || empty( $b['id'] ) ) {
    echo '<p>' . esc_html__( 'Booking not found.', 'easy-hotel' ) . '</p>';
    return;
}
?>
<div class="eshb-account-booking-detail">
    <div class="eshb-account-detail-head">
        <h3><?php
            /* translators: %d: booking ID */
            printf( esc_html__( 'Booking #%d', 'easy-hotel' ), (int) $b['id'] );
        ?></h3>
        <span class="eshb-badge eshb-badge--<?php echo esc_attr( $b['status'] ); ?>"><?php echo esc_html( $b['status_label'] ); ?></span>
    </div>

    <h4 class="eshb-account-detail-title"><?php esc_html_e( 'Booking information', 'easy-hotel' ); ?></h4>
    <table class="eshb-account-detail-table">
        <tr><th><?php esc_html_e( 'Accomodation', 'easy-hotel' ); ?></th><td><?php echo esc_html( $b['accomodation'] ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Check-in', 'easy-hotel' ); ?></th><td><?php echo esc_html( trim( $b['check_in_label'] . ' ' . $b['check_in_time'] ) ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Check-out', 'easy-hotel' ); ?></th><td><?php echo esc_html( trim( $b['check_out_label'] . ' ' . $b['check_out_time'] ) ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Rooms', 'easy-hotel' ); ?></th><td><?php echo esc_html( $b['room_quantity'] ); ?></td></tr>
    </table>

    <h4 class="eshb-account-detail-title"><?php esc_html_e( 'Guests', 'easy-hotel' ); ?></h4>
    <table class="eshb-account-detail-table">
        <tr><th><?php esc_html_e( 'Adults', 'easy-hotel' ); ?></th><td><?php echo esc_html( $b['adults'] ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Children', 'easy-hotel' ); ?></th><td><?php echo esc_html( $b['children'] ); ?></td></tr>
        <?php if ( $b['extra_beds'] > 0 ) : ?>
            <tr><th><?php esc_html_e( 'Extra beds', 'easy-hotel' ); ?></th><td><?php echo esc_html( $b['extra_beds'] ); ?></td></tr>
        <?php endif; ?>
    </table>

    <h4 class="eshb-account-detail-title"><?php esc_html_e( 'Guest details', 'easy-hotel' ); ?></h4>
    <table class="eshb-account-detail-table">
        <tr><th><?php esc_html_e( 'Name', 'easy-hotel' ); ?></th><td><?php echo esc_html( $b['customer_name'] ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Email', 'easy-hotel' ); ?></th><td><?php echo esc_html( $b['customer_email'] ); ?></td></tr>
        <?php if ( $b['customer_phone'] ) : ?>
            <tr><th><?php esc_html_e( 'Phone', 'easy-hotel' ); ?></th><td><?php echo esc_html( $b['customer_phone'] ); ?></td></tr>
        <?php endif; ?>
    </table>

    <?php if ( $b['extra_services'] ) : ?>
        <h4 class="eshb-account-detail-title"><?php esc_html_e( 'Additional services', 'easy-hotel' ); ?></h4>
        <p class="eshb-account-detail-text"><?php echo esc_html( $b['extra_services'] ); ?></p>
    <?php endif; ?>

    <h4 class="eshb-account-detail-title"><?php esc_html_e( 'Payment', 'easy-hotel' ); ?></h4>
    <table class="eshb-account-detail-table">
        <tr><th><?php esc_html_e( 'Subtotal', 'easy-hotel' ); ?></th><td><?php echo wp_kses_post( $b['subtotal_html'] ); ?></td></tr>
        <?php if ( $b['coupon_code'] ) : ?>
            <tr><th><?php esc_html_e( 'Coupon', 'easy-hotel' ); ?></th><td><?php echo esc_html( $b['coupon_code'] ); ?> (− <?php echo wp_kses_post( $b['coupon_html'] ); ?>)</td></tr>
        <?php endif; ?>
        <tr><th><?php esc_html_e( 'Tax', 'easy-hotel' ); ?></th><td><?php echo wp_kses_post( $b['tax_html'] ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Total', 'easy-hotel' ); ?></th><td><strong><?php echo wp_kses_post( $b['total_html'] ); ?></strong></td></tr>
        <tr><th><?php esc_html_e( 'Paid', 'easy-hotel' ); ?></th><td><?php echo wp_kses_post( $b['paid_html'] ); ?></td></tr>
        <?php if ( $b['gateway'] ) : ?>
            <tr><th><?php esc_html_e( 'Method', 'easy-hotel' ); ?></th><td><?php echo esc_html( ucwords( str_replace( [ '-', '_' ], ' ', $b['gateway'] ) ) ); ?></td></tr>
        <?php endif; ?>
    </table>

    <?php if ( 'cancelled' === $b['status'] && $b['cancelled_at'] ) : ?>
        <h4 class="eshb-account-detail-title"><?php esc_html_e( 'Cancellation', 'easy-hotel' ); ?></h4>
        <table class="eshb-account-detail-table">
            <tr><th><?php esc_html_e( 'Cancelled on', 'easy-hotel' ); ?></th><td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $b['cancelled_at'] ) ) ); ?></td></tr>
            <?php if ( $b['cancelled_by'] ) : ?>
                <tr><th><?php esc_html_e( 'Cancelled by', 'easy-hotel' ); ?></th><td><?php echo esc_html( ucfirst( $b['cancelled_by'] ) ); ?></td></tr>
            <?php endif; ?>
            <?php if ( $b['cancel_reason'] ) : ?>
                <tr><th><?php esc_html_e( 'Reason', 'easy-hotel' ); ?></th><td><?php echo esc_html( $b['cancel_reason'] ); ?></td></tr>
            <?php endif; ?>
        </table>
    <?php endif; ?>
</div>
