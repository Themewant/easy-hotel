<?php
/**
 * Logged-out view for the account page: a built-in login form.
 *
 * @var ESHB_Native_Account $account
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$redirect = $account->get_account_url();
?>
<div class="eshb-account eshb-account--login">
    <div class="eshb-container eshb-account-login-box">
        <div class="eshb-card">
            <h2><?php esc_html_e( 'Sign in to your account', 'easy-hotel' ); ?></h2>

            <?php
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( isset( $_GET['password-set'] ) ) : ?>
                <div class="eshb-account-notice eshb-account-notice--success">
                    <?php esc_html_e( 'Your password has been set. Please log in to continue.', 'easy-hotel' ); ?>
                </div>
            <?php endif; ?>

            <p class="eshb-account-login-intro">
                <?php esc_html_e( 'Log in to view and manage your bookings.', 'easy-hotel' ); ?>
            </p>

            <?php
            wp_login_form( [
                'echo'           => true,
                'redirect'       => $redirect,
                'label_username' => __( 'Email or Username', 'easy-hotel' ),
                'label_password' => __( 'Password', 'easy-hotel' ),
                'label_remember' => __( 'Remember me', 'easy-hotel' ),
                'label_log_in'   => __( 'Log In', 'easy-hotel' ),
                'remember'       => true,
            ] );
            ?>

            <p class="eshb-account-login-links">
                <a href="<?php echo esc_url( wp_lostpassword_url( $redirect ) ); ?>"><?php esc_html_e( 'Lost your password?', 'easy-hotel' ); ?></a>
                <?php if ( get_option( 'users_can_register' ) ) : ?>
                    <span class="eshb-sep">·</span>
                    <a href="<?php echo esc_url( wp_registration_url() ); ?>"><?php esc_html_e( 'Create an account', 'easy-hotel' ); ?></a>
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>
