<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Cpt2 Controller
 *
 * @author 		WC Lovers
 * @package 	wcfm/controllers/cpt2
 * @version   1.0.0
 */

class WCFM_Cpt2_Controller {
	
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
							'post_type'        => WCFM_CPT_2,
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
		
		if( isset($_POST['cpt2_status']) && !empty($_POST['cpt2_status']) ) $args['post_status'] = $_POST['cpt2_status'];
  	
		// Multi Vendor Support
		$is_marketplace = wcfm_is_marketplace();
		if( $is_marketplace ) {
			if( isset($_POST['cpt2_vendor']) && !empty($_POST['cpt2_vendor']) ) {
				if( !wcfm_is_vendor() ) {
					$args['author'] = $_POST['cpt2_vendor'];
				}
			}
			if( wcfm_is_vendor() ) {
				$args['author'] = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
			}
		}
		
		$args = apply_filters( 'wcfm_cpt2_args', $args );
		
		$wcfm_cpt2_array = get_posts( $args );
		
		$cpt2_count = 0;
		$filtered_cpt2_count = 0;
		if( wcfm_is_vendor() ) {
			// Get Cpt2 Count
			$for_count_args['posts_per_page'] = -1;
			$for_count_args['offset'] = 0;
			$for_count_args = apply_filters( 'wcfm_cpt2_args', $for_count_args );
			$wcfm_cpt2_count = get_posts( $for_count_args );
			$cpt2_count = count($wcfm_cpt2_count);
			
			// Get Filtered Post Count
			$args['posts_per_page'] = -1;
			$args['offset'] = 0;
			$wcfm_filterd_cpt2_array = get_posts( $args );
			$filtered_cpt2_count = count($wcfm_filterd_cpt2_array);
		} else {
			// Get Cpt2 Count
			$wcfm_cpt2_counts = wp_count_posts('post');
			foreach($wcfm_cpt2_counts as $wcfm_cpt2_type => $wcfm_cpt2_count ) {
				if( in_array( $wcfm_cpt2_type, array( 'publish', 'draft', 'pending' ) ) ) {
					$cpt2_count += $wcfm_cpt2_count;
				}
			}
			
			// Get Filtered Post Count
			$filtered_cpt2_count = $cpt2_count; 
		}
		
