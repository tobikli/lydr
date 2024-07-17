<?php

$options = [
	'woo_general_section_options' => [
		'type' => 'ct-options',
		'setting' => [ 'transport' => 'postMessage' ],
		'inner-options' => [

			blocksy_get_options('woocommerce/general/messages'),

			blocksy_get_options('woocommerce/general/star-rating'),

			blocksy_get_options('woocommerce/general/quantity-input'),

			blocksy_get_options('woocommerce/general/product-badges'),

			[
				blocksy_rand_md5() => [
					'type' => 'ct-divider',
				],	
			],

			blocksy_get_options('woocommerce/general/cart-page'),

			blocksy_get_options('woocommerce/general/checkout-page'),

			blocksy_get_options('woocommerce/general/account-page'),

			apply_filters(
				'blocksy_customizer_options:woocommerce:general:end',
				[]
			),

			[
				blocksy_rand_md5() => [
					'type' => 'ct-divider',
				],	
			],

			blocksy_get_options('woocommerce/general/store-notice'),

		],
	],
];