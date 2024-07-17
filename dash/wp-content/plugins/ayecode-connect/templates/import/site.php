<?php
global $ac_site_args,$ac_prefix;
$licences = get_option( $ac_prefix . "_licences" );
?>
<div class="col mb-4" >
	<div class="card h-100 p-0 m-0 mw-100  " data-demo="<?php echo esc_attr($ac_site_args->slug);?>">
		<div class="card-img-top overflow-hidden position-relative ">
			<div class="geodir-post-slider bsui sdel-b2db03d5"><div class=" geodir-image-container geodir-image-sizes-medium_large   ">
					<div class="geodir-images geodir-images-n-1 geodir-images-image carousel-inner  ">
						<div class="carousel-item  active">
							<a href="https://demos.ayecode.io/<?php echo esc_attr($ac_site_args->slug); ?>" onclick="ac_preview_site(this);return false;" class="embed-has-action embed-responsive embed-responsive-16by9 d-block">
								<img src="https://wordpress.com/mshots/v1/https://demos.ayecode.io/<?php echo esc_attr($ac_site_args->slug); ?>?w=825&h=430"  alt="" class="w-100 p-0 m-0 mw-100 border-0 embed-responsive-item embed-item-cover-xy"  >
								<i class="far fa-eye"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="card-body d-none">
			<?php echo esc_attr( $ac_site_args->desc ); ?>
		</div>

		<div class="sd-src-theme d-none">
			<?php
			if(!empty($ac_site_args->theme)){
				$paid_theme = !empty($ac_site_args->theme->paid);

				$flex_wrap = '';
				if ( $paid_theme ) {
					$theme_active = get_template() == $ac_site_args->theme->slug;
					$badge_class = $theme_active ? 'badge-success' : 'badge-danger';
					$type_badge = '<span class="badge ' . $badge_class . '">'.__("Paid","ayecode-connect").'</span>';


					// warning
					if ( !$theme_active ) {
						$url = !empty($ac_site_args->theme->AuthorURI) ? esc_url($ac_site_args->theme->AuthorURI) : '';
						$warning = sprintf( __("This is a 3rd party paid theme, please install and activate this theme FIRST. %sGet Product%s","ayecode-connect"),"<br><a href='$url' target='_blank'>","</a>" );
						$flex_wrap = 'flex-wrap';
						$type_badge .= '<div class="alert alert-warning p-2 mx-0 mb-0 mt-2" role="alert">'.$warning.'</div>';
					}
				}else{
					$type_badge = '<span class="badge badge-success">'.__("Free","ayecode-connect").'</span>';
				}

				?>
				<h4 class="h5"><?php _e("Theme","ayecode-connect");?></h4>
				<ul class="list-group">
					<li class="list-group-item d-flex justify-content-between align-items-center mb-0 p-2 flex-wrap <?php echo $flex_wrap;?>"><?php echo esc_attr($ac_site_args->theme->Name);?>
						<?php echo $type_badge; ?>
					</li>
				</ul>
				<?php
			}
			?>
		</div>

		<div class="sd-src-plugins d-none">
			<?php
			if(!empty($ac_site_args->plugins)){
				?>
				<h4 class="h5"><?php _e("Plugins","ayecode-connect");?></h4>
				<ul class="list-group">
				<?php
				foreach($ac_site_args->plugins as $slug => $plugin){
					$product = !empty($plugin->{'Update URL'}) ? str_replace(array("http://","https://","/"),"",$plugin->{'Update URL'}) : '';
					$product_id = !empty($plugin->{'Update ID'}) ? absint($plugin->{'Update ID'}) : 0;
					$has_license = !empty($product) && isset($licences[$product][$product_id]) && ( $licences[$product][$product_id]->expires == '0' || $licences[$product][$product_id]->expires > time()) ? true : false;

					$type_badge = '';
					$flex_wrap = '';
					if( $product_id ){
						$valid_class = $has_license ? 'badge-success' : 'badge-danger';
						$type_badge = ' <span class="badge '.$valid_class.'">'.__("Paid","ayecode-connect").'</span>';
					}else{
						$type_badge = ' <span class="badge badge-success">'.__("Free","ayecode-connect").'</span>';
					}

					// paid external check
					if( !empty($plugin->paid_external)){
						$url = !empty($plugin->AuthorURI) ? esc_url($plugin->AuthorURI) : '';
						$warning = '';
						if ( is_plugin_active( $slug ) ) {
							$valid_class = 'badge-success';
						}else{
							$valid_class = 'badge-danger';
							$warning = sprintf( __("This is a 3rd party paid product, please install and activate this plugin FIRST. %sGet Product%s","ayecode-connect"),"<br><a href='$url' target='_blank'>","</a>" );
						}
						$type_badge = ' <a href="'.$url.'" target="_blank" ><span class="badge '.$valid_class.'">'.__("Paid 3rd Party","ayecode-connect").'</span></a>';

						// warning
						if ( $warning ) {
							$flex_wrap = 'flex-wrap';
							$type_badge .= '<div class="alert alert-warning p-2 mx-0 mb-0 mt-2" role="alert">'.$warning.'</div>';
						}
					}

					?>
					<li class="list-group-item d-flex justify-content-between align-items-center mb-0 p-2 <?php echo $flex_wrap;?>">
						<?php echo esc_attr( $plugin->Name );



						echo $type_badge;
						?>
					</li>
					<?php
				}
				?>
				</ul>
				<?php

			}
			?>
		</div>

		<div class="card-footer text-muted bg-white">
			<div class="row d-flex align-items-center">
				<div class="col">
					<div class="card-title h5 m-0 p-0">
						<?php
						echo esc_attr( $ac_site_args->title );


						if ( isset( $ac_site_args->requires ) ) {
							if ( in_array( 'elementor-pro', $ac_site_args->requires ) ) {
								echo '<span class="ml-2 h4"><i style="color:#db3157;" class="fab fa-elementor" data-toggle="tooltip" title="'.__('Requires Elementor Pro','ayecode-connect').'"></i></span>';
							}
						}
//						print_r($ac_site_args);
						?>
					</div>
				</div>
				<div class="col-2">
					<a href="https://demos.ayecode.io/<?php echo esc_attr($ac_site_args->slug); ?>" onclick="ac_preview_site(this);return false;" class="btn btn-primary btn-sm ml-auto float-right" role="button" aria-pressed="true"><?php _e("View","ayecode-connect");?></a>
				</div>
			</div>

		</div>
	</div>
</div>