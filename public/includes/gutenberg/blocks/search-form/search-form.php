<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function create_block_search_form_block_init() {
	// Register the main plugin style
	wp_register_style( 
		'eshb-style', 
		ESHB_PL_URL . 'public/assets/css/public.css', 
		array(), 
		ESHB_VERSION 
	);

	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-search-form-style',
		plugins_url( 'build/search-form/style-index.css', __FILE__ ),
		array( 'eshb-style' ),
		ESHB_VERSION
	);

	wp_register_style(
		'eshb-search-form-editor-style',
		plugins_url( 'build/search-form/index.css', __FILE__ ),
		array( 'eshb-style' ),
		ESHB_VERSION
	);

	register_block_type( __DIR__ . '/build/search-form', array(
		'style'         => 'eshb-search-form-style',
		'editor_style'  => 'eshb-search-form-editor-style',
	) );
}
add_action( 'init', 'create_block_search_form_block_init' );
