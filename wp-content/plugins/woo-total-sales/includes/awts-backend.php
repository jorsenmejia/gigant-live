<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Woo Total Sales Backend
 *
 * Allows admin to set WooCommerce Total Sales of specific product.
 *
 * @class   Woo_Total_Sales_backend 
 */


class Woo_Total_Sales_backend extends Woo_Total_Sales_Core{

	/**
	 * Init and hook in the integration.
	 *
	 * @return void
	 */

	private $options_general;
    private $options_monthly_sales;
  

	public function __construct() {
		$this->id                 = 'Woo_Total_Sales_backend';
		$this->method_title       = __( 'WooCommerce Total Sales Backend', 'woo-total-sales' );
		$this->method_description = __( 'WooCommerce Total Sales Backend', 'woo-total-sales' );

	
		
	
		//add_action( 'admin_enqueue_scripts', array( $this, 'awts_backend_scripts' ));
		add_action('admin_menu', array($this, 'awts_product_total_sales_page'), 10 );
		add_action( 'admin_init', array( $this, 'awts_options_init' ) );	
		
		
		
	}
	/**
	 * Loading scripts.
	 *
	 * @return void
	 */

	public function awts_backend_scripts(){
		
		// loading plugin custom css file
		wp_register_style( 'awts-backend-style', plugins_url( 'woo-total-sales/assets/css/awts-backend-style.css' ) );
		wp_enqueue_style( 'awts-backend-style' );
		wp_enqueue_style( 'woocommerce_admin_dashboard_styles' );
		
	
	}
	

	public function awts_product_total_sales_page() {
			    add_submenu_page( 
			    	'woocommerce', 
			    	'Total Sales', 
			    	'Total Sales', 
			    	'manage_options', 
			    	'woo-total-sales', 
			    	array($this, 'awts_product_total_sales_page_callbak') ); 
			}

