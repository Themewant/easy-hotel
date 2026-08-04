<?php
/**
 * "Assigned Room" metabox on the booking screen.
 *
 * Declared through the plugin's own settings framework, like every other Easy Hotel metabox.
 * The unit selects themselves go through a framework `callback` field, because how many
 * selects to draw depends on the booking's `room_quantity` and which options are offered
 * depends on live occupancy — neither is known when the framework collects its sections.
 * The field still carries an `id`, so the framework picks the submitted value up, runs our
 * `sanitize` handler on it and writes it with the rest of the metabox data.
 *
 * @package Easy_Hotel\PMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Unit assignment editor for a booking.
 */
class ESHB_PMS_Booking_Metabox {

    /**
     * Declares the metabox with the settings framework.
     *
     * Runs on `plugins_loaded`, i.e. before `init`, so the labels here are plain strings —
     * translating this early triggers the WordPress 6.7+
     * "_load_textdomain_just_in_time was called incorrectly" notice. Everything the user
     * reads inside the metabox is produced by `render_field()`, which runs at render time
     * and is fully translated.
     */
    public static function register() {

        if ( ! class_exists( 'ESHB' ) ) {
            return;
        }

        ESHB::createSection(
            ESHB_PMS_ASSIGN_META,
            [
                'title'  => '',
                'fields' => [
                    [
                        'id'       => ESHB_PMS_UNITS_FIELD,
                        'type'     => 'callback',
                        'function' => [ __CLASS__, 'render_field' ],
                        'sanitize' => [ __CLASS__, 'sanitize_field' ],
                    ],
                    [
                        'id'       => 'auto_assign',
                        'type'     => 'switcher',
                        'title'    => 'Auto-assign',
                        'subtitle' => 'Fill the empty rooms with the first free unit whenever this booking is saved.',
                        'default'  => false,
                    ],
                ],
            ]
        );

        ESHB::createMetabox(
            ESHB_PMS_ASSIGN_META,
            [
                'title'              => 'PMS — Assigned Room',
                'post_type'          => ESHB_PMS_Assignment::POST_TYPE,
                'data_type'          => 'serialize',
                'context'            => 'side',
                'priority'           => 'high',
                'exclude_post_types' => [],
                'show_restore'       => false,
                'enqueue_webfont'    => true,
                'async_webfont'      => false,
                'output_css'         => false,
                'nav'                => 'inline',
                'theme'              => 'light',
                'class'              => '',
            ]
        );

        // The framework hands us the collected data right before it writes the meta.
        add_filter( 'eshb_' . ESHB_PMS_ASSIGN_META . '_save', [ __CLASS__, 'on_save' ], 10, 2 );

        add_filter( 'manage_eshb_booking_posts_columns', [ __CLASS__, 'add_column' ], 20 );
        add_action( 'manage_eshb_booking_posts_custom_column', [ __CLASS__, 'render_column' ], 20, 2 );
    }

    /**
     * Framework `callback` field: one select per booked room.
     *
     * Inputs are named after the metabox unique key so the framework collects them exactly
     * like a native field.
     */
    public static function render_field() {

        global $post;

        if ( ! is_object( $post ) ) {
            return;
        }

        $booking = ESHB_PMS_Assignment::get_booking( $post->ID );

        if ( ! $booking ) {
            printf(
                '<div class="csf-notice csf-notice-warning">%s</div>',
                esc_html__( 'Save the accommodation and the stay dates first, then a room can be assigned here.', 'easy-hotel' )
            );
            return;
        }

        // Clears the framework's floats before our own stacked blocks start.
        echo '<div class="eshb-pms-assign">';

        $accommodation_id = $booking['accommodation_id'];
        $units            = ESHB_PMS_Units::get_units( $accommodation_id );
        $slots            = ESHB_PMS_Assignment::get_assignment( $post->ID );
        $taken            = ESHB_PMS_Assignment::get_taken_units( $accommodation_id, $booking['dates'], $booking['id'] );
        $name             = ESHB_PMS_ASSIGN_META . '[' . ESHB_PMS_UNITS_FIELD . ']';

        printf(
            '<div class="csf-desc-text eshb-pms-desc eshb-pms-desc--lead">%s</div>',
            esc_html(
                sprintf(
                    /* translators: 1: check-in date, 2: check-out date. */
                    esc_html__( 'Stay: %1$s → %2$s', 'easy-hotel' ),
                    $booking['start_date'],
                    $booking['end_date']
                )
            )
        );

        foreach ( $slots as $slot => $assigned_unit ) {

            /*
             * Reuses the framework's typography classes but not `csf-field` itself: this
             * whole callback already *is* one `csf-field`, and nesting another would double
             * its padding and draw a separator meant for sibling fields.
             */
            echo '<div class="eshb-pms-slot">';

            printf(
                '<div class="csf-title"><h4>%s</h4></div>',
                esc_html(
                    sprintf(
                        /* translators: %d: room number within the booking. */
                        esc_html__( 'Room %d', 'easy-hotel' ),
                        (int) $slot + 1
                    )
                )
            );

            echo '<div class="csf-fieldset">';
            printf(
                '<select name="%s[%d]" class="widefat">',
                esc_attr( $name ),
                (int) $slot
            );

            printf(
                '<option value="0"%s>%s</option>',
                selected( $assigned_unit, ESHB_PMS_Assignment::UNASSIGNED, false ),
                esc_html__( '— Not assigned —', 'easy-hotel' )
            );

            foreach ( $units as $index => $unit ) {

                $conflict     = isset( $taken[ $index ] ) ? (int) $taken[ $index ] : 0;
                $used_by_self = ( in_array( $index, $slots, true ) && $assigned_unit !== $index );
                $label        = $unit['label'];

                if ( '' !== $unit['floor'] ) {
                    $label .= ' (' . $unit['floor'] . ')';
                }

                if ( $conflict ) {
                    /* translators: %d: booking ID. */
                    $label .= ' — ' . sprintf( esc_html__( 'busy, booking #%d', 'easy-hotel' ), $conflict );
                } elseif ( $used_by_self ) {
                    $label .= ' — ' . esc_html__( 'used by another room of this booking', 'easy-hotel' );
                }

                printf(
                    '<option value="%d"%s%s>%s</option>',
                    (int) $index,
                    selected( $assigned_unit, $index, false ),
                    disabled( ( $conflict || $used_by_self ) && $assigned_unit !== $index, true, false ),
                    esc_html( $label )
                );
            }

            echo '</select>';
            echo '</div>';
            echo '</div>';
        }

        printf(
            '<div class="csf-desc-text eshb-pms-desc"><a href="%s">%s</a></div>',
            esc_url(
                admin_url(
                    'admin.php?page=' . ESHB_PMS_Rack::PAGE
                    . '&accommodation=' . (int) $accommodation_id
                    . '&from=' . rawurlencode( $booking['start_date'] )
                )
            ),
            esc_html__( 'Open in room rack', 'easy-hotel' )
        );

        echo '</div>';
    }

