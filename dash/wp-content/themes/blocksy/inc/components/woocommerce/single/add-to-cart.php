<?php

namespace Blocksy;

class WooCommerceAddToCart {
	use WordPressActionsManager;

	private $handled_product_ids = [];

	private $actions = [
		[
			'action' => 'woocommerce_before_add_to_cart_form',
			'priority' => 10
		],

		[
			'action' => 'woocommerce_before_add_to_cart_quantity',
			'priority' => PHP_INT_MAX
		],

		[
			'action' => 'woocommerce_before_add_to_cart_button',
			'priority' => PHP_INT_MAX
		],

		[
			'action' => 'woocommerce_after_add_to_cart_button',
			'priority' => 100
		],

		[
			'action' => 'woocommerce_post_class',
			'priority' => 10
		]
	];

	public function __construct() {
		$this->attach_hooks([
			'exclude' => [
				'woocommerce_after_add_to_cart_button'
			]
		]);
	}

	private function product_was_handled($product) {
		return in_array($product->get_id(), $this->handled_product_ids);
	}

	private function output_cart_action_open() {
		if (
			(is_product() || wp_doing_ajax())
			&&
			! blocksy_manager()->screen->uses_woo_default_template()
		) {
			return;
		}

		$attr = apply_filters('blocksy:woocommerce:cart-actions:attr', [
			'class' => 'ct-cart-actions'
		]);

		echo '<div ' . blocksy_attr_to_html($attr) . '>';

		$this->attach_hooks([
			'only' => [
				'woocommerce_after_add_to_cart_button'
			]
		]);
	}

	public function woocommerce_before_add_to_cart_form() {
		global $product;
		global $root_product;

		if ($this->product_was_handled($product)) {
			return;
		}

		$root_product = $product;
	}

	public function woocommerce_before_add_to_cart_quantity() {
		global $product;
		global $root_product;

		if ($this->product_was_handled($product)) {
			return;
		}

		if (! $root_product) {
			return;
		}

		if (
			! $root_product->is_type('simple')
			&&
			! $root_product->is_type('variation')
			&&
			! $root_product->is_type('variable')
			&&
			! $this->check_product_type($root_product)
		) {
			return;
		}

		$this->output_cart_action_open();
	}

	public function woocommerce_before_add_to_cart_button() {
		global $product;
		global $root_product;

		if ($this->product_was_handled($product)) {
			return;
		}

		if (! $root_product) {
			return;
		}

		if (
			! $root_product->is_type('grouped')
			&&
			! $root_product->is_type('external')
		) {
			return;
		}

		$this->output_cart_action_open();
	}

	public function woocommerce_after_add_to_cart_button() {
		global $product;

		if ($this->product_was_handled($product)) {
			return;
		}

		if (! $product) {
			return;
		}

		if (
			! $product->is_type('simple')
			&&
			! $product->is_type('variable')
			&&
			! $product->is_type('grouped')
			&&
			! $product->is_type('external')
			&&
			! $this->check_product_type($product)
		) {
			return;
		}

		if (
			(
				$product->is_type('simple')
				||
				$product->is_type('variable')
				||
				$this->check_product_type($product)
			)
			&&
			! did_action('woocommerce_before_add_to_cart_quantity')
		) {
			return;
		}

		echo '</div>';

		// On single product pages we know for sure that there's only one
		// product that needs handling. On other pages or during AJAX requests,
		// we need to make sure that we don't handle the same product twice.
		if (is_product()) {
			$this->detach_hooks();
		} else {
			$this->handled_product_ids[] = $product->get_id();
		}
	}

	public function woocommerce_post_class($classes) {
		global $product;
		global $woocommerce_loop;

		if ($this->product_was_handled($product)) {
			return $classes;
		}

		$default_product_layout = blocksy_get_woo_single_layout_defaults();

		$layout = blocksy_get_theme_mod(
			'woo_single_layout',
			blocksy_get_woo_single_layout_defaults()
		);

		if (
			
			(
				function_exists('blocksy_has_product_specific_layer')
				&&
				! blocksy_has_product_specific_layer('product_add_to_cart')
			)
			||
			! $product
			||
			$product->is_type('external')
			||
			$woocommerce_loop['name'] === 'related'
			||
			(
				! is_product()
				&&
				! wp_doing_ajax()
			)
		) {
			return $classes;
		}

		$has_ajax_add_to_cart = blocksy_get_theme_mod(
			'has_ajax_add_to_cart',
			'no'
		);

		if (
			$has_ajax_add_to_cart === 'yes'
			&&
			get_option('woocommerce_cart_redirect_after_add', 'no') === 'no'
		) {
			$classes[] = 'ct-ajax-add-to-cart';
		}

		return $classes;
	}

	public function check_product_type($product) {
		$allowed_custom_product_types = [
			'subscription',
			'variable-subscription',
			'woosb'
		];

		return in_array($product->get_type(), $allowed_custom_product_types);
	}
}

