<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * Feed the Booking Rules tab's tables into the filters core already asks.
 *
 * Each group of filters is only registered for a feature the bundled module owns; while
 * the matching standalone add-on is active it answers these same filters itself and this
 * class stays out of the way.
 */
class ESHB_Booking_Rules_Filters {

    public function __construct() {

        if ( ESHB_Booking_Rules::owns_nights() ) {
            add_filter('eshb_is_global_source_for_min_max', [$this, 'add_source_type'], 10, 3);
            add_filter('eshb_min_max_settings', [$this, 'add_min_max_settings'], 10, 3);
            add_filter('eshb_min_max_nights_settings_before_search', [$this, 'add_min_max_settings'], 10, 3);
            add_filter('eshb_min_max_global_settings_localize', [$this, 'add_min_max_global_settings'], 10, 3);
            add_filter('eshb_min_max_global_settings_in_searach', [$this, 'add_min_max_global_settings'], 10, 3);
        }

        if ( ESHB_Booking_Rules::owns_check_in_day() ) {
            add_filter('eshb_booking_allowed_check_in_day', [$this, 'add_allowed_check_in_day'], 10, 3);
            add_filter('eshb_get_disabled_dates_in_search', [$this, 'add_allowed_check_in_day_in_search'], 15, 3);
            add_filter('eshb_week_settings', [$this, 'add_week_settings']);
        }
    }

    public function add_source_type($is_global_source_for_min_max, $accomodation_id, $accomodation_metaboxes) {
         // The bundled module has no per accomodation "Nights" metabox — everything
         // comes from the Booking Rules tab, so the source is always the globals.
         // Any value left in post meta by the standalone add-on is ignored on purpose.
         return true;
    }

    public function add_min_max_global_settings($min_max_settings) {

         $min_max_settings = array_merge( (array) $min_max_settings, ESHB_Booking_Rules::get_settings() );

         // No accomodation is in context here (the search form), so only rules
         // targeting "All Accomodations" can move the site wide limits.
         $rule_min_nights = ESHB_Booking_Rules::get_min_nights_for_accomodation( 0 );
         $rule_max_nights = ESHB_Booking_Rules::get_max_nights_for_accomodation( 0 );

         if ( $rule_min_nights !== null ) {
             $min_max_settings['required_min_nights'] = $rule_min_nights;
         }

         if ( $rule_max_nights !== null ) {
             $min_max_settings['required_max_nights'] = $rule_max_nights;
         }

         return $min_max_settings;
    }

    public function add_min_max_settings($min_max_settings, $accomodation_id, $accomodation_metaboxes) {
        // Both limits come from the Booking Rules tab's rules tables. Accomodation post
        // meta is never consulted — the "Nights" metabox belongs to the standalone
        // add-on, and while that is active these filters are not registered.

        $rule_min_nights = ESHB_Booking_Rules::get_min_nights_for_accomodation( $accomodation_id );
        $rule_max_nights = ESHB_Booking_Rules::get_max_nights_for_accomodation( $accomodation_id );

        $min_max_settings['required_min_nights'] = ( $rule_min_nights !== null ) ? $rule_min_nights : 1;
        $min_max_settings['required_max_nights'] = ( $rule_max_nights !== null ) ? $rule_max_nights : 999;

        return $min_max_settings;
    }

    /**
     * The weekdays a stay may start on, for one accomodation.
     *
     * A string, comma separated when a rule allows several days: core matches it with
     * strpos() against a lowercase day name, and no weekday name is a substring of
     * another, so that stays exact. 'all' is core's word for unrestricted, and it has to
     * keep saying that rather than '' — the checks are written around it.
     */
    public function add_allowed_check_in_day($allowed_check_in_day, $accomodation_id, $accomodation_metaboxes) {

        $days = ESHB_Booking_Rules::get_check_in_days_for_accomodation( $accomodation_id );

        return ! empty( $days ) ? implode( ',', $days ) : 'all';
    }

    /**
     * Same rule for the search / calendar payload, as an array — the calendar script
     * reads a list of day names, and an empty one means no restriction.
     */
    public function add_allowed_check_in_day_in_search($all_dates, $accomodation_id, $accomodation_metas) {

        $all_dates['allowed_check_in_day'] = ESHB_Booking_Rules::get_check_in_days_for_accomodation( $accomodation_id );

        return $all_dates;
    }

    public function add_week_settings($week_settings) {
        return array_merge( (array) $week_settings, ESHB_Booking_Rules::get_week_settings() );
    }
}

new ESHB_Booking_Rules_Filters();
