import { createElement, useState, useCallback } from '@wordpress/element'
import OptionsPanel from '../OptionsPanel'
import classnames from 'classnames'

const Accordion = (props) => {
	const [currentItems, setCurrentItems] = useState([])

	const handleVisibility = useCallback(
		(id) => {
			if (currentItems.includes(id)) {
				setCurrentItems(currentItems.filter((item) => item !== id))
			} else {
				setCurrentItems([...currentItems, id])
			}
		},
		[currentItems]
	)

	return (
		<div className="ct-option-accordion">
			{props.renderingChunk.map((singleItem) => {
				const isActive = currentItems.includes(singleItem.id)

				return (
					<div
						key={singleItem.id}
						className={classnames('ct-accordion-item', {
							active: isActive,
						})}>
						<h3
							className="ct-accordion-heading"
							onClick={(e) => {
								if (e.target.tagName === 'SELECT') {
									return
								}

								handleVisibility(singleItem.id)
							}}>
							{singleItem.title
								? singleItem.title
								: singleItem.id}

							<span className="ct-accordion-item-controls">
								{singleItem.afterHeading
									? singleItem.afterHeading(singleItem)
									: null}

								<svg
									width="8"
									height="8"
									fill="currentColor"
									viewBox="0 0 20 15">
									<path d="M20 0 9.92 15 0 0h20Z" />
								</svg>
							</span>
						</h3>

						<div className="ct-accordion-item-content">
							<div className="ct-accordion-options-container">
								<OptionsPanel
									purpose={props.purpose}
									onChange={(key, val) =>
										props.onChange(key, val)
									}
									options={singleItem.options}
									value={props.value}
								/>
							</div>
						</div>
					</div>
				)
			})}
		</div>
	)
}

export default Accordion
