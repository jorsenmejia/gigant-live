<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://makewebbetter.com/
 * @since             1.0.0
 * @package           woocommerce_refund_and_exchange_lite
 *
 * @wordpress-plugin
 * Plugin Name:       Return Refund and Exchange for Woocommerce
 * Plugin URI:        http://makewebbetter.com/woocommerce-refund-and-exchange-lite
 * Description:       WooCommerce Refund and Exchange lite allows users to submit product refund. The plugin provides a dedicated mailing system that would help to communicate better between store owner and customers.This is lite version of Woocommerce Refund And Exchnage.
 * Version:           1.0.8
 * Author:            MakeWebBetter
 * Author URI:        http://makewebbetter.com/
 * WC tested up to:   3.8.0
 * Tested up to: 	  5.3.0
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       woocommerce-refund-and-exchange-lite
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
$activated = true;
$ced_rnx_activated_main = false;
$ced_rnx_activated_main_cc= false;
if (function_exists('is_multisite') && is_multisite())
{
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) )
	{
		$activated = false;
	}
	if ( is_plugin_active( 'woocommerce-rma-for-return-refund-and-exchange/mwb-woocommerce-rma.php' ) )
	{
		$activated = false;
		$ced_rnx_activated_main = true;
	}
	if ( is_plugin_active( 'woocommerce-refund-and-exchange/woocommerce-refund-and-exchange.php' ) )
	{
		$activated = false;
		$ced_rnx_activated_main_cc = true;
	}

}
else
{
	if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
	{
		$activated = false;
	}
	if (in_array('woocommerce-rma-for-return-refund-and-exchange/mwb-woocommerce-rma.php', apply_filters('active_plugins', get_option('active_plugins'))))
	{
		$activated = false;
		$ced_rnx_activated_main = true;
	}
	if (in_array('woocommerce-refund-and-exchange/woocommerce-refund-and-exchange.php', apply_filters('active_plugins', get_option('active_plugins'))))
	{
		$activated = false;
		$ced_rnx_activated_main_cc = true;
	}
}

/**
 * Check if WooCommerce is active
 **/
