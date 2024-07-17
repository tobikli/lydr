<?php

namespace Hostinger\WpHelper;

defined( 'ABSPATH' ) || exit;

class Config {
	private array $config      = array();
	public const TOKEN_HEADER  = 'X-Hpanel-Order-Token';
	public const DOMAIN_HEADER = 'X-Hpanel-Domain';
	public const CONFIG_PATH = ABSPATH . '.private/config.json';
	public function __construct() {
		$this->decodeConfig( self::CONFIG_PATH );
	}

	private function decodeConfig( string $path ): void {
		if ( file_exists( $path ) ) {
			$config_content = file_get_contents( $path );
			$this->config   = json_decode( $config_content, true );
		}
	}

	public function getConfigValue( string $key, $default ): string {
		if ( $this->config && isset( $this->config[ $key ] ) && ! empty( $this->config[ $key ] ) ) {
			return $this->config[ $key ];
		}

		return $default;
	}
}
