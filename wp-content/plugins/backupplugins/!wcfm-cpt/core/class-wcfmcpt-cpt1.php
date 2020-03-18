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
 
class WCFM_CPT1 {

	public function __construct() {
		global $WCFM;
		
		if ( !is_admin() || defined('DOING_AJAX') ) {
			if( $is_allow_cpt1 = apply_filters( 'wcfm_is_allow_cpt1', true ) ) {
				// WC Cpt1 Query Var Filter
				add_filter( 'wcfm_query_vars', array( &$this, 'cpt1_wcfm_query_vars' ), 20 );
				add_filter( 'wcfm_endpoint_title', array( &$this, 'cpt1_wcfm_endpoint_title' ), 20, 2 );
				add_action( 'init', array( &$this, 'cpt1_wcfm_init' ), 20 );
				
				// WCFM Cpt1 Endpoint Edit
				add_filter( 'wcfm_endpoints_slug', array( $this, 'wcfm_cpt1_endpoints_slug' ) );
				
				// WCFM CPT1 Manage Dependency Map
				add_filter( 'wcfm_menu_dependancy_map', array( $this, 'wcfm_cpt1_manage_dependency_map' ) );
				
				// WCFM CPT1 Exclude Product Popup
				add_filter( 'wcfm_blocked_product_popup_views', array( $this, 'wcfm_cpt1_blocked_product_popup_views' ) );
					
				// WC Cpt1 Menu Filter
				add_filter( 'wcfm_menus', array( &$this, 'cpt1_wcfm_menus' ), 20 );
				
				// Cpt1 Load WCFMu Scripts
				add_action( 'wcfm_load_scripts', array( &$this, 'wcfm_cpt1_load_scripts' ), 30 );
				
				// Cpt1 Load WCFMu Styles
				add_action( 'wcfm_load_styles', array( &$this, 'wcfm_cpt1_load_styles' ), 30 );
				
				// Cpt1 Load WCFMu views
				add_action( 'wcfm_load_views', array( &$this, 'wcfm_cpt1_load_views' ), 30 );
				
				// Cpt1 Ajax Controllers
				add_action( 'after_wcfm_ajax_controller', array( &$this, 'wcfm_cpt1_ajax_controller' ), 30 );
				
				// Cpt1 Delete
				add_action( 'wp_ajax_delete_wcfm_cpt1', array( &$this, 'delete_wcfm_cpt1' ) );
				
				// Cpt1 Capability Options 
				add_action( 'wcfm_capability_settings_product', array( &$this, 'wcfm_cpt1_capability_settings' ), 50 );
			}
		}
	}
	
	/**
   * WCFM Cpt1 Query Var
   */
  function cpt1_wcfm_query_vars( $query_vars ) {
  	$wcfm_modified_endpoints = (array) get_option( 'wcfm_endpoints' );
  	
		$query_cpt1_vars = array(
			'wcfm-cpt1'                 => ! empty( $wcfm_modified_endpoints['wcfm-cpt1'] ) ? $wcfm_modified_endpoints['wcfm-cpt1'] : WCFM_CPT_1,
			'wcfm-cpt1-manage'          => ! empty( $wcfm_modified_endpoints['wcfm-cpt1-manage'] ) ? $wcfm_modified_endpoints['wcfm-cpt1-manage'] : WCFM_CPT_1.'-manage',
		);
		
		$query_vars = array_merge( $query_vars, $query_cpt1_vars );
		
		return $query_vars;
  }
  
