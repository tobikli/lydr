<?php
/**
 * This class handles PHP errors, keeping tabs of errors thrown
 * and the messages displayed back to the user.
 *
 * @package wpcode
 */

/**
 * WPCode_Error class.
 */
class WPCode_Error {

	/**
	 * An array of errors already caught.
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * The error count.
	 *
	 * @var int
	 */
	private $error_count;

	/**
	 * The snippets that have errors.
	 *
	 * @var int[]
	 */
	private $snippets_with_errors;

	/**
	 * The previous exception handler.
	 *
	 * @var callable
	 */
	private $previous_exception_handler;

	/**
	 * WPCode_Error constructor.
	 */
	public function __construct() {
		// When the admin is loaded let's check if there were any errors recorded.
		add_action( 'admin_init', array( $this, 'gather_errors' ) );

		$this->previous_exception_handler = set_exception_handler( array( $this, 'track_errors' ) );
	}

	/**
	 * The error object caught when running the code.
	 *
	 * @param ParseError|Exception|Error|array $error The caught error.
	 *
	 * @return void
	 */
	public function add_error( $error ) {
		$this->errors[] = $error;
		$this->write_error_to_log( $error );
	}

	/**
	 * Check if an error has been recorded.
	 *
	 * @return bool
	 */
	public function has_error() {
		return ! empty( $this->errors );
	}

	/**
	 * Empty the errors record, useful if you want to
	 * make sure the last error was thrown by your code.
	 *
	 * @return void
	 */
	public function clear_errors() {
		$this->errors = array();
	}

	/**
	 * Store the error in the logs.
	 *
	 * @param array|Exception $error The error object.
	 *
	 * @return void
	 */
	private function write_error_to_log( $error ) {
		$handle = 'error';
		if ( is_array( $error ) && isset( $error['snippet'] ) ) {
			$this->track_snippet_error( $error );
		}

		wpcode()->logger->handle( time(), $this->get_error_message( $error ), $handle );
	}

	/**
	 * Log the error in the snippet specific log and mark the snippet as having an error.
	 *
	 * @param array $error The error object.
	 *
	 * @return void
	 */
	private function track_snippet_error( $error ) {
		if ( ! isset( $error['snippet'] ) ) {
			return;
		}
		if ( is_int( $error['snippet'] ) ) {
			$snippet = new WPCode_Snippet( absint( $error['snippet'] ) );
		} else {
			$snippet = $error['snippet'];
		}
		// Let's see if the snippet is currently marked as having an error.
		if ( ! isset( $snippet->id ) ) {
			return;
		}
		if ( empty( $error['wpc_type'] ) ) {
			$error['wpc_type'] = 'error';
		}
		$last_error = $snippet->get_last_error();
		// If we already have an error and the type of the current error is not deactivated let's not log it.
		if ( ! empty( $last_error ) && 'deactivated' === $last_error['wpc_type'] || ! empty( $last_error ) && $last_error['wpc_type'] === $error['wpc_type'] ) {
			return;
		}

		if ( ! isset( $error['time'] ) ) {
			$error['time'] = time();
		}

		$handle = 'snippet-' . $snippet->get_id();

		// Log to snippet specific log.
		wpcode()->logger->handle( time(), $this->get_error_message( $error ), $handle );
		// Store the error details in the snippet meta.
		$snippet->set_last_error( $error );

		do_action( 'wpcode_snippet_error_tracked', $error, $snippet );

		// Reset the error count.
		$this->clear_snippets_errors();
	}

	/**
	 * Get the last error message.
	 *
	 * @return string
	 */
	public function get_last_error_message() {
		if ( empty( $this->errors ) ) {
			return '';
		}
		$last_error = end( $this->errors );

		return $this->get_error_message( $last_error );
	}

	/**
	 * Get the error message from the error object, either an array or an Exception object.
	 *
	 * @param array|Exception $error The error object.
	 *
	 * @return string
	 */
	public function get_error_message( $error ) {
		if ( is_array( $error ) && isset( $error['message'] ) ) {
			return $error['message'];
		}

		if ( ! is_array( $error ) && method_exists( $error, 'getMessage' ) ) {
			return $error->getMessage();
		}

		return '';
	}

