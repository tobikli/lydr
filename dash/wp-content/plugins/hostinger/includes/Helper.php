<?php

namespace Hostinger;

defined( 'ABSPATH' ) || exit;

class Helper {
	public const HOSTINGER_FREE_SUBDOMAIN_URL = 'hostingersite.com';
	public const HOSTINGER_PAGE = '/wp-admin/admin.php?page=hostinger';
	public const CLIENT_WOO_COMPLETED_ACTIONS = 'woocommerce_task_list_tracked_completed_tasks';
	private const PROMOTIONAL_LINKS = array(
		'fr_FR' => 'https://www.hostinger.fr/cpanel-login?r=%2Fjump-to%2Fnew-panel%2Fsection%2Freferrals&utm_source=Banner&utm_medium=HostingerWPplugin',
		'es_ES' => 'https://www.hostinger.es/cpanel-login?r=%2Fjump-to%2Fnew-panel%2Fsection%2Freferrals&utm_source=Banner&utm_medium=HostingerWPplugin',
		'ar'    => 'https://www.hostinger.ae/cpanel-login?r=%2Fjump-to%2Fnew-panel%2Fsection%2Freferrals&utm_source=Banner&utm_medium=HostingerWPplugin',
		'zh_CN' => 'https://www.hostinger.com.hk/cpanel-login?r=%2Fjump-to%2Fnew-panel%2Fsection%2Freferrals&utm_source=Banner&utm_medium=HostingerWPplugin',
		'id_ID' => 'https://www.hostinger.co.id/cpanel-login?r=%2Fjump-to%2Fnew-panel%2Fsection%2Freferrals&utm_source=Banner&utm_medium=HostingerWPplugin',
		'lt_LT' => 'https://www.hostinger.lt/cpanel-login?r=%2Fjump-to%2Fnew-panel%2Fsection%2Freferrals&utm_source=Banner&utm_medium=HostingerWPplugin',
		'pt_PT' => 'https://www.hostinger.pt/cpanel-login?r=%2Fjump-to%2Fnew-panel%2Fsection%2Freferrals&utm_source=Banner&utm_medium=HostingerWPplugin',
		'uk'    => 'https://www.hostinger.com.ua/cpanel-login?r=%2Fjump-to%2Fnew-panel%2Fsection%2Freferrals&utm_source=Banner&utm_medium=HostingerWPplugin',
		'tr_TR' => 'https://www.hostinger.com.tr/cpanel-login?r=%2Fjump-to%2Fnew-panel%2Fsection%2Freferrals&utm_source=Banner&utm_medium=HostingerWPplugin',
		'en_US' => 'https://www.hostinger.com/cpanel-login?r=%2Fjump-to%2Fnew-panel%2Fsection%2Freferrals&utm_source=Banner&utm_medium=HostingerWPplugin',
	);

	private const HPANEL_DOMAIN_URL = 'https://hpanel.hostinger.com/websites/';

	/**
	 *
	 * Check if plugin is active
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public static function is_plugin_active( $plugin_slug ): bool {
		$active_plugins = (array) get_option( 'active_plugins', array() );
		foreach ( $active_plugins as $active_plugin ) {
			if ( strpos( $active_plugin, $plugin_slug . '.php' ) !== false ) {
				return true;
			}
		}

		return false;
	}

	public static function get_api_token(): string {
		$api_token  = '';
		$token_file = HOSTINGER_WP_TOKEN;

		if ( file_exists( $token_file ) && ! empty( file_get_contents( $token_file ) ) ) {
			$api_token = file_get_contents( $token_file );
		}

		return $api_token;
	}

	/**
	 *
	 * Get the host info (domain, subdomain, subdirectory)
	 *
	 * @since    1.7.0
	 * @access   public
	 */
	public function get_host_info(): string {
		$host     = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : '';
		$site_url = get_site_url();
		$site_url = preg_replace( '#^https?://#', '', $site_url );

		if ( ! empty( $site_url ) && ! empty( $host ) && strpos( $site_url, $host ) === 0 ) {
			if ( $site_url === $host ) {
				return $host;
			} else {
				return substr( $site_url, strlen( $host ) + 1 );
			}
		}

		return $host;
	}

	public function is_preview_domain(): bool {
		if ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
		}

		if ( isset( $headers['X-Preview-Indicator'] ) && $headers['X-Preview-Indicator'] ) {
			return true;
		}

