<?php
/**
 * Admin Dashboard
 *
 * @author      WooThemes/AWTS
 * @category    Admin
 * @package     AWTS/Admin
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Woo_Total_Sales_GO_WC_Reports', false ) ) :

/**
 * Woo_Total_Sales_GO_WC_Reports Class.
 */
class Woo_Total_Sales_GO_WC_Reports {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		// Only hook in admin parts if the user has admin access
		if ( current_user_can( 'view_woocommerce_reports' ) || current_user_can( 'manage_woocommerce' ) || current_user_can( 'publish_shop_orders' ) ) {
			//add_action( 'wp_dashboard_setup', array( $this, 'init' ) );
		}
	}

	/**
	 * Init dashboard widgets.
	 */
	public function init() {
		if ( current_user_can( 'publish_shop_orders' ) && post_type_supports( 'product', 'comments' ) ) {
			wp_add_dashboard_widget( 'woocommerce_dashboard_recent_reviews', __( 'WooCommerce recent reviews', 'woocommerce' ), array( $this, 'recent_reviews' ) );
		}
		wp_add_dashboard_widget( 'woocommerce_dashboard_status', __( 'WooCommerce status', 'woocommerce' ), array( $this, 'status_widget' ) );
	}

	/**
	 * Get top seller from DB.
	 * @return object
	 */
	private function get_top_seller() {
		global $wpdb;

		$query            = array();
		$query['fields']  = "SELECT SUM( order_item_meta.meta_value ) as qty, order_item_meta_2.meta_value as product_id
			FROM {$wpdb->posts} as posts";
		$query['join']    = "INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_id ";
		$query['join']   .= "INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id ";
		$query['join']   .= "INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id ";
		$query['where']   = "WHERE posts.post_type IN ( '" . implode( "','", wc_get_order_types( 'order-count' ) ) . "' ) ";
		$query['where']  .= "AND posts.post_status IN ( 'wc-" . implode( "','wc-", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed' ) ) ) . "' ) ";

		/*$query['where']  .= "AND posts.post_status IN ( 'wc-" . implode( "','wc-", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "' ) ";*/
		$query['where']  .= "AND order_item_meta.meta_key = '_qty' ";
		$query['where']  .= "AND order_item_meta_2.meta_key = '_product_id' ";
		$query['where']  .= "AND posts.post_date >= '" . date( 'Y-m-01', current_time( 'timestamp' ) ) . "' ";
		$query['where']  .= "AND posts.post_date <= '" . date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) . "' ";
		$query['groupby'] = "GROUP BY product_id";
		$query['orderby'] = "ORDER BY qty DESC";
		$query['limits']  = "LIMIT 1";

		return $wpdb->get_row( implode( ' ', apply_filters( 'woocommerce_dashboard_status_widget_top_seller_query', $query ) ) );
	}

	/**
	 * Get sales report data.
	 * @return object
	 */
	private function get_sales_report_data() {
		global $woocommerce;
		include_once( $woocommerce->plugin_path() . '/includes/admin/reports/class-wc-report-sales-by-date.php' );

		$sales_by_date                 = new WC_Report_Sales_By_Date();
		$sales_by_date->start_date     = strtotime( date( 'Y-m-01', current_time( 'timestamp' ) ) );
		$sales_by_date->end_date       = current_time( 'timestamp' );
		$sales_by_date->chart_groupby  = 'day';
		$sales_by_date->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';

		return $sales_by_date->get_report_data();
	}

	/**
	 * Show status widget.
	 */
	public function status_widget() {
		global $woocommerce;
		include_once( $woocommerce->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php' );

		$reports = new WC_Admin_Report();

		echo '<ul class="wc_status_list awts_wc_status_list">';

		if ( current_user_can( 'view_woocommerce_reports' ) && ( $report_data = $this->get_sales_report_data() ) ) {
			?>
			<!-- <li>
			<ul class="awts-monthly-overview"> -->
			<li class="sales-this-month awts-sales-this-month">
				<?php 							
							printf(
							__( '<h3>' .'%s sales summary'. '</h3>', 'woocommerce' ),
							'<strong>' . date( 'd D - M, Y', current_time( 'timestamp' ) ) . '</strong>'
							);
						?>	
				<a href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=orders&range=month' ); ?>" class="awts-monthly-sales-summary">
					<?php echo $reports->sales_sparkline( '', max( 7, date( 'd', current_time( 'timestamp' ) ) ) ); ?>
					<?php
						/* translators: %s: net sales */
						printf(
							__( '%s net sales this month', 'woocommerce' ),
							'<strong>' . wc_price( $report_data->net_sales ) . '</strong>'
							);
					?>

				
				<ul class="awts-additional-data">
					
					<li>
						<?php 							
							printf(
							_n( '%s order placed', '%s orders placed', '<strong>' . $report_data->total_orders . '</strong>', 'woocommerce' ),
							'<strong>' . $report_data->total_orders . '</strong>'
							);
						?>						
					</li>
					<li>
						<?php 							
							printf(
							_n( '%s item ordered', '%s items ordered', '<strong>' . $report_data->total_items . '</strong>', 'woocommerce' ),
							'<strong>' . $report_data->total_items . '</strong>'
							);
						?>						
					</li>

					<li>
						<?php 							
							printf(
							_n( '%s order refunded', '%s orders refunded', '<strong>' . $report_data->total_refunds . '</strong>', 'woocommerce' ),
							'<strong>' . $report_data->total_refunds . '</strong>'
							);
						?>						
					</li>

					<li>
						<?php 							
							printf(
							__( '%s total tax', 'woocommerce' ),
							'<strong>' . wc_price( $report_data->total_tax ) . '</strong>'
							);
						?>						
					</li>

					<li>
						<?php 							
							printf(
							__( '%s total shipping', 'woocommerce' ),
							'<strong>' . wc_price( $report_data->total_shipping ) . '</strong>'
							);
						?>						
					</li>

					<li>
						<?php 							
							printf(
							__( '%s total shipping tax', 'woocommerce' ),
							'<strong>' . wc_price( $report_data->total_shipping_tax ) . '</strong>'
							);
						?>						
					</li>

					<li>
						<?php 							
							printf(
							__( '%s discounts applied', 'woocommerce' ),
							'<strong>' . wc_price( $report_data->total_coupons ) . '</strong>'
							);
						?>						
					</li>

					<li>
						<?php 							
							printf(
							__( '%s total sales', 'woocommerce' ),
							'<strong>' . wc_price( $report_data->total_sales ) . '</strong>'
							);
						?>						
					</li>
				</ul>	
				
			</a>
			</li>
			<?php
		}

			?>
			<li class="best-seller-this-month awts-best-seller-this-month">
			<?php 							
							printf(
							__( '<h3>Sales of the month (%s)</h3>', 'woocommerce' ),
							'<strong>' . date( 'M', current_time( 'timestamp' ) ) . '</strong>'
							);
						?>

				<?php 
				if ( current_user_can( 'view_woocommerce_reports' ) && ( $top_seller = $this->get_top_seller() ) && $top_seller->qty ) { ?>			
				<span class="awts-monthly-sales-summary">		
				<a href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_product&range=month&product_ids=' . $top_seller->product_id ); ?>" >
					<?php echo $reports->sales_sparkline( $top_seller->product_id, max( 7, date( 'd', current_time( 'timestamp' ) ) ), 'count' ); ?>
					<?php
						/* translators: 1: top seller product title 2: top seller quantity */
						printf(
							__( '%1$s top seller this month (sold %2$d)', 'woocommerce' ),
							'<strong>' . get_the_title( $top_seller->product_id ) . '</strong>',
							$top_seller->qty
						);
					?>
				</a>
				<?php $_product = wc_get_product( $top_seller->product_id ); ?>	
				
					<ul class="awts-additional-data">
						<li class="awts-product-thumbnail">
							<?php
								printf(
							__( '%s', 'woocommerce' ),  '<a href="'.get_edit_post_link($top_seller->product_id).'">'.$_product->get_image().'</a>'
							);
							?>
						</li>				
						<li class="awts-product-type awts-capitalize">
							<?php								
								printf(
							__( '%s', 'woocommerce' ),  $_product->get_type()
							);
								$downloadable = $_product->is_downloadable() ? ', Downloadable' : '';
								
								printf(
							__( '%s', 'woocommerce' ),  $downloadable
							);

								$virtual = $_product->is_virtual() ? ', Virtual' : '';
								printf(
							__( '%s', 'woocommerce' ),  $virtual
							);
							?>
						</li>
						<li class="awts-product-stock">
							<?php
							$availability = $_product->get_availability();
							if ( !$_product->is_in_stock() ) {
								$stock_html = __( 'Out of stock', 'woocommerce' );
							} elseif  ( $_product->managing_stock() && $_product->is_on_backorder( 1 ) ) {
								$stock_html = $_product->backorders_require_notification() ? __( 'Available on backorder, but notify customer', 'woocommerce' ) : 'Available on backorder';
							} else {
								$stock_html =  __( 'In stock', 'woocommerce' );								
							}

							if ( $_product->managing_stock() ) {
								$stock_html .= ' (' . wc_stock_amount( $_product->get_stock_quantity() ) . ')';
							}

							$availability_texts = apply_filters( 'woocommerce_admin_stock_html', $stock_html, $_product );

							//print_pre($availability);
								printf(
							__( '%s', 'woocommerce' ),
							'<strong>' . $availability_texts . '</strong>'
							);
							?>
						</li>
						<li class="awts-product-details">
							<?php

								if( method_exists( $_product, 'get_short_description' ) ){

										if(!empty($_product->get_short_description())){
											$description = $_product->get_short_description();
										} else {
											$description = $_product->get_description();
										}

										printf(
											__( '%s', 'woocommerce' ),  awts_product_summary( $description, 175)
											);
										
									}
							?>

						</li>
						<li class="awts-product-edit-link">
							
							<?php
								printf(
							__( '%s', 'woocommerce' ),  '<a class="awts-additional-data-wrap awts-link" href="'.get_edit_post_link($top_seller->product_id).'">Go to Product</a>'
							);
							?>
						</li>

						
					</ul>
					
					</span>
			<?php	} else { ?>
					<span class="awts-monthly-sales-summary awts-no-sales-summary">
						
						<ul class="awts-additional-data">
							<li class="awts-product-thumbnail">
								<?php
									printf(
								__( '<a href="#"><img src="%s" class="awts-empty-clipboard" alt="Currently, there is no sale for this month." /></a>', 'woocommerce' ),  AWTS_PLUGIN_URL.'/assets/img/empty-clipboard.png'
								);
								?>
							</li>
							<li class="awts-product-details">								
								<?php 						
									
									printf(
									__( '<a href="#"><strong>%s</strong>Currently, there is no sale for this month</a>', 'woocommerce' ),
									 date( 'd D - M, Y', current_time( 'timestamp' ) )
									);
								?>
								
							</li>
						</ul>
					</span>
			<?php } ?>
			</li>

			<!-- </ul>
			</li> -->
		
		<li class="awts-full-width">
		<?php 							
				printf(
				__( '<h3>' .'%s'. '</h3>', 'woocommerce' ),
				'Overall order summary'
				);
			?>	
			<ul class="awts-status-widget-order-rows">
				<?php $this->status_widget_order_rows(); ?>
			</ul>
			<?php 							
				printf(
				__( '<h3>' .'%s'. '</h3>', 'woocommerce' ),
				'Stock summary'
				);
			?>	
			<ul class="awts-status-widget-stock-rows">
				<?php $this->status_widget_stock_rows(); ?>
			</ul>
		</li>

		<?php

		do_action( 'woocommerce_after_dashboard_status_widget', $reports );
		echo '</ul>';
	}

	/**
	 * Show order data is status widget.
	 */
	private function status_widget_order_rows() {
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			return;
		}
		$on_hold_count    = 0;
		$completed_count = 0;
		$processing_count = 0;
		$cancelled_count = 0;

		foreach ( wc_get_order_types( 'order-count' ) as $type ) {
			$counts           = (array) wp_count_posts( $type );
			$completed_count    += isset( $counts['wc-completed'] ) ? $counts['wc-completed'] : 0;
			$on_hold_count    += isset( $counts['wc-on-hold'] ) ? $counts['wc-on-hold'] : 0;
			$processing_count += isset( $counts['wc-processing'] ) ? $counts['wc-processing'] : 0;
			$cancelled_count += isset( $counts['wc-cancelled'] ) ? $counts['wc-cancelled'] : 0;
		}
		
		?>
		<li class="awts-completed-orders completed-orders">

			<a href="<?php echo admin_url( 'edit.php?post_status=wc-completed&post_type=shop_order' ); ?>">
				<?php
					/* translators: %s: order count */
					printf(
						_n( '<strong>%s order</strong> completed', '<strong>%s orders</strong> completed', $completed_count, 'woocommerce' ),
						$completed_count
					);
				?>
			</a>
		</li>

		<li class="processing-orders">

			<a href="<?php echo admin_url( 'edit.php?post_status=wc-processing&post_type=shop_order' ); ?>">
				<?php
					/* translators: %s: order count */
					printf(
						_n( '<strong>%s order</strong> awaiting processing', '<strong>%s orders</strong> awaiting processing', $processing_count, 'woocommerce' ),
						$processing_count
					);
				?>
			</a>
		</li>
		<li class="on-hold-orders">
			<a href="<?php echo admin_url( 'edit.php?post_status=wc-on-hold&post_type=shop_order' ); ?>">
				<?php
					/* translators: %s: order count */
					printf(
						_n( '<strong>%s order</strong> on-hold', '<strong>%s orders</strong> on-hold', $on_hold_count, 'woocommerce' ),
						$on_hold_count
					);
				?>
			</a>
		</li>

		<li class="cancelled-orders">
			<a href="<?php echo admin_url( 'edit.php?post_status=wc-cancelled&post_type=shop_order' ); ?>">
				<?php
					/* translators: %s: order count */
					printf(
						_n( '<strong>%s order</strong> cancelled', '<strong>%s orders</strong> cancelled', $cancelled_count, 'woocommerce' ),
						$cancelled_count
					);
				?>
			</a>
		</li>
		<?php
	}

	/**
	 * Show stock data is status widget.
	 */
	private function status_widget_stock_rows() {
		global $wpdb;

		// Get products using a query - this is too advanced for get_posts :(
		$stock          = absint( max( get_option( 'woocommerce_notify_low_stock_amount' ), 1 ) );
		$nostock        = absint( max( get_option( 'woocommerce_notify_no_stock_amount' ), 0 ) );
		$transient_name = 'wc_low_stock_count';
		$transient_lis_products = 'wc_low_stock_products';
		
		

		if ( false === ( $lowinstock_count = get_transient( $transient_name ) ) || false === ( $lowinstock_products = get_transient( $transient_lis_products ) )  ) {
			$query_from = apply_filters( 'woocommerce_report_low_in_stock_query_from', "FROM {$wpdb->posts} as posts
				INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
				INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
				WHERE 1=1
				AND posts.post_type IN ( 'product', 'product_variation' )
				AND posts.post_status = 'publish'
				AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
				AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$stock}'
				AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) > '{$nostock}'
			" );
			
			$lowinstock_count = absint( $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" ) );
			
			$lowinstock_products = $wpdb->get_results( "SELECT posts.ID  {$query_from};" );
			
			set_transient( $transient_name, $lowinstock_count, DAY_IN_SECONDS * 30 );
			set_transient( $transient_lis_products, $lowinstock_products, DAY_IN_SECONDS * 30 );
		}

		$transient_name = 'wc_outofstock_count';
		$transient_oos_products = 'wc_outofstock_products';

		//$outofstock_products = array();

		if ( false === ( $outofstock_count = get_transient( $transient_name ) ) || false === ( $outofstock_products = get_transient( $transient_oos_products ) )) {
			$query_from = apply_filters( 'woocommerce_report_out_of_stock_query_from', "FROM {$wpdb->posts} as posts
				INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
				INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
				WHERE 1=1
				AND posts.post_type IN ( 'product', 'product_variation' )
				AND posts.post_status = 'publish'
				AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
				AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$nostock}'
			" );
			$outofstock_count = absint( $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" ) );
			
			$outofstock_products = $wpdb->get_results( "SELECT posts.ID  {$query_from};" );

			set_transient( $transient_name, $outofstock_count, DAY_IN_SECONDS * 30 );

			set_transient( $transient_oos_products, $outofstock_products, DAY_IN_SECONDS * 30 );
		}
		?>
		<li class="low-in-stock">			
		
			<a href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=stock&report=low_in_stock' ); ?>">
				<?php
					/* translators: %s: order count */
					printf(
						_n( '<strong>%s product</strong> low in stock', '<strong>%s products</strong> low in stock', $lowinstock_count, 'woocommerce' ),
						$lowinstock_count
					);
				?>
			</a>
			<ul class="awts-additional-data">
				<?php if( isset($lowinstock_products) && !empty($lowinstock_products ) ){ 
						foreach($lowinstock_products as $lis_product){
							$_product = wc_get_product( $lis_product->ID );														
					?>
							<li>	
							<a href="<?php echo get_edit_post_link($lis_product->ID); ?>">
								<?php
									printf(
										__( '<span class="awts-shop-thumbnail">%s</span>', 'woocommerce' ),  $_product->get_image()
										);
								?>
								<?php
									/* translators: %s: order count */								

									printf(
										__( '<span class="awts-stock-detail"><span class="awts-poduct-title"><strong>%s</strong></span>', 'woocommerce' ),
										$_product->get_title()
									);

									printf(
										_n( '<span class="awts-stock-status">%s item available</span>', '<span class="awts-stock-status">%s items available</span>', $_product->get_stock_quantity(), 'woocommerce' ), $_product->get_stock_quantity()
									);


									printf(
											__( '<span class="awts-stock-status">Stock Status: %s</span>', 'woocommerce' ),  $_product->get_stock_status()
											);
									
									$backorders = $_product->get_backorders();
									if($backorders == 'no'){

										$status_class = 'awts-red';

									} else {
										
										$status_class = '';
									}

									printf(
											__( '<span class="awts-allow-backorders '. $status_class. '">Allow Backorders: %s</span></span>', 'woocommerce' ),  $_product->get_backorders()
											);
								?>
							</a>
							</li>
				<?php } 
					}
				?>
			</ul>
		</li>
		<li class="out-of-stock">
			<a href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=stock&report=out_of_stock' ); ?>">
				<?php
					/* translators: %s: order count */
					printf(
						_n( '<strong>%s product</strong> out of stock', '<strong>%s products</strong> out of stock', $outofstock_count, 'woocommerce' ),
						$outofstock_count
					);
				?>
			</a>
			<ul class="awts-additional-data">
				<?php if(isset($outofstock_products) && !empty($outofstock_products) ){ 
					
						foreach($outofstock_products as $oos_product){
							$_product = wc_get_product( $oos_product->ID );
												
					?>
							<li>	
							<a href="<?php echo get_edit_post_link($oos_product->ID); ?>">
								<?php
									printf(
										__( '<span class="awts-shop-thumbnail">%s</span>', 'woocommerce' ),  $_product->get_image()
										);
								?>
								<?php
									/* translators: %s: order count */
									$product_status = $_product->get_status();
									$stock_status = $_product->get_stock_status();
									$backorders = $_product->get_backorders();

									printf(
										__( '<span class="awts-stock-detail"><span class="awts-poduct-title"><strong>%s</strong></span>', 'woocommerce' ),
										$_product->get_title()
									);

									printf(
											__( '<span class="awts-stock-status">Product Status: <span class="awts-product-status">%s</span></span>', 'woocommerce' ),  $product_status
											);

									if($stock_status == 'outofstock'){

										$status_class = 'awts-red';

									} else {
										
										$status_class = '';
									}

									printf(
											__( '<span class="awts-stock-status '.$status_class.'">Stock Status: %s</span>', 'woocommerce' ),  $stock_status
											);

									if($backorders == 'no'){

										$status_class = 'awts-red';

									} else {
										
										$status_class = '';
									}

									printf(
											__( '<span class="awts-allow-backorders '.$status_class.'">Allow Backorders: %s</span></span>', 'woocommerce' ),  $backorders
											);
								?>
							</a>
							</li>
				<?php } 
					}
				?>
			</ul>
		</li>
		<?php
	}

	
}

endif;

return new Woo_Total_Sales_GO_WC_Reports();
