<?php
/**
 * Plugin Name: Easy Hotel
 * Description: Easy Hotel Plugin, A complete hotel booking solution for WordPress website.
 * Plugin URI:  https://themewant.com/downloads/hotel-booking/
 * Author:      Themewant
 * Author URI:  http://themewant.com/
 * Version:     2.0.9
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: easy-hotel
 * Domain Path: /languages
*/
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    define( 'ESHB_VERSION', '2.0.9' );
    define( 'ESHB_PL_ROOT', __FILE__ );
    define( 'ESHB_PL_URL', plugins_url( '/', ESHB_PL_ROOT ) );
    define( 'ESHB_PL_PATH', plugin_dir_path( ESHB_PL_ROOT ) );
    define( 'ESHB_DIR_URL', plugin_dir_url( ESHB_PL_ROOT ) );
    define( 'ESHB_PLUGIN_BASE', plugin_basename( ESHB_PL_ROOT ) );
    
    include 'admin/includes/classes/class.helper.php';
    include 'admin/includes/admin-init.php';
    include 'admin/includes/activation.php';
    include 'admin/includes/compat-bogo.php';

    include 'public/includes/widgets/widgets.php';
    include 'public/includes/gutenberg/blocks/blocks.php';
    include 'admin/includes/dashboard/init.php';
    include 'class.easy-hotel.php';

    // PMS module. Self contained under /pms — remove this line to disable it entirely.
    include 'pms/pms.php';

    register_activation_hook(__FILE__, 'eshb_create_easy_hotel_pages');
    register_activation_hook(__FILE__, 'eshb_schedule_rewrite_flush');
    register_deactivation_hook(__FILE__, 'eshb_clear_rewrite_rules_on_deactivation');
    add_action( 'plugins_loaded', function(){
            ESHB_MAIN::instance();
    }, 12 );