		// Generate Cpt2 JSON
		$wcfm_cpt2_json = '';
		$wcfm_cpt2_json = '{
															"draw": ' . $_POST['draw'] . ',
															"recordsTotal": ' . $cpt2_count . ',
															"recordsFiltered": ' . $filtered_cpt2_count . ',
															"data": ';
		if(!empty($wcfm_cpt2_array)) {
			$index = 0;
			$wcfm_cpt2_json_arr = array();
			foreach($wcfm_cpt2_array as $wcfm_cpt2_single) {
				
				// Thumb
				if( apply_filters( 'wcfm_is_allow_edit_cpt2', true ) ) {
					$wcfm_cpt2_json_arr[$index][] =  '<a href="' . get_wcfm_cpt2_manage_url( $wcfm_cpt2_single->ID ) . '"><img width="40" height="40" class="attachment-thumbnail size-thumbnail wp-post-image" src="' . get_the_post_thumbnail_url( $wcfm_cpt2_single->ID ) . '" /></a>';
				} else {
					$wcfm_cpt2_json_arr[$index][] =  '<img width="40" height="40" class="attachment-thumbnail size-thumbnail wp-post-image" src="' . get_the_post_thumbnail_url( $wcfm_cpt2_single->ID ) . '" />';
				}
				
				// Title
				if( apply_filters( 'wcfm_is_allow_edit_cpt2', true ) ) {
					$wcfm_cpt2_json_arr[$index][] =  '<a href="' . get_wcfm_cpt2_manage_url( $wcfm_cpt2_single->ID ) . '" class="wcfm_cpt2_title wcfm_dashboard_item_title">' . $wcfm_cpt2_single->post_title . '</a>';
				} else {
					if( $wcfm_cpt2_single->post_status == 'publish' ) {
						$wcfm_cpt2_json_arr[$index][] =  apply_filters( 'wcfm_cpt2_title_dashboard', $wcfm_cpt2_single->post_title, $wcfm_cpt2_single->ID );
					} elseif( apply_filters( 'wcfm_is_allow_edit_cpt2', true ) ) {
						$wcfm_cpt2_json_arr[$index][] =  apply_filters( 'wcfm_cpt2_title_dashboard', '<a href="' . get_wcfm_cpt2_manage_url( $wcfm_cpt2_single->ID ) . '" class="wcfm_cpt2_title wcfm_dashboard_item_title">' . $wcfm_cpt2_single->post_title . '</a>', $wcfm_cpt2_single->ID );
					} else {
						$wcfm_cpt2_json_arr[$index][] =  apply_filters( 'wcfm_cpt2_title_dashboard', $wcfm_cpt2_single->post_title, $wcfm_cpt2_single->ID );
					}
				}
				
				// Status
				if( $wcfm_cpt2_single->post_status == 'publish' ) {
					$wcfm_cpt2_json_arr[$index][] =  '<span class="cpt2-status cpt2-status-' . $wcfm_cpt2_single->post_status . '">' . __( 'Published', 'wcfm-cpt' ) . '</span>';
				} else {
					$wcfm_cpt2_json_arr[$index][] =  '<span class="cpt2-status cpt2-status-' . $wcfm_cpt2_single->post_status . '">' . __( ucfirst( $wcfm_cpt2_single->post_status ), 'wcfm-cpt' ) . '</span>';
				}
				
				// Views
				$wcfm_cpt2_json_arr[$index][] =  '<span class="view_count">' . (int) get_post_meta( $wcfm_cpt2_single->ID, '_wcfm_cpt2_views', true ) . '</span>';
				
				// Taxonomies
				$taxonomies = '';
				$product_taxonomies = get_object_taxonomies( WCFM_CPT_2, 'objects' );
				if( !empty( $product_taxonomies ) ) {
					foreach( $product_taxonomies as $product_taxonomy ) {
						if( !in_array( $product_taxonomy->name, array( 'post_tag' ) ) ) {
							if( $product_taxonomy->public && $product_taxonomy->show_ui && $product_taxonomy->meta_box_cb && $product_taxonomy->hierarchical ) {
								// Fetching Saved Values
								$taxonomy_values = get_the_terms( $wcfm_cpt2_single->ID, $product_taxonomy->name );
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
				$wcfm_cpt2_json_arr[$index][] =  $taxonomies;
				
				// Author
				$author = get_user_by( 'id', $wcfm_cpt2_single->post_author );
				if( $author ) {
					$wcfm_cpt2_json_arr[$index][] =  $author->display_name;
				} else {
					$wcfm_cpt2_json_arr[$index][] =  '&ndash;';
				}
				
				// Date
				$wcfm_cpt2_json_arr[$index][] =  date_i18n( wc_date_format(), strtotime($wcfm_cpt2_single->post_date) );
				
				// Action
				$actions = '<a class="wcfm-action-icon" target="_blank" href="' . get_permalink( $wcfm_cpt2_single->ID ) . '"><span class="fa fa-eye text_tip" data-tip="' . esc_attr__( 'View', 'wcfm-cpt' ) . '"></span></a>';
				
				if( $wcfm_cpt2_single->post_status == 'publish' ) {
					$actions .= ( apply_filters( 'wcfm_is_allow_edit_cpt2', true ) ) ? '<a class="wcfm-action-icon" href="' . get_wcfm_cpt2_manage_url( $wcfm_cpt2_single->ID ) . '"><span class="fa fa-edit text_tip" data-tip="' . esc_attr__( 'Edit', 'wcfm-cpt' ) . '"></span></a>' : '';
					$actions .= ( apply_filters( 'wcfm_is_allow_delete_cpt2', true ) ) ? '<a class="wcfm-action-icon wcfm_cpt2_delete" href="#" data-cpt2id="' . $wcfm_cpt2_single->ID . '"><span class="fa fa-trash-o text_tip" data-tip="' . esc_attr__( 'Delete', 'wcfm-cpt' ) . '"></span></a>' : '';
				} else {
					$actions .= ( apply_filters( 'wcfm_is_allow_edit_cpt2', true ) ) ? '<a class="wcfm-action-icon" href="' . get_wcfm_cpt2_manage_url( $wcfm_cpt2_single->ID ) . '"><span class="fa fa-edit text_tip" data-tip="' . esc_attr__( 'Edit', 'wcfm-cpt' ) . '"></span></a>' : '';
					$actions .= ( apply_filters( 'wcfm_is_allow_delete_cpt2', true ) ) ? '<a class="wcfm_cpt2_delete wcfm-action-icon" href="#" data-cpt2id="' . $wcfm_cpt2_single->ID . '"><span class="fa fa-trash-o text_tip" data-tip="' . esc_attr__( 'Delete', 'wcfm-cpt' ) . '"></span></a>' : '';
				}
				
				$wcfm_cpt2_json_arr[$index][] =  apply_filters ( 'wcfm_cpt2_actions',  $actions, $wcfm_cpt2_single );
				
				
				$index++;
			}												
		}
		if( !empty($wcfm_cpt2_json_arr) ) $wcfm_cpt2_json .= json_encode($wcfm_cpt2_json_arr);
		else $wcfm_cpt2_json .= '[]';
		$wcfm_cpt2_json .= '
													}';
													
		echo $wcfm_cpt2_json;
	}
}