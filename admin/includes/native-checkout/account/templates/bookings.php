<?php
/**
 * Bookings tab: the customer's full booking list with View / Cancel.
 *
 * @var ESHB_Native_Account          $account
 * @var ESHB_Native_Account_Bookings $bookings
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$ids = $account->customer->get_user_booking_ids( wp_get_current_user() );
?>
<div class="eshb-account-panel">
    <div class="eshb-account-section-head">
        <h2><?php esc_html_e( 'My Bookings', 'easy-hotel' ); ?></h2>
    </div>

    <?php // Success notice shown after a booking is cancelled (filled by account.js). ?>
    <div class="eshb-account-notice eshb-account-notice--info" data-eshb-notice hidden></div>

    <?php if ( empty( $ids ) ) : ?>
        <p class="eshb-account-empty"><?php esc_html_e( 'You have no bookings yet.', 'easy-hotel' ); ?></p>
    <?php else : ?>
        <div class="eshb-account-table-wrap">
            <table class="eshb-account-table eshb-account-bookings-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Booking ID', 'easy-hotel' ); ?></th>
                        <th><?php esc_html_e( 'Accomodation', 'easy-hotel' ); ?></th>
                        <th><?php esc_html_e( 'Check-in / Check-out', 'easy-hotel' ); ?></th>
                        <th><?php esc_html_e( 'Total', 'easy-hotel' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'easy-hotel' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'easy-hotel' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $ids as $id ) :
                        $row = $bookings->get_row_view( $id );
                        if ( empty( $row ) ) continue;
                        ?>
                        <tr data-booking-id="<?php echo esc_attr( $row['id'] ); ?>">
                            <td data-label="<?php esc_attr_e( 'Booking ID', 'easy-hotel' ); ?>">#<?php echo esc_html( $row['id'] ); ?></td>
                            <td data-label="<?php esc_attr_e( 'Accomodation', 'easy-hotel' ); ?>"><?php echo esc_html( $row['accomodation'] ); ?></td>
                            <td data-label="<?php esc_attr_e( 'Check-in / Check-out', 'easy-hotel' ); ?>">
                                <?php echo esc_html( $row['check_in_label'] ); ?>
                                <span class="eshb-account-date-sep">→</span>
                                <?php echo esc_html( $row['check_out_label'] ); ?>
                            </td>
                            <td data-label="<?php esc_attr_e( 'Total', 'easy-hotel' ); ?>"><?php echo wp_kses_post( $row['total_html'] ); ?></td>
                            <td data-label="<?php esc_attr_e( 'Status', 'easy-hotel' ); ?>">
                                <span class="eshb-badge eshb-badge--<?php echo esc_attr( $row['status'] ); ?>" data-eshb-status>
                                    <?php echo esc_html( $row['status_label'] ); ?>
                                </span>
                            </td>
                            <td data-label="<?php esc_attr_e( 'Actions', 'easy-hotel' ); ?>" class="eshb-account-actions">
                                <button type="button" class="eshb-btn-link" data-eshb-view="<?php echo esc_attr( $row['id'] ); ?>">
                                    <?php esc_html_e( 'View', 'easy-hotel' ); ?>
                                </button>
                                <?php if ( $row['can_cancel'] ) : ?>
                                    <button type="button" class="eshb-btn-link eshb-btn-danger" data-eshb-cancel="<?php echo esc_attr( $row['id'] ); ?>">
                                        <?php esc_html_e( 'Cancel', 'easy-hotel' ); ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php // Cancellation form markup, cloned into the modal by account.js. ?>
<script type="text/template" id="eshbCancelTemplate">
    <div class="eshb-account-cancel">
        <h3><?php esc_html_e( 'Cancel booking', 'easy-hotel' ); ?></h3>
        <p><?php esc_html_e( 'Are you sure you want to cancel this booking? This cannot be undone.', 'easy-hotel' ); ?></p>

        <label class="eshb-account-field-label" for="eshbCancelReason"><?php esc_html_e( 'Reason (optional)', 'easy-hotel' ); ?></label>
        <select id="eshbCancelReason" class="eshb-account-select" data-eshb-cancel-reason>
            <option value=""><?php esc_html_e( 'Select a reason…', 'easy-hotel' ); ?></option>
            <option value="<?php esc_attr_e( 'Change of plans', 'easy-hotel' ); ?>"><?php esc_html_e( 'Change of plans', 'easy-hotel' ); ?></option>
            <option value="<?php esc_attr_e( 'Found another hotel', 'easy-hotel' ); ?>"><?php esc_html_e( 'Found another hotel', 'easy-hotel' ); ?></option>
            <option value="<?php esc_attr_e( 'Price issue', 'easy-hotel' ); ?>"><?php esc_html_e( 'Price issue', 'easy-hotel' ); ?></option>
            <option value="other"><?php esc_html_e( 'Other', 'easy-hotel' ); ?></option>
        </select>

        <textarea class="eshb-account-textarea" data-eshb-cancel-custom rows="3"
            placeholder="<?php esc_attr_e( 'Tell us more (optional)', 'easy-hotel' ); ?>" hidden></textarea>

        <p class="eshb-account-modal-msg" data-eshb-cancel-msg></p>

        <div class="eshb-account-modal-actions">
            <button type="button" class="eshb-btn-secondary" data-eshb-modal-close><?php esc_html_e( 'Keep booking', 'easy-hotel' ); ?></button>
            <button type="button" class="eshb-btn-submit eshb-btn-danger" data-eshb-cancel-confirm><?php esc_html_e( 'Confirm cancellation', 'easy-hotel' ); ?></button>
        </div>
    </div>
</script>
