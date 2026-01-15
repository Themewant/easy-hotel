<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function create_block_check_in_out_times_block_init() {
	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-check-in-out-times-style',
		plugins_url( 'build/check-in-out-times/style-index.css', __FILE__ ),
		array( 'eshb-style' ),
		filemtime( __DIR__ . '/build/check-in-out-times/style-index.css' )
	);

	register_block_type( __DIR__ . '/build/check-in-out-times', array(
		'style'         => 'eshb-check-in-out-times-style',
		'editor_style'  => 'eshb-check-in-out-times-style',
	) );
}
add_action( 'init', 'create_block_check_in_out_times_block_init' );
