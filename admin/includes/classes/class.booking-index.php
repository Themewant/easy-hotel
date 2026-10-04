<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Flat, queryable check-out date for bookings.
 *
 * Booking dates live inside the serialized `eshb_booking_metaboxes` meta, so
 * availability checks used to load every booking ever made and unserialize
 * each one. This class mirrors the check-out date into its own meta key so
 * those queries can skip bookings that ended before the dates being checked.
 *
 * The copy is written whenever `eshb_booking_metaboxes` is written, which
 * covers every booking path (WooCommerce, native checkout, admin, add-ons).
 * Older bookings are back-filled in small batches; until a booking has the
 * key, the query helper still includes it, so results never miss a booking.
 */
class ESHB_Booking_Index {

    /** Check-out date (Y-m-d) of the booking. */
    const META = '_eshb_booking_end_date';

    /** Stored when the end date can't be read, so the booking is always included. */
    const ALWAYS = '9999-12-31';

    /** Option set once every existing booking has the key. */
    const BACKFILL_DONE_OPTION = 'eshb_booking_index_backfilled';

    const BACKFILL_BATCH = 200;

    public static function init() {
        add_action( 'added_post_meta', [ __CLASS__, 'sync_from_meta' ], 10, 4 );
        add_action( 'updated_post_meta', [ __CLASS__, 'sync_from_meta' ], 10, 4 );
        add_action( 'deleted_post_meta', [ __CLASS__, 'delete_from_meta' ], 10, 3 );
        add_action( 'admin_init', [ __CLASS__, 'maybe_backfill' ] );
    }

    /**
     * Keep the flat date in step with the serialized booking meta.
     */
    public static function sync_from_meta( $meta_id, $post_id, $meta_key, $meta_value ) {
        if ( 'eshb_booking_metaboxes' !== $meta_key || 'eshb_booking' !== get_post_type( $post_id ) ) {
            return;
        }
        update_post_meta( $post_id, self::META, self::end_date_from_meta( $meta_value ) );
    }

    public static function delete_from_meta( $meta_ids, $post_id, $meta_key ) {
        if ( 'eshb_booking_metaboxes' === $meta_key && 'eshb_booking' === get_post_type( $post_id ) ) {
            delete_post_meta( $post_id, self::META );
        }
    }

    /**
     * Check-out date as Y-m-d, or ALWAYS when it can't be read.
     *
     * @param mixed $meta Booking meta array.
     * @return string
     */
    public static function end_date_from_meta( $meta ) {
        $meta = maybe_unserialize( $meta );
        $date = is_array( $meta ) ? self::normalize_date( $meta['booking_end_date'] ?? '' ) : '';
        return '' !== $date ? $date : self::ALWAYS;
    }

    /**
     * Normalize a date the booking code can parse into Y-m-d ('' if invalid).
     *
     * @param mixed $date
     * @return string
     */
    public static function normalize_date( $date ) {
        if ( ! is_string( $date ) || '' === trim( $date ) ) {
            return '';
        }
        try {
            return ( new DateTime( $date ) )->format( 'Y-m-d' );
        } catch ( Exception $e ) {
            return '';
        }
    }

    /**
     * meta_query limiting bookings to those that end on/after $from_date.
     * Bookings not indexed yet are kept, so the result is never incomplete.
     * Returns [] (no limit) when $from_date can't be read.
     *
     * @param string $from_date Any date DateTime understands.
     * @return array
     */
    public static function ending_on_or_after_query( $from_date ) {
        $from = self::normalize_date( $from_date );
        if ( '' === $from ) {
            return [];
        }
        return [
            'relation' => 'OR',
            [
                'key'     => self::META,
                'value'   => $from,
                'compare' => '>=',
                'type'    => 'DATE',
            ],
            [
                'key'     => self::META,
                'compare' => 'NOT EXISTS',
            ],
        ];
    }

    /**
     * Index existing bookings a batch at a time on admin page loads.
     */
    public static function maybe_backfill() {
        if ( get_option( self::BACKFILL_DONE_OPTION ) ) {
            return;
        }

        $ids = get_posts( [
            'post_type'      => 'eshb_booking',
            'post_status'    => 'any',
            'posts_per_page' => self::BACKFILL_BATCH,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- One-off back-fill, batched.
            'meta_query'     => [
                [
                    'key'     => self::META,
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ] );

        if ( ! empty( $ids ) ) {
            update_meta_cache( 'post', $ids );
            foreach ( $ids as $id ) {
                update_post_meta( $id, self::META, self::end_date_from_meta( get_post_meta( $id, 'eshb_booking_metaboxes', true ) ) );
            }
        }

        if ( count( $ids ) < self::BACKFILL_BATCH ) {
            update_option( self::BACKFILL_DONE_OPTION, 1, false );
        }
    }
}

ESHB_Booking_Index::init();
