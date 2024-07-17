import ctEvents from 'ct-events'
import { getItemsDistribution } from './get-items-distribution'
import { getCurrentScreen } from '../helpers/current-screen'

const isEligibleForSubmenu = (el) =>
	el.className.includes('animated-submenu') &&
	(!el.parentNode.classList.contains('menu') ||
		(el.className.indexOf('ct-mega-menu') === -1 &&
			el.parentNode.classList.contains('menu')))

let cacheInfo = {}

export const getCacheFor = (id) => cacheInfo[id]

const getNavRootEl = (nav) => {
	return Array.from(nav.children).filter(
		(t) => !t.matches('link') && !t.matches('style')
	)[0]
}

const maybeCreateMoreItemsFor = (nav, onDone) => {
	if (nav.querySelector('.more-items-container')) {
		onDone(nav.querySelector('.more-items-container'))
		return
	}

	const moreContainer = document.createElement('li')

	moreContainer.classList.add('menu-item-has-children')
	moreContainer.classList.add('more-items-container')
	moreContainer.classList.add('animated-submenu-block')
	moreContainer.classList.add('menu-item')
	moreContainer.role = 'none'

	moreContainer.insertAdjacentHTML(
		'afterbegin',
		`<a href="#" class="ct-menu-link" role="menuitem">
      ${ct_localizations.more_text}
      <span class="ct-toggle-dropdown-desktop">
        <svg class="ct-icon" width="8" height="8" viewBox="0 0 15 15">
            <path d="M2.1,3.2l5.4,5.4l5.4-5.4L15,4.3l-7.5,7.5L0,4.3L2.1,3.2z"></path>
        </svg>
      </span>
    </a>
    <button class="ct-toggle-dropdown-desktop-ghost" aria-expanded="false" aria-label="${ct_localizations.expand_submenu}" role="menuitem"></button>
    <ul class="sub-menu" role="menu"></ul>`
	)

	getNavRootEl(nav).appendChild(moreContainer)
	onDone && onDone(moreContainer)
}

const countElWidth = (el) => {
	if (el.firstElementChild.matches('a') && !el.querySelector('svg')) {
		const a = el.firstElementChild
		a.innerHTML = `<span>${a.innerHTML}</span>`

		const props = window.getComputedStyle(a, null)

		let extraWidth = 0

		let parentComputedStyle = window.getComputedStyle(el.parentNode, null)

		if (parentComputedStyle.gap !== 'normal') {
			extraWidth = parseFloat(parentComputedStyle.gap)

			if (
				el.parentNode.firstElementChild === el ||
				el === el.parentNode.lastElementChild
			) {
				extraWidth = extraWidth / 2
			}
		}

		let actualWidth =
			a.firstElementChild.getBoundingClientRect().width +
			parseInt(props.getPropertyValue('padding-left'), 10) +
			parseInt(props.getPropertyValue('padding-right'), 10) +
			(a.querySelector('.ct-toggle-dropdown-desktop') ? 13 : 0) +
			extraWidth

		a.innerHTML = a.firstElementChild.innerHTML

		return actualWidth
	}

	return el.firstElementChild.getBoundingClientRect().width
}

const computeMoreItemWidth = (nav) => {
	let data = null

	let hadMoreItems = !!nav.querySelector('.more-items-container')

	maybeCreateMoreItemsFor(nav, (el) => {
		data = countElWidth(el)

		if (!hadMoreItems) {
			el.remove()
		}
	})

	return data
}

const computeItemsWidth = (nav) => {
	return Array.from(getNavRootEl(nav).children)
		.filter(
			(el) =>
				!el.classList.contains('.more-items-container') &&
				el.firstElementChild
		)
		.map((el) => countElWidth(el))
}

