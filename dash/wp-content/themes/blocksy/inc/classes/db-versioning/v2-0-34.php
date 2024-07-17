<?php

namespace Blocksy\DbVersioning;

class V2034 {
	public function migrate() {
		if (! function_exists('wc_get_attribute_taxonomies')) {
			return;
		}

		foreach (array_values(wc_get_attribute_taxonomies()) as $tax) {
			$taxonomy = (array) $tax;

			$meta = blocksy_get_taxonomy_options($taxonomy['attribute_id']);

			if (
				$meta
				&&
				isset($meta['swatch_type'])
				&&
				$taxonomy['attribute_type'] !== $meta['swatch_type']
			) {
				wc_update_attribute(
					$taxonomy['attribute_id'],
					[
						'name' => $taxonomy['attribute_label'],
						'slug' => $taxonomy['attribute_name'],
						'type' => $meta['swatch_type'],
						'order_by' => $taxonomy['attribute_orderby'],
						'has_archives' => $taxonomy['attribute_public']
					]
				);
			}
		}
	}
}

