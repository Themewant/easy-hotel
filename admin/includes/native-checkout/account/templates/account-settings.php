<?php
/**
 * Account tab: edit profile details and change password.
 *
 * @var ESHB_Native_Account $account
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$user = wp_get_current_user();
?>
<div class="eshb-account-panel">
    <div class="eshb-account-section-head">
        <h2><?php esc_html_e( 'Account Details', 'easy-hotel' ); ?></h2>
    </div>

    <form class="eshb-account-form" id="eshbAccountProfileForm">
        <div class="eshb-account-grid-2">
            <div class="eshb-account-field">
                <label for="eshbFirstName"><?php esc_html_e( 'First Name', 'easy-hotel' ); ?></label>
                <input type="text" id="eshbFirstName" name="first_name" value="<?php echo esc_attr( $user->first_name ); ?>">
            </div>
            <div class="eshb-account-field">
                <label for="eshbLastName"><?php esc_html_e( 'Last Name', 'easy-hotel' ); ?></label>
                <input type="text" id="eshbLastName" name="last_name" value="<?php echo esc_attr( $user->last_name ); ?>">
            </div>
        </div>
        <div class="eshb-account-field">
            <label for="eshbDisplayName"><?php esc_html_e( 'Display Name', 'easy-hotel' ); ?></label>
            <input type="text" id="eshbDisplayName" name="display_name" value="<?php echo esc_attr( $user->display_name ); ?>">
        </div>
        <div class="eshb-account-field">
            <label for="eshbEmail"><?php esc_html_e( 'Email Address', 'easy-hotel' ); ?></label>
            <input type="email" id="eshbEmail" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" required>
        </div>

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
