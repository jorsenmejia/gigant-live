<?php

if(!function_exists('get_wcfm_cpt1_url')) {
	function get_wcfm_cpt1_url( $cpt1_status = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_cpt1_url = wcfm_get_endpoint_url( WCFM_CPT_1, '', $wcfm_page );
		if($cpt1_status) $get_wcfm_cpt1_url = add_query_arg( 'cpt1_status', $cpt1_status, $get_wcfm_cpt1_url );
		return apply_filters( 'wcfm_cpt1_url', $get_wcfm_cpt1_url );
	}
}

if(!function_exists('get_wcfm_cpt1_manage_url')) {
	function get_wcfm_cpt1_manage_url( $cpt1_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_cpt1_manage_url = wcfm_get_endpoint_url( WCFM_CPT_1 . '-manage', $cpt1_id, $wcfm_page );
		return apply_filters( 'wcfm_cpt1_manage_url', $get_wcfm_cpt1_manage_url );
	}
}

if(!function_exists('get_wcfm_cpt2_url')) {
	function get_wcfm_cpt2_url( $cpt2_status = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_cpt2_url = wcfm_get_endpoint_url( WCFM_CPT_2, '', $wcfm_page );
		if($cpt2_status) $get_wcfm_cpt2_url = add_query_arg( 'cpt2_status', $cpt2_status, $get_wcfm_cpt2_url );
		return apply_filters( 'wcfm_cpt2_url', $get_wcfm_cpt2_url );
	}
}

if(!function_exists('get_wcfm_cpt2_manage_url')) {
	function get_wcfm_cpt2_manage_url( $cpt2_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_cpt2_manage_url = wcfm_get_endpoint_url( WCFM_CPT_2 . '-manage', $cpt2_id, $wcfm_page );
		return apply_filters( 'wcfm_cpt2_manage_url', $get_wcfm_cpt2_manage_url );
	}
}

if(!function_exists('get_wcfm_cpt3_url')) {
	function get_wcfm_cpt3_url( $cpt3_status = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_cpt3_url = wcfm_get_endpoint_url( WCFM_CPT_3, '', $wcfm_page );
		if($cpt3_status) $get_wcfm_cpt3_url = add_query_arg( 'cpt3_status', $cpt3_status, $get_wcfm_cpt3_url );
		return apply_filters( 'wcfm_cpt3_url', $get_wcfm_cpt3_url );
	}
}

if(!function_exists('get_wcfm_cpt3_manage_url')) {
	function get_wcfm_cpt3_manage_url( $cpt3_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$get_wcfm_cpt3_manage_url = wcfm_get_endpoint_url( WCFM_CPT_3 . '-manage', $cpt3_id, $wcfm_page );
		return apply_filters( 'wcfm_cpt3_manage_url', $get_wcfm_cpt3_manage_url );
	}
}

if(!function_exists('get_wcfm_cpt_manager_messages')) {
	function get_wcfm_cpt_manager_messages() {
		global $WCFM;
		
		$messages = apply_filters( 'wcfm_validation_messages_cpt_manager', array(
																																								'no_title'        => __('Please insert Title before submit.', 'wcfm-cpt'),
																																								'cpt_saved'       => __('Successfully Saved.', 'wcfm-cpt'),
																																								'cpt_pending'     => __( 'Successfully submitted for moderation.', 'wcfm-cpt' ),
																																								'cpt_published'   => __('Successfully Published.', 'wcfm-cpt'),
																																								'delete_confirm'  => __( "Are you sure and want to delete this?\nYou can't undo this action ...", 'wcfm-cpt'),
																																								) );
		
		return $messages;
	}
}