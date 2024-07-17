<?php

// quantity input colors
$has_custom_quantity = blocksy_get_theme_mod('has_custom_quantity', 'yes');

if ($has_custom_quantity === 'yes') {
	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('global_quantity_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.quantity',
				'variable' => 'quantity-initial-color'
			],

			'hover' => [
				'selector' => '.quantity',
				'variable' => 'quantity-hover-color'
			],
		],
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('global_quantity_arrows'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'default_type_2' => [ 'color' => 'var(--theme-text-color)' ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.quantity[data-type="type-1"]',
				'variable' => 'quantity-arrows-initial-color'
			],

			'default_type_2' => [
				'selector' => '.quantity[data-type="type-2"]',
				'variable' => 'quantity-arrows-initial-color'
			],

			'hover' => [
				'selector' => '.quantity',
				'variable' => 'quantity-arrows-hover-color'
			],
		],
	]);
}


// sale badge
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('saleBadgeColor'),
	'default' => [
		'text' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'background' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'text' => [
			'selector' => ':root',
			'variable' => 'badge-text-color'
		],

		'background' => [
			'selector' => ':root',
			'variable' => 'badge-background-color'
		],
	],
]);


// out of stock badge
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('outOfStockBadgeColor'),
	'default' => [
		'text' => [ 'color' => '#ffffff' ],
		'background' => [ 'color' => '#24292E' ],
	],
	'css' => $css,
	'variables' => [
		'text' => [
			'selector' => '.out-of-stock-badge',
			'variable' => 'badge-text-color'
		],

		'background' => [
			'selector' => '.out-of-stock-badge',
			'variable' => 'badge-background-color'
		],
	],
]);


// store notice
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('wooNoticeContent'),
	'default' => [
		'default' => ['color' => '#ffffff']
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.demo_store',
			'variable' => 'theme-text-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('wooNoticeBackground'),
	'default' => [
		'default' => ['color' => 'var(--theme-palette-color-1)']
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.demo_store',
			'variable' => 'background-color'
		],
	],
]);


// info message
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('info_message_text_color'),
	'default' => [
		'default' => ['color' => 'var(--theme-text-color)'],
		'hover' => ['color' => 'var(--theme-link-hover-color)'],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-info, .woocommerce-thankyou-order-received, .wc-block-components-notice-banner.is-info',
			'variable' => 'theme-text-color'
		],

		'hover' => [
			'selector' => '.woocommerce-info, .woocommerce-thankyou-order-received, .wc-block-components-notice-banner.is-info',
			'variable' => 'theme-link-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('info_message_background_color'),
	'default' => [
		'default' => ['color' => '#F0F1F3'],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-info, .woocommerce-thankyou-order-received, .wc-block-components-notice-banner.is-info',
			'variable' => 'background-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('info_message_button_text_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-info, .wc-block-components-notice-banner.is-info',
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => '.woocommerce-info, .wc-block-components-notice-banner.is-info',
			'variable' => 'theme-button-text-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('info_message_button_background'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-info, .wc-block-components-notice-banner.is-info',
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => '.woocommerce-info, .wc-block-components-notice-banner.is-info',
			'variable' => 'theme-button-background-hover-color'
		],
	],
]);


// success message
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('success_message_text_color'),
	'default' => [
		'default' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
		'hover' => ['color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT')],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-message, .wc-block-components-notice-banner.is-success',
			'variable' => 'theme-text-color'
		],

		'hover' => [
			'selector' => '.woocommerce-message, .wc-block-components-notice-banner.is-success',
			'variable' => 'theme-link-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('success_message_background_color'),
	'default' => [
		'default' => ['color' => '#F0F1F3'],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-message, .wc-block-components-notice-banner.is-success',
			'variable' => 'background-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('success_message_button_text_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-message, .wc-block-components-notice-banner.is-success',
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => '.woocommerce-message, .wc-block-components-notice-banner.is-success',
			'variable' => 'theme-button-text-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('success_message_button_background'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-message, .wc-block-components-notice-banner.is-success',
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => '.woocommerce-message, .wc-block-components-notice-banner.is-success',
			'variable' => 'theme-button-background-hover-color'
		],
	],
]);


