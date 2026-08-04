<?php
/**
 * "Rooms" tab on the accommodation (room type) screen.
 *
 * The tab is appended to the booking engine's own "Options & Features" metabox — the same nav
 * that already holds Capacity, Pricing, Basic Info and Services — by calling
 * `ESHB::createSection()` with the engine's metabox key. No engine file is touched: the
 * settings framework collects sections into a shared registry, so the module only adds one
 * more, and it lands last in the nav because the module declares on `plugins_loaded` at
 * priority 20, after the engine's priority 10.
 *
 * The unit rows are still stored in the module's own meta key, never inside the engine's
 * serialized array. `extract_units()` lifts them out of the metabox payload before the
 * framework writes it, which is what keeps the isolation contract intact: remove the module
 * and `eshb_accomodation_metaboxes` is exactly what it always was, while the unit rows survive
 * in their own key instead of being wiped by the next save of the accommodation.
 *
 * The unit list itself is a stock framework `group` field — the same accordion used by the
 * Basic Info tab. Filling it in is optional, and an unnamed unit still exists and can still be
 * assigned, it just shows up as "Unit 3" instead of "Room 302".
 *
 * @package Easy_Hotel\PMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Unit list editor for an accommodation.
 */
class ESHB_PMS_Accommodation_Metabox {

    /**
     * Metabox the "Rooms" tab is added to. Owned by the booking engine.
     */
    const ENGINE_METABOX = 'eshb_accomodation_metaboxes';

    /**
     * Field ID of the unit list inside that metabox.
     *
     * Prefixed, so it can never collide with a field the engine declares in the same metabox.
     */
    const FIELD_ID = 'eshb_pms_units';

    /**
     * Declares the "Rooms" section with the settings framework.
     *
     * Must run before `init`, which is when `ESHB::setup()` turns the declarations into real
     * metabox instances. Because of that, the labels below are plain strings: calling a
     * translation function this early makes WordPress 6.7+ emit the
     * "_load_textdomain_just_in_time was called incorrectly" notice. This matches how every
     * other Easy Hotel metabox declares its fields — translated strings live in the render
     * callbacks further down, which run well after `init`.
     */
    public static function register() {

        if ( ! class_exists( 'ESHB' ) ) {
            return;
        }

        ESHB::createSection(
            self::ENGINE_METABOX,
            [
                'title'  => 'Rooms',
                'fields' => [
                    [
                        // No `id`: purely informative, nothing to persist.
                        'type'     => 'callback',
                        'function' => [ __CLASS__, 'render_summary' ],
                    ],
                    [
                        'id'                     => self::FIELD_ID,
                        'type'                   => 'group',
                        'title'                  => 'Units',
                        'subtitle'               => 'One row per physical room, in order. Row 1 is unit #1.',
                        'button_title'           => 'Add Unit',
                        // Accordion header reads "1. 101": the number is the unit index the
                        // rest of the module addresses the unit by, the value is whatever was
                        // typed into the first sub field.
                        'accordion_title_number' => true,
                        'accordion_title_by'     => [ 'label' ],
                        // The framework's default field layout is a 20% title / 80% control
                        // split, which leaves the accordion no room to breathe. This opts the
                        // field into the stacked layout defined in `assets/css/pms-admin.css`:
                        // title and subtitle on their own row, rows full width underneath.
                        'class'                  => 'eshb-pms-stacked',
                        // The rows live in the module's own meta key, so the framework finds
                        // nothing for this field in the metabox payload and falls back to the
                        // declared default — which makes the default the loader. See
                        // `extract_units()` for the other half of the round trip.
                        'default'                => self::stored_rows(),
                        'sanitize'               => [ 'ESHB_PMS_Units', 'sanitize_units' ],
                        'fields'                 => [
                            [
                                'id'          => 'label',
                                'type'        => 'text',
                                'title'       => 'Room number / label',
                                'placeholder' => '101',
                            ],
                            [
                                'id'          => 'floor',
                                'type'        => 'text',
                                'title'       => 'Floor / wing',
                                'placeholder' => 'Ground',
                            ],
                        ],
                    ],
                    [
                        // No `id`: purely informative, nothing to persist.
                        'type'     => 'callback',
                        'function' => [ __CLASS__, 'render_footer' ],
                    ],
                ],
            ]
        );

        add_filter( 'eshb_' . self::ENGINE_METABOX . '_save', [ __CLASS__, 'extract_units' ], 10, 2 );
    }

