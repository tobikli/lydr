<?php

namespace Hostinger;

defined( 'ABSPATH' ) || exit;

class Errors {

	private $error_messages;

	public function __construct() {
		$this->error_messages = array(
			'action_failed'    => array(
				'default' => __( 'Action Failed. Try again or contact support. Apologies.', 'hostinger-easy-onboarding' ),
			),
			'unexpected_error' => array(
				'default' => __( 'An unexpected error occurred. Please try again or contact support.', 'hostinger-easy-onboarding' ),
			),
			'server_error'     => array(
				'default' => __( 'We apologize for the inconvenience. The AI content generation process encountered a server error. Please try again later, and if the issue persists, kindly contact our support team for assistance.', 'hostinger-easy-onboarding' ),
			),
		);
	}

	public function get_error_message( string $error_code ) {
		if ( array_key_exists( $error_code, $this->error_messages ) ) {
			$message_data = $this->error_messages[ $error_code ];

			return $message_data['default'];
		} else {
			return __( 'Unknown error code.', 'hostinger-easy-onboarding' );
		}
	}
}

new Errors();
