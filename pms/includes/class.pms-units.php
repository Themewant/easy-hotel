<?php
/**
 * Unit registry for the PMS module.
 *
 * A "unit" is one physical room of an accommodation (room type). The number of units is
 * NOT stored by this module: it is read from the `total_rooms` capacity field already owned
 * by the booking engine, so the two can never drift apart. Only the optional descriptive
 * data (room number, floor) lives in this module's own meta key.
 *
 * @package Easy_Hotel\PMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Reads and writes the optional unit list of an accommodation.
 */
class ESHB_PMS_Units {

    /**
     * Accommodation post type.
     */
    const POST_TYPE = 'eshb_accomodation';

    /**
     * Meta key owned by the booking engine. Read only from here.
     */
    const ENGINE_META = 'eshb_accomodation_metaboxes';

    /**
     * Hard ceiling on how many unit rows the rack will draw, to keep the chart usable.
     */
    const MAX_RENDERED_UNITS = 100;

    /**
     * Number of physical units of an accommodation.
     *
     * Mirrors the `total_rooms` capacity field of the booking engine.
     *
     * @param int $accommodation_id Accommodation post ID.
     *
     * @return int Always at least 1.
     */
    public static function get_total_units( $accommodation_id ) {

        $metas = get_post_meta( (int) $accommodation_id, self::ENGINE_META, true );
        $total = ( is_array( $metas ) && isset( $metas['total_rooms'] ) ) ? (int) $metas['total_rooms'] : 0;

        return max( 1, $total );
    }

    /**
     * Full unit list of an accommodation, normalised to 1..total_units.
     *
     * Units the hotelier never described still come back, with an auto generated label, so
     * the rest of the module can always address a unit by its index.
     *
     * @param int $accommodation_id Accommodation post ID.
     *
     * @return array<int, array{label:string, floor:string, custom:bool}>
     */
    public static function get_units( $accommodation_id ) {

        $accommodation_id = (int) $accommodation_id;
        $total            = self::get_total_units( $accommodation_id );
        $stored           = self::get_stored_units( $accommodation_id );
        $units            = [];

        for ( $index = 1; $index <= $total; $index++ ) {

            $row    = isset( $stored[ $index ] ) ? $stored[ $index ] : [];
            $label  = isset( $row['label'] ) ? trim( (string) $row['label'] ) : '';
            $custom = ( '' !== $label );

            $units[ $index ] = [
                'label'  => $custom ? $label : self::get_fallback_label( $index ),
                'floor'  => isset( $row['floor'] ) ? trim( (string) $row['floor'] ) : '',
                'custom' => $custom,
            ];
        }

        /**
         * Filters the normalised unit list of an accommodation.
         *
         * @param array $units            Unit list keyed by 1 based index.
         * @param int   $accommodation_id Accommodation post ID.
         */
        return apply_filters( 'eshb_pms_accommodation_units', $units, $accommodation_id );
    }

    /**
     * Unit rows exactly as the settings framework stored them.
     *
     * The rows come from a framework `group` field, so they are a sequential, zero based list
     * and their position *is* the unit index: the first row describes unit #1. Rows beyond the
     * current `total_rooms` are kept in the database, so lowering and then raising the
     * capacity does not destroy the room numbers the hotelier typed in.
     *
     * @param int $accommodation_id Accommodation post ID.
     *
     * @return array<int, array{label:string, floor:string}> Keyed by 1 based index.
     */
    public static function get_stored_units( $accommodation_id ) {

        $meta = get_post_meta( (int) $accommodation_id, ESHB_PMS_UNITS_META, true );
        $rows = ( is_array( $meta ) && isset( $meta[ ESHB_PMS_UNITS_FIELD ] ) ) ? $meta[ ESHB_PMS_UNITS_FIELD ] : [];

        if ( ! is_array( $rows ) ) {
            return [];
        }

        $units = [];
        $index = 0;

        foreach ( $rows as $row ) {

            if ( ! is_array( $row ) ) {
                continue;
            }

            $index++;

            $units[ $index ] = [
                'label' => isset( $row['label'] ) ? trim( (string) $row['label'] ) : '',
                'floor' => isset( $row['floor'] ) ? trim( (string) $row['floor'] ) : '',
            ];
        }

        return $units;
    }

