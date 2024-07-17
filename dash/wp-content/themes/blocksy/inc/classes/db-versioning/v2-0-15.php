<?php

namespace Blocksy\DbVersioning;

class V2015 {
	public function migrate() {
		$this->migrate_language_switcher('header_placements');
		$this->migrate_language_switcher('footer_placements');
	}

	public function migrate_language_switcher($panel_type) {
		$placements = get_theme_mod($panel_type, []);

		if (empty($placements)) {
			return;
		}

		$made_changes = false;

		foreach ($placements['sections'] as $section_index => $single_section) {
			foreach ($single_section['items'] as $item_index => $single_item) {
				if ($single_item['id'] !== 'language-switcher') {
					continue;
				}

				if (isset($single_item['values']['language_type'])) {
					$placements['sections'][
						$section_index
					]['items'][$item_index]['values'][
						'top_level_language_type'
					] = $single_item['values']['language_type'];

					$made_changes = true;
				}

				if (isset($single_item['values']['language_label'])) {
					$placements['sections'][
						$section_index
					]['items'][$item_index]['values'][
						'top_level_language_label'
					] = $single_item['values']['language_label'];

					$made_changes = true;
				}
			}
		}


		if ($made_changes) {
			set_theme_mod($panel_type, $placements);
		}
	}
}