	/**
	 * Get the short version of the error message without the file and line number.
	 *
	 * @param string $message The error message.
	 *
	 * @return string
	 */
	public function get_error_message_short( $message ) {
		$pattern = '/^([^:]+: .+?) in/';

		if ( preg_match( $pattern, $message, $matches ) ) {
			$message = $matches[1];
		}

		return $message;
	}

	/**
	 * Set the error count.
	 *
	 * @param int $count The error count.
	 *
	 * @return void
	 */
	private function set_error_count( $count ) {
		$this->error_count = $count;
	}

	/**
	 * Set the snippets that have errors.
	 *
	 * @param int[] $snippets Ids of the snippets with errors.
	 *
	 * @return void
	 */
	private function set_snippets_with_errors( $snippets ) {
		update_option( 'wpcode_snippets_errors', $snippets );
	}

	/**
	 * Clear the persistent error count.
	 *
	 * @return void
	 */
	public function clear_snippets_errors() {
		unset( $this->error_count );
		delete_option( 'wpcode_snippets_errors' );
		$this->snippets_with_errors = false;
		$this->gather_errors();
	}

	/**
	 * Get the error count.
	 *
	 * @return int
	 */
	public function get_error_count() {
		if ( ! isset( $this->error_count ) ) {
			$snippets_with_errors = $this->get_snippets_with_errors();
			$this->error_count    = is_array( $snippets_with_errors ) ? count( $snippets_with_errors ) : 0;
		}

		return $this->error_count;
	}

	/**
	 * Get the snippets that have errors.
	 *
	 * @return int[]
	 */
	public function get_snippets_with_errors() {
		if ( ! isset( $this->snippets_with_errors ) ) {
			$this->snippets_with_errors = get_option( 'wpcode_snippets_errors' );
		}

		return $this->snippets_with_errors;
	}

	/**
	 * Let's query all the snippets that have an error set to them.
	 *
	 * @return void
	 */
	public function gather_errors() {

		// If we already have an error count, no need to query the db.
		if ( false !== $this->get_snippets_with_errors() ) {
			return;
		}

		$snippets = get_posts(
			array(
				'post_type'      => 'wpcode',
				'posts_per_page' => - 1,
				'post_status'    => 'any',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array(
						'key'     => '_wpcode_last_error',
						'compare' => 'EXISTS',
					),
				),
				'fields'         => 'ids',
			)
		);

		$this->set_snippets_with_errors( $snippets );
		$this->set_error_count( count( $snippets ) );
	}

	/**
	 * Track errors thrown by PHP.
	 *
	 * @param Throwable $e The error or exception object.
	 *
	 * @return void
	 */
	public function track_errors( $e ) {
		$error = array(
			'line'    => $e->getLine(),
			'file'    => $e->getFile(),
			'message' => $e->getMessage(),
			'type'    => 'error',
		);

		if ( wpcode()->execute->is_error_from_wpcode( $error ) && ! empty( $error['line'] ) ) {
			$snippet = wpcode()->execute->snippet_executed;
			if ( ! empty( $snippet ) && ! wpcode()->execute->snippet_location_disable( $snippet ) ) {
				$error['snippet']    = $snippet;
				$error['error_line'] = $error['line'];
				// Let's try to determine on which page we are and potentially save that URL in the error details.
				global $wp;
				if ( isset( $wp->request ) ) {
					$error['url'] = home_url( $wp->request );
				}

				wpcode()->error->add_error( $error );
			}
		}

		$this->call_previous_exception_handler( $e );
	}

	/**
	 * Call the previous exception handler.
	 *
	 * @param Throwable $e The error or exception object.
	 *
	 * @return void
	 * @throws Throwable The error or exception object.
	 */
	private function call_previous_exception_handler( $e ) {
		if ( isset( $this->previous_exception_handler ) ) {
			call_user_func( $this->previous_exception_handler, $e );
		} else {
			throw $e;
		}

		exit();
	}
}
