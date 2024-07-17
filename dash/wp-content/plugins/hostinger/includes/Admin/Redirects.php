<?php

namespace Hostinger\Admin;

use Hostinger\Settings;

defined( 'ABSPATH' ) || exit;

class Redirects {
	private string $platform;
	public const PLATFORM_HPANEL = 'hpanel';

	public function __construct() {

		if ( ! Settings::get_setting( 'first_login_at' ) ) {
			Settings::update_setting( 'first_login_at', gmdate( 'Y-m-d H:i:s' ) );
		}

		if ( ! isset( $_GET['platform'] ) ) {
			return;
		}

		$this->platform = sanitize_text_field( $_GET['platform'] );
		$this->login_redirect();
	}

	private function login_redirect(): void {
		if ( $this->platform === self::PLATFORM_HPANEL ) {
			add_action(
				'init',
				static function () {
					$redirect_url = admin_url( 'admin.php?page=hostinger' );
					wp_safe_redirect( $redirect_url );
					exit;
				}
			);
		}
	}
}
