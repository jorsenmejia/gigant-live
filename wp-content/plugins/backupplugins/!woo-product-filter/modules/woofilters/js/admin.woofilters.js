(function ($, app) {

    function goTo(item) {
        jQuery('html,body').animate({'scrollTop': jQuery(item).offset().top - 30}, 1000);

        return false;
    }

	function AdminPage() {
		this.$obj = this;
		this.$allowMultipleFilters = ['wpfAttribute', 'wpfBrand', 'wpfCustomMeta'];
		this.$multiSelectFields = ['f_mlist[]'];
		this.$noOptionsFilters = [''];
		return this.$obj;
	}

	AdminPage.prototype.init = (function () {
		var _thisObj = this.$obj;
		_thisObj.eventsAdminPage();
		_thisObj.eventsFilters();
		_thisObj.setupPriceByHands();
	});

	/*AdminPage.prototype.chooseIconPopup = (function () {
		var _thisObj = this.$obj;
		if(typeof(_thisObj.chooseIconPopup) == 'function') {
			_thisObj.chooseIconPopup();
		}
	});*/

	AdminPage.prototype.setupPriceByHands = (function () {
		var _this = this.$obj;
		var options = {
			modal: true
			, autoOpen: false
			, width: 600
			, height: 400
			, buttons: {
				OK: function () {
					var emptyInput = false;
					var options = '';
					var range = jQuery('#wpfSetupPriceRangeByHand .wpfRangeByHand');

					//check if input is empty
					range.find('input').each(function () {
						if(!jQuery(this).val()) {
							jQuery(this).addClass('wpfWarning');
							emptyInput = true;
						}
					});

					if(!emptyInput){
						var rangeCount = range.length;
						var i = 1;
						range.each(function () {
							var el = jQuery(this);
							options += el.find('.wpfRangeByHandHandlerFrom input').val() + ',';
							if(i === rangeCount){
								options += el.find('.wpfRangeByHandHandlerTo input').val();
							}else{
								options += el.find('.wpfRangeByHandHandlerTo input').val() + ',';
							}

							i++;
						});

						jQuery('input[name="f_range_by_hands_values"]').val(options);
						$container.empty();
						$container.dialog('close');
						_this.saveFilters();
					}

				}
				, Cancel: function () {
					$container.empty();
					$container.dialog('close');

				}
			}
		};
		var $container = jQuery('<div id="wpfSetupPriceRangeByHand"></div>').dialog( options );

		jQuery('body').on('click', '.wpfRangeByHandSetup', function (e) {
			e.preventDefault();
			var appendTemplate = '';
			var priceRange = jQuery('input[name="f_range_by_hands_values"]').val();
			var template = jQuery('.wpfRangeByHandTemplate').clone().html();
			var templAddButton = jQuery('.wpfRangeByHandTemplateAddButton').clone().html();
			$container.empty();

			if(priceRange.length <= 0){
				for(var i = 1; i < 2; i++ ){
					appendTemplate += template;
				}
				appendTemplate += templAddButton;
				$container.append(appendTemplate);
				$container.dialog("option", "title", 'Price Range');
				$container.dialog('open');
			}else{
				var priceRangeArray = priceRange.split(",");
				for(var i = 0; i < priceRangeArray.length/2; i++ ){
					appendTemplate += template;
				}

				appendTemplate += templAddButton;
				$container.append(appendTemplate);
				$container.dialog("option", "title", 'Price Range');
				$container.dialog('open');

				var k = 0;
				jQuery('#wpfSetupPriceRangeByHand input').each(function(){
					var input = jQuery(this);
					if(k < priceRangeArray.length){
						input.val(priceRangeArray[k]);
						k++;
					}else{
						input.closest('.wpfRangeByHand').remove();
					}
				});
			}

		});

		jQuery('body').on('click', '.wpfAddPriceRange', function (e) {
			e.preventDefault();
			var templates = jQuery('.wpfRangeByHandTemplate').clone().html();
			jQuery(templates).insertBefore('.wpfRangeByHandAddButton');
			sortablePrice();
		});

		jQuery('body').on('click', '.wpfRangeByHandRemove', function(e){
			e.preventDefault();
			var _this = jQuery(this);
			_this.closest('.wpfRangeByHand').remove();
		});

		//make properties sortable
		function sortablePrice(){
			jQuery("#wpfSetupPriceRangeByHand").sortable({
				//containment: "parent",
				cursor: "move",
				axis: "y",
				handle: ".wpfRangeByHandHandler"
			});
		}
		sortablePrice();

	});

	AdminPage.prototype.eventsAdminPage = (function () {
		var _thisObj = this.$obj;
		// Initialize Main Tabs
		var $mainTabsContent = jQuery('.row-tab'),
			$mainTabs = jQuery('.wpfSub.tabs-wrapper.wpfMainTabs .button'),
			$currentTab = $mainTabs.filter('.current').attr('href');

		$mainTabsContent.filter($currentTab).addClass('active');

		$mainTabs.on('click', function (e) {
			e.preventDefault();
			var $this = jQuery(this),
				$curTab = $this.attr('href');

			$mainTabsContent.removeClass('active');
			$mainTabs.filter('.current').removeClass('current');
			$this.addClass('current');
			$mainTabsContent.filter($curTab).addClass('active');
		});

		//change Border color in preview and ajax save
		jQuery('.wpfColorObserver .wp-color-result').attr('id', 'wp-color-result-border');

		var observer = new MutationObserver(styleChangedCallback);
		observer.observe(document.getElementById('wp-color-result-border'), {
			attributes: true,
			attributeFilter: ['style'],
		});
		var oldIndex = document.getElementById('wp-color-result-border').style.backgroundColor;

		function styleChangedCallback(mutations) {
			var newIndex = mutations[0].target.style.backgroundColor;
			if (newIndex !== oldIndex) {
				jQuery('.supsystic-filter-loader').not('.spinner').css('color', newIndex);
			}
		}

		jQuery('.chooseLoaderIcon').on('click', function(e){
			e.preventDefault();
			if(typeof(_thisObj.chooseIconPopup) == 'function') {
				_thisObj.chooseIconPopup();
			}
		});

		jQuery('#wpfFiltersEditForm').submit(function (e) {
			e.preventDefault();
			_thisObj.saveFilters();
			var _this = jQuery(this);
			setTimeout(function() {
				_this.sendFormWpf({
					btn: jQuery('#buttonSave')
					, onSuccess: function (res) {
						var currentUrl = window.location.href;
						if (!res.error && res.data.edit_link && currentUrl !== res.data.edit_link) {
							toeRedirect(res.data.edit_link);
						}
					}
				});
			}, 200);

			return false;

		});

		jQuery('body').on('click', '#buttonDelete', function (e) {
			e.preventDefault();
			var deleteForm = confirm("Are you sure you want to delete filter?")
		    if (deleteForm) {
				var id = jQuery('#wpfFiltersEditForm').attr('data-table-id');

				if (id) {
					var data = {
						mod: 'woofilters',
						action: 'deleteByID',
						id: id,
						pl: 'wpf',
						reqType: "ajax"
					};
					jQuery.ajax({
						url: url,
						data: data,
						type: 'POST',
						success: function (res) {
							var redirectUrl = jQuery('#wpfFiltersEditForm').attr('data-href');
							if (!res.error) {
								toeRedirect(redirectUrl);
							}
						}
					});
				}
			} else {
				return false;
			}
			return false;


		});

		// Work with shortcode copy text
		jQuery('#wpfCopyTextCodeExamples').on('change', function (e) {
			var optName = jQuery(this).val();
			switch (optName) {
				case 'shortcode' :
					jQuery('.wpfCopyTextCodeShowBlock').hide();
					jQuery('.wpfCopyTextCodeShowBlock.shortcode').show();
					break;
				case 'phpcode' :
					jQuery('.wpfCopyTextCodeShowBlock').hide();
					jQuery('.wpfCopyTextCodeShowBlock.phpcode').show();
					break;
				case 'shortcode_product' :
					jQuery('.wpfCopyTextCodeShowBlock').hide();
					jQuery('.wpfCopyTextCodeShowBlock.shortcode_product').show();
					break;
				case 'phpcode_product' :
					jQuery('.wpfCopyTextCodeShowBlock').hide();
					jQuery('.wpfCopyTextCodeShowBlock.phpcode_product').show();
					break;
			}
		});

		//-- Work with title --//
		$('#wpfFilterTitleShell').on('click', function(){
			$('#wpfFilterTitleLabel').hide();
			$('#wpfFilterTitleTxt').show();
		});

		$('#wpfFilterTitleTxt').on('focusout', function(){
			var filterTitle = $(this).val();
			$('#wpfFilterTitleLabel').text(filterTitle);
			$('#wpfFilterTitleTxt').hide();
			$('#wpfFilterTitleLabel').show();
			$('#buttonSave').trigger('click');
		});
		//-- Work with title --//

		jQuery('body').on('focus', '.wpfFilter div > input', function() {
			if( typeof jQuery(this).attr('placeholder') !== 'undefined' && jQuery(this).attr('placeholder').length > 0){
				jQuery(this).attr('data-placeholder', jQuery(this).attr('placeholder') );
				jQuery(this).attr('placeholder', '');
			}
		});
		jQuery('body').on('blur', '.wpfFilter div > input', function() {
			jQuery(this).attr('placeholder', jQuery(this).attr('data-placeholder'));
		});

		jQuery('input[name="settings[enable_ajax]"]').on('change', function () {
			jQuery('input[name="settings[show_filtering_button]"]').prop('checked', false);
			jQuery('input[name="settings[show_filtering_button]"]').prop('checked', !jQuery(this).is(':checked'));
		}).trigger('change');

		jQuery('input[name="settings[show_filtering_button]"]').on('change', function () {
			jQuery('input[name="settings[enable_ajax]"]').prop('checked', false);
			jQuery('input[name="settings[enable_ajax]"]').prop('checked', !jQuery(this).is(':checked'));
		}).trigger('change');

        jQuery('input[name="settings[show_clean_button]"]').on('change', function () {
        	var checked = jQuery(this).prop('checked');
        	if (checked) {
                jQuery('input[name="settings[show_clean_button_word]"]').closest('tr').removeClass('wpfHidden');
			} else {
                jQuery('input[name="settings[show_clean_button_word]"]').closest('tr').addClass('wpfHidden');
			}
        }).trigger('change');

        jQuery('input[name="settings[show_clean_block]"]').on('change', function () {
            var checked = jQuery(this).prop('checked');
            if (checked) {
                jQuery('input[name="settings[show_clean_block_word]"]').closest('tr').removeClass('wpfHidden');
            } else {
                jQuery('input[name="settings[show_clean_block_word]"]').closest('tr').addClass('wpfHidden');
            }
        }).trigger('change');

        jQuery('input[name="settings[display_items_in_a_row]"]').on('change', function () {
            var checked = jQuery(this).is(':checked'),
				colsTr = jQuery('input[name="settings[display_cols_in_a_row]"]').closest('tr');
            if (checked) {
                colsTr.removeClass('wpfHidden');
			} else {
                colsTr.addClass('wpfHidden');
			}
        }).trigger('change');
	});

	AdminPage.prototype.eventsFilters = (function () {
		var _this = this.$obj;
		var _noOptionsFilters = this.$noOptionsFilters;
		var wpfGetPreviewInit = false;

		jQuery('.wpfMainWrapper').find('select[multiple]').multiselect({
			columns: 1,
			placeholder: 'Select options'
		});

		jQuery('document').ready(function(){
			jQuery(".chosen-choices").sortable();
		});


		jQuery("body").on('change', "[name='f_show_inputs']", function (e) {
			e.preventDefault();
			if(jQuery(this).prop('checked')) {
				if (jQuery("[name='f_skin_type']").val() == 'default') {
					jQuery(".f_show_inputs_enabled_tooltip").show();
				} else {
					jQuery(".f_show_inputs_enabled_tooltip").hide();
				}
				jQuery(".f_show_inputs_enabled_position").show();
				jQuery(".f_show_inputs_enabled_currency").show();
			} else {
				jQuery(".f_show_inputs_enabled_tooltip").hide();
				jQuery(".f_show_inputs_enabled_position").hide();
				jQuery(".f_show_inputs_enabled_currency").hide();
				jQuery("[name='f_currency_position']").val('before');
				jQuery("[name='f_currency_show_as']").val('symbol');
				jQuery("[name='f_price_tooltip_show_as']").prop("checked",false);
				jQuery("[name='f_price_tooltip_show_as']").attr("checked",false);
			}
		});

		jQuery("body").on('change', "[name='f_skin_type']", function (e) {
			e.preventDefault();
			if(jQuery(this).val() == 'default') {
				if ( jQuery("[name='f_show_inputs']").prop("checked") ) {
					jQuery(".f_show_inputs_enabled_tooltip").show();
				} else {
					jQuery(".f_show_inputs_enabled_tooltip").hide();
				}
			} else {
				jQuery(".f_show_inputs_enabled_tooltip").hide();
				jQuery("[name='f_price_tooltip_show_as']").prop("checked",false);
				jQuery("[name='f_price_tooltip_show_as']").attr("checked",false);
			}
		});

		jQuery("body").on("click keyup", "#wpfFiltersEditForm input", function(){
			if(! jQuery(this).closest(".wpfFiltersBlockPreview").length ) {
			   _this.saveFilters();
			   getPreviewAjax();
			}
		});
		jQuery("body").on("change", "#wpfFiltersEditForm select", function(e) {
			if(! jQuery(this).closest(".wpfFiltersBlockPreview").length ) {
			   _this.saveFilters();
			   getPreviewAjax();
			}
		});

        jQuery("body").on("change", '#wpfFiltersEditForm [name="f_hidden_attributes"]', function(e) {
            if(! jQuery(this).closest(".wpfFiltersBlockPreview").length ) {
                _this.saveFilters();
                getPreviewAjax();
            }
        });

        jQuery('#wpfFiltersEditForm select[name="f_mlist[]"]').on('chosen:updated',function(){
            if(! jQuery(this).closest(".wpfFiltersBlockPreview").length ) {
                _this.saveFilters();
                getPreviewAjax();
            }
        });

        jQuery("body").on("change", '#wpfFiltersEditForm [name="f_hide_taxonomy"]', function(e) {
            var mList = jQuery(this).closest('table').find('select[name="f_mlist[]"]')
			,	parentCats = mList.data('parents');

            mList.find('option').show();
            if (jQuery(this).is(':checked')){
                mList.find('option').each(function(){
                	var optVal = jQuery(this).val();
                	if(toeInArray(optVal, parentCats) == -1){
                        jQuery(this).hide();
					}
				});
			}
            mList.trigger("chosen:updated");
        });

		var wpfWaitResponse = false;
		function getPreviewAjax() {
			if (wpfWaitResponse == false || wpfWaitResponse == undefined) {
				wpfWaitResponse = true;
				jQuery('#wpfFiltersEditForm').sendFormWpf({
					data: jQuery('#wpfFiltersEditForm').serializeAnythingWpf()
				,	appendData: {mod: 'woofilters', action: 'drawFilterAjax'}
				,	onSuccess: function(res) {
						if(!res.error) {
							jQuery(".wpfFiltersBlockPreview").html(res.html);
							jQuery(".wpfFiltersBlockPreview").find("input").attr("name",'');
							jQuery(".wpfFiltersBlockPreview").find("select").attr("name",'');
							jQuery(".wpfFiltersBlockPreview").find("input[type=number]").attr("type",'');
							jQuery(".wpfFiltersBlockPreview").find("select").attr("type",'');
                            jQuery(".wpfFiltersBlockPreview").find('.wpfFilterWrapper').css({
                                visibility: 'visible'
                            });
						}
						wpfWaitResponse = false;
					},
				});
			}
		}

        function wpfAddFilter(id, text){
            var optionsTemplate = jQuery('.wpfTemplates .wpfOptionsTemplate table[data-filter="'+id+'"]').clone();
            var blockTemplate = jQuery('.wpfTemplates .wpfFiltersBlockTemplate')
                .clone()
                .removeClass('wpfFiltersBlockTemplate')
                .attr('data-filter', id)
                .attr('data-title', optionsTemplate.attr('data-title'));
            if(_noOptionsFilters.includes(id)){
                blockTemplate.find('.wpfToggle').css({'visibility':'hidden'});
            }
            blockTemplate.find('.wpfOptions').html(optionsTemplate);
            blockTemplate.find('.wpfFilterTitle').text(text);

            jQuery('.wpfFiltersBlock').append(blockTemplate);
            _this.initAttributeFilter(blockTemplate);

            //refresh data in ['settings']['filters']
            jQuery(document.body).trigger('changeTooltips');
            jQuery('#wpfFiltersEditForm select[name="f_mlist[]"]').chosen({ width:"95%" });
        }

		//add new filter
		jQuery('.wpfChooseBlock button').on('click', function(e){
			e.preventDefault();
			var text = jQuery(this).text();
			var id = jQuery(this).attr('id');

			//check if filter exist
			if( jQuery('.wpfFilter[data-filter="'+id+'"]').length ){
				//check if allows multiple filters
				if( _this.$allowMultipleFilters.includes(id) ){
					if (id === 'wpfAttribute') {
						text = 'Attribute';
					}
					//add more filter for this type
					wpfAddFilter(id, text);
					//check if current filter already exist if yes make visible delete icon
					if(jQuery('.wpfFiltersBlock .wpfFilter[data-filter="' + id + '"]').length > 1){
						jQuery('.wpfFiltersBlock .wpfFilter[data-filter="' + id + '"]').find('.wpfDelete').removeClass('wpfVisibilityHidden');
					}
				}
			}else{
				//add filter
				wpfAddFilter(id, text);
			}
		});

		//remove existing filter
		jQuery('.wpfFiltersBlock').on('click', '.wpfFilter a.wpfDelete', function(e){
			e.preventDefault();
			var _this = jQuery(this);
			var id = _this.attr('data-filter');

			jQuery(this).closest('.wpfFilter').remove();
			if(jQuery('.wpfFiltersBlock .wpfFilter[data-filter="' + id + '"]').length < 1){
				jQuery('.wpfFiltersBlock .wpfFilter[data-filter="' + id + '"]').find('.wpfDelete').addClass('wpfVisibilityHidden');
			}
			//refresh data in ['settings']['filters']
		});

		//show / hide filter options
		jQuery('.wpfFiltersBlock').on('click', '.wpfFilter a.wpfToggle', function(e){
			e.preventDefault();
			var el = jQuery(this);
			var i = el.find('i');
			var span = el.find('.wpfTextSt_1');

			if (i.hasClass('fa-chevron-down')){
				i.removeClass('fa-chevron-down').addClass('fa-chevron-up');
				span.text(span.attr('data-title-open'));
				el.closest('.wpfFilter').find('.wpfOptions').removeClass('wpfHidden');
			}else{
				i.removeClass('fa-chevron-up').addClass('fa-chevron-down');
				span.text(span.attr('data-title-close'));
				el.closest('.wpfFilter').find('.wpfOptions').addClass('wpfHidden');
			}

			//refresh data in ['settings']['filters']
		});

		//make properties sortable
		jQuery(".wpfFiltersBlock").sortable({
			cursor: "move",
			axis: "y",
			handle: ".wpfMove",
			stop: function () {
				//jQuery('#buttonSave').trigger('click');
				_this.saveFilters();
				_this.saveFilters();
				getPreviewAjax();
			},
		});

        jQuery('[href="#row-tab-filters"]').on('click tap', function(){
            getPreviewAjax();
        });

		//after load page display filters tab
		displayFiltersTab();

		function displayFiltersTab(){
			jQuery('.wpfFiltersBlock').html('');
			var defFilters = [
					{'id':'wpfPrice'},
					{'id':'wpfPriceRange'},
					{'id':'wpfSortBy'},
					{'id':'wpfCategory'},
					{'id':'wpfTags'},
					{'id':'wpfAuthor'},
					{'id':'wpfFeatured'},
					{'id':'wpfOnSale'},
					{'id':'wpfInStock'},
					{'id':'wpfRating'},
					{'id':'wpfSearchText'},
					{'id':'wpfBrand'},
				];
			try{
				var filters = JSON.parse(jQuery('input[name="settings[filters][order]"]').val()),
					cntFilters = filters.length;
				defFilters.forEach(function(value){
					var found = false,
						id = value.id;
					for(var i = 0; i < cntFilters; i++) {
						if(filters[i].id == id) {
							found = true;
							break;
						}
					}
					if(!found) {
						filters.push(Object.assign({}, value));
					}
				});
			}catch(e){
				var filters = defFilters;
			}

			filters.forEach(function (value) {
				var id = value.id,
					template = jQuery('.wpfTemplates .wpfOptionsTemplate table[data-filter="'+id+'"]');
				if(template.length == 0) return true;

				var settings = value.settings,
					optionsTemplate = template.clone(),
					text = optionsTemplate.find('input[name=f_name]').val(),
					isDisabled = (optionsTemplate.attr('data-disabled') == '1');

				if( typeof settings !== 'undefined' ) {
					optionsTemplate.find('input, select').map(function (index, elm) {
						var name = elm.name;
						if (elm.type === 'checkbox') {

							if (elm.name === 'f_options[]') {
								jQuery(elm).prop("checked", false);
								if (settings[name]) {
									var checkedArr = settings[name].split(",");
									if (checkedArr.includes(elm.value)) {
										jQuery(elm).prop("checked", true);
									}
								}
							} else {
								jQuery(elm).prop("checked", settings[name]);
							}

						} else if (elm.type === 'select-multiple') {
							if (_this.$multiSelectFields.includes(elm.name)) {
								if (settings[name]) {
									var selectedArr = settings[name].split(",");
									jQuery.each(selectedArr, function (i, e) {
										var option = jQuery(elm).find("option[value='" + e + "']");
										option.remove();
										jQuery(elm).append(option);
										jQuery(elm).find("option[value='" + e + "']").prop("selected", true);
									});
								}
							}
						} else {
							if(typeof settings[name] !== 'undefined') {
								elm.value = settings[name];
							}
						}
					});
				}
				var blockTemplate = jQuery('.wpfTemplates .wpfFiltersBlockTemplate')
					.clone()
					.removeClass('wpfFiltersBlockTemplate')
					.attr('data-filter', id)
					.attr('data-title', text),
					title = text;
				blockTemplate.find('.wpfOptions').html(optionsTemplate);
				if( id === 'wpfAttribute' ){
					title = blockTemplate.find('select[name="f_list"] option:selected').text();
					text = text + ' - ' + title;
					if (blockTemplate.find('select[name="f_list"]').val() > 0) {
                        blockTemplate.find('select[name="f_mlist[]"]').closest('tr').removeClass('wpfHidden');
                        fListChanged( blockTemplate.find('select[name="f_list"]') );
                    }
				}
				if(_noOptionsFilters.includes(id)){
					blockTemplate.find('.wpfToggle').css({'visibility':'hidden'});
				}
				blockTemplate.find('.wpfFilterTitle').text(text);
				if( typeof settings !== 'undefined' ){
					blockTemplate.find('.wpfFilterFrontDescOpt input').val(settings['f_description']);
					if(settings['f_enable'] == true){
						blockTemplate.find('.wpfEnable input').prop( "checked", true );
					}
					if(typeof settings['f_title'] !== 'undefined' && settings['f_title'].length > 0) {
						title = settings['f_title'];
					}
				}
				blockTemplate.find('.wpfFilterFrontTitleOpt input').val(title);
				if(isDisabled) {
					blockTemplate.find('input').prop('disabled', true);
				}
				jQuery('.wpfFiltersBlock').append(blockTemplate);

				if(jQuery('.wpfFiltersBlock .wpfFilter[data-filter="' + id + '"]').length > 1){
					jQuery('.wpfFiltersBlock .wpfFilter[data-filter="' + id + '"]').find('.wpfDelete').removeClass('wpfVisibilityHidden');
				}
				if(id === 'wpfAttribute'){
					setTimeout(function() {
                        _this.initAttributeFilter(blockTemplate, settings);
					}, 200);
				}
				if (blockTemplate.find('[name="f_show_images_custom"]').length) {
                    setTimeout(function() {
                        if(typeof(_this.initCustomImages) == 'function') {
                            _this.initCustomImages(blockTemplate, settings);
                        }
                    }, 200);
				}
			});
			jQuery('#wpfFiltersEditForm select[name="f_mlist[]"]').chosen({ width:"95%" });
			jQuery(document.body).trigger('changeTooltips');

			//filter Price - options
			var filterPrice = jQuery('.wpfFiltersBlock .wpfFilter[data-filter="wpfPrice"]');
			if(filterPrice.length) {
				var defaultSlider = filterPrice.find('#wpfSliderRange'),
					minValue = 200,
					maxValue = 600,
					minSelector = filterPrice.find('#wpfMinPrice').val(minValue),
					maxSelector = filterPrice.find('#wpfMaxPrice').val(maxValue);
				defaultSlider.slider({
					range: true,
					orientation: "horizontal",
					min: 0,
					max: 1000,
					values: [minValue, maxValue],
					step: 1,
					slide: function (event, ui) {
						minSelector.val(ui.values[0]);
						maxSelector.val(ui.values[1]);
					}
				});
				filterPrice.find('input[name="f_show_inputs"]').on('change', function(e){
					e.preventDefault();
					if($(this).prop('checked')) {
						filterPrice.find('.wpfPriceInputs').show();
					} else {
						filterPrice.find('.wpfPriceInputs').hide();
					}
				}).trigger('change');
				minSelector.on('change', function(e){
					e.preventDefault();
					defaultSlider.slider('values', 0, $(this).val());
				});
				maxSelector.on('change', function(e){
					e.preventDefault();
					defaultSlider.slider('values', 1, $(this).val());
				});
				filterPrice.find('select[name="f_skin_type"].wpfWithProAd').on('change', function(e){
					e.preventDefault();
					filterPrice.find('.wpfPriceSkinPro').addClass('wpfHidden');
					filterPrice.find('.wpfPriceSkinPro[data-type="'+$(this).val()+'"]').removeClass('wpfHidden');
				}).trigger('change');
			}


			//filter Price Range - options
			var filterPriceRange = jQuery('.wpfFiltersBlock .wpfFilter[data-filter="wpfPriceRange"]');
			if(filterPriceRange.length) {
				filterPriceRange.find('select[name="f_frontend_type"]').on('change', function(e){
					e.preventDefault();
					if($(this).val() == 'dropdown') {
						filterPriceRange.find('input[name="f_dropdown_first_option_text"]').closest('tr').css('display', 'table-row');
					} else {
						filterPriceRange.find('input[name="f_dropdown_first_option_text"]').closest('tr').hide();
					}
				}).trigger('change');
			}

			//filter Category - options
			var filterCategory = jQuery('.wpfFiltersBlock .wpfFilter[data-filter="wpfCategory"]');
			if(filterCategory.length) {
				filterCategory.find('select[name="f_frontend_type"]').on('change', function(e){
					e.preventDefault();
					var frontendType = $(this).val();
					filterCategory.find('input[name="f_dropdown_first_option_text"], input[name="f_show_search_input"], input[name^="f_multi"], select[name^="f_multi"]').closest('tr').hide();
					if(frontendType == 'dropdown') {
						filterCategory.find('input[name="f_dropdown_first_option_text"]').closest('tr').css('display', 'table-row');
					} else if(frontendType == 'list'){
						filterCategory.find('input[name="f_show_search_input"]').closest('tr').css('display', 'table-row');
					} else {
						filterCategory.find('input[name^="f_'+frontendType+'"], select[name^="f_'+frontendType+'"]').closest('tr').css('display', 'table-row');
					}
				}).trigger('change');

                filterCategory.find('input[name="f_show_hierarchical"]').on('change', function(e){
                    e.preventDefault();
                    var parentFilterCat = jQuery(this).closest('.wpfFilter[data-filter="wpfCategory"]');
                    if (jQuery(this).prop('checked')) {
                        parentFilterCat.find('[data-trigger="f_show_hierarchical"]').removeClass('wpfHidden');
                    } else {
                        parentFilterCat.find('[data-trigger="f_show_hierarchical"]').addClass('wpfHidden');
					}
                }).trigger('change');
			}

			//filter Tags - options
			var filterTags = jQuery('.wpfFiltersBlock .wpfFilter[data-filter="wpfTags"]');
			if(filterTags.length) {
				filterTags.find('select[name="f_frontend_type"]').on('change', function(e){
					e.preventDefault();
					filterTags.find('input[name="f_dropdown_first_option_text"], input[name="f_show_search_input"]').closest('tr').hide();
					var frontendType = $(this).val();
					if(frontendType == 'dropdown') {
						filterTags.find('input[name="f_dropdown_first_option_text"]').closest('tr').css('display', 'table-row');
					} else if(frontendType == 'list'){
						filterTags.find('input[name="f_show_search_input"]').closest('tr').css('display', 'table-row');
					}
				}).trigger('change');
			}

			//filter Author - options
			var filterAuthor = jQuery('.wpfFiltersBlock .wpfFilter[data-filter="wpfAuthor"]');
			if(filterAuthor.length) {
				filterAuthor.find('select[name="f_frontend_type"]').on('change', function(e){
					e.preventDefault();
					filterAuthor.find('input[name="f_dropdown_first_option_text"], input[name="f_show_search_input"]').closest('tr').hide();
					var frontendType = $(this).val();
					if(frontendType == 'dropdown') {
						filterAuthor.find('input[name="f_dropdown_first_option_text"]').closest('tr').css('display', 'table-row');
					} else if(frontendType == 'list'){
						filterAuthor.find('input[name="f_show_search_input"]').closest('tr').css('display', 'table-row');
					}
				}).trigger('change');
			}

			//filter Rating - options
			var filterRating = jQuery('.wpfFiltersBlock .wpfFilter[data-filter="wpfRating"]');
			if(filterRating.length) {
				filterRating.find('select[name="f_frontend_type"]').on('change', function(e){
					e.preventDefault();
					var el = $(this),
						value = el.val();

					filterRating.find('.wpfTypeSwitchable').addClass('wpfHidden');
					filterRating.find('.wpfTypeSwitchable[data-type~="'+value+'"]').removeClass('wpfHidden');
					if(el.hasClass('wpfWithProAd')) {
						filterRating.find('.wpfRatingTypePro').addClass('wpfHidden');
						filterRating.find('.wpfRatingTypePro[data-type="'+value+'"]').removeClass('wpfHidden');
					}
				}).trigger('change');
			}
		}

		jQuery("body").on('change', 'select[name="f_list"]', function (e) {
			e.preventDefault();
            fListChanged(jQuery(this));
		});

		function fListChanged(_this){
            var changedName = _this.val() == 0 ? '' : ' - ' + _this.find('option:selected').text(),
                startName = _this.closest('.wpfFilter').attr('data-title'),
                fullTitle = startName + changedName;
            _this.closest('.wpfFilter').find('.wpfFilterTitle').text(fullTitle);

            var wpfOptions = _this.closest('.wpfOptions'),
                attr_terms = wpfOptions.find('[name="f_mlist[]"]'),
                attr_term_options = attr_terms.find('option'),
                terms_options = attr_terms.data('attr-terms');
            if (_this.val() > 0 && typeof terms_options !== 'undefined') {
                attr_terms.closest('tr').removeClass('wpfHidden');
                attr_term_options.each(function(){
                    var option = jQuery(this);
                    if ((_this.val() in terms_options) && terms_options[_this.val()].includes(parseInt(option.attr('value')))) {
                        option.show();
                    } else {
                        option.hide();
                    }
                });
                attr_terms.trigger("chosen:updated");
                attr_terms.trigger('change');
            } else {
                attr_terms.closest('tr').addClass('wpfHidden');
                attr_terms.val('').trigger("chosen:updated");
                attr_terms.trigger('change');
                attr_term_options.show();
            }
		}

		jQuery('.wpfFiltersBlock').on('change', '.wpfAutomaticOrByHand input', function(){
			var _this = jQuery(this);
			var id = _this.closest('.wpfAutomaticOrByHand').attr('id');
			var checked = _this.prop('checked');

			jQuery('.wpfAutomaticOrByHand').not('#'+id).find('input').prop('checked', !checked );
		});

		//Sort by filter. Disable unchecking last two checkbox.
		/*jQuery('body').on('click', '.wpfFilter[data-filter="wpfSortBy"] input[name="f_options[]"]', function (e) {
			var countCheckedCheckbox = jQuery('.wpfFilter[data-filter="wpfSortBy"]').find('input[name="f_options[]"]:checked').length;
			if(countCheckedCheckbox < 2){
				e.preventDefault();
				return false;
			}
		});*/

		//Sort by filter. Disable unchecking last two checkbox.
		/*jQuery('body').on('click', '.wpfFilter[data-filter="wpfInStock"] input[name="f_options[]"]', function (e) {
			var countCheckedCheckbox = jQuery('.wpfFilter[data-filter="wpfInStock"]').find('input[name="f_options[]"]:checked').length;
			if(countCheckedCheckbox < 2){
				e.preventDefault();
				return false;
			}
		});*/

		// duplicate cat/tags filter
        jQuery('body').on('click', '.wpfDuplicateButton', function (e) {
        	var duplicateFilter = jQuery(this).closest('.wpfFilter');
        	var id = duplicateFilter.data('filter');
        	var text = 'Filter '+ (jQuery('.wpfFilter').length + 1);
            wpfAddFilter(id, text);
            setTimeout(function(){
            	goTo(jQuery('.wpfFilter:last-child'));
			},300);
            displayFiltersTab();

            return false;
        });

        // change stock names
        jQuery('.wpfFilter .wpfStatusNamesTrigger').on('change', function(e) {
            e.preventDefault();
            if (jQuery(this).is(':checked')) {
                jQuery('.wpfInStockInput').closest('tr').removeClass('wpfHidden');
            } else {
                jQuery('.wpfInStockInput').closest('tr').addClass('wpfHidden');
            }
        }).trigger('change');
	});

	AdminPage.prototype.initAttributeFilter = (function(filter, settings) {
		var _thisObj = this.$obj;
		if(typeof(_thisObj.initAttributeColorFilter) == 'function') {
			_thisObj.initAttributeColorFilter(filter, settings);
		}

		filter.find('select[name="f_frontend_type"]').on('change', function(e){
			e.preventDefault();
			var el = $(this),
				value = el.val();

			filter.find('.wpfTypeSwitchable').addClass('wpfHidden');
			filter.find('.wpfTypeSwitchable[data-type~="'+value+'"]').removeClass('wpfHidden');
			if(el.hasClass('wpfWithProAd')) {
				filter.find('.wpfAttributesTypePro').addClass('wpfHidden');
				filter.find('.wpfAttributesTypePro[data-type="'+value+'"]').removeClass('wpfHidden');
			}
		}).trigger('change');
	});

	AdminPage.prototype.saveFilters = (function () {
		var _this = this.$obj;
		var filtersArr = [];
		var i = 0;
		if( jQuery('.wpfFiltersBlock .wpfFilter').length <=0 ){
			return;
		}
		jQuery('.wpfFilter').not('.wpfFiltersBlockTemplate').each(function () {
			var valueToPush = {},
				el = jQuery(this),
				id = 'wpfFilter'+i,
				items = {},
				title = el.find('input[name="f_title"]');
			el.attr('id', id);

			if(title.val() == '') {
				title.val(el.find('.wpfFilterTitle').text());
			}

			jQuery("#" + id +" input, #" + id +" select").map(function(index, elm) {

				if(elm.type === 'checkbox'){
					//for multi checkbox
					if(elm.name === 'f_options[]'){
						if(elm.checked){
							if(typeof items[elm.name] !== 'undefined'){
								var temp = items[elm.name];
								temp = temp + ',' + jQuery(elm).val();
								items[elm.name] = temp;
							}else{
								items[elm.name] = jQuery(elm).val();
							}
						}
					}else{
						items[elm.name] = elm.checked;
					}
				}else if(elm.type === 'select-multiple'){
					if( _this.$multiSelectFields.includes(elm.name) ){
						//add more filter for this type
						var arrayValues = jQuery(elm).getSelectionOrder();
						//arrayValues = arrayValues.reverse();
						//console.log(arrayValues);
						if(arrayValues){
							items[elm.name] = arrayValues.toString();
						}
					}
				}else{
					items[elm.name] = jQuery(elm).val();
				}
			});
			valueToPush['id'] = el.attr('data-filter');
			valueToPush['settings'] = items;
			filtersArr.push(valueToPush);
			i++;
		});

		var filtersJson = JSON.stringify(filtersArr);
		jQuery('input[name="settings[filters][order]"]').val(filtersJson);

	});

	jQuery(document).ready(function () {
		window.wpfAdminPage = new AdminPage();
		window.wpfAdminPage.init();
	});

}(window.jQuery, window.supsystic));
