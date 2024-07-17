<?php

namespace Hostinger;

use Hostinger\Admin\Options\PluginOptions;
use Hostinger\Admin\PluginSettings;

defined( 'ABSPATH' ) || exit;

class ComingSoon {
    /**
     * @var PluginOptions
     */
    private PluginOptions $plugin_options;

	public function __construct() {
        $plugin_settings = new PluginSettings();
        $this->plugin_options = $plugin_settings->get_plugin_settings();

		add_action( 'wp_footer', array( $this, 'register_styles' ) );
        add_action( 'template_redirect', array( $this, 'coming_soon' ) );
        add_filter( 'wp_headers', array( $this, 'modify_headers' ) );

        // Fix deprecated warning.
        if ( has_action( 'wp_footer', 'the_block_template_skip_link' ) ) {
            remove_action( 'wp_footer', 'the_block_template_skip_link' );
        }
	}

    /**
     * @return void
     */
	public function coming_soon(): void {
        // Do not cache coming soon page.
        if ( has_action( 'litespeed_purge_all' ) && !defined('DONOTCACHEPAGE') ) {
            define('DONOTCACHEPAGE', true);
        }

		if ( ! $this->can_bypass_coming_soon() ) {
			include_once HOSTINGER_ABSPATH . 'includes/Views/ComingSoon.php';
			die;
		}
	}

    /**
     * @return void
     */
	public function register_styles(): void {
		wp_enqueue_style(
            'hostinger_main_styles',
            HOSTINGER_ASSETS_URL . '/css/coming-soon.min.css',
            array(),
            HOSTINGER_VERSION
        );
	}

    /**
     * @param array $headers
     *
     * @return array
     */
    public function modify_headers( array $headers ): array {
        $headers['Cache-Control'] = 'no-cache';

        return $headers;
    }

    /**
     * @return bool
     */
    private function can_bypass_coming_soon(): bool {
        $bypass_code = $_COOKIE['hostinger_bypass_code'] ?? '';

        if( isset( $_GET['bypass_code'] ) && $this->plugin_options->get_bypass_code() == $_GET['bypass_code'] ) {
            setcookie( 'hostinger_bypass_code', $this->plugin_options->get_bypass_code() );
            $bypass_code = $this->plugin_options->get_bypass_code();
        }

        if( is_admin() ) {
            return true;
        }

        if( current_user_can( 'update_plugins' ) ) {
            return true;
        }

        if( !empty($bypass_code) && $bypass_code == $this->plugin_options->get_bypass_code() ) {
            return true;
        }

        return false;
    }
}

new ComingSoon();