    /**
     * Moves the submitted unit rows out of the engine payload and into the module's meta key.
     *
     * Hooked on the framework's own `eshb_{$unique}_save` filter, which runs after the fields
     * have been sanitized and before the metabox array is written — so the module never has to
     * touch `save_post` itself, and the engine's meta never carries a PMS key.
     *
     * @param array $data    Sanitized metabox data about to be stored.
     * @param int   $post_id Post being saved.
     *
     * @return array Data without the unit rows.
     */
    public static function extract_units( $data, $post_id ) {

        if ( ! is_array( $data ) || ! array_key_exists( self::FIELD_ID, $data ) ) {
            return $data;
        }

        $rows = $data[ self::FIELD_ID ];

        // Removed in every case — revisions and autosaves included — so the key can never end
        // up inside `eshb_accomodation_metaboxes`.
        unset( $data[ self::FIELD_ID ] );

        if ( ESHB_PMS_Units::POST_TYPE !== get_post_type( $post_id ) ) {
            return $data;
        }

        // Merged rather than replaced, so anything else the module may keep under this key
        // survives a save of the accommodation.
        $meta = get_post_meta( $post_id, ESHB_PMS_UNITS_META, true );
        $meta = is_array( $meta ) ? $meta : [];

        unset( $meta[ ESHB_PMS_UNITS_FIELD ] );

        if ( is_array( $rows ) && ! empty( $rows ) ) {
            $meta[ ESHB_PMS_UNITS_FIELD ] = $rows;
        }

        if ( empty( $meta ) ) {
            delete_post_meta( $post_id, ESHB_PMS_UNITS_META );
        } else {
            update_post_meta( $post_id, ESHB_PMS_UNITS_META, $meta );
        }

        return $data;
    }

    /**
     * Stored unit rows of the accommodation currently on screen, as the `group` field wants them.
     *
     * @return array<int, array{label:string, floor:string}> Plain list, zero based.
     */
    private static function stored_rows() {

        $post_id = self::current_post_id();

        if ( ! $post_id ) {
            return [];
        }

        // `get_stored_units()` keys its rows by 1 based unit index; the field wants a list.
        return array_values( ESHB_PMS_Units::get_stored_units( $post_id ) );
    }

    /**
     * The post being edited or saved, as far as the request can tell.
     *
     * Sections are declared on `plugins_loaded`, long before `$post` exists, so the ID has to
     * come from the request. Nothing is written here — the value is only used to look up rows
     * for display, and the framework verifies its own nonce before anything is stored.
     *
     * @return int Post ID, or 0 on any screen that is not editing a single post.
     */
    private static function current_post_id() {

        if ( ! is_admin() ) {
            return 0;
        }

        // phpcs:disable WordPress.Security.NonceVerification
        if ( isset( $_GET['post'] ) ) {
            return (int) $_GET['post'];
        }

        if ( isset( $_POST['post_ID'] ) ) {
            return (int) $_POST['post_ID'];
        }
        // phpcs:enable

        return 0;
    }

