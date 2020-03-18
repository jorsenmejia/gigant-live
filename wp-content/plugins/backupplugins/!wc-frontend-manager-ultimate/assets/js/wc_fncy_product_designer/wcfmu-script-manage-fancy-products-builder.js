jQuery(document).ready(function($) {
		
	var $manageLayersPanel = $('#fpd-manage-elements'),
		$elementLists = $manageLayersPanel.children('#fpd-elements-list'),
		$actionsToolbar = $('#fpd-actions-toolbar'),
		$fpdContainer =  $('#fpd-canvas-container'),
		$fpd = $fpdContainer.children('#fpd-preview-wrapper'),
		$sidebar = $('#fpd-element-props-sidebar'),
		$sidebarForm =  $sidebar.children('form'),
		fancyProductDesigner,
		fpdActions,
		viewInstance = null,
		stage = null,
		mediaUploader = null,
		$currentListItem = null,
		changesAreSaved = true,
		productCreated = false,
		initialProduct = [],
		currentViewOptions = null,
		updatingFormFields = false,
		pluginsOptions = {
			stageWidth: Number($fpd.data('stagewidth')),
			stageHeight: Number($fpd.data('stageheight')),
			responsive: false,
	    	langJSON: false,
	    	templatesDirectory: fpd_product_builder_opts.templates_directory,
	    	mainBarModules: [],
	    	editorMode: true,
	    	keyboardControl: false,
	    	deselectActiveOnOutside: false,
	    	uploadZonesTopped: parseInt(fpd_product_builder_opts.uploadZonesTopped),
	    	elementParameters: {
		    	originX: fpd_product_builder_opts.originX,
				originY: fpd_product_builder_opts.originY,
	    	},
	    	textParameters: {
		    	fontFamily: fpd_product_builder_opts.defaultFont,
				fontSize: 18
	    	},
	    	fonts: JSON.parse(fpd_product_builder_opts.enabled_fonts),
	    	toolbarTheme: 'dark'

		},
		pbElementProps = [
			'adminLocked'
		];

	//dropdown handler for choicing a view
	$('#fpd-view-switcher').change(function() {
		location.href = fpd_product_builder_opts.builder_url+this.value;
	});

	//edit product options
	var $modalEditViewOptions = $('#fpd-modal-edit-view-options');
	$('#fpd-edit-view-options').click(function(evt) {

		evt.preventDefault();

		openModal($modalEditViewOptions);
		toggleModalLoader($modalEditViewOptions, true);

		$.ajax({
			url: fpd_admin_opts.adminAjaxUrl,
			data: {
				action: 'fpd_loadviewoptions',
				_ajax_nonce: fpd_admin_opts.ajaxNonce,
				view_id: $fpd.data('viewid')
			},
			type: 'post',
			dataType: 'json',
			success: function(data) {

				toggleModalLoader($modalEditViewOptions, false);
				if(data && data.options) {

					currentViewOptions = data.options;
					if(!_.isObject(currentViewOptions)) {
						currentViewOptions = JSON.parse(currentViewOptions);
					}

					fpdFillFormWithObject(currentViewOptions, $modalEditViewOptions);

				}

			}
		});

	});

	$modalEditViewOptions.on('change', '[name="output_format"]', function() {

		$modalEditViewOptions.find('[name="output_width"], [name="output_height"]')
		.parents('tr').toggle(this.value === 'man');

		if(this.value == '') {
			$modalEditViewOptions.find('[name="output_width"]').val('')
			$modalEditViewOptions.find('[name="output_height"]').val('');
		}
		else if(this.value !== 'man') {

			var size = this.value.split('x');
			$modalEditViewOptions.find('[name="output_width"]').val(size[0])
			$modalEditViewOptions.find('[name="output_height"]').val(size[1]);

		}

	}).find('[name="output_format"]').change();

	$modalEditViewOptions.on('click', '#auto_calc_canvas_size', function(evt) {

		evt.preventDefault();

		var pageWidth = Number($modalEditViewOptions.find('[name="output_width"]').val()),
			pageHeight = Number($modalEditViewOptions.find('[name="output_height"]').val());

		if(pageWidth !== 0 && pageHeight !== 0) {

			var canvasWidth,
				canvasHeight,
				aspectRatio;

			if(pageWidth > pageHeight) {

				aspectRatio = pageHeight / pageWidth;
				canvasWidth = 1000;
				canvasHeight = Math.round(canvasWidth * aspectRatio);

			}
			else {

				aspectRatio = pageWidth / pageHeight;
				canvasHeight = 600;
				canvasWidth = Math.round(canvasHeight * aspectRatio);

			}

			$modalEditViewOptions.find('[name="stage_width"]').val(canvasWidth);
			$modalEditViewOptions.find('[name="stage_height"]').val(canvasHeight);

		}
		else {
			fpdMessage(fpd_product_builder_opts.page_size_missing, 'error');
		}

	});

	$modalEditViewOptions.find('.fpd-save-admin-modal').click(function() {

		var $formFields = $modalEditViewOptions.find('input, select'),
			newValues = fpdSerializeObject($formFields);

		newValues = _.isEmpty(newValues) ? '' : newValues;

		currentViewOptions = currentViewOptions === null || currentViewOptions.length === 0 ? {} : currentViewOptions;
		if(currentViewOptions.mask) {
			newValues.mask = currentViewOptions.mask;
		}

		//save old printing box
		if(currentViewOptions.printing_box) {
			newValues.printing_box = currentViewOptions.printing_box;
		}

		//check if new width and height is set for output
		if(currentViewOptions.output_width !== newValues.output_width || currentViewOptions.output_height !== newValues.output_height) {

			if(newValues.output_width !== undefined && newValues.output_height !== undefined) {

				var pageWidth = parseInt(newValues.output_width),
					pageHeight = parseInt(newValues.output_height),
					canvasWidth = (72 * pageWidth) / 25.4,
					canvasHeight = (72 * pageHeight) / 25.4;

				_updatePrintingBox(canvasWidth, canvasHeight);

			}
			else { //remove printing box if no output width/height is set
				_updatePrintingBox();

			}

			newValues.printing_box = viewInstance.options.printingBox;

		}

		toggleModalLoader($modalEditViewOptions, true);

		$.ajax({
			url: fpd_admin_opts.adminAjaxUrl,
			data: {
				action: 'fpd_editview',
				_ajax_nonce: fpd_admin_opts.ajaxNonce,
				id: $fpd.data('viewid'),
				options: JSON.stringify(newValues)
			},
			type: 'post',
			dataType: 'json',
			success: function(data) {

				toggleModalLoader($modalEditViewOptions, false);
				closeModal($modalEditViewOptions);

				if(!_.isUndefined(data) && !_.isUndefined(data.columns)) {

					if(!_.isUndefined(data.columns.options)) {

						var stageWidth = fpdGlobalProductBuilderOpts.stageWidthTemp,
							stageHeight = fpdGlobalProductBuilderOpts.stageHeightTemp;

						if(newValues.hasOwnProperty('stage_width')) {
							stageWidth = parseInt(newValues.stage_width);
						}

						if(newValues.hasOwnProperty('stage_height')) {
							stageHeight = parseInt(newValues.stage_height);
						}

						$('#fpd-stage-width-label').text(stageWidth);
						$('#fpd-stage-height-label').text(stageHeight);
						fancyProductDesigner.setDimensions(stageWidth, stageHeight);

						fpdMessage(data.message, 'success');
					}
					else {
						fpdMessage(fpd_admin_opts.tryAgain, 'error');
					}

				}

			}
		});

	});

	//when select a list item, select the corresponding element in stage
	$elementLists.on('click', '.fpd-layer-item', function(evt) {
		stage.setActiveObject(viewInstance.getElementByID(this.id));
	});

	//make elements list sortable
	var sortDir = 0;
	$elementLists.sortable({
		placeholder: 'fpd-sortable-placeholder',
		helper : 'clone',
		start: function(evt, ui) {
			sortDir = ui.originalPosition.left;
		},
		change: function(evt, ui) {

			var targetElement = viewInstance.getElementByID(ui.item.attr('id')),
				relatedItem;

			if(ui.position.left > sortDir) { //down
				relatedItem = ui.placeholder.prevAll(".fpd-layer-item:not(.ui-sortable-helper)").first();
			}
			else { //up
				relatedItem = ui.placeholder.nextAll(".fpd-layer-item:not(.ui-sortable-helper)").first();
			}

			var fabricElem = viewInstance.getElementByID(relatedItem.attr('id')),
				index = viewInstance.getZIndex(fabricElem);

			targetElement.moveTo(index);

			sortDir = ui.position.left;
			changesAreSaved = false;

		}
	});

	$sidebarForm.find('[name="_color"]').tagsManager({
		delimiters: [13],
		tagsContainer: '#fpd-color-tags'
	})
	.on('tm:pushed', function(e, tag) {

		var $this = $(this);

		if(tag.search(',') > -1) {

			$this.tagsManager('popTag');
			var colorsArray = tag.split(',');
			for(var i=0; i < colorsArray.length; ++i) {
				$this.tagsManager('pushTag', colorsArray[i]);
			}

		}
		else {

			if(FPDUtil.isHex(tag)) {

				//remove uppercase tab and add tag in lowercase
				if(tag.toLowerCase() != tag) {
					$this.tagsManager('popTag');
					$this.tagsManager('pushTag', tag.toLowerCase());
				}

				$('.tm-tag:last').css('background-color', tag);
			}
			else {
				$this.tagsManager('popTag');
			}

		}

    });

	//add color via btn
	$('#fpd-add-color').click(function(evt) {

		evt.preventDefault();

		var evt = jQuery.Event("keydown");
		evt.which = 13;
		$sidebarForm.find('[name="_color"]').trigger(evt);

	});

	//empty colors
	$('#fpd-empty-colors').click(function(evt) {

		evt.preventDefault();

		$sidebarForm.find('[name="_color"]').tagsManager('empty');

	});

	//change element text when related input text field is changed
	$elementLists.on('keyup', 'input[type="text"]', function(evt) {

		var $this = $(this),
			activeObj = stage.getActiveObject();

		//when list item is not selected
		if(activeObj === undefined) {
			$this.parents('li:first').click();
			activeObj = stage.getActiveObject()
		}

		activeObj.set('title', this.value)

	});

	//change element text when related input text field is changed
	$elementLists.on('keyup', 'textarea', function(evt) {

		var $this = $(this),
			activeObj = stage.getActiveObject();

		//when list item is not selected
		if(activeObj === undefined) {
			$this.parents('li:first').click();
			activeObj = stage.getActiveObject()
		}

		if(FPDUtil.getType(activeObj.type) == 'text') {
			activeObj.set('text', this.value).setCoords();
			stage.renderAll().calcOffset();
		}

	});

	//change image source handler
	$elementLists.on('click', '.fpd-change-image', function(evt) {

		evt.preventDefault();

		var $this = $(this),
			$listItem = $this.parents('.fpd-layer-item:first'),
			element = viewInstance.getElementByID($listItem.attr('id'));

        mediaUploader = wp.media({
            title: fpd_product_builder_opts.chooseElementImageTitle,
            button: {
                text: fpd_product_builder_opts.set
            },
            multiple: false
        });

		mediaUploader.on('select', function() {

			fabric.util.loadImage(mediaUploader.state().get('selection').toJSON()[0].url, function(img) {

				$listItem.find('img').attr('src', img.src);
				element.source = img.src;
				element.setElement(img);
				element.setCoords();
				stage.renderAll();

			});

			mediaUploader = null;

        });

        mediaUploader.open();

	});

	//element lock handler
	$elementLists.on('click', '.fpd-lock-element', function(evt) {

		evt.preventDefault();
		evt.stopPropagation();

		var $this = $(this),
			element = viewInstance.getElementByID($this.parents('.fpd-layer-item:first').attr('id'));

		stage.setActiveObject(element);

		//lock
		if($this.children('i').hasClass('fpd-admin-icon-lock-open')) {
			$this.children('i').removeClass('fpd-admin-icon-lock-open').addClass('fpd-admin-icon-lock');
			element.set('evented', false);
			element.adminLocked = true;
			fancyProductDesigner.deselectElement();
		}
		//unlock
		else {
			$this.children('i').removeClass('fpd-admin-icon-lock').addClass('fpd-admin-icon-lock-open');
			element.set('evented', true);
			element.adminLocked = false;
		}

	});

	//remove element
	$elementLists.on('click', '.fpd-trash-element', function(evt) {

		evt.preventDefault();
		evt.stopPropagation();

		var $this = $(this);

		radykalConfirm({ msg: fpd_product_builder_opts.removeElement}, function(c) {

			if(c) {

				viewInstance.removeElement(viewInstance.getElementByID($this.parents('.fpd-layer-item:first').attr('id')));

			}

		});

	});

	$('#fpd-save-layers').click(function(evt) {

		evt.preventDefault();

		if(productCreated) {

			$manageLayersPanel.children('.fpd-ui-blocker').show();
			fancyProductDesigner.toggleSpinner(true, fpd_product_builder_opts.loading);

			changesAreSaved = true;

			var imageProps = fpd_product_builder_opts.general_props.concat(fpd_product_builder_opts.image_props),
				textProps = fpd_product_builder_opts.general_props.concat(fpd_product_builder_opts.text_props),
				elementsJSON = [];

			fancyProductDesigner.getElements().forEach(function(elem) {

				var elemJSON = {
						title: elem.title,
						source: elem.hasOwnProperty('text') ? elem.text : elem.source,
						type: FPDUtil.getType(elem.type)
					},
					relevantProps = elemJSON.type == 'text' ? textProps : imageProps,
					propsJSON = {};

				relevantProps = relevantProps.concat(pbElementProps);

				relevantProps.forEach(function(prop) {

					if(elem[prop] !== undefined && elem[prop] !== '') {
						propsJSON[prop] = elem[prop];
					}

				});

				if(elem.type !== FPDPathGroupName) {
					delete propsJSON['svgFill'];
				}

				delete propsJSON['strokeColors'];

				elemJSON.parameters = propsJSON;

				elementsJSON.push(elemJSON);

			});

			$.ajax({
				url: fpd_admin_opts.adminAjaxUrl,
				data: {
					action: 'fpd_saveelements',
					_ajax_nonce: fpd_admin_opts.ajaxNonce,
					view_id: $fpd.data('viewid'),
					elements: JSON.stringify(elementsJSON)
				},
				type: 'post',
				dataType: 'json',
				success: function(data) {

					$manageLayersPanel.children('.fpd-ui-blocker').hide();
					fancyProductDesigner.toggleSpinner(false);

					if(!data || data.error) {
						fpdMessage(data ? data.message : 'Server AJAX error', 'error');
					}
					else {
						fpdMessage(data.message, 'success');
					}


				}
			});

		}

	});

	//radio butons handler
	$('.fpd-radio-buttons').siblings('span').click(function(evt) {

		evt.preventDefault();

		var $this = $(this);

		$this.addClass('fpd-active').siblings('span').removeClass('fpd-active');
		$this.siblings('.fpd-radio-buttons').val($this.data('value')).change();

	});

	//toggle buttons handler
	$('.fpd-toggle-button').prevAll('.button').click(function(evt) {

		evt.preventDefault();

		var $this = $(this);
			$checkbox = $this.toggleClass('active').nextAll('.fpd-toggle-button').filter('[value="'+$this.data('value')+'"]');

		$checkbox .prop('checked', $this.hasClass('active')).change();

	});

	//undo/redo
	$actionsToolbar
	.on('click', '#fpd-undo, #fpd-redo', function(evt) {

		evt.preventDefault();

		if($(this).attr('id') == 'fpd-undo') {
			fancyProductDesigner.currentViewInstance.undo();
		}
		else {
			fancyProductDesigner.currentViewInstance.redo();
		}

	})
	.on('click', '#fpd-ruler', function(evt) {

		evt.preventDefault();
		fpdActions.doAction($(this));

	})
	.on('click', '#fpd-preview-buttons span', function() {

		var $this = $(this);

		$this.toggleClass('fpd-active').siblings().removeClass('fpd-active');

		$fpd.removeClass(function (index, className) {
		    return (className.match (/(^|\s)fpd-responsive-\S+/g) || []).join(' '); //remove class that starts with fpd-responsive-
		}).removeClass('fpd-responsive');

		if($this.hasClass('fpd-active')) {

			$fpd.addClass('fpd-responsive fpd-responsive-'+$this.data('value'));
			viewInstance.options.responsive = true;
			viewInstance.resetCanvasSize();

		}
		else {
			viewInstance.options.responsive = false;
			viewInstance.resetCanvasSize();
		}

	});

	//init fancy product designer
	$fpd.on('ready', function() {

		fancyProductDesigner = $(this).data('instance');

		fpdActions = new FPDActions(fancyProductDesigner, false);

		$.ajax({
			url: fpd_admin_opts.adminAjaxUrl,
			data: {
				action: 'fpd_loadview',
				_ajax_nonce: fpd_admin_opts.ajaxNonce,
				view_id: $fpd.data('viewid')
			},
			type: 'post',
			dataType: 'json',
			success: function(data) {

				initialProduct = [{
					title: 'preview',
					thumbnail: '',
					elements: [],
					options: {}
				}];

				if(data && data.elements) {

					//V2 - views are serialized - string is newer
					var elements = typeof data.elements === 'string' ? JSON.parse(data.elements) : data.elements;

					//check that all number stings are parsed as number, <V3.0
					for(var i=0; i < elements.length; ++i) {

						var elementParams = elements[i].parameters;

						$.each(elementParams, function(key, value) {
							//value is number or not text property, parse as number
							elementParams[key] = isNaN(value) || key === 'text' ? value : Number(value);
						});


						delete elements[i].parameters['originParams'];

					}

					initialProduct[0].elements = elements;
				}

				fancyProductDesigner.toggleSpinner(true, fpd_product_builder_opts.loading);
				fancyProductDesigner.loadProduct(initialProduct);

			}
		});

	})
	.on('elementAdd', function(evt, element) {

		if(element.adminLocked) {
			element.set('evented', false);
		}

		var type = FPDUtil.getType(element.type),
			imageHTML = type === 'image' ? "<img src='"+element.source+"' />" : "",
			imageToolHTML = type === 'image' ? "<a href='#' class='fpd-change-image'><i class='fpd-admin-icon-repeat'></i></a>" : "",
			lockedIcon = element.adminLocked ? "fpd-admin-icon-lock" : "fpd-admin-icon-lock-open";

			//new element added
			if(element.__new) {
				element.center();
				element.setCoords();
				stage.renderAll();
				delete element['__new'];
			}

		$elementLists.append("<div id='"+element.id+"' class='fpd-layer-item fpd-layer-item--"+type+"'><input type='text' value='"+element.title+"' placeholder='Title' class='radykal-input' />"+imageHTML+"<textarea placeholder='Text Content' class='radykal-input'>"+(element.text ? element.text : element.source)+"</textarea><div class='fpd-layer-item-tools'>"+imageToolHTML+"<a href='#' class='fpd-lock-element'><i class='"+lockedIcon+"'></i></a><a href='#' class='fpd-trash-element'><i class='fpd-admin-icon-bin'></i></a></div></div>");

		if(productCreated) {
			stage.setActiveObject(element);
		}

	})
	.on('elementSelect', function(evt, element) {

		if(element === null) {
			$fpdContainer.removeClass('fpd-element-selected');
		}
		else {

			if(element._ignore) {
				return;
			}

			$fpdContainer.addClass('fpd-element-selected');

			if($currentListItem && element.id !== $currentListItem.attr('id')) {
				$currentListItem.find('textarea, input').blur();
			}

			_updateFormState(element);
			_setFormFields(element);

			setTimeout(function(){
				element.setCoords();
				stage.calcOffset().renderAll();
			}, 50);

			element.drawBorders(stage.contextContainer);
			element.drawControls(stage.contextContainer);

		}

	})
	.on('productCreate', function() {

		viewInstance = fancyProductDesigner.currentViewInstance;
		stage = viewInstance.stage;

		if(fpdGlobalProductBuilderOpts.printingBox) {

			var printingBox = fpdGlobalProductBuilderOpts.printingBox;
			_updatePrintingBox(printingBox.width, printingBox.height, printingBox.left, printingBox.top);

		}

		$sidebarForm.find('[name="bounding_box_control"]').change(function() {

			var $this = $(this),
				visibleBBInputs = this.checked ? $sidebarForm.find($this.data('checkedsel')) : $sidebarForm.find($this.data('uncheckedsel')).find('input');

			visibleBBInputs.change();
			viewInstance.renderElementBoundingBox(viewInstance.currentElement);

		})

		stage.on({
			'object:moving': function(opts) {

				if(opts.target.name === 'printing-boxes') {

					$printingBoxToolbar.find('[name="left"]').val(parseInt(viewInstance.printingBoxObject.left));
					$printingBoxToolbar.find('[name="top"]').val(parseInt(viewInstance.printingBoxObject.top));

				}
				else if(!opts.target._ignore) {
					$sidebarForm.find('[name="left"]').val(parseInt(opts.target.left));
					$sidebarForm.find('[name="top"]').val(parseInt(opts.target.top));
				}

			},
			'object:scaling': function(opts) {

				if(opts.target.name === 'printing-boxes') {
					$printingBoxToolbar.find('[name="pb_width"]').val(parseInt(parseInt(viewInstance.printingBoxObject.width) * viewInstance.printingBoxObject.scaleX));
					$printingBoxToolbar.find('[name="pb_height"]').val(parseInt(parseInt(viewInstance.printingBoxObject.height) * viewInstance.printingBoxObject.scaleY));
				}

				if(FPDUtil.getType(opts.target.type) === 'image') {
					_setScaleToInputs();
				}

			},
			'object:modified': function(opts) {

				if(opts.target.name === 'view-mask') {
					_setMaskFields();
				}

			}
		});

		if($fpd.data('viewmask') && typeof $fpd.data('viewmask') === 'object') {
			viewInstance.setMask($fpd.data('viewmask'));
		}

		$manageLayersPanel.children('.fpd-ui-blocker').hide();
		productCreated = true;

		var elements = fancyProductDesigner.getElements();

		if(elements[0]) {
			setTimeout(function() {
				stage.setActiveObject(elements[0]);
				stage.renderAll();
			}, 1);

		}

	})
	.on('elementRemove', function(evt, element) {

		$elementLists.children('#'+element.id).remove();
		changesAreSaved = false;

	})
	.on('elementModify', function(evt, element, parameters) {

		if(productCreated) {

			if(parameters.text) {
				$elementLists.children('.fpd-layer-item#'+element.id+'')
				.find('textarea').val(parameters.text);
			}

		}

	})
	.on('undoRedoSet', function(evt, undos, redos) {

		_toggleUndoRedoBtn(undos, redos);

	});

	$sidebar.on('click', '#fpd-element-props-sidebar-close', function() {

		$sidebar.toggleClass('fpd-closed');

	})
	.on('click', '#fpd-element-props-sidebar-switcher', function() {

		$sidebar.toggleClass('fpd-dock-left');

	});

	//initi FPD
	new FancyProductDesigner($fpd, pluginsOptions);

	//add new element buttons handler
	$('#fpd-add-image-element, #fpd-add-text-element, #fpd-add-curved-text-element, #fpd-add-upload-zone, #fpd-add-text-box-element').click(function(evt) {
		evt.preventDefault();

		fancyProductDesigner.deselectElement();
		$currentListItem = null;

		var $this = $(this);

		radykalPrompt({placeholder: fpd_product_builder_opts.enterTitlePrompt}, function(title) {

			if(title === false) {
				fpdMessage(fpd_admin_opts.enterTitlePrompt, 'error');
			}
			else if(title !== null) {

				var defaultProps = {
					removable: false,
					draggable: false,
					rotatable: false,
					resizable: false,
					zChangeable: false,
					__new: true
				};

				//add image or upload zone
				if($this.attr('id') == 'fpd-add-image-element' || $this.attr('id') == 'fpd-add-upload-zone') {

					var addUploadZone = $this.attr('id') == 'fpd-add-upload-zone';

			        mediaUploader = wp.media({
			            title: fpd_product_builder_opts.chooseElementImageTitle,
			            button: {
			                text: fpd_product_builder_opts.set
			            },
			            multiple: false
			        });

					mediaUploader.elementTitle = title;
					mediaUploader.on('select', function() {

						var imgObj = mediaUploader.state().get('selection').toJSON()[0];

						if(addUploadZone) {
							defaultProps.uploadZone = 1;
							defaultProps.adds_uploads = defaultProps.adds_texts = defaultProps.adds_designs = 1;
						}

						_getScaleCanvasFit(imgObj, function(scale) {

							defaultProps.scaleX = defaultProps.scaleY = scale;

							fancyProductDesigner.addElement(
								'image',
								imgObj.url,
								mediaUploader.elementTitle,
								defaultProps
							);

							mediaUploader = null;

						});

			        });

			        mediaUploader.open();

				}
				//add text
				else {

					if($this.attr('id') == 'fpd-add-curved-text-element') {
						defaultProps.curved = 1;
						defaultProps.curvable = true;
						defaultProps.textAlign = 'center';
					}
					else if($this.attr('id') == 'fpd-add-text-box-element') {
						defaultProps.textBox = true;
						defaultProps.width = 200;
						defaultProps.height = 100;
					}

					fancyProductDesigner.addElement(
						'text',
						title,
						title,
						defaultProps
					);

				}

			}

		});

    });


	//edit view mask
	var $maskToolbar = $('#fpd-mask-toolbar'),
		maskOptionsLoaded = false;

	$maskToolbar.draggable({
		containment: '.wrap#fpd-product-builder'
	});

	$('#fpd-edit-mask').click(function() {

		if(!productCreated) {
			return;
		}

		if(!$maskToolbar.hasClass('fpd-show')) {

			toggleModalLoader($maskToolbar, true);
			$.ajax({
				url: fpd_admin_opts.adminAjaxUrl,
				data: {
					action: 'fpd_loadviewoptions',
					_ajax_nonce: fpd_admin_opts.ajaxNonce,
					view_id: $fpd.data('viewid')
				},
				type: 'post',
				dataType: 'json',
				success: function(data) {

					if(data && data.options) {

						currentViewOptions = data.options;
						if(!_.isObject(currentViewOptions)) {
							currentViewOptions = JSON.parse(currentViewOptions);
						}

						if(viewInstance.maskObject) {

							_setMaskFields();
							_setVisualMask(true);

							if(currentViewOptions.mask && currentViewOptions.mask.url) {
								$maskToolbar.find('.fpd-single-image-upload img').remove();
								$maskToolbar.find('.fpd-single-image-upload').append('<img src="'+currentViewOptions.mask.url+'" />')
								.children('input').val(currentViewOptions.mask.url);

							}

							stage.setActiveObject(viewInstance.maskObject).renderAll();
						}

					}

					toggleModalLoader($maskToolbar);

				}
			});

			$maskToolbar.addClass('fpd-show');
		}
		else {
			_setVisualMask(false);
			$maskToolbar.removeClass('fpd-show');
		}

	});

	$maskToolbar.find('.fpd-single-image-upload').click(function(evt) {

		evt.preventDefault();

		mediaUploader = wp.media({
            title: fpd_product_builder_opts.chooseElementImageTitle,
            button: {
                text: fpd_product_builder_opts.set
            },
            multiple: false
        });

		mediaUploader.on('select', function() {

			_setVisualMask(false);
			fancyProductDesigner.currentViewInstance.setMask(null);

			var imgObj = mediaUploader.state().get('selection').toJSON()[0],
				imageURL = mediaUploader.state().get('selection').toJSON()[0].url;

			if($.inArray('svg', imageURL.split('.')) != -1) {
				$maskToolbar.find('.fpd-single-image-upload img').remove();
				$maskToolbar.find('.fpd-single-image-upload').append('<img src="'+imageURL+'" />')
				.children('input').val(imageURL);

				var maskOpts = {
					url: imageURL
				};

				//check paths length
				fabric.loadSVGFromURL(imageURL, function(objects) {
					if(objects.length > 1) {
						fpdMessage(fpd_product_builder_opts.mask_svg_path_alert, 'error');
					}
				});

				_getScaleCanvasFit(imgObj, function(scale) {

					maskOpts.scaleX = maskOpts.scaleY = scale;

					fancyProductDesigner.currentViewInstance.setMask(maskOpts, function(maskObject) {

						_setVisualMask(true);

						maskObject.center();
						maskObject.setCoords();
						stage.setActiveObject(maskObject).renderAll();

						_setMaskFields();


					});

				});

			}
			else {
				fpdMessage(fpd_product_builder_opts.mask_svg_alert, 'error');
			}

			mediaUploader = null;
        });

        mediaUploader.open();

	});

	//remove view image
	$maskToolbar.find('.fpd-single-image-upload > .fpd-remove').click(function(evt) {

		evt.preventDefault();
		evt.stopPropagation();

		$maskToolbar.find('.fpd-single-image-upload img').remove();
		_setVisualMask(false);
		fancyProductDesigner.currentViewInstance.setMask(null);
		_setMaskFields();

	});

	$maskToolbar.find('input[type="number"]').change(function(evt) {

		if(viewInstance && viewInstance.maskObject) {

			if(this.name === 'mask_width' || this.name === 'mask_height') {
				this.name === 'mask_width' ? viewInstance.maskObject.scaleToWidth(parseInt(this.value)) : viewInstance.maskObject.scaleToHeight(parseInt(this.value));
			}
			else {
				viewInstance.maskObject[this.name] = parseInt(this.value);
			}

			viewInstance.maskObject.setCoords();
			viewInstance.stage.renderAll();

		}

	});

	$('#fpd-save-mask-options').click(function() {

		_setVisualMask(false);

		var maskValues = null;
		if(viewInstance.maskObject) {
			maskValues = {
				url: $maskToolbar.find('[name="url"]').val(),
				left: viewInstance.maskObject.left,
				top: viewInstance.maskObject.top,
				scaleX: viewInstance.maskObject.scaleX,
				scaleY: viewInstance.maskObject.scaleY
			};
		}

		currentViewOptions = currentViewOptions === null || currentViewOptions.length === 0 ? {} : currentViewOptions;

		var optionsData = $.extend({}, currentViewOptions, {mask: maskValues});
		optionsData = fpdIsEmptyObject(optionsData) ? '' : optionsData;

		toggleModalLoader($maskToolbar, true);
		$.ajax({
			url: fpd_admin_opts.adminAjaxUrl,
			data: {
				action: 'fpd_editview',
				_ajax_nonce: fpd_admin_opts.ajaxNonce,
				id: $fpd.data('viewid'),
				options: JSON.stringify(optionsData)
			},
			type: 'post',
			dataType: 'json',
			success: function(data) {

				toggleModalLoader($maskToolbar);

				if(!_.isUndefined(data) && !_.isUndefined(data.columns)) {

					if(!_.isUndefined(data.columns.options)) {
						fpdMessage(data.message, 'success');
					}
					else {
						fpdMessage(fpd_admin_opts.tryAgain, 'error');
					}

				}

				$maskToolbar.removeClass('fpd-show');

			}
		});

	});

	function _setVisualMask(showHide) {

		if(!viewInstance.maskObject) {
			return;
		}

		if(showHide) {

			viewInstance.maskObject.setOptions({
				fill: 'rgba(184,233,134,0.1)',
				strokeWidth: 1,
				stroke: '#B8E986',
				evented: true
			});

			viewInstance.stage.add(viewInstance.maskObject);
			viewInstance.maskObject.setCoords();

		}
		else {

			viewInstance.maskObject.setOptions({
				fill: 'rgba(0,0,0,0)',
				strokeWidth: 0,
				evented: false
			});

			viewInstance.stage.remove(viewInstance.maskObject);

		}

		viewInstance.stage.renderAll();

	};

	function _setMaskFields() {

		if(viewInstance.maskObject) {

			var maskOpts = viewInstance.maskObject;

			$maskToolbar.find('[name="left"]').val(parseInt(maskOpts.left));
			$maskToolbar.find('[name="top"]').val(parseInt(maskOpts.top));
			$maskToolbar.find('[name="mask_width"]').val(parseInt(parseInt(maskOpts.width) * maskOpts.scaleX));
			$maskToolbar.find('[name="mask_height"]').val(parseInt(parseInt(maskOpts.height) * maskOpts.scaleY));

		}
		else {
			$maskToolbar.find('input').val('');
		}

	}


	//edit printing box
	var $printingBoxToolbar = $('#fpd-printing-box-toolbar'),
		printingBoxOptionsLoaded = false;

	$printingBoxToolbar.draggable({
		containment: '.wrap#fpd-product-builder'
	});

	$('#fpd-edit-printing-box').click(function() {

		if(!$printingBoxToolbar.hasClass('fpd-show')) {

			toggleModalLoader($printingBoxToolbar, true);
			$.ajax({
				url: fpd_admin_opts.adminAjaxUrl,
				data: {
					action: 'fpd_loadviewoptions',
					_ajax_nonce: fpd_admin_opts.ajaxNonce,
					view_id: $fpd.data('viewid')
				},
				type: 'post',
				dataType: 'json',
				success: function(data) {

					if(data && data.options) {

						currentViewOptions = data.options;
						if(!_.isObject(currentViewOptions)) {
							currentViewOptions = JSON.parse(currentViewOptions);
						}

						$printingBoxToolbar.find('[name="left"]').val(parseInt(viewInstance.printingBoxObject.left));
						$printingBoxToolbar.find('[name="top"]').val(parseInt(viewInstance.printingBoxObject.top));
						$printingBoxToolbar.find('[name="pb_width"]').val(parseInt(parseInt(viewInstance.printingBoxObject.width) * viewInstance.printingBoxObject.scaleX));
						$printingBoxToolbar.find('[name="pb_height"]').val(parseInt(parseInt(viewInstance.printingBoxObject.height) * viewInstance.printingBoxObject.scaleY));

						viewInstance.printingBoxObject.evented = true;
						stage.setActiveObject(viewInstance.printingBoxObject);

					}

					toggleModalLoader($printingBoxToolbar);

				}
			});

			$printingBoxToolbar.addClass('fpd-show');
		}
		else {

			viewInstance.printingBoxObject.evented = false;
			stage.discardActiveObject().renderAll();
			$printingBoxToolbar.removeClass('fpd-show');

		}

	});

	$printingBoxToolbar
	.on('change input', 'input', function() {

		if(viewInstance.printingBoxObject) {

			if(this.name === 'pb_width' || this.name === 'pb_height') {

				if(this.name === 'pb_width') {

					var width = parseInt(this.value),
						aspectRatio = parseFloat(width / viewInstance.printingBoxObject.width);

					viewInstance.printingBoxObject.scaleX = aspectRatio;
					viewInstance.printingBoxObject.scaleY = aspectRatio;

					$printingBoxToolbar.find('[name="pb_height"]').val(parseInt(viewInstance.printingBoxObject.height * aspectRatio));

				}
				else {

					var height = parseInt(this.value),
						aspectRatio = parseFloat(height / viewInstance.printingBoxObject.height);

					viewInstance.printingBoxObject.scaleX = aspectRatio;
					viewInstance.printingBoxObject.scaleY = aspectRatio;

					$printingBoxToolbar.find('[name="pb_width"]').val(parseInt(viewInstance.printingBoxObject.width * aspectRatio));
				}

			}
			else {
				viewInstance.printingBoxObject[this.name] = parseInt(this.value);
			}

			viewInstance.printingBoxObject.setCoords();
			stage.renderAll();

		}

	})
	.on('click', '#fpd-pb-center-h, #fpd-pb-center-v', function(evt) {

		evt.preventDefault();

		if(this.id === 'fpd-pb-center-h') {
			viewInstance.printingBoxObject.centerH();
			$printingBoxToolbar.find('[name="left"]').val(parseInt(viewInstance.printingBoxObject.left));
		}
		else {
			viewInstance.printingBoxObject.centerV();
			$printingBoxToolbar.find('[name="top"]').val(parseInt(viewInstance.printingBoxObject.top));
		}

	});

	$('#fpd-save-printing-box-options').click(function() {

		var bbBoundingRect = viewInstance.printingBoxObject.getBoundingRect();
		Object.keys(bbBoundingRect).forEach(function(key) {
			bbBoundingRect[key] = parseInt(bbBoundingRect[key]);
		});

		currentViewOptions = currentViewOptions === null || currentViewOptions.length === 0 ? {} : currentViewOptions;

		var optionsData = $.extend({}, currentViewOptions, {printing_box: bbBoundingRect});
		optionsData = fpdIsEmptyObject(optionsData) ? '' : optionsData;

		toggleModalLoader($printingBoxToolbar, true);

		$.ajax({
			url: fpd_admin_opts.adminAjaxUrl,
			data: {
				action: 'fpd_editview',
				_ajax_nonce: fpd_admin_opts.ajaxNonce,
				id: $fpd.data('viewid'),
				options: JSON.stringify(optionsData)
			},
			type: 'post',
			dataType: 'json',
			success: function(data) {

				toggleModalLoader($printingBoxToolbar);

				if(!_.isUndefined(data) && !_.isUndefined(data.columns)) {

					if(!_.isUndefined(data.columns.options)) {
						fpdMessage(data.message, 'success');
					}
					else {
						fpdMessage(fpd_admin_opts.tryAgain, 'error');
					}

				}

			}
		});

		viewInstance.printingBoxObject.evented = false;
		stage.discardActiveObject().renderAll();
		$printingBoxToolbar.removeClass('fpd-show');

	});

	var _updatePrintingBox = function(width, height, left, top) {

		var printingBox = null;
		if(width && height) {

			var bbWidth = parseInt(width),
				bbHeight = parseInt(height);

			printingBox = {width: bbWidth, height: bbHeight};

			if(_.isNumber(top) && _.isNumber(left)) { // set existing printing box


				printingBox.left = left;
				printingBox.top = top;

			}
			else { //set new printing box and scale to fit in canvas and center

				if(bbWidth > bbHeight) {
					if(bbWidth > viewInstance.options.stageWidth) {

						var scale = viewInstance.options.stageWidth / bbWidth;
						printingBox.width = viewInstance.options.stageWidth;
						printingBox.height = scale * bbHeight;

					}
				}
				else {
					if(bbHeight > viewInstance.options.stageHeight) {

						var scale = viewInstance.options.stageHeight / bbHeight;
						printingBox.width = scale * bbWidth;
						printingBox.height = viewInstance.options.stageHeight;

					}
				}

				printingBox.left = 0;
				printingBox.top = 0;

			}

			$('#fpd-edit-printing-box').removeClass('radykal-hidden');

		}
		else {
			$('#fpd-edit-printing-box').addClass('radykal-hidden');
		}

		viewInstance.options.printingBox = printingBox;
		viewInstance.renderPrintingBox();

	};

	//form change handler
	$sidebarForm.on('change keyup', 'input, select', function(evt) {

		var $option = $(this),
			type = $option.attr('type');

		if(viewInstance && viewInstance.currentElement && updatingFormFields === false) {

			var params = {},
				key = $option.attr('name'),
				value = $option.val();

			if(type == 'checkbox') {

				params[key] = $option.is(':checked') ? 1 : 0;
				if($option.hasClass('fpd-toggle-button')) {
					params[key] = $option.is(':checked') ?  value : $option.data('unchecked');
				}

				if($option.data('checkedsel')) {
					$($option.data('checkedsel')).toggle($option.is(':checked'));
				}

				if($option.data('uncheckedsel')) {
					$($option.data('uncheckedsel')).toggle(!$option.is(':checked'));
				}

			}
			else if(key == 'fill' && !FPDUtil.isHex(value)) {
				params.fill = false;
			}
			else if(key === 'hidden-_color') {
				params.colors = value;
			}
			else if($option.hasClass('fpd-scale-to')) {

				key === 'width' ? viewInstance.currentElement.scaleToWidth(Number(value)) : viewInstance.currentElement.scaleToHeight(Number(value));

				stage.renderAll();

				_setScaleToInputs(key === 'width' ? 'height' : 'width');

			}
			else if(key === 'width') { //textbox
				params.width = value < 20 ? 20 : Number(value);
			}
			else if(key === 'designCategories[]') { //upload zone
				params.designCategories = value;
			}
			else if(key.indexOf('bounding_box_') != -1) { //textbox

				var bbKey = key.replace('bounding_box_', '');
				if(bbKey == 'by_other') { //element bb
					params.boundingBox = value;
				}
				else { //custom bb
					var elementBB = viewInstance.currentElement.boundingBox;
					elementBB = typeof elementBB === 'object' ? elementBB : {};
					elementBB[bbKey] = Number(value);
					params.boundingBox = elementBB;
				}

				viewInstance.renderElementBoundingBox(viewInstance.currentElement);

			}
			else {
				params[key] = isNaN(value) || value === '' ? value : Number(value);
			}

			_optionHandling($option);

			if(!$option.hasClass('fpd-ignore')) {
				viewInstance.setElementParameters(params);
			}

		}

	})
	.on('keypress', function(evt) {

		if (evt.keyCode == 13) {
			$(evt.target).change();
			return false;
		}

	})
	.on('change', '[type="checkbox"][name="colors"]', function() {

		updatingFormFields = true;

		if(!this.checked) {
			$sidebarForm.find('[name="_color"]').tagsManager('empty');
		}

		updatingFormFields = false;

	});

	//enable editing of the form when an element is selected in stage
	var _updateFormState = function(element) {

		updatingFormFields = true;

		$sidebarForm.find('[name="_color"]').tagsManager('empty');

		//object is selected
		if(element && element.selectable) {

			$sidebarForm.find('input, select').prop('disabled', false);
			$elementLists.children('.fpd-layer-item').removeClass('fpd-active-item');
			$currentListItem = $elementLists.children('#'+element.id).addClass('fpd-active-item');

			_hideTab('colors', FPDUtil.elementIsColorizable(element) === false);
			_hideTab('bounding-box', Boolean(element.uploadZone));
			_hideTab('text', FPDUtil.getType(element.type) !== 'text');
			_hideTab('upload-zone', !element.uploadZone);

			if(FPDUtil.elementIsColorizable(element) === 'svg') {
				//if every path color is false or colors are set
				_toggleFormFields('.fpd-color-options:first input', Number(element.colors) === 0 || $.isArray(element.colors));
			}
			else {
				_toggleFormFields('.fpd-color-options input', FPDUtil.elementIsColorizable(element));
			}

			//color link group (disable for path groups)
			$('.fpd-color-options').not(':first').toggleClass('radykal-hidden', (fabric.version === '1.6.7' && element.type === FPDPathGroupName) || element.type === 'group');
			_toggleFormFields('.fpd-svg-options', FPDUtil.elementIsColorizable(element) === 'svg');
			_toggleFormFields('.fpd-upload-zone-hidden', !element.uploadZone);
			_toggleFormFields('.fpd-upload-zone-show', element.uploadZone);
			_toggleFormFields('[name="resizable"]', element.type !== 'textbox');
			_toggleFormFields('[name="curvable"]', element.type === 'i-text' || element.type == 'curvedText');
			_toggleFormFields('[name="minFontSize"], [name="maxFontSize"], [name="widthFontSize"]', element.type !== 'textbox');
			_toggleFormFields('.fpd-text-hidden', FPDUtil.getType(element.type) !== 'text');
			_toggleFormFields('[name="advancedEditing"]', FPDUtil.getType(element.type) === 'image');
			_toggleFormFields('#fpd-tab-text ~ .fpd-content [name="width"]', element.textBox);

		}
		//no selected object
		else {

			$elementLists.children('.fpd-layer-item').removeClass('fpd-active-item');
			$currentListItem = null;

		}

		//$sidebarForm.find('[name="accordion_sidebar"]:first').click();

	};

	//update form fields when element is changed via product stage
	var _setFormFields = function(element) {

		updatingFormFields = true;

		//old bounding box <V3.9.0
		if(!element.bounding_box_control && element.bounding_box_x && element.bounding_box_y && element.bounding_box_width && element.bounding_box_height) {
			element.boundingBox = {x: element.bounding_box_x, y: element.bounding_box_y, width: element.bounding_box_width, height: element.bounding_box_height};
		}
		else if(element.bounding_box_by_other) { //old bounding box <V3.9.0
			element.boundingBox = element.bounding_box_by_other;
		}

		$sidebarForm.find('input, select').each(function(i, option) {

			var $option = $(option),
				type = $option.attr('type'),
				prop = $option.attr('name');

			if(type == 'text' || type == 'number') {

				if(element.hasOwnProperty(prop) && element[prop] !== false) {
					var value = element[prop];

					if(type == 'number') {
						value = $option.hasClass('fpd-allow-dots') ? parseFloat(value).toFixed(2) : parseInt(value);
					}
					else {
						value = isNaN(value) ? value : '';
					}

					$option.val(value);

					if($option.prev('.ui-slider').length > 0) {
						$option.prev('.ui-slider').slider('value', Number(value));
					}

					if($option.hasClass('wp-color-picker')) {
						$option.wpColorPicker('color', value);
					}
					else if($option.hasClass('fpd-scale-to')) {
						_setScaleToInputs();
					}

				}
				else {
					$option.val('');
				}

			}
			else if(type == 'checkbox') {

				if($option.hasClass('fpd-toggle-button')) {
					$option.prop('checked', element[prop] == $option.val());
					$option.prevAll('.fpd-'+$option.val()+'.button').toggleClass('active', $option.is(':checked'));
				}
				else {

					var value = element[prop];
					if(prop === 'colors') {
						value =  value == 1;
					}
					else if(prop === 'bounding_box_control') {
						value = typeof element.boundingBox === 'string';
					}
					else {
						value = Boolean(value) || false;
					}
					$option.prop('checked', Boolean(value) || false);

					if($option.data('checkedsel')) {
						$($option.data('checkedsel')).toggle($option.is(':checked'));

					}

					if($option.data('uncheckedsel')) {
						$($option.data('uncheckedsel')).toggle(!$option.is(':checked'));

					}

				}

			}
			else if(type == 'radio') {

				if(element[prop] !== undefined) {
					$option.prop('checked', element[prop] == $option.val());
				}

				if(prop == 'colors' && isNaN(formElementOpts[prop])) {
					$option.prop('checked', Number($option.val()) === 0);
				}

			}
			else if($option.is('select')) {

				if(prop == 'fontFamily') {
					$option.val(element.hasOwnProperty(prop) ? element[prop] : fpd_product_builder_opts.defaultFont);
				}
				else if(prop == 'designCategories[]') {
					$option.val(element.hasOwnProperty(prop) ? element[prop] : '');
				}
				else {
					//set select to value by element property. If element has no propery with this key, set select to first option
					$option.val(element.hasOwnProperty(prop) ? element[prop] :$option.children('option:first').val());
				}

				if($option.attr('multiple')) {

					var selectValues = element[prop];
					if(prop == 'designCategories[]' && element.designCategories) {
						selectValues = element.designCategories;
					}

					if($.isArray(selectValues)) {

						selectValues.forEach(function(val) {
							$option.children('option[value="'+val+'"]').prop('selected', true);
						});
						$option.trigger('change');

					}

				}

			}
			else if($option.hasClass('fpd-radio-buttons')) {

				$option.val((element[prop] || $option.val()))
				.siblings('span').removeClass('fpd-active')
				.filter('[data-value="'+(element[prop] || $option.val())+'"]').addClass('fpd-active');

			}

			_optionHandling($option);


		});

		//set color tags
		if(element.colors && element.colors.length > 0 && unescape(element.colors).charAt(0) == '#') {

			var colorArray = unescape(element.colors).split(',');
			for(var i=0; i < colorArray.length; ++i) {
				$sidebarForm.find('[name="_color"]').tagsManager('pushTag', colorArray[i]);
			}

		}

		if(element.boundingBox) {

			if(typeof element.boundingBox == 'object') {
				Object.keys(element.boundingBox).forEach(function(bbKey) {
					$sidebarForm.find('[name="bounding_box_'+bbKey+'"]').val(element.boundingBox[bbKey]);
				})
			}
			else {
				$sidebarForm.find('[name="bounding_box_by_other"]').val(element.boundingBox);
			}

		}

		stage.calcOffset().renderAll();

		updatingFormFields = false;

	};

	var _toggleFormFields = function(selector, toggle) {

		toggle = toggle === undefined ? false : toggle;
		toggle = Boolean(toggle);

		$sidebarForm.find(selector).each(function(i, field) {

			var $field = $(field);

			//if input:text or number, reset input
			if(!toggle && ($field.is('input:text') || $field.attr('type') == 'number')) {
				$field.val('');
			}

			$field.prop('disabled', !toggle);

			if($field.parent().hasClass('fpd-group-field')) {
				$field.parent().toggle(toggle);
			}
			else {
				$field.parents('td:first').toggle(toggle);
			}


		});

	};

	var _hideTab = function(id, hide) {

		hide = hide === undefined ? true : hide;
		hide = Boolean(hide);

		$sidebarForm.find('#fpd-tab-'+id).parent('.fpd-accordion-tab').toggleClass('radykal-hidden', hide)
		.find('input, select').prop('disabled', hide);

	};

	var _toggleUndoRedoBtn = function(undos, redos) {

		if(undos.length === 0) {
		  	$('#fpd-undo').addClass('radykal-disabled');
  		}
  		else {
	  		$('#fpd-undo').removeClass('radykal-disabled');
  		}

  		if(redos.length === 0) {
	  		$('#fpd-redo').addClass('radykal-disabled');
  		}
  		else {
	  		$('#fpd-redo').removeClass('radykal-disabled');
  		}

	};

	var _optionHandling = function($option) {

		if($option.is('select')) {

			if($option.data('toggle')) {

				var toggles = $option.data('toggle').split(',');
				for(var i=0; i<toggles.length; ++i) {

					var toggleEntry = toggles[i].split('=');
					_toggleFormFields(toggleEntry[0], $option.val() == toggleEntry[1]);

				}

			}

		}

	};

	var _setScaleToInputs = function(target) {

		if(viewInstance && viewInstance.currentElement) {

			if(target) {

				var scaledDim = target == 'width' ? viewInstance.currentElement.width * viewInstance.currentElement.scaleX : viewInstance.currentElement.height * viewInstance.currentElement.scaleY;
				$sidebarForm.find('.fpd-scale-to[name="'+target+'"]').val(parseInt(scaledDim));
			}
			else {
				$sidebarForm.find('.fpd-scale-to[name="width"]').val(parseInt(viewInstance.currentElement.width * viewInstance.currentElement.scaleX));
				$sidebarForm.find('.fpd-scale-to[name="height"]').val(parseInt(viewInstance.currentElement.height * viewInstance.currentElement.scaleY));
			}


		}

	};

	//imgObj: wp media library, scale image to fit into canvas
	var _getScaleCanvasFit = function(imgObj, callback) {

		var iconTolerance = fancyProductDesigner.mainOptions.elementParameters.cornerSize;

		//svg
		if(!imgObj.width && (!imgObj.sizes || !imgObj.sizes.full || imgObj.sizes.full.width.length === 0)) {

			fabric.util.loadImage(imgObj.url, function(img){

				callback(FPDUtil.getScalingByDimesions(
					img.width,
					img.height,
					fancyProductDesigner.currentViewInstance.options.stageWidth - iconTolerance,
					fancyProductDesigner.currentViewInstance.options.stageHeight - iconTolerance
				));
			})

			return;

		}

		var imgWidth = imgObj.width ? imgObj.width : parseInt(imgObj.sizes.full.width.replace('px', '')),
			imgHeight = imgObj.height ? imgObj.height : parseInt(imgObj.sizes.full.height.replace('px', ''));

		if((imgWidth + iconTolerance) > fancyProductDesigner.currentViewInstance.options.stageWidth
			|| (imgHeight + iconTolerance) > fancyProductDesigner.currentViewInstance.options.stageHeight) {

			return callback(FPDUtil.getScalingByDimesions(
				imgWidth,
				imgHeight,
				fancyProductDesigner.currentViewInstance.options.stageWidth - iconTolerance,
				fancyProductDesigner.currentViewInstance.options.stageHeight - iconTolerance
			));

		}

		return callback(1);

	}
	
	$('input[type="checkbox"]').addClass('wcfm-checkbox');

	//check if changes are saved before page unload
	/*$(window).on('beforeunload', function () {
		if(!changesAreSaved) {
			return fpd_product_builder_opts.notChanged;
		}
	});*/

});