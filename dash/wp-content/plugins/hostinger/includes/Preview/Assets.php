<?php

namespace Hostinger\Preview;

use Hostinger\Helper;

defined( 'ABSPATH' ) || exit;

class Assets {
	public function __construct() {
		$helper = new Helper();
		if ( $helper->is_preview_domain() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_preview_css' ) );
		}
	}

	public function enqueue_preview_css(): void {
		if ( is_user_logged_in() ) {
			wp_enqueue_style( 'hostinger-preview-styles', HOSTINGER_ASSETS_URL . '/css/hts-preview.css', array(), HOSTINGER_VERSION );
		}
	}
}
