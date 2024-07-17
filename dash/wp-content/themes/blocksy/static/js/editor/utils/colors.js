export const getColorsDefaults = (colors) =>
	Object.keys(colors).reduce((acc, key) => {
		acc[key] = {
			type: 'string',
			default: colors[key],
		}

		return acc
	}, {})
