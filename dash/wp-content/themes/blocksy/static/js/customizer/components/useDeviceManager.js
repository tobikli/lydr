import {
	useState,
	useEffect,
	useCallback,
	createContext,
	createElement,
	useContext,
} from '@wordpress/element'

export const DeviceManagerStateContext = createContext()
export const DeviceManagerActionsContext = createContext()

export const useDeviceManagerState = () => {
	const context = useContext(DeviceManagerStateContext)

	return context || { currentView: 'desktop' }
}

export const useDeviceManagerActions = () => {
	const context = useContext(DeviceManagerActionsContext)
	return context || {}
}

export const getCurrentDevice = (select = null) => {
	if (wp.customize && wp.customize.previewedDevice) {
		return wp.customize.previewedDevice()
	}

	let maybeSelect = select

	if (wp.data && wp.data.select) {
		maybeSelect = wp.data.select
	}

	let device = 'desktop'

	if (maybeSelect) {
		if (
			maybeSelect('core/editor') &&
			maybeSelect('core/editor').getDeviceType
		) {
			device = maybeSelect('core/editor').getDeviceType().toLowerCase()
		} else {
			if (
				maybeSelect('core/edit-post') &&
				maybeSelect(
					'core/edit-post'
				).__experimentalGetPreviewDeviceType()
			) {
				device = maybeSelect('core/edit-post')
					.__experimentalGetPreviewDeviceType()
					.toLowerCase()
			}
		}
	}

	return device
}

export const useDeviceManager = (args = {}) => {
	const { withTablet = true } = args

	const [currentView, setCurrentView] = useState(
		wp.customize && wp.customize.previewedDevice
			? wp.customize.previewedDevice()
			: 'desktop'
	)

	const listener = () => {
		setCurrentView(
			wp.customize && wp.customize.previewedDevice
				? wp.customize.previewedDevice()
				: 'desktop'
		)
	}

	useEffect(() => {
		if (!wp.customize) return
		setTimeout(() => wp.customize.previewedDevice.bind(listener), 1000)

		return () => {
			if (!wp.customize) return
			wp.customize.previewedDevice.unbind(listener)
		}
	}, [])

	return [
		withTablet
			? currentView
			: currentView === 'tablet'
			? 'mobile'
			: currentView,
		(device) => {
			setCurrentView(device)
			wp.customize && wp.customize.previewedDevice.set(device)
		},
	]
}

export const DeviceManagerProvider = ({ children }) => {
	const [currentView, setDevice] = useDeviceManager()

	return (
		<DeviceManagerStateContext.Provider value={{ currentView }}>
			<DeviceManagerActionsContext.Provider value={{ setDevice }}>
				{children}
			</DeviceManagerActionsContext.Provider>
		</DeviceManagerStateContext.Provider>
	)
}
