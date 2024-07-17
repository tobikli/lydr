<?php

if (! function_exists('blocksy_assemble_selector')) {
	return;
}

// Icon size
$iconSize = blocksy_akg( 'cta_button_icon_size', $atts, 15 );
$has_secondary_text = blocksy_akg( 'has_header_button_secondary_text', $atts, 'no' );

if ($iconSize !== 15) {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector($root_selector),
		'variableName' => 'theme-icon-size',
		'value' => $iconSize,
		'responsive' => true
	]);
}

blocksy_output_font_css([
	'font_value' => blocksy_akg('headerButtonFont', $atts,
		blocksy_typography_default_values([])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'prefix' => 'button',
	'selector' => blocksy_assemble_selector(
		blocksy_mutate_selector([
			'selector' => $root_selector,
			'operation' => 'suffix',
			'to_add' => '[class*="ct-button"]'
		])
	),
]);

// Font color
blocksy_output_colors([
	'value' => blocksy_akg('headerButtonFontColor', $atts),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],

		'default_2' => [ 'color' => 'var(--theme-button-background-initial-color)' ],
		'hover_2' => [ 'color' => '#ffffff' ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'variables' => [
		'default' => [
			'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => '.ct-button'
			])),
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => '.ct-button'
			])),
			'variable' => 'theme-button-text-hover-color'
		],


		'default_2' => [
			'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => '.ct-button-ghost'
			])),
			'variable' => 'theme-button-text-initial-color'
		],

		'hover_2' => [
			'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => '.ct-button-ghost'
			])),
			'variable' => 'theme-button-text-hover-color'
		],
	],
	'responsive' => true
]);


if ($has_secondary_text === 'yes') {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => blocksy_assemble_selector(
			blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => '.ct-button-text'
			])
		),
		'variableName' => 'horizontal-alignment',
		'value' => blocksy_akg( 'header_button_text_horizontal_alignment', $atts, 'CT_CSS_SKIP_RULE' ),
		'unit' => '',
	]);

	blocksy_output_font_css([
		'font_value' => blocksy_akg('headerButtonSecondaryLabelFont', $atts,
			blocksy_typography_default_values([
				'size' => '12px',
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'prefix' => 'button',
		'selector' => blocksy_assemble_selector(
			blocksy_mutate_selector([
				'selector' => $root_selector,
				'operation' => 'suffix',
				'to_add' => '.ct-button-secondary-text'
			])
		),
	]);

	blocksy_output_colors([
		'value' => blocksy_akg('headerButtonSecondaryFontColor', $atts),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],

			'default_2' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover_2' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'variables' => [
			'default' => [
				'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
					'selector' => $root_selector,
					'operation' => 'suffix',
					'to_add' => '.ct-button .ct-button-secondary-text'
				])),
				'variable' => 'theme-button-text-initial-color'
			],

			'hover' => [
				'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
					'selector' => $root_selector,
					'operation' => 'suffix',
					'to_add' => '.ct-button .ct-button-secondary-text'
				])),
				'variable' => 'theme-button-text-hover-color'
			],


			'default_2' => [
				'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
					'selector' => $root_selector,
					'operation' => 'suffix',
					'to_add' => '.ct-button-ghost .ct-button-secondary-text'
				])),
				'variable' => 'theme-button-text-initial-color'
			],

			'hover_2' => [
				'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
					'selector' => $root_selector,
					'operation' => 'suffix',
					'to_add' => '.ct-button-ghost .ct-button-secondary-text'
				])),
				'variable' => 'theme-button-text-hover-color'
			],
		],
		'responsive' => true
	]);
}

// Background color
blocksy_output_colors([
	'value' => blocksy_akg('headerButtonForeground', $atts),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'variables' => [
		'default' => [
			'selector' => blocksy_assemble_selector($root_selector),
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => blocksy_assemble_selector($root_selector),
			'variable' => 'theme-button-background-hover-color'
		],
	],
	'responsive' => true
]);

