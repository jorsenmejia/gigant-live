<?php
global $WCFM, $WCFMu, $wp_query, $wpdb;

$page_links = false;

$vendor_id   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
	
if( isset($_POST['fpd_filter_by']) )
	update_option('fpd_admin_filter_by', $_POST['fpd_filter_by']);

if( isset($_POST['fpd_order_by']) )
	update_option('fpd_admin_order_by', $_POST['fpd_order_by']);

$filter_by = get_option('fpd_admin_filter_by', 'title');
$order_by = get_option('fpd_admin_order_by', 'ASC');

$where = '';
if( wcfm_is_vendor() )
	$where = "user_id={$vendor_id }";

$categories = FPD_Category::get_categories( array(
	'order_by' => 'title ASC'
) );

$pagenum = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$limit = 20;
$offset = ( $pagenum - 1 ) * $limit;
if( wcfm_is_vendor() ) {
	$total = sizeof( FPD_Product::get_products( array( 'where' => "user_id={$vendor_id }")) );
} else {
	$total = sizeof( FPD_Product::get_products() );
}
$num_of_pages = ceil( $total / $limit );

$page_links = paginate_links( array(
		'base' 		=> add_query_arg( 'paged', '%#%' ),
		'format' 	=> '',
		'prev_text' => '&laquo;',
		'next_text' => '&raquo;',
		'total' 	=> $num_of_pages,
		'current' 	=> $pagenum
) );

$products = FPD_Product::get_products( array(
	'where' 	=> $where,
	'order_by' 	=> $filter_by . ' '. $order_by,
	'limit' 	=> $limit,
	'offset' 	=> $offset
) );

//select by category
if( isset($_GET['category_id']) ) {

	$page_links = false;
	$products = FPD_Product::get_products( array(
		'where' 	=> "ID IN (SELECT product_id FROM ".FPD_CATEGORY_PRODUCTS_REL_TABLE." WHERE category_id={$_GET['category_id']})",
	) );

}

if ( isset($_GET['info']) ) {
	require_once(FPD_PLUGIN_ADMIN_DIR.'/modals/modal-updated-installed-info.php');
}

$total_product_templates = 0;
require_once(FPD_PLUGIN_ADMIN_DIR.'/modals/modal-load-demo.php');
require_once(FPD_PLUGIN_ADMIN_DIR.'/modals/modal-load-template.php');
require_once(FPD_PLUGIN_ADMIN_DIR.'/modals/modal-edit-product-options.php');
require_once(FPD_PLUGIN_ADMIN_DIR.'/modals/modal-shortcodes.php');
require_once(FPD_PLUGIN_ADMIN_DIR.'/modals/modal-templates-library.php');

?>

