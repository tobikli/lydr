import md5 from 'md5'

// Stable JSON serialization
// Props to: https://github.com/fraunhoferfokus/JSum
function serialize(obj) {
	if (Array.isArray(obj)) {
		return `[${obj.map((el) => serialize(el)).join(',')}]`
	} else if (typeof obj === 'object' && obj !== null) {
		let acc = ''
		const keys = Object.keys(obj).sort()
		acc += `{${JSON.stringify(keys)}`
		for (let i = 0; i < keys.length; i++) {
			acc += `${serialize(obj[keys[i]])},`
		}
		return `${acc}}`
	}

	return `${JSON.stringify(obj)}`
}

export const getStableJsonKey = (input) => {
	return md5(serialize(input))
}
