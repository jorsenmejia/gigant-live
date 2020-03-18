<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    woocommerce_refund_and_exchange_lite
 * @subpackage woocommerce_refund_and_exchange_lite/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    woocommerce_refund_and_exchange_lite
 * @subpackage woocommerce_refund_and_exchange_lite/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class woocommerce_refund_and_exchange_lite_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woocommerce-refund-and-exchange-lite',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
