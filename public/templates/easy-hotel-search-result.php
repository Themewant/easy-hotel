<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Name: Easy Hotel Search Result
 * Description: A Page template for accoodation sarch.
 */
get_header();

    // Check if the custom template exists in your plugin directory
    $eshb_plugin_template = ESHB_PL_PATH . 'public/templates/template-parts/search-results-contents.php';
    $eshb_theme_template = get_stylesheet_directory() . '/easy-hotel/templates/template-parts/search-results-contents.php';
    $eshb_child_theme_template = get_template_directory() . '/easy-hotel/templates/template-parts/search-results-contents.php';

    if (file_exists($eshb_child_theme_template)) {
        $eshb_template = $eshb_child_theme_template;
    } elseif (file_exists($eshb_theme_template)) {
        $eshb_template = $eshb_theme_template;
    } else {
        $eshb_template = $eshb_plugin_template;
    }

    
include $eshb_template;

get_footer();