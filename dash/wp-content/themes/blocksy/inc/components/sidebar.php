<?php

namespace Blocksy;

class Sidebar {
	public function render() {
		if (blocksy_sidebar_position() === 'none') {
			return '';
		}

		$sticky_output = [];

		$type = blocksy_get_theme_mod('sidebar_type', 'type-1');

		if (blocksy_get_theme_mod('has_sticky_sidebar', 'no') === 'yes') {
			$sidebar_stick_behavior = blocksy_get_theme_mod(
				'sidebar_stick_behavior',
				'sidebar'
			);

			if ($sidebar_stick_behavior === 'sidebar') {
				$sticky_output['data-sticky'] = 'sidebar';
			} else {
				$sticky_output['data-sticky'] = 'widgets';
			}
		}

		$widgets_separated_output = [];

		if (
			$type === 'type-2'
			&&
			blocksy_get_theme_mod('separated_widgets', 'no') === 'yes'
		) {
			$widgets_separated_output['data-widgets'] = 'separated';
		}

		$class_output = '';

		$sidebar_classes = blocksy_visibility_classes(blocksy_get_theme_mod('sidebar_visibility', [
			'desktop' => true,
			'tablet' => false,
			'mobile' => false,
		]));

		if (! empty(trim($sidebar_classes))) {
			$class_output = $sidebar_classes;
		}

		$prefix = blocksy_manager()->screen->get_prefix();

		$deep_link_args = [
			'return' => 'array'
		];

		if (! is_singular()) {
			$deep_link_args['suffix'] = $prefix . '_has_sidebar';
		}

		$outputs = [
			'before' => $this->make_action('blocksy:sidebar:before'),

			'start' => $this->make_action('blocksy:sidebar:start'),

			'widgets' => $this->get_widgets(),

			'end' => $this->make_action('blocksy:sidebar:end'),

			'after' => $this->make_action('blocksy:sidebar:after'),
		];

		$main_content = $outputs['start'] . $outputs['widgets'] . $outputs['end'];

		if (! empty($main_content)) {
			$main_content = blocksy_html_tag(
				'div',
				array_merge(
					[
						'class' => 'ct-sidebar',
					],
					$sticky_output,
					$widgets_separated_output
				),
				$main_content
			);
		}

		$aside_content = $outputs['before'] . $main_content . $outputs['after'];

		if (empty($aside_content)) {
			return '<aside></aside>';
		}

		return blocksy_html_tag(
			'aside',
			array_merge(
				[
					'class' => $class_output,
					'data-type' => $type,
					'id' => 'sidebar',
				],
				blocksy_generic_get_deep_link($deep_link_args),
				blocksy_schema_org_definitions('sidebar', [
					'array' => true,
				])
			),
			$aside_content
		);
	}

	private function make_action($action) {
		ob_start();
		do_action($action);
		return trim(ob_get_clean());
	}

	private function get_widgets() {
		$sidebar_to_render = $this->get_sidebar_to_render();

		if (! is_active_sidebar($sidebar_to_render)) {
			return '';
		}

		ob_start();

		$has_last_n_widgets = false;

		if (blocksy_get_theme_mod('has_sticky_sidebar', 'no') === 'yes') {
			$sidebar_stick_behavior = blocksy_get_theme_mod(
				'sidebar_stick_behavior',
				'sidebar'
			);

			if ($sidebar_stick_behavior === 'last_n_widgets') {
				$sidebars_widgets = wp_get_sidebars_widgets();
				$has_last_n_widgets = true;
			}
		}

		if ($has_last_n_widgets) {
			add_action('dynamic_sidebar', [
				$this,
				'render_dynamic_sidebar_hook'
			]);
		}

		dynamic_sidebar($sidebar_to_render);

		if ($has_last_n_widgets) {
			echo '</div>';

			remove_action('dynamic_sidebar', [
				$this,
				'render_dynamic_sidebar_hook'
			]);
		}

		return trim(ob_get_clean());
	}

	public function render_dynamic_sidebar_hook($widget) {
		$sidebars_widgets = wp_get_sidebars_widgets();
		$widget_id = $widget['id'];

		$reversed_widgets = array_reverse(
			$sidebars_widgets[$this->get_sidebar_to_render()]
		);

		$widget_index = array_search($widget_id, $reversed_widgets);

		$sticky_widget_number = min(
			intval(blocksy_get_theme_mod(
				'sticky_widget_number',
				1
			)),
			count($reversed_widgets)
		);

		if ($widget_index + 1 === $sticky_widget_number) {
			echo '<div class="ct-sticky-widgets">';
		}
	}

	private function get_sidebar_to_render() {
		if (class_exists('BlocksySidebarsManager')) {
			$manager = new \BlocksySidebarsManager();

			$maybe_sidebar = $manager->maybe_get_sidebar_that_matches();

			if ($maybe_sidebar) {
				return $maybe_sidebar;
			}
		}

		$prefix = blocksy_manager()->screen->get_prefix();

		if ($prefix === 'product' || $prefix === 'woo_categories') {
			return 'sidebar-woocommerce';
		}

		if (
			is_search()
			&&
			isset($_GET['post_type'])
			&&
			$_GET['post_type'] === 'product'
		) {
			return 'sidebar-woocommerce';
		}

		return 'sidebar-1';
	}
}

