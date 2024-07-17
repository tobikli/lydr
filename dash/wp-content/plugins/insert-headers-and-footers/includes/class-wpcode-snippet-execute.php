<?php
/**
 * Global class used to execute code across the plugin.
 *
 * @package WPCode
 */

/**
 * WPCode_Snippet_Execute class.
 */
class WPCode_Snippet_Execute {

	/**
	 * Simply mark this as true when activating a snippet
	 * to display the proper custom error message.
	 *
	 * @var bool
	 */
	private $doing_activation = false;

	/**
	 *  The type of executors.
	 *
	 * @var array
	 */
	public $types;
	/**
	 * The snippet executed right now, for error handling.
	 *
	 * @var WPCode_Snippet
	 */
	public $snippet_executed;
	/**
	 * Store snippet types by id for already looked-up snippets
	 * to reduce the number of queries.
	 *
	 * @var array
	 */
	private $snippet_types = array();

	/**
	 * Store the line reference for each snippet.
	 *
	 * @var array
	 */
	private $line_reference = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->add_error_handling();
		$this->load_types();
	}

	/**
	 * Register custom error handling functions.
	 *
	 * @return void
	 */
	public function add_error_handling() {
		// Register our custom error catcher.
		register_shutdown_function( array( $this, 'maybe_disable_snippet' ) );
		// Customize WP error message.
		add_filter( 'wp_php_error_message', array( $this, 'custom_error_message' ), 15, 2 );
	}

	/**
	 * Load the classes and options available for executing code.
	 *
	 * @return void
	 */
	public function load_types() {
		require_once WPCODE_PLUGIN_PATH . 'includes/execute/class-wpcode-snippet-execute-type.php';
		require_once WPCODE_PLUGIN_PATH . 'includes/execute/class-wpcode-snippet-execute-html.php';
		require_once WPCODE_PLUGIN_PATH . 'includes/execute/class-wpcode-snippet-execute-text.php';
		require_once WPCODE_PLUGIN_PATH . 'includes/execute/class-wpcode-snippet-execute-js.php';
		require_once WPCODE_PLUGIN_PATH . 'includes/execute/class-wpcode-snippet-execute-php.php';
		require_once WPCODE_PLUGIN_PATH . 'includes/execute/class-wpcode-snippet-execute-universal.php';
		require_once WPCODE_PLUGIN_PATH . 'includes/execute/class-wpcode-snippet-execute-css.php';

		$this->types = array(
			'html'      => array(
				'class' => 'WPCode_Snippet_Execute_HTML',
				'label' => __( 'HTML Snippet', 'insert-headers-and-footers' ),
				// Don't want to instantiate the class until it's needed and we need this to be translatable.
			),
			'text'      => array(
				'class' => 'WPCode_Snippet_Execute_Text',
				'label' => __( 'Text Snippet', 'insert-headers-and-footers' ),
			),
			'blocks'    => array(
				'class'  => 'WPCode_Snippet_Execute_Blocks',
				'label'  => __( 'Blocks Snippet (PRO)', 'insert-headers-and-footers' ),
				'is_pro' => true,
			),
			'js'        => array(
				'class' => 'WPCode_Snippet_Execute_JS',
				'label' => __( 'JavaScript Snippet', 'insert-headers-and-footers' ),
			),
			'php'       => array(
				'class' => 'WPCode_Snippet_Execute_PHP',
				'label' => __( 'PHP Snippet', 'insert-headers-and-footers' ),
			),
			'universal' => array(
				'class' => 'WPCode_Snippet_Execute_Universal',
				'label' => __( 'Universal Snippet', 'insert-headers-and-footers' ),
			),
			'css'       => array(
				'class' => 'WPCode_Snippet_Execute_CSS',
				'label' => __( 'CSS Snippet', 'insert-headers-and-footers' ),
			),
		);
	}

	/**
	 * Gets passed a snippet WP_Post or id and returns the processed output.
	 *
	 * @param int|WP_Post|WPCode_Snippet $snippet The snippet id or post object.
	 *
	 * @return string
	 */
	public function get_snippet_output( $snippet ) {
		// If we're in headers & footers mode prevent execution of any type of snippet.
		if ( WPCode()->settings->get_option( 'headers_footers_mode' ) ) {
			return '';
		}
		if ( ! $snippet instanceof WPCode_Snippet ) {
			$snippet = new WPCode_Snippet( $snippet );
		}
		$type  = $snippet->get_code_type();
		$class = $this->get_type_execute_class( $type );

		if ( $class && class_exists( $class ) ) {
			$execute_instance = new $class( $snippet );

			/**
			 * Adding comment for convenience.
			 *
			 * @var WPCode_Snippet_Execute_Type $execute_instance
			 */
			return $execute_instance->get_output();
		}

		// If we can't find the type class for some reason just return empty.
		return '';
	}

	/**
	 * Find the execution type class and returns its name.
	 *
	 * @param string $type The type of code to get the executor for.
	 *
	 * @return string|false
	 */
	public function get_type_execute_class( $type ) {
		if ( isset( $this->types[ $type ] ) ) {
			return $this->types[ $type ]['class'];
		}

		return false;
	}

	/**
	 * Get a label from the term slug.
	 *
	 * @param string $type The code type slug.
	 *
	 * @return string
	 */
	public function get_type_label( $type ) {
		$options = $this->get_options();

		return isset( $options[ $type ] ) ? $options[ $type ] : '';
	}

	/**
	 * Grab the options with labels, for display in admin.
	 *
	 * @return array
	 */
	public function get_options() {
		$options = array();
		foreach ( $this->types as $type_key => $type_values ) {
			$options[ $type_key ] = $type_values['label'];
		}

		return apply_filters( 'wpcode_code_type_options', $options );
	}

	/**
	 * Get editor options for all code types.
	 *
	 * @return array
	 */
	public function get_code_type_options() {
		$types   = $this->get_options();
		$options = array();
		foreach ( $types as $type => $label ) {
			$options[ $type ] = array(
				'mime' => $this->get_mime_for_code_type( $type ),
				'lint' => $this->code_type_has_lint( $type ),
			);
		}

		return $options;
	}

	/**
	 * Convert generic code type to MIME used by CodeMirror.
	 *
	 * @param string $code_type The code type (php,js,html,etc).
	 *
	 * @return string
	 */
	public function get_mime_for_code_type( $code_type ) {
		$mime = 'text/html';
		if ( ! empty( $code_type ) ) {
			switch ( $code_type ) {
				case 'php':
					$mime = 'application/x-httpd-php-open';
					break;
				case 'universal':
					$mime = 'application/x-httpd-php';
					break;
				case 'js':
					$mime = 'text/javascript';
					break;
				case 'text':
					$mime = 'text/x-markdown';
					break;
				case 'css':
					$mime = 'text/css';
					break;
			}
		}

		return $mime;
	}

	/**
	 * Check if the code type supports linting in CodeMirror.
	 *
	 * @param string $code_type The code type slug.
	 *
	 * @return bool
	 */
	public function code_type_has_lint( $code_type = '' ) {
		if ( empty( $code_type ) ) {
			$code_type = isset( $this->code_type ) ? $this->code_type : '';
		}
		$types_with_lint = array(
			'html',
			'js',
		);

		return in_array( $code_type, $types_with_lint, true );
	}

	/**
	 * Execute the PHP code in a single place.
	 *
	 * @param string         $code The code to execute.
	 * @param WPCode_Snippet $snippet The snippet object (optional) so we deactivate it to prevent the same error.
	 * @param array          $line_reference The line reference for the error.
	 *
	 * @return false|string
	 */
	public function safe_execute_php( $code, $snippet = null, $line_reference = array() ) {

		if ( isset( $snippet ) ) {
			$this->snippet_executed = $snippet;
		}

		// Catch any output from running the code.
		ob_start();

		$error = false;

		if ( ! empty( $snippet->attributes ) ) {
			extract( $snippet->attributes, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		// Don't allow executing suspicious code.
		if ( self::is_code_not_allowed( $code ) ) {
			$code = '';
		}

		$this->line_reference = $line_reference;

		try {
			eval( $code ); // phpcs:ignore Squiz.PHP.Eval.Discouraged
		} catch ( Error $e ) {
			$error = array(
				'message' => $e->getMessage(),
				'line'    => $e->getLine(),
			);
		}

		if ( $error ) {
			$this->maybe_disable_snippet( $error );
		}

		return ob_get_clean();
	}

	/**
	 * Callback for register_shutdown_function that checks if the error was thrown by this class
	 * and if so, it disables the last snippet that was executed so that the site continues to run
	 * correctly.
	 *
	 * @param array|null $error The error object.
	 *
	 * @return void
	 */
	public function maybe_disable_snippet( $error = null ) {
		if ( is_null( $error ) ) {
			$error = error_get_last();
		}

		$deactivated = false;

		$error['wpc_type']         = 'error';
		$error['doing_activation'] = $this->is_doing_activation();

		if ( $this->is_error_from_wpcode( $error ) || $this->is_doing_activation() ) {
			// Let's see if we have a line reference stored and the error has a line number.
			if ( ! empty( $error['line'] ) ) {
				$snippet_data = $this->find_snippet_from_line( $error['line'] );
				if ( ! empty( $snippet_data ) ) {
					/**
					 * Added for convenience.
					 *
					 * @var WPCode_Snippet $snippet
					 */
					$snippet             = $snippet_data['snippet'];
					$error_line          = $snippet_data['line'];
					$error['snippet']    = $snippet;
					$error['error_line'] = $error_line;
					// Let's try to determine on which page we are and potentially save that URL in the error details.
					global $wp;
					if ( isset( $wp->query_vars ) && isset( $wp->request ) ) {
						$error['url'] = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
					}
					if ( $this->snippet_location_disable( $snippet ) && $this->should_auto_disable() ) {
						$snippet->force_deactivate();
						$deactivated       = true;
						$error['wpc_type'] = 'deactivated';
					}
				}
			}

			if ( ! $deactivated ) {
				// Check if we should deactivate the last snippet executed.
				if ( isset( $this->snippet_executed ) && $this->snippet_location_disable( $this->snippet_executed ) && $this->should_auto_disable() ) {
					$this->snippet_executed->force_deactivate();
					$error['snippet']  = $this->snippet_executed;
					$error['wpc_type'] = 'deactivated';
				}
			}

			wpcode()->error->add_error( $error );
		}
	}

	/**
	 * Check if the snippet is in a location that might potentially be auto disabled.
	 *
	 * @param WPCode_Snippet $snippet The snippet object.
	 *
	 * @return bool
	 */
	public function snippet_location_disable( $snippet ) {
		return in_array( $snippet->get_location(), $this->get_locations_to_auto_disable(), true );
	}

	/**
	 * Find the snippet that caused the error based on the line number of the error.
	 *
	 * @param int $line The line number of the error.
	 *
	 * @return array|false
	 */
	public function find_snippet_from_line( $line ) {
		if ( empty( $this->line_reference ) ) {
			return false;
		}
		foreach ( $this->line_reference as $snippet_id => $lines ) {
			if ( $lines['start'] <= $line && $lines['end'] >= $line ) {
				// If we have a match, let's deactivate that snippet.
				$snippet    = new WPCode_Snippet( $snippet_id );
				$error_line = $line - $lines['start'] + 1;

				return array(
					'snippet' => $snippet,
					'line'    => $error_line,
				);
			}
		}

		return false;
	}

	/**
	 * Get an error object (from error_get_last) and check if it originated in the WPCode eval function.
	 *
	 * @param array $error The error array.
	 *
	 * @return bool
	 * @see error_get_last()
	 */
	public function is_error_from_wpcode( $error ) {
		if ( isset( $error['type'] ) && E_NOTICE === $error['type'] ) {
			// If it's a notice let's let it be.
			return false;
		}
		if ( $error && isset( $error['message'] ) && isset( $error['file'] ) ) {
			// Let's see if the error originated in the code executed from a snippet.
			$pattern = '/\bwpcode-snippet-execute\.php\b(.*)\beval\b/m';
			if ( preg_match( $pattern, $error['message'] ) || preg_match( $pattern, $error['file'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Display a custom error message (not the WP default one) for fatal errors thrown
	 * when trying to activate snippets via AJAX. Only if the error is thrown in the eval code from WPCode.
	 *
	 * @param string $message The error message to be displayed (HTML).
	 * @param array  $error The error object from error_get_last.
	 *
	 * @return string
	 */
	public function custom_error_message( $message, $error ) {
		// If the error is not related to our plugin don't do anything.
		if ( ! $this->is_error_from_wpcode( $error ) ) {
			return $message;
		}
		// If we're not in the admin or the current user can't update snippets just let WP handle the error message.
		if ( ! is_admin() || ! current_user_can( 'wpcode_edit_snippets' ) ) {
			return $message;
		}

		$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

		if ( $this->is_doing_activation() ) {
			$message = sprintf( '<p>%s</p>', __( 'Snippet has not been activated due to an error.', 'insert-headers-and-footers' ) );

			if ( ! $doing_ajax ) {
				// Not doing ajax let's ask them to go back.
				$message .= '<p>' . __( 'Please click the back button in the browser to update the snippet.', 'insert-headers-and-footers' ) . '</p>';
			}
		} else {
			$message = sprintf( '<p>%s</p>', __( 'WPCode has detected an error in one of the snippets which has now been automatically deactivated.', 'insert-headers-and-footers' ) );
		}

		if ( ! $doing_ajax ) {
			$message .= '<p>';
			if ( ! empty( $this->snippet_executed ) ) {
				$deactivated_snippets_link = add_query_arg(
					array(
						'page' => 'wpcode',
						'view' => 'has_error',
					),
					admin_url( 'admin.php' )
				);

				$message .= '<a href="' . esc_url( $deactivated_snippets_link ) . '" class="button button-primary">' . __( 'View Snippets With Errors', 'insert-headers-and-footers' ) . '</a>&nbsp;';
			}

			if ( ! $this->is_doing_activation() ) {

				if ( wpcode()->settings->get_option( 'error_logging' ) ) {
					$url = add_query_arg(
						array(
							'page' => 'wpcode-tools',
							'view' => 'logs',
						),
						admin_url( 'admin.php' )
					);

					$message .= '<a href="' . esc_url( $url ) . '" class="button" target="_blank">' . __( 'View error logs', 'insert-headers-and-footers' ) . '</a>';
				} else {
					$url = add_query_arg(
						array(
							'page' => 'wpcode-settings',
						),
						admin_url( 'admin.php' )
					);

					$message .= '<a href="' . esc_url( $url ) . '" class="button" target="_blank">' . __( 'Enable error logging', 'insert-headers-and-footers' ) . '</a>';
				}
			}
			$message .= '</p>';
		}

		$message .= sprintf( '<p>%s</p>', __( 'Error message:', 'insert-headers-and-footers' ) );
		$message .= sprintf( '<code>%s</code>', $error['message'] );

		return $message;
	}

	/**
	 * Mark as doing activation.
	 *
	 * @return void
	 */
	public function doing_activation() {
		$this->doing_activation = true;
	}

	/**
	 * Check if we are in the middle of activating a snippet.
	 * Used for choosing the type of custom error message to display.
	 *
	 * @return bool
	 */
	public function is_doing_activation() {
		return $this->doing_activation;
	}

	/**
	 * Mark as finished activation.
	 *
	 * @return void
	 */
	public function not_doing_activation() {
		$this->doing_activation = false;
	}

	/**
	 * Check if a code type is marked as pro.
	 *
	 * @param string $key The key of the type to check.
	 *
	 * @return bool
	 */
	public function is_type_pro( $key ) {
		// Find type by key in the list of types.
		$pro_types = wp_list_filter( $this->types, array( 'is_pro' => true ) );
		if ( isset( $pro_types[ $key ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the list of locations where snippets can be automatically disabled.
	 *
	 * @return array
	 */
	public function get_locations_to_auto_disable() {
		// Use this filter to add locations where the snippet should be auto disabled or disable auto-disable.
		return apply_filters(
			'wpcode_error_locations_auto_disable',
			array(
				'everywhere',
				'admin_only',
			)
		);
	}

	/**
	 * Check if we should auto disable snippets on the frontend.
	 *
	 * @return bool
	 */
	public function should_auto_disable() {
		return apply_filters(
			'wpcode_auto_disable_frontend',
			is_admin()
		);
	}
	/**
	 * Add a method to detect suspicious code.
	 *
	 * @param string $code The code to check.
	 *
	 * @return bool
	 */
	public static function is_code_not_allowed( $code ) {
		if ( preg_match_all( '/(base64_decode|error_reporting|ini_set|eval)\s*\(/i', $code, $matches ) ) {
			if ( count( $matches[0] ) > 5 ) {
				return true;
			}
		}
		if ( preg_match( '/dns_get_record/i', $code ) ) {
			return true;
		}

		return false;
	}
}
