<?php
/**
 * Table of snippets for the admin list.
 *
 * @package WPCode
 */

/**
 * Generate the table for the list of code snippets.
 */
class WPCode_Code_Snippets_Table extends WP_List_Table {

	/**
	 * Number of snippets to show per page.
	 *
	 * @var int
	 */
	public $per_page;

	/**
	 * Number of snippets in different views.
	 *
	 * @var array
	 */
	private $count;

	/**
	 * Current view.
	 *
	 * @var string
	 */
	private $view;

	/**
	 * Primary class constructor.
	 */
	public function __construct() {

		// Utilize the parent constructor to build the main class properties.
		parent::__construct(
			array(
				'singular' => 'wpcode-snippet',
				'plural'   => 'wpcode-snippets',
				'ajax'     => false,
			)
		);

		// Default number of snippets to show per page.
		$this->per_page = $this->get_items_per_page( 'wpcode_snippets_per_page', (int) apply_filters( 'wpcode_code_snippets_per_page', 20 ) );
		$this->view     = $this->get_current_view();
	}

	/**
	 * Load the current view from the get param.
	 *
	 * @return string
	 */
	private function get_current_view() {
		return isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Render the checkbox column.
	 *
	 * @param WP_Post $item Snippet.
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return '<input type="checkbox" name="snippet_id[]" value="' . absint( $item->ID ) . '" />';
	}

	/**
	 * Load the snippet for the columns.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return WPCode_Snippet
	 */
	public function get_snippet( $post ) {
		return new WPCode_Snippet( $post );
	}

