<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Cpt3 Manage Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers
 * @version   1.0.0
 */

class WCFM_Cpt3_Manage_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$wcfm_cpt3_manage_form_data = array();
	  parse_str($_POST['wcfm_cpt3_manage_form'], $wcfm_cpt3_manage_form_data);
	  //print_r($wcfm_cpt3_manage_form_data);
	  $wcfm_cpt3_manage_messages = get_wcfm_cpt_manager_messages();
	  $has_error = false;
	  
	  if(isset($wcfm_cpt3_manage_form_data['title']) && !empty($wcfm_cpt3_manage_form_data['title'])) {
	  	$is_update = false;
	  	$is_publish = false;
	  	$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
	  	
	  	// WCFM form custom validation filter
	  	$custom_validation_results = apply_filters( 'wcfm_form_custom_validation', $wcfm_cpt3_manage_form_data, 'cpt3_manage' );
	  	if(isset($custom_validation_results['has_error']) && !empty($custom_validation_results['has_error'])) {
	  		$custom_validation_error = __( 'There has some error in submitted data.', 'wcfm-cpt' );
	  		if( isset( $custom_validation_results['message'] ) && !empty( $custom_validation_results['message'] ) ) { $custom_validation_error = $custom_validation_results['message']; }
	  		echo '{"status": false, "message": "' . $custom_validation_error . '"}';
	  		die;
	  	}
	  	                  
	  	if(isset($_POST['status']) && ($_POST['status'] == 'draft')) {
	  		$cpt3_status = 'draft';
	  	} else {
	  		if( apply_filters( 'wcfm_is_allow_publish_cpt3', true ) )
	  			$cpt3_status = 'publish';
	  		else
	  		  $cpt3_status = 'pending';
			}
	  	
	  	// Creating new cpt3
			$new_cpt3 = apply_filters( 'wcfm_cpt3_content_before_save', array(
				'post_title'   => wc_clean( $wcfm_cpt3_manage_form_data['title'] ),
				'post_status'  => $cpt3_status,
				'post_type'    => WCFM_CPT_3,
				//'post_excerpt' => apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['excerpt'], ENT_QUOTES, 'UTF-8' ) ) ),
				'post_content' => apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $_POST['description'], ENT_QUOTES, 'UTF-8' ) ) ),
				'post_author'  => $current_user_id,
				'post_name' => sanitize_title($wcfm_cpt3_manage_form_data['title'])
			), $wcfm_cpt3_manage_form_data );
			
			if(isset($wcfm_cpt3_manage_form_data['cpt3_id']) && $wcfm_cpt3_manage_form_data['cpt3_id'] == 0) {
				if ($cpt3_status != 'draft') {
					$is_publish = true;
				}
				$new_cpt3_id = wp_insert_post( $new_cpt3, true );
			} else { // For Update
				$is_update = true;
				$new_cpt3['ID'] = $wcfm_cpt3_manage_form_data['cpt3_id'];
				unset( $new_cpt3['post_author'] );
				unset( $new_cpt3['post_name'] );
				if( ($cpt3_status != 'draft') && (get_post_status( $new_cpt3['ID'] ) == 'publish') ) {
					if( apply_filters( 'wcfm_is_allow_publish_live_cpt3', true ) ) {
						$new_cpt3['post_status'] = 'publish';
					}
				} else if( (get_post_status( $new_cpt3['ID'] ) == 'draft') && ($cpt3_status != 'draft') ) {
					$is_publish = true;
				}
				$new_cpt3_id = wp_update_post( $new_cpt3, true );
			}
			
			if(!is_wp_error($new_cpt3_id)) {
				// For Update
				if($is_update) $new_cpt3_id = $wcfm_cpt3_manage_form_data['cpt3_id'];
				
				// Set Cpt3 Custom Taxonomies
				if(isset($wcfm_cpt3_manage_form_data['product_custom_taxonomies']) && !empty($wcfm_cpt3_manage_form_data['product_custom_taxonomies'])) {
					foreach($wcfm_cpt3_manage_form_data['product_custom_taxonomies'] as $taxonomy => $taxonomy_values) {
						if( !empty( $taxonomy_values ) ) {
							$is_first = true;
							foreach( $taxonomy_values as $taxonomy_value ) {
								if($is_first) {
									$is_first = false;
									wp_set_object_terms( $new_cpt3_id, (int)$taxonomy_value, $taxonomy );
								} else {
									wp_set_object_terms( $new_cpt3_id, (int)$taxonomy_value, $taxonomy, true );
								}
							}
						}
					}
				}
				
				// Set Cpt3 Custom Taxonomies Flat
				if(isset($wcfm_cpt3_manage_form_data['cpt3_custom_taxonomies_flat']) && !empty($wcfm_cpt3_manage_form_data['cpt3_custom_taxonomies_flat'])) {
					foreach($wcfm_cpt3_manage_form_data['cpt3_custom_taxonomies_flat'] as $taxonomy => $taxonomy_values) {
						if( !empty( $taxonomy_values ) ) {
							wp_set_post_terms( $new_cpt3_id, $taxonomy_values, $taxonomy );
						}
					}
				}
				
				// Set Cpt3 Featured Image
				if(isset($wcfm_cpt3_manage_form_data['featured_img']) && !empty($wcfm_cpt3_manage_form_data['featured_img'])) {
					$featured_img_id = $WCFM->wcfm_get_attachment_id($wcfm_cpt3_manage_form_data['featured_img']);
					set_post_thumbnail( $new_cpt3_id, $featured_img_id );
					wp_update_post( array( 'ID' => $featured_img_id, 'post_parent' => $new_cpt3_id ) );
				} elseif(isset($wcfm_cpt3_manage_form_data['featured_img']) && empty($wcfm_cpt3_manage_form_data['featured_img'])) {
					delete_post_thumbnail( $new_cpt3_id );
				}
				
				// Custom Fields 
				if(isset($wcfm_cpt3_manage_form_data['custom_1']) && !empty($wcfm_cpt3_manage_form_data['custom_1'])) {
					update_post_meta( $new_cpt3_id, 'custom_1', $wcfm_cpt3_manage_form_data['custom_1'] );
				} else {
					update_post_meta( $new_cpt3_id, 'custom_1', '' );
				}
				if(isset($wcfm_cpt3_manage_form_data['custom_2']) && !empty($wcfm_cpt3_manage_form_data['custom_2'])) {
					update_post_meta( $new_cpt3_id, 'custom_2', $wcfm_cpt3_manage_form_data['custom_2'] );
				} else {
					update_post_meta( $new_cpt3_id, 'custom_2', '' );
				}
				if(isset($wcfm_cpt3_manage_form_data['custom_3']) && !empty($wcfm_cpt3_manage_form_data['custom_3'])) {
					update_post_meta( $new_cpt3_id, 'custom_3', $wcfm_cpt3_manage_form_data['custom_3'] );
				} else {
					update_post_meta( $new_cpt3_id, 'custom_3', '' );
				}
				if(isset($wcfm_cpt3_manage_form_data['custom_4']) && !empty($wcfm_cpt3_manage_form_data['custom_4'])) {
					update_post_meta( $new_cpt3_id, 'custom_4', $wcfm_cpt3_manage_form_data['custom_4'] );
				} else {
					update_post_meta( $new_cpt3_id, 'custom_4', '' );
				}
				
				do_action( 'after_wcfm_cpt3_manage_meta_save', $new_cpt3_id, $wcfm_cpt3_manage_form_data );
				
				// Notify Admin on New Cpt3 Creation
				if( $is_publish ) {
					// Have to test before adding action
				} 
				
				if(!$has_error) {
					if( get_post_status( $new_cpt3_id ) == 'publish' ) {
						if(!$has_error) echo '{"status": true, "message": "' . apply_filters( 'cpt3_published_message', $wcfm_cpt3_manage_messages['cpt_published'], $new_cpt3_id ) . '", "redirect": "' . apply_filters( 'wcfm_cpt3_save_publish_redirect', get_wcfm_cpt3_manage_url( $new_cpt3_id ), $new_cpt3_id ) . '", "id": "' . $new_cpt3_id . '", "title": "' . get_the_title( $new_cpt3_id ) . '"}';	
					} elseif( get_post_status( $new_cpt3_id ) == 'pending' ) {
						if(!$has_error) echo '{"status": true, "message": "' . apply_filters( 'cpt3_pending_message', $wcfm_cpt3_manage_messages['cpt_pending'], $new_cpt3_id ) . '", "redirect": "' . apply_filters( 'wcfm_cpt3_save_pending_redirect', get_wcfm_cpt3_manage_url( $new_cpt3_id ), $new_cpt3_id ) . '", "id": "' . $new_cpt3_id . '", "title": "' . get_the_title( $new_cpt3_id ) . '"}';
					} else {
						if(!$has_error) echo '{"status": true, "message": "' . apply_filters( 'cpt3_saved_message', $wcfm_cpt3_manage_messages['cpt_saved'], $new_cpt3_id ) . '", "redirect": "' . apply_filters( 'wcfm_cpt3_save_draft_redirect', get_wcfm_cpt3_manage_url( $new_cpt3_id ), $new_cpt3_id ) . '", "id": "' . $new_cpt3_id . '"}';
					}
				}
				die;
			}
		} else {
			echo '{"status": false, "message": "' . $wcfm_cpt3_manage_messages['no_title'] . '"}';
		}
	  die;
	}
}