<?php
/**
 * WCFM CPT plugin core
 *
 * Plugin Capability Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmcpt/core
 * @version   1.0.0
 */
 
class WCFM_CPT_Capability {
	
	private $wcfm_capability_options = array();

	public function __construct() {
		global $WCFM, $WCFMcpt;
		
		$this->wcfm_capability_options = apply_filters( 'wcfm_capability_options_rules', get_option( 'wcfm_capability_options', array() ) );
		
		// CPT1 Filter
		add_filter( 'wcfm_is_allow_manage_cpt1', array( &$this, 'wcfmcap_is_allow_manage_cpt1' ), 500 );
		add_filter( 'wcfm_article_menu', array( &$this, 'wcfmcap_is_allow_manage_cpt1' ), 500 );
		add_filter( 'wcfm_add_new_cpt1_sub_menu', array( &$this, 'wcfmcap_is_allow_add_cpt1' ), 500 );
		add_filter( 'wcfm_is_allow_add_cpt1', array( &$this, 'wcfmcap_is_allow_add_cpt1' ), 500 );
		add_filter( 'wcfm_is_allow_edit_cpt1', array( &$this, 'wcfmcap_is_allow_edit_cpt1' ), 500 );
		add_filter( 'wcfm_is_allow_publish_cpt1', array( &$this, 'wcfmcap_is_allow_publish_cpt1' ), 500 );
		add_filter( 'wcfm_is_allow_publish_live_cpt1', array( &$this, 'wcfmcap_is_allow_publish_live_cpt1' ), 500 );
		add_filter( 'wcfm_is_allow_delete_cpt1', array( &$this, 'wcfmcap_is_allow_delete_cpt1' ), 500 );
		
		// CPT2 Filter
		add_filter( 'wcfm_is_allow_manage_cpt2', array( &$this, 'wcfmcap_is_allow_manage_cpt2' ), 500 );
		add_filter( 'wcfm_article_menu', array( &$this, 'wcfmcap_is_allow_manage_cpt2' ), 500 );
		add_filter( 'wcfm_add_new_cpt2_sub_menu', array( &$this, 'wcfmcap_is_allow_add_cpt2' ), 500 );
		add_filter( 'wcfm_is_allow_add_cpt2', array( &$this, 'wcfmcap_is_allow_add_cpt2' ), 500 );
		add_filter( 'wcfm_is_allow_edit_cpt2', array( &$this, 'wcfmcap_is_allow_edit_cpt2' ), 500 );
		add_filter( 'wcfm_is_allow_publish_cpt2', array( &$this, 'wcfmcap_is_allow_publish_cpt2' ), 500 );
		add_filter( 'wcfm_is_allow_publish_live_cpt2', array( &$this, 'wcfmcap_is_allow_publish_live_cpt2' ), 500 );
		add_filter( 'wcfm_is_allow_delete_cpt2', array( &$this, 'wcfmcap_is_allow_delete_cpt2' ), 500 );
		
		// CPT3 Filter
		add_filter( 'wcfm_is_allow_manage_cpt3', array( &$this, 'wcfmcap_is_allow_manage_cpt3' ), 500 );
		add_filter( 'wcfm_article_menu', array( &$this, 'wcfmcap_is_allow_manage_cpt3' ), 500 );
		add_filter( 'wcfm_add_new_cpt3_sub_menu', array( &$this, 'wcfmcap_is_allow_add_cpt3' ), 500 );
		add_filter( 'wcfm_is_allow_add_cpt3', array( &$this, 'wcfmcap_is_allow_add_cpt3' ), 500 );
		add_filter( 'wcfm_is_allow_edit_cpt3', array( &$this, 'wcfmcap_is_allow_edit_cpt3' ), 500 );
		add_filter( 'wcfm_is_allow_publish_cpt3', array( &$this, 'wcfmcap_is_allow_publish_cpt3' ), 500 );
		add_filter( 'wcfm_is_allow_publish_live_cpt3', array( &$this, 'wcfmcap_is_allow_publish_live_cpt3' ), 500 );
		add_filter( 'wcfm_is_allow_delete_cpt3', array( &$this, 'wcfmcap_is_allow_delete_cpt3' ), 500 );
		
	}
	
