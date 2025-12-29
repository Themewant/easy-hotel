<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function create_block_checkInOutTimes_block_init() {
	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-checkInOutTimes-style',
		plugins_url( 'build/checkInOutTimes/style-index.css', __FILE__ ),
		array( 'eshb-style' ),
		filemtime( __DIR__ . '/build/checkInOutTimes/style-index.css' )
	);

	register_block_type( __DIR__ . '/build/checkInOutTimes', array(
		'style'         => 'eshb-checkInOutTimes-style',
		'editor_style'  => 'eshb-checkInOutTimes-style',
	) );
}
add_action( 'init', 'create_block_checkInOutTimes_block_init' );
