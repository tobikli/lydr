<?php

namespace Hostinger\EasyOnboarding;

defined( 'ABSPATH' ) || exit;

class Config {
	private array $config      = array();
	public const TOKEN_HEADER  = 'X-Hpanel-Order-Token';
	public const DOMAIN_HEADER = 'X-Hpanel-Domain';
	public function __construct() {
		$this->decode_config( HOSTINGER_EASY_ONBOARDING_WP_CONFIG_PATH );
	}

	private function decode_config( string $path ): void {
		if ( file_exists( $path ) ) {
			$config_content = file_get_contents( $path );
			$this->config   = json_decode( $config_content, true );
		}
	}

	public function get_config_value( string $key, $default ): string {
		if ( $this->config && isset( $this->config[ $key ] ) && ! empty( $this->config[ $key ] ) ) {
			return $this->config[ $key ];
		}

		return $default;
	}
}
