import { loadStyle } from '../helpers'
import { isTouchDevice } from './helpers/is-touch-device'

const mountPopper = (reference) => {
	if (!reference.nextElementSibling) {
		return
	}

	if (reference.nextElementSibling.popperMounted) {
		return
	}

	reference.nextElementSibling.popperMounted = true

	// Prevent focus on click for non-touch devices when element is tabbable
	// but not clickable.
	if (!isTouchDevice() && reference.tabIndex === 0) {
		reference.addEventListener('mousedown', (e) => {
			e.preventDefault()
		})
	}

	const target = reference.nextElementSibling

	const referenceRect = reference.getBoundingClientRect()
	const targetRect = target.getBoundingClientRect()

	let initialPlacement =
		referenceRect.left > innerWidth / 2 ? 'left' : 'right'

	let placement = initialPlacement

	const offset = parseFloat(
		getComputedStyle(target).getPropertyValue(
			'--theme-submenu-inline-offset'
		) || -20
	)

	if (targetRect.width > innerWidth - referenceRect.left + offset) {
		placement = 'left'
	}

	if (referenceRect.right - offset - targetRect.width < 0) {
		placement = 'right'
	}

	if (
		referenceRect.left + targetRect.width > innerWidth &&
		referenceRect.right - targetRect.width < 0
	) {
		placement = initialPlacement

		let offset = 0
		let edgeOffset = 20

		if (placement === 'left') {
			offset = innerWidth - referenceRect.right - edgeOffset
		}

		if (placement === 'right') {
			offset = referenceRect.left - edgeOffset
		}

		offset = Math.round(offset)

		target.style.setProperty(
			'--theme-submenu-inline-offset',
			`${offset * -1}px`
		)
	}

	target.dataset.placement = placement

	const observer = new MutationObserver((mutations) => {
		const maybeMutation = mutations.find(
			(mutation) =>
				mutation.type === 'childList' &&
				[...mutation.removedNodes].find((el) => el === target)
		)

		if (!maybeMutation) {
			return
		}

		observer.disconnect()

		mountPopper(reference)
	})

	observer.observe(target.parentNode, { childList: true })
}

export const mount = (reference) => {
	Promise.all(
		ct_localizations.dynamic_styles_selectors
			.filter(
				(styleDescriptor) =>
					!!reference.closest(styleDescriptor.selector)
			)
			.map(({ url }) => loadStyle(url))
	).then(() => mountPopper(reference))
}
