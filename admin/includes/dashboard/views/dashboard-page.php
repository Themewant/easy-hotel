<?php
/**
 * Easy Hotel – Dashboard page markup.
 *
 * Server renders the static shell; dashboard.js fills the live values
 * from the REST endpoint.
 *
 * @package Easy_Hotel
 *
 * @var array $links Admin navigation URLs (from ESHB_Dashboard_Menu).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Stat cards: key, label, dashicon, accent class.
 */
$eshb_stat_cards = array(
	array( 'totalBookings', __( 'Total Bookings', 'easy-hotel' ), 'cart', 'blue' ),
	array( 'pendingBookings', __( 'Pending Bookings', 'easy-hotel' ), 'clock', 'orange' ),
	array( 'checkinsToday', __( 'Check-ins Today', 'easy-hotel' ), 'arrow-down-alt', 'green' ),
	array( 'checkoutsToday', __( 'Check-outs Today', 'easy-hotel' ), 'arrow-up-alt', 'purple' ),
	array( 'availableRooms', __( 'Available Rooms', 'easy-hotel' ), 'admin-home', 'teal' ),
);

/**
 * Quick action buttons: label, dashicon, url, accent class.
 */
$eshb_quick_actions = array(
	array( __( 'Add New Room', 'easy-hotel' ), 'admin-home', $links['addRoom'], 'blue' ),
	array( __( 'View All Bookings', 'easy-hotel' ), 'list-view', $links['bookings'], 'green' ),
	array( __( 'Create Manual Booking', 'easy-hotel' ), 'plus-alt', $links['addBooking'], 'purple' ),
	array( __( 'Manage Availability', 'easy-hotel' ), 'calendar-alt', $links['availability'], 'orange' ),
	array( __( 'Create Coupon', 'easy-hotel' ), 'tickets-alt', $links['coupons'], 'red' ),
);

/**
 * The room rack lives in the optional PMS module. Adding the action only when
 * that module is loaded keeps the quick actions free of a link that would 404
 * on an install running the plugin without it.
 */
if ( class_exists( 'ESHB_PMS_Rack' ) ) {
	$eshb_quick_actions[] = array( __( 'Open Room Rack', 'easy-hotel' ), 'grid-view', $links['rack'], 'teal' );
}

/**
 * The two movement panels. Same shell, different data key and date column, so
 * they are rendered from one loop instead of twice the markup.
 *
 * key, title, dashicon, accent, extra column heading.
 */
$eshb_movement_panels = array(
	array( 'arrivals', __( 'Arriving', 'easy-hotel' ), 'arrow-down-alt', 'green', __( 'Check-out', 'easy-hotel' ) ),
	array( 'departures', __( 'Departing', 'easy-hotel' ), 'arrow-up-alt', 'purple', __( 'Check-in', 'easy-hotel' ) ),
);

/**
 * Day switch shown in every movement panel: value, label.
 */
