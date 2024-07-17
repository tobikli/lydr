<?php
/**
 * Implement meta boxes
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

function blocksy_get_post_options($post_id = null, $args = []) {
	$args = wp_parse_args(
		$args,
		[
			'meta_id' => 'blocksy_post_meta_options'
		]
	);

	static $post_opts = [];

	if (! $post_id) {
		global $post;

		if ($post && is_singular()) {
			$post_id = $post->ID;
		}

		$maybe_page = blocksy_is_page();

		if ($maybe_page) {
			$post_id = $maybe_page;
		}
	}

	if (is_array($post_id)) {
		blocksy_debug_log(
			'blocksy_get_post_options() post_id is array. This is not valid.',
			[
				'post_id' => $post_id,
				'is_page' => blocksy_is_page(),
				'args' => $args
			]
		);

		return [];
	}

	$cache_key = $post_id . ':' . $args['meta_id'];

	if (isset($post_opts[$cache_key])) {
		return $post_opts[$cache_key];
	}

	$values = get_post_meta($post_id, $args['meta_id']);

	if (empty($values)) {
		$values = [[]];
	}

	if (! is_array($values[0]) || ! $values[0]) {
		return [];
	}

	$final_values = $values[0];

	// inc/options/meta/blog.php
	$allowed_keys_for_special_posts = apply_filters(
		'blocksy:posts:meta:blog-special-keys',
		[
			'disable_header',
			'disable_footer',
		]
	);

	if (
		intval(get_option('page_for_posts')) === intval($post_id)
		||
		intval(get_option('woocommerce_shop_page_id')) === intval($post_id)
	) {
		foreach ($values[0] as $key => $value) {
			if (! in_array($key, $allowed_keys_for_special_posts)) {
				unset($final_values[$key]);
			}
		}
	}

	$final_values = apply_filters(
		'blocksy:posts:meta:values',
		$final_values,
		$post_id
	);

	$post_opts[$cache_key] = $final_values;

	return $final_values;
}

function blocksy_get_taxonomy_options($term_id = null) {
	static $taxonomy_opts = [];

	if (! $term_id) {
		$term_id = get_queried_object_id();
	}

	if (isset($taxonomy_opts[$term_id])) {
		return $taxonomy_opts[$term_id];
	}

	$values = get_term_meta(
		$term_id,
		'blocksy_taxonomy_meta_options'
	);

	if (empty($values)) {
		$values = [[]];
	}

	$taxonomy_opts[$term_id] = $values[0];

	return $values[0];
}

