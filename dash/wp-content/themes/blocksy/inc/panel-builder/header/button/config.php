<?php

$config = [
	'name' => __('Button', 'blocksy'),
	'clone' => true,

	'selective_refresh' => [
		'header_button_open',
		'header_button_select_popup',
		'has_header_button_secondary_text',
		'icon',
		'icon_position',
		'user_visibility'
	],

	'typography_keys' => [
		'headerButtonFont', 
		'headerButtonSecondaryLabelFont'
	],

	'translation_keys' => [
		[
			'key' => 'header_button_text',
			'multiline' => false
		],

		[
			'key' => 'header_button_secondary_text',
			'multiline' => false
		],

		[
			'key' => 'button_aria_label',
			'multiline' => false
		],

		[
			'key' => 'header_button_link',
			'multiline' => false
		]
	]
];