	// WCFM wcfmcap Manage Cpt1
  function wcfmcap_is_allow_manage_cpt1( $allow ) {
  	$manage_cpt1 = ( isset( $this->wcfm_capability_options['submit_cpt1'] ) ) ? $this->wcfm_capability_options['submit_cpt1'] : 'no';
  	if( $manage_cpt1 == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Add Cpt1
  function wcfmcap_is_allow_add_cpt1( $allow ) {
  	$manage_cpt1 = ( isset( $this->wcfm_capability_options['submit_cpt1'] ) ) ? $this->wcfm_capability_options['submit_cpt1'] : 'no';
  	if( $manage_cpt1 == 'yes' ) return false;
  	$add_cpt1 = ( isset( $this->wcfm_capability_options['add_cpt1'] ) ) ? $this->wcfm_capability_options['add_cpt1'] : 'no';
  	if( $add_cpt1 == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Edit Cpt1
  function wcfmcap_is_allow_edit_cpt1( $allow ) {
  	$manage_cpt1 = ( isset( $this->wcfm_capability_options['submit_cpt1'] ) ) ? $this->wcfm_capability_options['submit_cpt1'] : 'no';
  	if( $manage_cpt1 == 'yes' ) return false;
  	$edit_cpt1 = ( isset( $this->wcfm_capability_options['edit_live_cpt1'] ) ) ? $this->wcfm_capability_options['edit_live_cpt1'] : 'no';
  	if( $edit_cpt1 == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Publish Cpt1
  function wcfmcap_is_allow_publish_cpt1( $allow ) {
  	$manage_cpt1 = ( isset( $this->wcfm_capability_options['submit_cpt1'] ) ) ? $this->wcfm_capability_options['submit_cpt1'] : 'no';
  	if( $manage_cpt1 == 'yes' ) return false;
  	$publish_cpt1 = ( isset( $this->wcfm_capability_options['publish_cpt1'] ) ) ? $this->wcfm_capability_options['publish_cpt1'] : 'no';
  	if( $publish_cpt1 == 'yes' ) return false;                
  	return $allow;
  }
  
  // WCFM auto publish live cpt1
  function wcfmcap_is_allow_publish_live_cpt1( $allow ) {
  	$manage_cpt1 = ( isset( $this->wcfm_capability_options['submit_cpt1'] ) ) ? $this->wcfm_capability_options['submit_cpt1'] : 'no';
  	if( $manage_cpt1 == 'yes' ) return false;
  	$publish_live_cpt1 = ( isset( $this->wcfm_capability_options['publish_live_cpt1'] ) ) ? $this->wcfm_capability_options['publish_live_cpt1'] : 'no';
  	if( $publish_live_cpt1 == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Delete Cpt1
  function wcfmcap_is_allow_delete_cpt1( $allow ) {
  	$manage_cpt1 = ( isset( $this->wcfm_capability_options['submit_cpt1'] ) ) ? $this->wcfm_capability_options['submit_cpt1'] : 'no';
  	if( $manage_cpt1 == 'yes' ) return false;
  	$delete_cpt1 = ( isset( $this->wcfm_capability_options['delete_cpt1'] ) ) ? $this->wcfm_capability_options['delete_cpt1'] : 'no';
  	if( $delete_cpt1 == 'yes' ) return false;              
  	return $allow;
  }
  
  //////////////////////////////////////////////////////// CPT 2 Capability Checking //////////////////////////////////////////////////////////////////////////
  
  // WCFM wcfmcap Manage Cpt2
  function wcfmcap_is_allow_manage_cpt2( $allow ) {
  	$manage_cpt2 = ( isset( $this->wcfm_capability_options['submit_cpt2'] ) ) ? $this->wcfm_capability_options['submit_cpt2'] : 'no';
  	if( $manage_cpt2 == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Add Cpt2
  function wcfmcap_is_allow_add_cpt2( $allow ) {
  	$manage_cpt2 = ( isset( $this->wcfm_capability_options['submit_cpt2'] ) ) ? $this->wcfm_capability_options['submit_cpt2'] : 'no';
  	if( $manage_cpt2 == 'yes' ) return false;
  	$add_cpt2 = ( isset( $this->wcfm_capability_options['add_cpt2'] ) ) ? $this->wcfm_capability_options['add_cpt2'] : 'no';
  	if( $add_cpt2 == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Edit Cpt2
  function wcfmcap_is_allow_edit_cpt2( $allow ) {
  	$manage_cpt2 = ( isset( $this->wcfm_capability_options['submit_cpt2'] ) ) ? $this->wcfm_capability_options['submit_cpt2'] : 'no';
  	if( $manage_cpt2 == 'yes' ) return false;
  	$edit_cpt2 = ( isset( $this->wcfm_capability_options['edit_live_cpt2'] ) ) ? $this->wcfm_capability_options['edit_live_cpt2'] : 'no';
  	if( $edit_cpt2 == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Publish Cpt2
  function wcfmcap_is_allow_publish_cpt2( $allow ) {
  	$manage_cpt2 = ( isset( $this->wcfm_capability_options['submit_cpt2'] ) ) ? $this->wcfm_capability_options['submit_cpt2'] : 'no';
  	if( $manage_cpt2 == 'yes' ) return false;
  	$publish_cpt2 = ( isset( $this->wcfm_capability_options['publish_cpt2'] ) ) ? $this->wcfm_capability_options['publish_cpt2'] : 'no';
  	if( $publish_cpt2 == 'yes' ) return false;                
  	return $allow;
  }
  
  // WCFM auto publish live cpt2
  function wcfmcap_is_allow_publish_live_cpt2( $allow ) {
  	$manage_cpt2 = ( isset( $this->wcfm_capability_options['submit_cpt2'] ) ) ? $this->wcfm_capability_options['submit_cpt2'] : 'no';
  	if( $manage_cpt2 == 'yes' ) return false;
  	$publish_live_cpt2 = ( isset( $this->wcfm_capability_options['publish_live_cpt2'] ) ) ? $this->wcfm_capability_options['publish_live_cpt2'] : 'no';
  	if( $publish_live_cpt2 == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Delete Cpt2
  function wcfmcap_is_allow_delete_cpt2( $allow ) {
  	$manage_cpt2 = ( isset( $this->wcfm_capability_options['submit_cpt2'] ) ) ? $this->wcfm_capability_options['submit_cpt2'] : 'no';
  	if( $manage_cpt2 == 'yes' ) return false;
  	$delete_cpt2 = ( isset( $this->wcfm_capability_options['delete_cpt2'] ) ) ? $this->wcfm_capability_options['delete_cpt2'] : 'no';
  	if( $delete_cpt2 == 'yes' ) return false;              
  	return $allow;
  }
  
  //////////////////////////////////////////////////////// CPT 3 Capability Checking //////////////////////////////////////////////////////////////////////////
  
  // WCFM wcfmcap Manage Cpt3
  function wcfmcap_is_allow_manage_cpt3( $allow ) {
  	$manage_cpt3 = ( isset( $this->wcfm_capability_options['submit_cpt3'] ) ) ? $this->wcfm_capability_options['submit_cpt3'] : 'no';
  	if( $manage_cpt3 == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Add Cpt3
  function wcfmcap_is_allow_add_cpt3( $allow ) {
  	$manage_cpt3 = ( isset( $this->wcfm_capability_options['submit_cpt3'] ) ) ? $this->wcfm_capability_options['submit_cpt3'] : 'no';
  	if( $manage_cpt3 == 'yes' ) return false;
  	$add_cpt3 = ( isset( $this->wcfm_capability_options['add_cpt3'] ) ) ? $this->wcfm_capability_options['add_cpt3'] : 'no';
  	if( $add_cpt3 == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Edit Cpt3
  function wcfmcap_is_allow_edit_cpt3( $allow ) {
  	$manage_cpt3 = ( isset( $this->wcfm_capability_options['submit_cpt3'] ) ) ? $this->wcfm_capability_options['submit_cpt3'] : 'no';
  	if( $manage_cpt3 == 'yes' ) return false;
  	$edit_cpt3 = ( isset( $this->wcfm_capability_options['edit_live_cpt3'] ) ) ? $this->wcfm_capability_options['edit_live_cpt3'] : 'no';
  	if( $edit_cpt3 == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Publish Cpt3
  function wcfmcap_is_allow_publish_cpt3( $allow ) {
  	$manage_cpt3 = ( isset( $this->wcfm_capability_options['submit_cpt3'] ) ) ? $this->wcfm_capability_options['submit_cpt3'] : 'no';
  	if( $manage_cpt3 == 'yes' ) return false;
  	$publish_cpt3 = ( isset( $this->wcfm_capability_options['publish_cpt3'] ) ) ? $this->wcfm_capability_options['publish_cpt3'] : 'no';
  	if( $publish_cpt3 == 'yes' ) return false;                
  	return $allow;
  }
  
  // WCFM auto publish live cpt3
  function wcfmcap_is_allow_publish_live_cpt3( $allow ) {
  	$manage_cpt3 = ( isset( $this->wcfm_capability_options['submit_cpt3'] ) ) ? $this->wcfm_capability_options['submit_cpt3'] : 'no';
  	if( $manage_cpt3 == 'yes' ) return false;
  	$publish_live_cpt3 = ( isset( $this->wcfm_capability_options['publish_live_cpt3'] ) ) ? $this->wcfm_capability_options['publish_live_cpt3'] : 'no';
  	if( $publish_live_cpt3 == 'yes' ) return false;
  	return $allow;
  }
  
  // WCFM wcfmcap Delete Cpt3
  function wcfmcap_is_allow_delete_cpt3( $allow ) {
  	$manage_cpt3 = ( isset( $this->wcfm_capability_options['submit_cpt3'] ) ) ? $this->wcfm_capability_options['submit_cpt3'] : 'no';
  	if( $manage_cpt3 == 'yes' ) return false;
  	$delete_cpt3 = ( isset( $this->wcfm_capability_options['delete_cpt3'] ) ) ? $this->wcfm_capability_options['delete_cpt3'] : 'no';
  	if( $delete_cpt3 == 'yes' ) return false;              
  	return $allow;
  }
	
}