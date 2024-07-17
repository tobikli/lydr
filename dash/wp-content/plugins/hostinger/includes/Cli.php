<?php

namespace Hostinger;

use WP_CLI;

defined( 'ABSPATH' ) || exit;

class Cli {
    /**
     * Load required files and hooks to make the CLI work.
     */
    public function __construct() {
		$this->hooks();
	}

    /**
     * Sets up and hooks WP CLI to our CLI code.
     *
     * @return void
     */
	private function hooks(): void {
		if ( class_exists( '\WP_CLI' ) ) {
			WP_CLI::add_hook( 'after_wp_load', array( 'Hostinger\Cli\Commands\Maintenance', 'define_command' ) );
		}
	}
}
