<?php
/**
 * Logged-in account wrapper: sidebar navigation + active tab content.
 *
 * @var ESHB_Native_Account          $account
 * @var ESHB_Native_Account_Bookings $bookings
 * @var string                       $active_tab  dashboard|bookings|account
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$current_user = wp_get_current_user();

$nav = [
    'dashboard' => [ 'label' => __( 'Dashboard', 'easy-hotel' ), 'icon' => 'dashicons-dashboard' ],
    'bookings'  => [ 'label' => __( 'Bookings', 'easy-hotel' ),  'icon' => 'dashicons-calendar-alt' ],
    'account'   => [ 'label' => __( 'Account', 'easy-hotel' ),   'icon' => 'dashicons-admin-users' ],
];
?>
<div class="eshb-account" id="eshbAccount">
    <div class="eshb-container">

        <div class="eshb-account-layout">

            <aside class="eshb-account-nav">
                <div class="eshb-account-user">
                    <span class="eshb-account-avatar"><?php echo get_avatar( $current_user->ID, 64 ); ?></span>
                    <span class="eshb-account-username"><?php echo esc_html( $current_user->display_name ); ?></span>
                </div>
                <ul class="eshb-account-menu">
                    <?php foreach ( $nav as $key => $item ) : ?>
                        <li>
                            <a href="<?php echo esc_url( $account->get_tab_url( $key ) ); ?>"
                               class="eshb-account-menu-item<?php echo $active_tab === $key ? ' is-active' : ''; ?>">
                                <span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>"></span>
                                <span><?php echo esc_html( $item['label'] ); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li>
                        <a href="<?php echo esc_url( $account->get_logout_url() ); ?>" class="eshb-account-menu-item eshb-account-logout">
                            <span class="dashicons dashicons-exit"></span>
                            <span><?php esc_html_e( 'Logout', 'easy-hotel' ); ?></span>
                        </a>
                    </li>
                </ul>
            </aside>

            <section class="eshb-account-content">
                <?php
                switch ( $active_tab ) {
                    case 'bookings':
                        $account->render_template( 'bookings.php' );
                        break;
                    case 'account':
                        $account->render_template( 'account-settings.php' );
                        break;
                    case 'dashboard':
                    default:
                        $account->render_template( 'dashboard.php' );
                        break;
                }
                ?>
            </section>

        </div>
    </div>

    <?php // Shared modal shell used by the bookings tab (view + cancel). ?>
    <div class="eshb-account-modal" id="eshbAccountModal" hidden>
        <div class="eshb-account-modal-overlay" data-eshb-modal-close></div>
        <div class="eshb-account-modal-dialog" role="dialog" aria-modal="true">
            <button type="button" class="eshb-account-modal-x" data-eshb-modal-close aria-label="<?php esc_attr_e( 'Close', 'easy-hotel' ); ?>">&times;</button>
            <div class="eshb-account-modal-body" id="eshbAccountModalBody"></div>
        </div>
    </div>
</div>
