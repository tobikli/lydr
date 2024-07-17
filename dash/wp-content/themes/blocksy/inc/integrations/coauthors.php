<?php

if (! function_exists('coauthors__echo')) {
	return;
}

add_filter(
	'blocksy:post_meta:author_output',
	function($output, $with_iamge = true, $meta_label = '') {
		$author_output = '';

		if ($with_iamge) {
			$author_output .= coauthors__echo(
				'blocksy_render_post_author_avatar',
				'callback',
				[
					'between' => '',
					'betweenLast' => '',
					'before' => '',
					'after' => '',
				],
				null,
				false
			);
		}

		$author_output .= coauthors__echo(
			'blocksy_render_post_author_details',
			'callback',
			[
				'between' => ', ',
				'betweenLast' => __(' and ', 'blocksy'),
				'before' => $meta_label,
				'after' => '',
			],
			null,
			false
		);

		return $author_output;
	},
	10,
	3
);

add_filter(
	'blocksy:author:count_user_posts',
	function($result, $author_id) {
		if (
			! class_exists('\CoAuthors_Plus')
			||
			! class_exists('\CoAuthors_Guest_Authors')
		) {
			return $result;
		}

		$coguest = new \CoAuthors_Guest_Authors();

		$guest_author = $coguest->get_guest_author_by('ID', $author_id);

		if (! $guest_author) {
			return $result;
		}

		$coauthor = new \CoAuthors_Plus();

		return $coauthor->get_guest_author_post_count($guest_author);
	},
	10, 2
);

add_filter(
	'blocksy:author:get_the_author_meta',
	function ($result, $field, $author_id) {
		if (
			! class_exists('\CoAuthors_Plus')
			||
			! class_exists('\CoAuthors_Guest_Authors')
		) {
			return $result;
		}

		$coguest = new \CoAuthors_Guest_Authors();

		$guest_author = $coguest->get_guest_author_by('ID', $author_id);

		if (! $guest_author || ! isset($guest_author->$field)) {
			return $result;
		}

		return $guest_author->$field;
	},
	10, 3
);

