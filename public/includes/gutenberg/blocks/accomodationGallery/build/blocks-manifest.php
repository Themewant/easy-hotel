<?php
// This file is generated. Do not modify it manually.
return array(
	'accomodationGallery' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-hotel/accomodationgallery',
		'version' => '0.1.0',
		'title' => 'Easy Hotel Accomodation Gallery',
		'category' => 'easy-hotel',
		'icon' => 'calendar-alt',
		'description' => 'A gallery for easy hotel rooms.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false,
			'align' => true,
			'layout' => true
		),
		'selectors' => array(
			'root' => '.eshb-accomodation-gallery-wrapper',
			'color' => array(
				'text' => '.eshb-accomodation-gallery-wrapper .field-label',
				'background' => '.eshb-accomodation-gallery-wrapper',
				'link' => '.eshb-accomodation-gallery-wrapper a'
			),
			'typography' => array(
				'root' => '.eshb-accomodation-gallery-wrapper .field-label',
				'text' => '.eshb-accomodation-gallery-wrapper .field-label'
			),
			'spacing' => array(
				'root' => '.eshb-accomodation-gallery-wrapper',
				'padding' => '.eshb-accomodation-gallery-wrapper',
				'margin' => '.eshb-accomodation-gallery-wrapper'
			)
		),
		'textdomain' => 'easy-hotel',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'attributes' => array(
			'slidesPerView' => array(
				'type' => 'number',
				'default' => 3
			),
			'slidesPerViewTablet' => array(
				'type' => 'number',
				'default' => 2
			),
			'slidesPerViewMobile' => array(
				'type' => 'number',
				'default' => 1
			),
			'slidesPerViewMobileSmall' => array(
				'type' => 'number',
				'default' => 1
			),
			'thumbnail_size' => array(
				'type' => 'string',
				'default' => 'eshb_thumbnail'
			),
			'spaceBetween' => array(
				'type' => 'string',
				'default' => '20'
			),
			'effect' => array(
				'type' => 'string',
				'default' => 'slide'
			),
			'centeredSlides' => array(
				'type' => 'boolean',
				'default' => false
			),
			'speed' => array(
				'type' => 'number',
				'default' => 300
			),
			'loop' => array(
				'type' => 'boolean',
				'default' => false
			),
			'autoplay' => array(
				'type' => 'boolean',
				'default' => false
			),
			'autoplaySpeed' => array(
				'type' => 'number',
				'default' => 3000
			),
			'pauseOnHover' => array(
				'type' => 'boolean',
				'default' => false
			),
			'pauseOnInter' => array(
				'type' => 'boolean',
				'default' => false
			),
			'itemBorderRadius' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'navigationButtonPadding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'nextBtnBorderRadius' => array(
				'type' => 'object',
				'default' => array(
					'top' => '20px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '20px'
				)
			),
			'prevBtnBorderRadius' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '20px',
					'bottom' => '20px',
					'left' => '0px'
				)
			),
			'navBtnBgColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'navBtnBgColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'navBtnColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'navBtnColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'dotsBgColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'dotsBgColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'dotsColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'dotsColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'dotsPadding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'dotsBorderRadius' => array(
				'type' => 'object',
				'default' => array(
					'top' => '100%',
					'right' => '100%',
					'bottom' => '100%',
					'left' => '100%'
				)
			),
			'dotsSize' => array(
				'type' => 'string',
				'default' => '8'
			)
		)
	)
);
