<?php
/**
 * A settings class for AyeCode Connect.
 */

/**
 * Bail if we are not in WP.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'AyeCode_Connect_Settings' ) ) {

	/**
	 * The settings for AyeCode Connect
	 */
	class AyeCode_Connect_Settings {
		/**
		 * The title.
		 *
		 * @var string
		 */
		public $name = 'AyeCode Connect';

		/**
		 * The relative url to the assets.
		 *
		 * @var string
		 */
		public $url = '';

		/**
		 * The AyeCode_Connect instance.
		 * @var
		 */
		public $client;

		/**
		 * The base url of the plugin.
		 *
		 * @var
		 */
		public $base_url;

		/**
		 * AyeCode_UI_Settings instance.
		 *
		 * @access private
		 * @since  1.0.0
		 * @var    AyeCode_Connect_Settings There can be only one!
		 */
		private static $instance = null;

		/**
		 * Main AyeCode_Connect_Settings Instance.
		 *
		 * Ensures only one instance of AyeCode_Connect_Settings is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return AyeCode_Connect_Settings - Main instance.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AyeCode_Connect_Settings ) ) {
				self::$instance = new AyeCode_Connect_Settings;

				$args                   = ayecode_connect_args();
				self::$instance->client = new AyeCode_Connect( $args );

				if ( is_admin() ) {

					// check for demo redirect
					if ( self::$instance->client->is_active() && get_transient( 'ac-demo-import' ) ) {
						$demo = esc_attr( sanitize_title_with_dashes( get_transient( 'ac-demo-import' ) ) );
						delete_transient( 'ac-demo-import' );
						wp_redirect( admin_url( "admin.php?page=ayecode-demo-content&ac-demo-import=" . $demo ) );
						exit;
					} else {
						// set a transient for demo redirect if set
						if ( ! empty( $_REQUEST['alert'] ) && $_REQUEST['alert'] == 'connect' && ! empty( $_REQUEST['ac-demo-import'] ) ) {
							set_transient( 'ac-demo-import', sanitize_title_with_dashes( $_REQUEST['ac-demo-import'] ), 300 );
						}
					}

					add_action( 'admin_menu', array( self::$instance, 'menu_item' ) );


					self::$instance->base_url = str_replace( "/includes/../", "/", plugins_url( '../', __FILE__ ) );

					// ajax
					add_action( 'wp_ajax_ayecode_connect_updates', array( self::$instance, 'ajax_toggle_updates' ) );
					add_action( 'wp_ajax_ayecode_connect_disconnect', array(
						self::$instance,
						'ajax_disconnect_site'
					) );
					add_action( 'wp_ajax_ayecode_connect_licences', array( self::$instance, 'ajax_toggle_licences' ) );
					add_action( 'wp_ajax_ayecode_connect_support', array( self::$instance, 'ajax_toggle_support' ) );
					add_action( 'wp_ajax_ayecode_connect_support_user', array(
						self::$instance,
						'ajax_toggle_support_user'
					) );
					add_action( 'wp_ajax_ayecode_connect_install_must_use_plugin', array(
						self::$instance,
						'install_mu_plugin'
					) );
					add_action( 'wp_ajax_ayecode_connect_check_connection', array(
						self::$instance,
						'ajax_check_connection'
					) );
					add_action( 'wp_ajax_ayecode_connect_clear_licenses', array(
						self::$instance,
						'ajax_clear_licenses'
					) );

					require_once plugin_dir_path( __FILE__ ) . 'class-ayecode-demo-content.php';


				}

				// cron, this needs to be outside the is_admin() check.
				add_action( self::$instance->client->prefix . "_callback", array(
					self::$instance,
					'cron_callback'
				), 10 );

				do_action( 'ayecode_connect_settings_loaded' );
			}

			return self::$instance;
		}

		public function install_mu_plugin() {
			$result = false;
			if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php' );
			}

			if ( class_exists( 'WP_Filesystem_Direct' ) ) {
				$wp_filesystem_direct = new WP_Filesystem_Direct( true );
				$src                  = dirname( __FILE__ ) . "/../assets/wpmu/ayecode-connect-filter-fix.php";
				$des                  = WPMU_PLUGIN_DIR . "/ayecode-connect-filter-fix.php";
				$result               = $wp_filesystem_direct->move( $src, $des, true );
			}

			if ( $result ) {
				wp_send_json_success( __( "Plugin installed, this should resolve any update issues, if you still have issues please contact support.", "ayecode-connect" ) );
			} else {
				wp_send_json_error( __( "Something went wrong, please contact support.", "ayecode-connect" ) );
			}

			wp_die();
		}

		/**
		 * The Cron callback to run checks.
		 */
		public function cron_callback() {

			// check we are registered
			if ( $this->client->is_registered() && defined( 'WP_EASY_UPDATES_ACTIVE' ) ) {

				// licence sync
				if ( get_option( $this->client->prefix . "_licence_sync" ) ) {
					// Sync licences now
					$this->client->sync_licences();
				}
			}

			// sync user info
			if ( $this->client->is_registered() ) {
				$this->client->get_remote_user_info();
			}

            // if using object cache clear secret if not used
			if ( wp_using_ext_object_cache() ) {
				delete_option( $this->client->prefix . '_activation_secret' );
			}
		}

		/**
		 * Remove all site licenses.
		 */
		public function clear_all_licenses() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( - 1 );
			}

			// remove AC licenses
			delete_option( $this->client->prefix . '_licences' );

			// remove WPEU licences
			delete_option( 'exup_keys' );
		}

		/**
		 * Disconnect site via ajax call.
		 */
		public function ajax_disconnect_site() {
			// security
			check_ajax_referer( 'ayecode-connect', 'security' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( - 1 );
			}

			$result = $this->client->disconnect_site();

			if ( ! is_wp_error( $result ) ) {
				wp_send_json_success();
			} else {
				wp_send_json_error();
			}

			wp_die();
		}

		/**
		 * Toggle updates via ajax.
		 */
		public function ajax_toggle_updates() {

			// security
			check_ajax_referer( 'ayecode-connect', 'security' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( - 1 );
			}

			$success = true;
			$state   = isset( $_POST['state'] ) && $_POST['state'] ? true : false;
			$plugin  = 'wp-easy-updates/external-updates.php';
			if ( $state ) { // enable
				$installed_plugins = array_map( array( $this, 'format_plugin_slug' ), array_keys( get_plugins() ) );

				if ( in_array( 'external-updates', $installed_plugins ) ) {
					$result = activate_plugin( $plugin );

					if ( is_wp_error( $result ) ) {
						$success = false;
					}
				} else {// request
					$result = $this->client->request_updates();
					if ( is_wp_error( $result ) ) {
						$success = false;
					}
				}

			} else { // disable
				$result = deactivate_plugins( $plugin );
				if ( is_wp_error( $result ) ) {
					$success = false;
				}
			}


			if ( $success ) {
				wp_send_json_success();
			} else {
				wp_send_json_error();
			}

			wp_clean_plugins_cache();

			wp_die();
		}

		/**
		 * Toggle licences via ajax call.
		 */
		public function ajax_toggle_licences() {

			// security
			check_ajax_referer( 'ayecode-connect', 'security' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( - 1 );
			}

			$success = true;
			$state   = isset( $_POST['state'] ) && $_POST['state'] ? true : false;

			if ( $state ) { // enable

				// sanity check
				if ( ! defined( 'WP_EASY_UPDATES_ACTIVE' ) ) {
					wp_send_json_error( __( "Plugin and theme update notifications must be enabled first", "ayecode-connect" ) );
				}

				update_option( $this->client->prefix . "_licence_sync", true );
				wp_clear_scheduled_hook( $this->client->prefix . "_callback" );
				wp_schedule_event( time(), 'daily', $this->client->prefix . "_callback" );


				// Sync licences now
				$this->client->sync_licences();

			} else { // disable
				update_option( $this->client->prefix . "_licence_sync", false );
				wp_clear_scheduled_hook( $this->client->prefix . "_callback" );

				// clear all licenses
				$this->clear_all_licenses();
			}


			if ( $success ) {
				wp_send_json_success();
			} else {
				wp_send_json_error();
			}

			wp_die();
		}

		/**
		 * Toggle support widget via ajax call.
		 */
		public function ajax_toggle_support() {

			// security
			check_ajax_referer( 'ayecode-connect', 'security' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( - 1 );
			}

			$success = true;
			$state   = isset( $_POST['state'] ) && $_POST['state'] ? true : false;

			if ( $state ) { // enable
				update_option( $this->client->prefix . "_support", true );

				// Sync user info
				$this->client->get_remote_user_info();

			} else { // disable
				update_option( $this->client->prefix . "_support", false );
			}


			if ( $success ) {
				wp_send_json_success();
			} else {
				wp_send_json_error();
			}

			wp_die();
		}

		/**
		 * Toggle temp support user via ajax call.
		 */
		public function ajax_toggle_support_user() {

			// security
			check_ajax_referer( 'ayecode-connect', 'security' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( - 1 );
			}

			$success = true;
			$state   = isset( $_POST['state'] ) && $_POST['state'] ? true : false;

			if ( $state ) { // enable
				// Sync support user info
				$this->client->set_remote_support_user( true );

			} else { // disable
				// Sync support user info
				$this->client->set_remote_support_user( false );
			}


			if ( $success ) {
				$data = array(
					'message' => sprintf( __( "Auto expires in %s", "ayecode-connect" ), human_time_diff( time(), time() + 3 * DAY_IN_SECONDS ) )
				);
				wp_send_json_success( $data );
			} else {
				wp_send_json_error();
			}

			wp_die();
		}


		/**
		 * Add the WordPress settings menu item.
		 */
		public function menu_item() {
			$url_change_disconnection_notice = get_transient( $this->client->prefix . '_site_moved' );

			$menu_name = "AyeCode";//$this->name

			$page = add_menu_page(
				$menu_name,
				$url_change_disconnection_notice ? sprintf( $menu_name . ' <span class="awaiting-mod">%s</span>', "!" ) : $menu_name,
				'manage_options',
				'ayecode-connect',
				array(
					$this,
					'settings_page'
				),
				'data:image/svg+xml;base64,' . base64_encode( file_get_contents( dirname( __FILE__ ) . '/../assets/img/ayecode.svg' ) ),
				4
			);


			add_action( "admin_print_styles-{$page}", array( $this, 'scripts' ) );

		}

		/**
		 * Add scripts to our settings page.
		 */
		public function scripts() {

			// Register the script
			wp_register_script( 'ayecode-connect', $this->base_url . 'assets/js/ayecode-connect.js', array( 'jquery' ), AYECODE_CONNECT_VERSION );

			// Localize the script with new data
			$translation_array = array(
				'nonce'          => wp_create_nonce( 'ayecode-connect' ),
				'error_msg'      => __( "Something went wrong, try refreshing the page and trying again.", "ayecode-connect" ),
				'disconnect_msg' => __( "Are you sure you with to disconnect your site?", "ayecode-connect" ),
			);
			wp_localize_script( 'ayecode-connect', 'ayecode_connect', $translation_array );
			wp_enqueue_script( 'ayecode-connect' );
		}

		/**
		 * Disconnect site via ajax call.
		 */
		public function ajax_check_connection() {
			// security
			check_ajax_referer( 'ayecode-connect', 'security' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( - 1 );
			}

			$api_url = $this->client->get_api_url( '/test-connection' );

			$test_hash = wp_generate_password();

			set_transient( 'ac_test_connection', $test_hash, MINUTE_IN_SECONDS );

			$args = array(
				'method'      => 'POST',
				'timeout'     => 60,
				'redirection' => 0,
				'headers'     => array(),
				'stream'      => false,
				'filename'    => null,
				'sslverify'   => AYECODE_CONNECT_SSL_VERIFY,
				'body'        => array(
					'hash'    => $test_hash,
					'api_url' => get_rest_url( null, $this->client->local_api_namespace )
				)
			);

			$result       = wp_remote_post( esc_url( $api_url ), $args );
			$api_response = json_decode( wp_remote_retrieve_body( $result ), true );


			if ( is_wp_error( $result ) ) {
				wp_send_json_success( "to ayecode:" . $result->get_error_message() );
			} elseif ( empty( $api_response['success'] ) && ! empty( $api_response['message'] ) ) {
				wp_send_json_error( esc_attr("from ayecode:" . $api_response['message'] ) );
			} elseif ( ! empty( $api_response['success'] ) && ! empty( $api_response['message'] ) ) {
				wp_send_json_success( esc_attr( $api_response['message'] ) );
			}

			wp_die();
		}

		/**
		 * Disconnect site via ajax call.
		 */
		public function ajax_clear_licenses() {
			// security
			check_ajax_referer( 'ayecode-connect', 'security' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( - 1 );
			}

			$this->clear_all_licenses();

			wp_send_json_success( __( "Licenses cleared, refresh page to confirm.", "ayecode-connect" ) );

			wp_die();
		}

		/**
		 * Settings page HTML.
		 */
		public function settings_page() {
            global $aui_bs5;

			// bsui wrapper makes our bootstrap wrapper work
			?>
			<!-- Clean & Mean UI -->
			<style>
				#wpbody-content > div.notice,
				#wpbody-content > div.error {
					display: none;
				}
                #ayecode-connect-wrapper input[type=checkbox]:checked::before{
                    content: '';
                  }
			</style>

			<div class="bsui" style="margin-left: -20px;">
				<!-- Just an image -->
				<nav class="navbar bg-white border-bottom">
					<a class="navbar-brand p-0" href="#">
						<img src="<?php echo $this->base_url; ?>assets/img/ayecode.png" width="120" alt="AyeCode Ltd">
					</a>
				</nav>
			</div>


			<div class="bsui" style="margin-left: -20px; display: flex">

				<div id="ayecode-connect-wrapper" class="containerx bg-white w-100 p-4 m-4 border rounded text-center">
					<div class="ac-header">

					</div>
					<div class="ac-body mt-5">
						<div class="ac-button-container text-center">
							<h1 class="h5 mx-auto w-50 mb-3"><?php _e( "One click addon installs, live documentation search, support right from your WordPress Dashboard", "ayecode-connect" ); ?></h1>

							<?php
							if ( $this->client->is_registered() ) {

								$connected_username = $this->client->get_connected_username();
								?>
								<div class="alert alert-success  w-50 mx-auto text-left" role="alert">
									<?php echo sprintf( __( "You are connected to AyeCode Connect as user: %s", "ayecode-connect" ), "<b>$connected_username</b>" ); ?>
								</div>

								<ul class="list-group w-50 mx-auto">

									<li class="list-group-item d-flex justify-content-between align-items-center">
										<span
											class="mr-auto me-auto"><?php _e( "Plugin and theme update notifications", "ayecode-connect" ); ?></span>
										<div class="spinner-border spinner-border-sm mr-2 me-2 d-none text-muted"
										     role="status">
											<span class="sr-only"><?php _e( "Loading...", "ayecode-connect" ); ?></span>
										</div>
										<div class="<?php echo $aui_bs5 ? 'form-check form-switch' : 'custom-control custom-switch'; ?>">
											<input type="checkbox" class="custom-control-input form-check-input" id="ac-setting-updates"
												<?php if ( defined( 'WP_EASY_UPDATES_ACTIVE' ) ) {
													echo "checked";
												} ?>
												   onclick="if(jQuery(this).is(':checked')){ayecode_connect_updates(this,1);}else{ayecode_connect_updates(this,0);}"
											>
											<label class="custom-control-label form-check-label" for="ac-setting-updates"></label>
										</div>
									</li>

									<li class="list-group-item d-flex justify-content-between align-items-center">
										<span
											class="mr-auto me-auto"><?php _e( "One click addon installs, no more license keys", "ayecode-connect" ); ?></span>
										<div class="spinner-border spinner-border-sm mr-2 me-2 d-none text-muted"
										     role="status">
											<span class="sr-only"><?php _e( "Loading...", "ayecode-connect" ); ?></span>
										</div>
										<div class="<?php echo $aui_bs5 ? 'form-check form-switch' : 'custom-control custom-switch'; ?>">
											<input type="checkbox" class="custom-control-input form-check-input" id="ac-setting-licences"
												<?php if ( get_option( $this->client->prefix . "_licence_sync" ) ) {
													echo "checked";
												} ?>
												   onclick="if(jQuery(this).is(':checked')){ayecode_connect_licences(this,1);}else{ayecode_connect_licences(this,0);}"
											>
											<label class="custom-control-label form-check-label" for="ac-setting-licences"></label>
										</div>
									</li>

									<li class="list-group-item d-flex justify-content-between align-items-center">
										<span
											class="mr-auto me-auto"><?php _e( "Documentation and Support Widget", "ayecode-connect" ); ?></span>
										<div class="spinner-border spinner-border-sm mr-2 me-2 d-none text-muted"
										     role="status">
											<span class="sr-only"><?php _e( "Loading...", "ayecode-connect" ); ?></span>
										</div>
										<div class="<?php echo $aui_bs5 ? 'form-check form-switch' : 'custom-control custom-switch'; ?>">
											<input type="checkbox" class="custom-control-input form-check-input" id="ac-setting-support"
												<?php if ( get_option( $this->client->prefix . "_support", true ) ) {
													echo "checked";
												} ?>
												   onclick="if(jQuery(this).is(':checked')){ayecode_connect_support(this,1);}else{ayecode_connect_support(this,0);}"
											>
											<label class="custom-control-label form-check-label" for="ac-setting-support"></label>
										</div>
									</li>

									<?php
									$status_text_class = 'd-none';
									if ( $expires = get_option( $this->client->prefix . "_support_user", 0 ) ) {
										$status_text_class = '';
									}
									?>
									<li class="list-group-item d-flex justify-content-between align-items-center">
										<span
											class="mr-auto me-auto"><?php _e( "Temporary Support User Access", "ayecode-connect" ); ?></span>
										<div
											class=" mr-2 me-2 <?php echo $status_text_class; ?> text-muted ac-support-user-status"
											role="status">
											<span
												class="badge badge-warning font-weight-normal"><?php echo sprintf( __( "Auto expires in %s", "ayecode-connect" ), human_time_diff( time(), $expires ) ); ?></span>
										</div>
										<div class="spinner-border spinner-border-sm mr-2 me-2 d-none text-muted"
										     role="status">
											<span class="sr-only"><?php _e( "Loading...", "ayecode-connect" ); ?></span>
										</div>
										<div class="<?php echo $aui_bs5 ? 'form-check form-switch' : 'custom-control custom-switch'; ?>">
											<input type="checkbox" class="custom-control-input form-check-input"
											       id="ac-setting-support-user"
												<?php if ( get_option( $this->client->prefix . "_support_user", false ) ) {
													echo "checked";
												} ?>
												   onclick="if(jQuery(this).is(':checked')){ayecode_connect_support_user(this,1);}else{ayecode_connect_support_user(this,0);}"
											>
											<label class="custom-control-label form-check-label" for="ac-setting-support-user"></label>
										</div>
									</li>

								</ul>

								<p class="mt-4">
									<span class="spinner-border spinner-border-sm mr-2 me-2 d-none text-muted" role="status">
										<span class="sr-only"><?php _e( "Loading...", "ayecode-connect" ); ?></span>
									</span>
									<a href="javascript:void(0)"
									   onclick="ayecode_connect_disconnect(this);return false;"
									   class="text-muted">
										<u><?php _e( 'Disconnect site', 'ayecode-connect' ); ?></u></a>
								</p>

								<?php


								// fix for other plugins calling get_plugins() too early.
								if ( defined( 'WP_EASY_UPDATES_ACTIVE' ) ) {
									$plugins = get_plugins();
									$first   = reset( $plugins );
									if ( ( ! empty( $plugins ) && ! isset( $first['Update URL'] ) ) || isset( $_REQUEST['ayemu'] ) || isset( $_REQUEST['ayedebug'] ) ) {
										echo '<div class="ac-get-plugins-fix"><div class="alert alert-danger w-50 mx-auto " role="alert"><span class="badge badge-pill badge-light">!</span> ';
										_e( "Another plugin is calling the get_plugins() function too early which may block updates. We can try to fix this by calling our filter first with a must use plugin.", "ayecode-connect" );
										echo "<button class='btn btn-white d-block mt-2 mx-auto' onclick='ayecode_connect_install_must_use_plugin();'>" . __( "Install now", "ayecode-connect" ) . "</button>";
										echo '<div class="spinner-border spinner-border-sm mt-2 d-none text-white"  role="status">
											<span class="sr-only">' . __( "Loading...", "ayecode-connect" ) . '</span>
									</div></div></div>';
									}
								}


							} else {

								$connect_url = esc_url( $this->client->build_connect_url() );

								// check if alert message
								if ( ! empty( $_REQUEST['alert'] ) && $_REQUEST['alert'] == 'connect' ) {
									echo '<div class="alert alert-warning w-50 mx-auto" role="alert"><span class="badge badge-pill badge-light">!</span> ';
									_e( "You must connect your site before you can import demo content.", "ayecode-connect" );
									echo "</div>";
								}

								$active_plugins = get_option('active_plugins');
								$coming_soon_warn = false;
								if(!empty($active_plugins ) ){
									foreach ( $active_plugins as $plugin ) {
										if (strpos($plugin, 'soon') !== false || strpos($plugin, 'maintenance') !== false) {
											$coming_soon_warn = true;
										}
									}
								}

								if ( $coming_soon_warn ) {
									echo '<div class="alert alert-warning w-50 mx-auto" role="alert"><span class="badge badge-pill badge-light">!</span> ';
									_e( "It looks like you might have a coming soon or maintenance plugin active? This may block our connection server.", "ayecode-connect" );
									echo "</div>";
								}

								?>
								<small
									class="text-muted"><?php _e( "By clicking the <b>Connect Site</b> button, you agree to our <a href='https://ayecode.io/terms-and-conditions/' target='_blank' class='text-muted' ><u>Terms of Service</u></a> and to share details with AyeCode Ltd", "ayecode-connect" ); ?></small>
								<p class="mt-4">
									<a href="<?php echo $connect_url; ?>"
									   class="btn btn-primary"><?php _e( 'Connect Site', 'ayecode-connect' ); ?></a>
								</p>
								<?php
								// check for url change
								$url_change_disconnection_notice = get_transient( $this->client->prefix . '_site_moved' );
								if ( $url_change_disconnection_notice ) {
									echo '<div class="alert alert-danger w-50 mx-auto" role="alert"><span class="badge badge-pill badge-light">!</span> ';
									_e( "Your website URL has changed, please re-connect with your new URL.", "ayecode-connect" );
									echo "</div>";
								}

								// check for local domain
								$host      = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';
								$localhost = $this->client->is_usable_domain( $host );
								if ( is_wp_error( $localhost ) ) {
									echo '<div class="alert alert-danger w-50 mx-auto" role="alert"><span class="badge badge-pill badge-light">!</span> ';
									_e( "It looks like you might be running on localhost, AyeCode Connect will only work on a live website.", "ayecode-connect" );
									echo "</div>";
								}


							}


							?>

						</div>

						<img src="<?php echo $this->base_url; ?>assets/img/connect-site.png" class="img-fluid mt-4"
						     alt="AyeCode Connect">
					</div>
					<div class="ac-footer border-top mt-5">
						<p class="text-muted h6 mt-4"><?php _e( 'AyeCode Ltd are the creators of:', 'ayecode-connect' ); ?>
							<a href="https://wpgeodirectory.com/">wpgeodirectory.com</a>,
							<a href="https://wpinvoicing.com/">wpinvoicing.com</a> &
							<a href="https://userswp.io/">userswp.io</a>
						</p>
					</div>

					<?php
					// Debug info
					$support_user = get_user_by( 'login', 'ayecode_connect_support_user' );
					if ( ( isset( $_REQUEST['ayedebug'] ) || ( ! empty( $support_user->ID ) && $support_user->ID == get_current_user_id() ) ) && current_user_can( 'manage_options' ) ) {
						$all_licences    = get_option( $this->client->prefix . "_licences" );
						$actual_licences = get_option( "exup_keys" );

						$blog_id  = get_option( $this->client->prefix . '_blog_id', false );
						$site_url = get_option( $this->client->prefix . '_url', false );
//						$aui_bs5 = 0;
                        $bs5_prefix = $aui_bs5 ? '-bs' : '';
						?>
						<div class='ayedebug-wrapper bsui'>
							<h4><?php _e( "Debug Info", "ayecode-connect" ); ?></h4>
							<div class="accordion text-left text-start mb-4" id="accordionExample">
								<div class="<?php echo $aui_bs5 ? 'accordion-item' : 'card '; ?> mw-100 p-0 m-0">
									<div class="<?php echo $aui_bs5 ? 'accordion-header' : 'card-header'; ?> position-relative" id="headingOne">
                                        <a class="<?php echo $aui_bs5 ? 'accordion-button' : 'stretched-link mb-0 h5 py-2 px-4'; ?>" type="button" data<?php echo $bs5_prefix;?>-toggle="collapse"
                                           data<?php echo $bs5_prefix;?>-target="#collapseOne" aria-expanded="true"
                                           aria-controls="collapseOne">
                                            <?php _e( "Connection Info", "ayecode-connect" ); ?>
                                        </a>
									</div>

									<div id="collapseOne" class="<?php echo $aui_bs5 ? 'accordion-collapse' : ''; ?> collapse show" aria-labelledby="headingOne"
									     data-parent="#accordionExample">
										<div class="<?php echo $aui_bs5 ? 'accordion-body' : 'card-body'; ?> ">
											<?php
											echo '<h5>blog id ' . absint( $blog_id ) . '</h5>';
											echo '<h5>Site URL ' . esc_attr( $site_url ) . '</h5>';
											?>
										</div>
									</div>
								</div>
                                <div class="<?php echo $aui_bs5 ? 'accordion-item' : 'card '; ?> mw-100 p-0 m-0">
                                    <div class="<?php echo $aui_bs5 ? 'accordion-header' : 'card-header'; ?> position-relative" id="headingTwo2">
                                        <a class="<?php echo $aui_bs5 ? 'accordion-button' : 'stretched-link mb-0 h5 py-2 px-4'; ?> collapsed" type="button" data<?php echo $bs5_prefix;?>-toggle="collapse"
                                           data<?php echo $bs5_prefix;?>-target="#collapseTwo2" aria-expanded="false"
                                           aria-controls="collapseTwo2">
                                            <?php _e( "Activation Secret", "ayecode-connect" ); ?>
                                        </a>
                                    </div>
                                    <div id="collapseTwo2" class="<?php echo $aui_bs5 ? 'accordion-collapse' : ''; ?> collapse" aria-labelledby="headingTwo2"
                                         data-parent="#accordionExample">
                                        <div class="<?php echo $aui_bs5 ? 'accordion-body' : 'card-body'; ?>">
											<?php
											echo $this->client->get_activation_secret();
							                ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="<?php echo $aui_bs5 ? 'accordion-item' : 'card '; ?> mw-100 p-0 m-0">
                                    <div class="<?php echo $aui_bs5 ? 'accordion-header' : 'card-header'; ?> position-relative" id="headingTwo">
                                        <a class="<?php echo $aui_bs5 ? 'accordion-button' : 'stretched-link mb-0 h5 py-2 px-4'; ?> collapsed" type="button" data<?php echo $bs5_prefix;?>-toggle="collapse"
                                           data<?php echo $bs5_prefix;?>-target="#collapseTwo" aria-expanded="false"
                                           aria-controls="collapseTwo">
                                            <?php _e( "Debug Tools", "ayecode-connect" ); ?>
                                        </a>
                                    </div>
                                    <div id="collapseTwo" class="<?php echo $aui_bs5 ? 'accordion-collapse' : ''; ?> collapse" aria-labelledby="headingTwo"
                                         data-parent="#accordionExample">
                                        <div class="<?php echo $aui_bs5 ? 'accordion-body' : 'card-body'; ?>">
											<?php
											echo "<button class='btn btn-primary' onclick='ayecode_connect_clear_licenses();'>" . __( "Clear all licenses", "ayecode-connect" ) . "</button>\n";
											echo "<button class='btn btn-primary' onclick='ayecode_connect_check_connection();'>" . __( "Test Connection ability", "ayecode-connect" ) . "</button>\n";
											?>

                                            <div class="ac-test-results py-3"></div>
                                        </div>
                                    </div>
                                </div>
								<div class="<?php echo $aui_bs5 ? 'accordion-item' : 'card '; ?> mw-100 p-0 m-0">
									<div class="<?php echo $aui_bs5 ? 'accordion-header' : 'card-header'; ?> position-relative" id="headingThree">
                                        <a class="<?php echo $aui_bs5 ? 'accordion-button' : 'stretched-link mb-0 h5 py-2 px-4'; ?> collapsed" type="button" data<?php echo $bs5_prefix;?>-toggle="collapse"
                                           data<?php echo $bs5_prefix;?>-target="#collapseThree" aria-expanded="false"
                                           aria-controls="collapseThree">
                                            <?php _e( "All User Licenses Found", "ayecode-connect" ); ?>
                                        </a>
									</div>
									<div id="collapseThree" class="<?php echo $aui_bs5 ? 'accordion-collapse' : ''; ?> collapse" aria-labelledby="headingThree"
									     data-parent="#accordionExample">
										<div class="<?php echo $aui_bs5 ? 'accordion-body' : 'card-body'; ?>">
													<pre>
													<?php
													if ( empty( $all_licences ) ) {
														_e( "No licenses found", "ayecode-connect" );
													} else {
														print_r( $all_licences );
													}
													?>
													</pre>
										</div>
									</div>
								</div>
								<div class="<?php echo $aui_bs5 ? 'accordion-item' : 'card '; ?> mw-100 p-0 m-0">
									<div class="<?php echo $aui_bs5 ? 'accordion-header' : 'card-header'; ?> position-relative" id="heading4">
                                        <a class="<?php echo $aui_bs5 ? 'accordion-button' : 'stretched-link mb-0 h5 py-2 px-4'; ?> collapsed" type="button" data<?php echo $bs5_prefix;?>-toggle="collapse"
                                           data<?php echo $bs5_prefix;?>-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                            <?php _e( "All Installed Licenses", "ayecode-connect" ); ?>
                                        </a>
									</div>
									<div id="collapse4" class="<?php echo $aui_bs5 ? 'accordion-collapse' : ''; ?> collapse" aria-labelledby="heading4"
									     data-parent="#accordionExample">
										<div class="<?php echo $aui_bs5 ? 'accordion-body' : 'card-body'; ?>">
													<pre>
													<?php
													if ( empty( $actual_licences ) ) {
														_e( "No licenses found", "ayecode-connect" );
													} else {
														print_r( $actual_licences );
													}
													?>
													</pre>
										</div>
									</div>
								</div>
								<div class="<?php echo $aui_bs5 ? 'accordion-item' : 'card '; ?> mw-100 p-0 m-0">
									<div class="<?php echo $aui_bs5 ? 'accordion-header' : 'card-header'; ?> position-relative" id="heading5">
                                        <a class="<?php echo $aui_bs5 ? 'accordion-button' : 'stretched-link mb-0 h5 py-2 px-4'; ?> collapsed" type="button" data<?php echo $bs5_prefix;?>-toggle="collapse"
                                           data<?php echo $bs5_prefix;?>-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                            <?php _e( "get_plugins() return <small>(if Update ID is missing from out,  install helper plugin below)</small>", "ayecode-connect" ); ?>
                                        </a>
									</div>
									<div id="collapse5" class="<?php echo $aui_bs5 ? 'accordion-collapse' : ''; ?> collapse" aria-labelledby="heading5"
									     data-parent="#accordionExample">
										<div class="<?php echo $aui_bs5 ? 'accordion-body' : 'card-body'; ?>">
													<pre>
													<?php
													print_r( get_plugins() );
													?>
													</pre>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php

					} ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Get slug from path
		 *
		 * @param  string $key
		 *
		 * @return string
		 */
		private function format_plugin_slug( $key ) {
			$slug = explode( '/', $key );
			$slug = explode( '.', end( $slug ) );

			return $slug[0];
		}

	}

	/**
	 * Run the class if found.
	 */
	AyeCode_Connect_Settings::instance();

}