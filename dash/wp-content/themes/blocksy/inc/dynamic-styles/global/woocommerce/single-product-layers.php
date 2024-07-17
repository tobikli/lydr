<?php


$product_view_type = blocksy_get_product_view_type();

$woo_single_layout = [];

if (
	$product_view_type === 'default-gallery'
	||
	$product_view_type === 'stacked-gallery'
) {
	$default_product_layout = blocksy_get_woo_single_layout_defaults();

	$woo_single_layout = blocksy_get_theme_mod(
		'woo_single_layout',
		$default_product_layout
	);
} else {
	$woo_single_split_layout = blocksy_get_theme_mod(
		'woo_single_split_layout',
		[
			'left' => blocksy_get_woo_single_layout_defaults('left'),

			'right' => blocksy_get_woo_single_layout_defaults('right')
		]
	);

	$woo_single_layout_left = $woo_single_split_layout['left'];
	$woo_single_layout_right = $woo_single_split_layout['right'];

	$woo_single_layout = array_merge(
		$woo_single_layout_left,
		$woo_single_layout_right
	);
}

foreach ($woo_single_layout as $layer) {
	if (! $layer['enabled'] ) {
		continue;
	}

	$selectors_map = [
		'product_title' => '.entry-summary-items > .entry-title',
		'product_rating' => '.entry-summary-items > .woocommerce-product-rating',
		'product_price' => '.entry-summary-items > .price',
		'product_desc' => '.entry-summary-items > .woocommerce-product-details__short-description',
		'product_add_to_cart' => '.entry-summary-items > .ct-product-add-to-cart',
		'product_meta' => '.entry-summary-items > .product_meta',
		'product_payment_methods' => '.entry-summary-items > .ct-payment-methods',
		'additional_info' => '.entry-summary-items > .ct-product-additional-info',
		'product_tabs' => '.entry-summary-items > .woocommerce-tabs',
		'product_breadcrumbs' => '.entry-summary-items > .ct-breadcrumbs',

		// companion
		'product_brands' => '.entry-summary-items > .ct-product-brands-single',
		'product_sharebox' => '.entry-summary-items > .ct-share-box',
		'free_shipping' => '.entry-summary-items > .ct-shipping-progress',
		'product_actions' => '.entry-summary-items > .ct-product-additional-actions',
		'product_countdown' => '.entry-summary-items > .ct-product-sale-countdown',
	];

	$spacing_default = 10;

	if (
		$layer['id'] === 'product_price'
		||
		$layer['id'] === 'product_desc'
		||
		$layer['id'] === 'product_add_to_cart'
		||
		$layer['id'] === 'divider'
		||
		$layer['id'] === 'product_actions'
		||
		$layer['id'] === 'product_countdown'
	) {
		$spacing_default = 35;
	}

	$spacing = blocksy_akg('spacing', $layer, $spacing_default);

	if (
		isset($selectors_map[$layer['id']])
		&&
		(
			intval($spacing) !== $spacing_default
			||
			$spacing_default === 35
		)
	) {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => $selectors_map[$layer['id']],
			'variableName' => 'product-element-spacing',
			'value' => $spacing
		]);
	}

	if ( $layer['id'] === 'product_sharebox' ) {
		$share_icons_size = blocksy_akg('share_box_icon_size', $layer, '15px');
		$share_box_icons_spacing = blocksy_akg('share_box_icons_spacing', $layer, '15px');

		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => $selectors_map[$layer['id']],
			'variableName' => 'theme-icon-size',
			'value' => $share_icons_size,
			'unit' => ''
		]);

		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => $selectors_map[$layer['id']],
			'variableName' => 'items-spacing',
			'value' => $share_box_icons_spacing,
			'unit' => ''
		]);
	}

	if ($layer['id'] === 'product_payment_methods') {
		$payment_icons_size = blocksy_akg('payment_icons_size', $layer, 40);

		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => $selectors_map[$layer['id']],
			'variableName' => 'theme-icon-size',
			'value' => $payment_icons_size,
			'unit' => 'px'
		]);
	}

	if ($layer['id'] === 'product_add_to_cart') {
		$add_to_cart_button_width = blocksy_akg(
			'add_to_cart_button_width',
			$layer,
			blocksy_get_theme_mod('add_to_cart_button_width', '100%')
		);

		if ($add_to_cart_button_width !== '100%') {
			blocksy_output_responsive([
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,
				'selector' => $selectors_map[$layer['id']] . ' > .cart',
				'variableName' => 'theme-button-max-width',
				'unit' => '',
				'value' => $add_to_cart_button_width,
			]);
		}
	}

	if ($layer['id'] === 'product_brands') {
		$brand_logo_size = blocksy_akg('brand_logo_size', $layer, 100);

		if ($brand_logo_size !== 100) {
			blocksy_output_responsive([
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,
				'selector' => $selectors_map[$layer['id']],
				'variableName' => 'product-brand-logo-size',
				'value' => $brand_logo_size,
			]);
		}

		$brand_logo_gap = blocksy_akg('brand_logo_gap', $layer, 10);

		if ($brand_logo_gap !== 10) {
			blocksy_output_responsive([
				'css' => $css,
				'tablet_css' => $tablet_css,
				'mobile_css' => $mobile_css,
				'selector' => $selectors_map[$layer['id']],
				'variableName' => 'product-brands-gap',
				'value' => $brand_logo_gap,
			]);
		}
	}

	if ($layer['id'] === 'divider') {
		$id = isset($layer["__id"]) ? $layer["__id"] : 'default';

		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '.entry-summary-items > .ct-product-divider[data-id="' . $id . '"]',
			'variableName' => 'product-element-spacing',
			'value' => $spacing,
		]);
	}

	if ($layer['id'] === 'content-block') {
		$id = isset($layer["__id"]) ? $layer["__id"] : 'default';

		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '.entry-summary-items > .ct-product-content-block[data-id="' . $id . '"]',
			'variableName' => 'product-element-spacing',
			'value' => $spacing,
			'unit' => 'px'
		]);
	}

	if (in_array(
		$layer['id'],
		[
			'acf_field',
			'metabox_field',
			'toolset_field',
			'jetengine_field',
			'custom_field',
			'pods_field',
		]
	)) {
		$id = substr(isset($layer["__id"]) ? $layer["__id"] : 'default', 0, 6);

		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '.entry-summary-items > .ct-dynamic-data-layer[data-field*=":' . $id . '"]',
			'variableName' => 'product-element-spacing',
			'value' => $spacing,
			'unit' => 'px'
		]);
	
	}
}


