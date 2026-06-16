/**
 * Easy Hotel – Dashboard front-end.
 *
 * Pure vanilla JS. Pulls live data from the REST endpoint and renders the
 * stat cards, calendar, trend chart, recent bookings and room availability.
 * No jQuery, no admin-ajax.
 */
( function () {
	'use strict';

	if ( typeof window.eshbDashboard === 'undefined' ) {
		return;
	}

	var cfg = window.eshbDashboard;
	var i18n = cfg.i18n || {};
	var root = document.getElementById( 'eshb-dashboard' );
	if ( ! root ) {
		return;
	}

	// Bed icon for the room availability rows.
	var BED_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/></svg>';

	var state = {
		data: null,
		trendRange: '30d',
		bookedMap: {}, // date (Y-m-d) -> status, for the data month only.
		calYear: null,
		calMonth: null, // 1-12
	};

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

	function formatNumber( n ) {
		return Number( n || 0 ).toLocaleString();
	}

	function formatMoney( amount, currency ) {
		var value = Number( amount || 0 ).toLocaleString( undefined, {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2,
		} );
		var sym = currency && currency.symbol ? currency.symbol : '';
		if ( currency && currency.position === 'right' ) {
			return value + sym;
		}
		return sym + value;
	}

	/* -------------------------------------------------------------- fetching */

	function load() {
		fetch( cfg.restUrl, {
			method: 'GET',
			credentials: 'same-origin',
			headers: {
				'X-WP-Nonce': cfg.nonce,
				Accept: 'application/json',
			},
		} )
			.then( function ( res ) {
				if ( ! res.ok ) {
					throw new Error( 'HTTP ' + res.status );
				}
				return res.json();
			} )
			.then( function ( data ) {
				state.data = data;
				render( data );
				root.setAttribute( 'aria-busy', 'false' );
			} )
			.catch( function () {
				root.setAttribute( 'aria-busy', 'false' );
				showError();
			} );
	}

	function showError() {
		var tbody = document.getElementById( 'eshb-recent-bookings' );
		if ( tbody ) {
			tbody.innerHTML = '';
			var tr = el( 'tr' );
			var td = el( 'td', 'eshb-dash-empty', i18n.error || 'Error' );
			td.setAttribute( 'colspan', '7' );
			tr.appendChild( td );
			tbody.appendChild( tr );
		}
	}

	/* ------------------------------------------------------------- rendering */

	function render( data ) {
		renderStats( data.stats );
		renderRecent( data.recentBookings, data.currency );
		renderRooms( data.roomAvailability );
		renderTrend( getTrendSeries( data ) );
		initCalendar( data.calendar );
	}

	// Pick the series for the currently selected range. Supports both the
	// ranged object payload and a plain array (legacy / fallback).
	function getTrendSeries( data ) {
		var trend = data && data.bookingTrend;
		if ( ! trend ) {
			return [];
		}
		if ( Array.isArray( trend ) ) {
			return trend;
		}
		return trend[ state.trendRange ] || trend['30d'] || [];
	}

	function renderStats( stats ) {
		if ( ! stats ) {
			return;
		}
		Object.keys( stats ).forEach( function ( key ) {
			var item = stats[ key ];
			var valNode = root.querySelector( '[data-stat="' + key + '"]' );
			var deltaNode = root.querySelector( '[data-delta="' + key + '"]' );

			if ( valNode ) {
				valNode.textContent = formatNumber( item.value );
			}
			if ( ! deltaNode ) {
				return;
			}

			deltaNode.innerHTML = '';
			deltaNode.className = 'eshb-dash-stat-delta';

			if ( item.delta ) {
				var dir = item.delta.direction;
				var suffix = i18n[ item.delta.suffix ] || i18n.fromLast || '';
				deltaNode.classList.add( dir === 'down' ? 'is-down' : 'is-up' );
				var arrow = el( 'span', 'dashicons dashicons-arrow-' + ( dir === 'down' ? 'down' : 'up' ) + '-alt' );
				deltaNode.appendChild( arrow );
				deltaNode.appendChild( document.createTextNode( ' ' + item.delta.percent + '% ' + suffix ) );
			} else if ( key === 'availableRooms' ) {
				deltaNode.classList.add( 'is-muted' );
				deltaNode.textContent = i18n.updated || '';
			}
		} );
	}

	function statusSlug( status ) {
		return String( status || '' ).toLowerCase().replace( /[^a-z0-9]+/g, '-' );
	}

	function renderRecent( rows, currency ) {
		var tbody = document.getElementById( 'eshb-recent-bookings' );
		if ( ! tbody ) {
			return;
		}
		tbody.innerHTML = '';

		if ( ! rows || ! rows.length ) {
			var trEmpty = el( 'tr' );
			var tdEmpty = el( 'td', 'eshb-dash-empty', i18n.noData || '' );
			tdEmpty.setAttribute( 'colspan', '7' );
			trEmpty.appendChild( tdEmpty );
			tbody.appendChild( trEmpty );
			return;
		}

		rows.forEach( function ( r ) {
			var tr = el( 'tr' );

			var tdId = el( 'td' );
			if ( r.editLink ) {
				var a = el( 'a', 'eshb-dash-booking-id', r.label );
				a.href = r.editLink;
				tdId.appendChild( a );
			} else {
				tdId.textContent = r.label;
			}
			tr.appendChild( tdId );

			tr.appendChild( el( 'td', null, r.room ) );
			tr.appendChild( el( 'td', null, r.checkIn ) );
			tr.appendChild( el( 'td', null, r.checkOut ) );
			tr.appendChild( el( 'td', null, r.guests ) );
			tr.appendChild( el( 'td', null, formatMoney( r.total, currency ) ) );

			var tdStatus = el( 'td' );
			var badge = el( 'span', 'eshb-dash-badge status-' + statusSlug( r.status ), r.statusLabel );
			tdStatus.appendChild( badge );
			tr.appendChild( tdStatus );

			tbody.appendChild( tr );
		} );
	}

	function renderRooms( rooms ) {
		var wrap = document.getElementById( 'eshb-room-availability' );
		if ( ! wrap ) {
			return;
		}
		wrap.innerHTML = '';

		if ( ! rooms || ! rooms.length ) {
			wrap.appendChild( el( 'p', 'eshb-dash-empty', i18n.noData || '' ) );
			return;
		}

		rooms.forEach( function ( room ) {
			var row = el( 'div', 'eshb-dash-room' );

			// Name (bed icon + label).
			var name = el( 'span', 'eshb-dash-room-name' );
			var icon = el( 'span', 'eshb-dash-room-icon' );
			icon.innerHTML = BED_ICON;
			name.appendChild( icon );
			name.appendChild( el( 'span', 'eshb-dash-room-label', room.name ) );
			row.appendChild( name );

			// Progress bar.
			var bar = el( 'div', 'eshb-dash-room-bar' );
			var fill = el( 'span' );
			var pct = room.percent;
			fill.style.width = pct + '%';
			fill.className = pct <= 25 ? 'is-low' : pct <= 60 ? 'is-mid' : 'is-high';
			bar.appendChild( fill );
			row.appendChild( bar );

			// Count ("8 / 10 Available").
			var count = el( 'span', 'eshb-dash-room-count' );
			count.appendChild( el( 'span', 'eshb-dash-room-count-num', room.available + ' / ' + room.total ) );
			count.appendChild( document.createTextNode( ' ' + ( i18n.available || '' ) ) );
			row.appendChild( count );

			wrap.appendChild( row );
		} );
	}

	/* ---------------------------------------------------------- trend chart */

	function renderTrend( series ) {
		var wrap = document.getElementById( 'eshb-trend' );
		if ( ! wrap ) {
			return;
		}
		wrap.innerHTML = '';

		if ( ! series || ! series.length ) {
			wrap.appendChild( el( 'p', 'eshb-dash-empty', i18n.noData || '' ) );
			return;
		}

		var W = 640;
		var H = 230;
		var padL = 34;
		var padR = 12;
		var padT = 14;
		var padB = 26;
		var innerW = W - padL - padR;
		var innerH = H - padT - padB;

		var counts = series.map( function ( p ) {
			return p.count;
		} );
		var max = Math.max.apply( null, counts );
		max = max <= 0 ? 5 : Math.ceil( max / 5 ) * 5;

		var stepX = innerW / Math.max( 1, series.length - 1 );

		function x( i ) {
			return padL + i * stepX;
		}
		function y( v ) {
			return padT + innerH - ( v / max ) * innerH;
		}

		var svgNS = 'http://www.w3.org/2000/svg';
		var svg = document.createElementNS( svgNS, 'svg' );
		svg.setAttribute( 'viewBox', '0 0 ' + W + ' ' + H );
		svg.setAttribute( 'class', 'eshb-trend-svg' );
		svg.setAttribute( 'preserveAspectRatio', 'none' );

		// Gradient.
		var defs = document.createElementNS( svgNS, 'defs' );
		var grad = document.createElementNS( svgNS, 'linearGradient' );
		grad.setAttribute( 'id', 'eshbTrendGrad' );
		grad.setAttribute( 'x1', '0' );
		grad.setAttribute( 'y1', '0' );
		grad.setAttribute( 'x2', '0' );
		grad.setAttribute( 'y2', '1' );
		[ [ '0', '#6b8bff', '0.35' ], [ '1', '#6b8bff', '0' ] ].forEach( function ( s ) {
			var stop = document.createElementNS( svgNS, 'stop' );
			stop.setAttribute( 'offset', s[ 0 ] );
			stop.setAttribute( 'stop-color', s[ 1 ] );
			stop.setAttribute( 'stop-opacity', s[ 2 ] );
			grad.appendChild( stop );
		} );
		defs.appendChild( grad );
		svg.appendChild( defs );

		// Horizontal grid + y labels.
		var ticks = 5;
		for ( var t = 0; t <= ticks; t++ ) {
			var val = ( max / ticks ) * t;
			var gy = y( val );
			var line = document.createElementNS( svgNS, 'line' );
			line.setAttribute( 'x1', padL );
			line.setAttribute( 'x2', W - padR );
			line.setAttribute( 'y1', gy );
			line.setAttribute( 'y2', gy );
			line.setAttribute( 'class', 'eshb-trend-grid' );
			svg.appendChild( line );

			var label = document.createElementNS( svgNS, 'text' );
			label.setAttribute( 'x', padL - 8 );
			label.setAttribute( 'y', gy + 3 );
			label.setAttribute( 'class', 'eshb-trend-axis' );
			label.setAttribute( 'text-anchor', 'end' );
			label.textContent = Math.round( val );
			svg.appendChild( label );
		}

		// Build smooth path (Catmull-Rom -> cubic bezier).
		var pts = series.map( function ( p, i ) {
			return [ x( i ), y( p.count ) ];
		} );
		var d = smoothPath( pts );

		var area = document.createElementNS( svgNS, 'path' );
		area.setAttribute( 'd', d + ' L ' + x( series.length - 1 ) + ' ' + ( padT + innerH ) + ' L ' + x( 0 ) + ' ' + ( padT + innerH ) + ' Z' );
		area.setAttribute( 'fill', 'url(#eshbTrendGrad)' );
		svg.appendChild( area );

		var path = document.createElementNS( svgNS, 'path' );
		path.setAttribute( 'd', d );
		path.setAttribute( 'class', 'eshb-trend-line' );
		path.setAttribute( 'fill', 'none' );
		svg.appendChild( path );

		// X axis labels (every ~7th day).
		series.forEach( function ( p, i ) {
			if ( i % 7 !== 0 && i !== series.length - 1 ) {
				return;
			}
			var tx = document.createElementNS( svgNS, 'text' );
			tx.setAttribute( 'x', x( i ) );
			tx.setAttribute( 'y', H - 8 );
			tx.setAttribute( 'class', 'eshb-trend-axis' );
			tx.setAttribute( 'text-anchor', 'middle' );
			tx.textContent = p.label;
			svg.appendChild( tx );
		} );

		wrap.appendChild( svg );
	}

	function smoothPath( pts ) {
		if ( pts.length < 2 ) {
			return pts.length ? 'M ' + pts[ 0 ][ 0 ] + ' ' + pts[ 0 ][ 1 ] : '';
		}
		var d = 'M ' + pts[ 0 ][ 0 ] + ' ' + pts[ 0 ][ 1 ];
		for ( var i = 0; i < pts.length - 1; i++ ) {
			var p0 = pts[ i === 0 ? 0 : i - 1 ];
			var p1 = pts[ i ];
			var p2 = pts[ i + 1 ];
			var p3 = pts[ i + 2 ] ? pts[ i + 2 ] : p2;
			var c1x = p1[ 0 ] + ( p2[ 0 ] - p0[ 0 ] ) / 6;
			var c1y = p1[ 1 ] + ( p2[ 1 ] - p0[ 1 ] ) / 6;
			var c2x = p2[ 0 ] - ( p3[ 0 ] - p1[ 0 ] ) / 6;
			var c2y = p2[ 1 ] - ( p3[ 1 ] - p1[ 1 ] ) / 6;
			d += ' C ' + c1x + ' ' + c1y + ' ' + c2x + ' ' + c2y + ' ' + p2[ 0 ] + ' ' + p2[ 1 ];
		}
		return d;
	}

	/* -------------------------------------------------------------- calendar */

	function initCalendar( calendar ) {
		if ( ! calendar ) {
			return;
		}
		state.bookedMap = {};
		( calendar.days || [] ).forEach( function ( c ) {
			state.bookedMap[ c.date ] = c.status;
		} );
		state.dataYear = calendar.year;
		state.dataMonth = calendar.month;
		state.calYear = calendar.year;
		state.calMonth = calendar.month;
		state.today = ( state.data && state.data.today ) || null;

		// Bind nav arrows only once (render may run twice: initial + refresh).
		if ( ! state.calNavBound ) {
			var prev = root.querySelector( '.eshb-cal-prev' );
			var next = root.querySelector( '.eshb-cal-next' );
			if ( prev ) {
				prev.addEventListener( 'click', function () {
					shiftMonth( -1 );
				} );
			}
			if ( next ) {
				next.addEventListener( 'click', function () {
					shiftMonth( 1 );
				} );
			}
			state.calNavBound = true;
		}

		drawCalendar();
	}

	function shiftMonth( delta ) {
		state.calMonth += delta;
		if ( state.calMonth < 1 ) {
			state.calMonth = 12;
			state.calYear--;
		} else if ( state.calMonth > 12 ) {
			state.calMonth = 1;
			state.calYear++;
		}
		drawCalendar();
	}

	function pad( n ) {
		return ( n < 10 ? '0' : '' ) + n;
	}

	function drawCalendar() {
		var grid = document.getElementById( 'eshb-calendar' );
		var title = document.getElementById( 'eshb-calendar-title' );
		if ( ! grid ) {
			return;
		}

		var year = state.calYear;
		var month = state.calMonth; // 1-12
		var first = new Date( year, month - 1, 1 );
		var startDow = first.getDay();
		var daysInMonth = new Date( year, month, 0 ).getDate();
		var monthNames = [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ];

		if ( title ) {
			title.textContent = monthNames[ month - 1 ] + ' ' + year;
		}

		grid.innerHTML = '';

		// Weekday header.
		var header = el( 'div', 'eshb-cal-row eshb-cal-head' );
		( i18n.weekDays || [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ] ).forEach( function ( wd ) {
			header.appendChild( el( 'span', 'eshb-cal-cell', wd ) );
		} );
		grid.appendChild( header );

		var row = el( 'div', 'eshb-cal-row' );
		var i;

		// Leading blanks.
		for ( i = 0; i < startDow; i++ ) {
			row.appendChild( el( 'span', 'eshb-cal-cell is-muted' ) );
		}

		for ( var day = 1; day <= daysInMonth; day++ ) {
			if ( ( startDow + day - 1 ) % 7 === 0 && day !== 1 ) {
				grid.appendChild( row );
				row = el( 'div', 'eshb-cal-row' );
			}

			var dateStr = year + '-' + pad( month ) + '-' + pad( day );
			var status = state.bookedMap[ dateStr ] || '';
			var cell = el( 'span', 'eshb-cal-cell is-day' );
			if ( status ) {
				cell.classList.add( 'is-' + status );
			}
			if ( dateStr === state.today ) {
				cell.classList.add( 'is-today' );
			}
			cell.appendChild( el( 'span', 'eshb-cal-num', String( day ) ) );
			row.appendChild( cell );
		}

		// Trailing blanks to complete the row.
		while ( row.children.length < 7 ) {
			row.appendChild( el( 'span', 'eshb-cal-cell is-muted' ) );
		}
		grid.appendChild( row );
	}

	/* --------------------------------------------------------- trend filter */

	function initTrendDropdown() {
		var dropdown = document.getElementById( 'eshb-trend-filter' );
		if ( ! dropdown ) {
			return;
		}

		var toggle = dropdown.querySelector( '.eshb-dash-pill' );
		var label = dropdown.querySelector( '.eshb-dash-pill-label' );
		var menu = dropdown.querySelector( '.eshb-dash-dropdown-menu' );
		var subtitle = document.getElementById( 'eshb-trend-subtitle' );
		var items = dropdown.querySelectorAll( '[data-range]' );

		function close() {
			dropdown.classList.remove( 'is-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
		}

		toggle.addEventListener( 'click', function ( e ) {
			e.stopPropagation();
			var open = dropdown.classList.toggle( 'is-open' );
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );

		Array.prototype.forEach.call( items, function ( item ) {
			item.addEventListener( 'click', function () {
				state.trendRange = item.getAttribute( 'data-range' );

				if ( label ) {
					label.textContent = item.textContent;
				}
				if ( subtitle && item.getAttribute( 'data-subtitle' ) ) {
					subtitle.textContent = item.getAttribute( 'data-subtitle' );
				}
				Array.prototype.forEach.call( items, function ( i ) {
					i.classList.toggle( 'is-active', i === item );
				} );

				if ( state.data ) {
					renderTrend( getTrendSeries( state.data ) );
				}
				close();
			} );
		} );

		// Close when clicking outside.
		document.addEventListener( 'click', function ( e ) {
			if ( ! dropdown.contains( e.target ) ) {
				close();
			}
		} );
	}

	/* ----------------------------------------------------------------- boot */

	function boot() {
		initTrendDropdown();

		// Paint instantly from the server-embedded payload, then refresh
		// silently from the REST endpoint.
		if ( cfg.initial && cfg.initial.stats ) {
			state.data = cfg.initial;
			render( cfg.initial );
			root.setAttribute( 'aria-busy', 'false' );
		}
		load();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
