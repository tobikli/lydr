import { makeDeferred } from './make-deferred'
import { getStableJsonKey } from './get-stable-json-key'

let cache = {
	cacheKey: {
		// empty | loading | loaded
		state: 'empty',

		response: null,

		waitingForResponse: [],
	},
}

// Maybe this will be the definitive version of the cachedFetch function.
// We will have two strategies for handling concurrency:
//
// - abort previous requests and start a new one
// - queue requests and resolve them in order
//
// Also, don't deal anymore with cloning responses. This is a bad idea.
// Just resolve the json from the response once and cache it.
const cachedFetch = (url, body = {}) => {
	const cacheKey = getStableJsonKey({
		...body,
		url,
	})

	if (!cache[cacheKey]) {
		cache[cacheKey] = {
			state: 'empty',
			response: null,
			waitingForResponse: [],
		}
	}

	if (cache[cacheKey].state === 'loaded') {
		const deferred = makeDeferred()

		deferred.resolve(cache[cacheKey].response)

		return deferred.promise
	}

	if (cache[cacheKey].state === 'loading') {
		const deferred = makeDeferred()

		cache[cacheKey].waitingForResponse.push(deferred)

		return deferred.promise
	}

	// This is the first that triggered the fetch for that particular
	// cache key. If any other request comes in while this is loading,
	// we will add it to the waitingForResponse array and resolve it
	// once the request is done.
	if (cache[cacheKey].state === 'empty') {
		cache[cacheKey].state = 'loading'

		const deferred = makeDeferred()

		// TODO: expose options to the user
		fetch(url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify(body),
		})
			.then((response) => response.json())
			.then((response) => {
				cache[cacheKey].response = response
				;[deferred, ...cache[cacheKey].waitingForResponse].forEach(
					(deferred) => {
						deferred.resolve(cache[cacheKey].response)
					}
				)

				cache[cacheKey].waitingForResponse = []
				cache[cacheKey].state = 'loaded'
			})

		return deferred.promise
	}

	throw new Error('Invalid state', { cacheEntry: cache[cacheKey] })
}

export default cachedFetch
