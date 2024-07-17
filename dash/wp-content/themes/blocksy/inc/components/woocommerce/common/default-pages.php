<?php

namespace Blocksy;

// This file is loaded even if WooCommerce is not active.
// It should never contain any calls to functions from WooCommerce, unles it
// is inside the woocommerce_create_pages filter.
class WooDefaultPages {
	use WordPressActionsManager;

	private $filters = [
		[
			'action' => 'woocommerce_create_pages',
			'priority' => 100
		]
	];

	public function __construct() {
		$this->attach_hooks();
	}

	public function woocommerce_create_pages($pages) {
		if (isset($pages['cart']) && isset($pages['cart']['content'])) {
			$cart_shortcode = 'woocommerce_cart';
			$pages['cart']['content'] = '<!-- wp:shortcode -->[' . $cart_shortcode . ']<!-- /wp:shortcode -->';
		}

		if (isset($pages['checkout']) && isset($pages['checkout']['content'])) {
			$checkout_shortcode = 'woocommerce_checkout';
			$pages['checkout']['content'] = '<!-- wp:shortcode -->[' . $checkout_shortcode . ']<!-- /wp:shortcode -->';
		}

		return $pages;
	}
}
