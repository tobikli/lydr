<?php
/**
 * Class for auto-insert inside content.
 *
 * @package wpcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPCode_Auto_Insert_Single.
 */
class WPCode_Auto_Insert_Content_Lite extends WPCode_Auto_Insert_Type {

	/**
	 * The type unique name (slug).
	 *
	 * @var string
	 */
	public $name = 'content';

	/**
	 * The category of this type.
	 *
	 * @var string
	 */
	public $category = 'page';

	/**
	 * Not available to select.
	 *
	 * @var string
	 */
	public $code_type = 'pro';

	/**
	 * Text to display next to optgroup label.
	 *
	 * @var string
	 */
	public $label_pill = 'PRO';

	/**
	 * Load the available options and labels.
	 *
	 * @return void
	 */
	public function init() {
		$this->label         = __( 'Content', 'insert-headers-and-footers' );
		$this->locations     = array(
			'after_words' => array(
				'label'       => esc_html__( 'Insert After # Words', 'insert-headers-and-footers' ),
				'description' => esc_html__( 'Insert snippet after a minimum number of words.', 'insert-headers-and-footers' ),
			),
			'every_words' => array(
				'label'       => esc_html__( 'Insert Every # Words', 'insert-headers-and-footers' ),
				'description' => esc_html__( 'Insert snippet every # number of words.', 'insert-headers-and-footers' ),
			),
		);
		$this->upgrade_title = __( 'Word-based content locations are a PRO feature', 'insert-headers-and-footers' );
		$this->upgrade_text  = __( 'Upgrade to PRO today and get access to automatic word-count based insert locations.', 'insert-headers-and-footers' );
		$this->upgrade_link  = wpcode_utm_url( 'https://wpcode.com/lite/', 'edit-snippet', 'auto-insert', 'content' );
	}

}

new WPCode_Auto_Insert_Content_Lite();
