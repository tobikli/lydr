<?php
/**
 * Class to auto-insert snippets in the Admin area.
 *
 * @package wpcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPCode_Auto_Insert_Admin.
 */
class WPCode_Auto_Insert_Admin extends WPCode_Auto_Insert_Type {

	/**
	 * The category of this type.
	 *
	 * @var string
	 */
	public $category = 'global';

	/**
	 * Load the available options and labels.
	 *
	 * @return void
	 */
	public function init() {
		$this->label     = esc_html__( 'Admin area', 'insert-headers-and-footers' );
		$this->locations = array(
			'admin_head'   => array(
				'label'       => esc_html__( 'Admin header', 'insert-headers-and-footers' ),
				'description' => esc_html__( 'Insert snippet in the wp-admin header area.', 'insert-headers-and-footers' ),
			),
			'admin_footer' => array(
				'label'       => esc_html__( 'Admin footer', 'insert-headers-and-footers' ),
				'description' => esc_html__( 'Insert snippet in the wp-admin footer.', 'insert-headers-and-footers' ),
			),
		);
	}

	/**
	 * Checks if we are on an archive page and we should be executing hooks.
	 *
	 * @return bool
	 */
	public function conditions() {
		return is_admin();
	}

	/**
	 * Add hooks specific to single posts.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_head', array( $this, 'insert_admin_head' ), 9 );
		add_action( 'admin_footer', array( $this, 'insert_admin_footer' ), 9 );
	}

	/**
	 * Output snippet in the admin head.
	 *
	 * @return void
	 */
	public function insert_admin_head() {
		$this->output_location( 'admin_head' );
	}

	/**
	 * Output snippet in the admin footer.
	 *
	 * @return void
	 */
	public function insert_admin_footer() {
		$this->output_location( 'admin_footer' );
	}

	/**
	 * Override the parent method to add the start hook specific to the admin.
	 *
	 * @return void
	 */
	protected function add_start_hook() {
		add_action( 'admin_init', array( $this, 'maybe_run_hooks' ) );
	}
}

new WPCode_Auto_Insert_Admin();
