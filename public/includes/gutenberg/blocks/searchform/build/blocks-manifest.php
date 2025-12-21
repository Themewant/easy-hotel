<?php
// This file is generated. Do not modify it manually.
return array(
	'searchform' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-hotel/searchform',
		'version' => '0.1.0',
		'title' => 'Easy Hotel Search',
		'category' => 'easy-hotel',
		'icon' => 'search',
		'description' => 'A search form for easy hotel rooms.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false,
			'align' => true,
			'layout' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true
			),
			'color' => array(
				'background' => true,
				'text' => true,
				'link' => true
			),
			'typography' => array(
				'fontSize' => true,
				'fontWeight' => true,
				'fontStyle' => true,
				'lineHeight' => true,
				'textDecoration' => true,
				'textTransform' => true
			)
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
		'style' => array(
			'file:./style.scss',
			'eshb-style'
		),
		'render' => 'file:./render.php',
		'attributes' => array(
			'customBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			)
		)
	)
);
