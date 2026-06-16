<?php
/**
 * Easy Hotel – Dashboard REST controller.
 *
 * Exposes GET /wp-json/eshb/v1/dashboard/stats which the dashboard JS
 * consumes via fetch() (no admin-ajax, no jQuery).
 *
 * @package Easy_Hotel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ESHB_Dashboard_REST {

	const NAMESPACE = 'eshb/v1';

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the dashboard routes.
	 */
	public function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/dashboard/stats',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_stats' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			)
		);
	}

	/**
	 * Only logged-in users who can manage the site may read the data.
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
	 * Return the full dashboard payload.
	 *
	 * @return WP_REST_Response
	 */
	public function get_stats() {
		$data = new ESHB_Dashboard_Data();

		return rest_ensure_response( $data->get_dashboard_data() );
	}
}
