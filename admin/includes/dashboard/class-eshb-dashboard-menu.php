<?php
/**
 * Easy Hotel – Dashboard admin page (menu, assets, render).
 *
 * Registers a "Dashboard" submenu pinned to the TOP of the Easy Hotel menu,
 * enqueues the page assets (only on this screen) and renders the view.
 *
 * @package Easy_Hotel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ESHB_Dashboard_Menu {

	/**
	 * Parent menu slug (the Accommodation CPT menu used by the plugin).
	 */
	const PARENT_SLUG = 'edit.php?post_type=eshb_accomodation';

	/**
	 * This page's slug.
	 */
	const PAGE_SLUG = 'eshb-dashboard';

	/**
	 * Hook suffix returned by add_submenu_page(), used to scope assets.
	 *
	 * @var string
	 */
	protected $hook_suffix = '';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		// Runs after the plugin's own reposition (priority 999) to pin us first.
		add_action( 'admin_menu', array( $this, 'pin_to_top' ), 1001 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add the Dashboard submenu.
	 */
	public function register_menu() {
		$this->hook_suffix = add_submenu_page(
			self::PARENT_SLUG,
			__( 'Dashboard', 'easy-hotel' ),
			__( 'Dashboard', 'easy-hotel' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Move the Dashboard submenu to the very top of the Easy Hotel menu.
	 */
	public function pin_to_top() {
		global $submenu;

		if ( empty( $submenu[ self::PARENT_SLUG ] ) ) {
			return;
		}

		$items   = $submenu[ self::PARENT_SLUG ];
		$dash     = null;
		$filtered = array();

		foreach ( $items as $item ) {
			if ( isset( $item[2] ) && self::PAGE_SLUG === $item[2] ) {
				$dash = $item;
			} else {
				$filtered[] = $item;
			}
		}

		if ( $dash ) {
			array_unshift( $filtered, $dash );
			$submenu[ self::PARENT_SLUG ] = array_values( $filtered );
		}
	}

	/**
	 * Enqueue dashboard JS only on the dashboard screen.
	 *
	 * The dashboard CSS ships inside admin.min.css (already enqueued globally
	 * by plugin-scripts.php), scoped under .eshb-dashboard-wrap.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_assets( $hook ) {
		if ( $hook !== $this->hook_suffix ) {
			return;
		}

		$version = defined( 'ESHB_VERSION' ) ? ESHB_VERSION : '1.0.0';

		wp_enqueue_script(
			'eshb-dashboard',
			ESHB_PL_URL . 'admin/includes/dashboard/assets/js/dashboard.js',
			array(),
			$version,
			true
		);

		// Compute the data once on the server so the page paints instantly,
		// without waiting on the REST round-trip. The REST endpoint is still
		// used afterwards to refresh in the background.
		$initial = array();
		if ( class_exists( 'ESHB_Dashboard_Data' ) ) {
			$data    = new ESHB_Dashboard_Data();
			$initial = $data->get_dashboard_data();
		}

		wp_localize_script(
			'eshb-dashboard',
			'eshbDashboard',
			array(
				'restUrl' => esc_url_raw( rest_url( ESHB_Dashboard_REST::NAMESPACE . '/dashboard/stats' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'initial' => $initial,
				'links'   => $this->get_nav_links(),
				'i18n'    => array(
					'loading'   => __( 'Loading…', 'easy-hotel' ),
					'noData'    => __( 'No bookings found.', 'easy-hotel' ),
					'error'     => __( 'Could not load dashboard data.', 'easy-hotel' ),
					'available' => __( 'Available', 'easy-hotel' ),
					'updated'   => __( 'Updated just now', 'easy-hotel' ),
					'fromLast'  => __( 'from last month', 'easy-hotel' ),
					'fromYday'  => __( 'from yesterday', 'easy-hotel' ),
					'weekDays'  => array(
						__( 'Sun', 'easy-hotel' ),
						__( 'Mon', 'easy-hotel' ),
						__( 'Tue', 'easy-hotel' ),
						__( 'Wed', 'easy-hotel' ),
						__( 'Thu', 'easy-hotel' ),
						__( 'Fri', 'easy-hotel' ),
						__( 'Sat', 'easy-hotel' ),
					),
				),
			)
		);
	}

	/**
	 * Admin URLs used by the app sidebar / quick actions.
	 *
	 * @return array
	 */
	public function get_nav_links() {
		return array(
			'dashboard'    => admin_url( self::PARENT_SLUG . '&page=' . self::PAGE_SLUG ),
			'bookings'     => admin_url( 'edit.php?post_type=eshb_booking' ),
			'rooms'        => admin_url( 'edit.php?post_type=eshb_accomodation' ),
			'addRoom'      => admin_url( 'post-new.php?post_type=eshb_accomodation' ),
			'addBooking'   => admin_url( 'post-new.php?post_type=eshb_booking' ),
			'availability' => admin_url( 'edit.php?post_type=eshb_accomodation&page=eshb_bookings_calendar' ),
			'pricing'      => admin_url( 'edit.php?post_type=eshb_session' ),
			'coupons'      => admin_url( 'edit.php?post_type=eshb_coupon' ),
			'services'     => admin_url( 'edit.php?post_type=eshb_service' ),
			'settings'     => admin_url( 'edit.php?post_type=eshb_accomodation&page=easy-hotel-settings' ),
			'addons'       => admin_url( 'edit.php?post_type=eshb_accomodation&page=edit.php%3Fpost_type%3Deshb_addons' ),
		);
	}

	/**
	 * Render the dashboard page from the view template.
	 */
	public function render_page() {
		$links = $this->get_nav_links();
		require __DIR__ . '/views/dashboard-page.php';
	}
}
