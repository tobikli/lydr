<?php
/**
 * A class to carryout authenticated remote actions for AyeCode Connect.
 */

/**
 * Bail if we are not in WP.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AyeCode_Connect_Remote_Actions' ) ) {

	/**
	 * The remote actions for AyeCode Connect
	 */
	class AyeCode_Connect_Remote_Actions {
		/**
		 * The title.
		 *
		 * @var string
		 */
		public $name = 'AyeCode Connect';

		public $prefix = 'ayecode_connect';

		/**
		 * The relative url to the assets.
		 *
		 * @var string
		 */
		public $url = '';

		public $client;
		public $base_url;

		/**
		 * If debuggin is enabled.
		 *
		 * @var
		 */
		public $debug = false;

		/**
		 * Holds the settings values.
		 *
		 * @var array
		 */
		private $settings;

		/**
		 * AyeCode_UI_Settings instance.
		 *
		 * @access private
		 * @since  1.0.0
		 * @var    AyeCode_Connect_Remote_Actions There can be only one!
		 */
		private static $instance = null;

		/**
		 * Main AyeCode_Connect_Remote_Actions Instance.
		 *
		 * Ensures only one instance of AyeCode_Connect_Remote_Actions is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return AyeCode_Connect_Remote_Actions - Main instance.
		 */
		public static function instance( $prefix = '', $client = '' ) {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AyeCode_Connect_Remote_Actions ) ) {
				self::$instance = new AyeCode_Connect_Remote_Actions;

				if ( $prefix ) {
					self::$instance->prefix = $prefix;
				}

				if ( $client ) {
					self::$instance->client = $client;
				}

				self::$instance->debug = defined('AC_DEBUG') && AC_DEBUG ? true : false;

				$remote_actions = array(
					'install_plugin'  => 'install_plugin',
					'update_licences' => 'update_licences',
					'install_theme'   => 'install_theme',
					'update_options'  => 'update_options',
					'import_menus'    => 'import_menus',
					'import_content'  => 'import_content',
					'remote_import_options'    => 'remote_import_options',
					'remote_import_categories' => 'remote_import_categories',
					'remote_import_templates'  => 'remote_import_templates',
					'remote_import_posts'      => 'remote_import_posts',
					'remote_import_menus'      => 'remote_import_menus'
				);

				// set php limits
				self::set_php_limits();

				/*
				 * Add any actions in the style of "{$prefix}_remote_action_{$action}"
				 */
				foreach ( $remote_actions as $action => $call ) {
					if ( ! has_action( $prefix . '_remote_action_' . $action, array(
						self::$instance,
						$call
					) )
					) {
						add_action( $prefix . '_remote_action_' . $action, array(
							self::$instance,
							$call
						), 10,2 ); // set settings
					}
				}
			}

			return self::$instance;
		}

		/**
		 * Delete the old categories.
		 *
		 * @param $cpt
		 */
		public function delete_gd_categories( $cpt ) {
			$this->debug_log( 'start', __METHOD__ . ':' . $cpt, __FILE__, __LINE__ );

			$taxonomy = $cpt.'category';

			$terms = get_terms( array(
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
			) );

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					// Maybe delete default image and logo
					$attachment_data = get_term_meta( $term->term_id, 'ct_cat_icon', true );

					if ( is_array( $attachment_data ) && ! empty( $attachment_data['id'] ) ) {
						wp_delete_attachment($attachment_data['id'], true);
					}

					$attachment_data = get_term_meta( $term->term_id, 'ct_cat_default_img', true );

					if ( is_array( $attachment_data ) && ! empty( $attachment_data['id'] ) ) {
						wp_delete_attachment($attachment_data['id'], true);
					}

					wp_delete_term( $term->term_id, $taxonomy );
				}
			}

			$this->debug_log( 'end', __METHOD__ . ':' . $cpt, __FILE__, __LINE__ );
		}

		/**
		 * Fully sanitize the category API return.
		 *
		 * @param $categories
		 * @since 1.
		 * @return array
		 */
		public function sanitize_categories( $categories ) {
			$sanitized = array();

			if ( ! empty( $categories ) ) {
				foreach ( $categories as $cpt => $cats ) {
					$cpt = sanitize_title_with_dashes( $cpt );

					if ( ! empty( $cats ) ) {
						foreach ( $cats as $key => $cat ) {
							$key = sanitize_title_with_dashes( $key );
							//$this->debug_log( $cat, __METHOD__ . ':before', __FILE__, __LINE__ );

							if ( ! empty( $cat['name'] ) ) {
								$sanitized[ $cpt ][ $key ]['name'] = sanitize_text_field( $cat['name'] );
							}

							if ( ! empty( $cat['icon'] ) ) {
								$sanitized[ $cpt ][ $key ]['icon'] = esc_url_raw( $cat['icon'] );
							}

							if ( ! empty( $cat['default_img'] ) ) {
								$sanitized[ $cpt ][ $key ]['default_img'] = esc_url_raw( $cat['default_img'] );
							}

							if ( ! empty( $cat['font_icon'] ) ) {
								$sanitized[ $cpt ][ $key ]['font_icon'] = sanitize_text_field( $cat['font_icon'] );
							}

							if ( ! empty( $cat['color'] ) ) {
								$sanitized[ $cpt ][ $key ]['color'] = sanitize_hex_color( $cat['color'] );
							}

							if ( ! empty( $cat['demo_post_id'] ) ) {
								$sanitized[ $cpt ][ $key ]['demo_post_id'] = absint( $cat['demo_post_id'] );
							}

							//$this->debug_log( $sanitized[ $cpt ][ $key ], __METHOD__ . ':after', __FILE__, __LINE__ );
						}
					}
				}
			}

			return $sanitized;
		}

		/**
		 * Import content into site.
		 *
		 * @return array
		 */
		public function import_content() {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$result = array( "success" => false );

			// validate
			if ( $this->validate_request() ) {
				// de-sanitize for mod-security
				if ( ! empty( $_REQUEST['categories'] ) ) {
					$_REQUEST['categories'] = str_replace( $this->str_replace_args( true ), $this->str_replace_args( false ), $_REQUEST['categories'] );
				}
				if ( ! empty( $_REQUEST['posts'] ) ) {
					$_REQUEST['posts'] = str_replace( $this->str_replace_args( true ), $this->str_replace_args( false ), $_REQUEST['posts'] );
				}
				if ( ! empty( $_REQUEST['pages'] ) ) {
					$_REQUEST['pages'] = str_replace( $this->str_replace_args( true ), $this->str_replace_args( false ), $_REQUEST['pages'] );
				}

				// categories
				$categories = ! empty( $_REQUEST['categories'] ) ? $this->sanitize_categories( json_decode( stripslashes( $_REQUEST['categories'] ), true ) ) : array();
				$cat_old_and_new = array();

				if ( ! empty( $categories ) && class_exists( 'GeoDir_Admin_Dummy_Data' ) ) {
					foreach ( $categories as $cpt => $cats ) {
						// delete cats
						self::delete_gd_categories($cpt);

						GeoDir_Admin_Dummy_Data::create_taxonomies( $cpt, $cats );
						$tax = new GeoDir_Admin_Taxonomies();

						// set the replacements ids
						foreach ( $cats as $cat ) {
							$term = get_term_by('name', $cat['name'], $cpt.'category');
							if ( isset( $term->term_id ) ) {
								$old_cat_id = absint( $cat['demo_post_id'] );
								$cat_old_and_new[ $old_cat_id ] = absint( $term->term_id );
							}

							// regenerate term icons
							if ( method_exists( $tax,'regenerate_term_icon' ) ) {
								$tax->regenerate_term_icon( $term->term_id );
							}
						}
					}

					update_option('_acdi_replacement_cat_ids',$cat_old_and_new);
				}

				// maybe remove dummy data
				if ( ! empty( $_REQUEST['remove_dummy_data'] ) ) {
					$post_types = geodir_get_posttypes( 'names' );

					if ( ! empty( $post_types ) ) {
						foreach ( $post_types as $post_type ) {
							$table = geodir_db_cpt_table( $post_type );
							if ( $table ) {
								geodir_add_column_if_not_exist( $table, 'post_dummy', "TINYINT(1) NULL DEFAULT '0'" );
							}

							GeoDir_Admin_Dummy_Data::delete_dummy_posts( $post_type );
						}
					}

					// delete any previous posts
					self::delete_demo_posts( 'post' );
					self::delete_demo_posts( 'attachment' );

					// maybe set page featured images
					$fi = get_option('_acdi_page_featured_images');

					if ( ! empty( $fi ) ) {
						foreach($fi as $p => $i){
							$image = (array) GeoDir_Media::get_external_media( $i, '',array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/webp'),array('ext'=>'png','type'=>'image/png') );

							if(!empty($image['url'])){
								$attachment_id = GeoDir_Media::set_uploaded_image_as_attachment($image);
								if( $attachment_id ){
									set_post_thumbnail($p,$attachment_id );// this will not set if there are dummy posts
									update_post_meta($attachment_id,'_ayecode_demo',1);
								}
							}
						}

						delete_option('_acdi_page_featured_images');
					}
				}

				// posts, note that everything is sanitised further down, wp_insert_post passes everything through sanitize_post()
				$posts = ! empty( $_REQUEST['posts'] ) ? json_decode( stripslashes( $_REQUEST['posts'] ), true ) : array();

				if ( ! empty( $posts ) && class_exists( 'GeoDir_Admin_Dummy_Data' ) ) {
					$hello_world_trashed = false;

					foreach ( $posts as $post_info ) {
						unset( $post_info['ID'] );

						$post_info['post_title'] = wp_strip_all_tags( $post_info['post_title'] ); // WP does not automatically do this
						$post_info['post_status'] = 'publish';
						$post_info['post_dummy']  = '1';
						$post_info['post_author']   = 1;
						// set post data
						$insert_result = wp_insert_post( $post_info, true ); // we hook into the save_post hook

						// maybe insert attachments
						if ( ! is_wp_error( $insert_result ) && $insert_result && ! empty( $post_info['_raw_post_images'] ) ) {
							$this->set_external_media( $insert_result, $post_info['_raw_post_images'] );
						}

						// post stuff
						if($post_info['post_type']=='post' && $insert_result){
							// maybe soft delete original hello world post
							if ( ! $hello_world_trashed ) {
								wp_delete_post(1,false);
								$hello_world_trashed = true;
							}

							// set cats
							$terms = isset($post_info['_cats']) ? $post_info['_cats'] : array();
							$post_terms = array();
							if ( ! empty( $terms ) ) {
								require_once( ABSPATH . '/wp-admin/includes/taxonomy.php');
								foreach($terms as $term_name){
									$term = get_term_by('name', $term_name, 'category');
									if(!empty($term->term_id)){
										$post_terms[] = absint($term->term_id);
									}else{
										$term_name = sanitize_title( $term_name );
										$term_id = wp_create_category($term_name);
										if ( $term_id ) {
											$post_terms[] = absint($term_id);
										}
									}
								}

								if ( ! empty( $post_terms ) ) {
									wp_set_post_categories($insert_result, $post_terms, false);
								}
							}

							// featured image
							$image_url = !empty($post_info['_featured_image_url']) ? esc_url_raw($post_info['_featured_image_url']) : '';

							if ( $image_url ) {
								$image = (array) GeoDir_Media::get_external_media( $image_url, '',array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/webp'),array('ext'=>'png','type'=>'image/png') );

								if(!empty($image['url'])){
									$attachment_id = GeoDir_Media::set_uploaded_image_as_attachment($image);
									if( $attachment_id ){
										set_post_thumbnail($insert_result,$attachment_id );
										update_post_meta($attachment_id,'_ayecode_demo',1);
									}
								}
							}
						}
					}
				}

				// page templates, note that everything is sanitised further down, wp_insert_post passes everything through sanitize_post()
				$pages = ! empty( $_REQUEST['pages'] ) ? json_decode( stripslashes( $_REQUEST['pages'] ), true ) : array();

				$featured_images_assign = array();
				$old_and_new = array();

				if ( ! empty( $pages ) && function_exists( 'geodir_get_settings' ) ) {
					// remove pages
					self::delete_demo_posts( 'page' );

					// GD page templates
					if ( ! empty( $pages['gd'] ) ) {
						foreach ( $pages['gd'] as $cpt => $page_templates ) {
							if ( ! empty( $page_templates ) ) {
								foreach ( $page_templates as $type => $page ) {
									$post_id = $this->import_page_template( $page, $type, $cpt );
									$old_id = isset($page['demo_post_id']) ? absint( $page['demo_post_id'] ) : '';
									if ( $post_id && $old_id ) {
										$old_and_new[ $old_id ] = $post_id;
									}
								}
							}
						}
					}

					// UWP page templates
					if ( ! empty( $pages['uwp'] ) ) {
						foreach ( $pages['uwp'] as $cpt => $page_templates ) {
							if ( ! empty( $page_templates ) ) {
								foreach ( $page_templates as $type => $page ) {
									$post_id = $this->import_page_template( $page, $type, $cpt );
									$old_id = isset($page['demo_post_id']) ? absint( $page['demo_post_id'] ) : '';
									if ( $post_id && $old_id ) {
										$old_and_new[ $old_id ] = $post_id;
									}
								}
							}
						}
					}

					// WP
					if ( ! empty( $pages['wp'] ) ) {
						foreach ( $pages['wp'] as $type => $page ) {
							$post_id = $this->import_page_template( $page, $type );
							$old_id = isset($page['demo_post_id']) ? absint( $page['demo_post_id'] ) : '';
							if ( $post_id && $old_id ) {
								$old_and_new[ $old_id ] = $post_id;
							}

							// featured image
							$image_url = !empty($page['_featured_image_url']) ? esc_url_raw($page['_featured_image_url']) : '';

							if ( $image_url ) {
								$featured_images_assign[$post_id] = $image_url;
							}
						}

						if ( ! empty( $featured_images_assign ) ) {
							update_option('_acdi_page_featured_images', $featured_images_assign);
						}
					}

					// Elementor @todo add check for elementor pro
					if ( ! empty( $pages['elementor'] ) ) {
						$default_kit_id = get_option( 'elementor_active_kit' );
						$new_kit_id = 0;

						delete_option( 'elementor_active_kit' );

						foreach ( $pages['elementor'] as $cpt => $page_templates ) {
							// Remove old demos
							$this->delete_demo_posts( $cpt );

							$archives    = array();
							$items       = array();
							if ( ! empty( $page_templates ) ) {
								foreach ( $page_templates as $page ) {
									$post_id = $this->import_page_template( $page, 'elementor', $cpt );

									if ( $post_id && $page['demo_post_id'] ) {
										$old_id                 = absint( $page['demo_post_id'] );
										$old_and_new[ $old_id ] = $post_id;

										// archives
										if ( ! empty( $page['meta_input']['_elementor_template_type'] ) && $page['meta_input']['_elementor_template_type'] == 'geodirectory-archive' ) {
											$archives[ $old_id ] = absint( $post_id );
										}

										// items
										if ( ! empty( $page['meta_input']['_elementor_template_type'] ) && $page['meta_input']['_elementor_template_type'] == 'geodirectory-archive-item' ) {
											$items[ $old_id ] = absint( $post_id );
										}

										// kit
										if ( ! empty( $page['meta_input']['_elementor_template_type'] ) && $page['meta_input']['_elementor_template_type'] == 'kit' ) {
											$new_kit_id = absint( $post_id );
										}
									}
								}
							}

							if ( $new_kit_id ) {
								update_option( 'elementor_active_kit', $new_kit_id);
							}

							// temp save replace ids
							update_option( '_acdi_replacement_post_ids', $old_and_new );
							update_option( '_acdi_replacement_archive_item_ids', $items );
							update_option( '_acdi_original_elementor_active_kit', $default_kit_id );

							// extras
							if ( ! empty( $old_and_new ) ) {
								// update the elementor display conditions
								$display_conditions     = get_option( 'elementor_pro_theme_builder_conditions' );
								$new_display_conditions = $display_conditions;
								if ( ! empty( $display_conditions ) ) {
									foreach ( $display_conditions as $type => $condition ) {
										if ( ! empty( $condition ) ) {
											foreach ( $condition as $id => $rule ) {
												if ( isset( $old_and_new[ $id ] ) ) {
													unset( $new_display_conditions[ $type ][ $id ] );
													$new_id                                     = absint( $old_and_new[ $id ] );
													$new_display_conditions[ $type ][ $new_id ] = $rule;
												}
											}
										}
									}
								}

								update_option( 'elementor_pro_theme_builder_conditions', $new_display_conditions );

								// check pages for replaceable data
								if ( ! empty( $old_and_new ) ) {
									foreach ( $old_and_new  as $id ) {
										$this->parse_elementor_data( $id );
									}
								}
							}
						}

						// clear elementor cache after changes
						if ( defined( 'ELEMENTOR_VERSION' ) ) {
							\Elementor\Plugin::$instance->files_manager->clear_cache();
						}
					}
				}

				// set as success
				$result = array( "success" => true );
			}

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $result;
		}

		public function parse_elementor_data( $post_id ) {
			//$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$_elementor_data = get_post_meta( $post_id, '_elementor_data', true );

			if ( ! empty( $_elementor_data ) ) {
				$old_and_new = get_option( '_acdi_replacement_post_ids' );
				$cat_old_and_new = get_option( '_acdi_replacement_cat_ids' );
				$items = get_option( '_acdi_replacement_archive_item_ids' );
				$demo_url = get_option( '_acdi_demo_url' );

				// Replace archive item ids
				$original = $_elementor_data;

				if ( ! empty( $items ) ) {
					foreach ( $items as $old_item => $new_item ) {
						$_elementor_data = str_replace(
							array('"gd_archive_custom_skin_template":"' . $old_item . '"',
								'\"gd_archive_custom_skin_template\":\"' . $old_item . '\"',
								'"gd_custom_skin_template":"' . $old_item . '"',
								'\"gd_custom_skin_template\":\"' . $old_item . '\"',
							),
							array('"gd_archive_custom_skin_template":"' . $new_item . '"',
								'\"gd_archive_custom_skin_template\":\"' . $new_item . '\"',
								'"gd_custom_skin_template":"' . $new_item . '"',
								'\"gd_custom_skin_template\":\"' . $new_item . '\"'
							),
							$_elementor_data
						);
					}
				}

				// Replace cat ids
				if ( ! empty( $cat_old_and_new ) ) {
					foreach ( $cat_old_and_new as $old_item => $new_item ) {
						$_elementor_data = str_replace(
							array(
								'taxonomy_id%22%3A%22'.$old_item.'%22',
								'taxonomy_id":"'.$old_item.'"'
							),
							array(
								'taxonomy_id%22%3A%22'.$new_item.'%22',
								'taxonomy_id":"'.$new_item.'"'
							),
							$_elementor_data
						);
					}
				}

				// Replace URL
				if ( $demo_url ) {
					$_elementor_data = str_replace(
						array(
							$demo_url,
							str_replace('/','\/', $demo_url ),
						),
						array(
							get_home_url(),
							str_replace('/','\/', get_home_url() ),
						),
						$_elementor_data
					);
				}

				if ( $original !== $_elementor_data ) {
					update_post_meta( $post_id, '_elementor_data', wp_slash( $_elementor_data ) );
				}
			}

			//$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );
		}

		/**
		 * Delete all dummy posts.
		 *
		 * @param $cpt
		 */
		public function delete_demo_posts( $cpt ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			// Elementor allow delete kit (without this it throws a confirmation page and blocks import)
			$_GET['force_delete_kit'] = 1;

			$posts = get_posts(
				array(
					'post_type'   => esc_attr( $cpt ),
					'meta_key'    => '_ayecode_demo',
					'meta_value'  => '1',
					'numberposts' => - 1
				)
			);

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $p ) {
					if ( $p->post_name != 'default-kit' ) {
						wp_delete_post( $p->ID, true );
					}
				}
			}

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );
		}

		/**
		 * Set external attachments.
		 *
		 * @param $post_id
		 * @param $files
		 */
		public function set_external_media( $post_id, $files ) {
			//$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			if ( ! empty( $files ) && class_exists( 'GeoDir_Media' ) ) {
				foreach ( $files as $file ) {
					$field        = ! empty( $file['type'] ) ? esc_attr( $file['type'] ) : 'post_images';
					$file_url     = ! empty( $file['file'] ) ? esc_url_raw( $file['file'] ) : '';
					$file_title   = ! empty( $file['title'] ) ? esc_attr( $file['title'] ) : '';
					$file_caption = ! empty( $file['caption'] ) ? esc_url_raw( $file['caption'] ) : '';
					$order        = ! empty( $file['menu_order'] ) ? absint( $file['menu_order'] ) : '';
					$other_id     = '';
					$approved     = ! empty( $file['is_approved'] ) ? absint( $file['is_approved'] ) : '';
					$placeholder  = true;
					$metadata     = ! empty( $file['metadata'] ) ? maybe_unserialize( $file['metadata'] ) : '';

					if ( ! empty( $metadata ) && is_array( $metadata ) && $file_url && ( geodir_is_full_url( $file_url ) || strpos( $file_url, '#' ) === 0 ) ) {
						if ( isset( $metadata['file'] ) ) {
							unset( $metadata['file'] );
						}

						if ( isset( $metadata['sizes'] ) ) {
							unset( $metadata['sizes'] );
						}
					}

					GeoDir_Media::insert_attachment( $post_id, $field, $file_url, $file_title, $file_caption, $order, $approved, $placeholder, $other_id, $metadata );
				}
			}

			//$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );
		}

		/**
		 * Import page templates.
		 *
		 * @param $page_template
		 * @param string $type
		 * @param string $cpt
		 *
		 * @return int|WP_Error
		 */
		public function import_page_template( $page_template, $type = '', $cpt = '' ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			/*
			 * The API can't insert unfiltered HTML which is needed for some page builders, so we allow this here and add the filters back at the end.
			 */
			kses_remove_filters();

			$settings = geodir_get_settings();

			// Some meta data may need to be unserialized
			$page_template = (array) $page_template;

			if ( ! empty( $page_template['meta_input'] ) ) {
				foreach ( $page_template['meta_input'] as $key => $val ) {
					// Elementor json needs slashed
					if ( $key != '_elementor_data' ) {
						$val = wp_unslash( $val );
					}

					$page_template['meta_input'][$key] = maybe_unserialize( $val );
				}
			}

			$post_id  = 0;

			if ( ! empty( $page_template['post_title'] ) ) {
				$this->debug_log( $page_template['post_title'], __METHOD__ . ':' . $type, __FILE__, __LINE__ );
			}

			if ( $type == 'elementor' ) {
				$page_template['post_title']   = wp_strip_all_tags( $page_template['post_title'] );
				$page_template['post_author']   = 1;
				$page_template['post_type']   = $cpt;
				$page_template['post_status'] = 'publish';

				$post_id = wp_insert_post( $page_template, true );

				if ( is_wp_error( $post_id ) ) {
					$this->debug_log( $post_id->get_error_message(), __METHOD__ . ':wp_insert_post error', __FILE__, __LINE__ );
				} else {
					$this->debug_log( $post_id, __METHOD__ . ':wp_insert_post', __FILE__, __LINE__ );
				}

				// maybe set tax (not working from wp_insert_post)
				if ( $post_id && ! empty( $page_template['tax_input'] ) ) {
					// Default kit
					if ( ! empty( $page_template['meta_input']['active_kit'] ) ) {
						update_option( 'elementor_active_kit', $post_id );
					}

					if ( ! function_exists( 'wp_create_term' ) ) {
						include_once ABSPATH . 'wp-admin/includes/taxonomy.php';
					}

					foreach ( $page_template['tax_input'] as $tax => $slug ) {
						$tax  = sanitize_title_with_dashes( $tax );
						$slug = sanitize_title_with_dashes( $slug );
						wp_set_object_terms( $post_id, $slug, $tax );
					}
				}
			} elseif ( $type && $cpt ) {
				$type = sanitize_title_with_dashes( $type );
				$cpt  = sanitize_title_with_dashes( $cpt );

				// GD
				$page_templates = array(
					'page_add',
					'page_search',
					'page_terms_conditions',
					'page_location',
					'page_archive',
					'page_archive_item',
					'page_details',
				);

				if ( in_array( $type, $page_templates ) ) {
					$page_template = (array) $page_template;
					$current_page_id = 0;

					if ( $cpt == 'core' ) {
						$current_page_id = ! empty( $settings[ $type ] ) ? absint( $settings[ $type ] ) : 0;
					} else {
						$current_page_id = ! empty( $settings['post_types'][ $cpt ][ $type ] ) ? absint( $settings['post_types'][ $cpt ][ $type ] ) : 0;
					}

					if ( false === get_post_status( $current_page_id ) ) {
						// We create a new page
					} else {
						// Send to trash
						wp_delete_post( absint( $current_page_id ), false );
					}

					$page_template['post_title']   = wp_strip_all_tags( $page_template['post_title'] );
					$page_template['post_type']   = 'page';
					$page_template['post_status'] = 'publish';
					$page_template['post_author'] = 1;
					$post_id                      = wp_insert_post( $page_template, true );

					if ( ! is_wp_error( $post_id ) && $post_id ) {
						if ( $cpt == 'core' ) {
							geodir_update_option( $type, $post_id );
						} else {
							$settings['post_types'][ $cpt ][ $type ] = $post_id;
							geodir_update_option( 'post_types', $settings['post_types'] );
						}
					}
				}

				// UWP
				$page_templates = array(
					'register_page',
					'login_page',
					'account_page',
					'forgot_page',
					'reset_page',
					'change_page',
					'profile_page',
					'users_page',
					'user_list_item_page',
				);

				if ( function_exists( 'uwp_get_settings' ) && in_array( $type, $page_templates ) ) {
					$settings = uwp_get_settings();
					$page_template = (array) $page_template;
					$current_page_id = 0;

					if ( $cpt == 'core' ) {
						$current_page_id = ! empty( $settings[ $type ] ) ? absint( $settings[ $type ] ) : 0;
					}

					if ( false === get_post_status( $current_page_id ) ) {
						// We create a new page
					} else {
						// Send to trash
						wp_delete_post( absint( $current_page_id ), false );
					}

					$page_template['post_title']   = wp_strip_all_tags( $page_template['post_title'] );
					$page_template['post_type']   = 'page';
					$page_template['post_status'] = 'publish';
					$page_template['post_author'] = 1;
					$post_id                      = wp_insert_post( $page_template, true );

					if ( ! is_wp_error( $post_id ) && $post_id ) {
						if ( $cpt == 'core' ) {
							uwp_update_option( $type, $post_id );
						}
					}
				}
			} elseif ( $type == 'page_on_front' ) {
				$current_page_id = get_option( 'page_on_front' );

				if ( false === get_post_status( $current_page_id ) ) {
					// We create a new page
				} else {
					// Send to trash
					wp_delete_post( absint( $current_page_id ), false );
				}

				$page_template['post_title']   = wp_strip_all_tags( $page_template['post_title'] );
				$page_template['post_type']   = 'page';
				$page_template['post_status'] = 'publish';
				$page_template['post_author'] = 1;

				$post_id = wp_insert_post( $page_template, true );

				if ( ! is_wp_error( $post_id ) && $post_id ) {
					update_option( 'show_on_front', 'page' );
					update_option( 'page_on_front', $post_id );
				}
			} elseif ( $type && $cpt == '' ) {
				$page_template['post_title']   = wp_strip_all_tags( $page_template['post_title'] );
				$page_template['post_type']   = 'page';
				$page_template['post_status'] = 'publish';
				$page_template['post_author'] = 1;
				$post_id = wp_insert_post( $page_template, true );

				if ( ! empty( $page_template['meta_input']['_page_for_posts'] ) ) {
					update_option( 'page_for_posts', $post_id );
				}
			}

			// We add back the filters for security
			kses_init_filters();

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $post_id;
		}

		/**
		 * Import menus.
		 *
		 * @return array
		 */
		public function import_menus() {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$result = array( "success" => false );

			// validate
			if ( $this->validate_request() ) {

				// note, everything is sanitized in import_menu()
				$menus = ! empty( $_REQUEST['menus'] ) ? wp_unslash( $_REQUEST['menus'] ) : array();

				if ( ! empty( $menus ) ) {
					foreach ( $menus as $location => $menu ) {
						$import = $this->import_menu( $location, $menu );
					}
				}

				// set as success
				$result = array( "success" => true );
			}

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $result;
		}

		/**
		 * Import menu.
		 *
		 * @param $location
		 * @param $menu
		 *
		 * @return bool
		 */
		public function import_menu( $location, $menu ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$result = false;

			if ( ! empty( $menu ) ) {
				$name = sanitize_title( $menu['name'] );

				// Does the menu exist already?
				$menu_exists = wp_get_nav_menu_object( $name );

				// if it exists with the exact name then lets delete it
				if ( $menu_exists ) {
					wp_delete_nav_menu( $name );
					$menu_exists = false;
				}

				// If it doesn't exist, let's create it.
				if ( ! $menu_exists ) {

					$old_and_new = get_option('_acdi_replacement_post_ids');

					$menu_id = wp_create_nav_menu( $name );

					$locations = get_theme_mod( 'nav_menu_locations' );

					if ( $menu_id ) {
						$locations[ $location ] = $menu_id;
						set_theme_mod( 'nav_menu_locations', $locations );

						if ( ! empty( $menu['items'] ) ) {
							$menu_ids   = array();
							$parent_ids = array();
							foreach ( $menu['items'] as $item ) {
								// unset some things
								$p           = $item['post'];
								$metas       = $item['post_metas'];
								$original_id = absint( $p['ID'] );
								$p['post_author']   = 1;
								unset( $p['ID'] );
								$db_id = wp_insert_post( $p );

								// set id relations
								$menu_ids[ $original_id ] = $db_id;

								if ( $menu_id ) {
									// Associate the menu item with the menu term.
									wp_set_object_terms( $db_id, array( $menu_id ), 'nav_menu' );

									// set meta items
									if ( ! empty( $metas ) ) {
										foreach ( $metas as $key => $meta ) {
											$meta = maybe_unserialize( $meta[0] );
											if ( is_array( $meta ) ) {
												$meta = implode( " ", $meta );
											}

											// set the correct id
											if ( $key == '_menu_item_object_id' ) {
												if($original_id == $meta){
													$meta = absint( $db_id );
												}

												// maybe replace page ids
												if ( ! empty( $old_and_new ) ) {
													foreach ( $old_and_new as $old => $new ) {
														if($meta == $old){
															$meta = $new;
														}
													}
												}

											}

											// set correct parent id
											if ( $key == '_menu_item_menu_item_parent' && ! empty( $meta ) ) {
												$parent_ids[ $db_id ] = absint( $meta );
											}

											// set the correct url for add listing pages
											if ( $key == '_menu_item_url' && ! empty( $meta ) && strpos( $meta, 'listing_type=gd_' ) !== false && function_exists( 'geodir_add_listing_page_url' ) ) {
												$url_parts = explode( "=", $meta );
												if ( ! empty( $url_parts[1] ) ) {
													$meta = geodir_add_listing_page_url( esc_attr( $url_parts[1] ) );
												}

											}

											update_post_meta( $db_id, sanitize_title_with_dashes( $key ), wp_strip_all_tags( $meta ) );
										}
									}
								}

							}

							// set parent ids after insert
							if ( ! empty( $parent_ids ) ) {
								foreach ( $parent_ids as $id => $p_id ) {
									$n_id = ! empty( $menu_ids[ $p_id ] ) ? absint( $menu_ids[ $p_id ] ) : 0;
									if ( $n_id ) {
										update_post_meta( $id, '_menu_item_menu_item_parent', $n_id );
									}
								}
							}
						}
					}
				}
			}

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $result;
		}

		/**
		 * Update site options.
		 *
		 * @return array
		 */
		public function update_options() {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$result = array( "success" => false );

			// validate
			if ( $this->validate_request() ) {
				// de-sanitize for mod-security
				if ( ! empty( $_REQUEST['update'] ) ) {
					$_REQUEST['update'] = str_replace( $this->str_replace_args( true ), $this->str_replace_args( false ), $_REQUEST['update'] );
				}
				if ( ! empty( $_REQUEST['merge'] ) ) {
					$_REQUEST['merge'] = str_replace( $this->str_replace_args( true ), $this->str_replace_args( false ), $_REQUEST['merge'] );
				}
				if ( ! empty( $_REQUEST['delete'] ) ) {
					$_REQUEST['delete'] = str_replace( $this->str_replace_args( true ), $this->str_replace_args( false ), $_REQUEST['delete'] );
				}
				if ( ! empty( $_REQUEST['geodirectory_settings'] ) ) {
					$_REQUEST['geodirectory_settings'] = str_replace( $this->str_replace_args( true ), $this->str_replace_args( false ), $_REQUEST['geodirectory_settings'] );
				}


				// update
				$options = ! empty( $_REQUEST['update'] ) ? json_decode( stripslashes( $_REQUEST['update'] ), true ) : array();

				if ( ! empty( $options ) ) {
					foreach ( $options as $key => $option ) {

						if($key=='custom_css'){
							$option = wp_strip_all_tags( $option );
							$post_css = wp_update_custom_css_post($option);
							if(isset($post_css->ID)){
								set_theme_mod( 'custom_css_post_id', $post_css->ID );
							}
						}


						// theme logo
						if(isset($option['custom_logo_src'])){
							$image = (array) GeoDir_Media::get_external_media( esc_url_raw( $option['custom_logo_src'] ), '',array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/webp'),array('ext'=>'png','type'=>'image/png') );

							if(!empty($image['url'])){
								$attachment_id = GeoDir_Media::set_uploaded_image_as_attachment($image);
								if( $attachment_id ){
									update_post_meta($attachment_id,'_ayecode_demo_img',1);
									$option['custom_logo'] = $attachment_id;
								}
							}

						}

						if( $this->can_modify_option( $key ) ) {
							update_option( sanitize_title_with_dashes( $key ), $option );
						}
					}

				}

				// merge
				$options = ! empty( $_REQUEST['merge'] ) ? json_decode( stripslashes( $_REQUEST['merge'] ), true ) : array();
				if ( ! empty( $options ) ) {
					foreach ( $options as $key => $option ) {

						$key     = sanitize_title_with_dashes( $key );
						$current = get_option( $key );

						if( $this->can_modify_option( $key ) ) {
							if ( ! empty( $current ) && is_array( $current ) ) {
								update_option( sanitize_title_with_dashes( $key ), array_merge( $current, $option ) );
							} else {
								update_option( sanitize_title_with_dashes( $key ), $option );
							}
						}

					}
				}

				// delete
				$options = ! empty( $_REQUEST['delete'] ) ? json_decode( stripslashes( $_REQUEST['delete'] ), true ) : array();
				if ( ! empty( $options ) ) {
					foreach ( $options as $key => $option ) {
						$key = sanitize_title_with_dashes( $key );
						if( $this->can_modify_option( $key ) ){
							delete_option( $key );
						}
					}
				}


				// GD Settings. Sanitized in save functions
				$settings = ! empty( $_REQUEST['geodirectory_settings'] ) ? json_decode( stripslashes( $_REQUEST['geodirectory_settings'] ), true ) : array();

				if ( ! empty( $settings ) ) {

					// run the create tables function to add our new columns.
					if ( class_exists( 'GeoDir_Admin_Install' ) ) {
						global $geodir_options;
						$geodir_options = geodir_get_settings(); // we need to update the global settings values with the new values.
						GeoDir_Admin_Install::create_tables();

					}

					$this->import_geodirectory_settings( $settings );
				}

				// set as success
				$result = array( "success" => true );
			}

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $result;
		}

		/**
		 * Check if a options key is allowed to be modified.
		 *
		 * @param $key
		 * @since 1.2.6
		 * @return bool
		 */
		public function can_modify_option( $key ){
			$can_modify = false;

			$white_list = array(
				'ayecode-ui-settings',
				'aui_options',
				'custom_css',
				'geodir_settings',
				'widget_block',
				'sidebars_widgets'
			);

			if ( defined( 'ELEMENTOR_VERSION' ) ) {
				$white_list[] = 'elementor_pro_theme_builder_conditions';
				$white_list[] = 'elementor_disable_color_schemes';
				$white_list[] = 'elementor_disable_typography_schemes';
			}

			if ( in_array( $key,$white_list ) || substr( $key, 0, 11 ) === "theme_mods_" || substr( $key, 0, 7 ) === "widget_" ) {
				$can_modify = true;
			}

			return $can_modify;
		}

		/**
		 * Import GeoDirectory custom table settings.
		 *
		 * @param $settings
		 */
		public function import_geodirectory_settings( $settings ) {
			global $wpdb;

			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			// custom_fields
			if ( ! empty( $settings['custom_fields'] ) && defined( 'GEODIR_CUSTOM_FIELDS_TABLE' ) ) {
				$this->debug_log( 'start', __METHOD__ . ':custom_fields', __FILE__, __LINE__ );

				// empty the table first
				$wpdb->query( "TRUNCATE TABLE " . GEODIR_CUSTOM_FIELDS_TABLE );
				$has_event_table = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}geodir_gd_event_detail'" );

				// insert
				foreach ( $settings['custom_fields'] as $custom_field ) {
					if ( ! empty( $custom_field['post_type'] ) && $custom_field['post_type'] == 'gd_event' && ! $has_event_table ) {
						continue;
					}

					// Create check & default package.
					if ( ! empty( $custom_field['htmlvar_name'] ) && $custom_field['htmlvar_name'] == 'package_id' && function_exists( 'geodir_pricing_default_package_id' ) ) {
						geodir_pricing_default_package_id( $custom_field['post_type'] );
					}

					// maybe unserialize and change name
					if ( ! empty( $custom_field['extra_fields'] ) ) {
						$custom_field['extra'] = maybe_unserialize( $custom_field['extra_fields'] );
					}

					// packaged key change
					if ( ! empty( $custom_field['packages'] ) ) {
						$custom_field['show_on_pkg'] = $custom_field['packages'];
					}

					unset( $custom_field['id'] );

					//$this->debug_log( $custom_field, __METHOD__ . ':custom_field', __FILE__, __LINE__ );

					$r = geodir_custom_field_save( $custom_field );
				}
				$this->debug_log( 'end', __METHOD__ . ':custom_fields', __FILE__, __LINE__ );
			}

			// sort_fields
			if ( ! empty( $settings['sort_fields'] ) && defined( 'GEODIR_CUSTOM_SORT_FIELDS_TABLE' ) ) {
				// empty the table first
				$wpdb->query( "TRUNCATE TABLE " . GEODIR_CUSTOM_SORT_FIELDS_TABLE );

				// insert
				foreach ( $settings['sort_fields'] as $sort_fields ) {
					GeoDir_Settings_Cpt_Sorting::save_custom_field( $sort_fields );
				}
			}

			// tabs
			if ( ! empty( $settings['tabs'] ) && defined( 'GEODIR_TABS_LAYOUT_TABLE' ) ) {
				// empty the table first
				$wpdb->query( "TRUNCATE TABLE " . GEODIR_TABS_LAYOUT_TABLE );

				// insert
				foreach ( $settings['tabs'] as $tab ) {
					unset( $tab['id'] );// we need insert not update
					GeoDir_Settings_Cpt_Tabs::save_tab_item( $tab );
				}
			}

			// Advanced Search
			if ( ! empty( $settings['search_fields'] ) && defined( 'GEODIR_ADVANCE_SEARCH_TABLE' ) ) {
				// empty the table first
				$wpdb->query( "TRUNCATE TABLE " . GEODIR_ADVANCE_SEARCH_TABLE );

				// insert
				foreach ( $settings['search_fields'] as $search_field ) {

					GeoDir_Adv_Search_Settings_Cpt_Search::save_field( $search_field );
				}

			}

			// price_packages
			if ( ! empty( $settings['price_packages'] ) && defined( 'GEODIR_ADVANCE_SEARCH_TABLE' ) ) {
				// not implemented yet
			}

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );
		}

		/**
		 * Update licence info.
		 *
		 * @return array
		 */
		public function update_licences() {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$result = array( "success" => false );

			// Validate
			if ( $this->validate_request() ) {
				$result    = array( "success" => true );
				$installed = ! empty( $_REQUEST['installed'] ) ? $this->sanitize_licences( $_REQUEST['installed'] ) : array();
				$all       = ! empty( $_REQUEST['all'] ) ? $this->sanitize_licences( $_REQUEST['all'], true ) : array();
				$site_id   = ! empty( $_REQUEST['site_id'] ) ? absint( $_REQUEST['site_id'] ) : '';
				$site_url  = ! empty( $_REQUEST['site_url'] ) ? esc_url_raw( $_REQUEST['site_url'] ) : '';


				// verify site_id
				if ( $site_id != get_option( $this->prefix . '_blog_id', false ) ) {
					return array( "success" => false );
				}

				// verify site_url
				if ( $site_url && get_option( $this->prefix . "_url" ) ) {
					$changed = $this->client->check_for_url_change( $site_url );
					if ( $changed ) {
						return array( "success" => false );
					}
				}

				// Update licence keys for installed addons
				if ( ! empty( $installed ) && defined( 'WP_EASY_UPDATES_ACTIVE' ) ) {
					$wpeu_admin = new External_Updates_Admin( 'ayecode-connect', AYECODE_CONNECT_VERSION );
					$wpeu_admin->update_keys( $installed );
					$result = array( "success" => true );
				}

				// add all licence keys so new addons can be installed with one click.
				if ( ! empty( $all ) && defined( 'WP_EASY_UPDATES_ACTIVE' ) ) {
					update_option( $this->prefix . "_licences", $all );
				} elseif ( isset( $_REQUEST['all'] ) ) {
					update_option( $this->prefix . "_licences", array() );
				}
			}

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $result;
		}

		/**
		 * Get an array of our valid domains.
		 *
		 * @return array
		 */
		public function get_valid_domains() {
			return array(
				'ayecode.io',
				'wpgeodirectory.com',
				'wpinvoicing.com',
				'userswp.io',
			);
		}

		/**
		 * Sanitize the array of licences.
		 *
		 * @param $licences
		 * @param bool $has_domain This indicates if the licences have another level of array key.
		 *
		 * @return array
		 */
		private function sanitize_licences( $licences, $has_domain = false ) {
			$valid_licences = array();

			if ( ! empty( $licences ) ) {

				// maybe json_decode
				if ( ! is_array( $licences ) ) {
					$licences = stripslashes_deep($licences);
					$licences = json_decode($licences,true);
				}

				if ( $has_domain ) {
					// get the array of valid domains
					$valid_domains = $this->get_valid_domains();

					foreach ( $licences as $domain => $domain_licences ) {
						// Check we have licences and the domain is valid.
						if ( ! empty( $domain_licences ) && in_array( $domain, $valid_domains ) ) {
							foreach ( $domain_licences as $plugin => $licence ) {
								$maybe_valid = (object) $this->validate_licence( $licence );
								if ( ! empty( $maybe_valid ) ) {
									$plugin                               = absint( $plugin ); // this is the plugin product id.
									$valid_licences[ $domain ][ $plugin ] = $maybe_valid;
								}
							}
						}
					}
				} else {
					foreach ( $licences as $plugin => $licence ) {
						$maybe_valid = (object) $this->validate_licence( $licence );
						if ( ! empty( $maybe_valid ) ) {
							$plugin                    = sanitize_text_field( $plugin ); // non domain this is a string
							$valid_licences[ $plugin ] = $maybe_valid;
						}
					}
				}
			}

			return $valid_licences;
		}

		/**
		 * Validate and sanitize licence info.
		 *
		 * @param $licence
		 *
		 * @return array
		 */
		private function validate_licence( $licence ) {
			$valid = array();

			if ( ! empty( $licence ) && is_array( $licence ) && ! empty( $licence['license_key'] ) ) {
				// key
				if ( isset( $licence['license_key'] ) ) {
					$valid['key'] = sanitize_title_with_dashes( $licence['license_key'] );
				}
				// status
				if ( isset( $licence['status'] ) ) {
					$valid['status'] = $this->validate_licence_status( $licence['status'] );
				}
				// download_id
				if ( isset( $licence['download_id'] ) ) {
					$valid['download_id'] = absint( $licence['download_id'] );
				}
				// price_id
				if ( isset( $licence['price_id'] ) ) {
					$valid['price_id'] = absint( $licence['price_id'] );
				}
				// payment_id
				if ( isset( $licence['payment_id'] ) ) {
					$valid['payment_id'] = absint( $licence['payment_id'] );
				}
				// expires
				if ( isset( $licence['expiration'] ) ) {
					$valid['expires'] = absint( $licence['expiration'] );
				}
				// parent
				if ( isset( $licence['parent'] ) ) {
					$valid['parent'] = absint( $licence['parent'] );
				}
				// user_id
				if ( isset( $licence['user_id'] ) ) {
					$valid['user_id'] = absint( $licence['user_id'] );
				}
			}

			return $valid;
		}

		/**
		 * Validate the licence status.
		 *
		 * @param $status
		 *
		 * @return string
		 */
		public function validate_licence_status( $status ) {

			// possible statuses
			$valid_statuses = array(
				'active',
				'inactive',
				'expired',
				'disabled',
			);

			// set empty if not a valid status
			if ( ! in_array( $status, $valid_statuses ) ) {
				$status = '';
			}

			return $status;
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

			$result = true; // @TODO validate for remote request instead from server.

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
		 * Validate a download url is from our own server: 173.208.153.114
		 *
		 * @param $url
		 *
		 * @return bool
		 */
		private function validate_download_url( $url ) {
			$result = false;

			if ( $url ) {
				$parse = parse_url( $url );

				if ( ! empty( $parse['host'] ) ) {
					$ip = gethostbyname( $parse['host'] );

					if ( $ip === "173.208.153.114" ) { // AyeCode.io Server
						$result = true;
					} else if ( $ip === "198.143.164.252" ) { // wordpress.org server
						$result = true;
					} else if ( $ip === "127.0.0.1" ) {
						//$result = true; // @todo localhost
					}
				}
			}

			return $result;
		}

		/**
		 * Install plugin.
		 *
		 * @param $result
		 *
		 * @return mixed
		 */
		public function install_plugin( $result, $request = array() ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			// Validate
			if ( ! $this->validate_request() ) {
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return array( "success" => false );
			}

			if ( ! function_exists( 'plugins_api' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); // For plugins_api.
			}

			if ( ! empty( $request ) && is_object( $request ) && is_a( $request, 'WP_REST_Request' ) ) {
				$params = $request->get_params();
			} else {
				$params = $_REQUEST;
			}

			$plugin_slug = isset( $params['slug'] ) ? sanitize_title_for_query( $params['slug'] ) : '';

			$plugin      = array(
				'name'             => isset( $params['name'] ) ? esc_attr( $params['name'] ) : '',
				'repo-slug'        => $plugin_slug,
				'file-slug'        => isset( $params['file-slug'] ) ? sanitize_title_for_query( $params['file-slug'] ) : '',
				'download_link'    => isset( $params['download_link'] ) ? esc_url_raw( $params['download_link'] ) : '',
				'activate'         => isset( $params['activate'] ) && $params['activate'] ? true : false,
				'network_activate' => isset( $params['network_activate'] ) && $params['network_activate'] ? true : false,
			);

			if ( empty( $plugin['repo-slug'] ) ) {
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return array( "success" => false );
			}

			$install = $this->background_installer( $plugin_slug, $plugin );

			if ( is_wp_error( $install ) ) {
				$this->debug_log( $install->get_error_message(), __METHOD__ . ':background_installer error', __FILE__, __LINE__ );

				$result = array( "success" => false, 'error' => $install->get_error_message() );
			} else if ( $install ) {
				$result = array( "success" => true );
			}

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $result;
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
			//$slug = explode( '.', end( $slug ) ); // @todo We use plugin folder as slug, so it breaks when plugin folder is renamed.

			return $slug[0];
		}

		/**
		 * Install a plugin from .org in the background via a cron job (used by
		 * installer - opt in).
		 *
		 * @param string $plugin_to_install_id
		 * @param array $plugin_to_install
		 *
		 * @since 2.6.0
		 *
		 * @return bool
		 */
		public function background_installer( $plugin_to_install_id, $plugin_to_install ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );
			$this->debug_log( $plugin_to_install_id, __METHOD__ . ':plugin_to_install_id', __FILE__, __LINE__ );
			$this->debug_log( $plugin_to_install, __METHOD__ . ':plugin_to_install', __FILE__, __LINE__ );

			$task_result = false;
			$error = false;

			if ( ! empty( $plugin_to_install['repo-slug'] ) ) {
				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
				}
				if ( ! function_exists( 'plugins_api' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
				}
				if ( ! class_exists( 'WP_Upgrader' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
				}
				if ( ! function_exists( 'get_plugins' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}

				WP_Filesystem();

				$skin              = new Automatic_Upgrader_Skin;
				$upgrader          = new WP_Upgrader( $skin );
				$installed_plugins = array_map( array( $this, 'format_plugin_slug' ), array_keys( get_plugins() ) );
				$plugin_slug       = $plugin_to_install['repo-slug'];
				$plugin_file_slug  = ! empty( $plugin_to_install['file-slug'] ) ? $plugin_to_install['file-slug'] : $plugin_slug;
				$plugin            = $plugin_slug . '/' . $plugin_file_slug . '.php';
				$installed         = false;
				$activate          = isset( $plugin_to_install['activate'] ) && $plugin_to_install['activate'] ? true : false;
				$network_activate  = isset( $plugin_to_install['network_activate'] ) && $plugin_to_install['network_activate'] ? true : false;

				// See if the plugin is installed already
				if ( in_array( $plugin_to_install['repo-slug'], $installed_plugins ) ) {
					$installed = true;
				}

				$this->debug_log( $plugin, __METHOD__ . ':plugin', __FILE__, __LINE__ );
				$this->debug_log( $installed, __METHOD__ . ':installed', __FILE__, __LINE__ );
				$this->debug_log( $activate, __METHOD__ . ':activate', __FILE__, __LINE__ );

				// Install this thing!
				if ( ! $installed ) {
					// Suppress feedback
					ob_start();

					try {
						// if a download link is provided then validate it.
						if ( ! empty( $plugin_to_install['download_link'] ) ) {
							if ( ! $this->validate_download_url( $plugin_to_install['download_link'] ) ) {
								ob_end_clean();

								return new WP_Error( 'download_invalid', __( "Download source not valid.", "ayecode-connect" ) );
							}

							$plugin_information = (object) array(
								'name'          => esc_attr( $plugin_to_install['name'] ),
								'slug'          => esc_attr( $plugin_to_install['repo-slug'] ),
								'download_link' => esc_url( $plugin_to_install['download_link'] ),
							);
						} else {
							$this->debug_log( $plugin_to_install['repo-slug'], __METHOD__ . ':plugin-slug', __FILE__, __LINE__ );

							$plugin_information = plugins_api( 'plugin_information', array(
								'slug'   => $plugin_to_install['repo-slug'],
								'fields' => array(
									'short_description' => false,
									'sections'          => false,
									'requires'          => false,
									'rating'            => false,
									'ratings'           => false,
									'downloaded'        => false,
									'last_updated'      => false,
									'added'             => false,
									'tags'              => false,
									'homepage'          => false,
									'donate_link'       => false,
									'author_profile'    => false,
									'author'            => false,
								),
							) );
						}

						if ( is_wp_error( $plugin_information ) ) {
							$this->debug_log( $plugin_information->get_error_message(), __METHOD__ . ':plugins_api error', __FILE__, __LINE__ );
							$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

							ob_end_clean();

							return $plugin_information;
						}

						//$this->debug_log( $plugin_information, __METHOD__ . ':plugin_information', __FILE__, __LINE__ );

						$package = $plugin_information->download_link;
						$this->debug_log( $package, __METHOD__ . ':' . $plugin_to_install['repo-slug'] . ':download_link', __FILE__, __LINE__ );

						$upgrader->init();
						$download = $upgrader->download_package( $package );

						if ( is_wp_error( $download ) ) {
							$this->debug_log( $download->get_error_message(), __METHOD__ . ':download_package error', __FILE__, __LINE__ );
							$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

							ob_end_clean();

							return $download;
						}

						$this->debug_log( 'plugin-downloaded', __METHOD__, __FILE__, __LINE__ );

						$working_dir = $upgrader->unpack_package( $download, true );

						if ( is_wp_error( $working_dir ) ) {
							//throw new Exception( $working_dir->get_error_message() );
							ob_end_clean();

							$this->debug_log( $working_dir->get_error_message(), __METHOD__ . ':unpack_package error', __FILE__, __LINE__ );
							$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

							return $working_dir;
						}

						$result = $upgrader->install_package( array(
							'source'                      => $working_dir,
							'destination'                 => WP_PLUGIN_DIR,
							'clear_destination'           => false,
							'abort_if_destination_exists' => false,
							'clear_working'               => true,
							'hook_extra'                  => array(
								'type'   => 'plugin',
								'action' => 'install'
							)
						) );

						//$this->debug_log( $result, __METHOD__ . ':plugin-install', __FILE__, __LINE__ );

						if ( is_wp_error( $result ) ) {
							ob_end_clean();

							$this->debug_log( $result->get_error_message(), __METHOD__ . ':install_package error', __FILE__, __LINE__ );
							$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

							return $result;
						} else {
							$task_result = true;
						}
					} catch ( Exception $e ) {
						ob_end_clean();

						$this->debug_log( $e->getMessage(), __METHOD__ . ':plugin_to_install error', __FILE__, __LINE__ );
						$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

						return new WP_Error( 'plugin_install_fail', __METHOD__ . ' - ' . $e->getMessage() );
					}

					// Discard feedback
					ob_end_clean();
				}

				wp_clean_plugins_cache();

				// Activate this thing
				if ( $activate ) {
					try {
						$this->debug_log( $plugin, __METHOD__ . ':activate_plugin', __FILE__, __LINE__ );

						$result = activate_plugin( $plugin, "", $network_activate );

						//$this->debug_log( $result, __METHOD__ . ':plugin-activate', __FILE__, __LINE__ );

						if ( is_wp_error( $result ) ) {
							$task_result = $result;
						} else {
							$task_result = true;
						}
					} catch ( Exception $e ) {
						$this->debug_log( $e->getMessage(), __METHOD__ . ':activate_plugin error', __FILE__, __LINE__ );

						$task_result =  new WP_Error( 'activate_plugin_fail', __METHOD__ . ' - ' . $e->getMessage() );
					}
				}
			}

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $task_result;
		}

		/**
		 * Install theme.
		 *
		 * @param $result
		 *
		 * @return mixed
		 */
		public function install_theme( $result ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			// Validate
			if ( ! $this->validate_request() ) {
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return array( "success" => false );
			}

			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}

			if ( ! class_exists( 'WP_Upgrader' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
			}

			if ( ! function_exists( 'themes_api' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/theme.php' );
			}

			$slug          = isset( $_REQUEST['slug'] ) ? sanitize_title_for_query( $_REQUEST['slug'] ) : '';
			$download_link = ! empty( $_REQUEST['download_link'] ) ? esc_url_raw( $_REQUEST['download_link'] ) : '';

			$this->debug_log( $slug, __METHOD__ . ':slug', __FILE__, __LINE__ );
			$this->debug_log( $download_link, __METHOD__ . ':download_link', __FILE__, __LINE__ );

			if ( empty( $download_link ) ) {
				$api = themes_api(
					'theme_information',
					array(
						'slug'   => $slug,
						'fields' => array( 'sections' => false ),
					)
				);

				$this->debug_log( $api, __METHOD__ . ':' . $slug . ':themes_api', __FILE__, __LINE__ );

				if ( is_wp_error( $api ) ) {
					$this->debug_log( $api->get_error_message(), __METHOD__ . ':themes_api error', __FILE__, __LINE__ );

					$result = array( "success" => false );
				} else if ( ! empty( $api->download_link ) ) {
					$download_link = $api->download_link;
				}
			}

			$this->debug_log( $download_link, __METHOD__ .  ':' . $slug . ':download_link', __FILE__, __LINE__ );

			if ( ! empty( $download_link ) ) {
				WP_Filesystem();

				$skin      = new WP_Ajax_Upgrader_Skin();
				$upgrader  = new Theme_Upgrader( $skin );
				$install   = $upgrader->install( $download_link );

				if ( is_wp_error( $install ) ) {
					$error = $install->get_error_message();
				} elseif ( is_wp_error( $skin->result ) ) {
					$error = $skin->result->get_error_message();
				} elseif ( $skin->get_errors()->has_errors() ) {
					$error = $skin->get_error_messages();
				} elseif ( is_null( $result ) ) {
					global $wp_filesystem;

					// Pass through the error from WP_Filesystem if one was raised.
					if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors() ) {
						$error = esc_html( $wp_filesystem->errors->get_error_message() );
					} else {
						$error = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'ayecode-connect' );
					}
				} else {
					$error = false;
				}

				if ( $error ) {
					$upgrade_messages = $skin->get_upgrade_messages();

					if ( ! empty( $upgrade_messages ) ) {
						$_error = count( $upgrade_messages ) > 1 ? implode( " ", array_slice( $upgrade_messages, -2, 2, true ) ) : $upgrade_messages[0];
						$error .= ' ' . str_replace( $error, "", $_error );
					}

					$this->debug_log( $error, __METHOD__ . ':' . $slug . ':install error', __FILE__, __LINE__ );

					$result =  new WP_Error( 'install_theme_fail', $error );
				} else {
					$result = array( "success" => true );
				}
			}

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $result;
		}

		/**
		 * Update settings.
		 *
		 * @param $result
		 * @param $request
		 *
		 * @return mixed
		 */
		public function remote_import_options( $result = array(), $request = array() ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			if ( empty( $result ) ) {
				$result = array( 'success' => false );
			}

			// Validate
			if ( ! $this->validate_request() ) {
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return $result;
			}

			if ( ! empty( $request ) && is_object( $request ) && is_a( $request, 'WP_REST_Request' ) ) {
				$params = $request->get_params();
			} else {
				$params = array();
			}
			//$this->debug_log( $update_options, __METHOD__ . ' - params', __FILE__, __LINE__ );

			$update_options = ! empty( $params['update'] ) ? $params['update'] : array();
			$merge_options = ! empty( $params['merge'] ) ? $params['merge'] : array();
			$delete_options = ! empty( $params['delete'] ) ? $params['delete'] : array();
			$geodir_settings = ! empty( $params['geodirectory_settings'] ) ? $params['geodirectory_settings'] : array();

			$errors = array();

			// Update WP options.
			if ( ! empty( $update_options ) ) {
				foreach ( $update_options as $option_key => $option_value ) {
					if ( $option_key == 'custom_css' ) {
						$option_value = wp_strip_all_tags( $option_value );

						$post_css = wp_update_custom_css_post( $option_value );

						if ( ! empty( $post_css ) && isset( $post_css->ID ) ) {
							set_theme_mod( 'custom_css_post_id', $post_css->ID );
						}
					}

					// Theme logo
					if ( is_array( $option_value ) && isset( $option_value['custom_logo_src'] ) ) {
						$image = (array) GeoDir_Media::get_external_media( esc_url_raw( $option_value['custom_logo_src'] ), '', array( 'image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/webp', 'image/svg' ), array( 'ext' => 'png', 'type' => 'image/png' ) );

						if ( is_wp_error( $image ) ) {
							$errors['update'][ $option_key ] = $image->get_error_message();
						} elseif ( is_array( $image ) && ! empty( $image['url'] ) ) {
							$attachment_id = GeoDir_Media::set_uploaded_image_as_attachment( $image );

							if ( is_wp_error( $attachment_id ) ) {
								$errors['update'][ $option_key ] = $attachment_id->get_error_message();
							} elseif ( $attachment_id ) {
								update_post_meta( $attachment_id, '_ayecode_demo_img', 1 );

								$option_value['custom_logo'] = $attachment_id;
							}
						}
					}

					if ( $this->can_modify_option( $option_key ) ) {
						update_option( sanitize_title_with_dashes( $option_key ), $option_value );
					}
				}
			}

			// Merge WP options.
			if ( ! empty( $merge_options ) ) {
				foreach ( $merge_options as $option_key => $option_value ) {
					$option_key = sanitize_title_with_dashes( $option_key );
					$current = get_option( $option_key );

					if( $this->can_modify_option( $option_key ) ) {
						// Disable auto terms count to speedup add listing.
						if ( is_array( $option_value ) && isset( $option_value['lm_disable_term_auto_count'] ) ) {
							$option_value['lm_disable_term_auto_count'] = 1;
						}

						if ( ! empty( $current ) && is_array( $current ) ) {
							update_option( $option_key, array_merge( $current, $option_value ) );
						} else {
							update_option( $option_key, $option_value );
						}
					}
				}
			}

			// Delete WP options
			if ( ! empty( $delete_options ) ) {
				foreach ( $delete_options as $option_key => $option_value ) {
					$option_key = sanitize_title_with_dashes( $option_key );

					if ( $this->can_modify_option( $option_key ) ){
						delete_option( $option_key );
					}
				}
			}

			// GD Settings.
			if ( ! empty( $geodir_settings ) ) {
				// Run the create tables function to add our new columns.
				if ( class_exists( 'GeoDir_Admin_Install' ) ) {
					global $geodir_options;

					$geodir_options = geodir_get_settings(); // We need to update the global settings values with the new values.

					GeoDir_Admin_Install::create_tables();
				}

				$this->import_geodirectory_settings( $geodir_settings );
			}

			$result = array( 'success' => true, 'errors' => $errors );

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $result;
		}

		/**
		 * Import categories.
		 *
		 * @param $result
		 * @param $request
		 *
		 * @return mixed
		 */
		public function remote_import_categories( $result = array(), $request = array() ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			if ( empty( $result ) ) {
				$result = array( 'success' => false );
			}

			// Validate
			if ( ! $this->validate_request() ) {
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return $result;
			}

			if ( ! empty( $request ) && is_object( $request ) && is_a( $request, 'WP_REST_Request' ) ) {
				$params = $request->get_params();
			} else {
				$params = array();
			}

			$errors = array();
			$categories = ! empty( $params['categories'] ) ? $this->sanitize_categories( $params['categories'] ) : array();
			//$this->debug_log( $categories, __METHOD__ . ' - categories', __FILE__, __LINE__ );

			// Import Categories.
			if ( ! empty( $categories ) && class_exists( 'GeoDir_Admin_Dummy_Data' ) ) {
				foreach ( $categories as $cpt => $cats ) {
					self::delete_gd_categories( $cpt );

					GeoDir_Admin_Dummy_Data::create_taxonomies( $cpt, $cats );

					$taxonomy = new GeoDir_Admin_Taxonomies();

					// Set the replacements ids
					foreach ( $cats as $cat ) {
						$term = get_term_by( 'name', $cat['name'], $cpt . 'category' );

						if ( ! empty( $term ) && isset( $term->term_id ) && ! empty( $term->term_id ) ) {
							$old_cat_id = absint( $cat['demo_post_id'] );
							$cat_old_and_new[ $old_cat_id ] = absint( $term->term_id );

							// Regenerate term icons
							if ( method_exists( $taxonomy, 'regenerate_term_icon' ) ) {
								$taxonomy->regenerate_term_icon( $term->term_id );
							}
						}
					}
				}
			}

			$result = array( 'success' => true, 'errors' => $errors );

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $result;
		}

		/**
		 * Import templates.
		 *
		 * @param $result
		 * @param $request
		 *
		 * @return mixed
		 */
		public function remote_import_templates( $result = array(), $request = array() ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			if ( empty( $result ) ) {
				$result = array( 'success' => false );
			}

			// Validate
			if ( ! $this->validate_request() ) {
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return $result;
			}

			if ( ! empty( $request ) && is_object( $request ) && is_a( $request, 'WP_REST_Request' ) ) {
				$params = $request->get_params();
			} else {
				$params = array();
			}

			$errors = array();
			$pages = ! empty( $params['templates'] ) ? $params['templates'] : array();
			//$this->debug_log( $pages, __METHOD__ . ' - pages', __FILE__, __LINE__ );

			if ( ! ( ! empty( $pages ) && function_exists( 'geodir_get_settings' ) ) ) {
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				$result = array( 'success' => true );

				return $result;
			}

			// Remove pages
			self::delete_demo_posts( 'page' );

			$featured_images_assign = array();
			$old_and_new = array();

			// GD page templates
			if ( ! empty( $pages['gd'] ) ) {
				$this->debug_log( count( $pages['gd'] ), __METHOD__ . ':templates:gd', __FILE__, __LINE__ );

				foreach ( $pages['gd'] as $cpt => $page_templates ) {
					if ( ! empty( $page_templates ) ) {
						foreach ( $page_templates as $type => $page ) {
							$post_id = $this->import_page_template( $page, $type, $cpt );

							$old_id = isset($page['demo_post_id']) ? absint( $page['demo_post_id'] ) : '';

							if ( $post_id && $old_id ) {
								$old_and_new[ $old_id ] = $post_id;
							}
						}
					}
				}
			}

			// UWP page templates
			if ( ! empty( $pages['uwp'] ) ) {
				$this->debug_log( count( $pages['uwp'] ), __METHOD__ . ':templates:uwp', __FILE__, __LINE__ );

				foreach ( $pages['uwp'] as $cpt => $page_templates ) {
					if ( ! empty( $page_templates ) ) {
						foreach ( $page_templates as $type => $page ) {
							$post_id = $this->import_page_template( $page, $type, $cpt );

							$old_id = isset($page['demo_post_id']) ? absint( $page['demo_post_id'] ) : '';

							if ( $post_id && $old_id ) {
								$old_and_new[ $old_id ] = $post_id;
							}
						}
					}
				}
			}

			// WP
			if ( ! empty( $pages['wp'] ) ) {
				$this->debug_log( count( $pages['wp'] ), __METHOD__ . ':templates:wp', __FILE__, __LINE__ );

				foreach ( $pages['wp'] as $type => $page ) {
					$post_id = $this->import_page_template( $page, $type );

					$old_id = isset($page['demo_post_id']) ? absint( $page['demo_post_id'] ) : '';

					if ( $post_id && $old_id ) {
						$old_and_new[ $old_id ] = $post_id;
					}

					// Featured image
					$image_url = ! empty( $page['_featured_image_url'] ) ? esc_url_raw( $page['_featured_image_url'] ) : '';

					if ( $image_url ) {
						$featured_images_assign[$post_id] = $image_url;
					}
				}

				if ( ! empty( $featured_images_assign ) ) {
					update_option( '_acdi_page_featured_images', $featured_images_assign );
				}
			}

			// Elementor @todo add check for elementor pro
			if ( ! empty( $pages['elementor'] ) && defined( 'ELEMENTOR_VERSION' ) ) {
				$this->debug_log( count( $pages['elementor'] ), __METHOD__ . ':templates:elementor', __FILE__, __LINE__ );
				$default_kit_id = get_option( 'elementor_active_kit' );
				$new_kit_id = 0;

				delete_option( 'elementor_active_kit' );

				foreach ( $pages['elementor'] as $cpt => $page_templates ) {
					// Remove old demos
					$this->delete_demo_posts( $cpt );

					$archives = array();
					$items = array();

					if ( ! empty( $page_templates ) ) {
						foreach ( $page_templates as $page ) {
							$post_id = $this->import_page_template( $page, 'elementor', $cpt );

							if ( $post_id && $page['demo_post_id'] ) {
								$old_id = absint( $page['demo_post_id'] );
								$old_and_new[ $old_id ] = $post_id;

								// Archives
								if ( ! empty( $page['meta_input']['_elementor_template_type'] ) && $page['meta_input']['_elementor_template_type'] == 'geodirectory-archive' ) {
									$archives[ $old_id ] = absint( $post_id );
								}

								// Items
								if ( ! empty( $page['meta_input']['_elementor_template_type'] ) && $page['meta_input']['_elementor_template_type'] == 'geodirectory-archive-item' ) {
									$items[ $old_id ] = absint( $post_id );
								}

								// Kit
								if ( ! empty( $page['meta_input']['_elementor_template_type'] ) && $page['meta_input']['_elementor_template_type'] == 'kit' ) {
									$new_kit_id = absint( $post_id );
								}
							}
						}
					}

					if ( $new_kit_id ) {
						update_option( 'elementor_active_kit', $new_kit_id);
					}

					// Temp save replace ids
					update_option( '_acdi_replacement_archive_item_ids', $items );
					update_option( '_acdi_original_elementor_active_kit', $default_kit_id );

					// Extras
					if ( ! empty( $old_and_new ) ) {
						// Update the elementor display conditions
						$display_conditions = get_option( 'elementor_pro_theme_builder_conditions' );
						$new_display_conditions = $display_conditions;

						if ( ! empty( $display_conditions ) ) {
							foreach ( $display_conditions as $type => $condition ) {
								if ( ! empty( $condition ) ) {
									foreach ( $condition as $id => $rule ) {
										if ( isset( $old_and_new[ $id ] ) ) {
											unset( $new_display_conditions[ $type ][ $id ] );
											$new_id = absint( $old_and_new[ $id ] );
											$new_display_conditions[ $type ][ $new_id ] = $rule;
										}
									}
								}
							}
						}

						update_option( 'elementor_pro_theme_builder_conditions', $new_display_conditions );

						// Check pages for replaceable data
						if ( ! empty( $old_and_new ) ) {
							foreach ( $old_and_new  as $id ) {
								$this->parse_elementor_data( $id );
							}
						}
					}
				}

				// Clear elementor cache after changes
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			}

			// Temp save replace ids
			update_option( '_acdi_replacement_post_ids', $old_and_new );

			$result = array( 'success' => true, 'errors' => $errors );

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $result;
		}

		/**
		 * Import dummy posts.
		 *
		 * @param $result
		 * @param $request
		 *
		 * @return mixed
		 */
		public function remote_import_posts( $result = array(), $request = array() ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			if ( empty( $result ) ) {
				$result = array( 'success' => false );
			}

			// Validate
			if ( ! $this->validate_request() ) {
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return $result;
			}

			if ( ! empty( $request ) && is_object( $request ) && is_a( $request, 'WP_REST_Request' ) ) {
				$params = $request->get_params();
			} else {
				$params = array();
			}

			$errors = array();
			$total = ! empty( $params['total'] ) ? absint( $params['total'] ) : 0;
			//$this->debug_log( $total, __METHOD__ . ' - total', __FILE__, __LINE__ );
			$page = ! empty( $params['page'] ) ? absint( $params['page'] ) : 0;
			$this->debug_log( $page, __METHOD__ . ' - page', __FILE__, __LINE__ );
			$offset = ! empty( $params['offset'] ) ? absint( $params['offset'] ) : 0;
			//$this->debug_log( $offset, __METHOD__ . ' - offset', __FILE__, __LINE__ );
			$remove_dummy_data = ! empty( $params['remove_dummy_data'] ) ? true : false;
			//$this->debug_log( $remove_dummy_data, __METHOD__ . ' - remove_dummy_data', __FILE__, __LINE__ );
			$posts = ! empty( $params['posts'] ) ? $params['posts'] : array();
			//$this->debug_log( $posts, __METHOD__ . ' - posts', __FILE__, __LINE__ );

			// Maybe remove dummy data
			if ( ! empty( $remove_dummy_data ) ) {
				$post_types = geodir_get_posttypes( 'names' );

				if ( ! empty( $post_types ) ) {
					foreach ( $post_types as $post_type ) {
						$table = geodir_db_cpt_table( $post_type );

						if ( $table ) {
							geodir_add_column_if_not_exist( $table, 'post_dummy', "TINYINT(1) NULL DEFAULT '0'" );
						}

						GeoDir_Admin_Dummy_Data::delete_dummy_posts( $post_type );
					}
				}

				// Delete any previous posts
				self::delete_demo_posts( 'post' );
				self::delete_demo_posts( 'attachment' );

				// Maybe set page featured images
				$featured_images = get_option('_acdi_page_featured_images');

				if ( ! empty( $featured_images ) ) {
					foreach( $featured_images as $p => $i ) {
						$image = (array) GeoDir_Media::get_external_media( $i, '',array( 'image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/webp' ), array( 'ext' => 'png', 'type' => 'image/png' ) );

						if ( ! empty( $image['url'] ) ) {
							$attachment_id = GeoDir_Media::set_uploaded_image_as_attachment( $image );

							if ( is_wp_error( $attachment_id ) ) {
								$this->debug_log( $attachment_id->get_error_message(), __METHOD__ . ' - set_uploaded_image_as_attachment - ' . $i, __FILE__, __LINE__ );
							} elseif ( $attachment_id ) {
								set_post_thumbnail( $p, $attachment_id ); // This will not set if there are dummy posts.
								update_post_meta( $attachment_id, '_ayecode_demo', 1 );
							}
						}
					}

					delete_option('_acdi_page_featured_images');
				}
			}

			if ( ! empty( $posts ) && class_exists( 'GeoDir_Admin_Dummy_Data' ) ) {
				$hello_world_trashed = false;

				foreach ( $posts as $post_info ) {
					$this->debug_log( $post_info['post_title'], __METHOD__ . ':' . $post_info['post_type'] . ':wp_insert_post', __FILE__, __LINE__ );

					unset( $post_info['ID'] );

					$post_info['post_title'] = wp_strip_all_tags( $post_info['post_title'] ); // WP does not automatically do this
					$post_info['post_status'] = 'publish';
					$post_info['post_dummy'] = '1';
					$post_info['post_author'] = 1;
					// Set post data
					$insert_result = wp_insert_post( $post_info, true ); // We hook into the save_post hook

					// Maybe insert attachments
					if ( is_wp_error( $insert_result ) ) {
						$this->debug_log( $insert_result->get_error_message(), __METHOD__ . ':wp_insert_post:' . $post_info['post_title'], __FILE__, __LINE__ );
					} elseif ( ! is_wp_error( $insert_result ) && ! empty( $insert_result ) && ! empty( $post_info['_raw_post_images'] ) ) {
						$this->set_external_media( $insert_result, $post_info['_raw_post_images'] );
					}

					// Post stuff
					if ( $post_info['post_type'] == 'post' && ! empty( $insert_result ) && ! is_wp_error( $insert_result ) ) {
						// Maybe soft delete original hello world post
						if ( ! $hello_world_trashed ) {
							wp_delete_post( 1, false );

							$hello_world_trashed = true;
						}

						// Set cats
						$terms = isset( $post_info['_cats'] ) ? $post_info['_cats'] : array();
						$post_terms = array();

						if ( ! empty( $terms ) ) {
							if ( ! function_exists( 'wp_create_category' ) ) {
								require_once( ABSPATH . '/wp-admin/includes/taxonomy.php' );
							}

							foreach( $terms as $term_name ) {
								$term = get_term_by( 'name', $term_name, 'category' );

								if ( ! empty( $term->term_id ) ) {
									$post_terms[] = absint( $term->term_id );
								} else {
									$term_name = sanitize_title( $term_name );
									$term_id = wp_create_category( $term_name );

									if ( is_wp_error( $term_id ) ) {
										$this->debug_log( $term_id->get_error_message(), __METHOD__ . ' - wp_create_category - ' . $term_name, __FILE__, __LINE__ );
									} elseif ( $term_id ) {
										$post_terms[] = absint( $term_id );
									}
								}
							}

							if ( ! empty( $post_terms ) ) {
								wp_set_post_categories( $insert_result, $post_terms, false );
							}
						}

						// Featured image
						$image_url = ! empty( $post_info['_featured_image_url'] ) ? esc_url_raw( $post_info['_featured_image_url'] ) : '';

						if ( $image_url ) {
							$image = (array) GeoDir_Media::get_external_media( $image_url, '', array( 'image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/webp' ), array( 'ext' => 'png', 'type' => 'image/png' ) );

							if ( ! empty( $image['url'] ) ) {
								$attachment_id = GeoDir_Media::set_uploaded_image_as_attachment( $image );

								if ( is_wp_error( $attachment_id ) ) {
									$this->debug_log( $attachment_id->get_error_message(), __METHOD__ . ' - set_uploaded_image_as_attachment - ' . $image['url'], __FILE__, __LINE__ );
								} elseif ( $attachment_id ){
									set_post_thumbnail( $insert_result, $attachment_id );

									update_post_meta( $attachment_id, '_ayecode_demo', 1 );
								}
							}
						}
					}
				}
			}

			$result = array( 'success' => true, 'errors' => $errors, 'total' => $total, 'page' => $page, 'offset' => $offset );

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $result;
		}

		/**
		 * Import menus.
		 *
		 * @param $result
		 * @param $request
		 *
		 * @return mixed
		 */
		public function remote_import_menus( $result = array(), $request = array() ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			if ( empty( $result ) ) {
				$result = array( 'success' => false );
			}

			// Validate
			if ( ! $this->validate_request() ) {
				return $result;
			}

			if ( ! empty( $request ) && is_object( $request ) && is_a( $request, 'WP_REST_Request' ) ) {
				$params = $request->get_params();
			} else {
				$params = array();
			}

			$menus = ! empty( $params['menus'] ) ? $params['menus'] : array();
			//$this->debug_log( $menus, __METHOD__ . ' - menus', __FILE__, __LINE__ );
			$errors = array();

			if ( ! empty( $menus ) ) {
				foreach ( $menus as $location => $menu ) {
					$import = $this->import_menu( $location, $menu );
				}
			}

			$result = array( 'success' => true, 'errors' => $errors );

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $result;
		}

		/**
		 * Try to set higher limits on the fly
		 */
		public static function set_php_limits() {
//			if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
//				error_reporting( 0 );
//			}
//			@ini_set( 'display_errors', 0 );

			// try to set higher limits for import
			$max_input_time     = ini_get( 'max_input_time' );
			$max_execution_time = ini_get( 'max_execution_time' );
			$memory_limit       = ini_get( 'memory_limit' );

			if ( $max_input_time !== 0 && $max_input_time != -1 && ( ! $max_input_time || $max_input_time < 3000 ) ) {
				ini_set( 'max_input_time', 3000 );
			}

			if ( $max_execution_time !== 0 && ( ! $max_execution_time || $max_execution_time < 3000 ) ) {
				ini_set( 'max_execution_time', 3000 );
			}

			if ( $memory_limit && str_replace( 'M', '', $memory_limit ) ) {
				if ( str_replace( 'M', '', $memory_limit ) < 256 ) {
					ini_set( 'memory_limit', '256M' );
				}
			}

			/*
			 * The `auto_detect_line_endings` setting has been deprecated in PHP 8.1,
			 * but will continue to work until PHP 9.0.
			 * For now, we're silencing the deprecation notice as there may still be
			 * translation files around which haven't been updated in a long time and
			 * which still use the old MacOS standalone `\r` as a line ending.
			 * This fix should be revisited when PHP 9.0 is in alpha/beta.
			 */
			@ini_set( 'auto_detect_line_endings', true ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		/**
		 * Arguments for replacing mod-security triggers.
		 *
		 * @todo This is mirrored in the AyeCode Connect plugin and changes should be added there also if updating.
		 *
		 * @param $values
		 *
		 * @return array
		 */
		public function str_replace_args( $values = false ) {
			$salt = 'ZXY';
			$args = array(
				'VARCHAR' => 'VARCHAR' . $salt,
				'TEXT'    => 'TEXT' . $salt,
				'SELECT'  => 'SELECT' . $salt,
				'Select'  => 'Select' . $salt,
				'select'  => 'select' . $salt,
				'FROM'    => 'FROM' . $salt,
				'TINYINT' => 'TINYINT' . $salt,
				'FLOAT'   => 'FLOAT' . $salt,
				'INT'     => 'INT' . $salt,
				'-->'     => '--' . $salt . '>',
				'<!--'     => '<' . $salt . '!--',
				'javascript'     => 'javsrpt' . $salt,
			);

			return $values ? array_values( $args ) : array_keys( $args );
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
}