import { whenTransitionEnds } from './helpers/when-transition-ends'

const computeHasMarginManipulation = (childrenWrap, el) => {
	const m = getComputedStyle(el).marginBottom

	// return parseFloat(m) > 0 && el.nextElementSibling === childrenWrap
	return el.nextElementSibling === childrenWrap
}

// support 3 strategies:
// 1. data-target on el
// 2. when no data-target, find aria-hidden element on the same level
// 3. when no data-target attribute is present, find aria-hidden element on
//    upper level.
//
// If you can't find target — do nothing.
const findTargetForEl = (el) => {
	// case 1
	if (el.hasAttribute('data-target')) {
		return document.querySelector(el.getAttribute('data-target'))
	}

	// case 2
	const maybeCurrentLevel = el.parentNode.querySelector('[aria-hidden]')

	if (maybeCurrentLevel) {
		return maybeCurrentLevel
	}

	// case 3
	const maybeUpperLevel =
		el.parentNode.parentNode.querySelector('[aria-hidden]')

	if (maybeUpperLevel) {
		return maybeUpperLevel
	}

	// When nothing found -- do nothing
	return null
}

const showContent = (childrenWrap, el) => {
	const hasMarginManipulation = computeHasMarginManipulation(childrenWrap, el)

	if (hasMarginManipulation) {
		el.setAttribute('aria-expanded', 'true')
	}

	requestAnimationFrame(() => {
		if (hasMarginManipulation) {
			const m = getComputedStyle(el).marginBottom

			// Revert back aria-expanded
			el.setAttribute('aria-expanded', 'false')

			childrenWrap.firstElementChild.prevStyle =
				childrenWrap.firstElementChild.getAttribute('style')

			childrenWrap.firstElementChild.style.marginTop = m
		}

		childrenWrap.setAttribute('aria-hidden', 'false')

		const actualHeight = childrenWrap.getBoundingClientRect().height

		childrenWrap.style.height = '0px'
		childrenWrap.style.opacity = '0'

		requestAnimationFrame(() => {
			childrenWrap.classList.add('is-animating')

			requestAnimationFrame(() => {
				childrenWrap.style.height = `${actualHeight}px`
				childrenWrap.style.opacity = '1'

				whenTransitionEnds(childrenWrap, () => {
					childrenWrap.classList.remove('is-animating')
					childrenWrap.removeAttribute('style')

					if (hasMarginManipulation) {
						if (childrenWrap.firstElementChild.prevStyle) {
							childrenWrap.firstElementChild.style =
								childrenWrap.firstElementChild.prevStyle
						} else {
							childrenWrap.firstElementChild.removeAttribute(
								'style'
							)
						}
					}

					el.setAttribute('aria-expanded', 'true')
				})
			})
		})
	})
}

const hideContent = (childrenWrap, el, cb) => {
	const hasMarginManipulation = computeHasMarginManipulation(childrenWrap, el)

	if (hasMarginManipulation) {
		const m = getComputedStyle(el).marginBottom

		childrenWrap.firstElementChild.prevStyle =
			childrenWrap.firstElementChild.getAttribute('style')

		childrenWrap.firstElementChild.style.marginTop = m

		if (el.getAttribute('style')) {
			el.prevStyle = el.getAttribute('style')
		}

		el.style.marginBottom = '0px'
	}

	requestAnimationFrame(() => {
		const actualHeight = childrenWrap.getBoundingClientRect().height

		childrenWrap.style.height = `${actualHeight}px`
		childrenWrap.style.opacity = '1'
		childrenWrap.classList.add('is-animating')

		requestAnimationFrame(() => {
			childrenWrap.style.height = '0px'
			childrenWrap.style.opacity = '0'

			whenTransitionEnds(childrenWrap, () => {
				childrenWrap.classList.remove('is-animating')

				childrenWrap.removeAttribute('style')

				if (hasMarginManipulation) {
					if (childrenWrap.firstElementChild.prevStyle) {
						childrenWrap.firstElementChild.style =
							childrenWrap.firstElementChild.prevStyle
					} else {
						childrenWrap.firstElementChild.removeAttribute('style')
					}

					if (el.prevStyle) {
						el.style = el.prevStyle
					} else {
						el.removeAttribute('style')
					}
				}

				cb()
			})
		})
	})
}

export const mount = (el, { event }) => {
	event.stopPropagation()
	event.preventDefault()

	const targetEl = findTargetForEl(el)

	if (!targetEl) {
		return
	}

	const isExpanded = targetEl.getAttribute('aria-hidden') === 'false'

	if (isExpanded) {
		hideContent(targetEl, el, () => {
			el.setAttribute('aria-expanded', 'false')
			targetEl.setAttribute('aria-hidden', 'true')
		})

		return
	}

	if (typeof el.dataset.closeOthers !== 'undefined') {
		const parent = el.closest('.ct-accordion-tab').parentNode
		const toggles = parent.querySelectorAll(
			'.ct-expandable-trigger[aria-expanded="true"]'
		)

		toggles.forEach((toggle) => {
			const targetEl = findTargetForEl(toggle)

			if (targetEl) {
				hideContent(targetEl, el, () => {
					toggle.setAttribute('aria-expanded', 'false')
					targetEl.setAttribute('aria-hidden', 'true')
				})
			}
		})
	}

	showContent(targetEl, el)
}
