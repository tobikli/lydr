<?php

class Blocksy_Header_Builder_Render extends Blocksy_Builder_Render {
	public function get_section_type() {
		return 'header';
	}

	public function get_header_height($sticky_height = false) {
		$top_row = $this->get_item_data_for('top-row');
		$middle_row = $this->get_item_data_for('middle-row');
		$bottom_row = $this->get_item_data_for('bottom-row');

		$top_row_height = blocksy_akg('headerRowHeight', $top_row, [
			'mobile' => 50,
			'tablet' => 50,
			'desktop' => 50,
		]);

		$middle_row_height = blocksy_akg('headerRowHeight', $middle_row, [
			'mobile' => 70,
			'tablet' => 70,
			'desktop' => 120,
		]);

		$bottom_row_height = blocksy_akg('headerRowHeight', $bottom_row, [
			'mobile' => 80,
			'tablet' => 80,
			'desktop' => 80,
		]);

		if ($sticky_height) {
			if (
				$sticky_height['behaviour'] === 'middle'
				||
				$sticky_height['behaviour'] === 'middle_bottom'
				||
				$sticky_height['behaviour'] === 'bottom'
			) {
				$top_row_height = [
					'mobile' => 0,
					'tablet' => 0,
					'desktop' => 0,
				];
			}

			if (
				$sticky_height['behaviour'] === 'top'
				||
				$sticky_height['behaviour'] === 'bottom'
			) {
				$middle_row_height = [
					'mobile' => 0,
					'tablet' => 0,
					'desktop' => 0,
				];
			} else {
				if (
					blocksy_akg('has_sticky_shrink', $middle_row, 'no') === 'yes'
					&&
					false
				) {
					$middle_row_shrink = blocksy_expand_responsive_value(blocksy_akg(
						'stickyHeaderRowShrink',
						$middle_row,
						70
					));

					$middle_row_height = [
						'mobile' => intval($middle_row_shrink['mobile']) * intval($middle_row_height['mobile']) / 100,
						'tablet' => intval($middle_row_shrink['tablet']) * intval($middle_row_height['tablet']) / 100,
						'desktop' => intval($middle_row_shrink['desktop']) * intval($middle_row_height['desktop']) / 100,
					];
				}
			}

			if (
				$sticky_height['behaviour'] === 'middle'
				||
				$sticky_height['behaviour'] === 'top_middle'
				||
				$sticky_height['behaviour'] === 'top'
			) {
				$bottom_row_height = [
					'mobile' => 0,
					'tablet' => 0,
					'desktop' => 0,
				];
			}
		}

		$is_row_empty_mobile = $this->is_row_empty('top-row', 'mobile');
		$is_row_empty_desktop = $this->is_row_empty('top-row', 'desktop');

		if ($is_row_empty_mobile) {
			$top_row_height['mobile'] = 0;
			$top_row_height['tablet'] = 0;
		}

		if ($is_row_empty_desktop) {
			$top_row_height['desktop'] = 0;
		}

		$is_row_empty_mobile = $this->is_row_empty('middle-row', 'mobile');
		$is_row_empty_desktop = $this->is_row_empty('middle-row', 'desktop');

		if ($is_row_empty_mobile) {
			$middle_row_height['mobile'] = 0;
			$middle_row_height['tablet'] = 0;
		}

		if ($is_row_empty_desktop) {
			$middle_row_height['desktop'] = 0;
		}

		$is_row_empty_mobile = $this->is_row_empty('bottom-row', 'mobile');
		$is_row_empty_desktop = $this->is_row_empty('bottom-row', 'desktop');

		if ($is_row_empty_mobile) {
			$bottom_row_height['mobile'] = 0;
			$bottom_row_height['tablet'] = 0;
		}

		if ($is_row_empty_desktop) {
			$bottom_row_height['desktop'] = 0;
		}

		return [
			'mobile' => intval($top_row_height['mobile']) + intval(
				$middle_row_height['mobile']
			) + intval($bottom_row_height['mobile']),
			'tablet' => intval($top_row_height['tablet']) + intval(
				$middle_row_height['tablet']
			) + intval($bottom_row_height['tablet']),
			'desktop' => intval($top_row_height['desktop']) + intval(
				$middle_row_height['desktop']
			) + intval($bottom_row_height['desktop']),
		];
	}

