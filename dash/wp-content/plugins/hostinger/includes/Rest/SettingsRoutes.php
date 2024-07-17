<?php
namespace Hostinger\Rest;

use Hostinger\Admin\Options\PluginOptions;
use Hostinger\Admin\PluginSettings;
use Hostinger\DefaultOptions;
use Hostinger\Helper;

/**
 * Avoid possibility to get file accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class for handling Settings Rest API
 */
class SettingsRoutes {
    /**
     * @var PluginSettings plugin settings.
     */
    private PluginSettings $plugin_settings;

    /**
     * Construct class with dependencies
     *
     * @param PluginSettings $plugin_settings instance.
     */
    public function __construct( PluginSettings $plugin_settings ) {
        $this->plugin_settings = $plugin_settings;
    }

    /**
     * Return all stored plugin settings
     *
     * @param WP_REST_Request $request WordPress rest request.
     *
     * @return \WP_REST_Response
     */
    public function get_settings( \WP_REST_Request $request ): \WP_REST_Response {
        global $wp_version;

        $data = array(
            'newest_wp_version' => $this->get_latest_wordpress_version(),
            'current_wp_version' => $wp_version,
            'php_version' => phpversion(),
            'newest_php_version' => '8.1', // Will be refactored.
            'is_eligible_www_redirect' => $this->is_eligible_www_redirect(),
        );

        // If it is not set for some reason then set it.
        if(empty($this->plugin_settings->get_plugin_settings()->get_bypass_code())) {
            $options = new DefaultOptions();
            $options->install_bypass_code();
        }

        $plugin_settings = $this->plugin_settings->get_plugin_settings()->to_array();

        $response = array(
            'data' => array_merge($data, $plugin_settings)
        );

        $response = new \WP_REST_Response( $response );

        $response->set_headers(array('Cache-Control' => 'no-cache'));

        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function regenerate_bypass_code( \WP_REST_Request $request ): \WP_REST_Response {
        $settings = $this->plugin_settings->get_plugin_settings();

        $settings->set_bypass_code( Helper::generate_bypass_code( 16 ) );

        $new_settings = $settings->to_array();

        $new_plugin_options = new PluginOptions( $new_settings );

        $response = new \WP_REST_Response( array( 'data' => $this->plugin_settings->save_plugin_settings( $new_plugin_options )->to_array() ) );

        $response->set_headers(array('Cache-Control' => 'no-cache'));

        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function update_settings( \WP_REST_Request $request ): \WP_REST_Response {
        $settings = $this->plugin_settings->get_plugin_settings();

        $new_settings = array();

        $parameters = $request->get_params();

        foreach ( $settings->to_array() as $field_key => $field_value ) {
            // Don't allow to change bypass code
            if($field_key == 'bypass_code') {
                $new_settings[ $field_key ] = $field_value;
                continue;
            }

            if ( isset( $parameters[ $field_key ] ) ) {
                $new_settings[ $field_key ] = !empty( $parameters[ $field_key ] );
            } else {
                $new_settings[ $field_key ] = $field_value;
            }
        }

        $new_plugin_options = new PluginOptions( $new_settings );

        $response = new \WP_REST_Response( array( 'data' => $this->plugin_settings->save_plugin_settings( $new_plugin_options )->to_array() ) );

        $this->update_urls( $new_plugin_options );

        $response->set_headers(array('Cache-Control' => 'no-cache'));

        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param PluginOptions $plugin_options
     *
     * @return bool
     */
    private function update_urls( PluginOptions $plugin_options ): bool {
        $siteurl = get_option( 'siteurl' );
        $home = get_option( 'home' );

        if( empty( $siteurl ) || empty( $home ) ) {
            return false;
        }

        if( $plugin_options->get_force_https() ) {
            $siteurl = str_replace( 'http://', 'https://', $siteurl);
            $home = str_replace( 'http://', 'https://', $home);
        }

        if( $plugin_options->get_force_www() ) {
            $siteurl = $this->add_www_to_url( $siteurl );
            $home = $this->add_www_to_url( $home );
        } else {
            $siteurl = str_replace( 'www.', '', $siteurl);
            $home = str_replace( 'www.', '', $home);
        }

        update_option( 'siteurl', $siteurl );
        update_option( 'home', $home );

        return true;
    }

    /**
     * @param string $url
     *
     * @return mixed
     */
    private function add_www_to_url( string $url ): string {
        $parsedUrl = parse_url( $url );

        if ( isset( $parsedUrl['host'] ) ) {
            $host = $parsedUrl['host'];

            if ( strpos($host, 'www.') !== 0 ) {
                $host = 'www.' . $host;
            }

            $parsedUrl['host'] = $host;

            return $this->rebuild_url($parsedUrl);
        }

        return $url;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function rebuild_url( array $params ): string {
        $scheme   = isset($params['scheme']) ? $params['scheme'] . '://' : '';
        $host     = isset($params['host']) ? $params['host'] : '';
        $path     = isset($params['path']) ? $params['path'] : '';
        $query    = isset($params['query']) ? '?' . $params['query'] : '';
        $fragment = isset($params['fragment']) ? '#' . $params['fragment'] : '';

        return "$scheme$host$path$query$fragment";
    }

    /**
     * @return string
     */
    private function get_latest_wordpress_version(): string {
        $newest_wordpress_version = get_transient('hostinger_newest_wordpress_version');

        if ($newest_wordpress_version !== false) {
            return $newest_wordpress_version;
        }

        $newest_wordpress_version = $this->fetch_wordpress_version();

        if(!empty($newest_wordpress_version)) {
            set_transient('hostinger_newest_wordpress_version', $newest_wordpress_version, 86400);

            return $newest_wordpress_version;
        }

        return '';
    }

    /**
     * @return string
     */
    private function fetch_wordpress_version(): string {
        $url = "https://api.wordpress.org/core/version-check/1.7/";

        $response = file_get_contents($url);

        if ($response !== false) {
            $data = json_decode($response);

            return !empty($data->offers[0]->current) ? $data->offers[0]->current : '';
        }

        return '';
    }

    /**
     * @return bool
     */
    private function is_eligible_www_redirect(): bool {
        $is_eligible_www_redirect = get_transient('hostinger_is_eligible_www_redirect');

        if ($is_eligible_www_redirect !== false) {
            return $is_eligible_www_redirect;
        }

        $domain = str_replace('www.', '', get_option( 'siteurl' ));
        $www_domain = $this->add_www_to_url( $domain );

        $is_eligible_www_redirect = $this->check_domain_records($domain, $www_domain);

        if(isset($is_eligible_www_redirect)) {
            set_transient('hostinger_is_eligible_www_redirect', $is_eligible_www_redirect, 120);

            return $is_eligible_www_redirect;
        }

        return '';
    }

    /**
     * @param string $domain_a
     * @param string $domain_b
     *
     * @return bool
     */
    private function check_domain_records(string $domain_a, string $domain_b): bool {
        return (gethostbyname($domain_a) === gethostbyname($domain_b));
    }
}