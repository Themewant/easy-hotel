<?php
// This file is generated. Do not modify it manually.
return array(
	'search-form' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-hotel/search-form',
		'version' => '0.1.0',
		'title' => 'Easy Hotel Search Form',
		'category' => 'easy-hotel',
		'icon' => 'search',
		'description' => 'A search form for easy hotel rooms.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false,
			'align' => true,
			'layout' => true
		),
		'selectors' => array(
			'root' => '.eshb-search',
			'color' => array(
				'text' => '.eshb-search .field-label',
				'background' => '.eshb-search',
				'link' => '.eshb-search a'
			),
			'typography' => array(
				'root' => '.eshb-search .field-label',
				'text' => '.eshb-search .field-label'
			),
			'spacing' => array(
				'root' => '.eshb-search',
				'padding' => '.eshb-search',
				'margin' => '.eshb-search'
			)
		),
		'textdomain' => 'easy-hotel',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'attributes' => array(
			'customBackgroundColor' => array(
				'type' => 'string',
				'default' => 'var(--eshb-dark-color)'
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
			'fieldGroupPadding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '30px',
					'right' => '0px',
					'bottom' => '30px',
					'left' => '0px'
				)
			),
			'fieldGroupMargin' => array(
				'type' => 'object',
				'default' => array(
					'top' => '30px',
					'right' => '0px',
					'bottom' => '30px',
					'left' => '0px'
				)
			),
			'fieldLabelColor' => array(
				'type' => 'string',
				'default' => 'var(--eshb-primary-color)'
			),
			'fieldLabelColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'fieldTextColor' => array(
				'type' => 'string',
				'default' => 'var(--eshb-white-color)'
			),
			'fieldTextColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'fieldLabelTypography' => array(
				'type' => 'object',
				'default' => array(
					'fontSize' => '14px',
					'fontWeight' => '400',
					'lineHeight' => '1.5',
					'textTransform' => 'none',
					'letterSpacing' => '0px'
				)
			),
			'fieldTextTypography' => array(
				'type' => 'object',
				'default' => array(
					'fontSize' => '20px',
					'fontWeight' => '400',
					'lineHeight' => '1.5',
					'textTransform' => 'none',
					'letterSpacing' => '0px'
				)
			),
			'plusMinusBtnBackgroundColor' => array(
				'type' => 'string',
				'default' => 'var(--eshb-primary-color)'
			),
			'plusMinusBtnBackgroundColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'plusMinusBtnTextColor' => array(
				'type' => 'string',
				'default' => 'var(--eshb-white-color)'
			),
			'plusMinusBtnTextColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'plusMinusBtnTypography' => array(
				'type' => 'object',
				'default' => array(
					'fontSize' => '14px',
					'fontWeight' => '400',
					'lineHeight' => '2.5',
					'textTransform' => 'none',
					'letterSpacing' => '0px'
				)
			),
			'plusMinusBtnPadding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'submitBtnBackgroundColor' => array(
				'type' => 'string',
				'default' => 'var(--eshb-primary-color)'
			),
			'submitBtnBackgroundColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'submitBtnTextColor' => array(
				'type' => 'string',
				'default' => 'var(--eshb-white-color)'
			),
			'submitBtnTextColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'submitBtnPadding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '8px',
					'right' => '20px',
					'bottom' => '8px',
					'left' => '20px'
				)
			),
			'submitBtnMargin' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'submitBtnTypography' => array(
				'type' => 'object',
				'default' => array(
					'fontSize' => '14px',
					'fontWeight' => '400',
					'lineHeight' => '1.7',
					'textTransform' => 'none',
					'letterSpacing' => '0px'
				)
			)
		)
	)
);