	public function contains_item($item, $is_primary = false) {
		if (is_customize_preview() && ! wp_doing_ajax()) {
			// return true;
		}

		if ($is_primary) {
			return (
				! $this->is_row_empty($item, 'desktop')
				||
				! $this->is_row_empty($item, 'mobile')
			);
		}

		$section = $this->get_current_section();

		foreach ($section['desktop'] as $desktop_row) {
			foreach ($desktop_row['placements'] as $single_placement) {
				if (in_array($item, $single_placement['items'])) {
					return true;
				}
			}
		}

		foreach ($section['mobile'] as $mobile_row) {
			foreach ($mobile_row['placements'] as $single_placement) {
				if (in_array($item, $single_placement['items'])) {
					return true;
				}
			}
		}

		return false;
	}

	public function render() {
		$content = $this->render_for_device('desktop');
		$content .= $this->render_for_device('mobile');

		$current_section = $this->get_current_section();

		if (! isset($current_section['settings'])) {
			$current_section['settings'] = [];
		}

		$atts = $current_section['settings'];

		return blocksy_html_tag(
			'header',
			apply_filters(
				'blocksy:header:wrapper-attr',
				array_merge(
					[
						'id' => 'header',
						'class' => 'ct-header',
						'data-id' => $this->get_short_section_id(),
					],
					blocksy_schema_org_definitions('header', ['array' => true])
				),
				[
					'atts' => $atts,
					'current_section' => $current_section
				]
			),
			$content
		);
	}

	public function render_for_device($device) {
		$rows = [];

		$current_section = $this->get_current_section();
		$current_layout = $current_section[$device];

		foreach ($current_layout as $row) {
			if ($row['id'] === 'offcanvas') {
				continue;
			}

			$rendered_row = $this->render_row($row, [
				'device' => $device
			]);

			if (! empty(trim($rendered_row))) {
				$rows[$row['id']] = $rendered_row;
			}
		}

		$custom_content = apply_filters(
			'blocksy:header:rows-render',
			null,
			$rows,
			$device
		);

		if ($custom_content) {
			$content = $custom_content;
		} else {
			$content = implode('', array_values($rows));
		}

		return blocksy_html_tag(
			'div',
			apply_filters(
				'blocksy:header:device-wrapper-attr',
				['data-device' => $device],
				$device
			),
			$content
		);
	}

	public function render_row($row, $args = []) {
		$args = wp_parse_args($args, [
			'device' => 'desktop'
		]);

		$row_config = $this->get_item_config_for($row['id']);

		$simplified_id = str_replace(
			'-row',
			'',
			$row['id']
		);

        $atts = $this->get_item_data_for($row['id']);

		$container_class = 'ct-container';

		$headerRowWidth = blocksy_expand_responsive_value(blocksy_default_akg(
			'headerRowWidth', $atts, 'fixed'
		))[$args['device']];

		if ($headerRowWidth === 'fluid') {
			$container_class = 'ct-container-fluid';
		}

		$rendered_placements = [
			'start' => [
				'items' => [],
				'rendered' => ''
			],

			'start-middle' => [
				'items' => [],
				'rendered' => ''
			],

			'middle' => [
				'items' => [],
				'rendered' => ''
			],

			'end-middle' => [
				'items' => [],
				'rendered' => ''
			],

			'end' => [
				'items' => [],
				'rendered' => ''
			]
		];

		foreach ($row['placements'] as $placement) {
			$rendered_placements[$placement['id']] = $placement;

			$rendered_placements[
				$placement['id']
			]['rendered'] = $this->render_items_collection(
				$placement['items'],
				[
					'device' => $args['device']
				]
			);
		}

		$start_placement = $this->render_start_placement([
			'rendered_placements' => $rendered_placements,
			'device' => $args['device']
		]);
		$middle_placement = $this->render_middle_placement([
			'rendered_placements' => $rendered_placements,
			'device' => $args['device']
		]);
		$end_placement = $this->render_end_placement([
			'rendered_placements' => $rendered_placements,
			'device' => $args['device']
		]);

		$count = 0;

		if (! empty(trim($start_placement))) {
			$count++;
		}

		if (! empty(trim($middle_placement))) {
			$count++;
		}

		if (! empty(trim($end_placement))) {
			$count++;
		}

		$attr = [
			'data-row' => $simplified_id,
			'data-column-set' => $count,
		];

		if ($headerRowWidth === 'boxed') {
			$attr['data-row'] .= ':boxed';
		}

		/*
		if (
			$count === 2
			&&
			$this->should_column_be_expanded($row, 'start')
		) {
			$attr['data-column-expand'] = 'left';
		}

		if (
			$count === 2
			&&
			$this->should_column_be_expanded($row, 'end')
		) {
			$attr['data-column-expand'] = 'right';
		}
		 */

		if (
			$count === 3
			&&
			$this->should_column_be_expanded($rendered_placements, 'middle')
		) {
			$attr['data-middle'] = 'search-input';
		}

		$visibility_classes = blocksy_visibility_classes(
			blocksy_default_akg(
				'headerRowVisibility',
				$atts,
				[
					'tablet' => true,
					'mobile' => true,
				]
			)
		);

		if (! empty($visibility_classes)) {
			$attr['class'] = $visibility_classes;
		}

		$row_container_attr = apply_filters(
			'blocksy:header:row-wrapper-attr',
			array_merge($attr, (
				is_customize_preview() ? [
					'data-item-label' => $row_config['config']['name'],
					'data-shortcut' => 'border',
					'data-shortcut-location' => $this->get_customizer_location_for(
						$row['id']
					),
				] : []
			)),
			$row,
			$args['device']
		);

		$row_content = $start_placement . $middle_placement . $end_placement;

		if (
			empty($row_content)
			&&
			blocksy_akg('render_empty_row', $atts, 'no') === 'no'
		) {
			return '';
		}

		$result = '<div ' . blocksy_attr_to_html($row_container_attr) . '>';
		$result .= '<div ' . blocksy_attr_to_html(array_merge([
			'class' => $container_class
		])) . '>';
		// $result .= '<div class="' . $container_class . '">';

		$result .= $row_content;

		$result .= '</div>';
		$result .= '</div>';

		return $result;
	}

