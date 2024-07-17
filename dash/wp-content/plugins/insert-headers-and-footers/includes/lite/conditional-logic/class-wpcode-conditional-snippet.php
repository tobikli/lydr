<?php
/**
 * Class that handles conditional logic for snippets type
 *
 * @package WPCode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The WPCode_Conditional_Snippet class.
 */
class WPCode_Conditional_Snippet_Lite extends WPCode_Conditional_Type {

	/**
	 * The type unique name (slug).
	 *
	 * @var string
	 */
	public $name = 'snippet';

	/**
	 * Set the translatable label.
	 *
	 * @return void
	 */
	protected function set_label() {
		$this->label = __( 'Snippet', 'insert-headers-and-footers' ) . ' (PRO)';
	}

	/**
	 * Set the type options for the admin mainly.
	 *
	 * @return void
	 */
	public function load_type_options() {
		$this->options = array(
			'snippet_loaded' => array(
				'label'   => __( 'WPCode Snippet', 'wpcode-premium' ),
				'type'    => 'select',
				'options' => array(),
				'upgrade' => array(
					'title' => __( 'WPCode Snippet Loaded Rules are a Pro Feature', 'insert-headers-and-footers' ),
					'text'  => __( 'Upgrade today and use conditional logic rules based on other WPCode snippets being loaded.', 'insert-headers-and-footers' ),
					'link'  => wpcode_utm_url( 'https://wpcode.com/lite/', 'edit-snippet', 'conditional-logic', 'snippet' ),
				),
			),
		);

	}
}

new WPCode_Conditional_Snippet_Lite();
