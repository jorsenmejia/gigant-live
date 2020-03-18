<?php
/*
 * Plugin Name:       Woo Total Sales 
 * Plugin URI:        https://github.com/shshanker/woo-total-sales
 * Description:       This plugin facilitates extended overview of WooCommerce dashboard sales status. Additionally, it displays total sales of specific product on shop-archives and respective single product page (Backend and Frontend). 
 * Version:           3.1.3
 * Author:            Sh Shanker
 * Author URI:        https://github.com/shshanker
 * Text Domain:       woo-total-sales
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );  // prevent direct access

if ( ! class_exists( 'Woo_Total_Sales' ) ) :
	
	class Woo_Total_Sales {


		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		const VERSION = '3.1.3';

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;


		/**
		 * Initialize the plugin.
		 */
		public function __construct(){
				
				/**
				 * Check if WooCommerce is active
				 **/
				if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

				defined( 'AWTS_BASE_FILE' ) or define( 'AWTS_BASE_FILE', __FILE__ );			
			   	defined( 'AWTS_BASE_DIR' ) or define( 'AWTS_BASE_DIR', dirname( AWTS_BASE_FILE ) );		
			   	defined( 'AWTS_PLUGIN_URL' ) or define( 'AWTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			   	defined( 'AWTS_PLUGIN_DIR' ) or define( 'AWTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );	

			   		include_once 'includes/awts-core.php';				
			   		include_once 'includes/awts-frontend.php';
			   		include_once 'includes/awts-backend.php';				
			   		include_once 'includes/awts-backend-settings.php';				
			   		include_once 'includes/awts-backend-metabox.php';				
			   		include_once 'includes/awts-backend-general-overview.php';			
			   		include_once 'includes/awts-backend-general-overview-wc-reports.php';			
			   		include_once 'includes/awts-functions.php';				
					
					//add_filter( 'awts_include_order_statuses', array( $this, 'control_awts_include_order_statuses' ), 10, 1 );
					
				} else {
					
					add_action( 'admin_init', array( $this, 'awts_plugin_deactivate') );
					//add_action( 'admin_notices', array( $this, 'awts_woocommerce_missing_notice' ) );

				}

			} // end of contructor




		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		

		/**
		 * WooCommerce fallback notice.
		 *
		 * @return string
		 */
		public function awts_plugin_deactivate() {

			deactivate_plugins( plugin_basename( __FILE__ ) );

		}
		

	}// end of the class

add_action( 'plugins_loaded', array( 'Woo_Total_Sales', 'get_instance' ), 0 );

endif;