	public function render_single_item($item_id, $args = []) {
		$args = wp_parse_args($args, [
			'device' => 'desktop',
			'render_args' => []
		]);

		$item = null;
		$row_id = null;

		foreach ($this->get_current_section()[$args['device']] as $row) {
			foreach ($row['placements'] as $single_placement) {
				if (in_array($item_id, $single_placement['items'])) {
					$row_id = $row['id'];
				}
			}
		}

		$registered_items = blocksy_manager()->builder->get_registered_items_by('header');

		foreach ($registered_items as $single_item) {
			if ($single_item['id'] === $this->get_original_id($item_id)) {
				$item = $single_item;
				break;
			}
		}

		if (! $item) {
			return  '';
		}

		$render_args = apply_filters('blocksy:header:item-template-args', [
			'panel_type' => 'header',
			'atts' => $this->get_item_data_for($item_id),
			'section_id' => $this->get_current_section_id(),
			'row_id' => $row_id,
			'attr' => array_merge([
				'data-id' => $this->shorten_id($item_id),
			], (
				is_customize_preview() ? [
					'data-item-label' => $item['config']['name'],
					'data-shortcut' => $item['config']['shortcut_style'],
					'data-shortcut-location' => $this->get_customizer_location_for($item_id)
				] : []
			)),
			'device' => $args['device'],
			'render_args' => $args['render_args'],
			'item_id' => $item_id
		]);

		return blocksy_render_view(
			apply_filters(
				'blocksy:header:item-view-path:' . $item_id,
				$item['path'] . '/view.php'
			),
			$render_args
		);
	}

	private function render_start_placement($args = []) {
		$args = wp_parse_args($args, [
			'rendered_placements' => null,
			'device' => 'desktop'
		]);

		if (! $args['rendered_placements']) {
			return '';
		}

		// We will skip the start placement only in a few specific situations.
		if (empty($args['rendered_placements']['start']['rendered'])) {
			// Only middle is having some elements
			if (
				empty($args['rendered_placements']['start-middle']['rendered'])
				&&
				empty($args['rendered_placements']['end-middle']['rendered'])
				&&
				empty($args['rendered_placements']['end']['rendered'])
			) {
				return '';
			}

			// Middle is empty. End may or may not be empty.
			if (empty($args['rendered_placements']['middle']['rendered'])) {
				return '';
			}
		}

		$primary_output = '';
		$secondary_output = '';

		if (! empty($args['rendered_placements']['start']['rendered'])) {
			$primary_output = blocksy_html_tag(
				'div',
				[
					'data-items' => 'primary'
				],
				$args['rendered_placements']['start']['rendered']
			);
		}

		if (
			! empty($args['rendered_placements']['start-middle']['rendered'])
			&&
			! empty($args['rendered_placements']['middle']['rendered'])
		) {
			$secondary_output = blocksy_html_tag(
				'div',
				[
					'data-items' => 'secondary'
				],
				$args['rendered_placements']['start-middle']['rendered']
			);
		}

		$count = 0;

		if (! empty(trim($primary_output))) {
			$count++;
		}

		if (! empty(trim($secondary_output))) {
			$count++;
		}

		$column_content = $primary_output . $secondary_output;

		return blocksy_html_tag(
			'div',
			array_merge([
				'data-column' => 'start'
			], (
				$count > 0 ? [
					'data-placements' => $count
				] : []
			)),
			$primary_output . $secondary_output
		);
	}

