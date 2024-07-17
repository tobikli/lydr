<?php
/**
 * This class enables two-way communication between a Client's WordPress website and a remote WordPress website
 *
 * @link https://github.com/AyeCode/wp-service-provider - The server class
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AyeCode_Connect' ) ) :

	/**
	 * The  Connection class that is used as a single gateway between remote host and this plugin
	 */
	class AyeCode_Connect {

		/**
		 * Remote URL base
		 *
		 * @var string
		 */
		public $remote_url = '';

		/**
		 * Remote connection URL
		 *
		 * @var string
		 */
		public $connection_url = '';

		/**
		 * Remote api URL
		 *
		 * @var string
		 */
		public $api_url = '';

		/**
		 * Remote api URL namespace
		 *
		 * @var string
		 */
		public $api_namespace = 'ayecode/v1';

		/**
		 * Local api URL namespace
		 *
		 * @var string
		 */
		public $local_api_namespace = 'ayecode-connect/v1';

		/**
		 * Prefix
		 *
		 * @var string
		 */
		public $prefix = 'ayecode_connect';

		/**
		 * Prefix
		 *
		 * @var string
		 */
		public $textdomain = 'ayecode-connect';

		/**
		 * Version
		 *
		 * @var string
		 */
		public $version = '';

		/**
		 * If debugging is enabled.
		 *
		 * @var
		 */
		public $debug = false;

		/**
		 * Class constructor
		 *
		 */
		public function __construct( array $args = array() ) {
			foreach ( $args as $key => $value ) {
				$this->{$key} = $value;
			}

			$this->api_url       = trailingslashit( $this->api_url );
			$this->api_namespace = ltrim( $this->api_namespace, '/' );
		}

		/**
		 * Initializes required listeners.
		 *
		 */
		public function init() {
			if ( $this->is_active() ) {
				// Connected
				do_action( $this->prefix . '_connected_to_remote' );
				add_action( 'rest_api_init', array( $this, 'register_connected_routes' ) );
				add_action( 'edd_api_button_args', array( $this, 'edd_api_button_args' ), 8 );
				add_action( 'admin_init', array( $this, 'check_for_url_change') );
				add_filter( 'upgrader_post_install',array( $this, 'maybe_sync_licenses'),10,3);

				// Support Widget
				if ( is_admin() ) {
					require_once plugin_dir_path( __FILE__ ) . 'class-ayecode-connect-support.php';

					$support_args = array(
						'prefix'=>$this->prefix,
						'name'  => $this->get_connected_name(),
						'email'  => $this->get_connected_email(),
						'enabled'  => get_option( $this->prefix . '_support', true ),
						'support_user'  => get_option( $this->prefix . '_support_user', true ),
					);

					new AyeCode_Connect_Support( $support_args );
				}

				// Maybe show connected notice
				if ( is_admin() && isset( $_REQUEST['ayecode-connected'] ) ) {
					add_action( 'admin_notices', array( $this, 'connected_notice' ) );
				}
			} else {
				// Not Connected
				add_action( 'rest_api_init', array( $this, 'register_connection_routes' ) );
				add_action( 'init', array( $this, 'maybe_redirect_to_connection_page' ) );
				add_action( 'admin_notices', array( $this, 'website_url_change_error') );
				do_action( $this->prefix . '_not_connected_to_remote' );
			}

			// Register test route
			add_action( 'rest_api_init', array( $this, 'register_test_routes' ) );

			// Register blocks
			add_action( 'widgets_init', array( $this, 'register_blocks' ) );
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );

			if ( is_admin() ) {
				// Add AUI on our backend pages
				add_filter( 'aui_screen_ids', array( $this, 'add_aui_screens') );

				// Check for demo site redirect
				add_action( 'current_screen', array( $this, 'demo_site_redirect' ) );
			}
		}

		/**
		 * Enqueue editor JS required for blocks.
		 *
		 * @return void
		 */
		public function enqueue_editor_assets() {
			if ( $this->maybe_load_blocks() ) {
				global $pagenow,$wp_version;

				$AyeCode_Connect_Settings = AyeCode_Connect_Settings::instance();
				$deps = ( $pagenow == 'widgets.php' || ( $pagenow == 'site-editor.php' && ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) ) ) && version_compare( $wp_version, '5.8', '>=' ) ? array( 'wp-blocks', 'wp-element') : array( 'wp-blocks', 'wp-element', 'wp-editor' );
				wp_enqueue_script(
					'ayecode-editor',
					$AyeCode_Connect_Settings->base_url  . 'assets/js/blocks.js',
					$deps,
					AYECODE_CONNECT_VERSION
				);
			}
		}

		/**
		 * Check if we should load our blocks, check if one of our plugin are active.
		 *
		 * @return mixed|null
		 */
		public function maybe_load_blocks() {
			$load = false;

			if ( 
				defined( 'GEODIRLOCATION_VERSION' )
				|| defined( 'USERSWP_VERSION' )
				//|| defined( 'WPINV_VERSION' ) // not currently used
			) {
				$load = true;
			}

			return apply_filters( $this->prefix . '_load_blocks', $load );
		}

		/**
		 * Register blocks if one of our plugins active.
		 *
		 * @return void
		 */
		public function register_blocks() {
			if ( $this->maybe_load_blocks() ) {
				require_once plugin_dir_path( __FILE__ ) . '../blocks/class-ayecode-wp-nav.php';
				register_widget( 'AyeCode_WP_Nav' );
			}
		}

		public function demo_site_redirect(){
			$currentScreen = get_current_screen();

			if ( $currentScreen->id === "plugin-install" && ! empty( $_REQUEST['ac-demo-import'] ) ) {
				// If installed and active then open the correct demo importer
				if ( $this->is_active() ) {
					$demo = sanitize_title_with_dashes($_REQUEST['ac-demo-import']);
					wp_redirect(admin_url( "admin.php?page=ayecode-demo-content&ac-demo-import=" . $demo ));
				}
			}
		}

		/**
		 * @param $screen_ids
		 * @return mixed
		 */
		public function add_aui_screens( $screen_ids ) {
			// AC screens that need AUI
			$screen_ids[] = 'toplevel_page_ayecode-connect';
			$screen_ids[] = 'ayecode_page_ayecode-demo-content';

			return $screen_ids;
		}

		/**
		 * Maybe sync licenses on new plugin or theme install.
		 * 
		 * @param $result
		 * @param $extra_hooks
		 * @param $upgrader
		 *
		 * @return mixed
		 */
		public function maybe_sync_licenses($result,$extra_hooks,$upgrader) {
			if(!empty($extra_hooks['action']) && $extra_hooks['action']=='install' && !empty($extra_hooks['type']) &&  ($extra_hooks['type']=='plugin' || $extra_hooks['type']=='theme')){
				wp_schedule_single_event( time(), 'ayecode_connect_sync_licenses' );
			}
			return $result;
		}

		/**
		 * A notice to show that the site is now connected.
		 */
		public function connected_notice() {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php _e( '<b>AyeCode Connected!</b> You can now install any addons you have a license for and your license keys will automatically be synced.', 'ayecode-connect' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Maybe add licence keys to the install buttons if we have them.
		 *
		 * @param $button_args
		 *
		 * @return mixed
		 */
		public function edd_api_button_args( $button_args ) {
			if ( defined( 'WP_EASY_UPDATES_ACTIVE' ) ) {
				if ( ! empty( $button_args['licensing'] ) && ! empty( $button_args['update_url'] ) && ! empty( $button_args['id'] ) && empty( $button_args['license'] ) ) {
					$update_url = esc_url_raw( $button_args['update_url'] );
					$item_id    = absint( $button_args['id'] );
					$domain     = '';

					if ( trailingslashit( $update_url ) == "https://wpgeodirectory.com/" || trailingslashit( $update_url ) == "http://wpgeodirectory.com/" ) {
						$domain = 'wpgeodirectory.com';
					} elseif ( trailingslashit( $update_url ) == "https://userswp.io/" || trailingslashit( $update_url ) == "http://userswp.io/" ) {
						$domain = 'userswp.io';
					} elseif ( trailingslashit( $update_url ) == "https://wpinvoicing.com/" || trailingslashit( $update_url ) == "http://wpinvoicing.com/" ) {
						$domain = 'wpinvoicing.com'; /* TODO: need to fix this after check */
					}

					if ( $domain ) {
						$licences = get_option( $this->prefix . "_licences" );
						if ( isset( $licences[ $domain ][ $item_id ] ) ) {
							$licence = $licences[ $domain ][ $item_id ];
							if ( ! empty( $licence->key ) ) {
								$button_args['license'] = $licence->key;
							}
						}
					}
				}
			}

			return $button_args;
		}


		/**
		 * Returns true if the current site is connected to remote
		 *
		 * @return Boolean is the site connected?
		 */
		public function is_active() {
			return (bool) $this->get_access_token();
		}

		/**
		 * Returns true if the site has both a token and a blog id, which indicates a site has been registered.
		 *
		 * @access public
		 *
		 * @return bool
		 */
		public function is_registered() {
			$blog_id   = (bool) $this->get_blog_id();
			$has_token = $this->is_active();

			return $blog_id && $has_token;
		}

		/**
		 * Returns the requested remote API URL.
		 *
		 * @param String $relative_url the relative API path.
		 *
		 * @return String API URL.
		 */
		public function get_api_url( $relative_url ) {
			$api_url = $this->api_url;

			$api_base     = trailingslashit( $api_url . $this->api_namespace );
			$relative_url = ltrim( $relative_url, '/' );

			return $api_base . $relative_url;
		}

		/**
		 * Returns the requested local API URL.
		 *
		 * @param String $relative_url the relative API path.
		 *
		 * @return String API URL.
		 */
		public function get_local_api_url( $relative_url ) {
			$api_url = $this->api_url;

			$api_base     = trailingslashit( $api_url . $this->local_api_namespace );
			$relative_url = ltrim( $relative_url, '/' );

			return $api_base . $relative_url;
		}

		/**
		 * Builds the timeout limit for queries talking with the remote servers.
		 *
		 * Based on local php max_execution_time in php.ini
		 *
		 * @since 1.0.0
		 * @return int
		 **/
		public function get_max_execution_time() {
			$timeout = (int) ini_get( 'max_execution_time' );

			// Ensure exec time set in php.ini.
			if ( ! $timeout ) {
				$timeout = 30;
			}

			return $timeout;
		}

		/**
		 * Sets a minimum request timeout, and returns the current timeout
		 *
		 * @since 1.0.0
		 *
		 * @param Integer $min_timeout the minimum timeout value.
		 *
		 * @return int The timeout value.
		 **/
		public function set_min_time_limit( $min_timeout ) {
			$timeout = $this->get_max_execution_time();

			if ( $timeout < $min_timeout ) {
				$timeout = $min_timeout;

				set_time_limit( $timeout );
			}

			return $timeout;
		}

		/**
		 * Get our assumed site creation date.
		 * Calculated based on the earlier date of either:
		 * - Earliest admin user registration date.
		 * - Earliest date of post of any post type.
		 *
		 * @since 1.0.0
		 *
		 * @return string Assumed site creation date and time.
		 */
		public function get_assumed_site_creation_date() {
			$earliest_registered_users  = get_users(
				array(
					'role'    => 'administrator',
					'orderby' => 'user_registered',
					'order'   => 'ASC',
					'fields'  => array( 'user_registered' ),
					'number'  => 1,
				)
			);
			$earliest_registration_date = $earliest_registered_users[0]->user_registered;

			$earliest_posts = get_posts(
				array(
					'posts_per_page' => 1,
					'post_type'      => 'any',
					'post_status'    => 'any',
					'orderby'        => 'date',
					'order'          => 'ASC',
				)
			);

			// If there are no posts at all, we'll count only on user registration date.
			if ( $earliest_posts ) {
				$earliest_post_date = $earliest_posts[0]->post_date;
			} else {
				$earliest_post_date = PHP_INT_MAX;
			}

			return min( $earliest_registration_date, $earliest_post_date );
		}

		/**
		 * Deletes secret tokens in case they, for example, have expired.
		 */
		public function delete_secrets() {
			delete_option( $this->prefix . '_blog_id' );
			delete_option( $this->prefix . '_blog_token' );
			delete_option( $this->prefix . '_connected_username' );
			delete_option( $this->prefix . '_connected_email' );
			delete_option( $this->prefix . '_connected_name' );
			delete_option( $this->prefix . '_connected_user_id' );
			delete_option( $this->prefix . '_connected_user_signatures' );
			delete_option( $this->prefix . '_licence_sync' );
			delete_option( $this->prefix . '_licences' );
			delete_option( $this->prefix . '_support' );
			delete_option( $this->prefix . '_support_user' );
			delete_option( $this->prefix . '_url' );
			delete_option( $this->prefix . '_activation_secret' );
			delete_transient( $this->prefix . '_activation_secret' );
			delete_transient( $this->prefix . '_support_user_key' );
			delete_transient( $this->prefix . '_site_moved' );
		}

		/**
		 * Responds to the remote's call to register the current site.
		 *
		 * @param array $registration_data Array of [ activation_secret, blog_id, access_token ].
		 *
		 * @return mixed|WP_Error|WP_REST_Response
		 */
		public function handle_registration( array $registration_data ) {
			list( $activation_secret, $blog_id, $access_token, $username,$user_id,$user_email,$user_display_name ) = $registration_data;

			if ( empty( $activation_secret ) || empty( $access_token ) || empty( $blog_id ) ) {
				return new WP_Error( 'registration_state_invalid', __( 'Invalid Registration Data', 'ayecode-connect' ), 400 );
			}

			if ( $this->get_activation_secret() != $activation_secret ) {
				return new WP_Error( 'invalid_secret', __( 'Invalid Secret', 'ayecode-connect' ), 401 );
			}

			update_option( $this->prefix . '_connected_username', $username );
			update_option( $this->prefix . '_connected_email', $user_email );
			update_option( $this->prefix . '_connected_name', $user_display_name);
			update_option( $this->prefix . '_connected_user_id', $user_id );
			update_option( $this->prefix . '_blog_id', $blog_id );
			update_option( $this->prefix . '_blog_token', $access_token );
			update_option( $this->prefix . '_licence_sync', true );

			// just in case it was disabled, add it back here
			wp_clear_scheduled_hook( $this->prefix . "_callback" );
			wp_schedule_event( time(), 'daily', $this->prefix . "_callback" );

			// if using object cache clear secret if not used
			if ( wp_using_ext_object_cache() ) {
				delete_option( $this->prefix . '_activation_secret' );
			}

			// Make the licence sync run on next load
			//wp_schedule_single_event( time(), $this->prefix . "_callback" );

			return rest_ensure_response( true );
		}

		/**
		 * Returns the activation secret which expires after an hour.
		 *
		 * @return mixed|string
		 */
		public function get_activation_secret() {
			//Prepare transient name
			$transient = $this->prefix . '_activation_secret';

			// Persistent cache hates transients, either not changing or always changing.
			if ( wp_using_ext_object_cache() ) {
				// Fetch its value
				$secret = get_option( $transient );
			}else{
				// Fetch its value
				$secret = get_transient( $transient );
			}

			//If set, return
			if ( ! empty( $secret ) ) {
				return $secret;
			}

			//Else, create a new activation secret...
			$secret = wp_generate_password( 24, false );

			if ( wp_using_ext_object_cache() ) {
				//Then set it as an option
				add_option( $transient, $secret );
			}else{
				//Then cache it as a transient
				set_transient( $transient, $secret, 3 * HOUR_IN_SECONDS );
			}

			//Return the new activation secret
			return $secret;
		}

		/**
		 * Our own non-cached version.
		 *
		 * @param $transient
		 *
		 * @return false|mixed|void
		 */
		public function get_transient( $transient ){
			global $wpdb;

			$transient_option = '_transient_' . $transient;

			return $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = %s", $transient_option ) );
		}

		/**
		 * Builds a URL to the remote connection auth page.
		 *
		 * @param bool $raw If true, URL will not be escaped.
		 * @param bool|string $redirect If true, will redirect back to wp-admin page after connection.
		 *                              If string, will be a custom redirect.
		 *
		 * @return string Connect URL
		 */
		public function build_connect_url( $redirect = true ) {
			$user       = wp_get_current_user();
			$admin_page = esc_url_raw( admin_url( "admin.php?page=ayecode-connect" ) );

			//Setup a redirect url after successful connection
			$redirect = $redirect
				? wp_validate_redirect( esc_url_raw( $redirect ), $admin_page )
				: $admin_page;

			// Build the connection URL
			$args = urlencode_deep(
				array(
					'redirect_uri'      => urlencode( $redirect ),
					'remote_user_id'    => $user->ID,
					'remote_user_id'    => $user->ID,
					'user_email'        => $user->user_email,
					'user_login'        => $user->user_login,
					'activation_secret' => $this->get_activation_secret(),
					'secret_key'        => wp_generate_password( 40, true, true ),
					'blogname'          => get_option( 'blogname' ),
					'site_url'          => site_url(),
					'home_url'          => home_url(),
					'api_url'           => get_rest_url( null, $this->local_api_namespace ),
					'site_icon'         => get_site_icon_url(),
					'site_lang'         => get_locale(),
					'site_created'      => $this->get_assumed_site_creation_date(),
					'multisite'         => is_multisite() ? 1 : 0,
				)
			);

			return add_query_arg( $args, $this->connection_url );
		}

		/**
		 * Disconnects from the remote servers.
		 * Forgets all connection details and tells the remote servers to do the same.
		 */
		public function disconnect_site( $disconnect_remote = true ) {
			$site_id = $this->get_blog_id();

			//Abort early if it is not connected
			if ( ! $site_id ) {
				return false;
			}

			if($disconnect_remote){
				//Disconnect from remote...
				$args     = array(
					'url'    => $this->get_api_url( sprintf( '/sites/%d', $site_id ) ),
					'method' => 'DELETE'
				);
				$response = self::remote_request( $args );
			}else{
				$response = true;
			}

			//Then delete local secrets
			$this->delete_secrets();

			// remove all licences
			delete_option( 'exup_keys' );

			// remove cron
			wp_clear_scheduled_hook( $this->prefix . "_callback" );

			// destroy support user
			$support_user = get_user_by( 'login', 'ayecode_connect_support_user' );
			if ( ! empty( $support_user ) && isset( $support_user->ID ) && ! empty( $support_user->ID ) ) {
				require_once(ABSPATH.'wp-admin/includes/user.php');
				$user_id = absint($support_user->ID);
				// get all sessions for user with ID $user_id
				$sessions = WP_Session_Tokens::get_instance($user_id);
				// we have got the sessions, destroy them all!
				$sessions->destroy_all();
				$reassign = user_can( 1, 'manage_options' ) ? 1 : null;
				wp_delete_user( $user_id, $reassign );
				if ( is_multisite() ) {
					if ( ! function_exists( 'wpmu_delete_user' ) ) { 
						require_once( ABSPATH . 'wp-admin/includes/ms.php' );
					}
					revoke_super_admin( $user_id );
					wpmu_delete_user( $user_id );
				}
			}

			return $response;
		}

		/**
		 * Request to enable update notifications.
		 *
		 * @return array|mixed|void|WP_Error
		 */
		public function request_licences( $site = '' ) {
			$site_id = $this->get_blog_id();

			// Abort early if it is not connected
			if ( ! $site_id ) {
				return;
			}

			// Disconnect from remote...
			$args = array(
				'url'    => $this->get_api_url( '/licenses' ),
				'method' => 'GET'
			);

			$response = self::remote_request( $args );

			// In case the request failed...
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ) );

			return $body;
		}

		/**
		 * Request that licences for any of our plugins are auto synced.
		 *
		 * @return array|bool|mixed|WP_Error
		 */
		public function sync_licences() {
			error_log( 'sync_licenses' );

			// Only run if WPEU is active
			if ( ! defined( 'WP_EASY_UPDATES_ACTIVE' ) ) {
				return false;
			}

			$site_id = $this->get_blog_id();

			//Abort early if it is not connected
			if ( ! $site_id ) {
				return false;
			}

			//Request to remote...
			$args = array(
				'url'    => $this->get_api_url( '/activate_licenses' ),
				'method' => 'POST'
			);

			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			$plugins = get_plugins();

			// Remove any non AyeCode plugins.
			foreach ( $plugins as $slug => $plugin ) {
				if ( empty( $plugin['Update URL'] ) ) {
					// Check if a main plugin
					if ( isset( $plugin['TextDomain'] ) && in_array( $plugin['TextDomain'], array(
							"geodirectory",
							"userswp",
							"invoicing"
						) )
					) {
						// Don't remove
					} else {
						unset( $plugins[ $slug ] ); // remove
					}
				}
			}

			// Maybe add current licence keys
			$keys = get_option( 'exup_keys', array() );

			if ( ! empty( $keys ) ) {
				foreach ( $keys as $plugin_slug => $key_info ) {
					if ( isset( $plugins[ $plugin_slug ] ) && isset( $key_info->key ) ) {
						$plugins[ $plugin_slug ]['key'] = $key_info->key;
					}
				}
			}

			$body = array(
				'plugins' => $plugins,
				'json_format' => true,
			);

			$response = self::remote_request( $args, $body );

			// In case the request failed...
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ) );

			return $body;
		}

		/**
		 * Request to enable update notifications.
		 *
		 * @return array|mixed|void|WP_Error
		 */
		public function request_updates() {

			$site_id = $this->get_blog_id();

			//Abort early if it is not connected
			if ( ! $site_id ) {
				return;
			}

			//Disconnect from remote...
			$args = array(
				'url'    => $this->get_api_url( sprintf( '/enable-updates/%d', $site_id ) ),
				'method' => 'POST'
			);

			$response = self::remote_request( $args );

			//in case the request failed...
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ) );

			return $body;
		}

		/**
		 * Checks if the current domain can run the plugin
		 *
		 * @param string $domain The domain to check.
		 *
		 * @return bool|WP_Error
		 */
		public function is_usable_domain( $domain ) {

			// If it's empty, just fail out.
			if ( ! $domain ) {
				return new WP_Error(
					'fail_domain_empty',
					/* translators: %1$s is a domain name. */
					sprintf( __( 'Domain `%1$s` just failed is_usable_domain check as it is empty.', 'ayecode-connect' ), $domain )
				);
			}

			/**
			 * Skips the usable domain check when connecting a site.
			 *
			 * Allows site administrators with domains that fail gethostname-based checks to pass the request to remote
			 *
			 * @since 1.0.0
			 *
			 * @param bool If the check should be skipped. Default false.
			 */
			if ( apply_filters( $this->prefix . '_skip_usuable_domain_check', false ) ) {
				return true;
			}

			// None of the explicit localhosts.
			$forbidden_domains = array(
				'localhost',
				'localhost.localdomain',
				'127.0.0.1',
				'::1'
			);

			if ( in_array( $domain, $forbidden_domains, true ) ) {
				return new WP_Error(
					'fail_domain_forbidden',
					sprintf(
					/* translators: %1$s is a domain name. */
						__(
							'Domain `%1$s` just failed is_usable_domain check as it is in the forbidden array.',
							'ayecode-connect'
						),
						$domain
					)
				);
			}

			// No .test or .local domains.
			if ( preg_match( '#\.(test|local)$#i', $domain ) ) {
				return new WP_Error(
					'fail_domain_tld',
					sprintf(
					/* translators: %1$s is a domain name. */
						__(
							'Domain `%1$s` just failed is_usable_domain check as it uses an invalid top level domain.',
							'ayecode-connect'
						),
						$domain
					)
				);
			}

			return true;
		}

		/**
		 * Gets the remote's access token.
		 *
		 *
		 * @return string|false
		 */
		public function get_access_token() {
			$option_name = $this->prefix . '_blog_token';

			return get_option( $option_name, false );
		}

		/**
		 * Get the connected users username.
		 *
		 * @return mixed|void
		 */
		public function get_connected_username() {
			$option_name = $this->prefix . '_connected_username';

			return get_option( $option_name, false );
		}

		/**
		 * Get the connected users name.
		 *
		 * @return mixed|void
		 */
		public function get_connected_name() {
			$option_name = $this->prefix . '_connected_name';

			$value = get_option( $option_name, false );
			
			// if no value maybe try and get it.
			if($value === false){
				$username = $this->get_connected_username();
				if($username){
					$user = $this->get_remote_user_info();
					if(!empty($user->display_name)){
						$value = sanitize_text_field($user->display_name);
						update_option( $option_name, $value );
					}
				}
			}
			
			return $value;
		}

		/**
		 * Get the connected users email.
		 *
		 * @return mixed|void
		 */
		public function get_connected_email() {
			$option_name = $this->prefix . '_connected_email';

			$value = get_option( $option_name, false );

			// if no value maybe try and get it.
			if($value === false){
				$username = $this->get_connected_username();
				if($username){
					$user = $this->get_remote_user_info();
					if(!empty($user->user_email)){
						$value = sanitize_text_field($user->user_email);
						update_option( $option_name, $value );
					}
				}
			}

			return $value;
		}

		/**
		 * Get the connected users ID.
		 *
		 * @return mixed|void
		 */
		public function get_connected_user_id() {
			$option_name = $this->prefix . '_connected_user_id';

			$value = get_option( $option_name, false );

			// if no value maybe try and get it.
			if ( $value === false ) {
				$username = $this->get_connected_username();

				if ( $username ) {
					$user = $this->get_remote_user_info();

					if ( ! empty( $user->ID ) ) {
						$value = sanitize_text_field( $user->ID );

						update_option( $option_name, $value );
					}
				}
			}

			return $value;
		}

		/**
		 * Get the connected users signatures.
		 *
		 * @return mixed|void
		 */
		public function get_connected_user_signatures($type = '') {
			$option_name = $this->prefix . '_connected_user_signatures';

			$value = get_option( $option_name, false );

			return $value;
		}

		/**
		 * Get the connected users signatures.
		 *
		 * @return mixed|void
		 */
		public function get_connected_user_sites() {
			$option_name = $this->prefix . '_connected_user_sites';

			$value = get_option( $option_name, false );
			
			
			// remove this site
			if(!empty($value)){
				$site_id = $this->get_blog_id();
				//unset($value[$site_id]);
			}

			return $value;
		}

		/**
		 * Get any sync the remote user info.
		 *
		 * @return array|mixed|void|WP_Error
		 */
		public function get_remote_user_info(){
			$site_id = $this->get_blog_id();

			//Abort early if it is not connected
			if ( ! $site_id ) {
				return;
			}

			$cache = get_transient( $this->prefix . '_remote_user_info' );
			if($cache !== false){
				return $cache;
			}

			//Disconnect from remote...
			$args = array(
				'url'    => $this->get_api_url( sprintf( '/me/%d', $site_id ) ),
				'method' => 'GET'
			);

			$response = self::remote_request( $args );

			//in case the request failed...
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ) );

			// update user info
			if(!empty($body->ID)){update_option($this->prefix . '_connected_user_id',absint($body->ID));}
			if(!empty($body->display_name)){update_option($this->prefix . '_connected_name',sanitize_text_field($body->display_name));}
			if(!empty($body->user_email)){update_option($this->prefix . '_connected_email',sanitize_email($body->user_email));}
			if(!empty($body->user_signatures)){update_option($this->prefix . '_connected_user_signatures',array_map('sanitize_text_field',(array) $body->user_signatures) );}
			if(!empty($body->user_sites)){update_option($this->prefix . '_connected_user_sites',array_map('sanitize_text_field',(array) $body->user_sites) );}

			// cache results for 60 seconds so that we don't run multiple times on once page load
			set_transient( $this->prefix . '_remote_user_info', $body, 60);
			
			return $body;
		}

		/**
		 * Get any sync the remote user info.
		 *
		 * @return array|mixed|void|WP_Error
		 */
		public function set_remote_support_user( $enable = false ) {
			$site_id = $this->get_blog_id();

			//Abort early if it is not connected
			if ( ! $site_id ) {
				return;
			}

			// enable support user
			if($enable){
				// Generate a temp key
				$key = wp_generate_password( 20 );
				$hash = wp_hash_password( $key  );

				// Valid for seconds
				$valid_seconds = 7 * DAY_IN_SECONDS;
				$expires = time() + $valid_seconds;
				
				// Set info
				$args = array(
					'url'    => $this->get_api_url( sprintf( '/support/%d', $site_id ) ),
					'method' => 'POST'
				);

				$body = array(
					'key' => $key,
					'expire' => $expires
				);

				$response = self::remote_request( $args, $body );

				// in case the request failed...
				if ( is_wp_error( $response ) ) {
					return $response;
				}

				$body = json_decode( wp_remote_retrieve_body( $response ) );

				// Set a transient
				set_transient( $this->prefix . "_support_user_key", $hash, $valid_seconds );
				update_option( $this->prefix . "_support_user", $expires );
			} else {
				// Disable support user
				// Remove info early incase remote request fails
				delete_transient( $this->prefix . "_support_user_key" );
				update_option( $this->prefix . "_support_user", false );

				// destroy support user
				$support_user = get_user_by( 'login', 'ayecode_connect_support_user' );
				if ( ! empty( $support_user ) && isset( $support_user->ID ) && ! empty( $support_user->ID ) ) {
					require_once(ABSPATH.'wp-admin/includes/user.php');
					$user_id = absint($support_user->ID);
					// get all sessions for user with ID $user_id
					$sessions = WP_Session_Tokens::get_instance($user_id);
					// we have got the sessions, destroy them all!
					$sessions->destroy_all();
					$reassign = user_can( 1, 'manage_options' ) ? 1 : null;
					wp_delete_user( $user_id, $reassign );
					if ( is_multisite() ) {
						if ( ! function_exists( 'wpmu_delete_user' ) ) { 
							require_once( ABSPATH . 'wp-admin/includes/ms.php' );
						}
						revoke_super_admin( $user_id );
						wpmu_delete_user( $user_id );
					}
				}

				// Set info
				$args = array(
					'url'    => $this->get_api_url( sprintf( '/support/%d', $site_id ) ),
					'method' => 'POST'
				);

				$body = array(
					'key' => '',
					'expire' => 0
				);

				$response = self::remote_request( $args, $body );

				// In case the request failed...
				if ( is_wp_error( $response ) ) {
					return $response;
				}

				$body = json_decode( wp_remote_retrieve_body( $response ) );
			}

			return $body;
		}

		/**
		 * Gets the remote's blog id.
		 *
		 * @return string|false
		 */
		public function get_blog_id() {
			$option_name = $this->prefix . '_blog_id';

			return get_option( $option_name, false );
		}

		/**
		 * Redirects a user to the remote's connection page
		 *
		 * @return void
		 */
		public function maybe_redirect_to_connection_page() {
			// Only admins have the capability to connect
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Ensure that this is an admin page
			if ( ! is_admin() ) {
				return;
			}

			// And that the user wants to be redirected
			$action = $this->prefix . '_redirect_to_activation_url';
			if ( empty( $_GET['action'] ) || $action != $_GET['action'] ) {
				return;
			}

			// Prepare the connect URL
			$url = $this->build_connect_url();

			// Then redirect the user to the URL
			wp_redirect( esc_url( $url ) );
			exit;
		}

		/**
		 * Checks if the request actually came from the connected remote
		 *
		 * Use this as the permission callback when registering new REST Routes
		 *
		 * @since    1.0.0
		 */
		public function is_api_request_authenticated( $request ) {
			$headers = $request->get_header_as_array( 'Authorization' );

			if ( empty( $headers ) ) {
				return new WP_Error( 'rest_forbidden', esc_html__( 'Missing Authorization Header.', 'ayecode-connect' ), array( 'status' => 401 ) );
			}

			$jwt = '';
			foreach ( $headers as $header ) {
				$header = trim( $header );

				if ( strpos( $header, 'X_AUTH' ) === 0 ) {
					$jwt = trim( substr( $header, 7 ) );
					break;
				}
			}

			// Ensure the jwt auth is set...
			if ( empty( $jwt ) ) {
				return new WP_Error( 'rest_forbidden', esc_html__( 'Missing Authorization Header.', 'ayecode-connect' ), array( 'status' => 401 ) );
			}

			// And is valid
			$tokens = explode( '.', $jwt );
			if ( count( $tokens ) != 3 ) {
				return new WP_Error( 'rest_forbidden', esc_html__( 'Invalid Authorization Header.', 'ayecode-connect' ), array( 'status' => 401 ) );
			}

			// The key used to authenticate the request
			$key = $this->get_access_token();

			if ( empty( $key ) ) {
				return new WP_Error( 'missing_token', esc_html__( 'Missing blog token.', 'ayecode-connect' ), array( 'status' => 401 ) );
			}

			// Use it to decode the jwt token
			if ( false === self::decode( $jwt, $key ) ) {
				return new WP_Error( 'rest_forbidden', esc_html__( 'You are not authorized to do that.', 'ayecode-connect' ), array( 'status' => 401 ) );
			}

			// This request is authentic
			return true;
		}

		/**
		 * Decodes a JWT string into a PHP object.
		 *
		 * @param string $jwt The JWT
		 * @param string|array $key The key. If the algorithm used is asymmetric, this is the public key
		 *
		 * @return false|object The JWT's payload as a PHP object
		 */
		public static function decode( $jwt, $key ) {
			$tokens = explode( '.', $jwt );
			if ( count( $tokens ) != 3 ) {
				return false;
			}

			list( $header_64, $body_64, $hash_64 ) = $tokens;

			// Header contains the algorithym used to encode the jwt
			if ( null === ( $header = json_decode( self::url_safe_base64_decode( $header_64 ) ) ) ) {
				return false;
			}

			// Payload contains the blog id etc
			if ( null === $payload = json_decode( self::url_safe_base64_decode( $body_64 ) ) ) {
				return false;
			}

			// Signature is a hs256 encoding of the header and the payload
			if ( false === ( $signature = self::url_safe_base64_decode( $hash_64 ) ) ) {
				return false;
			}

			// Only HS256 is supported
			if ( empty( $header->alg ) || 'HS256' != $header->alg ) {
				return false;
			}

			// Check the signature
			$hash = hash_hmac( 'sha256', $header_64 . "." . $body_64, $key, true ); //(HS256)
			if ( ! hash_equals( $signature, $hash ) ) {
				return false;
			}

			return $payload;
		}

		/**
		 * Converts and signs a PHP object or array into a JWT string.
		 *
		 * @param object|array $payload PHP object or array
		 * @param string $key The secret key.
		 *                                  If the algorithm used is asymmetric, this is the private key
		 *
		 * @return string A signed JWT
		 *
		 */
		public static function encode( $payload, $key ) {
			$header = array( 'typ' => 'JWT', 'alg' => 'HS256' );

			$segments      = array();
			$segments[]    = self::url_safe_base64_encode( wp_json_encode( $header ) );
			$segments[]    = self::url_safe_base64_encode( wp_json_encode( $payload ) );
			$signing_input = implode( '.', $segments );
			$signature     = hash_hmac( 'sha256', $signing_input, $key, true ); //(HS256)
			$segments[]    = self::url_safe_base64_encode( $signature );

			return implode( '.', $segments );
		}

		/**
		 * Decode a string with URL-safe Base64.
		 *
		 * @param string $input A Base64 encoded string
		 *
		 * @return string A decoded string
		 */
		public static function url_safe_base64_decode( $input ) {
			$remainder = strlen( $input ) % 4;

			if ( $remainder ) {
				$padlen = 4 - $remainder;
				$input .= str_repeat( '=', $padlen );
			}

			return base64_decode( strtr( $input, '-_', '+/' ) );

		}

		/**
		 * Encode a string with URL-safe Base64.
		 *
		 * @param string $input The string you want encoded
		 *
		 * @return string The base64 encode of what you passed in
		 */
		public static function url_safe_base64_encode( $input ) {
			return str_replace( '=', '', strtr( base64_encode( $input ), '+/', '-_' ) );
		}

		/**
		 * Makes an authorized remote request to the remote website
		 *
		 * @param array $args the arguments for the remote request.
		 * @param array|String $body the request body.
		 *
		 * @return array|WP_Error WP HTTP response on success
		 */
		public function remote_request( $args, $body = null ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$defaults = array(
				'url'         => '',
				'blog_id'     => $this->get_blog_id(),
				'method'      => 'POST',
				'timeout'     => 60,
				'redirection' => 0,
				'headers'     => array(),
				'stream'      => false,
				'filename'    => null,
				'sslverify'   => AYECODE_CONNECT_SSL_VERIFY,
			);

			$args = wp_parse_args( $args, $defaults );

			$args['blog_id'] = (int) $args['blog_id'];

			$token = $this->get_access_token();

			if ( ! $token ) {
				$error = new WP_Error( 'missing_token', __( 'Missing token', 'ayecode-connect' ) );

				$this->debug_log( $error->get_error_message(), __METHOD__ . ':get_access_token error', __FILE__, __LINE__ );

				return $error;
			}

			$method = strtoupper( $args['method'] );

			$timeout = intval( $args['timeout'] );

			$redirection = $args['redirection'];
			$stream      = $args['stream'];
			$filename    = $args['filename'];
			$sslverify   = $args['sslverify'];

			$request = compact( 'method', 'body', 'timeout', 'redirection', 'stream', 'filename', 'sslverify' );

			$url = esc_url( $args['url'] );

			$this->debug_log( $url, __METHOD__ . ':url', __FILE__, __LINE__ );

			$signature = self::encode( array( 'blog_id' => $args['blog_id'] ), $token );

			$request['headers'] = array_merge(
				$args['headers'],
				array(
					'Authorization'          => 'X_AUTH ' . $signature,
					'X-HTTP-Method-Override' => $method
				)
			);

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return wp_remote_request( $url, $request );
		}

		/**
		 * Registers REST routes only when the current website is connected to the remote website
		 *
		 */
		public function register_connected_routes() {
			// Initiates a given action
			register_rest_route(
				$this->local_api_namespace,
				'/do_action',
				array(
					'methods'             => WP_REST_Server::ALLMETHODS,
					'callback'            => array( $this, 'do_action' ),
					'permission_callback' => array( $this, 'has_permission' ),
				)
			);
		}

		/**
		 * Checks for permission
		 *
		 * @since    1.0.0
		 */
		public function has_permission( $request ) {
			// Retrieve the jwt for the request
			$jwt = $this->get_jwt( $request );

			// Ensure the jwt auth is set...
			if ( empty( $jwt ) ) {
				return new WP_Error( 'rest_forbidden', esc_html__( 'Missing Authorization Header.', 'ayecode-connect' ), array( 'status' => 401 ) );
			}

			// And is valid
			$tokens = explode( '.', $jwt );
			if ( count( $tokens ) != 3 ) {
				return new WP_Error( 'rest_forbidden', esc_html__( 'Invalid Authorization Header.', 'ayecode-connect' ), array( 'status' => 401 ) );
			}

			// Ensure the body is not empty
			$body = json_decode( self::url_safe_base64_decode( $tokens[1] ) );
			if ( empty( $body ) ) {
				return new WP_Error( 'rest_forbidden', esc_html__( 'Invalid Authorization Header.', 'ayecode-connect' ), array( 'status' => 401 ) );
			}

			// Retrieve the secret key associated with the blog id
			$key = $this->get_access_token();

			//... then use it to decrypt the jwt
			if ( empty( $key ) || empty( self::decode( $jwt, $key ) ) ) {
				return new WP_Error( 'rest_forbidden', esc_html__( 'You are not authorized to do that.', 'ayecode-connect' ), array( 'status' => 401 ) );
			}

			return true;
		}

		/**
		 * Retrieves the JWT for the request
		 *
		 * @since    1.0.0
		 */
		public function get_jwt( $request ) {
			// Prepare authorization headers
			$auth_headers = $request->get_header_as_array( 'Authorization' );

			// If empty this might be because of the server removes the auth header https://github.com/WP-API/WP-API/issues/2512
			if ( empty( $auth_headers ) ) {
				$auth_headers = $request->get_header_as_array( 'X-AYE-Authorization' );
			}

			// The provided json web token
			$jwt = '';

			// Loop through them and retrieve our auth header
			if ( ! empty( $auth_headers ) ) {
				foreach ( $auth_headers as $header ) {

					$header = trim( $header );
					if ( strpos( $header, 'X_AUTH' ) === 0 ) {
						$jwt = trim( substr( $header, 7 ) );
						break;
					}
				}
			}

			return $jwt;
		}

		/**
		 * Init's a remote action
		 *
		 */
		public function do_action( $request ) {
			//$this->debug_log( $request, __METHOD__ . ':request', __FILE__, __LINE__ );
			//wp_mail("stiofansisland@gmail.com","update settings debug request", print_r( $request, true ) );
			$prefix = $this->prefix;
			$action = sanitize_title_with_dashes( $request->get_param( 'action' ) );
			$this->debug_log( $action, __METHOD__ . ':action', __FILE__, __LINE__ );

			if ( empty( $action ) ) {
				return new WP_Error( 'missing_action', __( 'Specify an action', 'ayecode-connect' ) );
			}

			if ( ! class_exists( 'AyeCode_Connect_Remote_Actions' ) ) {
				/**
				 * Run the remote actions class.
				 *
				 * This is only loaded if authenticated.
				 */
				require_once plugin_dir_path( __FILE__ ) . 'class-ayecode-connect-remote-actions.php';

				AyeCode_Connect_Remote_Actions::instance( $prefix, $this );
			}

			$response = apply_filters( "{$prefix}_remote_action_{$action}", array( "success" => false ), $request );

			return rest_ensure_response( $response );
		}

		/**
		 * Registers REST routes only when the current website is not connected to the remote website
		 *
		 */
		public function register_connection_routes() {
			// Verifies registration
			register_rest_route(
				$this->local_api_namespace,
				'/verify_registration',
				array(
					'methods'  => WP_REST_Server::EDITABLE,
					'callback' => array( $this, 'verify_registration' ),
					'permission_callback' => array( $this, 'verify_registration_permission_callback' )
				)
			);

			// Returns a url to the connection page.
			register_rest_route(
				$this->local_api_namespace,
				'/connection_page',
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( $this, 'connection_page' ),
					'permission_callback' => '__return_true'
				)
			);
		}

		/**
		 * Register routes used for testing.
		 */
		public function register_test_routes() {
			// Returns a url to the connection page.
			register_rest_route(
				$this->local_api_namespace,
				'/test-connection',
				array(
					'methods'  => WP_REST_Server::CREATABLE,
					'callback' => array( $this, 'test_connection' ),
					'permission_callback' => '__return_true'
				)
			);
		}

		/**
		 * Allow our server to reply to a test connection request.
		 *
		 * @param $request
		 *
		 * @return array
		 */
		public function test_connection( $request  ) {
			// Validate
			if ( ! $this->validate_request() ) {
				return array( "success" => false );
			}

			$hash = esc_attr( $request['hash'] );
			$stored_hash = esc_attr( get_transient('ac_test_connection') );
			$success = false;

			if ( ! $stored_hash || ! $hash ) {
				$success = false;
				$code = "no_hash";
			} elseif ( $hash && $stored_hash && $stored_hash != $hash ) {
				$success = false;
				$code = "hash_not_equal";
			} elseif ( $hash && $stored_hash && $stored_hash == $hash ) {
				$success = true;
				$code = "success";
			}

			$result = array(
				"success" => $success,
				"code" => $code
				);

			return $result;
		}

		/**
		 * Validate the request origin.
		 *
		 * This file is not even loaded unless it passes JWT validation.
		 *
		 * @return bool
		 */
		private function validate_request() {
			$result = false;

			if ( $this->get_server_ip() === "173.208.153.114" ) {
				$result = true;
			}

			return $result;
		}

		/**
		 * Get the request has come from our server.
		 *
		 * @return string
		 */
		private function get_server_ip() {
			if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				//check ip from share internet
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				//to check ip is pass from proxy
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}

			// Cloudflare can provide a comma separated ip list
			if ( strpos( $ip, ',' ) !== false ) {
				$ip = reset( explode( ",", $ip ) );
			}

			return $ip;
		}

		/**
		 * Permission callback for rest API registration route.
		 *
		 * @return bool
		 */
		public function verify_registration_permission_callback(){
			$result = false;

			$activation_secret = isset( $_REQUEST['activation_secret'] ) ? sanitize_text_field($_REQUEST['activation_secret']) : '';
			$current_activation_secret =  $this->get_activation_secret();

			if ($current_activation_secret && $activation_secret && $current_activation_secret == $activation_secret ) {
				$result = true;
			}

			return $result;
		}

		/**
		 * Handles verification that a site is registered.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request The request sent to the WP REST API.
		 *
		 * @return string|WP_Error
		 */
		public function verify_registration( WP_REST_Request $request ) {
			// Delete the URL change transient if set
			delete_transient( $this->prefix . '_site_moved');

			//Prepare the registration data
			$registration_data = array(
				$request['activation_secret'],
				$request['blog_id'],
				$request['access_token'],
				$request['username'],
				$request['user_id'],
				$request['user_email'],
				$request['user_display_name'],
			);

			// Then (maybe) save it
			return $this->handle_registration( $registration_data );
		}

		/**
		 * Returns a URL to the connection page.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request The request sent to the WP REST API.
		 *
		 * @return string|WP_Error
		 */
		public function connection_page( WP_REST_Request $request ) {
			$action = $this->prefix . '_redirect_to_activation_url';
			$url    = add_query_arg( 'action', $action, get_admin_url() );

			return rest_ensure_response( $url );
		}

		/**
		 * Check if the website URL changes and disconnect the site and show re-connect notice if so.
		 */
		public function check_for_url_change( $connected_site_url = '' ) {
			// If WPML is installed then bail as this can dynamically change the URL
			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				return false;
			}

			$result = false;

			// Get current site URL
			$connected_site_url = $connected_site_url ? trailingslashit( str_replace( array( "http://", "https://" ), "", $connected_site_url ) ): get_option( $this->prefix . "_url" );

			// Get the current site URL
			$site_url = trailingslashit( str_replace( array( "http://","https://" ),"", site_url() ) );

			// If current site URL is empty then add it
			if ( empty( $connected_site_url ) ) {
				$connected_site_url = $site_url;
				update_option( $this->prefix . "_url", $connected_site_url );
			}

			// Check for site URL change, disconnect site and add warning
			if ( $site_url && $site_url !== '/' && $connected_site_url && $connected_site_url != $site_url ) {
				// Disconnect site but not from remote (that would invalidate the other site)
				$this->disconnect_site(false);

				// Set a transient for 1 month so we can show a warning
				set_transient( $this->prefix . '_site_moved', true, MONTH_IN_SECONDS );

				$result = true;
			}

			return $result;
		}

		/**
		 * Show admin notice if site URL changes.
		 */
		public function website_url_change_error(){
			$url_change_disconnection_notice = get_transient( $this->prefix . '_site_moved');
			if($url_change_disconnection_notice){
				$ayecode_connect = admin_url( "admin.php?page=ayecode-connect" );
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo sprintf( __( '<b>AyeCode Connect:</b> Your website URL has changed, please %sre-connect%s this site.', 'ayecode-connect' ),"<a href='$ayecode_connect'>", "</a>" ); ?></p>
				</div>
				<?php
			}
		}

		/**
		 * Request to install plugins.
		 *
		 * @return array|mixed|void|WP_Error
		 */
		public function request_plugins( $plugins = array() ) {
			$site_id = $this->get_blog_id();

			//Abort early if it is not connected
			if ( ! $site_id ) {
				return;
			}

			// Remote args...
			$args = array(
				'url'    => $this->get_api_url( '/request_plugins' ),
				'method' => 'POST'
			);

			$body = array(
				'plugins' => $plugins,
			);

			$response = self::remote_request( $args,$body );

			print_r( $response );

			// in case the request failed...
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ) );

			return $body;
		}

		/**
		 * Request to download demo data.
		 *
		 * @return array|mixed|void|WP_Error
		 */
		public function download_demo_content( $demo, $args, $site ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$type = 'download-data';
			$site_id = $this->get_blog_id();

			$this->debug_log( $site_id, __METHOD__ . ':site_id', __FILE__, __LINE__ );
			$this->debug_log( $demo, __METHOD__ . ':demo', __FILE__, __LINE__ );

			// Abort early if it is not connected
			if ( ! $site_id ) {
				$error = new WP_Error( 'missing_site_id', __( 'Missing site id.', 'ayecode-connect' ) );

				$this->debug_log( $error->get_error_message(), __METHOD__ . ':remote_request error', __FILE__, __LINE__ );
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return $error;
			}

			// Remote args...
			$args = array(
				'url' => $this->get_api_url( sprintf( '/request_demo_content/%s/%s', $demo, $type ) ),
				'method' => 'POST'
			);

			$params = array(
				'site_id' => $site_id,
			);

			// 1. Theme
			$slug = $site->theme->slug;
			$theme_found = wp_get_theme( $slug );

			$this->debug_log( $slug, __METHOD__ . ':theme slug', __FILE__, __LINE__ );

			$params['theme'] = array(
				'action' => 'activate',
				'slug' => $slug,
				'parent_theme' => ! empty( $site->theme->Template ) ? $site->theme->Template : ''
			);

			if ( ! $theme_found->exists() ) {
				$params['theme']['action'] = 'install';
			} else if ( $slug == get_option( 'stylesheet' ) ) {
				$params['theme']['action'] = 'skip';
			}

			// 2. Plugins
			$params['plugins'] = array();

			// 3. Settings
			$params['settings'] = array();

			// 4. Categories
			$params['categories'] = array();

			// 5. Templates
			$params['templates'] = array();

			// 6. Dummy posts
			$params['dummy_posts'] = array();

			// 7. Widgets
			$params['widgets'] = array();

			// 8. Menus
			$params['menus'] = array();

			$response = self::remote_request( $args, $params );

			if ( is_wp_error( $response ) ) {
				$this->debug_log( $response->get_error_message(), __METHOD__ . ':remote_request error', __FILE__, __LINE__ );
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return $response;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ) );

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $body;
		}

		/**
		 * Request to install plugins.
		 *
		 * @return array|mixed|void|WP_Error
		 */
		public function request_demo_content( $demo, $type, $page = 0 ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$site_id = $this->get_blog_id();

			$this->debug_log( $demo, __METHOD__ . ':demo', __FILE__, __LINE__ );
			$this->debug_log( $type, __METHOD__ . ':type', __FILE__, __LINE__ );
			$this->debug_log( $site_id, __METHOD__ . ':site_id', __FILE__, __LINE__ );

			// Abort early if it is not connected
			if ( ! $site_id ) {
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return;
			}

			$version = ! empty( $this->version ) ? esc_attr( $this->version ) : '';
			$page_arg = $page ? "?page=" . absint( $page ) : '';
			$page_arg .= $page_arg ? '&ver=' . esc_attr( $version ) : '?ver=' . esc_attr( $version );

			// Remote args...
			$args = array(
				'url' => $this->get_api_url( sprintf( '/request_demo_content/%s/%s', $demo, $type ) ) . $page_arg,
				'method' => 'POST'
			);

			$response = self::remote_request( $args );

			// in case the request failed...
			if ( is_wp_error( $response ) ) {
				$this->debug_log( $response->get_error_message(), __METHOD__ . ':remote_request error', __FILE__, __LINE__ );
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return $response;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ) );

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $body;
		}

		public function debug_log( $log, $title = '', $file = '', $line = '', $exit = false ) {
			global $aye_usage;

			if ( empty( $aye_usage ) ) {
				$aye_usage = array();
			}

			$should_log = $this->debug;

			if ( defined( 'AYECODE_CONNECT_DEBUG' ) ) {
				$should_log = AYECODE_CONNECT_DEBUG;
			}

			$should_log = apply_filters( 'ayecode_connect_debug_log', $should_log );

			if ( $should_log ) {
				$label = '';
				if ( $file && $file !== '' ) {
					$label .= basename( $file ) . ( $line ? '(' . $line . ')' : '' );
				}

				if ( $title && $title !== '' ) {
					$label = $label !== '' ? $label . ' ' : '';
					$label .= $title . ' ';
				}

				$label = $label !== '' ? trim( $label ) . ' : ' : '';

				$append = '';
				if ( is_scalar( $log ) && ( $log === 'start' || $log === 'end' ) ) {
					$usage = memory_get_usage();

					$append = " " . $usage;

					$_label = '';

					if ( $file && $file !== '' ) {
						$_label .= basename( $file ) . ':';
					}

					if ( $title && $title !== '' ) {
						$_label .= $title;
					}

					if ( $_label ) {
						$aye_usage[ $_label ][ $log ] = $usage;

						if ( $log === 'end' && ! empty( $aye_usage[ $_label ][ 'start' ] ) ) {
							$append .= " - " . ( $usage - $aye_usage[ $_label ][ 'start' ] );
						}
					}
				}

				if ( is_array( $log ) || is_object( $log ) ) {
					error_log( $label . print_r( $log, true ) );
				} else {
					error_log( $label . $log . $append );
				}

				if ( $exit ) {
					exit;
				}
			}
		}
	}
endif;