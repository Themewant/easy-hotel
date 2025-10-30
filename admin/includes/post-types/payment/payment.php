<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
function eshb_payment_post_type_init() {
    $labels = array(
        'name'                  => _x( 'Payments', 'Post type general name', 'easy-hotel' ),
        'singular_name'         => _x( 'Payment', 'Post type singular name', 'easy-hotel' ),
        'menu_name'             => _x( 'Payments', 'Admin Menu text', 'easy-hotel' ),
        'name_admin_bar'        => _x( 'Payment', 'Add New on Toolbar', 'easy-hotel' ),
        'add_new'               => __( 'Add New', 'easy-hotel' ),
        'add_new_item'          => __( 'Add New Payment', 'easy-hotel' ),
        'new_item'              => __( 'New Payment', 'easy-hotel' ),
        'edit_item'             => __( 'Edit Payment', 'easy-hotel' ),
        'view_item'             => __( 'View Payment', 'easy-hotel' ),
        'all_items'             => __( 'All Payments', 'easy-hotel' ),
        'search_items'          => __( 'Search Payments', 'easy-hotel' ),
        'parent_item_colon'     => __( 'Parent Payments:', 'easy-hotel' ),
        'not_found'             => __( 'No payments found.', 'easy-hotel' ),
        'not_found_in_trash'    => __( 'No payments found in Trash.', 'easy-hotel' ),
        'featured_image'        => _x( 'Payment Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'archives'              => _x( 'Payment archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'easy-hotel' ),
        'insert_into_item'      => _x( 'Insert into payment', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'easy-hotel' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this payment', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'easy-hotel' ),
        'filter_items_list'     => _x( 'Filter payments list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'easy-hotel' ),
        'items_list_navigation' => _x( 'Payments list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'easy-hotel' ),
        'items_list'            => _x( 'Payments list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'easy-hotel' ),
    );
 
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'menu_icon'          => 'dashicons-building',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'eshb_payment' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'show_in_rest'       => true,
        'supports'           => array( '' ),
    );

    register_post_type( 'eshb_payment', $args );
}

add_action( 'init', 'eshb_payment_post_type_init' );
include 'metaboxes.php';



function eshb_add_view_payment_data_link($actions, $post) {
    if ($post->post_type == 'eshb_payment') { // Target the 'eshb_booking' post type
        unset($actions['view']);
        unset($actions['inline hide-if-no-js']);
        unset($actions['edit']);
    }
    return $actions;
}
add_filter('post_row_actions', 'eshb_add_view_payment_data_link', 10, 2);





