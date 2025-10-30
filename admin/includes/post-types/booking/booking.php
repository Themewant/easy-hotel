<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
function eshb_booking_post_type_init() {
    $labels = array(
        'name'                  => _x( 'Bookings', 'Post type general name', 'easy-hotel' ),
        'singular_name'         => _x( 'Booking', 'Post type singular name', 'easy-hotel' ),
        'menu_name'             => _x( 'Bookings', 'Admin Menu text', 'easy-hotel' ),
        'name_admin_bar'        => _x( 'Booking', 'Add New on Toolbar', 'easy-hotel' ),
        'add_new'               => __( 'Add New', 'easy-hotel' ),
        'add_new_item'          => __( 'Add New Booking', 'easy-hotel' ),
        'new_item'              => __( 'New Booking', 'easy-hotel' ),
        'edit_item'             => __( 'Edit Booking', 'easy-hotel' ),
        'view_item'             => __( 'View Booking', 'easy-hotel' ),
        'all_items'             => __( 'All Bookings', 'easy-hotel' ),
        'search_items'          => __( 'Search Bookings', 'easy-hotel' ),
        'parent_item_colon'     => __( 'Parent Bookings:', 'easy-hotel' ),
        'not_found'             => __( 'No bookings found.', 'easy-hotel' ),
        'not_found_in_trash'    => __( 'No bookings found in Trash.', 'easy-hotel' ),
        'featured_image'        => _x( 'Booking Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'archives'              => _x( 'Booking archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'easy-hotel' ),
        'insert_into_item'      => _x( 'Insert into booking', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'easy-hotel' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this booking', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'easy-hotel' ),
        'filter_items_list'     => _x( 'Filter bookings list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'easy-hotel' ),
        'items_list_navigation' => _x( 'Bookings list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'easy-hotel' ),
        'items_list'            => _x( 'Bookings list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'easy-hotel' ),
    );
 
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'menu_icon'          => 'dashicons-building',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'eshb_booking' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'show_in_rest'       => true,
        'supports'           => array( '' ),
    );

    register_post_type( 'eshb_booking', $args );
}

include 'metaboxes.php';
include 'hooks.php';
 
add_action( 'init', 'eshb_booking_post_type_init' );