	/**
	 * The post type for this view.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return wpcode_get_post_type();
	}

	/**
	 * Render the columns.
	 *
	 * @param WP_Post $item CPT object as a snippet representation.
	 * @param string  $column_name Column Name.
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		$snippet = $this->get_snippet( $item );

		switch ( $column_name ) {
			case 'id':
				$value = $snippet->get_id();
				break;

			case 'location':
				$location = $snippet->get_location();
				$value    = '';
				if ( ! empty( $location ) ) {
					$label = wpcode()->auto_insert->get_location_label( $location );
					if ( 'trash' === $this->view ) {
						$value = $label;
					} else {
						$url   = add_query_arg( 'location', $snippet->get_location_term()->slug );
						$url   = remove_query_arg( 'paged', $url );
						$value = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), esc_html( $label ) );
					}
				}
				break;

			case 'created':
				$value = sprintf(
				// Translators: This is the format for displaying the date in the admin list, [date] at [time].
					__( '%1$s at %2$s', 'insert-headers-and-footers' ),
					get_the_time( get_option( 'date_format' ), $snippet->get_post_data() ),
					get_the_time( get_option( 'time_format' ), $snippet->get_post_data() )
				);
				break;

			case 'updated':
				$value = sprintf(
				// Translators: This is the format for displaying the date in the admin list, [date] at [time].
					__( '%1$s at %2$s', 'insert-headers-and-footers' ),
					get_the_modified_date( get_option( 'date_format' ), $snippet->get_post_data() ),
					get_the_modified_date( get_option( 'time_format' ), $snippet->get_post_data() )
				);
				break;

			case 'author':
				$value  = '';
				$author = get_userdata( $snippet->get_snippet_author() );

				if ( ! $author ) {
					break;
				}

				$value         = $author->display_name;
				$user_edit_url = get_edit_user_link( $author->ID );

				if ( ! empty( $user_edit_url ) ) {
					$value = '<a href="' . esc_url( $user_edit_url ) . '">' . esc_html( $value ) . '</a>';
				}
				break;

			case 'tags':
				$tags       = $snippet->get_tags();
				$tags_links = array();
				if ( 'trash' !== $this->view ) {
					foreach ( $tags as $tag ) {
						$url          = add_query_arg( 'tag', $tag );
						$url          = remove_query_arg( 'paged', $url );
						$tags_links[] = sprintf(
							'<a href="%1$s" title="%2$s">%3$s</a>',
							esc_url( $url ),
							// Translators: The tag by which to filter the list of snippets in the admin.
							sprintf( __( 'Filter snippets by tag: %s', 'insert-headers-and-footers' ), esc_attr( $tag ) ),
							esc_html( $tag )
						);
					}
				} else {
					$tags_links = $tags;
				}
				$value = implode( ', ', $tags_links );
				break;

			case 'status':
				$value = $this->get_status_toggle( $snippet->is_active(), $snippet->get_id() );
				break;

			case 'shortcode':
				$shortcode = apply_filters( 'wpcode_shortcode_preview', '[wpcode id="' . absint( $snippet->get_id() ) . '"]', $snippet );
				// Show the shortcode in a code tag so it's easy to copy.
				$value = '<code class="wpcode-copy" data-copy-value="' . esc_attr( $shortcode ) . '">' . esc_html( $shortcode ) . get_wpcode_icon( 'copy' ) . '</code>';
				break;

			case 'code_type':
				// Let's display the code type with a link to filter by code type.
				$code_type = $snippet->get_code_type();
				if ( 'trash' === $this->view ) {
					$value = $code_type;
				} else {
					$url   = add_query_arg(
						array(
							'filter_action' => 'Filter',
							'type'          => $code_type,
						),
						$this->admin_url( 'admin.php?page=wpcode' )
					);
					$value = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), esc_html( $code_type ) );
				}
				break;

			case 'priority':
				echo esc_html( $snippet->get_priority() );

			default:
				$value = '';
		}

		return apply_filters( 'wpcode_code_snippets_table_column_value', $value, $snippet, $column_name );
	}

	/**
	 * Get the markup for the status toggle.
	 *
	 * @param bool $active If the snippet is active or not.
	 * @param int  $snippet_id The id of the snippet.
	 *
	 * @return string
	 */
	public function get_status_toggle( $active, $snippet_id ) {
		$markup = '<label class="wpcode-checkbox-toggle">';

		$markup .= '<input data-id=' . absint( $snippet_id ) . ' type="checkbox" ' . checked( $active, true, false ) . ' class="wpcode-status-toggle" />';
		$markup .= '<span class="wpcode-checkbox-toggle-slider"></span>';
		$markup .= '<span class="screen-reader-text">' . esc_html__( 'Toggle Snippet Status', 'insert-headers-and-footers' ) . '</span>';
		$markup .= '</label>';

		$snippets_with_errors = wpcode()->error->get_snippets_with_errors();
		// Let's check if the snippet threw an error.
		if ( ! empty( $snippets_with_errors ) && in_array( $snippet_id, $snippets_with_errors, true ) ) {
			$last_error = get_post_meta( $snippet_id, '_wpcode_last_error', true );
			if ( ! empty( $last_error ) ) {
				$type = 'error';
				if ( isset( $last_error['wpc_type'] ) && 'deactivated' === $last_error['wpc_type'] ) {
					$tooltip_text = sprintf(
					// Translators: %1$s is the time since the snippet was deactivated, %2$s is the date and time of deactivation.
						__( 'This snippet was automatically deactivated because of a fatal error at %2$s on %3$s (%1$s ago)', 'insert-headers-and-footers' ),
						human_time_diff( $last_error['time'], time() ),
						gmdate( 'H:i:s', $last_error['time'] ),
						gmdate( 'Y-m-d', $last_error['time'] )
					);

					$type = 'deactivated';
				} else {
					$tooltip_text = esc_html__( 'This snippet threw an error, you can see more details when editing the snippet.', 'insert-headers-and-footers' );
				}

				$markup .= '<span class="wpcode-table-status-icon wpcode-help-tooltip wpcode-table-status-icon-' . esc_attr( $type ) . '">' . get_wpcode_icon( 'info' ) . '<span class="wpcode-help-tooltip-text">' . $tooltip_text . '</span></span>';
			}
		}

		return $markup;
	}

	/**
	 * Render the snippet name column with action links.
	 *
	 * @param WP_Post $snippet Snippet.
	 *
	 * @return string
	 */
	public function column_name( $snippet ) {
		// Build the row action links and return the value.
		return $this->get_column_name_title( $snippet ) . $this->get_column_name_row_actions( $snippet );
	}

