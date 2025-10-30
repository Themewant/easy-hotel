<?php
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
add_action( 'init', 'eshb_accomodaiton_taxonomy_init', 0 );

  
function eshb_accomodaiton_taxonomy_init() {

  // Accomodation Category
  $labels = array(
    'name' => _x( 'Categories', 'taxonomy general name', 'easy-hotel' ),
    'singular_name' => _x( 'Category', 'taxonomy singular name', 'easy-hotel' ),
    'search_items' =>  __( 'Search Categories', 'easy-hotel' ),
    'all_items' => __( 'All Categories', 'easy-hotel' ),
    'parent_item' => __( 'Parent Category', 'easy-hotel' ),
    'parent_item_colon' => __( 'Parent Category:', 'easy-hotel' ),
    'edit_item' => __( 'Edit Category', 'easy-hotel' ), 
    'update_item' => __( 'Update Category', 'easy-hotel' ),
    'add_new_item' => __( 'Add New Category', 'easy-hotel' ),
    'new_item_name' => __( 'New Category Name', 'easy-hotel' ),
    'menu_name' => __( 'Categories', 'easy-hotel' ),
  );    
  
// Now register the taxonomy
  register_taxonomy('eshb_category',array('eshb_accomodation'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'eshb_category' ),
  ));
  
}



