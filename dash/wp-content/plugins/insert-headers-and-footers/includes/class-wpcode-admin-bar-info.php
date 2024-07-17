<?php
/**
 * This class adds the WPCode info to the admin bar for logged-in administrator users
 * that can manage snippets (wpcode_activate_snippets capability).
 * The class will gather info about the header & footer items added and all the active snippets
 * and display a count of scripts/snippets added by wpcode after the page is fully loaded.
 */

/**
 * Class WPCode_Admin_Bar_Info
 */
abstract class WPCode_Admin_Bar_Info {

	/**
	 * The global locations disabled for the current page through the Page Scripts settings.
	 *
	 * @var array
	 */
	protected $global_disabled = array();

	/**
	 * The snippets loaded on this page.
	 *
	 * @var array
	 */
	public $loaded_snippets = array();

	/**
	 * The WPCode_Admin_Bar_Info constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'maybe_init' ), 1 );
	}

	/**
	 * Check if we should be tracking for the current session.
	 *
	 * @return bool
	 */
	public function should_track() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( ! current_user_can( 'wpcode_activate_snippets' ) ) {
			return false;
		}

		// Don't load the admin bar menu in headers & footers mode.
		if ( wpcode()->settings->get_option( 'headers_footers_mode' ) ) {
			return false;
		}

		$show_menu = wpcode()->settings->get_option( 'admin_bar_info', true );

		if ( ! $show_menu ) {
			return false;
		}