<div class="collapse wcfm-collapse" id="wcfm_build_listing">
	
	<div class="wcfm-page-headig">
		<span class="wcfmfa fa-object-group"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Product Designer', 'wc-frontend-manager-ultimate' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		<?php do_action( 'before_wcfm_fancy_product_designer' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e('Product Designer', 'wc-frontend-manager-ultimate' ); ?></h2>
			<div class="wcfm-clearfix"></div>
	  </div>
	  <div class="wcfm-clearfix"></div><br />
		

		<div class="wcfm-container">
			<div id="wcfm_build_listing_expander" class="wcfm-content">
			
				<!-- wrap beginn -->
				<div class="wrap" id="fpd-manage-products">
					<div class="fpd-clearfix">
	
						<div id="fpd-products" class="fpd-panel">
							<h2 class="description"><?php _e('Create Product From:', 'radykal'); ?></h2>
							<h3>
								<?php
								$create_product_buttons = array(
									'fpd-open-templates-library' => array(
										'title' => __( 'Create a product from pre-made templates', 'radykal' ),
										'text' 	=> __( 'Templates Library', 'radykal' ),
										'attrs' => 'data-total='.$total_product_templates.''
									),
									'fpd-add-product' => array(
										'title' => __( 'Create a product from scratch', 'radykal' ),
										'text' 	=> __( 'New', 'radykal' )
									),
									'fpd-load-template' => array(
										'title' => __( 'Create a product from your saved templates', 'radykal' ),
										'text' 	=> __( 'My Templates', 'radykal' )
									),
								);
	
								$create_product_buttons = apply_filters( 'fpd_admin_manage_products_create_buttons', $create_product_buttons );
	
								foreach( $create_product_buttons as $key => $btn )
									echo '<button class="add-new-h2 fpd-admin-tooltip" id="'.esc_attr( $key ).'" title="'.esc_attr( $btn['title'] ).'" '.esc_attr( isset( $btn['attrs'] ) ? $btn['attrs'] : '' ).'> '.esc_html( $btn['text'] ).'</button>';
	
								?>
							</h3>
	
							<div id="fpd-products-nav" class="fpd-clearfix">
	
								<form method="POST">
									<span class="description"><?php _e('Filter:', 'radykal') ?></span>
									<select name="fpd_filter_by" class="radykal-input">
										<option value="ID" <?php selected($filter_by, 'ID'); ?> ><?php _e('ID', 'radykal') ?></option>
										<option value="title" <?php selected($filter_by, 'title'); ?> ><?php _e('Title', 'radykal') ?></option>
									</select>
									<select name="fpd_order_by" class="radykal-input">
										<option value="ASC" <?php selected($order_by, 'ASC'); ?> ><?php _e('Ascending', 'radykal') ?></option>
										<option value="DESC" <?php selected($order_by, 'DESC'); ?>><?php _e('Descending', 'radykal') ?></option>
									</select>
								</form>
								<form method="POST" name="fpd_search_products" style="display:none;">
									<input type="text" name="fpd_search_products_string" placeholder="<?php _e('Search Products...', 'radykal') ?>" class="radykal-input" />
									<input type="submit" class="button button-secondary" value="<?php _e('Search', 'radykal') ?>" />
								</form>
	
								<?php do_action( 'fpd_admin_manage_products_filter_nav' ); ?>
	
							</div>
	
							<?php if( empty($products) ): ?>
							  <p class="fpd-error-message"><strong><?php _e('No Products found!', 'radykal') ?></strong></p>
							<?php endif; ?>
	
							<ul id="fpd-products-list">
								<?php
	
								foreach($products as $product) {
									$fancy_product = new FPD_Product($product->ID);
									$category_ids = $fancy_product->get_category_ids();
	
									echo $WCFMu->wcfmu_wcfancyproducts->wcfmfpd_get_product_item_html(
										$product->ID,
										$product->title,
										implode(',', $category_ids),
										isset($product->thumbnail) ? stripslashes($product->thumbnail) : '',
										$product->user_id
									);
	
									echo '<ul class="fpd-views-list">';
									$product_views = $fancy_product->get_views();
									if( !empty($product_views) ) {
	
										foreach($product_views as $view) {
	
											echo $WCFMu->wcfmu_wcfancyproducts->wcfmfpd_get_view_item_html(
												$view->ID,
												$view->thumbnail,
												$view->title,
												$product->user_id
											);
	
										}
	
									}
									echo '</ul>';
	
								}
	
								?>
							</ul>
							<?php
							if ( $page_links ) {
								echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 0;">' . $page_links . '</div></div>';
							}
							?>
							<div class="fpd-ui-blocker"></div>
	
						</div>
						<div id="fpd-categories" class="fpd-panel">
							<h2>
								<?php _e('Categories', 'radykal'); ?>
								<a href="#" id="fpd-add-category" class="add-new-h2"><?php _e('Add New', 'radykal'); ?></a>
							</h2>
							<ul id="fpd-categories-list">
								<?php
	
								foreach($categories as $category) {
									echo $WCFMu->wcfmu_wcfancyproducts->wcfmfpd_get_category_item_html($category->ID, $category->title);
								}
	
								?>
							</ul>
							<div class="fpd-ui-blocker"></div>
	
						</div>
	
					</div>
	
				</div>

				<div class="wcfm-clearfix"></div>
			</div>
			<div class="wcfm-clearfix"></div>
		</div>
	
		<div class="wcfm-clearfix"></div>
		<?php
		do_action( 'after_wcfm_fancy_product_designer' );
		?>
	</div>
</div>