if (isset($has_transparent_header) && $has_transparent_header) {
	blocksy_output_colors([
		'value' => blocksy_akg('transparentHeaderButtonFontColor', $atts),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],

			'default_2' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover_2' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,

		'variables' => [
			'default' => [
				'selector' => blocksy_assemble_selector(
					blocksy_mutate_selector([
						'selector' => blocksy_mutate_selector([
							'selector' => $root_selector,
							'operation' => 'suffix',
							'to_add' => '.ct-button'
						]),
						'operation' => 'between',
						'to_add' => '[data-transparent-row="yes"]'
					])
				),
				'variable' => 'theme-button-text-initial-color'
			],

			'hover' => [
				'selector' => blocksy_assemble_selector(
					blocksy_mutate_selector([
						'selector' => blocksy_mutate_selector([
							'selector' => $root_selector,
							'operation' => 'suffix',
							'to_add' => '.ct-button'
						]),

						'operation' => 'between',
						'to_add' => '[data-transparent-row="yes"]'
					])
				),
				'variable' => 'theme-button-text-hover-color'
			],

			'default_2' => [
				'selector' => blocksy_assemble_selector(
					blocksy_mutate_selector([
						'selector' => blocksy_mutate_selector([
							'selector' => $root_selector,
							'operation' => 'suffix',
							'to_add' => '.ct-button-ghost'
						]),

						'operation' => 'between',
						'to_add' => '[data-transparent-row="yes"]'
					])
				),
				'variable' => 'theme-button-text-initial-color'
			],

			'hover_2' => [
				'selector' => blocksy_assemble_selector(
					blocksy_mutate_selector([
						'selector' => blocksy_mutate_selector([
							'selector' => $root_selector,
							'operation' => 'suffix',
							'to_add' => '.ct-button-ghost'
						]),

						'operation' => 'between',
						'to_add' => '[data-transparent-row="yes"]'
					])
				),
				'variable' => 'theme-button-text-hover-color'
			],
		],
		'responsive' => true
	]);

	if ($has_secondary_text === 'yes') {
		blocksy_output_colors([
			'value' => blocksy_akg('transparentHeaderButtonSecondaryFontColor', $atts),
			'default' => [
				'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],

				'default_2' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				'hover_2' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			],
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,

			'variables' => [
				'default' => [
					'selector' => blocksy_assemble_selector(
						blocksy_mutate_selector([
							'selector' => blocksy_mutate_selector([
								'selector' => $root_selector,
								'operation' => 'suffix',
								'to_add' => '.ct-button .ct-button-secondary-text'
							]),
							'operation' => 'between',
							'to_add' => '[data-transparent-row="yes"]'
						])
					),
					'variable' => 'theme-button-text-initial-color'
				],

				'hover' => [
					'selector' => blocksy_assemble_selector(
						blocksy_mutate_selector([
							'selector' => blocksy_mutate_selector([
								'selector' => $root_selector,
								'operation' => 'suffix',
								'to_add' => '.ct-button .ct-button-secondary-text'
							]),

							'operation' => 'between',
							'to_add' => '[data-transparent-row="yes"]'
						])
					),
					'variable' => 'theme-button-text-hover-color'
				],

				'default_2' => [
					'selector' => blocksy_assemble_selector(
						blocksy_mutate_selector([
							'selector' => blocksy_mutate_selector([
								'selector' => $root_selector,
								'operation' => 'suffix',
								'to_add' => '.ct-button-ghost .ct-button-secondary-text'
							]),

							'operation' => 'between',
							'to_add' => '[data-transparent-row="yes"]'
						])
					),
					'variable' => 'theme-button-text-initial-color'
				],

				'hover_2' => [
					'selector' => blocksy_assemble_selector(
						blocksy_mutate_selector([
							'selector' => blocksy_mutate_selector([
								'selector' => $root_selector,
								'operation' => 'suffix',
								'to_add' => '.ct-button-ghost .ct-button-secondary-text'
							]),

							'operation' => 'between',
							'to_add' => '[data-transparent-row="yes"]'
						])
					),
					'variable' => 'theme-button-text-hover-color'
				],
			],
			'responsive' => true
		]);
	}

	// Background color
	blocksy_output_colors([
		'value' => blocksy_akg('transparentHeaderButtonForeground', $atts),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,

		'variables' => [
			'default' => [
				'selector' => blocksy_assemble_selector(
					blocksy_mutate_selector([
						'selector' => $root_selector,
						'operation' => 'between',
						'to_add' => '[data-transparent-row="yes"]'
					])
				),
				'variable' => 'theme-button-background-initial-color'
			],

			'hover' => [
				'selector' => blocksy_assemble_selector(
					blocksy_mutate_selector([
						'selector' => $root_selector,
						'operation' => 'between',
						'to_add' => '[data-transparent-row="yes"]'
					])
				),
				'variable' => 'theme-button-background-hover-color'
			],
		],
		'responsive' => true
	]);
}


