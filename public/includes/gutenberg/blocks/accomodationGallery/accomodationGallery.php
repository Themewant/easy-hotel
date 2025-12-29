<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function create_block_accomodationgallery_block_init() {
	// if swier not existing
	if (!wp_style_is('swiper', 'enqueued')) {
		wp_enqueue_style( 'swiper', ESHB_PL_URL . 'public/assets/css/swiper-bundle.min.css', array(), ESHB_VERSION, 'all' );
	}
	if (!wp_script_is('eshb-swiper', 'enqueued')) {
		wp_enqueue_script( 'eshb-swiper', ESHB_PL_URL . 'public/assets/js/swiper-bundle.min.js', array(),'12.0.3',false );
	}

	// Register the main plugin style
	wp_register_style( 
		'eshb-style', 
		ESHB_PL_URL . 'public/assets/css/public.css', 
		array(), 
		ESHB_VERSION 
	);

	// Register block-specific styles manually to be sure
	wp_register_style(
		'eshb-accomodationgallery-style',
		plugins_url( 'build/accomodationGallery/style-index.css', __FILE__ ),
		array( 'eshb-style' ),
		filemtime( __DIR__ . '/build/accomodationGallery/style-index.css' )
	);

	wp_register_style(
		'eshb-accomodationgallery-editor-style',
		plugins_url( 'build/accomodationGallery/index.css', __FILE__ ),
		array( 'eshb-style' ),
		filemtime( __DIR__ . '/build/accomodationGallery/index.css' )
	);

	register_block_type( __DIR__ . '/build/accomodationGallery', array(
		'style'         => 'eshb-accomodationgallery-style',
		'editor_style'  => 'eshb-accomodationgallery-editor-style',
	) );
}
add_action( 'init', 'create_block_accomodationgallery_block_init' );
