export const getKeysToMutate = (args = {}) => {
	args = {
		changedKeys: [],

		device: 'desktop',

		devices: ['desktop', 'tablet', 'mobile'],

		...args,
	}

	const scopedDevices = args.devices.slice(args.devices.indexOf(args.device))

	const devicesWithLeader = scopedDevices.map((device, index) => {
		let leader =
			scopedDevices[
				Math.max(
					...[scopedDevices[0], ...args.changedKeys]
						.map((device) => scopedDevices.indexOf(device))
						.filter((leaderIndex) => leaderIndex <= index)
				)
			]

		return {
			device,
			leader,
		}
	})

	return devicesWithLeader
		.filter(({ leader }) => leader === args.device)
		.map(({ device }) => device)
}

export const mutateResponsiveValueWithScalar = (args = {}) => {
	args = {
		scalarValue: '__empty__',
		responsiveValue: '__empty__',

		device: 'desktop',
		devices: ['desktop', 'tablet', 'mobile'],

		...args,
	}

	if (args.scalarValue === '__empty__') {
		throw new Error('Scalar value is required')
	}

	if (args.responsiveValue === '__empty__') {
		throw new Error('Responsive value is required')
	}

	if (!args.devices.includes(args.device)) {
		args.device = args.devices[args.devices.length - 1]
	}

	const { __changed = [], ...responsiveValue } = args.responsiveValue

	let keysToMutate = getKeysToMutate({
		changedKeys: __changed,
		device: args.device,
		devices: args.devices,
	})

	let result = {
		...responsiveValue,
		...keysToMutate.reduce(
			(acc, key) => ({
				...acc,
				[key]: args.scalarValue,
			}),
			{}
		),
	}

	const changedKeys = [
		...__changed,
		...(args.device !== args.devices[0] ? [args.device] : []),
	]

	if (changedKeys.length > 0) {
		result.__changed = [...new Set(changedKeys)]
	}

	return result
}
