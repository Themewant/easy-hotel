<?php
/**
 * Easy Hotel – Dashboard data layer.
 *
 * Reads bookings / accommodations straight from the DB and computes every
 * stat the dashboard UI needs. No presentation here, pure data.
 *
 * @package Easy_Hotel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ESHB_Dashboard_Data {

	/**
	 * Booking statuses we treat as an active / occupying reservation.
	 *
	 * @var string[]
	 */
	protected $active_statuses = array( 'processing', 'completed', 'on-hold', 'deposit-payment' );

	/**
	 * Cached bookings list so multiple stats don't re-query.
	 *
	 * @var array|null
	 */
	protected $bookings = null;

	/**
	 * Today's date in the site timezone (Y-m-d).
	 *
	 * @var string
	 */
	protected $today;

	public function __construct() {
		$this->today = current_time( 'Y-m-d' );
	}

	/**
	 * Build the full payload returned by the REST endpoint.
	 *
	 * @return array
	 */
	public function get_dashboard_data() {
		$bookings = $this->get_bookings();

		return array(
			'today'            => $this->today,
			'currency'         => $this->get_currency(),
			'stats'            => $this->get_stats( $bookings ),
			'recentBookings'   => $this->get_recent_bookings(),
			'roomAvailability' => $this->get_room_availability( $bookings ),
			'bookingTrend'     => $this->get_booking_trend(),
			'calendar'         => $this->get_calendar( $bookings ),
		);
	}

	/**
	 * Currency symbol + position from plugin settings.
	 *
	 * @return array
	 */
	protected function get_currency() {
		$symbol   = '$';
		$position = 'left';

		if ( class_exists( 'ESHB_Core' ) ) {
			$core     = ESHB_Core::instance();
			$symbol   = $core->get_eshb_currency_symbol();
			$position = $core->get_eshb_currency_position();
		}

		return array(
			'symbol'   => $symbol,
			'position' => $position,
		);
	}

	/**
	 * Load every booking once and normalise the meta we care about.
	 *
	 * @return array
	 */
	protected function get_bookings() {
		if ( null !== $this->bookings ) {
			return $this->bookings;
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'eshb_booking',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$bookings = array();

		foreach ( $query->posts as $post ) {
			$meta = get_post_meta( $post->ID, 'eshb_booking_metaboxes', true );
			$meta = is_array( $meta ) ? $meta : array();

			$bookings[] = array(
				'id'              => $post->ID,
				'post_date'       => get_post_time( 'Y-m-d', false, $post ),
				'status'          => ! empty( $meta['booking_status'] ) ? $meta['booking_status'] : $post->post_status,
				'accomodation_id' => ! empty( $meta['booking_accomodation_id'] ) ? (int) $meta['booking_accomodation_id'] : 0,
				'start_date'      => ! empty( $meta['booking_start_date'] ) ? $meta['booking_start_date'] : '',
				'end_date'        => ! empty( $meta['booking_end_date'] ) ? $meta['booking_end_date'] : '',
				'total_price'     => isset( $meta['total_price'] ) ? (float) $meta['total_price'] : 0,
				'room_quantity'   => ! empty( $meta['room_quantity'] ) ? (int) $meta['room_quantity'] : 1,
				'adults'          => isset( $meta['adult_quantity'] ) ? (int) $meta['adult_quantity'] : 0,
				'children'        => isset( $meta['children_quantity'] ) ? (int) $meta['children_quantity'] : 0,
				'first_name'      => ! empty( $meta['first_name'] ) ? $meta['first_name'] : '',
				'last_name'       => ! empty( $meta['last_name'] ) ? $meta['last_name'] : '',
				'title'           => $post->post_title,
			);
		}

		wp_reset_postdata();

		$this->bookings = $bookings;

		return $this->bookings;
	}

	/**
	 * Whether a booking is "active" (occupies a room) on a given date range.
	 */
	protected function is_active( $status ) {
		return in_array( $status, $this->active_statuses, true );
	}

	/**
	 * Does a booking cover the given date (start <= date < end, or single day)?
	 */
	protected function covers_date( $booking, $date ) {
		if ( empty( $booking['start_date'] ) ) {
			return false;
		}
		$start = $booking['start_date'];
		$end   = ! empty( $booking['end_date'] ) ? $booking['end_date'] : $start;

		if ( $start === $end ) {
			return $date === $start;
		}

		return ( $date >= $start && $date < $end );
	}

	/**
	 * Top stat cards + month-over-month deltas.
	 *
	 * @param array $bookings Normalised bookings.
	 * @return array
	 */
	protected function get_stats( $bookings ) {
		$total       = count( $bookings );
		$pending     = 0;
		$checkins    = 0;
		$checkouts   = 0;

		$yesterday      = gmdate( 'Y-m-d', strtotime( $this->today . ' -1 day' ) );
		$checkins_yday  = 0;
		$checkouts_yday = 0;

		$this_month  = gmdate( 'Y-m', strtotime( $this->today ) );
		$last_month  = gmdate( 'Y-m', strtotime( $this->today . ' first day of last month' ) );

		$total_this  = 0;
		$total_last  = 0;
		$pend_this   = 0;
		$pend_last   = 0;

		foreach ( $bookings as $b ) {
			if ( 'pending' === $b['status'] || 'deposit-payment' === $b['status'] ) {
				$pending++;
			}
			if ( $b['start_date'] === $this->today ) {
				$checkins++;
			} elseif ( $b['start_date'] === $yesterday ) {
				$checkins_yday++;
			}
			if ( $b['end_date'] === $this->today ) {
				$checkouts++;
			} elseif ( $b['end_date'] === $yesterday ) {
				$checkouts_yday++;
			}

			$b_month = substr( $b['post_date'], 0, 7 );
			if ( $b_month === $this_month ) {
				$total_this++;
				if ( 'pending' === $b['status'] ) {
					$pend_this++;
				}
			} elseif ( $b_month === $last_month ) {
				$total_last++;
				if ( 'pending' === $b['status'] ) {
					$pend_last++;
				}
			}
		}

		$available = $this->get_available_rooms_today( $bookings );

		return array(
			'totalBookings'   => array(
				'value' => $total,
				'delta' => $this->delta( $total_this, $total_last, 'fromLast' ),
			),
			'pendingBookings' => array(
				'value' => $pending,
				'delta' => $this->delta( $pend_this, $pend_last, 'fromLast' ),
			),
			'checkinsToday'   => array(
				'value' => $checkins,
				'delta' => $this->delta( $checkins, $checkins_yday, 'fromYday' ),
			),
			'checkoutsToday'  => array(
				'value' => $checkouts,
				'delta' => $this->delta( $checkouts, $checkouts_yday, 'fromYday' ),
			),
			'availableRooms'  => array(
				'value' => $available['available'],
				'total' => $available['total'],
				'delta' => null,
			),
		);
	}

	/**
	 * Percentage change helper. Returns null when there's no baseline.
	 *
	 * @param int    $current  Current period count.
	 * @param int    $previous Previous period count (baseline).
	 * @param string $suffix   i18n key for the trailing label (fromLast|fromYday).
	 * @return array|null
	 */
	protected function delta( $current, $previous, $suffix = 'fromLast' ) {
		$current  = (int) $current;
		$previous = (int) $previous;

		// No baseline last period. If there's activity now, it's all-new growth
		// (↑ 100%); if both are zero there's genuinely nothing to show.
		if ( $previous <= 0 ) {
			if ( $current <= 0 ) {
				return null;
			}

			return array(
				'percent'   => 100,
				'direction' => 'up',
				'suffix'    => $suffix,
			);
		}

		$change = ( ( $current - $previous ) / $previous ) * 100;

		return array(
			'percent'   => round( abs( $change ) ),
			'direction' => ( $change >= 0 ) ? 'up' : 'down',
			'suffix'    => $suffix,
		);
	}

	/**
	 * Total room units across all accommodations minus those occupied today.
	 */
	protected function get_available_rooms_today( $bookings ) {
		$total = 0;

		$rooms = get_posts(
			array(
				'post_type'      => 'eshb_accomodation',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		foreach ( $rooms as $room_id ) {
			$total += $this->get_room_total_units( $room_id );
		}

		$occupied = 0;
		foreach ( $bookings as $b ) {
			if ( $this->is_active( $b['status'] ) && $this->covers_date( $b, $this->today ) ) {
				$occupied += max( 1, $b['room_quantity'] );
			}
		}

		return array(
			'total'     => $total,
			'available' => max( 0, $total - $occupied ),
		);
	}

	/**
	 * Total room units configured for an accommodation.
	 */
	protected function get_room_total_units( $room_id ) {
		$meta  = get_post_meta( $room_id, 'eshb_accomodation_metaboxes', true );
		$total = is_array( $meta ) && ! empty( $meta['total_rooms'] ) ? (int) $meta['total_rooms'] : 1;

		return max( 1, $total );
	}

	/**
	 * Latest 5 bookings for the table.
	 */
	protected function get_recent_bookings() {
		$bookings = array_slice( $this->get_bookings(), 0, 5 );
		$rows     = array();

		foreach ( $bookings as $b ) {
			$name = trim( $b['first_name'] . ' ' . $b['last_name'] );
			if ( '' === $name ) {
				$name = __( 'Guest', 'easy-hotel' );
			}

			$guests = array();
			if ( $b['adults'] > 0 ) {
				/* translators: %d: number of adults. */
				$guests[] = sprintf( _n( '%d Adult', '%d Adults', $b['adults'], 'easy-hotel' ), $b['adults'] );
			}
			if ( $b['children'] > 0 ) {
				/* translators: %d: number of children. */
				$guests[] = sprintf( _n( '%d Child', '%d Children', $b['children'], 'easy-hotel' ), $b['children'] );
			}

			$rows[] = array(
				'id'       => $b['id'],
				'label'    => '#' . ( '' !== $b['title'] ? $b['title'] : 'EHB' . $b['id'] ),
				'editLink' => get_edit_post_link( $b['id'], 'raw' ),
				'guest'    => $name,
				'room'     => $b['accomodation_id'] ? get_the_title( $b['accomodation_id'] ) : '—',
				'checkIn'  => $this->format_date( $b['start_date'] ),
				'checkOut' => $this->format_date( $b['end_date'] ),
				'guests'   => implode( ', ', $guests ),
				'total'    => (float) $b['total_price'],
				'status'   => $b['status'],
				'statusLabel' => $this->status_label( $b['status'] ),
			);
		}

		return $rows;
	}

	/**
	 * Per-room availability summary (available / total units).
	 */
	protected function get_room_availability( $bookings ) {
		$rooms = get_posts(
			array(
				'post_type'      => 'eshb_accomodation',
				'post_status'    => 'publish',
				'posts_per_page' => 6,
				'no_found_rows'  => true,
			)
		);

		$summary = array();

		foreach ( $rooms as $room ) {
			$total    = $this->get_room_total_units( $room->ID );
			$occupied = 0;

			foreach ( $bookings as $b ) {
				if ( $b['accomodation_id'] === $room->ID && $this->is_active( $b['status'] ) && $this->covers_date( $b, $this->today ) ) {
					$occupied += max( 1, $b['room_quantity'] );
				}
			}

			$available = max( 0, $total - $occupied );

			$summary[] = array(
				'name'      => get_the_title( $room->ID ),
				'available' => $available,
				'total'     => $total,
				'percent'   => $total > 0 ? round( ( $available / $total ) * 100 ) : 0,
			);
		}

		return $summary;
	}

	/**
	 * Booking trend series for each selectable range. The dashboard switches
	 * between these client-side, so no extra request is needed per range.
	 *
	 * @return array{ '7d': array, '30d': array, '1y': array }
	 */
	protected function get_booking_trend() {
		return array(
			'7d'  => $this->trend_daily( 7 ),
			'30d' => $this->trend_daily( 30 ),
			'1y'  => $this->trend_monthly( 12 ),
		);
	}

	/**
	 * Bookings created per day for the last N days.
	 */
	protected function trend_daily( $days ) {
		$counts = array();

		for ( $i = $days - 1; $i >= 0; $i-- ) {
			$date            = gmdate( 'Y-m-d', strtotime( $this->today . " -{$i} day" ) );
			$counts[ $date ] = 0;
		}

		foreach ( $this->get_bookings() as $b ) {
			if ( isset( $counts[ $b['post_date'] ] ) ) {
				$counts[ $b['post_date'] ]++;
			}
		}

		$series = array();
		foreach ( $counts as $date => $count ) {
			$series[] = array(
				'date'  => $date,
				'label' => gmdate( 'M j', strtotime( $date ) ),
				'count' => $count,
			);
		}

		return $series;
	}

	/**
	 * Bookings created per month for the last N months.
	 */
	protected function trend_monthly( $months ) {
		$counts = array();
		$base   = strtotime( gmdate( 'Y-m-01', strtotime( $this->today ) ) );

		for ( $i = $months - 1; $i >= 0; $i-- ) {
			$key            = gmdate( 'Y-m', strtotime( "-{$i} month", $base ) );
			$counts[ $key ] = 0;
		}

		foreach ( $this->get_bookings() as $b ) {
			$key = substr( $b['post_date'], 0, 7 );
			if ( isset( $counts[ $key ] ) ) {
				$counts[ $key ]++;
			}
		}

		$series = array();
		foreach ( $counts as $key => $count ) {
			$series[] = array(
				'date'  => $key,
				'label' => date_i18n( 'M', strtotime( $key . '-01' ) ),
				'count' => $count,
			);
		}

		return $series;
	}

	/**
	 * Per-day status map for the current month's calendar.
	 *
	 * Each day is one of: booked, blocked, available, past.
	 */
	protected function get_calendar( $bookings ) {
		$year  = (int) gmdate( 'Y', strtotime( $this->today ) );
		$month = (int) gmdate( 'n', strtotime( $this->today ) );
		$days  = (int) gmdate( 't', strtotime( $this->today ) );

		$blocked = $this->get_blocked_dates();

		$cells = array();

		for ( $d = 1; $d <= $days; $d++ ) {
			$date   = sprintf( '%04d-%02d-%02d', $year, $month, $d );
			$status = 'available';

			if ( in_array( $date, $blocked, true ) ) {
				$status = 'blocked';
			} else {
				foreach ( $bookings as $b ) {
					if ( $this->is_active( $b['status'] ) && $this->covers_date( $b, $date ) ) {
						$status = 'booked';
						break;
					}
				}
			}

			$cells[] = array(
				'date'   => $date,
				'day'    => $d,
				'status' => $status,
			);
		}

		return array(
			'year'      => $year,
			'month'     => $month,
			'monthName' => date_i18n( 'F Y', strtotime( $this->today ) ),
			'firstDow'  => (int) gmdate( 'w', strtotime( sprintf( '%04d-%02d-01', $year, $month ) ) ),
			'days'      => $cells,
		);
	}

	/**
	 * Blocked dates, if the calendar add-on exposes them. Filterable.
	 *
	 * @return string[]
	 */
	protected function get_blocked_dates() {
		return apply_filters( 'eshb_dashboard_blocked_dates', array() );
	}

	/**
	 * Human readable booking status label.
	 */
	protected function status_label( $status ) {
		if ( class_exists( 'ESHB_Helper' ) ) {
			$statuses = ESHB_Helper::eshb_get_booking_statuses();
			if ( isset( $statuses[ $status ] ) ) {
				return $statuses[ $status ];
			}
		}

		return ucfirst( str_replace( '-', ' ', $status ) );
	}

	/**
	 * Format a Y-m-d date for display, e.g. "Jun 18, 2025".
	 */
	protected function format_date( $date ) {
		if ( empty( $date ) ) {
			return '—';
		}
		$ts = strtotime( $date );

		return $ts ? date_i18n( 'M j, Y', $ts ) : $date;
	}
}
