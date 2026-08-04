<?php
/**
 * Unit assignment engine for the PMS module.
 *
 * Everything in this class is downstream of the booking engine: a booking is created and
 * counted against `total_rooms` exactly as before, and only afterwards may a physical unit
 * be attached to it. An unassigned booking is a normal, valid state - never an error - which
 * is what keeps the PMS layer optional and keeps channel/OTA style room-type inventory
 * intact.
 *
 * @package Easy_Hotel\PMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Reads and writes the per booking unit assignment.
 */
class ESHB_PMS_Assignment {

    /**
     * Booking post type.
     */
    const POST_TYPE = 'eshb_booking';

    /**
     * Meta key owned by the booking engine. Read only from here.
     */
    const ENGINE_META = 'eshb_booking_metaboxes';

    /**
     * Value stored in an assignment slot that has no unit yet.
     */
    const UNASSIGNED = 0;

    /**
     * Per request cache of every booking record.
     *
     * @var array<int, array>|null
     */
    protected static $bookings = null;

    /**
     * Booking statuses that actually occupy a room.
     *
     * Kept in sync with the statuses the booking engine counts in
     * `ESHB_Booking::get_available_room_count_by_date_range()`.
     *
     * @return string[]
     */
    public static function occupying_statuses() {

        return apply_filters(
            'eshb_pms_occupying_statuses',
            [ 'pending', 'processing', 'on-hold', 'completed', 'deposit-payment' ]
        );
    }

    /**
     * Nights covered by a stay, using the same convention as the booking engine:
     * the check-out day is not occupied, and a same day booking occupies one night.
     *
     * @param string $start_date `Y-m-d` check-in.
     * @param string $end_date   `Y-m-d` check-out.
     *
     * @return string[] List of `Y-m-d` dates.
     */
    public static function get_stay_dates( $start_date, $end_date ) {

        if ( empty( $start_date ) || empty( $end_date ) ) {
            return [];
        }

        try {
            $start = new DateTime( $start_date );
            $end   = new DateTime( $end_date );
        } catch ( Exception $e ) {
            return [];
        }

        $start->setTime( 0, 0, 0 );
        $end->setTime( 0, 0, 0 );

        if ( $end < $start ) {
            return [];
        }

        if ( $end == $start ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- DateTime comparison.
            return [ $start->format( 'Y-m-d' ) ];
        }

        $dates = [];

        foreach ( new DatePeriod( $start, new DateInterval( 'P1D' ), $end ) as $date ) {
            $dates[] = $date->format( 'Y-m-d' );
        }

        return $dates;
    }

    /**
     * Every booking of the site, normalised, in a single query.
     *
     * The booking engine stores its data in one serialized meta row, so it cannot be
     * filtered with `meta_query`. Loading the rows once per request and filtering in PHP is
     * both faster and simpler than the repeated `WP_Query` the engine itself performs.
     *
     * @return array<int, array> Normalised booking records keyed by booking ID.
     */
    public static function get_all_bookings() {

        if ( ! is_null( self::$bookings ) ) {
            return self::$bookings;
        }

        global $wpdb;

        $rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- No core API can read a serialized meta subkey.
            $wpdb->prepare(
                "SELECT p.ID, p.post_title, pm.meta_value
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = %s
                 WHERE p.post_type = %s
                   AND p.post_status NOT IN ( 'trash', 'auto-draft' )",
                self::ENGINE_META,
                self::POST_TYPE
            )
        );

        self::$bookings = [];

