<?php

namespace Hostinger\EasyOnboarding\Admin\Onboarding;

defined( 'ABSPATH' ) || exit;

class WelcomeCards {
    public function get_welcome_cards(): array {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $welcome_cards = array();

        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            $welcome_cards[] = array(
                'id' => 'woocommerce',
                'image' => 'setup-online-store.png',
                'title' => __( 'Set up an online store', 'hostinger-easy-onboarding' ),
                'link' => 'admin.php?page=hostinger-get-onboarding&subPage=hostinger-store-setup-information',
                'description' => wp_kses( __( 'Install <strong>WooCommerce</strong>, add products or services, and start selling today.', 'hostinger-easy-onboarding' ), array( 'strong' => array() ) ),
            );
        }

        if ( is_plugin_active( 'hostinger-affiliate-plugin/hostinger-affiliate-plugin.php' ) ) {
            $welcome_cards[] = array(
                'id' => 'affiliate',
                'image' => 'run-amazon-affiliate-site.png',
                'title' => __( 'Run an Amazon Affiliate site', 'hostinger-easy-onboarding' ),
                'link' => admin_url( 'admin.php?page=hostinger-amazon-affiliate' ),
                'description' => wp_kses( __( 'Connect your <strong>Amazon Associate</strong> account to fetch API details.', 'hostinger-easy-onboarding' ), array( 'strong' => array() ) ),
            );
        }

        if ( is_plugin_active( 'hostinger-ai-assistant/hostinger-ai-assistant.php' ) ) {
            $welcome_cards[] = array(
                'id' => 'ai',
                'image' => 'generate-content-with-ai.png',
                'title' => __( 'Generate content with AI', 'hostinger-easy-onboarding' ),
                'link' => admin_url( 'admin.php?page=hostinger-ai-assistant' ),
                'description' => wp_kses( __( 'Get images, text, and SEO keywords created for you instantly.', 'hostinger-easy-onboarding' ), array( 'strong' => array() ) ),
            );
        }

        return $welcome_cards;
    }
}