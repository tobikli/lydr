import { createElement, Component, Fragment } from '@wordpress/element'
import { __ } from 'ct-i18n'
import _ from 'underscore'

export default class ImageUploader extends Component {
	state = {}

	componentDidMount() {
		if (typeof parseInt(this.props.value) === 'number') {
			wp.media
				.attachment(parseInt(this.props.value))
				.fetch()
				.done((a) => {
					if (a.url) {
						this.props.onChange(a.url)
					}
				})
		}
	}

	/**
	 * Create a media modal select frame, and store it so the instance can be reused when needed.
	 */
	initFrame() {
		this.frame = wp.media({
			button: {
				text: 'Select',
				close: false,
			},
			states: [
				new wp.media.controller.Library({
					title:
						this.props.option.label || __('Select file', 'blocksy'),
					library: wp.media.query({
						type: this.props.option.mediaType || 'image',
					}),
					multiple: false,
					date: false,
					priority: 20,
				}),
			],
		})

		this.frame.on('select', this.onSelect, this)
		this.frame.on('close', () => {
			this.props.option.onFrameClose && this.props.option.onFrameClose()
		})
	}

	/**
	 * Open the media modal to the library state.
	 */
	openFrame() {
		this.initFrame()
		this.frame.setState('library').open()
		this.props.option.onFrameOpen && this.props.option.onFrameOpen()
	}

	/**
	 * After an image is selected in the media modal, switch to the cropper
	 * state if the image isn't the right size.
	 */
	onSelect = () => {
		var attachment = this.frame.state().get('selection').first().toJSON()

		this.props.onChange(attachment.url || '')
		this.frame.close()
	}

	render() {
		return (
			<div
				className="ct-file-uploader"
				{...(this.props.option.attr || {})}>
				<Fragment>
					<input
						type="text"
						value={this.props.value}
						onChange={({ target: { value: thumbnail } }) => {
							this.props.onChange(thumbnail)
						}}
					/>

					<button
						className="button"
						onClick={() => this.openFrame()}>
						{__('Choose File', 'blocksy-companion')}
					</button>
				</Fragment>
			</div>
		)
	}
}
