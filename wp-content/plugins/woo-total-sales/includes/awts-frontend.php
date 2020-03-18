<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Woo Total Sales Frontend
 *
 * Allows user to get WooCommerce Total Sales of specific product.
 *
 * @class   Woo_Total_Sales_Frontend 
 */


class Woo_Total_Sales_Frontend extends Woo_Total_Sales_Core{

	/**
	 * Init and hook in the integration.
	 *
	 * @return void
	 */


	public function __construct() {
		$this->id                 = 'Woo_Total_Sales_Frontend';
		$this->method_title       = __( 'WooCommerce Total Sales Frontend', 'woo-total-sales' );
		$this->method_description = __( 'WooCommerce Total Sales Frontend', 'woo-total-sales' );	
		
		// Scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'awts_scripts' ));
		add_action( 'wp_footer', array( $this, 'awts_footer_style' ));
		
		// Filters
		// Add saved price note
		add_filter( 'woocommerce_get_price_html', array( $this, 'awts_display_total_sales') , 101, 2 );
		
		add_shortcode( 'awts-total-sales', array( $this, 'awts_shortcode_total_sales') );
		
	}

	
	/**
	 * Loading scripts.
	 *
	 * @return void
	 */

	public function awts_scripts(){
		
		// loading plugin custom css file
		wp_register_style( 'awts-style', plugins_url( 'woo-total-sales/assets/css/awts-style.css' ) );
		wp_enqueue_style( 'awts-style' );
		
	
	} // end of awts_scripts



	

	/**
	 * Loading  functionality to user to get WooCommerce Total Sales information of the specific product.
	 *
	 * @return void
	 */


	public function awts_display_total_sales( $price='', $product='' ){  

		//woocommerce 3.0 compatible
	    if(method_exists($product, 'get_id')){
	    	$product_id = $product->get_id();	    	
	    }else{
	    	$product_id = $product->id;
	    }

	    $awts_visibility = get_post_meta($product_id, 'awts-visibility', true);
	   
	   	//check if visibile only on backend
		if( isset($awts_visibility) && $awts_visibility == 'awts-hide' ){
				if(!is_admin())
				return $price;
			} 
	   


		$only_backend = get_option('woo_total_sales_single_product_only_be'); 
		$only_single_product = get_option('woo_total_sales_single_product_only_fe'); 

		//check if visibile only on backend
		if( isset($only_backend) && $only_backend == 'yes' ){
				if(!is_admin())
				return $price;
			}

		//check if visibile only on single product page
		if( isset($only_single_product) && $only_single_product == 'yes' ){
				if(!is_product() && !is_admin())
				return $price;
			}	

		//From admin setting
		$singular 	= get_option('woo_total_sales_singular');
		$plural 	= get_option('woo_total_sales_plural');
		

		$awts_custom_count = get_post_meta( $product_id, 'awts-custom-count', true );

		if(!empty($awts_custom_count)){		
			$items_sold = $awts_custom_count;
		} else {
			$items_sold = $this->awts_get_total_sales_per_product( $product_id );			
			if(isset($items_sold)){
				$items_sold = $items_sold->_qty;			
			}else{
				$items_sold = 0;			

			}
		}


		$items_sold = (isset($items_sold) ? absint($items_sold) : 0);

	    $price_texts  = ''; 
	    $price_texts  .= $price; 

	  	if( $items_sold != 0 ){

		    $price_texts .= '<div class="items-sold" ><span class="items-sold-texts" >'; 
		    $price_texts .= sprintf( 

		    	esc_html( 
		    		_n( 
				    		(!empty($singular)) ? $singular : '%d item sold', 
				    		(!empty($plural)) ? $plural : '%d items sold', 
				    		$items_sold, 
				    		'woo-total-sales'  
				    		) 
			    		),

		    		$items_sold );
		    $price_texts .= '</span></div>';

		}
	    
	    return $price_texts;
	}

	/**
	 * Loading shortcode functionality to user to get WooCommerce Total Sales information of the specific product.
	 *
	 * @return void
	 */


	public function awts_shortcode_total_sales( $atts ){ 

		$_awts = shortcode_atts( array(
	        'product_id' => '0',	       
	        'include_setting' => 'true',	       
	    ), $atts ); 

		$product_id = $_awts['product_id'];
		$include_setting = $_awts['include_setting'];
		
		if( $include_setting == 'true' ){			
		    $awts_visibility = get_post_meta($product_id, 'awts-visibility', true);
		   
		   	//check if visibile only on backend
			if( isset($awts_visibility) && $awts_visibility == 'awts-hide' ){
					if(!is_admin())
					return;
				} 


			$only_backend = get_option('woo_total_sales_single_product_only_be'); 
			$only_single_product = get_option('woo_total_sales_single_product_only_fe'); 

			//check if visibile only on backend
			if( isset($only_backend) && $only_backend == 'yes' ){
					if(!is_admin())
					return;
				}

			//check if visibile only on single product page
			if( isset($only_single_product) && $only_single_product == 'yes' ){
					if(!is_product() && !is_admin())
					return;
				}	
		}

		//From admin setting
		$singular 	= get_option('woo_total_sales_singular');
		$plural 	= get_option('woo_total_sales_plural');
		

		$awts_custom_count = get_post_meta( $product_id, 'awts-custom-count', true );

		if(!empty($awts_custom_count)){
		
			$items_sold = $awts_custom_count;
		} else {
			$items_sold = $this->awts_get_total_sales_per_product( $product_id );			
			if(isset($items_sold)){
				$items_sold = $items_sold->_qty;			
			}else{
				$items_sold = 0;			

			}			
		}


		$items_sold = (isset($items_sold) ? absint($items_sold) : 0);

	    $price_texts  = ''; 	    

	  	if( $items_sold != 0 ){

		    $price_texts .= '<div class="items-sold" ><span class="items-sold-texts" >'; 
		    $price_texts .= sprintf( 

		    	esc_html( 
		    		_n( 
				    		(!empty($singular)) ? $singular : '%d item sold', 
				    		(!empty($plural)) ? $plural : '%d items sold', 
				    		$items_sold, 
				    		'woo-total-sales'  
				    		) 
			    		),

		    		$items_sold );
		    $price_texts .= '</span></div>';

		}
	    
	    return $price_texts;
	}


	
	/**
	 * Loading footer css.
	 *
	 * @return void
	 */
	public function awts_footer_style(){

			$barcolor 	= get_option('woo_total_sales_bar_color');
			$textcolor 	= get_option('woo_total_sales_texts_color');
			
			// check if bar-chart or texts color to start 'style' tag.
			if( !empty($barcolor) || !empty($textcolor) ){
			    	echo '<style type="text/css">';
			    }

			// check if bar-chart color.
			if(!empty($barcolor)){ echo '.items-sold span:before{color:'.$barcolor.'}'; }

			// check if total sales color.
			if(!empty($barcolor)){ echo '.items-sold span{color:'.$textcolor.'}'; }

			// check if bar-chart or texts color to end 'style' tag.
		    if( !empty($barcolor) || !empty($textcolor) ){
		    	echo '</style>';
		    }


		
	}

}

$awts_frontend = new Woo_Total_Sales_Frontend();