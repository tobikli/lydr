<?php

namespace Blocksy\DbVersioning;

class V2019 {
	public function migrate() {
		$this->migrate_menu_indicator_color('header_placements');
		$this->migrate_menu_indicator_color('footer_placements');
	}

	public function migrate_menu_indicator_color($panel_type) {
		$placements = get_theme_mod($panel_type, []);

		if (empty($placements)) {
			return;
		}

		$made_changes = false;

		foreach ($placements['sections'] as $section_index => $single_section) {
			foreach ($single_section['items'] as $item_index => $single_item) {
				if (strpos($single_item['id'], 'menu') === false) {
					continue;
				}

				if (
					isset($single_item['values']['menuIndicatorColor'])
					&&
					isset($single_item['values']['menuIndicatorColor']['active'])
					&&
					! isset($single_item['values']['menuIndicatorColor']['hover'])
				) {
					$placements['sections'][$section_index][
						'items'
					][$item_index]['values'][
						'menuIndicatorColor'
					]['hover'] = $single_item['values'][
						'menuIndicatorColor'
					]['active'];

					$made_changes = true;
				}
			}
		}

		if ($made_changes) {
			set_theme_mod($panel_type, $placements);
		}
	}
}

