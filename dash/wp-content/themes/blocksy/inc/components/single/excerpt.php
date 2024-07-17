<?php

defined('ABSPATH') || die("Don't run this file directly!");

if (! function_exists('blocksy_excerpt_length')) {
	function blocksy_excerpt_length($length) {
		return 300;
	}
}

if (! function_exists('blocksy_excerpt_more')) {
	function blocksy_excerpt_more($more) {
		return '…';
	}
}

if (! function_exists('blocksy_trim_excerpt')) {
	function blocksy_trim_excerpt($excerpt, $length) {
		$text = $excerpt;

		if ($length !== 'original') {
			$string_helpers = new \Blocksy\StringHelpers($excerpt);
			$text = $string_helpers->trim_words($length);
		}

		foreach (wp_extract_urls($text) as $url) {
			$text = str_replace($url, '', $text);
		}

		$text = apply_filters('blocksy:excerpt:output', $text);

		add_filter(
			'excerpt_more',
			'blocksy_excerpt_more',
			PHP_INT_MAX
		);
		echo apply_filters('the_excerpt', $text);
		remove_filter(
			'excerpt_more',
			'blocksy_excerpt_more',
			PHP_INT_MAX
		);
	}
}