// error message
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('error_message_text_color'),
	'default' => [
		'default' => ['color' => '#ffffff'],
		'hover' => ['color' => '#ffffff'],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-error, .wc-block-components-notice-banner.is-error',
			'variable' => 'theme-text-color'
		],

		'hover' => [
			'selector' => '.woocommerce-error, .wc-block-components-notice-banner.is-error',
			'variable' => 'theme-link-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('error_message_background_color'),
	'default' => [
		'default' => ['color' => 'rgba(218, 0, 28, 0.7)'],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-error, .wc-block-components-notice-banner.is-error',
			'variable' => 'background-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('error_message_button_text_color'),
	'default' => [
		'default' => [ 'color' => '#ffffff' ],
		'hover' => [ 'color' => '#ffffff' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-error, .wc-block-components-notice-banner.is-error',
			'variable' => 'theme-button-text-initial-color'
		],

		'hover' => [
			'selector' => '.woocommerce-error, .wc-block-components-notice-banner.is-error',
			'variable' => 'theme-button-text-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('error_message_button_background'),
	'default' => [
		'default' => [ 'color' => '#b92c3e' ],
		'hover' => [ 'color' => '#9c2131' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.woocommerce-error, .wc-block-components-notice-banner.is-error',
			'variable' => 'theme-button-background-initial-color'
		],

		'hover' => [
			'selector' => '.woocommerce-error, .wc-block-components-notice-banner.is-error',
			'variable' => 'theme-button-background-hover-color'
		],
	],
]);


// account page
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('account_nav_text_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
		'active' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.ct-acount-nav',
			'variable' => 'account-nav-text-initial-color'
		],

		'active' => [
			'selector' => '.ct-acount-nav',
			'variable' => 'account-nav-text-active-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('account_nav_background_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
		'active' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.ct-acount-nav',
			'variable' => 'account-nav-background-initial-color'
		],

		'active' => [
			'selector' => '.ct-acount-nav',
			'variable' => 'account-nav-background-active-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('account_nav_divider_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword() ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '.ct-acount-nav',
			'variable' => 'account-nav-divider-color'
		],
	],
]);

blocksy_output_box_shadow([
	'css' => $css,
	'selector' => '.ct-acount-nav',
	'value' => blocksy_get_theme_mod('account_nav_shadow', blocksy_box_shadow_value([
		'enable' => false,
		'h_offset' => 0,
		'v_offset' => 10,
		'blur' => 20,
		'spread' => 0,
		'inset' => false,
		'color' => [
			'color' => 'rgba(0, 0, 0, 0.03)',
		],
	])),
]);


// cart page
blocksy_output_border([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-woocommerce-cart-form .cart_totals',
	'variableName' => 'cart-totals-border',
	'value' => blocksy_get_theme_mod('cart_totals_border'),
	'default' => [
		'width' => 2,
		'style' => 'solid',
		'color' => [
			'color' => 'var(--theme-border-color)',
		],
	],
	'responsive' => true,
]);

blocksy_output_spacing([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-woocommerce-cart-form .cart_totals',
	'property' => 'cart-totals-border-radius',
	'value' => blocksy_get_theme_mod( 'cart_totals_border_radius',
		blocksy_spacing_value()
	)
]);

blocksy_output_spacing([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-woocommerce-cart-form .cart_totals',
	'property' => 'cart-totals-padding',
	'value' => blocksy_get_theme_mod(
		'cart_totals_padding',
		[
			'desktop' => blocksy_spacing_value([
				'top' => '30px',
				'left' => '30px',
				'right' => '30px',
				'bottom' => '30px',
			]),
			'tablet' => blocksy_spacing_value([
				'top' => '25px',
				'left' => '25px',
				'right' => '25px',
				'bottom' => '25px',
			]),
			'mobile'=> blocksy_spacing_value([
				'top' => '25px',
				'left' => '25px',
				'right' => '25px',
				'bottom' => '25px',
			]),
		]
	)
]);


// checkout page
blocksy_output_border([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-order-review',
	'variableName' => 'order-review-border',
	'value' => blocksy_get_theme_mod('checkout_order_border'),
	'default' => [
		'width' => 2,
		'style' => 'solid',
		'color' => [
			'color' => 'var(--theme-border-color)',
		],
	],
	'responsive' => true,
]);

blocksy_output_spacing([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-order-review',
	'property' => 'order-review-border-radius',
	'value' => blocksy_get_theme_mod( 'checkout_order_border_radius',
		blocksy_spacing_value()
	)
]);

blocksy_output_spacing([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-order-review',
	'property' => 'order-review-padding',
	'value' => blocksy_get_theme_mod(
		'checkout_order_padding',
		[
			'desktop' => blocksy_spacing_value([
				'top' => '35px',
				'left' => '35px',
				'right' => '35px',
				'bottom' => '35px',
			]),
			'tablet' => blocksy_spacing_value([
				'top' => '30px',
				'left' => '30px',
				'right' => '30px',
				'bottom' => '30px',
			]),
			'mobile'=> blocksy_spacing_value([
				'top' => '25px',
				'left' => '25px',
				'right' => '25px',
				'bottom' => '25px',
			]),
		]
	)
]);