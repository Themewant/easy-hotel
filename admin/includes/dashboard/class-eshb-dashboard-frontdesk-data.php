<?php
/**
 * Easy Hotel – Front desk data layer.
 *
 * Feeds the three front desk panels on the dashboard: who is arriving, who is
 * departing, and what is still free for a given stay.
 *
 * Extends the dashboard data layer instead of re-querying: bookings are loaded
 * and normalised exactly once, and status labels, date formatting and the
 * translation-aware accommodation ids all come from the same place the rest of
 * the dashboard uses. Nothing here writes.
 *
 * @package Easy_Hotel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ESHB_Dashboard_Frontdesk_Data extends ESHB_Dashboard_Data {

	/**
	 * Meta key the guest's name actually lives in.
	 *
	 * NOT inside `eshb_booking_metaboxes` — the customer details are their own
	 * metabox, which is why reading `first_name` off the booking meta yields
	 * nothing. The bookings list table resolves the name the same way.
	 */
	const CUSTOMER_META = 'eshb_booking_customer_details_metaboxes';

	/**
	 * Resolved guest names, keyed by booking id.
	 *
	 * A booking can appear on both an arrivals and a departures list, and the
	 * WooCommerce leg of the lookup loads an order, so the answer is kept.
	 *
	 * @var string[]
	 */
	protected $guest_names = array();

	/**
	 * Statuses that never appear on a movement list.
	 *
	 * Wider than the parent's $active_statuses on purpose: a pending booking
	 * still walks through the door today, so the front desk has to see it. Only
	 * bookings that will never show up are hidden.
	 *
	 * @var string[]
	 */
	protected $ignored_statuses = array( 'cancelled', 'refunded', 'failed', 'trash' );

	/**
	 * Statuses that hold a room, copied from the booking engine.
	 *
	 * Same list ESHB_Booking::get_available_room_count_by_date_range() uses, so
	 * the fallback count below can never contradict the engine's own answer.
	 * Wider than the parent's $active_statuses, which leaves out `pending`.
	 *
	 * @var string[]
	 */
	protected $occupying_statuses = array( 'pending', 'processing', 'on-hold', 'completed', 'deposit-payment' );

	/**
	 * Arrivals and departures for yesterday, today and tomorrow.
	 *
	 * All three days are computed in one pass so the UI can switch between them
	 * without another request — the same approach the booking trend uses for its
	 * ranges.
	 *
	 * @return array
	 */
	public function get_movements() {
		$dates      = $this->get_days();
		$arrivals   = array();
		$departures = array();

		foreach ( $dates as $key => $date ) {
			$arrivals[ $key ]   = array();
			$departures[ $key ] = array();
		}

		foreach ( $this->get_bookings() as $booking ) {
			if ( in_array( $booking['status'], $this->ignored_statuses, true ) ) {
				continue;
			}

			foreach ( $dates as $key => $date ) {
				if ( $booking['start_date'] === $date ) {
					$arrivals[ $key ][] = $this->movement_row( $booking );
				}
				if ( $booking['end_date'] === $date ) {
					$departures[ $key ][] = $this->movement_row( $booking );
				}
			}
		}

		foreach ( $dates as $key => $date ) {
			$arrivals[ $key ]   = $this->sort_rows( $arrivals[ $key ] );
			$departures[ $key ] = $this->sort_rows( $departures[ $key ] );
		}

		return array(
			'days'       => $dates,
			'dayLabels'  => array_map( array( $this, 'format_date' ), $dates ),
			'arrivals'   => $arrivals,
			'departures' => $departures,
		);
	}

	/**
	 * The three days a movement list can show, keyed by the UI's day buttons.
	 *
	 * @return array<string, string>
	 */
	public function get_days() {
		return array(
			'today'     => $this->today,
			'tomorrow'  => gmdate( 'Y-m-d', strtotime( $this->today . ' +1 day' ) ),
			'yesterday' => gmdate( 'Y-m-d', strtotime( $this->today . ' -1 day' ) ),
		);
	}

	/**
	 * One row of an arrivals / departures table.
	 *
	 * Carries both dates: the arrivals table shows the check-out and the
	 * departures table shows the check-in, and one shape serves both.
	 *
	 * @param array $booking Normalised booking.
	 * @return array
	 */
	protected function movement_row( $booking ) {
		$guest = $this->guest_name( $booking );
		$room  = $booking['accomodation_id'] ? get_the_title( $booking['accomodation_id'] ) : '—';

		return array(
			'id'          => $booking['id'],
			'label'       => '#' . $booking['id'],
			'editLink'    => get_edit_post_link( $booking['id'], 'raw' ),
			'guest'       => $guest,
			'room'        => $room,
			'adults'      => (int) $booking['adults'],
			'children'    => (int) $booking['children'],
			'rooms'       => max( 1, (int) $booking['room_quantity'] ),
			'checkIn'     => $this->format_date( $booking['start_date'] ),
			'checkOut'    => $this->format_date( $booking['end_date'] ),
			'status'      => $booking['status'],
			'statusLabel' => $this->status_label( $booking['status'] ),
			// Pre-built haystack so the search box never has to know the shape.
			'search'      => strtolower( $booking['id'] . ' ' . $guest . ' ' . $room ),
		);
	}

	/**
	 * Who the booking is for.
	 *
	 * Every source the engine's own screens read, in the order they read them —
	 * a booking can be created from the admin, the booking form, the native
	 * checkout or WooCommerce, and each of those leaves the name somewhere else:
	 *
	 *   1. the customer details metabox (admin, native checkout, payment flow);
	 *   2. `eshb_booking_metaboxes['customer']['name']` (booking form);
	 *   3. the WooCommerce order's billing name (WooCommerce checkout);
	 *
	 * and only then the generic "Guest". Resolved per displayed row rather than
	 * for every booking on the site, because step 3 loads an order.
	 *
	 * @param array $booking Normalised booking.
	 * @return string
	 */
	protected function guest_name( $booking ) {
		$booking_id = (int) $booking['id'];

		if ( isset( $this->guest_names[ $booking_id ] ) ) {
			return $this->guest_names[ $booking_id ];
		}

		$name    = '';
		$details = get_post_meta( $booking_id, self::CUSTOMER_META, true );

		if ( is_array( $details ) ) {
			$first = isset( $details['first_name'] ) ? $details['first_name'] : '';
			$last  = isset( $details['last_name'] ) ? $details['last_name'] : '';
			$name  = trim( $first . ' ' . $last );
		}

		$meta = get_post_meta( $booking_id, 'eshb_booking_metaboxes', true );
		$meta = is_array( $meta ) ? $meta : array();

		if ( '' === $name && ! empty( $meta['customer']['name'] ) ) {
			$name = trim( (string) $meta['customer']['name'] );
		}

		if ( '' === $name && ! empty( $meta['order_id'] ) && function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( $meta['order_id'] );

			if ( $order ) {
				$name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
			}
		}

		// Last resort: whatever the shared normalisation found, then a placeholder,
		// so a nameless booking still reads as a row rather than a blank cell.
		if ( '' === $name ) {
			$name = trim( $booking['first_name'] . ' ' . $booking['last_name'] );
		}

		$this->guest_names[ $booking_id ] = ( '' !== $name ) ? $name : __( 'Guest', 'easy-hotel' );

		return $this->guest_names[ $booking_id ];
	}

	/**
	 * Movement rows sorted by guest name, the order a printed arrivals list uses.
	 *
	 * @param array $rows Rows to sort.
	 * @return array
	 */
	protected function sort_rows( $rows ) {
		usort(
			$rows,
			function ( $a, $b ) {
				$name = strcasecmp( $a['guest'], $b['guest'] );

				return ( 0 !== $name ) ? $name : ( $a['id'] - $b['id'] );
			}
		);

		return $rows;
	}

	/**
	 * What is still bookable for a stay.
	 *
	 * The free unit count comes from the booking engine's own
	 * ESHB_Booking::get_available_room_count_by_date_range(), so this panel can
	 * never disagree with what the booking form or the availability calendar
	 * would allow. Capacity is filtered the same way the front-end search does
	 * (see ESHB_Search::eshb_get_available_accomodation_ids()).
	 *
	 * @param string $check_in Check-in date (Y-m-d).
	 * @param int    $nights   Number of nights.
	 * @param int    $adults   Adults travelling.
	 * @param int    $children Children travelling.
	 * @return array
	 */
	public function get_availability( $check_in, $nights, $adults, $children ) {
		$check_in  = $this->sanitize_date( $check_in );
		$nights    = max( 1, min( 365, (int) $nights ) );
		$adults    = max( 0, (int) $adults );
		$children  = max( 0, (int) $children );
		$check_out = gmdate( 'Y-m-d', strtotime( $check_in . ' +' . $nights . ' day' ) );

		$rooms      = array();
		$free_units = 0;
		$free_types = 0;

		foreach ( $this->get_accommodation_ids() as $room_id ) {
			$total     = $this->get_room_total_units( $room_id );
			$available = $this->count_free_units( $room_id, $check_in, $check_out, $total );
			$capacity  = $this->get_room_capacity( $room_id );
			$fits      = ( $adults <= $capacity['adults'] && $children <= $capacity['children'] );

			if ( $fits && $available > 0 ) {
				$free_units += $available;
				$free_types++;
			}

			$rooms[] = array(
				'id'        => $room_id,
				'name'      => get_the_title( $room_id ),
				'available' => $available,
				'total'     => $total,
				'fits'      => $fits,
				'bookable'  => ( $fits && $available > 0 ),
				'adults'    => $capacity['adults'],
				'children'  => $capacity['children'],
				'editLink'  => get_edit_post_link( $room_id, 'raw' ),
			);
		}

		// Bookable rooms first — the front desk is looking for somewhere to put a
		// guest, not for a list of everything that is full.
		usort(
			$rooms,
			function ( $a, $b ) {
				if ( $a['bookable'] !== $b['bookable'] ) {
					return $a['bookable'] ? -1 : 1;
				}
				if ( $a['available'] !== $b['available'] ) {
					return $b['available'] - $a['available'];
				}

				return strcasecmp( $a['name'], $b['name'] );
			}
		);

		return array(
			'checkIn'       => $check_in,
			'checkOut'      => $check_out,
			'checkInLabel'  => $this->format_date( $check_in ),
			'checkOutLabel' => $this->format_date( $check_out ),
			'nights'        => $nights,
			'adults'        => $adults,
			'children'      => $children,
			'freeUnits'     => $free_units,
			'freeTypes'     => $free_types,
			'totalTypes'    => count( $rooms ),
			'rooms'         => $rooms,
		);
	}

	/**
	 * Free units of one accommodation over a stay.
	 *
	 * @param int    $room_id   Accommodation post id.
	 * @param string $check_in  Check-in date (Y-m-d).
	 * @param string $check_out Check-out date (Y-m-d), not occupied.
	 * @param int    $total     Configured units.
	 * @return int
	 */
	protected function count_free_units( $room_id, $check_in, $check_out, $total ) {
		if ( class_exists( 'ESHB_Booking' ) ) {
			
			$count = ESHB_Booking::instance()->get_available_room_count_by_date_range( $room_id, $check_in, $check_out );

			if ( ! is_wp_error( $count ) ) {
				return max( 0, min( (int) $count, $total ) );
			}
		}

		return $this->count_free_units_locally( $room_id, $check_in, $check_out, $total );
	}

	/**
	 * Fallback free unit count, from the bookings already in memory.
	 *
	 * Only used when the engine's own calculation is unavailable. Uses the same
	 * night convention as the rest of the dashboard — the check-out night is not
	 * occupied — and the busiest night of the stay decides the answer.
	 *
	 * @param int    $room_id   Accommodation post id.
	 * @param string $check_in  Check-in date (Y-m-d).
	 * @param string $check_out Check-out date (Y-m-d), not occupied.
	 * @param int    $total     Configured units.
	 * @return int
	 */
	protected function count_free_units_locally( $room_id, $check_in, $check_out, $total ) {
		$busiest = 0;

		for ( $date = $check_in; $date < $check_out; $date = gmdate( 'Y-m-d', strtotime( $date . ' +1 day' ) ) ) {
			$occupied = 0;

			foreach ( $this->get_bookings() as $booking ) {
				if ( $this->canonical_accommodation_id( $booking['accomodation_id'] ) !== $room_id ) {
					continue;
				}
				if ( in_array( $booking['status'], $this->occupying_statuses, true )
					&& $this->covers_date( $booking, $date ) ) {
					$occupied += max( 1, $booking['room_quantity'] );
				}
			}

			$busiest = max( $busiest, $occupied );
		}

		return max( 0, $total - $busiest );
	}

	/**
	 * Per-room guest capacity, read the way the front-end search reads it.
	 *
	 * An empty adult / children capacity falls back to the total capacity, which
	 * is what ESHB_Search::eshb_get_available_accomodation_ids() does — so a room
	 * offered here is a room the search would also offer.
	 *
	 * @param int $room_id Accommodation post id.
	 * @return array{adults:int, children:int, total:int}
	 */
	protected function get_room_capacity( $room_id ) {
		$meta  = get_post_meta( $room_id, 'eshb_accomodation_metaboxes', true );
		$meta  = is_array( $meta ) ? $meta : array();
		$total = ! empty( $meta['total_capacity'] ) ? (int) $meta['total_capacity'] : 1;

		return array(
			'adults'   => ! empty( $meta['adult_capacity'] ) ? (int) $meta['adult_capacity'] : $total,
			'children' => ! empty( $meta['children_capacity'] ) ? (int) $meta['children_capacity'] : $total,
			'total'    => $total,
		);
	}

	/**
	 * A real Y-m-d date, or today.
	 *
	 * @param string $date Untrusted date string.
	 * @return string
	 */
	protected function sanitize_date( $date ) {
		$date = trim( (string) $date );

		if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date, $parts )
			&& checkdate( (int) $parts[2], (int) $parts[3], (int) $parts[1] ) ) {
			return $date;
		}

		return $this->today;
	}
}
