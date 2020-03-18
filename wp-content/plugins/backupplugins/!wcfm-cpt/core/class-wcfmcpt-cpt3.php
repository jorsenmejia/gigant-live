<?php
/**
 * WCFM plugin core
 *
 * Plugin CPT 1 Support Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmcpt/core
 * @version   1.0.0
 */
 
class WCFM_CPT3 {

	public function __construct() {
		global $WCFM;
		
		if ( !is_admin() || defined('DOING_AJAX') ) {
			if( $is_allow_cpt3 = apply_filters( 'wcfm_is_allow_cpt3', true ) ) {
				// WC Cpt3 Query Var Filter
				add_filter( 'wcfm_query_vars', array( &$this, 'cpt3_wcfm_query_vars' ), 20 );
				add_filter( 'wcfm_endpoint_title', array( &$this, 'cpt3_wcfm_endpoint_title' ), 20, 2 );
				add_action( 'init', array( &$this, 'cpt3_wcfm_init' ), 20 );
				
				// WCFM Cpt3 Endpoint Edit
				add_filter( 'wcfm_endpoints_slug', array( $this, 'wcfm_cpt3_endpoints_slug' ) );
				
				// WCFM CPT3 Manage Dependency Map
				add_filter( 'wcfm_menu_dependancy_map', array( $this, 'wcfm_cpt3_manage_dependency_map' ) );
				
				// WCFM CPT3 Exclude Product Popup
				add_filter( 'wcfm_blocked_product_popup_views', array( $this, 'wcfm_cpt3_blocked_product_popup_views' ) );
					
				// WC Cpt3 Menu Filter
				add_filter( 'wcfm_menus', array( &$this, 'cpt3_wcfm_menus' ), 20 );
				
				// Cpt3 Load WCFMu Scripts
				add_action( 'wcfm_load_scripts', array( &$this, 'wcfm_cpt3_load_scripts' ), 30 );
				
				// Cpt3 Load WCFMu Styles
				add_action( 'wcfm_load_styles', array( &$this, 'wcfm_cpt3_load_styles' ), 30 );
				
				// Cpt3 Load WCFMu views
				add_action( 'wcfm_load_views', array( &$this, 'wcfm_cpt3_load_views' ), 30 );
				
				// Cpt3 Ajax Controllers
				add_action( 'after_wcfm_ajax_controller', array( &$this, 'wcfm_cpt3_ajax_controller' ), 30 );
				
				// Cpt3 Delete
				add_action( 'wp_ajax_delete_wcfm_cpt3', array( &$this, 'delete_wcfm_cpt3' ) );
				
				// Cpt3 Capability Options 
				add_action( 'wcfm_capability_settings_product', array( &$this, 'wcfm_cpt3_capability_settings' ), 50 );
			}
		}
	}
	
	/**
   * WCFM Cpt3 Query Var
   */
  function cpt3_wcfm_query_vars( $query_vars ) {
  	$wcfm_modified_endpoints = (array) get_option( 'wcfm_endpoints' );
  	
		$query_cpt3_vars = array(
			'wcfm-cpt3'                 => ! empty( $wcfm_modified_endpoints['wcfm-cpt3'] ) ? $wcfm_modified_endpoints['wcfm-cpt3'] : WCFM_CPT_3,
			'wcfm-cpt3-manage'          => ! empty( $wcfm_modified_endpoints['wcfm-cpt3-manage'] ) ? $wcfm_modified_endpoints['wcfm-cpt3-manage'] : WCFM_CPT_3.'-manage',
		);
		
		$query_vars = array_merge( $query_vars, $query_cpt3_vars );
		
		return $query_vars;
  }
  
