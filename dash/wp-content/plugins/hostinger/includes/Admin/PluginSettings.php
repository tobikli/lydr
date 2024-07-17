<?php
namespace Hostinger\Admin;

use Hostinger\Admin\Options\PluginOptions;

/**
 * Avoid possibility to get file accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class for handling Settings
 */
class PluginSettings {

    /**
     * @var PluginOptions
     */
    private ?PluginOptions $plugin_options = null;

    /**
     * @param PluginOptions|null $plugin_options
     */
    public function __construct( PluginOptions $plugin_options = null ) {
        if ( ! empty( $plugin_options ) ) {
            $this->plugin_options = $plugin_options;
        }
    }

    /**
     * Return plugin settings
     *
     * @return PluginOptions
     */
    public function get_plugin_settings( ): PluginOptions {

        if ( ! empty( $this->plugin_options ) ) {

            $settings = $this->plugin_options;

        } else {
            $settings = get_option(
                HOSTINGER_PLUGIN_SETTINGS_OPTION,
                array()
            );

            $settings = new PluginOptions( $settings );
        }

        return $settings;
    }

    /**
     * @param PluginOptions $plugin_options plugin settings.
     *
     * @return PluginOptions
     */
    public function save_plugin_settings( PluginOptions $plugin_options ): PluginOptions {
        $existing_settings = $this->get_plugin_settings();

        $update = update_option( HOSTINGER_PLUGIN_SETTINGS_OPTION, $plugin_options->to_array(), false );

        if ( has_action( 'litespeed_purge_all' ) ) {
            do_action( 'litespeed_purge_all' );
        }

        return ! empty( $update ) ? $plugin_options : $existing_settings;
    }
}