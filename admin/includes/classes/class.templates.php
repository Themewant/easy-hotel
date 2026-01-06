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
        add_filter( 'get_block_templates', [$this, 'eshb_register_accomodation_block_template'], 10, 3 );
        add_filter('the_content', [$this, 'eshb_page_contents']);
        add_filter('the_content', [$this, 'eshb_search_result_page_contents']);
	}

    function eshb_is_block_theme() {
        return function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
    }

    
    public function eshb_accomodation_templates($template) {
        // Check if the post type is 'eshb_accomodation'
        if (is_singular('eshb_accomodation')) {
            if ( $this->eshb_is_block_theme() ) {
                return $template;
            }
            $eshb_settings = get_option('eshb_settings', []);
            $template_style = 'style-one';

            // Check if plugin settings and archive-page are available
            if (isset($eshb_settings['single-page-template-style']) && !empty($eshb_settings['single-page-template-style'])) {
                $template_style = $eshb_settings['single-page-template-style']; // Return original content if settings are not properly set
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
            if (!empty($_GET['template-style'])) {
                // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
                if(!empty($_GET['template-style'])){
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
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
            if ( $this->eshb_is_block_theme() ) {
                return $template;
            }
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

    public function eshb_register_accomodation_block_template( $templates, $query, $template_type ) {

        if (
            ! function_exists( 'wp_is_block_theme' ) ||
            ! wp_is_block_theme() ||
            $template_type !== 'wp_template' ||
            empty( $query['slug__in'] ) ||
            ! is_array( $query['slug__in'] )
        ) {
            return $templates;
        }

        $map = [
            'single-eshb_accomodation'  => 'eshb_get_accomodation_single_block_template',
            'archive-eshb_accomodation' => 'eshb_get_accomodation_archive_block_template',
        ];

        foreach ( $map as $slug => $callback ) {
            if ( in_array( $slug, $query['slug__in'], true ) && method_exists( $this, $callback ) ) {
                $template = $this->$callback();
                if ( $template ) {
                    $templates[] = $template;
                }
                break;
            }
        }

        return $templates;
    }


    function eshb_get_accomodation_single_block_template() {

        $file = ESHB_PL_PATH . 'public/templates/single/block/single-eshb_accomodation.html';

        if ( ! file_exists( $file ) ) {
            return null;
        }

        $content = file_get_contents( $file );

        if ( empty( $content ) ) {
            return null;
        }

        $template              = new WP_Block_Template();
        $template->id          = 'easyhotel-accomodation//single-eshb_accomodation';
        $template->theme       = get_stylesheet();
        $template->slug        = 'single-eshb_accomodation';
        $template->title       = __( 'Accommodation Single', 'eshb' );
        $template->description = __( 'Single template for Accommodation CPT', 'eshb' );
        $template->content     = $content;
        $template->source      = 'plugin';
        $template->type        = 'wp_template';
        $template->status      = 'publish';
        $template->is_custom   = true;

        return $template;
    }

    function eshb_get_accomodation_archive_block_template() {

        $file = ESHB_PL_PATH . 'public/templates/archive/block/archive-eshb_accomodation.html';

        if ( ! file_exists( $file ) ) {
            return null;
        }

        $content = file_get_contents( $file );

        if ( empty( $content ) ) {
            return null;
        }

        $template              = new WP_Block_Template();
        $template->id          = 'easyhotel-accomodation//archive-eshb_accomodation';
        $template->theme       = get_stylesheet();
        $template->slug        = 'archive-eshb_accomodation';
        $template->title       = __( 'Accommodation Archive', 'eshb' );
        $template->description = __( 'Archive template for Accommodation CPT', 'eshb' );
        $template->content     = $content;
        $template->source      = 'plugin';
        $template->type        = 'wp_template';
        $template->status      = 'publish';
        $template->is_custom   = true;

        return $template;
    }

    
}
ESHB_Templates::instance();