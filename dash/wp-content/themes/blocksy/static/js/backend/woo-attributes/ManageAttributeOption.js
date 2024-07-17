import $ from 'jquery'

import {
	useState,
	Fragment,
	createElement,
	useRef,
	render,
} from '@wordpress/element'
import { __ } from 'ct-i18n'
import OptionsPanel from '../../options/OptionsPanel'
import { getValueFromInput } from '../../options/helpers/get-value-from-input'
import { collectAttributes } from '.'
import AfterHeading from './AfterHeading'

const ManageAttributeOption = ({
	options,
	initialValue = {},
	input_id,
	input_name,
}) => {
	const attributes = collectAttributes()

	const [value, setValue] = useState(initialValue)
	const input = useRef()

	const getOptionsPanelFor = (attribute, term) => {
		let data = {}

		if (
			value[attribute.taxonomy] &&
			value[attribute.taxonomy].values &&
			value[attribute.taxonomy].values[term.value]
		) {
			data = value[attribute.taxonomy].values[term.value]
		}

		const swatchType =
			value[attribute.taxonomy]?.swatch_type ||
			(attribute.customAttr ? 'button' : 'inherit')

		options['inherit_options'].message = {
			...options['inherit_options'].message,
			text: options['inherit_options'].message.text
				.replace('{attribute_slug}', attribute.taxonomy)
				.replace('{tag_id}', term.value),
		}

		const swatchOptions =
			swatchType !== 'inherit'
				? {
						...(swatchType ? options[`${swatchType}_options`] : {}),
						...options['tooltip_options'],
				  }
				: {
						...options['inherit_options'],
				  }

		return (
			<OptionsPanel
				options={swatchOptions}
				value={getValueFromInput(swatchOptions, data)}
				onChange={(optionId, optionValue) => {
					const taxonomyValue = value[attribute.taxonomy] || {}

					const updatedValues = {
						...(taxonomyValue.values || {}),

						[term.value]: {
							...((taxonomyValue.values || {})[term.value] || {}),

							[optionId]: optionValue,
						},
					}

					const results = {
						...value,

						[attribute.taxonomy]: {
							...taxonomyValue,

							values: Object.keys(updatedValues).reduce(
								(result, val) => {
									if (
										!attribute.values.find(
											(a) => a.value === val
										)?.selected
									) {
										return result
									}

									return {
										...result,
										[val]: updatedValues[val],
									}
								},
								{}
							),
						},
					}

					setValue(results)
				}}
			/>
		)
	}

	const formatedOptions = attributes.map((attribute) => {
		return {
			title: attribute.name.trim(),
			type: 'accordion',

			options: attribute.values.reduce((result, term) => {
				if (!term.selected) {
					return result
				}

				return {
					...result,
					[term.value]: {
						type: 'accordion',
						title: term.label,

						options: {
							jsx: {
								type: 'jsx',
								design: 'none',
								render: () =>
									getOptionsPanelFor(attribute, term),
							},
						},
					},
				}
			}, {}),

			afterHeading: (item) => (
				<AfterHeading
					item={item}
					attribute={attribute}
					onChange={setValue}
					value={value}
				/>
			),

			id: attribute.taxonomy,
		}
	})

	const handleSave = (newValue) => {
		$('.product_data').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6,
			},
		})
		const body = new FormData()
		body.append('action', 'blocksy_save_attributes_swatches')
		body.append(
			'woocommerce_meta_nonce',
			document.querySelector('#woocommerce_meta_nonce').value
		)
		body.append('ct-woo-attributes-list', JSON.stringify(newValue))

		try {
			fetch(ct_localizations.ajax_url, {
				method: 'POST',
				body,
			}).then(() => {
				$('.product_data').unblock()
			})
		} catch (e) {
			$('.product_data').unblock()
		}
	}

	return (
		<Fragment>
			<input
				value={JSON.stringify(Array.isArray(value) ? {} : value)}
				onChange={() => {}}
				ref={input}
				name={input_name}
				id={input_id}
				type="hidden"
				data-options={JSON.stringify(options)}
			/>

			{attributes.length ? (
				<Fragment>
					<OptionsPanel
						options={formatedOptions}
						value={{}}
						onChange={() => {}}
					/>

					<div className="toolbar toolbar-buttons">
						<button
							type="button"
							className="button button-primary"
							onClick={() => handleSave(value)}>
							{__('Save changes', 'blocksy')}
						</button>

						<button
							type="button"
							class="button"
							onClick={() => {
								const message = __(
									'Are you sure you want to reset it to default setting?',
									'blocksy'
								)
								if (confirm(message) == true) {
									setValue({})
									handleSave({})
								}
							}}>
							{__('Reset to default', 'blocksy')}
						</button>
					</div>
				</Fragment>
			) : (
				<div className="toolbar toolbar-top">
					<div className="notice">
						<p>
							{__(
								'Please add some attributes in the Attributes tab to generate variations and save this product first.',
								'blocksy'
							)}
						</p>
					</div>
				</div>
			)}
		</Fragment>
	)
}

export default ManageAttributeOption
