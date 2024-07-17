import { isTouchDevice } from '../helpers/is-touch-device'
import { isIosDevice } from '../helpers/is-ios-device'

const isRtl = () => document.querySelector('html').dir === 'rtl'

const isEligibleForSubmenu = (el) =>
	el.className.includes('animated-submenu') &&
	(!el.parentNode.classList.contains('menu') ||
		(el.className.indexOf('ct-mega-menu') === -1 &&
			el.parentNode.classList.contains('menu')))

const getAllParents = (a) => {
	var els = []

	while (a) {
		els.unshift(a)
		a = a.parentNode
	}

	return els
}

function furthest(el, s) {
	var nodes = []

	while (el.parentNode) {
		if (
			el.parentNode &&
			el.parentNode.matches &&
			el.parentNode.matches(s)
		) {
			nodes.push(el.parentNode)
		}

		el = el.parentNode
	}

	return nodes[nodes.length - 1]
}

const getAllParentsUntil = (el, parent) => {
	const parents = []

	while (el.parentNode !== parent) {
		parents.push(el.parentNode)
		el = el.parentNode
	}

	return parents
}

const reversePlacementIfNeeded = (placement) => {
	if (!isRtl()) {
		return placement
	}

	if (placement === 'left') {
		return 'right'
	}

	if (placement === 'right') {
		return 'left'
	}

	return placement
}

const getPreferedPlacementFor = (el) => {
	let farmost = furthest(el, 'li.menu-item')

	if (!farmost) {
		return reversePlacementIfNeeded('right')
	}

	const submenusWithParents = [...farmost.querySelectorAll('.sub-menu')].map(
		(el) => {
			return { el, parents: getAllParentsUntil(el, farmost) }
		}
	)

	if (submenusWithParents.length === 0) {
		return reversePlacementIfNeeded('right')
	}

	const submenusWithParentsSorted = submenusWithParents
		.sort((a, b) => a.parents.length - b.parents.length)
		.reverse()

	const submenuWithMostParents = submenusWithParentsSorted[0]

	const allSubmenus = [
		...submenuWithMostParents.parents.filter((el) =>
			el.matches('.sub-menu')
		),

		...[submenuWithMostParents.el],
	]

	const allSubmenusAlignedWidth = allSubmenus.reduce((acc, el, index) => {
		const style = getComputedStyle(el)

		return (
			acc +
			el.getBoundingClientRect().width +
			(index === 0
				? 0
				: parseFloat(
						style.getPropertyValue(
							'--dropdown-horizontal-offset'
						) || '5px'
				  ))
		)
	}, 0)

	const farmostRect = farmost.getBoundingClientRect()

	let willItFitToTheLeft = allSubmenusAlignedWidth < farmostRect.right

	let willItFitToTheRight =
		innerWidth - farmostRect.left > allSubmenusAlignedWidth

	if (farmost.matches('.animated-submenu-inline')) {
		willItFitToTheRight =
			innerWidth - farmostRect.left - farmostRect.width >
			allSubmenusAlignedWidth
	}

	if (isRtl()) {
		if (!willItFitToTheLeft && !willItFitToTheRight) {
			return 'left'
		}

		return willItFitToTheLeft ? 'left' : 'right'
	}

	if (!willItFitToTheLeft && !willItFitToTheRight) {
		return 'right'
	}

	return willItFitToTheRight ? 'right' : 'left'
}

const computeItemSubmenuFor = (
	reference,
	{
		// left -- 1st level menu items
		// end  -- submenus
		startPosition = 'end',
	}
) => {
	const menu = reference.querySelector('.sub-menu')
	const placement = getPreferedPlacementFor(menu)

	const { left, width, right } = menu.getBoundingClientRect()

	let futurePlacement = placement
	let referenceRect = reference.getBoundingClientRect()

	if (placement === 'left') {
		let referencePoint =
			startPosition === 'end' ? referenceRect.left : referenceRect.right

		if (referencePoint - width < 0) {
			futurePlacement = 'right'
		}
	}

	if (placement === 'right') {
		let referencePoint =
			startPosition === 'end' ? referenceRect.right : referenceRect.left

		if (referencePoint + width > innerWidth) {
			futurePlacement = 'left'
		}
	}

	reference.dataset.submenu = futurePlacement

	reference.addEventListener('click', () => {})
}

const openSubmenu = (e) => {
	const li = e.target.closest('li')

	if (li.__closeSubmenuTimer__) {
		clearTimeout(li.__closeSubmenuTimer__)
		li.__closeSubmenuTimer__ = null
	}

	li.classList.add('ct-active')

	let childIndicator = [...li.children].find((el) =>
		el.matches('.ct-toggle-dropdown-desktop-ghost')
	)

	if (!childIndicator) {
		childIndicator = li.firstElementChild
	}

	if (childIndicator) {
		childIndicator.setAttribute('aria-expanded', 'true')
		if (childIndicator.tagName.toLowerCase() === 'button') {
			childIndicator.setAttribute(
				'aria-label',
				ct_localizations.collapse_submenu
			)
		}
	}

	if (!isEligibleForSubmenu(li)) {
		return
	}

	const menu = li.querySelector('.sub-menu')

	mountMenuLevel(menu)

	if (menu.closest('[data-interaction="hover"]')) {
		menu.parentNode.addEventListener(
			'mouseleave',
			() => {
				;[...menu.children]
					.filter((el) => isEligibleForSubmenu(el))
					.map((el) => el.removeAttribute('data-submenu'))
			},
			{ once: true }
		)
	}
}