const maybeMakeCacheForAllNavs = (nav) => {
	let baseContainer = nav.closest('[class*="ct-container"]')

	let allNavs = baseContainer.querySelectorAll('[data-id*="menu"]')

	;[...allNavs].map((nav) => {
		if (!nav.__id) {
			nav.__id = Math.random()
		}

		if (cacheInfo[nav.__id]) {
			return
		}

		if (!getNavRootEl(nav)) {
			return
		}

		cacheInfo[nav.__id] = {
			el: nav,
			previousRenderedWidth: null,
			children: [
				...Array.from(getNavRootEl(nav).children).filter(
					(el) => !el.classList.contains('more-items-container')
				),

				...(getNavRootEl(nav).querySelector('.more-items-container')
					? [
							...getNavRootEl(nav).querySelector(
								'.more-items-container .sub-menu'
							).children,
					  ]
					: []),
			],
			itemsWidth: computeItemsWidth(nav),
			moreItemWidth: computeMoreItemWidth(nav),
		}

		nav.dataset.responsive = 'yes'
	})
}

new ResizeObserver(() => {
	const els = [
		...document.querySelectorAll(
			'header [data-device="desktop"] [data-id^="menu"][data-responsive]'
		),
	]

	els.map((nav) => mount(nav))
}).observe(document.body)

export const mount = (nav) => {
	if (!getNavRootEl(nav)) {
		return
	}

	maybeMakeCacheForAllNavs(nav)

	if (
		cacheInfo[nav.__id].previousRenderedWidth &&
		cacheInfo[nav.__id].previousRenderedWidth === window.innerWidth
	) {
		return
	}

	cacheInfo[nav.__id].previousRenderedWidth = window.innerWidth

	if (getCurrentScreen() !== 'desktop') {
		return
	}

	let { fit, notFit } = getItemsDistribution(nav)

	if (notFit.length === 0) {
		if (nav.querySelector('.more-items-container')) {
			fit.map((el) => {
				getNavRootEl(nav).insertBefore(
					el,
					nav.querySelector('.more-items-container')
				)

				if (
					el.matches(
						'.menu-item-has-children, .page_item_has_children'
					)
				) {
					el.classList.remove('animated-submenu-inline')
					el.classList.add('animated-submenu-block')
				}

				Array.from(
					el.querySelectorAll(
						'.menu-item-has-children, .page_item_has_children'
					)
				)
					.filter((el) => !!el.closest('[class*="ct-mega-menu"]'))
					.map((el) => {
						el.classList.remove(
							'animated-submenu-block',
							'animated-submenu-inline'
						)
					})
			})

			nav.querySelector('.more-items-container').remove()
		}

		resetSubmenus()
		ctEvents.trigger('ct:header:refresh-menu-submenus')

		return
	}

	if (!document.querySelector('header [data-device="desktop"]')) {
		return
	}

	maybeCreateMoreItemsFor(nav, () => {
		notFit.map((el) => {
			nav.querySelector('.more-items-container .sub-menu').appendChild(el)

			if (
				el.matches('.menu-item-has-children, .page_item_has_children')
			) {
				el.classList.add('animated-submenu-inline')
				el.classList.remove('animated-submenu-block')
			}
		})

		fit.map((el) => {
			getNavRootEl(nav).insertBefore(
				el,
				nav.querySelector('.more-items-container')
			)

			if (
				el.matches('.menu-item-has-children, .page_item_has_children')
			) {
				el.classList.remove('animated-submenu-inline')
				el.classList.add('animated-submenu-block')
			}

			Array.from(
				el.querySelectorAll(
					'.menu-item-has-children, .page_item_has_children'
				)
			)
				.filter((el) => !!el.closest('[class*="ct-mega-menu"]'))
				.map((el) => {
					el.classList.remove(
						'animated-submenu-block',
						'animated-submenu-inline'
					)
				})
		})

		resetSubmenus()
		ctEvents.trigger('ct:header:refresh-menu-submenus')
	})
}

const resetSubmenus = () => {
	;[
		...document.querySelectorAll(
			'header [data-device="desktop"] [data-id*="menu"] > .menu'
		),
	].map((menu) => {
		;[...menu.children]
			.filter((el) => el.querySelector('.sub-menu'))
			.filter((el) => isEligibleForSubmenu(el))
			.map((el) => el.querySelector('.sub-menu'))
			.map((menu) => {
				;[...menu.querySelectorAll('[data-submenu]')].map((el) => {
					el.removeAttribute('data-submenu')
				})

				if (menu._popper) {
					menu._popper.destroy()
					menu._popper = null
				}
			})
	})
}
