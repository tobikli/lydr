<?php
/**
 * Trait for displaying code revisions in the admin.
 *
 * @package WPCode
 */

/**
 * Trait WPCode_Revisions_Display_Lite.
 */
trait WPCode_Revisions_Display_Lite {
	/**
	 * Get a code revisions list with a notice on top.
	 *
	 * @param string $title The title for the notice.
	 * @param string $description Description or text below the title.
	 * @param array  $button_1 Button 1 params for the get_upsell_box method.
	 * @param array  $button_2 Button 2 params for the get_upsell_box method.
	 *
	 * @return string
	 */
	public function code_revisions_list_with_notice( $title, $description = '', $button_1 = array(), $button_2 = array() ) {
		$html = '<div class="wpcode-revisions-list-area">';

		$html .= $this->get_code_revisions_empty_list();
		$html .= WPCode_Admin_Page::get_upsell_box(
			$title,
			$description,
			$button_1,
			$button_2
		);
		$html .= '</div>';// .wpcode-revisions-list-area.

		return $html;
	}

	/**
	 * Get the markup for a revision item in the list of revisions.
	 *
	 * @param int    $author_id The author id to display the avatar and name for.
	 * @param string $date The date used to display time passed.
	 * @param array  $actions Links specific to this row.
	 *
	 * @return string
	 */
	public function get_revision_item( $author_id, $date, $actions = array() ) {
		$list_item = '<li class="wpcode-revision-list-item">';

		$list_item .= get_avatar( $author_id, 30 );
		$list_item .= sprintf(
			'<span class="wpcode-revision-list-author">%s</span>',
			get_the_author_meta( 'display_name', $author_id )
		);
		$list_item .= sprintf(
			'<span class="wpcode-revision-list-date">%s</span>',
			$date
		);
		if ( ! empty( $actions ) ) {
			$list_item .= sprintf(
				'<span class="wpcode-revision-list-item-actions">%s</span>',
				implode( '', $actions )
			);
		}
		$list_item .= '</li>';

		return $list_item;
	}

	/**
	 * Get a list of code revisions to use behind the notice.
	 *
	 * @return string
	 */
	public function get_code_revisions_empty_list() {
		$list           = array();
		$post_modified  = isset( $this->snippet ) ? strtotime( $this->snippet->get_post_data()->post_modified ) : time();
		$snippet_author = isset( $this->snippet ) ? $this->snippet->get_snippet_author() : get_current_user_id();
		$revisions_data = array(
			$post_modified,
			$post_modified - DAY_IN_SECONDS,
			$post_modified - WEEK_IN_SECONDS,
			$post_modified - 2 * WEEK_IN_SECONDS,
			$post_modified - MONTH_IN_SECONDS,
			$post_modified - 2 * MONTH_IN_SECONDS,
		);

		$compare = sprintf(
			'<span>%s</span>',
			esc_html__( 'Compare', 'insert-headers-and-footers' )
		);
		$view    = sprintf(
			'<span>%s</a>',
			get_wpcode_icon( 'eye', 16, 11, '0 0 16 11' )
		);

		foreach ( $revisions_data as $revisions_date ) {
			$updated = sprintf(
			// Translators: time since the revision has been updated.
				esc_html__( 'Updated %s ago', 'insert-headers-and-footers' ),
				human_time_diff( $revisions_date )
			);

			$list[] = $this->get_revision_item(
				$snippet_author,
				$updated,
				array(
					$compare,
					$view,
				)
			);
		}

		$html = '<div class="wpcode-blur-area">';

		$html .= sprintf(
			'<ul class="wpcode-revisions-list">%s</ul>',
			implode( '', $list )
		);

		$button_text = sprintf(
		// Translators: The placeholder gets replaced with the extra number of revisions available.
			esc_html__( '%d Other Revisions', 'insert-headers-and-footers' ),
			3
		);

		$html .= sprintf(
			'<button type="button" class="wpcode-button wpcode-button-secondary wpcode-button-icon" id="wpcode-show-all-snippets">%1$s %2$s</button>',
			get_wpcode_icon( 'rewind', 16, 14 ),
			$button_text
		);

		$html .= '</div>';// .wpcode-blur-area.

		return $html;
	}
}
