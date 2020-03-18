<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Woo Total Sales Frontend
 *
 * Allows user to get WooCommerce Total Sales of specific product.
 *
 * @class   Woo_Total_Sales_Core 
 */


class Woo_Total_Sales_Core extends Woo_Total_Sales{

	/**
	 * Init and hook in the integration.
	 *
	 * @return void
	 */


	public function __construct() {
		$this->id                 = 'Woo_Total_Sales_Core';
		$this->method_title       = __( 'WooCommerce Total Sales Core', 'woo-total-sales' );
		$this->method_description = __( 'WooCommerce Total Sales Core', 'woo-total-sales' );
	}

	
	/**
		 * WooCommerce fallback notice.
		 *
		 * @return string
		 */
		public function awts_woocommerce_missing_notice() {
			echo '<div class="error"><p>' . sprintf( __( 'Woocommerce Total Sales says "There must be active install of %s to take a flight!"', 'woo-total-sales' ), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">' . __( 'WooCommerce', 'woo-total-sales' ) . '</a>' ) . '</p></div>';
			if ( isset( $_GET['activate'] ) )
                 unset( $_GET['activate'] );	
		}

		public function get_top_seller() {
		global $wpdb;

		$query            = array();
		$query['fields']  = "SELECT SUM( order_item_meta.meta_value ) as qty, order_item_meta_2.meta_value as product_id
			FROM {$wpdb->posts} as posts";
		$query['join']    = "INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_id ";
		$query['join']   .= "INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id ";
		$query['join']   .= "INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id ";
		$query['where']   = "WHERE posts.post_type IN ( '" . implode( "','", wc_get_order_types( 'order-count' ) ) . "' ) ";
		$query['where']  .= "AND posts.post_status IN ( 'wc-" . implode( "','wc-", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "' ) ";
		$query['where']  .= "AND order_item_meta.meta_key = '_qty' ";
		$query['where']  .= "AND order_item_meta_2.meta_key = '_product_id' ";
		$query['where']  .= "AND posts.post_date >= '" . date( 'Y-m-01', current_time( 'timestamp' ) ) . "' ";
		$query['where']  .= "AND posts.post_date <= '" . date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) . "' ";
		$query['groupby'] = "GROUP BY product_id";
		$query['orderby'] = "ORDER BY qty DESC";
		$query['limits']  = "LIMIT 1";

		return $wpdb->get_row( implode( ' ',  $query ) );
	}

	

		public function awts_get_total_sales_per_product($product_id ='') { 
			global $wpdb;

			//$post_status = array( 'wc-completed', 'wc-processing', 'wc-on-hold' );
			//$post_status = array('wc-completed', 'wc-processing');
			$post_status = array('wc-completed');		
			 
			$order_items = $wpdb->get_row( $wpdb->prepare(" SELECT SUM( order_item_meta.meta_value ) as _qty, SUM( order_item_meta_3.meta_value ) as _line_total FROM {$wpdb->prefix}woocommerce_order_items as order_items

			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_3 ON order_items.order_item_id = order_item_meta_3.order_item_id
			LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID

			WHERE posts.post_type = 'shop_order'			
			AND posts.post_status IN ( '".implode( "','", apply_filters( 'awts_include_order_statuses', $post_status ) )."' )
			AND order_items.order_item_type = 'line_item'
			AND order_item_meta.meta_key = '_qty'
			AND order_item_meta_2.meta_key = '_product_id'
			AND order_item_meta_2.meta_value = %s
			AND order_item_meta_3.meta_key = '_line_total'

			GROUP BY order_item_meta_2.meta_value

			", $product_id));
			
			return $order_items;

			}

		public function awts_get_total_sales_items(){
					global $wpdb;
					//$post_status = array('wc-completed', 'wc-processing');
					$post_status = array('wc-completed');	

					
					$order_items = apply_filters( 'woocommerce_reports_sales_overview_order_items', absint( $wpdb->get_var( "
					SELECT SUM( order_item_meta.meta_value )
					FROM {$wpdb->prefix}woocommerce_order_items as order_items
					LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
					LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
					
					WHERE 	order_items.order_item_type = 'line_item'
					AND posts.post_status IN ( '".implode( "','", apply_filters( 'awts_include_order_statuses', $post_status ) )."' )
					AND 	order_item_meta.meta_key = '_qty'
				" ) ) );

					return $order_items;

				}


		public function awts_get_total_sales() {

				global $wpdb;

				//$post_status = array('wc-completed', 'wc-processing');
					$post_status = array('wc-completed');		

				$order_totals =  $wpdb->get_row( "
				 
				SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts
				 
				LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id					 
				WHERE meta.meta_key = '_order_total'					 
				AND posts.post_type = 'shop_order'					 
				AND posts.post_status IN ( '".implode( "','", apply_filters( 'awts_include_order_statuses', $post_status ))."' )					 
				" );
				 
				return absint( $order_totals->total_sales);
				 
				}

		public function awts_overview_shipping_total(){
					global $wpdb;
					//$post_status = array('wc-completed', 'wc-processing');
					$post_status = array('wc-completed');		

					$shipping_total = apply_filters( 'woocommerce_reports_sales_overview_shipping_total', $wpdb->get_var( "
					SELECT SUM(meta.meta_value) AS total_sales FROM {$wpdb->posts} AS posts
				 
					LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id						
				 
					WHERE 	meta.meta_key 		= '_order_shipping'
					AND posts.post_type 	= 'shop_order'						
					AND posts.post_status IN ( '".implode( "','", apply_filters( 'awts_include_order_statuses', $post_status ))."' )
				" ) );

				return $shipping_total;	

				}

		public function awts_overview_discount_total(){
					global $wpdb;
					//$post_status = array('wc-completed', 'wc-processing');
					$post_status = array('wc-completed');	

					$discount_total = apply_filters( 'woocommerce_reports_sales_overview_discount_total', $wpdb->get_var( "
						SELECT SUM(meta.meta_value) AS total_sales FROM {$wpdb->posts} AS posts
					 
						LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id							
					 
						WHERE 	meta.meta_key 		IN ('_order_discount', '_cart_discount')
						AND posts.post_type 	= 'shop_order'
						AND posts.post_status IN ( '".implode( "','", apply_filters( 'awts_include_order_statuses', $post_status ))."' )
					" ) );

				return $discount_total;

				}

		/*public function control_awts_include_order_statuses($post_status){
			
			$total_sales_onhold = get_option('woo_total_sales_onhold');

			if( isset($total_sales_onhold) && $total_sales_onhold == 'yes' ){
				$post_status[] = 'wc-on-hold';
			}

			return $post_status;
		}*/

		/**
		 * Locate template.
		 *
		 * Locate the called template.
		 * Search Order:		
		 * /plugins/woo-total-sales/templates/$template_name.
		 * @param 	string 	$template_name			Template to load.
		 * @param 	string	$default_path			Default path to template files.
		 * @return 	string 							Path to the template file.
		 */
		public function awts_locate_template( $template_name, $default_path = '' ) {
			// Set default plugin templates path.
			if ( ! $default_path ) :
				$default_path = AWTS_PLUGIN_DIR . 'blocks/'; // Path to the template folder
			endif;
			// Search template file in theme folder.
			
			$template = $default_path . $template_name;
			
			return apply_filters( 'awts_locate_template', $template, $template_name, $default_path );
		}
				
		/**
		 * Get template.
		 *
		 * Search for the template and include the file.
		 *
		 * @see awts_locate_template()
		 *
		 * @param string 	$template_name			Template to load.
		 * @param array 	$args					Args passed for the template file.
		 * @param string	$default_path			Default path to template files.
		 */
		public function awts_get_template( $template_name, $args = array(), $default_path = '' ) {
			if ( is_array( $args ) && isset( $args ) ) :
				extract( $args );
			endif;
			$template_file = $this->awts_locate_template( $template_name, $default_path );
			if ( ! file_exists( $template_file ) ) :
				_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
				return;
			endif;
			include $template_file;
		}


}

$awts_frontend = new Woo_Total_Sales_Core();