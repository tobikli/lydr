<?php

namespace Blocksy\DbVersioning;

class V2038 {
	public function migrate() {
		if (
			! function_exists('wc_get_attribute_taxonomies')
			||
			! function_exists('blc_get_terms')
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
			$all_terms = blc_get_terms(
				[
					'taxonomy' => 'pa_' . $tax->attribute_name,
					'update_term_meta_cache' => false,
					'meta_query' => [
						[
							'key' => 'short_name',
							'compare' => 'EXISTS'
						]
					]
				]
			);

			foreach ($all_terms as $term) {
				$short_name = get_term_meta(
					$term->term_id,
					'short_name',
					true
				);

				if (empty($short_name)) {
					continue;
				}

				$meta = blocksy_get_taxonomy_options($term->term_id);

				$meta['short_name'] = $short_name;

				update_term_meta(
					$term->term_id,
					'blocksy_taxonomy_meta_options',
					$meta
				);
			}
		}
	}
}

