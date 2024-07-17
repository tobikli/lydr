<?php
/**
 * File editor page.
 *
 * @package WPCode
 */

/**
 * Class for the file editor page.
 */
class WPCode_Admin_Page_File_Editor extends WPCode_Admin_Page {

	/**
	 * The page slug.
	 *
	 * @var string
	 */
	public $page_slug = 'wpcode-file-editor';

	/**
	 * The default view.
	 *
	 * @var string
	 */
	public $view = 'adstxt';

	/**
	 * The capability required to view this page.
	 *
	 * @var string
	 */
	protected $capability = 'wpcode_file_editor';

	/**
	 * WPCode_Admin_Page_File_Editor constructor.
	 */
	public function __construct() {
		$this->page_title = __( 'File Editor', 'insert-headers-and-footers' );
		parent::__construct();
	}

	/**
	 * Add codemirror settings specific to the needs for this page.
	 *
	 * @return void
	 */
	public function page_hooks() {
		// Add the codemirror settings.
		add_filter( 'wpcode_editor_config', array( $this, 'editor_config' ) );
	}

	/**
	 * Output the page content.
	 *
	 * @return void
	 */
	public function output_content() {
		$this->can_edit = true;
		echo '<div class="wpcode-blur-area" style="min-height: 700px">';
		$this->file_editor_area();
		echo '</div>';
		echo $this->get_pixel_overlay();
	}

	/**
	 * File editor area.
	 *
	 * @return void
	 */
	public function file_editor_area() {
		$value = $this->get_value();
		?>
		<div class="wpcode-code-textarea" id="wpcode-edit-<?php echo esc_attr( $this->view ); ?>-area" style="max-width: 1000px">
			<div class="wpcode-flex">
				<div class="wpcode-column">
					<h2>
						<label for="wpcode_file_<?php echo esc_attr( $this->view ); ?>">
							<?php
							printf(
							// Translators: %s is the name of the file.
								esc_html__( '%s Editor', 'insert-headers-and-footers' ),
								esc_html( $this->views[ $this->view ] )
							);
							?>
						</label>
					</h2>
				</div>
				<div class="wpcode-column">
					<div class="wpcode-input-select">
						<label for="<?php echo esc_attr( 'wpcode_file_' . $this->view . '[enabled]' ); ?>">
							<?php esc_html_e( 'Enable file output', 'insert-headers-and-footers' ); ?>
						</label>
						<?php
						echo $this->get_checkbox_toggle(
							$value['enabled'],
							'wpcode_file_' . $this->view . '[enabled]'
						)
						?>
					</div>
				</div>
			</div>
			<textarea name="wpcode_file_<?php echo esc_attr( $this->view ); ?>[content]" id="wpcode_file_<?php echo esc_attr( $this->view ); ?>" class="widefat" rows="8"><?php echo esc_textarea( $value['content'] ); ?></textarea>
			<p>
				<?php
				printf(
				// Translators: %s is the name of the file.
					esc_html__( 'Use this area to edit your %s file.', 'insert-headers-and-footers' ),
					esc_html( $this->views[ $this->view ] )
				);
				?>
			</p>
			<p>
				<?php
				printf(
				// Translators: %1$s is the opening link tag, %2$s is the closing link tag.
					esc_html__( 'The file contents above will be used to generated a dynamic file. There is no physical file created on your server. %1$sLearn more%2$s.', 'insert-headers-and-footers' ),
					'<a href="' . esc_url( wpcode_utm_url( 'https://wpcode.com/docs/file-editor/', 'file-editor', 'docs', 'learn-more' ) ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);
				?>
			</p>
		</div>
		<?php

		wp_nonce_field( 'wpcode-edit-' . $this->view, 'wpcode-edit-' . $this->view . '-nonce' );

		// If we need to add page-specific content we can do this here.
		if ( method_exists( $this, 'output_view_' . $this->view ) ) {
			call_user_func( array( $this, 'output_view_' . $this->view ) );
		}

		?>
		<button class="wpcode-button" type="submit" <?php echo $this->can_edit ? '' : 'disabled'; ?>>
			<?php esc_html_e( 'Save Changes', 'insert-headers-and-footers' ); ?>
		</button>
		<a href="<?php echo esc_url( home_url() . '/' . $this->views[ $this->view ] ); ?>" class="wpcode-button wpcode-button-secondary" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'View File', 'insert-headers-and-footers' ); ?>
		</a>
		<?php
	}