        foreach ( (array) $rows as $row ) {

            $metas = maybe_unserialize( $row->meta_value );

            if ( ! is_array( $metas ) || empty( $metas['booking_accomodation_id'] ) ) {
                continue;
            }

            $booking_id = (int) $row->ID;
            $start      = isset( $metas['booking_start_date'] ) ? (string) $metas['booking_start_date'] : '';
            $end        = isset( $metas['booking_end_date'] ) ? (string) $metas['booking_end_date'] : '';
            $dates      = self::get_stay_dates( $start, $end );

            if ( empty( $dates ) ) {
                continue;
            }

            self::$bookings[ $booking_id ] = [
                'id'               => $booking_id,
                'title'            => $row->post_title,
                'accommodation_id' => (int) $metas['booking_accomodation_id'],
                'start_date'       => $start,
                'end_date'         => $end,
                'dates'            => $dates,
                'quantity'         => max( 1, isset( $metas['room_quantity'] ) ? (int) $metas['room_quantity'] : 1 ),
                'status'           => isset( $metas['booking_status'] ) ? (string) $metas['booking_status'] : '',
                'guest'            => self::build_guest_name( $metas ),
                'adults'           => isset( $metas['adult_quantity'] ) ? (int) $metas['adult_quantity'] : 0,
                'children'         => isset( $metas['children_quantity'] ) ? (int) $metas['children_quantity'] : 0,
            ];
        }

