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
/*
 * Rewrite rules for the plugin's post types and taxonomy.
 *
 * Nothing flushed them, so after activation (or after changing the
 * accommodation "Base Name" setting) room and category URLs returned 404
 * until Settings → Permalinks was saved by hand. The flush can't run in the
 * activation request itself (the post types are registered on `init`, which
 * has already run), so activation only sets a flag and the next request
 * flushes once, after every post type is registered.
 */
function eshb_schedule_rewrite_flush() {
    update_option( 'eshb_flush_rewrite_rules', 1, false );
}

function eshb_maybe_flush_rewrite_rules() {
    if ( get_option( 'eshb_flush_rewrite_rules' ) ) {
        delete_option( 'eshb_flush_rewrite_rules' );
        flush_rewrite_rules();
    }
}
add_action( 'init', 'eshb_maybe_flush_rewrite_rules', 999 );

/**
 * On deactivation drop the stored rules; WordPress rebuilds them on the next
 * request, which no longer registers the plugin's post types.
 */
function eshb_clear_rewrite_rules_on_deactivation() {
    delete_option( 'eshb_flush_rewrite_rules' );
    delete_option( 'rewrite_rules' );
}

/**
 * The accommodation base slug comes from the settings; rebuild the rules
 * when it changes.
 */
function eshb_flush_rewrite_rules_on_base_change( $old_value, $new_value ) {
    $old_base = is_array( $old_value ) ? (string) ( $old_value['accomodation_base_name'] ?? '' ) : '';
    $new_base = is_array( $new_value ) ? (string) ( $new_value['accomodation_base_name'] ?? '' ) : '';

    if ( $old_base !== $new_base ) {
        eshb_schedule_rewrite_flush();
    }
}
add_action( 'update_option_eshb_settings', 'eshb_flush_rewrite_rules_on_base_change', 10, 2 );
