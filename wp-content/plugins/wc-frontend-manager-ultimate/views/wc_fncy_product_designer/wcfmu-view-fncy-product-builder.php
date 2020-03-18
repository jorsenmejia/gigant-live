<?php
global $WCFM, $WCFMu, $wpdb, $wp_query, $wp;

require_once(FPD_PLUGIN_ADMIN_DIR.'/modals/modal-edit-view-options.php');

$request_view_id = NULL;
if( isset( $wp->query_vars['wcfm-fncy-product-builder'] ) && !empty( $wp->query_vars['wcfm-fncy-product-builder'] ) ) {
	$request_view_id = $wp->query_vars['wcfm-fncy-product-builder'];
}

$vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
	
//get all fancy products
if( wcfm_is_vendor() ) {
	$fancy_products = FPD_Product::get_products( array(
		'order_by' 	=> 'title ASC',
		'where' => "user_id={$vendor_id }"
	) );
} else {
	$fancy_products = FPD_Product::get_products( array(
		'order_by' 	=> 'title ASC',
	) );
}

if(sizeof($fancy_products) == 0) {
	echo '<div class="updated"><p><strong>'.__('There are no products!', 'radykal').'</strong></p></div></div>';
	return;
}
?>

<div class="collapse wcfm-collapse" id="wcfm_build_listing">
	
	<div class="wcfm-page-headig">
		<span class="wcfmfa fa-object-group"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Product Builder', 'wc-frontend-manager-ultimate' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		<?php do_action( 'before_wcfm_parts-request' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e('Product Builder', 'wc-frontend-manager-ultimate' ); ?></h2>
			<div class="wcfm-clearfix"></div>
	  </div>
	  <div class="wcfm-clearfix"></div><br />
		

		<div class="wcfm-container">
			<div id="wcfm_build_listing_expander" class="wcfm-content">
			

				<div class="wrap" id="fpd-product-builder">
	
					<div  id="fpd-product-builder-head" class="fpd-panel radykal-clearfix">
	
						<p class="description"><?php _e( 'Select the view of a product:', 'radykal' ); ?></p>
						<select id="fpd-view-switcher" class="radykal-select2" style="width: 400px;">
							<?php
	
							if(is_array($fancy_products)) {
	
								foreach($fancy_products as $fancy_product_val) {
	
									$fancy_product_id = $fancy_product_val->ID;
									echo '<optgroup label="#'.$fancy_product_id.' - '.$fancy_product_val->title.'" id="'.$fancy_product_id.'">';
									$fancy_product = new FPD_Product($fancy_product_id);
									$views = $fancy_product->get_views();
	
									if(is_array($views)) {
	
										for($i=0; $i < sizeof($views); ++$i) {
	
											$view = $views[$i];
	
											//get first view
											if($request_view_id == NULL) {
												$request_view_id = $view->ID;
											}
	
											echo '<option value="'.$view->ID.'" '.selected( $request_view_id ,  $view->ID, false).'>'.$view->title.' - ('.$fancy_product_val->title.')</option>';
										}
	
									}
									echo '</optgroup>';
								}
	
							}
	
							?>
						</select>
						<?php
	
						//create instance of selected fancy view
						$fancy_view = new FPD_View( $request_view_id );
						$product_id = $fancy_view->get_product_id();
	
						//main ui layout
						$main_ui_layout = FPD_UI_Layout_Composer::get_layout(fpd_get_option('fpd_product_designer_ui_layout'));
						$plugin_options = $main_ui_layout['plugin_options'];
	
						//get dimensions from ui layout
						$stage_width =  $plugin_options['stageWidth'];
						$stage_height = $plugin_options['stageHeight'];
	
						//get dimensions from fancy product options
						$fancy_product = new FPD_Product($product_id);
						$fp_options = $fancy_product->get_options();
						if(isset($fp_options['stage_width']))
							$stage_width = (int) $fp_options['stage_width'];
						if(isset($fp_options['stage_height']))
							$stage_height = (int) $fp_options['stage_height'];
	
						$stage_width_temp = $stage_width;
						$stage_height_temp = $stage_height;
	
						//get dimensions from fancy view options
						$fv_options = $fancy_view->get_options();
						if(isset($fv_options['stage_width']))
							$stage_width = (int) $fv_options['stage_width'];
						if(isset($fv_options['stage_height']))
							$stage_height = (int) $fv_options['stage_height'];
	
						$mask_options = '';
						if(isset($fv_options['mask']))
							$mask_options = json_encode($fv_options['mask']);
	
						?>
	
						<script type="text/javascript">
	
							var fpdGlobalProductBuilderOpts = {
								stageWidthTemp: <?php echo $stage_width_temp; ?>,
								stageHeightTemp: <?php echo $stage_height_temp; ?>,
								printingBox: <?php echo isset($fv_options['printing_box']) ? json_encode($fv_options['printing_box']) : 'null'; ?>,
							};
	
						</script>
	
					</div><!-- #fpd-product-builder-head (Panel) -->
	
					<div id="fpd-manage-elements" class="fpd-panel radykal-clearfix">
	
						<div class="fpd-ui-blocker"></div>
						<h2><?php _e( 'Layers', 'radykal' ); ?></h2>
						<div class="wcfm-clearfix"></div>
						<div id="fpd-add-element">
						  <a href="#" class="add-new-h2 wcfm_submit_button" id="fpd-add-image-element"><?php _e( 'Add Image', 'radykal' ); ?></a>
						  <a href="#" class="add-new-h2 fpd-admin-tooltip wcfm_submit_button" id="fpd-add-upload-zone" title="<?php _e( 'The user can add one element into a placeholder image.', 'radykal' ); ?>"><?php _e( 'Add Upload Zone', 'radykal' ); ?></a>
						  <a href="#" class="add-new-h2 wcfm_submit_button" id="fpd-add-text-element"><?php _e( 'Add Text', 'radykal' ); ?></a>
						  <a href="#" class="add-new-h2 wcfm_submit_button" id="fpd-add-curved-text-element"><?php _e( 'Add Curved Text', 'radykal' ); ?></a>
							<a href="#" class="add-new-h2 fpd-admin-tooltip wcfm_submit_button" id="fpd-add-text-box-element" title="<?php _e('Text box has a fixed width.', 'radykal'); ?>"><?php _e( 'Add Text Box', 'radykal' ); ?></a>
						</div>
						<div class="wcfm-clearfix"></div>
						<p class="description"><?php _e( 'You can change the order by dragging the items.', 'radykal' ); ?></p>
						<div id="fpd-elements-list" class="fpd-clearfix"></div>
	
					</div><!-- #fpd-manage-elements (Panel) -->
	
					<div class="fpd-panel">
	
						<h2><?php _e('Canvas', 'radykal'); ?>
							<span class="description">
								<span id="fpd-stage-width-label"><?php echo $stage_width; ?></span>px *
								<span id="fpd-stage-height-label"><?php echo $stage_height; ?></span>px
							</span>
						</h2>
						<div class="wcfm-clearfix"></div>
	
						<div class="fpd-clearfix">
	
							<div id="fpd-actions-toolbar" class="fpd-clearfix">
								<span id="fpd-undo" class="fpd-admin-tooltip radykal-disabled" title="<?php _e('Undo', 'radykal'); ?>">
									<i class="fpd-admin-icon-undo"></i>
								</span>
								<span id="fpd-redo" class="fpd-admin-tooltip radykal-disabled" title="<?php _e('Redo', 'radykal'); ?>">
									<i class="fpd-admin-icon-redo"></i>
								</span>
								<span id="fpd-ruler" class="fpd-admin-tooltip" title="<?php _e('Ruler', 'radykal'); ?>" data-action="ruler">
									<i class="fpd-admin-icon-ruler"></i>
								</span>
	
								<div class="fpd-button-modal">
									<span id="fpd-edit-mask" class="fpd-toolbar-btn fpd-toggle"><?php _e( 'Edit Mask', 'radykal' ); ?></span>
									<div id="fpd-mask-toolbar" class="fpd-dialog radykal-settings-form">
										<div class="fpd-drag-handler"></div>
										<h4><?php _e('Use a SVG with one path as mask', 'radykal'); ?></h4>
										<div class="fpd-single-image-upload">
											<span class="fpd-remove"><span class="dashicons dashicons-minus"></span></span>
											<input type="hidden" name="url" />
										</div>
										<div class="radykal-columns-two">
											<div>
												<?php _e( 'Left', 'radykal' ); ?>
												<input type="number" name="left" placeholder="0" min="0" />
											</div>
											<div>
												<?php _e( 'Top', 'radykal' ); ?>
												<input type="number" name="top" placeholder="0" min="0" />
											</div>
										</div>
										<div class="radykal-columns-two">
											<div>
												<?php _e( 'Width', 'radykal' ); ?>
												<input type="number" name="mask_width" min="1" step="1" />
											</div>
											<div>
												<?php _e( 'Height', 'radykal' ); ?>
												<input type="number" name="mask_height" min="1" step="1" />
											</div>
										</div>
										<button id="fpd-save-mask-options" class="button-secondary"><?php _e( 'Save', 'radykal' ); ?></button>
										<div class="fpd-ui-blocker"></div>
									</div>
								</div>
	
								<div class="fpd-button-modal">
									<span id="fpd-edit-printing-box" class="fpd-toolbar-btn radykal-hidden fpd-toggle"><?php _e( 'Edit Printing Box', 'radykal' ); ?></span>
									<div id="fpd-printing-box-toolbar" class="radykal-settings-form fpd-dialog">
										<div class="fpd-drag-handler"></div>
										<h4><?php _e('Printing box', 'radykal'); ?></h4>
										<div class="radykal-columns-two">
											<div>
												<?php _e( 'Left', 'radykal' ); ?>
												<div class="fpd-input-button">
													<input type="number" name="left" placeholder="0" min="0" />
													<button class="button secondary fpd-admin-tooltip" id="fpd-pb-center-h" title="<?php _e( 'Center Horizontal', 'radykal' ); ?>">
														<span class="fpd-icon-align-vertical-middle"></span>
													</button>
												</div>
											</div>
											<div>
												<?php _e( 'Top', 'radykal' ); ?>
												<div class="fpd-input-button">
													<input type="number" name="top" placeholder="0" min="0" />
													<button class="button secondary fpd-admin-tooltip" id="fpd-pb-center-v" title="<?php _e( 'Center Vertical', 'radykal' ); ?>">
														<span class="fpd-icon-align-horizontal-middle"></span>
													</button>
												</div>
											</div>
										</div>
										<div class="radykal-columns-two">
											<div>
												<?php _e( 'Width', 'radykal' ); ?>
												<input type="number" name="pb_width" type="number" step="1" min="0" />
											</div>
											<div>
												<?php _e( 'Height', 'radykal' ); ?>
												<input type="number" name="pb_height" type="number" step="1" min="0" />
											</div>
										</div>
	
										<button id="fpd-save-printing-box-options" class="button-secondary"><?php _e( 'Save', 'radykal' ); ?></button>
										<div class="fpd-ui-blocker"></div>
									</div>
								</div>
	
								<div id="fpd-preview-buttons" class="fpd-button-group radykal-clearfix">
									<span class="fpd-admin-tooltip" title="<?php _e( 'Preview: Tablet', 'radykal' ); ?>" data-value="tablet">
										<i class="fpd-admin-icon-tablet"></i>
									</span>
									<span class="fpd-admin-tooltip" title="<?php _e( 'Preview: Mobile Phone', 'radykal' ); ?>" data-value="phone">
										<i class="fpd-admin-icon-phone"></i>
									</span>
								</div>
	
							</div><!-- #fpd-element-toolbar -->
	
						</div>
	
						<div id="fpd-canvas-container">
	
								<div id="fpd-preview-wrapper" data-stagewidth="<?php echo $stage_width; ?>" data-stageheight="<?php echo $stage_height; ?>" data-viewid="<?php echo $request_view_id; ?>" data-viewmask='<?php echo $mask_options; ?>'></div>
	
								<?php require_once(FPD_PLUGIN_ADMIN_DIR.'/views/html-product-builder-sidebar.php'); ?>
	
						</div><!-- #fpd-canvas-container -->
	
					</div><!-- Panel -->
	
					<div id="fpd-fixed-side">
						<span id="fpd-save-layers" class="fpd-ad-btn fpd-primary fpd-admin-tooltip" title="<?php _e('Save Layers', 'radykal'); ?>"><i class="fpd-icon-save"></i></span>
						<span id="fpd-edit-view-options" class="fpd-ad-btn fpd-admin-tooltip" title="<?php _e('View Options', 'radykal'); ?>"><i class="dashicons dashicons-admin-generic"></i></span>
					</div>
	
				</div>




				<div class="wcfm-clearfix"></div>
			</div>
			<div class="wcfm-clearfix"></div>
		</div>
	
		<div class="wcfm-clearfix"></div>
		<?php
		do_action( 'after_wcfm_parts_request' );
		?>
	</div>
</div>

