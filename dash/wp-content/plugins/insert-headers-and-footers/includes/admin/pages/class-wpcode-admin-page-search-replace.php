<?php
/**
 * Admin page for the Search & Replace tool.
 *
 * @package WPCode
 */

/**
 * Class for the Search & Replace admin page.
 */
class WPCode_Admin_Page_Search_Replace extends WPCode_Admin_Page {

	/**
	 * The page slug to be used when adding the submenu.
	 *
	 * @var string
	 */
	public $page_slug = 'wpcode-search-replace';

	/**
	 * The action used for the nonce.
	 *
	 * @var string
	 */
	protected $action = 'wpcode-search-replace';

	/**
	 * The nonce name field.
	 *
	 * @var string
	 */
	protected $nonce_name = 'wpcode-search-replace_nonce';

	/**
	 * Call this just to set the page title translatable.
	 */
	public function __construct() {
		$this->page_title = 'Search & Replace Everything';
		$this->menu_title = 'Search & Replace';
		parent::__construct();
	}

	/**
	 * Register hook on admin init just for this page.
	 *
	 * @return void
	 */
	public function page_hooks() {
		add_action( 'admin_init', array( $this, 'maybe_redirect_to_search_replace' ) );
	}

	/**
	 * Override to hide default header on this page.
	 *
	 * @return void
	 */
	public function output_header() {

	}

	/**
	 * Redirect to the search replace page if the plugin is active.
	 *
	 * @return void
	 */
	public function maybe_redirect_to_search_replace() {
		if ( function_exists( 'wsrw_main' ) ) {
			wp_safe_redirect( admin_url( 'tools.php?page=wsrw-search-replace' ) );
			exit;
		}
	}

	/**
	 * The page output.
	 *
	 * @return void
	 */
	public function output_content() {
		?>
		<div class="wpcode-plugin-page wpcode-plugin-page-search-replace">
			<div class="wpcode-plugin-page-image">
				<?php wpcode_icon( 'logo-sr', 90, 90, '0 0 65 64' ); ?>
			</div>
			<div class="wpcode-plugin-page-title">
				<h1>Search & Replace Everything by WPCode</h1>
				<p>
					<?php esc_html_e( 'Confidently replace any text in your database and update images without duplicating uploads, all with a single plugin.', 'insert-headers-and-footers' ); ?>
				</p>
			</div>
			<section class="wpcode-plugin-screenshot">
				<div class="wpcode-plugin-screenshot-image">
					<img src="<?php echo esc_url( WPCODE_PLUGIN_URL ); ?>admin/images/sr-screenshot-thumb.jpg" alt="<?php esc_attr_e( 'Search & Replace Everything Screenshot', 'insert-headers-and-footers' ); ?>"/>
					<a href="<?php echo esc_url( WPCODE_PLUGIN_URL ); ?>admin/images/sr-screenshot.jpg" data-lity>
						<?php wpcode_icon( 'search', 16, 16 ); ?>
					</a>
				</div>
				<ul>
					<li><?php esc_html_e( 'Replace Text Across Your Whole Database.', 'insert-headers-and-footers' ); ?></li>
					<li><?php esc_html_e( 'Preview Changes Every Time.', 'insert-headers-and-footers' ); ?></li>
					<li><?php esc_html_e( 'Replace Image Sources to Clear Up Space.', 'insert-headers-and-footers' ); ?></li>
					<li><?php esc_html_e( 'Support for Serialized Data.', 'insert-headers-and-footers' ); ?></li>
				</ul>
			</section>
			<section class="wpcode-plugin-step wpcode-plugin-step-install">
				<aside class="wpcode-plugin-page-step-num">
					<?php wpcode_icon( 'step-1', 50, 50, '0 0 100 100' ); ?>
					<i class="wpcode-plugin-page-step-loader wpcode-plugin-page-step-loader-hidden"></i>
				</aside>
				<div>
					<h2>
						<?php
						printf(
						// translators: %s is the plugin name.
							esc_html__( 'Install and Activate %s', 'insert-headers-and-footers' ),
							'Search & Replace Everything'
						)
						?>
					</h2>
					<p>
						<?php
						printf(
						// translators: %s is the plugin name.
							esc_html__( 'Install %s from the WordPress.org plugin repository.', 'insert-headers-and-footers' ),
							'Search & Replace Everything'
						)
						?>
					</p>
					<?php
					// Let's check if you can install plugins on this site.
					if ( current_user_can( 'install_plugins' ) && wp_is_file_mod_allowed( 'install_plugins' ) ) {
						?>
						<button class="wpcode-button wpcode-button-install-plugin" data-slug="search-replace-wpcode">
							<?php
							printf(
							// translators: %s is the plugin name.
								esc_html__( 'Install %s', 'insert-headers-and-footers' ),
								'Search & Replace Everything'
							);
							?>
						</button>
						<?php
					} else {
						?>
						<p>
							<?php esc_html_e( 'Please ask your website administrator to install Search & Replace Everything.', 'insert-headers-and-footers' ); ?>
						</p>
						<?php
					}
					?>
				</div>
			</section>
		</div>
		<?php
	}

	/**
	 * For this page we output a title and the save button.
	 *
	 * @return void
	 */
	public function output_header_bottom() {
	}
}