	/**
	 * Get the snippet name HTML for the snippet name column.
	 *
	 * @param WP_Post $snippet Snippet post object.
	 *
	 * @return string
	 */
	protected function get_column_name_title( $snippet ) {

		$title = ! empty( $snippet->post_title ) ? $snippet->post_title : $snippet->post_name;
		$name  = sprintf(
			'<span><strong>%s</strong></span>',
			esc_html( $title )
		);

		if ( 'trash' === $this->view ) {
			return $name;
		}

		if ( current_user_can( 'edit_post', $snippet->ID ) ) {
			$name = sprintf(
				'<a href="%s" title="%s"><strong>%s</strong></a>',
				esc_url(
					add_query_arg(
						'snippet_id',
						$snippet->ID,
						$this->admin_url( 'admin.php?page=wpcode-snippet-manager' )
					)
				),
				esc_attr__( 'Edit This Snippet', 'insert-headers-and-footers' ),
				esc_html( $title )
			);
		}

		// Check if snippet is locked for editing.
		$post_lock = wp_check_post_lock( $snippet );
		if ( $post_lock ) {
			$user = get_user_by( 'id', $post_lock );

			$currently_editing = sprintf(
				/* translators: %s: User display name */
				esc_html__( '%s is currently editing', 'insert-headers-and-footers' ),
				esc_html( $user->display_name )
			);

			$name = '<div class="wpcode-locked-snippet">' . $currently_editing . '</div>' . $name;
		}

		return $name;
	}

	/**
	 * Get the row actions HTML for the snippet name column.
	 *
	 * @param WP_Post $snippet Snippet object.
	 *
	 * @return string
	 */
	protected function get_column_name_row_actions( $snippet ) {
		/**
		 * Filters row action links on the 'Code Snippets' admin page.
		 *
		 * @param array   $row_actions An array of action links for a given snippet.
		 * @param WP_Post $snippet Snippet object.
		 */
		$actions = array();

		if ( 'trash' === $this->view ) {
			if ( current_user_can( 'edit_post', $snippet->ID ) ) {
				$actions['untrash'] = sprintf(
					'<a href="%s" title="%s">%s</a>',
					esc_url(
						wp_nonce_url(
							add_query_arg(
								array(
									'action'     => 'untrash',
									'snippet_id' => $snippet->ID,
								),
								$this->admin_url( 'admin.php?page=wpcode' )
							),
							'wpcode_untrash_nonce'
						)
					),
					esc_attr__( 'Restore this snippet', 'insert-headers-and-footers' ),
					esc_html__( 'Restore', 'insert-headers-and-footers' )
				);
				$actions['delete']  = sprintf(
					'<a href="%s" title="%s">%s</a>',
					esc_url(
						wp_nonce_url(
							add_query_arg(
								array(
									'action'     => 'delete',
									'snippet_id' => $snippet->ID,
								),
								$this->admin_url( 'admin.php?page=wpcode' )
							),
							'wpcode_delete_nonce'
						)
					),
					esc_attr__( 'Delete this snippet permanently', 'insert-headers-and-footers' ),
					esc_html__( 'Delete Permanently', 'insert-headers-and-footers' )
				);
			}
		} else {

			if ( current_user_can( 'edit_post', $snippet->ID ) ) {
				$actions['edit'] = sprintf(
					'<a href="%s" title="%s">%s</a>',
					esc_url( add_query_arg( 'snippet_id', $snippet->ID, $this->admin_url( 'admin.php?page=wpcode-snippet-manager' ) ) ),
					esc_attr__( 'Edit This Snippet', 'insert-headers-and-footers' ),
					esc_html__( 'Edit', 'insert-headers-and-footers' )
				);
			}

			if ( current_user_can( 'edit_post', $snippet->ID ) && ! wpcode_testing_mode_enabled() ) {
				$actions['trash'] = sprintf(
					'<a href="%s" title="%s">%s</a>',
					esc_url(
						wp_nonce_url(
							add_query_arg(
								array(
									'action'     => 'trash',
									'snippet_id' => $snippet->ID,
								),
								$this->admin_url( 'admin.php?page=wpcode' )
							),
							'wpcode_trash_nonce'
						)
					),
					esc_attr__( 'Move this snippet to trash', 'insert-headers-and-footers' ),
					esc_html__( 'Trash', 'insert-headers-and-footers' )
				);
			}

			if ( current_user_can( 'edit_post', $snippet->ID ) ) {
				$actions['duplicate'] = sprintf(
					'<a href="%s" title="%s">%s</a>',
					esc_url(
						wp_nonce_url(
							add_query_arg(
								array(
									'action'     => 'duplicate',
									'snippet_id' => $snippet->ID,
								),
								$this->admin_url( 'admin.php?page=wpcode' )
							),
							'wpcode_duplicate_nonce'
						)
					),
					esc_attr__( 'Duplicate this snippet', 'insert-headers-and-footers' ),
					esc_html__( 'Duplicate', 'insert-headers-and-footers' )
				);
			}
		}

		return $this->row_actions( apply_filters( 'wpcode_code_snippets_row_actions', $actions, $snippet, $this->view ) );
	}

