<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Name: Easy Hotel Search
 * Description: A Page template for accoodation sarch.
 */

$eshb_view = new ESHB_View();
$eshb_search_form = $eshb_view->eshb_get_search_form_html();  
echo esc_html($eshb_search_form);

