<?php
/**
 * Settings admin page.
 *
 * @package WPCode
 */

/**
 * Class for the Settings admin page.
 */
class WPCode_Admin_Page_Settings extends WPCode_Admin_Page {

	/**
	 * The page slug to be used when adding the submenu.
	 *
	 * @var string
	 */
	public $page_slug = 'wpcode-settings';

	/**
	 * The action used for the nonce.
	 *
	 * @var string
	 */
	protected $action = 'wpcode-settings';

	/**
	 * The nonce name field.
	 *
	 * @var string
	 */
	protected $nonce_name = 'wpcode-settings_nonce';

	/**
	 * The view to be loaded by default.
	 *
	 * @var string
	 */
	public $view = 'general';

	/**
	 * The capability required to view this page.
	 *
	 * @var string
	 */
	protected $capability = 'wpcode_manage_settings';

	/**
	 * Call this just to set the page title translatable.
	 */
	public function __construct() {
		$this->page_title = __( 'WPCode Settings', 'insert-headers-and-footers' );
		$this->menu_title = __( 'Settings', 'insert-headers-and-footers' );
		parent::__construct();
	}

	/**
	 * Setup page-specific views.
	 *
	 * @return void
	 */
	protected function setup_views() {
		$this->views = array(
			'general' => __( 'General Settings', 'insert-headers-and-footers' ),
			'errors'  => __( 'Error Handling', 'insert-headers-and-footers' ),
			'access'  => __( 'Access Control', 'insert-headers-and-footers' ),
		);
	}

	/**
	 * Register hook on admin init just for this page.
	 *
	 * @return void
	 */
	public function page_hooks() {
		$this->process_message();
		add_action( 'admin_init', array( $this, 'submit_listener' ) );
		add_filter( 'wpcode_admin_js_data', array( $this, 'add_connect_strings' ) );
	}

