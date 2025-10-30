<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
function eshb_add_view_booking_request_data_link_eshb_booking_request($actions, $post) {
    if ($post->post_type == 'eshb_booking_request') { // Target the 'eshb_booking_request' post type
        unset($actions['view']);
    }
    return $actions;
}
add_filter('post_row_actions', 'eshb_add_view_booking_request_data_link_eshb_booking_request', 10, 2);










