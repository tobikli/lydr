<?php
/**
 * Plugin Name: AyeCode Connect - Early get_plugins filter
 * Plugin URI: https://ayecode.io/
 * Description: Fix the issue where another plugin is calling get_plugins() too early which disables future filters.
 * Version: 1.0.0
 * Author: AyeCode Ltd
 * Author URI: https://ayecode.io/
 * License: GPL-2.0+
 * License: GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 3.1
 * Tested up to: 5.5
 */

/*
 * Set a constant so we can check if the plugin is active.
 */
define("AYECODE_CONNECT_GET_PLUGINS_FILTER_FIX",true);

/**
 * If another plugin calls get_plugins() too early then some filters will not work, this adds our filter early.
 *
 * @param array $headers
 *
 * @return array
 */
function ayecode_connect_early_get_plugins_filter_fix( $headers = array() ) {
    $headers_extra = array(
        'UpdateURL' => 'Update URL',
        'UpdateID' => 'Update ID',
    );
 
    $all_headers = array_merge( $headers_extra, (array) $headers );
 
    return $all_headers;
}
add_filter( 'extra_plugin_headers', 'ayecode_connect_early_get_plugins_filter_fix' );