	private function render_middle_placement($args = []) {
		$args = wp_parse_args($args, [
			'rendered_placements' => null,
			'device' => 'desktop'
		]);

		if (! $args['rendered_placements']) {
			return '';
		}

		if (empty($args['rendered_placements']['middle']['rendered'])) {
			return '';
		}

		return blocksy_html_tag(
			'div',
			['data-column' => 'middle'],
			blocksy_html_tag(
				'div',
				['data-items' => ''],
				$args['rendered_placements']['middle']['rendered']
			)
		);
	}

	private function render_end_placement($args = []) {
		$args = wp_parse_args($args, [
			'rendered_placements' => null,
			'device' => 'desktop'
		]);

		if (! $args['rendered_placements']) {
			return '';
		}

		// We will skip the end placement only in a few specific situations.
		if (empty($args['rendered_placements']['end']['rendered'])) {
			// Only middle is having some elements
			if (
				empty($args['rendered_placements']['end-middle']['rendered'])
				&&
				empty($args['rendered_placements']['start-middle']['rendered'])
				&&
				empty($args['rendered_placements']['start']['rendered'])
			) {
				return '';
			}

			// Middle is empty. End may or may not be empty.
			if (empty($args['rendered_placements']['middle']['rendered'])) {
				return '';
			}
		}

		$primary_output = '';
		$secondary_output = '';

		if (! empty($args['rendered_placements']['end']['rendered'])) {
			$primary_output = blocksy_html_tag(
				'div',
				[
					'data-items' => 'primary'
				],
				$args['rendered_placements']['end']['rendered']
			);
		}

		if (
			! empty($args['rendered_placements']['end-middle']['rendered'])
			&&
			! empty($args['rendered_placements']['middle']['rendered'])
		) {
			$secondary_output = blocksy_html_tag(
				'div',
				[
					'data-items' => 'secondary'
				],
				$args['rendered_placements']['end-middle']['rendered']
			);
		}

		$count = 0;

		if (! empty(trim($primary_output))) {
			$count++;
		}

		if (! empty(trim($secondary_output))) {
			$count++;
		}

		return blocksy_html_tag(
			'div',
			array_merge([
				'data-column' => 'end'
			], (
				$count > 0 ? [
					'data-placements' => $count
				] : []
			)),
			$secondary_output . $primary_output
		);
	}

	// It will only check for items being placed in the row. It won't check if
	// those items actually exist or return anything.
	public function is_row_empty($row, $device = '') {
		if (! is_array($row)) {
			$current_section = $this->get_current_section();
			$current_layout = $current_section[$device];

			foreach ($current_layout as $single_row) {
				if ($single_row['id'] === $row) {
					$row = $single_row;
				}
			}
		}

		if (! is_array($row)) {
			return true;
		}

		$columns_to_check = ['start', 'middle', 'end'];

		foreach ($row['placements'] as $single_column) {
			if (
				in_array($single_column['id'], ['start', 'middle', 'end'])
				&&
				isset($single_column['items'])
				&&
				count($single_column['items']) > 0
			) {
				return false;
			}
		}

		$atts = $this->get_item_data_for($row['id']);

		if (blocksy_akg('render_empty_row', $atts, 'no') === 'yes') {
			return false;
		}

		return true;
	}

	public function render_items_collection($items, $args = []) {
		$args = wp_parse_args($args, [
			'device' => 'desktop'
		]);

		$result = '';

		foreach ($items as $item) {
			$result .= $this->render_single_item($item, [
				'device' => $args['device']
			]);
		}

		return $result;
	}

	public function get_primary_item($id) {
		return [];
	}

	public function should_column_be_expanded($rendered_placements, $placement) {
		$menus = [
			'menu',
			'menu-secondary',
			'menu-tertiary',
		];

		$items = $rendered_placements[$placement]['items'];

		if (in_array('search-input', $items)) {
			return true;
		}

		/*
		foreach ($menus as $menu_id) {
			if (! in_array($menu_id, $items)) {
				continue;
			}

			$atts = $this->get_item_data_for($menu_id);

			if (blocksy_default_akg('stretch_menu', $atts, 'no') === 'yes') {
				return true;
			}
		}
		 */

		return false;
	}
}

