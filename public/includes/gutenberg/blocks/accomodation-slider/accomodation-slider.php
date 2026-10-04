<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function eshb_create_block_accomodation_slider_block_init() {

	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-accomodation-slider-style',
		plugins_url( 'build/accomodation-slider/style-index.css', __FILE__ ),
		array( 'eshb-style', 'swiper' ),
		ESHB_VERSION
	);

	$block_type = register_block_type( __DIR__ . '/build/accomodation-slider', array(
		'style'         => 'eshb-accomodation-slider-style',
		'editor_style'  => 'eshb-accomodation-slider-style',
	) );

	// view.js calls Swiper as soon as it runs, so Swiper must print first.
	eshb_block_scripts_add_dependency( $block_type, array( 'eshb-swiper' ) );
}
add_action( 'init', 'eshb_create_block_accomodation_slider_block_init' );
