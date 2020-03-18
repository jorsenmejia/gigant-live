<div class="row row-tab" id="row-tab-design">
	<div class="col-xs-12">
		<table class="form-settings-table">
			<tbody class="col-md-6">

				<tr class="col-md-12 wpfHidden">
					<td class="col-md-5">
						<?php _e('Show Term Products Count', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show the number of products next to the title.', WPF_LANG_CODE))?>"></i></td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('settings[show_products_count]', array(
							'checked' => (isset($this->settings['settings']['show_products_count']) ? (int) $this->settings['settings']['show_products_count'] : '')
						))?>
					</td>
				</tr>
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-5">
						<?php _e('Show Term Search Fields', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('show the search field in supported filters.', WPF_LANG_CODE))?>"></i></td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('settings[show_products_search]', array(
							'checked' => (isset($this->settings['settings']['show_products_search']) ? (int) $this->settings['settings']['show_products_search'] : '')
						))?>
					</td>
				</tr>
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-5">
						<?php _e('Stepped Filter Selection', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('After selection filter options, you can confirm filtering in "Show result" popup.', WPF_LANG_CODE))?>"></i></td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('settings[stepped_selection]', array(
							'checked' => (isset($this->settings['settings']['stepped_selection']) ? (int) $this->settings['settings']['stepped_selection'] : '')
						))?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Enable Ajax', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('This option enables Ajax search. Filtering starts as soon as filter elements change and the page reloads automatically.', WPF_LANG_CODE))?>"></i></td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('settings[enable_ajax]', array(
							'checked' => (isset($this->settings['settings']['enable_ajax']) ? (int) $this->settings['settings']['enable_ajax'] : 0)
						))?>
					</td>
				</tr>
				<tr class="col-md-12 wpfHidden">
					<td class="col-md-5">
						<?php _e('Selected Terms Collector', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show the selected filter elements above the products, with the ability to remove them from the filter.', WPF_LANG_CODE))?>"></i></td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('settings[selected_terms_collector]', array(
							'checked' => (isset($this->settings['settings']['selected_terms_collector']) ? (int) $this->settings['settings']['selected_terms_collector'] : '')
						))?>
					</td>
				</tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show Clear all button', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('If this option is enabled, the "Clear" button appears at the page. All filter presets will be removed after pressing the button.', WPF_LANG_CODE))?>"></i></td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('settings[show_clean_button]', array(
							'checked' => (isset($this->settings['settings']['show_clean_button']) ? (int) $this->settings['settings']['show_clean_button'] : '')
						))?>
					</td>
				</tr>
                <tr class="col-md-12 wpfHidden">
                    <td class="col-md-5">
                        <?php _e('Clear all button word', WPF_LANG_CODE)?>
                    </td>
                    <td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may change Clear all button word. Default is "Clear".', WPF_LANG_CODE))?>"></i></td>
                    <td class="col-md-5">
                        <?php echo htmlWpf::text('settings[show_clean_button_word]', array(
                            'value' => (isset($this->settings['settings']['show_clean_button_word']) ? $this->settings['settings']['show_clean_button_word'] : __('Clear', WPF_LANG_CODE)),
                        ))?>
                    </td>
                </tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show Clear block', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('If this option is enabled, the "< clear" links appears at the page next to the filter block titles. The presets of this filter block will be deleted after clicking on the link.', WPF_LANG_CODE))?>"></i></td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('settings[show_clean_block]', array(
							'checked' => (isset($this->settings['settings']['show_clean_block']) ? (int) $this->settings['settings']['show_clean_block'] : '')
						))?>
					</td>
				</tr>
                <tr class="col-md-12 wpfHidden">
                    <td class="col-md-5">
                        <?php _e('Clear block word', WPF_LANG_CODE)?>
                    </td>
                    <td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may change Clear block word. Default is "clear".', WPF_LANG_CODE))?>"></i></td>
                    <td class="col-md-5">
                        <?php echo htmlWpf::text('settings[show_clean_block_word]', array(
                            'value' => (isset($this->settings['settings']['show_clean_block_word']) ? $this->settings['settings']['show_clean_block_word'] : __('clear', WPF_LANG_CODE)),
                        ))?>
                    </td>
                </tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Show Filtering button', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('This button is necessary, when ajax mode is disabled. It allows users to set all necessary filter parameters before starting the filtering. This option is not available when Ajax is enabled.', WPF_LANG_CODE))?>"></i></td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('settings[show_filtering_button]', array(
							'checked' => (isset($this->settings['settings']['show_filtering_button']) ? (int) $this->settings['settings']['show_filtering_button'] : 1)
						))?>
					</td>
				</tr>
                <tr class="col-md-12">
                    <td class="col-md-5">
                        <?php _e('Filtering button text', WPF_LANG_CODE)?>
                    </td>
                    <td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may change filtering button word', WPF_LANG_CODE))?>"></i></td>
                    <td class="col-md-5">
                        <?php echo htmlWpf::text('settings[filtering_button_word]', array(
                            'value' => (isset($this->settings['settings']['filtering_button_word']) ? $this->settings['settings']['filtering_button_word'] : __('Filter', WPF_LANG_CODE)),
                        ))?>
                    </td>
                </tr>
                <tr class="col-md-12">
                    <td class="col-md-5">
                        <?php _e('Display items in a row', WPF_LANG_CODE)?>
                    </td>
                    <td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show filter items as row', WPF_LANG_CODE))?>"></i></td>
                    <td class="col-md-5">
                        <?php echo htmlWpf::checkbox('settings[display_items_in_a_row]', array(
                            'checked' => (isset($this->settings['settings']['display_items_in_a_row']) ? (int) $this->settings['settings']['display_items_in_a_row'] : 0)
                        ))?>
                    </td>
                </tr>
                <tr class="col-md-12 wpfHidden">
                    <td class="col-md-5">
                        <?php _e('Display cols in a row', WPF_LANG_CODE)?>
                    </td>
                    <td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show filter items as cols in row', WPF_LANG_CODE))?>"></i></td>
                    <td class="col-md-5">
                        <?php echo htmlWpf::text('settings[display_cols_in_a_row]', array(
                            'value' => (isset($this->settings['settings']['display_cols_in_a_row']) ? $this->settings['settings']['display_cols_in_a_row'] : 1),
                        ))?>
                    </td>
                </tr>
				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Display filter on', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Chose where display filter', WPF_LANG_CODE))?>"></i></td>
					<td class="col-md-5">
						<?php echo htmlWpf::selectbox('settings[display_for]', array(
							'options' => array('mobile' => 'Only for mobile', 'desktop' => 'Only for desktop', 'both' => 'For all device'),
							'value' => (isset($this->settings['settings']['display_for']) ? $this->settings['settings']['display_for'] : 'both'),
						))
						?>
					</td>
				</tr>
				<!--									<tr class="col-md-12">-->
				<!--										<td class="col-md-5">-->
				<!--											--><?php //_e('Show count', WPF_LANG_CODE)?>
				<!--										</td>-->
				<!--										<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="--><?php //echo esc_html(__('Show count near the terms.', WPF_LANG_CODE))?><!--"></i></td>-->
				<!--										<td class="col-md-5">-->
				<!--											--><?php //echo htmlWpf::checkbox('settings[show_count]', array(
				//												'checked' => (isset($this->settings['settings']['show_count']) ? (int) $this->settings['settings']['show_count'] : 1)
				//											))?>
				<!--										</td>-->
				<!--									</tr>-->

				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Recount products by selected filter', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Automatically recount product by selected filters (If product category loading slowly - Disable this function).', WPF_LANG_CODE))?>"></i></td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('settings[filter_recount]', array(
							'checked' => (isset($this->settings['settings']['filter_recount']) ? (int) $this->settings['settings']['filter_recount'] : '')
						))?>
					</td>
				</tr>

				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Sort by title after filtering', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Sort product list by title.', WPF_LANG_CODE))?>"></i></td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('settings[sort_by_title]', array(
							'checked' => (isset($this->settings['settings']['sort_by_title']) ? (int) $this->settings['settings']['sort_by_title'] : '')
						))?>
					</td>
				</tr>

				<tr class="col-md-12">
					<td class="col-md-5">
						<?php _e('Checked items to the top', WPF_LANG_CODE)?>
					</td>
					<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Lets checked terms will be on the top', WPF_LANG_CODE))?>"></i></td>
					<td class="col-md-5">
						<?php echo htmlWpf::checkbox('settings[checked_items_top]', array(
							'checked' => (isset($this->settings['settings']['checked_items_top']) ? (int) $this->settings['settings']['checked_items_top'] : '')
						))?>
					</td>
				</tr>

			<tr class="col-md-12">
				<td class="col-md-5">
					<?php _e('Filter Width', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you can set the filter width in pixels or percent.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<div class="wpfCombineOptions">
					<?php echo htmlWpf::text('settings[filter_width]', array(
						'value' => isset($this->settings['settings']['filter_width']) ? $this->settings['settings']['filter_width'] : '100',
						'attrs' => 'class="wpfSmallInput"'));
						echo htmlWpf::selectbox('settings[filter_width_in]', array(
						'options' => array('%' => '%', 'px' => 'px'),
						'value' => (isset($this->settings['settings']['filter_width_in']) ? $this->settings['settings']['filter_width_in'] : '%'),
					))
					?>
					</div>
				</td>
			</tr>
			<tr class="col-md-12">
				<td class="col-md-5">
					<?php _e('Filter Block Width', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you can set the filter block width in pixels or percent.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<div class="wpfCombineOptions">
					<?php echo htmlWpf::text('settings[filter_block_width]', array(
						'value' => isset($this->settings['settings']['filter_block_width']) ? $this->settings['settings']['filter_block_width'] : '100',
						'attrs' => 'class="wpfSmallInput"'));
						echo htmlWpf::selectbox('settings[filter_block_width_in]', array(
						'options' => array('%' => '%', 'px' => 'px'),
						'value' => (isset($this->settings['settings']['filter_block_width_in']) ? $this->settings['settings']['filter_block_width_in'] : '%'),
					))
					?>
					</div>
				</td>
			</tr>
			<tr class="col-md-12">
				<td class="col-md-5">
					<?php _e('Select Filter Buttons Position', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may select the position of filter buttons on the page.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::selectbox('settings[main_buttons_position]', array(
						'options' => array('top' => 'Top', 'bottom' => 'Bottom', 'both' => 'Both'),
						'value' => (isset($this->settings['settings']['main_buttons_position']) ? $this->settings['settings']['main_buttons_position'] : 'bottom'),
					))
					?>
				</td>
			</tr>
			<tr class="col-md-12">
				<td class="col-md-5">
					<?php _e('Enable filter icon on load', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Enable filter icon while page is loading.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::checkbox('settings[filter_loader_icon_onload_enable]', array(
						'checked' => (isset($this->settings['settings']['filter_loader_icon_onload_enable']) ? (int) $this->settings['settings']['filter_loader_icon_onload_enable'] : 1)
					))?>
				</td>
			</tr>
			<?php
			$colorPreview = (isset($this->settings['settings']['filter_loader_icon_color']) ? $this->settings['settings']['filter_loader_icon_color'] : 'black');
			$iconName = (isset($this->settings['settings']['filter_loader_icon_name']) ? $this->settings['settings']['filter_loader_icon_name'] : 'default');
			$iconNumber = (isset($this->settings['settings']['filter_loader_icon_number']) ? $this->settings['settings']['filter_loader_icon_number'] : '0');
			if(!$this->is_pro) $iconName = 'default';

			if($iconName === 'custom'){
				$htmlPreview = '<div class="supsystic-filter-loader wpfCustomLoader" style="'.(isset($this->settings['settings']['filter_loader_custom_icon']) ? $this->settings['settings']['filter_loader_custom_icon'] : '').'"></div>';
			}else if($iconName === 'default' || $iconName === 'spinner'){
				$htmlPreview = '<div class="supsystic-filter-loader spinner"></div>';
			}else{
				$htmlPreview = '<div class="supsystic-filter-loader la-'.$iconName.' la-2x" style="color: '.$colorPreview.'">';
				for($i = 1; $i <= $iconNumber; $i++){
					$htmlPreview .= '<div></div>';
				}
				$htmlPreview .= '</div>';
			}

			?>
			<tr class="col-md-12 wpfLoader">
				<td class="col-md-5">
					<?php _e('Filter Loader Icon', WPF_LANG_CODE)?> <sup class="wpfProOption"><a href="<?php echo $this->proLink.'?utm_source=plugin&utm_medium=loader-logo&utm_campaign=woocommerce-filter' ?>" tartget="_blank"><?php _e('PRO', WPF_LANG_CODE)?></a></sup>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may select the animated loader, which appears when filtering results are loading.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<div class="button chooseLoaderIcon"><?php _e('Choose Icon', WPF_LANG_CODE)?></div>
					<div class="wpfIconPreview"><?php echo $htmlPreview; ?></div>
					<?php echo htmlWpf::hidden('settings[filter_loader_icon_name]', array(
						'value' => (isset($this->settings['settings']['filter_loader_icon_name']) ? $this->settings['settings']['filter_loader_icon_name'] : 'default')
					))?>
					<?php echo htmlWpf::hidden('settings[filter_loader_icon_number]', array(
						'value' => (isset($this->settings['settings']['filter_loader_icon_number']) ? $this->settings['settings']['filter_loader_icon_number'] : '0')
					))?>
					<div class="wpfSelectFile">
						<?php
							echo htmlWpf::hidden('settings[filter_loader_custom_icon]', array(
								'value' => (isset($this->settings['settings']['filter_loader_custom_icon']) ? $this->settings['settings']['filter_loader_custom_icon'] : '')));
							if($this->is_pro) {
								echo htmlWpf::buttonA(array(
									'value' => __('Select icon', WPF_LANG_CODE),
            						'attrs' => 'id="wpfSelectLoaderButton" data-type="image"'));
							}
						?>
            		</div>
				</td>
			</tr>
			<tr class="col-md-12 wpfLoader wpfColorObserver">
				<td class="col-md-5">
					<?php _e('Filter Loader Color', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may select the color of filter loader animation.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::colorpicker('settings[filter_loader_icon_color]', array(
						'value' => (isset($this->settings['settings']['filter_loader_icon_color']) ? $this->settings['settings']['filter_loader_icon_color'] : 'black'),
						'attrs' => 'style="width: 50px"',
					))?>
				</td>
			</tr>
			<tr class="col-md-12">
				<td class="col-md-5">
					<?php _e('Enable overlay', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Enable overlay.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::checkbox('settings[enable_overlay]', array(
						'checked' => (isset($this->settings['settings']['enable_overlay']) ? (int) $this->settings['settings']['enable_overlay'] : '')
					))?>
				</td>
			</tr>
			<tr class="col-md-12">
				<td class="col-md-5">
					<?php _e('Overlay background color and opacity', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Overlay background color and opacity.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					 <?php echo htmlWpf::colorpicker('settings[overlay_background]', array(
 						'value' => (isset($this->settings['settings']['overlay_background']) ? $this->settings['settings']['overlay_background'] : 'black'),
 						'attrs' => 'style="width: 50px"',
 					))?>
				</td>
			</tr>
			<tr class="col-md-12">
				<td class="col-md-5">
					<?php _e('Show Loader Icon on overlay', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Enable filter icon while filtering process ongoing.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::checkbox('settings[enable_overlay_icon]', array(
						'checked' => (isset($this->settings['settings']['enable_overlay_icon']) ? (int) $this->settings['settings']['enable_overlay_icon'] : '')
					))?>
				</td>
			</tr>
			<tr class="col-md-12">
				<td class="col-md-5">
					<?php _e('Show loading word on overlay', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Show search word on overlay', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::checkbox('settings[enable_overlay_word]', array(
						'checked' => (isset($this->settings['settings']['enable_overlay_word']) ? (int) $this->settings['settings']['enable_overlay_word'] : '')
					))?>
				</td>
			</tr>
			<tr class="col-md-12">
				<td class="col-md-5">
					<?php _e('Set loading word for overlay', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may select overlay word for filter', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::text('settings[overlay_word]', array(
						'value' => (isset($this->settings['settings']['overlay_word']) ? $this->settings['settings']['overlay_word'] : 'WooBeWoo'),
					))?>
				</td>
			</tr>
			<tr class="col-md-12">
				<td class="col-md-5">
					<?php _e('Set no products found text', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Here you may input "no products found" text for category', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::text('settings[text_no_products]', array(
						'value' => (isset($this->settings['settings']['text_no_products']) ? $this->settings['settings']['text_no_products'] : 'No products found'),
					))?>
				</td>
			</tr>
			<tr class="col-md-12 wpfHidden">
				<td class="col-md-5">
					<?php _e('Filter style', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Full screen filter - is located over products and occupies the entire width of the screen, with the ability to collapse / expand.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::selectbox('settings[filter_position]', array(
						'options' => array('left_sidebar' => 'Left Sidebar', 'right_sidebar' => 'Right Sidebar', 'full_screen' => 'Full screen filter'),
						'value' => (isset($this->settings['settings']['filter_position']) ? $this->settings['settings']['filter_position'] : 'left_sidebar'),
					))
					?>
				</td>
			</tr>
			<tr class="col-md-12 wpfHidden">
				<td class="col-md-5">
					<?php _e('Max Columns', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('For Full screen filter mode - how many columns will be displayed', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::text('settings[max_columns]', array(
						'value' => (isset($this->settings['settings']['max_columns']) ? $this->settings['settings']['max_columns'] : '4'),
						'attrs' => 'placeholder="4"'
					))?>
				</td>
			</tr>
			<tr class="col-md-12 wpfHidden">
				<td class="col-md-5">
					<?php _e('Max Height in px', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('The maximum height for each of the filters, if the height is greater than display scroll ', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::text('settings[max_columns_height]', array(
						'value' => (isset($this->settings['settings']['max_columns_height']) ? $this->settings['settings']['max_columns_height'] : ''),
						'attrs' => 'placeholder=""'
					))?>
				</td>
			</tr>
			<tr class="col-md-12 wpfHidden">
				<td class="col-md-5">
					<?php _e('Max Height in px (Full screen mode)', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('The maximum height container for Full screen mode filter ', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::text('settings[max_full_screen_height]', array(
						'value' => (isset($this->settings['settings']['max_full_screen_height']) ? $this->settings['settings']['max_full_screen_height'] : ''),
						'attrs' => 'placeholder=""'
					))?>
				</td>
			</tr>
			<tr class="col-md-12 wpfHidden">
				<td class="col-md-5">
					<?php _e('Custom scroll bar', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Enable beautiful scrollbar.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::checkbox('settings[enable_beauty_scrollbar]', array(
						'checked' => (isset($this->settings['settings']['enable_beauty_scrollbar']) ? (int) $this->settings['settings']['enable_beauty_scrollbar'] : '')
					))?>
				</td>
			</tr>
			<tr class="col-md-12 wpfHidden">
				<td class="col-md-5">
					<?php _e('Select checkbox style', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Select checkbox style in frontend.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::selectbox('settings[checkbox_style]', array(
						'options' => array('checkbox' => 'Checkbox', 'square' => 'Square'),
						'value' => (isset($this->settings['settings']['checkbox_style']) ? $this->settings['settings']['checkbox_style'] : 'checkbox'),
					))
					?>
				</td>
			</tr>
			<tr class="col-md-12 wpfHidden">
				<td class="col-md-5">
					<?php _e('Select Hierarchy Style', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Select hierarchy style in frontend.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::selectbox('settings[hierarchy_style]', array(
						'options' => array('arrow' => 'Arrow', 'line' => 'Line', 'circle' => 'Circle'),
						'value' => (isset($this->settings['settings']['hierarchy_style']) ? $this->settings['settings']['hierarchy_style'] : 'line'),
					))
					?>
				</td>
			</tr>
			<tr class="col-md-12 wpfHidden">
				<td class="col-md-5">
					<?php _e('Select Mobile Filter Position', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Select mobile filter location.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::selectbox('settings[mobile_filter_position]', array(
						'options' => array('left' => 'Left', 'right' => 'Right'),
						'value' => (isset($this->settings['settings']['mobile_filter_position']) ? $this->settings['settings']['mobile_filter_position'] : 'left'),
					))
					?>
				</td>
			</tr>
			<tr class="col-md-12 wpfHidden">
				<td class="col-md-5">
					<?php _e('Set mobile resolution px', WPF_LANG_CODE)?>
				</td>
				<td class="col-md-2"><i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('The width of the screen at which the mobile filter is turned on.', WPF_LANG_CODE))?>"></i></td>
				<td class="col-md-5">
					<?php echo htmlWpf::text('settings[mobile_resolution]', array(
						'value' => (isset($this->settings['settings']['max_mobile_resolution']) ? $this->settings['settings']['max_mobile_resolution'] : ''),
						'attrs' => 'placeholder=""'
					))?>
				</td>
			</tr>
			</tbody>
		</table>
		<div class="wpfLoaderIconTemplate wpfHidden">
			<?php
				$loaderSkins = array(
					'timer' => 1, //number means count of div necessary to display loader
					'ball-beat'=> 3,
					'ball-circus'=> 5,
					'ball-atom'=> 4,
					'ball-spin-clockwise-fade-rotating'=> 8,
					'line-scale'=> 5,
					'ball-climbing-dot'=> 4,
					'square-jelly-box'=> 2,
					'ball-rotate'=> 1,
					'ball-clip-rotate-multiple'=> 2,
					'cube-transition'=> 2,
					'square-loader'=> 1,
					'ball-8bits'=> 16,
					'ball-newton-cradle'=> 4,
					'ball-pulse-rise'=> 5,
					'triangle-skew-spin'=> 1,
					'fire'=> 3,
					'ball-zig-zag-deflect'=> 2
				);
				?>
				<div class="items items-list">
					<div class="item">
						<div class="item-inner">
							<div class="item-loader-container">
								<div class="preicon_img" data-name="spinner" data-items="0">
									<div class="supsystic-filter-loader spinner"></div>
								</div>
							</div>
						</div>
						<div class="item-title">woobewoo</div>
					</div>
					<?php
						foreach ($loaderSkins as $name=>$number) {
							?>
							<div class="item">
								<div class="item-inner">
									<div class="item-loader-container">
										<div class="supsystic-filter-loader la-<?php echo $name; ?> la-2x preicon_img" data-name="<?php echo $name; ?>" data-items="<?php echo $number; ?>" style="color: black;">
											<?php
											for($i=0;$i<$number;$i++){
												echo '<div></div>';
											}
											?>
										</div>
									</div>
								</div>
								<div class="item-title"><?php echo $name; ?></div>
							</div>
					<?php }	?>
				</div>
		</div>
	</div>
</div>
