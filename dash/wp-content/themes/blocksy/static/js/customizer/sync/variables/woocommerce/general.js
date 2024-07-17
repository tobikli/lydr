import { handleBackgroundOptionFor } from '../background'

export const getWooGeneralVariablesFor = () => ({
	// quantity inputs general
	global_quantity_color: [
		{
			selector: '.quantity',
			variable: 'quantity-initial-color',
			type: 'color:default',
		},

		{
			selector: '.quantity',
			variable: 'quantity-hover-color',
			type: 'color:hover',
		},
	],

	global_quantity_arrows: [
		{
			selector: '.quantity[data-type="type-1"]',
			variable: 'quantity-arrows-initial-color',
			type: 'color:default',
		},

		{
			selector: '.quantity[data-type="type-2"]',
			variable: 'quantity-arrows-initial-color',
			type: 'color:default_type_2',
		},

		{
			selector: '.quantity',
			variable: 'quantity-arrows-hover-color',
			type: 'color:hover',
		},
	],

	// sale badge
	saleBadgeColor: [
		{
			selector: ':root',
			variable: 'badge-text-color',
			type: 'color:text',
		},

		{
			selector: ':root',
			variable: 'badge-background-color',
			type: 'color:background',
		},
	],

	// out of stock badge
	outOfStockBadgeColor: [
		{
			selector: '.out-of-stock-badge',
			variable: 'badge-text-color',
			type: 'color:text',
		},

		{
			selector: '.out-of-stock-badge',
			variable: 'badge-background-color',
			type: 'color:background',
		},
	],

	// store notice
	wooNoticeContent: {
		selector: '.demo_store',
		variable: 'theme-text-color',
		type: 'color',
	},

	wooNoticeBackground: {
		selector: '.demo_store',
		variable: 'background-color',
		type: 'color',
	},

	// info message
	info_message_text_color: [
		{
			selector: '.woocommerce-info, .woocommerce-thankyou-order-received, .wc-block-components-notice-banner.is-info',
			variable: 'theme-text-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-info, .woocommerce-thankyou-order-received, .wc-block-components-notice-banner.is-info',
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},
	],

	info_message_background_color: {
		selector: '.woocommerce-info, .woocommerce-thankyou-order-received, .wc-block-components-notice-banner.is-info',
		variable: 'background-color',
		type: 'color',
	},

	info_message_button_text_color: [
		{
			selector: '.woocommerce-info, .wc-block-components-notice-banner.is-info',
			variable: 'theme-button-text-initial-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-info, .wc-block-components-notice-banner.is-info',
			variable: 'theme-button-text-hover-color',
			type: 'color:hover',
		},
	],

	info_message_button_background: [
		{
			selector: '.woocommerce-info, .wc-block-components-notice-banner.is-info',
			variable: 'theme-button-background-initial-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-info, .wc-block-components-notice-banner.is-info',
			variable: 'theme-button-background-hover-color',
			type: 'color:hover',
		},
	],

	// success message
	success_message_text_color: [
		{
			selector: '.woocommerce-message',
			variable: 'theme-text-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-message',
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},
	],

	success_message_background_color: {
		selector: '.woocommerce-message',
		variable: 'background-color',
		type: 'color',
	},

	success_message_button_text_color: [
		{
			selector: '.woocommerce-message',
			variable: 'theme-button-text-initial-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-message',
			variable: 'theme-button-text-hover-color',
			type: 'color:hover',
		},
	],

	success_message_button_background: [
		{
			selector: '.woocommerce-message',
			variable: 'theme-button-background-initial-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-message',
			variable: 'theme-button-background-hover-color',
			type: 'color:hover',
		},
	],

	// error message
	error_message_text_color: [
		{
			selector: '.woocommerce-error, .wc-block-components-notice-banner.is-error',
			variable: 'theme-text-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-error, .wc-block-components-notice-banner.is-error',
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},
	],

	error_message_background_color: {
		selector: '.woocommerce-error, .wc-block-components-notice-banner.is-error',
		variable: 'background-color',
		type: 'color',
	},

	error_message_button_text_color: [
		{
			selector: '.woocommerce-error, .wc-block-components-notice-banner.is-error',
			variable: 'theme-button-text-initial-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-error, .wc-block-components-notice-banner.is-error',
			variable: 'theme-button-text-hover-color',
			type: 'color:hover',
		},
	],

	error_message_button_background: [
		{
			selector: '.woocommerce-error, .wc-block-components-notice-banner.is-error',
			variable: 'theme-button-background-initial-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-error, .wc-block-components-notice-banner.is-error',
			variable: 'theme-button-background-hover-color',
			type: 'color:hover',
		},
	],

	// account page
	account_nav_text_color: [
		{
			selector: '.ct-acount-nav',
			variable: 'account-nav-text-initial-color',
			type: 'color:default',
		},

		{
			selector: '.ct-acount-nav',
			variable: 'account-nav-text-active-color',
			type: 'color:active',
		},
	],

	account_nav_background_color: [
		{
			selector: '.ct-acount-nav',
			variable: 'account-nav-background-initial-color',
			type: 'color:default',
		},

		{
			selector: '.ct-acount-nav',
			variable: 'account-nav-background-active-color',
			type: 'color:active',
		},
	],

	account_nav_divider_color: [
		{
			selector: '.ct-acount-nav',
			variable: 'account-nav-divider-color',
			type: 'color:default',
		},
	],

	account_nav_shadow: {
		selector: '.ct-acount-nav',
		type: 'box-shadow',
		variable: 'theme-box-shadow',
		responsive: true,
	},

	// cart page
	cart_totals_border: {
		selector: '.ct-woocommerce-cart-form .cart_totals',
		variable: 'cart-totals-border',
		type: 'border',
		responsive: true,
	},	

	cart_totals_border_radius: {
		selector: '.ct-woocommerce-cart-form .cart_totals',
		type: 'spacing',
		variable: 'cart-totals-border-radius',
		responsive: true,
	},

	cart_totals_padding: {
		selector: '.ct-woocommerce-cart-form .cart_totals',
		type: 'spacing',
		variable: 'cart-totals-padding',
		responsive: true,
		unit: '',
	},


	// checkout page
	checkout_order_border: {
		selector: '.ct-order-review',
		variable: 'order-review-border',
		type: 'border',
		responsive: true,
	},	

	checkout_order_border_radius: {
		selector: '.ct-order-review',
		type: 'spacing',
		variable: 'order-review-border-radius',
		responsive: true,
	},

	checkout_order_padding: {
		selector: '.ct-order-review',
		type: 'spacing',
		variable: 'order-review-padding',
		responsive: true,
		unit: '',
	},
})
