<?php
global $wp, $WCFM, $wc_cpt3_attributes;

$wcfm_is_allow_manage_cpt3 = apply_filters( 'wcfm_is_allow_manage_cpt3', true );
if( !$wcfm_is_allow_manage_cpt3 ) {
	wcfm_restriction_message_show( WCFM_CPT_3_LABEL );
	return;
}

if( isset( $wp->query_vars['wcfm-cpt3-manage'] ) && empty( $wp->query_vars['wcfm-cpt3-manage'] ) ) {
	if( !apply_filters( 'wcfm_is_allow_add_cpt3', true ) ) {
		wcfm_restriction_message_show( "Add " . WCFM_CPT_3_LABEL );
		return;
	}
	if( !apply_filters( 'wcfm_is_allow_cpt3_limit', true ) ) {
		wcfm_restriction_message_show( "Cpt3 Limit Reached" );
		return;
	}
} elseif( isset( $wp->query_vars['wcfm-cpt3-manage'] ) && !empty( $wp->query_vars['wcfm-cpt3-manage'] ) ) {
	$wcfm_cpt3_single = get_post( $wp->query_vars['wcfm-cpt3-manage'] );
	if( $wcfm_cpt3_single->post_status == 'publish' ) {
		if( !apply_filters( 'wcfm_is_allow_edit_cpt3', true ) ) {
			wcfm_restriction_message_show( "Edit " . WCFM_CPT_3_LABEL );
			return;
		}
	}
	if( wcfm_is_vendor() ) {
		$is_cpt3_from_vendor = $WCFM->wcfm_vendor_support->wcfm_is_article_from_vendor( $wp->query_vars['wcfm-cpt3-manage'] );
		if( !$is_cpt3_from_vendor ) {
			wcfm_restriction_message_show( "Restricted " . WCFM_CPT_3_LABEL );
			return;
		}
	}
}

$cpt3_id = 0;
$cpt3 = array();
$title = '';
$excerpt = '';
$description = '';

$featured_img = '';

if( isset( $wp->query_vars['wcfm-cpt3-manage'] ) && !empty( $wp->query_vars['wcfm-cpt3-manage'] ) ) {
	
	$cpt3_id = $wp->query_vars['wcfm-cpt3-manage'];
	$wcfm_cpt3_single = get_post($cpt3_id);
	// Fetching Cpt3 Data
	if($wcfm_cpt3_single && !empty($wcfm_cpt3_single)) {
		
		$title = $wcfm_cpt3_single->post_title;
		
		$excerpt = wpautop( $wcfm_cpt3_single->post_excerpt );
		$description = wpautop( $wcfm_cpt3_single->post_content );
		
		$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
		if( !$rich_editor && apply_filters( 'wcfm_is_allow_editor_newline_replace', false ) ) {
			$breaks = apply_filters( 'wcfm_editor_newline_generators', array("<br />","<br>","<br/>") ); 
			
			$excerpt = str_ireplace( $breaks, "\r\n", $excerpt );
			$excerpt = strip_tags( $excerpt );
			
			$description = str_ireplace( $breaks, "\r\n", $description );
			$description = strip_tags( $description );
		}
		
		// Cpt3 Images
		$featured_img = (get_post_thumbnail_id($cpt3_id)) ? get_post_thumbnail_id($cpt3_id) : '';
		if($featured_img) $featured_img = wp_get_attachment_url($featured_img);
		if(!$featured_img) $featured_img = '';
		
	}
}

$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );

$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
$wpeditor = apply_filters( 'wcfm_is_allow_cpt3_wpeditor', 'wpeditor' );
if( $wpeditor && $rich_editor ) {
	$rich_editor = 'wcfm_wpeditor';
} else {
	$wpeditor = 'textarea';
}
?>

