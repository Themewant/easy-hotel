<?php
// This file is generated. Do not modify it manually.
return array(
	'accomodationSlider' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-hotel/accomodation-slider',
		'version' => '0.1.0',
		'title' => 'Accomodation Slider',
		'category' => 'easy-hotel',
		'icon' => 'grid-view',
		'description' => 'Accomodation Slider block scaffolded with Create Block tool.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false,
			'spacing' => array(
				'padding' => true,
				'margin' => true
			),
			'color' => array(
				'background' => true,
				'text' => false,
				'gradients' => true
			)
		),
		'textdomain' => 'accomodation-slider',
		'editorScript' => 'file:./index.js',
		'viewScript' => 'file:./view.js',
		'render' => 'file:./render.php',
		'attributes' => array(
			'grid_style' => array(
				'type' => 'string',
				'default' => '1'
			),
			'per_page' => array(
				'type' => 'number',
				'default' => 10
			),
			'btn_text' => array(
				'type' => 'string',
				'default' => 'Book Now'
			),
			'thumbnail_size' => array(
				'type' => 'string',
				'default' => 'eshb_thumbnail'
			),
			'room_order' => array(
				'type' => 'string',
				'default' => 'ASC'
			),
			'room_orderby' => array(
				'type' => 'string',
				'default' => 'date'
			),
			'category' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemPadding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
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
			'itemTitleTypography' => array(
				'type' => 'object',
				'default' => array(
					'fontFamily' => '',
					'fontSize' => '',
					'fontWeight' => '',
					'fontStyle' => '',
					'textTransform' => '',
					'lineHeight' => '',
					'letterSpacing' => ''
				)
			),
			'itemDescriptionTypography' => array(
				'type' => 'object',
				'default' => array(
					'fontFamily' => '',
					'fontSize' => '',
					'fontWeight' => '',
					'fontStyle' => '',
					'textTransform' => '',
					'lineHeight' => '',
					'letterSpacing' => ''
				)
			),
			'capacitiesItemTypography' => array(
				'type' => 'object',
				'default' => array(
					'fontFamily' => '',
					'fontSize' => '',
					'fontWeight' => '',
					'fontStyle' => '',
					'textTransform' => '',
					'lineHeight' => '',
					'letterSpacing' => ''
				)
			),
			'itemPricingTypography' => array(
				'type' => 'object',
				'default' => array(
					'fontFamily' => '',
					'fontSize' => '',
					'fontWeight' => '',
					'fontStyle' => '',
					'textTransform' => '',
					'lineHeight' => '',
					'letterSpacing' => ''
				)
			),
			'itemPricingPerodicityTypography' => array(
				'type' => 'object',
				'default' => array(
					'fontFamily' => '',
					'fontSize' => '',
					'fontWeight' => '',
					'fontStyle' => '',
					'textTransform' => '',
					'lineHeight' => '',
					'letterSpacing' => ''
				)
			),
			'itemButtonTypography' => array(
				'type' => 'object',
				'default' => array(
					'fontFamily' => '',
					'fontSize' => '',
					'fontWeight' => '',
					'fontStyle' => '',
					'textTransform' => '',
					'lineHeight' => '',
					'letterSpacing' => ''
				)
			),
			'itemBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemBackgroundColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemBackgroundGradient' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemBackgroundGradientHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemOverlayBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemOverlayBackgroundColorHover' => array(
				'type' => 'string',
				'default' => 'var(--eshb-primary-color)'
			),
			'itemOverlayBackgroundGradient' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemOverlayBackgroundGradientHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemOverlayBackgroundColorTwo' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemOverlayBackgroundColorTwoHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemOverlayBackgroundGradientTwo' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemOverlayBackgroundGradientTwoHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemTitleColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemTitleColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemDescriptionColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemDescriptionColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'capacitiesItemColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'capacitiesItemColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemPricingColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemPricingColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemPricingPerodicityColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemPricingPerodicityColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemButtonBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemButtonBackgroundColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemButtonBackgroundGradient' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemButtonBackgroundGradientHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemButtonColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemButtonColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
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
			'spaceBetween' => array(
				'type' => 'string',
				'default' => '20'
			),
			'effect' => array(
				'type' => 'string',
				'default' => 'slide'
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
			)
		)
	)
);
