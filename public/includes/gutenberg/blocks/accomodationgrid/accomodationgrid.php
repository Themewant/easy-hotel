<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function create_block_accomodationgrid_block_init() {
	// Register the main plugin style
	wp_register_style( 
		'eshb-style', 
		ESHB_PL_URL . 'public/assets/css/public.css', 
		array(), 
		ESHB_VERSION 
	);

	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-accomodationgrid-style',
		plugins_url( 'build/accomodationgrid/style-index.css', __FILE__ ),
		array( 'eshb-style' ),
		filemtime( __DIR__ . '/build/accomodationgrid/style-index.css' )
	);

	register_block_type( __DIR__ . '/build/accomodationgrid', array(
		'style'         => 'eshb-accomodationgrid-style',
		'editor_style'  => 'eshb-accomodationgrid-style',
	) );
}
add_action( 'init', 'create_block_accomodationgrid_block_init' );
