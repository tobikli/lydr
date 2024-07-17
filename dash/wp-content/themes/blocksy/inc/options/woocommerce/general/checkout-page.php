<?php
/**
 * Checkout page options
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

$pages = get_pages(
	[
		'post_type' => 'page',
		'post_status' => 'publish,private,draft',
		'number' => 0,
		'child_of' => 0,
		'parent' => -1,
		'exclude' => [
			wc_get_page_id('cart'),
			wc_get_page_id('checkout'),
			wc_get_page_id('myaccount'),
		],
		'sort_order' => 'asc',
		'sort_column' => 'post_title'
	]
);

$page_choices_result = [];

$page_choices = array(
	'' => __('No page set', 'blocksy')
) + array_combine(
	array_map(
		'strval',
		wp_list_pluck($pages, 'ID')
	),
	wp_list_pluck($pages, 'post_title')
);

foreach ($page_choices as $page_id => $page_label) {
	$page_choices_result[strval($page_id)] = $page_label;
}

$options = [

	blocksy_rand_md5() => [
		'label' => __('Checkout Page', 'blocksy'),
		'type' => 'ct-panel',
		'setting' => ['transport' => 'postMessage'],
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					[
						'blocksy_has_checkout_coupon' => [
							'label' => __( 'Coupon Form', 'blocksy' ),
							'type' => 'ct-switch',
							'value' => false,
							'divider' => 'bottom:full',
							'behavior' => 'bool',
							'setting' => [
							],
						]
					],

					apply_filters(
						'blocksy_customizer_options:woocommerce:general:coupon:after',
						[]
					),

					'woocommerce_checkout_highlight_required_fields' => [
						'label' => __('Highlight Required Fields', 'blocksy'),
						'type' => 'ct-switch',
						'value' => 'yes',
						'behavior' => 'bool',
						'divider' => 'bottom',
						'setting' => [
							'type' => 'option'
						],
					],

					'woocommerce_checkout_company_field' => [
						'label' => __( 'Company Name Field', 'blocksy' ),
						'type' => 'ct-select',
						'value' => 'optional',
						'view' => 'text',
						'design' => 'block',
						'choices' => blocksy_ordered_keys(
							[
								'hidden' => __( 'Hidden', 'blocksy' ),
								'optional' => __( 'Optional', 'blocksy' ),
								'required' => __( 'Required', 'blocksy' ),
							]
						),
						'setting' => [
							'type' => 'option'
						],
					],

					'woocommerce_checkout_address_2_field' => [
						'label' => __( 'Address Line 2 Field', 'blocksy' ),
						'type' => 'ct-select',
						'value' => 'optional',
						'view' => 'text',
						'design' => 'block',
						'choices' => blocksy_ordered_keys(
							[
								'hidden' => __( 'Hidden', 'blocksy' ),
								'optional' => __( 'Optional', 'blocksy' ),
								'required' => __( 'Required', 'blocksy' ),
							]
						),
						'setting' => [
							'type' => 'option'
						],
					],

					'woocommerce_checkout_phone_field' => [
						'label' => __( 'Phone Field', 'blocksy' ),
						'type' => 'ct-select',
						'value' => 'required',
						'view' => 'text',
						'design' => 'block',
						'choices' => blocksy_ordered_keys(
							[
								'hidden' => __( 'Hidden', 'blocksy' ),
								'optional' => __( 'Optional', 'blocksy' ),
								'required' => __( 'Required', 'blocksy' ),
							]
						),
						'setting' => [
							'type' => 'option'
						],
					],

					'wp_page_for_privacy_policy' => [
						'label' => __('Privacy Policy Page', 'blocksy'),
						'type' => 'ct-select',
						'value' => '',
						'view' => 'text',
						'design' => 'block',
						'divider' => 'top:full',
						'choices' => blocksy_ordered_keys($page_choices_result),
						'setting' => [
							'type' => 'option'
						],
					],

					'woocommerce_terms_page_id' => [
						'label' => __( 'Terms And Conditions Page', 'blocksy' ),
						'type' => 'ct-select',
						'value' => '',
						'view' => 'text',
						'design' => 'block',
						'choices' => blocksy_ordered_keys($page_choices_result),
						'setting' => [
							'type' => 'option'
						],
					],

					'woocommerce_checkout_privacy_policy_text' => [
						'label' => __( 'Privacy policy', 'blocksy' ),
						'desc' => __( 'Optionally add some text about your store privacy policy to show during checkout.', 'blocksy' ),
						'type' => 'wp-editor',
						'value' => __('Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our [privacy_policy].', 'blocksy'),
						'disableRevertButton' => true,
						'quicktags' => false,
						'mediaButtons' => false,
						'tinymce' => [
							'toolbar1' => 'bold,italic,link,alignleft,aligncenter,alignright,undo,redo',
						],
						'setting' => [
							'type' => 'option'
						],
					],

					'woocommerce_checkout_terms_and_conditions_checkbox_text' => [
						'label' => __( 'Terms and conditions', 'blocksy' ),
						'desc' => __( 'Optionally add some text for the terms checkbox that customers must accept.', 'blocksy' ),
						'type' => 'text',
						'value' => blocksy_safe_sprintf(
							__(
								'I have read and agree to the website %s',
								'blocksy'
							),
							'[terms]'
						),
						'disableRevertButton' => true,
						'setting' => [
							'type' => 'option'
						],
					],

				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					'checkout_order_border' => [
						'label' => __( 'Order Review Border', 'blocksy' ),
						'type' => 'ct-border',
						'design' => 'block',
						'sync' => 'live',
						'value' => [
							'width' => 2,
							'style' => 'solid',
							'color' => [
								'color' => 'var(--theme-border-color)',
							],
						],
						'responsive' => true,
					],

					'checkout_order_border_radius' => [
						'label' => __( 'Order Review Border Radius', 'blocksy' ),
						'type' => 'ct-spacing',
						'divider' => 'top',
						'setting' => [ 'transport' => 'postMessage' ],
						'value' => blocksy_spacing_value(),
						'responsive' => true
					],

					'checkout_order_padding' => [
						'label' => __( 'Order Review Padding', 'blocksy' ),
						'type' => 'ct-spacing',
						'divider' => 'top',
						'value' => [
							'desktop' => blocksy_spacing_value([
								'top' => '35px',
								'left' => '35px',
								'right' => '35px',
								'bottom' => '35px',
							]),
							'tablet' => blocksy_spacing_value([
								'top' => '30px',
								'left' => '30px',
								'right' => '30px',
								'bottom' => '30px',
							]),
							'mobile'=> blocksy_spacing_value([
								'top' => '25px',
								'left' => '25px',
								'right' => '25px',
								'bottom' => '25px',
							]),
						],
						'responsive' => true,
						'sync' => 'live',
					],

				],
			],
		],
	],

];