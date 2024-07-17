import { Fragment, createElement } from '@wordpress/element'
import { __ } from 'ct-i18n'

const AfterHeading = ({ item, attribute, onChange, value }) => {
	const dopdownOptions = [
		{
			label: __('Color', 'blocksy'),
			value: 'color',
		},
		{
			label: __('Image', 'blocksy'),
			value: 'image',
		},
		{
			label: __('Button', 'blocksy'),
			value: 'button',
		},
		{
			label: __('Mixed', 'blocksy'),
			value: 'mixed',
		},
		{
			label: __('Select', 'blocksy'),
			value: 'select',
		},
	]

	if (!attribute.customAttr) {
		dopdownOptions.unshift({
			label: __('Inherit', 'blocksy'),
			value: 'inherit',
		})
	}

	const handleChange = (e) => {
		e.stopPropagation()

		const taxonomy = value[attribute.taxonomy] || {}

		onChange({
			...value,

			[attribute.taxonomy]: {
				...taxonomy,

				swatch_type: e.target.value,
			},
		})
	}

	return (
		<Fragment>
			{__('Type', 'blocksy')}
			<select
				id={`ct-variation-type-${attribute.taxonomy}`}
				name={`ct-variation-type-${attribute.taxonomy}`}
				onChange={handleChange}>
				{dopdownOptions.map(({ label, value: val }) => {
					return (
						<option
							value={val}
							selected={
								value[attribute.taxonomy]?.swatch_type ===
									val ||
								(attribute.customAttr
									? val === 'button'
									: false)
							}>
							{label}
						</option>
					)
				})}
			</select>
		</Fragment>
	)
}

export default AfterHeading