$eshb_movement_days = array(
	array( 'today', __( 'Today', 'easy-hotel' ) ),
	array( 'tomorrow', __( 'Tomorrow', 'easy-hotel' ) ),
	array( 'yesterday', __( 'Yesterday', 'easy-hotel' ) ),
);
?>
<div class="wrap eshb-dashboard-wrap">
	<div class="eshb-dash-shell" id="eshb-dashboard" aria-busy="true">

		<!-- Main -->
		<div class="eshb-dash-content">

			<!-- Stat cards -->
			<section class="eshb-dash-stats">
				<?php foreach ( $eshb_stat_cards as $eshb_card ) : ?>
					<div class="eshb-dash-stat-card">
						<div class="eshb-dash-stat-icon accent-<?php echo esc_attr( $eshb_card[3] ); ?>">
							<span class="dashicons dashicons-<?php echo esc_attr( $eshb_card[2] ); ?>"></span>
						</div>
						<div class="eshb-dash-stat-body">
							<span class="eshb-dash-stat-label"><?php echo esc_html( $eshb_card[1] ); ?></span>
							<span class="eshb-dash-stat-value" data-stat="<?php echo esc_attr( $eshb_card[0] ); ?>">—</span>
							<span class="eshb-dash-stat-delta" data-delta="<?php echo esc_attr( $eshb_card[0] ); ?>"></span>
						</div>
					</div>
				<?php endforeach; ?>
			</section>

			<!-- Main grid -->
			<section class="eshb-dash-grid">

				<!-- Booking Calendar -->
				<div class="eshb-dash-card eshb-card-calendar">
					<div class="eshb-dash-card-head">
						<h3><?php esc_html_e( 'Booking Calendar', 'easy-hotel' ); ?></h3>
						<div class="eshb-dash-cal-legend">
							<span class="legend booked"><?php esc_html_e( 'Booked', 'easy-hotel' ); ?></span>
							<span class="legend available"><?php esc_html_e( 'Available', 'easy-hotel' ); ?></span>
							<span class="legend blocked"><?php esc_html_e( 'Blocked', 'easy-hotel' ); ?></span>
						</div>
					</div>
					<div class="eshb-dash-cal-nav">
						<button type="button" class="eshb-cal-prev dashicons dashicons-arrow-left-alt2" aria-label="<?php esc_attr_e( 'Previous month', 'easy-hotel' ); ?>"></button>
						<span class="eshb-cal-title" id="eshb-calendar-title"></span>
						<button type="button" class="eshb-cal-next dashicons dashicons-arrow-right-alt2" aria-label="<?php esc_attr_e( 'Next month', 'easy-hotel' ); ?>"></button>
					</div>
					<div class="eshb-dash-calendar" id="eshb-calendar"></div>
				</div>

				<!-- Quick Actions -->
				<div class="eshb-dash-card eshb-card-actions">
					<div class="eshb-dash-card-head"><h3><?php esc_html_e( 'Quick Actions', 'easy-hotel' ); ?></h3></div>
					<div class="eshb-dash-actions">
						<?php foreach ( $eshb_quick_actions as $eshb_action ) : ?>
							<a class="eshb-dash-action" href="<?php echo esc_url( $eshb_action[2] ); ?>">
								<span class="eshb-dash-action-icon accent-<?php echo esc_attr( $eshb_action[3] ); ?> dashicons dashicons-<?php echo esc_attr( $eshb_action[1] ); ?>"></span>
								<span class="eshb-dash-action-label"><?php echo esc_html( $eshb_action[0] ); ?></span>
								<span class="eshb-dash-action-arrow dashicons dashicons-arrow-right-alt2"></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Booking Trend -->
				<div class="eshb-dash-card eshb-card-trend">
					<div class="eshb-dash-card-head">
						<h3><?php esc_html_e( 'Booking Trend', 'easy-hotel' ); ?> <small id="eshb-trend-subtitle"><?php esc_html_e( '(Last 30 Days)', 'easy-hotel' ); ?></small></h3>
						<div class="eshb-dash-dropdown" id="eshb-trend-filter">
							<button type="button" class="eshb-dash-pill" aria-haspopup="true" aria-expanded="false">
								<span class="eshb-dash-pill-label"><?php esc_html_e( '30 Days', 'easy-hotel' ); ?></span>
								<span class="dashicons dashicons-arrow-down-alt2"></span>
							</button>
							<ul class="eshb-dash-dropdown-menu" role="menu">
								<li role="none"><button type="button" role="menuitem" data-range="7d" data-subtitle="<?php esc_attr_e( '(Last 7 Days)', 'easy-hotel' ); ?>"><?php esc_html_e( '7 Days', 'easy-hotel' ); ?></button></li>
								<li role="none"><button type="button" role="menuitem" data-range="30d" data-subtitle="<?php esc_attr_e( '(Last 30 Days)', 'easy-hotel' ); ?>" class="is-active"><?php esc_html_e( '30 Days', 'easy-hotel' ); ?></button></li>
								<li role="none"><button type="button" role="menuitem" data-range="1y" data-subtitle="<?php esc_attr_e( '(Last 12 Months)', 'easy-hotel' ); ?>"><?php esc_html_e( '1 Year', 'easy-hotel' ); ?></button></li>
							</ul>
						</div>
					</div>
					<div class="eshb-dash-trend" id="eshb-trend"></div>
				</div>

				<!-- Recent Bookings -->
				<div class="eshb-dash-card eshb-card-recent">
					<div class="eshb-dash-card-head">
						<h3><?php esc_html_e( 'Recent Bookings', 'easy-hotel' ); ?></h3>
						<a class="eshb-dash-link" href="<?php echo esc_url( $links['bookings'] ); ?>"><?php esc_html_e( 'View All Bookings', 'easy-hotel' ); ?></a>
					</div>
					<div class="eshb-dash-table-wrap">
						<table class="eshb-dash-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Booking ID', 'easy-hotel' ); ?></th>
									<th><?php esc_html_e( 'Room', 'easy-hotel' ); ?></th>
									<th><?php esc_html_e( 'Check-in', 'easy-hotel' ); ?></th>
									<th><?php esc_html_e( 'Check-out', 'easy-hotel' ); ?></th>
									<th><?php esc_html_e( 'Guests', 'easy-hotel' ); ?></th>
									<th><?php esc_html_e( 'Total', 'easy-hotel' ); ?></th>
									<th><?php esc_html_e( 'Status', 'easy-hotel' ); ?></th>
								</tr>
							</thead>
							<tbody id="eshb-recent-bookings">
								<tr><td colspan="7" class="eshb-dash-empty"><?php esc_html_e( 'Loading…', 'easy-hotel' ); ?></td></tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- Room Availability + upsell -->
				<div class="eshb-dash-card-stack">
					<div class="eshb-dash-card eshb-card-rooms">
						<div class="eshb-dash-card-head"><h3><?php esc_html_e( 'Room Availability Summary', 'easy-hotel' ); ?></h3></div>
						<div class="eshb-dash-rooms" id="eshb-room-availability"></div>
					</div>

					<div class="eshb-dash-card eshb-dash-upsell">
						<span class="eshb-dash-upsell-lock dashicons dashicons-lock"></span>
						<h4><?php esc_html_e( 'Get Premium Addons & Themes', 'easy-hotel' ); ?></h4>
						<p><?php esc_html_e( 'Advanced pricing, diposit, ical syncronization, whatspp and many more with our premium add-ons.', 'easy-hotel' ); ?></p>
						<a href="<?php echo esc_url( $links['addons'] ); ?>" class="eshb-dash-upsell-btn"><?php esc_html_e( 'Explore Now', 'easy-hotel' ); ?></a>
					</div>
				</div>

				<!-- Arriving / Departing -->
				<?php foreach ( $eshb_movement_panels as $eshb_panel ) : ?>
					<div class="eshb-dash-card eshb-card-<?php echo esc_attr( $eshb_panel[0] ); ?> eshb-dash-movement" data-movement="<?php echo esc_attr( $eshb_panel[0] ); ?>">
						<div class="eshb-dash-card-head">
							<h3>
								<span class="eshb-dash-head-icon accent-<?php echo esc_attr( $eshb_panel[3] ); ?> dashicons dashicons-<?php echo esc_attr( $eshb_panel[2] ); ?>"></span>
								<?php echo esc_html( $eshb_panel[1] ); ?>
								<span class="eshb-dash-count" data-count>0</span>
							</h3>
							<div class="eshb-dash-head-tools">
								<div class="eshb-dash-search">
									<input type="search" data-search placeholder="<?php esc_attr_e( 'Search', 'easy-hotel' ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: panel name, e.g. Arriving. */ __( 'Search %s', 'easy-hotel' ), $eshb_panel[1] ) ); ?>" />
									<button type="button" class="eshb-dash-search-clear dashicons dashicons-no-alt" data-clear hidden aria-label="<?php esc_attr_e( 'Clear search', 'easy-hotel' ); ?>"></button>
								</div>
								<div class="eshb-dash-segment" role="group">
									<?php foreach ( $eshb_movement_days as $eshb_index => $eshb_day ) : ?>
										<button type="button" data-day="<?php echo esc_attr( $eshb_day[0] ); ?>"<?php echo ( 0 === $eshb_index ) ? ' class="is-active"' : ''; ?>><?php echo esc_html( $eshb_day[1] ); ?></button>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
						<div class="eshb-dash-table-wrap">
							<table class="eshb-dash-table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'ID', 'easy-hotel' ); ?></th>
										<th><?php esc_html_e( 'Customer Name', 'easy-hotel' ); ?></th>
										<th><?php esc_html_e( 'Room Type', 'easy-hotel' ); ?></th>
										<th><?php esc_html_e( 'Adults', 'easy-hotel' ); ?></th>
										<th><?php echo esc_html( $eshb_panel[4] ); ?></th>
										<th><?php esc_html_e( 'Status', 'easy-hotel' ); ?></th>
									</tr>
								</thead>
								<tbody data-rows>
									<tr><td colspan="6" class="eshb-dash-empty"><?php esc_html_e( 'Loading…', 'easy-hotel' ); ?></td></tr>
								</tbody>
							</table>
						</div>
					</div>
				<?php endforeach; ?>

				<!-- Check Availability -->
				<div class="eshb-dash-card eshb-card-availability" id="eshb-availability">
					<div class="eshb-dash-card-head">
						<h3>
							<span class="eshb-dash-head-icon accent-blue dashicons dashicons-calculator"></span>
							<?php esc_html_e( 'Check Availability', 'easy-hotel' ); ?>
						</h3>
					</div>
					<form class="eshb-dash-availability-form" data-availability-form>
						<label class="eshb-dash-field">
							<span class="eshb-dash-field-label"><?php esc_html_e( 'Check-in', 'easy-hotel' ); ?></span>
							<input type="date" name="check_in" data-field="check_in" />
						</label>
						<label class="eshb-dash-field eshb-dash-field--sm">
							<span class="eshb-dash-field-label"><?php esc_html_e( 'Nights', 'easy-hotel' ); ?></span>
							<input type="number" name="nights" data-field="nights" min="1" max="365" step="1" value="1" />
						</label>
						<label class="eshb-dash-field eshb-dash-field--sm">
							<span class="eshb-dash-field-label"><?php esc_html_e( 'Adults', 'easy-hotel' ); ?></span>
							<input type="number" name="adults" data-field="adults" min="0" step="1" value="2" />
						</label>
						<label class="eshb-dash-field eshb-dash-field--sm">
							<span class="eshb-dash-field-label"><?php esc_html_e( 'Children', 'easy-hotel' ); ?></span>
							<input type="number" name="children" data-field="children" min="0" step="1" value="0" />
						</label>
						<button type="submit" class="eshb-dash-btn">
							<span class="dashicons dashicons-admin-home"></span>
							<?php esc_html_e( 'Calculate', 'easy-hotel' ); ?>
						</button>
					</form>
					<p class="eshb-dash-availability-summary" data-availability-summary hidden></p>
					<div class="eshb-dash-availability-result" data-availability-result></div>
				</div>

			</section>
		</div>
	</div>
</div>