    /**
     * Framework `callback` field: how many units this room type currently has.
     *
     * Rendered on the post edit screen, long after `init`, so translation functions are safe
     * to use here — unlike in the declaration above.
     */
    public static function render_summary() {

        global $post;

        if ( ! is_object( $post ) ) {
            return;
        }

        $total  = ESHB_PMS_Units::get_total_units( $post->ID );
        $stored = count( ESHB_PMS_Units::get_stored_units( $post->ID ) );

        $message = sprintf(
            /* translators: %d: number of units. */
            esc_html__( 'This room type currently has %d unit(s), taken from the "Total Rooms" field in the Capacity tab. The unit count is never stored twice, so the two can not drift apart.', 'easy-hotel' ),
            (int) $total
        );

        printf( '<div class="csf-notice csf-notice-normal">%s</div>', esc_html( $message ) );

        // `csf-desc-text` is the framework's own description class: it clears the float and
        // applies the muted styling every other field description gets.
        printf(
            '<div class="csf-desc-text eshb-pms-desc">%s</div>',
            esc_html__( 'Naming the units is optional. Leave a row empty and the unit is still bookable — it is simply shown as "Unit 1", "Unit 2" and so on. Availability is always calculated on the room type, never on these units.', 'easy-hotel' )
        );

        self::render_duplicate_warning( $post->ID );

        if ( $stored > $total ) {
            printf(
                '<div class="csf-notice csf-notice-warning">%s</div>',
                esc_html(
                    sprintf(
                        /* translators: 1: stored rows, 2: capacity. */
                        esc_html__( 'There are %1$d rows but only %2$d unit(s) of capacity. The extra rows are kept but ignored until "Total Rooms" is raised again.', 'easy-hotel' ),
                        (int) $stored,
                        (int) $total
                    )
                )
            );
        }
    }

    /**
     * Warns about room numbers reused inside this accommodation.
     *
     * Deliberately a warning and not a save blocker. The number is a label, not the unit's
     * identity — that is the row's position — so a duplicate breaks nothing in the data, it
     * only makes the rack unreadable. Refusing the save would also throw away everything else
     * typed on the screen over a cosmetic field, which is a worse trade than one loud notice.
     *
     * Only same accommodation duplicates are reported: two accommodations sharing a room
     * number is a normal multi building setup.
     *
     * @param int $accommodation_id Accommodation post ID.
     */
    private static function render_duplicate_warning( $accommodation_id ) {

        $duplicates = ESHB_PMS_Units::find_duplicate_labels( $accommodation_id );

        if ( empty( $duplicates ) ) {
            return;
        }

        // One list item per reused number, so several duplicates stay readable.
        $items = '';

        foreach ( $duplicates as $duplicate ) {

            $positions = [];

            foreach ( $duplicate['indexes'] as $index ) {
                /* translators: %d: row number in the units list. */
                $positions[] = sprintf( esc_html__( 'row %d', 'easy-hotel' ), (int) $index );
            }

            $items .= sprintf(
                '<li><code>%1$s</code> — %2$s</li>',
                esc_html( $duplicate['label'] ),
                esc_html( implode( ', ', $positions ) )
            );
        }

        printf(
            '<div class="csf-notice csf-notice-danger eshb-pms-dupes">'
                . '<strong class="eshb-pms-dupes-title">%1$s</strong>'
                . '<ul class="eshb-pms-dupes-list">%2$s</ul>'
                . '<p class="eshb-pms-dupes-hint">%3$s</p>'
                . '<p class="eshb-pms-dupes-note">%4$s</p>'
            . '</div>',
            esc_html__( 'The same room number is used twice in this accommodation:', 'easy-hotel' ),
            $items,
            esc_html__( 'Nothing breaks if you leave it — a booking tracks the row position, not the number. But the room rack and the bookings list will show two rooms with the same name, so staff cannot tell which guest is in which room.', 'easy-hotel' ),
            esc_html__( 'Reusing a number in a different accommodation is fine and is never reported here.', 'easy-hotel' )
        );
    }

    /**
     * Framework `callback` field: link to the rack for this accommodation.
     */
    public static function render_footer() {

        global $post;

        if ( ! is_object( $post ) ) {
            return;
        }

        printf(
            '<div class="csf-desc-text eshb-pms-desc"><a href="%s">%s</a></div>',
            esc_url( admin_url( 'admin.php?page=' . ESHB_PMS_Rack::PAGE . '&accommodation=' . (int) $post->ID ) ),
            esc_html__( 'Open the room rack for this accommodation', 'easy-hotel' )
        );
    }
}
