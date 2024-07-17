<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Show the support widget when set to do so.
 *
 * Class AyeCode_Connect_Support
 */
class AyeCode_Connect_Support {

	/**
	 * Is enabled or not.
	 *
	 * @var string|void
	 */
	public $enabled;

	/**
	 * Is support user enabled or not.
	 *
	 * @var string|void
	 */
	public $support_user;

	/**
	 * The prefix for settings.
	 *
	 * @var string|void
	 */
	public $prefix;

	/**
	 * The Connected user display name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The Connected user email.
	 *
	 * @var string
	 */
	public $email;

	/**
	 * GeoDirectory help scout beacon ID for collecting beta features feedback in AyeCode Connect plugin.
	 *
	 * @var string
	 */
	public $ac_beta_beacon_id = 'f115773a-6890-4880-8600-59affa616d05';

	/**
	 * GeoDirectory help scout beacon ID.
	 *
	 * @var string
	 */
	public $gd_beacon_id = 'cf9047de-e433-4975-ae7e-7bb09f276533';

	/**
	 * UsersWP help scout beacon ID.
	 *
	 * @var string
	 */
	public $uwp_beacon_id = 'e18552cc-bdee-401d-af01-9a01474b144b';

	/**
	 * UsersWP help scout beacon ID.
	 *
	 * @var string
	 */
	public $wpi_beacon_id = 'b5584c88-1f9b-4147-a339-33e79cb2fee5';


	/**
	 * AyeCode_Connect_Support constructor.
	 *
	 * @param array $args
	 */
	public function __construct( array $args = array() ) {

		// support user login
		add_action( 'setup_theme', array( $this, 'maybe_remove_support_user' ), 9 ); // remove user if expired
		add_action( 'setup_theme', array( $this, 'maybe_login_support_user' ) );


		// support widget
		if ( $args['support_user'] ) {
			$this->support_user = absint( $args['support_user'] );
		}
		if ( $args['enabled'] ) {
			$this->enabled = esc_attr( $args['enabled'] );
		}
		if ( $args['prefix'] ) {
			$this->prefix = esc_attr( $args['prefix'] );
		}
		if ( $args['name'] ) {
			$this->name = sanitize_text_field( $args['name'] );
		}
		if ( $args['email'] ) {
			$this->email = sanitize_email( $args['email'] );
		}
		add_action( 'admin_footer', array( $this, 'maybe_add_admin_footer_script' ) );
	}

	/**
	 * Remove support user if expired.
	 */
	public function maybe_remove_support_user() {
		if ( $this->support_user && ! get_transient( $this->prefix . "_support_user_key" ) ) {
			update_option( $this->prefix . "_support_user", false );

			// destroy support user
			$support_user = get_user_by( 'login', 'ayecode_connect_support_user' );
			if ( ! empty( $support_user ) && isset( $support_user->ID ) && ! empty( $support_user->ID ) ) {
				require_once(ABSPATH.'wp-admin/includes/user.php');
				$user_id = absint( $support_user->ID );
				// get all sessions for user with ID $user_id
				$sessions = WP_Session_Tokens::get_instance( $user_id );
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
		}
	}

	/**
	 * Login the support user if conditions are met.
	 */
	public function maybe_login_support_user() {

		if ( ! empty( $_POST['ayecode_connect_support_user'] ) && $this->support_user && $key_hash = get_transient( $this->prefix . "_support_user_key" ) ) {

			$key = sanitize_text_field( urldecode( $_POST['ayecode_connect_support_user'] ) );
			if ( wp_check_password( $key, $key_hash ) && $this->support_user > time() ) {
				$support_user = get_user_by( 'login', 'ayecode_connect_support_user' );
				if ( ! ( ! empty( $support_user ) && isset( $support_user->ID ) && ! empty( $support_user->ID ) ) ) {
					$user_data = array(
						'user_pass'     => wp_generate_password( 20 ), // we never need to know this
						'user_login'    => 'ayecode_connect_support_user',
						'user_nicename' => 'AyeCode Support',
						'user_email'    => '', // no email so the pass can never be reset
						'first_name'    => 'AyeCode',
						'last_name'     => 'Support',
						'user_url'      => 'https://ayecode.io/',
						'role'          => 'administrator'
					);

					$user_id = wp_insert_user( $user_data );

					if ( is_wp_error( $user_id ) ) {
						echo $user_id->get_error_message();
					}elseif($user_id){
						if(is_multisite()){
							$blog_id = get_current_blog_id();
							add_user_to_blog( $blog_id, $user_id, $user_data['role'] );
							grant_super_admin( $user_id );
						}
					}
				} else {
					$user_id = absint( $support_user->ID );
				}

				if ( is_int( $user_id ) ) {
					wp_clear_auth_cookie();
					wp_set_current_user( $user_id );
					wp_set_auth_cookie( $user_id );
					wp_redirect( admin_url( "admin.php?page=ayecode-connect" ) );
					exit;
				}

			}
		}
	}

	/**
	 * Add the footer script is conditions are met.
	 */
	public function maybe_add_admin_footer_script() {
		if ( current_user_can( 'manage_options' ) && $beacon_id = $this->get_beacon_id() ) {
			if ( $this->enabled || ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'ayecode-connect' ) ) {
				echo $this->helpscout_base_js();
				echo $this->helpscout_beacon_js( $beacon_id );
			}

		}
	}

