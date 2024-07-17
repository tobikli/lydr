import $ from 'jquery'
import ctEvents from 'ct-events'

let mounted = false

let addedToCart = false

export const mount = (el, { event }) => {
	if (!$) return

	const maybeCartLink = el.querySelector('.ct-cart-item')

	if (
		maybeCartLink &&
		!maybeCartLink.classList.contains('ct-offcanvas-trigger') &&
		event &&
		event.type === 'touchstart'
	) {
		location.href = maybeCartLink.getAttribute('href')
		return
	}

	const selector = '.ct-header-cart, .ct-shortcuts-bar [data-id="cart"]'

	if (mounted) {
		return
	}

	mounted = true

	$(document.body).on('adding_to_cart', () =>
		[...document.querySelectorAll(selector)].map((cart) => {
			if (!cart.closest('.ct-shortcuts-bar')) {
				cart = cart.firstElementChild
			}

			cart.classList.remove('ct-added')
			cart.classList.add('ct-adding')
		})
	)

	$(document.body).on('wc_fragments_loaded', () => {
		setTimeout(() => ctEvents.trigger('blocksy:frontend:init'))
	})

	$(document.body).on('wc_cart_button_updated', (data, buttons) => {
		if (
			window.wc_add_to_cart_params &&
			window.wc_add_to_cart_params.i18n_view_cart_with_icon &&
			buttons &&
			buttons.length > 0
		) {
			const button = buttons[0]

			if (button.closest('[data-products]')) {
				button.nextElementSibling.innerHTML =
					wc_add_to_cart_params.i18n_view_cart_with_icon
			}
		}
	})

	const autoOpenCart = () => {
		;[...document.querySelectorAll(selector)].map((cart, index) => {
			if (index > 0) {
				return
			}

			if (
				!document.querySelector('#ct-compare-modal.active') &&
				!document.querySelector('.quick-view-modal.active') &&
				!document.querySelector('#woo-cart-panel.active') &&
				((!document.body.classList.contains('single-product') &&
					cart.querySelector('[data-auto-open*="archive"]')) ||
					(document.body.classList.contains('single-product') &&
						cart.querySelector('[data-auto-open*="product"]')))
			) {
				cart.querySelector('[data-auto-open]').focusDisabled = true
				cart.querySelector('[data-auto-open]').click()
			}
		})
	}

	$(document.body).on(
		'added_to_cart',
		(_, fragments, __, button, quantity) => {
			button = button[0]
			;[...document.querySelectorAll(selector)].map((cart, index) => {
				let elForOpen = cart

				if (!cart.closest('.ct-shortcuts-bar')) {
					elForOpen = cart.firstElementChild
				}

				elForOpen.classList.remove('ct-adding')
				elForOpen.classList.add('ct-added')
			})

			if (Object.keys(fragments).length > 0) {
				autoOpenCart()
			} else {
				addedToCart = true
			}
		}
	)

	$(document.body).on('wc_fragments_refreshed', () => {
		if (addedToCart) {
			autoOpenCart()
		}

		addedToCart = false
		setTimeout(() => {
			ctEvents.trigger('blocksy:frontend:init')
			clearCartContent()
		})
	})

	$(document).on('uael_quick_view_loader_stop', () => {
		ctEvents.trigger('ct:add-to-cart:quantity')
	})

	$(document).on('facetwp-loaded', () => {
		ctEvents.trigger('ct:custom-select:init')
	})

	const clearCartContent = () => {
		let maybeCartContent = document.querySelector(
			'.ct-header-cart .ct-cart-content'
		)

		if (maybeCartContent) {
			maybeCartContent.removeAttribute('style')
		}
	}

	$(document.body).on('wc_fragments_loaded', () => {
		setTimeout(() => {
			ctEvents.trigger('blocksy:frontend:init')

			clearCartContent()
		})
	})

	$(document.body).on('click', '.remove_from_cart_button', {}, (e) => {
		const maybeItem = e.target.closest('.woocommerce-mini-cart-item')

		if (maybeItem) {
			maybeItem.classList.add('processing')
		}
	})

	$(document.body).on('removed_from_cart', (_, __, ___, button) => {
		;[...document.querySelectorAll(selector)].map((cart) => {
			if (!button) return

			try {
				if (button[0].closest('.updating')) {
					button[0].closest('.updating').classList.remove('updating')
				}

				button[0].closest('li').remove()
			} catch (e) {}
		})
	})

	$(document.body).on('click', '.remove_from_cart_button', (e) => {
		if (!e.target.closest('[data-placement]')) {
			return
		}

		window.blocksySkippedFragments = [e.target.closest('[data-placement]')]
	})

	const originalReplaceWith = jQuery.fn.replaceWith

	jQuery.fn.replaceWith = function () {
		if (
			window.blocksySkippedFragments &&
			window.blocksySkippedFragments.includes(this[0])
		) {
			const div = document.createElement('div')
			div.innerHTML = arguments[0]
			this[0].innerHTML = div.firstElementChild.innerHTML
			;[...div.firstElementChild.attributes].map(({ name, value }) => {
				this[0].setAttribute(name, value)
			})

			return this
		}

		return originalReplaceWith.apply(this, arguments)
	}
}
