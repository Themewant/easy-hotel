<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * Booking Rules — minimum / maximum nights and allowed check-in day.
 *
 * STORAGE NOTE
 * These settings used to live in their own options, `eshb_min_max_settings` and
 * `eshb_week_settings`, created by the standalone "EHB Min Max" and "EHB Week"
 * add-ons. Both features are now bundled with Easy Hotel for free and their UI moved
 * into the core "Booking Rules" tab, which stores everything in `eshb_settings`.
 *
 * No key was renamed. The legacy options are still read as a fallback and are kept in
 * sync on every save, so a site upgrading from an add-on keeps its configured values
 * and any code still reading those options keeps working.
 */
class ESHB_Booking_Rules {

    /** Options written by the old standalone add-ons. Never rename them. */
    const LEGACY_OPTION      = 'eshb_min_max_settings';
    const WEEK_LEGACY_OPTION = 'eshb_week_settings';

    /** Add-on that owns each feature while it is active. */
    const MIN_MAX_ADDON = 'ehb-min-max/ehb-min-max.php';
    const WEEK_ADDON    = 'ehb-week/ehb-week.php';

    /**
     * Field ids shared by the core settings tab and the legacy option.
     *
     * `required_min_nights` and `required_max_nights` are intentionally absent: both
     * limits are maintained in one place only, the rules repeaters below. The legacy
     * keys are still read once, by migrate_global_nights(), to seed those repeaters
     * on upgrading sites.
     */
    const FIELDS = array(
        'calendar_start_date_buffer',
    );

    /**
     * Repeater fields holding the per accomodation nights rules.
     *
     * Not part of FIELDS: the legacy add-on never knew about them and their values are
     * arrays, while everything in FIELDS is a plain scalar.
     */
    const MIN_RULES_FIELD = 'min_nights_rules';
    const MAX_RULES_FIELD = 'max_nights_rules';

    /** Repeater field holding the per accomodation allowed check-in day rules. */
    const CHECK_IN_DAY_RULES_FIELD = 'check_in_day_rules';

    /** Scalar week field, mirrored to the legacy `eshb_week_settings` option. */
    const WEEK_FIELDS = array(
        'string_check_in_day_error_msg',
    );

    /** Checkbox value that makes a rule apply to every accomodation. */
    const TARGET_ALL = 'all';

    /** Set once the old global limits have been carried into the rules repeaters. */
    const MIGRATED_OPTION = 'eshb_booking_rules_nights_migrated';

    /** Set once per accomodation check-in days have been carried into the repeater. */
    const CHECK_IN_DAY_MIGRATED_OPTION = 'eshb_booking_rules_check_in_day_migrated';

    public function __construct() {
        add_action( 'eshb_eshb_settings_saved', array( $this, 'sync_legacy_option' ) );
    }