	/**
	 * Handle the message after the settings are saved with a redirect.
	 *
	 * @return void
	 */
	public function process_message() {
		if ( isset( $_GET['message'] ) && 1 === absint( $_GET['message'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->set_success_message( __( 'Settings Saved.', 'insert-headers-and-footers' ) );
		}
	}

	/**
	 * Wrap this page in a form tag.
	 *
	 * @return void
	 */
	public function output() {
		?>
		<form action="<?php echo esc_url( $this->get_page_action_url() ); ?>" method="post">
			<?php parent::output(); ?>
		</form>
		<?php
	}

	/**
	 * The Settings page output.
	 *
	 * @return void
	 */
	public function output_content() {
		if ( method_exists( $this, 'output_view_' . $this->view ) ) {
			call_user_func( array( $this, 'output_view_' . $this->view ) );
		}
	}

	/**
	 * The Settings page output.
	 *
	 * @return void
	 */
	public function output_view_general() {
		$header_and_footers = wpcode()->settings->get_option( 'headers_footers_mode' );
		$usage_tracking     = wpcode()->settings->get_option( 'usage_tracking' );

		$description = __( 'This allows you to disable all Code Snippets functionality and have a single "Headers & Footers" item under the settings menu.', 'insert-headers-and-footers' );

		$description .= '<br />';
		$description .= sprintf(
		// Translators: Placeholders make the text bold.
			__( '%1$sNOTE:%2$s Please use this setting with caution. It will disable all custom snippets that you add using the new snippet management interface.', 'insert-headers-and-footers' ),
			'<strong>',
			'</strong>'
		);

		$this->metabox_row(
			__( 'License Key', 'insert-headers-and-footers' ),
			$this->get_license_key_input()
		);

		$this->metabox_row(
			__( 'Headers & Footers mode', 'insert-headers-and-footers' ),
			$this->get_checkbox_toggle(
				$header_and_footers,
				'headers_footers_mode',
				$description
			),
			'headers_footers_mode'
		);

		$this->common_settings();

		$this->metabox_row(
			__( 'Allow Usage Tracking', 'insert-headers-and-footers' ),
			$this->get_checkbox_toggle(
				$usage_tracking,
				'usage_tracking',
				esc_html__( 'By allowing us to track usage data, we can better help you, as we will know which WordPress configurations, themes, and plugins we should test.', 'insert-headers-and-footers' )
			),
			'usage_tracking'
		);

		$this->uninstall_setting();

		wp_nonce_field( $this->action, $this->nonce_name );

		?>
		<button class="wpcode-button" type="submit">
			<?php esc_html_e( 'Save Changes', 'insert-headers-and-footers' ); ?>
		</button>
		<?php
	}

	/**
	 * Output the uninstall setting.
	 *
	 * @return void
	 */
	public function uninstall_setting() {
		$uninstall_description = esc_html__( 'Remove ALL WPCode data upon plugin deletion.', 'insert-headers-and-footers' );

		$uninstall_description .= '<br />';
		$uninstall_description .= sprintf(
			'<strong style="color: #DF2A35">%s</strong>',
			esc_html__( 'All WPCode snippets & scripts will be unrecoverable.', 'insert-headers-and-footers' )
		);

		$this->metabox_row(
			__( 'Delete All Data on Uninstall', 'insert-headers-and-footers' ),
			$this->get_checkbox_toggle(
				wpcode()->settings->get_option( 'uninstall_data', false ),
				'wpcode-uninstall-data',
				$uninstall_description,
				1
			),
			'wpcode-admin-bar-info'
		);
	}

	/**
	 * Move common settings to a separate method so we can use it in other versions of this page.
	 *
	 * @return void
	 */
	public function common_settings() {
		$this->metabox_row(
			__( 'WPCode Library Connection', 'insert-headers-and-footers' ),
			$this->get_library_connection_input()
		);

		$this->metabox_row(
			__( 'Editor Height', 'insert-headers-and-footers' ),
			$this->get_editor_height_input(),
			'wpcode-editor-height'
		);

		$this->metabox_row(
			__( 'Dark Mode', 'insert-headers-and-footers' ),
			$this->get_checkbox_toggle(
				wpcode()->settings->get_option( 'dark_mode' ),
				'wpcode-dark-mode',
				esc_html__( 'Enable Dark Mode across WPCode.', 'insert-headers-and-footers' ),
				1
			),
			'wpcode-dark-mode'
		);

		$this->metabox_row(
			__( 'Admin Bar Info', 'insert-headers-and-footers' ),
			$this->get_checkbox_toggle(
				wpcode()->settings->get_option( 'admin_bar_info', true ),
				'wpcode-admin-bar-info',
				esc_html__( 'Enable the admin bar menu that shows info about which snippets & scripts are loaded on the current page.', 'insert-headers-and-footers' ),
				1
			),
			'wpcode-admin-bar-info'
		);
	}

	/**
	 * Get an input to connect or disconnect from the snippet library.
	 *
	 * @return string
	 */
	public function get_library_connection_input() {
		$button_classes = array(
			'wpcode-button',
		);
		$button_text    = __( 'Connect to the WPCode Library', 'insert-headers-and-footers' );
		if ( WPCode()->library_auth->has_auth() ) {
			$button_classes[] = 'wpcode-delete-auth';
			$button_text      = __( 'Disconnect from the WPCode Library', 'insert-headers-and-footers' );
		} else {
			$button_classes[] = 'wpcode-start-auth';
		}

		return sprintf(
			'<button type="button" class="%1$s">%2$s</button>',
			esc_attr( implode( ' ', $button_classes ) ),
			esc_html( $button_text )
		);
	}

	/**
	 * For this page we output a menu.
	 *
	 * @return void
	 */
	public function output_header_bottom() {
		?>
		<div class="wpcode-column">
			<ul class="wpcode-admin-tabs">
				<?php
				foreach ( $this->views as $slug => $label ) {
					if ( 'importer' === $slug ) {
						continue;
					}
					$class = $this->view === $slug ? 'active' : '';
					?>
					<li>
						<a href="<?php echo esc_url( $this->get_view_link( $slug ) ); ?>" class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $label ); ?></a>
					</li>
				<?php } ?>
			</ul>
		</div>
		<div class="wpcode-column">
		</div>
		<?php

	}

	/**
	 * If the form is submitted attempt to save the values.
	 *
	 * @return void
	 */
	public function submit_listener() {
		if ( ! isset( $_REQUEST[ $this->nonce_name ] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST[ $this->nonce_name ] ), $this->action ) ) {
			// Nonce is missing, so we're not even going to try.
			return;
		}

		if ( 'errors' === $this->view ) {
			$settings = array(
				'error_logging' => isset( $_POST['wpcode-error-logging'] ),
			);
		} else {
			$settings = array(
				'headers_footers_mode' => isset( $_POST['headers_footers_mode'] ),
				'editor_height_auto'   => isset( $_POST['editor_height_auto'] ),
				'editor_height'        => isset( $_POST['editor_height'] ) ? absint( $_POST['editor_height'] ) : 300,
				'usage_tracking'       => isset( $_POST['usage_tracking'] ),
				'admin_bar_info'       => isset( $_POST['wpcode-admin-bar-info'] ),
				'dark_mode'            => isset( $_POST['wpcode-dark-mode'] ),
				'uninstall_data'       => isset( $_POST['wpcode-uninstall-data'] ),
			);
		}

		$settings = $this->before_save( $settings );

		wpcode()->settings->bulk_update_options( $settings );

		if ( true === $settings['headers_footers_mode'] ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'    => 'wpcode-headers-footers',
						'message' => 1,
					),
					admin_url( 'options-general.php' )
				)
			);
			exit;
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'message' => 1,
				),
				$this->get_page_action_url()
			)
		);
		exit;
	}

	/**
	 * Give child classes a chance to update the settings object before saving.
	 *
	 * @param array $settings The settings to be saved.
	 *
	 * @return array
	 */
	public function before_save( $settings ) {
		return $settings;
	}

	/**
	 * Allow users to change the code editor height.
	 *
	 * @return string
	 */
	public function get_editor_height_input() {
		$editor_auto_height = boolval( wpcode()->settings->get_option( 'editor_height_auto' ) );
		$editor_height      = wpcode()->settings->get_option( 'editor_height', 300 );

		$html = sprintf(
			'<input type="number" min="100" value="%1$d" id="wpcode-editor-height" name="editor_height" %2$s />',
			absint( $editor_height ),
			disabled( $editor_auto_height, true, false )
		);

		$html .= $this->get_checkbox_toggle(
			$editor_auto_height,
			'editor_height_auto'
		);
		$html .= '<label for="editor_height_auto">' . __( 'Auto height', 'insert-headers-and-footers' ) . '</label>';

		$html .= '<p class="description">';
		$html .= esc_html__( 'Set the editor height in pixels or enable auto-grow so the code editor automatically grows in height with the code.', 'insert-headers-and-footers' );
		$html .= '</p>';

		return $html;
	}

	/**
	 * Get the license key input.
	 *
	 * @return string
	 */
	public function get_license_key_input() {
		ob_start();
		?>
		<div class="wpcode-metabox-form">
			<p><?php esc_html_e( 'You\'re using WPCode Lite - no license needed. Enjoy!', 'insert-headers-and-footers' ); ?>
				<img draggable="false" role="img" class="emoji" alt="🙂" src="https://s.w.org/images/core/emoji/14.0.0/svg/1f642.svg">
			</p>
			<p>
				<?php
				printf(
				// Translators: %1$s - Opening anchor tag, do not translate. %2$s - Closing anchor tag, do not translate.
					esc_html__( 'To unlock more features consider %1$supgrading to PRO%2$s.', 'insert-headers-and-footers' ),
					'<strong><a href="' . esc_url( wpcode_utm_url( 'https://wpcode.com/lite/', 'settings-license', 'upgrading-to-pro' ) ) . '" target="_blank" rel="noopener noreferrer">',
					'</a></strong>'
				)
				?>
			</p>
			<hr>
			<p><?php esc_html_e( 'Already purchased? Simply enter your license key below to enable WPCode PRO!', 'insert-headers-and-footers' ); ?></p>
			<p>
				<input type="password" class="wpcode-input-text" id="wpcode-settings-upgrade-license-key" placeholder="<?php esc_attr_e( 'Paste license key here', 'insert-headers-and-footers' ); ?>" value="">
				<button type="button" class="wpcode-button" id="wpcode-settings-connect-btn">
					<?php esc_html_e( 'Verify Key', 'insert-headers-and-footers' ); ?>
				</button>
			</p>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Add the strings for the connect page to the JS object.
	 *
	 * @param array $data The localized data we already have.
	 *
	 * @return array
	 */
	public function add_connect_strings( $data ) {
		$data['oops']                = esc_html__( 'Oops!', 'insert-headers-and-footers' );
		$data['ok']                  = esc_html__( 'OK', 'insert-headers-and-footers' );
		$data['almost_done']         = esc_html__( 'Almost Done', 'insert-headers-and-footers' );
		$data['plugin_activate_btn'] = esc_html__( 'Activate', 'insert-headers-and-footers' );
		$data['server_error']        = esc_html__( 'Unfortunately there was a server connection error.', 'insert-headers-and-footers' );

		return $data;
	}

	/**
	 * Output the form for the access management tab.
	 *
	 * @return void
	 */
	public function output_view_access() {
		echo '<div class="wpcode-blur-area">';
		$this->access_view_content();
		echo '</div>';
		echo $this->get_access_overlay();
	}

	/**
	 * Output the form for the access management tab.
	 *
	 * @return void
	 */
	public function output_view_errors() {

		$this->error_view_fields();

		wp_nonce_field( $this->action, $this->nonce_name );

		?>
		<button class="wpcode-button" type="submit">
			<?php esc_html_e( 'Save Changes', 'insert-headers-and-footers' ); ?>
		</button>
		<?php

	}

	/**
	 * The error handling tab fields.
	 *
	 * @return void
	 */
	public function error_view_fields() {
		$this->error_logging_field();

		?>
		<h2><?php esc_html_e( 'Email Notifications', 'insert-headers-and-footers' ); ?>
			&nbsp;<span class="wpcode-pro-pill">PRO</span></h2>
		<p>
			<?php esc_html_e( 'Receive email notifications when snippets throw errors or are automatically deactivated.', 'insert-headers-and-footers' ); ?>
		</p>
		<?php $this->wp_mail_smtp_notice(); ?>
		<hr/>
		<div style="position: relative">
			<div class="wpcode-blur-area">
				<?php
				$this->error_emails_fields();
				?>
			</div>
			<?php
			echo self::get_upsell_box(
				esc_html__( 'Email Notifications is a Pro Feature', 'insert-headers-and-footers' ),
				'<p>' . esc_html__( 'Do you want to get email notifications the moment your snippets throw an error or are automatically deactivated? Upgrade today and improve your workflow with WPCode Error Email Notifications.', 'insert-headers-and-footers' ) . '</p>',
				array(
					'text' => esc_html__( 'Upgrade to WPCode PRO', 'insert-headers-and-footers' ),
					'url'  => esc_url( wpcode_utm_url( 'https://wpcode.com/lite/', 'settings', 'tab-' . $this->view, 'email' ) ),
				)
			);
			?>
		</div>
		<?php
	}

	/**
	 * The fields used for email settings.
	 *
	 * @return void
	 */
	public function error_emails_fields() {
		$this->metabox_row(
			__( 'Error Notifications', 'insert-headers-and-footers' ),
			$this->get_checkbox_toggle(
				wpcode()->settings->get_option( 'emails_errors' ),
				'wpcode-emails-errors',
				sprintf(
				// Translators: %1$s: opening anchor tag, %2$s: closing anchor tag. Link to docs about error notifications.
					esc_html__( 'Send email notifications when snippets throw errors? %1$sLearn more%2$s', 'insert-headers-and-footers' ),
					'<a href="' . esc_url( wpcode_utm_url( 'https://wpcode.com/docs/error-notifications/', 'settings', 'errors', 'errornotifications' ) ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				1
			),
			'wpcode-error-notifications'
		);

		$this->metabox_row(
			__( 'Send To', 'insert-headers-and-footers' ),
			$this->get_input_text(
				'wpcode-emails-errors-addresses',
				wpcode()->settings->get_option( 'emails_errors_addresses', get_option( 'admin_email' ) ),
				__( 'Enter a comma separated list of email addresses to receive error notifications. Defaults to the admin email address.', 'insert-headers-and-footers' ),
				true
			),
			'wpcode-emails-errors-addresses'
		);

		$preview_url = add_query_arg(
			array(
				'wpcode_email_template' => 'error',
				'wpcode_email_preview'  => '1',
			),
			admin_url()
		);

		$this->metabox_row(
			'',
			'<a href="' . esc_url( $preview_url ) . '" class="wpcode-button wpcode-button-secondary" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Preview Email', 'insert-headers-and-footers' ) . '</a>',
			'wpcode-emails-errors-preview'
		);

		$this->metabox_row(
			__( 'Deactivation Notifications', 'insert-headers-and-footers' ),
			$this->get_checkbox_toggle(
				wpcode()->settings->get_option( 'emails_deactivated' ),
				'wpcode-emails-deactivated',
				sprintf(
				// Translators: %1$s: opening anchor tag, %2$s: closing anchor tag. Link to docs about error notifications.
					esc_html__( 'Send an email when a snippet gets automatically deactivated? %1$sLearn more%2$s', 'insert-headers-and-footers' ),
					'<a href="' . esc_url( wpcode_utm_url( 'https://wpcode.com/docs/error-notifications/', 'settings', 'errors', 'deactivated' ) ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				1
			),
			'wpcode-emails-deactivated'
		);

		$this->metabox_row(
			__( 'Send To', 'insert-headers-and-footers' ),
			$this->get_input_text(
				'wpcode-emails-deactivated-addresses',
				wpcode()->settings->get_option( 'emails_deactivated_addresses', get_option( 'admin_email' ) ),
				__( 'Enter a comma separated list of email addresses to receive deactivation notifications. Defaults to the admin email address.', 'insert-headers-and-footers' ),
				true
			),
			'wpcode-emails-deactivated-addresses'
		);

		$preview_url = add_query_arg(
			array(
				'wpcode_email_template' => 'deactivated',
				'wpcode_email_preview'  => '1',
			),
			admin_url()
		);

		$this->metabox_row(
			'',
			'<a href="' . esc_url( $preview_url ) . '" class="wpcode-button wpcode-button-secondary" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Preview Email', 'insert-headers-and-footers' ) . '</a>',
			'wpcode-emails-deactivated-preview'
		);
	}

	/**
	 * The error logging field.
	 *
	 * @return void
	 */
	public function error_logging_field() {
		$this->metabox_row(
			__( 'Error Logging', 'insert-headers-and-footers' ),
			$this->get_checkbox_toggle(
				wpcode()->settings->get_option( 'error_logging' ),
				'wpcode-error-logging',
				sprintf(
				// Translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
					esc_html__( 'Log errors thrown by snippets? %1$sView Logs%2$s', 'insert-headers-and-footers' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=wpcode-tools&view=logs' ) ) . '">',
					'</a>'
				),
				1
			),
			'wpcode-error-logging'
		);
	}

	/**
	 * The access management tab content.
	 *
	 * @return void
	 */
	public function access_view_content() {
		?>
		<h2><?php esc_html_e( 'Access Control', 'insert-headers-and-footers' ); ?></h2>
		<p>
			<?php
			printf(
			// Translators: %1$s - Opening anchor tag. %2$s - Closing anchor tag.
				esc_html__( 'Select the user roles that are allowed to manage different types of snippets or parts of WPCode. By default, all permissions are provided only to administrator users. Please see our %1$sAccess Control documentation%2$s for more details.', 'insert-headers-and-footers' ),
				'<a href="' . esc_url( wpcode_utm_url( 'https://wpcode.com/docs/access-control/', 'settings-access-controls', 'access-controls-documentation' ) ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);
			?>
		</p>
		<hr/>
		<?php
		$capabilities = $this->get_capabilites();

		foreach ( $capabilities as $capability => $capability_data ) {
			$selected_roles = wpcode()->settings->get_option( $capability, array() );
			$this->metabox_row(
				$capability_data['label'],
				$this->get_roles_dropdown( $selected_roles, 'wpcode_capability_' . $capability ) .
				'<p class="description">' . esc_html( $capability_data['description'] ) . '</p>'
			);
		}

		$this->php_setting();

		?>
		<button class="wpcode-button" type="submit">
			<?php esc_html_e( 'Save Changes', 'insert-headers-and-footers' ); ?>
		</button>
		<?php
	}

	/**
	 * Access control overlay.
	 *
	 * @return string
	 */
	public function get_access_overlay() {
		$text = sprintf(
		// translators: %1$s and %2$s are <u> tags.
			'<p>' . esc_html__( 'Improve the way you and your team manage your snippets with the WPCode Access Control settings. Enable other users on your site to manage different types of snippets or configure Conversion Pixels settings and update configuration files. This feature is available on the %1$sWPCode Pro%2$s plan or higher.', 'insert-headers-and-footers' ) . '</p>',
			'<u>',
			'</u>'
		);

		return self::get_upsell_box(
			esc_html__( 'Access Control is a PRO Feature', 'insert-headers-and-footers' ),
			$text,
			array(
				'text' => esc_html__( 'Upgrade to WPCode PRO', 'insert-headers-and-footers' ),
				'url'  => esc_url( wpcode_utm_url( 'https://wpcode.com/lite/', 'settings', 'tab-' . $this->view, 'upgrade-to-pro' ) ),
			),
			array(),
			array(
				esc_html__( 'Save time and improve website management with your team', 'insert-headers-and-footers' ),
				esc_html__( 'Delegate snippet management to other users with full control', 'insert-headers-and-footers' ),
				esc_html__( 'Enable other users to set up ads & 3rd party services', 'insert-headers-and-footers' ),
				esc_html__( 'Choose if PHP snippets should be enabled on the site', 'insert-headers-and-footers' ),
			)
		);
	}

	/**
	 * Output the PHP disable setting.
	 *
	 * @return void
	 */
	public function php_setting() {
		?>
		<h2><?php esc_html_e( 'PHP Snippets', 'insert-headers-and-footers' ); ?></h2>
		<?php
		$this->metabox_row(
			esc_html__( 'Disable PHP snippets', 'insert-headers-and-footers' ),
			$this->get_checkbox_toggle(
				boolval( wpcode()->settings->get_option( 'completely_disable_php' ) ),
				'completely_disable_php',
				esc_html__( 'This option will completely disable PHP snippets execution and the option to edit or add new PHP snippets.', 'insert-headers-and-footers' )
			),
			'wpcode_disable_php'
		);
	}

	/**
	 * Get the custom capabilities.
	 *
	 * @return array[]
	 */
	public function get_capabilites() {
		return wpcode_custom_capabilities();
	}

	/**
	 * Get a dropdown with the user roles.
	 *
	 * @param array  $selected The roles that are selected.
	 * @param string $name The name of the select.
	 * @param string $id The ID of the select, defaults to the name.
	 *
	 * @return string
	 */
	public function get_roles_dropdown( $selected, $name, $id = '' ) {
		if ( empty( $id ) ) {
			$id = $name;
		}
		$user_roles = wp_roles()->roles;
		$dropdown   = '<select name="' . esc_attr( $name ) . '[]" id="' . esc_attr( $id ) . '" class="wpcode-select2" multiple>';
		foreach ( $user_roles as $key => $user_role ) {
			if ( ! isset( $user_role['name'] ) ) {
				continue;
			}
			$check_role = is_multisite() ? 'superadmin' : 'administrator';
			if ( $check_role === $key ) {
				continue;
			}
			$dropdown .= '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $selected, true ), true, false ) . '>' . esc_html( $user_role['name'] ) . '</option>';
		}
		$dropdown .= '</select>';

		return $dropdown;
	}

	/**
	 * Display a notice to suggest installing WP Mail SMTP.
	 *
	 * @return void
	 */
	public function wp_mail_smtp_notice() {
		if ( function_exists( 'wp_mail_smtp' ) ) {
			return;
		}
		$dismissed_notices = get_option( 'wpcode_admin_notices', array() );
		$slug              = 'emailsmtp';
		$smtp_url          = add_query_arg(
			array(
				'type' => 'term',
				's'    => 'wp mail smtp',
				'tab'  => 'search',
			),
			admin_url( 'plugin-install.php' )
		);
		if ( ! isset( $dismissed_notices[ $slug ] ) || empty( $dismissed_notices[ $slug ]['dismissed'] ) ) {
			?>
			<div class="notice wpcode-notice notice-global is-dismissible" id="wpcode-notice-global-<?php echo esc_attr( $slug ); ?>">
				<h3><?php echo esc_html__( 'Make Sure Important Emails Reach Your Inbox', 'insert-headers-and-footers' ); ?></h3>
				<p>
					<?php
					printf(
					// Translators: %1$s: opening anchor tag, %2$s: closing anchor tag.
						esc_html__( 'Solve common email deliverability issues for good. %1$sGet WP Mail SMTP%2$s!', 'insert-headers-and-footers' ),
						'<a href="' . esc_url( $smtp_url ) . '">',
						'</a>'
					);
					?>
				</p>
			</div>
		<?php
			wpcode()->notice->enqueues();
		}
	}
}
