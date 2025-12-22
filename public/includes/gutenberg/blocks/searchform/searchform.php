<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function create_block_searchform_block_init() {
	// Register the main plugin style
	wp_register_style( 
		'eshb-style', 
		ESHB_PL_URL . 'public/assets/css/public.css', 
		array(), 
		ESHB_VERSION 
	);

	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-searchform-style',
		plugins_url( 'build/searchform/style-index.css', __FILE__ ),
		array( 'eshb-style' ),
		filemtime( __DIR__ . '/build/searchform/style-index.css' )
	);

	wp_register_style(
		'eshb-searchform-editor-style',
		plugins_url( 'build/searchform/index.css', __FILE__ ),
		array( 'eshb-style' ),
		filemtime( __DIR__ . '/build/searchform/index.css' )
	);

	register_block_type( __DIR__ . '/build/searchform', array(
		'style'         => 'eshb-searchform-style',
		'editor_style'  => 'eshb-searchform-editor-style',
	) );
}
add_action( 'init', 'create_block_searchform_block_init' );
