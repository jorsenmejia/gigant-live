<?php
/**
 * Plugin Name: Woo Product Filter
 * Plugin URI: https://woobewoo.com/product/woocommerce-filter/
 * Description: Filter products in your store in most efficient way
 * Version: 1.1.8
 * Author: woobewoo
 * Author URI: https://woobewoo.com/
 * Text Domain: woo-product-filter
 * Domain Path: /languages
 * WC requires at least: 3.4.0
 * WC tested up to: 3.8.0
 **/
	/**
	 * Base config constants and functions
	 */
    require_once(dirname(__FILE__). DIRECTORY_SEPARATOR. 'config.php');
    require_once(dirname(__FILE__). DIRECTORY_SEPARATOR. 'functions.php');
	/**
	 * Connect all required core classes
	 */
    importClassWpf('dbWpf');
    importClassWpf('installerWpf');
    importClassWpf('baseObjectWpf');
    importClassWpf('moduleWpf');
    importClassWpf('modelWpf');
    importClassWpf('viewWpf');
    importClassWpf('controllerWpf');
    importClassWpf('helperWpf');
    importClassWpf('dispatcherWpf');
    importClassWpf('fieldWpf');
    importClassWpf('tableWpf');
    importClassWpf('frameWpf');
	/**
	 * @deprecated since version 1.0.1
	 */
    importClassWpf('langWpf');
    importClassWpf('reqWpf');
    importClassWpf('uriWpf');
    importClassWpf('htmlWpf');
    importClassWpf('responseWpf');
    importClassWpf('fieldAdapterWpf');
    importClassWpf('validatorWpf');
    importClassWpf('errorsWpf');
    importClassWpf('utilsWpf');
    importClassWpf('modInstallerWpf');
	importClassWpf('installerDbUpdaterWpf');
	importClassWpf('dateWpf');
	/**
	 * Check plugin version - maybe we need to update database, and check global errors in request
	 */
    installerWpf::update();
    errorsWpf::init();
    /**
	 * Start application
	 */
    frameWpf::_()->parseRoute();
    frameWpf::_()->init();
    frameWpf::_()->exec();
