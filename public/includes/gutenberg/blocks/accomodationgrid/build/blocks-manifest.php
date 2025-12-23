<?php
// This file is generated. Do not modify it manually.
return array(
	'accomodationgrid' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-hotel/accomodationgrid',
		'version' => '0.1.0',
		'title' => 'Accomodation Grid',
		'category' => 'easy-hotel',
		'icon' => 'smiley',
		'description' => 'Example block scaffolded with Create Block tool.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'accomodationgrid',
		'editorScript' => 'file:./index.js',
		'viewScript' => 'file:./view.js',
		'render' => 'file:./render.php',
		'attributes' => array(
			'grid_style' => array(
				'type' => 'string',
				'default' => 'default'
			),
			'room_columns' => array(
				'type' => 'number',
				'default' => 3
			),
			'btn_text' => array(
				'type' => 'string',
				'default' => 'Book Now'
			),
			'thumbnail_size' => array(
				'type' => 'string',
				'default' => 'large'
			),
			'per_page' => array(
				'type' => 'number',
				'default' => -1
			),
			'room_order' => array(
				'type' => 'string',
				'default' => 'ASC'
			),
			'room_orderby' => array(
				'type' => 'string',
				'default' => 'date'
			),
			'room_offset' => array(
				'type' => 'number',
				'default' => 0
			),
			'category' => array(
				'type' => 'string',
				'default' => ''
			),
			'customBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'containerBackgroundGradient' => array(
				'type' => 'string',
				'default' => ''
			),
			'containerBackgroundGradientHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'padding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'margin' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'borderRadius' => array(
				'type' => 'object',
				'default' => array(
					'topLeft' => '0px',
					'topRight' => '0px',
					'bottomLeft' => '0px',
					'bottomRight' => '0px'
				)
			),
			'boxShadow' => array(
				'type' => 'string',
				'default' => ''
			),
			'boxShadowX' => array(
				'type' => 'number',
				'default' => 0
			),
			'boxShadowY' => array(
				'type' => 'number',
				'default' => 0
			),
			'boxShadowBlur' => array(
				'type' => 'number',
				'default' => 0
			),
			'boxShadowSpread' => array(
				'type' => 'number',
				'default' => 0
			),
			'boxShadowColor' => array(
				'type' => 'string',
				'default' => 'rgba(0,0,0,0.1)'
			),
			'customBackgroundColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'boxShadowHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'boxShadowXHover' => array(
				'type' => 'number',
				'default' => 0
			),
			'boxShadowYHover' => array(
				'type' => 'number',
				'default' => 0
			),
			'boxShadowBlurHover' => array(
				'type' => 'number',
				'default' => 0
			),
			'boxShadowSpreadHover' => array(
				'type' => 'number',
				'default' => 0
			),
			'boxShadowColorHover' => array(
				'type' => 'string',
				'default' => 'rgba(0,0,0,0.1)'
			),
			'containerPadding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
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
			'itemTitlePadding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'itemTitleMargin' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'itemDescriptionPadding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'itemDescriptionMargin' => array(
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
			'itemPricingPadding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'itemPricingMargin' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
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
			'itemButtonPadding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'itemButtonMargin' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
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
				'default' => ''
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
			'itemGap' => array(
				'type' => 'string',
				'default' => '20px'
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
			'itemPricingColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemPricingColorHover' => array(
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
			)
		)
	)
);