<div class="collapse wcfm-collapse" id="">
  <div class="wcfm-page-headig">
		<span class="fa fa-codepen"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Manage ' . WCFM_CPT_3_LABEL, 'wcfm-cpt' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		<?php do_action( 'before_wcfm_cpt3_simple' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php if( $cpt3_id ) { _e('Edit ' . WCFM_CPT_3_LABEL, 'wcfm-cpt' ); } else { _e('Add ' . WCFM_CPT_3_LABEL, 'wcfm-cpt' ); } ?></h2>
			<?php
			if( $cpt3_id ) {
				?>
				<span class="cpt3-status cpt3-status-<?php echo $wcfm_cpt3_single->post_status; ?>"><?php if( $wcfm_cpt3_single->post_status == 'publish' ) { _e( 'Published', 'wcfm-cpt' ); } else { _e( ucfirst( $wcfm_cpt3_single->post_status ), 'wcfm-cpt' ); } ?></span>
				<?php
				if( $wcfm_cpt3_single->post_status == 'publish' ) {
					echo '<a target="_blank" href="' . get_permalink( $wcfm_cpt3_single->ID ) . '">';
					?>
					<span class="view_count"><span class="fa fa-eye text_tip" data-tip="<?php _e( 'Views', 'wcfm-cpt' ); ?>"></span>
					<?php
					echo get_post_meta( $wcfm_cpt3_single->ID, '_wcfm_cpt3_views', true ) . '</span></a>';
				} else {
					echo '<a target="_blank" href="' . get_permalink( $wcfm_cpt3_single->ID ) . '">';
					?>
					<span class="view_count"><span class="fa fa-eye text_tip" data-tip="<?php _e( 'Preview', 'wcfm-cpt' ); ?>"></span>
					<?php
					echo '</a>';
				}
			}
			
			if( $allow_wp_admin_view = apply_filters( 'wcfm_allow_wp_admin_view', true ) ) {
				?>
				<a target="_blank" class="wcfm_wp_admin_view text_tip" href="<?php echo admin_url('post-new.php?post_type='.WCFM_CPT_3); ?>" data-tip="<?php _e( 'WP Admin View', 'wcfm-cpt' ); ?>"><span class="fab fa-wordpress"></span></a>
				<?php
			}
			
			if( $has_new = apply_filters( 'wcfm_add_new_cpt3_sub_menu', true ) ) {
				echo '<a id="add_new_cpt3_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_cpt3_manage_url().'" data-tip="' . __('Add New '.WCFM_CPT_3_LABEL, 'wcfm-cpt') . '"><span class="fa fa-cube"></span><span class="text">' . __( 'Add New', 'wcfm-cpt') . '</span></a>';
			}
			?>
			
			<div class="wcfm-clearfix"></div>
		</div>
		<div class="wcfm-clearfix"></div><br />
		
		<form id="wcfm_cpt3_manage_form" class="wcfm">
		
			<?php do_action( 'begin_wcfm_cpt3_manage_form' ); ?>
			
			<!-- collapsible -->
			<div class="wcfm-container">
				<div id="wcfm_cpt3_manage_form_general_expander" class="wcfm-content">
				  <div class="wcfm_cpt3_manager_general_fields">
						<?php
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_cpt3_manage_fields_general', array(
																																																"title" => array( 'placeholder' => __( WCFM_CPT_3_LABEL.' Title', 'wcfm-cpt') , 'type' => 'text', 'class' => 'wcfm-text wcfm_cpt3_title wcfm_full_ele', 'value' => $title),
																																													), $cpt3_id ) );
							
						?>
						<div class="wcfm_clearfix"></div>
						
						<?php if( !$wcfm_is_category_checklist = apply_filters( 'wcfm_is_category_checklist', true ) ) { ?>
						  <?php if( $wcfm_is_allow_category = apply_filters( 'wcfm_is_allow_category', true ) ) { $catlimit = apply_filters( 'wcfm_catlimit', -1 ); ?>
								<?php
								if( $wcfm_is_allow_custom_taxonomy = apply_filters( 'wcfm_is_allow_custom_taxonomy', true ) ) {
									$cpt3_taxonomies = get_object_taxonomies( WCFM_CPT_3, 'objects' );
									if( !empty( $cpt3_taxonomies ) ) {
										foreach( $cpt3_taxonomies as $cpt3_taxonomy ) {
											if( !in_array( $cpt3_taxonomy->name, array( 'post_tag' ) ) ) {
												if( $cpt3_taxonomy->public && $cpt3_taxonomy->show_ui && $cpt3_taxonomy->meta_box_cb && $cpt3_taxonomy->hierarchical ) {
													// Fetching Saved Values
													$taxonomy_values_arr = array();
													if($cpt3 && !empty($cpt3)) {
														$taxonomy_values = get_the_terms( $cpt3_id, $cpt3_taxonomy->name );
														if( !empty($taxonomy_values) ) {
															foreach($taxonomy_values as $pkey => $ptaxonomy) {
																$taxonomy_values_arr[] = $ptaxonomy->term_id;
															}
														}
													}
													?>
													<p class="wcfm_title"><strong><?php _e( $cpt3_taxonomy->label, 'wcfm-cpt' ); ?></strong></p><label class="screen-reader-text" for="<?php echo $cpt3_taxonomy->name; ?>"><?php _e( $cpt3_taxonomy->label, 'wcfm-cpt' ); ?></label>
													<select id="<?php echo $cpt3_taxonomy->name; ?>" name="cpt3_custom_taxonomies[<?php echo $cpt3_taxonomy->name; ?>][]" class="wcfm-select cpt3_taxonomies " multiple="multiple" style="width: 100%; margin-bottom: 10px;">
														<?php
															$cpt3_taxonomy_terms   = get_terms( $cpt3_taxonomy->name, 'orderby=name&hide_empty=0&parent=0' );
															if ( $cpt3_taxonomy_terms ) {
																$WCFM->library->generateTaxonomyHTML( $cpt3_taxonomy->name, $cpt3_taxonomy_terms, $taxonomy_values_arr );
															}
														?>
													</select>
													<?php
												}
											}
										}
									}
								}
							}
							
							if( $wcfm_is_allow_tags = apply_filters( 'wcfm_is_allow_tags', true ) ) {
								
								if( $wcfm_is_allow_custom_taxonomy = apply_filters( 'wcfm_is_allow_custom_taxonomy', true ) ) {
									$cpt3_taxonomies = get_object_taxonomies( WCFM_CPT_3, 'objects' );
									if( !empty( $cpt3_taxonomies ) ) {
										foreach( $cpt3_taxonomies as $cpt3_taxonomy ) {
											if( !in_array( $cpt3_taxonomy->name, array( 'post_tag' ) ) ) {
												if( $cpt3_taxonomy->public && $cpt3_taxonomy->show_ui && $cpt3_taxonomy->meta_box_cb && !$cpt3_taxonomy->hierarchical ) {
													// Fetching Saved Values
													$taxonomy_values_arr = wp_get_post_terms($cpt3_id, $cpt3_taxonomy->name, array("fields" => "names"));
													$taxonomy_values = implode(',', $taxonomy_values_arr);
													$WCFM->wcfm_fields->wcfm_generate_form_field( array(  $cpt3_taxonomy->name => array( 'label' => $cpt3_taxonomy->label, 'name' => 'cpt3_custom_taxonomies_flat[' . $cpt3_taxonomy->name . '][]', 'type' => 'textarea', 'class' => 'wcfm-textarea  wcfm_full_ele', 'label_class' => 'wcfm_title wcfm_full_ele', 'value' => $taxonomy_values, 'placeholder' => __('Separate Cpt3 ' . $cpt3_taxonomy->label . ' with commas', 'wcfm-cpt') )
																																			) );
												}
											}
										}
									}
								}
							}
							?>
						<?php } ?>
						<?php if( $wcfm_is_category_checklist = apply_filters( 'wcfm_is_category_checklist', true ) ) { ?>
							<div class="wcfm_clearfix"></div><br />
							<div class="wcfm_cpt3_manager_content_fields">
								<?php
								$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_cpt3_manage_fields_content', array(
																																																			//"excerpt" => array('label' => __('Short Description', 'wcfm-cpt') , 'type' => $wpeditor, 'class' => 'wcfm-textarea  wcfm_full_ele ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_ele ' . $rich_editor, 'rows' => 5, 'value' => $excerpt),
																																																			"description" => array('label' => __('Description', 'wcfm-cpt') , 'type' => $wpeditor, 'class' => 'wcfm-textarea  wcfm_full_ele ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_ele ' . $rich_editor, 'value' => $description),
																																																			"cpt3_id" => array('type' => 'hidden', 'value' => $cpt3_id)
																																															), $cpt3_id ) );
								?>
							</div>
						<?php } ?>
					</div>
					<div class="wcfm_cpt3_manager_gallery_fields">
					  <?php
					  if( $wcfm_is_allow_featured = apply_filters( 'wcfm_is_allow_featured', true ) ) {
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_cpt3_manage_fields_gallery', array(  "featured_img" => array( 'type' => 'upload', 'class' => 'wcfm-cpt3-feature-upload', 'label_class' => 'wcfm_title', 'prwidth' => 250, 'value' => $featured_img)
																																													), $cpt3_id ) );
						}
						?>
					
						<?php if( $wcfm_is_category_checklist = apply_filters( 'wcfm_is_category_checklist', true ) ) { ?>
							<?php 
							if( $wcfm_is_allow_category = apply_filters( 'wcfm_is_allow_category', true ) ) { 
								$catlimit = apply_filters( 'wcfm_catlimit', -1 ); 
								
								if( $wcfm_is_allow_custom_taxonomy = apply_filters( 'wcfm_is_allow_custom_taxonomy', true ) ) {
									$cpt3_taxonomies = get_object_taxonomies( WCFM_CPT_3, 'objects' );
									if( !empty( $cpt3_taxonomies ) ) {
										foreach( $cpt3_taxonomies as $cpt3_taxonomy ) {
											if( !in_array( $cpt3_taxonomy->name, array( 'post_tag' ) ) ) {
												if( $cpt3_taxonomy->public && $cpt3_taxonomy->show_ui && $cpt3_taxonomy->meta_box_cb && $cpt3_taxonomy->hierarchical ) {
													// Fetching Saved Values
													$taxonomy_values_arr = array();
													$taxonomy_values = get_the_terms( $cpt3_id, $cpt3_taxonomy->name );
													if( !empty($taxonomy_values) ) {
														foreach($taxonomy_values as $pkey => $ptaxonomy) {
															$taxonomy_values_arr[$ptaxonomy->term_id] = $ptaxonomy->term_id;
														}
													}
													?>
													<div class="wcfm_clearfix"></div>
													<div class="wcfm_cpt3_manager_cats_checklist_fields wcfm_cpt3_taxonomy_<?php echo $cpt3_taxonomy->name; ?>">
														<p class="wcfm_title wcfm_full_ele"><strong><?php _e( $cpt3_taxonomy->label, 'wcfm-cpt' ); ?></strong></p><label class="screen-reader-text" for="<?php echo $cpt3_taxonomy->name; ?>"><?php _e( $cpt3_taxonomy->label, 'wcfm-cpt' ); ?></label>
														<ul id="<?php echo $cpt3_taxonomy->name; ?>" class="cpt3_taxonomy_checklist ">
															<?php
																$cpt3_taxonomy_terms   = get_terms( $cpt3_taxonomy->name, 'orderby=name&hide_empty=0&parent=0' );
																if ( $cpt3_taxonomy_terms ) {
																	$WCFM->library->generateTaxonomyHTML( $cpt3_taxonomy->name, $cpt3_taxonomy_terms, $taxonomy_values_arr, '', true, true );
																}
															?>
														</ul>
													</div>
													<?php
												}
											}
										}
									}
								}
							}
							
							if( $wcfm_is_allow_tags = apply_filters( 'wcfm_is_allow_tags', true ) ) {
									
									if( $wcfm_is_allow_custom_taxonomy = apply_filters( 'wcfm_is_allow_custom_taxonomy', true ) ) {
										$cpt3_taxonomies = get_object_taxonomies( WCFM_CPT_3, 'objects' );
										if( !empty( $cpt3_taxonomies ) ) {
											foreach( $cpt3_taxonomies as $cpt3_taxonomy ) {
												if( !in_array( $cpt3_taxonomy->name, array( 'post_tag' ) ) ) {
													if( $cpt3_taxonomy->public && $cpt3_taxonomy->show_ui && $cpt3_taxonomy->meta_box_cb && !$cpt3_taxonomy->hierarchical ) {
														// Fetching Saved Values
														$taxonomy_values_arr = wp_get_post_terms($cpt3_id, $cpt3_taxonomy->name, array("fields" => "names"));
														$taxonomy_values = implode(',', $taxonomy_values_arr);
														$WCFM->wcfm_fields->wcfm_generate_form_field( array(  $cpt3_taxonomy->name => array( 'label' => $cpt3_taxonomy->label, 'name' => 'cpt3_custom_taxonomies_flat[' . $cpt3_taxonomy->name . '][]', 'type' => 'textarea', 'class' => 'wcfm-textarea  wcfm_full_ele', 'label_class' => 'wcfm_title wcfm_full_ele', 'value' => $taxonomy_values, 'placeholder' => __('Separate Cpt3 ' . $cpt3_taxonomy->label . ' with commas', 'wcfm-cpt') )
																																				) );
													}
												}
											}
										}
									}
								}
							?>
						<?php } ?>
						
						<?php do_action( 'wcfm_cpt3_manager_gallery_fields_end', $cpt3_id ); ?>
					</div>
				</div>
				
				<?php if( !$wcfm_is_category_checklist = apply_filters( 'wcfm_is_category_checklist', true ) ) { ?>
					<div class="wcfm-content">
						<div class="wcfm_cpt3_manager_content_fields">
							<?php
							$rich_editor = apply_filters( 'wcfm_is_allow_rich_editor', 'rich_editor' );
							$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_cpt3_manage_fields_content', array(
																																																		//"excerpt" => array('label' => __('Short Description', 'wcfm-cpt') , 'type' => $wpeditor, 'class' => 'wcfm-textarea  wcfm_full_ele ' . $rich_editor , 'label_class' => 'wcfm_title wcfm_full_ele ' . $rich_editor, 'rows' => 5, 'value' => $excerpt),
																																																		"description" => array('label' => __('Description', 'wcfm-cpt') , 'type' => $wpeditor, 'class' => 'wcfm-textarea  wcfm_full_ele ' . $rich_editor, 'label_class' => 'wcfm_title wcfm_full_ele ' . $rich_editor, 'value' => $description),
																																																		"cpt3_id" => array('type' => 'hidden', 'value' => $cpt3_id)
																																														), $cpt3_id ) );
							?>
						</div>
					</div>
				<?php } ?>
			</div>
			<!-- end collapsible -->
			<div class="wcfm_clearfix"></div><br />
			
			<!-- wrap -->
			<div class="wcfm-tabWrap">
			  <?php do_action( 'after_wcfm_cpt3_manage_general', $cpt3_id ); ?>
			
			  <?php include( 'wcfm-view-cpt3-manage-tabs.php' ); ?>
				
				<?php do_action( 'end_wcfm_cpt3_manage', $cpt3_id ); ?>
			
			</div> <!-- tabwrap -->
			
			<div id="wcfm_cpt3_simple_submit" class="wcfm_form_simple_submit_wrapper">
			  <div class="wcfm-message" tabindex="-1"></div>
			  
			  <?php if( $cpt3_id && ( $wcfm_cpt3_single->post_status == 'publish' ) ) { ?>
				  <input type="submit" name="submit-data" value="<?php if( apply_filters( 'wcfm_is_allow_publish_live_cpt3', true ) ) { _e( 'Submit', 'wcfm-cpt' ); } else { _e( 'Submit for Review', 'wcfm-cpt' ); } ?>" id="wcfm_cpt3_simple_submit_button" class="wcfm_submit_button" />
				<?php } else { ?>
					<input type="submit" name="submit-data" value="<?php if( apply_filters( 'wcfm_is_allow_publish_cpt3', true ) ) { _e( 'Submit', 'wcfm-cpt' ); } else { _e( 'Submit for Review', 'wcfm-cpt' ); } ?>" id="wcfm_cpt3_simple_submit_button" class="wcfm_submit_button" />
				<?php } ?>
				<?php if( apply_filters( 'wcfm_is_allow_draft_published_cpt3', true ) && apply_filters( 'wcfm_is_allow_add_cpt3', true ) ) { ?>
				  <input type="submit" name="draft-data" value="<?php _e( 'Draft', 'wcfm-cpt' ); ?>" id="wcfm_cpt3_simple_draft_button" class="wcfm_submit_button" />
				<?php } ?>
				
				<?php
				if( $cpt3_id && ( $wcfm_cpt3_single->post_status != 'publish' ) ) {
					echo '<a target="_blank" href="' . get_permalink( $wcfm_cpt3_single->ID ) . '">';
					?>
					<input type="button" class="wcfm_submit_button" value="<?php _e( 'Preview', 'wcfm-cpt' ); ?>" />
					<?php
					echo '</a>';
				} elseif( $cpt3_id && ( $wcfm_cpt3_single->post_status == 'publish' ) ) {
					echo '<a target="_blank" href="' . get_permalink( $wcfm_cpt3_single->ID ) . '">';
					?>
					<input type="button" class="wcfm_submit_button" value="<?php _e( 'View', 'wcfm-cpt' ); ?>" />
					<?php
					echo '</a>';
				}
				?>
			</div>
		</form>
		<?php
		do_action( 'after_wcfm_cpt3_manage' );
		?>
	</div>
</div>