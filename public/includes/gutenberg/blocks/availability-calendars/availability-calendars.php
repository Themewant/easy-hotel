<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function create_block_availability_calendars_block_init() {

	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-availability-calendars-style',
		plugins_url( 'build/availability-calendars/style-index.css', __FILE__ ),
		array( 'eshb-style' ),
		ESHB_VERSION
	);

	register_block_type( __DIR__ . '/build/availability-calendars', array(
		'style'         => 'eshb-availability-calendars-style',
		'editor_style'  => 'eshb-availability-calendars-style',
	) );
}
add_action( 'init', 'create_block_availability_calendars_block_init' );
