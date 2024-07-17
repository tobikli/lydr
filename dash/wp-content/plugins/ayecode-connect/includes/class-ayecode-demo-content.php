<?php
/**
 * A class for importing demo content.
 */

/**
 * Bail if we are not in WP.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'AyeCode_Demo_Content' ) ) {

	/**
	 * The settings for AyeCode Connect
	 */
	class AyeCode_Demo_Content {
		/**
		 * The title.
		 *
		 * @var string
		 */
		public $name = 'Import Demo Data';

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
		 * If debuggin is enabled.
		 *
		 * @var
		 */
		public $debug = false;

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
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AyeCode_Demo_Content ) ) {
				self::$instance = new AyeCode_Demo_Content;

				$args                     = ayecode_connect_args();
				self::$instance->client   = new AyeCode_Connect( $args );

				if ( is_admin() ) {
					add_action( 'admin_menu', array( self::$instance, 'menu_item' ) );

					self::$instance->base_url = str_replace( "/includes/../", "/", plugins_url( '../', __FILE__ ) );

					// prevent redirects after plugin/theme activations
					self::$instance->prevent_redirects();
					add_action( 'init', array( self::$instance, 'prevent_redirects' ),12 );

					// ajax
					add_action( 'wp_ajax_ayecode_connect_demo_content', array( self::$instance, 'import_content' ) );
//					add_action( 'wp_ajax_ayecode_connect_disconnect', array( self::$instance, 'ajax_disconnect_site' ) );
//					add_action( 'wp_ajax_ayecode_connect_licences', array( self::$instance, 'ajax_toggle_licences' ) );
//					add_action( 'wp_ajax_ayecode_connect_support', array( self::$instance, 'ajax_toggle_support' ) );
//					add_action( 'wp_ajax_ayecode_connect_support_user', array( self::$instance, 'ajax_toggle_support_user' ) );
//					add_action( 'wp_ajax_ayecode_connect_install_must_use_plugin', array( self::$instance, 'install_mu_plugin' ) );
				}
			}

			return self::$instance;
		}

		/**
		 * Prevent plugin/theme redirects after activation.
		 */
		public function prevent_redirects(){
			// prevent redirects when doing ajax
			if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'ayecode_connect_demo_content' ) {
				// prevent redirects to settings screens
				add_filter('wp_redirect','__return_empty_string',200);

				// prevent some transient redirects
				delete_transient( '_gd_activation_redirect' );
				delete_transient( 'gd_social_importer_redirect' );
			}
		}


		/**
		 * Add the WordPress settings menu item.
		 */
		public function menu_item() {
			$url_change_disconnection_notice = get_transient( $this->client->prefix . '_site_moved');

			$menu_name = $this->name;

//			add_menu_page(
//				$menu_name,
//				$url_change_disconnection_notice ? sprintf($menu_name.' <span class="awaiting-mod">%s</span>', "!") : $menu_name,
//				'manage_options',
//				'ayecode-connect',
//				array(
//					$this,
//					'settings_page'
//				),
//				'data:image/svg+xml;base64,' . base64_encode( file_get_contents( dirname( __FILE__ ).'/../assets/img/ayecode.svg' ) ),
//				4
//			);


			$page = add_submenu_page(
				'ayecode-connect',
				$this->name,
				$url_change_disconnection_notice ? sprintf($this->name.' <span class="awaiting-mod">%s</span>', "!") : $this->name,
				'manage_options',
//				$this->client->is_registered() ? 'ayecode-demo-content' : 'ayecode-connect&alert=connect',
				'ayecode-demo-content',// : 'ayecode-connect&alert=connect',
				array(
					$this,
					'settings_page'
				)
			);

			add_action( "admin_print_styles-{$page}", array( $this, 'scripts' ) );



			// maybe clear licenses
			$nonce = !empty($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';
			$action = !empty($_REQUEST['ac_action']) ? sanitize_title_with_dashes($_REQUEST['ac_action']) : '';
			if ( $action && $action == 'clear-licenses' && $nonce && wp_verify_nonce( $nonce, 'ayecode-connect-debug' ) ) {
				$this->clear_all_licenses();
				wp_redirect(admin_url( "admin.php?page=ayecode-connect&ayedebug=1" ));
				exit;
			}

		}

		/**
		 * Add scripts to our settings page.
		 */
		public function scripts() {
//			wp_enqueue_style( 'ayecode-connect-bootstrap', $this->base_url . 'assets/css/ayecode-ui-compatibility.css', array(), AYECODE_CONNECT_VERSION );

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
		 * Settings page HTML.
		 */
		public function settings_page( $wizard = false ) {
            global $aui_bs5;

			// if not connectd then redirect to connection screen
			if(!$this->client->is_active()){
				$maybe_demo_redirect = !empty($_REQUEST['ac-demo-import']) ? '&ac-demo-import='.sanitize_title_with_dashes($_REQUEST['ac-demo-import']) : '';
				$connect_url = admin_url("admin.php?page=ayecode-connect&alert=connect".$maybe_demo_redirect);
				?>
				<script>
					window.location.replace("<?php echo esc_url_raw($connect_url);?>");
				</script>
				<?php
			}else{

			// bsui wrapper makes our bootstrap wrapper work
			?>
			<!-- Clean & Mean UI -->
			<style>
				#wpbody-content > div.notice,
				#wpbody-content > div.error{
					display: none;
				}

				<?php if($wizard){ ?>
				.bsui .modal-backdrop.fade.show{
					display: none !important;
				}
				<?php } ?>
			</style>

				<?php if(!$wizard){ ?>
			<div class="bsui" style="margin-left: -20px;">
				<!-- Just an image -->
				<nav class="navbar bg-white border-bottom">
					<a class="navbar-brand p-0" href="#">
						<img src="<?php echo $this->base_url; ?>assets/img/ayecode.png" width="120" alt="AyeCode Ltd">
					</a>
				</nav>
			</div>
					<?php } ?>


			<div class="bsui" style="<?php if(!$wizard){ ?>margin-left: -20px; display: flex<?php } ?>">
				<div class="<?php if(!$wizard){ ?>containerx bg-white w-100 p-4 m-4 border rounded<?php } ?>">
					<?php
                    $sites = $this->get_sites();

                    echo aui()->alert(array(
							'type'=> 'danger',
//                            'class' => 'mt-4',
							'content'=> __("This importer should only be used on NEW sites, it will change the whole look and appearance of your site.","ayecode-connect")
						)
					);

                    echo $this->get_demo_tabs_head( $sites );
                    echo $this->get_demo_tabs_body( $sites );

					?>
				</div>

				<!-- Modal -->
				<div class="modal fade p-0 m-0" id="ac-item-preview" data-demo="" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" style="z-index: 10000;">
					<div class="modal-dialog mw-100  p-0 m-0">
						<div class="modal-content vh-100 rounded-0">
							<div class="row overflow-hidden">
								<div class="col-3 border-right pr-0 vh-100 d-flex flex-column">
									<div class="modal-header">
										<h5 class="modal-title" id="staticBackdropLabel"></h5>
										<button type="button" class="close btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
											<?php echo $aui_bs5 ? '' : '<span aria-hidden="true">&times;</span>'; ?>
										</button>
									</div>
									<div class="modal-body overflow-auto bg-light scrollbars-ios ac-import-progress d-none">
										<h6 class=" h6"><?php _e("Importing Demo","ayecode-connect");?></h6>
										<div class="progress">
											<div class="progress-bar main-progress progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 0"></div>
										</div>
										<div class="alert alert-danger aci-error mt-3 d-none" role="alert"></div>
										<ul class="list-group mt-3 aci-import-steps">
											<li class="list-group-item mb-0">
												<div class="d-flex justify-content-between align-items-center">
													<?php _e("Downloading Data","ayecode-connect");?>
													<span class="spinner-border spinner-border-sm" role="status"></span>
												</div>
											</li>
											<li class="list-group-item mb-0">
												<div class="d-flex justify-content-between align-items-center">
													<?php _e("Theme","ayecode-connect");?>
													<span class="text-muted h6 p-0 m-0"><i class="fas fa-check-circle"></i></span>
												</div>
											</li>
											<li class="list-group-item mb-0">
												<div class="d-flex justify-content-between align-items-center">
													<?php _e("Plugins","ayecode-connect");?>
													<span class="text-muted h6 p-0 m-0"><i class="fas fa-check-circle"></i></span>
												</div>
											</li>
											<li class="list-group-item mb-0">
												<div class="d-flex justify-content-between align-items-center">
													<?php _e("Settings","ayecode-connect");?>
													<span class="text-muted h6 p-0 m-0"><i class="fas fa-check-circle"></i></span>
												</div>
											</li>
											<li class="list-group-item mb-0">
												<div class="d-flex justify-content-between align-items-center">
													<?php _e("Categories","ayecode-connect");?>
													<span class="text-muted h6 p-0 m-0"><i class="fas fa-check-circle"></i></span>
												</div>
											</li>
											<li class="list-group-item mb-0">
												<div class="d-flex justify-content-between align-items-center">
													<?php _e("Page Templates","ayecode-connect");?>
													<span class="text-muted h6 p-0 m-0"><i class="fas fa-check-circle"></i></span>
												</div>
											</li>
											<li class="list-group-item mb-0">
												<div class="d-flex justify-content-between align-items-center">
													<?php _e("Dummy Posts","ayecode-connect");?>
													<span class="text-muted h6 p-0 m-0"><i class="fas fa-check-circle"></i></span>
												</div>
												<div class="progress mt-1 d-none ">
													<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 0"></div>
												</div>
											</li>
											<li class="list-group-item mb-0">
												<div class="d-flex justify-content-between align-items-center">
													<?php _e("Widgets","ayecode-connect");?>
													<span class="text-muted h6 p-0 m-0"><i class="fas fa-check-circle"></i></span>
												</div>
											</li>
											<li class="list-group-item mb-0">
												<div class="d-flex justify-content-between align-items-center">
													<?php _e("Menus","ayecode-connect");?>
													<span class="text-muted h6 p-0 m-0"><i class="fas fa-check-circle"></i></span>
												</div>
											</li>
										</ul>
									</div>
									<div class="modal-body overflow-auto bg-light scrollbars-ios ac-item-info">
										<div class="ac-item-img shadow-sm"></div>
										<div class="ac-item-desc pt-4"></div>
										<div class="ac-item-theme pt-4"></div>
										<div class="ac-item-plugins pt-4"></div>
									</div>
									<div class="modal-footer">
										<button onclick="aci_init(this);return false;" type="button" class="btn btn-primary w-100"><?php _e("Import","ayecode-connect");?></button>
									</div>
								</div>
								<div class="col-9 p-0 m-0 ">
									<div class="ac-preview-loading text-center position-absolute w-100 text-white vh-100 overlay overlay-black p-0 m-0 d-none d-flex justify-content-center align-items-center"><div class="spinner-border" role="status"></div></div>
									<iframe id="embedModal-iframe" class="w-100 vh-100 p-0 m-0" src="" width="100%" height="100%" frameborder="0" allowtransparency="true"></iframe>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<script>
				var $aci_url = '';
				var $aci_demo = '';
				var $aci_percent = 0;
				var $aci_sub_percent = 0;
				var $aci_step = 0;
				var $aci_page = 0;
				function aci_init($item) {

					var r = confirm("<?php _e("This import may remove all current GeoDirectory data, please only proceed if you are ok with this.","ayecode-connect");?>");
					if (r == true) {

						// set the import url
						$aci_url = jQuery('#ac-item-preview').find('iframe').attr('src');

						// prevent navigate away
						jQuery('#ac-item-preview').find('.modal-header button,.modal-footer .btn').prop('disabled', true);

						// set button as importing
						jQuery($item).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <?php _e( "Importing...", "ayecode-connect" );?>');

						// set status
						jQuery('#ac-item-preview').find('.ac-item-info,.ac-import-progress').toggleClass('d-none');

						// start import
						aci_step();

					}
				}

				function aci_step(data_file) {
					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'ayecode_connect_demo_content',
							security: ayecode_connect.nonce,
							demo: $aci_demo,
							step: $aci_step,
							p_num: $aci_page,
							data_file: data_file ? data_file : ''
						},
						beforeSend: function() {
						},
						success: function(data, textStatus, xhr) {
							console.log(data);
							if (data.success) {
								aci_progress($aci_step, data.data);
								if (data.data.step == 9) {
									// done
								} else {
									if (data.data.step == 6 && data.data.page !== "undefined") {
										$aci_step = 6;
										$aci_sub_percent = data.data.sub_percent;
										$aci_page++;
										aci_step(data.data.data_file);
									} else {
										$aci_step++;
										aci_step(data.data.data_file);
									}
								}
							} else {
								aci_error($aci_step, data.data);
							}
						},
						error: function(xhr, textStatus, errorThrown) {
							alert(textStatus + ': ' + errorThrown);
						}
					});
				}

				function aci_error($step,$error){
					$li = jQuery('#ac-item-preview .aci-import-steps').find('li').eq($step);
					// mark as failed
					$li.find('span').replaceWith('<span class="text-danger h6 p-0 m-0"><i class="fas fa-times-circle"></i></span>');

					// show error
					jQuery('#ac-item-preview .aci-error').html($error).removeClass('d-none');

					// stop progress animation
					jQuery('#ac-item-preview .progress-bar').removeClass('progress-bar-animated progress-bar-striped');
					// un-prevent navigate away
					jQuery('#ac-item-preview').find('.modal-header button,.modal-footer .btn').prop('disabled', false);

					// set button as view site
					jQuery('#ac-item-preview .modal-footer').html('<a href="#reload" onclick="location.reload();return false;" class="btn btn-primary w-100"><?php _e("ERROR","ayecode-connect");?></a>');
				}

				function aci_progress($step,$data){
					$li = jQuery('#ac-item-preview .aci-import-steps').find('li').eq($step);
					$li_next = jQuery('#ac-item-preview .aci-import-steps').find('li').eq($step+1);

					// set sub percent
					if(typeof($data.sub_percent) !== 'undefined' ){
						$li.find('.progress').removeClass('d-none');
						$li.find('.progress-bar').css("width",$data.sub_percent+"%");
					}else{
						$li.find('.progress').addClass('d-none');
					}

					// set percent done
					jQuery('#ac-item-preview .progress-bar.main-progress').css("width",$data.percent+"%");

					if(typeof($data.sub_percent) !== 'undefined' ){

					}else{
						// mark as done
						$li.find('span').replaceWith('<span class="text-success h6 p-0 m-0"><i class="fas fa-check-circle"></i></span>');

						// mark as doing
						$li_next.find('span').replaceWith('<span class="spinner-border spinner-border-sm" role="status"></span>');
					}

					// finish up
					if($step===8){
						// stop progress animation
						jQuery('#ac-item-preview .progress-bar.main-progress').removeClass('progress-bar-animated progress-bar-striped');
						// un-prevent navigate away
						jQuery('#ac-item-preview').find('.modal-header button,.modal-footer .btn').prop('disabled', false);

						// set button as view site
						jQuery('#ac-item-preview .modal-footer').html('<a href="<?php echo get_home_url();?>" class="btn btn-primary w-100"><?php _e("View Site","ayecode-connect");?></a>');
					}
				}

				jQuery(function(){
					var iFrame = jQuery( '#embedModal-iframe') ;

					jQuery( '#ac-item-preview' ).on( 'show.bs.modal', function ( e ) {
						jQuery('.ac-preview-loading').addClass('d-flex');
						var url = jQuery( '#ac-item-preview' ).data('iframe-url');
						iFrame.attr({
							src: url
						});
					});
					jQuery( "#ac-item-preview" ).on( "hidden.bs.modal", function() {
						iFrame.removeAttr( "src allow" );
					});

					//resize the iframe once loaded.
					iFrame.load(function() {
						jQuery('.ac-preview-loading').removeClass('d-flex');
					});

					// check for direct link
					<?php
						if(!empty($_REQUEST['ac-demo-import'])){
							$demo = sanitize_title_with_dashes($_REQUEST['ac-demo-import']);
							?>
							jQuery(".col").find("[data-demo='<?php echo esc_attr($demo);?>']").find(".btn").click();
							<?php
						}
					?>
				});

				function ac_preview_site($item){

					// replace vars
					var $title = jQuery($item).closest('.card').find('.card-title').html();
					jQuery('#ac-item-preview').find('.modal-title').html($title);

					// desc
					var $desc = jQuery($item).closest('.card').find('.card-body').html();
					jQuery('#ac-item-preview').find('.ac-item-desc').html($desc);

					// theme
					var $theme = jQuery($item).closest('.card').find('.sd-src-theme').html();
					jQuery('#ac-item-preview').find('.ac-item-theme').html($theme);

					// plugins
					var $plugins = jQuery($item).closest('.card').find('.sd-src-plugins').html();
					jQuery('#ac-item-preview').find('.ac-item-plugins').html($plugins);

					// img
					var $img = jQuery($item).closest('.card').find('img').clone();
					jQuery('#ac-item-preview').find('.ac-item-img').html($img);

					// iframe
					jQuery('#ac-item-preview').data('iframe-url',jQuery($item).attr('href'));

					// demo slug data
					jQuery('#ac-item-preview').data('demo',jQuery($item).closest('.card').data('demo'));
					$aci_demo = jQuery($item).closest('.card').data('demo');
					
					// open modal
					jQuery('#ac-item-preview').modal('show');

				}
			</script>
			<?php
			}
		}

        public function get_demo_site_types($sites)
        {
            // define the types
            $types = array(
                'blockstrap' => array(),
                'elementor' => array(),
                'kadence' => array(),
                'legacy' => array(),
            );

            foreach ($sites as $site ){
                // check if string contains

                $desc = !empty( $site->desc ) ? $site->desc : '';

                if ( !empty( $desc ) && strpos($desc, 'BlockStrap' ) !== false) {
                    $types['blockstrap'][] = $site;
                }elseif ( !empty( $desc ) && strpos($desc,  'Kadence' ) !== false) {
                    $types['kadence'][] = $site;
                }elseif ( !empty( $desc ) && strpos($desc, 'elementor' ) !== false) {
                    $types['elementor'][] = $site;
                }else{
                    $types['legacy'][] = $site;
                }

            }

            return $types;

        }

        public function get_demo_tabs_body( $sites )
        {


            $types = $this->get_demo_site_types($sites);

            ob_start();

            echo '<div class="tab-content" id="ayecode-connect-demo-tabsContent">';

            foreach ( $types as $type => $sites ){

                // maybe open
                if (!empty($sites)) {
                    $active = 'blockstrap'==$type ? 'show active' : '';
                    echo '<div class="tab-pane fade '.esc_attr($active).'" id="ayecode-demo-'.esc_attr($type).'-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">';

                    if ('legacy'==$type) {
                        echo aui()->alert(array(
                                'type'=> 'warning',
                                'class' => 'mt-4',
                                'content'=> __("These are legacy themes which are no longer supported and will be removed soon.","ayecode-connect")
                            )
                        );
                    }

                    echo '<div class="row row-cols-1 row-cols-sm-2  row-cols-md-2 mt-4">';


                    foreach ( $sites as $site ){
                        global $ac_site_args,$ac_prefix;
                        $ac_prefix = $this->client->prefix;
                        $ac_site_args = $site;
                        load_template( dirname( __FILE__ )."/../templates/import/site.php", false ); // $args only introduced in wp 5.5 so lets use a more backwards compat way
                    }

                    echo '</div></div>';
                }
            }

            echo '</div>';


            return ob_get_clean();

        }

        public function get_demo_tabs_head( $sites ) {
            global $aui_bs5;

            $types = $this->get_demo_site_types($sites);
            ob_start();

            echo '<ul class="nav nav-tabs mb-4" id="ayecode-connect-demo-tabs" role="tablist">';

            $names = array(
                'blockstrap' => 'BlockStrap',
                'elementor' => 'Elementor',
                'kadence' => 'Kadence WP',
                'legacy' => 'legacy',
            );

            foreach ( $types as $type => $sites ) {
                if ( ! empty( $sites ) ) {
                    $active = 'blockstrap' == $type ? 'active' : '';
                    $selected = 'blockstrap' ==  $type ? 'true' : 'false';

                    echo  '<li class="nav-item mb-0" role="presentation">';
                    echo '<button class="nav-link ' . esc_attr( $active ) . '" id="ayecode-demo-' . esc_attr( $type ) . '" data-' . ( $aui_bs5 ? 'bs-' : '' ) . 'toggle="pill" data-' . ( $aui_bs5 ? 'bs-' : '' ) . 'target="#ayecode-demo-' . esc_attr( $type ) . '-pane" type="button" role="tab" aria-controls="pills-home" aria-selected="' . esc_attr( $selected ) . '">';

                    if ( 'kadence' === $type ) {
                        echo '<img width="22px" class="me-1 mr-1" src="' . $this->base_url . 'assets/img/kadencewp-icon-dark.svg" alt="Kadence WP"/>';
                    } else if ( 'elementor' === $type ) {
                        echo '<i style="color:#db3157;font-size:22px" class="fab fa-elementor me-1 mr-1" ></i>';
                    } else if ( 'blockstrap' === $type ) {
                        echo '<img width="22px" class="me-1 mr-1" src = "' . $this->base_url . 'assets/img/blockstrap-logo.jpg" alt="BlockStrap"/>';
                    }

                    echo esc_html( $names[ $type ] );
                    echo '</button>';
                    echo '</li>';
                }
            }

            echo '</ul>';

            return ob_get_clean();
        }

		/**
		 * Get demo site info.
		 *
		 * @return mixed
		 */
		public function get_sites(){

			$sites = get_transient( 'ayecode_connect_demos' );
			if ( empty( $demos ) ) {
				$args = array(
					'timeout'     => 30,
					'redirection' => 0,
					'sslverify'   => AYECODE_CONNECT_SSL_VERIFY,
				);
				$url  = $this->client->get_api_url( '/demos' );
				$data = wp_remote_get( $url, $args );

				if ( ! is_wp_error( $data ) && $data['response']['code'] == 200 ) {
					$responseBody = wp_remote_retrieve_body( $data );
					$sites        = json_decode( $responseBody );
					set_transient( 'ayecode_connect_demos', $sites, HOUR_IN_SECONDS );
				}
			}


			return $sites;
		}

		public function download_content( $demo, $site = array() ) {
			$this->debug_log( $demo, __FUNCTION__ . ' : demo', __FILE__, __LINE__ );
			global $wp_filesystem;

			$response = $this->request_download( $demo, $site );
			$this->debug_log( $response, __FUNCTION__ . ' : response', __FILE__, __LINE__ );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			if ( empty( $response ) ) {
				return new WP_Error( 'demo_download_request_failed', __( 'Fail to request download package.', 'ayecode-connect' ) );
			}

			if ( is_object( $response ) ) {
				$response = (array) $response;
			}

			if ( empty( $response['download-link'] ) ) {
				return new WP_Error( 'demo_download_generate_failed', __( 'Fail to generate download package.', 'ayecode-connect' ) );
			}

			$wp_filesystem = $this->get_wp_filesystem();

			$download_url = $response['download-link'];
			$this->debug_log( $download_url, __FUNCTION__ . ' : download_url', __FILE__, __LINE__ );

			// Download package.
			$package = download_url( esc_url_raw( $download_url ), 300 );
			$this->debug_log( $package, __FUNCTION__ . ' : package', __FILE__, __LINE__ );

			if ( is_wp_error( $package ) ) {
				return $package;
			}

			// Check demo download folder.
			$this->check_ayecode_demo_folder_protection();

			$upload_dir      = wp_get_upload_dir();
			$download_folder = $upload_dir['basedir'] . '/ayedemo-download/';
			$delete_package = true;

			// We need a working file - strip off any .tmp or .zip suffixes.
			$working_file = $download_folder . basename( basename( $download_url, '.tmp' ), '.zip' ) . '.html';
			$this->debug_log( $working_file, __FUNCTION__ . ' : working_file', __FILE__, __LINE__ );

			// Clean up existing file.
			if ( file_exists( $working_file ) ) {
				$wp_filesystem->delete( $working_file );
			}

			// Unzip package to working file.
			$unzip_file = unzip_file( $package, $download_folder );
			$this->debug_log( $unzip_file, __FUNCTION__ . ' : unzip_file', __FILE__, __LINE__ );

			// Once extracted, delete the package if required.
			if ( $delete_package ) {
				@unlink( $package ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_unlink
			}

			if ( is_wp_error( $unzip_file ) ) {
				return $unzip_file;
			}

			if ( ! file_exists( $working_file ) ) {
				return new WP_Error( 'demo_download_data_file', __( 'Could not unpack demo data file.', 'ayecode-connect' ) );
			}$this->debug_log( $working_file, __FUNCTION__ . ' : return', __FILE__, __LINE__ );

			return $working_file;
		}

		public function request_download( $demo, $site = array() ) {
			$this->debug_log( $demo, __FUNCTION__ . ' : demo', __FILE__, __LINE__ );
			$args = array();
			//return array( 'download-link' => 'https://mysite.com/48435-kadence-jobs-directory-3zR3cT2F9pX6.zip' ); // Test with ready zip.

			if ( empty( $site ) ) {
				$sites = $this->get_sites();

				$site = isset($sites->{$demo}) ? $sites->{$demo} : array();
			}

			$response =  $this->client->download_demo_content( $demo, $args, $site );

			if ( ! is_wp_error( $response ) && is_object( $response ) ) {
				$response = (array) $response;
			}

			return $response;
		}

		public function import_content() {
			// Security
			check_ajax_referer( 'ayecode-connect', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( - 1 );
			}

			$sites = $this->get_sites();
			$step = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : '';
			$demo = isset( $_POST['demo'] ) ? sanitize_title_with_dashes( $_POST['demo'] ) : '';
			$page = isset( $_POST['p_num'] ) ? absint($_POST['p_num']) : 0;
			$site = isset( $sites->{$demo} ) ? $sites->{$demo} : array();
			$data_file = isset( $_POST['data_file'] ) ? sanitize_file_name( $_POST['data_file'] ) : '';

			$data = array(
				'step' => $step,
				'percent' => 0,
				'data_file' => $data_file
			);

			$error = array();

			if ( $step === 0 ) {
				$result = $this->download_content( $demo );
				//$result = '49015-kadence-jobs-directory-v5XCrTvqLMGp.html';// Test from remote site. // @todo

				$this->debug_log( $result, __METHOD__ . ':step:' . $step . ' response - download_content' , __FILE__, __LINE__ );

				if ( is_wp_error( $result ) ) {
					$error = $result;
				} else {
					$data_file = basename( $result );

					$data = array(
						'step' => $step + 1,
						'percent' => 5,
						'data_file' => $data_file
					);
				}
			} elseif ( $step === 1 ) {
				// Set the demo url
				update_option( '_acdi_demo_url', "https://demos.ayecode.io/" . $demo );

				// Theme
				$result = $this->set_demo_theme( $data_file );

				$this->debug_log( $result, __METHOD__ . ':step:' . $step . ' response - set_demo_theme' , __FILE__, __LINE__ );

				if ( is_wp_error( $result ) ) {
					$error = $result;
				} else {
					$data = array(
						'step' => $step + 1,
						'percent' => 10,
						'data_file' => $data_file
					);
				}
			} elseif ( $step === 2 ) {
				// Plugins
				$result = $this->set_demo_plugins( $data_file );

				$this->debug_log( $result, __METHOD__ . ':step:' . $step . ' response - set_demo_plugins' , __FILE__, __LINE__ );

				if ( is_wp_error( $result ) ) {
					$error = $result;
				} elseif ( ! empty( $result ) && is_array( $result ) && empty( $result['installed'] ) ) {
					$error = new WP_Error( 'plugins_not_installed', wp_sprintf( __( 'Could not install plugins: %s.', 'ayecode-connect' ), implode( ", ", array_values( $result['not_installed'] ) ) ) );
				} else {
					$message = '';

					if ( ! empty( $result['installed'] ) ) {
						$message .= wp_sprintf( __( 'Plugins installed: %s.', 'ayecode-connect' ), implode( ", ", array_values( $result['installed'] ) ) );
					}

					if ( ! empty( $result['not_installed'] ) ) {
						$message .= ' ' . wp_sprintf( __( 'Could not install plugins: %s.', 'ayecode-connect' ), implode( ", ", array_values( $result['not_installed'] ) ) );
					}

					if ( ! empty( $result['errors'] ) ) {
						$message .= ' ' . wp_sprintf( __( 'Errors: %s.', 'ayecode-connect' ), implode( ", ", array_values( $result['errors'] ) ) );
					}

					$data = array(
						'step' => $step + 1,
						'percent' => 25,
						'data_file' => $data_file,
						'message' => $message,
						'log_data' => ! empty( $result['errors'] ) ? $result['errors'] : array()
					);
				}
			} else if ( $step === 3 ) {
				// Settings
				$result = $this->set_demo_settings( $data_file );

				$this->debug_log( $result, __METHOD__ . ':step:' . $step . ' response - set_demo_settings' , __FILE__, __LINE__ );

				if ( is_wp_error( $result ) ) {
					$error = $result;
				} else {
					$data = array(
						'step'  => $step + 1,
						'percent' => 40,
						'data_file' => $data_file,
						'log_data' => ! empty( $result['errors'] ) ? $result['errors'] : array()
					);
				}
			} else if ( $step === 4 ) {
				// Categories
				$result = $this->set_demo_categories( $data_file );

				$this->debug_log( $result, __METHOD__ . ':step:' . $step . ' response - set_demo_categories' , __FILE__, __LINE__ );

				if ( is_wp_error( $result ) ) {
					$error = $result;
				} else {
					$data = array(
						'step' => $step + 1,
						'percent' => 50,
						'data_file' => $data_file,
						'log_data' => ! empty( $result['errors'] ) ? $result['errors'] : array()
					);
				}
			} else if( $step === 5 ) {
				// Page templates
				$result = $this->set_page_templates( $data_file );

				$this->debug_log( $result, __METHOD__ . ':step:' . $step . ' response - set_page_templates' , __FILE__, __LINE__ );

				if ( is_wp_error( $result ) ) {
					$error = $result;
				} else {
					$data = array(
						'step' => $step + 1,
						'percent' => 60,
						'data_file' => $data_file,
						'log_data' => ! empty( $result['errors'] ) ? $result['errors'] : array()
					);
				}
			} else if ( $step === 6 ) {
				// Dummy posts
				$result = $this->set_dummy_posts( $data_file, $site );

				$this->debug_log( $result, __METHOD__ . ':step:' . $step . ' response - set_dummy_posts:' . $page , __FILE__, __LINE__ );

				if ( is_wp_error( $result ) ) {
					$error = $result;
				} else {
					if ( isset( $result['total'] ) && isset( $result['offset'] ) && $result['total'] >= $result['offset'] ) {
						$sub_percent = $result['offset'] > $result['total'] ? 100 : ( $result['offset'] / $result['total'] ) * 100;

						$data = array(
							'step' => $step,
							'percent' => 60,
							'page' => $page++,
							'sub_percent' => round( $sub_percent ),
							'total' => $result['total'],
							'offset' => $result['offset'],
							'data_file' => $data_file,
							'log_data' => ! empty( $result['errors'] ) ? $result['errors'] : array()
						);
					} else {
						$data = array(
							'step' => $step + 1,
							'percent' => 80,
							'data_file' => $data_file,
							'log_data' => ! empty( $result['errors'] ) ? $result['errors'] : array()
						);
					}
				}
			} else if ( $step === 7 ) {
				// Widgets
				$result = $this->set_widgets( $data_file, $site );

				$this->debug_log( $result, __METHOD__ . ':step:' . $step . ' response - set_widgets' , __FILE__, __LINE__ );

				if ( is_wp_error( $result ) ) {
					$error = $result;
				} else {
					$data = array(
						'step' => $step + 1,
						'percent' => 90,
						'data_file' => $data_file,
						'log_data' => ! empty( $result['errors'] ) ? $result['errors'] : array()
					);
				}
			} else if ( $step === 8 ) {
				// Menus
				$result = $this->set_menus( $data_file, $site );;

				$this->debug_log( $result, __METHOD__ . ':step:' . $step . ' response - set_menus' , __FILE__, __LINE__ );

				if ( is_wp_error( $result ) ) {
					$error = $result;
				} else {
					$data = array(
						'step' => $step + 1,
						'percent' => 100,
						'data_file' => $data_file
					);
				}

				// Clear any unwanted data and flush rules
				if ( function_exists( 'goedir_loc_blank_term_counts' ) ) {
					// Clear term meta count.
					goedir_loc_blank_term_counts();
				}

				// Hide GD setup wizard notice.
				if ( class_exists( 'GeoDir_Admin_Notices' ) ) {
					GeoDir_Admin_Notices::remove_notice( 'install' );
				}

				// Hide Directory Starter downgrade notice.
				if ( $demo == 'starter' || $demo == 'supreme-directory' ) {
					update_option( 'ds_no_downgrade', true );
				}

				delete_transient( 'geodir_cache_excluded_uris' );
				wp_schedule_single_event( time(), 'geodir_flush_rewrite_rules' );
			} else if ( $step === 9 ) {
				// done
			}

			if ( empty( $error ) ) {
				wp_send_json_success( $data );
			} else {
				wp_send_json_error( $error->get_error_message() );
			}

			exit;
		}

		/**
		 * Install and activate a theme if needed.
		 *
		 */
		public function set_theme( $demo, $site = array() ) {
			// Maybe get site info
			if ( empty( $site ) ) {
				$sites = $this->get_sites();

				$site = isset($sites->{$demo}) ? $sites->{$demo} : array();
			}

			$slug = esc_attr( $site->theme->slug );

			$result = false;
			$activate_theme = false;
			$theme = wp_get_theme( $slug );

			if ( ! $theme->exists() ) {
				$result = $this->client->request_demo_content( $demo, 'theme' );

				if ( empty( $result->{$slug}->success ) ) {
					$result = new WP_Error( 'theme_install_fail', __( 'The theme installation failed.', 'ayecode-connect' ) );
				} else {
					$activate_theme = true;
				}
			} else if ( $slug == get_option( 'stylesheet' ) ) {
				// Its installed and active
				$result = true;
			} else {
				// Activate
				$activate_theme = true;
			}

			// Maybe activate theme
			if ( $activate_theme ) {
				// Activate

				switch_theme( $slug );

				if ( $slug == get_option( 'stylesheet' ) ) {
					$result = true;
				}

				// If a child theme then the main templare option can fail to update
				if ( $result && ! empty( $site->theme->Template ) ) {
					$parent_slug = esc_attr( $site->theme->Template );

					update_option( 'template', $parent_slug );
				}
			}

			return $result;
		}

		public function set_plugins( $demo, $site = array() ) {
			// Maybe get site info
			if ( empty( $site ) ) {
				$sites = $this->get_sites();
				$site = isset($sites->{$demo}) ? $sites->{$demo} : array();
			}

			$result = false;

			if ( ! empty( $site->plugins ) ) {
				$result = $this->client->request_demo_content( $demo, 'plugins' );
			}

			return $result;
		}

		public function get_demo_data( $data_file, $section = '' ) {
			global $wp_filesystem;

			//$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );
			//$this->debug_log( $section, __METHOD__ . ' - section', __FILE__, __LINE__ );

			$wp_filesystem = $this->get_wp_filesystem();

			if ( empty( $wp_filesystem ) ) {
				return new WP_Error( 'empty_wp_filesystem', __( 'Could not access to the filesystem.', 'ayecode-connect' ) );
			}

			$upload_dir     = wp_get_upload_dir();
			$downloads_path = $upload_dir['basedir'] . '/ayedemo-download/';

			$data_file_path = $downloads_path . $data_file;
			//$this->debug_log( $data_file_path, __METHOD__ . ' - data_file_path', __FILE__, __LINE__ );

			if ( ! file_exists( $data_file_path ) ) {
				return new WP_Error( 'no_data_file', __( 'Could not access to the data file.', 'ayecode-connect' ) );
			}

			$content = $wp_filesystem->get_contents( $data_file_path );
			//$this->debug_log( $content, __FUNCTION__ . ' : content', __FILE__, __LINE__ );

			if ( empty( $content ) ) {
				return new WP_Error( 'no_data_content', __( 'No demo data found.', 'ayecode-connect' ) );
			}

			$data = json_decode( $content, true );
			//$this->debug_log( $data, __FUNCTION__ . ' : data', __FILE__, __LINE__ );

			if ( ! is_array( $data ) ) {
				return new WP_Error( 'ivalid_data_content', __( 'Invalid demo data.', 'ayecode-connect' ) );
			}

			if ( ! empty( $section ) ) {
				if ( isset( $data[ $section ] ) && is_array( $data[ $section ] ) ) {
					$data = $data[ $section ];
				} else {
					$data = new WP_Error( 'no_data_found', wp_sprintf( __( 'No demo data found for %s.', 'ayecode-connect' ), $section ) );
				}
			}

			//$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $data;
		}

		/**
		 * Set Theme
		 */
		public function set_demo_theme( $demo_file ) {
			$this->debug_log( $demo_file, __FUNCTION__ . ' : demo_file', __FILE__, __LINE__ );
			$data = $this->get_demo_data( $demo_file, 'theme' );
			//$this->debug_log( $data, __FUNCTION__ . ' : data', __FILE__, __LINE__ );

			if ( is_wp_error( $data ) ) {
				return $data;
			}

			if ( ! ( is_array( $data ) && ! empty( $data['slug'] ) ) ) {
				return new WP_Error( 'invalid_demo_data', __( 'Invalid demo data found for theme.', 'ayecode-connect' ) );
			}

			$slug = $data['slug'];
			$this->debug_log( $slug, 'slug', __FILE__, __LINE__ );
			$theme = wp_get_theme( $slug );
			//$this->debug_log( $theme, 'theme', __FILE__, __LINE__ );
			$result = false;
			$activate = false;

			// Theme exists.
			if ( $theme->exists() ) {
				if ( $slug == get_option( 'stylesheet' ) ) {
					// Theme is active.
					$result = true;
				} else {
					// Activate theem.
					$activate = true;
				}
			} else {
				$_REQUEST['slug'] = $slug;

				if ( ! empty( $data['download_link'] ) ) {
					$_REQUEST['download_link'] = $data['download_link'];
				}

				$data['action'] = 'install_theme';

				$request = new WP_REST_Request( 'POST' );
				$request->set_body_params( $data );

				$response = self::$instance->client->do_action( $request );

				if ( is_wp_error( $response ) ) {
					$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

					return $response;
				} elseif ( $response->is_error() ) {
					$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

					return new WP_Error( 'demo_install_theme_failed', $response->as_error() );
				} else {
					$_response = $response->get_data();

					if ( is_array( $_response )&& ! empty( $_response['success'] ) ) {
						$activate = true;
						$result = true;
					} else {
						$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

						return new WP_Error( 'demo_install_theme_failed', wp_sprintf( __( 'Could not install theme %s.', 'ayecode-connect' ), $slug ) );
					}
				}
			}

			// Maybe activate theme.
			if ( $activate ) {
				// Activate
				switch_theme( $slug );

				if ( $slug == get_option( 'stylesheet' ) ) {
					$result = true;
				}

				// If a child theme then the main templare option can fail to update.
				if ( $result && ! empty( $data['parent_theme'] ) ) {
					update_option( 'template', esc_attr( $data['parent_theme'] ) );
				}
			}

			if ( ! $result ) {
				return new WP_Error( 'demo_set_theme_failed', wp_sprintf( __( 'Could not set theme %s.', 'ayecode-connect' ), $slug ) );
			}

			return true;
		}

		/**
		 * Set Plugins
		 */
		public function set_demo_plugins( $demo_file ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$data = $this->get_demo_data( $demo_file, 'plugins' );
			//$this->debug_log( $data, __FUNCTION__ . ' : data', __FILE__, __LINE__ );

			if ( is_wp_error( $data ) ) {
				return $data;
			}

			if ( ! ( is_array( $data['plugins'] ) && ! empty( $data['plugins'] ) ) ) {
				return new WP_Error( 'invalid_demo_data', __( 'Could not set plugins due to invalid data.', 'ayecode-connect' ) );
			}

			$response = array();
			$installed = array();
			$not_installed = array();
			$errors = array();

			foreach ( $data['plugins'] as $slug => $_args ) {
				$this->debug_log( $slug, __METHOD__ . ':plugin', __FILE__, __LINE__ );
				$args = $_args;
				$args['action'] = 'install_plugin';

				if ( empty( $args['slug'] ) ) {
					$slug_parts = explode( '/', $slug );
					$args['slug'] = $slug_parts[0];
				}

				// Check errors
				if ( ! empty( $args['errors'] ) && empty( $_args['slug'] ) ) {
					$status = false;
					$args_errors = array();

					if ( is_array( $args['errors'] ) ) {
						foreach ( $args['errors'] as $_key => $args_error ) {
							$args_errors[] = ( is_array( $args_error ) ? implode( ' ', array_values( $args_error ) ) : $args_error );
						}
					}

					$errors[ $args['slug'] ] = '[' . $args['slug'] . '] ' . implode( ' ', $args_errors );
				} else {
					$request = new WP_REST_Request( 'POST' );
					$request->set_body_params( $args );

					$_response = self::$instance->client->do_action( $request );
					//$this->debug_log( $_response, __METHOD__ . ':response', __FILE__, __LINE__ );

					if ( is_wp_error( $_response ) ) {
						$errors[ $args['slug'] ] = '[' . $args['slug'] . '] ' . $_response->get_error_message();

						$status = false;
					} else {
						if ( is_object( $_response ) && isset( $_response->data ) ) {
							$_response = $_response->data;
						}

						if ( is_array( $_response ) ) {
							if ( ! empty( $_response['error'] ) ) {
								$errors[ $args['slug'] ] = $_response['error'];
							}

							$status = $_response['success'] ? true : false;
						} else {
							$status = $_response ? true : false;
						}
					}
				}

				$plugin_title = $args['slug'];
				if ( isset( $args['name'] ) ) {
					$plugin_title .= '(' . $args['name'] . ')';
				}

				if ( $status ) {
					$installed[ $args['slug'] ] = $plugin_title;
				} else {
					$not_installed[ $args['slug'] ] = $plugin_title;
				}
			}

			$response = array(
				'installed' => $installed,
				'not_installed' => $not_installed,
				'errors' => $errors
			);

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			return $response;
		}

		/**
		 * Set Settings
		 */
		public function set_demo_settings( $demo_file ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$data = $this->get_demo_data( $demo_file, 'settings' );
			//$this->debug_log( $data, __METHOD__ . ' - settings', __FILE__, __LINE__ );

			if ( is_wp_error( $data ) ) {
				return $data;
			}

			if ( ! ( is_array( $data ) && ! empty( $data ) ) ) {
				return new WP_Error( 'invalid_demo_data', __( 'Could not set settings due to invalid data.', 'ayecode-connect' ) );
			}

			$data['action'] = 'remote_import_options';

			$request = new WP_REST_Request( 'POST' );
			$request->set_body_params( $data );

			$_response = self::$instance->client->do_action( $request );
			//$this->debug_log( $_response, __METHOD__ . ':response', __FILE__, __LINE__ );

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			if ( is_wp_error( $_response ) ) {
				return $_response;
			} elseif ( $_response->is_error() ) {
				return $_response->as_error();
			} else {
				return $_response->get_data();
			}
		}

		/**
		 * Set Categories
		 */
		public function set_demo_categories( $demo_file ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$data = $this->get_demo_data( $demo_file, 'categories' );
			//$this->debug_log( $data, __METHOD__ . ' - data', __FILE__, __LINE__ );

			if ( is_wp_error( $data ) ) {
				return $data;
			}

			if ( ! ( is_array( $data ) && ! empty( $data ) ) ) {
				return new WP_Error( 'invalid_demo_data', __( 'Could not import categories due to invalid data.', 'ayecode-connect' ) );
			}

			$params = array(
				'action' => 'remote_import_categories',
				'categories' => $data,
			);

			$request = new WP_REST_Request( 'POST' );
			$request->set_body_params( $params );

			$_response = self::$instance->client->do_action( $request );
			//$this->debug_log( $_response, __METHOD__ . ':response', __FILE__, __LINE__ );

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			if ( is_wp_error( $_response ) ) {
				return $_response;
			} elseif ( $_response->is_error() ) {
				return $_response->as_error();
			} else {
				return $_response->get_data();
			}
		}

		/**
		 * Set templates
		 */
		public function set_page_templates( $demo_file ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$data = $this->get_demo_data( $demo_file, 'templates' );
			//$this->debug_log( $data, __METHOD__ . ' - data', __FILE__, __LINE__ );

			if ( is_wp_error( $data ) ) {
				return $data;
			}

			if ( ! ( is_array( $data ) && ! empty( $data ) ) ) {
				return new WP_Error( 'invalid_demo_data', __( 'Could not import templates due to invalid data.', 'ayecode-connect' ) );
			}

			if ( ! empty( $data['elementor'] ) && ! defined( 'ELEMENTOR_VERSION' ) ) {
				unset( $data['elementor'] );
			}

			$params = array(
				'action' => 'remote_import_templates',
				'templates' => $data,
			);

			$request = new WP_REST_Request( 'POST' );
			$request->set_body_params( $params );

			$_response = self::$instance->client->do_action( $request );
			//$this->debug_log( $_response, __METHOD__ . ':response', __FILE__, __LINE__ );

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			if ( is_wp_error( $_response ) ) {
				return $_response;
			} elseif ( $_response->is_error() ) {
				return $_response->as_error();
			} else {
				return $_response->get_data();
			}
		}

		/**
		 * Set dummy posts
		 */
		public function set_dummy_posts( $demo_file, $demo ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$data = $this->get_demo_data( $demo_file, 'dummy_posts' );

			if ( is_wp_error( $data ) ) {
				if ( ! empty( $demo->dummy_posts ) ) {
					$data = $demo->dummy_posts;
				} else {
					$this->debug_log( $data, __METHOD__ . ':dummy_posts:get_demo_data error', __FILE__, __LINE__ );

					return $data;
				}
			}

			if ( ! ( is_array( $data ) && ! empty( $data ) ) ) {
				$this->debug_log( 'Empty dummy posts data.', __METHOD__ . ':dummy_posts:get_demo_data', __FILE__, __LINE__ );
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return array( 'success' => false );
			}

			$per_page = 5;
			$page = ! empty( $_REQUEST['p_num'] ) ? absint( $_REQUEST['p_num'] ) : 1;
			// $this->debug_log( $page, __METHOD__ . ' - page', __FILE__, __LINE__ );
			$offset = ( $per_page * $page ) - $per_page;
			// $this->debug_log( $offset, __METHOD__ . ' - offset', __FILE__, __LINE__ );
			$dummy_posts = array();

			if ( ! empty( $data ) ) {
				foreach ( $data as $cpt => $posts ) {
					$this->debug_log( count( $posts ), __METHOD__ . ':dummy_posts:' . $cpt, __FILE__, __LINE__ );

					$dummy_posts = array_merge( $dummy_posts, $posts );
				}
			}

			$total = count( $dummy_posts );

			$dummy_posts = array_slice( $dummy_posts, $offset, $per_page );
			$this->debug_log( count( $dummy_posts ), __METHOD__ . ':dummy_posts import', __FILE__, __LINE__ );

			if ( empty( $dummy_posts ) ) {
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return array( 'success' => true, 'total' => $total, 'page' => $page, 'offset' => $offset );
			}

			$params = array(
				'action' => 'remote_import_posts',
				'posts' => $dummy_posts,
				'remove_dummy_data' => $page === 1 ? true : false,
				'page' => $page,
				'total' => absint( $total ),
				'offset' => absint( $offset )
			);

			$request = new WP_REST_Request( 'POST' );
			$request->set_body_params( $params );

			$_response = self::$instance->client->do_action( $request );
			//$this->debug_log( $_response, __METHOD__ . ':response', __FILE__, __LINE__ );

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			if ( is_wp_error( $_response ) ) {
				return $_response;
			} elseif ( $_response->is_error() ) {
				return $_response->as_error();
			} else {
				return $_response->get_data();
			}
		}

		/**
		 * Set widgets.
		 */
		public function set_widgets( $demo_file, $demo ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$widgets = $this->get_demo_data( $demo_file, 'widgets' );
			//$this->debug_log( $widgets, __METHOD__ . ' - widgets', __FILE__, __LINE__ );

			if ( empty( $widgets ) ) {
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return array( 'success' => false );
			}

			$data = $widgets;
			$data['action'] = 'remote_import_options';

			$request = new WP_REST_Request( 'POST' );
			$request->set_body_params( $data );

			$_response = self::$instance->client->do_action( $request );
			//$this->debug_log( $_response, __METHOD__ . ':response', __FILE__, __LINE__ );

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			if ( is_wp_error( $_response ) ) {
				return $_response;
			} elseif ( $_response->is_error() ) {
				return $_response->as_error();
			} else {
				return $_response->get_data();
			}
		}

		/**
		 * Set menus.
		 */
		public function set_menus( $demo_file, $demo ) {
			$this->debug_log( 'start', __METHOD__, __FILE__, __LINE__ );

			$menus = $this->get_demo_data( $demo_file, 'menus' );
			//$this->debug_log( $menus, __METHOD__ . ' - menus', __FILE__, __LINE__ );
			if ( is_wp_error( $menus ) ) {
				$menus = array();
			}

			if ( empty( $menus ) ) {
				$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

				return array( 'success' => false );
			}

			$data = array();
			$data['action'] = 'remote_import_menus';
			$data['menus'] = $menus;

			$request = new WP_REST_Request( 'POST' );
			$request->set_body_params( $data );

			$_response = self::$instance->client->do_action( $request );
			//$this->debug_log( $_response, __METHOD__ . ':response', __FILE__, __LINE__ );

			$this->debug_log( 'end', __METHOD__, __FILE__, __LINE__ );

			if ( is_wp_error( $_response ) ) {
				return $_response;
			} elseif ( $_response->is_error() ) {
				return $_response->as_error();
			} else {
				return $_response->get_data();
			}
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

		/**
		 * Get the WP Filesystem access.
		 *
		 * @return object The WP Filesystem.
		 */
		public function get_wp_filesystem() {
			if ( ! function_exists( 'get_filesystem_method' ) ) {
				require_once( ABSPATH . "/wp-admin/includes/file.php" );
			}

			$access_type = get_filesystem_method();

			if ( $access_type === 'direct' ) {
				/* You can safely run request_filesystem_credentials() without any issues and don't need to worry about passing in a URL */
				$creds = request_filesystem_credentials( trailingslashit( site_url() ) . 'wp-admin/', '', false, false, array() );

				/* Initialize the API */
				if ( ! WP_Filesystem( $creds ) ) {
					/* Any problems and we exit */
					return false;
				}

				global $wp_filesystem;

				return $wp_filesystem;
				/* Do our file manipulations below */
			} else if ( defined( 'FTP_USER' ) ) {
				$creds = request_filesystem_credentials( trailingslashit( site_url() ) . 'wp-admin/', '', false, false, array() );

				/* Initialize the API */
				if ( ! WP_Filesystem( $creds ) ) {
					/* Any problems and we exit */
					return false;
				}

				global $wp_filesystem;

				return $wp_filesystem;
			} else {
				/* Don't have direct write access. Prompt user with our notice */
				return false;
			}
		}

		/**
		 * Checks which method we're using to serve downloads.
		 *
		 * If using force or x-sendfile, this ensures the .htaccess is in place.
		 */
		public function check_ayecode_demo_folder_protection() {
			$upload_dir      = wp_get_upload_dir();
			$downloads_path  = $upload_dir['basedir'] . '/ayedemo-download/';

			if ( wp_mkdir_p( $downloads_path ) ) {
				// index.php
				$index_pathname = $downloads_path . 'index.php';
				if ( ! file_exists( $index_pathname ) ) {
					$file = @fopen( $index_pathname, 'w' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen

					if ( false === $file ) {
						return new WP_Error( 'demo_download_protect_failed', __( 'Unable to protect demo data download folder from browsing.' ) );
					}

					fwrite( $file, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
					fclose( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
				}

				return true;
			} else {
				return new WP_Error( 'demo_download_folder_error', __( 'Unable to create demo data download folder.' ) );
			}
		}
	}

	/**
	 * Run the class if found.
	 */
	AyeCode_Demo_Content::instance();
}

/*
Import order

theme
plugins

CPTs
- Settings
- Price packages
- Custom Fields
- Search items
- Sort orders
- Tabs
- Posts
- Media? Do we import images or hotlink?

GD General Settings

Widgets
Menus (make it later so we know what items to add from CPTs)
Customizer settings
*/