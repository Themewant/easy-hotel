<?php
/**
 * Easy Hotel – Dashboard bootstrap.
 *
 * Loaded once from the main plugin file. Wires up the menu page and the
 * REST controller. The data layer is instantiated on demand.
 *
 * @package Easy_Hotel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/class-eshb-dashboard-data.php';
require_once __DIR__ . '/class-eshb-dashboard-rest.php';
require_once __DIR__ . '/class-eshb-dashboard-menu.php';

/**
 * Initialise the dashboard module.
 */
function eshb_dashboard_init() {
	new ESHB_Dashboard_Menu();
	new ESHB_Dashboard_REST();
}
add_action( 'plugins_loaded', 'eshb_dashboard_init', 13 );
