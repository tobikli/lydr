<?php
namespace Hostinger\Rest;

/**
 * Avoid possibility to get file accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class for handling Rest Api Routes
 */
class Routes {
    /**
     * @var Settings
     */
    private SettingsRoutes $settings_routes;

    /**
     * @param SettingsRoutes $settings_routes Settings route class.
     */
    public function __construct( SettingsRoutes $settings_routes ) {
        $this->settings_routes = $settings_routes;
    }

    /**
     * Init rest routes
     *
     * @return void
     */
    public function init(): void {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * @return void
     */
    public function register_routes() {
        // Register Settings Rest API Routes.
        $this->register_settings_routes();
    }

    /**
     *
     * @return void
     */
    private function register_settings_routes(): void {
        // Return settings.
        register_rest_route(
            HOSTINGER_PLUGIN_REST_API_BASE,
            'get-settings',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this->settings_routes, 'get_settings' ),
                'permission_callback' => array( $this, 'permission_check' ),
            )
        );

        // Update settings.
        register_rest_route(
            HOSTINGER_PLUGIN_REST_API_BASE,
            'update-settings',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this->settings_routes, 'update_settings' ),
                'permission_callback' => array( $this, 'permission_check' ),
            )
        );

        // Regenerate bypass link.
        register_rest_route(
            HOSTINGER_PLUGIN_REST_API_BASE,
            'regenerate-bypass-code',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this->settings_routes, 'regenerate_bypass_code' ),
                'permission_callback' => array( $this, 'permission_check' ),
            )
        );
    }

    /**
     * @param WP_REST_Request $request WordPress rest request.
     *
     * @return bool
     */
    public function permission_check( $request ): bool {
        // Workaround if Rest Api endpoint cache is enabled.
        // We don't want to cache these requests.
        if( has_action('litespeed_control_set_nocache') ) {
            do_action(
                'litespeed_control_set_nocache',
                'Custom Rest API endpoint, not cacheable.'
            );
        }

        if ( empty( is_user_logged_in() ) ) {
            return false;
        }

        // Implement custom capabilities when needed.
        return current_user_can( 'manage_options' );
    }
}