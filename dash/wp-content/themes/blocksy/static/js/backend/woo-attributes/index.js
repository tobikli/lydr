import { createElement, render } from '@wordpress/element'
import ManageAttributeOption from './ManageAttributeOption'

export const collectAttributes = () => {
	return [...document.querySelectorAll('[name*=attribute_values]')]
		.filter((el) => {
			return el
				.closest('.woocommerce_attribute_data')
				.querySelector(
					'input.woocommerce_attribute_used_for_variations'
				).checked
		})
		.map((el) => {
			const values = []

			const attributeNameField = el
				.closest('tr')
				.querySelector('.attribute_name')
			let attributeName = ''
			let customAttr = false

			if (el.tagName === 'SELECT') {
				;[...el.querySelectorAll('option')].map((option) => {
					values.push({
						value: option.value,
						label: option.textContent,
						selected: option.selected,
					})
				})

				attributeName =
					attributeNameField.querySelector('strong').textContent
			}

			if (el.tagName === 'TEXTAREA') {
				el.value
					.split('|')
					.map((value) => value.trim())
					.map((value) => {
						values.push({
							value,
							label: value,
							selected: true,
						})
					})
				customAttr = true
				attributeName = attributeNameField.querySelector('input').value
			}

			return {
				name: attributeName,
				taxonomy:
					el.closest('[data-taxonomy]').dataset.taxonomy ||
					attributeName,
				values,
				customAttr,
			}
		})
}

const initWooVariation = (variationWrapper) => {
	if (!variationWrapper) {
		return
	}

	const input = document.querySelector('#ct-woo-attributes-list')
	const options = JSON.parse(input.dataset.options)

	const initialValue = input.value ? JSON.parse(input.value) : {}

	variationWrapper.closest('.options_group').innerHtml = ''

	render(
		<ManageAttributeOption
			options={options}
			initialValue={initialValue}
			input_id={input.id}
			input_name={input.name}
		/>,
		variationWrapper.closest('.options_group')
	)
}

export const initAllWooAttributesOptions = () => {
	initWooVariation(document.querySelector('#ct-woo-attributes-list'))
}
