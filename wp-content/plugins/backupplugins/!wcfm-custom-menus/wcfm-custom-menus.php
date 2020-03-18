<?php
/**
 * Plugin Name: WCFM - Custom Menus
 * Plugin URI: http://wclovers.com
 * Description: WCFM Custom Menus.
 * Author: WC Lovers
 * Version: 1.0.0
 * Author URI: http://wclovers.com
 *
 * Text Domain: wcfm-custom-menus
 * Domain Path: /lang/
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 3.2.0
 *
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('WCFM')) return; // Exit if WCFM not installed

/**
 * WCFM - Custom Menus Query Var
 */
function wcfmcsm_query_vars( $query_vars ) {
	$wcfm_modified_endpoints = (array) get_option( 'wcfm_endpoints' );
	
	$query_custom_menus_vars = array(
		'wcfm-build'               => ! empty( $wcfm_modified_endpoints['wcfm-build'] ) ? $wcfm_modified_endpoints['wcfm-build'] : 'wcfm-build',
// 		'wcfm-service'             => ! empty( $wcfm_modified_endpoints['wcfm-service'] ) ? $wcfm_modified_endpoints['wcfm-service'] : 'wcfm-service',
// 		'wcfm-upgrade'             => ! empty( $wcfm_modified_endpoints['wcfm-upgrade'] ) ? $wcfm_modified_endpoints['wcfm-upgrade'] : 'wcfm-upgrade',
// 		'wcfm-contact'             => ! empty( $wcfm_modified_endpoints['wcfm-contact'] ) ? $wcfm_modified_endpoints['wcfm-contact'] : 'wcfm-contact',
// 		'wcfm-appointment'         => ! empty( $wcfm_modified_endpoints['wcfm-appointment'] ) ? $wcfm_modified_endpoints['wcfm-appointment'] : 'wcfm-appointment',
	);
	
	$query_vars = array_merge( $query_vars, $query_custom_menus_vars );
	
	return $query_vars;
}
add_filter( 'wcfm_query_vars', 'wcfmcsm_query_vars', 50 );

/**
 * WCFM - Custom Menus End Point Title
 */
function wcfmcsm_endpoint_title( $title, $endpoint ) {
	global $wp;
	switch ( $endpoint ) {
		case 'wcfm-build' :
			$title = __( 'Build', 'wcfm-custom-menus' );
		break;
		
// 		case 'wcfm-service' :
// 			$title = __( 'Service', 'wcfm-custom-menus' );
// 		break;
		
// 		case 'wcfm-upgrade' :
// 			$title = __( 'Upgrade', 'wcfm-custom-menus' );
// 		break;
		
// 		case 'wcfm-contact' :
// 			$title = __( 'Contact', 'wcfm-custom-menus' );
// 		break;
		
// 		case 'wcfm-appointment' :
// 			$title = __( 'Appointment', 'wcfm-custom-menus' );
// 		break;
	}
	
	return $title;
}
add_filter( 'wcfm_endpoint_title', 'wcfmcsm_endpoint_title', 50, 2 );

/**
 * WCFM - Custom Menus Endpoint Intialize
 */
function wcfmcsm_init() {
	global $WCFM_Query;

	// Intialize WCFM End points
	$WCFM_Query->init_query_vars();
	$WCFM_Query->add_endpoints();
	
	if( !get_option( 'wcfm_updated_end_point_cms' ) ) {
		// Flush rules after endpoint update
		flush_rewrite_rules();
		update_option( 'wcfm_updated_end_point_cms', 1 );
	}
}
add_action( 'init', 'wcfmcsm_init', 50 );

/**
 * WCFM - Custom Menus Endpoiint Edit
 */
function wcfm_custom_menus_endpoints_slug( $endpoints ) {
	
	$custom_menus_endpoints = array(
												'wcfm-build'        => 'wcfm-build',
												// 'wcfm-service'      => 'wcfm-service',
												// 'wcfm-upgrade'      => 'wcfm-upgrade',
												// 'wcfm-contact'      => 'wcfm-contact',
												// 'wcfm-appointment'  => 'wcfm-appointment',
												);
	
	$endpoints = array_merge( $endpoints, $custom_menus_endpoints );
	
	return $endpoints;
}
add_filter( 'wcfm_endpoints_slug', 'wcfm_custom_menus_endpoints_slug' );

if(!function_exists('get_wcfm_custom_menus_url')) {
	function get_wcfm_custom_menus_url( $endpoint ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_custom_menus_url = wcfm_get_endpoint_url( $endpoint, '', $wcfm_page );
		return $wcfm_custom_menus_url;
	}
}

/**
 * WCFM - Custom Menus
 */