    /**
     * Framework `sanitize` handler for the assignment field.
     *
     * Only casts here — the conflict check needs the post ID, which the framework passes to
     * the save filter rather than to the sanitizer.
     *
     * @param mixed $value Raw submitted slot list.
     *
     * @return int[]
     */
    public static function sanitize_field( $value ) {

        if ( ! is_array( $value ) ) {
            return [];
        }

        ksort( $value, SORT_NUMERIC );

        return array_values( array_map( 'intval', $value ) );
    }

    /**
     * Framework save filter: validates the submitted assignment against live occupancy.
     *
     * Runs inside the framework's own `save_post` pass, right before the meta is written, so
     * the module never writes this meta key itself on the booking screen.
     *
     * @param array $data    Collected metabox data.
     * @param int   $post_id Booking post ID.
     *
     * @return array
     */
    public static function on_save( $data, $post_id ) {

        // The engine's own metabox saved `eshb_booking_metaboxes` earlier in this request.
        ESHB_PMS_Assignment::flush_cache();

        $booking = ESHB_PMS_Assignment::get_booking( $post_id );

        if ( ! $booking ) {
            return $data;
        }

        $requested = isset( $data[ ESHB_PMS_UNITS_FIELD ] ) ? (array) $data[ ESHB_PMS_UNITS_FIELD ] : [];

        $slots = ESHB_PMS_Assignment::normalise_slots( $booking, $requested );
        $slots = ESHB_PMS_Assignment::drop_conflicting_slots( $booking, $slots );

        $auto = ! empty( $data['auto_assign'] ) || ESHB_PMS::get_option( 'auto_assign', 0 );

        if ( $auto ) {
            $slots = ESHB_PMS_Assignment::fill_empty_slots( $booking, $slots );
        }

        $data[ ESHB_PMS_UNITS_FIELD ] = $slots;

        /**
         * Fires after a booking's unit assignment changed.
         *
         * @param int   $post_id Booking post ID.
         * @param int[] $slots   Stored slots.
         */
        do_action( 'eshb_pms_assignment_updated', (int) $post_id, $slots );

        return $data;
    }

    /**
     * Adds an "Assigned room" column to the bookings list table.
     *
     * @param array $columns Existing columns.
     *
     * @return array
     */
    public static function add_column( $columns ) {

        $columns['eshb_pms_unit'] = esc_html__( 'Assigned Room', 'easy-hotel' );

        return $columns;
    }

    /**
     * Renders the "Assigned room" column.
     *
     * @param string $column  Column key.
     * @param int    $post_id Booking post ID.
     */
    public static function render_column( $column, $post_id ) {

        if ( 'eshb_pms_unit' !== $column ) {
            return;
        }

        $booking = ESHB_PMS_Assignment::get_booking( $post_id );

        if ( ! $booking ) {
            echo '—';
            return;
        }

        $labels = [];

        foreach ( ESHB_PMS_Assignment::get_assignment( $post_id ) as $unit ) {

            if ( ESHB_PMS_Assignment::UNASSIGNED === $unit ) {
                continue;
            }

            $labels[] = ESHB_PMS_Units::get_label( $booking['accommodation_id'], $unit );
        }

        if ( empty( $labels ) ) {
            echo '<span class="eshb-pms-badge eshb-pms-badge--empty">' . esc_html__( 'Unassigned', 'easy-hotel' ) . '</span>';
            return;
        }

        echo '<span class="eshb-pms-badge">' . esc_html( implode( ', ', $labels ) ) . '</span>';
    }
}
