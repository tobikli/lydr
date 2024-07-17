<?php

if (! isset($skip_sync_id)) {
	$skip_sync_id = null;
}

if (! isset($prefix)) {
	$prefix = '';
} else {
	$prefix = $prefix . '_';
}

if (! isset($sync_prefix)) {
	$sync_prefix = $prefix;
}

$options = [
	$prefix . 'has_post_tags' => [
		'label' => __( 'Post Tags', 'blocksy' ),
		'type' => 'ct-panel',
		'switch' => true,
		'value' => 'no',
		'sync' => blocksy_sync_single_post_container([
			'prefix' => $prefix
		]),
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					$prefix . 'post_tags_title' => array_merge([
						'label' => __( 'Module Title', 'blocksy' ),
						'type' => 'text',
						'design' => 'inline',
						'value' => __( 'Tags', 'blocksy' ),
					], $skip_sync_id ? [
						'sync' => $skip_sync_id,
						'setting' => [ 'transport' => 'postMessage' ],
					] : [
						'sync' => 'live'
					]),

					$prefix . 'post_tags_title_wrapper' => [
						'label' => __( 'Module Title Tag', 'blocksy' ),
						'type' => 'ct-select',
						'value' => 'span',
						'view' => 'text',
						'design' => 'inline',
						'choices' => blocksy_ordered_keys(
							[
								'h1' => 'H1',
								'h2' => 'H2',
								'h3' => 'H3',
								'h4' => 'H4',
								'h5' => 'H5',
								'h6' => 'H6',
								'p' => 'p',
								'span' => 'span',
							]
						),
						'sync' => blocksy_sync_whole_page([
							'prefix' => $sync_prefix,
							'loader_selector' => '.entry-tags'
						]),
					],

					$prefix . 'post_tags_alignment' => [
						'type' => 'ct-radio',
						'label' => __( 'Content Alignment', 'blocksy' ),
						'view' => 'text',
						'design' => 'block',
						'divider' => 'top:full',
						'responsive' => true,
						'attr' => [ 'data-type' => 'alignment' ],
						'setting' => [ 'transport' => 'postMessage' ],
						'value' => 'CT_CSS_SKIP_RULE',
						'choices' => [
							'flex-start' => '',
							'center' => '',
							'flex-end' => '',
						],
					],

					$prefix . 'post_tags_visibility' => [
						'label' => __( 'Visibility', 'blocksy' ),
						'type' => 'ct-visibility',
						'design' => 'block',
						'sync' => 'live',
						'divider' => 'top:full',

						'value' => [
							'desktop' => true,
							'tablet' => true,
							'mobile' => true,
						],

						'choices' => blocksy_ordered_keys([
							'desktop' => __( 'Desktop', 'blocksy' ),
							'tablet' => __( 'Tablet', 'blocksy' ),
							'mobile' => __( 'Mobile', 'blocksy' ),
						]),
					],

				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					$prefix . 'post_tags_title_font' => [
						'type' => 'ct-typography',
						'label' => __( 'Module Title Font', 'blocksy' ),
						'sync' => 'live',
						'value' => blocksy_typography_default_values([
							'size' => '14px',
							'variation' => 'n6',
						]),
					],

					$prefix . 'post_tags_title_color' => [
						'label' => __( 'Module Title Font Color', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
						'sync' => 'live',
						'value' => [
							'default' => [
								'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
							],
						],
						'pickers' => [
							[
								'title' => __( 'Initial', 'blocksy' ),
								'id' => 'default',
								'inherit' => [
									'var(--theme-heading-1-color, var(--theme-headings-color))' => [
										$prefix . 'post_tags_title_wrapper' => 'h1'
									],

									'var(--theme-heading-2-color, var(--theme-headings-color))' => [
										$prefix . 'post_tags_title_wrapper' => 'h2'
									],

									'var(--theme-heading-3-color, var(--theme-headings-color))' => [
										$prefix . 'post_tags_title_wrapper' => 'h3'
									],

									'var(--theme-heading-4-color, var(--theme-headings-color))' => [
										$prefix . 'post_tags_title_wrapper' => 'h4'
									],

									'var(--theme-heading-5-color, var(--theme-headings-color))' => [
										$prefix . 'post_tags_title_wrapper' => 'h5'
									],

									'var(--theme-heading-6-color, var(--theme-headings-color))' => [
										$prefix . 'post_tags_title_wrapper' => 'h6'
									],

									'var(--theme-text-color)' => [
										$prefix . 'post_tags_title_wrapper' => 'span|p'
									],
								]
							],
						],
					],

					$prefix . 'post_tags_border_radius' => [
						'label' => __( 'Border Radius', 'blocksy' ),
						'sync' => 'live',
						'type' => 'ct-spacing',
						'divider' => 'top',
						'value' => blocksy_spacing_value(),
						'responsive' => true
					],

				],
			],

		],
	],
];