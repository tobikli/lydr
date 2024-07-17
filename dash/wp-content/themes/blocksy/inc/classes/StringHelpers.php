<?php

namespace Blocksy;

class StringHelpers {
	private $string = '';

	// TODO: maybe expose context in the filter so we could apply a different
	// logic for different contexts.
	public function __construct($string, $context = 'excerpt') {
		$this->string = $string;
	}

	public function count_words() {
		$maybe_custom_counter = apply_filters(
			'blocksy:general:string-helpers:count-words',
			null,
			$this->string
		);

		if ($maybe_custom_counter !== null) {
			return $maybe_custom_counter;
		}

		$word_count = 0;

		if (
			str_starts_with(wp_get_word_count_type(), 'characters')
			// &&
			// preg_match('/^utf\-?8$/i', get_option('blog_charset'))
			&&
			function_exists('mb_strlen')
		) {
			$word_count = mb_strlen(strip_tags($this->string));
		} else {
			// https://www.php.net/manual/en/function.str-word-count.php#107363
			$word_count = count(
				preg_split('~[^\p{L}\p{N}\']+~u', strip_tags($this->string))
			);
		}

		return $word_count;
	}

	public function trim_words($length, $more = '…') {
		$maybe_custom_trim = apply_filters(
			'blocksy:general:string-helpers:trim-words',
			null,
			$this->string,
			$length,
			$more
		);

		if ($maybe_custom_trim !== null) {
			return $maybe_custom_trim;
		}

		if (
			str_starts_with(wp_get_word_count_type(), 'characters')
			// &&
			// preg_match('/^utf\-?8$/i', get_option('blog_charset'))
			&&
			function_exists('mb_strimwidth')
		) {
			$text = mb_strimwidth($this->string, 0, $length, $more);
		} else {
			$text = wp_trim_words($this->string, $length, $more);
		}

		return $text;
	}
}

