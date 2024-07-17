<?php
/**
 * Store notice options
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

$options = [

	'woocommerce_demo_store' => [
		'label' => __('Store Notice', 'blocksy'),
		'type' => 'ct-panel',
		'switch' => true,
		'value' => 'no',
		'switchBehavior' => 'boolean',
		'setting' => [
			'type' => 'option',
		],
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					'woocommerce_demo_store_notice' => [
						'label' => false,
						'type' => 'textarea',
						'value' => __( 'This is a demo store for testing purposes &mdash; no orders shall be fulfilled.', 'blocksy' ),
						'setting' => [
							'type' => 'option',
							'transport' => 'postMessage'
						],
						'disableRevertButton' => true,
					],

					'store_notice_position' => [
						'type' => 'ct-radio',
						'label' => __( 'Notice Position', 'blocksy' ),
						'value' => 'bottom',
						'view' => 'text',
						// 'disableRevertButton' => true,
						'setting' => [ 'transport' => 'postMessage' ],
						'choices' => [
							'top' => __('Top', 'blocksy'),
							'bottom' => __('Bottom', 'blocksy'),
						],
					],

				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					'wooNoticeContent' => [
						'label' => __( 'Notice Font Color', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
						'divider' => 'top',
						'skipEditPalette' => true,
						'setting' => [ 'transport' => 'postMessage' ],

						'value' => [
							'default' => [
								'color' => '#ffffff',
							],
						],

						'pickers' => [
							[
								'title' => __( 'Initial', 'blocksy' ),
								'id' => 'default',
							],
						],
					],

					'wooNoticeBackground' => [
						'label' => __( 'Notice Background Color', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
						'skipEditPalette' => true,
						'setting' => [ 'transport' => 'postMessage' ],

						'value' => [
							'default' => [
								'color' => 'var(--theme-palette-color-1)',
							],
						],

						'pickers' => [
							[
								'title' => __( 'Initial', 'blocksy' ),
								'id' => 'default',
							],
						],
					],

				],
			],

		],
	],

];