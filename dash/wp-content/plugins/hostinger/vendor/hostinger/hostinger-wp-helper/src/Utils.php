<?php

namespace Hostinger\WpHelper;

class Utils {

	private static string $apiTokenFile;

	private const HPANEL_DOMAIN_URL = 'https://hpanel.hostinger.com/websites/';
	private const HOSTINGER_SITE = '.hostingersite.com';

	private static function getApiTokenPath(): void {
		$hostingerDirParts = explode( '/', __DIR__ );
		if ( count( $hostingerDirParts ) >= 3 ) {
			$hostingerServerRootPath = '/' . $hostingerDirParts[1] . '/' . $hostingerDirParts[2];
			self::$apiTokenFile      = $hostingerServerRootPath . '/.api_token';
		}
	}

    /**
     * @param string $pluginSlug
     *
     * @return bool
     */
	// Check if a specific plugin is active by its slug
    public static function isPluginActive( string $pluginSlug ): bool {
        $plugin_relative_path = $pluginSlug . '/' . $pluginSlug . '.php';

        if ( is_multisite() ) {
            return self::checkIsPluginActiveMultiSite( $plugin_relative_path );
        }

        return self::checkIsPluginActive( $plugin_relative_path );
    }

    /**
     * @param string $plugin_relative_path
     *
     * @return bool
     */
    public static function checkIsPluginActiveMultiSite( string $plugin_relative_path ): bool {
        // Check network-wide active plugins
        $activePlugins = get_site_option( 'active_sitewide_plugins', [] );
        if ( in_array( $plugin_relative_path, $activePlugins ) ) {
            return true;
        }

        // Check each site in the network
        $sites = get_sites();
        foreach ( $sites as $site ) {
            switch_to_blog( $site->blog_id );
            $activePlugins = get_option( 'active_plugins', [] );
            if ( in_array( $plugin_relative_path, $activePlugins ) ) {
                restore_current_blog();

                return true;
            }
            restore_current_blog();
        }

        return false;
    }

    /**
     * @param string $plugin_relative_path
     *
     * @return bool
     */
    public static function checkIsPluginActive( string $plugin_relative_path ): bool {
        // Check active plugins in a single site
        $activePlugins = get_option( 'active_plugins', [] );

        if ( in_array( $plugin_relative_path, $activePlugins ) ) {
            return true;
        }

        return false;
    }

	// Get the content of the API token file
	public static function getApiToken(): string {
		self::getApiTokenPath();

		if ( file_exists( self::$apiTokenFile ) ) {
			$apiToken = file_get_contents( self::$apiTokenFile );
			if ( ! empty( $apiToken ) ) {
				return $apiToken;
			}
		}

		return '';
	}

	// Get the host info (domain, subdomain, subdirectory)
	public function getHostInfo(): string {
		$host     = $_SERVER['HTTP_HOST'] ?? '';
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

	// Check if the current domain is a preview domain
	public function isPreviewDomain(): bool {
		if ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
		}

		if ( isset( $headers['X-Preview-Indicator'] ) && $headers['X-Preview-Indicator'] ) {
			return true;
		}

		return false;
	}

	// Check if the current page is the specified page
	public function isThisPage( string $page ): bool {

		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$current_uri = sanitize_text_field( $_SERVER['REQUEST_URI'] );

		if ( defined( 'DOING_AJAX' ) && \DOING_AJAX ) {
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

	// Get Hpanel domain URL
	public function getHpanelDomainUrl(): string {
		$parsed_url = parse_url(get_site_url());
		$host = $parsed_url['host'];
		$path = isset($parsed_url['path']) ? trim($parsed_url['path'], '/') : '';

		// Remove 'www.' if it exists
		if (strpos($host, 'www.') === 0) {
			$host = substr($host, 4);
		}

		// Function to check if the domain is a preview domain
		if ($this->isPreviewDomain()) {
			// Handle preview domain logic
			$host_parts = explode('.', $host);
			$base_domain = str_replace('-', '.', $host_parts[0]);
			return self::HPANEL_DOMAIN_URL . "$base_domain" . '.' . end($host_parts);
		}

		// Identify whether it is a subdomain or not
		$host_parts = explode('.', $host);
		$is_subdomain = count($host_parts) > 2;

		// Construct the base domain and full domain
		if ($is_subdomain) {
			$base_domain = implode('.', array_slice($host_parts, -2));
			$full_domain = $host;
		} else {
			$base_domain = $host;
			$full_domain = $host;
		}

		// Adjust for free subdomains which have a specific pattern (like hostingersite.com)
		$free_subdomain_pattern = self::HOSTINGER_SITE;
		if (substr($host, -strlen($free_subdomain_pattern)) === $free_subdomain_pattern) {
			$base_domain = $full_domain;
		}

		// Construct the URL
		$url = self::HPANEL_DOMAIN_URL . "$base_domain/wordpress/dashboard/$full_domain";

		// Append subdirectory if it exists
		if ($path) {
			$url .= "/$path";
		}

		return $url;
	}

	// Check transient eligibility
	public function checkTransientEligibility( $transient_request_key, $cache_time = 3600 ): bool {
		try {
			// Set transient
			set_transient( $transient_request_key, true, $cache_time );

			// Check if transient was set successfully
			if ( false === get_transient( $transient_request_key ) ) {
				throw new \Exception( 'Unable to create transient in WordPress.' );
			}

			// If everything is fine, return true
			return true;
		} catch ( \Exception $exception ) {
			// If there's an exception, log the error and return false
			$this->errorLog( 'Error checking eligibility: ' . $exception->getMessage() );

			return false;
		}
	}

	public function errorLog( string $message ): void {
		if ( defined( 'WP_DEBUG' ) && \WP_DEBUG === true ) {
			error_log( print_r( $message, true ) );
		}
	}

	public static function getSetting( string $setting ): string {

		if ( $setting ) {
			return get_option( 'hostinger_' . $setting, '' );
		}

		return '';
	}

	public static function updateSetting( string $setting, $value, $autoload = null ): void {

		if ( $setting ) {
			update_option( 'hostinger_' . $setting, $value, $autoload );
		}
	}


}
