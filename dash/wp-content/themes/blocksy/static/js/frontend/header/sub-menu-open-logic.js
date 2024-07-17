import { mountMenuLevel } from './menu'

export const mount = (menu) => {
	if (window.wp && wp && wp.customize && wp.customize('active_theme')) {
		const maybeMegaMenuWithCustomWidth = [...menu.children].find((el) =>
			el.matches('.ct-mega-menu:not(.ct-mega-menu-custom-width)')
		)

		if (maybeMegaMenuWithCustomWidth) {
			const submenu =
				maybeMegaMenuWithCustomWidth.querySelector('.sub-menu')

			submenu.style.left = `${
				Math.round(
					maybeMegaMenuWithCustomWidth
						.closest('[class*="ct-container"]')
						.firstElementChild.getBoundingClientRect().x
				) -
				Math.round(
					maybeMegaMenuWithCustomWidth
						.closest('nav')
						.getBoundingClientRect().x
				)
			}px`
		}
	}

	mountMenuLevel(menu, { startPosition: 'left' })
}
