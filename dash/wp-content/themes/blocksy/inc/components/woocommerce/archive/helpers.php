<?php

if (! function_exists('blocksy_get_woocommerce_ratio')) {
	function blocksy_get_woocommerce_ratio($args = []) {
		$args = wp_parse_args($args, [
			'key' => 'archive_thumbnail',
			'cropping' => 'predefined',

			'default_width' => 3,
			'default_height' => 4
		]);

		if ($args['cropping'] === 'uncropped') {
			return 'original';
		}

		if ($args['cropping'] === '1:1') {
			return '1/1';
		}

		if ($args['cropping'] === 'custom' || $args['cropping'] === 'predefined') {
			$width = get_option(
				'woocommerce_' . $args['key'] . '_cropping_custom_width',
				3
			);

			$height = get_option(
				'woocommerce_' . $args['key'] . '_cropping_custom_height',
				4
			);

			return $width . '/' . $height;
		}

		return '1/1';
	}
}

function blocksy_has_product_card_specific_layer($layer_id = '') {
	if (empty($layer_id)) {
		return false;
	}

	$default_product_layout = blocksy_get_woo_archive_layout_defaults();

	$layout = blocksy_get_theme_mod(
		'woo_card_layout',
		$default_product_layout
	);

	$layer_to_find = array_values(array_filter($layout, function($k) use ($layer_id) {
		return $k['id'] === $layer_id;
	}));

	if (empty($layer_to_find)) {
		return false;
	}

	if (
		isset($layer_to_find[0]['enabled'])
		&&
		$layer_to_find[0]['enabled']
	) {
		return true;
	}

	return false;
}
