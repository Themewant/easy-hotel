<?php
/**
 * Account tab: edit profile details and change password.
 *
 * @var ESHB_Native_Account $account
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$eshb_user = wp_get_current_user();

// Email change awaiting confirmation (see ESHB_Native_Account_Customer::request_email_change()).
$eshb_pending       = get_user_meta( $eshb_user->ID, ESHB_Native_Account_Customer::META_PENDING_EMAIL, true );
$eshb_pending_email = ( is_array( $eshb_pending ) && ! empty( $eshb_pending['email'] ) && (int) ( $eshb_pending['expires'] ?? 0 ) >= time() )
    ? (string) $eshb_pending['email']
    : '';

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag set by the confirmation redirect.
$eshb_email_change = isset( $_GET['email-change'] ) ? sanitize_key( wp_unslash( $_GET['email-change'] ) ) : '';
?>
<div class="eshb-account-panel">
    <div class="eshb-account-section-head">
        <h2><?php esc_html_e( 'Account Details', 'easy-hotel' ); ?></h2>
    </div>

    <form class="eshb-account-form" id="eshbAccountProfileForm">
        <div class="eshb-account-grid-2">
            <div class="eshb-account-field">
                <label for="eshbFirstName"><?php esc_html_e( 'First Name', 'easy-hotel' ); ?></label>
                <input type="text" id="eshbFirstName" name="first_name" value="<?php echo esc_attr( $eshb_user->first_name ); ?>">
            </div>
            <div class="eshb-account-field">
                <label for="eshbLastName"><?php esc_html_e( 'Last Name', 'easy-hotel' ); ?></label>
                <input type="text" id="eshbLastName" name="last_name" value="<?php echo esc_attr( $eshb_user->last_name ); ?>">
            </div>
        </div>
        <div class="eshb-account-field">
            <label for="eshbDisplayName"><?php esc_html_e( 'Display Name', 'easy-hotel' ); ?></label>
            <input type="text" id="eshbDisplayName" name="display_name" value="<?php echo esc_attr( $eshb_user->display_name ); ?>">
        </div>
        <div class="eshb-account-field">
            <label for="eshbEmail"><?php esc_html_e( 'Email Address', 'easy-hotel' ); ?></label>
            <input type="email" id="eshbEmail" name="email" value="<?php echo esc_attr( $eshb_user->user_email ); ?>" required>
        </div>

        <?php if ( 'changed' === $eshb_email_change ) : ?>
            <div class="eshb-account-notice eshb-account-notice--success">
                <?php esc_html_e( 'Your email address has been updated.', 'easy-hotel' ); ?>
            </div>
        <?php elseif ( 'taken' === $eshb_email_change ) : ?>
            <div class="eshb-account-notice">
                <?php esc_html_e( 'That email address is already in use, so your email was not changed.', 'easy-hotel' ); ?>
            </div>
        <?php elseif ( 'invalid' === $eshb_email_change ) : ?>
            <div class="eshb-account-notice">
                <?php esc_html_e( 'This confirmation link is invalid or has expired. Please request the change again.', 'easy-hotel' ); ?>
            </div>
        <?php elseif ( '' !== $eshb_pending_email ) : ?>
            <div class="eshb-account-notice eshb-account-notice--info">
                <?php
                printf(
                    /* translators: %s: email address awaiting confirmation */
                    esc_html__( 'Waiting for confirmation of %s. Open the link we emailed to that address to finish the change.', 'easy-hotel' ),
                    '<strong>' . esc_html( $eshb_pending_email ) . '</strong>'
                );
                ?>
            </div>
        <?php endif; ?>

        <p class="eshb-account-form-msg" data-eshb-profile-msg></p>

        <div class="eshb-account-form-actions">
            <button type="submit" class="eshb-btn-submit"><?php esc_html_e( 'Save Changes', 'easy-hotel' ); ?></button>
        </div>
    </form>

    <div class="eshb-account-section-head eshb-account-section-head--spaced">
        <h2><?php esc_html_e( 'Change Password', 'easy-hotel' ); ?></h2>
    </div>

    <form class="eshb-account-form" id="eshbAccountPasswordForm">
        <div class="eshb-account-field">
            <label for="eshbCurrentPassword"><?php esc_html_e( 'Current Password', 'easy-hotel' ); ?></label>
            <input type="password" id="eshbCurrentPassword" name="current_password" autocomplete="current-password">
        </div>
        <div class="eshb-account-grid-2">
            <div class="eshb-account-field">
                <label for="eshbNewPassword"><?php esc_html_e( 'New Password', 'easy-hotel' ); ?></label>
                <input type="password" id="eshbNewPassword" name="new_password" autocomplete="new-password">
            </div>
            <div class="eshb-account-field">
                <label for="eshbConfirmPassword"><?php esc_html_e( 'Confirm New Password', 'easy-hotel' ); ?></label>
                <input type="password" id="eshbConfirmPassword" name="confirm_password" autocomplete="new-password">
            </div>
        </div>

        <p class="eshb-account-form-msg" data-eshb-password-msg></p>

        <div class="eshb-account-form-actions">
            <button type="submit" class="eshb-btn-submit"><?php esc_html_e( 'Update Password', 'easy-hotel' ); ?></button>
        </div>
    </form>
</div>
