<?php
/**
 * Placeholder Class that handles conditional logic for device type
 *
 * @package WPCode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The WPCode_Conditional_Device_Lite class.
 */
class WPCode_Conditional_Device_Lite extends WPCode_Conditional_Type {

	/**
	 * The type unique name (slug).
	 *
	 * @var string
	 */
	public $name = 'device';

	/**
	 * Set the translatable label.
	 *
	 * @return void
	 */
	protected function set_label() {
		$this->label = __( 'Device', 'insert-headers-and-footers' ) . ' (PRO)';
	}

	/**
	 * Set the type options for the admin mainly.
	 *
	 * @return void
	 */
	public function load_type_options() {
		$this->options = array(
			'device_type'  => array(
				'label'   => __( 'Device Type', 'insert-headers-and-footers' ),
				'type'    => 'select',
				'upgrade' => array(
					'title' => __( 'Device Type Rules are a Pro Feature', 'insert-headers-and-footers' ),
					'text'  => __( 'Get access to advanced device type conditional logic rules by upgrading to PRO today.', 'insert-headers-and-footers' ),
					'link'  => wpcode_utm_url( 'https://wpcode.com/lite/', 'edit-snippet', 'conditional-logic', 'device-type' ),
				),
				'options' => array(
					array(
						'label'    => __( 'Desktop', 'insert-headers-and-footers' ),
						'value'    => '',
						'disabled' => true,
					),
					array(
						'label'    => __( 'Mobile', 'insert-headers-and-footers' ),
						'value'    => '',
						'disabled' => true,
					),
				),
			),
			'browser'      => array(
				'label'   => __( 'Browser Type', 'insert-headers-and-footers' ),
				'type'    => 'select',
				'upgrade' => array(
					'title' => __( 'Browser Type Rules are a Pro Feature', 'insert-headers-and-footers' ),
					'text'  => __( 'Get access to advanced device conditional logic rules by upgrading to PRO today.', 'insert-headers-and-footers' ),
					'link'  => wpcode_utm_url( 'https://wpcode.com/lite/', 'edit-snippet', 'conditional-logic', 'browser' ),
				),
			),
			'os'           => array(
				'label'   => __( 'Operating System', 'insert-headers-and-footers' ),
				'type'    => 'select',
				'upgrade' => array(
					'title' => __( 'Operating System Rules are a Pro Feature', 'insert-headers-and-footers' ),
					'text'  => __( 'Get access to advanced operating system conditional logic rules by upgrading to PRO today.', 'insert-headers-and-footers' ),
					'link'  => wpcode_utm_url( 'https://wpcode.com/lite/', 'edit-snippet', 'conditional-logic', 'os' ),
				),
			),
			'cookie_name'  => array(
				'label'   => __( 'Cookie Name', 'insert-headers-and-footers' ),
				'type'    => 'text',
				'upgrade' => array(
					'title' => __( 'Cookie-based Rules are a Pro Feature', 'insert-headers-and-footers' ),
					'text'  => __( 'Get access to advanced cookie conditional logic rules by upgrading to PRO today.', 'insert-headers-and-footers' ),
					'link'  => wpcode_utm_url( 'https://wpcode.com/lite/', 'edit-snippet', 'conditional-logic', 'cookie-name' ),
				),
			),
			'cookie_value' => array(
				'label'   => __( 'Cookie Value', 'insert-headers-and-footers' ),
				'type'    => 'text',
				'upgrade' => array(
					'title' => __( 'Cookie-based Rules are a Pro Feature', 'insert-headers-and-footers' ),
					'text'  => __( 'Get access to advanced cookie conditional logic rules by upgrading to PRO today.', 'insert-headers-and-footers' ),
					'link'  => wpcode_utm_url( 'https://wpcode.com/lite/', 'edit-snippet', 'conditional-logic', 'cookie-value' ),
				),
			),
		);
	}
}

new WPCode_Conditional_Device_Lite();
