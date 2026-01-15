<?php
// This file is generated. Do not modify it manually.
return array(
	'booking-form' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-hotel/booking-form',
		'version' => '0.1.0',
		'title' => 'Easy Hotel Booking Form',
		'category' => 'easy-hotel',
		'icon' => 'calendar-alt',
		'description' => 'A booking form for easy hotel rooms.',
		'example' => array(
			
		),
		'textdomain' => 'easy-hotel',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'styles' => array(
			array(
				'name' => 'one',
				'label' => 'Style One'
			),
			array(
				'name' => 'two',
				'label' => 'Style Two',
				'isDefault' => true
			)
		),
		'attributes' => array(
			'accomodationId' => array(
				'type' => 'string',
				'default' => ''
			),
			'customBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'padding' => array(
				'type' => 'object',
				'default' => array(
					'top' => '35px',
					'right' => '40px',
					'bottom' => '35px',
					'left' => '40px'
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
				'default' => ''
			),
			'fieldLabelColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'fieldTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'fieldTextColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'fieldBorderRadius' => array(
				'type' => 'object',
				'default' => array(
					'topLeft' => '0px',
					'topRight' => '0px',
					'bottomLeft' => '0px',
					'bottomRight' => '0px'
				)
			),
			'serviceCheckboxBorderRadius' => array(
				'type' => 'object',
				'default' => array(
					'topLeft' => '0px',
					'topRight' => '0px',
					'bottomLeft' => '0px',
					'bottomRight' => '0px'
				)
			),
			'serviceQtyBorderRadius' => array(
				'type' => 'object',
				'default' => array(
					'topLeft' => '0px',
					'topRight' => '0px',
					'bottomLeft' => '0px',
					'bottomRight' => '0px'
				)
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
			'submitBtnBorderRadius' => array(
				'type' => 'object',
				'default' => array(
					'topLeft' => '0px',
					'topRight' => '0px',
					'bottomLeft' => '0px',
					'bottomRight' => '0px'
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
			),
			'extraServicesColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'extraServicesColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'extraServicesTypography' => array(
				'type' => 'object',
				'default' => array(
					'fontSize' => '14px',
					'fontWeight' => '400',
					'lineHeight' => '1.5',
					'textTransform' => 'none',
					'letterSpacing' => '0px'
				)
			),
			'extraServicesMargin' => array(
				'type' => 'object',
				'default' => array(
					'top' => '0px',
					'right' => '0px',
					'bottom' => '0px',
					'left' => '0px'
				)
			),
			'groupTitleColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'groupTitleColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'groupTitleTypography' => array(
				'type' => 'object',
				'default' => array(
					'fontSize' => '26px',
					'fontWeight' => '600',
					'lineHeight' => '1.5',
					'textTransform' => 'none',
					'letterSpacing' => '0px'
				)
			),
			'formTitleColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'formTitleColorHover' => array(
				'type' => 'string',
				'default' => ''
			)
		)
	)
);
