<?php

$post_type = get_current_screen()->post_type;

$post_id = null;

if (isset($_GET['post']) && $_GET['post']) {
	$post_id = $_GET['post'];
}

$prefix = blocksy_manager()->screen->get_admin_prefix($post_type);

$post_atts = blocksy_get_post_options($post_id);

$source = [
	'strategy' => $post_atts
];

$background_source = blocksy_default_akg(
	'background',
	$post_atts,
	blocksy_background_default_value([
		'backgroundColor' => [
			'default' => [
				'color' => Blocksy_Css_Injector::get_skip_rule_keyword()
			],
		],
	])
);

if ($post_type === 'ct_content_block') {
	$template_type = get_post_meta($post_id, 'template_type', true);
	$template_subtype = blocksy_akg('template_subtype', $post_atts, 'card');

	if ($template_type === 'popup') {
		$background_source = blocksy_default_akg(
			'popup_background',
			$post_atts,
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => '#ffffff'
					],
				],
			])
		);
	}

	if ($template_type !== 'popup') {
		$default_content_block_structure = 'yes';

		if ($template_type === 'hook') {
			$default_content_block_structure = 'no';
		}

		$has_content_block_structure = blocksy_akg(
			'has_content_block_structure',
			$post_atts,
			$default_content_block_structure
		);

		if (
			$has_content_block_structure !== 'yes'
			||
			$template_type === 'archive' && $template_subtype === 'card'
		) {
			$background_source = blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword()
					],
				],
			]);
		}
	}
}

if (
	isset($background_source['background_type'])
	&&
	$background_source['background_type'] === 'color'
	&&
	isset($background_source['backgroundColor']['default']['color'])
	&&
	$background_source['backgroundColor']['default']['color'] === Blocksy_Css_Injector::get_skip_rule_keyword()
) {
	$background_source = blocksy_get_theme_mod(
		$prefix . '_background',
		blocksy_background_default_value([
			'backgroundColor' => [
				'default' => [
					'color' => Blocksy_Css_Injector::get_skip_rule_keyword()
				],
			],
		])
	);

	if (
		isset($background_source['background_type'])
		&&
		$background_source['background_type'] === 'color'
		&&
		isset($background_source['backgroundColor']['default']['color'])
		&&
		$background_source['backgroundColor']['default']['color'] === Blocksy_Css_Injector::get_skip_rule_keyword()
	) {
		$background_source = blocksy_get_theme_mod(
			'site_background',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'var(--theme-palette-color-7)'
					],
				],
			])
		);
	}
}

$background_source = blocksy_expand_responsive_value($background_source);

global $wp_version;

$is_65_wordpress = version_compare($wp_version, '6.5', '>=');

$background_selectors = [
	'desktop' => '.ct-desktop-view iframe[name="editor-canvas"], .ct-desktop-view .edit-post-visual-editor',
	'tablet' => '.ct-tablet-view iframe[name="editor-canvas"]',
	'mobile' => '.ct-mobile-view iframe[name="editor-canvas"]'
];

// We don't support boxed styles for 6.4 and less. For that version of WP
// we will only support the main page background.
if (! $is_65_wordpress) {
	$background_selectors = [
		'desktop' => '.ct-desktop-view iframe[name="editor-canvas"], .ct-desktop-view .edit-post-visual-editor__content-area > div',
		'tablet' => '.ct-tablet-view iframe[name="editor-canvas"]',
		'mobile' => '.ct-mobile-view iframe[name="editor-canvas"]'
	];
}

blocksy_output_background_css([
	'selector' => $background_selectors['desktop'],
	'css' => $css,
	'value' => $background_source['desktop'],
	'responsive' => false,
	'important' => true
]);

blocksy_output_background_css([
	'selector' => $background_selectors['tablet'],
	'css' => $css,
	'value' => $background_source['tablet'],
	'responsive' => false,
	'important' => true
]);

blocksy_output_background_css([
	'selector' => $background_selectors['mobile'],
	'css' => $css,
	'value' => $background_source['mobile'],
	'responsive' => false,
	'important' => true
]);

