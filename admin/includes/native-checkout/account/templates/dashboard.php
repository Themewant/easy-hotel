<?php
/**
 * Dashboard tab: greeting, customer info and booking counters.
 *
 * @var ESHB_Native_Account          $account
 * @var ESHB_Native_Account_Bookings $bookings
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$eshb_user  = wp_get_current_user();
$eshb_stats = $bookings->get_dashboard_stats( $eshb_user );

// Last 5 bookings for the quick list.
$eshb_recent_ids = array_slice( $account->customer->get_user_booking_ids( $eshb_user ), 0, 5 );

$eshb_cards = [
    [ 'label' => __( 'Total Bookings', 'easy-hotel' ),     'value' => $eshb_stats['total'],     'mod' => 'total' ],
    [ 'label' => __( 'Active Bookings', 'easy-hotel' ),    'value' => $eshb_stats['active'],    'mod' => 'active' ],
    [ 'label' => __( 'Upcoming Bookings', 'easy-hotel' ),  'value' => $eshb_stats['upcoming'],  'mod' => 'upcoming' ],
    [ 'label' => __( 'Cancelled Bookings', 'easy-hotel' ), 'value' => $eshb_stats['cancelled'], 'mod' => 'cancelled' ],
];
?>
<div class="eshb-account-panel">
    <div class="eshb-account-welcome">
        <h2>
        <?php
            echo esc_html__( 'Welcome back', 'easy-hotel' ) . ' ';
            echo esc_html( $eshb_user->display_name );
        ?></h2>
        <p class="eshb-account-welcome-meta">
            <span><?php echo esc_html( trim( $eshb_user->first_name . ' ' . $eshb_user->last_name ) ?: $eshb_user->display_name ); ?></span>
            <span class="eshb-sep">·</span>
            <span><?php echo esc_html( $eshb_user->user_email ); ?></span>
        </p>
    </div>

    <div class="eshb-account-stats">
        <?php foreach ( $eshb_cards as $eshb_card ) : ?>
            <div class="eshb-account-stat eshb-account-stat--<?php echo esc_attr( $eshb_card['mod'] ); ?>">
                <span class="eshb-account-stat-value"><?php echo esc_html( $eshb_card['value'] ); ?></span>
                <span class="eshb-account-stat-label"><?php echo esc_html( $eshb_card['label'] ); ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="eshb-account-recent">
        <div class="eshb-account-section-head">
            <h3><?php esc_html_e( 'Recent Bookings', 'easy-hotel' ); ?></h3>
            <a href="<?php echo esc_url( $account->get_tab_url( 'bookings' ) ); ?>" class="eshb-account-viewall">
                <?php esc_html_e( 'View all', 'easy-hotel' ); ?>
            </a>
        </div>

        <?php if ( empty( $eshb_recent_ids ) ) : ?>
            <p class="eshb-account-empty"><?php esc_html_e( 'You have no bookings yet.', 'easy-hotel' ); ?></p>
        <?php else : ?>
            <div class="eshb-account-table-wrap">
                <table class="eshb-account-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Booking', 'easy-hotel' ); ?></th>
                            <th><?php esc_html_e( 'Hotel', 'easy-hotel' ); ?></th>
                            <th><?php esc_html_e( 'Check-in', 'easy-hotel' ); ?></th>
                            <th><?php esc_html_e( 'Total', 'easy-hotel' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'easy-hotel' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $eshb_recent_ids as $eshb_id ) :
                            $eshb_row = $bookings->get_row_view( $eshb_id );
                            if ( empty( $eshb_row ) ) continue;
                            ?>
                            <tr>
                                <td data-label="<?php esc_attr_e( 'Booking', 'easy-hotel' ); ?>">#<?php echo esc_html( $eshb_row['id'] ); ?></td>
                                <td data-label="<?php esc_attr_e( 'Hotel', 'easy-hotel' ); ?>"><?php echo esc_html( $eshb_row['accomodation'] ); ?></td>
                                <td data-label="<?php esc_attr_e( 'Check-in', 'easy-hotel' ); ?>"><?php echo esc_html( $eshb_row['check_in_label'] ); ?></td>
                                <td data-label="<?php esc_attr_e( 'Total', 'easy-hotel' ); ?>"><?php echo wp_kses_post( $eshb_row['total_html'] ); ?></td>
                                <td data-label="<?php esc_attr_e( 'Status', 'easy-hotel' ); ?>">
                                    <span class="eshb-badge eshb-badge--<?php echo esc_attr( $eshb_row['status'] ); ?>"><?php echo esc_html( $eshb_row['status_label'] ); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