	/**
	 * Define bulk actions available for our table listing.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		if ( 'trash' === $this->view ) {
			$bulk_actions = array(
				'untrash' => esc_html__( 'Restore', 'insert-headers-and-footers' ),
				'delete'  => esc_html__( 'Delete Permanently', 'insert-headers-and-footers' ),
			);
		} else {
			$bulk_actions = array(
				'trash'   => __( 'Trash', 'insert-headers-and-footers' ),
				'enable'  => __( 'Activate', 'insert-headers-and-footers' ),
				'disable' => __( 'Deactivate', 'insert-headers-and-footers' ),
			);
		}

		return apply_filters( 'wpcode_snippets_bulk_actions', $bulk_actions );
	}

	/**
	 * Message to be displayed when there are no snippets.
	 *
	 * @since 2.0.0
	 */
	public function no_items() {

		esc_html_e( 'No snippets found.', 'insert-headers-and-footers' );
	}

	/**
	 * Fetch and set up the final data for the table.
	 *
	 * @since 2.0.0
	 */
	public function prepare_items() {

		// Set up the columns.
		$columns = $this->get_columns();

		// Hidden columns (none).
		$hidden = get_hidden_columns( $this->screen );

		$sortable = array(
			'name'     => array( 'title', false ),
			'created'  => array( 'date', false ),
			'updated'  => array( 'last_updated', false ),
			'priority' => array( 'priority', false ),
		);

		$sortable_query_params = array(
			'last_updated' => 'modified',
			'title'        => 'title',
			'date'         => 'id',
			'priority'     => 'priority',
		);

		// Set column headers.
		$this->_column_headers = array( $columns, $hidden, $sortable, 'name' );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$page = $this->get_pagenum();
		if ( isset( $_GET['order'] ) ) {
			$order = 'asc' === $_GET['order'] ? 'ASC' : 'DESC';
		} else {
			$order      = 'DESC';
			$user_order = get_user_option( 'wpcode_snippets_order' );
			if ( ! empty( $user_order ) ) {
				$order = $user_order;
			}
		}
		// Same thing but for order by.
		if ( isset( $_GET['orderby'] ) ) {
			$order_by = sanitize_key( $_GET['orderby'] );
		} else {
			$order_by     = 'ID';
			$user_orderby = get_user_option( 'wpcode_snippets_order_by' );
			if ( ! empty( $user_orderby ) ) {
				$order_by = $user_orderby;
			}
		}

		if ( isset( $sortable_query_params[ $order_by ] ) ) {
			$order_by = $sortable_query_params[ $order_by ];
		} else {
			$order_by = 'ID';
		}

		$per_page    = $this->get_items_per_page( 'wpcode_snippets_per_page', $this->per_page );
		$is_filtered = false;

		$args = array(
			'orderby'        => $order_by,
			'order'          => $order,
			'nopaging'       => false,
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'no_found_rows'  => false,
			'post_status'    => array( 'publish', 'draft' ),
			'post_type'      => $this->get_post_type(),
		);

		if ( 'priority' === $order_by ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = '_wpcode_priority'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		}

		if ( ! empty( $_GET['location'] ) ) {
			$is_filtered       = true;
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'wpcode_location',
					'terms'    => array( sanitize_key( $_GET['location'] ) ),
					'field'    => 'slug',
				),
			);
		}

		if ( ! empty( $_GET['type'] ) ) {
			$is_filtered = true;
			if ( ! isset( $args['tax_query'] ) ) {
				$args['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			}
			$args['tax_query'][] = array(
				'taxonomy' => 'wpcode_type',
				'terms'    => array( sanitize_text_field( wp_unslash( $_GET['type'] ) ) ),
				'field'    => 'slug',
			);
		}

		if ( ! empty( $_GET['tag'] ) ) {
			$is_filtered = true;
			if ( ! isset( $args['tax_query'] ) ) {
				$args['tax_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			}
			$args['tax_query'][] = array(
				'taxonomy' => 'wpcode_tags',
				'terms'    => array( sanitize_text_field( wp_unslash( $_GET['tag'] ) ) ),
				'field'    => 'slug',
			);
		}

		if ( ! empty( $_GET['s'] ) ) {
			$args['s'] = sanitize_text_field( wp_unslash( $_GET['s'] ) );
			// This is a search so let's extend it to meta too.
			$this->add_meta_search();
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( 'all' !== $this->view ) {
			$args['post_status'] = $this->get_post_status_from_view();
		}

		if ( 'has_error' === $this->view ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array(
					'key'     => '_wpcode_last_error',
					'compare' => 'EXISTS',
				),
			);
		}

		/**
		 * Filters the `get_posts()` arguments while preparing items for the code snippets table.
		 *
		 * @param array $args Arguments array.
		 */
		$args = (array) apply_filters( 'wpcode_code_snippets_table_prepare_items_args', $args );

		$items_query = new WP_Query( $args );
		$this->items = $items_query->get_posts();
		// Remove filters to avoid conflicts.
		$this->remove_meta_search();

		$per_page = isset( $args['posts_per_page'] ) ? $args['posts_per_page'] : $this->get_items_per_page( 'wpcode_snippets_per_page', $this->per_page );

		$this->update_count( $args );

		$count_current_view = empty( $this->count[ $this->view ] ) ? 0 : $this->count[ $this->view ];
		if ( $is_filtered ) {
			$count_current_view = $items_query->found_posts;
		}

		// Finalize pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $count_current_view,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count_current_view / $per_page ),
			)
		);
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {

		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'id'        => esc_html__( 'ID', 'insert-headers-and-footers' ),
			'name'      => esc_html__( 'Name', 'insert-headers-and-footers' ),
			'author'    => esc_html__( 'Author', 'insert-headers-and-footers' ),
			'location'  => esc_html__( 'Location', 'insert-headers-and-footers' ),
			'created'   => esc_html__( 'Created', 'insert-headers-and-footers' ),
			'updated'   => esc_html__( 'Last Updated', 'insert-headers-and-footers' ),
			'tags'      => esc_html__( 'Tags', 'insert-headers-and-footers' ),
			'shortcode' => esc_html__( 'Shortcode', 'insert-headers-and-footers' ),
			'code_type' => esc_html__( 'Code Type', 'insert-headers-and-footers' ),
			'priority'  => esc_html__( 'Priority', 'insert-headers-and-footers' ),
		);
		if ( 'trash' !== $this->view ) {
			$columns['status'] = esc_html__( 'Status', 'insert-headers-and-footers' );
		}

		return apply_filters( 'wpcode_code_snippets_table_columns', $columns );
	}

	/**
	 * Dynamically add filters to extend search to meta fields.
	 *
	 * @return void
	 */
	public function add_meta_search() {
		add_filter( 'posts_join', array( $this, 'meta_search_join' ) );
		add_filter( 'posts_where', array( $this, 'meta_search_where' ) );
		add_filter( 'posts_distinct', array( $this, 'meta_search_distinct' ) );
	}

	/**
	 * Remove dynamically added filters to avoid spilling to other queries.
	 *
	 * @return void
	 */
	public function remove_meta_search() {
		remove_filter( 'posts_join', array( $this, 'meta_search_join' ) );
		remove_filter( 'posts_where', array( $this, 'meta_search_where' ) );
		remove_filter( 'posts_distinct', array( $this, 'meta_search_distinct' ) );
	}

	/**
	 * Convert custom view names to actual post statuses.
	 *
	 * @return string
	 */
	private function get_post_status_from_view() {
		switch ( $this->view ) {
			case 'active':
				$post_status = 'publish';
				break;
			case 'deactivated':
			case 'inactive':
				$post_status = 'draft';
				break;
			case 'trash':
				$post_status = 'trash';
				break;
			default:
				$post_status = 'all';
				break;
		}

		return $post_status;
	}

	/**
	 * Calculate and update snippets counts.
	 *
	 * @param array $args Get snippets arguments.
	 */
	private function update_count( $args ) {

		/**
		 * Allow counting snippets filtered by a given search criteria.
		 *
		 * If result will not contain `all` key, count All Snippets without filtering will be performed.
		 *
		 * @param array $count Contains counts of snippets in different views.
		 * @param array $args Arguments of the `get_posts`.
		 *
		 * @since 2.0.0
		 */
		$this->count = (array) apply_filters( 'wpcode_code_snippets_table_update_count', array(), $args );

		// We do not need to perform all snippets count if we have the result already.
		if ( isset( $this->count['all'] ) ) {
			return;
		}

		// Count all snippets.
		$counts                  = wp_count_posts( $this->get_post_type() );
		$this->count['all']      = array_sum( array( $counts->publish, $counts->draft ) );
		$this->count['active']   = $counts->publish;
		$this->count['inactive'] = $counts->draft;
		$this->count['trash']    = $counts->trash;

		if ( wpcode()->error->get_error_count() > 0 ) {
			// Grab a count of all the snippets with the '_wpcode_last_error' meta key.
			$threw_error              = get_posts(
				array(
					'post_type'      => $this->get_post_type(),
					'post_status'    => array( 'draft', 'publish' ),
					'posts_per_page' => - 1,
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'relation' => 'OR',
						array(
							'key'     => '_wpcode_last_error',
							'compare' => 'EXISTS',
						),
					),
				)
			);
			$this->count['has_error'] = count( $threw_error );
		} else {
			$this->count['has_error'] = 0;
		}

		/**
		 * Filters snippets count data after counting all snippets.
		 *
		 * This filter executes only if the result of `wpcode_code_snippets_table_update_count` filter
		 * doesn't contain `all` key.
		 *
		 * @param array $count Contains counts of snippets in different views.
		 * @param array $args Arguments of the `get_posts`.
		 */
		$this->count = (array) apply_filters( 'wpcode_code_snippets_table_update_count_all', $this->count, $args );
	}

	/**
	 * Extend the search to meta fields by joining the post meta table.
	 *
	 * @param string $join Join clause in the query.
	 *
	 * @return string
	 */
	public function meta_search_join( $join ) {
		global $wpdb;

		if ( is_admin() && isset( $_GET['s'] ) ) {
			$join .= ' LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
		}

		return $join;
	}

	/**
	 * Extend the where clause to include the post meta values.
	 *
	 * @param string $where Where clause in the query.
	 *
	 * @return string
	 */
	public function meta_search_where( $where ) {
		global $wpdb;

		if ( is_admin() && isset( $_GET['s'] ) ) {
			$where = preg_replace(
				"/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
				"(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where );
		}

		return $where;
	}

	/**
	 * Add distinct to the query to avoid duplicate results.
	 *
	 * @param string $distinct The distinct clause in the query.
	 *
	 * @return string
	 */
	public function meta_search_distinct( $distinct ) {

		if ( is_admin() && isset( $_GET['s'] ) ) {
			return "DISTINCT";
		}

		return $distinct;
	}

	/**
	 * Extending the `display_rows()` method in order to add hooks.
	 *
	 * @since 1.5.6
	 */
	public function display_rows() {

		do_action( 'wpcode_code_snippets_table_before_rows', $this );

		parent::display_rows();

		do_action( 'wpcode_code_snippets_table_after_rows', $this );
	}

	/**
	 * Display the pagination.
	 *
	 * @param string $which The location of the table pagination: 'top' or 'bottom'.
	 */
	protected function pagination( $which ) {

		if ( $this->has_items() ) {
			parent::pagination( $which );

			return;
		}

		printf(
			'<div class="tablenav-pages one-page">
				<span class="displaying-num">%s</span>
			</div>',
			esc_html__( '0 items', 'insert-headers-and-footers' )
		);
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination.
	 *
	 * @param string $which The location of the table navigation: 'top' or 'bottom'.
	 *
	 * @return void
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' === $which && 'trash' !== $this->view ) {
			echo '<div class="actions alignleft">';
			$this->type_dropdown( $this->get_post_type() );
			$this->location_dropdown( $this->get_post_type() );

			submit_button( __( 'Filter', 'insert-headers-and-footers' ), '', 'filter_action', false, array( 'id' => 'wpcode-filter-submit' ) );

			if ( isset( $_GET['filter_action'] ) || isset( $_GET['tag'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				echo '&nbsp;';
				submit_button( __( 'Clear', 'insert-headers-and-footers' ), '', 'filter_clear', false, array( 'id' => 'wpcode-filter-clear' ) );
			}
			echo '</div>';
		}
	}

	/**
	 * The dropdown to filter by the code type.
	 *
	 * @param string $post_type The post type.
	 *
	 * @return void
	 */
	protected function type_dropdown( $post_type ) {
		if ( ! is_object_in_taxonomy( $post_type, 'wpcode_type' ) ) {
			return;
		}

		$used_types = get_terms(
			array(
				'taxonomy'   => 'wpcode_type',
				'hide_empty' => false,
			)
		);

		if ( ! $used_types ) {
			return;
		}

		$displayed_type = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<label for="filter-by-type" class="screen-reader-text"><?php esc_html_e( 'Filter by code type', 'insert-headers-and-footers' ); ?></label>
		<select name="type" id="filter-by-type">
			<option<?php selected( $displayed_type, '' ); ?> value=""><?php esc_html_e( 'All types', 'insert-headers-and-footers' ); ?></option>
			<?php
			foreach ( $used_types as $used_type ) {

				$pretty_name = wpcode()->execute->get_type_label( $used_type->slug );
				?>
				<option<?php selected( $displayed_type, $used_type->slug ); ?> value="<?php echo esc_attr( $used_type->slug ); ?>"><?php echo esc_html( $pretty_name ); ?></option>
				<?php
			}
			?>
		</select>
		<?php
	}

	/**
	 * The dropdown to filter by location.
	 *
	 * @param string $post_type The post type.
	 *
	 * @return void
	 */
	protected function location_dropdown( $post_type ) {
		if ( ! is_object_in_taxonomy( $post_type, 'wpcode_location' ) ) {
			return;
		}

		$used_locations = get_terms(
			array(
				'taxonomy'   => 'wpcode_location',
				'hide_empty' => true,
			)
		);

		// Return if there are no posts using locations.
		if ( ! $used_locations ) {
			return;
		}

		$displayed_location = isset( $_GET['location'] ) ? sanitize_key( $_GET['location'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<label for="filter-by-location" class="screen-reader-text"><?php esc_html_e( 'Filter by location', 'insert-headers-and-footers' ); ?></label>
		<select name="location" id="filter-by-location">
			<option<?php selected( $displayed_location, '' ); ?> value=""><?php esc_html_e( 'All locations', 'insert-headers-and-footers' ); ?></option>
			<?php
			foreach ( $used_locations as $used_location ) {
				$pretty_name = wpcode()->auto_insert->get_location_label( $used_location->slug );
				?>
				<option<?php selected( $displayed_location, $used_location->slug ); ?> value="<?php echo esc_attr( $used_location->slug ); ?>"><?php echo esc_html( $pretty_name ); ?></option>
				<?php
			}
			?>
		</select>
		<?php
	}

	/**
	 * Get the list of views available on the overview table.
	 *
	 * @return array
	 */
	protected function get_views() {
		$views = array(
			'all' => $this->view_markup( 'all', __( 'All', 'insert-headers-and-footers' ) ),
		);
		if ( $this->count['active'] ) {
			$views['active'] = $this->view_markup( 'active', __( 'Active', 'insert-headers-and-footers' ) );
		}
		if ( $this->count['inactive'] ) {
			$views['inactive'] = $this->view_markup( 'inactive', __( 'Inactive', 'insert-headers-and-footers' ) );
		}
		if ( $this->count['trash'] && ! wpcode_testing_mode_enabled() ) {
			$views['trash'] = $this->view_markup( 'trash', __( 'Trash', 'insert-headers-and-footers' ) );
		}
		if ( $this->count['has_error'] ) {
			$views['has_error'] = $this->view_markup( 'has_error', __( 'Errors', 'insert-headers-and-footers' ) );
		}

		return $views;
	}

	/**
	 * Get view link markup for the nav above the table.
	 *
	 * @param string $slug The slug of the view.
	 * @param string $label The label for the view.
	 *
	 * @return string
	 */
	private function view_markup( $slug, $label ) {
		$base_url = remove_query_arg(
			array(
				'view',
				'trashed',
				'duplicated',
				'untrashed',
				'deleted',
				'enabled',
				'disabled',
				's',
			)
		);
		$url      = 'all' === $slug ? remove_query_arg( 'view', $base_url ) : add_query_arg( 'view', $slug, $base_url );
		$class    = $this->view === $slug ? ' class="current"' : '';
		$count    = isset( $this->count[ $slug ] ) ? $this->count[ $slug ] : 0;

		return sprintf( '<a href="%1$s"%2$s>%3$s&nbsp;<span class="count">(%4$d)</span></a>', esc_url( $url ), $class, esc_html( $label ), esc_html( $count ) );
	}

	/**
	 * Get an admin URL.
	 *
	 * @param string $path The path to append to the admin URL.
	 *
	 * @return string
	 */
	public function admin_url( $path ) {
		return admin_url( $path );
	}
}