// breadcrumbs
blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod(
		'singleProductBreadcrumbsFont',
		blocksy_typography_default_values([])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.entry-summary .ct-breadcrumbs'
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('singleProductBreadcrumbsFontColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'initial' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.entry-summary .ct-breadcrumbs',
			'variable' => 'theme-text-color'
		],

		'initial' => [
			'selector' => '.entry-summary .ct-breadcrumbs',
			'variable' => 'theme-link-initial-color'
		],

		'hover' => [
			'selector' => '.entry-summary .ct-breadcrumbs',
			'variable' => 'theme-link-hover-color'
		],
	],
]);


// product title
blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod(
		'singleProductTitleFont',
		blocksy_typography_default_values([
			'size' => '30px',
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.entry-summary .entry-title'
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('singleProductTitleColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.entry-summary .entry-title',
			'variable' => 'theme-heading-color'
		],
	],
]);


// product price
blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod(
		'singleProductPriceFont',
		blocksy_typography_default_values([
			'size' => '20px',
			'variation' => 'n7',
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.product-entry-wrapper .price'
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('singleProductPriceColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.product-entry-wrapper .price',
			'variable' => 'theme-text-color'
		],
	],
]);


// quantity input
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('quantity_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.entry-summary .quantity',
			'variable' => 'quantity-initial-color'
		],

		'hover' => [
			'selector' => '.entry-summary .quantity',
			'variable' => 'quantity-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('quantity_arrows'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'default_type_2' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.entry-summary .quantity[data-type="type-1"]',
			'variable' => 'quantity-arrows-initial-color'
		],

		'default_type_2' => [
			'selector' => '.entry-summary .quantity[data-type="type-2"]',
			'variable' => 'quantity-arrows-initial-color'
		],

		'hover' => [
			'selector' => '.entry-summary .quantity',
			'variable' => 'quantity-arrows-hover-color'
		],
	],
]);


// add to cart & view cart buttons
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('add_to_cart_text'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.entry-summary .single_add_to_cart_button',
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => '.entry-summary .single_add_to_cart_button',
			'variable' => 'theme-button-text-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('add_to_cart_background'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.entry-summary .single_add_to_cart_button',
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => '.entry-summary .single_add_to_cart_button',
			'variable' => 'theme-button-background-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('view_cart_button_text'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.entry-summary .ct-cart-actions .added_to_cart',
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => '.entry-summary .ct-cart-actions .added_to_cart',
			'variable' => 'theme-button-text-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('view_cart_button_background'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.entry-summary .ct-cart-actions .added_to_cart',
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => '.entry-summary .ct-cart-actions .added_to_cart',
			'variable' => 'theme-button-background-hover-color'
		],
	],
]);


// divider
blocksy_output_border([
	'css' => $css,
	'selector' => '.entry-summary .ct-product-divider',
	'variableName' => 'single-product-layer-divider',
	'value' => blocksy_get_theme_mod('woo_single_layers_divider'),
	'default' => [
		'width' => 1,
		'style' => 'solid',
		'color' => [
			'color' => 'var(--theme-border-color)',
		],
	],
]);


// payment methods
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('payment_method_icons_color'),
	'default' => [
		'default' => [ 'color' => '#4B4F58' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.entry-summary .ct-payment-methods[data-color="custom"]',
			'variable' => 'theme-icon-color'
		],
	],
]);


if (
	$product_view_type === 'top-gallery'
	||
	$product_view_type === 'columns-top-gallery'
) {

	blocksy_output_border([
		'css' => $css,
		'selector' => '.product[class*=top-gallery] .entry-summary',
		'variableName' => 'container-border',
		'value' => blocksy_get_theme_mod('entry_summary_container_border'),
		'default' => [
			'width' => 1,
			'style' => 'solid',
			'color' => [
				'color' => 'var(--theme-border-color)',
			],
		],
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.product[class*=top-gallery] .entry-summary',
		'property' => 'container-border-radius',
		'value' => blocksy_get_theme_mod('entry_summary_container_border_radius',
			blocksy_spacing_value()
		)
	]);
}