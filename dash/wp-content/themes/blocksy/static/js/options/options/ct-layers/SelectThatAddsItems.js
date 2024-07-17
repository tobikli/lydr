import { createElement, useContext } from '@wordpress/element'
import { itemsThatAreNotAdded, LayersContext } from '../ct-layers'
import { normalizeCondition, matchValuesWithCondition } from 'match-conditions'
import Select from '../ct-select'

const SelectThatAddsItems = ({ value, option }) => {
	const notAddedItems = itemsThatAreNotAdded(value, option)

	const { currentlyPickedItem, setCurrentItem, addCurrentlySelectedItem } =
		useContext(LayersContext)

	if (notAddedItems.length <= 0) {
		return null
	}

	return (
		<div className="ct-add-layer-controls">
			<Select
				onChange={(currentlyPickedItem) =>
					setCurrentItem(currentlyPickedItem)
				}
				option={{
					search: true,
					choices: notAddedItems
						.filter((item) => {
							const { condition, values_source } =
								option.settings[item]

							let valueForCondition = value

							if (values_source === 'global') {
								valueForCondition = Object.keys(
									condition
								).reduce(
									(current, key) => ({
										...current,
										[key.split(':')[0]]: wp.customize(
											key.split(':')[0]
										)(),
									}),
									{}
								)
							}

							return (
								!condition ||
								matchValuesWithCondition(
									normalizeCondition(condition),
									valueForCondition
								)
							)
						})
						.map((key) => ({
							key,

							value: window._.template(
								(
									option.settings[key] || {
										label: key,
									}
								).label
							)({
								label: '',
							}),
						})),
					...(option.selectOption || {}),
				}}
				value={currentlyPickedItem || notAddedItems[0]}
			/>

			<button
				type="button"
				className="button button-primary"
				onClick={() => addCurrentlySelectedItem()}>
				<svg width="10" height="10" viewBox="0 0 17 17">
					<path d="M17,10h-7v7H7v-7H0V7h7V0h3v7h7V10z" />
				</svg>
			</button>
		</div>
	)
}

export default SelectThatAddsItems
