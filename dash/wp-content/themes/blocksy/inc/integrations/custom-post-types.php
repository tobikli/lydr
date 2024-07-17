<?php

namespace Blocksy;

class CustomPostTypes {
	private $supported_post_types = null;

	public function wipe_caches() {
		$this->supported_post_types = null;
	}

	public function get_supported_post_types() {
		if ($this->supported_post_types === null) {
			$potential_post_types = array_keys(get_post_types([
				'public'   => true,
				'_builtin' => false,
			]));

			$potential_post_types = array_values(array_diff($potential_post_types, [

				// theme
				'ct_content_block',

				// elements kit
				'elementskit_content',
				'elementskit_template',
				'elementskit_widget',

				// zion builder
				'zion_template',

				// thrive
				'tcb_lightbox',
				'tcb_symbol',
				'tvo_display_post',
				'tvo_capture',
				'tvo_display',

				// learn dash
				'ld-exam',
				'groups',
				'sfwd-assignment',
				'sfwd-essays',
				'sfwd-transactions',
				'sfwd-certificates',

				// Lifter LMS
				'llms_quiz',
				'llms_membership',
				'llms_certificate',
				'llms_my_certificate',

				// tribe events
				'tribe_events',
				'tribe_event_series',
				'tribe_venue',
				'tribe_organizer',

				// tutor lms
				'tutor_quiz',
				'tutor_assignments',
				'tutor_zoom_meeting',

				// jet engine
				'jet-popup',
				'jet-smart-filters',
				'jet-theme-core',
				'jet-woo-builder',
				'jet-engine',
				'jet-engine-booking',
				'jet_options_preset',
				'jet-menu',

				// piotnet forms
				'piotnetforms',
				'piotnetforms-aban',
				'piotnetforms-data',
				'piotnetforms-book',
				'piotnetforms-fonts',
				'pafe-formabandonment',
				'pafe-form-database',
				'pafe-form-booking',
				'pafe-fonts',

				// BeRocket
				'br_labels',
				'br_product_filter',
				'br_filters_group',

				// give
				'give_forms',

				// complianz
				'cmplz-processing',
				'cmplz-dataleak',

				// elementor
				'elementor_library',

				// brizy
				'brizy_template',
				'editor-story',

				// mailpoet
				'mailpoet_page',

				// modern events calendar
				'mec_esb',
				'mec-events',

				// woolentor
				'woolentor-template',

				// shopengine
				'shopengine-template',

				// blockslider
				'blockslider',

				// funelkit
				'wffn_landing',
				'wffn_ty',
				'wffn_optin',
				'wffn_oty',
				'wfacp_checkout',
				'wfocu_funnel',
				'wfocu_offer',

				// co-authors
				'guest-author',

				// other
				'iamport_payment',
				'wpcw_achievements',
				'zoom-meetings',
				'adsforwp',
				'adsforwp-groups',
				'popup',
				'product',
				'forum',
				'topic',
				'reply',
				'ha_nav_content',
				'course',
				'lesson',
				'atbdp_orders',
				'at_biz_dir',
				'gspbstylebook',
				'testimonial',
				'frm_display',
				'e-landing-page',
				'pgc_simply_gallery',
				'pdfviewer',
				'da_image',
				'ha_library',
			]));

			$this->supported_post_types = array_unique(apply_filters(
				'blocksy:custom_post_types:supported_list',
				$potential_post_types
			));
		}

		return $this->supported_post_types;
	}

	public function is_supported_post_type($args = []) {
		$args = wp_parse_args($args, [
			'allow_built_in' => false
		]);

		global $post;
		global $wp_taxonomies;
		global $wp_query;

		$post_type = get_post_type($post);

		$tax_query = $wp_query->tax_query;

		if (
			$tax_query
			&&
			! is_home()
			&&
			! is_post_type_archive()
		) {
			$tax = null;

			foreach ($tax_query->queries as $taxonomy) {
				if (isset($taxonomy['taxonomy'])) {
					$taxonomy_obj = get_taxonomy($taxonomy['taxonomy']);

					if ($taxonomy_obj->public) {
						$tax = $taxonomy['taxonomy'];
						break;
					}
				}
			}

			if ($tax && ! is_array($tax) && isset($wp_taxonomies[$tax])) {
				$all_tax_post_types = $wp_taxonomies[$tax]->object_type;

				if (
					! empty($all_tax_post_types)
					&&
					isset($all_tax_post_types[0])
				) {
					$post_type = $all_tax_post_types[0];
				}
			}
		}

		if (! $post_type) {
			$post_type = get_query_var('post_type');
		}

		$builtin_post_types = ['post', 'page'];

		if (function_exists('is_woocommerce')) {
			$builtin_post_types[] = 'product';
		}

		if (
			in_array($post_type, $builtin_post_types)
			&&
			$args['allow_built_in']
		) {
			return $post_type;
		}

		$post_type = apply_filters(
			'blocksy:custom_post_types:current_post_type:compute',
			$post_type
		);

		if (in_array($post_type, $this->get_supported_post_types())) {
			return $post_type;
		}

		return null;
	}

	public function get_all($args = []) {
		$args = wp_parse_args($args, [
			'exclude_built_in' => false,
			'exclude_woo' => false
		]);

		$to_exclude = [
			'attachment',
			'ct_content_block'

			// documentation for now will not be excluded.
			// But we might want to exclude it again, in case there will be
			// some issues with it.
			// 'documentation',
		];

		if ($args['exclude_built_in']) {
			$to_exclude[] = 'post';
			$to_exclude[] = 'page';
		}

		if ($args['exclude_woo']) {
			$to_exclude[] = 'product';
		}

		$custom_post_types = array_values(array_diff(
			get_post_types(['public' => true]),
			$to_exclude
		));

		return apply_filters(
			'blocksy:custom_post_types:all_post_types',
			$custom_post_types
		);
	}
}

