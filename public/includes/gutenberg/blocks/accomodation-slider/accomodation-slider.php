<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function create_block_accomodation_slider_block_init() {

	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-accomodation-slider-style',
		plugins_url( 'build/accomodation-slider/style-index.css', __FILE__ ),
		array( 'eshb-style' ),
		ESHB_VERSION
	);

	register_block_type( __DIR__ . '/build/accomodation-slider', array(
		'style'         => 'eshb-accomodation-slider-style',
		'editor_style'  => 'eshb-accomodation-slider-style',
	) );
}
add_action( 'init', 'create_block_accomodation_slider_block_init' );
