<?php

$woo_single_layout_left_value = blocksy_get_woo_single_layout_defaults('left');

$woo_single_layout_right_value = blocksy_get_woo_single_layout_defaults('right');

$options = [
	'woo_product_elements' => [
		'label' => __('Product Elements', 'blocksy'),
		'type' => 'ct-panel',
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __('General', 'blocksy'),
				'type' => 'tab',
				'options' => [
					[
						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [
								'product_view_type' => 'default-gallery|stacked-gallery'
							],
							'options' => [
								'woo_single_layout' => [
									'label' => false,
									'type' => 'ct-layers',
									'manageable' => false,
									'sync' => [
										blocksy_sync_whole_page([
											'prefix' => 'product',
											'loader_selector' => '.entry-summary-items'
										]),

										[
											'prefix' => 'product',
											'id' => 'woo_single_layout_skip',
											'loader_selector' => 'skip',
											'container_inclusive' => false
										],

										[
											'prefix' => 'product',
											'id' => 'product_payment_methods',
											'loader_selector' => '.entry-summary-items .ct-payment-methods',
											'container_inclusive' => false
										]

									],
									'value' => blocksy_get_woo_single_layout_defaults(),
									'settings' => blocksy_get_options('woocommerce/single-product-layers'),
								],
							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [
								'product_view_type' => 'columns-top-gallery|top-gallery'
							],
							'options' => [
								'woo_single_split_layout' => [
									'type' => 'ct-layers-combined',
									'design' => 'none',
									'value' => [
										'left' => $woo_single_layout_left_value,
										'right' => $woo_single_layout_right_value
									],
									'sync' => [
										'id' => 'woo_single_layout'
									],
									'inner-options' => [
										'left' => [
											'label' => __('Left Column', 'blocksy'),
											'type' => 'ct-layers',
											'manageable' => false,
											'grouped' => true,
											'value' => $woo_single_layout_left_value,
											'settings' => blocksy_get_options(
												'woocommerce/single-product-layers'
											),
											'attr' => [
												'class' => 'ct-layers-split-layout'
											]
										],

										'right' => [
											'label' => __('Right Column', 'blocksy'),
											'type' => 'ct-layers',
											'manageable' => false,
											'grouped' => true,
											'value' => $woo_single_layout_right_value,
											'settings' => blocksy_get_options(
												'woocommerce/single-product-layers'
											),
											'attr' => [
												'class' => 'ct-layers-split-layout'
											]
										],
									]
								],
							]
						],

						blocksy_rand_md5() => [
							'type' => 'ct-divider',
						],
					],

					apply_filters('blocksy_woo_single_options:after_layers', []),

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [
							'product_view_type' => 'default-gallery|stacked-gallery'
						],
						'options' => [
							'has_product_sticky_summary' => [
								'label' => __('Sticky Container', 'blocksy'),
								'type' => 'ct-switch',
								'value' => 'no',
								'sync' => 'live',
							],
						],
					],
				],
			],

			blocksy_rand_md5() => [
				'title' => __('Design', 'blocksy'),
				'type' => 'tab',
				'options' => [
					[
						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => ['woo_single_layout:array-ids:product_title:enabled' => '!no'],
							'computed_fields' => ['woo_single_layout'],
							'options' => [

								'singleProductTitleFont' => [
									'type' => 'ct-typography',
									'label' => __('Title Font', 'blocksy'),
									'value' => blocksy_typography_default_values([
										'size' => '30px',
									]),
									'setting' => ['transport' => 'postMessage'],
								],

								'singleProductTitleColor' => [
									'label' => __('Title Font Color', 'blocksy'),
									'type'  => 'ct-color-picker',
									'design' => 'inline',
									'setting' => ['transport' => 'postMessage'],

									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __('Initial', 'blocksy'),
											'id' => 'default',
											'inherit' => 'var(--theme-heading-1-color, var(--theme-headings-color))'
										],
									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => ['woo_single_layout:array-ids:product_price:enabled' => '!no'],
							'computed_fields' => ['woo_single_layout'],
							'options' => [

								'singleProductPriceFont' => [
									'type' => 'ct-typography',
									'label' => __('Price Font', 'blocksy'),
									'value' => blocksy_typography_default_values([
										'size' => '20px',
										'variation' => 'n7',
									]),
									'setting' => ['transport' => 'postMessage'],
									'divider' => 'top:full',
								],

								'singleProductPriceColor' => [
									'label' => __('Price Font Color', 'blocksy'),
									'type'  => 'ct-color-picker',
									'design' => 'inline',
									'setting' => ['transport' => 'postMessage'],

									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __('Initial', 'blocksy'),
											'id' => 'default',
											'inherit' => 'var(--theme-text-color)'
										],
									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => ['woo_single_layout:array-ids:product_breadcrumbs:enabled' => '!no'],
							'computed_fields' => ['woo_single_layout'],
							'options' => [

								'singleProductBreadcrumbsFont' => [
									'type' => 'ct-typography',
									'label' => __( 'Breadcrumbs Font', 'blocksy' ),
									'value' => blocksy_typography_default_values([]),
									'design' => 'block',
									'sync' => 'live',
									'divider' => 'top:full',
								],

								'singleProductBreadcrumbsFontColor' => [
									'label' => __( 'Breadcrumbs Font Color', 'blocksy' ),
									'type'  => 'ct-color-picker',
									'design' => 'inline',
									'sync' => 'live',

									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],

										'initial' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],

										'hover' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __( 'Text', 'blocksy' ),
											'id' => 'default',
											'inherit' => 'var(--theme-text-color)'
										],

										[
											'title' => __( 'Link Initial', 'blocksy' ),
											'id' => 'initial',
											'inherit' => 'var(--theme-link-initial-color)'
										],

										[
											'title' => __( 'Link Hover', 'blocksy' ),
											'id' => 'hover',
											'inherit' => 'var(--theme-link-hover-color)'
										],
									],
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => ['woo_single_layout:array-ids:product_add_to_cart:enabled' => '!no'],
							'computed_fields' => ['woo_single_layout'],
							'options' => [

								'quantity_color' => [
									'label' => __('Quantity Color', 'blocksy'),
									'type'  => 'ct-color-picker',
									'design' => 'inline',
									'divider' => 'top:full',
									'sync' => 'live',
									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],

										'hover' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __('Initial', 'blocksy'),
											'id' => 'default',
											'inherit' => 'var(--quantity-initial-color, var(--theme-button-background-initial-color))'
										],

										[
											'title' => __('Hover', 'blocksy'),
											'id' => 'hover',
											'inherit' => 'var(--quantity-hover-color, var(--theme-button-background-hover-color))'
										],
									],
								],

								'quantity_arrows' => [
									'label' => __('Quantity Arrows Color', 'blocksy'),
									'type'  => 'ct-color-picker',
									'design' => 'inline',
									'sync' => 'live',
									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],

										'default_type_2' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],

										'hover' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __('Initial', 'blocksy'),
											'id' => 'default',
											'inherit' => 'var(--quantity-arrows-initial-color, #fff)',
											'condition_source' => 'global',
											'condition' => ['quantity_type' => 'type-1']
										],

										[
											'title' => __('Initial', 'blocksy'),
											'id' => 'default_type_2',
											'inherit' => 'var(--quantity-arrows-initial-color, var(--theme-text-color))',
											'condition_source' => 'global',
											'condition' => ['quantity_type' => 'type-2']
										],

										[
											'title' => __('Hover', 'blocksy'),
											'id' => 'hover',
											'inherit' => 'var(--quantity-arrows-hover-color, #fff)'
										],
									],
								],

								blocksy_rand_md5() => [
									'type' => 'ct-title',
									'variation' => 'small-divider',
									'label' => __('Add To Cart Button', 'blocksy'),
								],

								'add_to_cart_text' => [
									'label' => __('Button Font Color', 'blocksy'),
									'type'  => 'ct-color-picker',
									'design' => 'inline',
									// 'responsive' => true,
									'sync' => 'live',
									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],

										'hover' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __('Initial', 'blocksy'),
											'id' => 'default',
											'inherit' => 'var(--theme-button-text-initial-color)',
										],

										[
											'title' => __('Hover', 'blocksy'),
											'id' => 'hover',
											'inherit' => 'var(--theme-button-text-hover-color)',
										],
									],
								],

								'add_to_cart_background' => [
									'label' => __('Button Background Color', 'blocksy'),
									'type'  => 'ct-color-picker',
									'design' => 'inline',
									// 'responsive' => true,
									'sync' => 'live',
									'value' => [
										'default' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],

										'hover' => [
											'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
										],
									],

									'pickers' => [
										[
											'title' => __('Initial', 'blocksy'),
											'id' => 'default',
											'inherit' => 'var(--theme-button-background-initial-color)'
										],

										[
											'title' => __('Hover', 'blocksy'),
											'id' => 'hover',
											'inherit' => 'var(--theme-button-background-hover-color)'
										],
									],
								],

								blocksy_rand_md5() => [
									'type' => 'ct-condition',
									'condition' => ['has_ajax_add_to_cart' => 'yes'],
									'options' => [

										blocksy_rand_md5() => [
											'type' => 'ct-title',
											'variation' => 'small-divider',
											'label' => __('View Cart Button', 'blocksy'),
										],

										'view_cart_button_text' => [
											'label' => __('Button Font Color', 'blocksy'),
											'type'  => 'ct-color-picker',
											'design' => 'inline',
											// 'responsive' => true,
											'sync' => 'live',
											'value' => [
												'default' => [
													'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
												],

												'hover' => [
													'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
												],
											],

											'pickers' => [
												[
													'title' => __('Initial', 'blocksy'),
													'id' => 'default',
													'inherit' => 'var(--theme-text-color)',
												],

												[
													'title' => __('Hover', 'blocksy'),
													'id' => 'hover',
													'inherit' => 'var(--theme-text-color)',
												],
											],
										],

										'view_cart_button_background' => [
											'label' => __('Button Background Color', 'blocksy'),
											'type'  => 'ct-color-picker',
											'design' => 'inline',
											// 'responsive' => true,
											'sync' => 'live',
											'value' => [
												'default' => [
													'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
												],

												'hover' => [
													'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
												],
											],

											'pickers' => [
												[
													'title' => __('Initial', 'blocksy'),
													'id' => 'default',
													'inherit' => 'rgba(224,229,235,0.6)'
												],

												[
													'title' => __('Hover', 'blocksy'),
													'id' => 'hover',
													'inherit' => 'rgba(224,229,235,1)'
												],
											],
										],

									],
								],
							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => ['woo_single_layout:array-ids:divider:enabled' => '!no'],
							'computed_fields' => ['woo_single_layout'],
							'options' => [

								'woo_single_layers_divider' => [
									'label' => __( 'Divider', 'blocksy' ),
									'type' => 'ct-border',
									'sync' => 'live',
									'design' => 'inline',
									'divider' => 'top:full',
									'value' => [
										'width' => 1,
										'style' => 'solid',
										'color' => [
											'color' => 'var(--theme-border-color)',
										],
									]
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [
								'woo_single_layout:array-ids:product_payment_methods:enabled' => '!no',
								'woo_single_layout:array-ids:product_payment_methods:payment_icons_color' => 'custom'
							],
							'computed_fields' => [
								'woo_single_layout'
							],
							'options' => [

								'payment_method_icons_color' => [
									'label' => __('Payment Methods Icons Color', 'blocksy'),
									'type'  => 'ct-color-picker',
									'design' => 'inline',
									'divider' => 'top:full',
									'setting' => ['transport' => 'postMessage'],

									'value' => [
										'default' => [
											'color' => '#4B4F58',
										],
									],

									'pickers' => [
										[
											'title' => __('Initial', 'blocksy'),
											'id' => 'default',
										],
									],
								],

							],
						],

					],

					apply_filters(
						'blocksy:options:single_product:elements:design_tab:end',
						[]
					),

					[
						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [
								'product_view_type' => 'columns-top-gallery|top-gallery'
							],
							'options' => [

								'entry_summary_container_border' => [
									'label' => __( 'Container Border', 'blocksy' ),
									'type' => 'ct-border',
									'sync' => 'live',
									'design' => 'inline',
									'divider' => 'top:full',
									'value' => [
										'width' => 1,
										'style' => 'solid',
										'color' => [
											'color' => 'var(--theme-border-color)',
										],
									]
								],

								'entry_summary_container_border_radius' => [
									'label' => __( 'Container Border Radius', 'blocksy' ),
									'sync' => 'live',
									'type' => 'ct-spacing',
									'divider' => 'top',
									'value' => blocksy_spacing_value(),
									'responsive' => true
								],

							],
						],
					]
				],
			],

		],
	],
];
