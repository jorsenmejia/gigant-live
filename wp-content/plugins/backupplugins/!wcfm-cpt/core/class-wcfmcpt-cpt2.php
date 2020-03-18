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
 
class WCFM_CPT2 {

	public function __construct() {
		global $WCFM;
		
		if ( !is_admin() || defined('DOING_AJAX') ) {
			if( $is_allow_cpt2 = apply_filters( 'wcfm_is_allow_cpt2', true ) ) {
				// WC Cpt2 Query Var Filter
				add_filter( 'wcfm_query_vars', array( &$this, 'cpt2_wcfm_query_vars' ), 20 );
				add_filter( 'wcfm_endpoint_title', array( &$this, 'cpt2_wcfm_endpoint_title' ), 20, 2 );
				add_action( 'init', array( &$this, 'cpt2_wcfm_init' ), 20 );
				
				// WCFM Cpt2 Endpoint Edit
				add_filter( 'wcfm_endpoints_slug', array( $this, 'wcfm_cpt2_endpoints_slug' ) );
				
				// WCFM CPT2 Manage Dependency Map
				add_filter( 'wcfm_menu_dependancy_map', array( $this, 'wcfm_cpt2_manage_dependency_map' ) );
				
				// WCFM CPT2 Exclude Product Popup
				add_filter( 'wcfm_blocked_product_popup_views', array( $this, 'wcfm_cpt2_blocked_product_popup_views' ) );
					
				// WC Cpt2 Menu Filter
				add_filter( 'wcfm_menus', array( &$this, 'cpt2_wcfm_menus' ), 20 );
				
				// Cpt2 Load WCFMu Scripts
				add_action( 'wcfm_load_scripts', array( &$this, 'wcfm_cpt2_load_scripts' ), 30 );
				
				// Cpt2 Load WCFMu Styles
				add_action( 'wcfm_load_styles', array( &$this, 'wcfm_cpt2_load_styles' ), 30 );
				
				// Cpt2 Load WCFMu views
				add_action( 'wcfm_load_views', array( &$this, 'wcfm_cpt2_load_views' ), 30 );
				
				// Cpt2 Ajax Controllers
				add_action( 'after_wcfm_ajax_controller', array( &$this, 'wcfm_cpt2_ajax_controller' ), 30 );
				
				// Cpt2 Delete
				add_action( 'wp_ajax_delete_wcfm_cpt2', array( &$this, 'delete_wcfm_cpt2' ) );
				
				// Cpt2 Capability Options 
				add_action( 'wcfm_capability_settings_product', array( &$this, 'wcfm_cpt2_capability_settings' ), 50 );
			}
		}
	}
	
	/**
   * WCFM Cpt2 Query Var
   */
  function cpt2_wcfm_query_vars( $query_vars ) {
  	$wcfm_modified_endpoints = (array) get_option( 'wcfm_endpoints' );
  	
		$query_cpt2_vars = array(
			'wcfm-cpt2'                 => ! empty( $wcfm_modified_endpoints['wcfm-cpt2'] ) ? $wcfm_modified_endpoints['wcfm-cpt2'] : WCFM_CPT_2,
			'wcfm-cpt2-manage'          => ! empty( $wcfm_modified_endpoints['wcfm-cpt2-manage'] ) ? $wcfm_modified_endpoints['wcfm-cpt2-manage'] : WCFM_CPT_2.'-manage',
		);
		
		$query_vars = array_merge( $query_vars, $query_cpt2_vars );
		
		return $query_vars;
  }
  
