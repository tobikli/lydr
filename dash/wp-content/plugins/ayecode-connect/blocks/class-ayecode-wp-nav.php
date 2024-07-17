<?php
/**
 * GD WP Nav Widget
 *
 * @package GeoDirectory
 * @since 2.3.56
 */

/**
 * GeoDir_Widget_Page_Title class.
 *
 * @todo changes in this widget should be synced with the UWP > WP Nav widget
 */
class AyeCode_WP_Nav extends WP_Super_Duper {

	/**
	 * Sets up a widget instance.
	 */
	public function __construct() {
		$options = array(
			'textdomain'    => 'ayecode',
			'output_types'     => array( 'block', 'shortcode' ),
			'block-icon'    => 'fas fa-link',
			'block-category'=> 'design',
			'block-keywords'=> "['nav','menu','ayecode-connect','userswp','getpaid']",
			'block-supports'    => ['customClassName' => false],
			'block-wrap'       => '',
			'no_wrap'           => true,
			'class_name'    => __CLASS__,
			'block-edit-return' => "el('div', wp.blockEditor.useBlockProps({
									dangerouslySetInnerHTML: {__html: onChangeContent()},
									className: 'wp-block-navigation-item has-link bsui' ,
								}))",
			'base_id'       => 'aye_nav',
			'name'          => __( 'AyeNav', 'ayecode-connect' ),
			"parent" 		=>  array( 'core/navigation',  'core/navigation-submenu' ),
			'widget_ops'    => array(
				'classname'   => 'ayecode-wp-nav-container ',
				'description' => esc_html__( 'A Block to add GeoDirectory and UsersWP dynamic menu items to the WP Navigation Block.', 'ayecode-connect' ),
			),
			'block_group_tabs'  => [
				'content'  => [
					'groups' => [ __('Link', 'ayecode-connect') ],
					'tab'    => [
						'title'     => __('Content', 'ayecode-connect'),
						'key'       => 'bs_tab_content',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center',
					],
				],
				'styles'   => [
					'groups' => [
						__('Link styles', 'ayecode-connect'),
						__('Typography', 'ayecode-connect'),
					],
					'tab'    => [
						'title'     => __('Styles', 'ayecode-connect'),
						'key'       => 'bs_tab_styles',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center',
					],
				],
				'advanced' => [
					'groups' => [
						__('Wrapper Styles', 'ayecode-connect'),
						__('Advanced', 'ayecode-connect'),
					],
					'tab'    => [
						'title'     => __('Advanced', 'ayecode-connect'),
						'key'       => 'bs_tab_advanced',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center',
					],
				],
			],

		);


		parent::__construct( $options );
	}

	public function link_types()
	{
		$links = [
			'home'      => __('Home', 'ayecode-connect'),
			'page'      => __('Page', 'ayecode-connect'),
			'post-id'   => __('Post ID', 'ayecode-connect'),
			'wp-login'  => __('WP Login (logged out)', 'ayecode-connect'),
			'wp-logout' => __('WP Logout (logged in)', 'ayecode-connect'),
			'custom'    => __('Custom URL', 'ayecode-connect'),
			'lightbox'  => __('Open Lightbox', 'ayecode-connect'),
		];

		if (defined('GEODIRECTORY_VERSION')) {
			$post_types           = function_exists('geodir_get_posttypes') ? geodir_get_posttypes('options-plural') : [];
			$links['gd_search']   = __('GD Search', 'ayecode-connect');
			$links['gd_location'] = __('GD Location', 'ayecode-connect');
			foreach ($post_types as $cpt => $cpt_name) {
				// translators: Custom Post Type name.
				$links[$cpt] = sprintf(__('%s (archive)', 'ayecode-connect'), $cpt_name);
				// translators: Custom Post Type name.
				$links['add_'.$cpt] = sprintf(__('%s (add listing)', 'ayecode-connect'), $cpt_name);
			}
		}

		if (defined('GEODIRLOCATION_VERSION')) {
			$links['gd_location_switcher'] = __('GD Location Switcher', 'ayecode-connect');
		}

		if (defined('USERSWP_VERSION')) {
			// logged out
			$links['uwp_login']    = __('UWP Login (logged out)', 'ayecode-connect');
			$links['uwp_register'] = __('UWP Register (logged out)', 'ayecode-connect');
			$links['uwp_forgot']   = __('UWP Forgot Password? (logged out)', 'ayecode-connect');

			// logged in
			$links['uwp_account']         = __('Account (logged in)', 'ayecode-connect');
			$links['uwp_change_password'] = __('Change Password (logged in)', 'ayecode-connect');
			$links['uwp_profile']         = __('Profile (logged in)', 'ayecode-connect');
			$links['uwp_logout']          = __('Log out (logged in)', 'ayecode-connect');
		}

		return $links;

	}//end link_types()


	public function get_pages_array()
	{
		$options = [ '' => __('Select Page', 'ayecode-connect') ];

		$pages = get_pages();

		if (! empty($pages)) {
			foreach ($pages as $page) {
				if ($page->post_title) {
					$options[$page->ID] = esc_attr($page->post_title);
				}
			}
		}

		return $options;

	}//end get_pages_array()


	/**
	 * Set the arguments later.
	 *
	 * @return array
	 */
	public function set_arguments()
	{
		$arguments = [];

		$arguments['type'] = [
			'type'     => 'select',
			'title'    => __('Link Type', 'ayecode-connect'),
			'options'  => $this->link_types(),
			'default'  => 'home',
			'desc_tip' => true,
			'group'    => __('Link', 'ayecode-connect'),
		];

		$arguments['page_id'] = [
			'type'            => 'select',
			'title'           => __('Page', 'ayecode-connect'),
			'options'         => $this->get_pages_array(),
			'placeholder'     => __('Select Page', 'ayecode-connect'),
			'default'         => '',
			'desc_tip'        => true,
			'group'           => __('Link', 'ayecode-connect'),
			'element_require' => '[%type%]=="page"',
		];

		$arguments['post_id'] = [
			'type'            => 'number',
			'title'           => __('Post ID', 'ayecode-connect'),
			'placeholder'     => 123,
			'default'         => '',
			'desc_tip'        => true,
			'group'           => __('Link', 'ayecode-connect'),
			'element_require' => '[%type%]=="post-id"',
		];

		$arguments['custom_url'] = [
			'type'            => 'text',
			'title'           => __('Custom URL', 'ayecode-connect'),
			'desc'            => __('Add custom link URL', 'ayecode-connect'),
			'placeholder'     => __('https://example.com', 'ayecode-connect'),
			'default'         => '',
			'desc_tip'        => true,
			'group'           => __('Link', 'ayecode-connect'),
			'element_require' => '( [%type%]=="custom" || [%type%]=="lightbox" )',
		];

		$arguments['lightbox_notice'] = [
			'type'            => 'notice',
			'desc'            => __('Enter the BS > Contact form ID prefixed by a `#` eg: #contact-form', 'ayecode-connect'),
			'status'          => 'info',
			'group'           => __('Link', 'ayecode-connect'),
			'element_require' => '[%type%]=="lightbox"',
		];

		$arguments['text'] = [
			'type'        => 'text',
			'title'       => __('Link Text', 'ayecode-connect'),
			'desc'        => __('Add custom link text or leave blank for dynamic', 'ayecode-connect'),
			'placeholder' => __('Home', 'ayecode-connect'),
			'default'     => '',
			'desc_tip'    => true,
			'group'       => __('Link', 'ayecode-connect'),
		];

		$arguments['icon_class'] = [
			'type'        => 'text',
			'title'       => __('Icon class', 'ayecode-connect'),
			'desc'        => __('Enter a font awesome icon class.', 'ayecode-connect'),
			'placeholder' => __('fas fa-ship', 'ayecode-connect'),
			'default'     => '',
			'desc_tip'    => true,
			'group'       => __('Link', 'ayecode-connect'),
		];

		$arguments['icon_aria_label'] = [
			'type'            => 'text',
			'title'           => __('Aria label', 'ayecode-connect'),
			'desc'            => __('Describe the link for assistive technologies.', 'ayecode-connect'),
			'placeholder'     => __('Visit our facebook page', 'ayecode-connect'),
			'default'         => '',
			'desc_tip'        => true,
			'group'           => __('Link', 'ayecode-connect'),
			'element_require' => '( [%icon_class%]!="" && [%text%]=="" )',
		];

		if ( function_exists( 'sd_get_new_window_input' ) ) {
			$arguments['link_new_window'] = sd_get_new_window_input();
			$arguments['link_nofollow']   = sd_get_nofollow_input();
			$arguments['link_attributes'] = sd_get_attributes_input();
		}

		// Link styles.
		$arguments['link_type'] = [
			'type'     => 'select',
			'title'    => __('Link style', 'ayecode-connect'),
			'options'  => [
				''             => __('None', 'ayecode-connect'),
				'btn'          => __('Button', 'ayecode-connect'),
				'btn-round'    => __('Button rounded', 'ayecode-connect'),
				'btn-icon'     => __('Button Icon Circle', 'ayecode-connect'),
				'iconbox'      => __('Iconbox bordered', 'ayecode-connect'),
				'iconbox-fill' => __('Iconbox filled', 'ayecode-connect'),
			],
			'default'  => '',
			'desc_tip' => true,
			'group'    => __('Link styles', 'ayecode-connect'),
		];

		$arguments['link_size'] = [
			'type'            => 'select',
			'title'           => __('Size', 'ayecode-connect'),
			'options'         => [
				''            => __('Default', 'ayecode-connect'),
				'extra-small' => __('Extra Small (BS5)', 'ayecode-connect'),
				'small'       => __('Small', 'ayecode-connect'),
				'medium'      => __('Medium', 'ayecode-connect'),
				'large'       => __('Large', 'ayecode-connect'),
			],
			'default'         => '',
			'desc_tip'        => true,
			'group'           => __('Link styles', 'ayecode-connect'),
			'element_require' => '[%link_type%]!=""',
		];

		$arguments['link_bg'] = [
			'title'           => __('Color', 'ayecode-connect'),
			'desc'            => __('Select the color.', 'ayecode-connect'),
			'type'            => 'select',
			'options'         => ([
					'' => __('Custom colors', 'ayecode-connect'),
				] + sd_aui_colors(true, true, true, true)),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => __('Link styles', 'ayecode-connect'),
			'element_require' => '[%link_type%]!="iconbox" && [%link_type%]!=""',
		];

		// padding
		$arguments['link_pt'] = sd_get_padding_input(
			'pt',
			[
				'device_type' => 'Mobile',
				'group'       => __(
					'Link styles',
					'ayecode-connect'
				),
			]
		);
		$arguments['link_pr'] = sd_get_padding_input(
			'pr',
			[
				'device_type' => 'Mobile',
				'group'       => __(
					'Link styles',
					'ayecode-connect'
				),
			]
		);
		$arguments['link_pb'] = sd_get_padding_input(
			'pb',
			[
				'device_type' => 'Mobile',
				'group'       => __(
					'Link styles',
					'ayecode-connect'
				),
			]
		);
		$arguments['link_pl'] = sd_get_padding_input(
			'pl',
			[
				'device_type' => 'Mobile',
				'group'       => __(
					'Link styles',
					'ayecode-connect'
				),
			]
		);

		// padding tablet
		$arguments['link_pt_md'] = sd_get_padding_input(
			'pt',
			[
				'device_type' => 'Tablet',
				'group'       => __(
					'Link styles',
					'ayecode-connect'
				),
			]
		);
		$arguments['link_pr_md'] = sd_get_padding_input(
			'pr',
			[
				'device_type' => 'Tablet',
				'group'       => __(
					'Link styles',
					'ayecode-connect'
				),
			]
		);
		$arguments['link_pb_md'] = sd_get_padding_input(
			'pb',
			[
				'device_type' => 'Tablet',
				'group'       => __(
					'Link styles',
					'ayecode-connect'
				),
			]
		);
		$arguments['link_pl_md'] = sd_get_padding_input(
			'pl',
			[
				'device_type' => 'Tablet',
				'group'       => __(
					'Link styles',
					'ayecode-connect'
				),
			]
		);

		// padding desktop
		$arguments['link_pt_lg'] = sd_get_padding_input(
			'pt',
			[
				'device_type' => 'Desktop',
				'group'       => __(
					'Link styles',
					'ayecode-connect'
				),
			]
		);
		$arguments['link_pr_lg'] = sd_get_padding_input(
			'pr',
			[
				'device_type' => 'Desktop',
				'group'       => __(
					'Link styles',
					'ayecode-connect'
				),
			]
		);
		$arguments['link_pb_lg'] = sd_get_padding_input(
			'pb',
			[
				'device_type' => 'Desktop',
				'group'       => __(
					'Link styles',
					'ayecode-connect'
				),
			]
		);
		$arguments['link_pl_lg'] = sd_get_padding_input(
			'pl',
			[
				'device_type' => 'Desktop',
				'group'       => __(
					'Link styles',
					'ayecode-connect'
				),
			]
		);

		$arguments['link_divider'] = [
			'type'     => 'select',
			'title'    => __('Link Divider', 'ayecode-connect'),
			'options'  => [
				''      => __('None', 'ayecode-connect'),
				'left'  => __('Left', 'ayecode-connect'),
				'right' => __('Right', 'ayecode-connect'),
			],
			'default'  => '',
			'desc_tip' => true,
			'group'    => __('Link styles', 'ayecode-connect'),
		];

		// text color
		$arguments['text_color'] = sd_get_text_color_input();

		// Text justify
		$arguments['text_justify'] = sd_get_text_justify_input();

		// text align
		$arguments['text_align']    = sd_get_text_align_input(
			'text_align',
			[
				'device_type'     => 'Mobile',
				'element_require' => '[%text_justify%]==""',
			]
		);
		$arguments['text_align_md'] = sd_get_text_align_input(
			'text_align',
			[
				'device_type'     => 'Tablet',
				'element_require' => '[%text_justify%]==""',
			]
		);
		$arguments['text_align_lg'] = sd_get_text_align_input(
			'text_align',
			[
				'device_type'     => 'Desktop',
				'element_require' => '[%text_justify%]==""',
			]
		);

		// font weight
		$arguments['font_weight'] = sd_get_font_weight_input();

		// margins mobile
		$arguments['mt'] = sd_get_margin_input('mt', [ 'device_type' => 'Mobile' ]);
		$arguments['mr'] = sd_get_margin_input('mr', [ 'device_type' => 'Mobile' ]);
		$arguments['mb'] = sd_get_margin_input('mb', [ 'device_type' => 'Mobile' ]);
		$arguments['ml'] = sd_get_margin_input('ml', [ 'device_type' => 'Mobile' ]);

		// margins tablet
		$arguments['mt_md'] = sd_get_margin_input('mt', [ 'device_type' => 'Tablet' ]);
		$arguments['mr_md'] = sd_get_margin_input('mr', [ 'device_type' => 'Tablet' ]);
		$arguments['mb_md'] = sd_get_margin_input('mb', [ 'device_type' => 'Tablet' ]);
		$arguments['ml_md'] = sd_get_margin_input('ml', [ 'device_type' => 'Tablet' ]);

		// margins desktop
		$arguments['mt_lg'] = sd_get_margin_input('mt', [ 'device_type' => 'Desktop' ]);
		$arguments['mr_lg'] = sd_get_margin_input('mr', [ 'device_type' => 'Desktop' ]);
		$arguments['mb_lg'] = sd_get_margin_input('mb', [ 'device_type' => 'Desktop' ]);
		$arguments['ml_lg'] = sd_get_margin_input('ml', [ 'device_type' => 'Desktop' ]);

		// padding
		$arguments['pt'] = sd_get_padding_input('pt', [ 'device_type' => 'Mobile' ]);
		$arguments['pr'] = sd_get_padding_input('pr', [ 'device_type' => 'Mobile' ]);
		$arguments['pb'] = sd_get_padding_input('pb', [ 'device_type' => 'Mobile' ]);
		$arguments['pl'] = sd_get_padding_input('pl', [ 'device_type' => 'Mobile' ]);

		// padding tablet
		$arguments['pt_md'] = sd_get_padding_input('pt', [ 'device_type' => 'Tablet' ]);
		$arguments['pr_md'] = sd_get_padding_input('pr', [ 'device_type' => 'Tablet' ]);
		$arguments['pb_md'] = sd_get_padding_input('pb', [ 'device_type' => 'Tablet' ]);
		$arguments['pl_md'] = sd_get_padding_input('pl', [ 'device_type' => 'Tablet' ]);

		// padding desktop
		$arguments['pt_lg'] = sd_get_padding_input('pt', [ 'device_type' => 'Desktop' ]);
		$arguments['pr_lg'] = sd_get_padding_input('pr', [ 'device_type' => 'Desktop' ]);
		$arguments['pb_lg'] = sd_get_padding_input('pb', [ 'device_type' => 'Desktop' ]);
		$arguments['pl_lg'] = sd_get_padding_input('pl', [ 'device_type' => 'Desktop' ]);

		// border
		$arguments['border']       = sd_get_border_input('border');
		$arguments['rounded']      = sd_get_border_input('rounded');
		$arguments['rounded_size'] = sd_get_border_input('rounded_size');

		// shadow
		$arguments['shadow'] = sd_get_shadow_input('shadow');

		// block visibility conditions
		$arguments['visibility_conditions'] = sd_get_visibility_conditions_input();

		$arguments['css_class'] = sd_get_class_input();

		if (function_exists('sd_get_custom_name_input')) {
			$arguments['metadata_name'] = sd_get_custom_name_input();
		}

		return $arguments;

	}//end set_arguments()

	/**
	 * This is the output function for the widget, shortcode and block (front end).
	 *
	 * @param array  $args        The arguments values.
	 * @param array  $widget_args The widget arguments when used.
	 * @param string $content     The shortcode content argument
	 *
	 * @return string
	 */
	public function output($args=[], $widget_args=[], $content='')
	{
		global $aui_bs5;

		$content = '';

		$link               = '#';
		$link_text          = '';
		$link_class         = 'wp-block-navigation-item__content';//$this->is_preview() ? 'wp-block-navigation-item__content' : 'wp-block-navigation-item wp-block-navigation-link';
		$link_attr          = '';
		$icon_aria_label    = ! empty($args['icon_aria_label']) ? 'aria-label="'.esc_attr($args['icon_aria_label']).'"' : '';
		$icon               = '';
		$link_divider_pos   = ! empty($args['link_divider']) ? esc_attr($args['link_divider']) : '';
		$link_divider_left  = 'left' === $link_divider_pos ? '<span class="navbar-divider d-none d-lg-block position-absolute top-50 start-0 translate-middle-y"></span>' : '';
		$link_divider_right = 'right' === $link_divider_pos ? '<span class="navbar-divider d-none d-lg-block position-absolute top-50 end-0 translate-middle-y"></span>' : '';

		$font_weight = ! empty($args['font_weight']) ? esc_attr($args['font_weight']) : '';
		unset($args['font_weight']);
		// we don't want it on the parent.
		$wrap_class = sd_build_aui_class($args);
		if ('spacer' === $args['type']) {
			return '<li class=" wp-block-navigation-item wp-block-navigation-link '.$wrap_class.'"></li>';
		} else if ('home' === $args['type']) {
			$link      = get_home_url();
			$link_text = __('Home', 'ayecode-connect');
		} else if ('page' === $args['type'] || 'post-id' === $args['type']) {
			$page_id = ! empty($args['page_id']) ? absint($args['page_id']) : 0;
			$post_id = ! empty($args['post_id']) ? absint($args['post_id']) : 0;
			$id      = 'page' === $args['type'] ? $page_id : $post_id;
			if ($id) {
				$page = get_post($id);
				if (! empty($page->post_title)) {
					$link      = get_permalink($id);
					$link_text = esc_attr($page->post_title);
				}
			}
		} else if ('wp-login' === $args['type']) {
			$args['icon_class'] = $args['icon_class'] ?: 'far fa-user';
			$link               = esc_url(wp_login_url(get_permalink()));
			$link_text          = __('Sign in', 'ayecode-connect');
		} else if ('wp-logout' === $args['type']) {
			// $icon      = 'fas fa-sign-out-alt';
			$args['icon_class'] = $args['icon_class'] ?: 'fas fa-sign-out-alt';
			$link               = esc_url(wp_logout_url(get_permalink()));
			$link_text          = __('Sign out', 'ayecode-connect');
		} else if ('lightbox' === $args['type']) {
			$link      = ! empty($args['custom_url']) ? esc_url_raw($args['custom_url']) : '#';
			$link_text = __('Open Lightbox', 'ayecode-connect');
			$link_attr = ' data-bs-toggle="modal" ';
		} else if ('custom' === $args['type']) {
			$link      = ! empty($args['custom_url']) ? esc_url_raw($args['custom_url']) : '#';
			$link_text = __('Custom', 'ayecode-connect');
		} else if ('gd_search' === $args['type']) {
			$link      = function_exists('geodir_search_page_base_url') ? geodir_search_page_base_url() : '';
			$link_text = __('Search', 'ayecode-connect');
		} else if ('gd_location' === $args['type']) {
			$link      = function_exists('geodir_location_page_id') ? get_permalink(geodir_location_page_id()) : '';
			$link_text = __('Location', 'ayecode-connect');
		} else if ('gd_location_switcher' === $args['type']) {
			global $geodirectory;
			$location_name = __('Set Locationx', 'ayecode-connect');
			$location_set  = true;

			// print_r($geodirectory->location);
			if (! empty($geodirectory->location->neighbourhood)) {
				$location_name = $geodirectory->location->neighbourhood;
			} else if (! empty($geodirectory->location->city)) {
				$location_name = $geodirectory->location->city;
			} else if (! empty($geodirectory->location->region)) {
				$location_name = $geodirectory->location->region;
			} else if (! empty($geodirectory->location->country)) {
				$location_name = __($geodirectory->location->country, 'ayecode-connect');
			} else {
				$location_set = false;
			}

			if ($location_set) {
				$icon_class         = ! empty($args['icon_class']) ? esc_attr($args['icon_class']) : 'fas fa-map-marker-alt fa-lg text-primary';
				$icon               = '<span class="hover-swap gdlmls-menu-icon"><i class="'.$icon_class.' hover-content-original"></i><i class="fas fa-times hover-content c-pointer" title="'.__('Clear Location', 'geodirlocation').'" data-toggle="tooltip"></i></span> ';
				$args['icon_class'] = '';
				$args['text']       = esc_attr($location_name);
			}

			$link      = '#location-switcher';
			$link_text = esc_attr($location_name);
		} else if (substr($args['type'], 0, 3) === 'gd_') {
			$post_types = function_exists('geodir_get_posttypes') ? geodir_get_posttypes('options-plural') : '';
			if (! empty($post_types)) {
				foreach ($post_types as $cpt => $cpt_name) {
					if ($cpt === $args['type']) {
						$link      = get_post_type_archive_link($cpt);
						$link_text = $cpt_name;
					}
				}
			}
		} else if (substr($args['type'], 0, 7) === 'add_gd_') {
			$post_types = function_exists('geodir_get_posttypes') ? geodir_get_posttypes('options') : '';
			if (! empty($post_types)) {
				foreach ($post_types as $cpt => $cpt_name) {
					if ('add_'.$cpt === $args['type']) {
						$link = function_exists('geodir_add_listing_page_url') ? geodir_add_listing_page_url($cpt) : '';
						// translators: Custom Post Type name.
						$link_text = sprintf(__('Add %s', 'ayecode-connect'), $cpt_name);
					}
				}
			}
		} else if ('uwp_login' === $args['type']) {
			$link        = function_exists('uwp_get_login_page_url') ? uwp_get_login_page_url() : '';
			$link_text   = __('Login', 'ayecode-connect');
			$wrap_class .= ' uwp-login-link';
		} else if ('uwp_register' === $args['type']) {
			$link        = function_exists('uwp_get_register_page_url') ? uwp_get_register_page_url() : '';
			$link_text   = __('Register', 'ayecode-connect');
			$wrap_class .= ' uwp-register-link';
		} else if ('uwp_forgot' === $args['type']) {
			$link        = function_exists('uwp_get_forgot_page_url') ? uwp_get_forgot_page_url() : '';
			$link_text   = __('Forgot Password', 'ayecode-connect');
			$wrap_class .= ' uwp-forgot-password-link';
		} else if ('uwp_account' === $args['type']) {
			$link      = function_exists('uwp_get_account_page_url') ? uwp_get_account_page_url() : '';
			$link_text = __('Account', 'ayecode-connect');
		} else if ('uwp_change_password' === $args['type']) {
			$link      = function_exists('uwp_get_change_page_url') ? uwp_get_change_page_url() : '';
			$link_text = __('Change password', 'ayecode-connect');
		} else if ('uwp_profile' === $args['type']) {
			$link      = function_exists('uwp_get_profile_page_url') ? uwp_get_profile_page_url() : '';
			$link_text = __('Profile', 'ayecode-connect');
		} else if ('uwp_logout' === $args['type']) {
			$link      = wp_logout_url(get_permalink());
			$link_text = __('Log out', 'ayecode-connect');
		}//end if

		// UWP maybe bail if logged in.
		if (in_array($args['type'], [ 'wp-login', 'uwp_login', 'uwp_register', 'uwp_forgot' ], true)) {
			if (! $this->is_block_content_call() && get_current_user_id()) {
				return '';
			}
		} else if (in_array($args['type'], [ 'wp-logout', 'uwp_account', 'uwp_change_password', 'uwp_profile', 'uwp_logout' ], true)) {
			if (! $this->is_block_content_call() && ! get_current_user_id()) {
				return '';
			}
		}

		// maybe set custom link text
		$link_text = ! empty($args['text']) ? esc_attr($args['text']) : $link_text;

		// link type
		if (! empty($args['link_type'])) {
			if ('btn' === $args['link_type']) {
				$link_class = 'btn';
			} else if ('btn-round' === $args['link_type']) {
				$link_class = 'btn btn-round';
			} else if ('btn-icon' === $args['link_type']) {
				$link_class = 'btn btn-icon rounded-circle';
			} else if ('iconbox' === $args['link_type']) {
				$link_class = 'iconbox rounded-circle';
			} else if ('iconbox-fill' === $args['link_type']) {
				$link_class = 'iconbox fill rounded-circle';
			}

			if ('btn' === $args['link_type'] || 'btn-round' === $args['link_type'] || 'btn-icon' === $args['link_type']) {
				$link_class .= ' btn-'.sanitize_html_class($args['link_bg']);
				if ('small' === $args['link_size']) {
					$link_class .= ' btn-sm';
				} else if ('extra-small' === $args['link_size']) {
					$link_class .= ' btn-xs';
				} else if ('large' === $args['link_size']) {
					$link_class .= ' btn-lg';
				}
			} else {
				$link_class .= 'iconbox-fill' === $args['link_type'] ? ' bg-'.sanitize_html_class($args['link_bg']) : '';
				if (empty($args['link_size']) || 'small' === $args['link_size']) {
					$link_class .= ' iconsmall';
				} else if ('medium' === $args['link_size']) {
					$link_class .= ' iconmedium';
				} else if ('large' === $args['link_size']) {
					$link_class .= ' iconlarge';
				}
			}
		}//end if

		if (! empty($args['text_color'])) {
			$link_class .= $aui_bs5 ? ' link-'.esc_attr($args['text_color']) : ' text-'.esc_attr($args['text_color']);
		}

		// link padding / font weight
		$link_class .= ' '.sd_build_aui_class(
				[
					'pt'          => $args['link_pt'],
					'pt_md'       => $args['link_pt_md'],
					'pt_lg'       => $args['link_pt_lg'],

					'pr'          => $args['link_pr'],
					'pr_md'       => $args['link_pr_md'],
					'pr_lg'       => $args['link_pr_lg'],

					'pb'          => $args['link_pb'],
					'pb_md'       => $args['link_pb_md'],
					'pb_lg'       => $args['link_pb_lg'],

					'pl'          => $args['link_pl'],
					'pl_md'       => $args['link_pl_md'],
					'pl_lg'       => $args['link_pl_lg'],

					'font_weight' => $font_weight,
				]
			);

		if (! empty($args['icon_class']) && $args['icon_class'] !== ' ') {
			// remove default text if icon exists.
			if (empty($args['text'])) {
				$link_text = '';
			}

			$mr   = $aui_bs5 ? ' me-2' : ' mr-2';
			$icon = ! empty($link_text) ? '<i class="'.esc_attr($args['icon_class']).$mr.'"></i>' : '<i class="'.esc_attr($args['icon_class']).'"></i>';
		}

		// if a button add form-inline
		if (! empty($args['link_type'])) {
			$wrap_class .= $aui_bs5 ? ' align-self-center' : ' form-inline';
		}

		if ( !$this->is_preview() && $link && function_exists( 'sd_build_attributes_string_escaped' ) ) {
			$attributes_escaped = sd_build_attributes_string_escaped(
				array(
					'new_window' => ! empty( $args['link_new_window'] ) ? 1 : '',
					'nofollow'   => ! empty( $args['link_nofollow'] ) ? 1 : '',
					'custom'     => ! empty( $args['link_attributes'] ) ? $args['link_attributes'] : array(),
				)
			);

			if ( $attributes_escaped ) {
				$link_attr .= ' ' . $attributes_escaped;
			}
		}

		if ($this->is_block_content_call()) {
			return $link_text || $icon ? '<a href="#'.esc_url_raw($link).'" class="'.esc_attr($link_class).'" '.$icon_aria_label.$link_attr.'>'.$link_divider_left.$icon.esc_attr($link_text).$link_divider_right.'</a>' : '';
			// shortcode
		} else {
			$link_class .= ' wp-block-navigation-item__content';
			return $link_text || $icon ? '<li class="wp-block-navigation-item wp-block-navigation-link '.$wrap_class.'"><a href="'.esc_url_raw($link).'" class="'.esc_attr($link_class).'" '.$icon_aria_label.$link_attr.'>'.$link_divider_left.$icon.esc_attr($link_text).$link_divider_right.'</a></li>' : '';
			// shortcode
		}

	}//end output()

}
