<?php

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => ".editor-styles-wrapper",
	'variableName' => 'has-boxed',
	'value' => blocksy_map_values([
		'value' => $has_boxed,
		'map' => [
			'boxed' => 'var(--true)',
			'wide' => 'var(--false)'
		]
	]),
	'unit' => ''
]);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => ".editor-styles-wrapper",
	'variableName' => 'has-wide',
	'value' => blocksy_map_values([
		'value' => $has_boxed,
		'map' => [
			'wide' => 'var(--true)',
			'boxed' => 'var(--false)'
		]
	]),
	'unit' => ''
]);

// boxed content area styles
if (blocksy_some_device($has_boxed, 'boxed')) {
	blocksy_output_background_css([
		'selector' => '.editor-styles-wrapper',
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'value' => blocksy_akg_or_customizer(
			'content_background',
			$source,
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'var(--theme-palette-color-8)'
					],
				],
			])
		),
		'responsive' => true,
		'conditional_var' => '--has-boxed'
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.editor-styles-wrapper',
		'property' => 'theme-boxed-content-border-radius',
		'value' => blocksy_akg_or_customizer(
			'content_boxed_radius',
			$source,
			blocksy_spacing_value([
				'top' => '3px',
				'left' => '3px',
				'right' => '3px',
				'bottom' => '3px',
			])
		)
	]);

	blocksy_output_border([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.editor-styles-wrapper',
		'variableName' => 'theme-boxed-content-border',
		'value' => blocksy_akg_or_customizer(
			'content_boxed_border',
			$source,
			[
				'width' => 1,
				'style' => 'none',
				'color' => [
					'color' => 'rgba(44,62,80,0.2)',
				],
			]
		),
		'default' => [
			'width' => 1,
			'style' => 'none',
			'color' => [
				'color' => 'rgba(44,62,80,0.2)',
			],
		],
		'responsive' => true,
		'skip_none' => true
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.editor-styles-wrapper',
		'property' => 'theme-boxed-content-spacing',
		'value' => blocksy_akg_or_customizer(
			'boxed_content_spacing',
			$source,
			[
				'desktop' => blocksy_spacing_value([
					'top' => '40px',
					'left' => '40px',
					'right' => '40px',
					'bottom' => '40px',
				]),
				'tablet' => blocksy_spacing_value([
					'top' => '35px',
					'left' => '35px',
					'right' => '35px',
					'bottom' => '35px',
				]),
				'mobile'=> blocksy_spacing_value([
					'top' => '20px',
					'left' => '20px',
					'right' => '20px',
					'bottom' => '20px',
				]),
			]
		)
	]);

	blocksy_output_box_shadow([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.editor-styles-wrapper',
		'variableName' => 'theme-boxed-content-box-shadow',
		'value' => blocksy_akg_or_customizer(
			'content_boxed_shadow',
			$source,
			blocksy_box_shadow_value([
				'enable' => true,
				'h_offset' => 0,
				'v_offset' => 12,
				'blur' => 18,
				'spread' => -6,
				'inset' => false,
				'color' => [
					'color' => 'rgba(34, 56, 101, 0.04)',
				],
			])
		),
		'responsive' => true
	]);
} else {
	$css->put(
		'.editor-styles-wrapper',
		'background-color: transparent'
	);
}
