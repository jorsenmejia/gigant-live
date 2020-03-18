<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Woo Total Sales Backend
 *
 * Allows admin to set WooCommerce Total Sales of specific product.
 *
 * @class   Woo_Total_Sales_backend_mb 
 */


class Woo_Total_Sales_backend_mb extends Woo_Total_Sales_Core{

	/**
	 * Init and hook in the integration.
	 *
	 * @return void
	 */


	public function __construct() {
		$this->id                 = 'Woo_Total_Sales_backend_mb';
		$this->method_title       = __( 'WooCommerce Total Sales Backend', 'woo-total-sales' );
		$this->method_description = __( 'WooCommerce Total Sales Backend', 'woo-total-sales' );
	
		/**
	     * Meta box initialization.
	     */
		add_action( 'add_meta_boxes', array( $this, 'awts_product_total_sales_metabox'  ) );
		add_action( 'save_post',      array( $this, 'awts_product_total_sales_save'         ) );
	}
	/**
     * Adds the meta box.
     */
    public function awts_product_total_sales_metabox() {
        add_meta_box(
            'render_awts_product_total_sales_metabox',
            __( 'Total Sales', 'woocommerce' ),
            array( $this, 'render_awts_product_total_sales_metabox' ),
            'product',
            'side',
            'high'
        );
 
    }
    /**
     * Renders the meta box.
     */
    public function render_awts_product_total_sales_metabox( $post ) {
        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'awts_product_total_sales_metabox', 'awts_product_total_sales_metabox_nonce' );
        

        //From admin setting
		$singular 	= get_option('woo_total_sales_singular');
		$plural 	= get_option('woo_total_sales_plural');

		$post_id = $post->ID;
	
		$order_items = $this->awts_get_total_sales_per_product( $post_id );
		$items_sold_count = (isset($order_items) ? absint($order_items->_qty) : 0);
		$items_sold_total = (isset($order_items) ? absint($order_items->_line_total) : 0);


		$awts_visibility = get_post_meta( $post_id, 'awts-visibility', true );
		$awts_custom_count = get_post_meta( $post_id, 'awts-custom-count', true );

		$awts_show='';
		$awts_hide='';

		if($awts_visibility == 'awts-show'){
			$awts_show = 'checked';
			$awts_cc_class = '';
		} 

		if($awts_visibility == 'awts-hide'){
			$awts_hide = 'checked';
			//$awts_cc_class = 'awts-hide-section';
			$awts_cc_class = '';
		} 

		/*default value*/
			//print_pre('we are here');
		if(!isset($awts_visibility) || empty($awts_visibility)){
			$awts_show = 'checked';
			$awts_cc_class = '';
		}

		$awts_cc = '';
		if(isset($awts_custom_count) && !empty($awts_custom_count)){
			$awts_cc = $awts_custom_count;
		}

		/*print_pre($awts_visibility);
		print_pre($awts_custom_count);*/
		
	    $sold_texts  = ''; 

	  	/*if( $items_sold != 0 ){*/

		    $sold_texts .= '<table class="items-sold" ><tr><td><label for="items-sold-count" class="dashicons dashicons-chart-bar"></label></td><td class="misc-pub-section items-sold-count" >'; 
		    $sold_texts .= '<strong>';
		    $sold_texts .= sprintf( 
		    	esc_html( 
		    		_n( 
				    		(!empty($singular)) ? $singular : '%d item sold', 
				    		(!empty($plural)) ? $plural : '%d items sold', 
				    		$items_sold_count, 
				    		'woo-total-sales'  
				    		) 
			    		),

		    		$items_sold_count );
		    $sold_texts .= '</strong>';
		    $sold_texts .= '</td>';
		    $sold_texts .= '</tr>';

		    $sold_texts .= '<tr>';
			    $sold_texts .= '<td>';
			    $sold_texts .= '<label for="items-sold-count" class="dashicons dashicons-money"></label>';
			    
			    $sold_texts .= '</td>';
			    $sold_texts .= '<td class="misc-pub-section items-sold-count">';
			    $sold_texts .= '<strong>';
			    $sold_texts .=  
			    	wc_price( 	$items_sold_total, 
					    		'woo-total-sales' 				    		
				    		);
			    $sold_texts .= '</strong>';	
			    $sold_texts .= '</td>';
		    $sold_texts .= '</tr>';