		return true;
	}

	/**
	 * Early on the plugins_loaded hook we check if we should be tracking for the current session.
	 *
	 * @return void
	 */
	public function maybe_init() {
		if ( $this->should_track() ) {
			$this->hooks();
		}
	}

	/**
	 * Add hooks needed to track the way WPCode loads scripts and snippets.
	 */
	public function hooks() {
		// Use the snippet output to more accurately track the snippets loaded.
		add_filter( 'wpcode_snippet_output', array( $this, 'track_snippet_output' ), 999, 2 );

		// Add an admin menu item to display the results.
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_info' ), 999 );

		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_quick_links' ), 1200 );

		// Output results at the end of the page and use JS to populate the admin menu item.
		add_action( 'wp_footer', array( $this, 'add_footer_info' ), 15 );
		add_action( 'admin_footer', array( $this, 'add_footer_info' ), 999999 );

		add_action( 'admin_init', array( $this, 'enqueue_scripts' ), - 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );
		add_action( 'template_redirect', array( $this, 'enqueue_scripts' ), - 5 );
	}

	/**
	 * Load the scripts used by the admin bar.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! is_admin_bar_showing() ) {
			return;
		}
		// Allow other plugins to modify the screens where the admin bar scripts are loaded.
		if ( apply_filters( 'wpcode_load_admin_bar_scripts', false ) ) {
			return;
		}

		$admin_asset_file = WPCODE_PLUGIN_PATH . 'build/admin-bar.asset.php';

		if ( ! file_exists( $admin_asset_file ) ) {
			return;
		}

		$asset = require $admin_asset_file;

		wp_enqueue_style( 'wpcode-admin-bar-css', WPCODE_PLUGIN_URL . 'build/admin-bar.css', null, $asset['version'] );

		wp_enqueue_script( 'wpcode-admin-bar-js', WPCODE_PLUGIN_URL . 'build/admin-bar.js', null, $asset['version'], true );

	}

	/**
	 * Add the WPCode info to the admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
	 */
	public function add_admin_bar_info( $wp_admin_bar ) {
		// Let's see if we have any errors.
		$error_count = wpcode()->error->get_error_count();
		$indicator   = '';

		if ( $error_count > 0 ) {
			$indicator = ' <div class="wp-core-ui wp-ui-notification wpcode-menu-notification-counter">' . $error_count . '</div>';
		}

		// Add an admin menu item to append our count to.
		$wp_admin_bar->add_menu(
			array(
				'id'    => 'wpcode-admin-bar-info',
				'title' => 'WPCode' . $indicator,
				'meta'  => array(
					'class' => 'wpcode-admin-bar-info menupop',
				),
				'href'  => add_query_arg( 'page', 'wpcode', admin_url( 'admin.php' ) ),
			)
		);

		do_action( 'wpcode_admin_bar_info_top', $wp_admin_bar );

		if ( ! empty( $error_count ) ) {
			$wp_admin_bar->add_menu(
				array(
					'id'     => 'wpcode-error-count',
					'parent' => 'wpcode-admin-bar-info',
					'title'  => esc_html__( 'Snippets With Errors', 'insert-headers-and-footers' ) . $indicator,
					'meta'   => array(
						'class' => 'wpcode-admin-bar-info-submenu',
					),
					'href'   => add_query_arg(
						array(
							'page' => 'wpcode',
							'view' => 'has_error',
						),
						admin_url( 'admin.php' )
					),
				)
			);
		}

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'wpcode-description',
				'parent' => 'wpcode-admin-bar-info',
				'title'  => esc_html__( 'Loaded on this page', 'insert-headers-and-footers' ),
				'meta'   => array(
					'class' => 'wpcode-admin-bar-info-submenu wpcode-admin-bar-description',
				),
			)
		);

		$global_scripts_data = $this->get_global_scripts_data();

		if ( ! empty( $global_scripts_data ) ) {

			// Calculate total by adding up the "count" property of each item.
			$total = array_sum( wp_list_pluck( $global_scripts_data, 'count' ) );

			$wp_admin_bar->add_menu(
				array(
					'id'     => 'wpcode-global-scripts',
					'parent' => 'wpcode-admin-bar-info',
					'title'  => esc_html(
						sprintf(
						// translators: %d is the total number of global scripts.
							__( 'Global Scripts (%d)', 'insert-headers-and-footers' ),
							$total
						)
					),
					'meta'   => array(
						'class' => 'wpcode-admin-bar-info-submenu',
					),
					'href'   => add_query_arg( 'page', 'wpcode-headers-footers', admin_url( 'admin.php' ) ),
				)
			);

			foreach ( $global_scripts_data as $id => $global_scripts_area ) {
				$wp_admin_bar->add_menu(
					array(
						'id'     => 'wpcode-global-' . $id,
						'parent' => 'wpcode-global-scripts',
						'title'  => esc_html( $global_scripts_area['label'] ),
						'meta'   => array(
							'class' => 'wpcode-admin-bar-info-submenu',
						),
						'href'   => $global_scripts_area['href'],
					)
				);
			}
		}

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'wpcode-loaded-on-this-page',
				'parent' => 'wpcode-admin-bar-info',
				'title'  => esc_html__( 'Code Snippets', 'insert-headers-and-footers' ),
				'meta'   => array(
					'class' => 'wpcode-admin-bar-info-submenu',
				),
				'href'   => add_query_arg( 'page', 'wpcode', admin_url( 'admin.php' ) ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'wpcode-admin-bar-info-replace',
				'parent' => 'wpcode-loaded-on-this-page',
				'title'  => '',
				'meta'   => array(
					'class' => 'wpcode-admin-bar-info-submenu',
				),
			)
		);
	}

	/**
	 * Use the filter called in each location to keep tabs of which snippets were actually loaded in each location.
	 *
	 * @param WPCode_Snippet[] $snippets Array of snippets.
	 * @param string           $location The location.
	 *
	 * @return mixed
	 */
	public function track_used_snippets( $snippets, $location ) {

		foreach ( $snippets as $snippet ) {
			if ( ! isset( $this->loaded_snippets[ $location ] ) ) {
				$this->loaded_snippets[ $location ] = array();
			}
			$this->loaded_snippets[ $location ][] = $snippet;
		}

		return $snippets;
	}

	/**
	 * Output a script that builds the admin bar menu with the snippet/scripts info using JS after the page has loaded.
	 *
	 * @return void
	 */
	public function add_footer_info() {

		if ( ! is_admin_bar_showing() ) {
			return;
		}

		$footer_info = array();

		foreach ( $this->loaded_snippets as $location => $snippets ) {

			$location_label = wpcode()->auto_insert->get_location_label( $location );
			if ( 'shortcode' === $location ) {
				$location_label = esc_html__( 'Shortcode', 'insert-headers-and-footers' );
			}
			if ( 'block' === $location ) {
				$location_label = esc_html__( 'Gutenberg Block', 'insert-headers-and-footers' );
			}
			if ( 0 === strpos( $location, 'shortcode-' ) ) {
				$location_label = esc_html__( 'Custom Shortcode', 'insert-headers-and-footers' );
			}
			$location_info = array(
				'label'       => $location_label . ' (' . count( $snippets ) . ')',
				'location_id' => $location,
				'snippets'    => array(),
				'href'        => $this->get_location_filter_link( $location ),
			);
			foreach ( $snippets as $snippet ) {
				$location_info['snippets'][] = array(
					'id'        => $snippet->get_id(),
					'title'     => esc_html( $snippet->get_title() ),
					'edit_link' => $this->get_snippet_edit_link( $snippet ),
				);
			}
			$location_info['count'] = count( $snippets );

			$footer_info[] = $location_info;
		}

		$total_count = 0;
		foreach ( $footer_info as $location ) {
			$total_count += $location['count'];
		}

		// Output $footer_info as a JSON in a JS variable to be used in the script to populate the admin bar.
		?>
		<script>
			var wpcode_admin_bar_info = <?php echo wp_json_encode( $footer_info ); ?>;
			var wpcode_admin_bar_info_count = <?php echo absint( $total_count ); ?>;
		</script>
		<?php
	}

	/**
	 * Get the snippet edit link based on the snippet id.
	 *
	 * @param WPCode_Snippet $snippet The snippet object.
	 *
	 * @return string
	 */
	public function get_snippet_edit_link( $snippet ) {
		return apply_filters( 'wpcode_admin_bar_edit_snippet_link', admin_url( 'admin.php?page=wpcode-snippet-manager&snippet_id=' . $snippet->get_id() ), $snippet );
	}

	/**
	 * Get data related to the global Header & Footer scripts.
	 *
	 * @return array
	 */
	public function get_global_scripts_data() {
		if ( is_admin() ) {
			// Global scripts are never loaded in the admin.
			return array();
		}
		$data = array();

		$global_locations = array(
			'header' => array(
				'label' => __( 'Global Header', 'insert-headers-and-footers' ),
			),
		);
		if ( function_exists( 'wp_body_open' ) && version_compare( get_bloginfo( 'version' ), '5.2', '>=' ) ) {
			$global_locations['body'] = array(
				'label' => __( 'Global Body', 'insert-headers-and-footers' ),
			);
		}
		$global_locations['footer'] = array(
			'label' => __( 'Global Footer', 'insert-headers-and-footers' ),
		);

		$disabled_label = esc_html__( 'Disabled via Page Scripts', 'insert-headers-and-footers' );

		foreach ( $global_locations as $location => $location_texts ) {
			$scripts = get_option( 'ihaf_insert_' . $location );

			$count = ! empty( $scripts ) ? 1 : 0;

			if ( in_array( $location, $this->global_disabled, true ) ) {
				$count = 0;

				$location_texts['label'] .= ' (' . $disabled_label . ')';
			} else {
				$location_texts['label'] .= ' (' . $count . ')';
			}
			$data[ $location ] = array(
				'label' => $location_texts['label'],
				'href'  => admin_url( 'admin.php?page=wpcode-headers-footers#wpcode-global-' . $location ),
				'count' => $count,
			);
		}

		return $data;
	}

	/**
	 * Add the WPCode info to the admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
	 */
	public function add_admin_bar_quick_links( $wp_admin_bar ) {


		$wp_admin_bar->add_menu(
			array(
				'id'     => 'wpcode-admin-bar-info-add-new',
				'parent' => 'wpcode-admin-bar-info',
				'title'  => esc_html__( '+ Add Snippet', 'insert-headers-and-footers' ),
				'href'   => admin_url( 'admin.php?page=wpcode-snippet-manager' ),
				'meta'   => array(
					'class' => 'wpcode-admin-bar-info-submenu wpcode-admin-bar-info-separator-top',
				),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'wpcode-admin-bar-info-settings',
				'parent' => 'wpcode-admin-bar-info',
				'title'  => esc_html__( 'Settings', 'insert-headers-and-footers' ),
				'href'   => add_query_arg( 'page', 'wpcode-settings', admin_url( 'admin.php' ) ),
				'meta'   => array(
					'class' => 'wpcode-admin-bar-info-submenu',
				),
			)
		);

		// If error logging is enabled add a direct link here.
		if ( wpcode()->settings->get_option( 'error_logging' ) ) {
			$wp_admin_bar->add_menu(
				array(
					'id'     => 'wpcode-admin-bar-info-error-logs',
					'parent' => 'wpcode-admin-bar-info',
					'title'  => esc_html__( 'Logs', 'insert-headers-and-footers' ),
					'href'   => add_query_arg(
						array(
							'page' => 'wpcode-tools',
							'view' => 'logs',

						),
						admin_url( 'admin.php' )
					),
					'meta'   => array(
						'class' => 'wpcode-admin-bar-info-submenu',
					),
				)
			);
		}

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'wpcode-admin-bar-info-help',
				'parent' => 'wpcode-admin-bar-info',
				'title'  => esc_html__( 'Help Docs', 'insert-headers-and-footers' ),
				'href'   => wpcode_utm_url( 'https://wpcode.com/docs/', 'admin-bar', 'help' ),
				'meta'   => array(
					'class'  => 'wpcode-admin-bar-info-submenu',
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
				),
			)
		);
	}

	/**
	 * Track a snippet output for the admin bar.
	 *
	 * @param string         $output The snippet output (we don't use this).
	 * @param WPCode_Snippet $snippet The snippet object.
	 *
	 * @return string
	 */
	public function track_snippet_output( $output, $snippet ) {

		$location = $snippet->get_location();

		if ( ! isset( $this->loaded_snippets[ $location ] ) ) {
			$this->loaded_snippets[ $location ] = array();
		}
		$this->loaded_snippets[ $location ][] = $snippet;

		return $output;
	}

	/**
	 * Method for the location link.
	 *
	 * @param string $location The location slug.
	 *
	 * @return string
	 */
	public function get_location_filter_link( $location ) {

		if ( in_array( $location, array( 'shortcode', 'block' ), true ) ) {
			// If this is for a shortcode or a block let's try to link to the edit page of the currently loaded post.
			return get_edit_post_link();
		}

		return esc_url(
			add_query_arg(
				array(
					'page'          => 'wpcode',
					'location'      => $location,
					'filter_action' => 'filter',
				),
				admin_url( 'admin.php' )
			)
		);
	}

}
