<?php
/**
 * Cart page options
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

$options = [

	blocksy_rand_md5() => [
		'label' => __('Cart Page', 'blocksy'),
		'type' => 'ct-panel',
		'setting' => ['transport' => 'postMessage'],
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					apply_filters(
						'blocksy_customizer_options:woocommerce:cart_page:before',
						[]
					),

					'cart_page_image_ratio' => [
						'label' => __('Image Ratio', 'blocksy'),
						'type' => 'ct-ratio',
						'view' => 'inline',
						'value' => '1/1',
						'divider' => 'top:full',
						'sync' => blocksy_sync_whole_page([
							'loader_selector' => '.ct-cart-form .woocommerce-cart-form'
						]),
					],

					'cart_page_image_size' => [
						'label' => __('Image Size', 'blocksy'),
						'type' => 'ct-select',
						'value' => 'woocommerce_thumbnail',
						'view' => 'text',
						'design' => 'inline',
						'divider' => 'top',
						'choices' => blocksy_ordered_keys(
							blocksy_get_all_image_sizes()
						),
						'sync' => blocksy_sync_whole_page([
							'loader_selector' => '.ct-cart-form .woocommerce-cart-form'
						]),
					],

				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					'cart_totals_border' => [
						'label' => __( 'Cart Totals Border', 'blocksy' ),
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

					'cart_totals_border_radius' => [
						'label' => __( 'Cart Totals Border Radius', 'blocksy' ),
						'type' => 'ct-spacing',
						'divider' => 'top',
						'setting' => [ 'transport' => 'postMessage' ],
						'value' => blocksy_spacing_value(),
						'responsive' => true,
					],

					'cart_totals_padding' => [
						'label' => __( 'Cart Totals Padding', 'blocksy' ),
						'type' => 'ct-spacing',
						'divider' => 'top',
						'value' => [
							'desktop' => blocksy_spacing_value([
								'top' => '30px',
								'left' => '30px',
								'right' => '30px',
								'bottom' => '30px',
							]),
							'tablet' => blocksy_spacing_value([
								'top' => '25px',
								'left' => '25px',
								'right' => '25px',
								'bottom' => '25px',
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

		]
	],

];