		    /*show counts to frontend options*/
		    $only_backend = get_option('woo_total_sales_single_product_only_be'); 
		    /*var_dump($only_backend);*/
		    if( !isset($only_backend) || $only_backend == 'no' || empty($only_backend )){

		    		 $sold_texts .= '<tr class="awts-frontend-options-wrapper">';

					    $sold_texts .= '<table class="awts-frontend-options wp-list-table" >';
							    $sold_texts .= '<tr>';
								    $sold_texts .= '<td colspan="2" >';
									   	$sold_texts .= '<h3 class="hndle">';
									    	$sold_texts .= '<span>';
									    		$sold_texts .= __('Frontend Options:', 'woo-total-sales');
									    	$sold_texts .= '</span>';
									    $sold_texts .= '</h3>';
								    $sold_texts .= '</td>';					  
						    $sold_texts .= '</tr>';

						    $sold_texts .= '<tr class="awts-custom-count-tr '.$awts_cc_class.'">';
							    $sold_texts .= '<td class="misc-pub-section items-show-custom-count" colspan="2">';
							    $sold_texts .= '<strong>';
							    $sold_texts .= __('Display custom sales number on frontend?', 'woo-total-sales');
							    $sold_texts .= '</strong>';	
							    $sold_texts .= '</td>';
						    $sold_texts .= '</tr>';
						    $sold_texts .= '<tr class="awts-custom-count-tr '.$awts_cc_class.'">';
							
							    $sold_texts .= '<td colspan="2">';
							    $sold_texts .= '<span class="awts-custom-count-wrap">';
							    $sold_texts .= '<input class="awts-custom-count" type="number" name="awts-custom-count" placeholder="'.$items_sold_count.'" value="'.$awts_cc.'">';			   
							    $sold_texts .= '</span>';
							    $sold_texts .= '</td>';
						    $sold_texts .= '</tr>';

						    $sold_texts .= '<tr>';							    
							    $sold_texts .= '<td class="misc-pub-section items-show-count" colspan="2">';
							    $sold_texts .= '<strong>';
							    $sold_texts .= __('Show sales with price on frontend?', 'woo-total-sales');
							    $sold_texts .= '</strong>';
							    $sold_texts .= '</td>';
						    $sold_texts .= '</tr>';
						    $sold_texts .= '<tr>';							  
							    $sold_texts .= '<td colspan="2">';
							    $sold_texts .= '<span class="awts-radio-wrap">';
							    $sold_texts .= '<input class="awts-radio" type="radio" name="awts-visibility" value="awts-show" '.$awts_show.'>';
							    $sold_texts .= __('Yes', 'woo-total-sales');
							    $sold_texts .= '</span>';
							    
							    $sold_texts .= '<span class="awts-radio-wrap">';
				  				$sold_texts .= '<input class="awts-radio" type="radio" name="awts-visibility" value="awts-hide" '.$awts_hide.'>';
							    $sold_texts .= __('No', 'woo-total-sales');
							    $sold_texts .= '</span>';							   
							    $sold_texts .= '</td>';
						    $sold_texts .= '</tr>';

						    $sold_texts .= '<tr>';							    
							    $sold_texts .= '<td class="misc-pub-section items-shortcodes" colspan="2">';
							    $sold_texts .= '<strong>';
							    $sold_texts .= __('Use shortcodes?', 'woo-total-sales');
							    $sold_texts .= '</strong>';
							    $sold_texts .= '</td>';
						    $sold_texts .= '</tr>';
						    $sold_texts .= '<tr>';							  
							    $sold_texts .= '<td colspan="2">';
								    $sold_texts .= '<span class="awts-shortcodes-wrap">'; 
									    $sold_texts .= '[awts-total-sales product_id="'.$post_id.'" include_setting="true"]';  
								    $sold_texts .= '</span>';
							    $sold_texts .= '</td>';
						    $sold_texts .= '</tr>';
						    
						    $sold_texts .= '</table>';
						    $sold_texts .= '</tr>';
				}

		    $sold_texts .= '</table>';

		/*}*/
	    
	    echo $sold_texts;

    }

      /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function awts_product_total_sales_save( $post_id ) {
 
        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */
 
        // Check if our nonce is set.
        if ( ! isset( $_POST['awts_product_total_sales_metabox_nonce'] ) ) {
            return $post_id;
        }
 
        $nonce = $_POST['awts_product_total_sales_metabox_nonce'];
 
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'awts_product_total_sales_metabox' ) ) {
            return $post_id;
        }
 
        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
 
        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
 
        /* OK, it's safe for us to save the data now. */
 
        // Sanitize the user input.
        if(isset($_POST['awts-visibility'])){        	
        	$awts_visibility =  $_POST['awts-visibility'];
	        // Update the meta field.
	        update_post_meta( $post_id, 'awts-visibility', $awts_visibility );
        }

        if(isset($_POST['awts-custom-count'])){        	
        	$awts_custom_count = sanitize_text_field( $_POST['awts-custom-count'] );
        	 // Update the meta field.
	        update_post_meta( $post_id, 'awts-custom-count', $awts_custom_count );
        }
 
    }


	
}

$awts_backend = new Woo_Total_Sales_backend_mb();