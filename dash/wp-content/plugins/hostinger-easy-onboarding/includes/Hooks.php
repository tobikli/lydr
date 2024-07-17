<?php

namespace Hostinger\EasyOnboarding;

use Hostinger\EasyOnboarding\AmplitudeEvents\Amplitude;

defined( 'ABSPATH' ) || exit;

class Hooks {

    public function __construct() {
        add_action( 'init', array( $this, 'check_url_and_flush_rules' ) );
        add_action( 'template_redirect', array( $this, 'admin_preview_website' ) );

        add_filter( 'hostinger_once_per_day_events', array( $this, 'limit_triggered_amplitude_events' ) );
    }

    public function check_url_and_flush_rules() {
        if ( defined( 'DOING_AJAX' ) && \DOING_AJAX ) {
            return false;
        }

        $current_url    = home_url( add_query_arg( null, null ) );
        $url_components = wp_parse_url( $current_url );

        if ( isset( $url_components['query'] ) ) {
            parse_str( $url_components['query'], $params );

            if ( isset( $params['app_name'] ) ) {
                $app_name = sanitize_text_field( $params['app_name'] );

                if ( $app_name === 'Omnisend App' ) {
                    if ( function_exists( 'flush_rewrite_rules' ) ) {
                        flush_rewrite_rules();
                    }

                    if ( has_action( 'litespeed_purge_all' ) ) {
                        do_action( 'litespeed_purge_all' );
                    }
                }
            }
        }
    }

    public function admin_preview_website() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        $amplitude = new Amplitude();

        $appearance = get_option( 'hostinger_appearance', 'none' );
        $subscription_id = get_option( 'hostinger_subscription_id', 0 );

        $params = array(
            'action' => 'wordpress.preview_site',
            'appearance' => $appearance,
            'subscription_id' => $subscription_id
        );

        $amplitude->send_event($params);
    }

    public function limit_triggered_amplitude_events( $events ): array {
        $new_events = [
            'wordpress.preview_site',
            'wordpress.easy_onboarding.enter',
        ];

        return array_merge($events, $new_events);
    }
}