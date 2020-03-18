<?php
$isPro = $this->is_pro;
$labelPro = '';
if(!$isPro) {
	$adPath = $this->getModule()->getModPath().'img/ad/';
	$labelPro = ' Pro';
}

$catArgs = array(
	'orderby' => 'name',
	'order' => 'asc',
	'hide_empty' => false,
	//'parent' => 0
);

$productCategories = get_terms( 'product_cat', $catArgs );
$categoryDisplay = $parentCategories = array();
foreach ($productCategories as $cat){
    if ($cat->parent == 0) {
        array_push($parentCategories, $cat->term_id);
    }
	$categoryDisplay[$cat->term_id] = $cat->name;
}

$tagArgs = array(
	'orderby' => 'name',
	'order' => 'asc',
	'hide_empty' => false,
	'parent' => 0
);

$productTags = get_terms( 'product_tag', $tagArgs );
$tagsDisplay = array();
foreach ($productTags as $tag){
	$tagsDisplay[$tag->term_id] = $tag->name;
}


$productAttr = wc_get_attribute_taxonomies();

$attrDisplay = $attsTagsDisplay = array(0 => __('Select...', WPF_LANG_CODE));
$attrDisplayTerms = array();
foreach ($productAttr as $attr){
	$attrDisplay[$attr->attribute_id] = $attr->attribute_label;

    $productAttrValues = get_terms(wc_attribute_taxonomy_name($attr->attribute_name));
    foreach ($productAttrValues as $value){
        $attsTagsDisplay[$value->term_id] = $value->name;
        $attrDisplayTerms[$attr->attribute_id][] = $value->term_id;
    }
}
$attrDisplayTerms = 'data-attr-terms="'. htmlspecialchars(json_encode($attrDisplayTerms), ENT_QUOTES, 'UTF-8'). '"';

$rolesMain = get_editable_roles();
$roles = array();

foreach($rolesMain as $key => $role){
	$roles[$key] = $role['name'];
}

$wpfBrand = array(
    'exist' => taxonomy_exists('product_brand')
);

?>

