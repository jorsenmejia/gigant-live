<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Woo Total Sales Backend
 *
 * Allows admin to set WooCommerce Total Sales of specific product.
 *
 * @class   Woo_Total_Sales_backend_settings 
 */


class Woo_Total_Sales_backend_settings extends Woo_Total_Sales{

	/**
	 * Init and hook in the integration.
	 *
	 * @return void
	 */


	public function __construct() {
		$this->id                 = 'Woo_Total_Sales_backend_settings';
		$this->method_title       = __( 'WooCommerce Total Sales Backend', 'woo-total-sales' );
		$this->method_description = __( 'WooCommerce Total Sales Backend', 'woo-total-sales' );

	
		
		/**
		 * Create the section beneath the products tab
		 **/
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'awts_add_settings_tab'), 40 );
		add_action( 'woocommerce_settings_tabs_total_sales', array( $this, 'awts_settings_tab') );
		add_action( 'woocommerce_update_options_total_sales', array( $this, 'awfm_update_settings') );
		
	}

	/**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public static function awts_add_settings_tab( $settings_tabs ) {
        $settings_tabs['total_sales'] = __( 'Total Sales', 'woo-total-sales' );
        return $settings_tabs;
    }

	
	/**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::awfm_get_settings()
     */
    public static function awts_settings_tab() {
        woocommerce_admin_fields( self::awfm_floating_minicart_setting() );
    }

    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses self::awfm_get_settings()
     */
    public static function awfm_update_settings() {
        woocommerce_update_options( self::awfm_floating_minicart_setting() );
    }

    public static function awfm_floating_minicart_setting(){

		$settings[] = array( 'name' => __( 'Total Sales Setting', 'woo-total-sales' ), 'type' => 'title', 'desc' =>'', 'id' => 'woo_total_sales_title' );
		
		$settings[] = array(
			'title'    	=> __( 'Singular total sales text', 'woo-total-sales' ),
			'css'      => 'min-width:350px;',
			'id'       	=> 'woo_total_sales_singular',
			'desc'  	=> __( 'Please include %d at where you want to show the total sales number.,  e.g %d item sold', 'woo-total-sales' ),
			'type'     	=> 'text',
			'default'	=> '',
			'desc_tip'	=> true,
			'placeholder' => __( '%d item sold out', 'woo-total-sales' ),
		);

		$settings[] = array(
			'title'    	=> __( 'Plural total sales text', 'woo-total-sales' ),
			'css'      => 'min-width:350px;',
			'id'       	=> 'woo_total_sales_plural',
			'desc'  	=> __( 'Please include %d at where you want to show the total sales number., e.g %d items sold', 'woo-total-sales' ),
			'type'     	=> 'text',
			'default'	=> '',
			'desc_tip'	=> true,
			'placeholder' => __( '%d items sold out', 'woo-total-sales' ),
		);

		$settings[] = array(
			'title'    	=> __( 'Bar chart icon color', 'woo-total-sales' ),
			'css'      => 'min-width:55px;',
			'id'       	=> 'woo_total_sales_bar_color',
			'desc'  	=> __( 'Select/paste bar chart color', 'woo-total-sales' ),
			'type'     	=> 'color',
			'default'	=> '',
			'desc_tip'	=> true,
			'placeholder' => __( '#666666', 'woo-total-sales' ),
		);

		$settings[] = array(
			'title'    	=> __( 'Total sales texts color', 'woo-total-sales' ),
			'css'      => 'min-width:55px;',
			'id'       	=> 'woo_total_sales_texts_color',
			'desc'  	=> __( 'Select/paste total sales texts color', 'woo-total-sales' ),
			'type'     	=> 'color',
			'default'	=> '',
			'desc_tip'	=> true,
			'placeholder' => __( '#47a106', 'woo-total-sales' ),
		);

		$settings[] = array(
			'title'    	=> __( 'Only show on single product page (frontend)', 'woo-total-sales' ),
			'id'       	=> 'woo_total_sales_single_product_only_fe',
			'type'     	=> 'checkbox',
			'desc_tip'  => __( 'If this option is checked, it will only visible on single product page on frontend but not on shop archive pages.', 'woo-total-sales' ),
			'css'       => 'min-width:350px;',
			'default'	=> '',
			'desc'     => __( 'Total sales visible only on single product page (frontend)', 'woocommerce' ),					
		);

		$settings[] = array(
			'title'    	=> __( 'Only show on backend', 'woo-total-sales' ),
			'id'       	=> 'woo_total_sales_single_product_only_be',
			'type'     	=> 'checkbox',
			'desc_tip'  => __( 'If this option is checked, it will only visible on backend. it helps shopmanager to track the sales of the product but will not be disclosed to customers.', 'woo-total-sales' ),
			'css'       => 'min-width:350px;',
			'default'	=> '',
			'desc'     => __( 'Total sales visible only on backend', 'woocommerce' ),					
		);
		
		

		$settings[] = array( 'type' => 'sectionend', 'id' => 'woo_total_sales_sectionend');

		return $settings;
    }
}

$awts_backend = new Woo_Total_Sales_backend_settings();