<?php
/**
 * Snippet manager page - add/edit snippets.
 *
 * @package WPCode
 */

/**
 * WPCode_Admin_Page_Snippet_Manager class.
 */
class WPCode_Admin_Page_Snippet_Manager extends WPCode_Admin_Page {

	use WPCode_Revisions_Display_Lite;

	/**
	 * The page slug to be used when adding the submenu.
	 *
	 * @var string
	 */
	public $page_slug = 'wpcode-snippet-manager';
	/**
	 * The publish button text depending on the status.
	 *
	 * @var string
	 */
	public $publish_button_text;
	/**
	 * The header title text depending on the status.
	 *
	 * @var string
	 */
	public $header_title;
	/**
	 * The default code type for this page is HTML.
	 *
	 * @var string
	 */
	public $code_type = 'html';
	/**
	 * The action for the nonce when the current page is submitted.
	 *
	 * @var string
	 */
	protected $action = 'wpcode-save-snippet';

	/**
	 * The name of the nonce used for saving.
	 *
	 * @var string
	 */
	protected $nonce_name = 'wpcode-save-snippet-nonce';
	/**
	 * The snippet id.
	 *
	 * @var int
	 */
	protected $snippet_id;
	/**
	 * The snippet instance.
	 *
	 * @var WPCode_Snippet
	 */
	protected $snippet;

	/**
	 * Whether the snippet is currently edited by someone else.
	 *
	 * @var bool
	 */
	protected $is_locked = false;

