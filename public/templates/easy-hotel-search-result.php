

<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Name: Easy Hotel Search Result
 * Description: A Page template for accoodation sarch.
 */
get_header();

    // Check if the custom template exists in your plugin directory
    $plugin_template = ESHB_PL_PATH . 'public/templates/template-parts/search-results-contents.php';
    $theme_template = get_stylesheet_directory() . '/easy-hotel/templates/template-parts/search-results-contents.php';
    $child_theme_template = get_template_directory() . '/easy-hotel/templates/template-parts/search-results-contents.php';

    if (file_exists($child_theme_template)) {
        $template = $child_theme_template;
    } elseif (file_exists($theme_template)) {
        $template = $theme_template;
    } else {
        $template = $plugin_template;
    }

    
include $template;

get_footer();