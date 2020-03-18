<?php
/**
 * WCFMu plugin core
 *
 * Third Party Plugin Support Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmu/core
 * @version   2.2.2
 */
 
class WCFMu_Integrations {

	public function __construct() {
		global $WCFM, $WCFMu;
		
		// WCFM Thirdparty Query Var Filter
		add_filter( 'wcfm_query_vars', array( &$this, 'wcfmu_thirdparty_query_vars' ), 80 );
		add_filter( 'wcfm_endpoint_title', array( &$this, 'wcfmu_thirdparty_endpoint_title' ), 80, 2 );
		add_action( 'init', array( &$this, 'wcfmu_thirdparty_auction_init' ), 70 );
		add_action( 'init', array( &$this, 'wcfmu_thirdparty_rental_init' ), 80 );
		add_action( 'init', array( &$this, 'wcfmu_thirdparty_fooevents_init' ), 90 );
		
		// WCFMu Thirdparty Endpoint Edit
		add_filter( 'wcfm_endpoints_slug', array( $this, 'wcfmu_thirdparty_endpoints_slug' ) );
		
		// WCFMu Thirdparty Menu Filter
		add_filter( 'wcfm_menus', array( &$this, 'wcfmu_thirdparty_menus' ), 80 );
		
		// WCFMu Thirdparty Product Type
		add_filter( 'wcfm_product_types', array( &$this, 'wcfmu_thirdparty_product_types' ), 60 );
		
		// Third Party Product Type Capability
		add_filter( 'wcfm_capability_settings_fields_product_types', array( &$this, 'wcfmcap_product_types' ), 60, 3 );
		
		// WCFMu Thirdparty Load WCFMu Scripts
		add_action( 'wcfm_load_scripts', array( &$this, 'wcfmu_thirdparty_load_scripts' ), 80 );
		add_action( 'after_wcfm_load_scripts', array( &$this, 'wcfmu_thirdparty_load_scripts' ), 80 );
		
		// WCFMu Thirdparty Load WCFMu Styles
		add_action( 'wcfm_load_styles', array( &$this, 'wcfmu_thirdparty_load_styles' ), 80 );
		add_action( 'after_wcfm_load_styles', array( &$this, 'wcfmu_thirdparty_load_styles' ), 80 );
		
		// WCFMu Thirdparty Load WCFMu views
		//add_action( 'wcfm_load_views', array( &$this, 'wcfmu_thirdparty_load_views' ), 80 );
		add_action( 'before_wcfm_load_views', array( &$this, 'wcfmu_thirdparty_load_views' ), 80 );
		
		// WCFMu Thirdparty Ajax Controller
		add_action( 'after_wcfm_ajax_controller', array( &$this, 'wcfmu_thirdparty_ajax_controller' ) );
		
		// Product Manage Third Party Variation View
    add_filter( 'wcfm_product_manage_fields_variations', array( &$this, 'wcfmu_thirdparty_product_manage_fields_variations' ), 100, 4 );
		
    // Product Manage Third Party Variaton Date Edit
		add_filter( 'wcfm_variation_edit_data', array( &$this, 'wcfmu_thirdparty_product_data_variations' ), 100, 3 );
    
    // WP Job Manager - Resume Manager Support - 2.3.4
    if( $wcfm_allow_resume_manager = apply_filters( 'wcfm_is_allow_resume_manager', true ) ) {
			if ( WCFMu_Dependencies::wcfm_resume_manager_active_check() ) {
				// Resume Manager Product options
				add_filter( 'wcfm_product_manage_fields_pricing', array( &$this, 'wcfm_wpjrm_product_manage_fields' ), 60, 5 );
			}
		}
    
    // YITH Auction Support - 2.3.8
    if( $wcfm_allow_auction = apply_filters( 'wcfm_is_allow_auction', true ) ) {
			if( WCFMu_Dependencies::wcfm_yith_auction_active_check() ) {
				// YITH Auction Product options
				add_filter( 'after_wcfm_products_manage_general', array( &$this, 'wcfm_yithauction_product_manage_fields' ), 70, 2 );
			} else {
				if( get_option( 'wcfm_updated_end_point_auction' ) ) {
					delete_option( 'wcfm_updated_end_point_auction' );
				}
			}
		}
		
		// WooCommerce Simple Auction Support - 2.3.10
    if( $wcfm_allow_auction = apply_filters( 'wcfm_is_allow_auction', true ) ) {
			if( WCFMu_Dependencies::wcfm_wcs_auction_active_check() ) {
				// WooCommerce Simple Auction Products Query
				//update_option( 'simple_auctions_dont_mix_shop', 'no' );
				
				// WooCommerce Simple Auction Product options
				add_filter( 'after_wcfm_products_manage_general', array( &$this, 'wcfm_wcsauction_product_manage_fields' ), 70, 2 );
			} else {
				if( get_option( 'wcfm_updated_end_point_auction' ) ) {
					delete_option( 'wcfm_updated_end_point_auction' );
				}
			}
		}
		
		// WC Rental & Booking Pro Support - 2.3.10
    if( $wcfm_allow_rental = apply_filters( 'wcfm_is_allow_rental', true ) ) {
			if( WCFMu_Dependencies::wcfm_wc_rental_pro_active_check() ) {
				// WC Rental Product options
				add_filter( 'after_wcfm_products_manage_general', array( &$this, 'wcfm_wcrental_pro_product_manage_fields' ), 80, 2 );
				
				// WC Rental Product Inventory Management - 2.4.3
				add_filter( 'wcfm_product_fields_stock', array( &$this, 'wcfm_wcrental_product_inventory_manage' ), 80, 3 );
				
				// Order Item Meta Filter
				apply_filters( 'woocommerce_hidden_order_itemmeta', array( &$this, 'wcfm_wcrental_pro_hidden_order_itemmeta' ), 80 );
				
				// Quote Status Update
				add_action( 'wp_ajax_wcfm_modify_rental_quote_status', array( &$this, 'wcfm_modify_rental_quote_status' ) );
				
				// Quote Message
				add_action( 'wp_ajax_wcfm_rental_quote_message', array( &$this, 'wcfm_rental_quote_message' ) );
			} else {
				if( get_option( 'wcfm_updated_end_point_wcrental_pro_quote' ) ) {
					delete_option( 'wcfm_updated_end_point_wcrental_pro_quote' );
				}
			}
		}
		
		// WP Job Manager - Products Support - 2.3.4
    if( apply_filters( 'wcfm_is_allow_listings', true ) && apply_filters( 'wcfm_is_allow_products_for_listings', true ) ) {
			if ( WCFM_Dependencies::wcfm_wp_job_manager_plugin_active_check() ) {
				if( WCFM_Dependencies::wcfm_products_listings_active_check() && apply_filters( 'wcfm_is_allow_associate_listings_for_products', true ) ) {
					add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_wpjm_associate_listings_product_manage_fields' ), 120 );
				}
				if( apply_filters( 'wcfm_is_allow_manage_products', true ) && apply_filters( 'wcfm_is_allow_add_products', true ) ) {
					if( WCFM_Dependencies::wcfm_products_listings_active_check() || WCFM_Dependencies::wcfm_products_mylistings_active_check() ) {
						add_filter( 'submit_job_form_fields', array( &$this, 'wcfm_add_listing_product_manage_fields' ), 999 );
						add_filter( 'submit_job_form_required_label', array( &$this, 'wcfm_my_listing_product_manage_fields' ), 999, 2 );
						add_filter( 'the_content', array( &$this, 'wcfmu_add_listing_page' ), 50 );
					}
				}
				add_action( 'wp_enqueue_scripts', array( $this, 'wcfmu_add_listing_enqueue_scripts' ) );
			}
		}
		
		// Toolset Types - Products Support - 2.5.0
    if( apply_filters( 'wcfm_is_allow_toolset_types', true ) ) {
			if ( WCFMu_Dependencies::wcfm_toolset_types_active_check() ) {
				add_action( 'end_wcfm_settings', array( &$this, 'wcfm_toolset_types_settings' ), 15 );
				add_action( 'after_wcfm_products_manage_tabs_content', array( &$this, 'wcfm_toolset_types_product_manage_fields' ), 50 );
				add_action( 'end_wcfm_articles_manage', array( &$this, 'wcfm_toolset_types_article_manage_fields' ), 15 );
			}
		}
		
		// MapPress Support - 2.6.2
		if( $wcfm_is_allow_map = apply_filters( 'wcfm_is_allow_mappress', true ) ) {
			if ( WCFMu_Dependencies::wcfm_mappress_active_check() ) {
				//add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_mappress_product_manage_fields' ), 170 );
			}
		}
		
		// Toolset Types - User Fields Support - 3.0.1
		if ( WCFMu_Dependencies::wcfm_toolset_types_active_check() ) {
			if( apply_filters( 'wcfm_is_allow_toolset_types', true ) ) {
				add_action( 'end_wcfm_user_profile', array( &$this, 'wcfm_toolset_types_user_profile_fields' ), 150 );
				add_action( 'end_wcfm_customers_manage_form', array( &$this, 'wcfm_toolset_types_user_profile_fields' ), 150 );
			}
			if( apply_filters( 'wcfm_is_allow_toolset_types_view', true ) ) {
				add_action( 'after_wcfm_vendor_general_details', array( &$this, 'wcfm_toolset_types_user_profile_fields_view' ), 150 );
				add_action( 'after_wcfm_customer_general_details', array( &$this, 'wcfm_toolset_types_user_profile_fields_view' ), 150 );
			}
		}
		
		// Toolset Types - Taxonomy Fields Support - 3.0.2
    if( $wcfm_allow_toolset_types = apply_filters( 'wcfm_is_allow_toolset_types', true ) ) {
			if ( WCFMu_Dependencies::wcfm_toolset_types_active_check() ) {
				add_action( 'end_wcfm_wcpvendors_settings', array( &$this, 'wcfm_toolset_types_taxonomy_fields' ), 150 );
			}
		}
		
    if( $wcfm_allow_acf_fields = apply_filters( 'wcfm_is_allow_acf_fields', true ) ) {
    	if ( WCFMu_Dependencies::wcfm_acf_pro_active_check() ) { 
    		// Advanced Custom Fields(ACF) Pro - Products Support - 3.3.7
				add_action( 'after_wcfm_products_manage_tabs_content', array( &$this, 'wcfm_acf_pro_product_manage_fields' ), 60 );
				add_action( 'end_wcfm_articles_manage', array( &$this, 'wcfm_acf_pro_article_manage_fields' ), 160 );
			} elseif ( WCFMu_Dependencies::wcfm_acf_active_check() ) {
				// Advanced Custom Fields(ACF) - Products Support - 3.0.4
				add_action( 'after_wcfm_products_manage_tabs_content', array( &$this, 'wcfm_acf_product_manage_fields' ), 60 );
				add_action( 'end_wcfm_articles_manage', array( &$this, 'wcfm_acf_article_manage_fields' ), 160 );
			}
		}
		
		
		// Address Geocoder Support - 3.1.1
		if( $wcfm_is_allow_map = apply_filters( 'wcfm_is_allow_mappress', true ) ) {
			if ( WCFMu_Dependencies::wcfm_address_geocoder_active_check() ) {
				add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_address_geocoder_product_manage_fields' ), 170 );
			}
		}
		
		// Woocommerce Box Office Support - 3.3.3
    if( $wcfm_is_allow_wc_box_office = apply_filters( 'wcfm_is_allow_wc_box_office', true ) ) {
			if( WCFMu_Dependencies::wcfm_wc_box_office_active_check() ) {
				add_filter( 'wcfm_product_manage_fields_general', array( &$this, 'wcfm_wc_box_office_product_manage_fields_general' ), 20, 5 );
				add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_box_office_product_manage_fields' ), 90 );
			}
		}
		
		// WooCommerce Lottery - 3.5.0
    if( apply_filters( 'wcfm_is_allow_lottery', true ) ) {
			if( WCFMu_Dependencies::wcfm_wc_lottery_active_check() ) {
				add_filter( 'after_wcfm_products_manage_general', array( &$this, 'wcfm_wc_lottery_product_manage_fields' ), 70, 2 );
			}
		}
		
		
		// WooCommerce Deposit - 3.5.9
		if( apply_filters( 'wcfm_is_allow_wc_deposits', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wc_deposits_active_check() ) {
				add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_deposits_product_manage_fields' ), 180 );
			}
		}
		
		// WooCommerce PDF Vouchers - 4.0.0
		if( apply_filters( 'wcfm_is_allow_wc_pdf_vouchers', true ) && apply_filters( 'wcfmu_is_allow_downloadable', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wc_pdf_voucher_active_check() ) {
				add_filter( 'wcfm_product_fields_downloadable', array( &$this, 'wcfm_wc_pdf_vouchers_product_manage_downloadable_fields' ), 180, 3 );
				add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_pdf_vouchers_product_manage_fields' ), 180 );
				add_filter( 'wcfm_product_manage_fields_variations', array( &$this, 'wcfm_wc_pdf_vouchers_product_manage_fields_variations' ), 11, 4 );
				
				// Generate Voucher Cosed Form HTML
				add_action( 'wp_ajax_wcfm_generate_voucher_code_html', array( &$this, 'wcfm_generate_voucher_code_html' ) );
			}
		}
		
		// WooCommerce Tab Manager - 4.1.0
		if( apply_filters( 'wcfm_is_allow_wc_tabs_manager', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wc_tabs_manager_plugin_active_check() ) {
				add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_tabs_manager_product_manage_fields' ), 200 );
			}
		}
		
		// WooCommerce Warranty - 4.1.5
		if( apply_filters( 'wcfm_is_allow_wc_warranty', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wc_warranty_plugin_active_check() ) {
				add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_warranty_product_manage_fields' ), 210 );
			}
		}
		
		// WooCommerce Waitlist - 4.1.5
		if( apply_filters( 'wcfm_is_allow_wc_waitlist', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wc_waitlist_plugin_active_check() ) {
				add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_waitlist_product_manage_fields' ), 220 );
			}
		}
		
		// WooCommerce FooEvent - 5.4.0
		if( !$WCFMu->is_marketplace || ( $WCFMu->is_marketplace && ( $WCFMu->is_marketplace == 'wcfmmarketplace' ) ) ) {
			if( apply_filters( 'wcfm_is_allow_wc_fooevents', true ) ) {
				if ( WCFMu_Dependencies::wcfm_wc_fooevents() ) {
					add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_fooevents_product_manage_fields' ), 225 );
				} else {
					if( get_option( 'wcfm_updated_end_point_wc_fooevents' ) ) {
						delete_option( 'wcfm_updated_end_point_wc_fooevents' );
					}
				}
			}
		}
		
		// WooCommerce Measurement Price Calculator - 5.4.1
		if( apply_filters( 'wcfm_is_allow_wc_measurement_price_calculator', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wc_measurement_price_calculator() ) {
				add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_measurement_price_calculator_product_manage_fields' ), 230 );
			}
		}
		
		// WooCommerce Advanced Product Labels - 6.0.0
		if( apply_filters( 'wcfm_is_allow_wc_advanced_product_labels', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wc_advanced_product_labels_active_check() ) {
				add_action( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_advanced_product_labels_product_manage_fields' ), 240 );
			}
		}
		
		// WooCommerce Whole Sale Support - 6.0.3
    if( apply_filters( 'wcfm_is_allow_wholesale', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wholesale_active_check() ) {
				// Whole Sale Product options
				add_filter( 'wcfm_product_manage_fields_pricing', array( &$this, 'wcfm_wholesale_product_manage_fields' ), 60, 5 );
				add_filter( 'wcfm_product_manage_fields_variations', array( &$this, 'wcfm_wholesale_product_manage_fields_variations' ), 400, 4 );
				add_filter( 'wcfm_variation_edit_data', array( &$this, 'wcfm_wholesale_data_variations' ), 11, 3 );
			}
		}
		
		// WooCommerce Product Badge Manager Support - 6.0.5
    if( apply_filters( 'wcfm_is_allow_wc_product_badge', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wc_product_badge_manager_active_check() ) {
				// WooCommerce Product Badge options
				add_filter( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_product_badge_product_manage_fields' ), 230 );
			}
		}
		
		// WC Min/Max Quantities Support - 6.0.5
    if( apply_filters( 'wcfm_is_allow_wc_min_max_quantities', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wc_min_max_quantities_active_check() ) {
				// Whole Sale Product options
				add_filter( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_min_max_quantities_product_manage_fields' ), 230 );
				add_filter( 'wcfm_product_manage_fields_variations', array( &$this, 'wcfm_wc_min_max_quantities_product_manage_fields_variations' ), 400, 4 );
				add_filter( 'wcfm_variation_edit_data', array( &$this, 'wcfm_wc_min_max_quantities_data_variations' ), 11, 3 );
			}
		}
		
		// WooCommerce 360 images Support - 6.0.5
    if( apply_filters( 'wcfm_is_allow_wc_360_images', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wc_360_images_active_check() ) {
				// WC 360 Image options
				add_filter( 'wcfm_product_manager_gallery_fields_end', array( &$this, 'wcfm_product_manager_wc_360_images_fields' ), 50 );
			}
		}
		
		// WooCommerce Variation Swatch - 6.2.7
    if( apply_filters( 'wcfm_is_allow_wc_variaton_swatch', true ) ) {
			if( WCFMu_Dependencies::wcfm_wc_variaton_swatch_active_check() && WCFMu_Dependencies::wcfm_wc_variaton_swatch_pro_active_check() ) {
				add_filter( 'after_wcfm_products_manage_variable', array( &$this, 'wcfm_wc_variaton_swatch_product_manage_views' ), 50, 2 );
			}
		}
		
		// WooCommerce Quotation - 6.2.7
    if( apply_filters( 'wcfm_is_allow_wc_quotation', true ) ) {
			if( WCFMu_Dependencies::wcfm_wc_quotation_active_check() ) {
				add_filter( 'after_wcfm_products_manage_variable', array( &$this, 'wcfm_wc_quotation_product_manage_views' ), 50, 2 );
				add_filter( 'wcfm_order_status_display', array( &$this, 'wcfm_wc_quotation_order_status_label_display' ), 50, 2 );
				add_action( 'init',  array( &$this, 'wcfm_wc_quotation_order_quotation_process' ), 50 );
			}
		}
		
		// WooCommerce Dynamic Pricing - 6.2.9
    if( apply_filters( 'wcfm_is_allow_wc_dynamic_pricing', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wc_dynamic_pricing_active_check() ) {
				// Dynamic Pricing Product options
				add_filter( 'end_wcfm_products_manage', array( &$this, 'wcfm_wc_dynamic_pricing_product_manage_fields' ), 230 );
			}
		}
		
		// MSRP for WooCommerce (Algoritmika) Support - 6.2.9
    if( apply_filters( 'wcfm_is_allow_wc_msrp_pricing', true ) ) {
			if ( WCFMu_Dependencies::wcfm_msrp_for_wc_plugin_active_check() ) {
				// MSRP for WooCommerce Product options
				add_filter( 'wcfm_product_manage_fields_pricing', array( &$this, 'wcfm_msrp_for_wc_product_manage_fields' ), 55, 5 );
				add_filter( 'wcfm_product_manage_fields_variations', array( &$this, 'wcfm_msrp_for_wc_product_manage_fields_variations' ), 390, 4 );
				add_filter( 'wcfm_variation_edit_data', array( &$this, 'wcfm_msrp_for_wc_data_variations' ), 11, 3 );
			}
		}
		
		// Cost of Goods for WooCommerce (Algoritmika) Support - 6.2.9
    if( apply_filters( 'wcfm_is_allow_wc_cost_of_goods', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wcfm_wc_cost_of_goods_plugin_active_check() ) {
				// Cost of Goods for WooCommerce Product options
				add_filter( 'wcfm_product_manage_fields_pricing', array( &$this, 'wcfm_wc_cost_of_goods_product_manage_fields' ), 55, 5 );
				add_filter( 'wcfm_product_manage_fields_variations', array( &$this, 'wcfm_wc_cost_of_goods_product_manage_fields_variations' ), 390, 4 );
				add_filter( 'wcfm_variation_edit_data', array( &$this, 'wcfm_wc_cost_of_goods_data_variations' ), 11, 3 );
			}
		}
	}
	
	/**
   * Thirdparty Query Var
   */
  function wcfmu_thirdparty_query_vars( $query_vars ) {
  	global $WCFM, $WCFMu;
  	
  	$wcfm_modified_endpoints = wcfm_get_option( 'wcfm_endpoints', array() );
  	
  	// Auction
  	if( $wcfm_allow_auction = apply_filters( 'wcfm_is_allow_auction', true ) ) {
			if( WCFMu_Dependencies::wcfm_yith_auction_active_check() || WCFMu_Dependencies::wcfm_wcs_auction_active_check() ) {
				$query_auction_vars = array(
					'wcfm-auctions'        => ! empty( $wcfm_modified_endpoints['wcfm-auctions'] ) ? $wcfm_modified_endpoints['wcfm-auctions'] : 'auctions',
				);
				$query_vars = array_merge( $query_vars, $query_auction_vars );
			}
		}
		
		// Rental
		if( $wcfm_allow_rental = apply_filters( 'wcfm_is_allow_rental', true ) ) {
			if( WCFMu_Dependencies::wcfm_wc_rental_pro_active_check() ) {
				$query_rental_vars = array(
					'wcfm-rental-calendar'        => ! empty( $wcfm_modified_endpoints['wcfm-rental-calendar'] ) ? $wcfm_modified_endpoints['wcfm-rental-calendar'] : 'rental-calendar',
					'wcfm-rental-quote'           => ! empty( $wcfm_modified_endpoints['wcfm-rental-quote'] ) ? $wcfm_modified_endpoints['wcfm-rental-quote'] : 'rental-quote',
					'wcfm-rental-quote-details'   => ! empty( $wcfm_modified_endpoints['wcfm-rental-quote-details'] ) ? $wcfm_modified_endpoints['wcfm-rental-quote-details'] : 'rental-quote-details',
				);
				$query_vars = array_merge( $query_vars, $query_rental_vars );
			}
		}
		
		// FooEvent
		if( !$WCFMu->is_marketplace || ( $WCFMu->is_marketplace && ( $WCFMu->is_marketplace == 'wcfmmarketplace' ) ) ) {
			if( apply_filters( 'wcfm_is_allow_wc_fooevents', true ) ) {
				if ( WCFMu_Dependencies::wcfm_wc_fooevents() ) {
					$query_fooevent_vars = array(
						'wcfm-event-tickets'        => ! empty( $wcfm_modified_endpoints['wcfm-event-tickets'] ) ? $wcfm_modified_endpoints['wcfm-event-tickets'] : 'event-tickets',
					);
					$query_vars = array_merge( $query_vars, $query_fooevent_vars );
				}
			}
		}
		
		return $query_vars;
  }
  
  /**
   * Thirdparty End Point Title
   */
  function wcfmu_thirdparty_endpoint_title( $title, $endpoint ) {
  	
  	switch ( $endpoint ) {
			case 'wcfm-auctions' :
				$title = __( 'Auctions', 'wc-frontend-manager-ultimate' );
			break;
			
			case 'wcfm-rental-calendar' :
				$title = __( 'Rental Calendar', 'wc-frontend-manager-ultimate' );
			break;
			
			case 'wcfm-rental-quote' :
				$title = __( 'Quote Request', 'wc-frontend-manager-ultimate' );
			break;
			
			case 'wcfm-rental-quote-details' :
			  $title = __( 'Manage Quote Request', 'wc-frontend-manager-ultimate' );
			break;
			
			case 'wcfm-event-tickets' :
				$title = __( 'Tickets', 'wc-frontend-manager-ultimate' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * Thirdparty Endpoint Intialize - Auction
   */
  function wcfmu_thirdparty_auction_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if( WCFMu_Dependencies::wcfm_wcs_auction_active_check() || WCFMu_Dependencies::wcfm_yith_auction_active_check() ) {
			if( !get_option( 'wcfm_updated_end_point_auction' ) ) {
				// Flush rules after endpoint update
				flush_rewrite_rules();
				update_option( 'wcfm_updated_end_point_auction', 1 );
			}
		}
  }
  
  /**
   * Thirdparty Endpoint Intialize - Rental
   */
  function wcfmu_thirdparty_rental_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if( WCFMu_Dependencies::wcfm_wc_rental_pro_active_check() ) {
			if( !get_option( 'wcfm_updated_end_point_wcrental_pro_quote' ) ) {
				// Flush rules after endpoint update
				flush_rewrite_rules();
				update_option( 'wcfm_updated_end_point_wcrental_pro_quote', 1 );
			}
		}
  }
  
  /**
   * Thirdparty Endpoint Intialize - FooEvents
   */
  function wcfmu_thirdparty_fooevents_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if ( WCFMu_Dependencies::wcfm_wc_fooevents() ) {
			if( !get_option( 'wcfm_updated_end_point_wc_fooevents' ) ) {
				// Flush rules after endpoint update
				flush_rewrite_rules();
				update_option( 'wcfm_updated_end_point_wc_fooevents', 1 );
			}
		}
  }
  
  /**
	 * Thirdparty Endpoiint Edit
	 */
	function wcfmu_thirdparty_endpoints_slug( $endpoints ) {
		
		// Auction
		if( $wcfm_allow_auction = apply_filters( 'wcfm_is_allow_auction', true ) ) {
			if( WCFMu_Dependencies::wcfm_yith_auction_active_check() || WCFMu_Dependencies::wcfm_wcs_auction_active_check() ) {
				$auction_endpoints = array(
															'wcfm-auctions'  		   => 'auctions',
															);
				$endpoints = array_merge( $endpoints, $auction_endpoints );
			}
		}
		
		// Rental
		if( $wcfm_allow_rental = apply_filters( 'wcfm_is_allow_rental', true ) ) {
			if( WCFMu_Dependencies::wcfm_wc_rental_pro_active_check() ) {
				$rental_endpoints = array(
															'wcfm-rental-calendar'  		   => 'rental-calendar',
															'wcfm-rental-quote'  		       => 'rental-quote',
															'wcfm-rental-quote-details'    => 'rental-quote-details'
															);
				$endpoints = array_merge( $endpoints, $rental_endpoints );
			}
		}
		
		// FooEvent
		if( apply_filters( 'wcfm_is_allow_wc_fooevents', true ) ) {
			if ( WCFMu_Dependencies::wcfm_wc_fooevents() ) {
				$fooevents_endpoints = array(
															'wcfm-event-tickets'  		   => 'event-tickets',
															);
				$endpoints = array_merge( $endpoints, $fooevents_endpoints );
			}
		}
		
		return $endpoints;
	}
  
  /**
   * Thirdparty Menu
   */
  function wcfmu_thirdparty_menus( $menus ) {
  	global $WCFM, $WCFMu;
  	
  	// Auction
  	if( $wcfm_allow_auction = apply_filters( 'wcfm_is_allow_auction', true ) ) {
			if( WCFMu_Dependencies::wcfm_yith_auction_active_check() || WCFMu_Dependencies::wcfm_wcs_auction_active_check() ) {
				$menus = array_slice($menus, 0, 3, true) +
														array( 'wcfm-auctions' => array(   'label'  => __( 'Auctions', 'wc-frontend-manager-ultimate' ),
																												 'url'       => get_wcfm_auction_url(),
																												 'icon'      => 'gavel',
																												 'priority'  => 25
																												) )	 +
															array_slice($menus, 3, count($menus) - 3, true) ;
			}
		}
		
		// Rental
		if( $wcfm_allow_rental = apply_filters( 'wcfm_is_allow_rental', true ) ) {
			if( WCFMu_Dependencies::wcfm_wc_rental_pro_active_check() ) {
				$menus = array_slice($menus, 0, 3, true) +
														array( 'wcfm-rental-calendar' => array(   'label'  => __( 'Rentals', 'wc-frontend-manager-ultimate' ),
																												 'url'       => get_wcfm_rental_url(),
																												 'icon'      => 'calendar-check',
																												 'priority'  => 30
																												),
																		'wcfm-rental-quote' => array(   'label'  => __( 'Quote', 'wc-frontend-manager-ultimate' ),
																												 'url'       => get_wcfm_rental_quote_url(),
																												 'icon'      => 'snowflake',
																												 'priority'  => 32
																												)
																												)	 +
															array_slice($menus, 3, count($menus) - 3, true) ;
															
				if( get_option( 'rnb_enable_rfq_btn', 'closed' ) == 'closed' ) {
					unset( $menus['wcfm-rental-quote'] );
				}
			}
		}
		
		// FooEvent
		if( !$WCFMu->is_marketplace || ( $WCFMu->is_marketplace && ( $WCFMu->is_marketplace == 'wcfmmarketplace' ) ) ) {
			if( apply_filters( 'wcfm_is_allow_wc_fooevents', true ) ) {
				if ( WCFMu_Dependencies::wcfm_wc_fooevents() ) {
					$menus = array_slice($menus, 0, 3, true) +
														array( 'wcfm-event-tickets' => array(   'label'  => __( 'Tickets', 'wc-frontend-manager-ultimate' ),
																												 'url'       => get_wcfm_event_tickets_url(),
																												 'icon'      => 'ticket-alt',
																												 'priority'  => 25
																												) )	 +
															array_slice($menus, 3, count($menus) - 3, true) ;
				}
			}
		}
		
  	return $menus;
  }
  
  /**
   * WCFM Third Party Product Type
   */
  function wcfmu_thirdparty_product_types( $pro_types ) {
  	global $WCFM;
  	
  	// WP Job Manager - Resume Manager Product Type
  	if( $wcfm_allow_resume_manager = apply_filters( 'wcfm_is_allow_resume_manager', true ) ) {
			if ( WCFMu_Dependencies::wcfm_resume_manager_active_check() ) {
				$pro_types['resume_package'] = __( 'Resume Package', 'wp-job-manager-resumes' );
			}
		}
  	
  	// Auction
  	if( $wcfm_allow_auction = apply_filters( 'wcfm_is_allow_auction', true ) ) {
			if( WCFMu_Dependencies::wcfm_yith_auction_active_check() || WCFMu_Dependencies::wcfm_wcs_auction_active_check() ) {
				$pro_types['auction'] = __( 'Auction', 'wc-frontend-manager-ultimate' );
			}
		}
  	
  	// Rental
  	if( $wcfm_allow_rental = apply_filters( 'wcfm_is_allow_rental', true ) ) {
			if( WCFMu_Dependencies::wcfm_wc_rental_pro_active_check() ) {
				$pro_types['redq_rental'] = __( 'Rental Product', 'wc-frontend-manager-ultimate' );
			}
		}
		
		// Lottery
  	if( apply_filters( 'wcfm_is_allow_lottery', true ) ) {
			if( WCFMu_Dependencies::wcfm_wc_lottery_active_check() ) {
				$pro_types['lottery'] = __( 'Lottery', 'wc-frontend-manager-ultimate' );
			}
		}
  	
  	return $pro_types;
  }
  
  /**
	 * WCFM Capability Product Types
	 */
	function wcfmcap_product_types( $product_types, $handler = 'wcfm_capability_options', $wcfm_capability_options = array() ) {
		global $WCFM, $WCFMu;
		
		if ( WCFMu_Dependencies::wcfm_resume_manager_active_check() ) {
			$resume_package = ( isset( $wcfm_capability_options['resume_package'] ) ) ? $wcfm_capability_options['resume_package'] : 'no';
		
			$product_types["resume_package"] = array('label' => __('Resume Package', 'wc-frontend-manager-ultimate') , 'name' => $handler . '[resume_package]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $resume_package);
		}
		
		if( WCFMu_Dependencies::wcfm_yith_auction_active_check() || WCFMu_Dependencies::wcfm_wcs_auction_active_check() ) {
			$auction = ( isset( $wcfm_capability_options['auction'] ) ) ? $wcfm_capability_options['auction'] : 'no';
		
			$product_types["auction"] = array('label' => __('Auction', 'wc-frontend-manager-ultimate') , 'name' => $handler . '[auction]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $auction);
		}
		
		if( WCFMu_Dependencies::wcfm_wc_rental_pro_active_check() ) {
			$rental = ( isset( $wcfm_capability_options['rental'] ) ) ? $wcfm_capability_options['rental'] : 'no';
			
			$product_types["rental"] = array('label' => __('Rental', 'wc-frontend-manager') , 'name' => $handler . '[rental]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $rental);
		}
		
		if( WCFMu_Dependencies::wcfm_wc_box_office_active_check() ) {
			$wc_box_office_ticket = ( isset( $wcfm_capability_options['wc_box_office_ticket'] ) ) ? $wcfm_capability_options['wc_box_office_ticket'] : 'no';
			
			$product_types["wc_box_office_ticket"] = array('label' => __('Ticket', 'wc-frontend-manager-ultimate') , 'name' => $handler . '[wc_box_office_ticket]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $wc_box_office_ticket);
		}
		
		if( WCFMu_Dependencies::wcfm_wc_lottery_active_check() ) {
			$lottery = ( isset( $wcfm_capability_options['lottery'] ) ) ? $wcfm_capability_options['lottery'] : 'no';
		
			$product_types["lottery"] = array('label' => __('Lottery', 'wc-frontend-manager-ultimate') , 'name' => $handler . '[lottery]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $lottery);
		}
		
		return $product_types;
	}
	
  /**
   * Third Party Scripts
   */
  public function wcfmu_thirdparty_load_scripts( $end_point ) {
	  global $WCFM, $WCFMu, $wp_scripts;
    
	  switch( $end_point ) {
	  	case 'wcfm-articles-manage':
	  	  // Advanced Custom Fields(ACF) - Articles Support - 4.2.3
				if( $wcfm_allow_acf_fields = apply_filters( 'wcfm_is_allow_acf_fields', true ) ) {
					if ( WCFMu_Dependencies::wcfm_acf_active_check() ) {
						$WCFM->library->load_timepicker_lib();
						wp_enqueue_script( 'wcfmu_acf_articles_manage_js', $WCFMu->library->js_lib_url . 'integrations/acf/wcfmu-script-acf-articles-manage.js', array( 'jquery', 'wcfm_articles_manage_js' ), $WCFMu->version, true );
						
						$scheme      = is_ssl() ? 'https' : 'http';
						$acf_map_key = acf_get_setting( 'google_api_key' );
						if ( $acf_map_key ) {
							wp_enqueue_script( 'jquery-ui-autocomplete' );
							wp_enqueue_script( 'wcfm-acf-pro-pm-google-maps', $scheme . '://maps.googleapis.com/maps/api/js?key=' . $acf_map_key . '&libraries=places' );
						}
					}
				}
				
				// Advanced Custom Fields(ACF) Pro - Articles Support - 4.2.3
				if( $wcfm_allow_acf_fields = apply_filters( 'wcfm_is_allow_acf_fields', true ) ) {
					if ( WCFMu_Dependencies::wcfm_acf_pro_active_check() ) {
						$WCFM->library->load_timepicker_lib();
						wp_enqueue_script( 'wcfmu_acf_pro_articles_manage_js', $WCFMu->library->js_lib_url . 'integrations/acf/wcfmu-script-acf-pro-articles-manage.js', array( 'jquery', 'wcfm_articles_manage_js' ), $WCFMu->version, true );
						
						$scheme      = is_ssl() ? 'https' : 'http';
						$acf_map_key = acf_get_setting( 'google_api_key' );
						if ( $acf_map_key ) {
							wp_enqueue_script( 'jquery-ui-autocomplete' );
							wp_enqueue_script( 'wcfm-acf-pro-pm-google-maps', $scheme . '://maps.googleapis.com/maps/api/js?key=' . $acf_map_key . '&libraries=places' );
						}
					}
				}
	  	break;
	  	
	  	case 'wcfm-products-manage':
	  	 	 // YITH Auction Support
		  	if( $wcfm_allow_auction = apply_filters( 'wcfm_is_allow_auction', true ) ) {
					if( WCFMu_Dependencies::wcfm_yith_auction_active_check() ) {
						$WCFM->library->load_timepicker_lib();
						wp_enqueue_script( 'wcfmu_yithauction_products_manage_js', $WCFM->library->js_lib_url . 'products-manager/wcfm-script-yithauction-products-manage.js', array( 'jquery', 'wcfm_timepicker_js', 'wcfm_products_manage_js' ), $WCFMu->version, true );
					}
				}
				
				// WooCommerce Simple Auction Support
		  	if( $wcfm_allow_auction = apply_filters( 'wcfm_is_allow_auction', true ) ) {
					if( WCFMu_Dependencies::wcfm_wcs_auction_active_check() ) {
						$WCFM->library->load_timepicker_lib();
						wp_enqueue_script( 'wcfmu_wcsauction_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/auction/wcfmu-script-wcsauction-products-manage.js', array( 'jquery', 'wcfm_timepicker_js', 'wcfm_products_manage_js' ), $WCFMu->version, true );
					}
				}
				
				// WC Rental & Booking Pro Support - 2.3.10
				if( $wcfm_allow_rental = apply_filters( 'wcfm_is_allow_rental', true ) ) {
					if( WCFMu_Dependencies::wcfm_wc_rental_pro_active_check() ) {
						wp_enqueue_script( 'wcfmu_wc_rental_pro_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/rental/wcfmu-script-wc-rental-pro-products-manage.js', array( 'jquery', 'wcfm_products_manage_js' ), $WCFMu->version, true );
					}
				}
				
				// Toolset Types - Products Support - 3.1.7
				if( $wcfm_allow_toolset_types = apply_filters( 'wcfm_is_allow_toolset_types', true ) ) {
					if ( WCFMu_Dependencies::wcfm_toolset_types_active_check() ) {
						wp_enqueue_script( 'wcfmu_toolset_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/toolset/wcfmu-script-toolset-products-manage.js', array( 'jquery', 'wcfm_products_manage_js' ), $WCFMu->version, true );
						$wcfm_product_type_toolset_fields = (array) get_option( 'wcfm_product_type_toolset_fields' );
						wp_localize_script( 'wcfmu_toolset_products_manage_js', 'wcfm_product_type_toolset_fields', $wcfm_product_type_toolset_fields );
					}
				}
				
				// Advanced Custom Fields(ACF) - Products Support - 3.0.4
				if( $wcfm_allow_acf_fields = apply_filters( 'wcfm_is_allow_acf_fields', true ) ) {
					if ( WCFMu_Dependencies::wcfm_acf_active_check() ) {
						$WCFM->library->load_timepicker_lib();
						wp_enqueue_script( 'wcfmu_acf_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/acf/wcfmu-script-acf-products-manage.js', array( 'jquery', 'wcfm_products_manage_js' ), $WCFMu->version, true );
						
						$scheme      = is_ssl() ? 'https' : 'http';
						$acf_map_key = acf_get_setting( 'google_api_key' );
						if ( $acf_map_key ) {
							wp_enqueue_script( 'jquery-ui-autocomplete' );
							wp_enqueue_script( 'wcfm-acf-pro-pm-google-maps', $scheme . '://maps.googleapis.com/maps/api/js?key=' . $acf_map_key . '&libraries=places' );
						}
					}
				}
				
				// Advanced Custom Fields(ACF) Pro - Products Support - 3.3.7
				if( $wcfm_allow_acf_fields = apply_filters( 'wcfm_is_allow_acf_fields', true ) ) {
					if ( WCFMu_Dependencies::wcfm_acf_pro_active_check() ) {
						$WCFM->library->load_timepicker_lib();
						wp_enqueue_script( 'wcfmu_acf_pro_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/acf/wcfmu-script-acf-pro-products-manage.js', array( 'jquery', 'wcfm_products_manage_js' ), $WCFMu->version, true );
						
						$scheme      = is_ssl() ? 'https' : 'http';
						$acf_map_key = acf_get_setting( 'google_api_key' );
						if ( $acf_map_key ) {
							wp_enqueue_script( 'jquery-ui-autocomplete' );
							wp_enqueue_script( 'wcfm-acf-pro-pm-google-maps', $scheme . '://maps.googleapis.com/maps/api/js?key=' . $acf_map_key . '&libraries=places' );
						}
					}
				}
				
				// Address Geocoder Support - 3.1.1
				if( $wcfm_is_allow_map = apply_filters( 'wcfm_is_allow_mappress', true ) ) {
					if ( WCFMu_Dependencies::wcfm_address_geocoder_active_check() ) {
						$address_geocoder_options = get_option('address_geocoder_options');
            $apikey = $address_geocoder_options['apikey'];

            if ( ! empty( $apikey ) ) {
							$mapsapi = '//maps.googleapis.com/maps/api/js?key=' . $apikey;
							wp_register_script( 'wcfmu_googlemaps', $mapsapi );
							wp_register_script( 'wcfmu_geocoder_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/wcfmu-script-address-geocoder-products-manage.js', array( 'jquery', 'wcfm_products_manage_js' ), $WCFMu->version, true );

							wp_enqueue_script( 'wcfmu_googlemaps' );
							wp_enqueue_script( 'wcfmu_geocoder_products_manage_js' );
            }
					}
				}
				
				// Woocommerce Box Office Support - 3.3.3
				if( $wcfm_is_allow_wc_box_office = apply_filters( 'wcfm_is_allow_wc_box_office', true ) ) {
					if( WCFMu_Dependencies::wcfm_wc_box_office_active_check() ) {
						wp_enqueue_script( 'wcfmu_wc_box_office_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/wcfmu-script-wc-box-office-products-manage.js', array( 'jquery', 'wcfm_products_manage_js' ), $WCFMu->version, true );
					}
				}
				
				// WooCommerce Lottery Support
		  	if( apply_filters( 'wcfm_is_allow_lottery', true ) ) {
					if( WCFMu_Dependencies::wcfm_wc_lottery_active_check() ) {
						$WCFM->library->load_timepicker_lib();
						wp_enqueue_script( 'wcfmu_wc_lottery_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/wcfmu-script-wc-lottery-products-manage.js', array( 'jquery', 'wcfm_timepicker_js', 'wcfm_products_manage_js' ), $WCFMu->version, true );
					}
				}
				
				// WooCommerce Deposit Support
				if( apply_filters( 'wcfm_is_allow_wc_deposits', true ) ) {
					if ( WCFMu_Dependencies::wcfm_wc_deposits_active_check() ) {
						wp_enqueue_script( 'wcfmu_wc_deposit_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/wcfmu-script-wc-deposit-products-manage.js', array( 'jquery', 'wcfm_products_manage_js' ), $WCFMu->version, true );
					}
				}
				
				// WooCommerce PDF Vouchers Support - 4.0.0
				if( apply_filters( 'wcfm_is_allow_wc_pdf_vouchers', true ) && apply_filters( 'wcfmu_is_allow_downloadable', true ) ) {
					if ( WCFMu_Dependencies::wcfm_wc_pdf_voucher_active_check() ) {
						$WCFM->library->load_timepicker_lib();
						wp_enqueue_script( 'wcfmu_wc_pdf_vouchers_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/wcfmu-script-wc-pdf-vouchers-products-manage.js', array( 'jquery', 'wcfm_timepicker_js', 'wcfm_products_manage_js' ), $WCFMu->version, true );
						wp_localize_script( 'wcfmu_wc_pdf_vouchers_products_manage_js', 'WooVouMeta', array(	
																					'noofvouchererror' 			=> '<div>' . __( 'Please enter Number of Voucher Codes.', 'woovoucher' ) . '</div>',
																					'patternemptyerror' 		=> '<div>' . __( 'Please enter Pattern to import voucher code(s).', 'woovoucher' ) . '</div>',
																					'onlydigitserror' 			=> '<div>' . __( 'Please enter only Numeric values in Number of Voucher Codes.', 'woovoucher' ) . '</div>',
																					'generateerror' 			=> '<div>' . __( 'Please enter Valid Pattern to import voucher code(s).', 'woovoucher' ) . '</div>',
																					'filetypeerror'				=> '<div>' . __( 'Please upload csv file.', 'woovoucher' ) . '</div>',
																					'fileerror'					=> '<div>' . __( 'File can not be empty, please upload valid file.', 'woovoucher' ) . '</div>',
																					'enable_voucher'        	=> get_option( 'vou_enable_voucher' ), //Localize "Auto Enable Voucher" setting to use in JS 
																					'price_options'        		=> get_option( 'vou_voucher_price_options' ), //Localize "Voucher Price Options" setting to use in JS 
																					'invalid_price'         	=> __( 'You can\'t leave this empty.', 'woovoucher' ),
																					'woo_vou_nonce'				=> wp_create_nonce( 'woo_vou_pre_publish_validation' ),
																					'prefix_placeholder'		=> __('WPWeb', 'woovoucher'),
																					'seperator_placeholder' 	=> __('-', 'woovoucher'),
																					'pattern_placeholder'		=> __('LLDD', 'woovoucher'),
																					'global_vou_pdf_usability'	=> get_option('vou_pdf_usability'),
																					'vouchercodegenerated'  => __( 'Voucher codes successfully generated.', 'wc-frontend-manager-ultimate' )
																				) );
					}
				}
				
				// WooCommerce FooEvent - 5.4.0
				if( apply_filters( 'wcfm_is_allow_wc_fooevents', true ) ) {
					if ( WCFMu_Dependencies::wcfm_wc_fooevents() ) {
						global $wp_locale;
						
						$WCFM->library->load_datepicker_lib();
						$WCFM->library->load_colorpicker_lib();
						wp_enqueue_script( 'iris', admin_url('js/iris.min.js'),array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'), false, 1);
						wp_enqueue_script( 'wp-color-picker', admin_url('js/color-picker.min.js'), array('iris'), false,1);
						
						$colorpicker_l10n = array('clear' => __('Clear'), 'defaultString' => __('Default'), 'pick' => __('Select Color'));
						wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );
				
						wp_enqueue_script( 'wcfmu_wc_fooevents_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/fooevent/wcfmu-script-wc-fooevents-products-manage.js', array( 'jquery', 'wcfm_products_manage_js', 'wp-color-picker' ), $WCFMu->version, true );
						
						$dayTerm = __('Day', 'fooevents-multiday-events');
						
						$localArgs = array(
								'closeText'         => __( 'Done', 'woocommerce-events' ),
								'currentText'       => __( 'Today', 'woocommerce-events' ),
								'monthNames'        => $this->_strip_array_indices( $wp_locale->month ),
								'monthNamesShort'   => $this->_strip_array_indices( $wp_locale->month_abbrev ),
								'monthStatus'       => __( 'Show a different month', 'woocommerce-events' ),
								'dayNames'          => $this->_strip_array_indices( $wp_locale->weekday ),
								'dayNamesShort'     => $this->_strip_array_indices( $wp_locale->weekday_abbrev ),
								'dayNamesMin'       => $this->_strip_array_indices( $wp_locale->weekday_initial ),
								// set the date format to match the WP general date settings
								'dateFormat'        => wcfm_wp_date_format_to_js( get_option( 'date_format' ) ),
								// get the start of week from WP general setting
								'firstDay'          => get_option( 'start_of_week' ),
								// is Right to left language? default is false
								'isRTL'             => $wp_locale->is_rtl(),
								'dayTerm'           => $dayTerm
						);
						
						wp_localize_script( 'wcfmu_wc_fooevents_products_manage_js', 'localObj', $localArgs );
					}
				}
				
				// WooCommerce Measurement Price Calculator - 5.4.1
				if( apply_filters( 'wcfm_is_allow_wc_measurement_price_calculator', true ) ) {
					if ( WCFMu_Dependencies::wcfm_wc_measurement_price_calculator() ) {
						
						wp_enqueue_script( 'wcfmu_wc_price_calculator_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/wcfm-script-measurement-price-calculator.js', array( 'jquery', 'wcfm_products_manage_js' ), $WCFMu->version, true );

						// Variables for JS scripts
						$wc_price_calculator_admin_params = array(
							'woocommerce_currency_symbol'  => get_woocommerce_currency_symbol(),
							'woocommerce_weight_unit'      => 'no' !== get_option( 'woocommerce_enable_weight', true ) ? get_option( 'woocommerce_weight_unit' ) : '',
							'pricing_rules_enabled_notice' => __( 'Cannot edit price while a pricing table is active', 'woocommerce-measurement-price-calculator' ),
						);
				
						wp_localize_script( 'wcfmu_wc_price_calculator_products_manage_js', 'wc_price_calculator_admin_params', $wc_price_calculator_admin_params );
					}
				}
				
				// WooCommerce Advanced Product Labels - 6.0.0
				if( apply_filters( 'wcfm_is_allow_wc_advanced_product_labels', true ) ) {
					if ( WCFMu_Dependencies::wcfm_wc_advanced_product_labels_active_check() ) {
						$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
						
						$WCFM->library->load_colorpicker_lib();
						wp_enqueue_script( 'iris', admin_url('js/iris.min.js'),array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'), false, 1);
						wp_enqueue_script( 'wp-color-picker', admin_url('js/color-picker.min.js'), array('iris'), false,1);
						
						$colorpicker_l10n = array('clear' => __('Clear'), 'defaultString' => __('Default'), 'pick' => __('Select Color'));
						wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );

						wp_enqueue_script( 'woocommerce-advanced-product-labels', plugins_url( '/assets/admin/js/woocommerce-advanced-product-labels' . $suffix . '.js', WooCommerce_Advanced_Product_Labels()->file ), array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ), WooCommerce_Advanced_Product_Labels()->version );
				
						wp_localize_script( 'wp-conditions', 'wpc2', array(
							'action_prefix' => 'wapl_',
						) );
			
						wp_enqueue_script( 'wp-conditions' );
					}
				}
				
				// WooCommerce Variation Swatch - 6.2.7
				if( apply_filters( 'wcfm_is_allow_wc_variaton_swatch', true ) ) {
					if( WCFMu_Dependencies::wcfm_wc_variaton_swatch_active_check() && WCFMu_Dependencies::wcfm_wc_variaton_swatch_pro_active_check() ) {
						wp_enqueue_script( 'wcfmu_wc_variaton_swatch_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/wcfmu-script-wc-variation-swatch-products-manage.js', array( 'jquery', 'wcfm_products_manage_js' ), $WCFMu->version, true );
						
						$WCFM->library->load_colorpicker_lib();
						wp_enqueue_script( 'iris', admin_url('js/iris.min.js'),array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'), false, 1);
						wp_enqueue_script( 'wp-color-picker', admin_url('js/color-picker.min.js'), array('iris'), false,1);
						
						$colorpicker_l10n = array('clear' => __('Clear'), 'defaultString' => __('Default'), 'pick' => __('Select Color'));
						wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );
					}
				}
				
				// WooCommerce Quotation - 6.2.7
				if( apply_filters( 'wcfm_is_allow_wc_quotation', true ) ) {
					if( WCFMu_Dependencies::wcfm_wc_quotation_active_check() ) {
						wp_enqueue_script( 'wcfmu_wc_quotation_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/wcfmu-script-wc-quotation-products-manage.js', array( 'jquery', 'wcfm_products_manage_js' ), $WCFMu->version, true );
					}
				}
				
				// WooCommerce Dynamic Pricing - 6.2.9
				if( apply_filters( 'wcfm_is_allow_wc_dynamic_pricing', true ) ) {
					if ( WCFMu_Dependencies::wcfm_wc_dynamic_pricing_active_check() ) {
						/*wp_enqueue_script( 'woocommerce-pricing-admin', WC_Dynamic_Pricing::plugin_url() . '/assets/admin/admin.js', array(
							'jquery',
							'jquery-ui-datepicker'
						) );*/
						
						wp_enqueue_script( 'wcfmu_wc_dynamic_pricing_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/wcfmu-script-wc-dynamic-pricing-products-manage.js', array( 'jquery', 'wcfm_products_manage_js' ), $WCFMu->version, true );
						
						wp_localize_script( 'wcfmu_wc_dynamic_pricing_products_manage_js', 'woocommerce_pricing_admin', array(
							'calendar_image' => WC()->plugin_url() . '/assets/images/calendar.png'
						) );
			
						// Enqueue jQuery UI styles
						$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
						wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' );
					}
				}
				
	  	break; 	
	  	
	  	case 'wcfm-rental-calendar':
      	$WCFMu->library->load_fullcalendar_lib();
	    	wp_enqueue_script( 'wcfmu_rental_calendar_js', $WCFMu->library->js_lib_url . 'integrations/rental/wcfmu-script-wcrental-calendar.js', array('jquery'), $WCFMu->version, true );
      break;
      
      case 'wcfm-rental-quote':
      	$WCFM->library->load_datatable_lib();
	    	wp_enqueue_script( 'wcfmu_rental_quote_js', $WCFMu->library->js_lib_url . 'integrations/rental/wcfmu-script-wcrental-quote.js', array('jquery'), $WCFMu->version, true );
      break;
      
      case 'wcfm-rental-quote-details':
	    	wp_enqueue_script( 'wcfmu_rental_quote_details_js', $WCFMu->library->js_lib_url . 'integrations/rental/wcfmu-script-wcrental-quote-details.js', array('jquery'), $WCFMu->version, true );
      break;
      
      case 'wcfm-auctions':
      	$WCFM->library->load_datatable_lib();
	    	wp_enqueue_script( 'wcfmu_auctions_js', $WCFMu->library->js_lib_url . 'integrations/auction/wcfmu-script-auctions.js', array('jquery'), $WCFMu->version, true );
      break;
      
      case 'wcfm-event-tickets':
      	$WCFM->library->load_select2_lib();
      	$WCFM->library->load_datatable_lib();
      	$WCFM->library->load_datatable_download_lib();
	    	wp_enqueue_script( 'wcfmu_event_tickets_js', $WCFMu->library->js_lib_url . 'integrations/fooevent/wcfmu-script-wc-fooevents-tickets.js', array('jquery'), $WCFMu->version, true );
      break;
	  }
	}
	
	/**
   * Third Party Styles
   */
	public function wcfmu_thirdparty_load_styles( $end_point ) {
	  global $WCFM, $WCFMu;
		
	  switch( $end_point ) {
	  	case 'wcfm-products-manage':
		  	// WC Rental & Booking Pro Support - 2.3.10
				if( $wcfm_allow_rental = apply_filters( 'wcfm_is_allow_rental', true ) ) {
					if( WCFMu_Dependencies::wcfm_wc_rental_pro_active_check() ) {
						wp_enqueue_style( 'wcfmu_wc_rental_pro_products_manage_css', $WCFMu->library->css_lib_url . 'integrations/rental/wcfmu-style-wc-rental-pro-products-manage.css', array( ), $WCFMu->version );
					}
				}
				
				// MapPress Support - 2.6.2
				if( $wcfm_is_allow_map = apply_filters( 'wcfm_is_allow_map', true ) ) {
					if ( WCFMu_Dependencies::wcfm_mappress_active_check() ) {
						//wp_enqueue_style('mappress-admin', Mappress::$baseurl . '/css/mappress_admin.css', null, Mappress::VERSION);
						//wp_enqueue_style( 'wcfmu_mappress_products_manage_css', $WCFMu->library->css_lib_url . 'thirdparty/wcfmu-style-mappress-products-manage.css', array( 'mappress-admin' ), $WCFMu->version );
					}
				}
				
				// Woocommerce Box Office Support - 3.3.3
				if( $wcfm_is_allow_wc_box_office = apply_filters( 'wcfm_is_allow_wc_box_office', true ) ) {
					if( WCFMu_Dependencies::wcfm_wc_box_office_active_check() ) {
						wp_enqueue_style( 'wcfmu_wc_box_office_products_manage_css', $WCFMu->library->css_lib_url . 'integrations/wcfmu-style-wc-box-office-products-manage.css', array( ), $WCFMu->version );
					}
				}
				
				// WooCommerce PDF Vouchers Support - 4.0.0
				if( apply_filters( 'wcfm_is_allow_wc_pdf_vouchers', true ) && apply_filters( 'wcfmu_is_allow_downloadable', true ) ) {
					if ( WCFMu_Dependencies::wcfm_wc_pdf_voucher_active_check() ) {
						wp_enqueue_style( 'wcfmu_wc_pdf_vouchers_products_manage_css', $WCFMu->library->css_lib_url . 'integrations/pdf_voucher/wcfmu-style-wc-pdf-vouchers-products-manage.css', array( ), $WCFMu->version );
						
						if( is_rtl() ) {
							wp_enqueue_style( 'wcfmu_wc_pdf_vouchers_products_manage_rtl_css', $WCFMu->library->css_lib_url . 'integrations/pdf_voucher/wcfmu-style-wc-pdf-vouchers-products-manage-rtl.css', array( 'wcfmu_wc_pdf_vouchers_products_manage_css' ), $WCFMu->version );
						}
					}
				}
				
				// WooCommerce FooEvent - 5.3.4
				if( apply_filters( 'wcfm_is_allow_wc_fooevents', true ) ) {
					if ( WCFMu_Dependencies::wcfm_wc_fooevents() ) {
						wp_enqueue_style( 'wcfmu_wc_fooevents_products_manage_css', $WCFMu->library->css_lib_url . 'integrations/fooevent/wcfmu-style-wc-fooevents-products-manage.css', array(), $WCFMu->version );
					}
				}
				
				// WooCommerce Advanced Product Labels - 6.0.0
				if( apply_filters( 'wcfm_is_allow_wc_advanced_product_labels', true ) ) {
					if ( WCFMu_Dependencies::wcfm_wc_advanced_product_labels_active_check() ) {
						wp_enqueue_style( 'woocommerce-advanced-product-labels-front-end', plugins_url( '/assets/front-end/css/woocommerce-advanced-product-labels.min.css', WooCommerce_Advanced_Product_Labels()->file ), array(), WooCommerce_Advanced_Product_Labels()->version );
						wp_enqueue_style( 'woocommerce-advanced-product-labels', plugins_url( '/assets/admin/css/woocommerce-advanced-product-labels.min.css', WooCommerce_Advanced_Product_Labels()->file ), array( 'wp-color-picker' ), WooCommerce_Advanced_Product_Labels()->version );
					}
				}
				
				// WooCommerce Variation Swatch - 6.2.7
				if( apply_filters( 'wcfm_is_allow_wc_variaton_swatch', true ) ) {
					if( WCFMu_Dependencies::wcfm_wc_variaton_swatch_active_check() && WCFMu_Dependencies::wcfm_wc_variaton_swatch_pro_active_check() ) {
						wp_enqueue_style( 'wcfmu_wc_variation_swatch_products_manage_css', $WCFMu->library->css_lib_url . 'integrations/wcfmu-style-wc-variation-swatch-products-manage.css', array(), $WCFMu->version );
					}
				}
				
				// WooCommerce Dynamic Pricing - 6.2.9
				if( apply_filters( 'wcfm_is_allow_wc_dynamic_pricing', true ) ) {
					if ( WCFMu_Dependencies::wcfm_wc_dynamic_pricing_active_check() ) {
						wp_enqueue_style( 'wcfmu_wc_dynamic_pricing_products_manage_css', WC_Dynamic_Pricing::plugin_url() . '/assets/admin/admin.css' );
					}
				}
		  break;
		  
		  case 'wcfm-rental-quote':
	    	wp_enqueue_style( 'wcfmu_rental_quote_css', $WCFMu->library->css_lib_url . 'integrations/rental/wcfmu-style-wcrental-quote.css', array(), $WCFMu->version );
      break;
      
      case 'wcfm-rental-quote-details':
      	wp_enqueue_style( 'collapsible_css',  $WCFM->library->css_lib_url . 'wcfm-style-collapsible.css', array(), $WCFMu->version );
	    	wp_enqueue_style( 'wcfmu_rental_quote_details_css', $WCFMu->library->css_lib_url . 'integrations/rental/wcfmu-style-wcrental-quote-details.css', array(), $WCFMu->version );
      break;
		  
	  	case 'wcfm-auctions':
	    	wp_enqueue_style( 'wcfmu_auctions_css',  $WCFMu->library->css_lib_url . 'integrations/wcfmu-style-auctions.css', array(), $WCFMu->version );
		  break;
		  
		  case 'wcfm-event-tickets':
		  	wp_enqueue_style( 'wcfmu_event_tickets_css',  $WCFMu->library->css_lib_url . 'integrations/fooevent/wcfmu-style-wc-fooevents-tickets.css', array(), $WCFMu->version );
		  break;
	  }
	}
	
	/**
   * Third Party Views
   */
  public function wcfmu_thirdparty_load_views( $end_point ) {
	  global $WCFM, $WCFMu;
	  
	  switch( $end_point ) {
	  	case 'wcfm-products-manage':
	  		// WC Per Product Shipping Support - 2.5.0
				if( apply_filters( 'wcfm_is_allow_shipping', true ) && apply_filters( 'wcfm_is_allow_per_product_shipping', true ) ) {
					if ( WCFMu_Dependencies::wcfm_wc_per_peroduct_shipping_active_check() || ( $WCFMu->is_marketplace == 'wcpvendors' ) ) {
						$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-per-product-shipping-products-manage.php' );
					}
				}
				
				// WooCommerce Additional Variation Images - 3.0.2
				if( apply_filters( 'wcfm_is_allow_gallery', true ) ) {
					if ( WCFMu_Dependencies::wcfm_wc_variation_gallery_active_check() ) {
						$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-variation-gallery-products-manage.php' );
					}
				}
	  	 break;
	  	 
	  	case 'wcfm-rental-calendar':
        $WCFMu->template->get_template( 'integrations/rental/wcfmu-view-wcrental-calendar.php' );
      break;
      
      case 'wcfm-rental-quote':
        $WCFMu->template->get_template( 'integrations/rental/wcfmu-view-wcrental-quote.php' );
      break;
      
      case 'wcfm-rental-quote-details':
        $WCFMu->template->get_template( 'integrations/rental/wcfmu-view-wcrental-quote-details.php' );
      break;
      
      case 'wcfm-auctions':
        $WCFMu->template->get_template( 'integrations/auction/wcfmu-view-auctions.php' );
      break;
      
      case 'wcfm-event-tickets':
		  	$WCFMu->template->get_template( 'integrations/fooevent/wcfmu-view-wc-fooevents-tickets.php' );
		  break;
	  }
	}
	
	/**
   * Third Party Ajax Controllers
   */
  public function wcfmu_thirdparty_ajax_controller() {
  	global $WCFM, $WCFMu;
  	
  	$controllers_path = $WCFMu->plugin_path . 'controllers/integrations/';
  	
  	$controller = '';
  	if( isset( $_POST['controller'] ) ) {
  		$controller = $_POST['controller'];
  		
  		switch( $controller ) {
  			case 'wcfm-articles-manage':
  				// Toolset Types - Articles Support - 4.2.3
					if( $wcfm_allow_toolset_types = apply_filters( 'wcfm_is_allow_toolset_types', true ) ) {
						if ( WCFMu_Dependencies::wcfm_toolset_types_active_check() ) {
							include_once( $controllers_path . 'toolset/wcfmu-controller-toolset-types-articles-manage.php' );
							new WCFMu_Toolset_Types_Articles_Manage_Controller();
						}
					}
					
					// Advanced Custom Fields(ACF) - Articles Support - 4.2.3
					if( $wcfm_allow_acf_fields = apply_filters( 'wcfm_is_allow_acf_fields', true ) ) {
						if ( WCFMu_Dependencies::wcfm_acf_active_check() ) {
							include_once( $controllers_path . 'acf/wcfmu-controller-acf-articles-manage.php' );
							new WCFMu_ACF_Articles_Manage_Controller();
						}
					}
					
					// Advanced Custom Fields(ACF) - Articles Support - 4.2.3
					if( $wcfm_allow_acf_fields = apply_filters( 'wcfm_is_allow_acf_fields', true ) ) {
						if ( WCFMu_Dependencies::wcfm_acf_pro_active_check() ) {
							include_once( $controllers_path . 'acf/wcfmu-controller-acf-pro-articles-manage.php' );
							new WCFMu_ACF_Pro_Articles_Manage_Controller();
						}
					}
				break;
  			
  			case 'wcfm-products-manage':
  				include_once( $controllers_path . 'wcfmu-controller-integrations-products-manage.php' );
					new WCFMu_Integrations_Products_Manage_Controller();
					
					// WC Per Product Shipping - Products Support - 2.5.0
					if( apply_filters( 'wcfm_is_allow_shipping', true ) && apply_filters( 'wcfm_is_allow_per_product_shipping', true ) ) {
						if ( WCFMu_Dependencies::wcfm_wc_per_peroduct_shipping_active_check() || ( $WCFMu->is_marketplace == 'wcpvendors' ) ) {
							include_once( $controllers_path . 'wcfmu-controller-wc-per-product-shipping-products-manage.php' );
							new WCFMu_WC_Per_Product_Shipping_Products_Manage_Controller();
						}
					}
					
					// Toolset Types - Products Support - 2.5.0
					if( $wcfm_allow_toolset_types = apply_filters( 'wcfm_is_allow_toolset_types', true ) ) {
						if ( WCFMu_Dependencies::wcfm_toolset_types_active_check() ) {
							include_once( $controllers_path . 'toolset/wcfmu-controller-toolset-types-products-manage.php' );
							new WCFMu_Toolset_Types_Products_Manage_Controller();
						}
					}
					
					// WooCommerce Additional Variation Images - 3.0.2
					if( $wcfm_is_allow_gallery = apply_filters( 'wcfm_is_allow_gallery', true ) ) {
						if ( WCFMu_Dependencies::wcfm_wc_variation_gallery_active_check() ) {
							include_once( $controllers_path . 'wcfmu-controller-wc-variation-gallery-products-manage.php' );
							new WCFMu_WC_Variation_Gallery_Products_Manage_Controller();
						}
					}
					
					// Advanced Custom Fields(ACF) - Products Support - 3.0.4
					if( $wcfm_allow_acf_fields = apply_filters( 'wcfm_is_allow_acf_fields', true ) ) {
						if ( WCFMu_Dependencies::wcfm_acf_active_check() ) {
							include_once( $controllers_path . 'acf/wcfmu-controller-acf-products-manage.php' );
							new WCFMu_ACF_Products_Manage_Controller();
						}
					}
					
					// Advanced Custom Fields(ACF) - Products Support - 3.0.4
					if( $wcfm_allow_acf_fields = apply_filters( 'wcfm_is_allow_acf_fields', true ) ) {
						if ( WCFMu_Dependencies::wcfm_acf_pro_active_check() ) {
							include_once( $controllers_path . 'acf/wcfmu-controller-acf-pro-products-manage.php' );
							new WCFMu_ACF_Pro_Products_Manage_Controller();
						}
					}
					
					// Address Geocoder Support - 3.1.1
					if( $wcfm_is_allow_map = apply_filters( 'wcfm_is_allow_mappress', true ) ) {
						if ( WCFMu_Dependencies::wcfm_address_geocoder_active_check() ) {
							include_once( $controllers_path . 'wcfmu-controller-address-geocoder-products-manage.php' );
						  new WCFMu_Address_Geocoder_Products_Manage_Controller();
						}
					}
					
					// WooCommerce PDF Vouchers Support - 4.0.0
					if( apply_filters( 'wcfm_is_allow_wc_pdf_vouchers', true ) && apply_filters( 'wcfmu_is_allow_downloadable', true ) ) {
						if ( WCFMu_Dependencies::wcfm_wc_pdf_voucher_active_check() ) {
							include_once( $controllers_path . 'wcfmu-controller-wc-pdf-vouchers-products-manage.php' );
						  new WCFMu_WC_PDF_Vouchers_Products_Manage_Controller();
						}
					}
					
					// WooCommerce Tab Manager - 4.1.0
					if( apply_filters( 'wcfm_is_allow_wc_tabs_manager', true ) ) {
						if ( WCFMu_Dependencies::wcfm_wc_tabs_manager_plugin_active_check() ) {
							include_once( $controllers_path . 'wcfmu-controller-wc-tabs-manager-products-manage.php' );
						  new WCFMu_WC_Tabs_Manager_Products_Manage_Controller();
						}
					}
					
					// WooCommerce Warranty - 4.1.5
					if( apply_filters( 'wcfm_is_allow_wc_warranty', true ) ) {
						if ( WCFMu_Dependencies::wcfm_wc_warranty_plugin_active_check() ) {
							include_once( $controllers_path . 'wcfmu-controller-wc-warranty-products-manage.php' );
						  new WCFMu_WC_Warranty_Products_Manage_Controller();
						}
					}
					
					// WooCommerce FooEvent - 5.3.4
					if( apply_filters( 'wcfm_is_allow_wc_fooevents', true ) ) {
						if ( WCFMu_Dependencies::wcfm_wc_fooevents() ) {
							include_once( $controllers_path . 'fooevent/wcfmu-controller-wc-fooevents-products-manage.php' );
						  new WCFMu_WC_Fooevents_Products_Manage_Controller();
						}
					}
					
					// WooCommerce Measurement Price Calculator - 5.4.1
					if( apply_filters( 'wcfm_is_allow_wc_measurement_price_calculator', true ) ) {
						if ( WCFMu_Dependencies::wcfm_wc_measurement_price_calculator() ) {
							include_once( $controllers_path . 'wcfmu-controller-wc-measurement-price-calculator-products-manage.php' );
						  new WCFMu_WC_Measurement_Price_Calculator_Products_Manage_Controller();
						}
					}
  			break;
  			
  			case 'wcfm-rental-quote':
  				include_once( $controllers_path . 'rental/wcfmu-controller-rental-quote.php' );
					new WCFMu_Rental_Quote_Controller();
  			break;
  			
  			case 'wcfm-auctions':
  				include_once( $controllers_path . 'auction/wcfmu-controller-auctions.php' );
					new WCFMu_Auctions_Controller();
  			break;
  			
  			case 'wcfm-event-tickets':
					include_once( $controllers_path . 'fooevent/wcfmu-controller-wc-fooevents-tickets.php' );
					new WCFMu_Event_Tickets_Controller();
				break;
  			
  			case 'wcfm-profile':
  			case 'wcfm-customers-manage':
  				// Toolset Types - Products Support - 3.0.1
					if( $wcfm_allow_toolset_types = apply_filters( 'wcfm_is_allow_toolset_types', true ) ) {
						if ( WCFMu_Dependencies::wcfm_toolset_types_active_check() ) {
							include_once( $controllers_path . 'toolset/wcfmu-controller-toolset-types-user-profile.php' );
							new WCFMu_Toolset_Types_User_Profile_Controller();
						}
					}
				break;
				
				case 'wcfm-settings':
  				// Toolset Types - Products Support - 3.1.7
					if( $wcfm_allow_toolset_types = apply_filters( 'wcfm_is_allow_toolset_types', true ) ) {
						if ( WCFMu_Dependencies::wcfm_toolset_types_active_check() ) {
							include_once( $controllers_path . 'toolset/wcfmu-controller-toolset-types-settings.php' );
							new WCFMu_Toolset_Types_Settings_Controller();
						}
					}
  			break;
  		}
  	}
  }
  
  /**
	 * Product Manage Third Party Variation aditional options
	 */
	function wcfmu_thirdparty_product_manage_fields_variations( $variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options ) {
		global $WCFM, $WCFMu;
		
		// WooCommerce Barcode & ISBN Support
		if( $allow_barcode_isbn = apply_filters( 'wcfm_is_allow_barcode_isbn', true ) ) {
			if(WCFM_Dependencies::wcfm_wc_barcode_isbn_plugin_active_check()) {
				$barcode_fields = array(  
																"barcode" => array('label' => __('Barcode', 'wc-frontend-manager-ultimate') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_half_ele variable', 'label_class' => 'wcfm_title wcfm_ele wcfm_half_ele_title variable' ),
																"ISBN" => array('label' => __('ISBN', 'wc-frontend-manager-ultimate') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_half_ele variable', 'label_class' => 'wcfm_ele wcfm_title wcfm_half_ele_title variable' )
																);
				$variation_fileds = array_merge( $variation_fileds, $barcode_fields);
			}
		}
		
		// WooCommerce MSRP Pricing Support
		if( $allow_msrp_pricing = apply_filters( 'wcfm_is_allow_msrp_pricing', true ) ) {
			if(WCFM_Dependencies::wcfm_wc_msrp_pricing_plugin_active_check()) {
				$msrp_fields = array(  
																"_msrp" => array('label' => __('MSRP Price', 'wc-frontend-manager-ultimate') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele variable', 'label_class' => 'wcfm_title wcfm_ele variable' ),
																);
				$variation_fileds = array_merge( $variation_fileds, $msrp_fields);
			}
		}
		
		// WooCommerce Product Fees Support
		if( $allow_product_fees = apply_filters( 'wcfm_is_allow_product_fees', true ) ) {
			if(WCFM_Dependencies::wcfm_wc_product_fees_plugin_active_check()) {
				$product_fees_fields = array(  
																			"product-fee-name" => array('label' => __('Fee Name', 'wc-frontend-manager-ultimate') , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_half_ele variable', 'label_class' => 'wcfm_title wcfm_half_ele_title wcfm_ele variable', 'hints' => __( 'This will be shown at the checkout description the added fee.', 'wc-frontend-manager-ultimate' )),
																			"product-fee-amount" => array('label' => __('Fee Amount', 'wc-frontend-manager-ultimate') . '(' . get_woocommerce_currency_symbol() . ')' , 'type' => 'text', 'class' => 'wcfm-text wcfm_ele wcfm_half_ele variable', 'label_class' => 'wcfm_ele wcfm_title wcfm_half_ele_title variable', 'hints' => __( 'Enter a monetary decimal without any currency symbols or thousand separator. This field also accepts percentages.', 'wc-frontend-manager-ultimate' )),
																			"product-fee-multiplier" => array('label' => __('Multiple Fee by Quantity', 'wc-frontend-manager-ultimate') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele variable', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title variable', 'hints' => __( 'Multiply the fee by the quantity of this product that is added to the cart.', 'wc-frontend-manager-ultimate' ) ),
																		);
				$variation_fileds = array_merge( $variation_fileds, $product_fees_fields);
			}
		}
		
		// WooCOmmerce Role Based Price Suport
		if( apply_filters( 'wcfm_is_allow_role_based_price', true ) ) {
			if(WCFM_Dependencies::wcfm_wc_role_based_price_active_check()) {
				if ( !function_exists('get_editable_roles') ) {
					 include_once( ABSPATH . '/wp-admin/includes/user.php' );
				}
				$wp_roles = get_editable_roles();
				$wc_rbp_general = (array) get_option( 'wc_rbp_general' );
				if( !empty( $wc_rbp_general ) ) {
					$wc_rbp_allowed_roles = ( isset( $wc_rbp_general['wc_rbp_allowed_roles'] ) ) ? $wc_rbp_general['wc_rbp_allowed_roles'] : array();
					$wc_rbp_regular_price_label = ( isset( $wc_rbp_general['wc_rbp_regular_price_label'] ) ) ? $wc_rbp_general['wc_rbp_regular_price_label'] : __( 'Regular Price', 'wc-frontend-manager' );
					$wc_rbp_selling_price_label = ( isset( $wc_rbp_general['wc_rbp_selling_price_label'] ) ) ? $wc_rbp_general['wc_rbp_selling_price_label'] : __( 'Selling Price', 'wc-frontend-manager' );
					if( !empty( $wc_rbp_allowed_roles ) ) {
						foreach( $wc_rbp_allowed_roles as $wc_rbp_allowed_role ) {
							$role_based_price_fields = array(  
																								$wc_rbp_allowed_role . "-regularprice-rolebased" => array( 'label' => $wp_roles[$wc_rbp_allowed_role]['name'] . ' ' . $wc_rbp_regular_price_label, 'type' => 'text', 'class' => 'wcfm-text wcfm_ele variable', 'label_class' => 'wcfm_ele wcfm_title variable' ),
																								$wc_rbp_allowed_role . "-sellingprice-rolebased"    => array( 'label' => $wp_roles[$wc_rbp_allowed_role]['name'] . ' ' . $wc_rbp_selling_price_label, 'type' => 'text', 'class' => 'wcfm-text wcfm_ele variable', 'label_class' => 'wcfm_ele wcfm_title variable' ),
																							);
							$variation_fileds = array_merge( $variation_fileds, $role_based_price_fields);
						}
					}
				}
			}
		}
		
	  return $variation_fileds;									
	}
	
	/**
	 * Product Manage Third Party Variaton edit data
	 */
	function wcfmu_thirdparty_product_data_variations( $variations, $variation_id, $variation_id_key ) {
		global $WCFM, $WCFMu;
		
		if( $variation_id  ) {
			// WooCommerce Barcode & ISBN Support
			if( $allow_barcode_isbn = apply_filters( 'wcfm_is_allow_barcode_isbn', true ) ) {
				if(WCFM_Dependencies::wcfm_wc_barcode_isbn_plugin_active_check()) {
					$variations[$variation_id_key]['barcode'] = get_post_meta( $variation_id, 'barcode', true );
					$variations[$variation_id_key]['ISBN'] = get_post_meta( $variation_id, 'ISBN', true);
				}
			}
			
			// WooCommerce MSRP Pricing Support
			if( $allow_msrp_pricing = apply_filters( 'wcfm_is_allow_msrp_pricing', true ) ) {
				if(WCFM_Dependencies::wcfm_wc_msrp_pricing_plugin_active_check()) {
					$variations[$variation_id_key]['_msrp'] = get_post_meta( $variation_id, '_msrp', true);
				}
			}
			
			// WooCommerce Product Fees Support
			if( $allow_product_fees = apply_filters( 'wcfm_is_allow_product_fees', true ) ) {
				if(WCFM_Dependencies::wcfm_wc_product_fees_plugin_active_check()) {
					$variations[$variation_id_key]['product_fee_name'] = get_post_meta( $variation_id, 'product-fee-name', true );
					$variations[$variation_id_key]['product_fee_amount'] = get_post_meta( $variation_id, 'product-fee-amount', true );
					$variations[$variation_id_key]['product_fee_multiplier'] = get_post_meta( $variation_id, 'product-fee-multiplier', true );
				}
			}
			
			// WooCOmmerce Role Based Price Suport
			if( apply_filters( 'wcfm_is_allow_role_based_price', true ) ) {
				if(WCFM_Dependencies::wcfm_wc_role_based_price_active_check()) {
					$wc_rbp_general = (array) get_option( 'wc_rbp_general' );
					if( !empty( $wc_rbp_general ) ) {
						$wc_rbp_allowed_roles = ( isset( $wc_rbp_general['wc_rbp_allowed_roles'] ) ) ? $wc_rbp_general['wc_rbp_allowed_roles'] : array();
						if( !empty( $wc_rbp_allowed_roles ) ) {
							$role_based_price = (array) get_post_meta( $variation_id, '_role_based_price', true );
							foreach( $wc_rbp_allowed_roles as $wc_rbp_allowed_role ) {
								$regular_price = '';
								$selling_price = '';
								if( isset( $role_based_price[$wc_rbp_allowed_role] ) && isset( $role_based_price[$wc_rbp_allowed_role]['regular_price'] ) ) $regular_price = $role_based_price[$wc_rbp_allowed_role]['regular_price'];
								if( isset( $role_based_price[$wc_rbp_allowed_role] ) && isset( $role_based_price[$wc_rbp_allowed_role]['selling_price'] ) ) $selling_price = $role_based_price[$wc_rbp_allowed_role]['selling_price'];
								$variations[$variation_id_key][$wc_rbp_allowed_role.'-regularprice-rolebased'] = $regular_price;
								$variations[$variation_id_key][$wc_rbp_allowed_role.'-sellingprice-rolebased'] = $selling_price;
							}
						}
					}
				}
			}
			
			if( apply_filters( 'wcfm_is_allow_wc_pdf_vouchers', true ) && apply_filters( 'wcfmu_is_allow_downloadable', true ) ) {
				if ( WCFMu_Dependencies::wcfm_wc_pdf_voucher_active_check() ) {
					$variations[$variation_id_key]['_woo_vou_variable_pdf_template'] = get_post_meta( $variation_id, '_woo_vou_pdf_template', true );
					$variations[$variation_id_key]['_woo_vou_variable_voucher_delivery'] = get_post_meta( $variation_id, '_woo_vou_voucher_delivery', true );
					$variations[$variation_id_key]['_woo_vou_variable_codes'] = get_post_meta( $variation_id, '_woo_vou_codes', true );
					$variations[$variation_id_key]['_woo_vou_variable_vendor_address'] = get_post_meta( $variation_id, '_woo_vou_vendor_address', true );
				}
			}
			
		}
		
		return $variations;
	}
	
  /**
	 * WP Job Manager - Resume Manager Product General options
	 */
	function wcfm_wpjrm_product_manage_fields( $general_fields, $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
		global $WCFM;
		
		$_resume_package_subscription_type        = '';
		$_resume_limit     = '';
		$_resume_duration       = '';
		$_resume_featured = 'no';
		
		if( $product_id ) {
			$_resume_package_subscription_type        = get_post_meta( $product_id, '_resume_package_subscription_type', true );
			$_resume_limit     = get_post_meta( $product_id, '_resume_limit', true );
			$_resume_duration       = get_post_meta( $product_id, '_resume_duration', true );
			$_resume_featured = get_post_meta( $product_id, '_resume_featured', true );
		}
		
		$pos_counter = 4;
		if( WCFM_Dependencies::wcfmu_plugin_active_check() ) $pos_counter = 6;
		
		$general_fields = array_slice($general_fields, 0, $pos_counter, true) +
																	array( 
																				"_resume_package_subscription_type" => array( 'label' => __('Subscription Type', 'wp-job-manager-resumes' ), 'type' => 'select', 'options' => array( 'package' => __( 'Link the subscription to the package (renew listing limit every subscription term)', 'wp-job-manager-resumes' ), 'listing' => __( 'Link the subscription to posted listings (renew posted listings every subscription term)', 'wp-job-manager-resumes' ) ), 'class' => 'wcfm-select wcfm_ele resume_package_price_ele resume_package' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title wcfm_ele resume_package' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( 'Choose how subscriptions affect this package', 'wp-job-manager-resumes' ), 'value' => $_resume_package_subscription_type ),
																				"_resume_limit" => array( 'label' => __('Resume listing limit', 'wp-job-manager-resumes' ), 'placeholder' => __( 'Unlimited', 'wc-frontend-manager-ultimate'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele resume_package_price_ele resume_package' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title wcfm_ele resume_package' . ' ' . $wcfm_wpml_edit_disable_element, 'attributes' => array( 'min'   => '', 'step' 	=> '1' ), 'hints' => __( 'The number of resumes a user can post with this package.', 'wp-job-manager-resumes' ), 'value' => $_resume_limit ),
																				"_resume_duration" => array( 'label' => __('Resume listing duration', 'wp-job-manager-resumes' ), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele resume_package_price_ele resume_package' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title wcfm_ele resume_package' . ' ' . $wcfm_wpml_edit_disable_element, 'attributes' => array( 'min'   => '', 'step' 	=> '1' ), 'hints' => __( 'The number of days that the resume will be active.', 'wp-job-manager-resumes' ), 'value' => $_resume_duration ),
																				"_resume_featured" => array( 'label' => __('Feature Listings?', 'wp-job-manager-resumes' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele resume_package_price_ele resume_package' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title checkbox_title wcfm_ele resume_package' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( 'Feature this resume - it will be styled differently and sticky.', 'wp-job-manager-resumes' ), 'value' => 'yes', 'dfvalue' => $_resume_featured ),
																				) +
																	array_slice($general_fields, $pos_counter, count($general_fields) - 1, true) ;
		return $general_fields;
	}
	
  /**
	 * YITH Auction Product General options
	 */
	function wcfm_yithauction_product_manage_fields( $product_id = 0, $product_type ) {
		global $WCFM, $WCFMu;
		$WCFMu->template->get_template( 'integrations/auction/wcfmu-view-yithauctions-product-manage.php' );
	}
	
  /**
	 * WooCommerce Simple Auction Product General options
	 */
	function wcfm_wcsauction_product_manage_fields( $product_id = 0, $product_type ) {
		global $WCFM, $WCFMu;
		$WCFMu->template->get_template( 'integrations/auction/wcfmu-view-wcsauctions-product-manage.php' );
	}
	
  /**
	 * WC Rental Pro Product General options
	 */
	function wcfm_wcrental_pro_product_manage_fields( $product_id = 0, $product_type ) {
		global $WCFM, $WCFMu;
		$WCFMu->template->get_template( 'integrations/rental/wcfmu-view-wcrentalpro-product-manage.php' );
	}
	
	/**
   * WP Job Manager - Products Manage General options
   */
  function wcfm_wpjm_associate_listings_product_manage_fields( ) {
		global $WCFMu;
	  $WCFMu->template->get_template( 'integrations/wcfmu-view-wpjm-associate-listings-products-manage.php' );
	}
	
	/**
	 * WC Lottery Product General options
	 */
	function wcfm_wc_lottery_product_manage_fields( $product_id = 0, $product_type ) {
		global $WCFM, $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-lottery-product-manage.php' );
	}
	
	
	/**
	 * Listings - Associate Products Init - 3.2.1
	 */
	function wcfm_add_listing_product_manage_fields( $fields ) {
		
		if ( ! get_option( 'wpjmp_enable_products_on_listings' ) ) {
			return $fields;
		}
		
		if( isset( $fields['company'] ) && isset( $fields['company']['products'] ) ) {
			$products_create_description = sprintf( __( '%s | %s', 'wp-job-manager' ), '<a href="#" data-product_field="products" class="wcfm-add-product wcfm_listing_product_option"><i class="wcfmfa fa-cube"></i> ' . __( 'Create New Product', 'wc-frontend-manager-ultimate' ) . '</a>',  '<a href="' . get_wcfm_products_url() . '" class="wcfm-manage-products wcfm_listing_product_option" target="_blank"><i class="wcfmfa fa-cubes"></i> ' . __( 'Manage Products', 'wc-frontend-manager-ultimate' ) . '</a>');
			$fields['company']['products']['description'] = $products_create_description;
		} else {
			$products_create_description = sprintf( __( '%s | %s', 'wp-job-manager' ), '<a href="#" data-product_field="products" class="wcfm-add-product wcfm_listing_product_option"><i class="wcfmfa fa-cube"></i> ' . __( 'Create New Product', 'wc-frontend-manager-ultimate' ) . '</a>',  '<a href="' . get_wcfm_products_url() . '" class="wcfm-manage-products wcfm_listing_product_option" target="_blank"><i class="wcfmfa fa-cubes"></i> ' . __( 'Manage Products', 'wc-frontend-manager-ultimate' ) . '</a>');
			$fields['company']['products'] = array(
				'label'			  => get_option( 'wpjmp_select_products_text' ),
				'type'			  => 'multiselect',
				'options'		  => array(),
				'required'	  => false,
				'description' => $products_create_description,
				'priority' 	  => 10,
			);
		}
		
		return $fields;
	}
	
	/**
	 * My Listings - Associate Product init - 3.6.0
	 */
	function wcfm_my_listing_product_manage_fields( $label, $field ) {
		if( isset( $field['type'] ) && ( $field['type'] == 'select-products' ) ) {
			$products_create_description = sprintf( __( '%s | %s', 'wp-job-manager' ), '<a href="#" data-product_field="' . $field['slug'] . '" class="wcfm-add-product wcfm_listing_product_option"><i class="wcfmfa fa-cube"></i> ' . __( 'Create New Product', 'wc-frontend-manager-ultimate' ) . '</a>',  '<a href="' . get_wcfm_products_url() . '" class="wcfm-manage-products wcfm_listing_product_option" target="_blank"><i class="wcfmfa fa-cubes"></i> ' . __( 'Manage Products', 'wc-frontend-manager-ultimate' ) . '</a>');
			$label .= '&nbsp;&nbsp;' . $products_create_description;
		}
		return $label;
	}
	
	/**
	 * Listings - Associate Products CSS/JS - 3.2.1
	 */
	function wcfmu_add_listing_enqueue_scripts() {
		global $WCFM, $WCFMu, $post, $_GET;
		
		$job_dashboard_page = get_option( 'job_manager_job_dashboard_page_id' );
		$add_listings_page = get_option( 'job_manager_submit_job_form_page_id' );
		if( ( $add_listings_page && is_object( $post ) && ( $add_listings_page == $post->ID ) ) || ( $job_dashboard_page && is_object( $post ) && ( $job_dashboard_page == $post->ID ) && isset( $_GET['action'] ) && ( $_GET['action'] == 'edit' ) ) ) {
			
			if( WCFM_Dependencies::wcfm_products_listings_active_check() || WCFM_Dependencies::wcfm_products_mylistings_active_check() ) {
				// Load Scripts
				$WCFM->library->load_scripts( 'wcfm-products-manage' );
				wp_enqueue_script( 'wcfm_product_popup_js', $WCFM->library->js_lib_url . 'products-popup/wcfm-script-product-popup.js', array('jquery', 'wcfm_products_manage_js'), $WCFM->version, true );
				wp_enqueue_script( 'wcfm_add_listings_products_manage_js', $WCFMu->library->js_lib_url . 'integrations/wcfmu-script-add-listings-products-manage.js', array('jquery', 'wcfm_product_popup_js'), $WCFMu->version, true );
				
				// Load Styles
				$WCFM->library->load_styles( 'wcfm-products-manage' );
				wp_enqueue_style( 'wcfm_product_popup_css',  $WCFM->library->css_lib_url . 'products-popup/wcfm-style-product-popup.css', array( 'wcfm_products_manage_css' ), $WCFM->version );
				wp_enqueue_style( 'wcfm_add_listings_css', $WCFM->library->css_lib_url . 'listings/wcfm-style-listings-manage.css', array(), $WCFM->version );
			}
		}
	}
	
	/**
	 * Listings - Associate Product manager - 3.2.1
	 */
	function wcfmu_add_listing_page( $content ) {
		global $post, $WCFM;
		
		$job_dashboard_page = get_option( 'job_manager_job_dashboard_page_id' );
		$add_listings_page = get_option( 'job_manager_submit_job_form_page_id' );
		if( $add_listings_page && ( $add_listings_page == $post->ID ) ) {
			ob_start();
			$WCFM->template->get_template( 'products-popup/wcfm-view-product-popup.php' );
			$content .= '<div id="wcfm_listing_product_popup_wrapper">' . ob_get_clean() . '</div>';
		} elseif( $job_dashboard_page && ( $job_dashboard_page == $post->ID ) && isset( $_GET['action'] ) && ( $_GET['action'] == 'edit' ) ) {
			ob_start();
			$WCFM->template->get_template( 'products-popup/wcfm-view-product-popup.php' );
			$content .= '<div id="wcfm_listing_product_popup_wrapper">' . ob_get_clean() . '</div>';
		}
		
		return $content;
	}
	
	/**
   * Toolset Types - Products Type wise field group settings - 3.1.7
   */
	function wcfm_toolset_types_settings() {
		global $WCFMu;
	  $WCFMu->template->get_template( 'integrations/toolset/wcfmu-view-toolset-types-settings.php' );
	}
	
	/**
   * Toolset Types - Products Manage General options - 2.5.0
   */
  function wcfm_toolset_types_product_manage_fields( ) {
		global $WCFMu;
	  $WCFMu->template->get_template( 'integrations/toolset/wcfmu-view-toolset-types-products-manage.php' );
	}
	
	/**
   * Toolset Types - Articles Manage General options - 4.2.3
   */
  function wcfm_toolset_types_article_manage_fields( ) {
		global $WCFMu;
	  $WCFMu->template->get_template( 'integrations/toolset/wcfmu-view-toolset-types-articles-manage.php' );
	}
	
	/**
   * MapPress - Products Manage General options
   */
  function wcfm_mappress_product_manage_fields( ) {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-mappress-products-manage.php' );
	}
	
	/**
   * Toolset Types - User Profile Fields - 3.0.1
   */
  function wcfm_toolset_types_user_profile_fields() {
		global $WCFMu;
	  $WCFMu->template->get_template( 'integrations/toolset/wcfmu-view-toolset-types-user-profile.php' );
	}
	
	/**
   * Toolset Types - User Profile Fields View - 3.5.3
   */
	function wcfm_toolset_types_user_profile_fields_view( $user_id ) {
		global $WCFM, $WCFMu;
		$vendor_user = get_userdata( $user_id );
		if ( !is_object($vendor_user) ){
			$vendor_user = new stdClass();
			$vendor_user->ID = 0;
		}
		$current_user_roles = isset( $vendor_user->roles ) ? $vendor_user->roles : apply_filters( 'wcfm_allwoed_user_roles', array( 'vendor', 'dc_vendor', 'seller', 'customer', 'disable_vendor', 'wcfm_vendor' ) );
		$current_user_roles = array_values( $current_user_roles );
		$user_role = array_shift( $current_user_roles );
		include_once( WPCF_EMBEDDED_ABSPATH . '/includes/usermeta-post.php' );
		$field_groups = wpcf_admin_usermeta_get_groups_fields( );
		if( !empty( $field_groups )) {
			foreach( $field_groups as $field_group_index => $field_group ) {
				
				// User Role Based Fields
				$for_users = wpcf_admin_get_groups_showfor_by_group($field_group['id']);
				if ( count( $for_users ) != 0 ) {
					if ( !in_array( $user_role, $for_users ) ) {
						continue;
					}
				}
						
				//If Access plugin activated
				if ( function_exists( 'wpcf_access_register_caps' ) ) {
					//If user can't view own profile fields
					if ( !current_user_can( 'view_own_in_profile_' . $field_group['slug'] ) ) {
						continue;
					}
					//If user can modify current group in own profile
					if ( !current_user_can( 'modify_own_' . $field_group['slug'] ) ) {
						continue;
					}
				}
				
				if( version_compare( TYPES_VERSION, '3.0', '>=' ) || version_compare( TYPES_VERSION, '3.0.1', '>=' ) ) {
					$field_group_load = Toolset_Field_Group_User_Factory::load( $field_group['slug'] );
				} else {
					$field_group_load = Types_Field_Group_User_Factory::load( $field_group['slug'] );
				}
				if( null === $field_group_load ) continue;
				
				$wcfm_is_allowed_toolset_field_group = apply_filters( 'wcfm_is_allow_user_toolset_field_group', true, $field_group_index, $field_group, $user_id );
				if( !$wcfm_is_allowed_toolset_field_group ) continue;
				
				if ( !empty( $field_group['fields'] ) ) { 
					echo "<h2>" . $field_group['name'] . "</h2><div class=\"wcfm_clearfix\"></div>";
					
					if ( !empty( $field_group['fields'] ) ) {
						foreach( $field_group['fields'] as $field_group_field ) {
							
							$wcfm_is_allowed_toolset_field = apply_filters( 'wcfm_is_allow_user_toolset_field', true, $field_group_field, $user_id );
				  		if( !$wcfm_is_allowed_toolset_field ) continue;
							
							// Field Value
							$field_value = '';
							if( isset( $field_group_field['data'] ) && isset( $field_group_field['data']['user_default_value'] ) ) $field_value = $field_group_field['data']['user_default_value'];
							if( $user_id ) $field_value = get_user_meta( $user_id, $field_group_field['meta_key'], true );
							
							// Paceholder
							$field_paceholder = '';
							if( isset( $field_group_field['data'] ) && isset( $field_group_field['data']['placeholder'] ) ) $field_paceholder = $field_group_field['data']['placeholder'];
							
							// Is Required
							$custom_attributes = array();
							if( isset( $field_group_field['data'] ) && isset( $field_group_field['data']['validate'] ) && isset( $field_group_field['data']['validate']['required'] ) && $field_group_field['data']['validate']['required'] ) $custom_attributes = array( 'required' => 1 );
							if( isset( $field_group_field['data'] ) && isset( $field_group_field['data']['validate'] ) && isset( $field_group_field['data']['validate']['required'] ) && $field_group_field['data']['validate']['required'] && isset( $field_group_field['data']['validate']['message'] ) && $field_group_field['data']['validate']['message'] ) $custom_attributes['required_message'] = $field_group_field['data']['validate']['message'];
							
							// For Multi-line Fields
							if ( wpcf_admin_is_repetitive( $field_group_field ) ) {
								$field_value = array();
								$field_value_repetatives = (array) get_user_meta( $user_id, $field_group_field['meta_key'] );
								if( !empty( $field_value_repetatives ) ) {
									foreach( $field_value_repetatives as $field_value_repetative ) {
										$field_value[] = array( 'field' => $field_value_repetative );
									}
								}
							}
							
							// Field show befor filtr
							$wcfm_is_allowed_toolset_field_show = apply_filters( 'wcfm_is_allow_user_toolset_field_show', true, $field_group_field, $field_value, $user_id );
				  		if( !$wcfm_is_allowed_toolset_field_show ) continue;
				  		
				  		$field_type = 'text';
				  		$attributes = array();
				  		if( is_array( $field_value ) ) {
				  			$field_value_content = '';
				  			foreach( $field_value as $field_value_data ) {
				  				if( $field_group_field['type'] == 'colorpicker' ) {
				  					$field_type = 'html';
				  					$attributes = array( 'style' => 'width: 60%; padding: 5px; display: inline-block;' );
				  					$field_value_content .= '<span style="width: 15px; height: 15px; display: inline-block; margin-right: 10px; background-color: ' . implode( ',', $field_value_data ) . ';" class="text_tip" data-tip="'.implode( ',', $field_value_data ).'"></span>';
				  				} else {
										if( $field_value_content ) $field_value_content .= ', ';
										$field_value_content .= implode( ',', $field_value_data );
									}
				  			}
				  			$field_value = $field_value_content;
				  		}
				  		if( $field_group_field['type'] == 'date' ) {
				  			if($field_value) $field_value = date_i18n( wc_date_format(), $field_value ); 
				  		}
							if( !$field_value ) $field_value = '&ndash;';
							$WCFM->wcfm_fields->wcfm_generate_form_field(  array( $field_group_field['meta_key'] => array( 'label' => $field_group_field['name'], 'attributes' => $attributes, 'custom_attributes' => $custom_attributes, 'placeholder' => $field_paceholder, 'hints' => $field_group_field['description'], 'name' => 'wpcf[' . $field_group_field['meta_key'] . ']', 'type' => $field_type, 'class' => 'wcfm-text', 'label_class' => 'wcfm_title', 'value' => $field_value ) ) );
						}
					}
				}
			}
		}
	}
	
	/**
   * Toolset Types - Taxonomy Fields - 3.0.2
   */
  function wcfm_toolset_types_taxonomy_fields() {
		global $WCFMu;
	  $WCFMu->template->get_template( 'integrations/toolset/wcfmu-view-toolset-types-taxonomy.php' );
	}
	
	/**
   * ACF - Products Manage General options - 3.0.4
   */
  function wcfm_acf_product_manage_fields() {
		global $WCFMu;
	  $WCFMu->template->get_template( 'integrations/acf/wcfmu-view-acf-products-manage.php' );
	}
	
	/**
   * ACF Pro - Products Manage General options - 3.3.7
   */
  function wcfm_acf_pro_product_manage_fields() {
		global $WCFMu;
	  $WCFMu->template->get_template( 'integrations/acf/wcfmu-view-acf-pro-products-manage.php' );
	}
	
	/**
   * ACF - Articles Manage General options - 4.2.3
   */
  function wcfm_acf_article_manage_fields() {
		global $WCFMu;
	  $WCFMu->template->get_template( 'integrations/acf/wcfmu-view-acf-articles-manage.php' );
	}
	
	/**
   * ACF Pro - Articles Manage General options - 4.2.3
   */
  function wcfm_acf_pro_article_manage_fields() {
		global $WCFMu;
	  $WCFMu->template->get_template( 'integrations/acf/wcfmu-view-acf-pro-articles-manage.php' );
	}
	
	/**
   * Address Geocoder - Products Manage General options
   */
  function wcfm_address_geocoder_product_manage_fields( ) {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-address-geocoder-products-manage.php' );
	}
	
	/**
   * WC Deposits - Products Manage General options
   */
  function wcfm_wc_deposits_product_manage_fields( ) {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-deposits-products-manage.php' );
	}
	
	/**
	 * WC PDF Vouchers - Product manage Downloadable Options
	 */
	function wcfm_wc_pdf_vouchers_product_manage_downloadable_fields( $downloadable_fields, $product_id, $product_type ) {
		global $WCFMu;
		if( isset( $downloadable_fields['downloadable_files'] ) && isset( $downloadable_fields['downloadable_files']['options'] ) && isset( $downloadable_fields['downloadable_files']['options']['name'] ) && isset( $downloadable_fields['downloadable_files']['options']['name']['custom_attributes'] ) ) {
			unset( $downloadable_fields['downloadable_files']['options']['name']['custom_attributes'] );
		}
		if( isset( $downloadable_fields['downloadable_files'] ) && isset( $downloadable_fields['downloadable_files']['options'] ) && isset( $downloadable_fields['downloadable_files']['options']['file'] ) && isset( $downloadable_fields['downloadable_files']['options']['file']['custom_attributes'] ) ) {
			unset( $downloadable_fields['downloadable_files']['options']['file']['custom_attributes'] );
		}
		return $downloadable_fields;
	}
	
	/**
	 * WC PDF Vouchers - Product manage General Options
	 */
	function wcfm_wc_pdf_vouchers_product_manage_fields() {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/pdf_voucher/wcfmu-view-wc-pdf-vouchers-products-manage.php' );
	}
	
	/**
	 * WC Tabs Manager - Product manager Genaral Options
	 */
	function wcfm_wc_tabs_manager_product_manage_fields() {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-tabs-manager-products-manage.php' );
	}
	
	/**
	 * WC Warranty - Product manager Genaral Options
	 */
	function wcfm_wc_warranty_product_manage_fields() {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-warranty-products-manage.php' );
	}
	
	/**
	 * WC Waitlist - Product manager Genaral Options
	 */
	function wcfm_wc_waitlist_product_manage_fields() {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-waitlist-products-manage.php' );
	}
	
	/**
	 * WC Foo Events - Product Manager Genaral Options
	 */
	function wcfm_wc_fooevents_product_manage_fields() {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/fooevent/wcfmu-view-wc-fooevents-products-manage.php' );
	}
	
	/**
	 * WC Measurement Price Calculator - Product Manager Genaral Options
	 */
	function wcfm_wc_measurement_price_calculator_product_manage_fields() {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-measurement-price-calculator-products-manage.php' );
	}
	
	/**
	 * WC Advanced Product Labels - Product Manager Genaral Options
	 */
	function wcfm_wc_advanced_product_labels_product_manage_fields() {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-advanced-product-labels-products-manage.php' );
	}
	
	/**
	 * WooCommerce Whole Sale - Product General Fields
	 */
	function wcfm_wholesale_product_manage_fields( $pricing_fields, $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
		global $WCFM, $WCFMu;
		
		$currency_symbol = get_woocommerce_currency_symbol();
		
		$all_wholesale_roles = unserialize( get_option( WWP_OPTIONS_REGISTERED_CUSTOM_ROLES ) );
		if ( !is_array( $all_wholesale_roles ) )
			$all_wholesale_roles = array();
		
		if( !empty( $all_wholesale_roles ) ) {
			$pricing_fields['wholesale_prices_heading_1'] = array( 'type' => 'html', 'class' => 'wcfm_ele simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => '<h2>'. __( 'Wholesale Prices' , 'woocommerce-wholesale-prices' ) .'</h2><div class="wcfm-clearfix"></div>' );
			$pricing_fields['wholesale_prices_desc_1'] = array( 'type' => 'html', 'class' => 'wcfm_ele simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => '<p class="description">'. __( 'Wholesale Price for this product' , 'woocommerce-wholesale-prices' ) .'</p><div class="wcfm-clearfix"></div>' );
			
			foreach ( $all_wholesale_roles as $role_key => $role ) {
				if ( array_key_exists( 'currency_symbol' , $role ) && !empty( $role[ 'currency_symbol' ] ) )
					$currency_symbol = $role[ 'currency_symbol' ];
	
				$field_id        = $role_key . '_wholesale_price';
				$wholesale_price = '';
				
				if( $product_id ) {
					$wholesale_price = get_post_meta( $product_id , $field_id, true );
				}
				
				$field_label     = $role[ 'roleName' ] . " (" . $currency_symbol . ")";
				$field_desc      = sprintf( __( 'Only applies to users with the role of %1$s' , 'woocommerce-wholesale-prices' ) , $role[ 'roleName' ] );
				
				$pricing_fields[$field_id] = array( 'label' => __( $field_label, 'wc-frontend-manager-ultimate'), 'name' => 'wholesale_price['.$field_id.']', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $wholesale_price, 'attributes' => array( 'min' => '0.1', 'step'=> '0.1' ), 'hints' => $field_desc );
			}
		}
		
		if ( WCFMu_Dependencies::wcfm_wholesale_active_check() ) {
			
			if( !empty( $all_wholesale_roles ) ) {
				$pricing_fields['wholesale_prices_heading_2'] = array( 'type' => 'html', 'class' => 'wcfm_ele simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => '<hr /><h2>'. __( 'Wholesale Minimum Order Quantity' , 'woocommerce-wholesale-prices-premium' ) .'</h2><div class="wcfm-clearfix"></div>' );
				$pricing_fields['wholesale_prices_desc_2'] = array( 'type' => 'html', 'class' => 'wcfm_ele simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => '<br/><p class="description instructions">'. __( "Minimum number of items to be purchased in order to avail this product's wholesale price.<br/>Only applies to wholesale users.<br/><br/>Setting a step value below for the corresponding wholesale role will prevent the specific wholesale customer from adding to cart quantity of this product lower than the set minimum." , 'woocommerce-wholesale-prices-premium') .'</p><div class="wcfm-clearfix"></div>' );
				
				foreach ( $all_wholesale_roles as $role_key => $role ) {
					$field_id        = $role_key . '_wholesale_minimum_order_quantity';				
					$wholesale_price = '';
					
					if( $product_id ) {
						$wholesale_price = get_post_meta( $product_id , $field_id, true );
					}
					
					$field_desc      = sprintf( __( 'Only applies to users with the role of "%1$s"' , 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] );
					
					$pricing_fields[$field_id] = array( 'label' => __( $role[ 'roleName' ], 'wc-frontend-manager-ultimate'), 'name' => 'wholesale_minimum_order_quantity['.$field_id.']', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $wholesale_price, 'attributes' => array( 'min' => '1', 'step'=> '1' ), 'hints' => $field_desc );
				}
			}
			
			if( !empty( $all_wholesale_roles ) ) {
				$pricing_fields['wholesale_prices_heading_3'] = array( 'type' => 'html', 'class' => 'wcfm_ele simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => '<hr /><h2>'. __( 'Wholesale Order Quantity Step' , 'woocommerce-wholesale-prices-premium' ) .'</h2><div class="wcfm-clearfix"></div>' );
				$pricing_fields['wholesale_prices_desc_3'] = array( 'type' => 'html', 'class' => 'wcfm_ele simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => '<br /><p class="description instructions">'. __( "Order quantity step wholesale users are restricted to when purchasing this product.<br/>Only applies to wholesale users.<br/><br/>Minimum order quantity above for corresponding wholesale role must be set for this feature to take effect." , 'woocommerce-wholesale-prices-premium') .'</p><div class="wcfm-clearfix"></div>' );
				
				foreach ( $all_wholesale_roles as $role_key => $role ) {
					$field_id        = $role_key . '_wholesale_order_quantity_step';				
					$wholesale_price = '';
					
					if( $product_id ) {
						$wholesale_price = get_post_meta( $product_id , $field_id, true );
					}
					
					$field_desc      = sprintf( __( 'Only applies to users with the role of "%1$s"' , 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] );
					
					$pricing_fields[$field_id] = array( 'label' => __( $role[ 'roleName' ], 'wc-frontend-manager-ultimate'), 'name' => 'wholesale_order_quantity_step['.$field_id.']', 'type' => 'number', 'class' => 'wcfm-text wcfm_ele simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $wholesale_price, 'attributes' => array( 'min' => '1', 'step'=> '1' ), 'hints' => $field_desc );
				}
			
				// Quantity Based Wholesale Rule
				$pqbwp_enable = '';
				$wholesale_quantity_based_rules = array(); 
				if( $product_id ) {
					$pqbwp_enable = get_post_meta( $product_id , WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE , true );
					$wholesale_quantity_based_rules = get_post_meta( $product_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
				}
				
				$currency_symbol = " (" . get_woocommerce_currency_symbol() . ")";
				$wholesale_roles_arr = array();
				foreach ( $all_wholesale_roles as $roleKey => $role )
						$wholesale_roles_arr[ $roleKey ] = $role[ 'roleName' ];
				
				if ( !is_array( $wholesale_quantity_based_rules ) )
						$wholesale_quantity_based_rules = array();
					
				$pricing_fields['wholesale_prices_heading_4'] = array( 'type' => 'html', 'class' => 'wcfm_ele simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => '<hr /><h2>'. __( 'Product Quantity Based Wholesale Pricing' ,  'woocommerce-wholesale-prices-premium' ) .'</h2><div class="wcfm-clearfix"></div>' );
				$pricing_fields['wholesale_prices_desc_5'] = array( 'type' => 'html', 'class' => 'wcfm_ele simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => '<br /><p class="description instructions">'. __( 'Specify wholesale price for this current product depending on the quantity being purchased.<br><b>Ending Qty</b> can be left blank to apply that price for all quantities above the <b>Starting Qty.</b><br/>Only applies to the wholesale roles that you specify.' , 'woocommerce-wholesale-prices-premium' ) .'</p><div class="wcfm-clearfix"></div>' );
				$pricing_fields['pqbwp-enable'] = array( 'label' => __( 'Enable', 'wc-frontend-manager'), 'name' => 'pqbwp-enable', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title checkbox_title simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'dfvalue' => $pqbwp_enable, 'value' => 'yes', 'hints' => __( "Enable further wholesale pricing discounts based on quantity purchased?" , "woocommerce-wholesale-prices-premium" ) );
				
				$pricing_fields['wholesale_quantity_based_rules'] = array( 'label' => __( 'Rule(s)', 'wc-frontend-manager-ultimate'), 'name' => 'wholesale_quantity_based_rules', 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple wcfm_ele_hide' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $wholesale_quantity_based_rules, 'options' => array( 
																																		"wholesale_role" => array( 'label' => __( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ), 'type' => 'select', 'class' => 'wcfm-select wcfm_ele simple' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple' . ' ' . $wcfm_wpml_edit_disable_element, 'options' => $wholesale_roles_arr, 'hints' => __( 'Select wholesale role to which this rule applies.' , 'woocommerce-wholesale-prices-premium' ) ),
																																		"start_qty" => array( 'label' => __( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ), 'type' => 'number', 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele simple' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( 'Minimum order quantity required for this rule. Must be a number.' , 'woocommerce-wholesale-prices-premium' ) ),
																																		"end_qty" => array( 'label' => __( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ), 'type' => 'number', 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele simple' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( 'Maximum order quantity required for this rule. Must be a number. Leave this blank for no maximum quantity.' , 'woocommerce-wholesale-prices-premium' ) ),
																																		"price_type" => array( 'label' => __( 'Price Type' , 'woocommerce-wholesale-prices-premium' ), 'type' => 'select', 'class' => 'wcfm-select wcfm_ele simple' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple simple' . ' ' . $wcfm_wpml_edit_disable_element, 'options' => array( 'fixed-price'   => __( 'Fixed Price' , 'woocommerce-wholesale-prices-premium' ), 'percent-price' => __( 'Discount % off the wholesale price' , 'woocommerce-wholesale-prices-premium' ) ), 'hints' => __( 'Select pricing type' , 'woocommerce-wholesale-prices-premium' ) ),
																																		"wholesale_price" => array( 'label' => sprintf( __( 'Wholesale Price%1$s' , 'woocommerce-wholesale-prices-premium' ) , $currency_symbol ), 'type' => 'number', 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele simple' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_ele wcfm_title simple' . ' ' . $wcfm_wpml_edit_disable_element, 'hints' => __( '$ or the new % value off the regular price. This will be the discount value used for quantities within the given range. Please input value without comma separator.' , 'woocommerce-wholesale-prices-premium' ) ),
																																	) );
				
			}
		}
		
		return $pricing_fields;
	}
	
	/**
	 * WooCommerce Whole Sale - Product manage Variation Options
	 */
	function wcfm_wholesale_product_manage_fields_variations( $variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options ) {
		global $WCFM, $WCFMu;
		
		$currency_symbol = get_woocommerce_currency_symbol();
		
		$wholesale_fields = array();
		
		$all_wholesale_roles = unserialize( get_option( WWP_OPTIONS_REGISTERED_CUSTOM_ROLES ) );
		if ( !is_array( $all_wholesale_roles ) )
			$all_wholesale_roles = array();
		
		if( !empty( $all_wholesale_roles ) ) {
			$wholesale_fields['wholesale_prices_heading_1'] = array( 'type' => 'html', 'class' => 'wcfm_ele variable', 'value' => '<h2>'. __( 'Wholesale Prices' , 'woocommerce-wholesale-prices' ) .'</h2><div class="wcfm-clearfix"></div>' );
			//$wholesale_fields['wholesale_prices_desc_1'] = array( 'type' => 'html', 'class' => 'wcfm_ele variable', 'value' => '<p class="description">'. __( 'Wholesale Price for this product' , 'woocommerce-wholesale-prices' ) .'</p><div class="wcfm-clearfix"></div>' );
			
			foreach ( $all_wholesale_roles as $role_key => $role ) {
				if ( array_key_exists( 'currency_symbol' , $role ) && !empty( $role[ 'currency_symbol' ] ) )
					$currency_symbol = $role[ 'currency_symbol' ];
	
				$field_id        = $role_key . '_wholesale_price';
				$field_label     = $role[ 'roleName' ] . " (" . $currency_symbol . ")";
				$field_desc      = sprintf( __( 'Only applies to users with the role of %1$s' , 'woocommerce-wholesale-prices' ) , $role[ 'roleName' ] );
				
				$wholesale_fields[$field_id] = array( 'label' => __( $field_label, 'wc-frontend-manager-ultimate'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele variable', 'label_class' => 'wcfm_ele wcfm_title variable', 'attributes' => array( 'min' => '0.1', 'step'=> '0.1' ), 'hints' => $field_desc );
			}
		}
		
		if ( WCFMu_Dependencies::wcfm_wholesale_active_check() ) {
			
			if( !empty( $all_wholesale_roles ) ) {
				$wholesale_fields['wholesale_prices_heading_2'] = array( 'type' => 'html', 'class' => 'wcfm_ele variable', 'value' => '<hr /><h2>'. __( 'Wholesale Minimum Order Quantity' , 'woocommerce-wholesale-prices-premium' ) .'</h2><div class="wcfm-clearfix"></div>' );
				//$wholesale_fields['wholesale_prices_desc_2'] = array( 'type' => 'html', 'class' => 'wcfm_ele variable', 'value' => '<p class="description">'. __( "Minimum number of items to be purchased in order to avail this product's wholesale price.<br/>Only applies to wholesale users.<br/><br/>Setting a step value below for the corresponding wholesale role will prevent the specific wholesale customer from adding to cart quantity of this product lower than the set minimum." , 'woocommerce-wholesale-prices-premium') .'</p><div class="wcfm-clearfix"></div>' );
				
				foreach ( $all_wholesale_roles as $role_key => $role ) {
					$field_id        = $role_key . '_wholesale_minimum_order_quantity';				
					$field_desc      = sprintf( __( 'Only applies to users with the role of "%1$s"' , 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] );
					$wholesale_fields[$field_id] = array( 'label' => __( $role[ 'roleName' ], 'wc-frontend-manager-ultimate'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele variable', 'label_class' => 'wcfm_ele wcfm_title variable', 'attributes' => array( 'min' => '1', 'step'=> '1' ), 'hints' => $field_desc );
				}
			}
			
			if( !empty( $all_wholesale_roles ) ) {
				$wholesale_fields['wholesale_prices_heading_3'] = array( 'type' => 'html', 'class' => 'wcfm_ele variable', 'value' => '<hr /><h2>'. __( 'Wholesale Order Quantity Step' , 'woocommerce-wholesale-prices-premium' ) .'</h2><div class="wcfm-clearfix"></div>' );
				//$wholesale_fields['wholesale_prices_desc_3'] = array( 'type' => 'html', 'class' => 'wcfm_ele variable', 'value' => '<p class="description">'. __( "Order quantity step wholesale users are restricted to when purchasing this product.<br/>Only applies to wholesale users.<br/><br/>Minimum order quantity above for corresponding wholesale role must be set for this feature to take effect." , 'woocommerce-wholesale-prices-premium') .'</p><div class="wcfm-clearfix"></div>' );
				
				foreach ( $all_wholesale_roles as $role_key => $role ) {
					$field_id        = $role_key . '_wholesale_order_quantity_step';				
					$field_desc      = sprintf( __( 'Only applies to users with the role of "%1$s"' , 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] );
					$wholesale_fields[$field_id] = array( 'label' => __( $role[ 'roleName' ], 'wc-frontend-manager-ultimate'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele variable', 'label_class' => 'wcfm_ele wcfm_title variable', 'attributes' => array( 'min' => '1', 'step'=> '1' ), 'hints' => $field_desc );
				}
				
				// Quantity Based Wholesale Rule
				$currency_symbol = " (" . get_woocommerce_currency_symbol() . ")";
				$wholesale_roles_arr = array();
				foreach ( $all_wholesale_roles as $roleKey => $role )
						$wholesale_roles_arr[ $roleKey ] = $role[ 'roleName' ];
				
				$wholesale_fields['wholesale_prices_heading_4'] = array( 'type' => 'html', 'class' => 'wcfm_ele variable', 'value' => '<hr /><h2>'. __( 'Product Quantity Based Wholesale Pricing' ,  'woocommerce-wholesale-prices-premium' ) .'</h2><div class="wcfm-clearfix"></div>' );
				$wholesale_fields['wholesale_prices_desc_5'] = array( 'type' => 'html', 'class' => 'wcfm_ele variable', 'value' => '<br /><p class="description instructions">'. __( 'Specify wholesale price for this current product depending on the quantity being purchased.<br><b>Ending Qty</b> can be left blank to apply that price for all quantities above the <b>Starting Qty.</b><br/>Only applies to the wholesale roles that you specify.' , 'woocommerce-wholesale-prices-premium' ) .'</p><div class="wcfm-clearfix"></div>' );
				$wholesale_fields['pqbwp-enable'] = array( 'label' => __( 'Enable', 'wc-frontend-manager'), 'name' => 'pqbwp-enable', 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele variable wholesale_quantity_based_rules_enable', 'label_class' => 'wcfm_ele wcfm_title checkbox_title variable', 'value' => 'yes', 'hints' => __( "Enable further wholesale pricing discounts based on quantity purchased?" , "woocommerce-wholesale-prices-premium" ) );
				$wholesale_fields['wholesale_prices_desc_break_5'] = array( 'type' => 'html' );
				
				$wholesale_fields['wholesale_quantity_based_rules'] = array( 'label' => __( 'Rule(s)', 'wc-frontend-manager-ultimate'), 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele variable wholesale_quantity_based_rules', 'label_class' => 'wcfm_ele wcfm_title variable wholesale_quantity_based_rules', 'options' => array( 
																																		"wholesale_role" => array( 'label' => __( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ), 'type' => 'select', 'class' => 'wcfm-select wcfm_ele variable', 'label_class' => 'wcfm_ele wcfm_title variable', 'options' => $wholesale_roles_arr, 'hints' => __( 'Select wholesale role to which this rule applies.' , 'woocommerce-wholesale-prices-premium' ) ),
																																		"start_qty" => array( 'label' => __( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ), 'type' => 'number', 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele variable', 'label_class' => 'wcfm_ele wcfm_title variable', 'hints' => __( 'Minimum order quantity required for this rule. Must be a number.' , 'woocommerce-wholesale-prices-premium' ) ),
																																		"end_qty" => array( 'label' => __( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ), 'type' => 'number', 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele variable', 'label_class' => 'wcfm_ele wcfm_title variable', 'hints' => __( 'Maximum order quantity required for this rule. Must be a number. Leave this blank for no maximum quantity.' , 'woocommerce-wholesale-prices-premium' ) ),
																																		"price_type" => array( 'label' => __( 'Price Type' , 'woocommerce-wholesale-prices-premium' ), 'type' => 'select', 'class' => 'wcfm-select wcfm_ele variable', 'label_class' => 'wcfm_ele wcfm_title simple variable', 'options' => array( 'fixed-price'   => __( 'Fixed Price' , 'woocommerce-wholesale-prices-premium' ), 'percent-price' => __( 'Discount % off the wholesale price' , 'woocommerce-wholesale-prices-premium' ) ), 'hints' => __( 'Select pricing type' , 'woocommerce-wholesale-prices-premium' ) ),
																																		"wholesale_price" => array( 'label' => sprintf( __( 'Wholesale Price%1$s' , 'woocommerce-wholesale-prices-premium' ) , $currency_symbol ), 'type' => 'number', 'class' => 'wcfm-text wcfm_non_negative_input wcfm_ele variable', 'label_class' => 'wcfm_ele wcfm_title variable', 'hints' => __( '$ or the new % value off the regular price. This will be the discount value used for quantities within the given range. Please input value without comma separator.' , 'woocommerce-wholesale-prices-premium' ) ),
																																	) );
			}
			
		}
		
		$variation_fileds = array_slice($variation_fileds, 0, 12, true) +
																	$wholesale_fields +
																	array_slice($variation_fileds, 12, count($variation_fileds) - 1, true) ;
		
		return $variation_fileds;
	}
	
	/**
	 * WooCommerce Whole Sale - Product manage Variation Data
	 */
	function wcfm_wholesale_data_variations( $variations, $variation_id, $variation_id_key ) {
		global $WCFM, $WCFMmp;
		
		if( $variation_id  ) {
			
			$all_wholesale_roles = unserialize( get_option( WWP_OPTIONS_REGISTERED_CUSTOM_ROLES ) );
			if ( !is_array( $all_wholesale_roles ) )
				$all_wholesale_roles = array();
			
			if( !empty( $all_wholesale_roles ) ) {
				foreach ( $all_wholesale_roles as $role_key => $role ) {
					$field_id        = $role_key . '_wholesale_price';
					$wholesale_price = get_post_meta( $variation_id, $field_id, true );
					$variations[$variation_id_key][$field_id] = $wholesale_price;
				}
			}
			
			if ( WCFMu_Dependencies::wcfm_wholesale_active_check() ) {
				
				if( !empty( $all_wholesale_roles ) ) {
					foreach ( $all_wholesale_roles as $role_key => $role ) {
						$field_id        = $role_key . '_wholesale_minimum_order_quantity';				
						$wholesale_minimum_order_quantity = get_post_meta( $variation_id, $field_id, true );
						$variations[$variation_id_key][$field_id] = $wholesale_minimum_order_quantity;
					}
				}
				
				if( !empty( $all_wholesale_roles ) ) {
					foreach ( $all_wholesale_roles as $role_key => $role ) {
						$field_id        = $role_key . '_wholesale_order_quantity_step';				
						$wholesale_order_quantity_step = get_post_meta( $variation_id, $field_id, true );
						$variations[$variation_id_key][$field_id] = $wholesale_order_quantity_step;
					}
					
					$pqbwp_enable = get_post_meta( $variation_id , WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE , true );
					$wholesale_quantity_based_rules = get_post_meta( $variation_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
					
					$variations[$variation_id_key]['pqbwp-enable'] = $pqbwp_enable;
					$variations[$variation_id_key]['wholesale_quantity_based_rules'] = $wholesale_quantity_based_rules;
				}
			}
		}
		
		return $variations;
	}
	
	/**
	 * WooCommerce Product Badge - Product Manager Genaral Options
	 */
	function wcfm_wc_product_badge_product_manage_fields() {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-product-badge-products-manage.php' );
	}
	
	/**
	 * WooCommerce Min/Max Quantities - Product Manager Genaral Options
	 */
	function wcfm_wc_min_max_quantities_product_manage_fields() {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-min-max-quantities-products-manage.php' );
	}
	
	/**
	 * WC Min/Max Quantities - Product manage Variation Options
	 */
	function wcfm_wc_min_max_quantities_product_manage_fields_variations( $variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options ) {
		global $WCFM, $WCFMu;
		
		$min_max_fields = array(
			                      'wc_min_max_quantities_heading_1' => array( 'type' => 'html', 'class' => 'wcfm_ele variable', 'value' => '<h2>'. __( 'Min/Max Quantities' , 'wc-frontend-manager-ultimate' ) .'</h2><div class="wcfm-clearfix"></div>' ),
			                     
			                      "min_max_rules" => array( 'label' => __( 'Min/Max Rules', 'woocommerce-min-max-quantities' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele variable', 'label_class' => 'wcfm_title wcfm_ele variable checkbox_title', 'value' =>'yes', 'hints' => __( 'Enable this option to override min/max settings at variation level', 'woocommerce-min-max-quantities' ) ),
			                      'wc_min_max_quantities_break_1' => array( 'type' => 'html', 'class' => 'wcfm_ele variable', 'value' => '<div class="wcfm-clearfix"></div>' ),
			                      
			                      "variation_minimum_allowed_quantity" => array( 'label' => __( 'Minimum quantity', 'woocommerce-min-max-quantities' ) , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_half_ele  variable', 'label_class' => 'wcfm_title wcfm_ele wcfm_half_ele_title variable',  'hints' => __( 'Enter a quantity to prevent the user buying this product if they have fewer than the allowed quantity in their cart', 'woocommerce-min-max-quantities' ), 'attributes' => array( 'min' => 0, 'step' => 1 ) ),
														"variation_maximum_allowed_quantity" => array( 'label' => __( 'Maximum quantity', 'woocommerce-min-max-quantities' ) , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_half_ele  variable', 'label_class' => 'wcfm_title wcfm_ele wcfm_half_ele_title variable', 'hints' => __( 'Enter a quantity to prevent the user buying this product if they have more than the allowed quantity in their cart', 'woocommerce-min-max-quantities' ), 'attributes' => array( 'min' => 0, 'step' => 1 ) ),
														"variation_group_of_quantity" => array( 'label' => __( 'Group of...', 'woocommerce-min-max-quantities' ) , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_half_ele  variable', 'label_class' => 'wcfm_title wcfm_ele wcfm_half_ele_title variable', 'hints' => __( 'Enter a quantity to only allow this product to be purchased in groups of X', 'woocommerce-min-max-quantities' ), 'attributes' => array( 'min' => 0, 'step' => 1 ) ),
														
														"variation_minmax_do_not_count" => array( 'label' => __( 'Order rules: Do not count', 'woocommerce-min-max-quantities' ), 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele variable', 'label_class' => 'wcfm_title wcfm_ele variable checkbox_title', 'value' =>'yes', 'hints' => __( 'Don\'t count this product against your minimum order quantity/value rules.', 'woocommerce-min-max-quantities' ) ),
														"variation_minmax_cart_exclude" => array( 'label' => __( 'Order rules: Exclude', 'woocommerce-min-max-quantities' ) , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele variable', 'label_class' => 'wcfm_title wcfm_ele variable checkbox_title', 'value' =>'yes', 'hints' => __( 'Exclude this product from minimum order quantity/value rules. If this is the only item in the cart, rules will not apply.', 'woocommerce-min-max-quantities' ) ),
														"variation_minmax_category_group_of_exclude" => array( 'label' => __( 'Category rules: Exclude', 'woocommerce-min-max-quantities' ) , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele variable', 'label_class' => 'wcfm_title wcfm_ele variable checkbox_title', 'value' =>'yes', 'hints' => __( 'Exclude this product from category group-of-quantity rules. This product will not be counted towards category groups.', 'woocommerce-min-max-quantities' ) ),
			                     );
			
		
		$variation_fileds = array_merge( $variation_fileds, $min_max_fields );
		
		return $variation_fileds;
	}
	
	/**
	 * WC Min/Max Quantities - Product manage Variation Data
	 */
	function wcfm_wc_min_max_quantities_data_variations( $variations, $variation_id, $variation_id_key ) {
		global $WCFM, $WCFMu;
		
		if( $variation_id  ) {
			
			$label_fields = array(
				'variation_minimum_allowed_quantity',
				'variation_maximum_allowed_quantity',
				'variation_group_of_quantity',
				'variation_minmax_do_not_count',
				'variation_minmax_cart_exclude',
				'variation_minmax_category_group_of_exclude',
				'min_max_rules'
			);
			
			foreach ( $label_fields as $field_id ) {
				$variations[$variation_id_key][$field_id] = get_post_meta( $variation_id, $field_id, true );
			}
		}
		
		return $variations;
	}
	
	/**
	 * WC 360 Images - Product Manager Fields
	 */
	function wcfm_product_manager_wc_360_images_fields( $product_id ) {
		global $WCFM, $WCFMu;
		
		$wc360_enable = '';
		if( $product_id ) {
			$wc360_enable = get_post_meta( $product_id, 'wc360_enable', true );
		}
		$WCFM->wcfm_fields->wcfm_generate_form_field( array(
			                      'wc_360_images_heading' => array( 'type' => 'html', 'class' => 'wcfm_full_ele ', 'value' => '<h2>'. __( '360° Images' , 'wc-frontend-manager-ultimate' ) .'</h2><div class="wcfm-clearfix"></div>' ),
			                     
														"wcfm_enable_360_images" => array( 'label' => __( 'Enable', 'woocommerce-min-max-quantities' ) , 'type' => 'checkbox', 'class' => 'wcfm-checkbox', 'label_class' => 'wcfm_title checkbox_title', 'value' =>'yes', 'dfvalue' => $wc360_enable ),
			                     ) );
	}
	
	/**
	 * WC Variation Swatch Plugin's view
	 */
	function wcfm_wc_variaton_swatch_product_manage_views( $product_id, $product_type ) {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-variation-swatch-products-manage.php' );
	}
	
	/**
	 * WC Quotation Plugin's view
	 */
	function wcfm_wc_quotation_product_manage_views( $product_id, $product_type ) {
		global $WCFM, $WCFMu, $wp_roles;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-quotation-products-manage.php' );
	}
	
	/**
	 * WC Quotation Order Status Label Display
	 */
	function wcfm_wc_quotation_order_status_label_display( $label, $the_order ) {
		$order_status = sanitize_title( $the_order->get_status() );
		if( in_array( $order_status, apply_filters( 'wcfm_status_label_display_order_status', array( 'request', 'proposal', 'proposal-sent', 'proposal-expired', 'proposal-rejected', 'proposal-canceled', 'proposal-accepted' ) ) ) ) {
			$label .= '<br />' . wc_get_order_status_name( $order_status );
		}
		return $label;
	}
	
	/**
	 * WC Quotation Order Form Process
	 */
	function wcfm_wc_quotation_order_quotation_process() {
		global $WCFM, $WCFMu, $WCFMmp;
		
		$order_id = '';
		if ( isset( $_REQUEST["wc_quotation_order_id"] ) ) {
			$order_id = absint($_REQUEST["wc_quotation_order_id"]);
		}
		
		if( !$order_id ) return;
		
		if ( isset( $_REQUEST["send_proposal"] ) ) {
			//Since there is a bug with infinite loop wiht update_status, we force the post variable
			$order_status = 'wc-proposal-sent';
		}

		if ( isset( $_REQUEST["create_proposal"] ) ) {
			$order_status = 'wc-proposal';
		}

		if ( isset( $_REQUEST["accept_proposal"] ) ) {
			$order_status = 'wc-proposal-accepted';
		}

		if ( isset( $_REQUEST["reject_proposal"] ) ) {
			$order_status = 'wc-proposal-rejected';
		}

		if ( isset( $_POST['_validity_date'] ) ) {
			$validity_date = strtotime( $_POST['_validity_date'] . ' ' . (int)$_POST['_validity_date_hour'] . ':' . (int)$_POST['_validity_date_minute'] . ':00' );

			update_post_meta( $order_id, '_validity_date', date_i18n( 'Y-m-d H:i:s', $validity_date ) );
		}

		if ( isset( $_POST['_reminder_date'] ) ) {
			$reminder_date = strtotime( $_POST['_reminder_date'] . ' ' . (int)$_POST['_reminder_date_hour'] . ':' . (int)$_POST['_reminder_date_minute'] . ':00' );

			update_post_meta( $order_id, '_reminder_date', date_i18n( 'Y-m-d H:i:s', $reminder_date ) );
		}

		if ( isset( $_POST['_adq_additional_info'] ) ) {
			update_post_meta( $order_id, '_adq_additional_info', nl2br( $_POST['_adq_additional_info'] ) );
		}

		if ( isset( $_POST['wc_quotation_attached_files'] ) && count( $_POST['wc_quotation_attached_files'] ) > 0 ) {
			update_post_meta( $order_id, '_attached_files', $_POST['wc_quotation_attached_files'] );
		}
		
  	if ( wc_is_order_status( $order_status ) && $order_id ) {
  		
  		do_action( 'before_wcfm_order_status_update', $order_id, $order_status );
  		
			$order = wc_get_order( $order_id );
			$order->update_status( str_replace('wc-', '', $order_status), '', true );
			
			// Add Order Note for Log
			$user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
			$shop_name =  get_user_by( 'ID', $user_id )->display_name;
			if( wcfm_is_vendor() ) {
				$shop_name =  $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor( absint($user_id) );
			}
			$wcfm_messages = sprintf( __( 'Order status updated to <b>%s</b> by <b>%s</b>', 'wc-frontend-manager' ), wc_get_order_status_name( str_replace('wc-', '', $order_status) ), $shop_name );
			$is_customer_note = apply_filters( 'wcfm_is_allow_order_update_note_for_customer', '1' );
			
			if( wcfm_is_vendor() ) add_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
			$comment_id = $order->add_order_note( $wcfm_messages, $is_customer_note);
			if( wcfm_is_vendor() ) { add_comment_meta( $comment_id, '_vendor_id', $user_id ); }
			if( wcfm_is_vendor() ) remove_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
			
			$wcfm_messages = sprintf( __( '<b>%s</b> order status updated to <b>%s</b> by <b>%s</b>', 'wc-frontend-manager' ), '#<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url($order_id) . '">' . $order->get_order_number() . '</a>', wc_get_order_status_name( str_replace('wc-', '', $order_status) ), $shop_name );
			$WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'status-update' );
			
			do_action( 'woocommerce_order_edit_status', $order_id, str_replace('wc-', '', $order_status) );
			do_action( 'wcfm_order_status_updated', $order_id, str_replace('wc-', '', $order_status) );
			
		}
	}
	
	/**
	 * WooCommerce Dynamic Pricing - Product Manager Genaral Options
	 */
	function wcfm_wc_dynamic_pricing_product_manage_fields() {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-dynamic-pricing-products-manage.php' );
	}
	
	/**
	 * MSRP for WooCommerce - Product Manager General Options
	 */
	function wcfm_msrp_for_wc_product_manage_fields( $general_fields, $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
		global $WCFM;
		
		$alg_msrp_price = '';
		
		$alg_msrp_options = $alg_msrp_by_country_values = $alg_msrp_by_currency_values = array();
		
		// MSRP by country
		$alg_msrp_options['is_msrp_by_country_enabled']    = ( 'yes' === apply_filters( 'alg_wc_msrp_option', 'no', 'msrp_by_country_enabled' ) );
		$alg_msrp_options['msrp_countries']                = ( $alg_msrp_options['is_msrp_by_country_enabled'] ? get_option( 'alg_wc_msrp_countries', '' ) : '' );
		if ( ! empty( $alg_msrp_options['msrp_countries'] ) ) {
			$alg_msrp_options['msrp_countries_currencies'] = get_option( 'alg_wc_msrp_countries_currencies', '' );
			$alg_msrp_options['default_wc_country']        = get_option( 'woocommerce_default_country' );
		}
		// MSRP by currency
		$alg_msrp_options['is_msrp_by_currency_enabled'] = ( 'yes' === apply_filters( 'alg_wc_msrp_option', 'no', 'msrp_by_currency_enabled' ) );
		$alg_msrp_options['msrp_currencies']             = ( $alg_msrp_options['is_msrp_by_currency_enabled'] ? get_option( 'alg_wc_msrp_currencies', '' ) : '' );
		
		if( $product_id ) {
			$alg_msrp_price = get_post_meta( $product_id, '_alg_msrp', true );
			$alg_msrp_by_country_values = get_post_meta( $product_id, '_alg_msrp_by_country', true );
			$alg_msrp_by_currency_values = get_post_meta( $product_id, '_alg_msrp_by_currency', true );
		}
		
		$msrp_pricing_fields = array(  
																"_alg_msrp" => array('label' => __('MSRP Price', 'msrp-for-woocommerce') . '(' . get_woocommerce_currency_symbol() . ')' , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_ele_hide simple external non-variable-subscription wcfm_non_negative_input' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title wcfm_ele wcfm_ele_hide simple external non-variable-subscription' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => $alg_msrp_price )
											          );
		
		if ( ! empty( $alg_msrp_options['msrp_countries'] ) ) {
			foreach ( $alg_msrp_options['msrp_countries'] as $country_code ) {
				$currency = ( isset( $alg_msrp_options['msrp_countries_currencies'][ $country_code ] ) ? $alg_msrp_options['msrp_countries_currencies'][ $country_code ] : '' );
				$value    = ( isset( $alg_msrp_by_country_values[ $country_code ] ) ? $alg_msrp_by_country_values[ $country_code ] : '' );
				$msrp_pricing_fields['_alg_msrp_by_country_' . $country_code] = array(
																																							'name'        => '_alg_msrp_by_country[' . $country_code . ']',
																																							'value'       => $value,
																																							'type'        => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_ele_hide simple external non-variable-subscription wcfm_non_negative_input' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title wcfm_ele wcfm_ele_hide simple external non-variable-subscription' . ' ' . $wcfm_wpml_edit_disable_element,
																																							'label'       => __( 'MSRP', 'msrp-for-woocommerce' ) . ' [' . $country_code . '] (' . get_woocommerce_currency_symbol( $currency ) . ')',
																																						  );
			}
		}
		if ( ! empty( $alg_msrp_options['msrp_currencies'] ) ) {
			foreach ( $alg_msrp_options['msrp_currencies'] as $currency ) {
				$value = ( isset( $alg_msrp_by_currency_values[ $currency ] ) ? $alg_msrp_by_currency_values[ $currency ] : '' );
				$msrp_pricing_fields['_alg_msrp_by_currency_' . $currency] = array(
																																					'name'        => '_alg_msrp_by_currency[' . $currency . ']',
																																					'value'       => $value,
																																					'type'        => 'number', 'class' => 'wcfm-text wcfm_ele wcfm_ele_hide simple external non-variable-subscription wcfm_non_negative_input' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title wcfm_ele wcfm_ele_hide simple external non-variable-subscription' . ' ' . $wcfm_wpml_edit_disable_element,
																																					'label'       => __( 'MSRP', 'msrp-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol( $currency ) . ')',
																																				);
			}
		}
		
		
		
		$msrp_pricing_fields = apply_filters( 'product_manage_fields_msrp_pricing', $msrp_pricing_fields , $product_id, $product_type, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element );
		
		$general_fields = array_merge( $general_fields, $msrp_pricing_fields );
		
		return $general_fields;
	}
	
	/**
	 * MSRP for WooCommerce - Product manage Variation Options
	 */
	function wcfm_msrp_for_wc_product_manage_fields_variations( $variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options ) {
		global $WCFM, $WCFMu;
		
		// MSRP by country
		$alg_msrp_options['is_msrp_by_country_enabled']    = ( 'yes' === apply_filters( 'alg_wc_msrp_option', 'no', 'msrp_by_country_enabled' ) );
		$alg_msrp_options['msrp_countries']                = ( $alg_msrp_options['is_msrp_by_country_enabled'] ? get_option( 'alg_wc_msrp_countries', '' ) : '' );
		if ( ! empty( $alg_msrp_options['msrp_countries'] ) ) {
			$alg_msrp_options['msrp_countries_currencies'] = get_option( 'alg_wc_msrp_countries_currencies', '' );
			$alg_msrp_options['default_wc_country']        = get_option( 'woocommerce_default_country' );
		}
		// MSRP by currency
		$alg_msrp_options['is_msrp_by_currency_enabled'] = ( 'yes' === apply_filters( 'alg_wc_msrp_option', 'no', 'msrp_by_currency_enabled' ) );
		$alg_msrp_options['msrp_currencies']             = ( $alg_msrp_options['is_msrp_by_currency_enabled'] ? get_option( 'alg_wc_msrp_currencies', '' ) : '' );
		
		$msrp_pricing_fields = array(  
																"_alg_msrp" => array('label' => __('MSRP Price', 'msrp-for-woocommerce') . '(' . get_woocommerce_currency_symbol() . ')' , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele variable wcfm_non_negative_input', 'label_class' => 'wcfm_title wcfm_ele variable' )
											          );
		
		if ( ! empty( $alg_msrp_options['msrp_countries'] ) ) {
			foreach ( $alg_msrp_options['msrp_countries'] as $country_code ) {
				$currency = ( isset( $alg_msrp_options['msrp_countries_currencies'][ $country_code ] ) ? $alg_msrp_options['msrp_countries_currencies'][ $country_code ] : '' );
				$msrp_pricing_fields['_alg_msrp_by_country_' . $country_code] = array(
																																							'name'        => '_alg_msrp_by_country[' . $country_code . ']',
																																							'type'        => 'number', 'class' => 'wcfm-text wcfm_ele variable wcfm_non_negative_input', 'label_class' => 'wcfm_title wcfm_ele variable',
																																							'label'       => __( 'MSRP', 'msrp-for-woocommerce' ) . ' [' . $country_code . '] (' . get_woocommerce_currency_symbol( $currency ) . ')',
																																						  );
			}
		}
		if ( ! empty( $alg_msrp_options['msrp_currencies'] ) ) {
			foreach ( $alg_msrp_options['msrp_currencies'] as $currency ) {
				$msrp_pricing_fields['_alg_msrp_by_currency_' . $currency] = array(
																																					'name'        => '_alg_msrp_by_currency[' . $currency . ']',
																																					'type'        => 'number', 'class' => 'wcfm-text wcfm_ele variable wcfm_non_negative_input', 'label_class' => 'wcfm_title wcfm_ele variable',
																																					'label'       => __( 'MSRP', 'msrp-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol( $currency ) . ')',
																																				);
			}
		}
		
		$variation_fileds = array_slice($variation_fileds, 0, 12, true) +
																	$msrp_pricing_fields +
																	array_slice($variation_fileds, 12, count($variation_fileds) - 1, true) ;
		
		return $variation_fileds;
		
	}
	
	/**
	 * MSRP for WooCommerce - Product manage Variation Data
	 */
	function wcfm_msrp_for_wc_data_variations( $variations, $variation_id, $variation_id_key ) {
		global $WCFM, $WCFMmp;
		
		$alg_msrp_by_country_values = $alg_msrp_by_currency_values = array();
		
		// MSRP by country
		$alg_msrp_options['is_msrp_by_country_enabled']    = ( 'yes' === apply_filters( 'alg_wc_msrp_option', 'no', 'msrp_by_country_enabled' ) );
		$alg_msrp_options['msrp_countries']                = ( $alg_msrp_options['is_msrp_by_country_enabled'] ? get_option( 'alg_wc_msrp_countries', '' ) : '' );
		if ( ! empty( $alg_msrp_options['msrp_countries'] ) ) {
			$alg_msrp_options['msrp_countries_currencies'] = get_option( 'alg_wc_msrp_countries_currencies', '' );
			$alg_msrp_options['default_wc_country']        = get_option( 'woocommerce_default_country' );
		}
		// MSRP by currency
		$alg_msrp_options['is_msrp_by_currency_enabled'] = ( 'yes' === apply_filters( 'alg_wc_msrp_option', 'no', 'msrp_by_currency_enabled' ) );
		$alg_msrp_options['msrp_currencies']             = ( $alg_msrp_options['is_msrp_by_currency_enabled'] ? get_option( 'alg_wc_msrp_currencies', '' ) : '' );
		
		if( $variation_id  ) {
			$alg_msrp_price = get_post_meta( $variation_id, '_alg_msrp', true );
			$variations[$variation_id_key]['_alg_msrp'] = $alg_msrp_price;
				
			$alg_msrp_by_country_values = get_post_meta( $variation_id, '_alg_msrp_by_country', true );
			$alg_msrp_by_currency_values = get_post_meta( $variation_id, '_alg_msrp_by_currency', true );
			
			if ( ! empty( $alg_msrp_options['msrp_countries'] ) ) {
				foreach ( $alg_msrp_options['msrp_countries'] as $country_code ) {
					$currency = ( isset( $alg_msrp_options['msrp_countries_currencies'][ $country_code ] ) ? $alg_msrp_options['msrp_countries_currencies'][ $country_code ] : '' );
					$value    = ( isset( $alg_msrp_by_country_values[ $country_code ] ) ? $alg_msrp_by_country_values[ $country_code ] : '' );  
					$variations[$variation_id_key]['_alg_msrp_by_country_' . $country_code] = $value;
				}
			}
			
			if ( ! empty( $alg_msrp_options['msrp_currencies'] ) ) {
				foreach ( $alg_msrp_options['msrp_currencies'] as $currency ) {
					$value = ( isset( $alg_msrp_by_currency_values[ $currency ] ) ? $alg_msrp_by_currency_values[ $currency ] : '' );
					$variations[$variation_id_key]['_alg_msrp_by_currency_' . $currency] = $value;
				}
			}
		}
		
		return $variations;
	}
	
	/**
	 * Cost of Goods for WooCommerce - Product Manager General Options
	 */
	function wcfm_wc_cost_of_goods_product_manage_fields( $general_fields, $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
		global $WCFM;
		
		$alg_wc_cog_cost = '';
		$description = '';
		
		if( $product_id ) {
			$alg_wc_cog_cost = get_post_meta( $product_id, '_alg_wc_cog_cost', true );
			$description = alg_wc_cog()->core->get_product_profit_html( $product_id );
		}
		
		$wc_cog_fields = array(  
														"_alg_wc_cog_cost" => array('label' => __( 'Cost', 'cost-of-goods-for-woocommerce' ) .
																																	' (' . __( 'excl. tax', 'cost-of-goods-for-woocommerce' ) . ')' . ' (' . get_woocommerce_currency_symbol() . ')', 
																																	'type' => 'number', 
																																	'class' => 'wcfm-text wcfm_ele wcfm_ele_hide simple external non-variable-subscription wcfm_non_negative_input' . ' ' . $wcfm_wpml_edit_disable_element, 'label_class' => 'wcfm_title wcfm_ele wcfm_ele_hide simple external non-variable-subscription' . ' ' . $wcfm_wpml_edit_disable_element,
																																	'desc' => $description,
																																	'desc_class' => 'wcfm_page_options_desc' . ' ' . $wcfm_wpml_edit_disable_element,
																																	'value' => $alg_wc_cog_cost )
														);
		
		$wc_cog_fields = apply_filters( 'product_manage_fields_wc_cost_of_goods', $wc_cog_fields , $product_id, $product_type, $wcfm_is_translated_product, $wcfm_wpml_edit_disable_element );
		
		$general_fields = array_merge( $general_fields, $wc_cog_fields );
		
		return $general_fields;
	}
	
	/**
	 * Cost of Goods for WooCommerce - Product manage Variation Options
	 */
	function wcfm_wc_cost_of_goods_product_manage_fields_variations( $variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options ) {
		global $WCFM, $WCFMu;
		
		$wc_cog_fields = array(  
													"_alg_wc_cog_cost" => array('label' => __( 'Cost', 'cost-of-goods-for-woocommerce' ) .
																																' (' . __( 'excl. tax', 'cost-of-goods-for-woocommerce' ) . ')' . ' (' . get_woocommerce_currency_symbol() . ')', 
																																'type' => 'number', 
																																'class' => 'wcfm-text wcfm_ele variable wcfm_non_negative_input', 'label_class' => 'wcfm_title wcfm_ele variable',
																																//'desc' => sprintf( __( 'Profit: %s', 'cost-of-goods-for-woocommerce' ), $this->get_product_profit_html( $product_id ) ),
																																)
													);
		
		$variation_fileds = array_slice($variation_fileds, 0, 12, true) +
																	$wc_cog_fields +
																	array_slice($variation_fileds, 12, count($variation_fileds) - 1, true) ;
		
		return $variation_fileds;
		
	}
	
	/**
	 * Cost of Goods for WooCommerce - Product manage Variation Data
	 */
	function wcfm_wc_cost_of_goods_data_variations( $variations, $variation_id, $variation_id_key ) {
		global $WCFM, $WCFMmp;
		
		if( $variation_id  ) {
			$alg_wc_cog_cost = get_post_meta( $variation_id, '_alg_wc_cog_cost', true );
			$variations[$variation_id_key]['_alg_wc_cog_cost'] = $alg_wc_cog_cost;
		}
		
		return $variations;
	}
	
	/**
	 * WC PDF Vouchers - Product manage Variation Options
	 */
	function wcfm_wc_pdf_vouchers_product_manage_fields_variations( $variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options ) {
		global $WCFM, $WCFMu, $woo_vou_voucher;
		
		$voucher_options 	= array( '' => __( 'Select a PDF template.', 'woovoucher' ) );
		$multiple_voucher_options = array();
		$voucher_data 		= woo_vou_get_vouchers();
		foreach ( $voucher_data as $voucher ) {
			if( isset( $voucher['ID'] ) && !empty( $voucher['ID'] ) ) { // Check voucher id is not empty
				$voucher_options[$voucher['ID']] = $voucher['post_title'];
				$multiple_voucher_options[$voucher['ID']] = $voucher['post_title'];
			}
		}
		
		$voucher_delivery_opt 	= array(
																		'default' 	=> __( 'Default', 'woovoucher' ), 
																		'email' 	=> __( 'Email', 'woovoucher' ), 
																		'offline' 	=> __( 'Offline', 'woovoucher' )
																	);
		
		
		$wcfmu_variation_fields = array(
																		"_woo_vou_variable_pdf_template" => array('label' => __('PDF Template', 'woovoucher') , 'type' => 'select', 'options' => $multiple_voucher_options, 'class' => 'wcfm-select wcfm_ele wcfm_half_ele variable variable-subscription variation_downloadable_ele', 'label_class' => 'wcfm_title wcfm_ele wcfm_half_ele_title variable variable-subscription variation_downloadable_ele'),
																		"_woo_vou_variable_voucher_delivery" => array('label' => __('Voucher Delivery', 'woovoucher') , 'type' => 'select', 'options' => $voucher_delivery_opt, 'class' => 'wcfm-select wcfm_ele wcfm_half_ele variable variable-subscription variation_downloadable_ele', 'label_class' => 'wcfm_title wcfm_ele wcfm_half_ele_title variable variable-subscription variation_downloadable_ele'),
																		"_woo_vou_variable_codes" => array('label' => __('Voucher Codes', 'woovoucher') , 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele variable variable-subscription variation_downloadable_ele', 'label_class' => 'wcfm_title wcfm_ele wcfm_full_ele variable variable-subscription variation_downloadable_ele', 'hints' => __( 'If you have a list of Voucher Codes you can copy and paste them in to this option. Make sure, that they are comma separated.', 'woovoucher' ) ),
																		"_woo_vou_variable_vendor_address" => array('label' => __('Vendor Address', 'woovoucher') , 'type' => 'textarea', 'class' => 'wcfm-textarea wcfm_ele wcfm_full_ele variable variable-subscription variation_downloadable_ele', 'label_class' => 'wcfm_title wcfm_ele wcfm_full_ele variable variable-subscription variation_downloadable_ele', 'hints' => __( 'Here you can enter the complete Vendor\'s address. This will be displayed on the PDF document sent to the customers so that they know where to redeem this Voucher. Limited HTML is allowed.', 'woovoucher' ) ),
																		);
		$variation_fileds = array_merge( $variation_fileds, $wcfmu_variation_fields );
		
		return $variation_fileds;
	}
	
	/**
	 * WC PDF Vouchers - Generate Voucher code form HTML
	 */
	function wcfm_generate_voucher_code_html() {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/pdf_voucher/wcfmu-view-wc-pdf-vouchers-generate-codes.php' );
		die;
	}
	
	/**
   * Product Manage WooCommerce Box Office Fields - General
   */
	function wcfm_wc_box_office_product_manage_fields_general( $general_fields, $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
		global $WCFM;
		
		$_ticket = ( get_post_meta( $product_id, '_ticket', true) == 'yes' ) ? 'yes' : '';
		
		$general_fields = array_slice($general_fields, 0, 1, true) + 
													array(
														"_ticket" => array( 'desc' => __( 'Ticket', 'woocommerce-box-office') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele wcfm_half_ele_checkbox simple variable non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'desc_class' => 'wcfm_title wcfm_ele virtual_ele_title checkbox_title simple variable non-variable-subscription non-job_package non-resume_package non-auction non-redq_rental non-appointment non-accommodation-booking' . ' ' . $wcfm_wpml_edit_disable_element, 'value' => 'yes', 'dfvalue' => $_ticket),
														) +
											array_slice($general_fields, 1, count($general_fields) - 1, true) ;
		
		return $general_fields;
	}
	
	/**
   * WooCommerce Box Office - Products Manage General options
   */
  function wcfm_wc_box_office_product_manage_fields( ) {
		global $WCFMu;
		$WCFMu->template->get_template( 'integrations/wcfmu-view-wc-box-office-products-manage.php' );
	}
	
	/**
	 * WC Rental Pro Product Inventory Manage - 2.4.3
	 */
	function wcfm_wcrental_product_inventory_manage( $stock_fields, $product_id, $product_type ) {
		global $WCFM, $WCFMu;
		$redq_inventory_products = array();
		
		$inventory_taxonomies = array( 'rnb_categories' => __( 'Categories', 'wc-frontend-manager-ultimate' ), 'pickup_location' => __( 'Pickup Location', 'wc-frontend-manager-ultimate' ), 'dropoff_location' => __( 'Dropoff Location', 'wc-frontend-manager-ultimate' ), 'resource' => __( 'Resource', 'wc-frontend-manager-ultimate' ), 'person' => __( 'Person', 'wc-frontend-manager-ultimate' ), 'deposite' => __( 'Deposit', 'wc-frontend-manager-ultimate' ), 'attributes' => __( 'Attributes', 'wc-frontend-manager-ultimate' ), 'features' => __( 'Features', 'wc-frontend-manager-ultimate' ) );
		$inventory_taxonomie_elements = array(); 
		$inventory_taxonomie_elements['unique_name'] = array( 'label' => __('Unique product model', 'redq-rental'), 'placeholder' => __('Unique product model', 'redq-rental'), 'type' => 'text', 'class' => 'wcfm-text wcfm_ele redq_rental redq_rental_unique_name', 'label_class' => 'wcfm_title redq_rental redq_rental_unique_name', 'hints' => __( 'Hourly price will be applicabe if booking or rental days min 1day', 'redq-rental' ) );
		foreach( $inventory_taxonomies as $inventory_taxonomy => $inventory_taxonomy_label ) {
			if( !apply_filters( 'wcfm_is_allow_redq_rental_taxonomy', true, $inventory_taxonomy ) ) continue;
			//$inventory_taxonomy_terms   = get_terms( $inventory_taxonomy, array( 'hide_empty' => false ) );
			$inventory_taxonomy_terms = new WP_Term_Query( array( 'taxonomy' => $inventory_taxonomy, 'hide_empty' => false ) );
			$inventory_taxonomy_options = array(); // '' => __('Set', 'redq-rental') . ' ' . str_replace( '_', ' ',  str_replace( 'rnb_', '', $inventory_taxonomy ) ) );
			if ( ! empty( $inventory_taxonomy_terms->terms ) ) {
				foreach( $inventory_taxonomy_terms->terms as $inventory_taxonomy_term ) {
					$inventory_taxonomy_options[$inventory_taxonomy_term->slug] = $inventory_taxonomy_term->name;
				}
			}
			$inventory_taxonomie_elements[$inventory_taxonomy] = array( 'label' => $inventory_taxonomy_label, 'custom_attributes' => array( 'placeholder' => __('Set', 'redq-rental') . ' ' . $inventory_taxonomy_label ), 'attributes' => array( 'multiple' => 'multiple', 'style' => 'width: 60%;' ), 'type' => 'select', 'options' => $inventory_taxonomy_options, 'class' => 'wcfm-select wcfm_ele redq_rental', 'label_class' => 'wcfm_title redq_rental' );
		}
		$inventory_taxonomie_elements['inventory_id'] = array( 'type' => 'hidden' );
		
		$redq_rental_availability = array();
		
		// Stored Inventory Values
		if( $product_id ) {
			$redq_inventory_products = array();
			$resource_identifier = get_post_meta( $product_id, 'resource_identifier', true );
			$redq_inventory_child_ids = get_post_meta( $product_id, 'inventory_child_posts', true );
			$redq_inventory_unique_names = get_post_meta( $product_id, 'redq_inventory_products_quique_models', true );
			if( !empty( $redq_inventory_child_ids ) ) {
				foreach( $redq_inventory_child_ids as $inventory_index => $redq_inventory_child_id ) {
					$redq_inventory_products[$inventory_index]['inventory_id'] = $redq_inventory_child_id;
					$redq_inventory_products[$inventory_index]['unique_name'] = isset( $redq_inventory_unique_names[$inventory_index] ) ? $redq_inventory_unique_names[$inventory_index] : '';
					// Taxonomies
					foreach( $inventory_taxonomies as $inventory_taxonomy => $inventory_taxonomy_label ) {
						$inventory_taxonomy_values = get_the_terms( $redq_inventory_child_id, $inventory_taxonomy );
						if( !empty( $inventory_taxonomy_values ) && !is_wp_error( $inventory_taxonomy_values ) ) {
							foreach( $inventory_taxonomy_values as $inventory_taxonomy_value ) {
								$redq_inventory_products[$inventory_index][$inventory_taxonomy][] = $inventory_taxonomy_value->slug;
							}
						}
					}
					$redq_inventory_products[$inventory_index]['redq_rental_availability'] = (array) get_post_meta( $redq_inventory_child_id, 'redq_rental_availability', true );
				}
			}
		}
		
		$inventory_taxonomie_elements["redq_rental_availability"] = apply_filters( 'wcfm_redq_rental_fields_availability', array( 'label' => __('Product Availabilities', 'wc-frontend-manager') , 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele redq_rental_availability redq_rental', 'label_class' => 'wcfm_title redq_rental', 'desc' => __( 'Please select the date range to be disabled for the product.', 'wc-frontend-manager' ), 'desc_class' => 'avail_rules_desc', 'value' => $redq_rental_availability, 'options' => array(
																																					"type" => array('label' => __('Type', 'wc-frontend-manager'), 'type' => 'select', 'options' => array( 'custom_date' => __( 'Custom Date', 'wc-frontend-manager' )), 'class' => 'wcfm-select wcfm_ele wcfm_half_ele redq_rental', 'label_class' => 'wcfm_title wcfm_half_ele_title redq_rental' ),
																																					"from" => array('label' => __('From', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_datepicker wcfm_half_ele', 'label_class' => 'wcfm_title wcfm_half_ele_title' ),
																																					"to" => array('label' => __('To', 'wc-frontend-manager'), 'type' => 'text', 'class' => 'wcfm-text wcfm_datepicker wcfm_half_ele', 'label_class' => 'wcfm_title wcfm_half_ele_title' ),
																																					"rentable" => array('label' => __('Bookable', 'wc-frontend-manager'), 'type' => 'select', 'options' => array( 'no' => __('NO', 'wc-frontend-manager') ), 'class' => 'wcfm-select wcfm_ele wcfm_half_ele redq_rental', 'label_class' => 'wcfm_title wcfm_half_ele_title' ),
																																					)	) );
		
		$inventory_taxonomie_multy_elements = apply_filters( 'wcfm_redq_rental_fields_inventory', array( "redq_inventory_products" => array('label' => __('Inventory management', 'redq-rental') , 'type' => 'multiinput', 'class' => 'wcfm-text wcfm_ele redq_rental', 'label_class' => 'wcfm_title wcfm_ele redq_rental', 'value' => $redq_inventory_products, 'options' => $inventory_taxonomie_elements ) ) );
		
		$stock_fields = array_merge( $stock_fields, $inventory_taxonomie_multy_elements );
		
		return $stock_fields;
	}
	
	function wcfm_wcrental_pro_hidden_order_itemmeta( $hidden_metas ) {
		
		$hidden_metas[] = 'pickup_hidden_datetime';
		$hidden_metas[] = 'return_hidden_datetime';
		$hidden_metas[] = 'return_hidden_days';
		
		return $hidden_metas; 
	}
	
	/**
   * Handle Rental Request Quote Details Status Update
   */
  public function wcfm_modify_rental_quote_status() {
  	global $WCFM, $WCFMu;
  	
  	$quote_id     = $_POST['quote_id'];
  	$quote_status = $_POST['quote_status'];
  	$quote_price  = $_POST['quote_price'];
  	
  	$post_id      = $quote_id;
  	$post         = get_post( $post_id );
  	
  	if( isset($_POST['quote_status']) && ( $_POST['quote_status'] !== $post->post_status ) ) {
  		$my_post = array(
					'ID'           => $quote_id,
					'post_status'  => $quote_status,
			);
			wp_update_post( $my_post );
  
      // send email

      $form_data = json_decode( get_post_meta($post_id, 'order_quote_meta', true), true );



      $from_name = '';
      $from_email = '';
      $from_phone = '';
      $product_id = '';
      $to_email = '';
      $to_author_id = '';


      $message_from_sender_html = '';

      foreach ($form_data as $key => $meta) {
        /**
         * Get the post author_id, author_email, prodct_id
         */
        if( isset( $meta['name'] ) && $meta['name'] === 'add-to-cart' ) {
          $product_id = $meta['value'];
          $to_author_id = get_post_field( 'post_author', $product_id );
          $to_email = get_the_author_meta( 'user_email', $to_author_id);
        }

        /**
         * Get the customer name, email, phone, message
         */
        else if( isset( $meta['forms'] ) ) {
          $forms = $meta['forms'];
          foreach ($forms as $k => $v) {
            $message_from_sender_html .= "<p>".$k . " : " . $v . "</p>";
            if($k === 'email') {
              $from_email = $v;
            }
            if($k === 'name') {
              $from_name = $v;
            }

          }
        }
      }

      switch ($quote_status) {
        case 'quote-accepted':
          // send email to the customer

          $prodct_id = get_post_meta( $post->ID, 'add-to-cart', true );
          $from_author_id = get_post_field( 'post_author', $prodct_id );
          $from_email = get_the_author_meta( 'user_email', $from_author_id);
          $from_name = get_the_author_meta( 'user_nicename', $from_author_id);

          // To info
          $to_author_id = get_post_field( 'post_author', $post->ID );
          $to_email = get_the_author_meta( 'user_email', $to_author_id);

          $subject = __( "Congratulations! Your quote request has been accepted", 'wc-frontend-manager-ultimate' );;
          // $reply_message = $_POST['add-quote-message'];
          $data_object = array(
            // 'reply_message' => $reply_message,
            'quote_id'         => $quote_id,
          );

          // Send the mail to the customer
          $email = new RnB_Email();
          $email->quote_accepted_notify_customer( $to_email, $subject, $from_email, $from_name, $data_object );
          break;

        default:
          // send email to the customer

          $prodct_id = get_post_meta( $post->ID, 'add-to-cart', true );
          $from_author_id = get_post_field( 'post_author', $prodct_id );
          $from_email = get_the_author_meta( 'user_email', $from_author_id);
          $from_name = get_the_author_meta( 'user_nicename', $from_author_id);

          // To info
          $to_author_id = get_post_field( 'post_author', $post->ID );
          $to_email = get_the_author_meta( 'user_email', $to_author_id);

          $subject = __( "Your quote request status has been updated", 'wc-frontend-manager-ultimate' );
          // $reply_message = $_POST['add-quote-message'];
          $data_object = array(
            // 'reply_message' => $reply_message,
            'quote_id'         => $quote_id,
          );

          // Send the mail to the customer
          $email = new RnB_Email();
          $email->quote_status_update_notify_customer( $to_email, $subject, $from_email, $from_name, $data_object );
          break;
      }

    }

    if ( isset( $_POST['quote_price'] ) ) {
      update_post_meta($post_id, '_quote_price', $quote_price);
    }
    
    echo '{"status": true, "message": "'. __( 'Quote request status has been updated.', 'wc-frontend-manager-ultimate' ) .'"}';
  	
		die;
  }
  
  /**
   * Send Quote Request Message
   */
  function wcfm_rental_quote_message() {
  	global $WCFM, $WCFMu;
  	
  	if( isset( $_POST['quote_id'] ) ) {
			$quote_id     = $_POST['quote_id'];
			
			$post_id      = $quote_id;
			$post         = get_post( $post_id );
			
			global $current_user;

      $time = current_time('mysql');
      
      if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				//check ip from share internet
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				//to check ip is pass from proxy
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}

      $data = array(
        'comment_post_ID' => $post->ID,
        'comment_author' => $current_user->user_nicename,
        'comment_author_email' => $current_user->user_email,
        'comment_author_url' => $current_user->user_url,
        'comment_content' => $_POST['note'],
        'comment_type' => 'quote_message',
        'comment_parent' => 0,
        'user_id' => $current_user->ID,
        'comment_author_IP' => $ip,
        'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
        'comment_date' => $time,
        'comment_approved' => 1,
      );

      $comment_id = wp_insert_comment($data);


      // send email to the customer
      $prodct_id = get_post_meta( $post->ID, 'add-to-cart', true );
      $from_author_id = get_post_field( 'post_author', $prodct_id );
      $from_email = get_the_author_meta( 'user_email', $from_author_id);
      $from_name = get_the_author_meta( 'user_nicename', $from_author_id);

      // To info
      $to_author_id = get_post_field( 'post_author', $post->ID );
      $to_email = get_the_author_meta( 'user_email', $to_author_id);

      $quote_id = $post->ID;

      $subject = __( "New reply for your quote request", 'wc-frontend-manager-ultimate' );
      $reply_message = $_POST['note'];
      $data_object = array(
        'reply_message' => $reply_message,
        'quote_id'      => $quote_id,
      );

      // Send the mail to the customer
      $email = new RnB_Email();
      $email->owner_reply_message( $to_email, $subject, $from_email, $from_name, $data_object );
			
		}
  	
  	die;
  }
  
  /**
	* Format array for the datepicker
	*
	* WordPress stores the locale information in an array with a alphanumeric index, and
	* the datepicker wants a numerical index. This function replaces the index with a number
	*/
	private function _strip_array_indices( $ArrayToStrip ) {
		foreach( $ArrayToStrip as $objArrayItem) {
			$NewArray[] =  $objArrayItem;
		}
		return( $NewArray );
	}
}