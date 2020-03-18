<?php
global $WCFM, $wp_query;

$wcfm_is_allow_manage_cpt2 = apply_filters( 'wcfm_is_allow_manage_cpt2', true );
if( !$wcfm_is_allow_manage_cpt2 ) {
	wcfm_restriction_message_show( WCFM_CPT_2_LABEL );
	return;
}

$wcfmu_cpt2_menus = apply_filters( 'wcfmu_cpt2_menus', array( 'any' => __( 'All', 'wcfm-cpt'), 
																																			'publish' => __( 'Published', 'wcfm-cpt'),
																																			'draft' => __( 'Draft', 'wcfm-cpt'),
																																			'pending' => __( 'Pending', 'wcfm-cpt')
																																		) );

$cpt2_status = ! empty( $_GET['cpt2_status'] ) ? sanitize_text_field( $_GET['cpt2_status'] ) : 'any';

$current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
if( current_user_can( 'administrator' ) ) $current_user_id = 0;
$count_cpt2 = array();
$count_cpt2['publish'] = wcfm_get_user_posts_count( $current_user_id, WCFM_CPT_2, 'publish' );
$count_cpt2['pending'] = wcfm_get_user_posts_count( $current_user_id, WCFM_CPT_2, 'pending' );
$count_cpt2['draft']   = wcfm_get_user_posts_count( $current_user_id, WCFM_CPT_2, 'draft' );
$count_cpt2['any']     = $count_cpt2['publish'] + $count_cpt2['pending'] + $count_cpt2['draft'];

?>

<div class="collapse wcfm-collapse" id="wcfm_cpt2_listing">
	
	<div class="wcfm-page-headig">
		<span class="fa fa-codepen"></span>
		<span class="wcfm-page-heading-text"><?php _e( WCFM_CPT_2_LABEL, 'wcfm-cpt' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		<?php do_action( 'before_wcfm_cpt2' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<ul class="wcfm_cpt2_menus">
				<?php
				$is_first = true;
				foreach( $wcfmu_cpt2_menus as $wcfmu_cpt2_menu_key => $wcfmu_cpt2_menu) {
					?>
					<li class="wcfm_cpt2_menu_item">
						<?php
						if($is_first) $is_first = false;
						else echo " | ";
						?>
						<a class="<?php echo ( $wcfmu_cpt2_menu_key == $cpt2_status ) ? 'active' : ''; ?>" href="<?php echo get_wcfm_cpt2_url( $wcfmu_cpt2_menu_key ); ?>"><?php echo $wcfmu_cpt2_menu . ' ('. $count_cpt2[$wcfmu_cpt2_menu_key] .')'; ?></a>
					</li>
					<?php
				}
				?>
			</ul>
			
			<?php
			if( $allow_wp_admin_view = apply_filters( 'wcfm_allow_wp_admin_view', true ) ) {
				?>
				<a target="_blank" class="wcfm_wp_admin_view text_tip" href="<?php echo admin_url('edit.php?post_type='.WCFM_CPT_2); ?>" data-tip="<?php _e( 'WP Admin View', 'wcfm-cpt' ); ?>"><span class="fab fa-wordpress"></span></a>
				<?php
			}
			
			if( $has_new = apply_filters( 'wcfm_add_new_cpt2_sub_menu', true ) ) {
				echo '<a id="add_new_cpt2_dashboard" class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_cpt2_manage_url().'" data-tip="' . __('Add New ' . WCFM_CPT_2_LABEL, 'wcfm-cpt') . '"><span class="fa fa-cube"></span><span class="text">' . __( 'Add New', 'wcfm-cpt') . '</span></a>';
			}
			?>
			
			<?php	echo apply_filters( 'wcfm_cpt2_limit_label', '' ); ?>
			
			<div class="wcfm-clearfix"></div>
		</div>
		<div class="wcfm-clearfix"></div><br />
		
		<div class="wcfm_cpt2_filter_wrap wcfm_products_filter_wrap  wcfm_filters_wrap">
			<?php	
			if( $wcfm_is_cpt2_vendor_filter = apply_filters( 'wcfm_is_cpt2_vendor_filter', true ) ) {
				$is_marketplace = wcfm_is_marketplace();
				if( $is_marketplace ) {
					if( !wcfm_is_vendor() ) {
						$vendor_arr = array(); //$WCFM->wcfm_vendor_support->wcfm_get_vendor_list();
						$WCFM->wcfm_fields->wcfm_generate_form_field( array(
																											"dropdown_vendor" => array( 'type' => 'select', 'options' => $vendor_arr, 'attributes' => array( 'style' => 'width: 150px;' ) )
																											 ) );
					}
				}
			}
			?>
		</div>
		
		<div class="wcfm-container">
			<div id="wcfm_cpt2_listing_expander" class="wcfm-content">
				<table id="wcfm-cpt2" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th><span class="fa fa-image text_tip" data-tip="<?php _e( 'Image', 'wcfm-cpt' ); ?>"></span></th>
							<th style="max-width: 250px;"><?php _e( 'Name', 'wcfm-cpt' ); ?></th>
							<th><?php _e( 'Status', 'wcfm-cpt' ); ?></th>
							<th><span class="fa fa-eye text_tip" data-tip="<?php _e( 'Views', 'wcfm-cpt' ); ?>"></span></th>
							<th><?php _e( 'Taxonomies', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Author', 'wcfm-cpt' ); ?></th>
							<th><?php _e( 'Date', 'wcfm-cpt' ); ?></th>
							<th><?php _e( 'Actions', 'wcfm-cpt' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th><span class="fa fa-image text_tip" data-tip="<?php _e( 'Image', 'wcfm-cpt' ); ?>"></span></th>
							<th style="max-width: 250px;"><?php _e( 'Name', 'wcfm-cpt' ); ?></th>
							<th><?php _e( 'Status', 'wcfm-cpt' ); ?></th>
							<th><span class="fa fa-eye text_tip" data-tip="<?php _e( 'Views', 'wcfm-cpt' ); ?>"></span></th>
							<th><?php _e( 'Taxonomies', 'wc-frontend-manager' ); ?></th>
							<th><?php _e( 'Author', 'wcfm-cpt' ); ?></th>
							<th><?php _e( 'Date', 'wcfm-cpt' ); ?></th>
							<th><?php _e( 'Actions', 'wcfm-cpt' ); ?></th>
						</tr>
					</tfoot>
				</table>
				<div class="wcfm-clearfix"></div>
			</div>
		</div>
		<?php
		do_action( 'after_wcfm_cpt2' );
		?>
	</div>
</div>