    /**
     * Sanitize callback for the framework `group` field.
     *
     * Registered as the field's `sanitize` handler, so the framework calls it during its own
     * `save_post` pass, before `ESHB_PMS_Accommodation_Metabox::extract_units()` moves the
     * cleaned rows into this module's meta key.
     *
     * @param mixed $value Raw submitted rows.
     *
     * @return array Cleaned rows, re-indexed from zero.
     */
    public static function sanitize_units( $value ) {

        if ( ! is_array( $value ) ) {
            return [];
        }

        $rows = [];

        foreach ( $value as $row ) {

            if ( ! is_array( $row ) ) {
                continue;
            }

            $rows[] = [
                'label' => isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '',
                'floor' => isset( $row['floor'] ) ? sanitize_text_field( $row['floor'] ) : '',
            ];
        }

        return $rows;
    }

    /**
     * Display label of a single unit.
     *
     * @param int $accommodation_id Accommodation post ID.
     * @param int $index            1 based unit index.
     *
     * @return string
     */
    public static function get_label( $accommodation_id, $index ) {

        $index = (int) $index;

        if ( $index < 1 ) {
            return esc_html__( 'Unassigned', 'easy-hotel' );
        }

        $units = self::get_units( $accommodation_id );

        return isset( $units[ $index ] ) ? $units[ $index ]['label'] : self::get_fallback_label( $index );
    }

    /**
     * Label used when the hotelier has not named a unit.
     *
     * @param int $index 1 based unit index.
     *
     * @return string
     */
    public static function get_fallback_label( $index ) {
        /* translators: %d: unit number. */
        return sprintf( esc_html__( 'Unit %d', 'easy-hotel' ), (int) $index );
    }

    /**
     * Room numbers used by more than one unit of the same accommodation.
     *
     * Scoped to a single accommodation on purpose. Two accommodations sharing a number is
     * legitimate — Building A and Building B can both have a room 101 — and the module could
     * not tell that apart from the same physical room listed twice anyway. Within one
     * accommodation there is no such reading: the rack would draw two identical rows and no
     * one could tell which booking sits in which room.
     *
     * Comparison is case and whitespace insensitive, since "101" and "101 " are the same room
     * to everyone except `===`. Unnamed rows are skipped — they fall back to "Unit 1",
     * "Unit 2" and so on, which are distinct by construction.
     *
     * Returned as a list rather than a label keyed map: PHP casts a numeric string array key
     * to an integer, and room numbers are numeric far more often than not, so "101" would
     * silently come back as `101`.
     *
     * @param int $accommodation_id Accommodation post ID.
     *
     * @return array<int, array{label:string, indexes:int[]}> One entry per reused number.
     */
    public static function find_duplicate_labels( $accommodation_id ) {

        $seen = [];

        foreach ( self::get_stored_units( $accommodation_id ) as $index => $unit ) {

            $key = strtolower( preg_replace( '/\s+/', ' ', trim( $unit['label'] ) ) );

            if ( '' === $key ) {
                continue;
            }

            if ( ! isset( $seen[ $key ] ) ) {
                // Keeps the first spelling, so the notice echoes what was typed.
                $seen[ $key ] = [
                    'label'   => $unit['label'],
                    'indexes' => [],
                ];
            }

            $seen[ $key ]['indexes'][] = (int) $index;
        }

        $duplicates = [];

        foreach ( $seen as $entry ) {
            if ( count( $entry['indexes'] ) > 1 ) {
                $duplicates[] = $entry;
            }
        }

        return $duplicates;
    }

    /**
     * All published accommodations, as `id => title` pairs.
     *
     * @return array<int, string>
     */
    public static function get_accommodations() {

        static $cache = null;

        if ( ! is_null( $cache ) ) {
            return $cache;
        }

        $posts = get_posts(
            [
                'post_type'        => self::POST_TYPE,
                'post_status'      => [ 'publish', 'draft', 'private' ],
                'posts_per_page'   => -1,
                'orderby'          => 'title',
                'order'            => 'ASC',
                'suppress_filters' => false,
            ]
        );

        $cache = [];

        foreach ( $posts as $post ) {
            $cache[ (int) $post->ID ] = $post->post_title;
        }

        return $cache;
    }
}
