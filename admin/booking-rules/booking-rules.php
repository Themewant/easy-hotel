<?php
/**
 * Booking Rules — minimum / maximum nights and allowed check-in day.
 *
 * Formerly the paid "EHB Min Max" and "EHB Week" add-ons, now bundled with Easy Hotel
 * for free. Loaded through the core plugin, so it uses the ESHB_* constants defined in
 * easy-hotel.php and needs no constants, licensing or activation hooks of its own.
 *
 * Each feature stands aside on its own while the matching standalone add-on is still
 * active, otherwise the metabox sections and the filters would be registered twice.
 * A site can run one add-on and let the bundled module handle the other. Sites can
 * deactivate an add-on whenever they like — the settings are shared, so nothing is
 * lost.
 *
 * @see includes/class.booking-rules.php for the storage / backwards compatibility notes.
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once ESHB_PL_PATH . 'admin/booking-rules/includes/class.booking-rules.php';

/**
 * Kept for backwards compatibility — third party code has been able to call this since
 * the min max add-on was bundled.
 */
if ( ! function_exists( 'eshb_is_min_max_addon_active' ) ) {
    function eshb_is_min_max_addon_active() {
        return ! ESHB_Booking_Rules::owns_nights();
    }
}


require_once ESHB_PL_PATH . 'admin/booking-rules/includes/class.admin-settings.php';
require_once ESHB_PL_PATH . 'admin/booking-rules/includes/class.filters.php';
