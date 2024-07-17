<?php

namespace Hostinger\Cli\Commands;

use Hostinger\Admin\PluginSettings;
use WP_CLI;
use Hostinger\Settings;

defined( 'ABSPATH' ) || exit;

class Maintenance {

	public static function define_command(): void {
		if ( class_exists( '\WP_CLI' ) ) {
			WP_CLI::add_command( 'hostinger maintenance', self::class );
		}
	}

    /**
     * Command allows enable/disable maintenance mode.
     *
     * @param array $args
     *
     * @return void
     * @throws \Exception
     */
	public function mode( array $args ): void {
		if ( empty( $args ) ) {
			WP_CLI::error( 'Arguments cannot be empty. Use 0 or 1' );
		}

		if ( has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
		}

        $plugin_settings = new PluginSettings();
        $plugin_options = $plugin_settings->get_plugin_settings();

		switch ( $args[0] ) {
			case '1':
                $plugin_options->set_maintenance_mode(true);
				WP_CLI::success( 'Maintenance mode ENABLED' );
				break;
			case '0':
                $plugin_options->set_maintenance_mode(false);
				WP_CLI::success( 'Maintenance mode DISABLED' );
				break;
			default:
				throw new \Exception( 'Invalid maintenance mode value' );
		}

        $plugin_settings->save_plugin_settings( $plugin_options );
	}

    /**
     * Command return maintenance mode status.
     *
     * @return bool
     */
    public function status(): bool {
        $plugin_settings = new PluginSettings();
        $plugin_options = $plugin_settings->get_plugin_settings();

        if ( $plugin_options->get_maintenance_mode() ) {
            WP_CLI::success( 'Maintenance mode ENABLED' );
        } else {
            WP_CLI::success( 'Maintenance mode DISABLED' );
        }

        return (bool)$plugin_options->get_maintenance_mode();
    }
}
