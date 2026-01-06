<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
class ESHB_MAIN {

    private static $_instance = null;
	
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'includes' ], 11 );
		add_action( 'plugins_loaded', [ $this, 'init' ] );
		add_action( 'init', [$this, 'enable_elementor_for_custom_post_type'] );
		add_filter( 'admin_body_class',  [$this, 'add_admin_body_class'] );
		add_action( 'phpmailer_init', [$this, 'enable_local_mail'] );
	}

	

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Elementor (and other plugins) are loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function includes(){
		include 'class.session-manager.php';
        include 'public/includes/plugin-scripts.php';
        include 'public/includes/classes/class.view.php';
        include 'public/includes/dynamic-css.php';
        include 'admin/includes/woocommerce-filters.php';
	}
	 
	public function init() {
		// Add Plugin actions
		add_filter( 'plugin_action_links_' . ESHB_PLUGIN_BASE, [ $this, 'easy_hotel_plugin_action_links' ], 10, 4 );
		add_action( 'init', [$this, 'eshb_add_image_sizes'] );
		add_filter( 'image_size_names_choose', [$this, 'eshb_add_image_size_to_media_library'] );
	}


	public function easy_hotel_plugin_action_links( $plugin_actions, $plugin_file, $plugin_data, $context ) {
		$new_actions = array();
		$new_actions['easy_hotel_plugin_actions_setting'] = '<a href="'.admin_url( 'edit.php?post_type=eshb_accomodation&page=easy-hotel-settings' ).'">Settings</a>';
		return array_merge( $new_actions, $plugin_actions );
	}
	
	public function eshb_add_image_sizes() {
		// Register a new image size
		add_image_size( 'eshb_thumbnail', 533, 533, true );
	}
	public function eshb_add_image_size_to_media_library( $sizes ) {
		return array_merge( $sizes, array(
			'eshb_thumbnail' => 'Easy Hotel Thumbnail',
		) );
	}
	
	public function custom_oembed_autoplay($html, $url, $args) {
		if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
			$html = str_replace('?feature=oembed', '?feature=oembed&autoplay=1&mute=1', $html);
		} elseif (strpos($url, 'vimeo.com') !== false) {
			$html = str_replace('"', '?autoplay=1&muted=1"', $html);
		}
		return $html;
	}

	public function enable_elementor_for_custom_post_type (){
		add_post_type_support( 'eshb_accomodation', 'elementor' );
	}

	public function add_admin_body_class ($classes) {
		$plugin_screens = [
			'edit-eshb_booking', 
			'eshb_booking', 
			'edit-eshb_accomodation', 
			'eshb_accomodation',
			'edit-eshb_payment', 
			'eshb_payment',
			'edit-eshb_service', 
			'eshb_service',
			'edit-eshb_session', 
			'eshb_session',
			'edit-eshb_coupon', 
			'eshb_coupon',
			'admin_page_view_booking_data',
			'edit-eshb_email_template',
		];
		$screen = get_current_screen();
        if ( isset($screen->id) && in_array($screen->id, $plugin_screens) ) {
        	$classes .= ' eshb-plugin-page ';
    	}
		return $classes;
	}

	public function enable_local_mail($phpmailer){
		// Check if running on localhost
		if ( 
			!empty($_SERVER['HTTP_HOST']) &&
			strpos(sanitize_text_field( wp_unslash($_SERVER['HTTP_HOST'] ) ), 'localhost') !== false ||
    		strpos(sanitize_text_field( wp_unslash($_SERVER['HTTP_HOST'] ) ), '127.0.0.1') !== false 
		) {
			// error_log('mail server is running on localhost!');
			$phpmailer->isSMTP();
			$phpmailer->Host = 'localhost';
			$phpmailer->Port = 1025;
		}
	}
}

add_action( 'init', 'eshb_add_image_sizes' );
function eshb_add_image_sizes() {
	// Register a new image size
	add_image_size( 'eshb_thumbnail', 533, 533, true );
}
