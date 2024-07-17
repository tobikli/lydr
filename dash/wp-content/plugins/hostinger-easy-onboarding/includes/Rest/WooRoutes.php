<?php
namespace Hostinger\EasyOnboarding\Rest;

use Hostinger\EasyOnboarding\Admin\Onboarding\Onboarding;
use Hostinger\EasyOnboarding\Admin\Onboarding\PluginManager;
use Hostinger\EasyOnboarding\Helper;

/**
 * Avoid possibility to get file accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class for handling WooCommerce related routes
 */
class WooRoutes {
    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function get_plugins( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $parameters = $request->get_params();

        $locale = !empty($parameters['locale']) ? sanitize_text_field($parameters['locale']) : '';
        $available_languages = get_available_languages();

        if (!empty($locale) && in_array($locale, $available_languages)) {
            switch_to_locale($locale);
        }

        $parameters = $request->get_params();

        $type = !empty($parameters['type']) ? $this->filter_allowed_types($parameters['type']) : '';

        $errors = array();

        if(empty($type)) {
            $errors['type'] = sprintf( __( '%s missing or empty', 'hostinger-easy-onboarding' ), 'type' );
        }

        $locale = get_option( 'woocommerce_default_country', false );

        if(empty($locale)) {
            $errors['locale'] = __( 'Shop locale is empty, please setup store first', 'hostinger-easy-onboarding' );
        }

        if ( ! empty( $errors ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Sorry, there are validation errors.', 'hostinger-easy-onboarding' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                    'errors' => $errors,
                )
            );
        }

        $plugin_manager = new PluginManager();

        $data = array(
            'plugins' => $plugin_manager->get_plugins_by_criteria( $type, $locale ),
            'locale' => get_option('woocommerce_default_country', '')
        );

        $response = new \WP_REST_Response( array( 'data' => $data ) );

        $response->set_headers( array( 'Cache-Control' => 'no-cache' ) );

        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function activate_plugin( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $parameters = $request->get_params();

        $plugin = !empty($parameters['plugin']) ? sanitize_text_field($parameters['plugin']) : '';

        $errors = array();

        if(empty($plugin)) {
            $errors['plugin'] = sprintf( __( '%s missing or empty', 'hostinger-easy-onboarding' ), 'plugin' );
        }

		$helper = new Helper();
        $plugin_path = $helper->get_plugin_main_file( $plugin );

        if ( is_plugin_active( $plugin_path ) ) {
            /* translators: %s plugin slug */
            $errors['plugin'] = sprintf( __( '%s is already active', 'hostinger-easy-onboarding' ), 'plugin' );
        }

        if ( ! empty( $errors ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Sorry, there are validation errors.', 'hostinger-easy-onboarding' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                    'errors' => $errors,
                )
            );
        }

        $activated = activate_plugin( $plugin_path );

        if ( is_wp_error( $activated ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Sorry, there are activation errors.', 'hostinger-easy-onboarding' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                    'errors' => $activated->get_error_message(),
                )
            );
        }

        $response = new \WP_REST_Response( array( 'data' => '' ) );

        $response->set_headers( array( 'Cache-Control' => 'no-cache' ) );

        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|\WP_REST_Response
     */
    public function woo_setup( \WP_REST_Request $request )
    {
        $parameters = $request->get_params();

        $fields = array(
            'store_name',
            'industry',
            'store_location',
            'business_email',
            'is_agree_marketing',
        );

        $boolean_fields = array(
            'is_agree_marketing'
        );

        $errors = array();

        foreach( $fields as $field ) {

            $formatted_field = str_replace( '_', ' ', $field );

            // Boolean field have bit different validation
            if ( in_array( $field, $boolean_fields ) ) {
                $field_is_valid = isset( $parameters[$field] );
            } else {
                $field_is_valid = !empty( $parameters[$field] );
            }

            if ( !$field_is_valid ) {
                $errors[$field] = sprintf( __( '%s missing or empty', 'hostinger-easy-onboarding' ), $formatted_field );
            } else {
                $parameters[$field] = sanitize_text_field( $parameters[$field] );
            }

        }

        $locale_info = include WC()->plugin_path() . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'locale-info.php';

        if(str_contains($parameters['store_location'], ':')) {
            $store_location = explode( ':', $parameters['store_location'] );
            $store_location = $store_location[0];
        } else {
            $store_location = $parameters['store_location'];
        }

        if ( empty( $locale_info[$store_location] ) ) {
            $errors['store_location'] = __( 'Store location locale not found', 'hostinger-easy-onboarding' );
        }

        if ( ! empty( $errors ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Sorry, there are validation errors.', 'hostinger-easy-onboarding' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                    'errors' => $errors,
                )
            );
        }

        $store_locale = $locale_info[$store_location];

        // Default WooCommerce values.
        update_option('woocommerce_default_country', $parameters['store_location'], true);
        update_option('woocommerce_allowed_countries', 'all', true);
        update_option('woocommerce_all_except_countries', [], true);
        update_option('woocommerce_specific_allowed_countries', [], true);
        update_option('woocommerce_specific_ship_to_countries', [], true);
        update_option('woocommerce_default_customer_address', 'base', true);
        update_option('woocommerce_calc_taxes', 'no', true);
        update_option('woocommerce_enable_coupons', 'yes', true);
        update_option('woocommerce_calc_discounts_sequentially', 'no', true);
        update_option('woocommerce_currency', $store_locale['currency_code'], true);
        update_option('woocommerce_currency_pos', $store_locale['currency_pos'], true);
        update_option('woocommerce_price_thousand_sep', $store_locale['thousand_sep'], true);
        update_option('woocommerce_price_decimal_sep', $store_locale['decimal_sep'], true);
        update_option('woocommerce_price_num_decimals', $store_locale['num_decimals'], true);
        update_option('woocommerce_weight_unit', $store_locale['weight_unit'], true);
        update_option('woocommerce_dimension_unit', $store_locale['dimension_unit'], true);

        $onboarding_profile = array();
        $onboarding_profile['is_store_country_set'] = true;
        $onboarding_profile['industry'] = array( $parameters['industry'] );
        $onboarding_profile['is_agree_marketing'] = $parameters['is_agree_marketing'];
        $onboarding_profile['store_email'] = $parameters['business_email'];
        $onboarding_profile['completed'] = true;
        $onboarding_profile['is_plugins_page_skipped'] = true;

        update_option('woocommerce_onboarding_profile', $onboarding_profile, true);

        $onboarding = new Onboarding();
        $onboarding->init();

        $onboarding->complete_step( Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID, 'setup_store' );

        if ( has_action( 'litespeed_purge_all' ) ) {
            do_action( 'litespeed_purge_all' );
        }

        $response = new \WP_REST_Response( array( ) );

        $response->set_headers(array('Cache-Control' => 'no-cache'));

        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param string $type
     *
     * @return void
     */
    private function filter_allowed_types( string $type ) {
        $allowed_types = array( 'shipping', 'payment' );

        return in_array( $type, $allowed_types ) ? $type : '';
    }
}
