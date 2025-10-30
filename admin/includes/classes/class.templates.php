<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class ESHB_Templates {


    private static $_instance = null;
	
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

    public function __construct() {

		add_action( 'plugins_loaded', [ $this, 'init' ] );
        
	}

    public function init() {
        add_filter('template_include', [$this, 'eshb_accomodation_templates']);
        add_filter('the_content', [$this, 'eshb_page_contents']);
        add_filter('the_content', [$this, 'eshb_search_result_page_contents']);
	}
    
    public function eshb_accomodation_templates($template) {
        // Check if the post type is 'eshb_accomodation'
        if (is_singular('eshb_accomodation')) {

            $eshb_settings = get_option('eshb_settings', []);
            $template_style = 'style-one';

            // Check if plugin settings and archive-page are available
            if (isset($eshb_settings['single-page-template-style']) && !empty($eshb_settings['single-page-template-style'])) {
                $template_style = $eshb_settings['single-page-template-style']; // Return original content if settings are not properly set
            }

            // Verify nonce for security
            if (isset($_GET['template-style']) && !empty($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
                if(!empty($_GET['template-style'])){
                    $template_style = sanitize_text_field( wp_unslash( $_GET['template-style'] ) );
                }
            }

            // Get the page ID from the plugin settings
            $page_id = $eshb_settings['archive-page'];

            // Check if the custom template exists in your plugin directory
            $plugin_template = ESHB_PL_PATH . 'public/templates/single/single-eshb_accomodation-' . $template_style . '.php';
            $theme_template = get_stylesheet_directory() . '/easy-hotel/templates/single/single-eshb_accomodation-' . $template_style . '.php';
            $child_theme_template = get_template_directory() . '/easy-hotel/templates/single/single-eshb_accomodation-' . $template_style . '.php';
    
            if (file_exists($child_theme_template)) {
                $template = $child_theme_template;
            } elseif (file_exists($theme_template)) {
                $template = $theme_template;
            } else {
                $template = $plugin_template;
            }

    
            if (file_exists($template)) {
                // Use the custom template
                return $template;
            }
        }

        if (is_post_type_archive('eshb_accomodation') || is_tax( 'eshb_category' )) {
           
            // Check if the custom template exists in your plugin directory
            $plugin_template = ESHB_PL_PATH . 'public/templates/archive-eshb_accomodation.php';
            $theme_template = get_stylesheet_directory() . '/easy-hotel/templates/archive-eshb_accomodation.php';
            $child_theme_template = get_template_directory() . '/easy-hotel/templates/archive-eshb_accomodation.php';
    
            if (file_exists($child_theme_template)) {
                $template = $child_theme_template;
            } elseif (file_exists($theme_template)) {
                $template = $theme_template;
            } else {
                $template = $plugin_template;
            }
    
    
            if (file_exists($template)) {
                // Use the custom template
                return $template;
            }
        }
        
        // Return the default template if the custom template is not found
        return $template;
    }

    public function eshb_page_contents($content) {
        // Retrieve the plugin settings
        $eshb_settings = get_option('eshb_settings');
        
        // Check if plugin settings and archive-page are available
        if (empty($eshb_settings) || empty($eshb_settings['archive-page'])) {
            return $content; // Return original content if settings are not properly set
        }
    
        // Get the page ID from the plugin settings
        $page_id = $eshb_settings['archive-page'];

    
        // Get the current page ID
        $selected_page_id = get_queried_object_id();

        
        // If the current page ID matches the specified archive page ID, replace content with shortcode
        if ($selected_page_id == $page_id) {
            return do_shortcode('[eshb_accomodation_grid]');
        }
    
        // If IDs don't match, return the original content
        return $content;
    }

    public function eshb_search_result_page_contents($content) {
        // Retrieve the plugin settings
        $eshb_settings = get_option('eshb_settings');
        
        // Check if plugin settings and archive-page are available
        if (empty($eshb_settings) || empty($eshb_settings['search-result-page'])) {
            return $content; // Return original content if settings are not properly set
        }
    
        // Get the page ID from the plugin settings
        $search_result_page_id = $eshb_settings['search-result-page'];
    
        // Get the current page ID
        $selected_page_id = get_queried_object_id();

        
        // If the current page ID matches the specified archive page ID, replace content with shortcode
        if($selected_page_id == $search_result_page_id){
            return do_shortcode('[eshb_accomodation_search_result]');
        }
    
        // If IDs don't match, return the original content
        return $content;
    }
    
}
ESHB_Templates::instance();

