<?php
/**
 * WPCode_Admin_Bar_Info_Lite class.
 *
 * @package WPCode
 */

/**
 * Class WPCode_Admin_Bar_Info_Lite.
 *
 * @extends WPCode_Admin_Bar_Info
 */
class WPCode_Admin_Bar_Info_Lite extends WPCode_Admin_Bar_Info {

	/**
	 * Add the WPCode info to the admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
	 */
	public function add_admin_bar_info( $wp_admin_bar ) {
		parent::add_admin_bar_info( $wp_admin_bar );

		// Only show this on pages where the page scripts is an option.
		if ( is_singular() ) {
			$wp_admin_bar->add_menu(
				array(
					'id'     => 'wpcode-page-scripts',
					'parent' => 'wpcode-admin-bar-info',
					'title'  => esc_html__( 'Page Scripts', 'insert-headers-and-footers' ) . $this->get_pro_indicator(),
					'meta'   => array(
						'class' => 'wpcode-admin-bar-has-upsell-submenu',
					),
				)
			);
			$wp_admin_bar->add_menu(
				array(
					'id'     => 'wpcode-page-scripts-upgrade',
					'parent' => 'wpcode-page-scripts',
					'meta'   => array(
						'class' => 'wpcode-admin-bar-upsell-submenu',
						'html'  => $this->get_upsell_markup(),
					),
				)
			);
		}
	}

	/**
	 * Get the pro indicator.
	 *
	 * @return string
	 */
	public function get_pro_indicator() {
		return ' <span class="wpcode-pro-indicator">PRO</span>';
	}

	/**
	 * Add upgrade link to the admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The admin bar instance.
	 *
	 * @return void
	 */
	public function add_admin_bar_quick_links( $wp_admin_bar ) {
		parent::add_admin_bar_quick_links( $wp_admin_bar );

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'wpcode-upgrade',
				'parent' => 'wpcode-admin-bar-info',
				'title'  => esc_html__( 'Upgrade to Pro', 'insert-headers-and-footers' ),
				'meta'   => array(
					'class'  => 'wpcode-admin-bar-info-submenu',
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
				),
				'href'   => wpcode_utm_url( 'https://wpcode.com/lite/', 'admin-bar', 'upgrade-to-pro' ),
			)
		);
	}

	/**
	 * Get the upsell markup.
	 *
	 * @return string
	 */
	public function get_upsell_markup() {

		$html = '<div class="wpcode-admin-bar-submenu-upsell">';

		$html .= '<span class="wpcode-heading">' . esc_html__( 'Page Scripts is a Pro Feature', 'insert-headers-and-footers' ) . '</span>';
		$html .= '<p>' . esc_html__( 'While you can always use global snippets, in the PRO version you can easily add page-specific scripts and snippets directly from the post edit screen.', 'insert-headers-and-footers' ) . '</p>';
		$html .= '<a class="wpcode-button" href="' . esc_url( wpcode_utm_url( 'https://wpcode.com/lite/', 'admin-bar', 'page-scripts' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Upgrade to Pro and Unlock Page Scripts', 'insert-headers-and-footers' ) . '</a>';

		$html .= '</div>';

		return $html;
	}

}