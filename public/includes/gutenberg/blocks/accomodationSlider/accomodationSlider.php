<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function create_block_accomodationSlider_block_init() {
	// Register the main plugin style
	wp_register_style( 
		'eshb-style', 
		ESHB_PL_URL . 'public/assets/css/public.css', 
		array(), 
		ESHB_VERSION 
	);

	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-accomodationSlider-style',
		plugins_url( 'build/accomodationSlider/style-index.css', __FILE__ ),
		array( 'eshb-style' ),
		filemtime( __DIR__ . '/build/accomodationSlider/style-index.css' )
	);

	register_block_type( __DIR__ . '/build/accomodationSlider', array(
		'style'         => 'eshb-accomodationSlider-style',
		'editor_style'  => 'eshb-accomodationSlider-style',
	) );
}
add_action( 'init', 'create_block_accomodationSlider_block_init' );
