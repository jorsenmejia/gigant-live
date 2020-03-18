<?php
/**
 * Plugin Name: Product Add-Ons WooCommerce
 * Plugin URI: https://www.bizswoop.com/wp/productaddons
 * Description: Add Customized Product Add-Ons Support for WooCommerce
 * Version: 1.0.14
 * Text Domain: Product-Add-Ons-WooCommerce
 * WC requires at least: 2.4.0
 * WC tested up to: 3.9.0
 * Author: BizSwoop a CPF Concepts, LLC Brand
 * Author URI: http://www.bizswoop.com
 */

namespace ZAddons;
const ACTIVE = true;
const PLUGIN_ROOT = __DIR__;
const PLUGIN_ROOT_FILE = __FILE__;

spl_autoload_register(function ($name) {
	$name = explode('\\', $name);
	$name[0] = 'includes';
	$path = __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $name) . '.php';

	if (file_exists($path)) {
		require_once $path;
	}
}, false);

new Setup();
