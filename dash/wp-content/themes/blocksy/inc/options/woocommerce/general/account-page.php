<?php
/**
 * Account page options
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

$options = [

	blocksy_rand_md5() => [
		'label' => __('Account Page', 'blocksy'),
		'type' => 'ct-panel',
		'setting' => ['transport' => 'postMessage'],
		'inner-options' => [

			blocksy_rand_md5() => [
				'title' => __( 'General', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					'has_account_page_avatar' => [
						'label' => __( 'User Avatar', 'blocksy' ),
						'type' => 'ct-switch',
						'value' => 'no',
						'sync' => blocksy_sync_whole_page([
							'prefix' => 'single_page',
							'loader_selector' => '.ct-woo-account'
						]),
					],

					blocksy_rand_md5() => [
						'type' => 'ct-condition',
						'condition' => [ 'has_account_page_avatar' => 'yes' ],
						'options' => [

							'account_page_avatar_size' => [
								'label' => __( 'Avatar Size', 'blocksy' ),
								'type' => 'ct-number',
								'design' => 'inline',
								'value' => 35,
								'min' => 20,
								'max' => 100,
								'divider' => 'bottom',
								'setting' => [ 'transport' => 'postMessage' ],
							],
						],
					],

					'has_account_page_name' => [
						'label' => __( 'User Name', 'blocksy' ),
						'type' => 'ct-switch',
						'value' => 'no',
						'sync' => blocksy_sync_whole_page([
							'prefix' => 'single_page',
							'loader_selector' => '.ct-woo-account'
						]),
					],

					'has_account_page_quick_actions' => [
						'label' => __( 'Navigation Quick Links', 'blocksy' ),
						'type' => 'ct-switch',
						'value' => 'no',
						'sync' => blocksy_sync_whole_page([
							'prefix' => 'single_page',
							'loader_selector' => '.ct-woo-account'
						]),
					],
				],
			],

			blocksy_rand_md5() => [
				'title' => __( 'Design', 'blocksy' ),
				'type' => 'tab',
				'options' => [

					'account_nav_text_color' => [
						'label' => __( 'Navigation Text Color', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
						'setting' => [ 'transport' => 'postMessage' ],
						'value' => [
							'default' => [
								'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
							],

							'active' => [
								'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
							],
						],

						'pickers' => [
							[
								'title' => __( 'Initial', 'blocksy' ),
								'id' => 'default',
								'inherit' => 'var(--theme-palette-color-3)'
							],

							[
								'title' => __( 'Active', 'blocksy' ),
								'id' => 'active',
								'inherit' => '#ffffff'
							],
						],
					],

					'account_nav_background_color' => [
						'label' => __( 'Navigation Background Color', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
						'setting' => [ 'transport' => 'postMessage' ],
						'value' => [
							'default' => [
								'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
							],

							'active' => [
								'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
							],
						],

						'pickers' => [
							[
								'title' => __( 'Initial', 'blocksy' ),
								'id' => 'default',
								'inherit' => '#ffffff'
							],

							[
								'title' => __( 'Active', 'blocksy' ),
								'id' => 'active',
								'inherit' => 'var(--theme-palette-color-1)'
							],
						],
					],

					'account_nav_divider_color' => [
						'label' => __( 'Navigation Divider Color', 'blocksy' ),
						'type'  => 'ct-color-picker',
						'design' => 'inline',
						'setting' => [ 'transport' => 'postMessage' ],
						'value' => [
							'default' => [
								'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
							],
						],

						'pickers' => [
							[
								'title' => __( 'Initial', 'blocksy' ),
								'id' => 'default',
								'inherit' => 'rgba(0, 0, 0, 0.05)'
							],
						],
					],

					'account_nav_shadow' => [
						'label' => __( 'Navigation Shadow', 'blocksy' ),
						'type' => 'ct-box-shadow',
						'design' => 'inline',
						'sync' => 'live',
						// 'responsive' => true,
						'divider' => 'top',
						'value' => blocksy_box_shadow_value([
							'enable' => false,
							'h_offset' => 0,
							'v_offset' => 10,
							'blur' => 20,
							'spread' => 0,
							'inset' => false,
							'color' => [
								'color' => 'rgba(0, 0, 0, 0.03)',
							],
						])
					],

				],
			],

		],
	],

];