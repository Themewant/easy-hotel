<?php
// This file is generated. Do not modify it manually.
return array(
	'accomodation-info' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-hotel/accomodation-info',
		'version' => '0.1.0',
		'title' => 'Easy Hotel Accomodation Info',
		'category' => 'easy-hotel',
		'icon' => 'info-outline',
		'description' => 'Basic info for easy hotel room.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false,
			'align' => true,
			'layout' => true
		),
		'selectors' => array(
			'root' => '.eshb-accomodation-info-wrapper',
			'color' => array(
				'text' => '.eshb-accomodation-info-wrapper .field-label',
				'background' => '.eshb-accomodation-info-wrapper',
				'link' => '.eshb-accomodation-info-wrapper a'
			),
			'typography' => array(
				'root' => '.eshb-accomodation-info-wrapper .field-label',
				'text' => '.eshb-accomodation-info-wrapper .field-label'
			),
			'spacing' => array(
				'root' => '.eshb-accomodation-info-wrapper',
				'padding' => '.eshb-accomodation-info-wrapper',
				'margin' => '.eshb-accomodation-info-wrapper'
			)
		),
		'textdomain' => 'easy-hotel',
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php',
		'attributes' => array(
			'spaceBetween' => array(
				'type' => 'string',
				'default' => '20'
			),
			'textColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'textColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'textSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'iconSize' => array(
				'type' => 'string',
				'default' => '25'
			),
			'iconColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'iconColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'iconSpace' => array(
				'type' => 'string',
				'default' => '5'
			)
		)
	)
);
