/**
 * Easy Hotel – Dashboard front desk panels.
 *
 * Arriving / Departing lists (day switch + search, both client side, since all
 * three days ship with the page) and the availability calculator, which is the
 * only part that talks to the REST endpoint.
 *
 * Pure vanilla JS, same conventions as dashboard.js. No jQuery, no admin-ajax.
 */
( function () {
	'use strict';

	if ( typeof window.eshbFrontdesk === 'undefined' ) {
		return;
	}

	var cfg = window.eshbFrontdesk;
	var i18n = cfg.i18n || {};
	var movements = cfg.movements || {};

	/* ---------------------------------------------------------------- utils */

	function el( tag, className, text ) {
		var node = document.createElement( tag );
		if ( className ) {
			node.className = className;
		}
		if ( text !== undefined && text !== null ) {
			node.textContent = text;
		}
		return node;
	}

	// Minimal %1$s style formatter, so the PHP side keeps translatable
	// sentences instead of shipping glued-together fragments.
	function format( template, args ) {
		return String( template || '' ).replace( /%(\d+)\$s/g, function ( match, index ) {
			var value = args[ Number( index ) - 1 ];
			return value === undefined ? match : String( value );
		} );
	}

	function statusSlug( status ) {
		return String( status || '' ).toLowerCase().replace( /[^a-z0-9]+/g, '-' );
	}

	function emptyRow( tbody, message, columns ) {
		var tr = el( 'tr' );
		var td = el( 'td', 'eshb-dash-empty', message );
		td.setAttribute( 'colspan', String( columns ) );
		tr.appendChild( td );
		tbody.appendChild( tr );
	}

	/* ------------------------------------------------------ movement panels */

	function initMovementPanel( card ) {
		var key = card.getAttribute( 'data-movement' );
		var rowsByDay = movements[ key ] || {};
		var tbody = card.querySelector( '[data-rows]' );
		var counter = card.querySelector( '[data-count]' );
		var search = card.querySelector( '[data-search]' );
		var clear = card.querySelector( '[data-clear]' );
		var dayButtons = card.querySelectorAll( '[data-day]' );
		// Arrivals care about when the guest leaves, departures about when they came.
		var dateKey = 'arrivals' === key ? 'checkOut' : 'checkIn';
		var emptyText = ( 'arrivals' === key ? i18n.noArrivals : i18n.noDepartures ) || {};

		var state = { day: 'today', query: '' };

		if ( ! tbody ) {
			return;
		}

		function render() {
			var all = rowsByDay[ state.day ] || [];
			var query = state.query.trim().toLowerCase();
			var rows = ! query
				? all
				: all.filter( function ( row ) {
					return String( row.search || '' ).indexOf( query ) !== -1;
				} );

			if ( counter ) {
				counter.textContent = String( rows.length );
			}
			if ( clear ) {
				clear.hidden = '' === state.query;
			}

			tbody.innerHTML = '';

			if ( ! rows.length ) {
				emptyRow( tbody, ( all.length ? i18n.noMatch : emptyText[ state.day ] ) || '', 6 );
				return;
			}

			rows.forEach( function ( row ) {
				var tr = el( 'tr' );

				var tdId = el( 'td' );
				if ( row.editLink ) {
					var link = el( 'a', 'eshb-dash-booking-id', row.label );
					link.href = row.editLink;
					tdId.appendChild( link );
				} else {
					tdId.textContent = row.label;
				}
				tr.appendChild( tdId );

				tr.appendChild( el( 'td', null, row.guest ) );
				// The room count is folded in rather than given a column of its
				// own — it is 1 on almost every booking, and "Room / Rooms" side
				// by side just reads as the same column twice.
				tr.appendChild(
					el( 'td', null, row.rooms > 1 ? row.room + ' × ' + row.rooms : row.room )
				);
				tr.appendChild( el( 'td', null, String( row.adults ) ) );
				tr.appendChild( el( 'td', null, row[ dateKey ] ) );

				var tdStatus = el( 'td' );
				tdStatus.appendChild(
					el( 'span', 'eshb-dash-badge status-' + statusSlug( row.status ), row.statusLabel )
				);
				tr.appendChild( tdStatus );

				tbody.appendChild( tr );
			} );
		}

		Array.prototype.forEach.call( dayButtons, function ( button ) {
			button.addEventListener( 'click', function () {
				state.day = button.getAttribute( 'data-day' );
				Array.prototype.forEach.call( dayButtons, function ( other ) {
					other.classList.toggle( 'is-active', other === button );
				} );
				render();
			} );
		} );

		if ( search ) {
			search.addEventListener( 'input', function () {
				state.query = search.value;
				render();
			} );
		}

		if ( clear ) {
			clear.addEventListener( 'click', function () {
				state.query = '';
				if ( search ) {
					search.value = '';
					search.focus();
				}
				render();
			} );
		}

		render();
	}

	/* ------------------------------------------------- availability calculator */

	function initAvailability() {
		var card = document.getElementById( 'eshb-availability' );
		if ( ! card ) {
			return;
		}

		var form = card.querySelector( '[data-availability-form]' );
		var result = card.querySelector( '[data-availability-result]' );
		var summary = card.querySelector( '[data-availability-summary]' );
		var button = form ? form.querySelector( 'button[type="submit"]' ) : null;
		var checkIn = form ? form.querySelector( '[data-field="check_in"]' ) : null;

		if ( ! form || ! result ) {
			return;
		}

		// Start on today, so "Calculate" is one click away.
		if ( checkIn && ! checkIn.value && cfg.today ) {
			checkIn.value = cfg.today;
		}

		function field( name, fallback ) {
			var input = form.querySelector( '[data-field="' + name + '"]' );
			var value = input ? parseInt( input.value, 10 ) : NaN;

			return isNaN( value ) ? fallback : value;
		}

		function setBusy( busy ) {
			if ( button ) {
				button.disabled = busy;
			}
			card.setAttribute( 'aria-busy', busy ? 'true' : 'false' );
		}

		function showMessage( message ) {
			result.innerHTML = '';
			result.appendChild( el( 'p', 'eshb-dash-empty', message ) );
		}

		function renderRooms( data ) {
			result.innerHTML = '';

			if ( summary ) {
				summary.hidden = false;
				summary.textContent = format( i18n.summary, [
					data.freeUnits,
					data.freeTypes,
					data.checkInLabel,
					data.checkOutLabel,
				] );
			}

			if ( ! data.rooms || ! data.rooms.length ) {
				showMessage( i18n.noRooms || '' );
				return;
			}

			data.rooms.forEach( function ( room ) {
				var row = el( 'div', 'eshb-dash-avail-row' + ( room.bookable ? '' : ' is-out' ) );

				var name = el( 'span', 'eshb-dash-avail-name' );
				if ( room.editLink ) {
					var link = el( 'a', null, room.name );
					link.href = room.editLink;
					name.appendChild( link );
				} else {
					name.textContent = room.name;
				}
				row.appendChild( name );

				row.appendChild(
					el(
						'span',
						'eshb-dash-avail-count',
						room.available + ' / ' + room.total + ' ' + ( i18n.free || '' )
					)
				);

				row.appendChild(
					el(
						'span',
						'eshb-dash-avail-sleeps',
						( i18n.sleeps || '' ) + ' ' + room.adults + ' + ' + room.children
					)
				);

				var badge;
				if ( ! room.fits ) {
					badge = el( 'span', 'eshb-dash-badge is-small', i18n.tooSmall || '' );
				} else if ( room.available > 0 ) {
					badge = el( 'span', 'eshb-dash-badge is-ok', room.available + ' ' + ( i18n.free || '' ) );
				} else {
					badge = el( 'span', 'eshb-dash-badge is-full', i18n.full || '' );
				}
				row.appendChild( badge );

				result.appendChild( row );
			} );
		}

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();

			var params =
				'?check_in=' + encodeURIComponent( checkIn && checkIn.value ? checkIn.value : cfg.today ) +
				'&nights=' + field( 'nights', 1 ) +
				'&adults=' + field( 'adults', 2 ) +
				'&children=' + field( 'children', 0 );

			setBusy( true );
			showMessage( i18n.calculating || '' );

			fetch( cfg.availabilityUrl + params, {
				method: 'GET',
				credentials: 'same-origin',
				headers: {
					'X-WP-Nonce': cfg.nonce,
					Accept: 'application/json',
				},
			} )
				.then( function ( response ) {
					if ( ! response.ok ) {
						throw new Error( 'HTTP ' + response.status );
					}
					return response.json();
				} )
				.then( function ( data ) {
					renderRooms( data );
				} )
				.catch( function () {
					if ( summary ) {
						summary.hidden = true;
					}
					showMessage( i18n.error || '' );
				} )
				.then( function () {
					setBusy( false );
				} );
		} );
	}

	/* ----------------------------------------------------------------- boot */

	function boot() {
		var panels = document.querySelectorAll( '[data-movement]' );
		Array.prototype.forEach.call( panels, initMovementPanel );
		initAvailability();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
