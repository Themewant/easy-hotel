<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
function eshb_accomodation_post_type_init() {
    $labels = array(
        'name'                  => _x( 'Accomodations', 'Post type general name', 'easy-hotel' ),
        'singular_name'         => _x( 'Accomodation', 'Post type singular name', 'easy-hotel' ),
        'menu_name'             => _x( 'Easy Hotel', 'Admin Menu text', 'easy-hotel' ),
        'name_admin_bar'        => _x( 'Accomodation', 'Add New on Toolbar', 'easy-hotel' ),
        'add_new'               => __( 'Add Accomodation', 'easy-hotel' ),
        'add_new_item'          => __( 'Add New Accomodation', 'easy-hotel' ),
        'new_item'              => __( 'New Accomodation', 'easy-hotel' ),
        'edit_item'             => __( 'Edit Accomodation', 'easy-hotel' ),
        'view_item'             => __( 'View Accomodation', 'easy-hotel' ),
        'all_items'             => __( 'All Accomodations', 'easy-hotel' ),
        'search_items'          => __( 'Search Accomodations', 'easy-hotel' ),
        'parent_item_colon'     => __( 'Parent Accomodations:', 'easy-hotel' ),
        'not_found'             => __( 'No accomodations found.', 'easy-hotel' ),
        'not_found_in_trash'    => __( 'No accomodations found in Trash.', 'easy-hotel' ),
        'featured_image'        => _x( 'Accomodation Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'easy-hotel' ),
        'archives'              => _x( 'Accomodation archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'easy-hotel' ),
        'insert_into_item'      => _x( 'Insert into accomodation', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'easy-hotel' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this accomodation', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'easy-hotel' ),
        'filter_items_list'     => _x( 'Filter accomodations list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'easy-hotel' ),
        'items_list_navigation' => _x( 'Accomodations list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'easy-hotel' ),
        'items_list'            => _x( 'Accomodations list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'easy-hotel' ),
    );
 
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_icon'          =>  plugins_url( 'img/fav.svg', __FILE__ ),
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'eshb_accomodation' ),
        'capability_type'    => 'post',
        'taxonomies'         => array('eshb_category'),
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'comments' ),
        'show_in_rest'       => true,
    );
    if ( class_exists( 'WooCommerce' ) ) {
        $args['capability_type'] = 'product';
    }
    register_post_type( 'eshb_accomodation', $args );
}
add_action( 'init', 'eshb_accomodation_post_type_init' );

include 'metaboxes.php';
include 'taxonomies.php';
include 'taxonomies-metaboxes.php';


// Update post type base name
function eshb_change_post_type_archive_base( $args, $post_type ) {

    $eshb_settings = get_option('eshb_settings', []);
    $accomodation_base_name = isset($eshb_settings['accomodation_base_name']) && !empty($eshb_settings['accomodation_base_name']) ? $eshb_settings['accomodation_base_name'] : '';

    if ( !empty($accomodation_base_name) && 'eshb_accomodation' === $post_type ) { // Replace 'your_post_type' with your CPT slug
        $args['rewrite']['slug'] = eshb_get_translated_string($accomodation_base_name); // Set the new archive base name
        $args['has_archive'] = true; // Ensure archive is enabled
    }

    return $args;
}
add_filter( 'register_post_type_args', 'eshb_change_post_type_archive_base', 10, 2 );
