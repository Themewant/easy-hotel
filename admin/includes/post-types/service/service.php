<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
function eshb_service_post_type_init() {
    $labels = array(
        'name'                  => _x( 'Services', 'Post type general name', 'easy-hotel' ),
        'singular_name'         => _x( 'Service', 'Post type singular name', 'easy-hotel' ),
        'menu_name'             => _x( 'Services', 'Admin Menu text', 'easy-hotel' ),
        'name_admin_bar'        => _x( 'Service', 'Add New on Toolbar', 'easy-hotel' ),
        'add_new'               => __( 'Add New', 'easy-hotel' ),
        'add_new_item'          => __( 'Add New Service', 'easy-hotel' ),
        'new_item'              => __( 'New Service', 'easy-hotel' ),
        'edit_item'             => __( 'Edit Service', 'easy-hotel' ),
        'view_item'             => __( 'View Service', 'easy-hotel' ),
        'all_items'             => __( 'All Services', 'easy-hotel' ),
        'search_items'          => __( 'Search Services', 'easy-hotel' ),
        'parent_item_colon'     => __( 'Parent Services:', 'easy-hotel' ),
        'not_found'             => __( 'No services found.', 'easy-hotel' ),
        'not_found_in_trash'    => __( 'No services found in Trash.', 'easy-hotel' ),
        'featured_image'        => _x( 'Service Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'archives'              => _x( 'Service archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'easy-hotel' ),
        'insert_into_item'      => _x( 'Insert into service', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'easy-hotel' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this service', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'easy-hotel' ),
        'filter_items_list'     => _x( 'Filter services list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'easy-hotel' ),
        'items_list_navigation' => _x( 'Services list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'easy-hotel' ),
        'items_list'            => _x( 'Services list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'easy-hotel' ),
    );
 
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'menu_icon'          => 'dashicons-building',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'eshb_service' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'show_in_rest'       => true,
        'supports'           => array( 'title' ),
    );
 
    register_post_type( 'eshb_service', $args );
}
 
add_action( 'init', 'eshb_service_post_type_init' );

include 'metaboxes.php';





