export const getWooSingleGalleryVariablesFor = () => ({

	productGalleryWidth: {
		selector: '.product-entry-wrapper',
		variable: 'product-gallery-width',
		unit: '%',
	},

	product_image_border_radius: {
		selector: '.product-entry-wrapper',
		type: 'spacing',
		variable: 'border-radius',
		responsive: true,
	},

	// thumbnails
	product_thumbs_spacing: {
		selector: '.product-entry-wrapper',
		variable: 'thumbs-spacing',
		responsive: true,
		unit: '',
	},

	product_thumbs_border_radius: {
		selector: '.woocommerce-product-gallery .flexy-pills',
		type: 'spacing',
		variable: 'border-radius',
		responsive: true,
	},

	// slider arrows
	slider_nav_arrow_color: [
		{
			selector: '.woocommerce-product-gallery',
			variable: 'flexy-nav-arrow-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-product-gallery',
			variable: 'flexy-nav-arrow-hover-color',
			type: 'color:hover',
		},
	],

	slider_nav_background_color: [
		{
			selector: '.woocommerce-product-gallery',
			variable: 'flexy-nav-background-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-product-gallery',
			variable: 'flexy-nav-background-hover-color',
			type: 'color:hover',
		},
	],

	// lightbox button
	lightbox_button_icon_color: [
		{
			selector: '.woocommerce-product-gallery__trigger',
			variable: 'lightbox-button-icon-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-product-gallery__trigger',
			variable: 'lightbox-button-icon-hover-color',
			type: 'color:hover',
		},
	],

	lightbox_button_background_color: [
		{
			selector: '.woocommerce-product-gallery__trigger',
			variable: 'lightbox-button-background-color',
			type: 'color:default',
		},

		{
			selector: '.woocommerce-product-gallery__trigger',
			variable: 'lightbox-button-hover-background-color',
			type: 'color:hover',
		},
	],
})