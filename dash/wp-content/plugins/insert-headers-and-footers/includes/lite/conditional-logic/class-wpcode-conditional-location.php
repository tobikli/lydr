<?php
/**
 * Placeholder class that handles conditional logic based on location.
 *
 * @package WPCode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * The WPCode_Conditional_Location class.
 */
class WPCode_Conditional_Location_Lite extends WPCode_Conditional_Type {

	/**
	 * The type unique name (slug).
	 *
	 * @var string
	 */
	public $name = 'location';

	/**
	 * Set the type options for the admin mainly.
	 *
	 * @return void
	 */
	public function load_type_options() {
		$this->options = array(
			'country'   => array(
				'label'   => __( 'Country', 'wpcode-premium' ),
				'type'    => 'select',
				'options' => array(),
				'upgrade' => array(
					'title' => __( 'Location Rules are a Pro Feature', 'insert-headers-and-footers' ),
					'text'  => __( 'Get access to location-based conditional logic rules by upgrading to PRO today.', 'insert-headers-and-footers' ),
					'link'  => wpcode_utm_url( 'https://wpcode.com/lite/', 'edit-snippet', 'conditional-logic', 'location-country' ),
				),
			),
			'continent' => array(
				'label'   => __( 'Continent', 'wpcode-premium' ),
				'type'    => 'select',
				'options' => array(),
				'upgrade' => array(
					'title' => __( 'Location Rules are a Pro Feature', 'insert-headers-and-footers' ),
					'text'  => __( 'Get access to location-based conditional logic rules by upgrading to PRO today.', 'insert-headers-and-footers' ),
					'link'  => wpcode_utm_url( 'https://wpcode.com/lite/', 'edit-snippet', 'conditional-logic', 'location-continent' ),
				),
			),
		);
	}

	/**
	 * Set the translatable label.
	 *
	 * @return void
	 */
	protected function set_label() {
		$this->label = __( 'Location', 'insert-headers-and-footers' ) . ' (PRO)';
	}
}

new WPCode_Conditional_Location_Lite();
