<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function create_block_bookingform_block_init() {
	// Register the main plugin style
	wp_register_style( 
		'eshb-style', 
		ESHB_PL_URL . 'public/assets/css/public.css', 
		array(), 
		ESHB_VERSION 
	);

	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-bookingform-style',
		plugins_url( 'build/bookingform/style-index.css', __FILE__ ),
		array( 'eshb-style' ),
		filemtime( __DIR__ . '/build/bookingform/style-index.css' )
	);

	wp_register_style(
		'eshb-bookingform-editor-style',
		plugins_url( 'build/bookingform/index.css', __FILE__ ),
		array( 'eshb-style' ),
		filemtime( __DIR__ . '/build/bookingform/index.css' )
	);

	register_block_type( __DIR__ . '/build/bookingform', array(
		'style'         => 'eshb-bookingform-style',
		'editor_style'  => 'eshb-bookingform-editor-style',
	) );
}
add_action( 'init', 'create_block_bookingform_block_init' );
