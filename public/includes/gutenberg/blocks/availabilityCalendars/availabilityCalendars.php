<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function create_block_availabilityCalendars_block_init() {

	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-availabilityCalendars-style',
		plugins_url( 'build/availabilityCalendars/style-index.css', __FILE__ ),
		array( 'eshb-style' ),
		filemtime( __DIR__ . '/build/availabilityCalendars/style-index.css' )
	);

	register_block_type( __DIR__ . '/build/availabilityCalendars', array(
		'style'         => 'eshb-availabilityCalendars-style',
		'editor_style'  => 'eshb-availabilityCalendars-style',
	) );
}
add_action( 'init', 'create_block_availabilityCalendars_block_init' );
