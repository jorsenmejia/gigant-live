<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Woo Total Sales Backend
 *
 * Allows admin to set WooCommerce Total Sales of specific product.
 *
 * @class   Woo_Total_Sales_GO 
 */


class Woo_Total_Sales_GO extends Woo_Total_Sales_backend{

	/**
	 * Init and hook in the integration.
	 *
	 * @return void
	 */

	private $options_general;


	public function __construct() {
		$this->id                 = 'Woo_Total_Sales_GO';
		$this->method_title       = __( 'WooCommerce Total Sales General Overview', 'woo-total-sales' );
		$this->method_description = __( 'WooCommerce Total Sales General Overview', 'woo-total-sales' );
		
		add_action( 'wp_ajax_nopriv_get_orders_archive', array( $this, 'get_orders_archive') );
		add_action( 'wp_ajax_get_orders_archive', array( $this, 'get_orders_archive') );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_custom_wp_admin_style') );
		
		
	}
	
	public function load_custom_wp_admin_style(){
	
    wp_enqueue_script( 'awts_backend_scripts', plugins_url( 'woo-total-sales/assets/js/awts-backend-scripts.js' ) );
    wp_localize_script( 'awts_backend_scripts', 'ajax_var', array( 'url' => admin_url( 'admin-ajax.php' ), ) );


// woocommerce style
    wp_register_style( 'woocommerce_admin_dashboard_styles', WC()->plugin_url() . '/assets/css/dashboard.css', array(), WC_VERSION );
    wp_enqueue_style( 'woocommerce_admin_dashboard_styles' );

// woocommerce scripts
    $suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
wp_register_script( 'wc-reports', WC()->plugin_url() . '/assets/js/admin/reports' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker' ), WC_VERSION );

            wp_enqueue_script( 'wc-reports' );
            wp_register_script( 'flot', WC()->plugin_url() . '/assets/js/jquery-flot/jquery.flot' . $suffix . '.js', array( 'jquery' ), WC_VERSION );
            wp_enqueue_script( 'flot' );
           /* wp_enqueue_script( 'flot-resize' );
            wp_enqueue_script( 'flot-time' );
            wp_enqueue_script( 'flot-pie' );
            wp_enqueue_script( 'flot-stack' );  */ 


       wp_register_style( 'awts-backend-style', plugins_url( 'woo-total-sales/assets/css/awts-backend-style.css' ), array(), WC_VERSION );
        wp_enqueue_style( 'awts-backend-style' );      
	}

public function awts_montly_sales_dashboard_widget() {
    $this->monthly_archive_dropdown();
    $start_date = strtotime( date( 'Y-m', current_time( 'timestamp' ) ) . '-01 midnight' );
    $sold_products = $this->get_orders_list( $start_date );
    // List Sales Items
    if ( !empty( $sold_products ) ) {
   	 echo $html = $this->get_output( $sold_products );
    } else {
   	 printf(  __( '<p class="awts-no-sales-overview"><a href="#"><strong>(%s)</strong> Currently, there is no sale for this month</a></p>', 'woocommerce' ), date( 'd D - M, Y', current_time( 'timestamp' ) ) );
    }
}
/**
* Monthly orders archive dropdown
*
*/
public function monthly_archive_dropdown() {
    global $wp_locale, $wpdb;
    $extra_checks = "AND post_status ='wc-completed' ";
    $filter_post_status = filter_input( INPUT_GET, "post_status" );
    if ( !isset( $filter_post_status ) || 'trash' !== $filter_post_status ) {
     $extra_checks .= " AND post_status != 'trash'";
    } elseif ( isset( $filter_post_status ) ) {
     $extra_checks = $wpdb->prepare( ' AND post_status = %s', $filter_post_status );
    }
    $months = $wpdb->get_results( $wpdb->prepare( " SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month FROM $wpdb->posts WHERE post_type = %s $extra_checks ORDER BY post_date DESC ", 'shop_order' ) );
    $month_count = count( $months );
    if ( $month_count < 1 ) {
     echo 'There is no sale yet!';
    };
    ?>
    <div class="awts-filter-by-month-wrap">

    <label for="filter-by-date" class="screen-reader-text"><?php _e( 'Filter by date' ); ?></label>
    <select name="m" id="awts-filter-by-date">
      <!-- <option value='<?php ?>'><?php _e('Select a month', 'woo-total-sales'); ?></option>   -->
     <?php

     printf( "<option value='%s-1'>%s</option>\n", date('Y-m'), __('Select a month', 'woo-total-sales')
         );

     foreach ( $months as $arc_row ) {
         if ( 0 == $arc_row->year )
             continue;
         $month = zeroise( $arc_row->month, 2 );
         $year = $arc_row->year;
         //printf( "<option %s value='%s'>%s</option>\n", selected( $m, $year . $month, false ), esc_attr( $arc_row->year . '-' . $month . '-1' ),
         printf( "<option value='%s'>%s</option>\n", esc_attr( $arc_row->year . '-' . $month . '-1' ),
                 /* translators: 1: month name, 2: 4-digit year */ sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
         ); } ?>
    </select>
    </div>
<?php }




public function get_orders_list( $start_date ) {
    global $woocommerce, $wpdb, $product;
    include_once($woocommerce->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php');
    // WooCommerce Admin Report
    
  
    $wc_report = new WC_Admin_Report();
   
    $end_date = strtotime( '+1month', $start_date ) - 86400;
    $wc_report->start_date = $start_date;
    $wc_report->end_date = $end_date;
   // Avoid max join size error
    $wpdb->query( 'SET SQL_BIG_SELECTS=1' );
 	$sold_products = $wc_report->get_order_report_data( array(
     'data' => array(
          'ID' => array(
            'type'     => 'post_data',
            'function' => '',
            'name'     => 'order_id'
        ),               
         '_product_id' => array(
             'type' => 'order_item_meta',
             'order_item_type' => 'line_item',
             'function' => '',
             'name' => 'product_id'
         ),
         '_qty' => array(
             'type' => 'order_item_meta',
             'order_item_type' => 'line_item',
             'function' => 'SUM',
             'name' => 'quantity'
         ),

         '_line_tax' => array(
             'type' => 'order_item_meta',
             'order_item_type' => 'line_item',
             'function' => 'SUM',
             'name' => 'tax'
         ),
         '_line_subtotal' => array(
             'type' => 'order_item_meta',
             'order_item_type' => 'line_item',
             'function' => 'SUM',
             'name' => 'gross'
         ),
         '_line_total' => array(
             'type' => 'order_item_meta',
             'order_item_type' => 'line_item',
             'function' => 'SUM',
             'name' => 'gross_after_discount'
         )
     ),
     'query_type' => 'get_results',
     'order_by' => 'quantity DESC',
     'group_by' => 'product_id',
     'where_meta' => '',
     'order_types' => wc_get_order_types( 'order_count' ),
     'limit' => '',
     'filter_range' => 1,
     'order_status' => array( 'completed' ),
         ) );
    return $sold_products;
}
public function get_output( $sold_products ) {
	$html ='';
    $html .= '<table class="awts-products-list widefat striped">
        <thead><tr>
            <th style="" class="manage-column column-order-id" scope="col"><strong>' . __( 'Order ID', 'woocommerce-monthly-sales-summary' ) . '</strong></th>
            <th style="" class="manage-column column-product-name" scope="col"><strong>' . __( 'Product', 'woocommerce-monthly-sales-summary' ) . '</strong></th>
            <th style="" class="manage-column column-order-placed" scope="col"><strong>' . __( 'Order Placed', 'woocommerce-monthly-sales-summary' ) . '</strong></th>
            <th style="" class="manage-column column-discount" scope="col"><strong>' . __( 'Discount', 'woocommerce-monthly-sales-summary' ) . '</strong></th>

            <th style="" class="manage-column column-shipping-cost" scope="col"><strong>' . __( 'Shpping Cost', 'woocommerce-monthly-sales-summary' ) . '</strong></th>

            <th style="" class="manage-column column-shipping-tax" scope="col"><strong>' . __( 'Shipping Tax', 'woocommerce-monthly-sales-summary' ) . '</strong></th>
            <th style="" class="manage-column column-tax" scope="col"><strong>' . __( 'Tax', 'woocommerce-monthly-sales-summary' ) . '</strong></th>
            <th style="" class="manage-column column-sales" scope="col"><strong>' . __( 'Sales', 'woocommerce-monthly-sales-summary' ) . '</strong></th>
        </tr></thead>
    <tbody id="awts-wcds-products-table-body">';
    foreach ( $sold_products as $product ) {
    	   //print_pre($product);
            
            $product_edit_link = esc_url( get_edit_post_link( intval( $product->product_id ) ) );
            $product_edit_link = ($product_edit_link) ? $product_edit_link : "#";  

            $product_title = html_entity_decode( get_the_title( $product->product_id ) );  
            $product_title = ($product_title) ? $product_title : "Product not available";

            $order_id = intval( $product->order_id );    

            $order = new WC_Order($order_id);
            $shipping_total = $order->get_total_shipping();
            $shipping_method = $order->get_shipping_method();
            $shipping_total = !empty($shipping_total) ? wc_price($shipping_total) . "<span class='awts-shipping-methods'>(".$shipping_method.")</span>" : "No Shipping";

            $shipping_tax   = $order->get_shipping_tax();
            $shipping_tax   = !empty($shipping_tax) ? wc_price($shipping_tax) : "No Shipping Tax";


            $order_edit_link = esc_url( get_edit_post_link( intval( $order_id ) ) );

            $order_qty = intval( $product->quantity );

        	$price = $product->gross;
            $payment_method = get_post_meta( $order_id, '_payment_method_title', true );
        	$product_price = wc_price( $price ) . "<span class='awts-payment-methods'> (".$payment_method.")</span>";
            //print_pre($payment_method);


            $discounted_price = $product->gross_after_discount;
            $discount = wc_price($price - $discounted_price);

            $applied_tax = ($product->tax)? wc_price( $product->tax ) : wc_price(0);



             $html .= '<tr>';
             $html .='<td class="seller-this-month column-order-id" ><a  href="' . $order_edit_link . '"><strong>' . $order_id . '</strong></a></td>';
             $html .='<td><a  class="awts-product-link column-product-name" href="' . $product_edit_link . '"><strong>' . $product_title . '</strong></a></td>';
             $html .='<td class="column-order-placed">' . sprintf(_n( '%s item', '%s items', $order_qty, 'text-domain' ), $order_qty)  . '</td>';
             $html .='<td class="column-discount">' . $discount . '</td>';
             $html .='<td class="column-shipping-cost">' . $shipping_total . '</td>';
             $html .='<td class="column-shipping-tax">' . $shipping_tax . '</td>';
             $html .='<td class="column-tax">' . $applied_tax . '</td>';
             $html .='<td class="column-sales">' . $product_price . '</td>';
             $html .='</tr>';
            }
            $html .='</tbody>';
            $html .='</table>';
            return $html;
}


public function get_orders_archive() {
    $start_date = strtotime( $_POST['current_date'] );
    $sold_products = $this->get_orders_list( $start_date );
    // List Sales Items
    if ( !empty( $sold_products ) ) {
     $html = $this->get_output( $sold_products );
     $json['html'] = $html;
     $json['type'] = "success";
    } else {
     $json['html'] = sprintf(  __( '<p class="awts-no-sales-overview"><a href="#"><strong>(%s)</strong> Currently, there is no sale for this month</a></p>', 'woocommerce' ), date( 'd D - M, Y', current_time( 'timestamp' ) ) );
     $json['type'] = "error";
    }
    wp_send_json( $json );
}


	
}

if( is_admin() )
$awts_backend = new Woo_Total_Sales_GO();