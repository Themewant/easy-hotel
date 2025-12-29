<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function create_block_accomodationinfo_block_init() {
	// Register the main plugin style
	wp_register_style( 
		'eshb-style', 
		ESHB_PL_URL . 'public/assets/css/public.css', 
		array(), 
		ESHB_VERSION 
	);

	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-accomodationinfo-style',
		plugins_url( 'build/accomodationinfo/style-index.css', __FILE__ ),
		array( 'eshb-style' ),
		filemtime( __DIR__ . '/build/accomodationinfo/style-index.css' )
	);

	register_block_type( __DIR__ . '/build/accomodationinfo', array(
		'style'         => 'eshb-accomodationinfo-style',
		'editor_style'  => 'eshb-accomodationinfo-style',
	) );
}
add_action( 'init', 'create_block_accomodationinfo_block_init' );
