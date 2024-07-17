<?php

namespace Blocksy\DbVersioning;

class V2036 {
	public function migrate() {
		if (
			! function_exists('wc_get_attribute_taxonomies')
			||
			! class_exists('\Blocksy\Plugin')
			||
			! in_array(
				'woocommerce-extra',
				get_option('blocksy_active_extensions', [])
			)
		) {
			return;
		}

		if (! class_exists('\Blocksy\Extensions\WoocommerceExtra\Storage')) {
			return;
		}

		$storage = new \Blocksy\Extensions\WoocommerceExtra\Storage();
		$settings = $storage->get_settings();

		if (
			! isset($settings['features']['variation-swatches'])
			||
			! $settings['features']['variation-swatches']
		) {
			return;
		}

		foreach (array_values(wc_get_attribute_taxonomies()) as $tax) {
			$taxonomy = (array) $tax;

			$new_type = 'button';

			$meta = blocksy_get_taxonomy_options($taxonomy['attribute_id']);

			if (
				$meta
				&&
				isset($meta['swatch_type'])
			) {
				$new_type = $meta['swatch_type'];
			}

			if ($taxonomy['attribute_type'] !== $new_type) {
				wc_update_attribute(
					$taxonomy['attribute_id'],
					[
						'name' => $taxonomy['attribute_label'],
						'slug' => $taxonomy['attribute_name'],
						'type' => $new_type,
						'order_by' => $taxonomy['attribute_orderby'],
						'has_archives' => $taxonomy['attribute_public']
					]
				);
			}
		}
	}
}