	public function awts_product_total_sales_page_callbak() {
			   


	    /*tabbed starts*/
	    $this->options_general = get_option( 'awts_general' );
		$this->options_monthly_sales = get_option( 'awts_monthly_sales' );
		

		$monthly_sales_screen = ( isset( $_GET['action'] ) && 'monthly-sales' == $_GET['action'] ) ? true : false;
       	

       	?>
        <div class="wrap">
            <h1><?php _e('Total Sales Overview', 'woo-total-sales'); ?></h1>
            <h2 class="nav-tab-wrapper">
				
				<a href="<?php echo admin_url( 'admin.php?page=woo-total-sales' ); ?>" class="nav-tab<?php if ( ! isset( $_GET['action'] ) || isset( $_GET['action'] ) && 'monthly-sales' != $_GET['action']   ) echo ' nav-tab-active'; ?>">
					<?php esc_html_e( 'General' ); ?>					
				</a>
				
				<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'monthly-sales' ), admin_url( 'admin.php?page=woo-total-sales' ) ) ); ?>" class="nav-tab<?php if ( $monthly_sales_screen ) echo ' nav-tab-active'; ?>">
					<?php esc_html_e( 'Monthly Sales' ); ?>					
				</a>
				

			</h2>
    
        	 <form method="post" action="options.php"><?php //   settings_fields( 'awts_general' );
				 if($monthly_sales_screen) { 
					settings_fields( 'awts_monthly_sales' );
					do_settings_sections( 'awts-setting-social' );
					
				} else { 
					settings_fields( 'awts_general' );
					do_settings_sections( 'awts-setting-admin' );
					
				} ?>
			</form>
        </div> <?php
			    /*tabbed ends*/
			}
	
	public function awts_add_total_sales_section( $sections ) {
	
		$sections['awtstotalsales'] = __( 'Total Sales', 'woocommerce' );
		return $sections;
		
	}

	public function awts_options_init() { 
         register_setting(
            'awts_general', // Option group
            'awts_general', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'awts_setting_section_id', // ID
            'General Sales Overview', // Title
            array( $this, 'awts_general_overview_callback' ), // Callback
            'awts-setting-admin' // Page
        ); 

				
		
		register_setting(
            'awts_monthly_sales', // Option group
            'awts_monthly_sales', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'awts_setting_section_id', // ID
            'Monthly Sales Overview', // Title
            array( $this, 'awts_monthly_sales_callback' ), // Callback
            'awts-setting-social' // Page
        );  
		
		 
		
		
	}


	public function print_section_info(){
			//your code...
	}


	public function fb_url_callback() {
        printf(
            '<input type="text" id="fb_url" name="awts_monthly_sales[fb_url]" value="%s" />',
            isset( $this->options_monthly_sales['fb_url'] ) ? esc_attr( $this->options_monthly_sales['fb_url']) : ''
        );
    }

 

    public function awts_general_overview_callback() {
         
			    //echo $this->awts_show_total_sales_overview();
			    $this->awts_get_template('awts-block-1-general.php');
			    $this->awts_setting_link();
    } 

    public function awts_monthly_sales_callback() {         
			    //echo $this->awts_show_total_sales_overview();
			    $this->awts_get_template('awts-block-2-monthly-sales.php');
			    $this->awts_setting_link();
    }

    public function awts_setting_link(){

			$awts_setting_link = admin_url( 'admin.php?page=wc-settings&tab=total_sales');
    		$html = '<h4 ><a class="awts-setting-link" href="'.$awts_setting_link.'" >Go to Total Sales Settings</a></h4>';
    		echo $html;

    }

   public function sanitize( $input )  {
        $new_input = array();
        if( isset( $input['fb_url'] ) )
            $new_input['fb_url'] = sanitize_text_field( $input['fb_url'] );
      
        if( isset( $input['hide_more_themes'] ) )
            $new_input['hide_more_themes'] = sanitize_text_field( $input['hide_more_themes'] );
       
        if( isset( $input['logo_image'] ) )
            $new_input['logo_image'] = sanitize_text_field( $input['logo_image'] );

        return $new_input;
    }
	

public function awts_show_total_sales_overview(){

	$html = '';		
	$html .= '<table class="widefat">';
		$html .= '<thead>';
		$html .= '<tr>';
			$html .= '<th scope="row" class="titledesc">';
				$html .= __('Sold items','woocommerce');;
			$html .= '</th>';
			$html .= '<th scope="row" class="titledesc">';
				$html .= __('Total Sales','woocommerce');
			$html .= '</th>';
			$html .= '<th scope="row" class="titledesc">';
				$html .= __('Total Shipping Costs','woocommerce');
			$html .= '</th>';
			$html .= '<th scope="row" class="titledesc">';
				$html .= __('Total Discount Applied','woocommerce');
			$html .= '</th>';			
		$html .= '</tr>';
		$html .= '</thead>';

		$html .= '<tbody>';
		$html .= '<tr>';
			$html .= '<td scope="row" class="titledesc">';
				$html .= "<label for='awts_get_total_sales_items'>".$this->awts_get_total_sales_items()."</label>";
			$html .= '</td>';
			$html .= '<td scope="row" class="titledesc">';
				$html .= "<label for='awts_get_total_sales'>".wc_price($this->awts_get_total_sales())."</label>";
			$html .= '</td>';
			$html .= '<td scope="row" class="titledesc">';
				$html .= "<label for='awts_overview_shipping_total'>".wc_price($this->awts_overview_shipping_total())."</label>";
			$html .= '</td>';
			$html .= '<td scope="row" class="titledesc">';
				$html .= "<label for='awts_overview_discount_total'>".wc_price($this->awts_overview_discount_total())."</label>";
			$html .= '</td>';
		$html .= '</tr>';
		$html .= '</tbody>';
	$html .= '</table>';
	
	return $html;
}


}

if( is_admin() )
$awts_backend = new Woo_Total_Sales_backend();