<?php
// This file is generated. Do not modify it manually.
return array(
	'availabilityCalendars' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-hotel/availabilitycalendars',
		'version' => '0.1.0',
		'title' => 'Easy Hotel Availability Calendars',
		'category' => 'easy-hotel',
		'icon' => 'calendar-alt',
		'description' => 'Availability Calendars for easy hotel room.',
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
			'root' => '.eshb-availability-calendars-block-wrapper',
			'color' => array(
				'text' => '.eshb-availability-calendars-block-wrapper p',
				'background' => '.eshb-availability-calendars-block-wrapper'
			),
			'typography' => array(
				'root' => '.eshb-availability-calendars-block-wrapper p',
				'text' => '.eshb-availability-calendars-block-wrapper p'
			),
			'spacing' => array(
				'root' => '.eshb-availability-calendars-block-wrapper',
				'padding' => '.eshb-availability-calendars-block-wrapper',
				'margin' => '.eshb-availability-calendars-block-wrapper'
			)
		),
		'textdomain' => 'easy-hotel',
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	)
);
