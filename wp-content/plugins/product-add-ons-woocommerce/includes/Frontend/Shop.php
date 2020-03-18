<?php

namespace ZAddons\Frontend;

use ZAddons\Frontend;

class Shop
{
	public function __construct()
	{
		add_filter('woocommerce_product_add_to_cart_url', array($this, 'add_to_cart_url'), 50, 1);

		add_action('woocommerce_product_add_to_cart_text', array($this, 'add_to_cart_text'), 10, 1);

		add_filter('woocommerce_add_to_cart_validation', array($this, 'add_to_cart_validation'), 50, 2);

	}

	public function add_to_cart_url($url)
	{
		global $product;

		if (is_object($product) && ((is_shop() || is_product_category() || is_product_tag()))) {
			if (Frontend::hasTypes($product)) return get_permalink($product->get_id());
		}
		return $url;
	}

	public function add_to_cart_text($text = "")
	{

		global $product, $post;

		if (is_object($product) && !is_single($post)) {
			if (Frontend::hasTypes($product)) return 'Select options';
		}

		return $text;
	}

	public function add_to_cart_validation($passed, $product_id)
	{
		if (is_ajax()) {
			if (Frontend::hasTypes($product_id)) return false;
		}

		return $passed;
	}
}
