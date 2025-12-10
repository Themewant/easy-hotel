<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function eshb_create_easy_hotel_pages() {

    // Define pages and templates
    $pages = [
        'Easy Hotel Archive' => array('title' => 'Easy Hotel Archive', 'shortcode' => '[eshb_accomodation_grid]'),
        'Easy Hotel Search' => array('title' => 'Easy Hotel Search', 'shortcode' => '[eshb_search_form]'),
        'Easy Hotel Search Result' => array('title' => 'Easy Hotel Search Result', 'shortcode' => '[eshb_accomodation_search_result]'),
    ];

    foreach ($pages as $page_title => $page_data) {
        // Check if the page already exists using WP_Query
        $query = new WP_Query([
            'post_type'   => 'page',
            'title'       => $page_title,
            'posts_per_page' => 1, // Only need one result
        ]);

        // If no page found, create it
        if (!$query->have_posts()) {
            // Create the page with the shortcode in the content
            $page_id = wp_insert_post([
                'post_title'   => $page_data['title'],
                'post_content' => $page_data['shortcode'], // Add shortcode as content
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ]);

            // Check for errors in post creation
            if (is_wp_error($page_id)) {
                //error_log('Error creating page "' . $page_data['title'] . '": ' . $page_id->get_error_message());
            } else {
                // Assign the page template
                update_post_meta($page_id, '_wp_page_template', $page_data['title']);
            }
        }
    }
}



/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_easy_hotel() {

    if ( ! class_exists( 'Appsero\Client' ) ) {
      include ESHB_PL_PATH . 'appsero/Client.php';
    }

    $client = new Appsero\Client( 'aad425e0-9ec8-4de0-a3cf-011a98a4fb39', 'Easy Hotel Booking – Powerful Hotel Booking', __FILE__ );

    // Active insights
    $client->insights()->init();

}

appsero_init_tracker_easy_hotel();