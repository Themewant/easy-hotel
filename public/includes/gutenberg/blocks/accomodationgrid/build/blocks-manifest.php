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
		'icon' => 'grid-view',
		'description' => 'Example block scaffolded with Create Block tool.',
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
		'textdomain' => 'accomodationgrid',
		'editorScript' => 'file:./index.js',
		'viewScript' => 'file:./view.js',
		'render' => 'file:./render.php',
		'attributes' => array(
			'show_pagination' => array(
				'type' => 'boolean',
				'default' => true
			),
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
				'default' => 'eshb_thumbnail'
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
			'capacitiesWrapperPadding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'capacitiesWrapperMargin' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'capacitiesItemPadding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'capacitiesItemMargin' => array(
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
			)
		)
	)
);
