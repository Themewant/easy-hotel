<?php
/**
 * Easy Hotel – Front desk panels (assets + REST).
 *
 * Wires the "Arriving", "Departing" and "Check Availability" cards on the
 * dashboard: ships the movement lists with the page so they paint instantly,
 * and exposes one route for the availability calculator, which is the only part
 * that needs a round-trip (its answer depends on what the user types).
 *
 * @package Easy_Hotel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ESHB_Dashboard_Frontdesk {

	/**
	 * Script handle, also the localised object's suffix.
	 */
	const HANDLE = 'eshb-dashboard-frontdesk';

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		// After the dashboard's own assets (priority 10) so this script is
		// queued behind eshb-dashboard.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );
	}

	/**
	 * GET /wp-json/eshb/v1/dashboard/availability
	 */
	public function register_routes() {
		register_rest_route(
			ESHB_Dashboard_REST::NAMESPACE,
			'/dashboard/availability',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_availability' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(
					'check_in' => array(
						'type'    => 'string',
						'default' => '',
					),
					'nights'   => array(
						'type'    => 'integer',
						'default' => 1,
					),
					'adults'   => array(
						'type'    => 'integer',
						'default' => 2,
					),
					'children' => array(
						'type'    => 'integer',
						'default' => 0,
					),
				),
			)
		);
	}

	/**
	 * Same gate as the rest of the dashboard data.
	 *
	 * @return bool|WP_Error
	 */
	public function permissions_check() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'eshb_forbidden',
				__( 'You are not allowed to view the dashboard data.', 'easy-hotel' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Availability for the requested stay.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function get_availability( $request ) {
		$data = new ESHB_Dashboard_Frontdesk_Data();

		return rest_ensure_response(
			$data->get_availability(
				(string) $request->get_param( 'check_in' ),
				(int) $request->get_param( 'nights' ),
				(int) $request->get_param( 'adults' ),
				(int) $request->get_param( 'children' )
			)
		);
	}

	/**
	 * Load the front desk script on the dashboard screen only.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( (string) $hook, ESHB_Dashboard_Menu::PAGE_SLUG ) ) {
			return;
		}

		$version = defined( 'ESHB_VERSION' ) ? ESHB_VERSION : '1.0.0';
		$data    = new ESHB_Dashboard_Frontdesk_Data();
		$days    = $data->get_days();

		wp_enqueue_script(
			self::HANDLE,
			ESHB_PL_URL . 'admin/includes/dashboard/assets/js/frontdesk.js',
			array(),
			$version,
			true
		);

		// The movement lists are computed here rather than fetched, so the panels
		// are filled in the first paint. They are a snapshot of page load, which
		// is exactly what the rest of the dashboard shows too.
		wp_localize_script(
			self::HANDLE,
			'eshbFrontdesk',
			array(
				'availabilityUrl' => esc_url_raw( rest_url( ESHB_Dashboard_REST::NAMESPACE . '/dashboard/availability' ) ),
				'nonce'           => wp_create_nonce( 'wp_rest' ),
				'movements'       => $data->get_movements(),
				'today'           => $days['today'],
				'i18n'            => array(
					'noArrivals'   => array(
						'today'     => __( 'No arrivals for today.', 'easy-hotel' ),
						'tomorrow'  => __( 'No arrivals for tomorrow.', 'easy-hotel' ),
						'yesterday' => __( 'No arrivals for yesterday.', 'easy-hotel' ),
					),
					'noDepartures' => array(
						'today'     => __( 'No departures for today.', 'easy-hotel' ),
						'tomorrow'  => __( 'No departures for tomorrow.', 'easy-hotel' ),
						'yesterday' => __( 'No departures for yesterday.', 'easy-hotel' ),
					),
					'noMatch'      => __( 'No booking matches your search.', 'easy-hotel' ),
					'calculating'  => __( 'Checking availability…', 'easy-hotel' ),
					'error'        => __( 'Could not check availability. Please try again.', 'easy-hotel' ),
					'noRooms'      => __( 'No rooms are set up yet.', 'easy-hotel' ),
					'free'         => __( 'free', 'easy-hotel' ),
					'full'         => __( 'Fully booked', 'easy-hotel' ),
					'tooSmall'     => __( 'Too small for this party', 'easy-hotel' ),
					'sleeps'       => __( 'Sleeps', 'easy-hotel' ),
					/* translators: 1: free units, 2: room types, 3: check-in date, 4: check-out date. */
					'summary'      => __( '%1$s room(s) free across %2$s room type(s) — %3$s → %4$s', 'easy-hotel' ),
				),
			)
		);
	}
}
