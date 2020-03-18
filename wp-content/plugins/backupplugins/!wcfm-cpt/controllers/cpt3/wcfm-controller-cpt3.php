<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Cpt3 Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/cpt3
 * @version   1.0.0
 */

class WCFM_Cpt3_Controller {
	
	public function __construct() {
		global $WCFM;
		
		$this->processing();
		
	}
	
	public function processing() {
		global $WCFM, $wpdb, $_POST;
		
		$length = $_POST['length'];
		$offset = $_POST['start'];
		
		$args = array(
							'posts_per_page'   => $length,
							'offset'           => $offset,
							'category'         => '',
							'category_name'    => '',
							'orderby'          => 'date',
							'order'            => 'DESC',
							'include'          => '',
							'exclude'          => '',
							'meta_key'         => '',
							'meta_value'       => '',
							'post_type'        => WCFM_CPT_3,
							'post_mime_type'   => '',
							'post_parent'      => '',
							//'author'	   => get_current_user_id(),
							'post_status'      => array('draft', 'pending', 'publish'),
							'suppress_filters' => 0 
						);
		$for_count_args = $args;
		
		if( isset( $_POST['search'] ) && !empty( $_POST['search']['value'] )) {
			$args['s'] = $_POST['search']['value'];
		}
		
		if( isset($_POST['cpt3_status']) && !empty($_POST['cpt3_status']) ) $args['post_status'] = $_POST['cpt3_status'];
  	
		// Multi Vendor Support
		$is_marketplace = wcfm_is_marketplace();
		if( $is_marketplace ) {
			if( isset($_POST['cpt3_vendor']) && !empty($_POST['cpt3_vendor']) ) {
				if( !wcfm_is_vendor() ) {
					$args['author'] = $_POST['cpt3_vendor'];
				}
			}
			if( wcfm_is_vendor() ) {
				$args['author'] = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
			}
		}
		
		$args = apply_filters( 'wcfm_cpt3_args', $args );
		
		$wcfm_cpt3_array = get_posts( $args );
		
		$cpt3_count = 0;
		$filtered_cpt3_count = 0;
		if( wcfm_is_vendor() ) {
			// Get Cpt3 Count
			$for_count_args['posts_per_page'] = -1;
			$for_count_args['offset'] = 0;
			$for_count_args = apply_filters( 'wcfm_cpt3_args', $for_count_args );
			$wcfm_cpt3_count = get_posts( $for_count_args );
			$cpt3_count = count($wcfm_cpt3_count);
			
			// Get Filtered Post Count
			$args['posts_per_page'] = -1;
			$args['offset'] = 0;
			$wcfm_filterd_cpt3_array = get_posts( $args );
			$filtered_cpt3_count = count($wcfm_filterd_cpt3_array);
		} else {
			// Get Cpt3 Count
			$wcfm_cpt3_counts = wp_count_posts('post');
			foreach($wcfm_cpt3_counts as $wcfm_cpt3_type => $wcfm_cpt3_count ) {
				if( in_array( $wcfm_cpt3_type, array( 'publish', 'draft', 'pending' ) ) ) {
					$cpt3_count += $wcfm_cpt3_count;
				}
			}
			
			// Get Filtered Post Count
			$filtered_cpt3_count = $cpt3_count; 
		}
		
		// Generate Cpt3 JSON
		$wcfm_cpt3_json = '';
		$wcfm_cpt3_json = '{
															"draw": ' . $_POST['draw'] . ',
															"recordsTotal": ' . $cpt3_count . ',
															"recordsFiltered": ' . $filtered_cpt3_count . ',
															"data": ';
		if(!empty($wcfm_cpt3_array)) {
			$index = 0;
			$wcfm_cpt3_json_arr = array();
			foreach($wcfm_cpt3_array as $wcfm_cpt3_single) {
				
				// Thumb
				if( apply_filters( 'wcfm_is_allow_edit_cpt3', true ) ) {
					$wcfm_cpt3_json_arr[$index][] =  '<a href="' . get_wcfm_cpt3_manage_url( $wcfm_cpt3_single->ID ) . '"><img width="40" height="40" class="attachment-thumbnail size-thumbnail wp-post-image" src="' . get_the_post_thumbnail_url( $wcfm_cpt3_single->ID ) . '" /></a>';
				} else {
					$wcfm_cpt3_json_arr[$index][] =  '<img width="40" height="40" class="attachment-thumbnail size-thumbnail wp-post-image" src="' . get_the_post_thumbnail_url( $wcfm_cpt3_single->ID ) . '" />';
				}
				
				// Title
				if( apply_filters( 'wcfm_is_allow_edit_cpt3', true ) ) {
					$wcfm_cpt3_json_arr[$index][] =  '<a href="' . get_wcfm_cpt3_manage_url( $wcfm_cpt3_single->ID ) . '" class="wcfm_cpt3_title wcfm_dashboard_item_title">' . $wcfm_cpt3_single->post_title . '</a>';
				} else {
					if( $wcfm_cpt3_single->post_status == 'publish' ) {
						$wcfm_cpt3_json_arr[$index][] =  apply_filters( 'wcfm_cpt3_title_dashboard', $wcfm_cpt3_single->post_title, $wcfm_cpt3_single->ID );
					} elseif( apply_filters( 'wcfm_is_allow_edit_cpt3', true ) ) {
						$wcfm_cpt3_json_arr[$index][] =  apply_filters( 'wcfm_cpt3_title_dashboard', '<a href="' . get_wcfm_cpt3_manage_url( $wcfm_cpt3_single->ID ) . '" class="wcfm_cpt3_title wcfm_dashboard_item_title">' . $wcfm_cpt3_single->post_title . '</a>', $wcfm_cpt3_single->ID );
					} else {
						$wcfm_cpt3_json_arr[$index][] =  apply_filters( 'wcfm_cpt3_title_dashboard', $wcfm_cpt3_single->post_title, $wcfm_cpt3_single->ID );
					}
				}
				
				// Status
				if( $wcfm_cpt3_single->post_status == 'publish' ) {
					$wcfm_cpt3_json_arr[$index][] =  '<span class="cpt3-status cpt3-status-' . $wcfm_cpt3_single->post_status . '">' . __( 'Published', 'wcfm-cpt' ) . '</span>';
				} else {
					$wcfm_cpt3_json_arr[$index][] =  '<span class="cpt3-status cpt3-status-' . $wcfm_cpt3_single->post_status . '">' . __( ucfirst( $wcfm_cpt3_single->post_status ), 'wcfm-cpt' ) . '</span>';
				}
				
				// Views
				$wcfm_cpt3_json_arr[$index][] =  '<span class="view_count">' . (int) get_post_meta( $wcfm_cpt3_single->ID, '_wcfm_cpt3_views', true ) . '</span>';
				
				// Taxonomies
				$taxonomies = '';
				$product_taxonomies = get_object_taxonomies( WCFM_CPT_3, 'objects' );
				if( !empty( $product_taxonomies ) ) {
					foreach( $product_taxonomies as $product_taxonomy ) {
						if( !in_array( $product_taxonomy->name, array( 'post_tag' ) ) ) {
							if( $product_taxonomy->public && $product_taxonomy->show_ui && $product_taxonomy->meta_box_cb && $product_taxonomy->hierarchical ) {
								// Fetching Saved Values
								$taxonomy_values = get_the_terms( $wcfm_cpt3_single->ID, $product_taxonomy->name );
								if( !empty($taxonomy_values) ) {
									$taxonomies .= "<strong>" . __( $product_taxonomy->label, 'wc-frontend-manager' ) . '</strong>: ';
									$is_first = true;
									foreach($taxonomy_values as $pkey => $ptaxonomy) {
										if( !$is_first ) $taxonomies .= ', ';
										$is_first = false;
										$taxonomies .= '<a style="color: #dd4b39;" href="' . get_term_link( $ptaxonomy->term_id ) . '" target="_blank">' . $ptaxonomy->name . '</a>';
									}
								}
							}
						}
					}
				}
				
				if( !$taxonomies ) $taxonomies = '&ndash;';
				$wcfm_cpt3_json_arr[$index][] =  $taxonomies;
				
				// Author
				$author = get_user_by( 'id', $wcfm_cpt3_single->post_author );
				if( $author ) {
					$wcfm_cpt3_json_arr[$index][] =  $author->display_name;
				} else {
					$wcfm_cpt3_json_arr[$index][] =  '&ndash;';
				}
				
				// Date
				$wcfm_cpt3_json_arr[$index][] =  date_i18n( wc_date_format(), strtotime($wcfm_cpt3_single->post_date) );
				
				// Action
				$actions = '<a class="wcfm-action-icon" target="_blank" href="' . get_permalink( $wcfm_cpt3_single->ID ) . '"><span class="fa fa-eye text_tip" data-tip="' . esc_attr__( 'View', 'wcfm-cpt' ) . '"></span></a>';
				
				if( $wcfm_cpt3_single->post_status == 'publish' ) {
					$actions .= ( apply_filters( 'wcfm_is_allow_edit_cpt3', true ) ) ? '<a class="wcfm-action-icon" href="' . get_wcfm_cpt3_manage_url( $wcfm_cpt3_single->ID ) . '"><span class="fa fa-edit text_tip" data-tip="' . esc_attr__( 'Edit', 'wcfm-cpt' ) . '"></span></a>' : '';
					$actions .= ( apply_filters( 'wcfm_is_allow_delete_cpt3', true ) ) ? '<a class="wcfm-action-icon wcfm_cpt3_delete" href="#" data-cpt3id="' . $wcfm_cpt3_single->ID . '"><span class="fa fa-trash-o text_tip" data-tip="' . esc_attr__( 'Delete', 'wcfm-cpt' ) . '"></span></a>' : '';
				} else {
					$actions .= ( apply_filters( 'wcfm_is_allow_edit_cpt3', true ) ) ? '<a class="wcfm-action-icon" href="' . get_wcfm_cpt3_manage_url( $wcfm_cpt3_single->ID ) . '"><span class="fa fa-edit text_tip" data-tip="' . esc_attr__( 'Edit', 'wcfm-cpt' ) . '"></span></a>' : '';
					$actions .= ( apply_filters( 'wcfm_is_allow_delete_cpt3', true ) ) ? '<a class="wcfm_cpt3_delete wcfm-action-icon" href="#" data-cpt3id="' . $wcfm_cpt3_single->ID . '"><span class="fa fa-trash-o text_tip" data-tip="' . esc_attr__( 'Delete', 'wcfm-cpt' ) . '"></span></a>' : '';
				}
				
				$wcfm_cpt3_json_arr[$index][] =  apply_filters ( 'wcfm_cpt3_actions',  $actions, $wcfm_cpt3_single );
				
				
				$index++;
			}												
		}
		if( !empty($wcfm_cpt3_json_arr) ) $wcfm_cpt3_json .= json_encode($wcfm_cpt3_json_arr);
		else $wcfm_cpt3_json .= '[]';
		$wcfm_cpt3_json .= '
													}';
													
		echo $wcfm_cpt3_json;
	}
}