	/**
	 * For this page we output a menu.
	 *
	 * @return void
	 */
	public function output_header_bottom() {
		?>
		<ul class="wpcode-admin-tabs">
			<?php
			foreach ( $this->views as $slug => $label ) {
				$class = $this->view === $slug ? 'active' : '';
				?>
				<li>
					<a href="<?php echo esc_url( $this->get_view_link( $slug ) ); ?>" class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $label ); ?></a>
				</li>
			<?php } ?>
		</ul>
		<?php
	}

	/**
	 * Page specific scripts. Hooked to 'admin_enqueue_scripts'.
	 *
	 * @return void
	 */
	public function page_scripts() {
		$editor = new WPCode_Code_Editor( 'text' );

		$editor->register_editor( 'wpcode_file_' . $this->view );
		$editor->init_editor();
	}

	/**
	 * Disable all the codemirror settings for this page as we need a basic editor.
	 *
	 * @param array $config The WPCode editor config.
	 *
	 * @return array
	 */
	public function editor_config( $config ) {

		if ( 'serviceworkerjs' === $this->view ) {
			$config['type'] = 'text/javascript';
		} else {
			$config['type']       = 'shell';
			$config['codemirror'] = array();
			$config['showHint']   = 'false';
		}

		return $config;
	}

	/**
	 * Output content specific to the robots.txt file.
	 *
	 * @return void
	 */
	public function output_view_robotstxt() {
		?>
		<p>
			<?php esc_html_e( 'Content added here will be appended to the robots.txt content generated by WordPress and other plugins.', 'insert-headers-and-footers' ); ?>
		</p>
		<?php
	}

	/**
	 * Setup page-specific views.
	 *
	 * @return void
	 */
	protected function setup_views() {
		$this->views = array(
			'adstxt'          => 'ads.txt',
			'appadstxt'       => 'app-ads.txt',
			'serviceworkerjs' => 'service-worker.js',
			'robotstxt'       => 'robots.txt',
		);
	}

	/**
	 * Nothing to return by default.
	 *
	 * @return array
	 */
	public function get_value() {
		return array(
			'enabled' => true,
			'content' => '',
		);
	}

	/**
	 * Get the overlay for the file editor page.
	 *
	 * @return string
	 */
	public function get_pixel_overlay() {

		$text = '<p>' . esc_html__( 'Simplify your website management with the WPCode File Editor! Say goodbye to the hassle of manually editing files on your server.', 'insert-headers-and-footers' ) . '<p>';

		$text .= sprintf(
		// translators: %1$s and %2$s are <u> tags.
			'<p>' . esc_html__( 'With this powerful tool, you can easily customize crucial files like %1$sads.txt%2$s, %1$sapp-ads.txt%2$s, %1$srobots.txt%2$s, and %1$sservice-worker.js%2$s right from your WordPress admin.', 'insert-headers-and-footers' ) . '</p>',
			'<u>',
			'</u>'
		);


		return self::get_upsell_box(
			__( 'File Editor is a PRO Feature', 'insert-headers-and-footers' ),
			$text,
			array(
				'text' => esc_html__( 'Upgrade to WPCode PRO', 'insert-headers-and-footers' ),
				'url'  => esc_url( wpcode_utm_url( 'https://wpcode.com/lite/', 'file-editor', 'tab-' . $this->view, 'upgrade-to-pro' ) ),
			),
			array(
				'text' => esc_html__( 'Learn More about the File Editor', 'insert-headers-and-footers' ),
				'url'  => esc_url( wpcode_utm_url( 'https://wpcode.com/docs/file-editor/', 'file-editor', 'tab-' . $this->view, 'learn-more' ) ),
			),
			array(
				esc_html__( 'No manual coding, no FTP', 'insert-headers-and-footers' ),
				esc_html__( 'Effortless integrations setup', 'insert-headers-and-footers' ),
				esc_html__( 'Reduce the number of plugins', 'insert-headers-and-footers' ),
				esc_html__( 'Prevent advertising fraud', 'insert-headers-and-footers' ),
			)
		);
	}
}