function wcfmcsm_wcfm_menus( $menus ) {
	global $WCFM;
	
	$custom_menus = array( 'wcfm-build' => array(   'label'  => __( 'Build', 'wcfm-custom-menus'),
																									 'url'       => get_wcfm_custom_menus_url( 'wcfm-build' ),
																									 'icon'      => 'cubes',
																									 'priority'  => 5.1
																									)
												// 	'wcfm-service' => array(   'label'  => __( 'Service', 'wcfm-custom-menus'),
												// 														 'url'       => get_wcfm_custom_menus_url( 'wcfm-service' ),
												// 														 'icon'      => 'cubes',
												// 														 'priority'  => 5.2
												// 														),
												// 	'wcfm-upgrade' => array(   'label'  => __( 'Upgrade', 'wcfm-custom-menus'),
												// 														 'url'       => get_wcfm_custom_menus_url( 'wcfm-upgrade' ),
												// 														 'icon'      => 'cubes',
												// 														 'priority'  => 5.3
												// 														),
												// 	'wcfm-contact' => array(   'label'  => __( 'Contact', 'wcfm-custom-menus'),
												// 														 'url'       => get_wcfm_custom_menus_url( 'wcfm-contact' ),
												// 														 'icon'      => 'cubes',
												// 														 'priority'  => 5.4
												// 														),
												// 	'wcfm-appointment' => array(   'label'  => __( 'Appointment', 'wcfm-custom-menus'),
												// 																 'url'       => get_wcfm_custom_menus_url( 'wcfm-appointment' ),
												// 																 'icon'      => 'cubes',
												// 																 'priority'  => 5.5
												// 																)
											);
	
	$menus = array_merge( $menus, $custom_menus );
		
	return $menus;
}
add_filter( 'wcfm_menus', 'wcfmcsm_wcfm_menus', 20 );

/**
 *  WCFM - Custom Menus Views
 */
function wcfm_csm_load_views( $end_point ) {
	global $WCFM, $WCFMu;
	$plugin_path = trailingslashit( dirname( __FILE__  ) );
	
	switch( $end_point ) {
		case 'wcfm-build':
			require_once( $plugin_path . 'views/wcfm-views-build.php' );
		break;
		
// 		case 'wcfm-service':
// 			require_once( $plugin_path . 'views/wcfm-views-service.php' );
// 		break;
		
// 		case 'wcfm-upgrade':
// 			require_once( $plugin_path . 'views/wcfm-views-upgrade.php' );
// 		break;
		
// 		case 'wcfm-contact':
// 			require_once( $plugin_path . 'views/wcfm-views-contact.php' );
// 		break;
		
// 		case 'wcfm-appointment':
// 			require_once( $plugin_path . 'views/wcfm-views-appointment.php' );
// 		break;
	}
}
add_action( 'wcfm_load_views', 'wcfm_csm_load_views', 50 );
add_action( 'before_wcfm_load_views', 'wcfm_csm_load_views', 50 );

// Custom Load WCFM Scripts
function wcfm_csm_load_scripts( $end_point ) {
	global $WCFM;
	$plugin_url = trailingslashit( plugins_url( '', __FILE__ ) );
	
	switch( $end_point ) {
		case 'wcfm-build':
			wp_enqueue_script( 'wcfm_build_js', $plugin_url . 'js/wcfm-script-build.js', array( 'jquery' ), $WCFM->version, true );
		break;
	}
}

add_action( 'wcfm_load_scripts', 'wcfm_csm_load_scripts' );
add_action( 'after_wcfm_load_scripts', 'wcfm_csm_load_scripts' );

// Custom Load WCFM Styles
function wcfm_csm_load_styles( $end_point ) {
	global $WCFM, $WCFMu;
	$plugin_url = trailingslashit( plugins_url( '', __FILE__ ) );
	
	switch( $end_point ) {
		case 'wcfm-build':
			wp_enqueue_style( 'wcfmu_build_css', $plugin_url . 'css/wcfm-style-build.css', array(), $WCFM->version );
		break;
	}
}
add_action( 'wcfm_load_styles', 'wcfm_csm_load_styles' );
add_action( 'after_wcfm_load_styles', 'wcfm_csm_load_styles' );

/**
 *  WCFM - Custom Menus Ajax Controllers
 */
function wcfm_csm_ajax_controller() {
	global $WCFM, $WCFMu;
	
	$plugin_path = trailingslashit( dirname( __FILE__  ) );
	
	$controller = '';
	if( isset( $_POST['controller'] ) ) {
		$controller = $_POST['controller'];
		
		switch( $controller ) {
			case 'wcfm-build':
				require_once( $plugin_path . 'controllers/wcfm-controller-build.php' );
				new WCFM_Build_Controller();
			break;
		}
	}
}
add_action( 'after_wcfm_ajax_controller', 'wcfm_csm_ajax_controller' );