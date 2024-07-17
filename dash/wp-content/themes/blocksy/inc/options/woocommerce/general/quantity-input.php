<?php
/**
 * Quantity input options
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

$options = [

	blocksy_rand_md5() => [
		'label' => __('Quantity Input', 'blocksy'),
		'type' => 'ct-panel',
		'setting' => ['transport' => 'postMessage'],
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					'has_custom_quantity' => [
						'label' => __( 'Custom Quantity Input', 'blocksy' ),
						'type' => 'ct-switch',
						'value' => 'yes',
						'sync' => blocksy_sync_whole_page([
							'prefix' => 'product',
							'loader_selector' => '.quantity'
						]),
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [ 'has_custom_quantity' => 'yes' ],
						'options' => [

							'quantity_type' => [
								// 'label' => __( 'Quantity Input Type', 'blocksy' ),
								'label' => false,
								'type' => 'ct-radio',
								'value' => 'type-2',
								'view' => 'text',
								'design' => 'block',
								// 'divider' => 'top',
								'disableRevertButton' => true,
								'setting' => [ 'transport' => 'postMessage' ],
								'choices' => [
									'type-1' => __( 'Type 1', 'blocksy' ),
									'type-2' => __( 'Type 2', 'blocksy' ),
								],
							],

						],
					],

				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					'global_quantity_color' => [
						'label' => __( 'Quantity Main Color', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
						'sync' => 'live',
						'value' => [
							'default' => [
								'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
							],

							'hover' => [
								'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
							],
						],

						'pickers' => [
							[
								'title' => __( 'Initial', 'blocksy' ),
								'id' => 'default',
								'inherit' => 'var(--theme-button-background-initial-color)',

							],

							[
								'title' => __( 'Hover', 'blocksy' ),
								'id' => 'hover',
								'inherit' => 'var(--theme-button-background-hover-color)'
							],
						],
					],

					'global_quantity_arrows' => [
						'label' => __( 'Quantity Arrows Color', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
						'sync' => 'live',
						'value' => [
							'default' => [
								'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
							],

							'default_type_2' => [
								'color' => 'var(--theme-text-color)',
							],

							'hover' => [
								'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
							],
						],

						'pickers' => [
							[
								'title' => __( 'Initial', 'blocksy' ),
								'id' => 'default',
								'inherit' => '#ffffff',
								'condition' => [ 'quantity_type' => 'type-1' ]
							],

							[
								'title' => __( 'Initial', 'blocksy' ),
								'id' => 'default_type_2',
								'condition' => [ 'quantity_type' => 'type-2' ]
							],

							[
								'title' => __( 'Hover', 'blocksy' ),
								'id' => 'hover',
								'inherit' => '#ffffff'
							],
						],
					],

				],
			],

		],
	],

];