  /**
   * WCFM Cpt3 End Point Title
   */
  function cpt3_wcfm_endpoint_title( $title, $endpoint ) {
  	global $wp;
  	switch ( $endpoint ) {
  		case 'wcfm-cpt3' :
				$title = __( 'Cpt3 Dashboard', 'wc-frontend-manager' );
			break;
			case 'wcfm-cpt3-manage' :
				$title = __( 'Cpt3 Manager', 'wc-frontend-manager' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * WCFM Cpt3 Endpoint Intialize
   */
  function cpt3_wcfm_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if( !get_option( 'wcfm_updated_end_point_wcfm_cpt3' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_wcfm_cpt3', 1 );
		}
  }
  
  /**
	 * WCFM Cpt3 Endpoiint Edit
	 */
  function wcfm_cpt3_endpoints_slug( $endpoints ) {
		
		$cpt3_endpoints = array(
													'wcfm-cpt3'  		      => WCFM_CPT_3,
													'wcfm-cpt3-manage'  	=> WCFM_CPT_3.'-manage',
													);
		$endpoints = array_merge( $endpoints, $cpt3_endpoints );
		
		return $endpoints;
	}
	
	/**
   * CPT 1 manage menu dependency mapping
   */
  function wcfm_cpt3_manage_dependency_map( $mappings ) {
  	$mappings['wcfm-cpt3-manage'] = 'wcfm-cpt3'; 
  	return $mappings;
  }
	
	/**
	 * BLock Product Popup Views
	 */
	function wcfm_cpt3_blocked_product_popup_views( $views ) {
		$views[] = 'wcfm-cpt3-manage';
		return $views;
	}
  
  /**
   * WCFM Cpt3 Menu
   */
  function cpt3_wcfm_menus( $menus ) {
  	global $WCFM;
  	
		$cpt3_menus = array( 'wcfm-cpt3' => array(   'label'  => WCFM_CPT_3_LABEL,
																												 'url'       => get_wcfm_cpt3_url(),
																												 'icon'      => 'codepen',
																												 'has_new'    => 'yes',
																												 'new_class'  => 'wcfm_sub_menu_items_cpt3_manage',
																												 'new_url'    => get_wcfm_cpt3_manage_url(),
																												 'capability' => 'wcfm_cpt3_menu',
																												 'submenu_capability' => 'wcfm_add_new_cpt3_sub_menu',
																												 'priority'  => 4
																												) );
		
		$menus = array_merge( $menus, $cpt3_menus );
  	return $menus;
  }
  
  /**
   * Cpt3 Scripts
   */
  public function wcfm_cpt3_load_scripts( $end_point ) {
	  global $WCFM, $WCFMcpt;
    
	  switch( $end_point ) {
	  	case 'wcfm-cpt3':
      	$WCFM->library->load_datatable_lib();
      	$WCFM->library->load_select2_lib();
	    	wp_enqueue_script( 'wcfm_cpt3_js', $WCFMcpt->plugin_url . 'js/cpt3/wcfm-script-cpt3.js', array('jquery', 'dataTables_js'), $WCFM->version, true );
	    	
	    	// Screen manager
	    	$wcfm_screen_manager = get_option( 'wcfm_screen_manager', array() );
	    	$wcfm_screen_manager_data = array();
	    	if( isset( $wcfm_screen_manager['cpt3'] ) ) $wcfm_screen_manager_data = $wcfm_screen_manager['cpt3'];
	    	if( !isset( $wcfm_screen_manager_data['admin'] ) ) {
					$wcfm_screen_manager_data['admin'] = $wcfm_screen_manager_data;
					$wcfm_screen_manager_data['vendor'] = $wcfm_screen_manager_data;
				}
				if( wcfm_is_vendor() ) {
					$wcfm_screen_manager_data = $wcfm_screen_manager_data['vendor'];
					$wcfm_screen_manager_data[5] = 'yes';
				} else {
					$wcfm_screen_manager_data = $wcfm_screen_manager_data['admin'];
				}
				$wcfm_screen_manager_data[3] = 'yes';
	    	wp_localize_script( 'wcfm_cpt3_js', 'wcfm_cpt3_screen_manage', $wcfm_screen_manager_data );
	    	
	    	// Localized Script
        $wcfm_messages = get_wcfm_cpt_manager_messages();
			  wp_localize_script( 'wcfm_cpt3_js', 'wcfm_cpt3_manage_messages', $wcfm_messages );
      break;
      
      case 'wcfm-cpt3-manage':
      	$WCFM->library->load_upload_lib();
      	$WCFM->library->load_select2_lib();
      	$WCFM->library->load_collapsible_lib();
	  		wp_enqueue_script( 'wcfm_cpt3_manage_js', $WCFMcpt->plugin_url . 'js/cpt3/wcfm-script-cpt3-manage.js', array('jquery'), $WCFM->version, true );
	  		
	  		// Localized Script
        $wcfm_messages = get_wcfm_cpt_manager_messages();
			  wp_localize_script( 'wcfm_cpt3_manage_js', 'wcfm_cpt3_manage_messages', $wcfm_messages );
	  	break;
	  }
	}
	
	/**
   * Cpt3 Styles
   */
	public function wcfm_cpt3_load_styles( $end_point ) {
	  global $WCFM, $WCFMcpt;
		
	  switch( $end_point ) {
	    case 'wcfm-cpt3':
	    	wp_enqueue_style( 'wcfm_cpt3_css',  $WCFMcpt->plugin_url . 'css/cpt3/wcfm-style-cpt3.css', array(), $WCFM->version );
		  break;
		  
		  case 'wcfm-cpt3-manage':
		  	wp_enqueue_style( 'collapsible_css',  $WCFM->library->css_lib_url . 'wcfm-style-collapsible.css', array(), $WCFM->version );
	    	wp_enqueue_style( 'wcfm_cpt3_manage_css',  $WCFMcpt->plugin_url . 'css/cpt3/wcfm-style-cpt3-manage.css', array(), $WCFM->version );
		  break;
	  }
	}
	
	/**
   * Cpt3 Views
   */
  public function wcfm_cpt3_load_views( $end_point ) {
	  global $WCFM, $WCFMcpt;
	  
	  switch( $end_point ) {
	  	case 'wcfm-cpt3':
        include_once( $WCFMcpt->plugin_path . 'views/cpt3/wcfm-view-cpt3.php' );
      break;
      
      case 'wcfm-cpt3-manage':
        include_once( $WCFMcpt->plugin_path . 'views/cpt3/wcfm-view-cpt3-manage.php' );
      break;
	  }
	}
	
	/**
   * Cpt3 Ajax Controllers
   */
  public function wcfm_cpt3_ajax_controller() {
  	global $WCFM, $WCFMcpt;
  	
  	$controllers_path = $WCFMcpt->plugin_path . 'controllers/cpt3/';
  	
  	$controller = '';
  	if( isset( $_POST['controller'] ) ) {
  		$controller = $_POST['controller'];
  		
  		switch( $controller ) {
  			case 'wcfm-cpt3':
					include_once( $controllers_path . 'wcfm-controller-cpt3.php' );
					new WCFM_Cpt3_Controller();
				break;
				
				case 'wcfm-cpt3-manage':
					include_once( $controllers_path . 'wcfm-controller-cpt3-manage.php' );
					new WCFM_Cpt3_Manage_Controller();
				break;
  		}
  	}
  }
  
  /**
   * Handle Cpt3 Delete
   */
  public function delete_wcfm_cpt3() {
  	global $WCFM;
  	
  	$cpt3id = $_POST['cpt3id'];
		
		if( $cpt3id ) {
			do_action( 'wcfm_before_cpt3_delete', $cpt3id );
			if( apply_filters( 'wcfm_is_allow_cpt3_delete' , false ) ) {
				if(wp_delete_post($cpt3id)) {
					echo 'success';
					die;
				}
			} else {
				if(wp_trash_post($cpt3id)) {
					echo 'success';
					die;
				}
			}
			die;
		}
  }
  
  /**
   * CPT3 Capability Settings 
   */
  function wcfm_cpt3_capability_settings( $wcfm_capability_options ) {
  	global $WCFM, $WCFMcpt;
  	
  	// CPT3 Capabilities
		$submit_cpt3 = ( isset( $wcfm_capability_options['submit_cpt3'] ) ) ? $wcfm_capability_options['submit_cpt3'] : 'no';
		$add_cpt3 = ( isset( $wcfm_capability_options['add_cpt3'] ) ) ? $wcfm_capability_options['add_cpt3'] : 'no';
		$publish_cpt3 = ( isset( $wcfm_capability_options['publish_cpt3'] ) ) ? $wcfm_capability_options['publish_cpt3'] : 'no';
		$edit_live_cpt3 = ( isset( $wcfm_capability_options['edit_live_cpt3'] ) ) ? $wcfm_capability_options['edit_live_cpt3'] : 'no';
		$publish_live_cpt3 = ( isset( $wcfm_capability_options['publish_live_cpt3'] ) ) ? $wcfm_capability_options['publish_live_cpt3'] : 'no';
		$delete_cpt3 = ( isset( $wcfm_capability_options['delete_cpt3'] ) ) ? $wcfm_capability_options['delete_cpt3'] : 'no';
	
  	?>
  	
  	<div class="vendor_capability_sub_heading"><h3><?php _e( WCFM_CPT_3_LABEL, 'wc-frontend-manager' ); ?></h3></div>
									
		<?php
		$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_cpt3', array("submit_cpt3" => array('label' => __('Manage', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[submit_cpt3]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $submit_cpt3),
																																																							 "add_cpt3" => array('label' => __('Add', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[add_cpt3]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $add_cpt3),
																																																							 "publish_cpt3" => array('label' => __('Publish', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[publish_cpt3]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $publish_cpt3),
																																																							 "edit_live_cpt3" => array('label' => __('Edit Live', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[edit_live_cpt3]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $edit_live_cpt3),
																																																							 "publish_live_cpt3" => array('label' => __('Auto Publish Live', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[publish_live_cpt3]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $publish_live_cpt3),
																																																							 "delete_cpt3" => array('label' => __('Delete', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[delete_cpt3]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $delete_cpt3)
																							) ) );
	}
}