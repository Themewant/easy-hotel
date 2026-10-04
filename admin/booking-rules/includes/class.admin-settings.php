<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( class_exists( 'ESHB' ) ) {

    /**
     * Add the "Booking Rules" tab to the Easy Hotel settings screen, right after
     * "Booking".
     *
     * Registered through the sections filter rather than ESHB::createSection() so the
     * tab lands in a fixed position regardless of when this file is loaded.
     *
     * Both limits live in their rules repeater only — there are deliberately no global
     * "Minimum Nights" / "Maximum Nights" number fields, so there is a single place to
     * maintain each. Previously configured global values are carried into "All
     * Accomodations" rules by ESHB_Booking_Rules::migrate_global_nights().
     *
     * Accomodations have no "Nights" metabox tab either; that section belongs to the
     * standalone EHB Min Max add-on, which registers it itself when active (and this
     * whole module stands aside in that case — see booking-rules.php).
     */
    add_filter( 'eshb_eshb_settings_sections', function( $sections ){

        if ( ! is_array( $sections ) ) {
            return $sections;
        }

        $owns_nights       = ESHB_Booking_Rules::owns_nights();
        $owns_check_in_day = ESHB_Booking_Rules::owns_check_in_day();

        // Runs here, not on admin_init: the options object reads the stored values
        // straight after this filter, so a later migration would not show up until
        // the next request. Only for the features this site actually owns — an active
        // add-on is still the source of truth for its own.
        if ( $owns_nights ) {
            ESHB_Booking_Rules::migrate_global_nights();
        }

        if ( $owns_check_in_day ) {
            ESHB_Booking_Rules::migrate_check_in_day_meta();
        }

        $accomodations_field = array(
            'id'            => 'accomodations',
            'type'          => 'checkbox',
            'title'         => 'Accomodations',
            'class'         => 'eshb-nights-rule-accomodations',
            'options'       => 'eshb_booking_rules_accomodation_options',
            'default'       => array( ESHB_Booking_Rules::TARGET_ALL ),
            'empty_message' => 'No accomodation found. Publish an accomodation first.',
        );

        $fields = array();

        if ( $owns_nights ) {

            $fields[] = array(
                'type'    => 'subheading',
                'content' => 'Minimum Nights',
            );
            $fields[] = array(
                'id'           => ESHB_Booking_Rules::MIN_RULES_FIELD,
                'type'         => 'repeater',
                'title'        => 'Minimum Nights Rules',
                'help'         => 'Counted in nights, not calendar days — 3 nights means check-out 3 days after check-in. Tick "All" for a site wide minimum, or pick accomodations to set one for them only. A rule that picks accomodations beats an "All" rule; if several still apply, the highest wins. A season can require a longer stay for its own dates — whichever is stricter applies.',
                'button_title' => 'Add Rule',
                'fields'       => array(
                    array(
                        'id'      => 'min_nights',
                        'type'    => 'number',
                        'title'   => 'Minimum Nights',
                        'default' => 1,
                        'min'     => 1,
                    ),
                    $accomodations_field,
                ),
            );

            $fields[] = array(
                'type'    => 'subheading',
                'content' => 'Maximum Nights',
            );
            $fields[] = array(
                'id'           => ESHB_Booking_Rules::MAX_RULES_FIELD,
                'type'         => 'repeater',
                'title'        => 'Maximum Nights Rules',
                'help'         => 'The longest stay a guest can book. Tick "All" for a site wide cap, or pick accomodations to cap only those. A rule that picks accomodations beats an "All" rule; if several still apply, the lowest wins. Leave the table empty for no limit.',
                'button_title' => 'Add Rule',
                'fields'       => array(
                    array(
                        'id'      => 'max_nights',
                        'type'    => 'number',
                        'title'   => 'Maximum Nights',
                        'default' => 1,
                        'min'     => 1,
                    ),
                    $accomodations_field,
                ),
            );
        }

        if ( $owns_check_in_day ) {

            $fields[] = array(
                'type'    => 'subheading',
                'content' => 'Check-in Day',
            );
            $fields[] = array(
                'id'           => ESHB_Booking_Rules::CHECK_IN_DAY_RULES_FIELD,
                'type'         => 'repeater',
                'title'        => 'Check-in Day Rules',
                'help'         => 'Restrict which weekdays a stay may start on. Tick as many days as you like — a guest can then check in on any of them. "All Days" ticks every day at once, which means no restriction; use "Uncheck all" to clear them and pick your own. Tick "All" under Accomodations to apply the rule site wide, or pick accomodations to restrict only those; a rule that picks accomodations beats an "All" rule. Unlike nights there is no stricter of two, so if several rules still apply the first one in the table wins, with all of its days.',
                'button_title' => 'Add Rule',
                'fields'       => array(
                    array(
                        'id'      => 'check_in_day',
                        'type'    => 'checkbox',
                        'title'   => 'Allowed Check-in Days',
                        'class'   => 'eshb-check-in-day-choices',
                        'inline'  => true,
                        'options' => ESHB_Booking_Rules::get_check_in_day_options(),
                        'default' => array( ESHB_Booking_Rules::TARGET_ALL ),
                    ),
                    $accomodations_field,
                ),
            );
            $fields[] = array(
                'id'      => 'string_check_in_day_error_msg',
                'type'    => 'text',
                'title'   => 'Check-in Day Error Message',
                'desc'    => 'Shown when a guest picks a check-in date on a day that is not allowed. The allowed day is appended to it.',
                'default' => ESHB_Booking_Rules::get_week_field_default( 'string_check_in_day_error_msg', 'Only allowed check in day is :' ),
            );
        }

        // Core's own, always shown — moved here from the "Booking" tab, unchanged.
        //
        // Deliberately left as the plain repeater it has always been, rather than given
        // the rules tables' summary/Edit treatment: every id and field type is exactly
        // what the Booking tab used, so this moves the UI between tabs and nothing else.
        // The values live under the same `holidays` key of the same `eshb_settings`
        // option, so existing holidays keep working with no migration. Only the
        // labels say "Booking Blocked Dates" now — the ids stay `holidays` /
        // `holiday-date` / `holiday-title` so saved data is untouched.
        $fields[] = array(
            'type'    => 'subheading',
            'content' => 'Booking Blocked Dates',
        );
        $fields[] = array(
            'id'        => 'holidays',
            'type'      => 'repeater',
            'title'     => 'Booking Blocked Dates',
            'desc'      => 'Dates added here cannot be booked by guests. Use it for holidays, maintenance or any day you want to keep closed.',
            'fields'    => array(

                array(
                    'id'    => 'holiday-date',
                    'type'  => 'date',
                    'title' => 'Blocked Date',
                    'desc'  => 'Pick a single date, or a from–to range, to block from booking.',
                    'from_to'  => true,
                    'settings' => array(
                        'dateFormat'      => 'yy-mm-dd',
                        'changeMonth'     => true,
                        'changeYear'      => true,
                        'showButtonPanel' => true,
                        'weekHeader'      => 'Week',
                        'monthNamesShort' => array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ),
                        'dayNamesMin'     => array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ),
                    ),
                ),
                array(
                    'id'    => 'holiday-title',
                    'type'  => 'text',
                    'title' => 'Reason / Title',
                    'desc'  => 'Why these dates are blocked, e.g. "Eid Holiday" or "Maintenance".',
                ),
                array(
                    'id'          => 'accomodation-ids',
                    'type'        => 'select',
                    'title'       => 'Accomodations',
                    'placeholder' => 'Select Accomodations',
                    'desc'  => 'Block these dates only for the selected accomodations. Leave it empty to block for all.',
                    'options'     => 'posts',
                    'multiple'     => true,
                    'chosen'      => false,
                    'query_args'  => array(
                                        'post_type' => 'eshb_accomodation',
                                        'posts_per_page' => -1,
                                    ),
                ),

            ),
        );

        // Belongs to the min max feature, so it follows the same owner.
        if ( $owns_nights ) {
            $fields[] = array(
                'type'    => 'subheading',
                'content' => 'Global Settings',
            );
            $fields[] = array(
                'id'      => 'calendar_start_date_buffer',
                'type'    => 'number',
                'title'   => 'Calenader Start Date Buffer (+Night)',
                'default' => ESHB_Booking_Rules::get_field_default( 'calendar_start_date_buffer', 0 ),
            );
        }

        $booking_rules_section = array(
            'title'  => 'Booking Rules',
            'class'  => 'easy-hotel-booking-rules-settings',
            'fields' => $fields,
        );

        // Find the "Booking" tab and drop the new one straight after it.
        $position = false;
        foreach ( $sections as $index => $section ) {
            if ( isset( $section['title'] ) && $section['title'] === 'Booking' ) {
                $position = $index + 1;
                break;
            }
        }

        if ( $position === false ) {
            $sections[] = $booking_rules_section;
        } else {
            array_splice( $sections, $position, 0, array( $booking_rules_section ) );
        }

        return $sections;

    } );

    /**
     * jQuery UI datepicker for the "Booking Blocked Dates" fields.
     *
     * The framework enqueues a field type's own assets only for the types it has seen
     * through ESHB::createSection(), which is the one place ESHB::set_used_fields() is
     * called from. This tab is registered through the sections filter above instead, so
     * ESHB_Field_date::enqueue() never runs and jquery-ui-datepicker is never printed.
     *
     * Without it eshb_field_date() throws on the first date field it touches, and the
     * repeater initialises its saved rows before binding Add / Duplicate / Delete — so
     * one saved blocked date was enough to leave all three buttons dead. main.js now
     * guards against the missing plugin and binds before initialising, but the field is
     * still meant to have a calendar, which is what this puts back.
     *
     * Only the script is needed; the framework stylesheet already carries the
     * .ui-datepicker rules.
     */
    add_action( 'admin_enqueue_scripts', function(){

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only page check.
        if ( ! isset( $_GET['page'] ) || sanitize_key( wp_unslash( $_GET['page'] ) ) !== 'easy-hotel-settings' ) {
            return;
        }

        wp_enqueue_script( 'jquery-ui-datepicker' );

    } );

    /**
     * Turn each rules repeater into a plain admin list: an "Add Rule" button, a table
     * of the rules already configured, and Edit / Delete per row.
     *
     * The repeater itself stays the source of truth — the rows are still its inputs,
     * just collapsed until one is being edited, so saving, cloning and reindexing keep
     * working the way the settings framework expects. Nothing here is required for the
     * rules to work; a browser with the script blocked falls back to the plain
     * repeater.
     *
     * "All" acts as a check-all for the accomodation list: ticking it ticks every
     * accomodation so it is obvious the rule covers them, unticking it clears them, and
     * unticking any single one drops "All". The extra ids that ride along with "all"
     * change nothing — ESHB_Booking_Rules::resolve_nights_rule() reads a rule
     * containing "all" as the baseline rule regardless of what else is ticked.
     */
    add_action( 'admin_enqueue_scripts', function(){

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only page check.
        if ( ! isset( $_GET['page'] ) || sanitize_key( wp_unslash( $_GET['page'] ) ) !== 'easy-hotel-settings' ) {
            return;
        }

        wp_enqueue_style( 'eshb-booking-rules-admin', ESHB_PL_URL . 'admin/booking-rules/assets/css/booking-rules-admin.css', array(), ESHB_VERSION );

        // Depends on the settings framework's script ("easy-hotel") so it still runs
        // after it, as it did when it was printed on admin_print_footer_scripts.
        wp_enqueue_script( 'eshb-booking-rules-admin', ESHB_PL_URL . 'admin/booking-rules/assets/js/booking-rules-admin.js', array( 'jquery', 'easy-hotel' ), ESHB_VERSION, true );

        wp_localize_script( 'eshb-booking-rules-admin', 'eshbBookingRules', array(
            'all'      => ESHB_Booking_Rules::TARGET_ALL,
            'minField' => ESHB_Booking_Rules::MIN_RULES_FIELD,
            'maxField' => ESHB_Booking_Rules::MAX_RULES_FIELD,
            'dayField' => ESHB_Booking_Rules::CHECK_IN_DAY_RULES_FIELD,
        ) );

    } );

    /**
     * Add a "Booking Rules" entry to the Easy Hotel admin menu that jumps straight
     * to the matching tab on the settings screen.
     *
     * The slug is a full URL rather than a page slug, so WordPress prints it as the
     * link href untouched — that keeps the "#tab=" fragment intact, which is what the
     * settings framework reads on load to open the right tab. Passing an empty
     * callback is what puts core on that code path (a callback would make it rewrite
     * the href and encode the fragment away).
     *
     * Priority 10 lands it after the submenus registered in admin-settings.php and
     * before "Settings", which is re-added at priority 999.
     */
    add_action( 'admin_menu', function(){

        add_submenu_page(
            'edit.php?post_type=eshb_accomodation',
            'Booking Rules',
            'Booking Rules',
            'manage_options',
            'edit.php?post_type=eshb_accomodation&page=easy-hotel-settings#tab=booking-rules',
            ''
        );

    } );

}
