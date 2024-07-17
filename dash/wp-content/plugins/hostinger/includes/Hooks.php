<?php

namespace Hostinger;

use Hostinger\Admin\PluginSettings;
use Hostinger\WpHelper\Utils;

defined( 'ABSPATH' ) || exit;

class Hooks {
	public function __construct() {
        // XMLRpc / Force SSL
        add_filter( 'xmlrpc_enabled', array( $this, 'check_xmlrpc_enabled' ) );
        add_filter( 'wp_headers', array( $this, 'check_pingback' ));
        add_filter( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

    /**
     * @return void
     */
    public function plugins_loaded() {
        $utils = new Utils();
        $plugin_settings = new PluginSettings();
        $settings = $plugin_settings->get_plugin_settings();

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            return;
        }

        // Xmlrpc.
        if($settings->get_disable_xml_rpc() && $utils->isThisPage('xmlrpc.php')) {
            exit('Disabled');
        }

        // SSL redirect.
        if($settings->get_force_https() && !is_ssl()) {
            $host = $_SERVER['HTTP_HOST'];

            if ($settings->get_force_www() && strpos($_SERVER['HTTP_HOST'], 'www.') === false) {
                $host = 'www.'.$host;
            }

            wp_redirect('https://' . $host . $_SERVER['REQUEST_URI'], 301);
            exit();
        }

        // Force www.
        if ($settings->get_force_www() && strpos($_SERVER['HTTP_HOST'], 'www.') === false) {
            wp_redirect('https://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
            exit;
        }
    }

    /**
     * @param $headers
     *
     * @return mixed
     */
    public function check_pingback( $headers ) {
        $plugin_settings = new PluginSettings();
        $settings = $plugin_settings->get_plugin_settings();

        if($settings->get_disable_xml_rpc()) {
            unset($headers['X-Pingback']);
        }

        return $headers;
    }

    /**
     * @return bool
     */
    public function check_xmlrpc_enabled(): bool {
        $plugin_settings = new PluginSettings();
        $settings = $plugin_settings->get_plugin_settings();

        if($settings->get_disable_xml_rpc()) {
            return false;
        }

        return true;
    }
}
