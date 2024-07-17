<?php

namespace Blocksy;

class BlocksFallback {
	public function __construct() {
		add_filter(
			'render_block',
			[$this, 'render_block'],
			10,
			3
		);
	}

	public function render_block($content, $block, $render) {
		if (! isset($block['blockName'])) {
			return $content;
		}

		if (strpos($block['blockName'], 'blocksy/') !== 0) {
			return $content;
		}

		$blockRegistry = \WP_Block_Type_Registry::get_instance();

		if ($blockRegistry->is_registered($block['blockName'])) {
			return $content;
		}

		if (! current_user_can('manage_options')) {
			return '';
		}

		$block_names_map = [
			'blocksy/about-me' => __('About Me', 'blocksy'),
			'blocksy/breadcrumbs' => __('Breadcrumbs', 'blocksy'),
			'blocksy/contact-info' => __('Contact Info', 'blocksy'),
			'blocksy/query' => __('Advanced Posts', 'blocksy'),
			'blocksy/search' => __('Advanced Search', 'blocksy'),
			'blocksy/share-box' => __('Share Box', 'blocksy'),
			'blocksy/socials' => __('Socials', 'blocksy'),
			'blocksy/dynamic-data' => __('Dynamic Data', 'blocksy'),
		];

		$block_name = $block['blockName'];

		if ($block_name === 'blocksy/widgets-wrapper') {
			if (
				isset($block['attrs'])
				&&
				isset($block['attrs']['block'])
			) {
				$block_name = $block['attrs']['block'];
			} else {
				foreach ($block['innerBlocks'] as $inner_block) {
					if (
						isset($inner_block['blockName'])
						&&
						strpos($inner_block['blockName'], 'blocksy/') === 0
					) {
						$block_name = $inner_block['blockName'];
						break;
					}
				}
			}
		}

		if (! isset($block_names_map[$block_name])) {
			return $content;
		}

		return blocksy_html_tag(
			'div',
			[],
			sprintf(
				__('The %s block is moved to the Blocksy Companion plugin. Please install this plugin to get access to the block.', 'blocksy'),
				$block_names_map[$block_name]
			)
		);
	}
}
