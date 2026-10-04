<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function eshb_create_block_accomodation_gallery_block_init() {


	// Register the main plugin style
	wp_register_style( 
		'eshb-style', 
		ESHB_PL_URL . 'public/assets/css/public.css', 
		array(), 
		ESHB_VERSION 
	);

	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-accomodation-gallery-style',
		plugins_url( 'build/accomodation-gallery/style-index.css', __FILE__ ),
		array( 'eshb-style', 'swiper' ),
		ESHB_VERSION
	);

	wp_register_style(
		'eshb-accomodation-gallery-editor-style',
		plugins_url( 'build/accomodation-gallery/index.css', __FILE__ ),
		array( 'eshb-style' ),
		ESHB_VERSION
	);

	$block_type = register_block_type( __DIR__ . '/build/accomodation-gallery', array(
		'style'         => 'eshb-accomodation-gallery-style',
		'editor_style'  => 'eshb-accomodation-gallery-editor-style',
	) );

	// view.js calls Swiper (and jQuery) as soon as it runs, so they must print first.
	eshb_block_scripts_add_dependency( $block_type, array( 'jquery', 'eshb-swiper' ) );
}
add_action( 'init', 'eshb_create_block_accomodation_gallery_block_init' );
