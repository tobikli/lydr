<?php

namespace Hostinger\EasyOnboarding\Preview;

use Hostinger\EasyOnboarding\Helper;

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
			wp_enqueue_style( 'hostinger-onboarding-preview-styles', HOSTINGER_EASY_ONBOARDING_ASSETS_URL . '/css/hts-preview.css', array(), HOSTINGER_EASY_ONBOARDING_VERSION );
		}
	}
}
