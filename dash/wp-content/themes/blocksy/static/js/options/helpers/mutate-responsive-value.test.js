import {
	getKeysToMutate,
	mutateResponsiveValueWithScalar,
} from './mutate-responsive-value'

test('it properly gets keys to mutate', () => {
	expect({
		desktop: getKeysToMutate({
			changedKeys: [],
			device: 'desktop',
		}),

		tablet: getKeysToMutate({
			changedKeys: [],
			device: 'tablet',
		}),

		mobile: getKeysToMutate({
			changedKeys: [],
			device: 'mobile',
		}),
	}).toStrictEqual({
		desktop: ['desktop', 'tablet', 'mobile'],
		tablet: ['tablet', 'mobile'],
		mobile: ['mobile'],
	})
})

test('it properly gets keys to mutate desktop changed tablet', () => {
	expect(
		getKeysToMutate({
			changedKeys: ['tablet'],
			device: 'desktop',
		})
	).toStrictEqual(['desktop'])
})

test('it properly gets keys to mutate desktop changed mobile', () => {
	expect(
		getKeysToMutate({
			changedKeys: ['mobile'],
			device: 'desktop',
		})
	).toStrictEqual(['desktop', 'tablet'])
})

test('it properly gets keys to mutate mobile changed mobile', () => {
	expect(
		getKeysToMutate({
			changedKeys: ['mobile'],
			device: 'mobile',
		})
	).toStrictEqual(['mobile'])
})

test('it properly gets keys to mutate tablet changed tablet', () => {
	expect(
		getKeysToMutate({
			changedKeys: ['tablet'],
			device: 'tablet',
		})
	).toStrictEqual(['tablet', 'mobile'])
})

test('it properly mutates a desktop value', () => {
	expect(
		mutateResponsiveValueWithScalar({
			scalarValue: 1,

			responsiveValue: {
				desktop: 2,
				tablet: 2,
				mobile: 2,
			},

			device: 'desktop',
		})
	).toStrictEqual({
		desktop: 1,
		tablet: 1,
		mobile: 1,
	})
})

test('it properly mutates a tablet value', () => {
	expect(
		mutateResponsiveValueWithScalar({
			scalarValue: 1,

			responsiveValue: {
				desktop: 2,
				tablet: 2,
				mobile: 2,
			},

			device: 'tablet',
		})
	).toStrictEqual({
		desktop: 2,
		tablet: 1,
		mobile: 1,

		__changed: ['tablet'],
	})
})

test('it properly mutates a mobile value', () => {
	expect(
		mutateResponsiveValueWithScalar({
			scalarValue: 1,

			responsiveValue: {
				desktop: 2,
				tablet: 2,
				mobile: 2,
			},

			device: 'mobile',
		})
	).toStrictEqual({
		desktop: 2,
		tablet: 2,
		mobile: 1,

		__changed: ['mobile'],
	})
})

test('it properly mutates a desktop value with changed mobile', () => {
	expect(
		mutateResponsiveValueWithScalar({
			scalarValue: 1,

			responsiveValue: {
				desktop: 2,
				tablet: 2,
				mobile: 5,

				__changed: ['mobile'],
			},

			device: 'desktop',
		})
	).toStrictEqual({
		desktop: 1,
		tablet: 1,
		mobile: 5,

		__changed: ['mobile'],
	})
})

test('it properly mutates a desktop value with changed tablet', () => {
	expect(
		mutateResponsiveValueWithScalar({
			scalarValue: 1,

			responsiveValue: {
				desktop: 2,
				tablet: 5,
				mobile: 5,

				__changed: ['tablet'],
			},

			device: 'desktop',
		})
	).toStrictEqual({
		desktop: 1,
		tablet: 5,
		mobile: 5,

		__changed: ['tablet'],
	})
})

test('it properly mutates a mobile value with changed tablet', () => {
	expect(
		mutateResponsiveValueWithScalar({
			scalarValue: 1,

			responsiveValue: {
				desktop: 2,
				tablet: 5,
				mobile: 5,

				__changed: ['tablet'],
			},

			device: 'mobile',
		})
	).toStrictEqual({
		desktop: 2,
		tablet: 5,
		mobile: 1,

		__changed: ['tablet', 'mobile'],
	})
})

test('it properly mutates a tablet value with changed tablet', () => {
	expect(
		mutateResponsiveValueWithScalar({
			scalarValue: 1,

			responsiveValue: {
				desktop: 2,
				tablet: 5,
				mobile: 5,

				__changed: ['tablet'],
			},

			device: 'tablet',
		})
	).toStrictEqual({
		desktop: 2,
		tablet: 1,
		mobile: 1,

		__changed: ['tablet'],
	})
})

test('it properly mutates a desktop value with skipped tablet', () => {
	expect(
		mutateResponsiveValueWithScalar({
			scalarValue: 1,

			responsiveValue: {
				desktop: 2,
				tablet: 2,
				mobile: 2,
			},

			devices: ['desktop', 'mobile'],

			device: 'desktop',
		})
	).toStrictEqual({
		desktop: 1,
		tablet: 2,
		mobile: 1,
	})
})

test('it properly mutates a tablet value with skipped tablet', () => {
	expect(
		mutateResponsiveValueWithScalar({
			scalarValue: 1,

			responsiveValue: {
				desktop: 2,
				tablet: 2,
				mobile: 2,
			},

			devices: ['desktop', 'mobile'],

			device: 'tablet',
		})
	).toStrictEqual({
		desktop: 2,
		tablet: 2,
		mobile: 1,

		__changed: ['mobile'],
	})
})
