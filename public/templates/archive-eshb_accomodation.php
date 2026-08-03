<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
get_header();

// Check if the custom template exists in your plugin directory
$eshb_plugin_template = ESHB_PL_PATH . 'public/templates/easy-hotel-archive.php';
$eshb_theme_template = get_stylesheet_directory() . '/easy-hotel/templates/easy-hotel-archive.php';
$eshb_child_theme_template = get_template_directory() . '/easy-hotel/templates/easy-hotel-archive.php';

if (file_exists($eshb_child_theme_template)) {
    $eshb_template = $eshb_child_theme_template;
} elseif (file_exists($eshb_theme_template)) {
    $eshb_template = $eshb_theme_template;
} else {
    $eshb_template = $eshb_plugin_template;
}

include $eshb_template;

get_footer();