	/**
	 * The name of user who locked the snippet.
	 *
	 * @var string
	 */
	protected $locked_by;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Translators: This adds the name of the plugin "WPCode".
		$this->page_title = sprintf( __( 'Add %s Snippet', 'insert-headers-and-footers' ), 'WPCode' );
		$this->menu_title = sprintf( '+ %s', __( 'Add Snippet', 'insert-headers-and-footers' ) );
		parent::__construct();
	}

	/**
	 * Load the snippet if we're editing one.
	 *
	 * @return void
	 */
	public function load_snippet() {
		if ( isset( $_GET['snippet_id'] ) ) {
			$snippet_post = get_post( absint( $_GET['snippet_id'] ) );
			if ( ! is_null( $snippet_post ) && $this->get_post_type() === $snippet_post->post_type ) {
				$this->snippet_id = $snippet_post->ID;
				$this->snippet    = wpcode_get_snippet( $snippet_post );

				// Let's check if it's not already being edited by someone else.
				$snippet_locked = wp_check_post_lock( $this->snippet_id );
				if ( $snippet_locked ) {
					$locked_by = get_user_by( 'id', $snippet_locked );
					if ( $locked_by ) {
						$this->locked_by = $locked_by->display_name;
						$this->is_locked = true;
					}
				} else {
					wp_set_post_lock( $this->snippet_id );
				}
			}
		}
	}

	/**
	 * Page-specific hooks.
	 *
	 * @return void
	 */
	public function page_hooks() {
		$this->can_edit = current_user_can( 'wpcode_edit_snippets' );
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['snippet_id'] ) ) {
			$this->load_snippet();
			// If the post type does not match the page will act as an add new snippet page, the id will be ignored.
		} elseif ( ! isset( $_GET['custom'] ) ) {
			$this->show_library = apply_filters( 'wpcode_add_snippet_show_library', true );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		$this->publish_button_text = __( 'Save Snippet', 'insert-headers-and-footers' );
		$this->header_title        = __( 'Create Custom Snippet', 'insert-headers-and-footers' );
		if ( isset( $this->snippet ) ) {
			$this->header_title        = __( 'Edit Snippet', 'insert-headers-and-footers' );
			$this->publish_button_text = __( 'Update', 'insert-headers-and-footers' );
		}
		if ( $this->show_library ) {
			$this->header_title = __( 'Add Snippet', 'insert-headers-and-footers' );
		}
		$this->process_message();
		add_action( 'admin_init', array( $this, 'check_status' ) );
		add_filter( 'submenu_file', array( $this, 'change_current_menu' ) );
		add_filter( 'admin_title', array( $this, 'change_page_title' ), 15, 2 );
		add_action( 'admin_init', array( $this, 'submit_listener' ) );
		add_action( 'admin_init', array( $this, 'set_code_type' ) );
		add_filter( 'wpcode_admin_js_data', array( $this, 'add_conditional_rules_to_script' ) );
		add_filter( 'admin_body_class', array( $this, 'body_class_code_type' ) );
		add_filter( 'admin_body_class', array( $this, 'maybe_editor_height_auto' ) );
		add_filter( 'admin_body_class', array( $this, 'maybe_syntax_highlighting_disabled' ) );
		add_filter( 'admin_head', array( $this, 'maybe_editor_height' ) );
		add_action( 'wpcode_admin_notices', array( $this, 'maybe_show_error_notice' ) );

		add_filter( 'user_can_richedit', '__return_true', 150 ); // Override other plugins that disable TinyMCE.
	}

	/**
	 * Make sure we can't edit a trashed snippet.
	 *
	 * @return void
	 */
	public function check_status() {
		if ( ! isset( $this->snippet ) ) {
			return;
		}
		$post_data = $this->snippet->get_post_data();
		if ( 'trash' === $post_data->post_status ) {
			wp_die( esc_html__( 'You cannot edit this snippet because it is in the Trash. Please restore it and try again.', 'insert-headers-and-footers' ) );
		}
	}

	/**
	 * Process messages specific to this page.
	 *
	 * @return void
	 */
	public function process_message() {
		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_GET['message'] ) ) {
			$snippet_types = array_keys( wpcode()->execute->get_options() );
			if ( in_array( 'html', $snippet_types, true ) && ! current_user_can( 'unfiltered_html', 'wpcode-editor' ) ) {
				$this->set_error_message( __( 'Sorry, you only have read-only access to this page. Ask your administrator for assistance editing.', 'insert-headers-and-footers' ) );
			}

			return;
		}

		$error_details = '';
		if ( isset( $_GET['error'] ) ) {
			$error_details = sanitize_text_field( wp_unslash( $_GET['error'] ) );
		}

		$messages = array(
			1 => __( 'Snippet updated.', 'insert-headers-and-footers' ),
			2 => __( 'Snippet created & Saved.', 'insert-headers-and-footers' ),
			3 => __( 'We encountered an error activating your snippet, please check the syntax and try again.', 'insert-headers-and-footers' ),
			4 => __( 'Sorry, you are not allowed to change the status of the snippet.', 'insert-headers-and-footers' ),
			5 => __( 'Snippet updated & executed.', 'insert-headers-and-footers' ),
		);
		$message  = absint( $_GET['message'] );
		// phpcs:enable WordPress.Security.NonceVerification

		if ( ! isset( $messages[ $message ] ) ) {
			return;
		}

		if ( 3 === $message && ! empty( $error_details ) ) {
			$error_message = sprintf(
			/* translators: %s: Error message. */
				esc_html__( 'Error message: %s', 'insert-headers-and-footers' ),
				'<code>' . $error_details . '</code>'
			);

			$messages[ $message ] .= ' ' . $error_message;
		}

		if ( $message > 2 && $message < 5 ) {
			$this->set_error_message( $messages[ $message ] );
		} else {
			$this->set_success_message( $messages[ $message ] );
		}

		if ( in_array( $message, array( 1, 2 ), true ) ) {
			// The first time the user saves a snippet, if they did not activate it, highlight that and save a user meta to avoid the message from being displayed again.
			add_action( 'wpcode_admin_notices', array( $this, 'maybe_show_saved_without_activation_notice' ), 5 );
		}

	}

	/**
	 * The first time a snippet is saved without being activated, show a notice to the user.
	 *
	 * @return void
	 */
	public function maybe_show_saved_without_activation_notice() {
		if ( ! isset( $this->snippet ) ) {
			return;
		}
		$snippet = $this->snippet;
		if ( ! $snippet->is_active() && ! get_user_meta( get_current_user_id(), 'wpcode_snippet_activate_notice_shown', true ) ) {
			update_user_meta( get_current_user_id(), 'wpcode_snippet_activate_notice_shown', true );
			?>
			<div class="notice-warning fade notice is-dismissible">
				<p><?php esc_html_e( 'Don\'t forget to activate your snippet using the toggle next to the "Update" button when you are ready to start using it.', 'insert-headers-and-footers' ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * If we're editing a snippet, change the active submenu like WP does.
	 *
	 * @param null|string $submenu_file The submenu file.
	 *
	 * @return null|string
	 */
	public function change_current_menu( $submenu_file ) {
		if ( ! isset( $this->snippet_id ) ) {
			// Only change this for when editing a snippet.
			return $submenu_file;
		}

		return 'wpcode';
	}

	/**
	 * Change the admin page title when editing a snippet.
	 *
	 * @param string $title The admin page title to be displayed.
	 * @param string $original_title The page title before adding the WP suffix.
	 *
	 * @return string
	 */
	public function change_page_title( $title, $original_title ) {
		if ( isset( $this->snippet ) ) {
			// If the snippet post is loaded (so we're editing) replace the original page title with our edit snippet one.
			// Translators: this changes the edit page title to show the snippet title.
			return str_replace( $original_title, sprintf( __( 'Edit snippet "%s"', 'insert-headers-and-footers' ), $this->snippet->get_title() ), $title );
		}

		return $title;
	}

	/**
	 * The main page content.
	 *
	 * @return void
	 */
	public function output_content() {
		if ( $this->show_library ) {
			$this->show_snippet_library();
		} else {
			$this->show_snippet_editor();
		}
	}

	/**
	 * Show the snippet editor markup.
	 *
	 * @return void
	 */
	public function show_snippet_editor() {
		$this->field_title();
		$this->field_code_editor();
		$this->field_insert_options();
		$this->field_device_type();
		$this->field_conditional_logic();
		$this->field_code_revisions();
		$this->field_basic_info();
		$this->hidden_fields();
		wp_nonce_field( $this->action, $this->nonce_name );
	}

	/**
	 * Static items to be displayed in the library list when adding a new snippet.
	 *
	 * @param string $default_category The default category slug.
	 *
	 * @return array
	 */
	public function get_static_snippet_items( $default_category ) {
		return array(
			array(
				'library_id'    => 0,
				'title'         => esc_html__( 'Add Your Custom Code (New Snippet)', 'insert-headers-and-footers' ),
				'note'          => esc_html__( 'Choose this blank snippet to start from scratch and paste any custom code or simply write your own.', 'insert-headers-and-footers' ),
				'categories'    => array(
					$default_category,
				),
				'url'           => add_query_arg(
					array(
						'page'   => 'wpcode-snippet-manager',
						'custom' => true,
					),
					$this->admin_url( 'admin.php' )
				),
				'extra_classes' => array( 'wpcode-custom-snippet' ),
				'button_text'   => '+ ' . esc_html__( 'Add Custom Snippet', 'insert-headers-and-footers' ),
			),
			array(
				'library_id'    => 0,
				'title'         => esc_html__( 'Generate snippet using AI', 'insert-headers-and-footers' ),
				'note'          => esc_html__( 'Generate a new snippet specific to your needs leveraging the power of WPCode\'s AI integration.', 'insert-headers-and-footers' ),
				'categories'    => array(
					$default_category,
				),
				'pill_text'     => 'pro',
				'pill_class'    => 'light',
				'url'           => '#',
				'extra_classes' => array( 'wpcode-library-item-ai', 'wpcode-library-item-ai-not-available' ),
				'button_text'   => '+ ' . esc_html__( 'Generate Snippet', 'insert-headers-and-footers' ),
			),
		);
	}

	/**
	 * Show the snippet library markup.
	 *
	 * @return void
	 */
	public function show_snippet_library() {
		$library_data     = wpcode()->library->get_data();
		$categories       = $library_data['categories'];
		$snippets         = $library_data['snippets'];
		$default_category = isset( $categories[0]['slug'] ) ? $categories[0]['slug'] : '';

		$snippets = array_merge(
			$this->get_static_snippet_items( $default_category ),
			$snippets
		);

		?>
		<div class="wpcode-add-snippet-description">
			<?php
			$custom_url = add_query_arg(
				array(
					'page'   => 'wpcode-snippet-manager',
					'custom' => 1,
				),
				admin_url( 'admin.php' )
			);
			printf(
			// Translators: The placeholders add links to create a new custom snippet or the suggest-a-snippet form.
				esc_html__( 'To speed up the process you can select from one of our pre-made library, or you can start with a %1$sblank snippet%2$s and %1$screate your own%2$s. Have a suggestion for new snippet? %3$sWe’d love to hear it!%4$s', 'insert-headers-and-footers' ),
				'<a href="' . esc_url( $custom_url ) . '">',
				'</a>',
				'<a href="' . esc_url( wpcode_utm_url( 'https://wpcode.com/suggestions/?wpf78_8=Snippet Request', 'add-new', 'suggestions' ) ) . '" target="_blank">',
				'</a>'
			);
			?>
		</div>
		<?php
		$this->get_library_markup( $categories, $snippets );
		$this->library_preview_modal_content();
		$this->library_connect_banner_template();
	}

	/**
	 * Output the snippet title field.
	 *
	 * @return void
	 */
	public function field_title() {
		$value = isset( $this->snippet ) ? $this->snippet->get_title() : '';
		?>
		<div class="wpcode-input-title">
			<input type="text" class="widefat wpcode-input-text" value="<?php echo esc_attr( $value ); ?>" name="wpcode_snippet_title" placeholder="<?php esc_attr_e( 'Add title for snippet', 'insert-headers-and-footers' ); ?>"/>
		</div>
		<?php
	}

	/**
	 * The main code editor field.
	 *
	 * @return void
	 */
	public function field_code_editor() {
		$value = isset( $this->snippet ) ? $this->snippet->get_code() : '';
		?>
		<div class="wpcode-code-textarea" data-code-type="<?php echo esc_attr( $this->code_type ); ?>">
			<div class="wpcode-flex">
				<div class="wpcode-column">
					<h2>
						<label for="wpcode_snippet_code"><?php esc_html_e( 'Code Preview', 'insert-headers-and-footers' ); ?></label>
					</h2>
				</div>
				<div class="wpcode-column">
					<?php $this->ai_generate_button(); ?>
					<?php wpcode()->smart_tags->smart_tags_picker( 'wpcode_snippet_code' ); ?>
					<?php $this->field_code_type(); ?>
				</div>
			</div>
			<textarea name="wpcode_snippet_code" id="wpcode_snippet_code" class="widefat" rows="8"><?php echo esc_textarea( $value ); ?></textarea>
			<?php
			wp_editor(
				$value,
				'wpcode_snippet_text',
				array(
					'wpautop'        => false,
					'default_editor' => 'tinymce',
					'tinymce'        => array(
						'height' => 330,
					),
				)
			);
			?>
		</div>
		<div class="wpcode-resize-handle"></div>
		<?php
	}

	/**
	 * Snippet type field.
	 *
	 * @return void
	 */
	public function field_code_type() {
		$snippet_types = wpcode()->execute->get_options();
		?>
		<div class="wpcode-input-select">
			<label for="wpcode_snippet_type"><?php esc_html_e( 'Code Type', 'insert-headers-and-footers' ); ?></label>
			<select name="wpcode_snippet_type" id="wpcode_snippet_type">
				<?php
				foreach ( $snippet_types as $key => $label ) {
					$class = wpcode()->execute->is_type_pro( $key ) ? 'wpcode-pro' : '';
					?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $this->code_type, $key ); ?> class="<?php echo esc_attr( $class ); ?>">
						<?php echo esc_html( $label ); ?>
					</option>
				<?php } ?>
			</select>
		</div>
		<?php
	}

	/**
	 * The insert options - using a metabox-style layout to output the options.
	 *
	 * @return void
	 */
	public function field_insert_options() {
		$title           = __( 'Insertion', 'insert-headers-and-footers' );
		$insert_toggle   = $this->get_input_insert_toggle();
		$shortcode_field = $this->get_input_shortcode();
		// Build the field markup here.
		ob_start();
		?>
		<p><?php esc_html_e( 'Choose "Auto Insert" if you want the snippet to be automatically executed in one of the locations available. In "Shortcode" mode, the snippet will only be executed where the shortcode is inserted.', 'insert-headers-and-footers' ); ?></p>
		<div class="wpcode-separator"></div>
		<div class="wpcode-metabox-form">
			<?php $this->metabox_row( __( 'Insert Method', 'insert-headers-and-footers' ), $insert_toggle ); ?>
			<div class="wpcode-auto-insert-form-fields" data-show-if-id="#wpcode_auto_insert" data-show-if-value="1">
				<?php $this->metabox_row( __( 'Location', 'insert-headers-and-footers' ), $this->get_selected_auto_insert_location() ); ?>
			</div>
			<div class="wpcode-shortcode-form-fields" data-show-if-id="#wpcode_auto_insert" data-show-if-value="0">
				<?php
				$this->metabox_row( __( 'Shortcode', 'insert-headers-and-footers' ), $shortcode_field, 'wpcode_shortcode' );
				$this->get_input_row_custom_shortcode();
				$this->get_input_row_shortcode_attributes();
				?>
			</div>
		</div>
		<?php $this->get_input_auto_insert_options(); ?>
		<div class="wpcode-metabox-form">
			<?php $this->get_input_row_as_file(); ?>
			<?php $this->get_input_row_schedule(); ?>
		</div>
		<?php
		$content = ob_get_clean();

		$this->metabox(
			$title,
			$content,
			__( 'Your snippet can be either automatically executed or only used as a shortcode. When using the "Auto Insert" option you can choose the location where your snippet will be placed automatically.', 'insert-headers-and-footers' )
		);
	}

	/**
	 * Get all the descriptions for the insert number input with conditional rules.
	 *
	 * @return string
	 */
	public function get_insert_number_descriptions() {
		$descriptions = array(
			'before_paragraph'    => __( 'before paragraph number', 'insert-headers-and-footers' ),
			'after_paragraph'     => __( 'after paragraph number', 'insert-headers-and-footers' ),
			'archive_before_post' => __( 'before post number', 'insert-headers-and-footers' ),
			'archive_after_post'  => __( 'after post number', 'insert-headers-and-footers' ),
			'after_words'         => __( 'minimum number of words', 'insert-headers-and-footers' ),
			'every_words'         => __( 'number of words', 'insert-headers-and-footers' ),
		);
		$markup       = '';
		foreach ( $descriptions as $value => $description ) {
			$markup .= sprintf( '<div class="wpcode-location-extra-input-description" data-show-if-id="[name=\'wpcode_auto_insert_location\']" data-show-if-value="%1$s" style="display:none;">%2$s</div>', $value, esc_html( $description ) );
		}

		return $markup;
	}

	/**
	 * Get the input insert toggle markup.
	 *
	 * @return string
	 */
	public function get_input_insert_toggle() {
		ob_start();
		?>
		<div class="wpcode-button-toggle">
			<button class="wpcode-button wpcode-button-large wpcode-button-secondary <?php echo esc_attr( $this->get_active_toggle_class( 1 ) ); ?>" type="button" value="1">
				<?php wpcode_icon( 'auto', 18, 23 ); ?>
				<span><?php esc_html_e( 'Auto&nbsp;Insert', 'insert-headers-and-footers' ); ?></span>
			</button>
			<button class="wpcode-button wpcode-button-large wpcode-button-secondary <?php echo esc_attr( $this->get_active_toggle_class( 0 ) ); ?>" type="button" value="0">
				<?php wpcode_icon( 'shortcode', 24, 17 ); ?>
				<span><?php esc_html_e( 'Shortcode', 'insert-headers-and-footers' ); ?></span>
			</button>
			<input type="hidden" name="wpcode_auto_insert" class="wpcode-button-toggle-input" id="wpcode_auto_insert" value="<?php echo absint( $this->get_auto_insert_value() ); ?>"/>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get the active toggle class based on the auto-insert value.
	 *
	 * @param string|int $value The value of the button.
	 *
	 * @return string
	 */
	private function get_active_toggle_class( $value ) {
		$current_value = $this->get_auto_insert_value();
		if ( absint( $value ) !== $current_value ) {
			return 'wpcode-button-secondary-inactive';
		}

		return '';
	}

	/**
	 * Get the auto-insert value consistently.
	 *
	 * @return int
	 */
	private function get_auto_insert_value() {
		return isset( $this->snippet ) ? $this->snippet->get_auto_insert() : 1;
	}

	/**
	 * Renders the dropdown with the auto-insert options.
	 * This uses the auto-insert class that loads all the available types.
	 * Each type has some specific options.
	 *
	 * @return void
	 * @see WPCode_Auto_Insert
	 */
	public function get_input_auto_insert_options() {

		$code_type        = $this->code_type;
		$current_location = $this->get_current_snippet_location();

		$locations_by_category = wpcode()->auto_insert->get_type_categories();
		// Let's find the active category from the selected location.
		$active_category = 'global';
		foreach ( $locations_by_category as $category_key => $category_data ) {
			/**
			 * @var WPCode_Auto_Insert_Type $type
			 */
			foreach ( $category_data['types'] as $type ) {
				$locations = $type->get_locations();
				if ( array_key_exists( $current_location, $locations ) ) {
					$active_category = $category_key;
					break 2;
				}
			}
		}

		?>
		<div class="wpcode-items-metabox wpcode-items-metabox-inside" id="wpcode_auto_insert_location">
			<?php
			$this->get_items_list_sidebar(
				wpcode()->auto_insert->get_type_categories_for_sidebar(),
				'',
				__( 'Search locations', 'insert-headers-and-footers' ),
				$active_category
			);
			?>
			<div class="wpcode-items-list">
				<ul class="wpcode-items-list-category">
					<?php
					$index          = 0;
					$selected_label = __( 'Selected', 'insert-headers-and-footers' );
					foreach ( $locations_by_category as $category_key => $category_data ) {
						$style = '';
						if ( $category_key !== $active_category ) {
							$style = 'display:none;';
						}
						foreach ( $category_data['types'] as $type ) {
							$locations  = $type->get_locations();
							$label_pill = '';
							if ( ! empty( $type->label_pill ) ) {
								$label_pill = $type->label_pill;
							}
							?>
							<li class="wpcode-list-item wpcode-list-item-separator" data-index="<?php echo absint( $index ); ?>" data-categories='<?php echo wp_json_encode( array( $category_key ) ); ?>' style="<?php echo esc_attr( $style ); ?>" data-code-type="<?php echo esc_attr( $type->code_type ); ?>">
								<?php echo esc_html( $type->get_label() ); ?>
								<?php if ( ! empty( $label_pill ) ) : ?>
									<span class="wpcode-list-item-pill wpcode-list-item-pill-light"><?php echo esc_html( $label_pill ); ?></span>
								<?php endif; ?>
							</li>
							<?php
							$index ++;

							foreach ( $locations as $location_slug => $location ) {
								$description    = '';
								$style_class    = 'wpcode-list-item wpcode-list-item-location';
								$label          = $location;
								$extra_data     = '';
								$input_disabled = false;
								$tabindex       = 'tabindex="0"';
								if ( isset( $location['label'] ) ) {
									$label       = $location['label'];
									$description = $location['description'];
								}
								if ( 'all' !== $type->code_type && $type->code_type !== $code_type ) {
									$style_class .= ' wpcode-list-item-disabled';
									$tabindex    = '';

									$input_disabled = true;
								}
								if ( ! empty( $type->upgrade_title ) ) {
									$extra_data = ' data-upgrade-title="' . esc_attr( $type->upgrade_title ) . '"';
								}
								if ( ! empty( $type->upgrade_text ) ) {
									$extra_data .= ' data-upgrade-text="' . esc_attr( $type->upgrade_text ) . '"';
								}
								if ( ! empty( $type->upgrade_link ) ) {
									$extra_data .= ' data-upgrade-link="' . esc_attr( $type->upgrade_link ) . '"';
								}
								if ( ! empty( $type->upgrade_button ) ) {
									$extra_data .= ' data-upgrade-button="' . esc_attr( $type->upgrade_button ) . '"';
								}
								if ( $location_slug === $current_location ) {
									$style_class .= ' wpcode-list-item-selected';
								}
								?>
								<li class="<?php echo esc_attr( $style_class ); ?>" data-index="<?php echo absint( $index ); ?>" data-id="<?php echo esc_attr( $location_slug ); ?>" data-categories='<?php echo wp_json_encode( array( $category_key ) ); ?>' data-code-type="<?php echo esc_attr( $type->code_type ); ?>" style="<?php echo esc_attr( $style ); ?>" <?php echo $tabindex; ?>>
									<label <?php echo $extra_data; // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
										<span class="wpcode-list-item-title" title="<?php echo esc_attr( $label ); ?>" data-selected-label="<?php echo esc_attr( $selected_label ); ?>">
											<span class="wpcode-keywords">
												<?php
												// Output the type label to improve search results without displaying the text to the user.
												echo esc_html( $type->label );
												?>
											</span>
											<?php echo esc_html( $label ); ?>
										</span>
										<span class="wpcode-list-item-actions">
											<span class="wpcode-list-item-description">
											<?php echo esc_html( $description ); ?>
											</span>
										</span>
										<input type="radio" name="wpcode_auto_insert_location" value="<?php echo esc_attr( $location_slug ); ?>" <?php checked( $location_slug, $current_location ); ?> <?php disabled( $input_disabled ); ?> />
									</label>
								</li>
								<?php
								$index ++;
							}
						}
					}
					?>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Get the selected auto insert location.
	 *
	 * @return string
	 */
	public function get_selected_auto_insert_location() {
		$current_location = $this->get_current_snippet_location();

		if ( empty( $current_location ) ) {
			$current_location = 'site_wide_header';
		}

		$location_extra = isset( $this->snippet ) ? $this->snippet->get_location_extra() : '';

		// Show a faux select box with the current location.
		$location_label = wpcode()->auto_insert->get_location_label( $current_location );

		$markup = '<input type="hidden" name="wpcode_auto_insert_location_extra" id="wpcode_auto_insert_location_extra" value="' . esc_attr( $location_extra ) . '" />';

		$markup .= '<div class="wpcode-faux-select" id="wpcode-selected-location-display" tabindex="0"><span>' . esc_html( $location_label ) . '</span></div>';
		$markup .= '<div class="wpcode-extra-location-fields">';
		$markup .= '<div class="wpcode-extra-location-input" data-show-if-id="[name=\'wpcode_auto_insert_location\']" data-show-if-value="' . implode( ',', wpcode_get_auto_insert_locations_with_number() ) . '">';
		$markup .= $this->get_insert_number_descriptions();
		$markup .= $this->get_input_number(
			'wpcode_auto_insert_number',
			$this->get_auto_insert_number_value(),
			'',
			1
		);
		$markup .= '</div>';

		/**
		 * Filter the markup for the location display inputs.
		 * This is used to add the number input for auto insert locations.
		 *
		 * @param string               $markup The markup to display.
		 * @param WPCode_Snippet|false $snippet The snippet object.
		 * @param WPCode_Admin_Page    $this The admin page object.
		 */
		$markup = apply_filters( 'wpcode_location_display_inputs', $markup, isset( $this->snippet ) ? $this->snippet : false, $this );

		$markup .= '</div>';// End wpcode-extra-location-fields.

		return $markup;

	}

	/**
	 * Grab the current snippet location.
	 *
	 * @return mixed|string
	 */
	public function get_current_snippet_location() {
		$current_location = 'site_wide_header';
		if ( ! isset( $this->snippet_id ) ) {
			return $current_location;
		}

		return $this->snippet->get_location();
	}

	/**
	 * Get the shortcode field.
	 *
	 * @return string
	 */
	public function get_input_shortcode() {
		$shortcode = __( 'Please save the snippet first', 'insert-headers-and-footers' );
		if ( isset( $this->snippet_id ) ) {
			$shortcode  = sprintf( '[wpcode id="%d"]', $this->snippet_id );
			$attributes = $this->snippet->get_shortcode_attributes();
			if ( ! empty( $attributes ) ) {
				$attributes_string = implode( '="" ', $attributes );
				$shortcode         = str_replace( ']', ' ' . $attributes_string . '=""]', $shortcode );
			}
		}
		$input  = sprintf(
			'<input type="text" value=\'%1$s\' id="wpcode-shortcode" class="wpcode-input-text" readonly />',
			$shortcode
		);
		$button = wpcode_get_copy_target_button( 'wpcode-shortcode' );

		return sprintf( '<div class="wpcode-input-with-button">%1$s %2$s</div>', $input, $button );
	}

	/**
	 * Generic input number function.
	 *
	 * @param string     $id The id of the input field.
	 * @param string|int $value The value of the input.
	 * @param string     $description The description to display under the field.
	 * @param int        $min The minimum value.
	 *
	 * @return string
	 */
	public function get_input_number( $id, $value = '', $description = '', $min = 0 ) {
		$input = '<input type="number" class="wpcode-input-number" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '" min="' . absint( $min ) . '" />';
		if ( ! empty( $description ) ) {
			$input .= '<p>' . $description . '</p>';
		}

		return $input;
	}

	/**
	 * Get a simple textarea field.
	 *
	 * @param string $id The id of the input field.
	 * @param string $value The value of the input.
	 * @param string $description The description to display under the field.
	 *
	 * @return string
	 */
	public function get_input_textarea( $id, $value = '', $description = '' ) {
		$input = '<textarea class="wpcode-input-textarea" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" rows="3">' . esc_html( $value ) . '</textarea>';
		if ( ! empty( $description ) ) {
			$input .= '<p>' . $description . '</p>';
		}

		return $input;
	}

	/**
	 * Get the auto-insert value consistently.
	 *
	 * @return int
	 */
	private function get_auto_insert_number_value() {
		return isset( $this->snippet ) ? $this->snippet->get_auto_insert_number() : 1;
	}

	/**
	 * Markup for the basic info metabox.
	 *
	 * @return void
	 */
	public function field_basic_info() {
		$priority = isset( $this->snippet ) ? $this->snippet->get_priority() : 10;
		$note     = isset( $this->snippet ) ? $this->snippet->get_note() : '';

		ob_start();
		$this->metabox_row( __( 'Tag', 'insert-headers-and-footers' ), $this->get_input_tag_picker() );
		$this->metabox_row( __( 'Priority', 'insert-headers-and-footers' ), $this->get_input_number( 'wpcode_priority', $priority ), 'wpcode_priority' );
		$this->metabox_row( __( 'Note', 'insert-headers-and-footers' ), $this->get_input_textarea( 'wpcode_note', $note ), 'wpcode_note' );

		if ( isset( $this->snippet ) && $this->snippet->is_generated() ) {
			$this->metabox_row( __( 'Generator', 'insert-headers-and-footers' ), $this->get_input_generator() );
		}

		$this->metabox(
			__( 'Basic info', 'insert-headers-and-footers' ),
			ob_get_clean(),
			__( 'Tags: Use tags to make it easier to group similar snippets together. <br />Priority: A lower priority will result in the snippet being executed before others with a higher priority. <br />Note: Add a private note related to this snippet.', 'insert-headers-and-footers' )
		);
	}

	/**
	 * The conditional logic field.
	 *
	 * @return void
	 */
	public function field_conditional_logic() {
		$enable_logic = isset( $this->snippet ) && $this->snippet->conditional_rules_enabled();

		$content = '<p>' . __( 'Using conditional logic you can limit the pages where you want the snippet to be auto-inserted.', 'insert-headers-and-footers' ) . '</p>';

		$content .= '<div class="wpcode-separator"></div>';
		ob_start();
		$this->metabox_row( __( 'Enable Logic', 'insert-headers-and-footers' ), $this->get_checkbox_toggle( $enable_logic, 'wpcode_conditional_logic_enable' ), 'wpcode_conditional_logic_enable' );
		$this->metabox_row( __( 'Conditions', 'insert-headers-and-footers' ), $this->get_conditional_logic_input(), 'wpcode_contional_logic_conditions', '#wpcode_conditional_logic_enable', '1' );

		$content .= ob_get_clean();

		$this->metabox(
			__( 'Smart Conditional Logic', 'insert-headers-and-footers' ),
			$content,
			__( 'Enable logic to add rules and limit where your snippets are inserted automatically. Use multiple groups for different sets of rules.', 'insert-headers-and-footers' )
		);
	}

	/**
	 * Get the tag picker markup.
	 *
	 * @return string
	 */
	public function get_input_tag_picker() {
		$tags        = isset( $this->snippet ) ? $this->snippet->get_tags() : array();
		$tags_string = isset( $this->snippet ) ? implode( ',', $this->snippet->get_tags() ) : '';
		$markup      = '<select multiple="multiple" class="wpcode-tags-picker" data-target="#wpcode-tags">';
		foreach ( $tags as $tag ) {
			$markup .= '<option value="' . esc_attr( $tag ) . '" selected="selected">' . esc_html( $tag ) . '</option>';
		}
		$markup .= '</select>';
		$markup .= '<input type="hidden" name="wpcode_tags" id="wpcode-tags" value="' . esc_attr( $tags_string ) . '" />';

		return $markup;
	}

	/**
	 * Get the link to the generator page for the current snippet.
	 *
	 * @return string
	 */
	public function get_input_generator() {
		$generator = $this->snippet->get_generator();

		return sprintf(
			'<a href="%1$s" class="wpcode-button wpcode-button-secondary">%2$s</a>',
			add_query_arg(
				array(
					'generator' => $generator,
					'page'      => 'wpcode-generator',
					'snippet'   => $this->snippet->get_id(),
				),
				admin_url( 'admin.php' )
			),
			esc_html__( 'Update Generated Snippet', 'insert-headers-and-footers' )
		);
	}

	/**
	 * The hidden fields needed to identify the form submission.
	 *
	 * @return void
	 */
	public function hidden_fields() {
		if ( ! isset( $this->snippet_id ) ) {
			return;
		}
		?>
		<input type="hidden" name="id" value="<?php echo esc_attr( $this->snippet_id ); ?>"/>
		<?php
	}

	/**
	 * Output of the page wrapped in a form.
	 *
	 * @return void
	 */
	public function output() {
		if ( $this->show_library ) {
			// Don't wrap with form when showing library.
			parent::output();

			return;
		}
		?>
		<form action="<?php echo esc_url( $this->get_page_action_url() ); ?>" method="post" id="wpcode-snippet-manager-form" autocomplete="off">
			<?php parent::output(); ?>
		</form>
		<?php
	}

	/**
	 * The bottom of the header part.
	 *
	 * @return void
	 */
	public function output_header_bottom() {
		?>
		<div class="wpcode-column">
			<h1><?php echo esc_html( $this->header_title ); ?></h1>
		</div>
		<?php
		// If we're displaying the libray screen, return early and hide right-side buttons.
		if ( $this->show_library ) {
			return;
		}
		?>
		<div class="wpcode-column">
			<?php $this->header_buttons(); ?>
		</div>
		<?php
	}

	/**
	 * Add header buttons on the right side of the header.
	 *
	 * @return void
	 */
	public function header_buttons() {
		$active = isset( $this->snippet ) && $this->snippet->is_active();
		$this->save_to_library_button();
		?>
		<span class="wpcode-status-toggle" data-show-if-id="[name='wpcode_auto_insert_location']" data-hide-if-value="on_demand">
		<label class="wpcode-status-text" for="wpcode_active">
			<span class="screen-reader-text">
				<?php esc_html_e( 'Snippet Status:', 'insert-headers-and-footers' ); ?>
			</span>
			<span data-show-if-id="#wpcode_active" data-show-if-value="1" style="display: none">
				<?php esc_html_e( 'Active', 'insert-headers-and-footers' ); ?>
			</span>
			<span data-show-if-id="#wpcode_active" data-show-if-value="0" style="display:none;">
				<?php esc_html_e( 'Inactive', 'insert-headers-and-footers' ); ?>
			</span>
		</label>
		<?php echo $this->get_checkbox_toggle( $active, 'wpcode_active' ); ?>
		</span>
		<button
				class="wpcode-button wpcode-button-secondary wpcode-button-execute-now"
				id="wpcode_execute_now"
				data-show-if-id="[name='wpcode_auto_insert_location']"
				data-show-if-value="on_demand"
				type="submit"
				name="execute_now"
				value="execute_now" style="display:none;">
			<?php esc_html_e( 'Execute Snippet Now', 'wpcode-premium' ); ?>
		</button>
		<?php
		$this->update_button();
	}

	/**
	 * The Update snippet button.
	 *
	 * @return void
	 */
	public function update_button() {
		?>
		<button class="wpcode-button" type="submit" value="publish" name="button"><?php echo esc_html( $this->publish_button_text ); ?></button>
		<?php
	}

	/**
	 * Markup for the save to library button.
	 *
	 * @return void
	 */
	public function save_to_library_button() {
		?>
		<button
				class="wpcode-button wpcode-button-text wpcode-button-save-to-library"
				id="wpcode_save_to_library"
				type="button">
			<?php
			wpcode_icon( 'cloud', 16, 12 );
			esc_html_e( 'Save to Library', 'insert-headers-and-footers' );
			?>
		</button>
		<?php
	}

	/**
	 * Handle a form submit here.
	 *
	 * @return void
	 */
	public function submit_listener() {
		if ( ! isset( $_REQUEST[ $this->nonce_name ] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST[ $this->nonce_name ] ), $this->action ) ) {
			// Nonce is missing, so we're not even going to try.
			return;
		}
		if ( ! $this->can_edit ) {
			return;
		}

		$code_type    = isset( $_POST['wpcode_snippet_type'] ) ? sanitize_text_field( wp_unslash( $_POST['wpcode_snippet_type'] ) ) : 'html';
		$snippet_code = isset( $_POST['wpcode_snippet_code'] ) ? $_POST['wpcode_snippet_code'] : '';

		if ( WPCode_Snippet_Execute::is_code_not_allowed( $snippet_code ) ) {
			$title = esc_html__( 'Restricted Code Detected', 'insert-headers-and-footers' );
			wp_die(
				'<h2>' . $title . '</h2>' .
				sprintf(
				// Translators: %1$s is the opening link tag, %2$s is the closing link tag.
					esc_html__( 'For your protection, we blocked the addition of certain types of code that could compromise your website\'s security. If you believe this is in error or need assistance, please %1$scontact our support team%2$s.', 'insert-headers-and-footer' ),
					'<a href="' . wpcode_utm_url( 'https://wpcode.com/contact', 'error', 'restricted-code' ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				$title
			);
		}

		if ( 'text' === $code_type ) {
			$snippet_code = wpautop( $snippet_code );
		}

		$tags = array();

		if ( isset( $_POST['wpcode_tags'] ) ) {
			$tags = trim( sanitize_text_field( wp_unslash( $_POST['wpcode_tags'] ) ) );
			if ( ! empty( $tags ) ) {
				$tags = explode( ',', $tags );
			}
		}

		if ( 'php' === $code_type ) {
			$snippet_code = preg_replace( '|^\s*<\?(php)?|', '', $snippet_code );
			// Let's also replace the closing php tag if any.
			$snippet_code = preg_replace( '|\?>\s*$|', '', $snippet_code );
		}

		if ( 'js' === $code_type && apply_filters( 'wpcode_strip_script_tags_for_js', true ) ) {
			$snippet_code = preg_replace( '|^\s*<script[^>]*>|', '', $snippet_code );
			$snippet_code = preg_replace( '|</(script)>\s*$|', '', $snippet_code );
		}

		$rules = isset( $_POST['wpcode_cl_rules'] ) ? json_decode( sanitize_textarea_field( wp_unslash( $_POST['wpcode_cl_rules'] ) ), true ) : array();

		if ( isset( $_POST['wpcode_shortcode_attributes'] ) ) {
			$attributes = array_map( 'sanitize_key', wp_unslash( $_POST['wpcode_shortcode_attributes'] ) );
		} else {
			$attributes = array();
		}
		$active      = isset( $_REQUEST['wpcode_active'] );
		$location    = isset( $_POST['wpcode_auto_insert_location'] ) ? sanitize_text_field( wp_unslash( $_POST['wpcode_auto_insert_location'] ) ) : '';
		$execute_now = isset( $_POST['execute_now'] ) && 'execute_now' === $_POST['execute_now'] && 'on_demand' === $location;

		if ( $execute_now ) {
			// If we are executing now we should never save the snippet as active.
			$active = false;
		}

		$snippet = wpcode_get_snippet(
			array(
				'id'                   => empty( $_REQUEST['id'] ) ? 0 : absint( $_REQUEST['id'] ),
				'title'                => isset( $_POST['wpcode_snippet_title'] ) ? sanitize_text_field( wp_unslash( $_POST['wpcode_snippet_title'] ) ) : '',
				'code'                 => $snippet_code,
				'active'               => $active,
				'code_type'            => $code_type,
				'location'             => $location,
				'insert_number'        => isset( $_POST['wpcode_auto_insert_number'] ) ? absint( $_POST['wpcode_auto_insert_number'] ) : 0,
				'auto_insert'          => isset( $_POST['wpcode_auto_insert'] ) ? absint( $_POST['wpcode_auto_insert'] ) : 0,
				'tags'                 => $tags,
				'use_rules'            => isset( $_POST['wpcode_conditional_logic_enable'] ),
				'rules'                => $rules,
				'priority'             => isset( $_POST['wpcode_priority'] ) ? intval( $_POST['wpcode_priority'] ) : 10,
				'note'                 => isset( $_POST['wpcode_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wpcode_note'] ) ) : '',
				'location_extra'       => isset( $_POST['wpcode_auto_insert_location_extra'] ) ? sanitize_text_field( wp_unslash( $_POST['wpcode_auto_insert_location_extra'] ) ) : '',
				'shortcode_attributes' => $attributes,
			)
		);

		if ( empty( $snippet->title ) ) {
			$snippet->title = $snippet->get_untitled_title();
		}

		$message_number = 1;
		$active_wanted  = $snippet->active;

		if ( 0 === $snippet->id ) {
			// If it's a new snippet display a different message.
			$message_number = 2;
		}

		$this->add_extra_snippet_data( $snippet );

		$id = $snippet->save();

		if ( $active_wanted !== $snippet->is_active() ) {
			// If the snippet failed to change status display an error message.
			$message_number = 3;
			// If the current user is not allowed to change snippet status, display a different message.
			if ( ! current_user_can( 'wpcode_activate_snippets' ) ) {
				$message_number = 4;
			}
		}

		// Now that we saved the data, let's execute the snippet if needed.
		if ( $execute_now ) {
			wpcode()->execute->doing_activation(); // Mark this to unslash the code.
			$snippet->execute( apply_filters( 'wpcode_on_demand_ignore_conditional_logic', false ) );
			$message_number = 5;
		}

		if ( $id ) {
			wp_safe_redirect( $this->get_after_save_redirect_url( $id, $message_number ) );
			exit;
		}
	}

	/**
	 * Get the URL to redirect to after a snippet is saved.
	 *
	 * @param int $snippet_id The snippet id that was just saved.
	 * @param int $message_number The message number to display.
	 *
	 * @return string
	 */
	protected function get_after_save_redirect_url( $snippet_id, $message_number = 1 ) {
		return add_query_arg(
			array(
				'snippet_id' => $snippet_id,
				'message'    => $message_number,
				'error'      => wpcode()->error->get_last_error_message(),
			),
			$this->get_page_action_url()
		);
	}

	/**
	 * Load page-specific scripts.
	 *
	 * @return void
	 */
	public function page_scripts() {
		if ( $this->show_library ) {
			return;
		}

		$editor = new WPCode_Code_Editor( $this->code_type );
		$editor->load_hint_scripts();
		$editor->set_setting( 'autoCloseTags', true );
		$editor->set_setting( 'matchTags', array( 'bothTags' => true ) );

		$editor->register_editor( 'wpcode_snippet_code' );
		$editor->init_editor();
	}

	/**
	 * Get the snippet type based on the context.
	 *
	 * @return void
	 */
	public function set_code_type() {
		if ( isset( $this->snippet ) ) {
			$this->code_type = $this->snippet->get_code_type();
		} else {
			$this->code_type = apply_filters( 'wpcode_default_code_type', $this->code_type );
			$snippet_types   = wpcode()->execute->get_options();
			// If the selected code type is not in the available types, change the selected type to the first available type.
			if ( ! isset( $snippet_types[ $this->code_type ] ) ) {
				$this->code_type = key( $snippet_types );
			}
		}
	}

	/**
	 * Get the conditional logic options input markup.
	 *
	 * @return string
	 */
	public function get_conditional_logic_input() {

		$conditional_rules = isset( $this->snippet ) ? wp_json_encode( $this->snippet->get_conditional_rules() ) : '';

		$markup = $this->get_conditional_select_show_hide();

		$markup .= sprintf( '<div id="wpcode-conditions-holder">%s</div>', $this->build_conditional_rules_form() );
		$markup .= sprintf( '<button type="button" class="wpcode-button" id="wpcode-cl-add-group">%s</button>', __( '+ Add new group', 'insert-headers-and-footers' ) );
		$markup .= sprintf( '<script type="text/template" id="wpcode-conditions-group-markup">%s</script>', $this->get_conditions_group_markup() );
		$markup .= sprintf( '<script type="text/template" id="wpcode-conditions-group-row-markup">%s</script>', $this->get_conditions_group_row_markup() );
		$markup .= sprintf( '<input type="hidden" name="wpcode_cl_rules" id="wpcode-cl-rules" value="%s" />', esc_attr( $conditional_rules ) );

		return $markup;
	}

	/**
	 * Markup for the show/hide select input.
	 *
	 * @return string
	 */
	public function get_conditional_select_show_hide() {
		$rules    = isset( $this->snippet ) ? $this->snippet->get_conditional_rules() : array();
		$selected = empty( $rules ) ? 'show' : $rules['show'];
		$options  = array(
			'show' => __( 'Show', 'insert-headers-and-footers' ),
			'hide' => __( 'Hide', 'insert-headers-and-footers' ),
		);

		$markup = '<div class="wpcode-inline-select">';

		$markup .= '<select id="wpcode-cl-show-hide">';
		foreach ( $options as $value => $label ) {
			$markup .= sprintf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $value ),
				selected( $value, $selected, false ),
				esc_html( $label )
			);
		}
		$markup .= '</select>';
		$markup .= '<label for="wpcode-cl-show-hide">';
		$markup .= sprintf( '<span>%s</span>', __( 'This code snippet if', 'insert-headers-and-footers' ) );
		$markup .= '</label>';
		$markup .= '</div>';

		return $markup;
	}

	/**
	 * Build back the form markup from the stored conditions.
	 *
	 * @return string|void
	 */
	public function build_conditional_rules_form() {
		if ( ! isset( $this->snippet ) ) {
			return;
		}
		$options = wpcode()->conditional_logic->get_all_admin_options();
		$rules   = $this->snippet->get_conditional_rules();
		if ( empty( $rules ) || empty( $rules['groups'] ) ) {
			return;
		}
		$form_groups = array();
		foreach ( $rules['groups'] as $group_rows ) {
			$rows = array();
			foreach ( $group_rows as $row ) {
				$type_options = $options[ $row['type'] ];
				$value_option = $type_options['options'][ $row['option'] ];

				$rows[] = $this->get_conditions_group_row_markup( $row['option'], $row['relation'], $this->get_input_markup_by_type( $value_option, $row['value'] ) );
			}

			$form_groups[] = $this->get_conditions_group_markup( implode( '', $rows ) );
		}

		return implode( $form_groups );

	}

	/**
	 * Process the type options and return the input markup.
	 *
	 * @param array  $data The data with the settings/options.
	 * @param string $value The value currently selected.
	 *
	 * @return string
	 */
	private function get_input_markup_by_type( $data, $value ) {
		$markup = '';
		switch ( $data['type'] ) {
			case 'select':
				$multiple = isset( $data['multiple'] ) && $data['multiple'] ? 'multiple' : '';
				$class    = isset( $data['multiple'] ) && $data['multiple'] ? 'wpcode-select2' : '';
				$markup   = '<select class=' . esc_attr( $class ) . '  ' . $multiple . '>';
				if ( empty( $value ) ) {
					$value = false;
				}
				$selected = ! is_array( $value ) ? array( $value ) : $value;
				foreach ( $data['options'] as $option ) {
					$markup .= '<option value="' . esc_attr( $option['value'] ) . '" ' . selected( in_array( $option['value'], $selected, true ), true, false ) . ' ' . disabled( isset( $option['disabled'] ) && $option['disabled'], true, false ) . '>' . esc_html( $option['label'] ) . '</option>';
				}
				$markup .= '</select>';
				break;
			case 'text':
				$markup = sprintf( '<input type="text" class="wpcode-input-text" value="%s" />', esc_attr( $value ) );
				break;
			case 'time':
				$markup = sprintf( '<input type="text" class="wpcode-input-text wpcode-input-time" value="%s" />', esc_attr( $value ) );
				break;
			case 'date':
				$value  = ! empty( $value ) ? date_i18n( 'Y-m-d', strtotime( $value ) ) : '';
				$markup = sprintf( '<input type="text" class="wpcode-input-text wpcode-input-date" value="%s" />', esc_attr( $value ) );
				break;
			case 'datetime':
				$value  = ! empty( $value ) ? date_i18n( 'Y-m-d H:i', strtotime( $value ) ) : '';
				$markup = sprintf( '<input type="text" class="wpcode-input-text wpcode-input-datetime" value="%s" />', esc_attr( $value ) );
				break;
			case 'ajax':
				$options  = isset( $data['labels_callback'] ) ? $data['labels_callback']( $value ) : array();
				$multiple = isset( $data['multiple'] ) && $data['multiple'] ? 'multiple' : '';
				$markup   = '<select class="wpcode-select2-ajax" data-action="' . esc_attr( $data['options'] ) . '" ' . $multiple . '>';
				foreach ( $options as $option ) {
					$markup .= '<option value="' . esc_attr( $option['value'] ) . '" ' . selected( true, true, false ) . '>' . esc_html( $option['label'] ) . '</option>';
				}
				$markup .= '</select>';
				break;
		}

		return $markup;
	}

	/**
	 * Build the markup for an empty conditional logic group.
	 *
	 * @param string $rows Optional, already-built rows markup.
	 *
	 * @return string
	 */
	private function get_conditions_group_markup( $rows = '' ) {
		$markup = '<div class="wpcode-cl-group">';

		$markup .= $this->get_conditions_group_or_markup();
		$markup .= '<div class="wpcode-cl-group-rules">' . $rows . '</div>';
		$markup .= sprintf(
			'<button class="wpcode-button wpcode-cl-add-row" type="button" title="%2$s">%1$s</button>',
			_x( 'AND', 'Conditional logic add another "and" rules row.', 'insert-headers-and-footers' ),
			esc_attr__( 'Add another "AND" rules row.', 'insert-headers-and-footers' )
		);
		$markup .= '</div>';

		return $markup;
	}

	/**
	 * Build the markup for a conditional logic row. All parameters are optional and if
	 * left empty it will return the template to be used in JS.
	 *
	 * @param string $type The value for the type input.
	 * @param string $relation The value for the relation field.
	 * @param string $value The value selected for this row.
	 *
	 * @return string
	 */
	private function get_conditions_group_row_markup( $type = '', $relation = '', $value = '' ) {
		$options = wpcode()->conditional_logic->get_all_admin_options();

		$markup = '<div class="wpcode-cl-rules-row">';

		$markup .= '<div class="wpcode-cl-rules-row-options">';
		$markup .= '<select class="wpcode-cl-rule-type">';
		foreach ( $options as $opt_group ) {
			$markup .= '<optgroup label="' . esc_attr( $opt_group['label'] ) . '" data-type="' . esc_attr( $opt_group['name'] ) . '">';
			foreach ( $opt_group['options'] as $key => $option ) {
				$data_attrs = '';
				if ( isset( $option['upgrade'] ) && is_array( $option['upgrade'] ) ) {
					if ( isset( $option['upgrade']['title'] ) ) {
						$data_attrs = 'data-upgrade-title= "' . esc_attr( $option['upgrade']['title'] ) . '"';
					}
					if ( isset( $option['upgrade']['text'] ) ) {
						$data_attrs .= ' data-upgrade-text="' . esc_attr( $option['upgrade']['text'] ) . '"';
					}
					if ( isset( $option['upgrade']['link'] ) ) {
						$data_attrs .= ' data-upgrade-link="' . esc_attr( $option['upgrade']['link'] ) . '"';
					}
					if ( isset( $option['upgrade']['button'] ) ) {
						$data_attrs .= ' data-upgrade-button="' . esc_attr( $option['upgrade']['button'] ) . '"';
					}
				}
				$markup .= '<option value="' . esc_attr( $key ) . '" ' . selected( $type, $key, false ) . ' ' . disabled( isset( $option['disabled'] ) && $option['disabled'], true, false ) . $data_attrs . '>' . esc_html( $option['label'] ) . '</option>';
			}
			$markup .= '</optgroup>';
		}
		$markup .= '</select>';
		$markup .= $this->get_conditions_relation_select( $relation );
		$markup .= '<div class="wpcode-cl-rule-value">' . $value . '</div>';// This should be automatically populated based on the selected type.
		$markup .= '</div>'; // rules-row-options.
		$markup .= '<button class="wpcode-button-just-icon wpcode-cl-remove-row" type="button" title="' . esc_attr__( 'Remove Row', 'insert-headers-and-footers' ) . '">' . get_wpcode_icon( 'remove' ) . '</button>'; // rules-row-options.
		$markup .= '</div>'; // rules-row.

		return $markup;
	}

	/**
	 * Get the markup for the relation field.
	 *
	 * @param string $relation Optional selected relation.
	 *
	 * @return string
	 */
	private function get_conditions_relation_select( $relation = '' ) {
		$options = wpcode_get_conditions_relation_labels();
		$markup  = '<select class="wpcode-cl-rule-relation">';
		foreach ( $options as $value => $label ) {
			$markup .= '<option value="' . esc_attr( $value ) . '" ' . selected( $relation, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}
		$markup .= '</select>';

		return $markup;
	}

	/**
	 * The markup for the "or" displayed between groups.
	 *
	 * @return string
	 */
	private function get_conditions_group_or_markup() {
		$markup = '<div class="wpcode-cl-group-or">';

		$markup .= '<div class="wpcode-cl-group-or-line"></div>';
		$markup .= '<div class="wpcode-cl-group-or-text">' . _x( 'OR', 'Conditional logic "or" another rule', 'insert-headers-and-footers' ) . '</div>';
		$markup .= '</div>';

		return $markup;
	}

	/**
	 * Add conditions to the admin script when on this page.
	 *
	 * @param array $data The localized data used in wp_localize_script.
	 *
	 * @return array
	 * @see wpcode_admin_scripts
	 */
	public function add_conditional_rules_to_script( $data ) {
		if ( ! isset( $data['conditions'] ) ) {
			$data['conditions'] = wpcode()->conditional_logic->get_all_admin_options( true );
		}

		$data['snippet_id']             = isset( $this->snippet_id ) ? $this->snippet_id : 0;
		$data['save_to_library_url']    = wpcode_utm_url( 'https://wpcode.com/lite/', 'snippet-editor', 'save-to-library', 'upgrade-to-pro' );
		$data['save_to_library_title']  = __( 'Save to Library is a Pro Feature', 'insert-headers-and-footers' );
		$data['save_to_library_text']   = __( 'Upgrade to PRO today and save your private snippets to the WPCode library for easy access. You can also share your snippets with other users or load them on other sites.', 'insert-headers-and-footers' );
		$data['save_to_library_button'] = $data['upgrade_button'];
		$data['shortcode_title']        = __( 'Custom Shortcode is a Pro Feature', 'insert-headers-and-footers' );
		$data['shortcode_text']         = __( 'Upgrade today to use a custom shortcode and nerver worry about changing snippet ids again, even when importing your snippets to another site. You\'ll also get access to a private library that makes setting up new sites a lot easier.', 'insert-headers-and-footers' );
		$data['shortcode_url']          = wpcode_utm_url( 'https://wpcode.com/lite/', 'snippet-editor', 'custom-shortcode', 'modal' );
		$data['device_title']           = __( 'Device Type is a Pro Feature', 'insert-headers-and-footers' );
		$data['device_text']            = __( 'Upgrade to PRO today and unlock one-click device targeting for your snippets.', 'insert-headers-and-footers' );
		$data['device_url']             = wpcode_utm_url( 'https://wpcode.com/lite/', 'snippet-editor', 'device-type', 'modal' );
		$data['datetime_title']         = __( 'Scheduling snippets is a Pro Feature', 'insert-headers-and-footers' );
		$data['datetime_text']          = __( 'Upgrade to PRO today and unlock powerful scheduling options to limit when your snippet is active on the site.', 'insert-headers-and-footers' );
		$data['datetime_url']           = wpcode_utm_url( 'https://wpcode.com/lite/', 'snippet-editor', 'schedule', 'modal' );
		$data['blocks_title']           = __( 'Blocks snippets is a Pro Feature', 'insert-headers-and-footers' );
		$data['blocks_text']            = __( 'Upgrade to PRO today and unlock building snippets using the Gutenberg Block Editor. Create templates using blocks and use the full power of WPCode to insert them in your site.', 'insert-headers-and-footers' );
		$data['blocks_url']             = wpcode_utm_url( 'https://wpcode.com/lite/', 'snippet-editor', 'blocks', 'modal' );
		$data['blocks_button']          = $data['save_to_library_button'];
		$data['shortcode_attributes']   = __( 'Shortcode Attributes', 'insert-headers-and-footers' );
		$data['laf_title']              = __( 'Load as file is a Pro Feature', 'insert-headers-and-footers' );
		$data['laf_text']               = __( 'Upgrade to PRO today and unlock loading your CSS and JS snippets as files for better performance and improved compatibility with caching plugins.', 'insert-headers-and-footers' );
		$data['laf_url']                = wpcode_utm_url( 'https://wpcode.com/lite/', 'snippet-editor', 'laf', 'modal' );
		$data['php_cl_location_notice'] = sprintf(
		// Translators: %1$s Opening anchor tag. %2$s Closing anchor tag.
			__( 'For better results using conditional logic with PHP snippets we automatically switched the auto-insert location to "Frontend Conditional Logic" that runs later. If you want to run the snippet earlier please switch back to "Run Everywhere" but note not all conditional logic options will be available. %1$sRead more%2$s', 'insert-headers-and-footers' ),
			'<a href="' . wpcode_utm_url( 'https://wpcode.com/docs/conditional-logic-php-snippets/', 'snippet-editor', 'php-conditional-logic' ) . '" target="_blank">',
			'</a>'
		);

		$error_line = 0;
		if ( isset( $this->snippet ) ) {
			$last_error = $this->snippet->get_last_error();
			if ( ! empty( $last_error['error_line'] ) ) {
				$error_line = $last_error['error_line'];
			}
		}

		$data['cl_labels']          = wpcode_get_conditions_relation_labels();
		$data['cl_labels_custom']   = $this->get_conditional_logic_operators_custom_labels();
		$data['error_line']         = $error_line;
		$data['error_line_message'] = esc_html__( 'The snippet has been recently deactivated due to an error on this line', 'insert-headers-and-footers' );
		$data['is_locked']          = $this->is_locked;
		$data['locked_by']          = $this->locked_by;
		// Translators: The name of the user that is currently editing the snippet is appended at the end.
		$data['edited']           = esc_html__( 'This snippet is currently being edited by ', 'insert-headers-and-footers' );
		$data['ai_title']         = esc_html__( 'AI Snippet Generation is a Pro Feature', 'insert-headers-and-footers' );
		$data['ai_improve_title'] = esc_html__( 'Improving Snippets with AI is a Pro Feature', 'insert-headers-and-footers' );
		$data['ai_text']          = esc_html__( 'Upgrade today to access the WPCode AI code generator, which allows you to create code snippets simply by describing their functionality using the power of AI.', 'insert-headers-and-footers' );
		$data['ai_url']           = wpcode_utm_url( 'https://wpcode.com/lite/', 'snippet-editor', 'ai', 'generate' );
		$data['ai_improve_url']   = wpcode_utm_url( 'https://wpcode.com/lite/', 'snippet-editor', 'ai', 'improve' );
		$data['ai_button']        = esc_html__( 'Upgrade to PRO', 'insert-headers-and-footers' );

		return $data;
	}

	/**
	 * Add a body class specific to the code type of the current snippet.
	 *
	 * @param string $body_class The body class.
	 *
	 * @return string
	 */
	public function body_class_code_type( $body_class ) {
		$body_class .= ' wpcode-code-type-' . $this->code_type;

		return $body_class;
	}

	/**
	 * If the editor should grow with the code, add a body class.
	 *
	 * @param string $body_class The body class.
	 *
	 * @return string
	 */
	public function maybe_editor_height_auto( $body_class ) {
		$height_auto = wpcode()->settings->get_option( 'editor_height_auto' );
		if ( false !== $height_auto ) {
			$body_class .= ' wpcode-editor-auto ';
		}

		return $body_class;
	}

	/**
	 * If the current user has syntax_highlighting disabled add a body class.
	 *
	 * @param string $body_class The body class.
	 *
	 * @return string
	 */
	public function maybe_syntax_highlighting_disabled( $body_class ) {
		$user = wp_get_current_user();

		if ( ! isset( $user->syntax_highlighting ) || 'false' === $user->syntax_highlighting || ! function_exists( 'wp_enqueue_code_editor' ) ) {
			$body_class .= ' wpcode-syntax-highlighting-disabled ';
		}

		return $body_class;
	}

	/**
	 * If we have a custom height set, output the styles to change that.
	 * Also, check if the auto-height is set.
	 *
	 * @return void
	 */
	public function maybe_editor_height() {
		// Let's check if the auto-height is not enabled.
		$height_auto = wpcode()->settings->get_option( 'editor_height_auto' );
		if ( false !== $height_auto ) {
			return;
		}

		$height = wpcode()->settings->get_option( 'editor_height' );
		if ( ! $height ) {
			return;
		}

		echo '<style>.CodeMirror {height: ' . absint( $height ) . 'px;}</style>';
	}

	/**
	 * Get the markup of the custom shortcode row.
	 *
	 * @return void
	 */
	public function get_input_row_custom_shortcode() {
		$this->metabox_row(
			__( 'Custom Shortcode', 'insert-headers-and-footers' ),
			sprintf(
				'<input type="text" placeholder="%s" class="wpcode-input-text" id="wpcode-custom-shortcode-lite" readonly />',
				__( 'Shortcode name', 'insert-headers-and-footers' )
			),
			'',
			'',
			'',
			__( 'Use this field to define a custom shortcode name instead of the id-based one.', 'insert-headers-and-footers' ),
			true
		);
	}

	/**
	 * Method used to output the markup for the shortcode attributes input.
	 *
	 * @return void
	 */
	public function get_input_row_shortcode_attributes() {
		$button = sprintf(
			'<button class="wpcode-button wpcode-button-icon wpcode-button-secondary" id="wpcode_add_attribute" type="button"><span>%1$s</span> %2$s</button>',
			get_wpcode_icon( 'plus', 16, 16, '0 96 960 960' ),
			__( 'Add&nbsp;Attribute', 'insert-headers-and-footers' )
		);
		$input  = sprintf(
			'<div class="wpcode-input-with-button"><input type="text" id="wpcode-shortcode-attribute-name" placeholder="%1$s" class="wpcode-input-text" />%2$s</div>',
			__( 'Attribute name', 'insert-headers-and-footers' ),
			$button
		);

		$input .= $this->help_icon(
			sprintf(
			// Translators: %1$s is the opening <code> tag, %2$s is the closing </code> tag.
				__( 'Use this field to define the attribute name for your shortcode and click Add Attribute. Attributes added here will be available to use as smart tags and as variables inside snippets. E.g. an attribute named "keyword" will be available in a PHP snippet as %1$s$keyword%2$s. %3$sLearn more%4$s.', 'insert-headers-and-footers' ),
				'<code>',
				'</code>',
				'<a href="' . wpcode_utm_url( 'https://wpcode.com/docs/shortcode-attributes/', 'snippet-editor', 'shortcode-attributes' ) . '" target="_blank">',
				'</a>'
			),
			false
		);

		$input .= '<div id="wpcode-shortcode-attributes-list" class="wpcode-shortcode-attributes-list">';
		$input .= $this->get_shortcode_attributes_list();
		$input .= '<script type="text/template" id="wpcode_shortcode_attribute_list_item_template">' . $this->get_shortcode_attribute_list_item() . '</script>';
		$input .= '</div>';

		$this->metabox_row(
			__( 'Shortcode Attributes', 'insert-headers-and-footers' ),
			$input,
			'wpcode-shortcode-attribute-name',
			'',
			''
		);
	}

	/**
	 * Get the markup of the shortcode attributes list.
	 *
	 * @return string|void
	 */
	public function get_shortcode_attributes_list() {
		if ( ! isset( $this->snippet ) ) {
			return '<ul></ul>';
		}
		$attributes = $this->snippet->get_shortcode_attributes();
		if ( empty( $attributes ) ) {
			return '<ul></ul>';
		}
		$output = '<ul>';
		foreach ( $attributes as $attribute ) {
			$output .= sprintf(
				$this->get_shortcode_attribute_list_item(),
				esc_html( $attribute )
			);
		}
		$output .= '</ul>';

		return $output;
	}

	/**
	 * Get the markup of the shortcode attribute list item.
	 *
	 * @return string
	 */
	public function get_shortcode_attribute_list_item() {
		return sprintf(
			'<li><span class="wpcode-shortcode-attribute-name">%1$s</span><button class="wpcode-shortcode-attribute-remove wpcode-button-just-icon">%2$s</button><input name="wpcode_shortcode_attributes[]" class="wpcode-shortcode-attribute-item-input" value="%1$s" type="hidden" /></li>',
			'%1$s',
			get_wpcode_icon( 'trash' )
		);
	}

	/**
	 * Get the markup of the schedule main dates inputs.
	 *
	 * @return void
	 */
	public function get_input_row_schedule() {
		?>
		<div class="wpcode-schedule-form-fields">
			<?php
			$schedule_label = __( 'Schedule snippet', 'insert-headers-and-footers' );
			$this->metabox_row(
				$schedule_label,
				$this->get_input_row_schedule_contents( '', '', true ),
				'wpcode_schedule',
				'',
				'',
				'',
				true
			);
			?>
		</div>
		<?php
	}

	/**
	 * Get the description of the load as file option.
	 *
	 * @return string
	 */
	public function get_input_row_as_file_description() {
		return sprintf(
		// Translators: Link to the documentation article for files as snippets. %1$s is the opening anchor tag, %2$s is the closing anchor tag.
			esc_html__( 'If enabled, this snippet will be loaded as a physical file instead of being inserted in the source of the page. %1$sLearn more%2$s.', 'insert-headers-and-footers' ),
			'<a target="_blank" rel="noreferrer noopener" href="' . esc_url( wpcode_utm_url( 'https://wpcode.com/docs/snippet-files', 'snippet-manager', 'load-as-file', 'learn-more' ) ) . '">',
			'</a>'
		);
	}

	/**
	 * Get the markup for displaying an option to load the snippet as a file if the code type is CSS or JS.
	 *
	 * @return void
	 */
	public function get_input_row_as_file() {
		$this->metabox_row(
			esc_html__( 'Load as file', 'insert-headers-and-footers' ),
			$this->get_checkbox_toggle(
				false,
				'wpcode_snippet_as_file'
			),
			'wpcode_snippet_as_file',
			'#wpcode_snippet_type',
			'js,css',
			$this->get_input_row_as_file_description(),
			true,
			'wpcode_snippet_as_file_option'
		);
	}

	/**
	 * Get the markup of the schedule inputs.
	 *
	 * @param string $start Start date.
	 * @param string $end End date.
	 * @param bool   $read_only If the inputs should be read-only.
	 *
	 * @return string
	 */
	public function get_input_row_schedule_contents( $start = '', $end = '', $read_only = false ) {
		$markup = '<div class="wpcode-input-row wpcode-input-row-schedule">';

		$markup .= $this->get_input_datetime(
			'wpcode-schedule-start',
			$start,
			esc_html__( 'Start Date', 'insert-headers-and-footers' ),
			esc_html__( 'Start Date', 'insert-headers-and-footers' ),
			esc_html__( 'Clear start date', 'insert-headers-and-footers' ),
			$read_only
		);

		$markup .= $this->get_input_datetime(
			'wpcode-schedule-end',
			$end,
			esc_html__( 'End Date', 'insert-headers-and-footers' ),
			esc_html__( 'End Date', 'insert-headers-and-footers' ),
			esc_html__( 'Clear end date', 'insert-headers-and-footers' ),
			$read_only
		);

		$markup .= '</div>';

		$markup .= $this->help_icon(
			sprintf(
			// Translators: %1$s and %2$s are HTML tags for a link to the documentation article.
				__( 'Looking for more scheduling options? %1$sClick here%2$s to read more about all the available options.', 'insert-headers-and-footers' ),
				'<a href="' . wpcode_utm_url( 'https://wpcode.com/docs/how-to-create-advanced-scheduling-rules/', 'snippet-editor', 'schedule-more' ) . '" target="_blank">',
				'</a>'
			),
			false
		);

		return $markup;
	}

	/**
	 * Get the markup of the schedule main dates inputs.
	 *
	 * @param string $id The id of the input.
	 * @param string $value The value of the input.
	 * @param string $label The label of the input.
	 * @param string $placeholder The placeholder of the input.
	 * @param string $clear_text The text of the clear button.
	 *
	 * @return string
	 */
	public function get_input_datetime( $id, $value = '', $label = '', $placeholder = '', $clear_text = '', $readonly = false ) {

		$markup = '';
		if ( $label ) {
			$markup .= sprintf(
				'<div class="wpcode-input-row-label screen-reader-text">
					<label for="%1$s">%2$s</label>
				</div>',
				esc_attr( $id ),
				esc_html( $label )
			);
		}
		$markup .= '<div class="wpcode-input-row-input">';
		$markup .= sprintf(
			'<input type="text" class="wpcode-input-text wpcode-input-datetime" id="%1$s" name="%1$s" value="%2$s" placeholder="%3$s" %4$s />',
			esc_attr( $id ),
			esc_attr( $value ),
			esc_attr( $placeholder ),
			$readonly ? 'readonly' : ''
		);
		if ( ! empty( $clear_text ) ) {
			$markup .= sprintf(
				'<button type="button" class="wpcode-input-button wpcode-input-button-clear" title="%1$s" style="%3$s">%2$s</button>',
				esc_attr( $clear_text ),
				get_wpcode_icon( 'close', 16, 16 ),
				! empty( $value ) ? '' : 'display:none;'
			);
		}
		$markup .= '</div>';

		return $markup;
	}

	/**
	 * Add extra snippet data.
	 *
	 * @param WPCode_Snippet $snippet Snippet about to be saved, passed by reference.
	 *
	 * @return void
	 */
	public function add_extra_snippet_data( &$snippet ) {
	}

	/**
	 * Markup for the code revisions metabox.
	 *
	 * @return void
	 */
	public function field_code_revisions() {
		$html = sprintf(
			'<p>%s</p><hr />',
			esc_html__( 'As you make changes to your snippet and save, you will get a list of previous versions with all the changes made in each revision. You can compare revisions to the current version or see changes as they have been saved by going through each revision. Any of the revisions can then be restored as needed.', 'insert-headers-and-footers' )
		);

		$html .= $this->code_revisions_list();
		$this->metabox(
			__( 'Code Revisions', 'insert-headers-and-footers' ),
			$html,
			__( 'Easily switch back to a previous version of your snippet.', 'insert-headers-and-footers' )
		);
	}

	/**
	 * Markup for the device type metabox.
	 *
	 * @return void
	 */
	public function field_device_type() {
		$html = sprintf(
			'<p>%s</p>',
			esc_html__( 'Limit where you want this snippet to be loaded by device type. By default, snippets are loaded on all devices.', 'insert-headers-and-footers' )
		);

		$html .= '<div class="wpcode-separator"></div>';
		$html .= $this->device_type_picker();
		$this->metabox(
			__( 'Device Type', 'insert-headers-and-footers' ),
			$html
		);
	}

	/**
	 * This method returns the markup for the device type radio input picker, the
	 * three options available are Any device type, Desktop only and Mobile only.
	 * By default, any device type is selected.
	 *
	 * @return string
	 */
	public function device_type_picker() {
		$html = '<div class="wpcode-device-type-picker wpcode-device-type-picker-lite">';
		$html .= $this->get_radio_field_icon( 'devices', __( 'Any device type', 'insert-headers-and-footers' ), 'any', '', true );
		$html .= $this->get_radio_field_icon( 'desktop', __( 'Desktop only', 'insert-headers-and-footers' ), 'desktop', '', false, '', true );
		$html .= $this->get_radio_field_icon( 'mobile', __( 'Mobile only', 'insert-headers-and-footers' ), 'mobile', '', false, '', true );
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get a styled radio input with an icon.
	 *
	 * @param string $icon Icon to use for label, @see get_wpcode_icon
	 * @param string $label The text of the label to display.
	 * @param string $value The value of the radio input.
	 * @param string $name The input name (for PHP).
	 * @param bool   $checked Whether the input is checked or not.
	 * @param string $id Unique id for the input, by default name + value will be used.
	 * @param bool   $disabled Whether the input should be disabled.
	 *
	 * @return string
	 */
	public function get_radio_field_icon( $icon = '', $label = '', $value = '', $name = '', $checked = false, $id = '', $disabled = false ) {

		$id   = empty( $id ) ? $name . '-' . $value : $id;
		$html = '<div class="wpcode-input-radio">';
		$html .= sprintf(
			'<input type="radio" name="%1$s" id="%2$s" value="%3$s" %4$s %5$s />',
			esc_attr( $name ),
			esc_attr( $id ),
			esc_attr( $value ),
			checked( $checked, true, false ),
			disabled( $disabled, true, false )
		);
		$html .= sprintf(
			'<label for="%1$s"><span class="wpcode-input-radio-icon">%2$s</span><span class="wpcode-input-radio-label">%3$s</span></label>',
			esc_attr( $id ),
			get_wpcode_icon( $icon, 48, 48 ),
			wp_kses_post( $label )
		);
		$html .= '</div>';

		return $html;
	}

	/**
	 * List of code revision items.
	 *
	 * @return string
	 */
	public function code_revisions_list() {
		return $this->code_revisions_list_with_notice(
			esc_html__( 'Code Revisions is a Pro Feature', 'insert-headers-and-footers' ),
			sprintf(
				'<p>%s</p>',
				esc_html__( 'Upgrade to WPCode Pro today and start tracking revisions and see exactly who, when and which changes were made to your snippet.', 'insert-headers-and-footers' )
			),
			array(
				'text' => esc_html__( 'Upgrade to Pro and Unlock Revisions', 'insert-headers-and-footers' ),
				'url'  => wpcode_utm_url( 'https://wpcode.com/lite/', 'snippet-editor', 'revisions', 'upgrade-to-pro' ),
			),
			array(
				'text' => esc_html__( 'Learn more about all the features', 'insert-headers-and-footers' ),
				'url'  => wpcode_utm_url( 'https://wpcode.com/lite/', 'snippet-editor', 'revisions', 'features' ),
			)
		);
	}

	/**
	 * Display a notice if the snippet loaded for editing triggered an error.
	 *
	 * @return void
	 */
	public function maybe_show_error_notice() {
		if ( ! isset( $this->snippet ) ) {
			return;
		}
		if ( $this->is_locked ) {
			?>
			<div class="notice-warning fade notice is-dismissible">
				<p>
					<?php
					// Translators: The placeholder gets replaced with the display name of the user currently editing the snippet.
					printf( esc_html__( 'Notice: %1$s is also editing this snippet. Please be aware that your changes could be overwritten.', 'insert-headers-and-footers' ), esc_html( $this->locked_by ) );
					?>
				</p>
			</div>
			<?php
		}
		$last_error = $this->snippet->get_last_error();
		if ( empty( $last_error ) ) {
			return;
		}
		$error_line      = isset( $last_error['error_line'] ) ? $last_error['error_line'] : false;
		$time            = $last_error['time'];
		$logging_enabled = wpcode()->settings->get_option( 'error_logging' );
		if ( $logging_enabled ) {
			$button_text = esc_html__( 'View Error Logs', 'insert-headers-and-footers' );
			$button_url  = add_query_arg(
				array(
					'page' => 'wpcode-tools',
					'view' => 'logs',
				),
				admin_url( 'admin.php' )
			);
		} else {
			$button_text = esc_html__( 'Enable Error Logging', 'insert-headers-and-footers' );
			$button_url  = add_query_arg(
				array(
					'page' => 'wpcode-settings',
					'view' => 'errors',
				),
				admin_url( 'admin.php' )
			);
		}
		?>
		<div class="info fade notice is-dismissible">
			<?php if ( 'deactivated' === $last_error['wpc_type'] ) { ?>
				<p>
					<?php
					printf(
					// Translators: The placeholder gets replaced with the time passed since the snippet was deactivated.
						esc_html__( 'This snippet was automatically deactivated due to an error at %1$s on %2$s (%3$s ago).', 'insert-headers-and-footers' ),
						gmdate( 'H:i:s', $last_error['time'] ),
						gmdate( 'Y-m-d', $last_error['time'] ),
						human_time_diff( $last_error['time'] )
					);
					?>
				</p>
			<?php } else { ?>
				<p>
					<?php
					printf(
					// Translators: The placeholder gets replaced with the time passed since the snippet was deactivated.
						esc_html__( 'This snippet first threw an error at %1$s on %2$s (%3$s ago).', 'insert-headers-and-footers' ),
						gmdate( 'H:i:s', $time ),
						gmdate( 'Y-m-d', $time ),
						human_time_diff( $time )
					);
					?>
				</p>
			<?php } ?>
			<p>
				<?php
				// Display the error message after a label.
				printf( '<strong>%s</strong>', esc_html__( 'Error message:', 'insert-headers-and-footers' ) );
				?>
			</p>
			<pre class="wpcode-error-preview"><?php echo esc_html( wpcode()->error->get_error_message_short( $last_error['message'] ) ); ?></pre>
			<?php if ( $error_line ) { ?>
				<p>
					<?php
					printf(
					// Translators: The placeholders make the text bold and add the line number.
						esc_html__( 'The error occurred on %1$sline %3$d%2$s of this snippet\'s code (highlighted below).', 'insert-headers-and-footers' ),
						'<strong>',
						'</strong>',
						absint( $error_line )
					);
					?>
				</p>
			<?php } ?>

			<?php if ( ! empty( $last_error['url'] ) ) { ?>
				<p>
					<?php
					printf(
					// Translators: The placeholder is replaced with the URL where the error happened.
						esc_html__( 'The error was triggered at the following URL: %1$s', 'insert-headers-and-footers' ),
						'<a href=' . esc_url( $last_error['url'] ) . ' target="_blank" rel="noopener noreferrer">' . esc_url( $last_error['url'] ) . '</a>'
					);
					?>
				</p>
			<?php } ?>
			<p>
				<?php
				if ( $logging_enabled ) {
					printf(
					// Translators: The placeholders are for the link to the error logs.
						esc_html__( 'You can %1$sview the error log%2$s to get more details about the error that caused this. The error will also be in a snippet-specific log whose name starts with snippet-%3$d (the id of this snippet).', 'insert-headers-and-footers' ),
						'<a href="' . esc_url( $button_url ) . '">',
						'</a>',
						absint( $this->snippet->get_id() )
					);
				} else {
					esc_html_e( 'You can enable error logging to get more details about the error that caused this.', 'insert-headers-and-footers' );
				}
				?>
			</p>
			<p>
				<?php esc_html_e( 'This message will disappear when the snippet is updated.', 'insert-headers-and-footers' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $button_url ); ?>" class="button button-primary">
					<?php echo esc_html( $button_text ); ?>
				</a>
				<a href="<?php echo esc_url( wpcode_utm_url( 'https://wpcode.com/docs/how-to-debug-php-errors-in-wpcode/', 'snippet-error-notice', 'edit-snippet' ) ); ?>" class="button button-secondary" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Learn More', 'insert-headers-and-footers' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Get a list of custom conditional logic operators for the conditional logic builder.
	 *
	 * @return array
	 */
	public function get_conditional_logic_operators_custom_labels() {
		$options = wpcode()->conditional_logic->get_all_admin_options();

		$labels = array();

		foreach ( $options as $option ) {
			foreach ( $option['options'] as $key => $opt_group ) {
				if ( ! empty( $opt_group['operator_labels'] ) ) {
					$labels[ $key ] = $opt_group['operator_labels'];
				}
				if ( ! empty( $opt_group['placeholder'] ) ) {
					if ( ! isset( $labels[ $key ] ) ) {
						$labels[ $key ] = array();
					}
					$labels[ $key ]['placeholder'] = $opt_group['placeholder'];
				}
			}
		}

		return $labels;
	}

	/**
	 * Get the markup for the AI generate button.
	 *
	 * @param string $class The class to add to the button.
	 *
	 * @return void
	 */
	public function ai_generate_button( $class = 'wpcode-button-ai-not-available' ) {
		?>
		<button class="wpcode-button wpcode-button-secondary wpcode-button-just-icon wpcode-ai-improve wpcode-button-ai-generate <?php echo esc_attr( $class ); ?>" id="wpcode-ai-improve" type="button" data-show-if-id="#wpcode_snippet_type" data-show-if-value="php" title="<?php esc_attr_e( 'Use AI to improve your snippet by describing the changes you want', 'wpcode-premium' ); ?>">
			<?php wpcode_icon( 'aisparks', 16, 16, '0 0 29.57 30' ); ?>
		</button>
		<?php
	}
}
