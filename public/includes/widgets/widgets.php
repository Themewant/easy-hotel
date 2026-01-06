<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
final class Eshb_Elementor_Extension {
    /**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var Elementor_Test_Extension The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return Elementor_Test_Extension An instance of the class.
	 */
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
		add_action( 'plugins_loaded', [ $this, 'init' ] );
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
	public function init() {

		// Add Plugin actions
		add_action( 'elementor/widgets/register', [ $this, 'init_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_category' ] );


		$this->include_files();		
	}

    public function include_files() {       

    }

    public function add_category( $elements_manager ) {
        $elements_manager->add_category(
            'easy_hotel_category',
            [
                'title' => esc_html__('Easy Hotel', 'easy-hotel' ),
                'icon' => esc_attr('fa fa-smile-o'),
            ]
        );
    }

    public function init_widgets() {
		
		//Search Form		
		require_once(__DIR__ . '/search-form/search-form.php');
		\Elementor\Plugin::instance()->widgets_manager->register(new \Eshb_Search_Widget());

		//Booking Form		
		require_once(__DIR__ . '/booking-form/booking-form.php');
		\Elementor\Plugin::instance()->widgets_manager->register(new \Eshb_Booking_Form_Widget());
		
		//Availability Calendar		
		require_once(__DIR__ . '/availability-calendar/availability-calendar.php');
		\Elementor\Plugin::instance()->widgets_manager->register(new \Eshb_Availability_Calendar_Widget());


		//room grid		
		require_once(__DIR__ . '/room-grid/room-grid-widget.php');
		\Elementor\Plugin::instance()->widgets_manager->register(new \Eshb_Room_Grid_Widget());

		//room grid		
		require_once(__DIR__ . '/room-slider/room-slider-widget.php');
		\Elementor\Plugin::instance()->widgets_manager->register(new \Eshb_Room_Slider_Widget());

		//room gaallery		
		require_once(__DIR__ . '/room-gallery/room-gallery.php');
		\Elementor\Plugin::instance()->widgets_manager->register(new \Eshb_Room_Gallery_Widget());

    }
}
Eshb_Elementor_Extension::instance();

add_action( 'init', function() {
  if( ! class_exists( '\Bricks\Elements' ) ) {
	return; // Bricks is not active
  }
  $element_files = [
    __DIR__ . '/bricks/room-gallery/room-gallery.php',
	__DIR__ . '/bricks/room-grid/room-grid.php',
	__DIR__ . '/bricks/room-slider/room-slider.php',
  ];

  foreach ( $element_files as $file ) {
    \Bricks\Elements::register_element( $file );
  }
}, 11 );