  /**
   * WCFM Cpt1 End Point Title
   */
  function cpt1_wcfm_endpoint_title( $title, $endpoint ) {
  	global $wp;
  	switch ( $endpoint ) {
  		case 'wcfm-cpt1' :
				$title = __( 'Cpt1 Dashboard', 'wc-frontend-manager' );
			break;
			case 'wcfm-cpt1-manage' :
				$title = __( 'Cpt1 Manager', 'wc-frontend-manager' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * WCFM Cpt1 Endpoint Intialize
   */
  function cpt1_wcfm_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if( !get_option( 'wcfm_updated_end_point_wcfm_cpt1' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_wcfm_cpt1', 1 );
		}
  }
  
  /**
	 * WCFM Cpt1 Endpoiint Edit
	 */
  function wcfm_cpt1_endpoints_slug( $endpoints ) {
		
		$cpt1_endpoints = array(
													'wcfm-cpt1'  		      => WCFM_CPT_1,
													'wcfm-cpt1-manage'  	=> WCFM_CPT_1.'-manage',
													);
		$endpoints = array_merge( $endpoints, $cpt1_endpoints );
		
		return $endpoints;
	}
	
	/**
   * CPT 1 manage menu dependency mapping
   */
  function wcfm_cpt1_manage_dependency_map( $mappings ) {
  	$mappings['wcfm-cpt1-manage'] = 'wcfm-cpt1'; 
  	return $mappings;
  }
	
	/**
	 * BLock Product Popup Views
	 */
	function wcfm_cpt1_blocked_product_popup_views( $views ) {
		$views[] = 'wcfm-cpt1-manage';
		return $views;
	}
  
  /**
   * WCFM Cpt1 Menu
   */
  function cpt1_wcfm_menus( $menus ) {
  	global $WCFM;
  	
		$cpt1_menus = array( 'wcfm-cpt1' => array(   'label'  => WCFM_CPT_1_LABEL,
																												 'url'       => get_wcfm_cpt1_url(),
																												 'icon'      => 'codepen',
																												 'has_new'    => 'yes',
																												 'new_class'  => 'wcfm_sub_menu_items_cpt1_manage',
																												 'new_url'    => get_wcfm_cpt1_manage_url(),
																												 'capability' => 'wcfm_cpt1_menu',
																												 'submenu_capability' => 'wcfm_add_new_cpt1_sub_menu',
																												 'priority'  => 4
																												) );
		
		$menus = array_merge( $menus, $cpt1_menus );
  	return $menus;
  }
  
  /**
   * Cpt1 Scripts
   */
  public function wcfm_cpt1_load_scripts( $end_point ) {
	  global $WCFM, $WCFMcpt;
    
	  switch( $end_point ) {
	  	case 'wcfm-cpt1':
      	$WCFM->library->load_datatable_lib();
      	$WCFM->library->load_select2_lib();
	    	wp_enqueue_script( 'wcfm_cpt1_js', $WCFMcpt->plugin_url . 'js/cpt1/wcfm-script-cpt1.js', array('jquery', 'dataTables_js'), $WCFM->version, true );
	    	
	    	// Screen manager
	    	$wcfm_screen_manager = get_option( 'wcfm_screen_manager', array() );
	    	$wcfm_screen_manager_data = array();
	    	if( isset( $wcfm_screen_manager['cpt1'] ) ) $wcfm_screen_manager_data = $wcfm_screen_manager['cpt1'];
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
	    	wp_localize_script( 'wcfm_cpt1_js', 'wcfm_cpt1_screen_manage', $wcfm_screen_manager_data );
	    	
	    	// Localized Script
        $wcfm_messages = get_wcfm_cpt_manager_messages();
			  wp_localize_script( 'wcfm_cpt1_js', 'wcfm_cpt1_manage_messages', $wcfm_messages );
      break;
      
      case 'wcfm-cpt1-manage':
      	$WCFM->library->load_upload_lib();
      	$WCFM->library->load_select2_lib();
      	$WCFM->library->load_collapsible_lib();
	  		wp_enqueue_script( 'wcfm_cpt1_manage_js', $WCFMcpt->plugin_url . 'js/cpt1/wcfm-script-cpt1-manage.js', array('jquery'), $WCFM->version, true );
	  		
	  		// Localized Script
        $wcfm_messages = get_wcfm_cpt_manager_messages();
			  wp_localize_script( 'wcfm_cpt1_manage_js', 'wcfm_cpt1_manage_messages', $wcfm_messages );
	  	break;
	  }
	}
	
	/**
   * Cpt1 Styles
   */
	public function wcfm_cpt1_load_styles( $end_point ) {
	  global $WCFM, $WCFMcpt;
		
	  switch( $end_point ) {
	    case 'wcfm-cpt1':
	    	wp_enqueue_style( 'wcfm_cpt1_css',  $WCFMcpt->plugin_url . 'css/cpt1/wcfm-style-cpt1.css', array(), $WCFM->version );
		  break;
		  
		  case 'wcfm-cpt1-manage':
		  	wp_enqueue_style( 'collapsible_css',  $WCFM->library->css_lib_url . 'wcfm-style-collapsible.css', array(), $WCFM->version );
	    	wp_enqueue_style( 'wcfm_cpt1_manage_css',  $WCFMcpt->plugin_url . 'css/cpt1/wcfm-style-cpt1-manage.css', array(), $WCFM->version );
		  break;
	  }
	}
	
	/**
   * Cpt1 Views
   */
  public function wcfm_cpt1_load_views( $end_point ) {
	  global $WCFM, $WCFMcpt;
	  
	  switch( $end_point ) {
	  	case 'wcfm-cpt1':
        include_once( $WCFMcpt->plugin_path . 'views/cpt1/wcfm-view-cpt1.php' );
      break;
      
      case 'wcfm-cpt1-manage':
        include_once( $WCFMcpt->plugin_path . 'views/cpt1/wcfm-view-cpt1-manage.php' );
      break;
	  }
	}
	
	/**
   * Cpt1 Ajax Controllers
   */
  public function wcfm_cpt1_ajax_controller() {
  	global $WCFM, $WCFMcpt;
  	
  	$controllers_path = $WCFMcpt->plugin_path . 'controllers/cpt1/';
  	
  	$controller = '';
  	if( isset( $_POST['controller'] ) ) {
  		$controller = $_POST['controller'];
  		
  		switch( $controller ) {
  			case 'wcfm-cpt1':
					include_once( $controllers_path . 'wcfm-controller-cpt1.php' );
					new WCFM_Cpt1_Controller();
				break;
				
				case 'wcfm-cpt1-manage':
					include_once( $controllers_path . 'wcfm-controller-cpt1-manage.php' );
					new WCFM_Cpt1_Manage_Controller();
				break;
  		}
  	}
  }
  
  /**
   * Handle Cpt1 Delete
   */
  public function delete_wcfm_cpt1() {
  	global $WCFM;
  	
  	$cpt1id = $_POST['cpt1id'];
		
		if( $cpt1id ) {
			do_action( 'wcfm_before_cpt1_delete', $cpt1id );
			if( apply_filters( 'wcfm_is_allow_cpt1_delete' , false ) ) {
				if(wp_delete_post($cpt1id)) {
					echo 'success';
					die;
				}
			} else {
				if(wp_trash_post($cpt1id)) {
					echo 'success';
					die;
				}
			}
			die;
		}
  }
  
  /**
   * CPT1 Capability Settings 
   */
  function wcfm_cpt1_capability_settings( $wcfm_capability_options ) {
  	global $WCFM, $WCFMcpt;
  	
  	// CPT1 Capabilities
		$submit_cpt1 = ( isset( $wcfm_capability_options['submit_cpt1'] ) ) ? $wcfm_capability_options['submit_cpt1'] : 'no';
		$add_cpt1 = ( isset( $wcfm_capability_options['add_cpt1'] ) ) ? $wcfm_capability_options['add_cpt1'] : 'no';
		$publish_cpt1 = ( isset( $wcfm_capability_options['publish_cpt1'] ) ) ? $wcfm_capability_options['publish_cpt1'] : 'no';
		$edit_live_cpt1 = ( isset( $wcfm_capability_options['edit_live_cpt1'] ) ) ? $wcfm_capability_options['edit_live_cpt1'] : 'no';
		$publish_live_cpt1 = ( isset( $wcfm_capability_options['publish_live_cpt1'] ) ) ? $wcfm_capability_options['publish_live_cpt1'] : 'no';
		$delete_cpt1 = ( isset( $wcfm_capability_options['delete_cpt1'] ) ) ? $wcfm_capability_options['delete_cpt1'] : 'no';
	
  	?>
  	
  	<div class="vendor_capability_sub_heading"><h3><?php _e( WCFM_CPT_1_LABEL, 'wc-frontend-manager' ); ?></h3></div>
									
		<?php
		$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_cpt1', array("submit_cpt1" => array('label' => __('Manage', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[submit_cpt1]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $submit_cpt1),
																																																							 "add_cpt1" => array('label' => __('Add', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[add_cpt1]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $add_cpt1),
																																																							 "publish_cpt1" => array('label' => __('Publish', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[publish_cpt1]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $publish_cpt1),
																																																							 "edit_live_cpt1" => array('label' => __('Edit Live', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[edit_live_cpt1]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $edit_live_cpt1),
																																																							 "publish_live_cpt1" => array('label' => __('Auto Publish Live', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[publish_live_cpt1]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $publish_live_cpt1),
																																																							 "delete_cpt1" => array('label' => __('Delete', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[delete_cpt1]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $delete_cpt1)
																							) ) );
	}
}