<div id="wpfFiltersEditTabs">
	<section>
		<div class="supsystic-item supsystic-panel" style="padding-left: 10px;">
			<div id="containerWrapper">
				<form id="wpfFiltersEditForm" data-table-id="<?php echo $this->filter['id']; ?>" data-href="<?php echo $this->link;?>">

					<div class="row">
						<div class="wpfCopyTextCodeSelectionShell col-lg-8 col-md-9">
							<div class="row">
								<div class="col-md-4 wpfNamePadding">
									<span id="wpfFilterTitleWrapLabel"><?php echo __('Filter name:', WPF_LANG_CODE); ?></span>
									<span id="wpfFilterTitleShell" title="<?php echo esc_html(__('Click to edit', WPF_LANG_CODE))?>">
                                        <?php $title = isset($this->filter['title']) ? $this->filter['title'] : 'empty';?>
										<span id="wpfFilterTitleLabel"><?php echo $title; ?></span>
										<?php echo htmlWpf::text('title', array(
											'value' => $title,
											'attrs' => 'style="display:none;" id="wpfFilterTitleTxt"',
											'required' => true,
										)); ?>
										<i class="fa fa-fw fa-pencil"></i>
									</span>
								</div>
								<div class="col-md-4 wpfShortcodeAdm">
									<select name="shortcode_example" id="wpfCopyTextCodeExamples">
										<option value="shortcode"><?php echo __('Filter Shortcode', WPF_LANG_CODE); ?></option>
										<option value="phpcode"><?php echo __('Filter PHP code', WPF_LANG_CODE); ?></option>
										<option value="shortcode_product"><?php echo __('Product Shortcode', WPF_LANG_CODE); ?></option>
										<option value="phpcode_product"><?php echo __('Product PHP code', WPF_LANG_CODE); ?></option>
									</select>
									<i class="fa fa-question supsystic-tooltip" style="margin-left: 12px;" title="<?php echo esc_html(__('Using short code you can display the filter and products in the desired place of the template.', WPF_LANG_CODE))?>"></i>
								</div>
                                <?php $id = isset($this->filter['id']) ? $this->filter['id'] : ''; ?>
                                <?php if($id) {?>
								<div class="col-md-4 wpfCopyTextCodeShowBlock wpfShortcode shortcode" style="">
									<?php
										echo htmlWpf::text('', array(
												'value' => "[".WPF_SHORTCODE." id=$id]",
												'attrs' => 'readonly style="width: 100%" onclick="this.setSelectionRange(0, this.value.length);" class=""',
												'required' => true,
											));
									?>
								</div>
								<div class="col-md-4 wpfCopyTextCodeShowBlock wpfShortcode phpcode" style="display: none;">
									<?php
										echo htmlWpf::text('', array(
											'value' => "<?php echo do_shortcode('[".WPF_SHORTCODE." id=$id]') ?>",
											'attrs' => 'readonly style="width: 100%" onclick="this.setSelectionRange(0, this.value.length);" class=""',
											'required' => true,
										));
									?>
								</div>
								<div class="col-md-4 wpfCopyTextCodeShowBlock wpfShortcode shortcode_product" style="display: none;">
									<?php
										echo htmlWpf::text('', array(
												'value' => "[".WPF_SHORTCODE_PRODUCTS."]",
												'attrs' => 'readonly style="width: 100%" onclick="this.setSelectionRange(0, this.value.length);" class=""',
												'required' => true,
											));
									?>
								</div>
								<div class="col-md-4 wpfCopyTextCodeShowBlock wpfShortcode phpcode_product" style="display: none;">
									<?php
										echo htmlWpf::text('', array(
											'value' => "<?php echo do_shortcode('[".WPF_SHORTCODE_PRODUCTS."]') ?>",
											'attrs' => 'readonly style="width: 100%" onclick="this.setSelectionRange(0, this.value.length);" class=""',
											'required' => true,
										));
									?>
								</div>
                                <?php } ?>
								<div class="clear"></div>
							</div>
						</div>
						<div class="wpfMainBtnsShell col-lg-4 col-md-3 ">
							<ul class="wpfSub control-buttons">
								<li>
									<button id="buttonSave" class="button">
										<i class="fa fa-floppy-o" aria-hidden="true"></i><span><?php echo __('Save', WPF_LANG_CODE); ?></span>
									</button>
								</li>
								<li>
									<button id="buttonDelete" class="button" >
										<i class="fa fa-trash-o" aria-hidden="true"></i><span><?php echo __('Delete', WPF_LANG_CODE); ?></span>
									</button>
								</li>
							</ul>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<ul class="wpfSub tabs-wrapper wpfMainTabs">
								<li>
									<a href="#row-tab-filters" class="current button wpfFilters"><i class="fa fa-fw fa-eye"></i><?php echo __('Filters', WPF_LANG_CODE); ?></a>
								</li>
								<li>
									<a href="#row-tab-design" class="button"><i class="fa fa-fw fa-wrench"></i><?php echo __('Design', WPF_LANG_CODE); ?></a>
								</li>
								<!-- <li>
									<a href="#row-tab-options" class="button"><i class="fa fa-fw fa-th"></i><?php echo __('Options', WPF_LANG_CODE); ?></a>
								</li> -->
								<li>
									<a href="#row-tab-advanced" class="button"><i class="fa fa-fw fa-database"></i><?php echo __('Advanced', WPF_LANG_CODE); ?></a>
								</li>
								<li class="wpfHidden">
									<a href="#row-tab-filtering" class="button"><i class="fa fa-fw fa-eye"></i><?php echo __('Filtering', WPF_LANG_CODE); ?></a>
								</li>
							</ul>
							<span id="wpfFilterTitleEditMsg"></span>
						</div>
					</div>

					<?php //All templates in the same folder now. This is simplest way to include all. ?>
					<?php include_once 'woofiltersEditTabFilters.php' ?>
					<?php include_once 'woofiltersEditTabDesign.php' ?>
					<?php include_once 'woofiltersEditTabOptions.php' ?>
					<?php include_once 'woofiltersEditTabAdvanced.php' ?>

					<div class="row row-tab wpfHidden" id="row-tab-filtering">
						<div class="col-xs-12">
							<table class="form-settings-table">
								<tbody class="col-md-6">
								<tr class="col-md-12">
									<td class="col-md-5">
										<?php _e('Enable Cross filtration', WPF_LANG_CODE)?>
									</td>
									<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Enable Cross filtration. For example, if you select the category "new" in the category filter, all filters will hide options in products that do not have the category "new".', WPF_LANG_CODE))?>"></i></td>
									<td class="col-md-5">
										<?php echo htmlWpf::checkbox('settings[enable_cross_filtration]', array(
											'checked' => (isset($this->settings['settings']['enable_cross_filtration']) ? (int) $this->settings['settings']['enable_cross_filtration'] : '')
										))?>
									</td>
								</tr>
								<tr class="col-md-12">
									<td class="col-md-5">
										<?php _e('Cross filter style', WPF_LANG_CODE)?>
									</td>
									<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Select mobile filter location.', WPF_LANG_CODE))?>"></i></td>
									<td class="col-md-5">
										<?php echo htmlWpf::selectbox('settings[cross_filter_style]', array(
											'options' => array('hide_terms' => 'Hide terms', 'disable_unclickable' => 'Disable and unclickable', 'disable_clickable' => 'Disable and clickable'),
											'value' => (isset($this->settings['settings']['cross_filter_style']) ? $this->settings['settings']['cross_filter_style'] : 'left'),
										))
										?>
									</td>
								</tr>
								<tr class="col-md-12">
									<td class="col-md-5">
										<?php _e('Cross filter Term Products Count Mode', WPF_LANG_CODE)?>
									</td>
									<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Cross filter Term Products Count Mode displayed in frontend.', WPF_LANG_CODE))?>"></i></td>
									<td class="col-md-5">
										<?php echo htmlWpf::selectbox('settings[mobile_filter_position]', array(
											'options' => array('left' => 'Left', 'right' => 'Right'),
											'value' => (isset($this->settings['settings']['mobile_filter_position']) ? $this->settings['settings']['mobile_filter_position'] : 'left'),
										))
										?>
									</td>
								</tr>
								<tr class="col-md-12">
									<td class="col-md-5">
										<?php _e('Reorder Cross filter Terms', WPF_LANG_CODE)?>
									</td>
									<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('After filtering, sort the items in the filters (active / clickable options move up).', WPF_LANG_CODE))?>"></i></td>
									<td class="col-md-5">
										<?php echo htmlWpf::checkbox('settings[cross_filtration_reorder]', array(
											'checked' => (isset($this->settings['settings']['cross_filtration_reorder']) ? (int) $this->settings['settings']['cross_filtration_reorder'] : '')
										))?>
									</td>
								</tr>

								</tbody>
							</table>

						</div>
					</div>

					<?php echo htmlWpf::hidden('settings[filters][order]', array(
						'value' => (isset($this->settings['settings']['filters']['order']) ? htmlentities($this->settings['settings']['filters']['order']) : ''),
					));?>

					<?php echo htmlWpf::hidden( 'mod', array( 'value' => 'woofilters' ) ) ?>
					<?php echo htmlWpf::hidden( 'action', array( 'value' => 'save' ) ) ?>
					<?php echo htmlWpf::hidden( 'id', array( 'value' => $this->filter['id'] ) ) ?>
				</form>
				<div style="clear: both;"></div>
			</div>
		</div>
	</section>
</div>