if ($activated) 
{
	define('MWB_REFUND_N_EXCHANGE_LITE_DIRPATH', plugin_dir_path( __FILE__ ));
	define('MWB_REFUND_N_EXCHANGE_LITE_URL', plugin_dir_url( __FILE__ ));

	/**
	 * The code that runs during plugin activation.
	 * 
	 */
	function activate_woocommerce_refund_and_exchange_lite() {
		$email = get_option('admin_email', false);
		$admin = get_user_by('email', $email);
		$admin_id = $admin->ID;
		$ced_rnx_return_request_form_page_id = 0;
		$ced_rnx_return_request_form = array(
				'post_author'    => $admin_id,
				'post_name'      => 'refund-request-form',
				'post_title'     => 'Refund Request Form',
				'post_type'      => 'page',
				'post_status'    => 'publish',
					
		);
			
		$page_id = wp_insert_post($ced_rnx_return_request_form);
			
		if($page_id) {
			$ced_rnx_return_request_form_page_id=$page_id;
		}
		update_option('ced_rnx_return_request_form_page_id',$ced_rnx_return_request_form_page_id);
	}

	/**
	 * The code that runs during plugin deactivation.
	 * 
	 */
	function deactivate_woocommerce_refund_and_exchange_lite() {
		
		$page_id = get_option('ced_rnx_return_request_form_page_id');
		wp_delete_post($page_id);		
		delete_option('ced_rnx_return_request_form_page_id');
	}

	register_activation_hook( __FILE__, 'activate_woocommerce_refund_and_exchange_lite' );
	register_deactivation_hook( __FILE__, 'deactivate_woocommerce_refund_and_exchange_lite' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce_refund_and_exchange_lite.php';

	/**
	 * This function is used for formatting the price seprator
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 * @param unknown $price
	 * @return price
	 */
	function ced_rnx_lite_currency_seprator($price)
	{
		$price = apply_filters( 'formatted_woocommerce_price', number_format( $price, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() ), $price, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );

		return $price;
	}

	/**
	 * Add settings link on plugin page
	 * @name admin_settings_for_pmr()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	
	function ced_rnx_lite_admin_settings($actions, $plugin_file) {
		static $plugin;
		if (! isset ( $plugin )) {
	
			$plugin = plugin_basename ( __FILE__ );
		}
		if ($plugin == $plugin_file) {
			$settings = array (
					'settings' => '<a href="' . admin_url ( 'admin.php?page=wc-settings&tab=ced_rnx_setting' ) . '">' . __ ( 'Settings', 'woocommerce-refund-and-exchange-lite' ) . '</a>',
			);
			$actions = array_merge ( $settings, $actions );
		}
		return $actions;
	}
	add_filter( 'plugin_row_meta', 'mwb_upsell_lite_add_doc_and_premium_link', 10, 2 );

	function mwb_upsell_lite_add_doc_and_premium_link( $links, $file ) {

		if ( strpos( $file, 'woocommerce-refund-and-exchange-lite.php' ) !== false ) {

			$row_meta = array(
				'docs'    => '<a target="_blank" style="color:#FFF;background:linear-gradient(to right,#7a28ff 0,#00a1ff 100%);padding:5px;border-radius:6px;" href="https://docs.makewebbetter.com/woocommerce-refund-and-exchange-lite">'.esc_html__("Go to Docs", 'woocommerce-refund-and-exchange-lite' ).'</a>',
				'goPro' => '<a target="_blank" style="color:#FFF;background:linear-gradient(to right,#7a28ff 0,#00a1ff 100%);padding:5px;border-radius:6px;" href="https://makewebbetter.com/product/woocommerce-rma-return-refund-exchange/"><strong>'.esc_html__("Go Premium", 'woocommerce-refund-and-exchange-lite' ).'</strong></a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}
	
	//add link for settings
	add_filter ( 'plugin_action_links','ced_rnx_lite_admin_settings', 10, 5 );

	/**
	 * Add capabilities, priority must be after the initial role 
	 * @name admin_settings_for_pmr()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	function ced_rnx_role_capability()
	{
	    $ced_rnx_customer_role = get_role('customer');
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-request', true);

	    $ced_rnx_customer_role = get_role('administrator');
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-request', true);
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-approve', true);
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-cancel', true);
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-manage-stock', true);
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-amount', true);

	    $ced_rnx_customer_role = get_role('editor');
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-request', true);
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-approve', true);
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-cancel', true);
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-manage-stock', true);
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-amount', true);

	    $ced_rnx_customer_role = get_role('shop_manager');
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-request', true);
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-approve', true);
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-cancel', true);
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-manage-stock', true);
	    $ced_rnx_customer_role->add_cap('ced-rnx-refund-amount', true);
	    
	}

	// add capabilities, priority must be after the initial role   
	add_action('init', 'ced_rnx_role_capability', 11);

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_woocommerce_refund_and_exchange_lite() {

		$plugin = new woocommerce_refund_and_exchange_lite();
		$plugin->run();

	}
	run_woocommerce_refund_and_exchange_lite();
}
else
{
	/**
	 * Show warning message if woocommerce is not install
	 * @name ced_rnx_plugin_error_notice()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	
	function ced_rnx_plugin_error_notice_lite()
 	{ 
 		
		?>
		 <div class="error notice is-dismissible">
			<p><?php esc_html_e( 'Woocommerce is not activated, Please activate Woocommerce first to install WooCommerce Refund and Exchange Lite.', 'woocommerce-refund-and-exchange-lite' ); ?></p>
		</div>
   		<style>
   		#message{display:none;}
   		</style>
   	<?php 
 	} 
 	/**
	 * Show warning message if woocommerce is not install
	 * @name ced_rnx_plugin_error_notice()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	
	function ced_rnx_plugin_error_notice_when_pro_activated()
 	{ 
 		?>
		<div class="error notice is-dismissible">
			<p><?php esc_html_e( 'WooCommerce RMA | Return-Refund-Exchange is activated so you didnot need to install WooCommerce Refund and Exchange Lite because Main version is contains all the feature of our lite extention .', 'woocommerce-refund-and-exchange-lite' ); ?></p>
		</div>
   		<style>
   		#message{display:none;}
   		</style>
   	<?php 
 	} 
 	if($ced_rnx_activated_main)
 	{
 		add_action( 'admin_init', 'ced_rnx_plugin_deactivate_when_pro_is_activated' );
 	}
 	else
 	{
 		add_action( 'admin_init', 'ced_rnx_plugin_deactivate_lite' );  
 	}
 	
 	if($ced_rnx_activated_main_cc)
 	{
 		add_action( 'admin_init', 'ced_rnx_plugin_deactivate_when_pro_is_activated' );
 	}
 	else
 	{
 		add_action( 'admin_init', 'ced_rnx_plugin_deactivate_lite' ); 
 	}
 	/**
 	 * Call Admin notices
 	 * @name ced_rnx_plugin_deactivate()
 	 * @author makewebbetter<webmaster@makewebbetter.com>
 	 * @link http://www.makewebbetter.com/
 	 */
 	
  	function ced_rnx_plugin_deactivate_lite()
	{
	   deactivate_plugins( plugin_basename( __FILE__ ) );do_action( 'woocommerce_product_options_stock_fields' );
		add_action( 'admin_notices', 'ced_rnx_plugin_error_notice_lite' );
		
	}

	/**
 	 * Call Admin notices
 	 * @name ced_rnx_plugin_deactivate()
 	 * @author makewebbetter<webmaster@makewebbetter.com>
 	 * @link http://www.makewebbetter.com/
 	 */
 	
  	function ced_rnx_plugin_deactivate_when_pro_is_activated()
	{
	   deactivate_plugins( plugin_basename( __FILE__ ) );do_action( 'woocommerce_product_options_stock_fields' );
			add_action( 'admin_notices', 'ced_rnx_plugin_error_notice_when_pro_activated' );
	}

}