		return false;
	}

	public function is_free_subdomain(): bool {
		$site_url = preg_replace( '#^https?://#', '', get_site_url() );

		return ! empty( $site_url ) && strpos( $site_url, self::HOSTINGER_FREE_SUBDOMAIN_URL ) !== false;
	}

	public function is_hostinger_admin_page(): bool {

		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$current_uri = sanitize_text_field( $_SERVER['REQUEST_URI'] );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return false;
		}

		if ( isset( $current_uri ) && strpos( $current_uri, '/wp-json/' ) !== false ) {
			return false;
		}

		if ( strpos( $current_uri, self::HOSTINGER_PAGE ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 *
	 * Error log
	 *
	 * @since    1.9.6
	 * @access   public
	 */
	public function error_log( string $message ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
			error_log( print_r( $message, true ) );
		}
	}

	public function default_woocommerce_survey_steps_completed( array $steps ): bool {
		$completed_actions = get_option( self::CLIENT_WOO_COMPLETED_ACTIONS, array() );

		return empty( array_diff( $steps, $completed_actions ) );
	}

	public function is_this_page( string $page ): bool {

		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$current_uri = sanitize_text_field( $_SERVER['REQUEST_URI'] );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return false;
		}

		if ( isset( $current_uri ) && strpos( $current_uri, '/wp-json/' ) !== false ) {
			return false;
		}

		if ( strpos( $current_uri, $page ) !== false ) {
			return true;
		}

		return false;
	}

	public function get_promotional_link_url( string $locale ): string {
		if ( isset( self::PROMOTIONAL_LINKS[ $locale ] ) ) {
			return self::PROMOTIONAL_LINKS[ $locale ];
		}

		return self::PROMOTIONAL_LINKS['en_US'];
	}

	public function get_hpanel_domain_url(): string {
		$parsed_url = parse_url( get_site_url() );
		$host       = $parsed_url['host'];
		$host_parts = explode( '.', $host );
		$subdomain  = ( count( $host_parts ) > 2 ) ? array_shift( $host_parts ) . '.' : '';
		$domain     = implode( '.', $host_parts );

		return self::HPANEL_DOMAIN_URL . $domain . ( $subdomain ? "/wordpress/dashboard/$subdomain$domain" : '' );
	}

	public function check_transient_eligibility( $transient_request_key, $cache_time = 3600 ): bool {
		try {
			// Set transient
			set_transient( $transient_request_key, true, $cache_time );

			// Check if transient was set successfully
			if ( false === get_transient( $transient_request_key ) ) {
				throw new Exception( 'Unable to create transient in WordPress.' );
			}

			// If everything is fine, return true
			return true;
		} catch ( Exception $exception ) {
			// If there's an exception, log the error and return false
			$this->error_log( 'Error checking eligibility: ' . $exception->getMessage() );

			return false;
		}
	}

	public static function woocommerce_onboarding_choice(): bool {
		return (bool) get_option( 'hostinger_woo_onboarding_choice', false );
	}

	/**
	 * @return bool
	 */
	public static function is_woocommerce_site(): bool {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * @return bool
	 */
	public static function show_woocommerce_onboarding(): bool {
		$woo_onboarding_enabled     = get_option( 'hostinger_woo_onboarding_enabled', false );
		$woo_setup_wizard_completed = get_option( 'woocommerce_onboarding_profile', false );

		return ( self::is_woocommerce_site() && ! self::woocommerce_onboarding_choice() && $woo_onboarding_enabled && ! $woo_setup_wizard_completed );
	}

	/**
	 * @return bool
	 */
	public function can_show_store_ready_message(): bool {
		if ( ! self::is_woocommerce_site() || ! self::woocommerce_onboarding_choice() ) {
			return false;
		}

		$store_ready_message_shown = get_option( 'hostinger_woo_ready_message_shown', null );

		if ( $store_ready_message_shown === null ) {
			return false;
		}

		if ( (int) $store_ready_message_shown !== 0 ) {
			return false;
		}

		if ( ! $this->default_woocommerce_survey_completed() ) {
			return false;
		}

		return true;
	}

	public function default_woocommerce_survey_completed(): bool {
		$completed_actions          = get_option( self::CLIENT_WOO_COMPLETED_ACTIONS, array() );
		$required_completed_actions = array( 'products', 'payments' );

		return empty( array_diff( $required_completed_actions, $completed_actions ) );
	}

    /**
     * @return void
     */
    public static function generate_bypass_code( $length ) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $code = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $randomIndex = mt_rand(0, $maxIndex);
            $code .= $characters[$randomIndex];
        }

        return $code;
    }
}

$hostinger_helper = new Helper();
