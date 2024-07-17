import { onDocumentLoaded } from '../../helpers'
import ctEvents from 'ct-events'
import $ from 'jquery'

import './handle-events'

function isTouchDevice() {
	try {
		document.createEvent('TouchEvent')
		return true
	} catch (e) {
		return false
	}
}

export const wooEntryPoints = [
	{
		els: 'body.single-product .woocommerce-product-gallery',
		load: () => import('./single-product-gallery'),
		trigger: [
			{
				id: 'hover-with-click',
			},
		],
	},

	{
		els: 'form.variations_form',
		condition: () =>
			!!document.querySelector(
				'.woocommerce-product-gallery .ct-media-container'
			),
		load: () => import('./variable-products'),
		...(isTouchDevice() ||
		document.querySelectorAll(
			'form.variations_form[data-product_variations="false"]'
		)
			? {}
			: {
					trigger: ['hover'],
			  }),
	},

	{
		els: '.quantity .ct-increase, .quantity .ct-decrease',
		load: () => import('./quantity-input'),
		trigger: ['click'],
	},

	{
		els: '.ct-cart-auto-update .qty',
		load: () => import('./quantity-input'),
		trigger: ['change'],
	},

	{
		els: () => [...document.querySelectorAll('.ct-ajax-add-to-cart .cart')],
		load: () => import('./add-to-cart-single'),
		trigger: ['submit'],
	},

	{
		els: '.ct-header-cart, .ajax_add_to_cart, .ct-ajax-add-to-cart',
		load: () => import('./mini-cart'),
		events: ['ct:header:update'],
		trigger: ['hover-with-touch'],
	},

	{
		els: '#woo-cart-panel .qty',
		trigger: ['change'],
		load: () => import('./quantity-update'),
	},
]