        return self::$bookings;
    }

    /**
     * Clears the per request booking cache. Call after mutating a booking.
     */
    public static function flush_cache() {
        self::$bookings = null;
    }

    /**
     * Single normalised booking record.
     *
     * @param int $booking_id Booking post ID.
     *
     * @return array|null
     */
    public static function get_booking( $booking_id ) {

        $bookings = self::get_all_bookings();

        return isset( $bookings[ (int) $booking_id ] ) ? $bookings[ (int) $booking_id ] : null;
    }

    /**
     * Bookings of one accommodation that overlap a date window.
     *
     * @param int    $accommodation_id Accommodation post ID.
     * @param string $from             `Y-m-d` window start, inclusive. Empty for no bound.
     * @param string $to               `Y-m-d` window end, inclusive. Empty for no bound.
     * @param bool   $occupying_only   Whether to drop cancelled/refunded/failed bookings.
     *
     * @return array<int, array>
     */
    public static function get_bookings_for( $accommodation_id, $from = '', $to = '', $occupying_only = true ) {

        $accommodation_id = (int) $accommodation_id;
        $statuses         = self::occupying_statuses();
        $matches          = [];

        foreach ( self::get_all_bookings() as $booking_id => $booking ) {

            if ( $booking['accommodation_id'] !== $accommodation_id ) {
                continue;
            }

            if ( $occupying_only && ! in_array( $booking['status'], $statuses, true ) ) {
                continue;
            }

            if ( '' !== $from && end( $booking['dates'] ) < $from ) {
                continue;
            }

            if ( '' !== $to && reset( $booking['dates'] ) > $to ) {
                continue;
            }

            $matches[ $booking_id ] = $booking;
        }

        return $matches;
    }

    /**
     * Unit indexes assigned to a booking.
     *
     * The returned array always has exactly one slot per booked room, padded with
     * `self::UNASSIGNED` - that zero is the nullable state: the booking exists and is
     * counted by the engine, it simply has no physical room attached yet.
     *
     * @param int $booking_id Booking post ID.
     *
     * @return int[] Slot index (0 based) => unit index (1 based) or 0.
     */
    public static function get_assignment( $booking_id ) {

        $booking = self::get_booking( $booking_id );

        if ( ! $booking ) {
            return [];
        }

        $meta   = get_post_meta( (int) $booking_id, ESHB_PMS_ASSIGN_META, true );
        $stored = ( is_array( $meta ) && isset( $meta[ ESHB_PMS_UNITS_FIELD ] ) ) ? $meta[ ESHB_PMS_UNITS_FIELD ] : [];
        $stored = is_array( $stored ) ? array_values( array_map( 'intval', $stored ) ) : [];

        return self::normalise_slots( $booking, $stored );
    }

    /**
     * Clamps a raw slot list to the booking: right length, existing units only, no unit used
     * twice. Pure — nothing is written, so the settings framework can call it during its own
     * save pass.
     *
     * @param array $booking   Normalised booking record.
     * @param array $requested Raw unit indexes.
     *
     * @return int[] One entry per booked room.
     */
    public static function normalise_slots( array $booking, array $requested ) {

        $requested = array_values( $requested );
        $total     = ESHB_PMS_Units::get_total_units( $booking['accommodation_id'] );
        $slots     = [];
        $used      = [];

        for ( $slot = 0; $slot < $booking['quantity']; $slot++ ) {

            $unit = isset( $requested[ $slot ] ) ? (int) $requested[ $slot ] : self::UNASSIGNED;

            // Drop indexes that no longer exist, e.g. after the capacity was lowered.
            if ( $unit < 1 || $unit > $total || isset( $used[ $unit ] ) ) {
                $slots[ $slot ] = self::UNASSIGNED;
                continue;
            }

            $used[ $unit ]  = true;
            $slots[ $slot ] = $unit;
        }

        return $slots;
    }

    /**
     * Releases any slot whose unit clashes with another booking on the same nights.
     *
     * @param array $booking Normalised booking record.
     * @param int[] $slots   Normalised slot list.
     *
     * @return int[] Slot list with the conflicting entries reset to unassigned.
     */
    public static function drop_conflicting_slots( array $booking, array $slots ) {

        $taken = self::get_taken_units( $booking['accommodation_id'], $booking['dates'], $booking['id'] );

        foreach ( $slots as $slot => $unit ) {
            if ( self::UNASSIGNED !== $unit && isset( $taken[ $unit ] ) ) {
                $slots[ $slot ] = self::UNASSIGNED;
            }
        }

        return $slots;
    }

    /**
     * Fills the empty slots of a list with the first free units available.
     *
     * Pure: slots that already hold a unit are returned untouched, so a manual choice always
     * wins over the automatic one.
     *
     * @param array $booking Normalised booking record.
     * @param int[] $slots   Normalised slot list.
     *
     * @return int[] Slot list with the gaps filled where possible.
     */
    public static function fill_empty_slots( array $booking, array $slots ) {

        if ( ! in_array( $booking['status'], self::occupying_statuses(), true ) ) {
            return $slots;
        }

        if ( ! in_array( self::UNASSIGNED, $slots, true ) ) {
            return $slots;
        }

        $free = self::get_free_units(
            $booking['accommodation_id'],
            $booking['start_date'],
            $booking['end_date'],
            $booking['id']
        );

        // Units already held by the other slots of this very booking are not available.
        $free = array_values( array_diff( $free, array_filter( $slots ) ) );

        foreach ( $slots as $slot => $unit ) {

            if ( self::UNASSIGNED !== $unit || empty( $free ) ) {
                continue;
            }

            $slots[ $slot ] = (int) array_shift( $free );
        }

        return $slots;
    }

    /**
     * Stores the unit indexes of a booking.
     *
     * Used by the rack and the ajax endpoints. The booking edit screen does not go through
     * here: there the settings framework owns the write, and the same normalisation is
     * applied through the `eshb_..._save` filter instead.
     *
     * @param int   $booking_id Booking post ID.
     * @param int[] $indexes    Unit index per slot. Use 0 to release a slot.
     *
     * @return bool
     */
    public static function set_assignment( $booking_id, array $indexes ) {

        $booking_id = (int) $booking_id;
        $booking    = self::get_booking( $booking_id );

        if ( ! $booking ) {
            return false;
        }

        $slots = self::normalise_slots( $booking, $indexes );
        $meta  = get_post_meta( $booking_id, ESHB_PMS_ASSIGN_META, true );
        $meta  = is_array( $meta ) ? $meta : [];

        // Merge, never replace: the metabox stores its own settings under the same key.
        $meta[ ESHB_PMS_UNITS_FIELD ] = $slots;

        update_post_meta( $booking_id, ESHB_PMS_ASSIGN_META, $meta );

        /**
         * Fires after a booking's unit assignment changed.
         *
         * @param int   $booking_id Booking post ID.
         * @param int[] $slots      Stored slots.
         */
        do_action( 'eshb_pms_assignment_updated', $booking_id, $slots );

        return true;
    }

    /**
     * Occupancy map of an accommodation over a date window.
     *
     * @param int    $accommodation_id   Accommodation post ID.
     * @param string $from               `Y-m-d` window start.
     * @param string $to                 `Y-m-d` window end.
     * @param int    $exclude_booking_id Booking to leave out, used when re-assigning it.
     *
     * @return array<string, array<int, int>> `Y-m-d` => unit index => booking ID.
     */
    public static function get_occupancy_map( $accommodation_id, $from, $to, $exclude_booking_id = 0 ) {

        $exclude_booking_id = (int) $exclude_booking_id;
        $map                = [];

        foreach ( self::get_bookings_for( $accommodation_id, $from, $to ) as $booking_id => $booking ) {

            if ( $booking_id === $exclude_booking_id ) {
                continue;
            }

            $units = array_filter( self::get_assignment( $booking_id ) );

            if ( empty( $units ) ) {
                continue;
            }

            foreach ( $booking['dates'] as $date ) {

                if ( '' !== $from && $date < $from ) {
                    continue;
                }

                if ( '' !== $to && $date > $to ) {
                    continue;
                }

                foreach ( $units as $unit ) {
                    $map[ $date ][ $unit ] = $booking_id;
                }
            }
        }

        return $map;
    }

    /**
     * Unit indexes that are free for the whole span of a stay.
     *
     * @param int    $accommodation_id   Accommodation post ID.
     * @param string $start_date         `Y-m-d` check-in.
     * @param string $end_date           `Y-m-d` check-out.
     * @param int    $exclude_booking_id Booking to leave out of the conflict check.
     *
     * @return int[] Sorted list of free unit indexes.
     */
    public static function get_free_units( $accommodation_id, $start_date, $end_date, $exclude_booking_id = 0 ) {

        $dates = self::get_stay_dates( $start_date, $end_date );

        if ( empty( $dates ) ) {
            return [];
        }

        $taken = self::get_taken_units( $accommodation_id, $dates, $exclude_booking_id );
        $total = ESHB_PMS_Units::get_total_units( $accommodation_id );
        $free  = [];

        for ( $unit = 1; $unit <= $total; $unit++ ) {
            if ( ! isset( $taken[ $unit ] ) ) {
                $free[] = $unit;
            }
        }

        return $free;
    }

    /**
     * Unit indexes busy on at least one of the given nights.
     *
     * @param int      $accommodation_id   Accommodation post ID.
     * @param string[] $dates              List of `Y-m-d` nights.
     * @param int      $exclude_booking_id Booking to leave out.
     *
     * @return array<int, int> Unit index => conflicting booking ID.
     */
    public static function get_taken_units( $accommodation_id, array $dates, $exclude_booking_id = 0 ) {

        if ( empty( $dates ) ) {
            return [];
        }

        $map   = self::get_occupancy_map( $accommodation_id, reset( $dates ), end( $dates ), $exclude_booking_id );
        $taken = [];

        foreach ( $dates as $date ) {

            if ( empty( $map[ $date ] ) ) {
                continue;
            }

            foreach ( $map[ $date ] as $unit => $booking_id ) {
                $taken[ $unit ] = $booking_id;
            }
        }

        return $taken;
    }

    /**
     * Whether a unit can host a booking without clashing with another one.
     *
     * @param int $booking_id Booking post ID.
     * @param int $unit       1 based unit index.
     *
     * @return true|WP_Error
     */
    public static function can_assign( $booking_id, $unit ) {

        $booking = self::get_booking( $booking_id );

        if ( ! $booking ) {
            return new WP_Error( 'eshb_pms_no_booking', esc_html__( 'Booking not found.', 'easy-hotel' ) );
        }

        $unit  = (int) $unit;
        $total = ESHB_PMS_Units::get_total_units( $booking['accommodation_id'] );

        if ( $unit < 1 || $unit > $total ) {
            return new WP_Error( 'eshb_pms_bad_unit', esc_html__( 'That unit does not exist for this accommodation.', 'easy-hotel' ) );
        }

        $taken = self::get_taken_units( $booking['accommodation_id'], $booking['dates'], $booking_id );

        if ( isset( $taken[ $unit ] ) ) {
            return new WP_Error(
                'eshb_pms_conflict',
                sprintf(
                    /* translators: 1: unit label, 2: booking ID. */
                    esc_html__( '%1$s is already taken by booking #%2$d on those dates.', 'easy-hotel' ),
                    ESHB_PMS_Units::get_label( $booking['accommodation_id'], $unit ),
                    (int) $taken[ $unit ]
                )
            );
        }

        return true;
    }

    /**
     * Assigns a unit to one slot of a booking.
     *
     * @param int $booking_id Booking post ID.
     * @param int $slot       0 based slot index.
     * @param int $unit       1 based unit index, or 0 to release the slot.
     *
     * @return true|WP_Error
     */
    public static function assign_slot( $booking_id, $slot, $unit ) {

        $booking = self::get_booking( $booking_id );

        if ( ! $booking ) {
            return new WP_Error( 'eshb_pms_no_booking', esc_html__( 'Booking not found.', 'easy-hotel' ) );
        }

        $slot = (int) $slot;
        $unit = (int) $unit;

        if ( $slot < 0 || $slot >= $booking['quantity'] ) {
            return new WP_Error( 'eshb_pms_bad_slot', esc_html__( 'That room slot does not exist on this booking.', 'easy-hotel' ) );
        }

        $slots = self::get_assignment( $booking_id );

        if ( self::UNASSIGNED !== $unit ) {

            $can = self::can_assign( $booking_id, $unit );

            if ( is_wp_error( $can ) ) {
                return $can;
            }

            // The same unit cannot serve two slots of the same booking.
            foreach ( $slots as $other_slot => $other_unit ) {
                if ( $other_slot !== $slot && $other_unit === $unit ) {
                    $slots[ $other_slot ] = self::UNASSIGNED;
                }
            }
        }

        $slots[ $slot ] = $unit;

        self::set_assignment( $booking_id, $slots );

        return true;
    }

    /**
     * Fills the empty slots of a booking with the first free units available.
     *
     * Slots already assigned are never touched, so a manual choice always wins over the
     * automatic one.
     *
     * @param int  $booking_id Booking post ID.
     * @param bool $force      Whether to also re-assign slots that already have a unit.
     *
     * @return int Number of slots that received a unit.
     */
    public static function auto_assign( $booking_id, $force = false ) {

        $booking = self::get_booking( $booking_id );

        if ( ! $booking ) {
            return 0;
        }

        $before = $force
            ? array_fill( 0, $booking['quantity'], self::UNASSIGNED )
            : self::get_assignment( $booking_id );

        $after = self::fill_empty_slots( $booking, $before );

        $assigned = 0;

        foreach ( $after as $slot => $unit ) {
            if ( $unit !== $before[ $slot ] ) {
                $assigned++;
            }
        }

        if ( $assigned ) {
            self::set_assignment( $booking_id, $after );
        }

        return $assigned;
    }

    /**
     * Bookings of an accommodation that still miss at least one unit, within a window.
     *
     * @param int    $accommodation_id Accommodation post ID.
     * @param string $from             `Y-m-d` window start.
     * @param string $to               `Y-m-d` window end.
     *
     * @return array<int, array>
     */
    public static function get_unassigned_bookings( $accommodation_id, $from = '', $to = '' ) {

        $unassigned = [];

        foreach ( self::get_bookings_for( $accommodation_id, $from, $to ) as $booking_id => $booking ) {

            $slots = self::get_assignment( $booking_id );

            if ( count( array_filter( $slots ) ) < count( $slots ) ) {
                $booking['slots']      = $slots;
                $unassigned[ $booking_id ] = $booking;
            }
        }

        return $unassigned;
    }

    /**
     * Builds a printable guest name out of the booking meta.
     *
     * @param array $metas Booking engine meta array.
     *
     * @return string
     */
    protected static function build_guest_name( array $metas ) {

        $first = isset( $metas['first_name'] ) ? trim( (string) $metas['first_name'] ) : '';
        $last  = isset( $metas['last_name'] ) ? trim( (string) $metas['last_name'] ) : '';
        $name  = trim( $first . ' ' . $last );

        if ( '' !== $name ) {
            return $name;
        }

        return isset( $metas['email'] ) ? (string) $metas['email'] : '';
    }
}
