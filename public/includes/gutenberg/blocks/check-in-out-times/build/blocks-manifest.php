<?php
// This file is generated. Do not modify it manually.
return array(
	'check-in-out-times' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-hotel/check-in-out-times',
		'version' => '0.1.0',
		'title' => 'Easy Hotel Check In Out Times',
		'category' => 'easy-hotel',
		'icon' => 'clock',
		'description' => 'Check In Out Times for easy hotel room.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false,
			'align' => true,
			'layout' => true,
			'styles' => true,
			'color' => array(
				'background' => true,
				'text' => true,
				'link' => false
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true
			)
		),
		'selectors' => array(
			'root' => '.eshb-check-in-out-times-block-wrapper',
			'color' => array(
				'text' => '.eshb-check-in-out-times-block-wrapper p',
				'background' => '.eshb-check-in-out-times-block-wrapper'
			),
			'typography' => array(
				'root' => '.eshb-check-in-out-times-block-wrapper p',
				'text' => '.eshb-check-in-out-times-block-wrapper p'
			),
			'spacing' => array(
				'root' => '.eshb-check-in-out-times-block-wrapper',
				'padding' => '.eshb-check-in-out-times-block-wrapper',
				'margin' => '.eshb-check-in-out-times-block-wrapper'
			)
		),
		'textdomain' => 'easy-hotel',
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	)
);