const closeSubmenu = (e) => {
	if (!e.target) {
		return
	}

	const li = e.target.closest('li')
	li.classList.remove('ct-active')

	let childIndicator = [...li.children].find((el) =>
		el.matches('.ct-toggle-dropdown-desktop-ghost')
	)

	if (!childIndicator) {
		childIndicator = li.firstElementChild
	}

	if (childIndicator) {
		childIndicator.setAttribute('aria-expanded', 'false')

		if (childIndicator.tagName.toLowerCase() === 'button') {
			childIndicator.setAttribute(
				'aria-label',
				ct_localizations.expand_submenu
			)
		}

		if (e.focusOnIndicator) {
			childIndicator.focus({
				focusVisible: true,
			})
		}
	}

	li.__closeSubmenuTimer__ = setTimeout(() => {
		li.__closeSubmenuTimer__ = null
		;[...li.querySelectorAll('[data-submenu]')].map((el) => {
			el.removeAttribute('data-submenu')
		})
		;[...li.querySelectorAll('.ct-active')].map((el) => {
			el.classList.remove('ct-active')
		})
	}, 30)
}

const mountMenuForElement = (el, args = {}) => {
	if (el.classList.contains('ct-mega-menu-custom-width')) {
		const menu = el.querySelector('.sub-menu')

		const elRect = el.getBoundingClientRect()
		const menuRect = menu.getBoundingClientRect()

		let centerFits =
			elRect.left + elRect.width / 2 > menuRect.width / 2 &&
			innerWidth - (elRect.left + elRect.width / 2) > menuRect.width / 2

		if (!centerFits) {
			const placement = getPreferedPlacementFor(menu)

			let offset = 0

			let edgeOffset = 15

			if (placement === 'right') {
				offset = `${
					Math.round(
						elRect.left - (innerWidth - menuRect.width) + edgeOffset
					) * -1
				}px`

				if (!(elRect.left + elRect.width / 2 > menuRect.width / 2)) {
					offset = `${Math.round(elRect.left - edgeOffset) * -1}px`
				}
			}

			if (placement === 'left') {
				offset = `${
					Math.round(innerWidth - elRect.right - edgeOffset) * -1
				}px`
			}

			el.dataset.submenu = placement

			menu.style.setProperty('--theme-submenu-inline-offset', offset)
		}
	}

	if (isEligibleForSubmenu(el)) {
		computeItemSubmenuFor(el, args)
	}

	if (el.hasSubmenuEventListeners) {
		return
	}

	el.hasSubmenuEventListeners = true

	let hasClickInteraction = el.matches('[data-interaction*="click"] *')

	el.addEventListener('keydown', function (e) {
		if (e.keyCode == 27) {
			closeSubmenu({
				target: el.firstElementChild,
				focusOnIndicator: true,
			})
		}
	})

	el.addEventListener('focusout', (evt) => {
		if (el.contains(evt.relatedTarget)) {
			return
		}

		closeSubmenu({
			target: el.firstElementChild,
		})
	})

	if (!hasClickInteraction) {
		const handleMouseEnter = () => {
			// So that mouseenter event is catched before the open itself
			if (isIosDevice()) {
				openSubmenu({ target: el.firstElementChild })
			} else {
				requestAnimationFrame(() => {
					openSubmenu({ target: el.firstElementChild })
				})
			}

			// If first level
			if (!el.parentNode.classList.contains('.sub-menu')) {
				;[...el.parentNode.children]
					.filter((firstLevelEl) => firstLevelEl !== el)
					.map((firstLevelEl) => {
						closeSubmenu({
							target: firstLevelEl.firstElementChild,
						})
					})
			}

			el.addEventListener(
				'mouseleave',
				() => {
					closeSubmenu({ target: el.firstElementChild })
				},
				{ once: true }
			)
		}

		el.addEventListener('mouseenter', handleMouseEnter)

		if (el.matches(':hover')) {
			handleMouseEnter()
		}

		// On Android devices, allow only 2nd click to open the link.
		// First click will ensure the submenu is opened
		//
		// iOS has this behaviour out of the box.
		//
		// Important: only perform this for touch devices so that keyboard
		// users are not affected.
		if (isTouchDevice()) {
			el.addEventListener('click', (e) => {
				if (!el.classList.contains('ct-active')) {
					e.preventDefault()
				}
			})
		}
	}

	if (hasClickInteraction) {
		let itemTarget = el.matches('[data-interaction*="item"] *')
			? el.firstElementChild
			: el.firstElementChild.querySelector('.ct-toggle-dropdown-desktop')

		itemTarget.addEventListener('click', (e) => {
			e.preventDefault()

			if (e.target.closest('li').classList.contains('ct-active')) {
				closeSubmenu(e)
			} else {
				openSubmenu(e)

				if (isIosDevice()) {
					e.target.closest('li').addEventListener(
						'mouseleave',
						() => {
							closeSubmenu({
								target: el.firstElementChild,
							})
						},
						{ once: true }
					)
				}

				if (!e.target.hasDocumentListener) {
					e.target.hasDocumentListener = true
					// Add the event a bit later
					setTimeout(() => {
						document.addEventListener('click', (evt) => {
							if (!e.target.closest('li').contains(evt.target)) {
								closeSubmenu(e)
							}
						})
					})
				}
			}
		})
	}

	let childIndicator = [...el.children].find((el) =>
		el.matches('.ct-toggle-dropdown-desktop-ghost')
	)

	if (childIndicator) {
		childIndicator.addEventListener('click', (e) => {
			if (e.target.closest('li').classList.contains('ct-active')) {
				closeSubmenu(e)
			} else {
				openSubmenu(e)
			}
		})
	}
}

export const mountMenuLevel = (menuLevel, args = {}) => {
	;[...menuLevel.children]
		.filter((el) =>
			el.matches('.menu-item-has-children, .page_item_has_children')
		)
		.map((el) => {
			mountMenuForElement(el, args)
		})
}
