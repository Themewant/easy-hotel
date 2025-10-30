<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Name: Easy Hotel Search
 * Description: A Page template for accoodation sarch.
 */

$view = new ESHB_View();
$search_form = $view->eshb_get_search_form_html();  
echo esc_html($search_form);

