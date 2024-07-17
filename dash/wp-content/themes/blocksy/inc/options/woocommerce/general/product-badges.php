<?php
/**
 * Product badges options
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

$options = [

	blocksy_rand_md5() => [
		'label' => __('Product Badges', 'blocksy'),
		'type' => 'ct-panel',
		'setting' => ['transport' => 'postMessage'],
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					[
						'sale_badge_shape' => [
							'label' => __( 'Badge Shape', 'blocksy' ),
							'type' => 'ct-image-picker',
							'value' => 'type-2',
							'attr' => [
								'data-type' => 'background',
								'data-columns' => '3',
							],
							'setting' => [ 'transport' => 'postMessage' ],
							'choices' => [

								'type-1' => [
									'src'   => blocksy_image_picker_file( 'badge-1' ),
									'title' => __( 'Type 1', 'blocksy' ),
								],

								'type-2' => [
									'src'   => blocksy_image_picker_file( 'badge-2' ),
									'title' => __( 'Type 2', 'blocksy' ),
								],

								'type-3' => [
									'src'   => blocksy_image_picker_file( 'badge-3' ),
									'title' => __( 'Type 3', 'blocksy' ),
								],
							],
						],

						'has_sale_badge' => [
							'label' => __( 'Show Sale Badge', 'blocksy' ),
							'type' => 'ct-checkboxes',
							'design' => 'block',
							'view' => 'text',
							'allow_empty' => true,
							'value' => [
								'archive' => true,
								'single' => true,
							],
							'divider' => 'top:full',
							'choices' => blocksy_ordered_keys([
								'archive' => __( 'Archive', 'blocksy' ),
								'single' => __( 'Single', 'blocksy' ),
							]),
							'sync' => blocksy_sync_whole_page([
								// 'prefix' => 'product',
								'loader_selector' => '[data-products] > li, .woocommerce-product-gallery'
							])
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [
								'any' => [
									'has_sale_badge/archive' => true,
									'has_sale_badge/single' => true,
								]
							],
							'options' => [
								'sale_badge_value' => [
									'type' => 'ct-radio',
									'label' => __( 'Sale Badge Value', 'blocksy' ),
									'value' => 'default',
									'view' => 'text',
									'design' => 'block',
									'setting' => [ 'transport' => 'postMessage' ],
									'choices' => [
										'default' => __( 'Default', 'blocksy' ),
										'custom' => __( 'Custom', 'blocksy' ),
									],
									'sync' => blocksy_sync_whole_page([
										'prefix' => 'woo_categories',
										'loader_selector' => '.onsale'
									]),
								],

								blocksy_rand_md5() => [
									'type' => 'ct-condition',
									'condition' => [ 'sale_badge_value' => 'default' ],
									'options' => [
										'sale_badge_default_value' => [
											'label' => __( 'Badge Label', 'blocksy' ),
											'type' => 'text',
											'design' => 'block',
											'value' => 'SALE',
											'setting' => [ 'transport' => 'postMessage' ],
										],
									],
								],

								blocksy_rand_md5() => [
									'type' => 'ct-condition',
									'condition' => [ 'sale_badge_value' => 'custom' ],
									'options' => [
										'sale_badge_custom_value' => [
											'label' => __( 'Badge Label', 'blocksy' ),
											'type' => 'text',
											'design' => 'block',
											'value' => '-{value}%',
											'sync' => blocksy_sync_whole_page([
												'prefix' => 'woo_categories',
												'loader_selector' => '.onsale'
											]),
										],
									],
								],
							],
						],

						'has_stock_badge' => [
							'label' => __( 'Show Stock Badge', 'blocksy' ),
							'type' => 'ct-checkboxes',
							'design' => 'block',
							'view' => 'text',
							'allow_empty' => true,
							'value' => [
								'archive' => true,
								'single' => true,
							],
							'divider' => 'top:full',
							'choices' => blocksy_ordered_keys([
								'archive' => __( 'Archive', 'blocksy' ),
								'single' => __( 'Single', 'blocksy' ),
							]),
							'sync' => blocksy_sync_whole_page([
								'prefix' => 'woo_categories',
								'loader_selector' => '.out-of-stock-badge'
							])
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [
								'any' => [
									'has_stock_badge/archive' => true,
									'has_stock_badge/single' => true,
								]
							],
							'options' => [
								'stock_badge_value' => [
									'label' => __( 'Badge Label', 'blocksy' ),
									'type' => 'text',
									'design' => 'block',
									'value' => __('SOLD OUT', 'blocksy'),
									'setting' => [ 'transport' => 'postMessage' ],
								]
							],
						],
					],

					apply_filters(
						'blocksy_customizer_options:woocommerce:general:badges:options',
						[]
					)

				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					[
						'saleBadgeColor' => [
							'label' => __( 'Sale Badge', 'blocksy' ),
							'type'  => 'ct-color-picker',
							'design' => 'inline',
							'setting' => [ 'transport' => 'postMessage' ],

							'value' => [
								'text' => [
									'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
								],

								'background' => [
									'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
								],
							],

							'pickers' => [
								[
									'title' => __( 'Text', 'blocksy' ),
									'id' => 'text',
									'inherit' => '#ffffff'
								],

								[
									'title' => __( 'Background', 'blocksy' ),
									'id' => 'background',
									'inherit' => 'var(--theme-palette-color-1)'
								],
							],
						],

						'outOfStockBadgeColor' => [
							'label' => __( 'Out of Stock Badge', 'blocksy' ),
							'type'  => 'ct-color-picker',
							'design' => 'inline',
							'setting' => [ 'transport' => 'postMessage' ],

							'value' => [
								'text' => [
									'color' => '#ffffff',
								],

								'background' => [
									'color' => '#24292E',
								],
							],

							'pickers' => [
								[
									'title' => __( 'Text', 'blocksy' ),
									'id' => 'text',
								],

								[
									'title' => __( 'Background', 'blocksy' ),
									'id' => 'background',
								],
							],
						],
					],

					apply_filters(
						'blocksy_customizer_options:woocommerce:general:badges:design:options',
						[]
					)
				],
			],

		],
	],

];