	/**
	 * Return a beacon ID for the current page.
	 *
	 * @return mixed|string
	 */
	public function get_beacon_id() {
		$beacon_id = '';

		// page conditions
		if ( ! empty( $_REQUEST['page'] ) ) {
			$page            = sanitize_title_with_dashes( $_REQUEST['page'] );
			$page_conditions = array(
				// GD
				'ayecode-connect'     => $this->gd_beacon_id,
				'ayecode-demo-content'=> $this->ac_beta_beacon_id,
				'geodirectory'        => $this->gd_beacon_id,
				'gd-settings'         => $this->gd_beacon_id,
				'gd-status'           => $this->gd_beacon_id,
				'gd-addons'           => $this->gd_beacon_id,
				// WPI
				'wpinv-settings'      => $this->wpi_beacon_id,
				'wpinv-reports'       => $this->wpi_beacon_id,
				'wpinv-subscriptions' => $this->wpi_beacon_id,
				'wpi-addons'          => $this->wpi_beacon_id,
				// UWP
				'userswp'             => $this->uwp_beacon_id,
				'uwp_form_builder'    => $this->uwp_beacon_id,
				'uwp_status'          => $this->uwp_beacon_id,
				'uwp-addons'          => $this->uwp_beacon_id,

			);

			if ( isset( $page_conditions[ $page ] ) ) {
				$beacon_id = $page_conditions[ $page ];
			}
		} // post_type conditions
		elseif ( ! empty( $_REQUEST['post_type'] ) ) {

		}

		return $beacon_id;
	}

	/**
	 * The base script for Help Scout.
	 *
	 * @return string
	 */
	public function helpscout_base_js() {
		ob_start();
		?>
		<script type="text/javascript">!function (e, t, n) {
				function a() {
					var e = t.getElementsByTagName("script")[0], n = t.createElement("script");
					n.type = "text/javascript", n.async = !0, n.src = "https://beacon-v2.helpscout.net", e.parentNode.insertBefore(n, e)
				}

				if (e.Beacon = n = function (t, n, a) {
						e.Beacon.readyQueue.push({method: t, options: n, data: a})
					}, n.readyQueue = [], "complete" === t.readyState)return a();
				e.attachEvent ? e.attachEvent("onload", a) : e.addEventListener("load", a, !1)
			}(window, document, window.Beacon || function () {
				});
		</script>
		<?php
		return ob_get_clean();
	}