  /**
   * WCFM Cpt2 End Point Title
   */
  function cpt2_wcfm_endpoint_title( $title, $endpoint ) {
  	global $wp;
  	switch ( $endpoint ) {
  		case 'wcfm-cpt2' :
				$title = __( 'Cpt2 Dashboard', 'wc-frontend-manager' );
			break;
			case 'wcfm-cpt2-manage' :
				$title = __( 'Cpt2 Manager', 'wc-frontend-manager' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * WCFM Cpt2 Endpoint Intialize
   */
  function cpt2_wcfm_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		if( !get_option( 'wcfm_updated_end_point_wcfm_cpt2' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_wcfm_cpt2', 1 );
		}
  }
  
  /**
	 * WCFM Cpt2 Endpoiint Edit
	 */
  function wcfm_cpt2_endpoints_slug( $endpoints ) {
		
		$cpt2_endpoints = array(
													'wcfm-cpt2'  		      => WCFM_CPT_2,
													'wcfm-cpt2-manage'  	=> WCFM_CPT_2.'-manage',
													);
		$endpoints = array_merge( $endpoints, $cpt2_endpoints );
		
		return $endpoints;
	}
	
	/**
   * CPT 1 manage menu dependency mapping
   */
  function wcfm_cpt2_manage_dependency_map( $mappings ) {
  	$mappings['wcfm-cpt2-manage'] = 'wcfm-cpt2'; 
  	return $mappings;
  }
	
	/**
	 * BLock Product Popup Views
	 */
	function wcfm_cpt2_blocked_product_popup_views( $views ) {
		$views[] = 'wcfm-cpt2-manage';
		return $views;
	}
  
  /**
   * WCFM Cpt2 Menu
   */
  function cpt2_wcfm_menus( $menus ) {
  	global $WCFM;
  	
		$cpt2_menus = array( 'wcfm-cpt2' => array(   'label'  => WCFM_CPT_2_LABEL,
																												 'url'       => get_wcfm_cpt2_url(),
																												 'icon'      => 'codepen',
																												 'has_new'    => 'yes',
																												 'new_class'  => 'wcfm_sub_menu_items_cpt2_manage',
																												 'new_url'    => get_wcfm_cpt2_manage_url(),
																												 'capability' => 'wcfm_cpt2_menu',
																												 'submenu_capability' => 'wcfm_add_new_cpt2_sub_menu',
																												 'priority'  => 4
																												) );
		
		$menus = array_merge( $menus, $cpt2_menus );
  	return $menus;
  }
  
  /**
   * Cpt2 Scripts
   */
  public function wcfm_cpt2_load_scripts( $end_point ) {
	  global $WCFM, $WCFMcpt;
    
	  switch( $end_point ) {
	  	case 'wcfm-cpt2':
      	$WCFM->library->load_datatable_lib();
      	$WCFM->library->load_select2_lib();
	    	wp_enqueue_script( 'wcfm_cpt2_js', $WCFMcpt->plugin_url . 'js/cpt2/wcfm-script-cpt2.js', array('jquery', 'dataTables_js'), $WCFM->version, true );
	    	
	    	// Screen manager
	    	$wcfm_screen_manager = get_option( 'wcfm_screen_manager', array() );
	    	$wcfm_screen_manager_data = array();
	    	if( isset( $wcfm_screen_manager['cpt2'] ) ) $wcfm_screen_manager_data = $wcfm_screen_manager['cpt2'];
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
	    	wp_localize_script( 'wcfm_cpt2_js', 'wcfm_cpt2_screen_manage', $wcfm_screen_manager_data );
	    	
	    	// Localized Script
        $wcfm_messages = get_wcfm_cpt_manager_messages();
			  wp_localize_script( 'wcfm_cpt2_js', 'wcfm_cpt2_manage_messages', $wcfm_messages );
      break;
      
      case 'wcfm-cpt2-manage':
      	$WCFM->library->load_upload_lib();
      	$WCFM->library->load_select2_lib();
      	$WCFM->library->load_collapsible_lib();
	  		wp_enqueue_script( 'wcfm_cpt2_manage_js', $WCFMcpt->plugin_url . 'js/cpt2/wcfm-script-cpt2-manage.js', array('jquery'), $WCFM->version, true );
	  		
	  		// Localized Script
        $wcfm_messages = get_wcfm_cpt_manager_messages();
			  wp_localize_script( 'wcfm_cpt2_manage_js', 'wcfm_cpt2_manage_messages', $wcfm_messages );
	  	break;
	  }
	}
	
	/**
   * Cpt2 Styles
   */
	public function wcfm_cpt2_load_styles( $end_point ) {
	  global $WCFM, $WCFMcpt;
		
	  switch( $end_point ) {
	    case 'wcfm-cpt2':
	    	wp_enqueue_style( 'wcfm_cpt2_css',  $WCFMcpt->plugin_url . 'css/cpt2/wcfm-style-cpt2.css', array(), $WCFM->version );
		  break;
		  
		  case 'wcfm-cpt2-manage':
		  	wp_enqueue_style( 'collapsible_css',  $WCFM->library->css_lib_url . 'wcfm-style-collapsible.css', array(), $WCFM->version );
	    	wp_enqueue_style( 'wcfm_cpt2_manage_css',  $WCFMcpt->plugin_url . 'css/cpt2/wcfm-style-cpt2-manage.css', array(), $WCFM->version );
		  break;
	  }
	}
	
	/**
   * Cpt2 Views
   */
  public function wcfm_cpt2_load_views( $end_point ) {
	  global $WCFM, $WCFMcpt;
	  
	  switch( $end_point ) {
	  	case 'wcfm-cpt2':
        include_once( $WCFMcpt->plugin_path . 'views/cpt2/wcfm-view-cpt2.php' );
      break;
      
      case 'wcfm-cpt2-manage':
        include_once( $WCFMcpt->plugin_path . 'views/cpt2/wcfm-view-cpt2-manage.php' );
      break;
	  }
	}
	
	/**
   * Cpt2 Ajax Controllers
   */
  public function wcfm_cpt2_ajax_controller() {
  	global $WCFM, $WCFMcpt;
  	
  	$controllers_path = $WCFMcpt->plugin_path . 'controllers/cpt2/';
  	
  	$controller = '';
  	if( isset( $_POST['controller'] ) ) {
  		$controller = $_POST['controller'];
  		
  		switch( $controller ) {
  			case 'wcfm-cpt2':
					include_once( $controllers_path . 'wcfm-controller-cpt2.php' );
					new WCFM_Cpt2_Controller();
				break;
				
				case 'wcfm-cpt2-manage':
					include_once( $controllers_path . 'wcfm-controller-cpt2-manage.php' );
					new WCFM_Cpt2_Manage_Controller();
				break;
  		}
  	}
  }
  
  /**
   * Handle Cpt2 Delete
   */
  public function delete_wcfm_cpt2() {
  	global $WCFM;
  	
  	$cpt2id = $_POST['cpt2id'];
		
		if( $cpt2id ) {
			do_action( 'wcfm_before_cpt2_delete', $cpt2id );
			if( apply_filters( 'wcfm_is_allow_cpt2_delete' , false ) ) {
				if(wp_delete_post($cpt2id)) {
					echo 'success';
					die;
				}
			} else {
				if(wp_trash_post($cpt2id)) {
					echo 'success';
					die;
				}
			}
			die;
		}
  }
  
  /**
   * CPT2 Capability Settings 
   */
  function wcfm_cpt2_capability_settings( $wcfm_capability_options ) {
  	global $WCFM, $WCFMcpt;
  	
  	// CPT2 Capabilities
		$submit_cpt2 = ( isset( $wcfm_capability_options['submit_cpt2'] ) ) ? $wcfm_capability_options['submit_cpt2'] : 'no';
		$add_cpt2 = ( isset( $wcfm_capability_options['add_cpt2'] ) ) ? $wcfm_capability_options['add_cpt2'] : 'no';
		$publish_cpt2 = ( isset( $wcfm_capability_options['publish_cpt2'] ) ) ? $wcfm_capability_options['publish_cpt2'] : 'no';
		$edit_live_cpt2 = ( isset( $wcfm_capability_options['edit_live_cpt2'] ) ) ? $wcfm_capability_options['edit_live_cpt2'] : 'no';
		$publish_live_cpt2 = ( isset( $wcfm_capability_options['publish_live_cpt2'] ) ) ? $wcfm_capability_options['publish_live_cpt2'] : 'no';
		$delete_cpt2 = ( isset( $wcfm_capability_options['delete_cpt2'] ) ) ? $wcfm_capability_options['delete_cpt2'] : 'no';
	
  	?>
  	
  	<div class="vendor_capability_sub_heading"><h3><?php _e( WCFM_CPT_2_LABEL, 'wc-frontend-manager' ); ?></h3></div>
									
		<?php
		$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_capability_settings_fields_vendor_cpt2', array("submit_cpt2" => array('label' => __('Manage', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[submit_cpt2]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $submit_cpt2),
																																																							 "add_cpt2" => array('label' => __('Add', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[add_cpt2]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $add_cpt2),
																																																							 "publish_cpt2" => array('label' => __('Publish', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[publish_cpt2]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $publish_cpt2),
																																																							 "edit_live_cpt2" => array('label' => __('Edit Live', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[edit_live_cpt2]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $edit_live_cpt2),
																																																							 "publish_live_cpt2" => array('label' => __('Auto Publish Live', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[publish_live_cpt2]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $publish_live_cpt2),
																																																							 "delete_cpt2" => array('label' => __('Delete', 'wc-frontend-manager') , 'name' => 'wcfm_capability_options[delete_cpt2]','type' => 'checkboxoffon', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'yes', 'label_class' => 'wcfm_title checkbox_title', 'dfvalue' => $delete_cpt2)
																							) ) );
	}
}