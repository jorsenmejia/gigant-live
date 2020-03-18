<?php
/**
 * Plugin Name: WCFM - CPT
 * Plugin URI: https://wclovers.com/addons/
 * Description: WCFM - CPT Manager
 * Author: WC Lovers
 * Version: 1.0.0
 * Author URI: https://wclovers.com
 *
 * Text Domain: wcfm-cpt
 * Domain Path: /lang/
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 3.7.0
 *
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

if(!defined('WCFM_TOKEN')) return;
if(!defined('WCFM_TEXT_DOMAIN')) return;

if ( ! class_exists( 'WCFMcpt_Dependencies' ) )
	require_once 'helpers/class-wcfm-cpt-dependencies.php';

if( !WCFMcpt_Dependencies::woocommerce_plugin_active_check() )
	return;

if( !WCFMcpt_Dependencies::wcfm_plugin_active_check() )
	return;

require_once 'helpers/wcfm-cpt-core-functions.php';
require_once 'wcfm-cpt-config.php';

if(!class_exists('WCFM_CPT')) {
	include_once( 'core/class-wcfm-cpt.php' );
	global $WCFM, $WCFMcpt, $WCFM_Query;
	$WCFMcpt = new WCFM_CPT( __FILE__ );
	$GLOBALS['WCFMcpt'] = $WCFMcpt;
}