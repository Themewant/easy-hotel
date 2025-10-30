<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
get_header();

// Check if the custom template exists in your plugin directory
$plugin_template = ESHB_PL_PATH . 'public/templates/easy-hotel-archive.php';
$theme_template = get_stylesheet_directory() . '/easy-hotel/templates/easy-hotel-archive.php';
$child_theme_template = get_template_directory() . '/easy-hotel/templates/easy-hotel-archive.php';

if (file_exists($child_theme_template)) {
    $template = $child_theme_template;
} elseif (file_exists($theme_template)) {
    $template = $theme_template;
} else {
    $template = $plugin_template;
}

include $template;

get_footer();
