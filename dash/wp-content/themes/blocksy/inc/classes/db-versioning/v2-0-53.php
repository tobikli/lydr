<?php

namespace Blocksy\DbVersioning;

class V2053 {
	public function migrate() {
		$maybe_taxonomy = get_taxonomy('product_brands');

		if (! $maybe_taxonomy) {
			return;
		}

		$terms = get_terms([
			'taxonomy' => 'product_brands'
		]);

		foreach ($terms as $term) {
			$options = blocksy_get_taxonomy_options(
				$term->term_id,
				'product_brands'
			);

			if (! isset($options['image']) || empty($options['image'])) {
				continue;
			}

			$options['icon_image'] = $options['image'];
			$options['image'] = '';

			update_term_meta(
				$term->term_id,
				'blocksy_taxonomy_meta_options',
				$options
			);
		}
	}
}

