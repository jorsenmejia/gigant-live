<?php
/**
 * WCFM plugin view
 *
 * WCFM WC Fancy Product Manage View
 *
 * @author 		WC Lovers
 * @package 	wcfmu/views/fancy_products
 * @version   5.4.6
 */
 
global $wp, $WCFM, $WCFMu, $post, $woocommerce;

if( !apply_filters( 'wcfm_is_pref_fancy_product_designer' , true ) || !apply_filters( 'wcfm_is_allow_fancy_product_designer', true ) ) {
	return;
}

$product_id = 0;
$custom_fields = array();

if( isset( $wp->query_vars['wcfm-products-manage'] ) && !empty( $wp->query_vars['wcfm-products-manage'] ) ) {
	$product_id = $wp->query_vars['wcfm-products-manage'];
	
	if( $product_id ) {
		$custom_fields = get_post_custom( $product_id );
	}
}

//DESKTOP
$source_type = isset( $custom_fields["fpd_source_type"] ) ? $custom_fields["fpd_source_type"][0] : "category";
$current_ind_settings = isset( $custom_fields["fpd_product_settings"] ) ? $custom_fields["fpd_product_settings"][0] : "";

$selected_categories = isset( $custom_fields["fpd_product_categories"] ) ? $custom_fields["fpd_product_categories"][0] : "";
if( is_serialized($selected_categories) )
	$selected_categories = unserialize($selected_categories); //V2.0, saved as array in db
else
	$selected_categories = empty($selected_categories)? array() : explode(',', $selected_categories); //V3.0 saved as string in db

$selected_products = isset( $custom_fields["fpd_products"] ) ? $custom_fields["fpd_products"][0] : "";
if( is_serialized($selected_products) )
	$selected_products = unserialize($selected_products); //V2.0, saved as array in db
else
	$selected_products = empty($selected_products)? array() : explode(',', $selected_products); //V3.0 saved as string in db

//MOBILE
$source_type_mobile = isset( $custom_fields["fpd_source_type_mobile"] ) ? $custom_fields["fpd_source_type_mobile"][0] : "category";

$selected_categories_mobile = isset( $custom_fields["fpd_product_categories_mobile"] ) ? $custom_fields["fpd_product_categories_mobile"][0] : "";

$selected_categories_mobile = empty($selected_categories_mobile)? array() : explode(',', $selected_categories_mobile);

$selected_products_mobile = isset( $custom_fields["fpd_products_mobile"] ) ? $custom_fields["fpd_products_mobile"][0] : "";
$selected_products_mobile = empty($selected_products_mobile)? array() : explode(',', $selected_products_mobile);

