<?php
/**
 * Star rating options
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

$options = [

	blocksy_rand_md5() => [
		'label' => __('Star Rating', 'blocksy'),
		'type' => 'ct-panel',
		'setting' => ['transport' => 'postMessage'],
		'inner-options' => [

			'starRatingColor' => [
				'label' => __( 'Star Rating Color', 'blocksy' ),
				'type'  => 'ct-color-picker',
				'design' => 'inline',
				'setting' => [ 'transport' => 'postMessage' ],

				'value' => [
					'default' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
					],

					'inactive' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
					],
				],

				'pickers' => [
					[
						'title' => __( 'Active', 'blocksy' ),
						'id' => 'default',
						'inherit' => '#FDA256'
					],

					[
						'title' => __( 'Inactive', 'blocksy' ),
						'id' => 'inactive',
						'inherit' => '#F9DFCC'
					],
				],
			],

		],
	],

];