export const mountCF7Integration = () => {
	if (!window.wpcf7) {
		return
	}

	const forms = document.querySelectorAll('.wpcf7-form')

	if (!forms.length) {
		return
	}

	forms.forEach((form) => {
		const notInitializedParent = form.closest('.no-js')

		if (notInitializedParent) {
			wpcf7.init(form)

			notInitializedParent.classList.replace('no-js', 'js')
		}
	})
}
