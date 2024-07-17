<?php

namespace Hostinger;

use Hostinger\Bootstrap;

defined( 'ABSPATH' ) || exit;

class Hostinger {
	protected string $plugin_name = 'Hostinger';
	protected string $version;

    /**
     * @return void
     */
	public function bootstrap(): void {
		$this->version = $this->get_plugin_version();
		$bootstrap     = new Bootstrap();
		$bootstrap->run();
	}

    /**
     * @return void
     */
	public function run(): void {
		$this->bootstrap();
	}

	/**
	 * Define constant
	 *
	 * @param string $name Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( string $name, $value ): void {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

    /**
     * @return string
     */
	private function get_plugin_version(): string {
		if ( defined( 'HOSTINGER_VERSION' ) ) {
			return HOSTINGER_VERSION;
		}

		return '1.0.0';
	}
}
