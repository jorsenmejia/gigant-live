<div class="row row-tab active" id="row-tab-filters">

	<div class="col-lg-12 col-md-12">
		<div class="row">
			<div class="col-md-9">
				<div class="wpfFiltersBlock">

				</div>
			</div>
			<div class="col-md-3">
				<div class="wpfFiltersBlockPreview">

				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-12 col-md-12 wpfChooseBlock">

		<button id="wpfAttribute" class="button"><?php echo __('Add Attribute filter', WPF_LANG_CODE); ?></button>

		<button id="wpfBrand" class="button wpfHidden"><?php echo __('Brand', WPF_LANG_CODE); ?></button>
		<button id="wpfCustomMeta" class="button wpfHidden"><?php echo __('Custom meta', WPF_LANG_CODE); ?></button>

	</div>

	<div class="wpfTemplates wpfHidden">
		<div class="wpfRangeByHandTemplate">
			<div class="wpfRangeByHand">
				<div class="wpfRangeByHandHandlerFrom">
					<?php _e('From', WPF_LANG_CODE)?>
					<input type="text" name="from" value="">
				</div>
				<div class="wpfRangeByHandHandlerTo">
					<?php _e('To', WPF_LANG_CODE)?>
					<input type="text" name="to" value="">
				</div>
				<div class="wpfRangeByHandHandler">
					<i class="fa fa-arrows-v"></i>
				</div>
				<div class="wpfRangeByHandRemove">
					<i class="fa fa-trash-o"></i>
				</div>
			</div>
		</div>

		<div class="wpfRangeByHandTemplateAddButton">
			<div class="wpfRangeByHandAddButton">
				<button class="button wpfAddPriceRange"><?php _e('Add', WPF_LANG_CODE)?></button>
			</div>
		</div>

		<div class="wpfFilter wpfFiltersBlockTemplate">

			<a href="#" class="wpfMove"><i class="fa fa-arrows-v"></i></a>
			<div class="wpfEnable"><?php echo htmlWpf::checkbox('f_enable', array())?><?php _e('Enable', WPF_LANG_CODE)?></div>
			<div class="wpfFilterTitle"></div>
			<a href="#" class="wpfDelete wpfVisibilityHidden button button-small"><i class="fa fa-fw fa-trash-o"></i></a>
			<a href="#" class="wpfToggle"><span class="wpfTextSt_1" data-title-close="<?php _e('Show options', WPF_LANG_CODE)?>" data-title-open="<?php _e('Hide options', WPF_LANG_CODE)?>"><?php _e('Show options', WPF_LANG_CODE)?></span> <i class="fa fa-chevron-down"></i></a>
			<div class="wpfFilterFrontDescOpt">
				<?php echo htmlWpf::text('f_description', array(
					'placeholder' => __('Description', WPF_LANG_CODE),
				))?>
			</div>
			<div class="wpfFilterFrontTitleOpt">
				<?php echo htmlWpf::text('f_title', array(
					'placeholder' => __('Title', WPF_LANG_CODE),
				))?>
			</div>
			<div class="wpfOptions wpfHidden"></div>
		</div>

		<div class="wpfOptionsTemplate wpfHidden">
			<table class="form-settings-table" data-filter="wpfPrice">
				<tbody class="col-md-12">
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Show title label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show title label', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::selectbox('f_enable_title', array(
							'options' => array('no' => 'No', 'yes_close' => 'Yes, show as close', 'yes_open' => 'Yes, show as opened'),
						));
						?>
					</td>
					<td class="col-md-3"></td>
				</tr>
				<?php
					$skins = array(
						'default' => 'default',
						'flat' => 'Flat skin'.$labelPro,
						'big' => 'Big skin'.$labelPro,
						'modern' => 'Modern skin'.$labelPro,
						'sharp' => 'Sharp skin'.$labelPro,
						'round' => 'Round skin'.$labelPro,
						'square' => 'Square skin'.$labelPro
					);
				?>
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Filter skin', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may select the price filter skin', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::selectbox('f_skin_type', array(
							'options' => $skins,
							'attrs' => $isPro ? '' : 'class="wpfWithProAd"'
						))
						?>
					</td>
					<td class="col-md-3">
						<!-- <div class="wpfDefaultSkinPreview">
							<label>Preview</label><br>
							<div id="wpfSliderRange" class="wpfPriceFilterRange"></div>
						</div> -->
					</td>
				</tr>
				<?php if($isPro) {
					dispatcherWpf::doAction('addEditTabFilters', 'partEditTabFiltersPriceSkin');
				} else {
					foreach ($skins as $key => $value) {
						if(strpos($value, $labelPro)) {
					?>
					<tr class="col-md-12 wpfPriceSkinPro wpfHidden" data-type="<?php echo $key; ?>">
						<td class="col-md-12" colspan="3">
							<a href="https://woobewoo.com/plugins/woocommerce-filter/" target="_blank">
								<img class="wpfProAd" src="<?php echo $adPath.'price_skin_'.$key.'.png';?>">
							</a>
						</td>
					</tr>
				<?php }}} ?>
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Show price input fields', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::checkbox('f_show_inputs', array('checked' => 1))?>
					</td>
					<!-- <td class="col-md-3" style="display:none">
						<div class="wpfPriceInputs">
							<input type="number" min="0" max="999" id="wpfMinPrice" class="wpfPriceRangeField" value="200" />
							<span class="wpfFilterDelimeter"> - </span>
							<input type="number" min="0" max="1000" id="wpfMaxPrice" class="wpfPriceRangeField" value="600" /> <?php echo get_woocommerce_currency_symbol()?>
						</div>
					</td> -->
				</tr>
				<tr class="col-md-12 f_show_inputs_enabled_position">
					<td class="col-md-3">
						<?php _e('Symbol position', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::selectbox('f_currency_position', array(
							'options' => array('before' => 'Before', 'after' => 'After'),
						)); ?>
					</td>
				</tr>
				<tr class="col-md-12 f_show_inputs_enabled_currency">
					<td class="col-md-3">
						<?php _e('Show currency as', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::selectbox('f_currency_show_as', array(
							'options' => array('symbol' => 'Symbol', 'code' => 'Code'),
						)); ?>
					</td>
				</tr>
				<tr class="col-md-12 f_show_inputs_enabled_tooltip">
					<td class="col-md-3">
						<?php _e('Use text tooltip instead of input fields', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::checkbox('f_price_tooltip_show_as', array('checked' => 1))?>
					</td>
				</tr>
				</tbody>
				<?php echo htmlWpf::hidden('f_name', array(
					'value' => __('Price', WPF_LANG_CODE)
				))?>
			</table>

			<table class="form-settings-table" data-filter="wpfPriceRange">
				<tbody class="col-md-12">
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show title label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show title label', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_enable_title', array(
							'options' => array('no' => 'No', 'yes_close' => 'Yes, show as close', 'yes_open' => 'Yes, show as opened'),
						));
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show on frontend as', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Depending on whether you need one or several categories to be available at the same time, you may show your categories list as checkbox or dropdown.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_frontend_type', array(
							'options' => array('list' => 'Checkbox list', 'dropdown' => 'Dropdown'),
						))
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Dropdown label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Dropdown first option text.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_dropdown_first_option_text', array('placeholder' => esc_html(__('Select all', WPF_LANG_CODE))))?>
					</td>
				</tr>
				<tr id="wpfRangeAuthomatic" class="col-md-12 wpfAutomaticOrByHand">
					<td class="col-md-5">
						<?php _e('Set range automatically', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('If this option is enabled, you may set the price range settings automatically.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_range_automatic', array(
							'checked' => 1
						))?>
					</td>
				</tr>
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-5">
						<?php _e('Set start price', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Set the minimum price. If field live empty  will be set the price of the cheapest product from the store.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_min', array())?>
					</td>
				</tr>
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-5">
						<?php _e('Set end price', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Set the maximum price. If field live empty  will be set the price of the dearest product from the store.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_max', array())?>
					</td>
				</tr>
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-5">
						<?php _e('Set the range between prices', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Set the price range between the filter options.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_range', array())?>
					</td>
				</tr>
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-5">
						<?php _e('Set maximum range count', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Evenly calculate the range for a given number of positions.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_max_options_count', array())?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Price range step', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may set the value of prise increase step. The default value is set to 20. All the steps are equal. When setting the step, please note that the number of elements in the list should not exceed 100, otherwise the step setting will be reset and automatically calculated.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_step', array(
							'value' => 20,
						))?>
					</td>
				</tr>
				<tr id="wpfRangeByHand" class="col-md-12 wpfAutomaticOrByHand">
					<td class="col-md-5">
						<?php _e('Set range manually', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('If this option is enabled, you may press the "Setup" button and customize your price range settings. You may increase or decrease the number of steps and set different values for each step.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_range_by_hands', array()) ?>
						<button class="button wpfRangeByHandSetup"><?php echo esc_html(__('Setup', WPF_LANG_CODE))?></button>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Maximum height in frontend', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Set maximum displayed height in frontend.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_max_height', array('value'=>'200'))?> px
					</td>
				</tr>
				</tbody>
				<?php echo htmlWpf::hidden( 'f_range_by_hands_values', array()) ?>
				<?php echo htmlWpf::hidden('f_name', array(
					'value' => __('Price range', WPF_LANG_CODE)
				))?>
			</table>

			<table class="form-settings-table" data-filter="wpfSortBy">
				<tbody class="col-md-12">
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show title label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show title label', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_enable_title', array(
							'options' => array('no' => 'No', 'yes_close' => 'Yes, show as close', 'yes_open' => 'Yes, show as opened'),
						));
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Sort options', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may select the sorting options available for your site users (min two options).', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-6">
						<?php
							$options = array();
							foreach ($this->getModel('woofilters')->getFilterLabels('SortBy') as $key => $value) {
								$options[] = array('id' => $key, 'checked' => true, 'text' => htmlWpf::text('f_option_labels['.$key.']', array('placeholder' => esc_html($value), 'attrs' => ' style="padding:3px 5px;"')), 'usetable' => false, 'attrs' => false);
							}
							echo htmlWpf::checkboxlist('f_options', array('options' => $options))
						?>
					</td>
				</tr>
				<?php echo htmlWpf::hidden('f_name', array(
					'value' => __('Sort by', WPF_LANG_CODE)
				))?>
				</tbody>
			</table>

			<table class="form-settings-table" data-filter="wpfCategory">
                <thead class="col-md-12">
                    <tr class="col-md-12">
                        <td colspan="2" class="col-md-12">
                            <button class="button button-duplicate wpfDuplicateButton">
                                <i class="fa fa-fw fa-clone" aria-hidden="true"></i><span><?php echo __('Duplicate filter', WPF_LANG_CODE); ?></span>
                            </button>
                        </td>
                    </tr>
                </thead>
				<tbody class="col-md-12">
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show title label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show title label', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_enable_title', array(
							'options' => array('no' => 'No', 'yes_close' => 'Yes, show as close', 'yes_open' => 'Yes, show as opened'),
						));
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show on frontend as', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Depending on whether you need one or several categories to be available at the same time, you may show your categories list as checkbox or dropdown.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_frontend_type', array(
							'options' => array('list' => 'Radiobuttons list (single select)', 'dropdown' => 'Dropdown (single select)', 'multi' => 'Checkbox list (multiple select)'.$labelPro),
						))
						?>
					</td>
				</tr>
				<?php if($isPro) {
					dispatcherWpf::doAction('addEditTabFilters', 'partEditTabFiltersCategoryMulti');
				} else {?>

				<?php } ?>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Dropdown label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Dropdown first option text.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_dropdown_first_option_text', array('placeholder' => esc_html(__('Select all', WPF_LANG_CODE))))?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Sort by', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may set categories sorting by ascendance or descendance.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_sort_by', array(
							'options' => array('asc' => 'ASC', 'desc' => 'DESC', 'default' => 'Default'.$labelPro),
							'attrs' => $isPro ? '' : 'class="wpfWithProAd"'
						))
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Order by custom', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Categories are displayed according to the order of their selection in the input fields.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_order_custom', array())?>
					</td>
				</tr>
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-5">
						<?php _e('Show search field', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show fast search field.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_search_field', array())?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show hierarchical', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show paternal and subsidiary categories (for checkbox list).', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_show_hierarchical', array())?>
					</td>
				</tr>
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-5">
						<?php _e('Hierarchical limit', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Hierarchical limit .', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_hierarchical_limit', array())?>
					</td>
				</tr>
                <tr class="col-md-12 wpfHidden" data-trigger="f_show_hierarchical">
                    <td class="col-md-5">
                        <?php _e('Hide categories parent', WPF_LANG_CODE)?>
                        <i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show only categories children.', WPF_LANG_CODE))?>"></i>
                    </td>
                    <td class="col-md-5">
                        <?php echo htmlWpf::checkbox('f_hide_parent', array())?>
                    </td>
                </tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show count', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show count display the number of products that have the appropriate parameter (attribute, category, tag).', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_show_count', array())?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Hide categories without products', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Hide categories without products', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_hide_empty', array())?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Product categories', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may select product categories to be displayed on your site from the list. If you want to select several categories, hold the "Shift" button and click on category names. Or you can hold "Ctrl" and click on category names. Press "Ctrl" + "a" for checking all categories.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectlist('f_mlist', array(
							'options' => $categoryDisplay,
                            'data-parents' => json_encode($parentCategories, JSON_HEX_QUOT | JSON_HEX_TAG)
						))?>
					</td>
				</tr>
                <tr class="col-md-12">
                    <td class="col-md-5">
                        <?php _e('Make selected categories as default', WPF_LANG_CODE)?>
                        <i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Selected categories will be marked as default and hidden on frontend.', WPF_LANG_CODE))?>"></i>
                    </td>
                    <td class="col-md-5">
                        <?php echo htmlWpf::checkbox('f_hidden_categories', array())?>
                    </td>
                </tr>
                <tr class="col-md-12">
                    <td class="col-md-5">
                        <?php _e('Clear filter only to selected categories', WPF_LANG_CODE)?>
                        <i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('When the filter is clear, he will be filtered only by selected items. Be careful when using two or more category filters!', WPF_LANG_CODE))?>"></i>
                    </td>
                    <td class="col-md-5">
                        <?php echo htmlWpf::checkbox('f_filtered_by_selected', array())?>
                    </td>
                </tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Exclude terms ids', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may exclude category terms from filter by ids. Example input: 1,2,3 ', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_exclude_terms', array())?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show search', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show search display the bar for searching by category name in the filter.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php
							$labels = $this->getModel('woofilters')->getFilterLabels('Category');
							echo htmlWpf::checkbox('f_show_search_input', array()).'&nbsp;';
							echo htmlWpf::text('f_search_label', array('placeholder' => esc_html($labels['search'])));
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Maximum height in frontend', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Set maximum displayed height in frontend.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_max_height', array('value'=>'200'))?> px
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Hide child', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Hide child taxonomy.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_hide_taxonomy', array())?>
					</td>
				</tr>
				<tr class="col-md-12">
                    <td class="col-md-5">
                        <?php _e('Always display all categories', WPF_LANG_CODE)?>
                        <i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('If checked, the entire list of categories will always be visible, otherwise only available for filtered items', WPF_LANG_CODE))?>"></i>
                    </td>
                    <td class="col-md-5">
                        <?php echo htmlWpf::checkbox('f_show_all_categories', array())?>
                    </td>
                </tr>
				</tbody>
				<?php echo htmlWpf::hidden('f_name', array(
					'value' => __('Product categories', WPF_LANG_CODE)
				))?>
			</table>
			<table class="form-settings-table" data-filter="wpfTags">
                <thead class="col-md-12">
                <tr class="col-md-12">
                    <td colspan="2" class="col-md-12">
                        <button class="button button-duplicate wpfDuplicateButton">
                            <i class="fa fa-fw fa-clone" aria-hidden="true"></i><span><?php echo __('Duplicate filter', WPF_LANG_CODE); ?></span>
                        </button>
                    </td>
                </tr>
                </thead>
				<tbody class="col-md-12">
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show title label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show title label', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_enable_title', array(
							'options' => array('no' => 'No', 'yes_close' => 'Yes, show as close', 'yes_open' => 'Yes, show as opened'),
						));
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show on frontend as', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Depending on whether you need one or several tags to be available at the same time, you may show your tags list as checkbox or dropdown.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_frontend_type', array(
							'options' => array('list' => 'Checkbox list', 'dropdown' => 'Dropdown', 'mul_dropdown' => 'Multiple Dropdown'),
						))
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Dropdown label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Dropdown first option text.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_dropdown_first_option_text', array('placeholder' => esc_html(__('Select all', WPF_LANG_CODE))))?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Sort by', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may set tags sorting by ascendance or descendance.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_sort_by', array(
							'options' => array('asc' => 'ASC', 'desc' => 'DESC'),
						))
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Order by custom', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Tags are displayed according to the order of their selection in the input fields.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_order_custom', array())?>
					</td>
				</tr>
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-5">
						<?php _e('Show search field', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show fast search field.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_search_field', array())?>
					</td>
				</tr>
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-5">
						<?php _e('Show hierarchical', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show hierarchical.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_show_hierarchical', array())?>
					</td>
				</tr>
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-5">
						<?php _e('Hierarchical limit', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Hierarchical limit .', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_hierarchical_limit', array())?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show count', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show count display the number of products that have the appropriate parameter.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_show_count', array())?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Hide tags without products', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Hide tags without products', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('f_hide_empty', array())?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Product tags', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may select product tags to be displayed on your site from the list. If you want to select several tags, hold the "Shift" button and click on category names. Or you can hold "Ctrl" and click on category names. Press "Ctrl" + "a" for checking all tags. ', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php
						if(!empty($tagsDisplay)){
							echo htmlWpf::selectlist('f_mlist', array(
								'options' => $tagsDisplay,
							));
						}else{
							_e('No tags', WPF_LANG_CODE);
						}
						?>
					</td>
				</tr>
                <tr class="col-md-12">
                    <td class="col-md-5">
                        <?php _e('Make selected tags as default', WPF_LANG_CODE)?>
                        <i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Selected tags will be marked as default.', WPF_LANG_CODE))?>"></i>
                    </td>
                    <td class="col-md-5">
                        <?php echo htmlWpf::checkbox('f_hidden_tags', array())?>
                    </td>
                </tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Exclude terms ids', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may exclude tags terms from filter by ids. Example input: 1,2,3 ', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_exclude_terms', array())?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show search', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show search.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php
							$labels = $this->getModel('woofilters')->getFilterLabels('Tags');
							echo htmlWpf::checkbox('f_show_search_input', array()).'&nbsp;';
							echo htmlWpf::text('f_search_label', array('placeholder' => esc_html($labels['search'])));
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Maximum height in frontend', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Set maximum displayed height in frontend.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_max_height', array('value'=>'200'))?> px
					</td>
				</tr>
                <tr class="col-md-12">
                    <td class="col-md-5">
                        <?php _e('Always display all tags', WPF_LANG_CODE)?>
                        <i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('If checked, the entire list of tags will always be visible, otherwise only available for filtered items', WPF_LANG_CODE))?>"></i>
                    </td>
                    <td class="col-md-6">
                        <?php echo htmlWpf::checkbox('f_show_all_tags', array())?>
                    </td>
                </tr>
				</tbody>
				<?php echo htmlWpf::hidden('f_name', array(
					'value' => __('Product tags', WPF_LANG_CODE)
				))?>
			</table>

			<table class="form-settings-table" data-filter="wpfAttribute" data-title="Attribute">
				<tbody class="col-md-12">
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Select attribute', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may select attribute to be displayed on your site from the list.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-6">
						<?php
						if(!empty($attrDisplay)){
							echo htmlWpf::selectbox('f_list', array(
								'options' => $attrDisplay,
							));
						}else{
							_e('No attribute', WPF_LANG_CODE);
						}
						?>
					</td>
					<td class="col-md-3"></td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Show title label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show title label', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::selectbox('f_enable_title', array(
							'options' => array('no' => 'No', 'yes_close' => 'Yes, show as close', 'yes_open' => 'Yes, show as opened'),
						));
						?>
					</td>
					<td class="col-md-3"></td>
				</tr>
				<?php
					$attributesTypes = array(
						'list' => 'Checkbox list',
						'dropdown' => 'Dropdown',
						'mul_dropdown' => 'Multiple Dropdown',
						'colors' => 'Colors'.$labelPro
					);
				?>
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Show on frontend as', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Depending on whether you need one or several attributes to be available at the same time, you may show your attributes list as checkbox or dropdown.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_frontend_type', array(
							'options' => $attributesTypes,
							'attrs' => $isPro ? '' : 'class="wpfWithProAd"'
						))
						?>
					</td>
					<td class="col-md-3"></td>
				</tr>
				<?php if($isPro) {
					dispatcherWpf::doAction('addEditTabFilters', 'partEditTabFiltersAttributeColors');
				} else {
					foreach ($attributesTypes as $key => $value) {
						if(strpos($value, $labelPro)) {
					?>
					<tr class="col-md-12 wpfAttributesTypePro wpfHidden" data-type="<?php echo $key; ?>">
						<td class="col-md-12" colspan="3">
							<a href="https://woobewoo.com/plugins/woocommerce-filter/" target="_blank">
								<img class="wpfProAd" src="<?php echo $adPath.'attributes_'.$key.'.png';?>">
							</a>
						</td>
					</tr>
				<?php }}} ?>
				<tr class="col-md-12 wpfTypeSwitchable" data-type="dropdown">
					<td class="col-md-3">
						<?php _e('Dropdown label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Dropdown first option text.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::text('f_dropdown_first_option_text', array('placeholder' => esc_html(__('Select all', WPF_LANG_CODE)), 'attrs' => 'class="wpfSmallInput"'))?>
					</td>
					<td class="col-md-3"></td>
				</tr>
				<!--<tr class="col-md-12 wpfHidden">
					<td class="col-md-3">
						<?php //_e('Show search field', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php //echo esc_html(__('Show fast search field.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php //echo htmlWpf::checkbox('f_search_field', array())?>
					</td>
				</tr>-->
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-3">
						<?php _e('Show hierarchical', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show hierarchical.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::checkbox('f_show_hierarchical', array())?>
					</td>
					<td class="col-md-3"></td>
				</tr>
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-3">
						<?php _e('Hierarchical limit', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Hierarchical limit .', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::checkbox('f_hierarchical_limit', array())?>
					</td>
					<td class="col-md-3"></td>
				</tr>
                <tr class="col-md-12">
                    <td class="col-md-3">
                        <?php _e('Order by custom', WPF_LANG_CODE)?>
                        <i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Attributes are displayed according to the order of their selection in the input fields.', WPF_LANG_CODE))?>"></i>
                    </td>
                    <td class="col-md-6">
                        <?php echo htmlWpf::checkbox('f_order_custom', array())?>
                    </td>
                </tr>
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Hide attributes without products', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Hide attributes without products', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::checkbox('f_hide_empty', array())?>
					</td>
					<td class="col-md-3"></td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Show count', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show count display the number of products that have the appropriate parameter.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::checkbox('f_show_count', array())?>
					</td>
					<td class="col-md-3"></td>
				</tr>
                <tr class="col-md-12 wpfHidden">
                    <td class="col-md-3">
                        <?php _e('Attributes', WPF_LANG_CODE)?>
                        <i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may select attributes to be displayed on your site from the list. If you want to select several attributes, hold the "Shift" button and click on names. Or you can hold "Ctrl" and click on names. Press "Ctrl" + "a" for checking all names. ', WPF_LANG_CODE))?>"></i>
                    </td>
                    <td class="col-md-5">
                        <?php
                        if(!empty($attsTagsDisplay)){
                            echo htmlWpf::selectlist('f_mlist', array(
                                'options' => $attsTagsDisplay,
                                'attrs' => $attrDisplayTerms
                            ));
                        }else{
                            _e('No attributes', WPF_LANG_CODE);
                        }
                        ?>
                    </td>
                    <td class="col-md-3"></td>
                </tr>
                <tr class="col-md-12">
                    <td class="col-md-5">
                        <?php _e('Make selected attributes as default', WPF_LANG_CODE)?>
                        <i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Selected attributes will be marked as default.', WPF_LANG_CODE))?>"></i>
                    </td>
                    <td class="col-md-5">
                        <?php echo htmlWpf::checkbox('f_hidden_attributes', array())?>
                    </td>
                </tr>
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Logic', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_query_logic', array('options' => array('and' => 'And', 'or' => 'Or'), 'value' => 'or'))?>
					</td>
					<td class="col-md-3"></td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Sort by', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may set attributes sorting by ascendance or descendance.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::selectbox('f_sort_by', array(
							'options' => array('' => __('Don\'t sort', WPF_LANG_CODE), 'asc' => 'ASC', 'desc' => 'DESC'),
						))
						?>
					</td>
					<td class="col-md-3"></td>
				</tr>
				<tr class="col-md-12 wpfTypeSwitchable" data-type="list">
					<td class="col-md-3">
						<?php _e('Show search', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show search.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-6">
						<?php
							$labels = $this->getModel('woofilters')->getFilterLabels('Attribute');
							echo htmlWpf::checkbox('f_show_search_input', array()).'&nbsp;';
							echo htmlWpf::text('f_search_label', array('placeholder' => esc_html($labels['search'])));
						?>
					</td>
					<td class="col-md-3"></td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Maximum height in frontend', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Set maximum displayed height in frontend.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-6">
						<?php echo htmlWpf::text('f_max_height', array('value'=>'200', 'attrs' => 'class="wpfSmallInput"'))?> px
					</td>
					<td class="col-md-3"></td>
				</tr>
                <tr class="col-md-12">
                    <td class="col-md-3">
                        <?php _e('Always display all attributes', WPF_LANG_CODE)?>
                        <i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('If checked, the entire list of attributes will always be visible, otherwise only available for filtered items', WPF_LANG_CODE))?>"></i>
                    </td>
                    <td class="col-md-6">
                        <?php echo htmlWpf::checkbox('f_show_all_attributes', array())?>
                    </td>
                    <td class="col-md-3"></td>
                </tr>
				<!--										<tr class="col-md-12">-->
				<!--											<td class="col-md-5">-->
				<!--												--><?php //_e('Hide child', WPF_LANG_CODE)?>
				<!--												<i class="fa fa-question supsystic-tooltip" title="--><?php //echo esc_html(__('Hide child taxonomy.', WPF_LANG_CODE))?><!--"></i>-->
				<!--											</td>-->
				<!--											<td class="col-md-5">-->
				<!--												--><?php //echo htmlWpf::checkbox('f_hide_taxonomy', array())?>
				<!--											</td>-->
				<!--										</tr>-->
				</tbody>
				<?php echo htmlWpf::hidden('f_name', array(
					'value' => __('Attribute', WPF_LANG_CODE)
				))?>
			</table>
			<table class="form-settings-table" data-filter="wpfAuthor">
				<tbody class="col-md-12">
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show title label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show title label', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_enable_title', array(
							'options' => array('no' => 'No', 'yes_close' => 'Yes, show as close', 'yes_open' => 'Yes, show as opened'),
						));
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('You can define which role show users in the drop down', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('You can define which role show users in the drop down', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectlist('f_mlist', array(
							'options' => $roles,
						))?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show on frontend as', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('You may show your roles list as checkbox or dropdown.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_frontend_type', array(
							'options' => array('list' => 'Checkbox list', 'dropdown' => 'Dropdown'),
						))
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Dropdown label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Dropdown first option text.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_dropdown_first_option_text', array('placeholder' => esc_html(__('Select all', WPF_LANG_CODE))))?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show search', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show search display the bar for searching by author name in the filter.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php
							$labels = $this->getModel('woofilters')->getFilterLabels('Author');
							echo htmlWpf::checkbox('f_show_search_input', array()).'&nbsp;';
							echo htmlWpf::text('f_search_label', array('placeholder' => esc_html($labels['search'])));
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Maximum height in frontend', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Set maximum displayed height in frontend.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_max_height', array('value'=>'200'))?> px
					</td>
				</tr>
				</tbody>
				<?php echo htmlWpf::hidden('f_name', array(
					'value' => __('Author', WPF_LANG_CODE)
				))?>
			</table>
			<table class="form-settings-table" data-filter="wpfFeatured">
				<tbody class="col-md-12">
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show title label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show title label', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_enable_title', array(
							'options' => array('no' => 'No', 'yes_close' => 'Yes, show as close', 'yes_open' => 'Yes, show as opened'),
						));
						?>
					</td>
				</tr>
				</tbody>
				<?php echo htmlWpf::hidden('f_name', array(
					'value' => __('Featured', WPF_LANG_CODE)
				))?>
			</table>
			<table class="form-settings-table" data-filter="wpfOnSale">
				<tbody class="col-md-12">
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show title label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show title label', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_enable_title', array(
							'options' => array('no' => 'No', 'yes_close' => 'Yes, show as close', 'yes_open' => 'Yes, show as opened'),
						));
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Checkbox label', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-5">
						<?php 
							$labels = $this->getModel('woofilters')->getFilterLabels('OnSale');
							echo htmlWpf::text('f_checkbox_label', array('placeholder' => esc_html($labels['onsale'])));
						?>
					</td>
				</tr>
				</tbody>
				<?php echo htmlWpf::hidden('f_name', array(
					'value' => __('On sale', WPF_LANG_CODE)
				))?>
			</table>
			<table class="form-settings-table" data-filter="wpfInStock">
				<tbody class="col-md-12">
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show title label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show title label', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_enable_title', array(
							'options' => array('no' => 'No', 'yes_close' => 'Yes, show as close', 'yes_open' => 'Yes, show as opened'),
						));
						?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Dropdown label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Dropdown first option text.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_dropdown_first_option_text', array('placeholder' => esc_html(__('Select all', WPF_LANG_CODE))))?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Stock status', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may select the sorting options available for your site users (min two options).', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php 
							$options = array();
							$labels = $this->getModel('woofilters')->getFilterLabels('InStock');
							foreach($labels as $key => $value) {
								$options[] = array( 'id' => $key, 'checked' => true, 'text' => esc_html($value), 'usetable' => false, 'attrs' => false);
							}
							echo htmlWpf::checkboxlist('f_options', array('options' => $options))
						?>
					</td>
				</tr>
                <tr class="col-md-12">
                    <td class="col-md-5">
                        <?php _e('Change status names', WPF_LANG_CODE)?>
                        <i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may change stock status names.', WPF_LANG_CODE))?>"></i>
                    </td>
                    <td class="col-md-5">
                        <?php echo htmlWpf::checkbox('f_status_names', array('attrs' => 'class="wpfStatusNamesTrigger"'))?>
                    </td>
                </tr>
                <tr class="col-md-12 wpfHidden">
                    <td class="col-md-5"></td>
                    <td class="col-md-5">
                        <?php echo htmlWpf::text('f_stock_statuses[in]', array('placeholder' => esc_html($labels['instock']), 'attrs' => 'class="wpfInStockInput"'))?>
                        <?php echo htmlWpf::text('f_stock_statuses[out]', array('placeholder' => esc_html($labels['outofstock']), 'attrs' => 'class="wpfInStockInput"'))?>
                        <?php echo htmlWpf::text('f_stock_statuses[on]', array('placeholder' => esc_html($labels['onbackorder']), 'attrs' => 'class="wpfInStockInput"'))?>
                    </td>
                </tr>
				</tbody>
				<?php echo htmlWpf::hidden('f_name', array(
					'value' => __('Stock status', WPF_LANG_CODE)
				))?>
			</table>
			<table class="form-settings-table" data-filter="wpfRating">
				<tbody class="col-md-12">
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Show title label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show title label', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_enable_title', array(
							'options' => array('no' => 'No', 'yes_close' => 'Yes, show as close', 'yes_open' => 'Yes, show as opened'),
						));
						?>
					</td>
					<td></td>
				</tr>
				<?php
					$ratingTypes = array(
						'linestars' => 'Single line star rating'.$labelPro,
						'liststars' => 'Multiline star rating'.$labelPro,
						'list' => 'Ratiobuttons list',
						'dropdown' => 'Dropdown',
					);
				?>
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Show on frontend as', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Depending on whether you need one or several attributes to be available at the same time, you may show your attributes list as checkbox or dropdown.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('f_frontend_type', array(
							'options' => $ratingTypes,
							'attrs' => $isPro ? '' : 'class="wpfWithProAd"'
						))
						?>
					</td>
					<td></td>
				</tr>
				<tr class="col-md-12 wpfTypeSwitchable" data-type="dropdown">
					<td class="col-md-3">
						<?php _e('Dropdown label', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Dropdown first option text.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_dropdown_first_option_text', array('placeholder' => esc_html(__('Select all', WPF_LANG_CODE)), 'attrs' => 'class="wpfSmallInput"'))?>
					</td>
					<td></td>
				</tr>
				<?php if($isPro) {
					dispatcherWpf::doAction('addEditTabFilters', 'partEditTabFiltersRatingStars');
				} else {
					foreach ($ratingTypes as $key => $value) {
						if(strpos($value, $labelPro)) {
					?>
					<tr class="col-md-12 wpfRatingTypePro wpfHidden" data-type="<?php echo $key; ?>">
						<td class="col-md-12" colspan="3">
							<a href="https://woobewoo.com/plugins/woocommerce-filter/" target="_blank">
								<img class="wpfProAd" src="<?php echo $adPath.'rating_'.$key.'.png';?>">
							</a>
						</td>
					</tr>
				<?php }}} ?>
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Additional text for 1-4', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Additional text for 1-4 rating filter.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_add_text', array('placeholder' => esc_html(__('and up', WPF_LANG_CODE)), 'attrs' => 'class="wpfSmallInput"'))?>
					</td>
					<td></td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-3">
						<?php _e('Additional text for 5', WPF_LANG_CODE)?>
						<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Additional text for 5-star rating filter.', WPF_LANG_CODE))?>"></i>
					</td>
					<td class="col-md-5">
						<?php echo htmlWpf::text('f_add_text5', array('placeholder' => esc_html(__('5 only', WPF_LANG_CODE)), 'attrs' => 'class="wpfSmallInput"'))?>
					</td>
					<td></td>
				</tr>
				</tbody>
				<?php echo htmlWpf::hidden('f_name', array(
					'value' => __('Rating', WPF_LANG_CODE)
				))?>
			</table>
			<?php if($isPro) {
				dispatcherWpf::doAction('addEditTabFilters', 'partEditTabFiltersSearchText');
			} else {?>
				<table class="form-settings-table" data-filter="wpfSearchText" data-disabled="1">
					<tbody class="col-md-12">
						<tr class="col-md-12">
							<td class="col-md-12">
								<a href="https://woobewoo.com/plugins/woocommerce-filter/" target="_blank">
									<img class="wpfProAd" src="<?php echo $adPath.'search_text.png';?>">
								</a>
							</td>
						</tr>
					</tbody>
					<?php echo htmlWpf::hidden('f_name', array(
						'value' => __('Search by Text', WPF_LANG_CODE)
					))?>
				</table>
			<?php }?>
            <?php if($isPro && $wpfBrand['exist']) {
                dispatcherWpf::doAction('addEditTabFilters', 'partEditTabFiltersBrand');
            } elseif ($wpfBrand['exist']) {?>
                <table class="form-settings-table" data-filter="wpfBrand" data-disabled="1">
                    <tbody class="col-md-12">
                    <tr class="col-md-12">
                        <td class="col-md-12">
                            <a href="https://woobewoo.com/plugins/woocommerce-filter/" target="_blank">
                                <img class="wpfProAd" src="<?php echo $adPath.'product_brands.png';?>">
                            </a>
                        </td>
                    </tr>
                    </tbody>
                    <?php echo htmlWpf::hidden('f_name', array(
                        'value' => __('Product brands', WPF_LANG_CODE)
                    ))?>
                </table>
            <?php }?>
		</div>
	</div>
</div>