?>
<div class="page_collapsible products_manage_wc_fancy_product simple variable external grouped booking" id="wcfm_products_manage_form_wc_fancy_products_head"><label class="wcfmfa fa-object-group"></label><?php _e('Fancy Product', 'wc-frontend-manager-ultimate'); ?><span></span></div>
<div class="wcfm-container simple variable external grouped booking">
	<div id="wcfm_products_manage_form_wc_fancy_products_expander" class="wcfm-content">
	  <h2><?php _e('Fancy Product Designer', 'wc-frontend-manager-ultimate'); ?></h2>
	  <div class="wcfm-clearfix"></div>
	  
	  <div class="radykal-tabs">
			<div class="radykal-tabs-nav">
				<a href="desktop" class="current"><?php _e('Desktop', 'radykal'); ?></a>
				<a href="mobile"><?php _e('Mobile', 'radykal'); ?></a>
			</div>
		
			<div class="radykal-tabs-content">
		
				<div data-id="desktop" class="radykal-tab-content current">
		
					<div>
						<span class="wcfm_title"><strong><?php _e( 'Source Type', 'radykal' ); ?></strong></span>
						<span style="padding-right: 20px;">
							<input type="radio" name="fpd_source_type" value="category" <?php checked($source_type, 'category') ?> />
							<?php _e( 'Category', 'radykal' ); ?>
						</span>
						<span>
							<input type="radio" name="fpd_source_type" value="product" <?php checked($source_type, 'product') ?> />
							<?php _e( 'Product', 'radykal' ); ?>
						</span>
					</div>
					<div>
						<div class="fpd-categories">
							<span class="wcfm_title"><strong><?php _e( 'Product Categories', 'radykal' ); ?></strong></span>
							<select multiple="multiple" data-placeholder="<?php _e( 'Add categories to selection.', 'radykal' ); ?>" class="radykal-select-sortable" style="width: 100%;" data-selected="<?php echo implode(',', $selected_categories); ?>" name="fpd_product_categories">
							<?php
		
								$categories = FPD_Category::get_categories( array(
									'order_by' => 'title ASC'
								) );
		
								foreach($categories as $category) {
									$cat_title = '#'.$category->ID . ' - ' . $category->title;
									echo '<option value="'.$category->ID.'" data-title="'.$cat_title.'">'.$cat_title.'</option>';
								}
		
							?>
							</select>
							<p class="description"><?php _e( 'Sort items by drag & drop.', 'radykal' ); ?></p>
						</div>
						<div class="fpd-products">
							<span class="wcfm_title"><strong><?php _e( 'Products', 'radykal' ); ?></strong></span>
							<select data-placeholder="<?php _e( 'Add products to selection.', 'radykal' ); ?>" class="radykal-select-sortable" style="width: 100%;" name="fpd_products" data-selected="<?php echo implode(',', $selected_products); ?>">
								<?php
								
								 $vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		
								 if( wcfm_is_vendor() ) {
										$products = FPD_Product::get_products( array(
											'order_by' 	=> "ID ASC",
											'where'     => "user_id={$vendor_id }"
										) );
								 } else {
								 	 $products = FPD_Product::get_products( array(
										'order_by' 	=> "ID ASC",
									 ) );
								 }
		
								 if( !empty( $products ) ) {
										foreach($products as $fpd_product) {
											$product_title = '#'.$fpd_product->ID . ' - ' . $fpd_product->title;
											echo '<option value="'.$fpd_product->ID.'" data-title="'.$product_title.'">'.$product_title.'</option>';
										}
								 }
		
								?>
							</select>
							<p class="description"><?php _e( 'Sort items by drag & drop.', 'radykal' ); ?></p>
						</div>
					</div>
		
				</div>
				<div data-id="mobile" class="radykal-tab-content">
		
					<div>
						<span class="wcfm_title"><strong><?php _e( 'Source Type', 'radykal' ); ?></strong></span>
						<span style="padding-right: 20px;">
							<input type="radio" name="fpd_source_type_mobile" value="category" <?php checked($source_type_mobile, 'category') ?> />
							<?php _e( 'Category', 'radykal' ); ?>
						</span>
						<span>
							<input type="radio" name="fpd_source_type_mobile" value="product" <?php checked($source_type_mobile, 'product') ?> />
							<?php _e( 'Product', 'radykal' ); ?>
						</span>
					</div>
					<div>
						<div class="fpd-categories">
							<span class="wcfm_title"><strong><?php _e( 'Product Categories', 'radykal' ); ?></strong></span>
							<select multiple="multiple" data-placeholder="<?php _e( 'Add categories to selection.', 'radykal' ); ?>" class="radykal-select-sortable" style="width: 100%;" data-selected="<?php echo implode(',', $selected_categories_mobile); ?>" name="fpd_product_categories_mobile">
							<?php
		
								$categories = FPD_Category::get_categories( array(
									'order_by' => 'title ASC'
								) );
		
								foreach($categories as $category) {
									$cat_title = '#'.$category->ID . ' - ' . $category->title;
									echo '<option value="'.$category->ID.'" data-title="'.$cat_title.'">'.$cat_title.'</option>';
								}
		
							?>
							</select>
							<p class="description"><?php _e( 'Sort items by drag & drop.', 'radykal' ); ?></p>
						</div>
						<div class="fpd-products">
							<span class="wcfm_title"><strong><?php _e( 'Products', 'radykal' ); ?></strong></span>
							<select data-placeholder="<?php _e( 'Add products to selection.', 'radykal' ); ?>" class="radykal-select-sortable" style="width: 100%;" name="fpd_products_mobile" data-selected="<?php echo implode(',', $selected_products_mobile); ?>">
								<?php
		
								if( wcfm_is_vendor() ) {
										$products = FPD_Product::get_products( array(
											'order_by' 	=> "ID ASC",
											'where'     => "user_id={$vendor_id }"
										) );
								 } else {
								 	 $products = FPD_Product::get_products( array(
										'order_by' 	=> "ID ASC",
									 ) );
								 }
		
								 if( !empty( $products ) ) {
										foreach($products as $fpd_product) {
											$product_title = '#'.$fpd_product->ID . ' - ' . $fpd_product->title;
											echo '<option value="'.$fpd_product->ID.'" data-title="'.$product_title.'">'.$product_title.'</option>';
										}
								 }
		
								?>
							</select>
							<p class="description"><?php _e( 'Sort items by drag & drop.', 'radykal' ); ?></p>
						</div>
					</div>
		
				</div>
		
			</div>
		</div>
		
		<div>
			<input type="hidden" name="fpd_product_settings" class="widefat" value="<?php echo $current_ind_settings; ?>" />
			<a class="wcfm_submit_button" href="#" id="fpd-change-settings"><?php _e( 'Individual Product Settings', 'radykal' ); ?></a>
		</div>
		
		
		<script type="text/javascript">
		
			jQuery(document).ready(function($) {
		
				//FANCY PRODUCT CHECKBOX
				$('#_fancy_product').change(function() {
					if($(this).is(':checked')) {
						$('.hide_if_fancy_product').show();
					}
					else {
						$('.hide_if_fancy_product').hide();
					}
				}).change();
		
				//source type
				$('[name="fpd_source_type"], [name="fpd_source_type_mobile"]').change(function() {
		
					var $tabContent = $(this).parents('.radykal-tab-content:first');
		
					if($tabContent.find('input[type="radio"]:checked').val() === 'category') {
						$tabContent.find('.fpd-categories').show();
						$tabContent.find('.fpd-products').hide();
					}
					else {
						$tabContent.find('.fpd-categories').hide();
						$tabContent.find('.fpd-products').show();
					}
		
				}).change();
		
			});
		
		</script>
		
		
		<?php
		FPD_Admin_Modal::output_header(
			'fpd-modal-individual-product-settings',
			__('Individual Settings', 'radykal'),
			__('Here you can set individual product designer settings for this product.', 'radykal')
		);
		?>

		<div class="radykal-tabs">
		
			<div class="radykal-tabs-nav">
				<a href="general-options" class="current"><?php _e('General', 'radykal'); ?></a>
				<a href="image-options"><?php _e('Image Properties', 'radykal'); ?></a>
				<a href="custom-text-options"><?php _e('Custom Text Properties', 'radykal'); ?></a>
				<?php if( get_post_type($post) === 'product'): ?>
				<a href="woocommerce-options"><?php _e('WooCommerce', 'radykal'); ?></a>
				<?php endif; ?>
				<?php do_action( 'fpd_ips_tabs_end', $post ); ?>
			</div>
		
			<div class="radykal-tabs-content">
		
				<div data-id="general-options" class="current">
					<table class="form-table radykal-settings-form">
						<tbody>
							<tr valign="top">
								<th scope="row"><label><?php _e('UI Layout', 'radykal'); ?></label></th>
								<td>
									<select name="product_designer_ui_layout" class="radykal-select2" style="width: 100%;">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<?php
											//get all created categories
											$ui_layouts = FPD_Settings_General::get_saved_ui_layouts();
											foreach($ui_layouts as $key => $value) {
												echo '<option value="'.$key.'">'.$value.'</option>';
											}
										?>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Open Product Designer in...', 'radykal'); ?></label></th>
								<td>
									<select name="product_designer_visibility" style="width: 100%;">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<?php
											//get all created categories
											$visibilities = FPD_Settings_General::get_product_designer_visibilities();
											foreach($visibilities as $key => $value) {
												echo '<option value="'.$key.'">'.$value.'</option>';
											}
										?>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Main Bar Position', 'radykal'); ?></label></th>
								<td>
									<select name="main_bar_position" style="width: 100%;">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<?php
											//get all created categories
											$mb_positions = FPD_Settings_General::get_main_bar_positions();
											foreach($mb_positions as $key => $value) {
												echo '<option value="'.$key.'">'.$value.'</option>';
											}
										?>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Design Categories', 'radykal'); ?></label></th>
								<td>
									<select class="radykal-select2" name="design_categories[]" multiple data-placeholder="<?php _e('All Categories', 'radykal'); ?>" style="width: 100%;">
										<?php fpd_output_top_level_design_cat_options(true, true); ?>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Available Fonts', 'radykal'); ?></label></th>
								<td>
									<select class="radykal-select2" name="font_families[]" multiple data-placeholder="<?php _e('All Fonts', 'radykal'); ?>" style="width: 100%;">
										<?php
											//get all created categories
											$fonts = FPD_Fonts::get_enabled_fonts();
											foreach($fonts as $key => $font) {
												echo '<option value="'.$font.'">'.$font.'</option>';
											}
										?>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Background', 'radykal'); ?></label></th>
								<td>
									<label><input type="radio" name="background_type" value="image" checked="checked" /> <?php _e('Image', 'radykal'); ?></label>
									<label><input type="radio" name="background_type" value="color" /> <?php _e('Color', 'radykal'); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"> </th>
								<td >
									<button class="button button-secondary" id="fpd-set-background-image"><?php _e('Set Image', 'radykal'); ?></button>
									<input type="hidden" value="<?php echo plugins_url('/img/grid.png', dirname(__FILE__)); ?>" name="background_image">
									<img src="" alt="Background Image" id="fpd-background-image-preview" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"> </th>
								<td><input type="text" name="background_color" value="#ffffff" class="radykal-color-picker" /></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Replace Initial Elements', 'radykal'); ?></label></th>
								<td>
									<select name="replace_initial_elements">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="0"><?php _e( 'No', 'radykal' ); ?></option>
										<option value="1"><?php _e( 'Yes', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Color Prices for Images', 'radykal'); ?></label></th>
								<td>
									<select name="enable_image_color_prices">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="0"><?php _e( 'No', 'radykal' ); ?></option>
										<option value="1"><?php _e( 'Yes', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Color Prices for Texts', 'radykal'); ?></label></th>
								<td>
									<select name="enable_text_color_prices">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="0"><?php _e( 'No', 'radykal' ); ?></option>
										<option value="1"><?php _e( 'Yes', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Hide Dialog On Add', 'radykal'); ?></label></th>
								<td>
									<select name="hide_dialog_on_add">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="0"><?php _e( 'No', 'radykal' ); ?></option>
										<option value="1"><?php _e( 'Yes', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Customization Required', 'radykal'); ?></label></th>
								<td>
									<select name="customization_required">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="0"><?php _e( 'No', 'radykal' ); ?></option>
										<option value="1"><?php _e( 'Yes', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Layouts', 'radykal'); ?></label></th>
								<td>
									<select name="layouts" class="radykal-select2" style="width: 100%;">
										<option value=""><?php _e( 'None', 'radykal' ); ?></option>
										<?php
										
										if( wcfm_is_vendor() ) {
												$fpd_products = FPD_Product::get_products( array(
													'cols' => "ID, title",
													'order_by' 	=> "ID ASC",
													'where'     => "user_id={$vendor_id }"
												) );
										 } else {
											$fpd_products = FPD_Product::get_products( array(
												'cols' => "ID, title",
												'order_by' 	=> "ID ASC",
											) );
										 }
		
										foreach( $fpd_products as $fpd_product ) {
											echo '<option value="'.$fpd_product->ID.'">#'.$fpd_product->ID.' - '.$fpd_product->title.'</option>';
										}
		
										?>
									</select>
								</td>
							</tr>
							<?php do_action( 'fpd_ips_general_tbody_end' ); ?>
						</tbody>
					</table>
		
				</div><!-- general -->
		
				<div data-id="image-options">
					<h4><?php _e('Custom Images & Designs', 'radykal'); ?></h4>
					<table class="form-table radykal-settings-form">
						<tbody>
							<tr valign="top">
								<th scope="row"><label><?php _e('Colors', 'radykal'); ?></label></th>
								<td><input type="text" name="designs_parameter_colors" class="widefat" placeholder="<?php echo get_option('fpd_designs_parameter_colors'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Color Link Group', 'radykal'); ?></label></th>
								<td><input type="text" name="designs_parameter_colorLinkGroup" class="widefat" placeholder="<?php echo get_option('fpd_designs_parameter_colorLinkGroup'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Price', 'radykal'); ?></label></th>
								<td><input type="number" min="0" step="0.01" name="designs_parameter_price" placeholder="<?php echo fpd_get_option('fpd_designs_parameter_price'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Layer Depth', 'radykal'); ?></label></th>
								<td><input type="number" name="designs_parameter_z" placeholder="<?php echo get_option('fpd_designs_parameter_z'); ?>" value="" min="-1" step="1"></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Replace', 'radykal'); ?></label></th>
								<td><input type="text" name="designs_parameter_replace" class="widefat" placeholder="<?php echo get_option('fpd_designs_parameter_replace'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Replace In All Views', 'radykal'); ?></label></th>
								<td>
									<select name="designs_parameter_replaceInAllViews">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="0"><?php _e( 'No', 'radykal' ); ?></option>
										<option value="1"><?php _e( 'Yes', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Bounding Box', 'radykal'); ?></label></th>
								<td>
									<select name="designs_parameter_bounding_box_control">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="0" data-class="custom-bb"><?php _e( 'Custom Bounding Box', 'radykal' ); ?></option>
										<option value="1" data-class="target-bb"><?php _e( 'Use another element as bounding box', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Bounding Box Mode', 'radykal'); ?></label></th>
								<td>
									<select name="designs_parameter_boundingBoxMode">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<?php
		
										$bb_modi = FPD_Settings_Default_Element_Options::get_bounding_box_modi();
										foreach($bb_modi as $key => $value) {
											echo '<option value="'.$key.'">'.$value.'</option>';
										}
		
										?>
									</select>
								</td>
							</tr>
							<tr valign="top" class="custom-bb">
								<th scope="row"><label><?php _e('Bounding Box Left Position', 'radykal'); ?></label></th>
								<td><input type="number" name="designs_parameter_bounding_box_x" min="0" step="1" placeholder="<?php echo get_option('fpd_designs_parameter_bounding_box_x'); ?>" value=""></td>
							</tr>
							<tr valign="top" class="custom-bb">
								<th scope="row"><label><?php _e('Bounding Box Top Position', 'radykal'); ?></label></th>
								<td><input type="number" name="designs_parameter_bounding_box_y" min="0" step="1" placeholder="<?php echo get_option('fpd_designs_parameter_bounding_box_y'); ?>" value=""></td>
							</tr>
							<tr valign="top" class="custom-bb">
								<th scope="row"><label><?php _e('Bounding Box Width', 'radykal'); ?></label></th>
								<td><input type="number" name="designs_parameter_bounding_box_width" min="0" step="1" placeholder="<?php echo get_option('fpd_designs_parameter_bounding_box_width'); ?>" value=""></td>
							</tr>
							<tr valign="top" class="custom-bb">
								<th scope="row"><label><?php _e('Bounding Box Height', 'radykal'); ?></label></th>
								<td><input type="number" name="designs_parameter_bounding_box_height" min="0" step="1" placeholder="<?php echo get_option('fpd_designs_parameter_bounding_box_height'); ?>" value=""></td>
							</tr>
							<tr valign="top" class="target-bb">
								<th scope="row"><label><?php _e('Bounding Box Target', 'radykal'); ?></label></th>
								<td><input type="text" name="designs_parameter_bounding_box_by_other" placeholder="<?php echo get_option('fpd_designs_parameter_bounding_box_by_other'); ?>" value=""></td>
							</tr>
							<?php do_action( 'fpd_ips_image_tbody_end' ); ?>
						</tbody>
					</table>
					<h4><?php _e('Custom Images', 'radykal'); ?></h4>
					<table class="form-table radykal-settings-form">
						<tbody>
							<tr valign="top">
								<th scope="row"><label><?php _e('Minimum Width', 'radykal'); ?></label></th>
								<td><input type="number" min="1" step="1" name="uploaded_designs_parameter_minW" placeholder="<?php echo fpd_get_option('fpd_uploaded_designs_parameter_minW'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Minimum Height', 'radykal'); ?></label></th>
								<td><input type="number" min="1" step="1" name="uploaded_designs_parameter_minH" placeholder="<?php echo fpd_get_option('fpd_uploaded_designs_parameter_minH'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Maximum Width', 'radykal'); ?></label></th>
								<td><input type="number" min="1" step="1" name="uploaded_designs_parameter_maxW" placeholder="<?php echo fpd_get_option('fpd_uploaded_designs_parameter_maxW'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Maximum Height', 'radykal'); ?></label></th>
								<td><input type="number" min="1" step="1" name="uploaded_designs_parameter_maxH" placeholder="<?php echo fpd_get_option('fpd_uploaded_designs_parameter_maxH'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Scale To Width', 'radykal'); ?></label></th>
								<td><input type="number" min="1" step="1" name="uploaded_designs_parameter_resizeToW" placeholder="<?php echo fpd_get_option('fpd_uploaded_designs_parameter_resizeToW'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Scale To Height', 'radykal'); ?></label></th>
								<td><input type="number" min="1" step="1" name="uploaded_designs_parameter_resizeToH" placeholder="<?php echo fpd_get_option('fpd_uploaded_designs_parameter_resizeToH'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Advanced Editing', 'radykal'); ?></label></th>
								<td>
									<select name="uploaded_designs_parameter_advancedEditing">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="0"><?php _e( 'No', 'radykal' ); ?></option>
										<option value="1"><?php _e( 'Yes', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Filter', 'radykal'); ?></label></th>
								<td>
									<select name="uploaded_designs_parameter_filter" class="radykal-select2" style="width: 100%;">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<?php
											//get all created categories
											$ui_layouts = FPD_Settings_Default_Element_Options::get_image_filters();
											foreach($ui_layouts as $key => $value) {
												echo '<option value="'.$key.'">'.$value.'</option>';
											}
										?>
									</select>
								</td>
							</tr>
							<?php do_action( 'fpd_ips_custom_image_tbody_end' ); ?>
						</tbody>
					</table>
				</div><!-- image options -->
		
				<div data-id="custom-text-options">
					<table class="form-table radykal-settings-form">
						<tbody>
							<tr valign="top">
								<th scope="row"><label><?php _e('Colors', 'radykal'); ?></label></th>
								<td><input type="text" name="custom_texts_parameter_colors" value="" class="widefat" placeholder="<?php echo fpd_get_option('fpd_custom_texts_parameter_colors'); ?>"></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Color Link Group', 'radykal'); ?></label></th>
								<td><input type="text" name="custom_texts_parameter_colorLinkGroup" class="widefat" placeholder="<?php echo get_option('fpd_custom_texts_parameter_colorLinkGroup'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Default Color', 'radykal'); ?></label></th>
								<td><input type="text" name="custom_texts_parameter_fill" value="" placeholder="<?php echo fpd_get_option('fpd_custom_texts_parameter_fill'); ?>"></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Price', 'radykal'); ?></label></th>
								<td><input type="number" min="0" step="0.01" name="custom_texts_parameter_price" placeholder="<?php echo fpd_get_option('fpd_custom_texts_parameter_price'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Layer Depth', 'radykal'); ?></label></th>
								<td><input type="number" name="custom_texts_parameter_z" placeholder="<?php echo get_option('fpd_custom_texts_parameter_z'); ?>" value="" min="-1" step="1"></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Replace', 'radykal'); ?></label></th>
								<td><input type="text" name="custom_texts_parameter_replace" class="widefat" placeholder="<?php echo get_option('fpd_custom_texts_parameter_replace'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Replace In All Views', 'radykal'); ?></label></th>
								<td>
									<select name="custom_texts_parameter_replaceInAllViews">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="0"><?php _e( 'No', 'radykal' ); ?></option>
										<option value="1"><?php _e( 'Yes', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Bounding Box', 'radykal'); ?></label></th>
								<td>
									<select name="custom_texts_parameter_bounding_box_control">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="0" data-class="custom-bb"><?php _e( 'Custom Bounding Box', 'radykal' ); ?></option>
										<option value="1" data-class="target-bb"><?php _e( 'Use another element as bounding box', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top" class="custom-bb">
								<th scope="row"><label><?php _e('Bounding Box Left Position', 'radykal'); ?></label></th>
								<td><input type="number" name="custom_texts_parameter_bounding_box_x" min="0" step="1" placeholder="<?php echo get_option('fpd_custom_texts_parameter_bounding_box_x'); ?>" value=""></td>
							</tr>
							<tr valign="top" class="custom-bb">
								<th scope="row"><label><?php _e('Bounding Box Top Position', 'radykal'); ?></label></th>
								<td><input type="number" name="custom_texts_parameter_bounding_box_y" min="0" step="1" placeholder="<?php echo get_option('fpd_custom_texts_parameter_bounding_box_y'); ?>" value=""></td>
							</tr>
							<tr valign="top" class="custom-bb">
								<th scope="row"><label><?php _e('Bounding Box Width', 'radykal'); ?></label></th>
								<td><input type="number" name="custom_texts_parameter_bounding_box_width" min="0" step="1" placeholder="<?php echo get_option('fpd_custom_texts_parameter_bounding_box_width'); ?>" value=""></td>
							</tr>
							<tr valign="top" class="custom-bb">
								<th scope="row"><label><?php _e('Bounding Box Height', 'radykal'); ?></label></th>
								<td><input type="number" name="custom_texts_parameter_bounding_box_height" min="0" step="1" placeholder="<?php echo get_option('fpd_custom_texts_parameter_bounding_box_height'); ?>" value=""></td>
							</tr>
							<tr valign="top" class="target-bb">
								<th scope="row"><label><?php _e('Bounding Box Target', 'radykal'); ?></label></th>
								<td><input type="text" name="custom_texts_parameter_bounding_box_by_other" placeholder="<?php echo get_option('fpd_custom_texts_parameter_bounding_box_by_other'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Bounding Box Mode', 'radykal'); ?></label></th>
								<td>
									<select name="custom_texts_parameter_boundingBoxMode">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<?php
		
										$bb_modi = FPD_Settings_Default_Element_Options::get_bounding_box_modi();
										foreach($bb_modi as $key => $value) {
											echo '<option value="'.$key.'">'.$value.'</option>';
										}
		
										?>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Default Font Size', 'radykal'); ?></label></th>
								<td><input type="text" name="custom_texts_parameter_textSize" value="" placeholder="<?php echo fpd_get_option('fpd_custom_texts_parameter_textSize'); ?>"></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Minimum Font Size', 'radykal'); ?></label></th>
								<td><input type="text" name="custom_texts_parameter_minFontSize" value="" placeholder="<?php echo fpd_get_option('fpd_custom_texts_parameter_minFontSize'); ?>"></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Maximum Font Size', 'radykal'); ?></label></th>
								<td><input type="text" name="custom_texts_parameter_maxFontSize" value="" placeholder="<?php echo fpd_get_option('fpd_custom_texts_parameter_maxFontSize'); ?>"></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Maximum Characters	', 'radykal'); ?></label></th>
								<td><input type="number" min="0" step="1" name="custom_texts_parameter_maxLength" placeholder="<?php echo fpd_get_option('fpd_custom_texts_parameter_maxLength'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Maximum Lines	', 'radykal'); ?></label></th>
								<td><input type="number" min="0" step="1" name="custom_texts_parameter_maxLines" placeholder="<?php echo fpd_get_option('fpd_custom_texts_parameter_maxLines'); ?>" value=""></td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Text Link Group', 'radykal'); ?></label></th>
								<td><input type="text" name="custom_texts_parameter_textLinkGroup" class="widefat" placeholder="<?php echo get_option('fpd_custom_texts_parameter_textLinkGroup'); ?>" value=""></td>
							</tr>
							<?php do_action( 'fpd_ips_custom_text_tbody_end' ); ?>
						</tbody>
					</table>
				</div><!-- custom text options -->
		
				<div data-id="woocommerce-options">
					<table class="form-table radykal-settings-form">
						<tbody>
							<tr valign="top">
								<th scope="row"><label><?php _e('Product Designer Position', 'radykal'); ?></label></th>
								<td>
									<select name="placement">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<?php
											//get all created categories
											foreach(FPD_Settings_WooCommerce::get_product_designer_positions() as $key => $value) {
												echo '<option value="'.$key.'">'.$value.'</option>';
											}
										?>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Hide Product Image', 'radykal'); ?></label></th>
								<td>
									<select name="hide_product_image">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="yes"><?php _e( 'Yes', 'radykal' ); ?></option>
										<option value="no"><?php _e( 'No', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Fullwidth Summary', 'radykal'); ?></label></th>
								<td>
									<select name="fullwidth_summary">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="yes"><?php _e( 'Yes', 'radykal' ); ?></option>
										<option value="no"><?php _e( 'No', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Get A Quote', 'radykal'); ?></label></th>
								<td>
									<select name="get_quote">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="yes"><?php _e( 'Yes', 'radykal' ); ?></option>
										<option value="no"><?php _e( 'No', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e('Customize Button: Variation Needed', 'radykal'); ?></label></th>
								<td>
									<select name="wc_customize_variation_needed">
										<option value=""><?php _e( 'Use Option From Main Settings', 'radykal' ); ?></option>
										<option value="yes"><?php _e( 'Yes', 'radykal' ); ?></option>
										<option value="no"><?php _e( 'No', 'radykal' ); ?></option>
									</select>
								</td>
							</tr>
							<?php do_action( 'fpd_ips_wc_tbody_end' ); ?>
						</tbody>
					</table>
				</div><!-- wc options -->
		
				<?php do_action( 'fpd_ips_tabs_content_end' ); ?>
		
			</div>
		</div>

		<?php
			FPD_Admin_Modal::output_footer(
				__('Set', 'radykal')
			);
		?>

		<script type="text/javascript">
		
			jQuery(document).ready(function($) {
		
				var $modalWrapper = $('#fpd-modal-individual-product-settings'),
					mediaUploader = null;
		
		
				//SETTINGS
		
				$('#fpd-change-settings').click(function(evt) {
		
					evt.preventDefault();
		
					openModal($modalWrapper.removeClass('hidden'));
		
					radykalFillForm($modalWrapper, $('[name="fpd_product_settings"]').val());
		
					$modalWrapper.find('select').change();
					$modalWrapper.find('[name="background_color"], [name="background_type"]:checked').change();
					$modalWrapper.find('#fpd-background-image-preview').attr('src', $modalWrapper.find('[name="background_image"]').val());
		
				});
		
				//background type switcher
				$modalWrapper.find('[name="background_type"]').change(function() {
		
					if(this.value == 'image') {
						$modalWrapper.find('[name="background_image"]').parents('tr:first').show();
						$modalWrapper.find('[name="background_color"]').parents('tr:first').hide();
					}
					else {
						$modalWrapper.find('[name="background_image"]').parents('tr:first').hide();
						$modalWrapper.find('[name="background_color"]').parents('tr:first').show();
					}
		
				});
		
				//bounding box switcher
				$('[name="designs_parameter_bounding_box_control"], [name="custom_texts_parameter_bounding_box_control"]').change(function() {
		
					var $this = $(this),
						$tbody = $this.parents('tbody');
		
					$tbody.find('.custom-bb, .target-bb').hide().addClass('no-serialization');
					if(this.value != '') {
						$tbody.find('.'+$this.find(":selected").data('class')).show().removeClass('no-serialization');
					}
		
		
				});
		
				$('#fpd-set-background-image').click(function(evt) {
		
					evt.preventDefault();
		
					if (mediaUploader) {
									mediaUploader.open();
									return;
							}
		
							mediaUploader = wp.media({
									title: '<?php _e( 'Choose a background image', 'radykal' ); ?>',
									multiple: false
							});
		
							mediaUploader.on('select', function() {
		
						backgroundImage = mediaUploader.state().get('selection').toJSON()[0].url;
						$modalWrapper.find('[name="background_image"]').val(backgroundImage);
						$modalWrapper.find('#fpd-background-image-preview').attr('src', backgroundImage);
		
					});
		
					mediaUploader.open();
		
				});
		
				$modalWrapper.on('click', '.fpd-save-admin-modal', function(evt) {
		
					evt.preventDefault();
		
					var $formFields = $modalWrapper.find('input[type="number"],input[type="text"],input[type="hidden"],input[type="radio"]:checked,select,input[type="checkbox"]:checked').filter(':not(.no-serialization)'),
						serializedObj = fpdSerializeObject($formFields);
						serializedStr = JSON.stringify(serializedObj);
		
					$('[name="fpd_product_settings"]').val(serializedStr);
		
					closeModal($modalWrapper);
		
				});
		
			});
		
		</script>
	  
	</div>
</div>