// sticky state
if (isset($has_sticky_header) && $has_sticky_header) {
	blocksy_output_colors([
		'value' => blocksy_akg('stickyHeaderButtonFontColor', $atts),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],

			'default_2' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover_2' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,

		'variables' => [
			'default' => [
				'selector' => blocksy_assemble_selector(
					blocksy_mutate_selector([
						'selector' => blocksy_mutate_selector([
							'selector' => $root_selector,
							'operation' => 'suffix',
							'to_add' => '.ct-button'
						]),

						'operation' => 'between',
						'to_add' => '[data-sticky*="yes"]'
					])
				),
				'variable' => 'theme-button-text-initial-color'
			],

			'hover' => [
				'selector' => blocksy_assemble_selector(
					blocksy_mutate_selector([
						'selector' => blocksy_mutate_selector([
							'selector' => $root_selector,
							'operation' => 'suffix',
							'to_add' => '.ct-button'
						]),

						'operation' => 'between',
						'to_add' => '[data-sticky*="yes"]'
					])
				),
				'variable' => 'theme-button-text-hover-color'
			],

			'default_2' => [
				'selector' => blocksy_assemble_selector(
					blocksy_mutate_selector([
						'selector' => blocksy_mutate_selector([
							'selector' => $root_selector,
							'operation' => 'suffix',
							'to_add' => '.ct-button-ghost'
						]),

						'operation' => 'between',
						'to_add' => '[data-sticky*="yes"]'
					])
				),
				'variable' => 'theme-button-text-initial-color'
			],

			'hover_2' => [
				'selector' => blocksy_assemble_selector(
					blocksy_mutate_selector([
						'selector' => blocksy_mutate_selector([
							'selector' => $root_selector,
							'operation' => 'suffix',
							'to_add' => '.ct-button-ghost'
						]),

						'operation' => 'between',
						'to_add' => '[data-sticky*="yes"]'
					])
				),
				'variable' => 'theme-button-text-hover-color'
			],
		],
		'responsive' => true
	]);


	if ($has_secondary_text === 'yes') {
		blocksy_output_colors([
			'value' => blocksy_akg('stickyHeaderButtonSecondaryFontColor', $atts),
			'default' => [
				'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],

				'default_2' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				'hover_2' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			],
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,

			'variables' => [
				'default' => [
					'selector' => blocksy_assemble_selector(
						blocksy_mutate_selector([
							'selector' => blocksy_mutate_selector([
								'selector' => $root_selector,
								'operation' => 'suffix',
								'to_add' => '.ct-button .ct-button-secondary-text'
							]),

							'operation' => 'between',
							'to_add' => '[data-sticky*="yes"]'
						])
					),
					'variable' => 'theme-button-text-initial-color'
				],

				'hover' => [
					'selector' => blocksy_assemble_selector(
						blocksy_mutate_selector([
							'selector' => blocksy_mutate_selector([
								'selector' => $root_selector,
								'operation' => 'suffix',
								'to_add' => '.ct-button .ct-button-secondary-text'
							]),

							'operation' => 'between',
							'to_add' => '[data-sticky*="yes"]'
						])
					),
					'variable' => 'theme-button-text-hover-color'
				],

				'default_2' => [
					'selector' => blocksy_assemble_selector(
						blocksy_mutate_selector([
							'selector' => blocksy_mutate_selector([
								'selector' => $root_selector,
								'operation' => 'suffix',
								'to_add' => '.ct-button-ghost .ct-button-secondary-text'
							]),

							'operation' => 'between',
							'to_add' => '[data-sticky*="yes"]'
						])
					),
					'variable' => 'theme-button-text-initial-color'
				],

				'hover_2' => [
					'selector' => blocksy_assemble_selector(
						blocksy_mutate_selector([
							'selector' => blocksy_mutate_selector([
								'selector' => $root_selector,
								'operation' => 'suffix',
								'to_add' => '.ct-button-ghost .ct-button-secondary-text'
							]),

							'operation' => 'between',
							'to_add' => '[data-sticky*="yes"]'
						])
					),
					'variable' => 'theme-button-text-hover-color'
				],
			],
			'responsive' => true
		]);
	}


	blocksy_output_colors([
		'value' => blocksy_akg('stickyHeaderButtonForeground', $atts),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,

		'variables' => [
			'default' => [
				'selector' => blocksy_assemble_selector(
					blocksy_mutate_selector([
						'selector' => $root_selector,
						'operation' => 'between',
						'to_add' => '[data-sticky*="yes"]'
					])
				),
				'variable' => 'theme-button-background-initial-color'
			],

			'hover' => [
				'selector' => blocksy_assemble_selector(
					blocksy_mutate_selector([
						'selector' => $root_selector,
						'operation' => 'between',
						'to_add' => '[data-sticky*="yes"]'
					])
				),
				'variable' => 'theme-button-background-hover-color'
			],
		],
		'responsive' => true
	]);
}

// Border radius
blocksy_output_spacing([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_assemble_selector($root_selector),
	'property' => 'theme-button-border-radius',
	'value' => blocksy_default_akg(
		'headerCtaRadius',
		$atts,
		blocksy_spacing_value()
	)
]);

// Padding
blocksy_output_spacing([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_assemble_selector(
		blocksy_mutate_selector([
			'selector' => $root_selector,
			'operation' => 'suffix',
			'to_add' => 'a'
		])
	),
	'property' => 'theme-button-padding',
	'value' => blocksy_default_akg(
		'headerCtaPadding',
		$atts,
		blocksy_spacing_value()
	)
]);

// Margin
blocksy_output_spacing([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_assemble_selector($root_selector),
    'important' => true,
	'value' => blocksy_default_akg(
		'headerCtaMargin',
		$atts,
		blocksy_spacing_value()
	)
]);


// footer button
$horizontal_alignment =
blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
		'selector' => $root_selector,
		'operation' => 'replace-last',
		'to_add' => $column_selector
	])),
	'variableName' => 'horizontal-alignment',
	'value' => blocksy_akg( 'footer_button_horizontal_alignment', $atts, 'CT_CSS_SKIP_RULE' ),
	'unit' => '',
]);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => blocksy_assemble_selector(blocksy_mutate_selector([
		'selector' => $root_selector,
		'operation' => 'replace-last',
		'to_add' => $column_selector
	])),
	'variableName' => 'vertical-alignment',
	'value' => blocksy_akg( 'footer_button_vertical_alignment', $atts, 'CT_CSS_SKIP_RULE' ),
	'unit' => '',
]);
