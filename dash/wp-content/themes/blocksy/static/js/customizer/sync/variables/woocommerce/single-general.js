import { responsiveClassesFor, updateAndSaveEl } from '../../../sync'
import { typographyOption } from '../typography'

export const getWooSingleGeneralVariablesFor = () => ({	

	// product tabs
	...typographyOption({
		id: 'woo_tabs_font',
		selector: '.woocommerce-tabs .tabs, .woocommerce-tabs .ct-accordion-heading, .woocommerce-tabs[data-type*="type-4"] .entry-content > h2',
	}),

	woo_tabs_font_color: [
		{
			selector: '.woocommerce-tabs .tabs, .woocommerce-tabs .ct-accordion-heading, .woocommerce-tabs[data-type*="type-4"] .entry-content > h2',
			variable: 'theme-link-initial-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-tabs .tabs, .woocommerce-tabs .ct-accordion-heading',
			variable: 'theme-link-hover-color',
			type: 'color:hover',
		},

		{
			selector: '.woocommerce-tabs .tabs, .woocommerce-tabs .ct-accordion-heading',
			variable: 'theme-link-active-color',
			type: 'color:active',
		},
	],

	woo_tabs_border_color: {
		selector: '.woocommerce-tabs[data-type] .tabs, .woocommerce-tabs .ct-accordion-heading',
		variable: 'tab-border-color',
		type: 'color',
	},

	woo_actibe_tab_border: {
		selector: '.woocommerce-tabs[data-type] .tabs',
		variable: 'tab-background',
		type: 'color',
	},

	woo_actibe_tab_background: [
		{
			selector: '.woocommerce-tabs[data-type*="type-2"] .tabs',
			variable: 'tab-background',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-tabs[data-type*="type-2"] .tabs li.active',
			variable: 'tab-border-color',
			type: 'color:border',
		},
	],

	woo_separated_tabs_spacing: {
		selector: '.woocommerce-tabs[data-type*="type-4"]',
		variable: 'woo-separated-tabs-spacing',
		responsive: true,
		unit: 'px',
	},


	// related & upsells
	...typographyOption({
		id: 'related_upsells_heading_font',
		selector: '.related > h2, .up-sells > h2',
	}),

	related_upsells_heading_font_color: {
		selector: '.related > h2, .up-sells > h2',
		variable: 'theme-heading-color',
		type: 'color',
	},

	related_upsell_slider_nav_arrow_color: [
		{
			selector: '.related .flexy-container, .up-sells .flexy-container',
			variable: 'flexy-nav-arrow-color',
			type: 'color:default',
		},

		{
			selector: '.related .flexy-container, .up-sells .flexy-container',
			variable: 'flexy-nav-arrow-hover-color',
			type: 'color:hover',
		},
	],

	related_upsell_slider_nav_background_color: [
		{
			selector: '.related .flexy-container, .up-sells .flexy-container',
			variable: 'flexy-nav-background-color',
			type: 'color:default',
		},

		{
			selector: '.related .flexy-container, .up-sells .flexy-container',
			variable: 'flexy-nav-background-hover-color',
			type: 'color:hover',
		},
	],
	
})