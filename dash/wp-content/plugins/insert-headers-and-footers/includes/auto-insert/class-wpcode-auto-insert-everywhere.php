<?php
/**
 * Class to auto-insert snippets site-wide.
 *
 * @package wpcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPCode_Auto_Insert_Single.
 */
class WPCode_Auto_Insert_Everywhere extends WPCode_Auto_Insert_Type {

	/**
	 * The type unique name (slug).
	 *
	 * @var string
	 */
	public $name = 'everywhere';

	/**
	 * The category of this type.
	 *
	 * @var string
	 */
	public $category = 'global';

	/**
	 * This should is only available for PHP scripts.
	 *
	 * @var string
	 */
	public $code_type = 'php';

	/**
	 * Load the available options and labels.
	 *
	 * @return void
	 */
	public function init() {
		$this->label     = __( 'PHP Snippets Only', 'insert-headers-and-footers' );
		$this->locations = array(
			'everywhere'    => array(
				'label'       => esc_html__( 'Run Everywhere', 'insert-headers-and-footers' ),
				'description' => esc_html__( 'Snippet gets executed everywhere on your website.', 'insert-headers-and-footers' ),
			),
			'frontend_only' => array(
				'label'       => esc_html__( 'Frontend Only', 'insert-headers-and-footers' ),
				'description' => esc_html__( 'Snippet gets executed only in the frontend of the website.', 'insert-headers-and-footers' ),
			),
			'admin_only'    => array(
				'label'       => esc_html__( 'Admin Only', 'insert-headers-and-footers' ),
				'description' => esc_html__( 'The snippet only gets executed in the wp-admin area.', 'insert-headers-and-footers' ),
			),
			'frontend_cl'   => array(
				'label'       => esc_html__( 'Frontend Conditional Logic', 'insert-headers-and-footers' ),
				'description' => esc_html__( 'Ideal for running the snippet later with conditional logic rules in the frontend.', 'insert-headers-and-footers' ),
			),
			'on_demand'     => array(
				'label'       => esc_html__( 'On Demand', 'insert-headers-and-footers' ),
				'description' => esc_html__( 'Execute this snippet on demand or programmatically just when you need it.', 'insert-headers-and-footers' ),
			),
		);
	}

	/**
	 * Execute snippets.
	 *
	 * @return void
	 */
	public function run_snippets() {
		$snippets       = $this->get_snippets_for_location( 'everywhere' );
		$line_reference = array();
		$code           = array();
		$last_line      = 0;
		$last_snippet   = null;

		if ( is_admin() ) {
			$snippets = array_merge( $snippets, $this->get_snippets_for_location( 'admin_only' ) );
		}

		// Merge all the code into 1, so we can track on which line the error occurs, if any.
		foreach ( $snippets as $snippet ) {
			// Use the WPCode_Snippet_Execute_Type filters here for compatibility with class even thought we're skipping it for these particular locations.
			$snippet_code = apply_filters( 'wpcode_snippet_output_php', $snippet->get_code(), $snippet );
			$snippet_code = apply_filters( 'wpcode_snippet_output', $snippet_code, $snippet );
			// Let's see how many lines the code has.
			$lines = substr_count( $snippet_code, PHP_EOL );
			// Let's keep a record of the start and end line of each snippet.
			$last_line ++;
			$line_reference[ $snippet->get_id() ] = array(
				'start' => $last_line,
				'end'   => $last_line + $lines,
			);
			$last_line                            = $last_line + $lines;
			$code[]                               = $snippet_code;
			$last_snippet                         = $snippet;
		}
		if ( ! empty( $code ) ) {
			// Implode all the code and execute it.
			$code = implode( PHP_EOL, $code );
			// Execute the code.
			wpcode()->execute->safe_execute_php( $code, $last_snippet, $line_reference );
		}

		if ( ! is_admin() ) {
			$snippets = $this->get_snippets_for_location( 'frontend_only' );
			foreach ( $snippets as $snippet ) {
				wpcode()->execute->get_snippet_output( $snippet );
			}
		}
	}

	/**
	 * Execute snippets on the init hook to allow using more Conditional Logic options.
	 *
	 * @return void
	 */
	public function run_init_snippets() {
		$snippets = $this->get_snippets_for_location( 'frontend_cl' );
		foreach ( $snippets as $snippet ) {
			wpcode()->execute->get_snippet_output( $snippet );
		}
	}

	/**
	 * Override the default hook and short-circuit any other conditions
	 * checks as these snippets will run everywhere.
	 *
	 * @return void
	 */
	protected function add_start_hook() {
		add_action( 'plugins_loaded', array( $this, 'run_snippets' ), 5 );
		add_action( 'wp', array( $this, 'run_init_snippets' ) );
	}
}

new WPCode_Auto_Insert_Everywhere();