	/**
	 * Script to enable the Help Scout Beacon.
	 *
	 * @param string $beacon_id
	 *
	 * @return string
	 */
	public function helpscout_beacon_js( $beacon_id = '' ) {
		if ( ! $beacon_id ) {
			return '';
		}
		$signature = $this->get_signature( $beacon_id );
		ob_start();
		?>
		<script type="text/javascript">
			/**
			 * Variable for tracking if support email was sent.
			 */
			var ayecodeSupportSent = false;
			/**
			 * Set variable if support email was sent.
			 */
			function ayecode_connect_set_support_sent() {
				ayecodeSupportSent = true;
			}

			/**
			 * Fire the support access message if sent and then closed.
			 */
			function ayecode_connect_maybe_suggest_support_user_access() {
				if (ayecodeSupportSent) {
					<?php
					$message_id = $this->get_message_id( $beacon_id, 'support-sent' );
					if ( $message_id ) {
						echo "window.Beacon('show-message', '" . esc_attr( $message_id ) . "',{ force: true });";
					}
					?>
				}
			}

			/**
			 * Fire up the support widget.
			 */
			function ayecode_connect_init_widget() {
				// reset the beacon
				window.Beacon('logout');

				window.Beacon('init', '<?php echo esc_attr( $beacon_id );?>');

				<?php
				// maybe identify connected user
				if($this->name && $this->email){
				?>
				window.Beacon('identify', {
					name: '<?php echo addslashes( $this->name ); ?>',
					email: '<?php echo addslashes( $this->email ); ?>',
					<?php
					if ( $signature ) {
						echo "signature: '$signature',";
					}
					?>

				});

				// maybe suggest support access on email send
				window.Beacon('on', 'close', ayecode_connect_maybe_suggest_support_user_access);
				window.Beacon('on', 'email-sent', ayecode_connect_set_support_sent);


					<?php
					if ( $this->ac_beta_beacon_id == $beacon_id ) {
						?>
						// beta feedback config
						Beacon('config', {
							display: {
								style: 'text',
								text: 'Feedback'
							}
						});
						Beacon('prefill', {
							subject: 'Demo Data Import Feedback'
						});
						<?php
					}else{
						?>
						// standard config
						Beacon('config', {
							display: {
								style: 'iconAndText',
								text: 'help'
							}
						});
						<?php
					}


				}

				// Set session data
				$data = $this->get_session_data();

				if(! empty( $data )){
				?>
				window.Beacon('session-data', {
					<?php
					foreach ( $data as $label => $value ) {
						echo "'" . addslashes( esc_attr( $label ) ) . "': " . "'" . addslashes( esc_attr( $value ) ) . "',";
					}
					?>
				});
				<?php
				};
				?>
			}

			// run if enabled
			<?php
			if ( $this->enabled ) {
				echo "ayecode_connect_init_widget();";
			}
			?>
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Return a beacon ID for the current page.
	 *
	 * @return mixed|string
	 */
	public function get_signature( $beacon_id ) {
		$signature = '';

		$signatures = get_option( $this->prefix . '_connected_user_signatures' );

		// page conditions
		if ( $beacon_id == $this->gd_beacon_id && ! empty( $signatures['gd'] ) ) {
			$signature = esc_attr( $signatures['gd'] );
		} elseif ( $beacon_id == $this->uwp_beacon_id && ! empty( $signatures['uwp'] ) ) {
			$signature = esc_attr( $signatures['uwp'] );
		} elseif ( $beacon_id == $this->wpi_beacon_id && ! empty( $signatures['wpi'] ) ) {
			$signature = esc_attr( $signatures['wpi'] );
		}

		return $signature;
	}

	/**
	 * Get message id depending on beacon id.
	 *
	 * @param $beacon_id
	 * @param $type
	 *
	 * @return string|void
	 */
	public function get_message_id( $beacon_id, $type ) {
		$messages   = array();
		$message_id = '';
		if ( $beacon_id == $this->gd_beacon_id ) {
			$messages['support-sent'] = 'd7db9fd8-c81a-42ee-8e58-43db6a6d277c';
		} elseif ( $beacon_id == $this->uwp_beacon_id ) {
			$messages['support-sent'] = '5a8981bb-627e-43c2-85bd-8b3dfa1925ba';
		} elseif ( $beacon_id == $this->wpi_beacon_id ) {
			$messages['support-sent'] = 'd9d4a2b1-a744-4a7e-8319-bd52dc8131b3';
		}

		// check if exists
		if ( ! empty( $messages[ $type ] ) ) {
			$message_id = esc_attr( $messages[ $type ] );
		}

		return $message_id;
	}

	/**
	 * Gather some basic info to send with the support request.
	 *
	 * @return array
	 */
	public function get_session_data() {
		$data = array();

		if ( defined( 'AYECODE_CONNECT_VERSION' ) ) {
			$data['AyeCode Connect Version'] = AYECODE_CONNECT_VERSION;
		}

		if ( defined( 'GEODIRECTORY_VERSION' ) ) {
			$data['GeoDirectory Version'] = GEODIRECTORY_VERSION;
		}

		if ( defined( 'USERSWP_VERSION' ) ) {
			$data['UsersWP Version'] = USERSWP_VERSION;
		}

		if ( defined( 'WPINV_VERSION' ) ) {
			$data['Wp Invoicing Version'] = WPINV_VERSION;
		}

		return $data;
	}


}