<?php

namespace Blocksy\DbVersioning;

class V2031 {
	public function migrate() {
		$this->migrate_share_box_title();
		$this->migrate_share_box_title_products();
		$this->migrate_post_types_extra_filters();
	}

	public function migrate_share_box_title() {
		$prefixes = blocksy_manager()->screen->get_single_prefixes([
			'has_bbpress' => true,
			'has_buddy_press' => true
		]);

		foreach ($prefixes as $prefix) {
			$has_share_box_title = get_theme_mod(
				$prefix . '_has_share_box_title',
				'no'
			);

			$share_box_title = get_theme_mod(
				$prefix . '_share_box_title',
				__('Share your love', 'blocksy')
			);

			if ($has_share_box_title === 'no') {
				set_theme_mod($prefix . '_share_box_title', '');
			}
		}
	}

	public function migrate_share_box_title_products() {
		$woo_single_layout = get_theme_mod(
			'woo_single_layout',
			[]
		);

		if (! empty($woo_single_layout)) {
			$descriptor = $this->migrate_share_box_in_layout($woo_single_layout);

			if ($descriptor['changed']) {
				set_theme_mod('woo_single_layout', $descriptor['layout']);
			}
		}

		$woo_single_split_layout = get_theme_mod(
			'woo_single_split_layout',
			[
				'left' => [],
				'right' => []
			]
		);

		$split_changed = false;

		if (! empty($woo_single_split_layout['left'])) {
			$descriptor = $this->migrate_share_box_in_layout(
				$woo_single_split_layout['left']
			);

			if ($descriptor['changed']) {
				$split_changed = true;
				$woo_single_split_layout['left'] = $descriptor['layout'];
			}
		}

		if (! empty($woo_single_split_layout['right'])) {
			$descriptor = $this->migrate_share_box_in_layout(
				$woo_single_split_layout['right']
			);

			if ($descriptor['changed']) {
				$split_changed = true;
				$woo_single_split_layout['right'] = $descriptor['layout'];
			}
		}

		if ($split_changed) {
			set_theme_mod('woo_single_split_layout', $woo_single_split_layout);
		}
	}

	public function migrate_share_box_in_layout($layout) {
		$changed = false;

		foreach ($layout as $index => $element) {
			if ($element['id'] !== 'product_sharebox') {
				continue;
			}

			$has_share_box_title = blocksy_akg(
				'has_share_box_title',
				$element,
				'no'
			);

			$share_box_title = get_theme_mod(
				'share_box_title',
				$element,
				__('Share your love', 'blocksy')
			);

			if ($has_share_box_title === 'no') {
				$layout[$index]['share_box_title'] = '';
				$changed = true;
			}
		}

		return [
			'layout' => $layout,
			'changed' => $changed
		];
	}

	public function migrate_post_types_extra_filters() {
		$prefixes = blocksy_manager()->screen->get_archive_prefixes();

		foreach ($prefixes as $prefix) {
			$has_archive_filtering = get_theme_mod(
				$prefix . '_has_archive_filtering',
				'no'
			);

			if ($has_archive_filtering !== 'yes') {
				continue;
			}

			$filter_font_color = get_theme_mod(
				$prefix . '_filter_font_color',
				'__empty__'
			);

			if ($filter_font_color === '__empty__') {
				continue;
			}

			$filter_type = get_theme_mod(
				$prefix . '_filter_type',
				'__empty__'
			);

			if ($filter_type !== 'buttons') {
				continue;
			}

			if (
				isset($filter_font_color['default_2'])
				||
				isset($filter_font_color['hover_2'])
				||
				! isset($filter_font_color['default'])
				||
				! isset($filter_font_color['hover'])
			) {
				continue;
			}

			$filter_font_color['default_2'] = $filter_font_color['default'];
			$filter_font_color['hover_2'] = $filter_font_color['hover'];

			set_theme_mod($prefix . '_filter_font_color', $filter_font_color);
		}
	}
}