    /**
     * Is a standalone add-on still active?
     *
     * @param  string $plugin Plugin basename.
     * @return bool
     */
    public static function is_addon_active( $plugin ) {

        if ( in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ) {
            return true;
        }

        return is_multisite() && array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) );
    }

    /** Does the bundled module handle minimum / maximum nights on this site? */
    public static function owns_nights() {
        return ! self::is_addon_active( self::MIN_MAX_ADDON );
    }

    /** Does the bundled module handle the allowed check-in day on this site? */
    public static function owns_check_in_day() {
        return ! self::is_addon_active( self::WEEK_ADDON );
    }

    /**
     * The effective global booking rules.
     *
     * Reads the core settings first and falls back to the legacy option, so the
     * values stay correct before the settings page is ever saved.
     *
     * @return array
     */
    public static function get_settings() {

        $settings = get_option( 'eshb_settings', array() );
        $settings = is_array( $settings ) ? $settings : array();

        $legacy = get_option( self::LEGACY_OPTION, array() );
        $legacy = is_array( $legacy ) ? $legacy : array();

        $rules = array();

        foreach ( self::FIELDS as $field ) {
            if ( array_key_exists( $field, $settings ) ) {
                $rules[ $field ] = $settings[ $field ];
            } elseif ( array_key_exists( $field, $legacy ) ) {
                $rules[ $field ] = $legacy[ $field ];
            }
        }

        return $rules;
    }

    /**
     * Default value for a Booking Rules field, taken from whatever the site already
     * has stored. This is what makes the previously saved add-on values show up in
     * the new tab before it has been saved once.
     *
     * @param  string $field    Field id.
     * @param  mixed  $fallback Value for a site that never configured the add-on.
     * @return mixed
     */
    public static function get_field_default( $field, $fallback = '' ) {

        $settings = self::get_settings();

        return ( isset( $settings[ $field ] ) && $settings[ $field ] !== '' ) ? $settings[ $field ] : $fallback;
    }

    /**
     * The effective week settings — currently just the check-in day error message.
     *
     * Same core-then-legacy fallback as get_settings(), against the option the EHB Week
     * add-on used.
     *
     * @return array
     */
    public static function get_week_settings() {

        $settings = get_option( 'eshb_settings', array() );
        $settings = is_array( $settings ) ? $settings : array();

        $legacy = get_option( self::WEEK_LEGACY_OPTION, array() );
        $legacy = is_array( $legacy ) ? $legacy : array();

        $week = array();

        foreach ( self::WEEK_FIELDS as $field ) {
            if ( array_key_exists( $field, $settings ) ) {
                $week[ $field ] = $settings[ $field ];
            } elseif ( array_key_exists( $field, $legacy ) ) {
                $week[ $field ] = $legacy[ $field ];
            }
        }

        return $week;
    }

    /**
     * Default value for a week field, taken from whatever the site already has stored.
     *
     * @param  string $field    Field id.
     * @param  mixed  $fallback Value for a site that never configured the add-on.
     * @return mixed
     */
    public static function get_week_field_default( $field, $fallback = '' ) {

        $settings = self::get_week_settings();

        return ( isset( $settings[ $field ] ) && $settings[ $field ] !== '' ) ? $settings[ $field ] : $fallback;
    }

    /**
     * The saved rows of one rules repeater.
     *
     * Each row looks like:
     *   array( 'min_nights' => '3', 'accomodations' => array( 'all' ) )
     *   array( 'min_nights' => '5', 'accomodations' => array( '12', '48' ) )
     *   array( 'check_in_day' => 'monday', 'accomodations' => array( '12' ) )
     *
     * @param  string $rules_field MIN_RULES_FIELD, MAX_RULES_FIELD or CHECK_IN_DAY_RULES_FIELD.
     * @return array
     */
    public static function get_rules( $rules_field ) {

        $settings = get_option( 'eshb_settings', array() );

        if ( ! is_array( $settings ) || empty( $settings[ $rules_field ] ) || ! is_array( $settings[ $rules_field ] ) ) {
            return array();
        }

        return $settings[ $rules_field ];
    }

    /**
     * The days a rule row can allow: "All Days" plus the seven weekdays.
     *
     * Keys match what the EHB Week add-on stored in post meta and what core compares
     * against (`strtolower( gmdate( 'l' ) )`), so nothing needs translating on the way
     * through.
     *
     * @return array
     */
    public static function get_check_in_day_options() {

        return array(
            self::TARGET_ALL => __( 'All Days', 'easy-hotel' ),
            'monday'         => __( 'Monday', 'easy-hotel' ),
            'tuesday'        => __( 'Tuesday', 'easy-hotel' ),
            'wednesday'      => __( 'Wednesday', 'easy-hotel' ),
            'thursday'       => __( 'Thursday', 'easy-hotel' ),
            'friday'         => __( 'Friday', 'easy-hotel' ),
            'saturday'       => __( 'Saturday', 'easy-hotel' ),
            'sunday'         => __( 'Sunday', 'easy-hotel' ),
        );
    }

    /**
     * Normalise a rule row's day value to a clean list of weekday keys.
     *
     * Accepts an array (the checkbox field) or a single string (what the EHB Week
     * add-on's select stored), so old data reads back without a migration step.
     *
     * @param  mixed $value
     * @return array
     */
    protected static function sanitize_check_in_days( $value ) {

        $valid = self::get_check_in_day_options();
        $days  = array();

        foreach ( (array) $value as $day ) {

            $day = strtolower( trim( (string) $day ) );

            if ( isset( $valid[ $day ] ) && ! in_array( $day, $days, true ) ) {
                $days[] = $day;
            }
        }

        return $days;
    }

    /**
     * The weekdays one accomodation may be checked into.
     *
     * Same targeting as the nights rules — a rule picking the accomodation beats a rule
     * set to "All". Unlike nights there is no stricter-of-two to fall back on, so when
     * several rules apply at the same level the first one in the table wins; that whole
     * row's set of days is used, rules are never merged together.
     *
     * @param  int $accomodation_id Pass 0 where no accomodation is in context yet.
     * @return array Weekday keys, or an empty array when any day is allowed.
     */
    public static function get_check_in_days_for_accomodation( $accomodation_id = 0 ) {

        $accomodation_id = absint( $accomodation_id );

        $specific = null;
        $for_all  = null;

        foreach ( self::get_rules( self::CHECK_IN_DAY_RULES_FIELD ) as $rule ) {

            $days = self::sanitize_check_in_days( isset( $rule['check_in_day'] ) ? $rule['check_in_day'] : array() );

            if ( empty( $days ) ) {
                continue;
            }

            $targets = isset( $rule['accomodations'] ) ? array_map( 'strval', (array) $rule['accomodations'] ) : array();

            if ( in_array( self::TARGET_ALL, $targets, true ) ) {
                // "All" is a check-all in the UI, so every accomodation id is ticked
                // alongside it — see resolve_nights_rule() for why those are skipped.
                $for_all = ( $for_all === null ) ? $days : $for_all;
                continue;
            }

            if ( $accomodation_id && in_array( (string) $accomodation_id, $targets, true ) ) {
                $specific = ( $specific === null ) ? $days : $specific;
            }
        }

        $days = ( $specific !== null ) ? $specific : $for_all;

        if ( $days === null ) {
            return array();
        }

        // "All Days" is no restriction at all, and it is what a single accomodation ticks
        // to opt out of an "All accomodations" rule.
        return in_array( self::TARGET_ALL, $days, true ) ? array() : $days;
    }

    /**
     * Carry per accomodation check-in days out of post meta into the rules repeater.
     *
     * The EHB Week add-on stored the day on each accomodation. That metabox belongs to
     * the add-on, so once it is deactivated there is nowhere left to edit those values —
     * this groups the accomodations by the day they were set to and writes one rule row
     * per distinct day, so the configuration survives.
     *
     * Runs at most once, and only on a site where the bundled module owns the feature.
     */
    public static function migrate_check_in_day_meta() {

        if ( get_option( self::CHECK_IN_DAY_MIGRATED_OPTION ) ) {
            return;
        }

        update_option( self::CHECK_IN_DAY_MIGRATED_OPTION, 1, false );

        $settings = get_option( 'eshb_settings', array() );
        $settings = is_array( $settings ) ? $settings : array();

        // Rules already configured — nothing to carry over.
        if ( ! empty( $settings[ self::CHECK_IN_DAY_RULES_FIELD ] ) ) {
            return;
        }

        $accomodations = get_posts( array(
            'post_type'              => 'eshb_accomodation',
            'post_status'            => 'any',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_term_cache' => false,
        ) );

        $valid = self::get_check_in_day_options();
        $by_day = array();

        foreach ( $accomodations as $accomodation_id ) {

            $meta = get_post_meta( $accomodation_id, 'eshb_accomodation_metaboxes', true );

            if ( ! is_array( $meta ) || empty( $meta['allowed_check_in_day'] ) ) {
                continue;
            }

            // The add-on's field was a single select, but be tolerant of an array.
            $day = is_array( $meta['allowed_check_in_day'] ) ? reset( $meta['allowed_check_in_day'] ) : $meta['allowed_check_in_day'];
            $day = strtolower( (string) $day );

            // "All days" was the default, so it carries no configuration worth keeping.
            if ( $day === self::TARGET_ALL || ! isset( $valid[ $day ] ) ) {
                continue;
            }

            $by_day[ $day ][] = (string) $accomodation_id;
        }

        if ( empty( $by_day ) ) {
            return;
        }

        $rules = array();

        foreach ( $by_day as $day => $ids ) {
            $rules[] = array(
                'check_in_day'  => array( $day ),
                'accomodations' => $ids,
            );
        }

        $settings[ self::CHECK_IN_DAY_RULES_FIELD ] = $rules;

        update_option( 'eshb_settings', $settings );
    }

    /**
     * Carry previously configured global limits into "All Accomodations" rules.
     *
     * The global "Minimum Nights" and "Maximum Nights" fields are gone, so without
     * this an upgrading site that required, say, 3 nights would silently drop back to
     * 1. Runs at most once — after that the repeaters are the only source and the site
     * is free to empty them.
     */
    public static function migrate_global_nights() {

        if ( get_option( self::MIGRATED_OPTION ) ) {
            return;
        }

        update_option( self::MIGRATED_OPTION, 1, false );

        $settings = get_option( 'eshb_settings', array() );
        $settings = is_array( $settings ) ? $settings : array();

        $legacy = get_option( self::LEGACY_OPTION, array() );
        $legacy = is_array( $legacy ) ? $legacy : array();

        $changed = false;

        // The old global value, the repeater it seeds, the row key it writes, and the
        // value that meant "no limit" — a rule for that would only be noise.
        $limits = array(
            array( 'required_min_nights', self::MIN_RULES_FIELD, 'min_nights', 1 ),
            array( 'required_max_nights', self::MAX_RULES_FIELD, 'max_nights', 999 ),
        );

        foreach ( $limits as list( $legacy_key, $rules_field, $nights_key, $no_op ) ) {

            // Rules already configured — nothing to carry over.
            if ( ! empty( $settings[ $rules_field ] ) ) {
                continue;
            }

            $previous = 0;

            foreach ( array( $settings, $legacy ) as $source ) {
                if ( ! empty( $source[ $legacy_key ] ) ) {
                    $previous = absint( $source[ $legacy_key ] );
                    break;
                }
            }

            if ( $previous < 1 || $previous === $no_op ) {
                continue;
            }

            $settings[ $rules_field ] = array(
                array(
                    $nights_key     => $previous,
                    'accomodations' => array( self::TARGET_ALL ),
                ),
            );

            $changed = true;
        }

        if ( $changed ) {
            update_option( 'eshb_settings', $settings );
        }
    }

    /**
     * Minimum nights required for one accomodation by the rules table.
     *
     * @param  int $accomodation_id Pass 0 where no accomodation is in context yet
     *                              (the search form) to get the "all" rule only.
     * @return int|null Nights, or null when no rule covers this accomodation.
     */
    public static function get_min_nights_for_accomodation( $accomodation_id = 0 ) {
        // Stricter means a longer stay, so a higher number wins.
        return self::resolve_nights_rule( self::MIN_RULES_FIELD, 'min_nights', $accomodation_id, 'max' );
    }

    /**
     * Maximum nights allowed for one accomodation by the rules table.
     *
     * @param  int $accomodation_id Pass 0 where no accomodation is in context yet
     *                              (the search form) to get the "all" rule only.
     * @return int|null Nights, or null when no rule covers this accomodation.
     */
    public static function get_max_nights_for_accomodation( $accomodation_id = 0 ) {
        // Stricter means a shorter stay, so a lower number wins.
        return self::resolve_nights_rule( self::MAX_RULES_FIELD, 'max_nights', $accomodation_id, 'min' );
    }

    /**
     * Resolve one limit for one accomodation out of its rules table.
     *
     * A rule naming the accomodation explicitly beats a rule targeting "all", so a
     * site can set one baseline and then override it for a handful of rooms. When more
     * than one rule applies at the same level the strictest one wins.
     *
     * @param  string $rules_field     MIN_RULES_FIELD or MAX_RULES_FIELD.
     * @param  string $nights_key      Row key holding the number of nights.
     * @param  int    $accomodation_id Accomodation, or 0 for the "all" rules only.
     * @param  string $strictest       'max' or 'min' — which way is stricter for this limit.
     * @return int|null Nights, or null when no rule covers this accomodation.
     */
    protected static function resolve_nights_rule( $rules_field, $nights_key, $accomodation_id, $strictest ) {

        $accomodation_id = absint( $accomodation_id );

        $specific = null;
        $for_all  = null;

        foreach ( self::get_rules( $rules_field ) as $rule ) {

            $nights = isset( $rule[ $nights_key ] ) ? absint( $rule[ $nights_key ] ) : 0;

            if ( $nights < 1 ) {
                continue;
            }

            // Checkbox values come back as strings, post ids are compared as such.
            $targets = isset( $rule['accomodations'] ) ? array_map( 'strval', (array) $rule['accomodations'] ) : array();

            if ( in_array( self::TARGET_ALL, $targets, true ) ) {
                // "All" is a check-all in the UI, so every accomodation id is ticked
                // alongside it. Treat the rule as the baseline anyway, or those ids
                // would count as explicit targets and outrank a real per accomodation
                // rule instead of losing to it.
                $for_all = ( $for_all === null ) ? $nights : $strictest( $for_all, $nights );
                continue;
            }

            if ( $accomodation_id && in_array( (string) $accomodation_id, $targets, true ) ) {
                $specific = ( $specific === null ) ? $nights : $strictest( $specific, $nights );
            }
        }

        return ( $specific !== null ) ? $specific : $for_all;
    }

    /**
     * Checkbox options for a rule row: "All" followed by every published
     * accomodation.
     *
     * Resolved lazily by the field itself (see eshb_booking_rules_accomodation_options)
     * so the query only runs when the tab is actually rendered.
     *
     * @return array
     */
    public static function get_accomodation_options() {

        static $options = null;

        if ( $options !== null ) {
            return $options;
        }

        $options = array( self::TARGET_ALL => __( 'All', 'easy-hotel' ) );

        $accomodations = get_posts( array(
            'post_type'              => 'eshb_accomodation',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'orderby'                => 'title',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ) );

        foreach ( $accomodations as $accomodation ) {
            $options[ $accomodation->ID ] = ( $accomodation->post_title !== '' ) ? $accomodation->post_title : sprintf( '#%d', $accomodation->ID );
        }

        return $options;
    }

    /**
     * Mirror the tab values back into the legacy options, so anything still reading
     * `eshb_min_max_settings` or `eshb_week_settings` directly keeps seeing the current
     * configuration.
     *
     * @param array $data Saved `eshb_settings` data.
     */
    public function sync_legacy_option( $data ) {

        if ( ! is_array( $data ) ) {
            return;
        }

        $map = array(
            self::LEGACY_OPTION      => self::FIELDS,
            self::WEEK_LEGACY_OPTION => self::WEEK_FIELDS,
        );

        foreach ( $map as $option => $fields ) {

            $legacy = get_option( $option, array() );
            $legacy = is_array( $legacy ) ? $legacy : array();

            $changed = false;

            foreach ( $fields as $field ) {
                if ( array_key_exists( $field, $data ) ) {
                    $legacy[ $field ] = $data[ $field ];
                    $changed = true;
                }
            }

            if ( $changed ) {
                update_option( $option, $legacy );
            }
        }
    }
}

new ESHB_Booking_Rules();

/**
 * Checkbox `options` resolver for the minimum nights rules repeater.
 *
 * The settings framework accepts any callable as an options source and calls it
 * while rendering the field, which keeps the accomodation query off every other
 * admin request. Signature matches ESHB_Fields::field_data().
 *
 * @param  string|false $term       Search term, unused — the list is always complete.
 * @param  array        $query_args Unused.
 * @return array
 */
if ( ! function_exists( 'eshb_booking_rules_accomodation_options' ) ) {
    function eshb_booking_rules_accomodation_options( $term = false, $query_args = array() ) {
        return ESHB_Booking_Rules::get_accomodation_options();
    }
}
