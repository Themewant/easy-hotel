<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function create_block_accomodation_grid_block_init() {
	// Register the main plugin style
	wp_register_style( 
		'eshb-style', 
		ESHB_PL_URL . 'public/assets/css/public.css', 
		array(), 
		ESHB_VERSION 
	);

	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-accomodation-grid-style',
		plugins_url( 'build/accomodation-grid/style-index.css', __FILE__ ),
		array( 'eshb-style' ),
		ESHB_VERSION
	);

	register_block_type( __DIR__ . '/build/accomodation-grid', array(
		'style'         => 'eshb-accomodation-grid-style',
		'editor_style'  => 'eshb-accomodation-grid-style',
	) );
}
add_action( 'init', 'create_block_accomodation_grid_block_init' );
