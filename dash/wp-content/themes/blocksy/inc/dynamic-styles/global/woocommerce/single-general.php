<?php

// product tabs
$tabs_type = blocksy_get_theme_mod( 'woo_tabs_type', 'type-1' );

blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod( 'woo_tabs_font',
		blocksy_typography_default_values([
			'size' => '12px',
			'variation' => 'n6',
			'text-transform' => 'uppercase',
			'line-height' => '1',
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.woocommerce-tabs .tabs, .woocommerce-tabs .ct-accordion-heading, .woocommerce-tabs[data-type*="type-4"] .entry-content > h2',
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('woo_tabs_font_color'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-text-color)' ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'active' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-tabs .tabs, .woocommerce-tabs .ct-accordion-heading, .woocommerce-tabs[data-type*="type-4"] .entry-content > h2',
			'variable' => 'theme-link-initial-color'
		],

		'hover' => [
			'selector' => '.woocommerce-tabs .tabs, .woocommerce-tabs .ct-accordion-heading',
			'variable' => 'theme-link-hover-color'
		],

		'active' => [
			'selector' => '.woocommerce-tabs .tabs, .woocommerce-tabs .ct-accordion-heading',
			'variable' => 'theme-link-active-color'
		],
	],
]);

if ($tabs_type !== 'type-4') {
	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('woo_tabs_border_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.woocommerce-tabs[data-type] .tabs, .woocommerce-tabs .ct-accordion-heading',
				'variable' => 'tab-border-color'
			],
		],
	]);
}

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('woo_actibe_tab_border'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-palette-color-1)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-tabs[data-type] .tabs',
			'variable' => 'tab-background'
		],
	],
]);

if ($tabs_type === 'type-2') {
	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('woo_actibe_tab_background'),
		'default' => [
			'default' => [ 'color' => 'rgba(242, 244, 247, 0.7)' ],
			'border' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.woocommerce-tabs[data-type*="type-2"] .tabs',
				'variable' => 'tab-background'
			],

		'border' => [
				'selector' => '.woocommerce-tabs[data-type*="type-2"] .tabs li.active',
				'variable' => 'tab-border-color'
			],
		],
	]);
}

if ($tabs_type === 'type-4') {
	$woo_separated_tabs_spacing = blocksy_get_theme_mod('woo_separated_tabs_spacing', 50);

	if ($woo_separated_tabs_spacing !== 50) {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '.woocommerce-tabs[data-type*="type-4"]',
			'variableName' => 'woo-separated-tabs-spacing',
			'value' => $woo_separated_tabs_spacing
		]);
	}
}


// related & upsells
$related_columns = blocksy_get_theme_mod('woo_product_related_cards_columns', [
	'mobile' => 1,
	'tablet' => 3,
	'desktop' => 4,
]);

$related_columns['desktop'] = 'CT_CSS_SKIP_RULE';
$related_columns['tablet'] = 'repeat(' . $related_columns['tablet'] . ', minmax(0, 1fr))';
$related_columns['mobile'] = 'repeat(' . $related_columns['mobile'] . ', minmax(0, 1fr))';

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.related [data-products], .upsells [data-products]',
	'variableName' => 'shop-columns',
	'value' => $related_columns,
	'unit' => ''
]);


blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod( 'related_upsells_heading_font',
		blocksy_typography_default_values([
			'size' => '20px',
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.related > h2, .up-sells > h2',
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('related_upsells_heading_font_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'variables' => [
		'default' => [
			'selector' => '.related > h2, .up-sells > h2',
			'variable' => 'theme-heading-color'
		],
	],
	'responsive' => true,
]);

$has_slider = blocksy_get_theme_mod( 'woocommerce_related_products_slideshow', 'default' );

if ($has_slider === 'slider') {
	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('related_upsell_slider_nav_arrow_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.related .flexy-container, .up-sells .flexy-container',
				'variable' => 'flexy-nav-arrow-color'
			],

			'hover' => [
				'selector' => '.related .flexy-container, .up-sells .flexy-container',
				'variable' => 'flexy-nav-arrow-hover-color'
			],
		],
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('related_upsell_slider_nav_background_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.related .flexy-container, .up-sells .flexy-container',
				'variable' => 'flexy-nav-background-color'
			],

			'hover' => [
				'selector' => '.related .flexy-container, .up-sells .flexy-container',
				'variable' => 'flexy-nav-background-hover-color'
			],
		],
	]);
}