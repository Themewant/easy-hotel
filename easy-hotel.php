<?php
/**
 * Plugin Name: Easy Hotel
 * Description: Easy Hotel Plugin, A complete hotel booking solution for WordPress website.
 * Plugin URI:  https://themewant.com/downloads/hotel-booking/
 * Author:      Themewant
 * Author URI:  http://themewant.com/
 * Version:     1.8.0
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: easy-hotel
 * Domain Path: /languages
*/
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    define( 'ESHB_VERSION', '1.8.0' );
    define( 'ESHB_PL_ROOT', __FILE__ );
    define( 'ESHB_PL_URL', plugins_url( '/', ESHB_PL_ROOT ) );
    define( 'ESHB_PL_PATH', plugin_dir_path( ESHB_PL_ROOT ) );
    define( 'ESHB_DIR_URL', plugin_dir_url( ESHB_PL_ROOT ) );
    define( 'ESHB_PLUGIN_BASE', plugin_basename( ESHB_PL_ROOT ) );
    
    include 'admin/includes/classes/class.helper.php';
    include 'admin/includes/admin-init.php';
    include 'admin/includes/activation.php';
    include 'admin/includes/notice.php';

    include 'public/includes/widgets/widgets.php';
    include 'class.easy-hotel.php';

    add_action( 'plugins_loaded', function(){
        register_activation_hook(__FILE__, 'eshb_create_easy_hotel_pages');
            ESHB_MAIN::instance();
    }, 12 );
    
    /**
     * Initialize the plugin tracker
     *
     * @return void
     */
    function eshb_appsero_init_tracker() {

        if ( ! class_exists( 'Appsero\Client' ) ) {
        include ESHB_PL_PATH . 'apps/Client.php';
        }

        $client = new Appsero\Client( 'aad425e0-9ec8-4de0-a3cf-011a98a4fb39', 'Easy Hotel Booking – Powerful Hotel Booking', __FILE__ );

        // Active insights
        $client->insights()->init();

    }
    add_action( 'plugins_loaded', 'eshb_appsero_init_tracker' );